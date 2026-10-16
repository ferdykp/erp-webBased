<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingBatch;
// use App\Models\BookingProduct;
use App\Models\ProductionLine;
use App\Models\BatchQa; // Pastikan Model QA di-import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminProductionController extends Controller
{
    public function index()
    {
        // 1. Hitung jumlah batch secara real-time berdasarkan status di model BookingBatch
        $stats = [
            'waiting'    => \App\Models\BookingBatch::where('status', 'waiting')->count(),
            'processing' => \App\Models\BookingBatch::where('status', 'processing')->count(),
            'done'       => \App\Models\BookingBatch::where('status', 'done')->count(),
        ];

        // 2. Ambil data booking yang aktif beserta relasinya (eager loading untuk mencegah N+1 query)
        $bookings = Booking::with(['customer.contacts', 'products', 'batches.productionLine'])
            ->whereIn('status', ['pending', 'approved', 'processing'])->whereNotNull('arrival_time')
            ->latest()
            ->get();

        // 3. Ambil data mesin untuk kebutuhan dropdown parameter di dalam loop batch
        $productionLines = ProductionLine::orderBy('name')->get();

        // 4. Kirim semua data ke view dashboard
        return view('admin.production.index', compact('stats', 'bookings', 'productionLines'));
    }
    public function parameterSetting()
    {
        $bookings = Booking::with(['customer', 'products', 'batches.productionLine'])
            ->whereIn('status', ['pending', 'approved', 'processing'])->whereNotNull('arrival_time')
            ->latest()
            ->get()
            ->filter(function ($booking) {
                $totalProductQty = $booking->products->sum('quantity');
                $finalizedBatchQty = $booking->batches->where('status', '!=', 'pending')->sum('quantity');
                $hasPendingBatches = $booking->batches->where('status', 'pending')->count() > 0;

                return $hasPendingBatches || ($totalProductQty > $finalizedBatchQty);
            });

        $productionLines = ProductionLine::orderBy('name')->get();
        $porters = \App\Models\Porter::where('is_active', true)->get();

        return view('admin.production.parameter', compact('bookings', 'productionLines', 'porters'));
    }

    public function storeParameter(Request $request, $batchId)
    {
        $data = $request->validate([
            'production_line_id' => 'required|exists:production_lines,id',
            'target_dose' => 'required|numeric|gt:0', 'beam_speed' => 'required|numeric|gt:0',
            'loading_mode' => 'required|in:single_side,double_side',
            'freq' => 'nullable|numeric|min:0', 'scan_gear' => 'nullable|numeric|min:0',
        ]);
        $batch = BookingBatch::findOrFail($batchId);
        DB::transaction(function () use ($batch, $data) {
            $booking = Booking::whereKey($batch->booking_id)->lockForUpdate()->firstOrFail();
            $locked = BookingBatch::whereKey($batch->id)->lockForUpdate()->firstOrFail();
            if (!in_array($locked->status, ['pending', 'waiting'], true)
                || DB::table('irradiation_sessions')->where('booking_batch_id', $locked->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['parameters' => 'Machine settings are locked after production starts.']);
            }
            $this->validateDose($booking, (float) $data['target_dose'], $locked->booking_product_id);
            $locked->update($data + ['status' => 'waiting']);
            \App\Services\WarehouseInventory::audit('batch.parameters_updated', 'booking_batch', $locked->id, $data);
        }, 3);
        return back()->with('success', 'Machine settings saved.');
    }

    private function validateDose(Booking $booking, float $dose, ?int $productId = null): void
    {
        if ($booking->products->isEmpty() || ($productId !== null && !$booking->products->contains('id', $productId))
            || ($productId === null && $booking->products->count() > 1)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['target_dose' => 'Assign the batch to a valid product before verifying its machine settings.']);
        }
        foreach ($booking->products->when($productId, fn ($rows) => $rows->where('id', $productId)) as $product) {
            if ($dose < $product->dmin || ($product->dmax !== null && $dose > $product->dmax)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['target_dose' => 'Target dose must meet every allocated product specification.']);
            }
        }
    }

    public function processBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'booking_product_id' => 'nullable|integer',
            'batch_quantities' => 'required|array|min:1',
            'batch_quantities.*' => 'required|integer|min:1',

            // Machine parameters
            'production_line_id' => 'required|exists:production_lines,id',
            'target_dose' => 'required|numeric|gt:0',
            'beam_speed' => 'required|numeric|gt:0',

            'loading_mode' => 'required|in:single_side,double_side',

            'freq' => 'nullable|numeric|min:0',
            'scan_gear' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($validated) {

                // Lock booking to prevent concurrent batch allocation.
                $booking = Booking::query()
                    ->whereKey($validated['booking_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check-in is required. Manager approval is not required.
                if (
                    ! $booking->arrival_time ||
                    ! in_array(
                        $booking->status,
                        ['pending', 'approved', 'processing'],
                        true
                    )
                ) {
                    throw new \RuntimeException(
                        'Complete goods receiving before creating batches.'
                    );
                }

                $products = $booking->products()->get();
                $product = isset($validated['booking_product_id']) ? $products->firstWhere('id', $validated['booking_product_id'])
                    : ($products->count() === 1 ? $products->first() : null);
                if (!$product) throw \Illuminate\Validation\ValidationException::withMessages(['booking_product_id' => 'Select the product to allocate into batches.']);
                $this->validateDose($booking, (float) $validated['target_dose'], $product->id);

                $totalProductQty = (int) $product->quantity;

                // Lock existing batches before checking allocation.
                $existingBatches = $booking->batches()
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $allocatedQty = (int) $existingBatches
                    ->filter(fn ($batch) => (int) $batch->booking_product_id === $product->id || (!$batch->booking_product_id && $products->count() === 1))
                    ->sum('quantity');

                $remainingQty = max(
                    0,
                    $totalProductQty - $allocatedQty
                );

                $requestedQty = array_sum(
                    array_map(
                        'intval',
                        $validated['batch_quantities']
                    )
                );

                if (
                    $requestedQty <= 0 ||
                    $requestedQty > $remainingQty
                ) {
                    throw new \RuntimeException(
                        "Batch quantity ({$requestedQty}) exceeds " .
                            "the remaining quantity ({$remainingQty})."
                    );
                }

                $nextBatchNumber =
                    ((int) $booking->batches()->max('batch_number')) + 1;

                $unit = $product->unit;

                foreach ($validated['batch_quantities'] as $qty) {

                    BookingBatch::create([
                        'booking_id' => $booking->id,
                        'booking_product_id' => $product->id,
                        'batch_number' => $nextBatchNumber++,
                        'quantity' => (int) $qty,
                        'unit' => $unit,

                        'status' => 'waiting',

                        // Porter is taken from Receiving.
                        // No Porter input is required in Batch Planning.
                        'porter_name' => $booking->porter_name,

                        'production_line_id' =>
                        $validated['production_line_id'],

                        'target_dose' =>
                        $validated['target_dose'],

                        'beam_speed' =>
                        $validated['beam_speed'],

                        'loading_mode' =>
                        $validated['loading_mode'],

                        'freq' =>
                        $validated['freq'] ?? null,

                        'scan_gear' =>
                        $validated['scan_gear'] ?? null,
                    ]);
                }
            }, 3);

            $firstBatch = BookingBatch::where('booking_id', $validated['booking_id'])->where('status', 'waiting')->orderBy('id')->first();
            return redirect()
                ->route('admin.workspace.production-board', ['batch' => $firstBatch?->id, 'search' => Booking::findOrFail($validated['booking_id'])->booking_code])
                ->with(
                    'success',
                    'Batches created successfully.'
                );
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {

            \Illuminate\Support\Facades\Log::error(
                'Batch creation failed',
                [
                    'booking_id' => $validated['booking_id'],
                    'exception' => $e,
                ]
            );

            return back()
                ->with('error', get_class($e) === \RuntimeException::class
                    ? $e->getMessage()
                    : 'Could not create batches. Please try again.')
                ->withInput();
        }
    }


    public function batchQueue()
    {
        $bookings = Booking::with(['customer', 'products', 'batches.productionLine'])
            ->whereIn('status', ['pending', 'approved', 'processing'])->whereNotNull('arrival_time')
            ->whereHas('batches', function ($query) {
                $query->where('status', 'waiting');
            })
            ->latest()
            ->get();

        return view('admin.production.batch-queue', compact('bookings'));
    }


    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'booking_product_id' => 'nullable|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $booking = Booking::with(['products', 'batches'])->lockForUpdate()->findOrFail($validated['booking_id']);
                if (!$booking->arrival_time || !in_array($booking->status, ['pending', 'approved', 'processing'], true)) {
                    throw new \RuntimeException('Receive goods before creating batches.');
                }

                $product = isset($validated['booking_product_id']) ? $booking->products->firstWhere('id', $validated['booking_product_id']) : ($booking->products->count() === 1 ? $booking->products->first() : null);
                if (!$product) throw \Illuminate\Validation\ValidationException::withMessages(['booking_product_id' => 'Select an ordered product.']);
                $totalProductQty = (int) $product->quantity;
                $existingBatchQty = (int) $booking->batches->filter(fn($batch) => (int)$batch->booking_product_id === $product->id || (!$batch->booking_product_id && $booking->products->count() === 1))->sum('quantity');
                $remainingCapacity = max(0, $totalProductQty - $existingBatchQty);
                $quantity = (int) $validated['quantity'];

                if ($quantity > $remainingCapacity) {
                    throw new \RuntimeException("Quantity batch ({$quantity}) melebihi sisa kapasitas ({$remainingCapacity}).");
                }

                BookingBatch::create([
                    'booking_id' => $booking->id,
                    'booking_product_id' => $product->id,
                    'porter_name' => $booking->porter_name,
                    'batch_number' => ((int) $booking->batches()->max('batch_number')) + 1,
                    'quantity' => $quantity,
                    'unit' => $product->unit,
                    'status' => 'pending',
                ]);
            });

            return back()->with('success', 'Batch created.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function startIrradiation($batchId)
    {
        abort_unless(in_array(auth('admin')->user()?->role, ['superadmin', 'production'], true), 403);

        try {
            DB::transaction(function () use ($batchId) {
                $candidate = BookingBatch::findOrFail($batchId);
                $booking = Booking::whereKey($candidate->booking_id)->lockForUpdate()->firstOrFail();
                $batch = BookingBatch::whereKey($batchId)->lockForUpdate()->firstOrFail();

                if ($batch->status !== 'waiting') {
                    throw new \RuntimeException('Only ready batches can be started.');
                }
                if (!$booking->arrival_time || !in_array($booking->status, ['pending', 'approved', 'processing'], true)) {
                    throw new \RuntimeException('Complete goods receiving before starting production.');
                }
                $this->validateDose($booking, (float) $batch->target_dose, $batch->booking_product_id);
                if (!$batch->production_line_id || !$batch->target_dose || !$batch->beam_speed || !in_array($batch->loading_mode, ['single_side', 'double_side'], true)) {
                    throw new \RuntimeException('Complete machine settings before starting production.');
                }

                // This action changes workflow state only. Do not infer actual irradiation time.
                $batch->update(['status' => 'processing']);
                if ($booking->status !== 'processing') {
                    $booking->update(['status' => 'processing']);
                }
            }, 3);

            return redirect()->route('admin.workspace.production-board', ['batch' => $batchId])->with('success', 'Production started. Record actual session times after each run.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function offline()
    {
        $bookings = Booking::with(['customer', 'products', 'batches.productionLine'])
            ->whereIn('status', ['processing'])
            ->latest()
            ->get();

        return view('admin.production.offline', compact('bookings'));
    }


    public function finishPage()
    {
        $bookings = Booking::with(['customer', 'products', 'batches.productionLine'])
            ->whereIn('status', ['processing', 'completed'])
            ->latest()
            ->get();

        return view('admin.production.finish', compact('bookings'));
    }



    public function finishBatch(Request $request, $batchId)
    {
        $batch = BookingBatch::findOrFail($batchId);
        $validated = $request->validate([
            'actual_dose' => 'required|numeric|min:0',
            'visual_check' => 'required|in:pass,fail',
            'indicator_check' => 'required|in:changed,no_change',
            'is_damaged' => 'required|in:yes,no',
            'damaged_qty' => 'required_if:is_damaged,yes|nullable|integer|min:1|max:' . $batch->quantity,
            'damage_description' => 'nullable|string|max:500',
            'qa_notes' => 'nullable|string|max:2000',
        ]);

        try {
            DB::transaction(function () use ($validated, $batchId) {
                $candidate = BookingBatch::findOrFail($batchId);
                $booking = Booking::whereKey($candidate->booking_id)->lockForUpdate()->firstOrFail();
                $batch = BookingBatch::whereKey($batchId)->lockForUpdate()->firstOrFail();
                if ($batch->status !== 'processing') {
                    throw new \RuntimeException('The batch must be in progress.');
                }
                $booking = Booking::whereKey($batch->booking_id)->lockForUpdate()->firstOrFail();
                \App\Services\WarehouseInventory::audit('batch.checks_recorded', 'booking_batch', $batch->id, $validated);
                BatchQa::updateOrCreate(
                    ['batch_id' => $batch->id],
                    [
                        'actual_dose' => $validated['actual_dose'],
                        'visual_check' => $validated['visual_check'],
                        'indicator_check' => $validated['indicator_check'],
                        'is_damaged' => $validated['is_damaged'] === 'yes',
                        'damaged_qty' => $validated['is_damaged'] === 'yes' ? $validated['damaged_qty'] : 0,
                        'damage_description' => $validated['is_damaged'] === 'yes' ? ($validated['damage_description'] ?? null) : null,
                        'qa_notes' => $validated['qa_notes'] ?? null,
                        'inspected_at' => now(),
                    ]
                );
                // Data QA gagal tetap disimpan, tetapi batch tidak dapat dirilis.
                if ($validated['visual_check'] !== 'pass' || $validated['indicator_check'] !== 'changed' || $validated['is_damaged'] === 'yes') {
                    return;
                }
                $lastSessionEnd = DB::table('irradiation_sessions')->where('booking_batch_id', $batch->id)->whereNull('voided_at')->max('ended_at');
                if (!$lastSessionEnd) {
                    throw new \RuntimeException('Record at least one completed irradiation run before finishing the batch.');
                }
                \App\Services\WarehouseInventory::audit('batch.finished', 'booking_batch', $batch->id, $validated);
                $batch->update(['status' => 'done', 'finished_at' => $lastSessionEnd]);
                $totalOrdered = (int) $booking->products()->sum('quantity');
                $totalBatched = (int) $booking->batches()->sum('quantity');
                $hasUnfinished = $booking->batches()->where('status', '!=', 'done')->exists();
                $productsComplete = $booking->products->every(function ($product) use ($booking) {
                    $batches = $booking->batches()->where('booking_product_id', $product->id);
                    if ($booking->products->count() === 1) $batches->orWhere(fn($q) => $q->where('booking_id', $booking->id)->whereNull('booking_product_id'));
                    return (int) $batches->sum('quantity') === (int) $product->quantity;
                });
                if ($productsComplete && !$hasUnfinished) {
                    $booking->update(['status' => 'completed']);
                }
            });
            $qaPassed = BookingBatch::findOrFail($batchId)->status === 'done' && $validated['visual_check'] === 'pass'
                && $validated['indicator_check'] === 'changed'
                && $validated['is_damaged'] === 'no';
            $completedBatch = BookingBatch::findOrFail($batchId);
            $product = $completedBatch->booking->products->firstWhere('id', $completedBatch->booking_product_id)
                ?? ($completedBatch->booking->products->count() === 1 ? $completedBatch->booking->products->first() : null);
            $doseDifference = $product && ($product->dmin === null || $validated['actual_dose'] < $product->dmin
                || ($product->dmax !== null && $validated['actual_dose'] > $product->dmax));
            return back()->with($qaPassed ? 'success' : 'error', $qaPassed
                ? 'Checks saved. The batch is complete.'.($doseDifference ? ' Warning: measured dose differs from the product limits. This does not require approval or block dispatch.' : '')
                : 'Checks saved, but an inspection failed. The batch remains in progress for correction.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    // 🟢 TAMBAHKAN METHOD INI DI DALAM CONTROLLER
    public function updateDuration(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:booking_batches,id',
            'offline_at' => 'nullable|date',
            'finished_at' => 'nullable|date|after_or_equal:offline_at',
            'total_duration' => 'required|integer|min:0',
        ]);

        return back()->withErrors([
            'duration' => 'Direct edits to production timestamps are disabled. Record corrections using the authorized production review workflow.',
        ]);
    }
    public function printCertificate($id)
    {
        $batch = \App\Models\BookingBatch::with(['booking.customer', 'booking.products', 'qa', 'productionLine'])->findOrFail($id);

        return view('admin.production.certificate', compact('batch'));
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:paid,unpaid',
        ]);

        $booking = Booking::findOrFail($id);
        if ($booking->booking_type === 'test') {
            return back()->with('error', 'Product Testing does not use commercial payment status.');
        }

        $booking->update(['payment_status' => $validated['payment_status']]);
        return back()->with('success', 'Payment status updated.');
    }
}
