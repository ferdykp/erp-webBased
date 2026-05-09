@extends('admin.layout.app')

@section('title', 'Edit Booking - ' . $booking->booking_code)

@section('content')
    <div class="w-full pb-10 space-y-6">
        {{-- HEADER SECTION --}}
        <div class="flex flex-col gap-4 px-2 mb-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.bookings') }}"
                    class="flex items-center justify-center w-10 h-10 transition-colors bg-white border shadow-sm border-slate-100 rounded-xl hover:bg-slate-50 group">
                    <i class="transition-colors text-slate-400 fa-solid fa-arrow-left group-hover:text-blue-600"></i>
                </a>
                <div>
                    <h2 class="text-2xl font-black tracking-tight md:text-3xl text-slate-900">
                        Edit <span class="text-blue-600">Booking</span>
                    </h2>
                    <p class="text-xs font-medium md:text-sm text-slate-400">
                        Perbarui detail pesanan: <span class="font-bold text-slate-600">{{ $booking->booking_code }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm border border-slate-100 rounded-[2rem] md:rounded-[3rem] overflow-hidden">
            <div class="p-6 md:p-10">
                <form id="mainBookingForm" class="space-y-8">
                    @csrf

                    {{-- SELECT CUSTOMER --}}
                    <div class="p-5 space-y-3 border border-blue-100 bg-blue-50/50 rounded-3xl md:p-8">
                        <label class="text-[10px] md:text-[11px] font-black text-blue-600 uppercase tracking-widest ml-1">
                            Customer Owner
                        </label>
                        <select id="in_customer_id"
                            class="w-full px-4 py-3.5 bg-white border border-blue-100 rounded-2xl font-bold text-slate-700 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all">
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    {{ $booking->customer_id == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->contacts->first()->name ?? 'No Name' }} ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BOOKING CODE (READONLY) --}}
                    <div class="px-2">
                        <label class="text-[10px] md:text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">
                            Booking Code
                        </label>
                        <input type="text" id="display_booking_code_input"
                            class="w-full px-4 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-black text-blue-600 tracking-widest mt-1"
                            readonly value="{{ $booking->booking_code }}">
                    </div>

                    {{-- PRODUCT INFO GRID --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:gap-8">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Product
                                Name</label>
                            <input type="text" id="in_product_name" value="{{ $booking->product_name }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white focus:border-blue-300 transition-all outline-none">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Product
                                Type</label>
                            <input type="text" id="in_product_type" value="{{ $booking->product_type }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white focus:border-blue-300 transition-all outline-none">
                        </div>
                    </div>

                    {{-- WEIGHT GRID --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:gap-8">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Nett
                                Weight / Pcs (Kg)</label>
                            <input type="number" step="any" id="in_net_pcs" value="{{ $booking->net_weight_pcs }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white focus:border-blue-300 transition-all outline-none">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Gross
                                Weight / Pcs (Kg)</label>
                            <input type="number" step="any" id="in_gross_pcs"
                                value="{{ $booking->gross_weight_per_pcs }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white focus:border-blue-300 transition-all outline-none">
                        </div>
                    </div>

                    {{-- DOSE & DIMENSION GRID --}}
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Dose
                                Range (kGy)</label>
                            <div class="flex gap-3">
                                <input type="number" step="any" id="in_dmin" value="{{ $booking->dmin }}"
                                    placeholder="Min"
                                    class="w-1/2 px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white transition-all outline-none">
                                <input type="number" step="any" id="in_dmax" value="{{ $booking->dmax }}"
                                    placeholder="Max"
                                    class="w-1/2 px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white transition-all outline-none">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Dimension
                                (P x L x T) cm</label>
                            @php $dims = explode('x', $booking->dimension_pack); @endphp
                            <div class="grid grid-cols-3 gap-2">
                                <input type="number" id="in_length" value="{{ $dims[0] ?? '' }}" placeholder="P"
                                    class="px-2 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-center font-bold focus:bg-white transition-all outline-none">
                                <input type="number" id="in_width" value="{{ $dims[1] ?? '' }}" placeholder="L"
                                    class="px-2 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-center font-bold focus:bg-white transition-all outline-none">
                                <input type="number" id="in_height" value="{{ $dims[2] ?? '' }}" placeholder="T"
                                    class="px-2 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl text-center font-bold focus:bg-white transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- QTY & UNIT GRID --}}
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 lg:gap-8">
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Quantity</label>
                            <input type="number" id="in_qty" value="{{ $booking->quantity }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white transition-all outline-none">
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Unit</label>
                            <select id="in_unit"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold outline-none focus:bg-white transition-all">
                                <option value="box" {{ $booking->unit == 'box' ? 'selected' : '' }}>BOX / DUS</option>
                                <option value="sack" {{ $booking->unit == 'sack' ? 'selected' : '' }}>SACK</option>
                                <option value="drum" {{ $booking->unit == 'drum' ? 'selected' : '' }}>DRUM</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">Temp
                                Req.</label>
                            <input type="text" id="in_temp" value="{{ $booking->expect_temp }}" placeholder="None"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white transition-all outline-none">
                        </div>
                    </div>

                    {{-- LIVE CALCULATION RESULTS --}}
                    <div class="p-6 space-y-6 border border-slate-100 bg-slate-50 rounded-[2rem] md:p-8">
                        <div class="flex items-center gap-2">
                            <i class="text-blue-600 fa-solid fa-calculator"></i>
                            <h3 class="text-[10px] md:text-[11px] font-black text-blue-600 uppercase tracking-widest">
                                Calculated Results</h3>
                        </div>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-6">
                            <div class="flex flex-col p-4 bg-white border shadow-sm border-slate-100 rounded-2xl">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Vol/Pcs
                                    (cm³)</span>
                                <p id="res_vol_pcs" class="text-lg font-black truncate text-slate-800">0</p>
                            </div>
                            <div class="flex flex-col p-4 bg-white border shadow-sm border-slate-100 rounded-2xl">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Total Vol
                                    (cm³)</span>
                                <p id="res_vol_total" class="text-lg font-black truncate text-slate-800">0</p>
                            </div>
                            <div class="flex flex-col p-4 bg-white border shadow-sm border-slate-100 rounded-2xl">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Nett Total
                                    (kg)</span>
                                <p id="res_net_total" class="text-lg font-black truncate text-slate-800">0</p>
                            </div>
                            <div class="flex flex-col p-4 bg-white border shadow-sm border-slate-100 rounded-2xl">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Total Gross
                                    (kg)</span>
                                <p id="res_gross_total" class="text-lg font-black truncate text-slate-800">0</p>
                            </div>
                            <div class="flex flex-col p-4 bg-white border shadow-sm border-slate-100 rounded-2xl">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Density
                                    (Nett)</span>
                                <p id="res_density_nett" class="text-lg font-black text-blue-600 truncate">0</p>
                            </div>
                            <div class="flex flex-col p-4 bg-white border shadow-sm border-slate-100 rounded-2xl">
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tight">Density
                                    (Gross)</span>
                                <p id="res_density_gross" class="text-lg font-black text-blue-600 truncate">0</p>
                            </div>
                        </div>
                    </div>

                    {{-- FOOTER BUTTONS --}}
                    <div
                        class="flex flex-col items-center justify-between gap-4 pt-8 border-t border-slate-100 sm:flex-row">
                        <a href="{{ route('admin.bookings') }}"
                            class="w-full text-sm font-bold text-center transition-colors text-slate-400 sm:w-auto hover:text-rose-500">
                            Batal
                        </a>
                        <button type="button" onclick="openVerifyModal()"
                            class="w-full px-12 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-xl sm:w-auto rounded-2xl hover:bg-blue-700 active:scale-95 shadow-blue-200">
                            Update & Review <i class="ml-2 fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.bookings.partials.order-detail', ['isEdit' => true])

    <script>
        function formatNum(num, decimals = 2) {
            if (num === null || num === undefined || isNaN(num)) return "0";
            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals
            }).format(num);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Pasang event listener
            ['in_qty', 'in_length', 'in_width', 'in_height', 'in_net_pcs', 'in_gross_pcs'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', calculateSummary);
            });

            // Trigger kalkulasi awal untuk data yang sudah ada
            calculateSummary();
        });

        function calculateSummary() {
            const qty = parseFloat(document.getElementById('in_qty').value) || 0;
            const l = parseFloat(document.getElementById('in_length').value) || 0;
            const w = parseFloat(document.getElementById('in_width').value) || 0;
            const h = parseFloat(document.getElementById('in_height').value) || 0;
            const net_pcs = parseFloat(document.getElementById('in_net_pcs').value) || 0;
            const gross_pcs = parseFloat(document.getElementById('in_gross_pcs').value) || 0;

            const vol_pcs = l * w * h;
            const vol_total = vol_pcs * qty;
            const net_total = net_pcs * qty;
            const gross_total = gross_pcs * qty;
            const dNett = vol_total > 0 ? (net_total / vol_total) : 0;
            const dGross = vol_total > 0 ? (gross_total / vol_total) : 0;

            document.getElementById('res_vol_pcs').innerText = formatNum(vol_pcs, 2);
            document.getElementById('res_vol_total').innerText = formatNum(vol_total, 2);
            document.getElementById('res_net_total').innerText = formatNum(net_total, 2);
            document.getElementById('res_gross_total').innerText = formatNum(gross_total, 2);
            document.getElementById('res_density_nett').innerText = formatNum(dNett, 6);
            document.getElementById('res_density_gross').innerText = formatNum(dGross, 6);

            return {
                vol_pcs,
                vol_total,
                net_total,
                gross_total,
                dNett,
                dGross
            };
        }

        function openVerifyModal() {
            const customer = document.getElementById('in_customer_id');
            const prodName = document.getElementById('in_product_name');
            const qtyVal = document.getElementById('in_qty').value;

            if (!customer.value || !prodName.value || !qtyVal) {
                alert("Harap isi Nama Customer, Nama Produk, dan Quantity.");
                return;
            }

            const calc = calculateSummary();

            try {
                // UI Update
                document.getElementById('check_product_name').innerText = prodName.value;
                document.getElementById('check_qty').innerText = formatNum(qtyVal, 0);
                document.getElementById('check_unit').innerText = document.getElementById('in_unit').value;
                document.getElementById('check_dmin').innerText = formatNum(document.getElementById('in_dmin').value, 2);
                document.getElementById('check_dmax').innerText = formatNum(document.getElementById('in_dmax').value, 2);
                document.getElementById('check_dimension').innerText =
                    `${document.getElementById('in_length').value}x${document.getElementById('in_width').value}x${document.getElementById('in_height').value} cm`;

                // Modal Readonly Inputs
                document.getElementById('mod_vol_pcs').value = formatNum(calc.vol_pcs, 2);
                document.getElementById('mod_vol_total').value = formatNum(calc.vol_total, 2);
                document.getElementById('mod_net_pcs').value = formatNum(document.getElementById('in_net_pcs').value, 2);
                document.getElementById('mod_net_total').value = formatNum(calc.net_total, 2);
                document.getElementById('mod_density_nett').value = formatNum(calc.dNett, 6);
                document.getElementById('mod_density_gross').value = formatNum(calc.dGross, 6);

                // Hidden Inputs for POST/PUT
                document.getElementById('final_customer_id').value = customer.value;
                document.getElementById('final_product_name').value = prodName.value;
                document.getElementById('final_product_type').value = document.getElementById('in_product_type').value;
                document.getElementById('final_qty').value = qtyVal;
                document.getElementById('final_unit').value = document.getElementById('in_unit').value;
                document.getElementById('final_dmin').value = document.getElementById('in_dmin').value || 0;
                document.getElementById('final_dmax').value = document.getElementById('in_dmax').value || 0;
                document.getElementById('final_dim_pack').value =
                    `${document.getElementById('in_length').value}x${document.getElementById('in_width').value}x${document.getElementById('in_height').value}`;
                document.getElementById('final_temp').value = document.getElementById('in_temp').value;
                document.getElementById('final_vol_per_pcs').value = calc.vol_pcs;
                document.getElementById('final_vol_total').value = calc.vol_total;
                document.getElementById('final_net_weight_pcs').value = document.getElementById('in_net_pcs').value;
                document.getElementById('final_total_net_weight').value = calc.net_total;
                document.getElementById('final_gross_pcs').value = document.getElementById('in_gross_pcs').value;
                document.getElementById('final_gross_total').value = calc.gross_total;
                document.getElementById('final_density_nett').value = calc.dNett;
                document.getElementById('final_density_gross').value = calc.dGross;

                if (typeof calculatePrice === "function") calculatePrice();

                document.getElementById('display_booking_code').innerText = document.getElementById(
                    'display_booking_code_input').value;
                const modal = document.getElementById('orderDetailModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');

            } catch (error) {
                console.error("Error modal:", error);
            }
        }
    </script>
@endsection
