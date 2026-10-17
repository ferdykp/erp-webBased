@extends('admin.layout.app')

@section('title', 'Edit Booking - ' . $booking->booking_code)

@section('content')
    @php
        $product = $booking->products->first();

        $isLocked =
            $booking->arrival_time !== null ||
            in_array($booking->status, ['approved', 'processing', 'completed', 'cancelled'], true) ||
            $booking->batches()->exists() ||
            \App\Models\PlacementDetail::where('booking_id', $booking->id)->exists() ||
            \App\Models\PalletContent::where('booking_id', $booking->id)->exists();

        $isLocked = $isLocked;

        $dimensions = preg_split('/\s*[xX×]\s*/', (string) ($product?->dimension_pack ?? ''));

        $inputClass =
            'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10';

        $labelClass = 'mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500';
    @endphp

    <div class="max-w-6xl pb-12 mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.bookings') }}"
                    class="flex items-center justify-center transition bg-white border h-11 w-11 rounded-xl border-slate-200 text-slate-500 hover:border-blue-200 hover:text-blue-600">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>

                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                        Edit Booking
                    </h1>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $booking->booking_code }}
                        <span class="mx-1">·</span>
                        {{ ucfirst($booking->status) }}
                    </p>
                </div>
            </div>

            @if ($isLocked)
                <span
                    class="inline-flex items-center self-start gap-2 px-4 py-2 text-xs font-semibold border rounded-full border-amber-200 bg-amber-50 text-amber-800">
                    <i class="fa-solid fa-lock"></i>
                    Operational Data Locked
                </span>
            @else
                <span
                    class="inline-flex items-center self-start gap-2 px-4 py-2 text-xs font-semibold border rounded-full border-emerald-200 bg-emerald-50 text-emerald-800">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Editable
                </span>
            @endif
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="px-5 py-4 text-sm font-medium border rounded-xl border-emerald-200 bg-emerald-50 text-emerald-800">
                <i class="mr-2 fa-solid fa-circle-check"></i>
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="px-5 py-4 text-sm font-medium text-red-800 border border-red-200 rounded-xl bg-red-50">
                <i class="mr-2 fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="px-5 py-4 border border-red-200 rounded-xl bg-red-50">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5 text-red-600"></i>

                    <div>
                        <p class="text-sm font-bold text-red-800">
                            Data belum dapat disimpan
                        </p>

                        <ul class="mt-2 space-y-1 text-sm text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Operational Lock Information --}}
        @if ($isLocked)
            <div class="p-5 border rounded-2xl border-amber-200 bg-amber-50">
                <div class="flex items-start gap-3">
                    <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-amber-100 text-amber-700">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>

                    <div>
                        <h2 class="text-sm font-bold text-amber-950">
                            Booking sudah memasuki proses operasional
                        </h2>

                        <p class="mt-1 text-sm leading-relaxed text-amber-800">
                            Data customer, produk, quantity, dimensi, berat,
                            dosis, dan tanggal booking dikunci untuk menjaga
                            konsistensi Warehouse, Production, dan Quality.
                            Informasi pembayaran tetap dapat diperbarui oleh
                            pengguna yang memiliki izin.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- =========================================================
             LOCKED BOOKING: READ-ONLY DETAILS + FINANCIAL FORM
        ========================================================== --}}
        @if ($isLocked)

            <div class="overflow-hidden bg-white border rounded-2xl border-slate-200">

                <div class="px-6 py-5 border-b border-slate-100">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Booking Information
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Informasi operasional tidak dapat diubah dari halaman ini.
                            </p>
                        </div>

                        <i class="fa-solid fa-lock text-slate-400"></i>
                    </div>
                </div>

                <div class="grid gap-5 p-6 sm:grid-cols-2 lg:grid-cols-3">

                    <div>
                        <p class="{{ $labelClass }}">Customer</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $booking->customer?->company_name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Booking Date</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $booking->created_at?->format('d M Y') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Arrival Date</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $booking->arrival_time ? \Carbon\Carbon::parse($booking->arrival_time)->format('d M Y H:i') : 'Not received' }}
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Product</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $product?->product_name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Product Type</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $product?->product_type ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Quantity</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ number_format((int) ($product?->quantity ?? 0)) }}
                            {{ $product?->unit ?? '' }}
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Dose Range</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $product?->dmin ?? '-' }}
                            –
                            {{ $product?->dmax ?? '-' }}
                            kGy
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Dimensions</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $product?->dimension_pack ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="{{ $labelClass }}">Gross Weight / Pcs</p>
                        <p class="text-sm font-semibold text-slate-900">
                            {{ $product?->gross_weight_per_pcs ?? '-' }} kg
                        </p>
                    </div>

                </div>
            </div>

            {{-- Financial Form --}}
            <form method="POST" action="{{ route('admin.bookings.update', $booking->id) }}" id="financialForm"
                class="overflow-hidden bg-white border rounded-2xl border-slate-200">

                @csrf
                @method('PUT')

                <div class="px-6 py-5 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex items-center justify-center w-10 h-10 text-blue-600 rounded-xl bg-blue-50">
                            <i class="fa-solid fa-wallet"></i>
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Financial Information
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Perbarui harga dan status pembayaran.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 p-6 sm:grid-cols-2">

                    <div>
                        <label for="payment_status" class="{{ $labelClass }}">
                            Payment Status
                        </label>

                        <select id="payment_status" name="payment_status" required class="{{ $inputClass }}">

                            <option value="unpaid" @selected(old('payment_status', $booking->payment_status) === 'unpaid')>
                                Unpaid
                            </option>

                            <option value="paid" @selected(old('payment_status', $booking->payment_status) === 'paid')>
                                Paid
                            </option>

                        </select>

                        @error('payment_status')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="total_price" class="{{ $labelClass }}">
                            Total Price (IDR)
                        </label>

                        <input type="number" id="total_price" name="total_price" min="0" step="0.01"
                            value="{{ old('total_price', $booking->total_price ?? 0) }}" required
                            class="{{ $inputClass }}">

                        @error('total_price')
                            <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <div
                    class="flex flex-col-reverse justify-between gap-3 px-6 py-5 border-t border-slate-100 sm:flex-row sm:items-center">

                    <a href="{{ route('admin.bookings') }}"
                        class="px-5 py-3 text-sm font-semibold text-center transition border rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50">
                        Cancel
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-3 text-sm font-bold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Financial Changes
                    </button>

                </div>
            </form>
        @else
            {{-- =========================================================
                 UNLOCKED BOOKING: FULL EDIT FORM
            ========================================================== --}}
            <form method="POST" action="{{ route('admin.bookings.update', $booking->id) }}" id="bookingEditForm"
                class="space-y-6">

                @csrf
                @method('PUT')

                {{-- Customer & Booking --}}
                <section class="overflow-hidden bg-white border rounded-2xl border-slate-200">

                    <div class="px-6 py-5 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900">
                            <i class="mr-2 text-blue-600 fa-solid fa-building"></i>
                            General Information
                        </h2>
                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">

                        <div>
                            <label for="customer_id" class="{{ $labelClass }}">
                                Customer
                            </label>

                            <select name="customer_id" id="customer_id" required class="{{ $inputClass }}">

                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected((string) old('customer_id', $booking->customer_id) === (string) $customer->id)>
                                        {{ $customer->company_name ?? $customer->email }}
                                    </option>
                                @endforeach

                            </select>
                        </div>

                        <div>
                            <label for="created_at" class="{{ $labelClass }}">
                                Booking Date
                            </label>

                            <input type="date" name="created_at" id="created_at"
                                value="{{ old('created_at', $booking->created_at?->format('Y-m-d')) }}" required
                                class="{{ $inputClass }}">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">
                                Booking Code
                            </label>

                            <div
                                class="px-4 py-3 text-sm font-bold tracking-wide text-blue-600 border rounded-xl border-slate-200 bg-slate-50">
                                {{ $booking->booking_code }}
                            </div>

                            <p class="mt-2 text-xs text-slate-400">
                                Booking code dikelola otomatis oleh sistem.
                            </p>
                        </div>

                    </div>
                </section>

                {{-- Product --}}
                <section class="overflow-hidden bg-white border rounded-2xl border-slate-200">

                    <div class="px-6 py-5 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900">
                            <i class="mr-2 text-blue-600 fa-solid fa-box"></i>
                            Product Information
                        </h2>
                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">

                        <div>
                            <label for="product_name" class="{{ $labelClass }}">
                                Product Name
                            </label>

                            <input type="text" name="product_name" id="product_name"
                                value="{{ old('product_name', $product?->product_name) }}" required maxlength="255"
                                class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="product_type" class="{{ $labelClass }}">
                                Product Type
                            </label>

                            <input type="text" name="product_type" id="product_type"
                                value="{{ old('product_type', $product?->product_type) }}" required maxlength="255"
                                class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="quantity" class="{{ $labelClass }}">
                                Quantity
                            </label>

                            <input type="number" name="quantity" id="quantity" min="1" step="1"
                                value="{{ old('quantity', $product?->quantity) }}" required class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="unit" class="{{ $labelClass }}">
                                Unit
                            </label>

                            @php
                                $selectedUnit = old('unit', $product?->unit ?? 'box');
                            @endphp

                            <select name="unit" id="unit" required class="{{ $inputClass }}">

                                @if (!in_array($selectedUnit, ['box', 'sack', 'drum'], true))
                                    <option value="{{ $selectedUnit }}" selected>
                                        {{ strtoupper($selectedUnit) }}
                                    </option>
                                @endif

                                <option value="box" @selected($selectedUnit === 'box')>
                                    BOX / DUS
                                </option>

                                <option value="sack" @selected($selectedUnit === 'sack')>
                                    SACK
                                </option>

                                <option value="drum" @selected($selectedUnit === 'drum')>
                                    DRUM
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="dmin" class="{{ $labelClass }}">
                                Minimum Dose (kGy)
                            </label>

                            <input type="number" name="dmin" id="dmin" min="0" step="any"
                                value="{{ old('dmin', $product?->dmin) }}" required class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="dmax" class="{{ $labelClass }}">
                                Maximum Dose (kGy)
                            </label>

                            <input type="number" name="dmax" id="dmax" min="0" step="any"
                                value="{{ old('dmax', $product?->dmax) }}" class="{{ $inputClass }}">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $labelClass }}">
                                Dimensions (P × L × T) cm
                            </label>

                            <div class="grid grid-cols-3 gap-3">

                                <input type="number" id="dimension_length" min="0" step="any"
                                    placeholder="Panjang"
                                    value="{{ old('dimension_length', trim($dimensions[0] ?? '')) }}" required
                                    class="{{ $inputClass }}">

                                <input type="number" id="dimension_width" min="0" step="any"
                                    placeholder="Lebar" value="{{ old('dimension_width', trim($dimensions[1] ?? '')) }}"
                                    required class="{{ $inputClass }}">

                                <input type="number" id="dimension_height" min="0" step="any"
                                    placeholder="Tinggi"
                                    value="{{ old('dimension_height', trim($dimensions[2] ?? '')) }}" required
                                    class="{{ $inputClass }}">
                            </div>

                            <input type="hidden" name="dimension_pack" id="dimension_pack"
                                value="{{ old('dimension_pack', $product?->dimension_pack) }}">
                        </div>

                        <div>
                            <label for="net_weight_pcs" class="{{ $labelClass }}">
                                Nett Weight / Pcs (kg)
                            </label>

                            <input type="number" name="net_weight_pcs" id="net_weight_pcs" min="0"
                                step="any" value="{{ old('net_weight_pcs', $product?->net_weight_pcs) }}"
                                class="{{ $inputClass }}">
                        </div>

                        <div>
                            <label for="gross_weight_per_pcs" class="{{ $labelClass }}">
                                Gross Weight / Pcs (kg)
                            </label>

                            <input type="number" name="gross_weight_per_pcs" id="gross_weight_per_pcs" min="0"
                                step="any" value="{{ old('gross_weight_per_pcs', $product?->gross_weight_per_pcs) }}"
                                required class="{{ $inputClass }}">
                        </div>

                        <div class="sm:col-span-2">
                            <label for="expect_temp" class="{{ $labelClass }}">
                                Temperature Requirement
                            </label>

                            <input type="text" name="expect_temp" id="expect_temp"
                                value="{{ old('expect_temp', $product?->expect_temp) }}" placeholder="Optional"
                                maxlength="100" class="{{ $inputClass }}">
                        </div>

                    </div>
                </section>

                {{-- Calculated Summary --}}
                <section class="p-6 border rounded-2xl border-slate-200 bg-slate-50">

                    <div class="flex items-center gap-2 mb-5">
                        <i class="text-blue-600 fa-solid fa-calculator"></i>

                        <h2 class="text-sm font-bold text-slate-900">
                            Calculated Results
                        </h2>
                    </div>

                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">

                        @foreach ([['id' => 'result_vol_pcs', 'label' => 'Volume / Pcs'], ['id' => 'result_vol_total', 'label' => 'Total Volume'], ['id' => 'result_net_total', 'label' => 'Total Nett'], ['id' => 'result_gross_total', 'label' => 'Total Gross'], ['id' => 'result_density_nett', 'label' => 'Density Nett'], ['id' => 'result_density_gross', 'label' => 'Density Gross']] as $metric)
                            <div class="p-4 bg-white border rounded-xl border-slate-200">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                    {{ $metric['label'] }}
                                </p>

                                <p id="{{ $metric['id'] }}" class="mt-2 text-lg font-bold truncate text-slate-900">
                                    0
                                </p>
                            </div>
                        @endforeach

                    </div>

                </section>

                {{-- Financial --}}
                <section class="overflow-hidden bg-white border rounded-2xl border-slate-200">

                    <div class="px-6 py-5 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900">
                            <i class="mr-2 text-blue-600 fa-solid fa-wallet"></i>
                            Financial Information
                        </h2>
                    </div>

                    <div class="grid gap-5 p-6 sm:grid-cols-2">

                        <div>
                            <label for="payment_status" class="{{ $labelClass }}">
                                Payment Status
                            </label>

                            <select name="payment_status" id="payment_status" class="{{ $inputClass }}">

                                <option value="unpaid" @selected(old('payment_status', $booking->payment_status) === 'unpaid')>
                                    Unpaid
                                </option>

                                <option value="paid" @selected(old('payment_status', $booking->payment_status) === 'paid')>
                                    Paid
                                </option>

                            </select>
                        </div>

                        <div>
                            <label for="total_price" class="{{ $labelClass }}">
                                Total Price (IDR)
                            </label>

                            <input type="number" name="total_price" id="total_price" min="0" step="0.01"
                                value="{{ old('total_price', $booking->total_price ?? 0) }}"
                                class="{{ $inputClass }}">
                        </div>

                    </div>
                </section>

                {{-- Actions --}}
                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">

                    <a href="{{ route('admin.bookings') }}"
                        class="px-6 py-3 text-sm font-semibold text-center bg-white border rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50">
                        Cancel
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 py-3 text-sm font-bold text-white transition bg-blue-600 rounded-xl px-7 hover:bg-blue-700">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save Booking Changes
                    </button>

                </div>

            </form>

        @endif

    </div>

    @if (!$isLocked)
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const form = document.getElementById("bookingEditForm");

                if (!form) return;

                const field = (id) => document.getElementById(id);

                const number = (id) => {
                    const value = Number(field(id)?.value ?? 0);
                    return Number.isFinite(value) ? value : 0;
                };

                const format = (value, decimals = 2) => {
                    return Number(value || 0).toLocaleString("en-US", {
                        maximumFractionDigits: decimals,
                    });
                };

                const updateSummary = () => {
                    const quantity = number("quantity");

                    const length = number("dimension_length");
                    const width = number("dimension_width");
                    const height = number("dimension_height");

                    const nett = number("net_weight_pcs");
                    const gross = number("gross_weight_per_pcs");

                    const volume = length * width * height;
                    const totalVolume = volume * quantity;

                    const totalNett = nett * quantity;
                    const totalGross = gross * quantity;

                    const densityNett = totalVolume > 0 ?
                        totalNett / totalVolume :
                        0;

                    const densityGross = totalVolume > 0 ?
                        totalGross / totalVolume :
                        0;

                    const results = {
                        result_vol_pcs: volume,
                        result_vol_total: totalVolume,
                        result_net_total: totalNett,
                        result_gross_total: totalGross,
                        result_density_nett: densityNett,
                        result_density_gross: densityGross,
                    };

                    for (const [id, value] of Object.entries(results)) {
                        const element = field(id);

                        if (element) {
                            element.textContent = format(
                                value,
                                id.includes("density") ? 6 : 2
                            );
                        }
                    }

                    const dimensionInput = field("dimension_pack");

                    if (dimensionInput) {
                        dimensionInput.value = [
                            field("dimension_length")?.value || "0",
                            field("dimension_width")?.value || "0",
                            field("dimension_height")?.value || "0",
                        ].join("x");
                    }
                };

                [
                    "quantity",
                    "dimension_length",
                    "dimension_width",
                    "dimension_height",
                    "net_weight_pcs",
                    "gross_weight_per_pcs",
                ].forEach((id) => {
                    field(id)?.addEventListener("input", updateSummary);
                });

                updateSummary();

                form.addEventListener("submit", function(event) {
                    updateSummary();

                    const dmin = number("dmin");
                    const dmaxElement = field("dmax");

                    if (
                        dmaxElement?.value !== "" &&
                        Number(dmaxElement.value) < dmin
                    ) {
                        event.preventDefault();

                        dmaxElement.setCustomValidity(
                            "Maximum dose must not be smaller than minimum dose."
                        );

                        dmaxElement.reportValidity();
                        return;
                    }

                    dmaxElement?.setCustomValidity("");
                });

                field("dmax")?.addEventListener("input", function() {
                    this.setCustomValidity("");
                });
            });
        </script>
    @endif
@endsection
