<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Customer;
use App\Models\Pallet;
use App\Models\Porter;
use App\Models\WarehousePic;
// use Illuminate\Support\Str;
// use Barryvdh\DomPDF\Facade\Pdf;
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

        // $pallets = Pallet::where('status', 'empty')
        //     ->orderBy('line')
        //     ->orderBy('slot_section')
        //     ->get();
        $pallets = Pallet::orderBy('line')
            ->orderBy('slot_section')
            ->get();
        $warehousePics = WarehousePic::where('is_active', true)->get();


        return view('admin.dashboard.index', compact('bookings', 'porters', 'pallets', 'warehousePics'));
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
        // $pallets = Pallet::where('status', 'empty')->get();
        $pallets = Pallet::orderBy('line')
            ->orderBy('slot_section')
            ->get();

        $warehousePics = WarehousePic::where('is_active', true)->get();

        // Diarahkan ke view table (index.blade.php di folder bookings)
        return view('admin.bookings.index', compact('bookings', 'pageTitle', 'porters', 'pallets', 'warehousePics'));
        // return view('admin.bookings.index', compact('bookings', 'pageTitle', 'porters'));
    }

    public function updateStatus(Request $request, int $id)
    {
        // dd($request->all()); // Ini akan menghentikan proses dan menampilkan isi data yang dikirim
        $request->validate([
            'status' => 'required|string|in:pending,approved,completed,cancelled'
        ]);
        $booking = Booking::findOrFail($id);
        $newStatus = $request->status;

        $booking->update(['status' => $newStatus]);

        // if ($newStatus === 'completed') {
        //     Pallet::where('current_booking_id', $booking->id)->update([
        //         'status' => 'empty',
        //         'current_booking_id' => null,
        //         'filled_boxes' => 0
        //     ]);
        // }
        if ($newStatus === 'completed') {

            $palletContents = \App\Models\PalletContent::where('booking_id', $booking->id)->get();

            foreach ($palletContents as $content) {
                $pallet = Pallet::find($content->pallet_id);

                if ($pallet) {
                    $pallet->decrement('filled_boxes', $content->quantity);
                }
            }

            // hapus isi pallet khusus booking ini
            \App\Models\PalletContent::where('booking_id', $booking->id)->delete();
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

    public function businessDetail(int $id)
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

    public function businessApprove(Request $request, int $id)
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
                            // 'status' => 'filled',
                            'current_booking_id' => $booking->id,
                            // 'filled_boxes' => $qty
                            'filled_boxes' => $pallet->filled_boxes + $qty

                        ]);
                    }
                }
            });

            return back()->with('success', 'Check-in Berhasil! Status telah diperbarui ke Approved.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }


    public function statusPage(string $status)
    {
        $bookings = Booking::with(['customer', 'products', 'batches', 'pallets'])
            ->where('status', $status)
            ->latest()
            ->paginate(10);

        $pageTitle = ucfirst($status) . " Bookings";

        // Variabel pendukung untuk view
        $porters = \App\Models\Porter::where('is_active', true)->get();
        // $pallets = Pallet::where('status', 'empty')->get();
        $pallets = Pallet::orderBy('line')
            ->orderBy('slot_section')
            ->get();

        $warehousePics = WarehousePic::where('is_active', true)->get();


        return view('admin.bookings.index', compact('bookings', 'pageTitle', 'status', 'porters', 'pallets', 'warehousePics'));
        // return view('admin.bookings.index', compact('bookings', 'pageTitle', 'status', 'porters'));
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
            // 'status' => 'empty',
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
                            // ['status' => 'empty', 'filled_boxes' => 0]
                            ['filled_boxes' => 0]
                        );
                    }
                }
            });
            return back()->with('success', 'Petak gudang berhasil di-generate.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function palletDestroy(int $id)
    {
        $pallet = Pallet::findOrFail($id);
        // if ($pallet->status == 'filled')
        //     return back()->with('error', 'Cannot delete a filled pallet!');
        if ($pallet->filled_boxes > 0) {
            return back()->with('error', 'Cannot delete a used pallet!');
        }
        $pallet->delete();
        return back()->with('success', 'Pallet deleted');
    }


    public function create()
    {
        $customers = Customer::orderBy('company_name', 'asc')->get();
        return view('admin.bookings.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $today = now();
        $prefix = $today->format('ymd');

        // $countThisMonth = Booking::whereYear('created_at', $today->year)
        //     ->whereMonth('created_at', $today->month)
        //     ->count();
        $countToday = Booking::whereDate('created_at', $today->toDateString())
            ->count();

        $sequence = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

        $bookingCode = $prefix . $sequence;
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'product_name'   => 'required|string',
            'quantity'       => 'required|numeric',
            'dimension_pack' => 'required|string',
            'vol_total'      => 'required|numeric',
            'payment_status' => 'required|in:paid,unpaid',
        ]);

        try {
            DB::beginTransaction();

            $booking = Booking::create([
                'user_id'        => auth()->id(),
                'customer_id'  => $request->customer_id,
                'booking_code'   => $bookingCode,
                'status'       => 'pending',
                'payment_status' => $request->payment_status,
                'qr_token' => \Str::uuid(),
                'total_price'    => $request->total_price ?? 0,
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
                'density_gross'        => $request->density_gross,
                'density_nett'         => $request->density_nett,
            ]);

            DB::commit();
            return redirect()->route('admin.bookings')->with('success', 'Order #' . $booking->booking_code . ' berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form edit untuk booking tertentu.
     */
    public function edit(int $id)
    {
        // Load booking beserta relasi produknya
        $booking = Booking::with('products')->findOrFail($id);

        // Ambil data customer untuk dropdown
        $customers = Customer::orderBy('company_name', 'asc')->get();

        // Data produk diambil dari relasi (asumsi 1 booking 1 produk sesuai logic store)
        $bookingProduct = $booking->products->first();

        // Gabungkan data agar mudah diakses di view
        // Kita timpa properti booking dengan data dari booking_products agar view tetap bersih
        if ($bookingProduct) {
            $booking->product_name = $bookingProduct->product_name;
            $booking->product_type = $bookingProduct->product_type;
            $booking->quantity = $bookingProduct->quantity;
            $booking->unit = $bookingProduct->unit;
            $booking->dmin = $bookingProduct->dmin;
            $booking->dmax = $bookingProduct->dmax;
            $booking->dimension_pack = $bookingProduct->dimension_pack;
            $booking->vol_per_pcs = $bookingProduct->vol_per_pcs;
            $booking->vol_total = $bookingProduct->vol_total;
            $booking->net_weight_pcs = $bookingProduct->net_weight_pcs;
            $booking->total_net_weight = $bookingProduct->total_net_weight;
            $booking->gross_weight_per_pcs = $bookingProduct->gross_weight_per_pcs;
            $booking->total_gross_weight = $bookingProduct->total_gross_weight;
            $booking->expect_temp = $bookingProduct->expect_temp;
            $booking->density_gross = $bookingProduct->density_gross;
            $booking->density_nett = $bookingProduct->density_nett;
        }

        return view('admin.bookings.edit', compact('booking', 'customers'));
    }

    /**
     * Memperbarui data booking di database.
     */
    public function update(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);

        // 1. HAPUS 'status' dari validasi karena memang tidak ada di form
        $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'product_name'   => 'required|string',
            'quantity'       => 'required|numeric',
            'dimension_pack' => 'required|string',
            'vol_total'      => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // 2. Update data UTAMA tanpa menyentuh status
            // Jangan masukkan 'status' => $request->status di sini
            $booking->update([
                'customer_id'    => $request->customer_id,
                'total_price'    => $request->total_price ?? $booking->total_price,
                'payment_status' => $request->payment_status ?? $booking->payment_status,
            ]);

            // 3. Update data PRODUK
            BookingProduct::updateOrCreate(
                ['booking_id' => $booking->id],
                [
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
                    'density_gross'        => $request->density_gross,
                    'density_nett'         => $request->density_nett,
                ]
            );

            DB::commit();
            return redirect()->route('admin.bookings')->with('success', 'Order #' . $booking->booking_code . ' berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
        }
    }

    public function generateCode()
    {
        $today = now();
        $prefix = $today->format('ymd');

        $countToday = Booking::whereDate('created_at', $today->toDateString())
            ->count();


        $sequence = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

        return response()->json([
            'code' => $prefix . $sequence
        ]);
    }
    public function storePlacement(Request $request, int $bookingId)
    {
        $request->validate([
            'product_names' => 'required|array',
            'lines' => 'required|array',
            'petaks' => 'required|array',
            'pallet_qty' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($request, $bookingId) {

                $booking = Booking::findOrFail($bookingId);
                $booking->update([
                    'status'       => 'approved',
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

                    // $slot->update([
                    //     'status'             => 'filled',
                    //     'current_booking_id' => $bookingId,
                    //     'filled_boxes'       => $qty,
                    // ]);
                    $slot->update([
                        'current_booking_id' => $bookingId,
                        'filled_boxes' => $slot->filled_boxes + $qty,
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
    public function show(int $id)
    {
        $booking = Booking::with(['customer.contacts', 'products', 'batches', 'pallets'])->findOrFail($id);

        return view('admin.bookings.partials.detail-content', compact('booking'));
    }

    public function previewInvoice(int $id)
    {
        $booking = Booking::with(['products', 'batches', 'customer.contacts'])->findOrFail($id);

        if (!$booking->arrival_time) {
            return "<div style='padding:40px; text-align:center; font-weight:bold; color:red;'>
                    Invoice belum tersedia. Barang belum check-in.
                </div>";
        }

        return view('admin.bookings.invoice', compact('booking'));
    }

    public function relocatePallet(Request $request)
    {
        $request->validate([
            'pallet_content_id' => 'required|exists:pallet_contents,id',
            'new_pallet_id'     => 'required|exists:pallets,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Ambil data isi pallet (barang) yang akan dipindah
                $content = \App\Models\PalletContent::findOrFail($request->pallet_content_id);
                $oldPallet = Pallet::findOrFail($content->pallet_id);
                $newPallet = Pallet::findOrFail($request->new_pallet_id);

                // 2. Kurangi 'filled_boxes' di pallet asal (Pre-Irradiation)
                $oldPallet->decrement('filled_boxes', $content->quantity);

                // Opsional: Jika pallet lama benar-benar kosong, hapus booking_id-nya
                if ($oldPallet->filled_boxes <= 0) {
                    $oldPallet->update(['current_booking_id' => null]);
                }

                // 3. Tambah 'filled_boxes' di pallet tujuan (Post-Irradiation)
                $newPallet->increment('filled_boxes', $content->quantity);

                // 4. Update relasi di tabel isi pallet agar menunjuk ke lokasi (pallet) baru
                $content->update([
                    'pallet_id' => $newPallet->id
                ]);

                // 5. Tandai pallet baru dengan booking_id tersebut
                $newPallet->update(['current_booking_id' => $content->booking_id]);
            });

            return back()->with('success', 'Pallet berhasil dipindahkan ke area Post-Irradiation.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }
    public function finishIndex()
    {
        // 1. Ambil data booking yang memiliki batch berstatus 'done' (Finish)
        // Kita load relasi 'products', 'batches.qa', 'customer', dan 'batches.productionLine'
        $bookings = Booking::with([
            'customer',
            'products',
            'batches' => function ($query) {
                $query->where('status', 'done');
            },
            'batches.qa',
            'batches.productionLine'
        ])
            ->whereHas('batches', function ($query) {
                $query->where('status', 'done');
            })
            ->latest()
            ->get();

        // 2. Ambil semua data master lokasi pallet untuk pilihan "Post-Irradiation Area"
        $allLocations = \App\Models\Pallet::orderBy('line')
            ->orderBy('slot_section')
            ->get();

        // 3. Kirim ke view admin.production.finish
        return view('admin.production.finish', compact('bookings', 'allLocations'));
    }

    // app/Http/Controllers/Admin/PalletController.php

    public function addLayout(Request $request)
    {
        $request->validate([
            'line_number' => 'required|integer|min:1',
            'slot_count' => 'required|integer|min:1',
        ]);

        $line = $request->line_number;
        $slots = $request->slot_count;
        $addedCount = 0;

        for ($i = 1; $i <= $slots; $i++) {
            // Cek apakah line dan petak ini sudah ada
            $exists = \App\Models\Pallet::where('line', $line)
                ->where('slot_section', $i)
                ->exists();

            if (!$exists) {
                \App\Models\Pallet::create([
                    'line' => $line,
                    'slot_section' => $i,
                    'status' => 'empty' // sesuaikan dengan kolom di DB Anda
                ]);
                $addedCount++;
            }
        }

        return redirect()->back()->with('success', "Berhasil menambahkan $addedCount petak baru pada Line $line.");
    }

    public function destroy(int $id)
    {
        try {
            $booking = \App\Models\Booking::findOrFail($id);

            // 1. Hapus isi palet yang terkait dengan booking ini
            // Pastikan model PalletContent sudah ada
            \DB::table('pallet_contents')->where('booking_id', $id)->delete();

            // 2. Hapus produk terkait (jika ada relasi)
            $booking->products()->delete();

            // 3. Baru hapus booking-nya
            $booking->delete();

            return redirect()->route('admin.bookings')
                ->with('success', 'Booking and associated pallet contents deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
