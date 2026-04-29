@extends('layouts.master')

@section('content')
    <div class="w-full space-y-6">
        {{-- HEADER SECTION --}}
        <div class="flex flex-col gap-2 mb-4">
            <h2 class="text-3xl font-black tracking-tight text-gray-900">
                New <span class="text-blue-600">Booking</span>
            </h2>
            <p class="text-sm font-medium text-gray-400">
                Silahkan lengkapi data barang yang akan disterilisasi menggunakan E-Beam.
            </p>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">
            {{-- FORM HEADER --}}
            <div class="px-8 py-8 border-b border-gray-50 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-12 h-12 bg-blue-600 shadow-lg rounded-2xl shadow-blue-100">
                            <i class="text-xl text-white fa-solid fa-file-signature"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-black tracking-tight text-gray-900">Isi Detail Produk</h2>
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-0.5">Product Information
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BODY --}}
            <div class="p-8">
                {{-- Penampung ID Customer agar script tidak error --}}
                <input type="hidden" id="in_customer_id" value="{{ auth()->user()->id }}">

                {{-- Hidden input untuk booking code (digunakan script detail) --}}
                <input type="hidden" id="display_booking_code_input">

                <form action="{{ route('customer.booking.store') }}" method="POST" enctype="multipart/form-data"
                    class="space-y-8">
                    @csrf

                    {{-- Hidden inputs untuk hasil kalkulasi awal --}}
                    <input type="hidden" name="dimension_pack" id="dimension_pack">
                    <input type="hidden" name="vol_per_pcs" id="vol_per_pcs">
                    <input type="hidden" name="vol_total" id="vol_total">
                    <input type="hidden" name="total_net_weight" id="total_net_weight">
                    <input type="hidden" name="total_gross_weight" id="total_gross_weight">
                    <input type="hidden" name="density_nett" id="density_nett">
                    <input type="hidden" name="density_gross" id="density_gross">
                    <input type="hidden" name="payment_status" value="unpaid">

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        {{-- PRODUCT NAME --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Name</label>
                            <input type="text" name="product_name" id="in_product_name" value="{{ old('product_name') }}"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold focus:border-blue-500 outline-none transition-all"
                                required>
                        </div>
                        {{-- PRODUCT TYPE --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Product
                                Type</label>
                            <input type="text" name="product_type" id="in_product_type" value="{{ old('product_type') }}"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold focus:border-blue-500 outline-none transition-all"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        {{-- GROSS WEIGHT --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Gross Weight
                                Per Pcs</label>
                            <div class="relative">
                                <input type="number" step="any" id="in_gross_pcs" name="gross_weight_per_pcs"
                                    value="{{ old('gross_weight_per_pcs') }}"
                                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold outline-none"
                                    required>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-400">KG</span>
                            </div>
                        </div>
                        {{-- NETT WEIGHT --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Nett Weight
                                Per Pcs</label>
                            <div class="relative">
                                <input type="number" step="any" id="in_net_pcs" name="net_weight_pcs"
                                    value="{{ old('net_weight_pcs') }}"
                                    class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold outline-none"
                                    required>
                                <span
                                    class="absolute right-4 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-400">KG</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        {{-- DOSE RANGE --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Dose Range
                                (kGy)</label>
                            <div class="flex gap-4">
                                <input type="number" step="any" name="dmin" id="in_dmin" placeholder="Min"
                                    class="w-1/2 px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold outline-none">
                                <input type="number" step="any" name="dmax" id="in_dmax" placeholder="Max"
                                    class="w-1/2 px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold outline-none">
                            </div>
                        </div>
                        {{-- DIMENSION --}}
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Dimension (P
                                x L x T) cm</label>
                            <div class="flex items-center space-x-2">
                                <input type="number" id="in_length" name="dim_length" placeholder="P"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold outline-none">
                                <input type="number" id="in_width" name="dim_width" placeholder="L"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold outline-none">
                                <input type="number" id="in_height" name="dim_height" placeholder="T"
                                    class="w-1/3 px-2 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-center font-bold outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Total
                                Quantity</label>
                            <input type="number" id="in_qty" name="quantity" value="{{ old('quantity') }}"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold outline-none"
                                required>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Satuan
                                (UOM)</label>
                            <select name="unit" id="in_unit"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold outline-none">
                                <option value="box">Box / Dus</option>
                                <option value="sack">Sack</option>
                                <option value="drum">Drum</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-gray-700 uppercase tracking-widest ml-1">Req. Temp
                                (°C)</label>
                            <input type="text" name="expect_temp" id="in_temp" placeholder="None"
                                class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl font-bold outline-none">
                        </div>
                    </div>

                    {{-- LIVE CALCULATION RESULTS --}}
                    <div class="p-6 space-y-4 border border-blue-50 bg-blue-50/30 rounded-3xl">
                        <h3 class="text-[11px] font-black text-blue-600 uppercase tracking-widest ml-1">Live Calculation
                            Results</h3>
                        <div class="grid grid-cols-2 gap-6 md:grid-cols-3 lg:grid-cols-6">
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Vol/Pcs (cm³)</span>
                                <p id="res_vol_pcs" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Total Vol (cm³)</span>
                                <p id="res_vol_total" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Total Nett (Kg)</span>
                                <p id="res_net_total" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Total Gross (Kg)</span>
                                <p id="res_gross_total" class="text-lg font-black text-gray-800">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Density (Nett)</span>
                                <p id="res_density_nett" class="text-lg font-black text-blue-600">0</p>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] font-bold text-gray-400 uppercase">Density (Gross)</span>
                                <p id="res_density_gross" class="text-lg font-black text-blue-600">0</p>
                            </div>
                        </div>
                    </div>

                    {{-- ACTION BUTTONS --}}
                    <div class="flex items-center justify-between pt-8 border-t border-gray-50">
                        <a href="{{ route('customer.dashboard') }}"
                            class="flex items-center gap-2 px-6 py-3 text-sm font-bold text-gray-400 transition-colors hover:text-gray-700">
                            <i class="fa-solid fa-arrow-left"></i> Batal
                        </a>
                        <button type="button" onclick="openVerifyModal()"
                            class="px-10 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-xl rounded-2xl hover:bg-blue-700">
                            Review Order <i class="ml-2 fa-solid fa-magnifying-glass"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- MODAL DETAIL --}}
    @include('customer.booking.booking_detail')
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = ['in_gross_pcs', 'in_net_pcs', 'in_length', 'in_width', 'in_height', 'in_qty'];

            inputs.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener('input', calculateLive);
            });

            function calculateLive() {
                const grossPcs = parseFloat(document.getElementById('in_gross_pcs').value) || 0;
                const netPcs = parseFloat(document.getElementById('in_net_pcs').value) || 0;
                const length = parseFloat(document.getElementById('in_length').value) || 0;
                const width = parseFloat(document.getElementById('in_width').value) || 0;
                const height = parseFloat(document.getElementById('in_height').value) || 0;
                const qty = parseFloat(document.getElementById('in_qty').value) || 0;

                const volPcs = length * width * height;
                const totalVolCm3 = volPcs * qty;
                const totalNet = netPcs * qty;
                const totalGross = grossPcs * qty;

                const densityNet = totalVolCm3 > 0 ? (totalNet / totalVolCm3) : 0;
                const densityGross = totalVolCm3 > 0 ? (totalGross / totalVolCm3) : 0;

                // Update UI Live Display
                document.getElementById('res_vol_pcs').innerText = volPcs.toLocaleString('id-ID');
                document.getElementById('res_vol_total').innerText = totalVolCm3.toLocaleString('id-ID');
                document.getElementById('res_net_total').innerText = totalNet.toLocaleString('id-ID');
                document.getElementById('res_gross_total').innerText = totalGross.toLocaleString('id-ID');
                document.getElementById('res_density_nett').innerText = densityNet.toFixed(6);
                document.getElementById('res_density_gross').innerText = densityGross.toFixed(6);

                // Update Hidden Inputs
                document.getElementById('vol_per_pcs').value = volPcs;
                document.getElementById('vol_total').value = totalVolCm3;
                document.getElementById('total_net_weight').value = totalNet;
                document.getElementById('total_gross_weight').value = totalGross;
                document.getElementById('density_nett').value = densityNet;
                document.getElementById('density_gross').value = densityGross;
                document.getElementById('dimension_pack').value = `${length}x${width}x${height}`;
            }
        });
    </script>
@endpush
