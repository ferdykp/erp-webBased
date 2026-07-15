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

                    {{-- GRID TANGGAL & BOOKING CODE --}}
                    <div class="grid grid-cols-1 gap-6 px-2 md:grid-cols-2 lg:gap-8">
                        {{-- INPUT TANGGAL --}}
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-700 uppercase tracking-widest ml-1">
                                Tanggal Input (Created At)
                            </label>
                            <input type="date" id="in_created_at" name="created_at"
                                value="{{ $booking->created_at ? $booking->created_at->format('Y-m-d') : date('Y-m-d') }}"
                                class="w-full px-5 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-bold focus:bg-white focus:border-blue-300 transition-all outline-none">
                        </div>

                        {{-- BOOKING CODE (READONLY DISPLAY) --}}
                        <div class="space-y-2">
                            <label
                                class="text-[10px] md:text-[11px] font-black text-slate-400 uppercase tracking-widest ml-1">
                                Booking Code
                            </label>
                            <input type="text" id="display_booking_code_input"
                                class="w-full px-4 py-3.5 bg-slate-50 border border-slate-100 rounded-2xl font-black text-blue-600 tracking-widest"
                                readonly value="{{ $booking->booking_code }}">
                            <input type="hidden" id="in_booking_code" name="booking_code"
                                value="{{ $booking->booking_code }}">
                        </div>
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
            // Pasang event listener kalkulator
            ['in_qty', 'in_length', 'in_width', 'in_height', 'in_net_pcs', 'in_gross_pcs'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', calculateSummary);
            });

            // --- LOGIC BARU: Event Listener Perubahan Tanggal ---
            const dateInput = document.getElementById('in_created_at');
            if (dateInput) {
                dateInput.addEventListener('change', function() {
                    const selectedDate = this.value;
                    if (!selectedDate) return;

                    // Panggil API internal untuk mengambil code berdasarkan tanggal
                    fetch(`{{ route('admin.bookings.get-code-by-date') }}?date=${selectedDate}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.code) {
                                // Update visual input dan value asli code
                                document.getElementById('display_booking_code_input').value = data.code;
                                document.getElementById('in_booking_code').value = data.code;
                            }
                        })
                        .catch(err => console.error('Gagal mengambil Booking Code baru:', err));
                });
            }

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
    </script>
@endsection
