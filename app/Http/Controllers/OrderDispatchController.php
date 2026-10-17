<?php

namespace App\Http\Controllers;
use App\Services\SuperadminAccess;

use App\Models\Booking;
use App\Models\Pallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Dispatch is a separate physical handover, NOT an automatic side effect of production completion. */
class OrderDispatchController extends Controller
{
    private function requireRole(array $roles): void
    {
        abort_unless(SuperadminAccess::isSuperadmin() || in_array(auth('admin')->user()?->role, $roles, true), 403);
    }

    public function show(Booking $booking): View
    {
        $this->requireRole(['superadmin', 'manager', 'cargo_admin']);
        $booking->load(['customer', 'products', 'placementDetails']);
        $contents = DB::table('pallet_contents')->where('booking_id', $booking->id)->get();
        $locations = Pallet::whereIn('id', $contents->pluck('pallet_id')->unique())->orderBy('line')->orderBy('slot_section')->get();
        $dispatch = DB::table('order_dispatches')->where('booking_id', $booking->id)->first();
        $review = DB::table('quality_reviews')->where('booking_id', $booking->id)->latest('id')->first();
        $verificationWarnings = $dispatch?->status === 'dispatched'
            ? (json_decode($dispatch->verification_warnings ?? '[]', true) ?: [])
            : app(\App\Services\DosimetryRelease::class)->warnings($booking);
        $readinessIssue = null;
        if ($dispatch?->status !== 'dispatched') {
            try {
                $this->assertReady($booking);
            } catch (ValidationException $error) {
                $readinessIssue = collect($error->errors())->flatten()->first();
            }
        }
        return view('admin.workspace.dispatch', compact('booking', 'contents', 'locations', 'dispatch', 'review', 'readinessIssue', 'verificationWarnings'));
    }

    public function confirm(Request $request, Booking $booking): RedirectResponse
    {
        $this->requireRole(['superadmin', 'cargo_admin']);

        $data = $request->validate([
            'reference' => ['required', 'string', 'max:150'],
            'dispatch_notes' => ['nullable', 'string', 'max:2000'],
            'physical_handover' => ['accepted'],
            'quantity_confirmed' => ['accepted'],
        ]);

        DB::transaction(function () use ($booking, $data) {
            $locked = Booking::whereKey($booking->id)
                ->lockForUpdate()
                ->firstOrFail();



            $existingDispatch = DB::table('order_dispatches')
                ->where('booking_id', $locked->id)
                ->lockForUpdate()
                ->first();

            if ($existingDispatch?->status === 'dispatched' && SuperadminAccess::overriding()) {
                DB::table('order_dispatches')->where('id',$existingDispatch->id)->update([
                    'reference'=>$data['reference'], 'dispatch_notes'=>$data['dispatch_notes'] ?? null, 'updated_at'=>now(),
                ]);
                return; // Correct metadata without issuing the same goods twice.
            }
            if ($existingDispatch?->status === 'dispatched') {
                throw ValidationException::withMessages([
                    'dispatch' => 'This order has already been dispatched.',
                ]);
            }

            $this->assertReady($locked);
            $verificationWarnings = app(\App\Services\DosimetryRelease::class)->warnings($locked);

            $contents = DB::table('pallet_contents')->where('booking_id', $locked->id)->orderBy('pallet_id')->get();

            if ($contents->isEmpty()) {
                throw ValidationException::withMessages([
                    'dispatch' => 'No stored goods found for this order.',
                ]);
            }

            $pallets = Pallet::whereIn(
                'id',
                $contents->pluck('pallet_id')->unique()
            )
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if (SuperadminAccess::overriding()) foreach ($pallets as $pallet) \App\Services\WarehouseInventory::sync($pallet);
            $contents = DB::table('pallet_contents')->where('booking_id', $locked->id)->lockForUpdate()->get();
            foreach ($locked->products as $product) {
                $stored = $contents->where('booking_product_id', $product->id);
                if ((int) $stored->sum('quantity') !== (int) $product->quantity || $stored->contains(fn($row) => $row->unit !== $product->unit)) {
                    throw ValidationException::withMessages(['dispatch' => 'Stored product quantities or units do not match the order. Reconcile inventory first.']);
                }
            }
            $expectedQty = (int) $locked->products()->sum('quantity');
            $actualQty = (int) $contents->sum('quantity');

            if ($actualQty <= 0 || $actualQty !== $expectedQty) {
                throw ValidationException::withMessages([
                    'dispatch' =>
                    'Stored quantity does not match the order. ' .
                        'Check inventory before dispatch.',
                ]);
            }

            $released = [];

            foreach ($pallets as $pallet) {
                $allContents = DB::table('pallet_contents')
                    ->where('pallet_id', $pallet->id)
                    ->lockForUpdate()
                    ->get();

                $orderContents = $allContents
                    ->where('booking_id', $locked->id);

                if (
                    $orderContents->isEmpty() ||
                    $allContents->contains(fn($row) => $row->quantity <= 0) ||
                    $pallet->status !== 'filled' ||
                    (int) $pallet->filled_boxes !==
                    (int) $allContents->sum('quantity')
                ) {
                    throw ValidationException::withMessages([
                        'dispatch' =>
                        'Warehouse inventory is inconsistent. ' .
                            'Please check the affected slots.',
                    ]);
                }

                $placements = DB::table('placement_details')
                    ->where('booking_id', $locked->id)
                    ->where('line', $pallet->line)
                    ->where('slot_section', $pallet->slot_section)
                    ->lockForUpdate()
                    ->get();

                foreach ($orderContents->groupBy('booking_product_id') as $productId => $rows) {
                    if ((int) $placements->where('status', 'placed')->where('booking_product_id', $productId)->sum('quantity') !== (int) $rows->sum('quantity')) {
                        throw ValidationException::withMessages(['dispatch' => 'Product placement records need reconciliation before dispatch.']);
                    }
                }
                $placementQty = (int) $placements
                    ->where('status', 'placed')
                    ->sum('quantity');

                $orderQty = (int) $orderContents->sum('quantity');

                if ($placementQty !== $orderQty) {
                    throw ValidationException::withMessages([
                        'dispatch' =>
                        'Stored goods do not match their placement records.',
                    ]);
                }

                $released[] = [
                    'pallet_id' => $pallet->id,
                    'line' => $pallet->line,
                    'slot' => $pallet->slot_section,
                    'quantity' => $orderQty,
                    'quantities' => $orderContents->groupBy('unit')->map(fn($rows) => (int) $rows->sum('quantity'))->all(),
                ];
            }

            // Remove goods belonging to this order only.
            DB::table('pallet_contents')
                ->where('booking_id', $locked->id)
                ->delete();

            DB::table('placement_details')
                ->where('booking_id', $locked->id)
                ->where('status', 'placed')
                ->update([
                    'status' => 'dispatched',
                    'updated_at' => now(),
                ]);

            // Keep other orders stored in shared slots.
            foreach ($pallets as $pallet) {
                $remaining = (int) DB::table('pallet_contents')
                    ->where('pallet_id', $pallet->id)
                    ->orderBy('id')->lockForUpdate()->get()->sum('quantity');

                $pallet->update([
                    'status' => $remaining > 0 ? 'filled' : 'empty',
                    'filled_boxes' => $remaining,
                    'current_booking_id' => null,
                ]);
            }

            $now = now();
            $adminId = auth('admin')->id();

            $dispatchData = [
                'status' => 'dispatched',
                'dispatched_by' => $adminId,
                'dispatched_at' => $now,
                'reference' => $data['reference'],
                'dispatch_notes' => $data['dispatch_notes'] ?? null,
                'dispatched_quantity' => $actualQty,
                'quantity_by_unit' => json_encode($contents->groupBy('unit')->map(fn($rows) => (int) $rows->sum('quantity'))->all()),
                'released_locations' => json_encode($released),
                'verification_warnings' => json_encode($verificationWarnings),
                'updated_at' => $now,
            ];

            if ($existingDispatch) {
                DB::table('order_dispatches')
                    ->where('id', $existingDispatch->id)
                    ->update($dispatchData);
            } else {
                DB::table('order_dispatches')->insert(
                    array_merge($dispatchData, [
                        'booking_id' => $locked->id,
                        'created_at' => $now,
                    ])
                );
            }

            $this->audit($locked->id, 'dispatch.completed', [
                'quantity' => $actualQty,
                'reference' => $data['reference'],
                'locations' => $released,
                'verification_warnings' => $verificationWarnings,
            ]);
        }, 3);

        return redirect()
            ->route('admin.dispatch.show', $booking)
            ->with('success', 'Goods dispatched successfully.');
    }




    private function assertReady(Booking $booking): void
    {
        if (SuperadminAccess::overriding()) return;
        // Load required relationships.
        $booking->loadMissing('products');

        /*
    |--------------------------------------------------------------------------
    | 1. Production Status
    |--------------------------------------------------------------------------
    */

        if (
            $booking->booking_type !== 'regular' ||
            $booking->status !== 'completed'
        ) {
            throw ValidationException::withMessages([
                'dispatch' => 'Complete production before dispatch.',
            ]);
        }

        /*
    |--------------------------------------------------------------------------
    | 2. Batch Completion
    |--------------------------------------------------------------------------
    */

        $batches = $booking->batches()
            ->with('qa')
            ->get();

        if ($batches->isEmpty()) {
            throw ValidationException::withMessages([
                'dispatch' => 'No production batches found.',
            ]);
        }

        if ($batches->contains(
            fn($batch) => $batch->status !== 'done'
        )) {
            throw ValidationException::withMessages([
                'dispatch' => 'All production batches must be completed.',
            ]);
        }

        $orderedByUnit = $booking->products->groupBy('unit')->map(fn($rows) => (int) $rows->sum('quantity'))->sortKeys()->all();
        $batchedByUnit = $batches->groupBy('unit')->map(fn($rows) => (int) $rows->sum('quantity'))->sortKeys()->all();
        if ($orderedByUnit !== $batchedByUnit) {
            throw ValidationException::withMessages(['dispatch' => 'Production batch quantities do not match the order by unit. Reconcile the production records before dispatch.']);
        }

        /*
    |--------------------------------------------------------------------------
    | 3. Batch Quality Checks
    |--------------------------------------------------------------------------
    |
    | No separate Quality Review approval is required.
    | Validate actual recorded batch checks instead.
    |
    */

        foreach ($batches as $batch) {
            $qa = $batch->qa;

            if (
                !$qa ||
                $qa->actual_dose === null ||
                $qa->visual_check !== 'pass' ||
                $qa->indicator_check !== 'changed' ||
                (bool) $qa->is_damaged
            ) {
                throw ValidationException::withMessages([
                    'dispatch' =>
                    "Batch #{$batch->batch_number} has incomplete " .
                        "or failed quality checks.",
                ]);
            }

        }

        app(\App\Services\DosimetryRelease::class)->validate($booking);

    }



    private function audit(int $bookingId, string $action, array $details): void
    {
        DB::table('operational_audit_events')->insert([
            'admin_id' => auth('admin')->id(),
            'action' => $action,
            'subject_type' => 'booking',
            'subject_id' => $bookingId,
            'details' => json_encode($details),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
