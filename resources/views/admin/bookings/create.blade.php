@extends('admin.layout.app')

@section('title', 'Make Order')

@section('content')
    <div class="w-full space-y-6">
        <div class="flex flex-col gap-2 mb-4">
            <h2 class="text-3xl font-black tracking-tight text-gray-900">
                Admin <span class="text-blue-600">Manual Booking</span>
            </h2>
            <p class="text-sm font-medium text-gray-400">Silakan isi detail produk untuk kalkulasi otomatis.</p>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">
            <div class="p-8">
                <form id="mainBookingForm" class="space-y-8">
                    @csrf
                    {{-- SELECT CUSTOMER --}}
                    <div class="p-6 space-y-3 border border-blue-100 bg-blue-50/50 rounded-3xl">
                        <label class="text-[11px] font-black text-blue-600 uppercase tracking-widest ml-1">Customer
                            Owner</label>
                        <select id="in_customer_id"
                            class="w-full px-4 py-3.5 bg-white border border-blue-100 rounded-2xl font-bold text-gray-700">
                            <option value="" disabled selected>Pilih Customer...</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->contacts->first()->name ?? 'No Name' }}
                                    ({{ $customer->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Generated Booking
                            Code</label>
                        <input type="text" id="display_booking_code_input"
                            class="w-full px-4 py-3.5 bg-white border border-gray-100 rounded-2xl font-black text-blue-600 tracking-widest"
                            readonly value="Generating...">
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Name</label>
                            <input type="text" id="in_product_name"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Type</label>
                            <input type="text" id="in_product_type"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Nett Weight /
                                Pcs (Kg)</label>
                            <input type="number" step="any" id="in_net_pcs"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Gross Weight
                                / Pcs (Kg)</label>
                            <input type="number" step="any" id="in_gross_pcs"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Dose Range
                                (kGy)</label>
                            <div class="flex gap-4">
                                <input type="number" step="any" id="in_dmin" placeholder="Min"
                                    class="w-1/2 px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                                <input type="number" step="any" id="in_dmax" placeholder="Max"
                                    class="w-1/2 px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Dimension (P
                                x L x T) cm</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" id="in_length" placeholder="P"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold">
                                <input type="number" id="in_width" placeholder="L"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold">
                                <input type="number" id="in_height" placeholder="T"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="space-y-2">
                            <label
                                class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Quantity</label>
                            <input type="number" id="in_qty"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Unit</label>
                            <select id="in_unit"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                                <option value="box">BOX / DUS</option>
                                <option value="sack">SACK</option>
                                <option value="drum">DRUM</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Temp
                                Req.</label>
                            <input type="text" id="in_temp" placeholder="None"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold">
                        </div>
                    </div>

                    {{-- LIVE CALCULATION RESULTS (6 KOLOM) --}}
                    <div class="p-6 space-y-4 border border-gray-100 bg-gray-50 rounded-3xl">
                        <h3 class="text-[11px] font-black text-blue-600 uppercase tracking-widest ml-1">Live Calculation
                            Results</h3>
                        <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Volume/Pcs (cm³)</span>
                                <p id="res_vol_pcs" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Total Volume (cm³)</span>
                                <p id="res_vol_total" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Nett Total (kg)</span>
                                <p id="res_net_total" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Total Gross (kg)</span>
                                <p id="res_gross_total" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Density (Nett)</span>
                                <p id="res_density_nett" class="text-lg font-black text-blue-600">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] font-bold text-gray-400 uppercase">Density (Gross)</span>
                                <p id="res_density_gross" class="text-lg font-black text-blue-600">0</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-8 border-t">
                        <a href="{{ route('admin.bookings') }}" class="text-sm font-bold text-gray-400">Batal</a>
                        <button type="button" onclick="openVerifyModal()"
                            class="px-10 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-xl rounded-2xl hover:bg-blue-700">
                            Review Order <i class="ml-2 fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('admin.bookings.partials.order-detail')

    <script>
        // Fungsi pembantu agar angka rapi (menghapus 0 tidak perlu di belakang koma)
        function formatNum(num, decimals = 2) {
            if (num === null || num === undefined || isNaN(num)) return "0";

            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals
            }).format(num);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // generateBookingCode();
            fetchBookingCode();


            // Pasang event listener ke semua input agar kalkulasi live
            ['in_qty', 'in_length', 'in_width', 'in_height', 'in_net_pcs', 'in_gross_pcs'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', calculateSummary);
            });
        });

        function fetchBookingCode() {
            fetch('/admin/bookings/generate-code')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('display_booking_code_input').value = data.code;
                })
                .catch(err => {
                    console.error('Failed generate code:', err);
                    document.getElementById('display_booking_code_input').value = 'ERROR';
                });
        }

        // function generateBookingCode() {
        //     const now = new Date();
        //     const year = now.getFullYear().toString().slice(-2);
        //     const month = (now.getMonth() + 1).toString().padStart(2, '0');
        //     const day = now.getDate().toString().padStart(2, '0');
        //     const randomSeq = Math.floor(Math.random() * 900 + 100);
        //     const code = `${year}${month}${day}${randomSeq}`;

        //     document.getElementById('display_booking_code_input').value = code;
        // }

        function calculateSummary() {
            const qty = parseFloat(document.getElementById('in_qty').value) || 0;
            const l = parseFloat(document.getElementById('in_length').value) || 0;
            const w = parseFloat(document.getElementById('in_width').value) || 0;
            const h = parseFloat(document.getElementById('in_height').value) || 0;
            const net_pcs = parseFloat(document.getElementById('in_net_pcs').value) || 0;
            const gross_pcs = parseFloat(document.getElementById('in_gross_pcs').value) || 0;

            // Kalkulasi
            const vol_pcs = l * w * h;
            const vol_total = vol_pcs * qty;
            const net_total = net_pcs * qty;
            const gross_total = gross_pcs * qty;
            const dNett = vol_total > 0 ? (net_total / vol_total) : 0;
            const dGross = vol_total > 0 ? (gross_total / vol_total) : 0;

            // Update UI Form Utama (Live Results)
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
                // Set ringkasan teks di modal
                document.getElementById('check_product_name').innerText = prodName.value;
                document.getElementById('check_qty').innerText = formatNum(qtyVal, 0);
                document.getElementById('check_unit').innerText = document.getElementById('in_unit').value;
                document.getElementById('check_dmin').innerText = formatNum(document.getElementById('in_dmin').value, 2);
                document.getElementById('check_dmax').innerText = formatNum(document.getElementById('in_dmax').value, 2);
                document.getElementById('check_dimension').innerText =
                    `${document.getElementById('in_length').value}x${document.getElementById('in_width').value}x${document.getElementById('in_height').value} cm`;

                // Set input readonly di modal
                document.getElementById('mod_vol_pcs').value = formatNum(calc.vol_pcs, 2);
                document.getElementById('mod_vol_total').value = formatNum(calc.vol_total, 2);
                document.getElementById('mod_net_pcs').value = formatNum(document.getElementById('in_net_pcs').value, 2);
                document.getElementById('mod_net_total').value = formatNum(calc.net_total, 2);
                document.getElementById('mod_density_nett').value = formatNum(calc.dNett, 6);
                document.getElementById('mod_density_gross').value = formatNum(calc.dGross, 6);

                // Set hidden inputs untuk database
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

                // Hitung harga final di modal
                if (typeof calculatePrice === "function") calculatePrice();

                // Tampilkan modal
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
