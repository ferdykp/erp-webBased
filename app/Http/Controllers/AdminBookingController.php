<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\BookingProduct;
use Illuminate\Support\Str;


use App\Models\Pallet;
use App\Models\Porter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class AdminBookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['customer', 'products', 'batches'])
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

    // public function checkIn(Request $request)
    // {
    //     $request->validate([
    //         'booking_code' => 'required',
    //         'pic_warehouse' => 'required|string',
    //         'porters' => 'required|array|min:1',
    //         // 'lines' => 'required|array',
    //         // 'petaks' => 'required|array',
    //         // 'pallet_qty' => 'required|array',
    //         'total_qty' => 'required|numeric', // Tambahkan ini
    //         'per_pallet' => 'required|numeric',

    //         'vol_per_pcs' => 'nullable|numeric',
    //         'vol_total' => 'nullable|numeric',
    //         'net_weight_pcs' => 'nullable|numeric',
    //         'total_net_weight' => 'nullable|numeric',
    //         'gross_weight_pcs' => 'nullable|numeric',
    //         'total_gross_weight' => 'nullable|numeric',
    //     ]);

    //     $booking = Booking::with(['products'])
    //         ->where('booking_code', $request->booking_code)
    //         ->first();

    //     if (!$booking) {
    //         return back()->with('error', 'Booking tidak ditemukan');
    //     }

    //     if ($booking->status !== 'pending') {
    //         return back()->with('error', 'Gagal! Order ini sudah diproses.');
    //     }

    //     try {

    //         DB::transaction(function () use ($booking, $request) {

    //             $product = $booking->products->first();

    //             $product->update([
    //                 'vol_per_pcs' => $request->vol_per_pcs,
    //                 'vol_total' => $request->vol_total,
    //                 'net_weight_pcs' => $request->net_weight_pcs,
    //                 'total_net_weight' => $request->total_net_weight,
    //                 'gross_weight_pcs' => $request->gross_weight_pcs,
    //                 'total_gross_weight' => $request->total_gross_weight,
    //             ]);

    //             // Reset pallet jika sebelumnya pernah dipakai
    //             Pallet::where('current_booking_id', $booking->id)->update([
    //                 'status' => 'empty',
    //                 'current_booking_id' => null,
    //                 'filled_boxes' => 0
    //             ]);

    //             $booking->update([
    //                 'arrival_time' => now(),
    //                 'pic_warehouse' => $request->pic_warehouse,
    //                 'status' => 'approved',
    //             ]);

    //             // 2. Clear rencana lama jika ada (agar tidak double)
    //             $booking->placementDetails()->delete();

    //             // 3. Logika Pembagian Logis (Batching)
    //             // Misalnya: Total 194, Per palet 30 -> 30, 30, 30, 30, 30, 30, 14
    //             $totalQty = $request->total_qty; // Ambil dari input form
    //             $perPallet = $request->per_pallet; // Ambil dari input form

    //             $sequence = 1;
    //             $remaining = $totalQty;

    //             while ($remaining > 0) {
    //                 $qty = ($remaining >= $perPallet) ? $perPallet : $remaining;

    //                 $booking->placementDetails()->create([
    //                     'booking_id' => $booking->id, // Tambahkan ini
    //                     'sequence' => $sequence,
    //                     'quantity' => $qty,
    //                     'status' => 'planned' // Belum ditempatkan di rak
    //                 ]);

    //                 $remaining -= $qty;
    //                 $sequence++;
    //             }
    //         });

    //         return redirect()->route('admin.dashboard')->with('success', 'Rencana penempatan dibuat. Silahkan tentukan lokasi petak.');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Gagal: ' . $e->getMessage());
    //     }
    // }
    public function checkIn(Request $request)
    {
        $request->validate([
            'booking_code' => 'required',
            'pic_warehouse' => 'required|string',
            'porters' => 'required|array|min:1',
            'total_qty' => 'required|numeric',
            'per_pallet' => 'required|numeric',

            'vol_per_pcs' => 'nullable|numeric',
            'vol_total' => 'nullable|numeric',
            'net_weight_pcs' => 'nullable|numeric',
            'total_net_weight' => 'nullable|numeric',
            'gross_weight_pcs' => 'nullable|numeric',
            'total_gross_weight' => 'nullable|numeric',
        ]);

        $booking = Booking::with(['products', 'customer'])
            ->where('booking_code', $request->booking_code)
            ->first();

        if (!$booking) {
            return back()->with('error', 'Booking tidak ditemukan');
        }

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Gagal! Order ini sudah diproses.');
        }

        try {

            DB::transaction(function () use ($booking, $request) {

                $product = $booking->products->first();

                /*
            |--------------------------------------------------------------------------
            | Update Product Dimension Data
            |--------------------------------------------------------------------------
            */

                $product->update([
                    'vol_per_pcs' => $request->vol_per_pcs,
                    'vol_total' => $request->vol_total,
                    'net_weight_pcs' => $request->net_weight_pcs,
                    'total_net_weight' => $request->total_net_weight,
                    'gross_weight_pcs' => $request->gross_weight_pcs,
                    'total_gross_weight' => $request->total_gross_weight,
                ]);


                /*
            |--------------------------------------------------------------------------
            | Reset Pallet jika sebelumnya pernah dipakai
            |--------------------------------------------------------------------------
            */

                Pallet::where('current_booking_id', $booking->id)->update([
                    'status' => 'empty',
                    'current_booking_id' => null,
                    'filled_boxes' => 0
                ]);


                /*
            |--------------------------------------------------------------------------
            | Update Booking Status
            |--------------------------------------------------------------------------
            */

                $booking->update([
                    'arrival_time' => now(),
                    'pic_warehouse' => $request->pic_warehouse,
                    'status' => 'approved',
                ]);


                /*
            |--------------------------------------------------------------------------
            | Clear Placement Lama
            |--------------------------------------------------------------------------
            */

                $booking->placementDetails()->delete();


                /*
            |--------------------------------------------------------------------------
            | Batching / Pallet Planning
            |--------------------------------------------------------------------------
            */

                $totalQty = $request->total_qty;
                $perPallet = $request->per_pallet;

                $sequence = 1;
                $remaining = $totalQty;

                while ($remaining > 0) {

                    $qty = ($remaining >= $perPallet) ? $perPallet : $remaining;

                    $booking->placementDetails()->create([
                        'booking_id' => $booking->id,
                        'sequence' => $sequence,
                        'quantity' => $qty,
                        'status' => 'planned'
                    ]);

                    $remaining -= $qty;
                    $sequence++;
                }


                /*
            |--------------------------------------------------------------------------
            | Generate Proforma Invoice
            |--------------------------------------------------------------------------
            */

                $invoice = Invoice::create([
                    'booking_id' => $booking->id,
                    'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                    'type' => 'proforma',
                    'status' => 'draft',
                    'invoice_date' => now(),
                ]);


                /*
            |--------------------------------------------------------------------------
            | Pricing Engine (Basic)
            |--------------------------------------------------------------------------
            */

                $volume = $request->vol_total ?? 0;

                $pricePerM3 = 50; // harga default irradiation per m3

                $subtotal = $volume * $pricePerM3;


                /*
            |--------------------------------------------------------------------------
            | Invoice Item
            |--------------------------------------------------------------------------
            */

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => 'E-Beam Irradiation Service',
                    'qty' => $volume,
                    'unit_price' => $pricePerM3,
                    'total' => $subtotal
                ]);


                /*
            |--------------------------------------------------------------------------
            | Update Invoice Total
            |--------------------------------------------------------------------------
            */

                $invoice->update([
                    'subtotal' => $subtotal,
                    'total' => $subtotal
                ]);


                /*
            |--------------------------------------------------------------------------
            | Log Activity (Opsional ERP Audit Trail)
            |--------------------------------------------------------------------------
            */

                activity()
                    ->performedOn($booking)
                    ->causedBy(auth()->user())
                    ->log('Warehouse Check-In completed');
            });

            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Check-in berhasil. Rencana pallet dan proforma invoice dibuat.');
        } catch (\Exception $e) {

            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    // Di dalam AdminBookingController.php

    public function storePlacement(Request $request, $bookingId)
    {
        // Validasi input array dari form
        $request->validate([
            'product_names' => 'required|array', // Pastikan name="product_names[]" di form
            'lines' => 'required|array',
            'petaks' => 'required|array',
            'pallet_qty' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($request, $bookingId) {
                // Hapus data lama milik booking ini agar tidak duplikat saat edit
                \App\Models\PalletContent::where('booking_id', $bookingId)->delete();

                foreach ($request->lines as $index => $line) {
                    $petak = $request->petaks[$index];
                    $qty = $request->pallet_qty[$index];
                    $productName = $request->product_names[$index];

                    // Cari master lokasi palet
                    $slot = \App\Models\Pallet::where('line', (string)$line)
                        ->where('slot_section', (int)$petak)
                        ->first();

                    if (!$slot) {
                        throw new \Exception("Slot Line {$line} Petak {$petak} tidak terdaftar di sistem!");
                    }

                    // Simpan detail produk di lokasi tersebut
                    \App\Models\PalletContent::create([
                        'pallet_id'    => $slot->id,
                        'booking_id'   => $bookingId,
                        'product_name' => $productName,
                        'quantity'     => $qty
                    ]);
                }
            });

            return back()->with('success', 'Penempatan produk di petak berhasil disimpan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage());
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
        $porters = Porter::where('is_active', true)->get();
        // $pallets = Pallet::where('status', 'empty')->get();
        $pallets = Pallet::all();

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
        if ($pallet->status == 'filled') return back()->with('error', 'Cannot delete a filled pallet!');
        $pallet->delete();
        return back()->with('success', 'Pallet deleted');
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


    public function create()
    {
        // Mengambil user dengan role customer untuk dropdown pilihan admin
        // $customers = User::where('role', 'admin')->orderBy('name', 'asc')->get();
        $customers = Customer::orderBy('name', 'asc')->get();
        return view('admin.bookings.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'product_name'         => 'required|string|max:255',
            'product_type'         => 'required|string|max:255',
            'quantity'             => 'required|numeric|min:1',
            'unit'                 => 'required',
            'dmin'                 => 'required|numeric',
            'dmax'                 => 'required|numeric',
            'dim_length'           => 'required|numeric',
            'dim_width'            => 'required|numeric',
            'dim_height'           => 'required|numeric',
            'gross_weight_per_pcs' => 'required|numeric',
            'expect_temp'          => 'nullable|string',
        ], [
            'customer_id.exists'   => 'Customer yang dipilih tidak terdaftar dalam sistem.',
        ]);

        try {
            $booking = Booking::create([
                'customer_id'  => $request->customer_id,
                'booking_code' => 'BOK-' . strtoupper(Str::random(8)),
                'status'       => 'pending',
            ]);

            BookingProduct::create([
                'booking_id'           => $booking->id,
                'product_name'         => $request->product_name,
                'product_type'         => $request->product_type,
                'quantity'             => $request->quantity,
                'unit'                 => $request->unit,
                'dmin'                 => $request->dmin,
                'dmax'                 => $request->dmax,
                'dimension_pack'       => $request->dim_length . 'x' . $request->dim_width . 'x' . $request->dim_height,
                'gross_weight_per_pcs' => $request->gross_weight_per_pcs,
                'expect_temp'          => $request->expect_temp,
            ]);

            return redirect()->route('admin.bookings')->with('success', 'Booking berhasil dibuat untuk customer.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
