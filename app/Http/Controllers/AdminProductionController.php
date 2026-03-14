<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingBatch;
// use App\Models\BookingProduct;
use App\Models\ProductionLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ==========================================================================
 * AdminProductionController
 * ==========================================================================
 *
 * Controller utama untuk Layer 3 – Production Management.
 * Terdiri dari 3 halaman (sesuai submenu):
 *
 *   Step 1 → Process Parameter Setting
 *   Step 2 → Batch Queue
 *   Step 3 → Offline / Finish
 *
 * Route prefix : /admin/production
 * Middleware   : auth:admin, role:technologist,production_engineer,admin
 *
 * ── Daftar Method ──────────────────────────────────────────────────────────
 *  STEP 1 (Process Parameter Setting):
 *    1. parameterSetting()          → Halaman form parameter setting
 *    2. storeParameter()            → Simpan parameter ke batch
 *
 *  STEP 2 (Batch Queue / Process Irradiation):
 *    3. batchQueue()                → Halaman batch queue (legacy)
 *    4. storeBatch()                → Buat batch baru (pecah quantity)
 *    5. startIrradiation()          → Ubah status batch → processing
 *    6. offline()                   → Halaman daftar batch In Irradiation
 *
 *  STEP 3 (Product Finish):
 *    7. finishPage()                → Halaman daftar batch selesai (done)
 *    8. finishBatch()               → Ubah status batch → done
 * ==========================================================================
 */
class AdminProductionController extends Controller
{
    // =====================================================================
    // STEP 1 – PROCESS PARAMETER SETTING
    // =====================================================================

    /**
     * Halaman Process Parameter Setting.
     *
     * Menampilkan daftar booking (status approved/processing) untuk dipilih,
     * lalu user mengisi parameter: Production Line, Target Dose, Beam Speed,
     * Loading Mode. Parameter disimpan per batch.
     *
     * Method : GET
     * Route  : /admin/production/parameter
     * Name   : admin.production.parameter
     */
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

                // Tampilkan jika:
                // 1. Masih ada batch pending yang harus di-update parameter-nya
                // 2. ATAU masih ada quantity produk yang belum dibuatkan batch
                return $hasPendingBatches || ($totalProductQty > $finalizedBatchQty);
            });

        $productionLines = ProductionLine::orderBy('name')->get();
        $porters = \App\Models\Porter::where('is_active', true)->get();

        return view('admin.production.parameter', compact('bookings', 'productionLines', 'porters'));
    }

    /**
     * Simpan parameter produksi ke batch tertentu.
     *
     * Input: production_line_id, target_dose, beam_speed, loading_mode
     *
     * Method : PUT
     * Route  : /admin/production/batches/{batch}/parameter
     * Name   : admin.production.batches.parameter.update
     */
    public function storeParameter(Request $request, $batchId)
    {
        $request->validate([
            'production_line_id' => 'nullable|exists:production_lines,id',
            'target_dose' => 'nullable|numeric|min:0',
            'beam_speed' => 'nullable|numeric|min:0',
            'loading_mode' => 'nullable|string|max:255',
        ]);

        $batch = BookingBatch::findOrFail($batchId);

        $batch->update([
            'production_line_id' => $request->production_line_id,
            'target_dose' => $request->target_dose,
            'beam_speed' => $request->beam_speed,
            'loading_mode' => $request->loading_mode,
        ]);

        return back()->with('success', "Parameter Batch #{$batch->batch_number} berhasil disimpan.");
    }

    /**
     * Update process untuk sebuah booking:
     * - Set parameter produksi (line, dose, beam speed, loading mode)
     * - Split quantity ke batch baru
     * - Langsung set status batch ke "processing" (In Irradiation)
     *
     * Flow ini merepresentasikan langkah "Process Set → In Irradiation"
     * dalam satu aksi dari halaman Process Parameter.
     *
     * Method : POST
     * Route  : /admin/production/process
     * Name   : admin.production.process
     */
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
                ]);
            }
        });

        return redirect()->route('admin.production.batch-queue')->with(
            'success',
            "Proses untuk Booking #{$booking->booking_code} berhasil disetup menjadi batch dan masuk antrean Queue Task."
        );
    }

    // =====================================================================
    // STEP 2 – BATCH QUEUE
    // =====================================================================

    /**
     * Halaman Batch Queue.
     *
     * Menampilkan booking yang sudah approved/processing.
     * User bisa memecah quantity dari booking_products menjadi batch-batch.
     * Setiap batch bisa di-set status "In Irradiation" (processing).
     *
     * Method : GET
     * Route  : /admin/production/batch-queue
     * Name   : admin.production.batch-queue
     */
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

    /**
     * Buat batch baru untuk sebuah booking (pecah quantity).
     *
     * Validasi: Total quantity seluruh batch TIDAK BOLEH melebihi
     * total quantity di booking_products.
     *
     * Contoh: booking_products.quantity = 100
     *   Batch A = 50, Batch B = 50 → DITERIMA (total = 100)
     *   Batch A = 60, Batch B = 50 → DITOLAK (total = 110 > 100)
     *
     * Method : POST
     * Route  : /admin/production/batches
     * Name   : admin.production.batches.store
     */
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

    /**
     * Ubah status batch menjadi "processing" (In Irradiation).
     *
     * Juga otomatis mengubah status booking menjadi 'processing'
     * jika booking masih berstatus 'approved'.
     *
     * Method : PUT
     * Route  : /admin/production/batches/{batch}/start
     * Name   : admin.production.batches.start
     */
    public function startIrradiation($batchId)
    {
        $batch = BookingBatch::findOrFail($batchId);

        if ($batch->status !== 'waiting') {
            return back()->with('error', "Batch #{$batch->batch_number} tidak berstatus waiting.");
        }

        DB::transaction(function () use ($batch) {
            $batch->update(['status' => 'processing']);

            // Jika booking masih approved, ubah ke processing
            $booking = $batch->booking;
            if ($booking->status === 'approved') {
                $booking->update(['status' => 'processing']);
            }
        });

        return back()->with('success', "Batch #{$batch->batch_number} → In Irradiation.");
    }

    // =====================================================================
    // STEP 3 – OFFLINE / FINISH
    // =====================================================================

    /**
     * Halaman Offline / Finish.
     *
     * Menampilkan batch-batch yang sedang "processing" (In Irradiation).
     * User bisa mengubah status menjadi "done" (Offline / Finish).
     *
     * Method : GET
     * Route  : /admin/production/offline
     * Name   : admin.production.offline
     */
    public function offline()
    {
        $bookings = Booking::with(['customer', 'products', 'batches.productionLine'])
            ->whereIn('status', ['processing'])
            ->latest()
            ->get();

        return view('admin.production.offline', compact('bookings'));
    }

    /**
     * Halaman Product Finish.
     *
     * Menampilkan batch-batch yang sudah "done" (Offline / Finish)
     * dalam bentuk tabel agar bisa direview detailnya.
     *
     * Method : GET
     * Route  : /admin/production/finish
     * Name   : admin.production.finish
     */
    public function finishPage()
    {
        $bookings = Booking::with(['customer', 'products', 'batches.productionLine'])
            ->whereIn('status', ['processing', 'completed'])
            ->latest()
            ->get();

        return view('admin.production.finish', compact('bookings'));
    }

    /**
     * Ubah status batch menjadi "done" (Offline / Finish).
     *
     * Trigger otomatis: Jika SEMUA batch dalam satu booking sudah 'done',
     * status booking otomatis berubah ke 'completed' (untuk Layer 4 QC).
     *
     * Method : PUT
     * Route  : /admin/production/batches/{batch}/finish
     * Name   : admin.production.batches.finish
     */
    public function finishBatch($batchId)
    {
        $batch = BookingBatch::findOrFail($batchId);

        if ($batch->status !== 'processing') {
            return back()->with('error', "Batch #{$batch->batch_number} harus berstatus In Irradiation terlebih dahulu.");
        }

        DB::transaction(function () use ($batch) {
            $batch->update(['status' => 'done']);

            // Cek apakah semua batch di booking ini sudah done
            $booking = $batch->booking;
            $pendingBatches = $booking->batches()
                ->where('status', '!=', 'done')
                ->count();

            // Jika semua done → booking otomatis completed
            if ($pendingBatches === 0) {
                $booking->update(['status' => 'completed']);
            }
        });

        $batch->refresh();
        $booking = $batch->booking;

        $message = "Batch #{$batch->batch_number} → Offline / Finish.";
        if ($booking->status === 'completed') {
            $message .= " Semua batch selesai — Booking #{$booking->booking_code} otomatis Completed.";
        }

        return back()->with('success', $message);
    }
}
