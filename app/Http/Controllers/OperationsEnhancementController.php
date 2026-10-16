<?php

namespace App\Http\Controllers;

use App\Models\{Booking, BookingBatch, DosimeterRecord, Pallet, ProductionLine};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OperationsEnhancementController extends Controller
{
    private function role(array $allowed): void
    {
        abort_unless(in_array(auth('admin')->user()->role, $allowed, true), 403);
    }

    private function audit(string $action, string $type, int $id, array $details = []): void
    {
        DB::table('operational_audit_events')->insert([
            'admin_id' => auth('admin')->id(),
            'action' => $action,
            'subject_type' => $type,
            'subject_id' => $id,
            'details' => json_encode($details),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function quality(Booking $booking)
    {
        $this->role(['superadmin', 'manager', 'production']);
        $booking->load(['customer', 'batches.qa']);
        $record = DosimeterRecord::with('details')->where('booking_id', $booking->id)->first();
        $reviews = DB::table('quality_reviews')->where('booking_id', $booking->id)->latest()->get();
        return view('admin.enhancements.quality', compact('booking', 'record', 'reviews'));
    }

    public function qualityReview(Request $request, Booking $booking)
    {
        $this->role(['superadmin', 'manager']);
        $data = $request->validate(['decision' => 'required|in:reviewed,hold,needs_correction', 'notes' => 'nullable|string|max:2000']);
        DB::transaction(function () use ($booking, $data) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $record = DosimeterRecord::with('details')->where('booking_id', $booking->id)->first();
            if ($data['decision'] === 'reviewed') {
                if (!$record || $record->details->isEmpty() || $record->details->contains(fn($d) => $d->absorbance === null || blank($d->dosimeter_number))) {
                    throw ValidationException::withMessages(['decision' => 'All required dosimeter identifiers and measurements must be recorded before review.']);
                }
                if ($booking->batches()->doesntExist() || $booking->batches()->where('status', '!=', 'done')->exists() || $booking->batches()->whereDoesntHave('qa')->exists()) {
                    throw ValidationException::withMessages(['decision' => 'All batches must be finished with QA records before review.']);
                }
            }
            DB::table('quality_reviews')->insert(['booking_id' => $booking->id, 'decision' => $data['decision'], 'notes' => $data['notes'] ?? null, 'reviewed_by' => auth('admin')->id(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit('quality.review', 'booking', $booking->id, $data);
        });
        return back()->with('success', 'Quality review recorded. This does not authorize product release.');
    }

    public function documents(Booking $booking)
    {
        $this->role(['superadmin', 'manager', 'production', 'cargo_admin']);
        $drafts = DB::table('order_document_drafts')->where('booking_id', $booking->id)->orderByDesc('id')->get();
        return view('admin.enhancements.documents', compact('booking', 'drafts'));
    }

    public function generateDraft(Request $request, Booking $booking)
    {
        $this->role(['superadmin', 'manager', 'production', 'cargo_admin']);
        $data = $request->validate(['document_type' => 'required|in:receiving_summary,production_summary,quality_summary']);
        DB::transaction(function () use ($booking, $data) {
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            $booking->load(['customer', 'products', 'batches.productionLine', 'batches.qa', 'placementDetails']);
            $record = DosimeterRecord::with('details')->where('booking_id', $booking->id)->first();
            $snapshot = [
                'generated_at' => now()->toIso8601String(),
                'booking_code' => $booking->booking_code,
                'customer' => $booking->customer?->company_name,
                'booking_status' => $booking->status,
                'products' => $booking->products->map(fn($p) => $p->toArray())->all(),
                'placements' => $booking->placementDetails->map(fn($p) => $p->toArray())->all(),
                'batches' => $booking->batches->map(fn($b) => $b->toArray() + ['machine' => $b->productionLine?->name, 'qa' => $b->qa?->toArray()])->all(),
                'dosimeters' => $record?->details?->map(fn($d) => $d->toArray())->all() ?? [],
            ];
            $version = 1 + (int) DB::table('order_document_drafts')->where('booking_id', $booking->id)->where('document_type', $data['document_type'])->max('version');
            $id = DB::table('order_document_drafts')->insertGetId(['booking_id' => $booking->id, 'document_type' => $data['document_type'], 'version' => $version, 'snapshot' => json_encode($snapshot), 'status' => 'draft', 'created_by' => auth('admin')->id(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit('document.draft_created', 'order_document_draft', $id, ['booking_id' => $booking->id, 'type' => $data['document_type'], 'version' => $version]);
        });
        return back()->with('success', 'Versioned document draft created from recorded data. It is not an issued certificate.');
    }

    public function templateIndex()
    {
        $this->role(['superadmin', 'manager', 'production']);
        $templates = DB::table('production_parameter_templates')->orderByDesc('id')->get();
        $machines = ProductionLine::orderBy('name')->get();
        $editableBatches = BookingBatch::with('booking')->whereIn('status', ['pending', 'waiting'])->latest('id')->limit(100)->get();
        return view('admin.enhancements.templates', compact('templates', 'machines', 'editableBatches'));
    }

    public function storeTemplate(Request $request)
    {
        $this->role(['superadmin', 'production']);
        $v = $request->validate(['name' => 'required|string|max:150', 'production_line_id' => 'required|exists:production_lines,id', 'target_dose' => 'required|numeric|gt:0', 'beam_speed' => 'required|numeric|gt:0', 'loading_mode' => 'required|in:single_side,double_side', 'freq' => 'nullable|numeric|gte:0', 'scan_gear' => 'nullable|numeric|gte:0']);
        DB::transaction(function () use ($v) {
            $id = DB::table('production_parameter_templates')->insertGetId($v + ['approval_status' => 'draft', 'created_by' => auth('admin')->id(), 'created_at' => now(), 'updated_at' => now()]);
            $this->audit('template.created', 'production_parameter_template', $id);
        });
        return back()->with('success', 'Draft parameter template submitted for Manager review.');
    }

    public function approveTemplate(Request $request, int $id)
    {
        $this->role(['superadmin', 'manager']);
        $v = $request->validate(['decision' => 'required|in:approved,rejected', 'approval_notes' => 'nullable|string|max:1000']);
        DB::transaction(function () use ($id, $v) {
            $template = DB::table('production_parameter_templates')->where('id', $id)->lockForUpdate()->first();
            abort_unless($template, 404);
            if ($template->approval_status !== 'draft') throw ValidationException::withMessages(['decision' => 'Only draft templates can be reviewed.']);
            DB::table('production_parameter_templates')->where('id', $id)->update(['approval_status' => $v['decision'], 'approved_by' => auth('admin')->id(), 'approved_at' => now(), 'approval_notes' => $v['approval_notes'] ?? null, 'updated_at' => now()]);
            $this->audit('template.' . $v['decision'], 'production_parameter_template', $id);
        });
        return back()->with('success', 'Template review saved. Operators must still verify parameters for each batch.');
    }

    public function applyTemplate(Request $request, BookingBatch $batch)
    {
        $this->role(['superadmin', 'production']);
        $v = $request->validate(['template_id' => 'required|integer|exists:production_parameter_templates,id', 'confirmed' => 'accepted']);
        DB::transaction(function () use ($batch, $v) {
            $booking = Booking::whereKey($batch->booking_id)->lockForUpdate()->firstOrFail();
            $batch = BookingBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if (!in_array($batch->status, ['pending', 'waiting'], true)) throw ValidationException::withMessages(['template_id' => 'Parameters can only be prepared for batches not yet started.']);
            $t = DB::table('production_parameter_templates')->where('id', $v['template_id'])->where('approval_status', 'approved')->first();
            if (!$t) throw ValidationException::withMessages(['template_id' => 'Only Manager-approved templates can be applied.']);
            $products = $booking->products;
            if ($batch->booking_product_id !== null) $products = $products->where('id', $batch->booking_product_id);
            elseif ($products->count() > 1) throw ValidationException::withMessages(['template_id' => 'Assign this batch to its product before applying a template.']);
            if ($products->isEmpty()) throw ValidationException::withMessages(['template_id' => 'The booking has no verified products.']);
            $mode = ['single-side' => 'single_side', 'double-side' => 'double_side'][$t->loading_mode] ?? $t->loading_mode;
            if (!in_array($mode, ['single_side', 'double_side'], true)) throw ValidationException::withMessages(['template_id' => 'This template has an unsupported loading mode. Create a reviewed revision with Single Side or Double Side.']);
            foreach ($products as $product) {
                if ($product->dmin !== null && (float)$t->target_dose < (float)$product->dmin || $product->dmax !== null && (float)$t->target_dose > (float)$product->dmax) {
                    throw ValidationException::withMessages(['template_id' => 'Template target dose does not meet all booked product dose bounds.']);
                }
            }
            $batch->update(['production_line_id' => $t->production_line_id, 'target_dose' => $t->target_dose, 'beam_speed' => $t->beam_speed, 'loading_mode' => $mode, 'freq' => $t->freq, 'scan_gear' => $t->scan_gear, 'status' => 'waiting']);
            $this->audit('template.applied_to_batch', 'booking_batch', $batch->id, ['template_id' => $t->id]);
        }, 3);
        return back()->with('success', 'Approved parameter template copied to batch. Verify before start.');
    }

    public function warehouse()
    {
        $this->role(['superadmin', 'manager', 'cargo_admin']);
        $pallets = Pallet::orderBy('line')->orderBy('slot_section')->get();
        $movements = DB::table('pallet_movements')->latest()->limit(50)->get();
        return view('admin.enhancements.warehouse', compact('pallets', 'movements'));
    }

    public function relocateFromForm(Request $request)
    {
        $this->role(['superadmin', 'cargo_admin']);
        $data = $request->validate(['source_pallet_id' => 'required|integer|exists:pallets,id']);
        return $this->relocate($request, Pallet::findOrFail($data['source_pallet_id']));
    }

    public function relocate(Request $request, Pallet $pallet)
    {
        $this->role(['superadmin', 'cargo_admin']);
        $data = $request->validate([
            'destination_pallet_id' => 'required|integer|exists:pallets,id',
            'reason' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($pallet, $data) {
            $ids = collect([$pallet->id, (int) $data['destination_pallet_id']])->unique()->sort()->values();
            if ($ids->count() !== 2) {
                throw ValidationException::withMessages(['destination_pallet_id' => 'Choose a different destination slot.']);
            }

            $rows = Pallet::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $source = $rows[$pallet->id];
            $destination = $rows[(int) $data['destination_pallet_id']];
            $contents = $source->contents()->orderBy('id')->lockForUpdate()->get();
            $destinationContents = $destination->contents()->orderBy('id')->lockForUpdate()->get();
            $movingQuantity = (int) $contents->sum('quantity');

            if ($contents->isEmpty() || (int) $source->filled_boxes !== $movingQuantity
                || (int) $destination->filled_boxes !== (int) $destinationContents->sum('quantity')) {
                throw ValidationException::withMessages(['destination_pallet_id' => 'Source or destination inventory is inconsistent. Reconcile inventory before moving goods.']);
            }

            $bookingIds = $contents->pluck('booking_id')->unique()->values();
            foreach ($bookingIds as $bookingId) {
                $fromContentQty = (int) $contents->where('booking_id', $bookingId)->sum('quantity');
                $placements = DB::table('placement_details')
                    ->where('booking_id', $bookingId)
                    ->where('line', $source->line)
                    ->where('slot_section', $source->slot_section)
                    ->where('status', 'placed')
                    ->lockForUpdate()->get();
                if ((int) $placements->sum('quantity') !== $fromContentQty) {
                    throw ValidationException::withMessages(['destination_pallet_id' => 'Placement history does not match stored goods. Reconcile inventory first.']);
                }
                DB::table('placement_details')->whereIn('id', $placements->pluck('id'))
                    ->update(['line' => $destination->line, 'slot_section' => $destination->slot_section, 'updated_at' => now()]);
            }

            foreach ($contents as $content) {
                $content->update(['pallet_id' => $destination->id]);
            }

            $destination->update([
                'status' => 'filled',
                'filled_boxes' => (int) $destination->filled_boxes + $movingQuantity,
                'current_booking_id' => null,
            ]);
            $source->update(['status' => 'empty', 'filled_boxes' => 0, 'current_booking_id' => null]);

            $movementId = DB::table('pallet_movements')->insertGetId([
                'pallet_id' => $source->id,
                'booking_id' => $bookingIds->count() === 1 ? $bookingIds->first() : null,
                'from_line' => $source->line,
                'from_slot' => $source->slot_section,
                'to_line' => $destination->line,
                'to_slot' => $destination->slot_section,
                'reason' => $data['reason'] ?? 'Warehouse relocation',
                'moved_by' => auth('admin')->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->audit('pallet.contents_relocated', 'pallet', $source->id, [
                'movement_id' => $movementId,
                'destination_pallet_id' => $destination->id,
                'booking_ids' => $bookingIds->all(),
                'quantity' => $movingQuantity,
            ]);
        }, 3);

        return back()->with('success', 'All slot contents moved. Movement history recorded.');
    }

    public function auditIndex(Request $request)
    {
        $this->role(['superadmin', 'manager']);
        $events = DB::table('operational_audit_events')->orderByDesc('id')->paginate(30);
        return view('admin.enhancements.audit', compact('events'));
    }
}
