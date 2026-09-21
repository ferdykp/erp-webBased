<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Customer;
use App\Models\Pallet;
use App\Models\PalletContent;
use App\Models\PlacementDetail;
use App\Models\Porter;
use App\Models\WarehousePic;
// use Illuminate\Support\Str;
// use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;


class AdminBookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['customer.contacts', 'products', 'batches'])
            ->where('booking_type', 'regular')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $porters = Porter::where('is_active', true)->orderBy('name')->get();
        $pallets = Pallet::orderBy('line')->orderBy('slot_section')->get();
        $warehousePics = WarehousePic::where('is_active', true)->orderBy('name')->get();

        return view('admin.dashboard.index', compact('bookings', 'porters', 'pallets', 'warehousePics'));
    }

    public function allOrder()
    {
        $bookings = Booking::with(['customer', 'products', 'batches', 'pallets'])
            ->where('booking_type', 'regular')
            ->latest()
            ->paginate(10);

        $pageTitle = 'All Order History';
        $porters = Porter::where('is_active', true)->orderBy('name')->get();
        $pallets = Pallet::orderBy('line')->orderBy('slot_section')->get();
        $warehousePics = WarehousePic::where('is_active', true)->orderBy('name')->get();

        return view('admin.bookings.index', compact('bookings', 'pageTitle', 'porters', 'pallets', 'warehousePics'));
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,approved,processing,completed,cancelled',
        ]);

        $booking = Booking::findOrFail($id);

        DB::transaction(function () use ($booking, $validated) {
            $booking->update(['status' => $validated['status']]);

            // Completed means irradiation/QA is finished, not necessarily that goods
            // have left the warehouse. Inventory is released on cancellation or shipping.
            if ($validated['status'] === 'cancelled') {
                $this->releaseBookingInventory($booking->id);
            }
        });

        return back()->with('success', 'Status berhasil diperbarui ke ' . strtoupper($validated['status']));
    }

    public function businessIndex(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $customers = Customer::with(['contacts', 'bookings.products', 'bookings.batches'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('contacts', function ($contact) use ($search) {
                            $contact->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pageTitle = 'Customer Business Monitoring';
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
        $booking = Booking::where('booking_code', $request->booking_code)->firstOrFail();
        return $this->storePlacement($request, $booking->id);
    }


    public function statusPage(string $status)
    {
        abort_unless(in_array($status, ['pending', 'approved', 'processing', 'completed', 'cancelled'], true), 404);

        $bookings = Booking::with(['customer', 'products', 'batches', 'pallets'])
            ->where('booking_type', 'regular')
            ->where('status', $status)
            ->latest()
            ->paginate(10);

        $pageTitle = ucfirst($status) . ' Bookings';
        $porters = Porter::where('is_active', true)->orderBy('name')->get();
        $pallets = Pallet::orderBy('line')->orderBy('slot_section')->get();
        $warehousePics = WarehousePic::where('is_active', true)->orderBy('name')->get();

        return view('admin.bookings.index', compact('bookings', 'pageTitle', 'status', 'porters', 'pallets', 'warehousePics'));
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
        $validated = $request->validate([
            'line' => 'required|string|max:50',
            'slot_section' => 'required|integer|min:1',
        ]);

        Pallet::firstOrCreate(
            ['line' => strtoupper(trim($validated['line'])), 'slot_section' => $validated['slot_section']],
            ['status' => 'empty', 'filled_boxes' => 0]
        );

        return back()->with('success', 'Pallet location created successfully.');
    }

    public function palletGenerate(Request $request)
    {
        $validated = $request->validate([
            'lines' => 'nullable|integer|min:1|max:100',
            'slots' => 'nullable|integer|min:1|max:100',
        ]);

        $maxLines = $validated['lines'] ?? 2;
        $maxSlots = $validated['slots'] ?? 5;

        DB::transaction(function () use ($maxLines, $maxSlots) {
            for ($lineNum = 1; $lineNum <= $maxLines; $lineNum++) {
                for ($slot = 1; $slot <= $maxSlots; $slot++) {
                    // firstOrCreate is intentional: generating layout must never reset
                    // stock already stored in an existing location.
                    Pallet::firstOrCreate(
                        ['line' => (string) $lineNum, 'slot_section' => $slot],
                        ['status' => 'empty', 'filled_boxes' => 0]
                    );
                }
            }
        });

        return back()->with('success', 'Petak gudang berhasil di-generate tanpa mereset stok yang sudah ada.');
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
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'dmin' => 'required|numeric|min:0',
            'dmax' => 'nullable|numeric|gte:dmin',
            'dimension_pack' => 'required|string|max:255',
            'vol_per_pcs' => 'nullable|numeric|min:0',
            'net_weight_pcs' => 'nullable|numeric|min:0',
            'gross_weight_per_pcs' => 'required|numeric|min:0',
            'expect_temp' => 'nullable|string|max:100',
            'payment_status' => 'required|in:paid,unpaid',
            'total_price' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $quantity = (int) $validated['quantity'];
        $volPerPcs = (float) ($validated['vol_per_pcs'] ?? 0);
        $netPerPcs = (float) ($validated['net_weight_pcs'] ?? 0);
        $grossPerPcs = (float) $validated['gross_weight_per_pcs'];
        $volTotal = $volPerPcs * $quantity;
        $totalNet = $netPerPcs * $quantity;
        $totalGross = $grossPerPcs * $quantity;

        $booking = DB::transaction(function () use ($validated, $customer, $quantity, $volPerPcs, $netPerPcs, $grossPerPcs, $volTotal, $totalNet, $totalGross) {
            $booking = Booking::create([
                'user_id' => $customer->user_id,
                'customer_id' => $customer->id,
                'booking_code' => $this->nextBookingCodeForDate(now()),
                'booking_type' => 'regular',
                'status' => 'pending',
                'payment_status' => $validated['payment_status'],
                'qr_token' => Str::uuid(),
                'total_price' => $validated['total_price'] ?? 0,
            ]);

            BookingProduct::create([
                'booking_id' => $booking->id,
                'product_name' => $validated['product_name'],
                'product_type' => $validated['product_type'],
                'quantity' => $quantity,
                'unit' => $validated['unit'],
                'dmin' => $validated['dmin'],
                'dmax' => $validated['dmax'] ?? null,
                'dimension_pack' => $validated['dimension_pack'],
                'vol_per_pcs' => $volPerPcs,
                'vol_total' => $volTotal,
                'net_weight_pcs' => $netPerPcs,
                'total_net_weight' => $totalNet,
                'gross_weight_per_pcs' => $grossPerPcs,
                'total_gross_weight' => $totalGross,
                'expect_temp' => $validated['expect_temp'] ?? null,
                'density_gross' => $volTotal > 0 ? $totalGross / $volTotal : 0,
                'density_nett' => $volTotal > 0 ? $totalNet / $volTotal : 0,
            ]);

            return $booking;
        });

        return redirect()->route('admin.bookings')->with('success', 'Order #' . $booking->booking_code . ' berhasil dibuat.');
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
    // public function update(Request $request, int $id)
    // {
    //     $booking = Booking::findOrFail($id);

    //     // 1. HAPUS 'status' dari validasi karena memang tidak ada di form
    //     $request->validate([
    //         'customer_id'    => 'required|exists:customers,id',
    //         'product_name'   => 'required|string',
    //         'quantity'       => 'required|numeric',
    //         'dimension_pack' => 'required|string',
    //         'vol_total'      => 'required|numeric',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         // 2. Update data UTAMA tanpa menyentuh status
    //         // Jangan masukkan 'status' => $request->status di sini
    //         $booking->update([
    //             'customer_id'    => $request->customer_id,
    //             'total_price'    => $request->total_price ?? $booking->total_price,
    //             'payment_status' => $request->payment_status ?? $booking->payment_status,
    //         ]);

    //         // 3. Update data PRODUK
    //         BookingProduct::updateOrCreate(
    //             ['booking_id' => $booking->id],
    //             [
    //                 'product_name'         => $request->product_name,
    //                 'product_type'         => $request->product_type,
    //                 'quantity'             => $request->quantity,
    //                 'unit'                 => $request->unit,
    //                 'dmin'                 => $request->dmin,
    //                 'dmax'                 => $request->dmax,
    //                 'dimension_pack'       => $request->dimension_pack,
    //                 'vol_per_pcs'          => $request->vol_per_pcs,
    //                 'vol_total'            => $request->vol_total,
    //                 'net_weight_pcs'       => $request->net_weight_pcs,
    //                 'total_net_weight'     => $request->total_net_weight,
    //                 'gross_weight_per_pcs' => $request->gross_weight_per_pcs,
    //                 'total_gross_weight'   => $request->total_gross_weight,
    //                 'expect_temp'          => $request->expect_temp,
    //                 'density_gross'        => $request->density_gross,
    //                 'density_nett'         => $request->density_nett,
    //             ]
    //         );

    //         DB::commit();
    //         return redirect()->route('admin.bookings')->with('success', 'Order #' . $booking->booking_code . ' berhasil diperbarui.');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage());
    //     }
    // }

    public function update(Request $request, int $id)
    {
        $booking = Booking::with('products')->where('booking_type', 'regular')->findOrFail($id);
        if ($booking->arrival_time || in_array($booking->status, ['processing', 'completed'], true)) {
            return back()->with('error', 'Order yang sudah check-in/masuk produksi tidak dapat mengubah quantity atau data produk.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'dmin' => 'required|numeric|min:0',
            'dmax' => 'nullable|numeric|gte:dmin',
            'dimension_pack' => 'required|string|max:255',
            'vol_per_pcs' => 'nullable|numeric|min:0',
            'net_weight_pcs' => 'nullable|numeric|min:0',
            'gross_weight_per_pcs' => 'required|numeric|min:0',
            'expect_temp' => 'nullable|string|max:100',
            'created_at' => 'required|date',
            'payment_status' => 'nullable|in:paid,unpaid',
            'total_price' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($booking, $validated) {
                $customer = Customer::findOrFail($validated['customer_id']);
                $newDate = Carbon::parse($validated['created_at']);
                $oldPrefix = $booking->created_at?->format('ymd');
                $newPrefix = $newDate->format('ymd');
                $code = $oldPrefix === $newPrefix ? $booking->booking_code : $this->nextBookingCodeForDate($newDate);

                $quantity = (int) $validated['quantity'];
                $volPerPcs = (float) ($validated['vol_per_pcs'] ?? 0);
                $netPerPcs = (float) ($validated['net_weight_pcs'] ?? 0);
                $grossPerPcs = (float) $validated['gross_weight_per_pcs'];
                $volTotal = $volPerPcs * $quantity;
                $totalNet = $netPerPcs * $quantity;
                $totalGross = $grossPerPcs * $quantity;

                $booking->update([
                    'customer_id' => $customer->id,
                    'user_id' => $customer->user_id,
                    'booking_code' => $code,
                    'created_at' => $newDate,
                    'total_price' => $validated['total_price'] ?? $booking->total_price,
                    'payment_status' => $validated['payment_status'] ?? $booking->payment_status,
                ]);

                BookingProduct::updateOrCreate(['booking_id' => $booking->id], [
                    'product_name' => $validated['product_name'],
                    'product_type' => $validated['product_type'],
                    'quantity' => $quantity,
                    'unit' => $validated['unit'],
                    'dmin' => $validated['dmin'],
                    'dmax' => $validated['dmax'] ?? null,
                    'dimension_pack' => $validated['dimension_pack'],
                    'vol_per_pcs' => $volPerPcs,
                    'vol_total' => $volTotal,
                    'net_weight_pcs' => $netPerPcs,
                    'total_net_weight' => $totalNet,
                    'gross_weight_per_pcs' => $grossPerPcs,
                    'total_gross_weight' => $totalGross,
                    'expect_temp' => $validated['expect_temp'] ?? null,
                    'density_gross' => $volTotal > 0 ? $totalGross / $volTotal : 0,
                    'density_nett' => $volTotal > 0 ? $totalNet / $volTotal : 0,
                ]);
            });

            return redirect()->route('admin.bookings')->with('success', 'Order berhasil diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui: ' . $e->getMessage())->withInput();
        }
    }

    public function generateCode()
    {
        return response()->json(['code' => $this->nextBookingCodeForDate(now())]);
    }
    public function storePlacement(Request $request, int $bookingId)
    {
        $validated = $request->validate([
            'pic_warehouse' => 'required|string|max:255',
            'porter_name' => 'required|string|max:255',
            'total_qty' => 'required|integer|min:1',
            'per_pallet' => 'required|integer|min:1',
            'lines' => 'required|array|min:1',
            'lines.*' => 'required|string|max:50',
            'petaks' => 'required|array|min:1',
            'petaks.*' => 'required|integer|min:1',
            'pallet_qty' => 'required|array|min:1',
            'pallet_qty.*' => 'required|integer|min:1',
            'product_names' => 'required|array|min:1',
            'product_names.*' => 'required|string|max:255',
        ]);

        $count = count($validated['lines']);
        if ($count !== count($validated['petaks']) || $count !== count($validated['pallet_qty']) || $count !== count($validated['product_names'])) {
            return back()->with('error', 'Data placement tidak lengkap. Silakan ulangi check-in.');
        }

        $locations = [];
        foreach ($validated['lines'] as $i => $line) {
            $key = trim((string) $line) . ':' . (int) $validated['petaks'][$i];
            if (isset($locations[$key])) {
                return back()->with('error', "Lokasi {$key} dipilih lebih dari sekali.");
            }
            $locations[$key] = true;
        }

        $booking = Booking::with('products')->findOrFail($bookingId);
        $actualTotal = (int) $booking->products->sum('quantity');
        if ($actualTotal <= 0 || (int) $validated['total_qty'] !== $actualTotal) {
            return back()->with('error', 'Quantity booking berubah/tidak sesuai. Muat ulang halaman lalu ulangi check-in.');
        }

        $neededPallets = (int) ceil($actualTotal / (int) $validated['per_pallet']);
        if (array_sum(array_map('intval', $validated['pallet_qty'])) !== $neededPallets) {
            return back()->with('error', "Total alokasi pallet harus {$neededPallets} pallet.");
        }

        try {
            DB::transaction(function () use ($booking, $validated, $count, $actualTotal) {
                $this->releaseBookingInventory($booking->id);
                PlacementDetail::where('booking_id', $booking->id)->delete();

                $remainingQty = $actualTotal;
                $perPallet = (int) $validated['per_pallet'];

                for ($i = 0; $i < $count; $i++) {
                    $line = trim((string) $validated['lines'][$i]);
                    $petak = (int) $validated['petaks'][$i];
                    $palletCount = (int) $validated['pallet_qty'][$i];

                    $slot = Pallet::where('line', $line)
                        ->where('slot_section', $petak)
                        ->lockForUpdate()
                        ->first();

                    if (!$slot) {
                        throw new \RuntimeException("Slot Line {$line} Petak {$petak} tidak terdaftar.");
                    }

                    if ($slot->current_booking_id && (int) $slot->current_booking_id !== (int) $booking->id) {
                        throw new \RuntimeException("Line {$line} Petak {$petak} sedang dipakai booking lain.");
                    }

                    $itemQty = min($remainingQty, $palletCount * $perPallet);
                    if ($itemQty <= 0) {
                        throw new \RuntimeException('Alokasi pallet melebihi quantity booking.');
                    }

                    $slot->update([
                        'status' => 'filled',
                        'current_booking_id' => $booking->id,
                        'filled_boxes' => $itemQty,
                    ]);

                    PalletContent::create([
                        'pallet_id' => $slot->id,
                        'booking_id' => $booking->id,
                        'product_name' => $validated['product_names'][$i],
                        'quantity' => $itemQty,
                    ]);

                    PlacementDetail::create([
                        'booking_id' => $booking->id,
                        'sequence' => $i + 1,
                        'quantity' => $itemQty,
                        'line' => $line,
                        'slot_section' => $petak,
                        'status' => 'placed',
                    ]);

                    $remainingQty -= $itemQty;
                }

                if ($remainingQty !== 0) {
                    throw new \RuntimeException("Masih ada {$remainingQty} item yang belum dialokasikan.");
                }

                $booking->update([
                    'status' => 'approved',
                    'arrival_time' => now(),
                    'pic_warehouse' => $validated['pic_warehouse'],
                    'porter_name' => $validated['porter_name'],
                ]);
            });

            return back()->with('success', 'Check-in berhasil. Placement dan inventory warehouse sudah tersinkron.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal check-in: ' . $e->getMessage());
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
        $validated = $request->validate([
            'pallet_content_id' => 'required|string',
            'new_pallet_id' => 'nullable|exists:pallets,id',
            'is_bulk' => 'nullable|boolean',
        ]);

        $contentIds = array_values(array_filter(array_map('intval', explode(',', $validated['pallet_content_id']))));
        if (!$contentIds) {
            return back()->with('error', 'Pallet content tidak valid.');
        }

        try {
            DB::transaction(function () use ($validated, $contentIds) {
                $contents = PalletContent::whereIn('id', $contentIds)->lockForUpdate()->get();
                if ($contents->count() !== count($contentIds)) {
                    throw new \RuntimeException('Sebagian pallet content tidak ditemukan.');
                }

                $isBulk = (bool) ($validated['is_bulk'] ?? false);
                $newPallet = null;
                if (!$isBulk) {
                    if (empty($validated['new_pallet_id'])) {
                        throw new \RuntimeException('Pilih lokasi pallet tujuan.');
                    }
                    $newPallet = Pallet::whereKey($validated['new_pallet_id'])->lockForUpdate()->firstOrFail();
                    $bookingIds = $contents->pluck('booking_id')->unique();
                    if ($bookingIds->count() !== 1) {
                        throw new \RuntimeException('Relokasi hanya dapat dilakukan untuk satu booking dalam satu transaksi.');
                    }
                    $bookingId = (int) $bookingIds->first();
                    if ($newPallet->current_booking_id && (int) $newPallet->current_booking_id !== $bookingId) {
                        throw new \RuntimeException('Lokasi tujuan sedang digunakan booking lain.');
                    }
                }

                foreach ($contents as $content) {
                    $oldPallet = Pallet::whereKey($content->pallet_id)->lockForUpdate()->first();
                    if ($oldPallet) {
                        $remaining = max(0, (int) $oldPallet->filled_boxes - (int) $content->quantity);
                        $oldPallet->update([
                            'filled_boxes' => $remaining,
                            'current_booking_id' => $remaining === 0 ? null : $oldPallet->current_booking_id,
                            'status' => $remaining === 0 ? 'empty' : 'filled',
                        ]);
                    }

                    if ($isBulk) {
                        $content->delete();
                    } else {
                        $newPallet->update([
                            'filled_boxes' => (int) $newPallet->filled_boxes + (int) $content->quantity,
                            'current_booking_id' => $content->booking_id,
                            'status' => 'filled',
                        ]);
                        $content->update(['pallet_id' => $newPallet->id]);
                    }
                }
            });

            return back()->with('success', !empty($validated['is_bulk'])
                ? 'Barang sudah ditandai keluar dan lokasi warehouse dikosongkan.'
                : 'Pallet berhasil direlokasi.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses tindakan: ' . $e->getMessage());
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
            DB::transaction(function () use ($id) {
                $booking = Booking::findOrFail($id);
                $this->releaseBookingInventory($booking->id);
                $booking->delete();
            });

            return redirect()->route('admin.bookings')->with('success', 'Booking dan data terkait berhasil dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getBookingCodeByDate(Request $request)
    {
        $validated = $request->validate(['date' => 'required|date']);
        return response()->json(['code' => $this->nextBookingCodeForDate(Carbon::parse($validated['date']))]);
    }

    private function nextBookingCodeForDate($date): string
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        $prefix = $date->format('ymd');

        $lastCode = Booking::where('booking_code', 'like', $prefix . '%')
            ->orderByDesc('booking_code')
            ->value('booking_code');

        $lastSequence = $lastCode ? (int) substr($lastCode, strlen($prefix)) : 0;
        return $prefix . str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
    }

    private function releaseBookingInventory(int $bookingId): void
    {
        $contents = PalletContent::where('booking_id', $bookingId)->lockForUpdate()->get();

        foreach ($contents as $content) {
            $pallet = Pallet::whereKey($content->pallet_id)->lockForUpdate()->first();
            if (!$pallet) {
                continue;
            }

            $remaining = max(0, (int) $pallet->filled_boxes - (int) $content->quantity);
            $pallet->update([
                'filled_boxes' => $remaining,
                'current_booking_id' => $remaining === 0 ? null : $pallet->current_booking_id,
                'status' => $remaining === 0 ? 'empty' : 'filled',
            ]);
        }

        PalletContent::where('booking_id', $bookingId)->delete();
    }

}
