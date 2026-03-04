<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Pallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;


class AdminBookingController extends Controller
{
    // public function index()
    // {
    //     // 1. Ambil data booking (Status pending biasanya untuk scanner di dashboard)
    //     $bookings = Booking::with(['customer', 'products', 'batches'])
    //         ->latest()
    //         ->get(); // Gunakan get() jika Dashboard butuh semua data untuk JS scanner

    //     // 2. Ambil Porter (Pemicu utama error jika variabel ini hilang di view)
    //     $porters = \App\Models\Porter::where('is_active', true)->get();

    //     // 3. Ambil Palet Kosong (Untuk dropdown step 3)
    //     $pallets = Pallet::where('status', 'empty')
    //         ->orderBy('line')
    //         ->orderBy('slot_section')
    //         ->get();

    //     // Pastikan view-nya sesuai. Jika view dashboard ada di admin.dashboard.index:
    //     return view('admin.dashboard.index', compact('bookings', 'porters', 'pallets'));
    // }
    public function index()
    {
        // Ambil data booking untuk scanner dashboard
        $bookings = Booking::with(['customer', 'products', 'batches'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        // VARIABEL WAJIB: Jangan sampai absen
        $porters = \App\Models\Porter::where('is_active', true)->get();

        // Ambil data palet kosong untuk Step 3 di Modal Dashboard
        $pallets = Pallet::where('status', 'empty')
            ->orderBy('line')
            ->orderBy('slot_section')
            ->get();

        // Pastikan me-return ke view dashboard
        return view('admin.dashboard.index', compact('bookings', 'porters', 'pallets'));
    }

    public function allOrder()
    {
        // Fungsi khusus tabel daftar semua booking
        $bookings = Booking::with(['customer', 'products', 'batches', 'pallets'])
            ->latest()
            ->paginate(10); // Gunakan paginate agar tidak berat

        $pageTitle = "All Order History";

        // Kirim data tambahan agar modal detail di halaman index tidak error
        $porters = \App\Models\Porter::where('is_active', true)->get();
        $pallets = Pallet::where('status', 'empty')->get();

        // Diarahkan ke view table (index.blade.php di folder bookings)
        return view('admin.bookings.index', compact('bookings', 'pageTitle', 'porters', 'pallets'));
    }

    // public function updateStatus(Request $request, $id)
    // {
    //     $request->validate([
    //         'status' => 'required|string'
    //     ]);

    //     $booking = Booking::findOrFail($id);

    //     // Simpan perubahan status booking
    //     $booking->update([
    //         'status' => $request->status
    //     ]);

    //     // LOGIKA PENGOSONGAN PALET
    //     // Jika status diubah menjadi 'completed', cari palet yang terkait dan kosongkan
    //     if ($request->status === 'completed') {
    //         \App\Models\Pallet::where('current_booking_id', $booking->id)->update([
    //             'status' => 'empty',
    //             'current_booking_id' => null,
    //             'filled_boxes' => 0 // WAJIB DI-RESET KE 0
    //         ]);
    //     }

    //     return back()->with('success', 'Status updated' . ($request->status === 'completed' ? ' and pallet released' : ''));
    // }
    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $newStatus = $request->status;

        // Tambahkan validasi sederhana jika perlu
        $booking->update(['status' => $newStatus]);

        if ($newStatus === 'completed') {
            Pallet::where('current_booking_id', $booking->id)->update([
                'status' => 'empty',
                'current_booking_id' => null,
                'filled_boxes' => 0
            ]);
        }

        return back()->with('success', "Status berhasil diperbarui ke " . strtoupper($newStatus));
    }


    public function checkIn(Request $request)
    {
        $request->validate([
            'booking_code' => 'required',
            'pic_warehouse' => 'required|string',
            'batch_porters' => 'required|array|min:1',
            'batch_quantities' => 'required|array',
            'pallet_ids' => 'required|array',
        ]);

        $booking = Booking::with(['products', 'batches', 'pallets'])->where('booking_code', $request->booking_code)->first();

        if (!$booking) return back()->with('error', 'Booking tidak ditemukan');

        // VALIDASI STATUS: Hanya status 'pending' yang boleh melakukan check-in
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Gagal! Order ini sudah melewati tahap Check-in.');
        }

        try {
            DB::transaction(function () use ($booking, $request) {
                // PEMBERSIHAN DATA (Mencegah Double-double)
                // Jika admin mengulang proses sebelum status berubah, bersihkan data lama
                $booking->batches()->delete();

                // Reset status palet yang sebelumnya mungkin sudah terikat ke booking ini
                Pallet::where('current_booking_id', $booking->id)->update([
                    'status' => 'empty',
                    'current_booking_id' => null,
                    'filled_boxes' => 0
                ]);

                // UPDATE DATA UTAMA
                $booking->update([
                    'arrival_time' => now(),
                    'pic_warehouse' => $request->pic_warehouse,
                    'status' => 'approved', // Pindah status ke Approved
                ]);

                $unit = $booking->products->first()->unit ?? 'box';

                foreach ($request->batch_quantities as $index => $totalBatchQty) {
                    // Simpan Data Batch Baru
                    $booking->batches()->create([
                        'batch_number' => $index + 1,
                        'quantity' => $totalBatchQty,
                        'unit' => $unit,
                        'porter_name' => $request->batch_porters[$index],
                        'status' => 'waiting'
                    ]);

                    // LOGIKA AUTO-SPLIT PALET
                    $remainingInBatch = $totalBatchQty;
                    $startPalletNumber = $request->pallet_ids[$index];

                    while ($remainingInBatch > 0) {
                        $pallet = Pallet::where('status', 'empty')
                            ->where(function ($q) use ($startPalletNumber) {
                                $q->where('pallet_number', $startPalletNumber)->orWhere('status', 'empty');
                            })
                            ->orderByRaw("pallet_number = '$startPalletNumber' DESC")
                            ->orderBy('pallet_number', 'asc')
                            ->first();

                        if (!$pallet) throw new \Exception("Stok palet tidak mencukupi.");

                        $capacity = 10;
                        $fillAmount = ($remainingInBatch >= $capacity) ? $capacity : $remainingInBatch;

                        $pallet->update([
                            'status' => 'filled',
                            'current_booking_id' => $booking->id,
                            'filled_boxes' => $fillAmount
                        ]);

                        $remainingInBatch -= $fillAmount;
                        $startPalletNumber = null;
                    }
                }
            });

            return redirect()->route('admin.dashboard')->with('success', "Check-in sukses! Status berubah menjadi Approved.");
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function statusPage($status)
    {
        // Tambahkan 'pallets' di sini agar list palet muncul di modal
        $bookings = Booking::with(['customer', 'products', 'batches', 'pallets'])
            ->where('status', $status)
            ->latest()
            ->paginate(10);

        $pageTitle = ucfirst($status) . " Bookings";

        // Variabel pendukung untuk view
        $porters = \App\Models\Porter::where('is_active', true)->get();
        $pallets = Pallet::where('status', 'empty')->get();

        return view('admin.bookings.index', compact('bookings', 'pageTitle', 'status', 'porters', 'pallets'));
    }

    public function palletIndex()
    {
        $pallets = Pallet::orderBy('pallet_number', 'asc')->get();
        return view('admin.pallets.index', compact('pallets'));
    }


    public function palletStore(Request $request)
    {
        $request->validate([
            'pallet_number' => 'required|unique:pallets,pallet_number',
            'line' => 'required',
            'slot_section' => 'required|integer'
        ]);

        Pallet::create([
            'pallet_number' => strtoupper($request->pallet_number),
            'line' => strtoupper($request->line),
            'slot_section' => $request->slot_section,
            'status' => 'empty',
            'filled_boxes' => 0
        ]);

        return back()->with('success', 'Pallet created successfully');
    }


    public function palletGenerate(Request $request)
    {
        // Kita tentukan jumlah yang ingin di-generate (bisa dikembangkan jadi input form)
        $maxLines = $request->input('lines', 2);
        $maxSlots = $request->input('slots', 5);
        $maxPallets = 10;
        try {
            DB::transaction(function () use ($maxLines, $maxSlots, $maxPallets) {
                for ($lineNum = 1; $lineNum <= $maxLines; $lineNum++) {
                    for ($slot = 1; $slot <= $maxSlots; $slot++) {
                        for ($p = 1; $p <= $maxPallets; $p++) {

                            $palletCode = "PLT-L$lineNum-S$slot-P" . str_pad($p, 2, '0', STR_PAD_LEFT);

                            // updateOrCreate akan mencegah error "Duplicate Entry"
                            \App\Models\Pallet::updateOrCreate(
                                ['pallet_number' => $palletCode], // Kunci pencarian
                                [
                                    'line' => $lineNum,
                                    'slot_section' => $slot,
                                    'status' => 'empty',
                                    'box_capacity' => 10,
                                    'filled_boxes' => 0
                                ]
                            );
                        }
                    }
                }
            });

            return back()->with('success', 'Struktur gudang berhasil diperbarui tanpa menghapus data lama.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }
    }

    public function palletDestroy($id)
    {
        $pallet = Pallet::findOrFail($id);
        if ($pallet->status == 'filled') return back()->with('error', 'Cannot delete a filled pallet!');
        $pallet->delete();
        return back()->with('success', 'Pallet deleted');
    }

    public function previewInvoice($id)
    {
        $booking = Booking::with(['products', 'batches', 'customer'])->findOrFail($id);

        if (!$booking->arrival_time) {
            return "<div class='p-8 font-bold text-center text-red-500'>Invoice belum tersedia. Barang belum check-in.</div>";
        }

        // Menggunakan file invoice yang sudah Anda buat
        return view('admin.bookings.invoice', compact('booking'));
    }


    public function downloadInvoice($id)
    {
        $booking = Booking::with(['products', 'batches', 'customer'])
            ->findOrFail($id);

        // Pastikan invoice hanya bisa didownload jika sudah check-in (arrival_time tidak null)
        if (!$booking->arrival_time) {
            return back()->with('error', 'Invoice belum tersedia. Silahkan lakukan check-in terlebih dahulu.');
        }

        $pdf = Pdf::loadView('admin.bookings.invoice', compact('booking'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('Invoice-' . $booking->booking_code . '.pdf');
    }
}
