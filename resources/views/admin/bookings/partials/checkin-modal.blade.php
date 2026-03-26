<div id="warehouseModal"
    class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 md:p-6">
    <div
        class="bg-white w-full max-w-5xl rounded-[2.5rem] md:rounded-[3.5rem] shadow-2xl relative max-h-[95vh] flex flex-col overflow-hidden">

        {{-- Header & Progress Indicator --}}
        <div class="px-6 py-8 border-b md:px-12 md:pt-12 md:pb-6 border-slate-50">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-2xl font-black md:text-3xl text-slate-800">Check-in Process</h3>
                    <p class="text-xs font-medium tracking-widest uppercase text-slate-500">
                        Code: <span id="display_booking_code" class="text-blue-600"></span>
                    </p>
                </div>
                <button onclick="closeWarehouseModal()"
                    class="p-3 transition-all bg-slate-50 hover:bg-red-50 rounded-2xl group">
                    <i class="fa-solid fa-xmark text-slate-400 group-hover:text-red-500"></i>
                </button>
            </div>

            {{-- Step Tracker --}}
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                @php $steps = [['Verify', 1], ['Batching', 2], ['Placement', 3], ['Payment', 4]]; @endphp
                @foreach ($steps as $index => $step)
                    <div class="flex flex-col items-center gap-2 step-item {{ $index == 0 ? 'active' : '' }}"
                        data-step="{{ $step[1] }}">
                        <div
                            class="flex items-center justify-center w-10 h-10 font-bold transition-all rounded-full step-circle {{ $index == 0 ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-slate-100 text-slate-400' }}">
                            {{ $step[1] }}
                        </div>
                        <span
                            class="text-[9px] md:text-[10px] font-black uppercase text-slate-400">{{ $step[0] }}</span>
                    </div>
                    @if (!$loop->last)
                        <div class="flex-1 h-px mx-2 bg-slate-100 md:mx-4"></div>
                    @endif
                @endforeach
            </div>
        </div>

        <form action="{{ route('admin.bookings.checkin') }}" method="POST" id="checkInForm"
            class="flex flex-col flex-1 overflow-hidden">
            @csrf
            <input type="hidden" name="booking_code" id="modal_booking_code">

            <input type="hidden" name="total_qty" id="hidden_total_qty">
            <input type="hidden" name="per_pallet" id="hidden_per_pallet">
            <input type="hidden" name="booking_id" id="modal_booking_id">
            <input type="hidden" name="finance_total" id="finance_total_hidden">

            <div class="flex-1 px-6 py-3 overflow-y-auto md:px-12 scrollbar-hide">

                {{-- STEP 1: VERIFICATION --}}
                <div class="step-content" id="step1">
                    <div class="p-6 md:p-10 bg-blue-50/40 border border-blue-100 rounded-[2rem] md:rounded-[3rem] mb-8">
                        <h4 class="mb-6 text-base font-black md:text-lg text-slate-800">Mencocokkan Data Aktual</h4>

                        <div class="grid grid-cols-2 gap-6 mb-8 md:grid-cols-4">
                            @php
                                $infoFields = [
                                    ['Product', 'check_product_name', 'text-slate-800'],
                                    ['Category', 'check_product_type', 'text-slate-600'],
                                    ['Booked Qty', 'check_qty', 'text-slate-800', 'check_unit'],
                                    ['Exp. Temp', 'check_temp', 'text-blue-600', '°C'],
                                ];
                            @endphp
                            @foreach ($infoFields as $field)
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        {{ $field[0] }}</p>
                                    <p id="{{ $field[1] }}"
                                        class="text-sm font-black md:text-base {{ $field[2] }}">-</p>
                                    @if (isset($field[3]))
                                        <span id="{{ $field[3] }}" class="text-xs font-bold text-slate-500"></span>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 gap-6 pt-6 border-t border-blue-100/50 md:grid-cols-3">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Dose
                                    Range</p>
                                <p class="text-sm font-black text-emerald-600"><span id="check_dmin">-</span> - <span
                                        id="check_dmax">-</span> kGy</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Dimension
                                    / Pack</p>
                                <p id="check_dimension" class="text-sm font-bold text-slate-700">-</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Weight /
                                    Pcs</p>
                                <p class="text-sm font-bold text-slate-700"><span id="check_weight">-</span> kg</p>
                            </div>
                        </div>
                    </div>

                    {{-- Weight & Volume Inputs --}}
                    {{-- <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                        @php
                            $inputs = [
                                ['vol_per_pcs', 'Product Volume / pcs', 'fa-box-open', 'm³', 'readonly'],
                                ['vol_total', 'Total Volume', 'fa-tags', 'm³', 'readonly'],
                                ['net_weight_pcs', 'Net Weight / pcs', 'fa-weight-hanging', 'kg', ''],
                                ['total_net_weight', 'Total Net Weight', 'fa-calculator', 'kg', 'readonly'],
                                ['gross_weight_pcs', 'Gross Weight / pcs', 'fa-weight-hanging', 'kg', ''],
                                ['total_gross_weight', 'Total Gross Weight', 'fa-calculator', 'kg', 'readonly'],
                            ];
                        @endphp
                        @foreach ($inputs as $input)
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">{{ $input[1] }}</label>
                                <div class="relative">
                                    <i
                                        class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid {{ $input[2] }}"></i>
                                    <input type="number" step="0.000001" name="{{ $input[0] }}"
                                        {{ $input[4] }}
                                        class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700">
                                    <span
                                        class="absolute text-xs font-bold text-gray-400 -translate-y-1/2 right-4 top-1/2">{{ $input[3] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div> --}}
                    {{-- Weight & Volume Inputs pada Check-in Modal --}}
                    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                        @php
                            $inputs = [
                                // [name, label, icon, unit, attributes, ID_TAMBAHAN]
                                [
                                    'vol_per_pcs',
                                    'Product Volume / pcs',
                                    'fa-box-open',
                                    'm³',
                                    'readonly',
                                    'ci_vol_per_pcs',
                                ],
                                ['vol_total', 'Total Volume', 'fa-tags', 'm³', 'readonly', 'ci_vol_total'],
                                [
                                    'net_weight_pcs',
                                    'Net Weight / pcs',
                                    'fa-weight-hanging',
                                    'kg',
                                    '',
                                    'ci_net_weight_pcs',
                                ],
                                [
                                    'total_net_weight',
                                    'Total Net Weight',
                                    'fa-calculator',
                                    'kg',
                                    'readonly',
                                    'ci_total_net_weight',
                                ],
                                [
                                    'gross_weight_pcs',
                                    'Gross Weight / pcs',
                                    'fa-weight-hanging',
                                    'kg',
                                    '',
                                    'ci_gross_weight_pcs',
                                ],
                                [
                                    'total_gross_weight',
                                    'Total Gross Weight',
                                    'fa-calculator',
                                    'kg',
                                    'readonly',
                                    'ci_total_gross_weight',
                                ],
                            ];
                        @endphp
                        @foreach ($inputs as $input)
                            <div class="space-y-2">
                                <label
                                    class="text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1">{{ $input[1] }}</label>
                                <div class="relative">
                                    <i
                                        class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid {{ $input[2] }}"></i>
                                    <input type="number" step="0.000001" name="{{ $input[0] }}"
                                        id="{{ $input[5] }}" {{-- ID Unik untuk Check-in --}} {{ $input[4] }}
                                        class="w-full pl-11 pr-12 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700">
                                    <span
                                        class="absolute text-xs font-bold text-gray-400 -translate-y-1/2 right-4 top-1/2">{{ $input[3] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-8">
                        <label
                            class="flex items-center gap-4 p-6 transition-all border-2 cursor-pointer border-slate-50 bg-slate-50/30 rounded-3xl hover:border-blue-500 group">
                            <input type="checkbox" required
                                class="w-6 h-6 text-blue-600 transition-all border-gray-200 rounded-lg focus:ring-blue-500">
                            <span class="text-xs font-bold leading-relaxed text-slate-600 group-hover:text-slate-800">
                                Saya mengonfirmasi bahwa data fisik yang datang telah sesuai dengan spesifikasi teknis
                                di atas.
                            </span>
                        </label>
                    </div>
                </div>

                {{-- STEP 2: BATCHING --}}
                <div class="hidden step-content" id="step2">
                    <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">PIC
                                Warehouse</label>
                            <input type="text" name="pic_warehouse" placeholder="Nama penanggung jawab..." required
                                class="w-full px-6 py-4 font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Porter
                                Team</label>
                            <div id="porterContainer" class="space-y-2">
                                <select name="porters[]"
                                    class="w-full px-6 py-4 font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                                    <option value="">Pilih Porter Utama</option>
                                    @foreach ($porters as $p)
                                        <option value="{{ $p->name }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="p-8 mb-8 border border-slate-100 bg-slate-50/50 rounded-[2.5rem]">
                        <h4 class="mb-6 text-sm font-black tracking-widest uppercase text-slate-400">Pallet Planning
                        </h4>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Jumlah Palet</label>
                                <input type="number" id="pallet_count" min="1"
                                    class="w-full px-6 py-4 font-bold bg-white border-none shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Qty per
                                    Palet</label>
                                <input type="number" id="per_pallet" min="1"
                                    class="w-full px-6 py-4 font-bold bg-white border-none shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[10px] font-black text-slate-500 uppercase ml-1">Sisa Box
                                    (Manual)</label>
                                <input type="number" id="pallet_remainder" readonly
                                    class="w-full px-6 py-4 font-bold border-none bg-slate-100 rounded-2xl text-slate-500">
                            </div>
                        </div>

                        {{-- Pallet Summary Card --}}
                        <div id="pallet_summary"
                            class="hidden p-6 mt-8 bg-white border shadow-sm border-emerald-100 rounded-3xl animate-in fade-in slide-in-from-bottom-4">
                            <div class="flex items-center justify-between mb-6">
                                <h5 class="text-xs font-black tracking-widest uppercase text-emerald-600">Distribution
                                    Logic</h5>
                                <span
                                    class="px-3 py-1 text-[9px] font-black bg-emerald-50 text-emerald-600 rounded-full uppercase">Verified</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
                                @foreach (['Total Qty' => 'sum_qty', 'Pallets' => 'sum_pallet', 'Isi/Pallet' => 'sum_per_pallet', 'Sisa' => 'sum_remainder'] as $label => $id)
                                    <div class="p-4 bg-slate-50 rounded-2xl">
                                        <p class="text-[9px] font-black text-slate-400 uppercase mb-1">
                                            {{ $label }}</p>
                                        <p id="{{ $id }}" class="text-sm font-black text-slate-800">0</p>
                                    </div>
                                @endforeach
                            </div>
                            <div id="pallet_distribution"
                                class="grid grid-cols-1 gap-2 md:grid-cols-2 text-[11px] font-bold text-slate-600">
                            </div>
                        </div>
                    </div>

                    <div id="batchContainer" class="space-y-3"></div>
                </div>

                {{-- STEP 3: PLACEMENT --}}
                {{-- STEP 3: PLACEMENT --}}
                {{-- <div class="hidden step-content" id="step3">
                    <div class="flex flex-col gap-6 mb-8 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h4 class="text-lg font-black text-slate-800">Placement Strategy</h4>
                            <p class="text-xs font-medium text-slate-500">Tentukan lokasi gudang untuk setiap palet.
                            </p>
                        </div>
                    </div>

                    <div id="placementContainer" class="space-y-4">
                    </div>
                </div> --}}
                {{-- STEP 3: PLACEMENT --}}
                <div class="hidden step-content" id="step3">
                    <div class="mb-8">
                        <h4 class="text-sm font-black tracking-widest uppercase text-slate-800">Assign Location</h4>
                        <p class="mt-1 text-xs font-bold text-slate-400">Tentukan lokasi rak untuk setiap batch/palet.
                        </p>
                    </div>

                    <div id="placementContainer" class="space-y-4">
                    </div>
                </div>
            </div>

            {{-- STEP 4: VERTICAL INDUSTRIAL FINANCE --}}
            <div class="hidden px-8 step-content" id="step4"
                style="max-height: 70vh; overflow-y: auto; padding-bottom: 50px;">
                {{-- Header --}}
                <div class="mb-4">
                    <h4 class="text-xs font-black tracking-widest uppercase text-slate-800">Financial Calculation</h4>
                    <p class="text-[10px] font-bold text-slate-400">Input tarif untuk estimasi biaya layanan.</p>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- 3. CALCULATION LOGIC PANEL --}}
                    <div class="p-4 border border-blue-100 shadow-inner bg-blue-50/50 rounded-2xl">
                        <h5 class="text-[9px] font-black text-blue-700 uppercase mb-3 flex items-center gap-2">
                            <i class="fa-solid fa-calculator"></i> Calculation Methodology
                        </h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3">
                            <div class="text-xs text-slate-500"><b class="text-slate-700">Irradiation:</b> Volume
                                (m³) × Rate/m³.</div>
                            <div class="text-xs text-slate-500"><b class="text-slate-700">Handling:</b> Qty Pallet
                                × Rate/Pallet.</div>
                            <div class="text-xs text-slate-500"><b class="text-slate-700">Tax:</b> 11% dari
                                (Irradiation + Handling).</div>
                            <div class="text-xs italic text-slate-500">*Rounding applied for bank compliance.</div>
                        </div>
                    </div>
                    {{-- 1. INPUT TARIF --}}
                    <div class="p-5 bg-white border border-slate-200 rounded-[2rem] shadow-sm">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            {{-- Tarif Irradiation --}}
                            <div class="space-y-1.5">
                                <label
                                    class="text-[9px] font-black text-slate-400 uppercase tracking-tight">Irradiation
                                    Rate / m³</label>
                                <div class="relative group">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 group-focus-within:text-blue-500 transition-colors">Rp</span>
                                    <input type="text" name="tariff_volume" id="tariff_volume" placeholder="0"
                                        oninput="handleCurrencyInput(this)"
                                        class="w-full py-2.5 pl-10 pr-4 text-xs font-bold bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all">
                                </div>
                            </div>

                            {{-- Handling Fee --}}
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black text-slate-400 uppercase tracking-tight">Handling
                                    Fee / Pallet</label>
                                <div class="relative group">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-slate-400 group-focus-within:text-blue-500 transition-colors">Rp</span>
                                    <input type="text" name="tariff_pallet" id="tariff_pallet" placeholder="0"
                                        oninput="handleCurrencyInput(this)"
                                        class="w-full py-2.5 pl-10 pr-4 text-xs font-bold bg-slate-50 border-none rounded-xl focus:ring-2 focus:ring-blue-500 transition-all">
                                </div>
                            </div>
                        </div>

                        {{-- Tax Switch --}}
                        <div
                            class="flex items-center justify-between p-3 mt-4 border border-slate-100 bg-slate-50 rounded-xl">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-receipt text-slate-400 text-[10px]"></i>
                                <span class="text-[10px] font-black text-slate-500 uppercase">Apply VAT (PPN
                                    11%)</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="tax_toggle" checked class="sr-only peer">
                                <div
                                    class="w-8 h-4 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all">
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- 2. SUMMARY BOX --}}
                    <div class="p-6 bg-slate-900 rounded-[2.5rem] shadow-xl overflow-hidden">
                        <div class="flex flex-col h-full border border-slate-800 p-5 rounded-[2rem]">
                            <div class="mb-4 text-center">
                                <span class="text-[9px] font-black text-slate-300 uppercase tracking-[0.3em]">Proforma
                                    Summary</span>
                            </div>
                            <div class="mb-4 space-y-3">
                                {{-- Irradiation --}}
                                <div class="flex flex-col">
                                    <div class="flex justify-between text-[10px]">
                                        <span class="text-slate-300">Subtotal Irradiation</span>
                                        <span class="font-bold text-white" id="sub_irrad">Rp 0</span>
                                    </div>
                                    {{-- Penambahan ID detail_irrad --}}
                                    <div class="flex justify-start">
                                        <span class="text-xs italic font-medium text-slate-600" id="detail_irrad">0
                                            m³ x Rp 0</span>
                                    </div>
                                </div>

                                {{-- Handling --}}
                                <div class="flex flex-col">
                                    <div class="flex justify-between text-[10px]">
                                        <span class="text-slate-300">Handling Total</span>
                                        <span class="font-bold text-white" id="sub_handling">Rp 0</span>
                                    </div>
                                    {{-- Penambahan ID detail_handling --}}
                                    <div class="flex justify-start">
                                        <span class="text-xs italic font-medium text-slate-600" id="detail_handling">0
                                            Pallet x Rp 0</span>
                                    </div>
                                </div>

                                {{-- VAT --}}
                                <div class="flex justify-between text-[10px] pb-2 border-b border-slate-800">
                                    <span class="text-slate-300">VAT (11%)</span>
                                    <span class="font-bold text-blue-400" id="tax_amount">Rp 0</span>
                                </div>
                            </div>
                            <div class="pt-4 mt-auto border-t border-slate-800">
                                <p class="mb-1 text-xs font-black uppercase text-emerald-400">Total Payable Amount
                                </p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-sm font-bold text-emerald-500">Rp</span>
                                    <input readonly id="finance_total_display"
                                        class="w-full p-0 text-xl font-black text-white bg-transparent border-none focus:ring-0"
                                        value="0">
                                    <input type="hidden" name="finance_total" id="finance_total_hidden">
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            {{-- Navigation Buttons --}}
            <div class="flex flex-col gap-4 px-6 py-8 border-t md:px-12 bg-slate-50/50 md:flex-row">
                <button type="button" id="prevBtn" onclick="changeStep(-1)"
                    class="flex-1 hidden py-5 text-xs font-black uppercase transition-all bg-white border text-slate-500 border-slate-200 rounded-3xl hover:bg-slate-100">
                    <i class="mr-2 fa-solid fa-arrow-left"></i> Previous
                </button>
                <button type="button" id="nextBtn" onclick="changeStep(1)"
                    class="flex-[2] py-5 text-xs font-black text-white uppercase bg-blue-600 shadow-xl shadow-blue-100 rounded-3xl hover:bg-blue-700 transition-all">
                    Continue <i class="ml-2 fa-solid fa-arrow-right"></i>
                </button>
                <button type="submit" id="finalSubmitBtn" onclick="setFormAction()"
                    class="flex-[2] hidden py-5 text-xs font-black text-white uppercase bg-emerald-500 rounded-3xl">
                    Confirm & Complete Check-in
                </button>

                {{-- <script>
                    function setFormAction() {
                        const form = document.getElementById('checkInForm');
                        // Pastikan Anda menggunakan ID booking yang benar dari hidden input
                        const bookingId = document.getElementById('modal_booking_code').value;
                        // SESUAIKAN DENGAN ROUTE DI WEB.PHP
                        form.action = `/admin/bookings/${bookingId}/placement`;
                    }
                </script> --}}
            </div>
        </form>
    </div>
</div>
