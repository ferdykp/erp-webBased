<?php



namespace App\Http\Controllers;

use App\Services\SuperadminAccess;



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
use Illuminate\Support\Facades\Storage;

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



    public function allOrder(Request $request)

    {

        $view = (string) $request->query('view', 'all');

        $allowedViews = ['all', 'incoming', 'progress', 'completed'];

        if (!in_array($view, $allowedViews, true)) {

            $view = 'all';
        }



        $search = trim((string) $request->query('search', ''));

        $search = mb_substr($search, 0, 100);

        $base = Booking::query()->where('booking_type', 'regular');

        $counts = [
            'all' => (clone $base)->count(),
            'incoming' => (clone $base)->whereIn('status', ['pending', 'approved'])->whereNull('arrival_time')->whereDoesntHave('placementDetails')->count(),
            'progress' => (clone $base)->whereIn('status', ['pending', 'approved', 'processing'])->where(fn($q) => $q->whereNotNull('arrival_time')->orWhereHas('placementDetails'))->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
        ];
        $query = $base->with(['customer.contacts', 'products', 'batches', 'pallets', 'placementDetails','dispatchRecord','palletContents']);
        if ($view === 'incoming') {
            $query->whereIn('status', ['pending', 'approved'])->whereNull('arrival_time')->whereDoesntHave('placementDetails');
        } elseif ($view === 'progress') {
            $query->whereIn('status', ['pending', 'approved', 'processing'])->where(fn($q) => $q->whereNotNull('arrival_time')->orWhereHas('placementDetails'));
        } elseif ($view === 'completed') {
            $query->where('status', 'completed');
        }

        if ($search !== '') {

            $query->where(function ($q) use ($search) {

                $q->where('booking_code', 'like', "%{$search}%")

                    ->orWhereHas('customer', function ($customer) use ($search) {

                        $customer->where('company_name', 'like', "%{$search}%")

                            ->orWhereHas('contacts', function ($contact) use ($search) {

                                $contact->where('name', 'like', "%{$search}%");
                            });
                    })

                    ->orWhereHas('products', function ($product) use ($search) {

                        $product->where('product_name', 'like', "%{$search}%");
                    });
            });
        }



        $bookings = $query->latest()->paginate(10)->withQueryString();

        $porters = Porter::where('is_active', true)->orderBy('name')->get();

        $pallets = Pallet::orderBy('line')->orderBy('slot_section')->get();

        $warehousePics = WarehousePic::where('is_active', true)->orderBy('name')->get();



        return view('admin.bookings.index', compact('bookings', 'porters', 'pallets', 'warehousePics', 'counts', 'view', 'search'));
    }



    public function updateStatus(Request $request, int $id)

    {

        $validated = $request->validate([

            'status' => 'required|string|in:pending,approved,processing,completed,cancelled',

        ]);



        // This administrative endpoint must not bypass Receiving, Production,

        // QA, Dispatch, or the warehouse reconciliation workflow.

        $allowed = ['pending' => ['cancelled']];

        DB::transaction(function () use ($id, $validated, $allowed) {

            $booking = Booking::whereKey($id)->lockForUpdate()->firstOrFail();

            $target = $validated['status'];

            if ($target === $booking->status) {

                return; // Safe repeat request; do not perform inventory side-effects.

            }

            if ((!in_array($target, $allowed[$booking->status] ?? [], true)) && !SuperadminAccess::overriding()) {

                throw \Illuminate\Validation\ValidationException::withMessages([

                    'status' => 'Use the authorized receiving, approval, production, quality or dispatch workflow to change this status.',

                ]);
            }

            if (($booking->arrival_time || $booking->batches()->exists()

                || PalletContent::where('booking_id', $booking->id)->exists()

                || PlacementDetail::where('booking_id', $booking->id)->exists()) && !SuperadminAccess::overriding()) {

                throw \Illuminate\Validation\ValidationException::withMessages([

                    'status' => 'Cancellation requires an authorized inventory and production reconciliation.',

                ]);
            }

            $booking->update(['status' => $target]);
        }, 3);

        return back()->with('success', 'Order status updated.');
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



    public function checkIn(Request $request)

    {

        $booking = Booking::where('booking_code', $request->booking_code)->firstOrFail();

        return $this->storePlacement($request, $booking->id);
    }





    public function statusPage(string $status)

    {

        abort_unless(in_array($status, ['pending', 'approved', 'processing', 'completed', 'cancelled'], true), 404);

        $view = match ($status) {

            'pending' => 'incoming',

            'approved', 'processing' => 'progress',

            'completed' => 'completed',

            default => 'all',
        };



        return redirect()->route('admin.bookings', ['view' => $view]);
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
        try {
            DB::transaction(function () use ($id) {
                $pallet = Pallet::whereKey($id)->lockForUpdate()->firstOrFail();
                if (SuperadminAccess::overriding()) { app(\App\Services\AdminRecordHistory::class)->delete($pallet); return; }
                $this->assertLocationDeletable($pallet);
                $pallet->delete();
            }, 3);
            return back()->with('success', 'Warehouse location deleted.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->with('error', $e->validator->errors()->first());
        }
    }

    public function deleteLine(Request $request, string $line)
    {
        DB::transaction(function () use ($line) {
            $slots = Pallet::where('line', $line)->orderBy('id')->lockForUpdate()->get();
            abort_if($slots->isEmpty(), 404);
            if (SuperadminAccess::overriding()) { foreach ($slots as $slot) app(\App\Services\AdminRecordHistory::class)->delete($slot); return; }
            foreach ($slots as $slot) $this->assertLocationDeletable($slot);
            foreach ($slots as $slot) $slot->delete();
        }, 3);
        return back()->with('success', 'Empty warehouse line deleted.');
    }

    private function assertLocationDeletable(Pallet $pallet): void
    {
        if (
            (int) $pallet->filled_boxes !== 0 || $pallet->status !== 'empty' || $pallet->current_booking_id
            || $pallet->contents()->exists()
            || PlacementDetail::where('line', $pallet->line)->where('slot_section', $pallet->slot_section)->where('status','placed')->exists()
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages(['location' => 'This location contains goods or is referenced by operational history and cannot be deleted.']);
        }
    }

    public function create()

    {

        $customers = Customer::orderBy('company_name', 'asc')->get();

        return view('admin.bookings.create', compact('customers'));
    }



    public function store(Request $request)

    {

        $validated = $request->validate([

            'product_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',

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



        if ($request->hasFile('product_photo')) {
            $photoPath = $request->file('product_photo')->store('products', 'public');
            try {
                $booking->products()->firstOrFail()->update(['photo_path' => $photoPath]);
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($photoPath);
                throw $e;
            }
        }

        return redirect()->route('admin.bookings')->with('success', 'Order #' . $booking->booking_code . ' berhasil dibuat.');
    }



    /**

     * Menampilkan form edit untuk booking tertentu.

     */

    public function edit(int $id)

    {

        // Load booking beserta relasi produknya

        $booking = Booking::with('products')->findOrFail($id);
        abort_unless(\App\Services\WorkflowState::canEdit($booking),409,'This order is locked because its workflow has advanced.');



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
        // All decisions are made against the locked, current booking state.
        $result = DB::transaction(function () use ($request, $id) {
            $booking = Booking::query()
                ->where('booking_type', 'regular')
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(\App\Services\WorkflowState::canEdit($booking), 409, 'This order is locked because receiving has started.');

            $product = BookingProduct::query()
                ->where('booking_id', $booking->id)
                ->orderBy('id')
                ->first();

            $operationallyLocked = $booking->arrival_time !== null
                || in_array($booking->status, ['approved', 'processing', 'completed'], true)
                || $booking->batches()->exists()
                || PalletContent::where('booking_id', $booking->id)->exists()
                || PlacementDetail::where('booking_id', $booking->id)->exists();

            if ($operationallyLocked && !SuperadminAccess::overriding()) {
                // Do not silently discard attempted changes from the old edit form.
                $unchangeable = [
                    'customer_id' => $booking->customer_id,
                    'product_name' => $product?->product_name,
                    'product_type' => $product?->product_type,
                    'quantity' => $product?->quantity,
                    'unit' => $product?->unit,
                    'dmin' => $product?->dmin,
                    'dmax' => $product?->dmax,
                    'dimension_pack' => $product?->dimension_pack,
                    'vol_per_pcs' => $product?->vol_per_pcs,
                    'net_weight_pcs' => $product?->net_weight_pcs,
                    'gross_weight_per_pcs' => $product?->gross_weight_per_pcs,
                    'expect_temp' => $product?->expect_temp,
                ];

                $errors = [];

                foreach ($unchangeable as $field => $stored) {
                    if (! $request->exists($field)) {
                        continue;
                    }

                    $submitted = $request->input($field);
                    $same = (is_numeric($submitted) && is_numeric($stored))
                        ? abs((float) $submitted - (float) $stored) < 0.000001
                        : (string) ($submitted ?? '') === (string) ($stored ?? '');

                    if (! $same) {
                        $errors[$field] = 'Data ini terkunci setelah penerimaan atau proses operasional. Gunakan workflow koreksi yang berwenang.';
                    }
                }

                if (
                    $request->filled('created_at') &&
                    ! Carbon::parse($request->input('created_at'))->isSameDay($booking->created_at)
                ) {
                    $errors['created_at'] = 'Tanggal booking tidak dapat diubah setelah receiving.';
                }

                if ($errors !== []) {
                    throw \Illuminate\Validation\ValidationException::withMessages($errors);
                }

                abort_unless(in_array(auth('admin')->user()?->role, ['superadmin', 'manager'], true), 403);

                $validated = $request->validate([
                    'payment_status' => ['sometimes', 'required', 'in:paid,unpaid'],
                    'total_price' => ['sometimes', 'required', 'numeric', 'min:0'],
                ]);

                if (
                    ! array_key_exists('payment_status', $validated)
                    && ! array_key_exists('total_price', $validated)
                ) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'payment_status' => 'Tidak ada informasi administratif yang dikirim untuk diperbarui.',
                    ]);
                }

                // Financial fields must additionally be protected by the
                // appropriate role / policy at the route or controller level.
                if (array_key_exists('payment_status', $validated)) {
                    $booking->payment_status = $validated['payment_status'];
                }
                if (array_key_exists('total_price', $validated)) {
                    $booking->total_price = $validated['total_price'];
                }
                $booking->save();

                return 'administrative';
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

            $customer = Customer::findOrFail($validated['customer_id']);
            $newDate = Carbon::parse($validated['created_at']);
            $oldPrefix = $booking->created_at?->format('ymd');
            $newPrefix = $newDate->format('ymd');
            $code = $oldPrefix === $newPrefix
                ? $booking->booking_code
                : $this->nextBookingCodeForDate($newDate);

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

            return 'full';
        }, 3);

        return redirect()->route('admin.bookings')->with(
            'success',
            $result === 'administrative'
                ? 'Informasi pembayaran dan harga berhasil diperbarui. Data operasional tidak berubah.'
                : 'Order berhasil diperbarui.'
        );
    }

    public function generateCode()

    {

        return response()->json(['code' => $this->nextBookingCodeForDate(now())]);
    }

    public function storePlacement(Request $request, int $bookingId)
    {
        $staff = $request->validate([
            'pic_warehouse' => 'required|string|max:255',
            'porter_name' => 'required|string|max:255',
        ]);
        $booking = Booking::with('products')->findOrFail($bookingId);
        if ($request->has('allocations')) {
            $data = $request->validate([
                'allocations' => 'required|array|min:1',
                'allocations.*.product_id' => 'required|integer',
                'allocations.*.pallet_id' => 'required|integer',
                'allocations.*.quantity' => 'required|integer|min:1',
                'physical_verified' => 'accepted',
            ]);
            $allocations = $data['allocations'];
        } else {
            // Preserve the existing single-product receiving form and uneven split calculation.
            $data = $request->validate([
                'total_qty' => 'required|integer|min:1',
                'per_pallet' => 'required|integer|min:1',
                'planning_mode' => 'nullable|in:quantity,manual',
                'manual_pallet_count' => 'nullable|required_if:planning_mode,manual|integer|min:1',
                'lines' => 'required|array|min:1',
                'lines.*' => 'required|string|max:50',
                'petaks' => 'required|array|min:1',
                'petaks.*' => 'required|integer|min:1',
                'pallet_qty' => 'required|array|min:1',
                'pallet_qty.*' => 'required|integer|in:1',
            ]);
            $product = $booking->products->first();
            $manual = ($data['planning_mode'] ?? 'quantity') === 'manual';
            $count = $manual ? (int) $data['manual_pallet_count'] : (int) ceil($data['total_qty'] / $data['per_pallet']);
            if (
                $booking->products->count() !== 1 || (int) $product->quantity !== (int) $data['total_qty']
                || $count > $data['total_qty'] || $count !== count($data['lines'])
                || $count !== count($data['petaks']) || $count !== count($data['pallet_qty'])
                || ($manual && (int) $data['per_pallet'] !== (int) ceil($data['total_qty'] / $count))
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages(['allocations' => 'The allocation has changed. Reload and check the ordered quantities.']);
            }
            $allocations = [];
            $remaining = (int) $product->quantity;
            foreach ($data['lines'] as $i => $line) {
                $slot = Pallet::where('line', $line)->where('slot_section', $data['petaks'][$i])->first();
                if (!$slot) throw \Illuminate\Validation\ValidationException::withMessages(['lines' => 'The selected warehouse location does not exist.']);
                $qty = $manual ? intdiv((int) $product->quantity, $count) + ($i < $product->quantity % $count ? 1 : 0)
                    : min($remaining, (int) $data['per_pallet']);
                $allocations[] = ['pallet_id' => $slot->id, 'product_id' => $product->id, 'quantity' => $qty];
                $remaining -= $qty;
            }
        }
        app(\App\Services\WarehouseInventory::class)->receive($bookingId, $allocations, $staff);
        return redirect()->route('admin.workspace.show', ['booking' => $bookingId, 'stage' => 'preparation'])
            ->with('success', 'Goods received. The order is ready for production setup.');
    }

    public function productPhoto(int $id)
    {
        $product = BookingProduct::query()->findOrFail($id);
        abort_unless($product->photo_path && Storage::disk('public')->exists($product->photo_path), 404);
        return response()->file(Storage::disk('public')->path($product->photo_path));
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
        // Endpoint lama tidak menjaga placement_details untuk gudang multi-booking.
        // Gunakan relokasi resmi pada OperationsEnhancementController.
        return back()->with('error', 'Gunakan menu Gudang → Pemindahan Barang untuk menjaga riwayat lokasi dan stok.');
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
        abort_unless(auth('admin')->user()?->role === 'superadmin',403);
        DB::transaction(function () use ($id) {
            $booking=Booking::whereKey($id)->lockForUpdate()->firstOrFail();
            if (!\App\Services\WorkflowState::canArchive($booking)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['booking'=>'Complete goods handover before moving an operational order to Trash. Active warehouse stock must remain visible.']);
            }
            $booking->delete();
        },3);
        return redirect()->route('admin.bookings')->with('success','Order moved to Trash. Its history and documents are retained.');
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



        $lastCode = Booking::withTrashed()->where('booking_code', 'like', $prefix . '%')

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
