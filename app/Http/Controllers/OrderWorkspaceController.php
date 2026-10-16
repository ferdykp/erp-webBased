<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\BookingBatch;

class OrderWorkspaceController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Booking::query()->where('booking_type', 'regular')->with('customer')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim((string) $request->input('search'));
                $q->where(function ($inner) use ($term) {
                    $inner->where('booking_code', 'like', "%{$term}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('company_name', 'like', "%{$term}%"));
                });
            })->latest()->paginate(20)->withQueryString();

        return view('admin.workspace.index', compact('orders'));
    }

    public function show(Request $request, Booking $booking): View
    {
        $booking->load(['customer', 'products', 'batches.productionLine', 'batches.qa', 'placementDetails']);
        $dispatch = \Illuminate\Support\Facades\DB::table('order_dispatches')->where('booking_id', $booking->id)->first();
        $hasDosimetry = \Illuminate\Support\Facades\DB::table('dosimeter_records')->where('booking_id', $booking->id)->exists();
        $isReceived = $booking->arrival_time !== null || $booking->placementDetails->isNotEmpty();
        $batchesFinished = $booking->batches->isNotEmpty() && $booking->batches->every(fn ($batch) => $batch->status === 'done');
        $allocated = (int) $booking->batches->sum('quantity');
        $remaining = (int) $booking->products->sum('quantity') - $allocated;
        $record = \App\Models\DosimeterRecord::with('details')->where('booking_id', $booking->id)->first();
        $hasDosimetry = $record && $record->tablet_quantity > 0 && $record->details->count() === (int) $record->tablet_quantity && !$record->details->contains(fn($d) => blank($d->dosimeter_number) || $d->absorbance === null || $d->dose_kgy === null);
        $nextStage = !$isReceived ? 'receiving' : ($remaining > 0 ? 'preparation' : (!$batchesFinished ? 'irradiation' : (!$hasDosimetry ? 'quality' : ($dispatch?->status === 'dispatched' ? 'summary' : 'handover'))));
        $stage = $request->input('stage', 'summary');
        if (!in_array($stage, ['summary','receiving','preparation','irradiation','quality','documents', 'handover'], true)) {
            $stage = 'summary';
        }
        return view('admin.workspace.show', compact('booking', 'stage', 'dispatch', 'hasDosimetry', 'isReceived', 'batchesFinished', 'nextStage'));
    }

    public function productionBoard(Request $request): View
    {
        $batches = BookingBatch::query()
            ->with(['booking.customer', 'productionLine'])
            ->whereIn('status', ['pending', 'waiting', 'processing', 'done'])
            ->when($request->filled('machine'), fn ($q) => $q->where('production_line_id', $request->integer('machine')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim((string) $request->input('search'));
                $q->whereHas('booking', fn ($b) => $b->where('booking_code', 'like', "%{$term}%"));
            })
            ->orderByRaw("CASE status WHEN 'processing' THEN 0 WHEN 'waiting' THEN 1 ELSE 2 END")
            ->latest('id')->paginate(36)->withQueryString();
        // Receiving is separate from batch creation. Show received orders without batches
        // so staff can see the next action even before manager approval.
        $receivedOrders = Booking::query()
            ->with(['customer', 'products'])
            ->where('booking_type', 'regular')
            ->where(function ($q) {
                $q->whereNotNull('arrival_time')->orWhereHas('placementDetails');
            })
            ->whereIn('status', ['pending', 'approved', 'processing'])
            ->whereDoesntHave('batches')
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = trim((string) $request->input('search'));
                $q->where('booking_code', 'like', "%{$term}%");
            })
            ->latest()
            ->limit(30)
            ->get();
        $machines = \App\Models\ProductionLine::orderBy('name')->get();
        $counts = BookingBatch::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total','status');
        return view('admin.workspace.production-board', compact('batches', 'machines', 'counts', 'receivedOrders'));
    }

    public function tasks(): View
    {
        $role = auth('admin')->user()->role;
        $tasks = collect();
        if (in_array($role, ['cargo_admin', 'superadmin'], true)) {
            $tasks = $tasks->concat(Booking::with('customer')->where('status', 'pending')->whereNull('arrival_time')->latest()->limit(30)->get()
                ->map(fn ($b) => ['booking' => $b, 'stage' => 'receiving', 'title' => 'Receive goods and assign warehouse locations', 'label' => 'Receiving']));
        }
        if (in_array($role, ['production', 'superadmin', 'manager'], true)) {
            $tasks = $tasks->concat(Booking::with('customer')
                ->where('booking_type', 'regular')
                ->whereNotNull('arrival_time')
                ->whereIn('status', ['pending', 'approved', 'processing'])
                ->latest()->limit(50)->get()
                ->map(fn ($b) => [
                    'booking' => $b,
                    'stage' => $b->batches()->exists() ? 'irradiation' : 'preparation',
                    'title' => $b->batches()->exists() ? 'Continue irradiation work' : 'Create batches and set machine parameters',
                    'label' => 'Production',
                ]));
        }
        return view('admin.workspace.tasks', compact('tasks', 'role'));
    }
}
