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
            ->whereIn('status', ['approved', 'processing'])
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
            ->whereIn('status', ['approved', 'processing'])
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
        $request->validate([
            'production_line_id' => 'nullable|exists:production_lines,id',
            'target_dose' => 'nullable|numeric|min:0',
            'beam_speed' => 'nullable|numeric|min:0',
            'loading_mode' => 'nullable|string|max:255',
            'freq' => 'nullable|numeric|min:0',
            'scan_gear' => 'nullable|numeric|min:0',
        ]);

        $batch = BookingBatch::findOrFail($batchId);

        $batch->update([
            'production_line_id' => $request->production_line_id,
            'target_dose' => $request->target_dose,
            'beam_speed' => $request->beam_speed,
            'loading_mode' => $request->loading_mode,
            'freq' => $request->freq,
            'scan_gear' => $request->scan_gear,
        ]);

        return back()->with('success', "Parameter Batch #{$batch->batch_number} berhasil disimpan.");
    }


    public function processBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'batch_quantities' => 'required|array|min:1',
            'batch_quantities.*' => 'required|numeric|min:0.01',
            'batch_porters' => 'required|array|min:1',
            'batch_porters.*' => 'required|string|max:255',
            'production_line_id' => 'required|exists:production_lines,id',
            'target_dose' => 'required|numeric|min:0.01',
            'beam_speed' => 'required|numeric|min:0.01',
            'loading_mode' => 'required|string|max:255',
            'freq' => 'nullable|numeric|min:0',
            'scan_gear' => 'nullable|numeric|min:0',
        ]);

        if (count($validated['batch_quantities']) !== count($validated['batch_porters'])) {
            return back()->with('error', 'Jumlah porter harus sama dengan jumlah batch.')->withInput();
        }

        try {
            DB::transaction(function () use ($validated) {
                $booking = Booking::with(['products', 'batches'])
                    ->lockForUpdate()
                    ->findOrFail($validated['booking_id']);

                if (!$booking->arrival_time || !in_array($booking->status, ['approved', 'processing'], true)) {
                    throw new \RuntimeException('Booking harus sudah warehouse check-in sebelum masuk Production.');
                }

                $totalProductQty = (float) $booking->products->sum('quantity');
                $existingBatchQty = (float) $booking->batches->where('status', '!=', 'pending')->sum('quantity');
                $remainingCapacity = max(0, $totalProductQty - $existingBatchQty);
                $totalRequestedQty = array_sum(array_map('floatval', $validated['batch_quantities']));

                if ($totalRequestedQty <= 0 || $totalRequestedQty > $remainingCapacity + 0.00001) {
                    throw new \RuntimeException("Total quantity batch ({$totalRequestedQty}) melebihi sisa kapasitas ({$remainingCapacity}).");
                }

                $booking->batches()->where('status', 'pending')->delete();
                $nextBatchNumber = ((int) $booking->batches()->max('batch_number')) + 1;
                $unit = $booking->products->first()?->unit ?? 'box';

                foreach ($validated['batch_quantities'] as $index => $qty) {
                    BookingBatch::create([
                        'booking_id' => $booking->id,
                        'batch_number' => $nextBatchNumber++,
                        'quantity' => $qty,
                        'unit' => $unit,
                        'status' => 'waiting',
                        'porter_name' => $validated['batch_porters'][$index],
                        'production_line_id' => $validated['production_line_id'],
                        'target_dose' => $validated['target_dose'],
                        'beam_speed' => $validated['beam_speed'],
                        'loading_mode' => $validated['loading_mode'],
                        'freq' => $validated['freq'] ?? null,
                        'scan_gear' => $validated['scan_gear'] ?? null,
                    ]);
                }
            });

            return redirect()->route('admin.production.batch-queue')
                ->with('success', 'Batch berhasil dibuat dan masuk Queue Task.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function batchQueue()
    {
        $bookings = Booking::with(['customer', 'products', 'batches.productionLine'])
            ->whereIn('status', ['approved', 'processing'])
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
            'quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $booking = Booking::with(['products', 'batches'])->lockForUpdate()->findOrFail($validated['booking_id']);
                if (!$booking->arrival_time || !in_array($booking->status, ['approved', 'processing'], true)) {
                    throw new \RuntimeException('Booking harus sudah warehouse check-in sebelum batch dibuat.');
                }

                $totalProductQty = (float) $booking->products->sum('quantity');
                $existingBatchQty = (float) $booking->batches->sum('quantity');
                $remainingCapacity = max(0, $totalProductQty - $existingBatchQty);
                $quantity = (float) $validated['quantity'];

                if ($quantity > $remainingCapacity + 0.00001) {
                    throw new \RuntimeException("Quantity batch ({$quantity}) melebihi sisa kapasitas ({$remainingCapacity}).");
                }

                BookingBatch::create([
                    'booking_id' => $booking->id,
                    'batch_number' => ((int) $booking->batches()->max('batch_number')) + 1,
                    'quantity' => $quantity,
                    'unit' => $booking->products->first()?->unit ?? 'box',
                    'status' => 'waiting',
                ]);
            });

            return back()->with('success', 'Batch berhasil dibuat.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }


    public function startIrradiation($batchId)
    {
        $batch = BookingBatch::with('booking')->findOrFail($batchId);

        if ($batch->status !== 'waiting') {
            return back()->with('error', "Batch #{$batch->batch_number} tidak berstatus waiting.");
        }

        if (!$batch->production_line_id || !$batch->target_dose || !$batch->beam_speed || !$batch->loading_mode) {
            return back()->with('error', 'Lengkapi machine, target dose, beam speed, dan loading mode sebelum irradiation dimulai.');
        }

        DB::transaction(function () use ($batch) {
            $batch->update(['status' => 'processing', 'offline_at' => now()]);
            if ($batch->booking->status === 'approved') {
                $batch->booking->update(['status' => 'processing']);
            }
        });

        return back()->with('success', "Batch #{$batch->batch_number} → In Irradiation.");
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
            'damaged_qty' => 'required_if:is_damaged,yes|nullable|numeric|min:1|max:' . $batch->quantity,
            'damage_description' => 'required_if:is_damaged,yes|nullable|string|max:500',
            'qa_notes' => 'nullable|string|max:2000',
        ]);

        if ($batch->status !== 'processing') {
            return back()->with('error', "Batch #{$batch->batch_number} harus berstatus In Irradiation terlebih dahulu.");
        }

        try {
            DB::transaction(function () use ($validated, $batch) {
                BatchQa::updateOrCreate(
                    ['batch_id' => $batch->id],
                    [
                        'actual_dose' => $validated['actual_dose'],
                        'visual_check' => $validated['visual_check'],
                        'indicator_check' => $validated['indicator_check'],
                        'is_damaged' => $validated['is_damaged'] === 'yes',
                        'damaged_qty' => $validated['is_damaged'] === 'yes' ? $validated['damaged_qty'] : 0,
                        'damage_description' => $validated['is_damaged'] === 'yes' ? $validated['damage_description'] : null,
                        'qa_notes' => $validated['qa_notes'] ?? null,
                        'inspected_at' => now(),
                    ]
                );

                $batch->update(['status' => 'done', 'finished_at' => now()]);
                $booking = $batch->booking;
                if (!$booking->batches()->where('status', '!=', 'done')->exists()) {
                    $booking->update(['status' => 'completed']);
                }
            });

            return back()->with('success', "Batch #{$batch->batch_number} berhasil diselesaikan dan QA tersimpan.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan QA: ' . $e->getMessage());
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

        $batch = BookingBatch::findOrFail($validated['batch_id']);
        $batch->update([
            'offline_at' => $validated['offline_at'] ?? null,
            'finished_at' => $validated['finished_at'] ?? null,
            'total_duration' => $validated['total_duration'],
        ]);

        return back()->with('success', "Durasi untuk Batch #{$batch->batch_number} berhasil diperbarui.");
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
            return back()->with('error', 'Product Testing tidak menggunakan status pembayaran commercial order.');
        }

        $booking->update(['payment_status' => $validated['payment_status']]);
        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }
}
