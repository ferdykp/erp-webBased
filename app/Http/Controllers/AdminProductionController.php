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
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'batch_quantities' => 'required|array|min:1',
            'batch_porters' => 'required|array|min:1',
            'production_line_id' => 'required|exists:production_lines,id',
            'target_dose' => 'required|numeric|min:0',
            'beam_speed' => 'required|numeric|min:0',
            'loading_mode' => 'required|string|max:255',
            'freq' => 'nullable|numeric|min:0',
            'scan_gear' => 'nullable|numeric|min:0',
        ]);

        $booking = Booking::with(['products', 'batches'])->findOrFail($request->booking_id);

        $totalProductQty = $booking->products->sum('quantity');
        // Filter out 'pending' batches for calculation because they are about to be replaced
        $existingBatchQty = $booking->batches->where('status', '!=', 'pending')->sum('quantity');
        $remainingCapacity = $totalProductQty - $existingBatchQty;

        $totalRequestedQty = array_sum($request->batch_quantities);

        if ($totalRequestedQty > $remainingCapacity) {
            return back()->with(
                'error',
                "Gagal! Total Quantity batch ($totalRequestedQty) melebihi sisa kapasitas ($remainingCapacity)."
            );
        }

        DB::transaction(function () use ($request, $booking) {
            // Delete existing pending batches as they are being finalized/replaced
            $booking->batches()->where('status', 'pending')->delete();

            $nextBatchNumber = ($booking->batches->max('batch_number') ?? 0) + 1;
            $unit = $booking->products->first()->unit ?? 'box';

            foreach ($request->batch_quantities as $index => $qty) {
                BookingBatch::create([
                    'booking_id' => $booking->id,
                    'batch_number' => $nextBatchNumber++,
                    'quantity' => $qty,
                    'unit' => $unit,
                    'status' => 'waiting', // Set to waiting so it goes to Queue Task
                    'porter_name' => $request->batch_porters[$index] ?? null,
                    'production_line_id' => $request->production_line_id,
                    'target_dose' => $request->target_dose,
                    'beam_speed' => $request->beam_speed,
                    'loading_mode' => $request->loading_mode,
                    'freq' => $request->freq,
                    'scan_gear' => $request->scan_gear,
                ]);
            }
        });

        return redirect()->route('admin.production.batch-queue')->with(
            'success',
            "Proses untuk Booking #{$booking->booking_code} berhasil disetup menjadi batch dan masuk antrean Queue Task."
        );
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
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'quantity' => 'required|numeric|min:0.01',
        ]);

        $booking = Booking::with(['products', 'batches'])->findOrFail($request->booking_id);

        // Hitung total quantity yang diizinkan dari booking_products
        $totalProductQty = $booking->products->sum('quantity');

        // Hitung total quantity batch yang sudah ada
        $existingBatchQty = $booking->batches->sum('quantity');

        // Sisa kapasitas
        $remainingCapacity = $totalProductQty - $existingBatchQty;

        // Validasi ketat: jumlah baru tidak boleh melebihi sisa
        if ($request->quantity > $remainingCapacity) {
            return back()->with(
                'error',
                "Gagal! Quantity batch ({$request->quantity}) melebihi sisa kapasitas ({$remainingCapacity}). " .
                    "Total qty produk: {$totalProductQty}, sudah terbagi: {$existingBatchQty}."
            );
        }

        // Tentukan nomor batch berikutnya
        $nextBatchNumber = ($booking->batches->max('batch_number') ?? 0) + 1;

        // Ambil unit dari produk pertama
        $unit = $booking->products->first()->unit ?? 'box';

        BookingBatch::create([
            'booking_id' => $booking->id,
            'batch_number' => $nextBatchNumber,
            'quantity' => $request->quantity,
            'unit' => $unit,
            'status' => 'waiting',
        ]);

        return back()->with(
            'success',
            "Batch #{$nextBatchNumber} berhasil dibuat ({$request->quantity} {$unit})."
        );
    }


    public function startIrradiation($batchId)
    {
        $batch = BookingBatch::findOrFail($batchId);

        if ($batch->status !== 'waiting') {
            return back()->with('error', "Batch #{$batch->batch_number} tidak berstatus waiting.");
        }

        DB::transaction(function () use ($batch) {
            $batch->update([
                'status' => 'processing',
                'offline_at' => now(), // <--- Tambahkan ini
            ]);

            // Jika booking masih approved, ubah ke processing
            $booking = $batch->booking;
            if ($booking->status === 'approved') {
                $booking->update(['status' => 'processing']);
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
        // 1. Validasi Input Form QA dari Step 2 Modal
        $request->validate([
            'actual_dose'        => 'required|numeric|min:0',
            'visual_check'       => 'required|in:pass,fail',
            'indicator_check'    => 'required|in:changed,no_change',
            'is_damaged'         => 'required|in:yes,no',
            // Field di bawah ini wajib diisi HANYA JIKA is_damaged == yes
            'damaged_qty'        => 'required_if:is_damaged,yes|nullable|numeric|min:1',
            'damage_description' => 'required_if:is_damaged,yes|nullable|string|max:500',
            'qa_notes'           => 'nullable|string',
        ]);

        $batch = BookingBatch::findOrFail($batchId);

        if ($batch->status !== 'processing') {
            return back()->with('error', "Batch #{$batch->batch_number} harus berstatus In Irradiation terlebih dahulu.");
        }

        try {
            DB::transaction(function () use ($request, $batch) {
                // 2. Simpan Data ke tabel batch_qas
                BatchQa::create([
                    'batch_id'           => $batch->id,
                    'actual_dose'        => $request->actual_dose,
                    'visual_check'       => $request->visual_check,
                    'indicator_check'    => $request->indicator_check,
                    'is_damaged'         => $request->is_damaged === 'yes', // Simpan sebagai boolean
                    'damaged_qty'        => $request->is_damaged === 'yes' ? $request->damaged_qty : 0,
                    'damage_description' => $request->is_damaged === 'yes' ? $request->damage_description : null,
                    'qa_notes'           => $request->qa_notes,
                    'inspected_at'       => now(),
                ]);

                // 3. Update status batch menjadi 'done'
                $batch->update([
                    'status' => 'done',
                    'finished_at' => now()
                ]);


                // 4. Cek apakah semua batch dalam booking ini sudah selesai
                $booking = $batch->booking;
                $remainingBatches = $booking->batches()
                    ->where('status', '!=', 'done')
                    ->count();

                // Jika tidak ada lagi batch yang tersisa (semua done), booking selesai
                if ($remainingBatches === 0) {
                    $booking->update(['status' => 'completed']);
                }
            });

            $batch->refresh();
            $booking = $batch->booking;

            $message = "Batch #{$batch->batch_number} berhasil diselesaikan dengan catatan QA.";
            if ($booking->status === 'completed') {
                $message .= " Semua batch selesai — Booking #{$booking->booking_code} otomatis Completed.";
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat menyimpan data QA: ' . $e->getMessage());
        }
    }

    public function printCertificate($id)
    {
        $batch = \App\Models\BookingBatch::with(['booking.customer', 'booking.products', 'qa', 'productionLine'])->findOrFail($id);

        return view('admin.production.certificate', compact('batch'));
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update([
            'payment_status' => $request->payment_status
        ]);

        return back()->with('success', 'Status pembayaran berhasil diperbarui.');
    }

    // 🟢 TAMBAHKAN METHOD INI DI DALAM CONTROLLER
    public function updateDuration(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'batch_id'       => 'required|exists:booking_batches,id',
            'offline_at'     => 'nullable|date',
            'finished_at'    => 'nullable|date',
            'total_duration' => 'required|integer|min:0',
        ]);

        try {
            // 2. Cari batch berdasarkan ID
            $batch = BookingBatch::findOrFail($request->batch_id);

            // 3. Update data waktu dan total durasi
            $batch->update([
                // Mengubah format dari datetime-local picker (T) kembali ke format database standar Y-m-d H:i:s
                'offline_at'     => $request->offline_at ? date('Y-m-d H:i:s', strtotime($request->offline_at)) : null,
                'finished_at'    => $request->finished_at ? date('Y-m-d H:i:s', strtotime($request->finished_at)) : null,
                'total_duration' => $request->total_duration,
            ]);

            return back()->with('success', "Durasi untuk Batch #{$batch->batch_number} berhasil diperbarui.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui durasi: ' . $e->getMessage());
        }
    }
}
