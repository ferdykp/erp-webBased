<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Customer;
use App\Models\Pallet;
use App\Models\Porter;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AdminBookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['customer.contacts', 'products', 'batches'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $porters = Porter::where('is_active', true)->get();

        $pallets = Pallet::where('status', 'empty')
            ->orderBy('line')
            ->orderBy('slot_section')
            ->get();

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
        $porters = Porter::where('is_active', true)->get();
        $pallets = Pallet::where('status', 'empty')->get();

        // Diarahkan ke view table (index.blade.php di folder bookings)
        return view('admin.bookings.index', compact('bookings', 'pageTitle', 'porters', 'pallets'));
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $newStatus = $request->status;

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

    public function businessIndex(Request $request)
    {
        $search = $request->query('search');

        $customers = Customer::with(['contacts', 'bookings.products', 'bookings.batches'])
            ->when($search, function ($query) use ($search) {
                $query->where('company_name', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        $pageTitle = "Customer Business Monitoring";
        return view('admin.business.index', compact('customers', 'pageTitle', 'search'));
    }

    public function businessDetail($id)
    {
        $booking = Booking::with(['customer', 'products', 'batches', 'pallets'])->findOrFail($id);
        $isSpecial = false;
        $reasons = [];
        $product = $booking->products->first();

        if ($product) {
            // Contoh Kriteria Anomaly: Dosis > 50 kGy atau ada suhu khusus
            if ($product->dmax > 50) {
                $isSpecial = true;
                $reasons[] = "High Dose Request (>50 kGy)";
            }
            if (!empty($product->expect_temp) && $product->expect_temp < 10) {
                $isSpecial = true;
                $reasons[] = "Sensitive Temperature Control Required";
            }
            if ($product->product_type == 'Experiment' || $product->product_type == 'New Product') {
                $isSpecial = true;
                $reasons[] = "Non-Standard Product Category";
            }
        }

        return view('admin.business.detail', compact('booking', 'isSpecial', 'reasons'));
    }

    public function businessApprove(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'approved']);

        return redirect()->route('admin.business.index')
            ->with('success', "Order #{$booking->booking_code} telah disetujui secara teknis/bisnis.");
    }


    public function checkIn(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'booking_code' => 'required',
            'pic_warehouse' => 'required|string',
            'lines' => 'required|array',
            'petaks' => 'required|array',
            'pallet_qty' => 'required|array',
        ]);

        $booking = Booking::where('booking_code', $request->booking_code)->first();

        if (!$booking || $booking->status !== 'pending') {
            return back()->with('error', 'Booking tidak ditemukan atau sudah diproses.');
        }

        try {
            DB::transaction(function () use ($booking, $request) {

                // 2. UPDATE STATUS & DATA UTAMA
                $booking->update([
                    'arrival_time' => now(),
                    'pic_warehouse' => $request->pic_warehouse,
                    // 'total_price'  => $request->finance_total, // Diambil dari hidden input finance_total_hidden
                    'status'       => 'approved',
                ]);

                // 3. Reset data palet lama milik booking ini (jika ada)
                Pallet::where('current_booking_id', $booking->id)->update([
                    'status' => 'empty',
                    'current_booking_id' => null,
                    'filled_boxes' => 0
                ]);

                // 4. Update status fisik palet di gudang
                foreach ($request->lines as $index => $line) {
                    $petak = $request->petaks[$index];
                    $qty = $request->pallet_qty[$index];

                    $pallet = Pallet::where('line', $line)
                        ->where('slot_section', $petak)
                        ->first();

                    if ($pallet) {
                        $pallet->update([
                            'status' => 'filled',
                            'current_booking_id' => $booking->id,
                            'filled_boxes' => $qty
                        ]);
                    }
                }
            });

            return back()->with('success', 'Check-in Berhasil! Status telah diperbarui ke Approved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }


    public function statusPage($status)
    {
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

    // public function palletIndex()
    // {
    //     $pallets = Pallet::orderBy('pallet_number', 'asc')->get();
    //     return view('admin.pallets.index', compact('pallets'));
    // }
    public function palletIndex()
    {
        // Ambil semua lokasi petak
        $allLocations = \App\Models\Pallet::orderBy('line')
            ->orderBy('slot_section')
            ->get();

        // Kirim ke view
        return view('admin.pallets.index', compact('allLocations'));
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
        $maxLines = $request->input('lines', 2);
        $maxSlots = $request->input('slots', 5);

        try {
            DB::transaction(function () use ($maxLines, $maxSlots) {
                for ($lineNum = 1; $lineNum <= $maxLines; $lineNum++) {
                    for ($slot = 1; $slot <= $maxSlots; $slot++) {
                        // Cukup gunakan kombinasi line & slot
                        \App\Models\Pallet::updateOrCreate(
                            ['line' => $lineNum, 'slot_section' => $slot],
                            ['status' => 'empty', 'filled_boxes' => 0]
                        );
                    }
                }
            });
            return back()->with('success', 'Petak gudang berhasil di-generate.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function palletDestroy($id)
    {
        $pallet = Pallet::findOrFail($id);
        if ($pallet->status == 'filled')
            return back()->with('error', 'Cannot delete a filled pallet!');
        $pallet->delete();
        return back()->with('success', 'Pallet deleted');
    }

    // public function previewInvoice($id)
    // {
    //     $booking = Booking::with(['products', 'batches', 'customer'])->findOrFail($id);

    //     if (!$booking->arrival_time) {
    //         return "<div class='p-8 font-bold text-center text-red-500'>Invoice belum tersedia. Barang belum check-in.</div>";
    //     }

    //     // Menggunakan file invoice yang sudah Anda buat
    //     return view('admin.bookings.invoice', compact('booking'));
    // }


    // public function downloadInvoice($id)
    // {
    //     $booking = Booking::with(['products', 'batches', 'customer'])->findOrFail($id);
    //     if (!$booking->arrival_time) {
    //         return back()->with('error', 'Invoice belum tersedia.');
    //     }
    //     $pdf = Pdf::loadView('admin.bookings.invoice', compact('booking'))->setPaper('a4', 'portrait');
    //     return $pdf->download('Invoice-' . $booking->booking_code . '.pdf');
    // }


    public function create()
    {
        // Mengambil user dengan role customer untuk dropdown pilihan admin
        // $customers = User::where('role', 'admin')->orderBy('name', 'asc')->get();
        $customers = Customer::orderBy('company_name', 'asc')->get();
        return view('admin.bookings.create', compact('customers'));
    }

    public function store(Request $request)
    {
        // Generate booking code berdasarkan tanggal + urutan bulanan
        $today = now();
        $prefix = $today->format('ymd'); // contoh: 260328

        // Hitung jumlah booking di bulan yang sama
        $countThisMonth = Booking::whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->count();

        // Urutan (001, 002, dst)
        $sequence = str_pad($countThisMonth + 1, 3, '0', STR_PAD_LEFT);

        // Final booking code
        $bookingCode = $prefix . $sequence;
        // 1. Validasi
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'product_name'   => 'required|string',
            'quantity'       => 'required|numeric',
            'dimension_pack' => 'required|string', // Format: 20x20x20
            'vol_total'      => 'required|numeric',
            'payment_status' => 'required|in:paid,unpaid',
        ]);

        try {
            DB::beginTransaction();

            // 2. Create Master Booking
            $booking = Booking::create([
                'user_id'        => auth()->id(), // <--- SOLUSI UTAMA: Ambil ID admin yang login
                'customer_id'  => $request->customer_id,
                // 'booking_code' => 'BOK-' . strtoupper(Str::random(8)),
                // 'booking_code' => 'EB-' . strtoupper(\Str::random(6)),
                'booking_code'   => $bookingCode,
                'status'       => 'pending',
                'payment_status' => $request->payment_status,
                'qr_token' => \Str::uuid(),
                'total_price'    => $request->total_price ?? 0, // Pastikan field ini ada di tabel bookings
            ]);

            // 3. Create Booking Product (Data Aktual)
            BookingProduct::create([
                'booking_id'           => $booking->id,
                'product_name'         => $request->product_name,
                'product_type'         => $request->product_type,
                'quantity'             => $request->quantity,
                'unit'                 => $request->unit,
                'dmin'                 => $request->dmin,
                'dmax'                 => $request->dmax,
                'dimension_pack'       => $request->dimension_pack,
                'vol_per_pcs'          => $request->vol_per_pcs,
                'vol_total'            => $request->vol_total,
                'net_weight_pcs'       => $request->net_weight_pcs,
                'total_net_weight'     => $request->total_net_weight,
                'gross_weight_per_pcs' => $request->gross_weight_per_pcs,
                'total_gross_weight'   => $request->total_gross_weight,
                'expect_temp'          => $request->expect_temp,
            ]);

            DB::commit();
            return redirect()->route('admin.bookings')->with('success', 'Order #' . $booking->booking_code . ' berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // public function storePlacement(Request $request, $bookingId)
    // {
    //     // Validasi input array dari form
    //     $request->validate([
    //         'product_names' => 'required|array', // Pastikan name="product_names[]" di form
    //         'lines' => 'required|array',
    //         'petaks' => 'required|array',
    //         'pallet_qty' => 'required|array',
    //     ]);

    //     try {
    //         DB::transaction(function () use ($request, $bookingId) {
    //             // Hapus data lama milik booking ini agar tidak duplikat saat edit
    //             \App\Models\PalletContent::where('booking_id', $bookingId)->delete();

    //             foreach ($request->lines as $index => $line) {
    //                 $petak = $request->petaks[$index];
    //                 $qty = $request->pallet_qty[$index];
    //                 $productName = $request->product_names[$index];

    //                 // Cari master lokasi palet
    //                 $slot = \App\Models\Pallet::where('line', (string)$line)
    //                     ->where('slot_section', (int)$petak)
    //                     ->first();

    //                 if (!$slot) {
    //                     throw new \Exception("Slot Line {$line} Petak {$petak} tidak terdaftar di sistem!");
    //                 }

    //                 // Simpan detail produk di lokasi tersebut
    //                 \App\Models\PalletContent::create([
    //                     'pallet_id'    => $slot->id,
    //                     'booking_id'   => $bookingId,
    //                     'product_name' => $productName,
    //                     'quantity'     => $qty
    //                 ]);
    //             }
    //         });

    //         return back()->with('success', 'Penempatan produk di petak berhasil disimpan.');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Gagal: ' . $e->getMessage());
    //     }
    // }
    public function storePlacement(Request $request, $bookingId)
    {
        $request->validate([
            'product_names' => 'required|array',
            'lines' => 'required|array',
            'petaks' => 'required|array',
            'pallet_qty' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($request, $bookingId) {

                // ✅ TAMBAHKAN INI — update status booking
                $booking = Booking::findOrFail($bookingId);
                $booking->update([
                    'status'       => 'approved', // sesuaikan dengan enum status kamu
                    'arrival_time' => now(),
                    'pic_warehouse' => $request->pic_warehouse ?? null,
                ]);

                // Hapus data lama
                \App\Models\PalletContent::where('booking_id', $bookingId)->delete();

                foreach ($request->lines as $index => $line) {
                    $petak = $request->petaks[$index];
                    $qty = $request->pallet_qty[$index];
                    $productName = $request->product_names[$index];

                    $slot = \App\Models\Pallet::where('line', (string)$line)
                        ->where('slot_section', (int)$petak)
                        ->first();

                    if (!$slot) {
                        throw new \Exception("Slot Line {$line} Petak {$petak} tidak terdaftar di sistem!");
                    }

                    // ✅ TAMBAHKAN INI — update status fisik palet
                    $slot->update([
                        'status'             => 'filled',
                        'current_booking_id' => $bookingId,
                        'filled_boxes'       => $qty,
                    ]);

                    \App\Models\PalletContent::create([
                        'pallet_id'    => $slot->id,
                        'booking_id'   => $bookingId,
                        'product_name' => $productName,
                        'quantity'     => $qty,
                    ]);
                }
            });

            return back()->with('success', 'Check-in berhasil! Status booking telah diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
    public function show($id)
    {
        // Load data lengkap dengan relasi
        $booking = Booking::with(['customer.contacts', 'products', 'batches', 'pallets'])->findOrFail($id);

        // Return hanya view partial-nya saja
        return view('admin.bookings.partials.detail-content', compact('booking'));
    }

    // public function downloadInvoice($id)
    // {
    //     $booking = Booking::with(['products', 'batches', 'customer'])->findOrFail($id);

    //     $pdf = Pdf::loadView('admin.bookings.invoice', [
    //         'booking' => $booking,
    //         'pdf' => true // 🔥 kirim flag
    //     ])->setPaper('a4', 'portrait');

    //     return $pdf->stream('Invoice-' . $booking->booking_code . '.pdf');
    // }


    public function previewInvoice($id)
    {
        $booking = Booking::with(['products', 'batches', 'customer.contacts'])->findOrFail($id);

        if (!$booking->arrival_time) {
            return "<div style='padding:40px; text-align:center; font-weight:bold; color:red;'>
                    Invoice belum tersedia. Barang belum check-in.
                </div>";
        }

        return view('admin.bookings.invoice', compact('booking'));
    }
}
