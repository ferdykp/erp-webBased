@extends('admin.layout.app')

@section('title', 'Process Product Irradiation')

@section('content')

    <div class="w-full pb-10 space-y-6 md:space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-4 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tighter md:text-4xl text-slate-800">Process Product Irradiation</h2>
                <p class="mt-1 text-xs font-medium md:text-sm text-slate-500">
                    Step 2: monitor batch with status <span class="font-semibold text-blue-600">In Irradiation</span> and
                    direct to step <span class="font-semibold text-emerald-600">Finish</span>.
                </p>
            </div>
        </div>

        {{-- ═══ DATA PROCESSING ═══ --}}
        @php
            $processingRows = [];
            foreach ($bookings as $booking) {
                $product = $booking->products->first();
                foreach ($booking->batches->where('status', 'processing') as $batch) {
                    $processingRows[] = [
                        'booking' => $booking,
                        'product' => $product,
                        'batch' => $batch,
                    ];
                }
            }
        @endphp

        {{-- ═══ CONTAINER UTAMA ═══ --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2rem] md:rounded-[2.5rem] p-5 md:p-8">
            <h3 class="mb-6 text-base font-black md:text-lg text-slate-700">
                <i class="mr-2 text-blue-600 fa-solid fa-radiation"></i>List Batch In Irradiation
            </h3>

            @if (empty($processingRows))
                <div class="py-16 text-center">
                    <div class="flex flex-col items-center max-w-xs gap-4 mx-auto">
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 text-slate-300">
                            <i class="text-3xl fa-solid fa-flag-checkered"></i>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-slate-600">No Batch In Irradiation</h3>
                            <p class="text-xs font-medium text-slate-400">Mulai proses dari menu Process Parameter untuk
                                membuat batch baru.</p>
                        </div>
                    </div>
                </div>
            @else
                {{-- Mobile View: Card Layout --}}
                <div class="grid grid-cols-1 gap-4 lg:hidden">
                    @foreach ($processingRows as $row)
                        @php
                            $booking = $row['booking'];
                            $product = $row['product'];
                            $batch = $row['batch'];
                        @endphp
                        <div class="p-5 space-y-4 border bg-slate-50/50 border-slate-100 rounded-2xl">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex items-center justify-center text-[10px] font-black text-blue-700 w-8 h-8 rounded-lg bg-blue-100">
                                        {{ strtoupper(substr($booking->customer->company_name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-black text-slate-800">#{{ $booking->booking_code }}</p>
                                        <p class="text-[9px] font-bold text-blue-600 uppercase">Batch
                                            #{{ $batch->batch_number }}</p>
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-1 text-[9px] font-black bg-blue-50 text-blue-600 rounded-md animate-pulse">
                                    IRRADIATING
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase">Product</p>
                                    <p class="text-xs font-bold truncate text-slate-700">{{ $product->product_name ?? '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase text-right">Target Dose</p>
                                    <p class="text-xs font-bold text-right text-slate-700">{{ (int) $batch->target_dose }}
                                        kGy</p>
                                </div>
                            </div>

                            <button onclick="openBatchDetailModal(this)" data-batch-id="{{ $batch->id }}"
                                data-booking-code="{{ $booking->booking_code }}"
                                data-company-name="{{ $booking->customer->company_name ?? '-' }}"
                                data-customer-name="{{ $booking->customer->contacts->first()->name ?? 'Guest' }}"
                                data-product-name="{{ $product->product_name ?? '-' }}"
                                data-quantity="{{ $batch->quantity }}" data-unit="{{ $batch->unit }}"
                                data-line="{{ $batch->productionLine->name ?? '-' }}"
                                data-target-dose="{{ $batch->target_dose ?? '' }}"
                                data-beam-speed="{{ $batch->beam_speed ?? '' }}" data-frequency="{{ $batch->freq ?? '' }}"
                                data-scangear="{{ $batch->scan_gear ?? '' }}"
                                data-loading-mode="{{ $batch->loading_mode ?? '' }}"
                                class="w-full py-3 text-xs font-black text-white uppercase transition-all shadow-lg bg-slate-900 rounded-xl active:scale-95 shadow-slate-200">
                                <i class="mr-2 fa-solid fa-eye"></i> Finish Process
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop View: Table Layout --}}
                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[10px] font-black tracking-[0.18em] text-slate-400 uppercase">
                                <th class="px-6 py-3">Booking & Batch</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Qty</th>
                                <th class="px-6 py-3 text-center">Target</th>
                                <th class="px-6 py-3 text-center">Status</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($processingRows as $row)
                                @php
                                    $booking = $row['booking'];
                                    $product = $row['product'];
                                    $batch = $row['batch'];
                                @endphp
                                <tr class="transition-colors bg-white border shadow-sm group hover:bg-slate-50">
                                    <td class="px-6 py-4 border-l border-y border-slate-100 rounded-l-2xl">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center text-xs font-black text-blue-700 w-9 h-9 rounded-xl bg-blue-50">
                                                {{ strtoupper(substr($booking->customer->company_name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-800">#{{ $booking->booking_code }}
                                                </p>
                                                <p class="text-[10px] font-bold text-blue-600">BATCH
                                                    #{{ $batch->batch_number }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 border-y border-slate-100">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $booking->customer->company_name ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-400 font-bold italic">
                                            {{ $booking->customer->contacts->first()->name ?? 'Guest' }}</p>
                                    </td>
                                    <td class="px-6 py-4 border-y border-slate-100">
                                        <p class="text-sm font-bold text-slate-700 truncate max-w-[150px]">
                                            {{ $product->product_name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center border-y border-slate-100">
                                        <p class="text-sm font-bold text-slate-700">{{ number_format($batch->quantity) }}
                                            {{ $batch->unit }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center border-y border-slate-100">
                                        <p class="text-sm font-black text-slate-700">{{ (int) $batch->target_dose }} <span
                                                class="text-[10px] text-slate-400">kGy</span></p>
                                    </td>
                                    <td class="px-6 py-4 text-center border-y border-slate-100">
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[9px] font-black text-blue-600 uppercase bg-blue-50 rounded-lg">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Irradiating
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right border-r border-y border-slate-100 rounded-r-2xl">
                                        <button onclick="openBatchDetailModal(this)" data-batch-id="{{ $batch->id }}"
                                            data-booking-code="{{ $booking->booking_code }}"
                                            data-company-name="{{ $booking->customer->company_name ?? '-' }}"
                                            data-customer-name="{{ $booking->customer->contacts->first()->name ?? 'Guest' }}"
                                            data-product-name="{{ $product->product_name ?? '-' }}"
                                            data-quantity="{{ $batch->quantity }}" data-unit="{{ $batch->unit }}"
                                            data-line="{{ $batch->productionLine->name ?? '-' }}"
                                            data-target-dose="{{ $batch->target_dose ?? '' }}"
                                            data-beam-speed="{{ $batch->beam_speed ?? '' }}"
                                            data-frequency="{{ $batch->freq ?? '' }}"
                                            data-scangear="{{ $batch->scan_gear ?? '' }}"
                                            data-loading-mode="{{ $batch->loading_mode ?? '' }}"
                                            data-offline-at="{{ $batch->offline_at }}" {{-- Tambahkan ini --}}
                                            class="inline-flex items-center gap-2 px-4 py-2.5 text-[10px] font-black uppercase border-2 border-slate-200 rounded-xl hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all active:scale-95">
                                            <i class="fa-solid fa-eye"></i> Finish
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══ MULTISTEP MODAL (Finish Process) ═══ --}}
    <div id="batchDetailModal" x-data="{ step: 1, hasDamage: 'no' }"
        class="fixed inset-0 z-[160] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

        <div id="modalContent"
            class="bg-white w-full max-w-2xl rounded-[2.5rem] shadow-2xl flex flex-col max-h-[90vh] overflow-hidden transform transition-all scale-95 opacity-0">

            {{-- Header --}}
            <div class="p-6 border-b md:p-8 border-slate-50 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-10 h-10 text-blue-600 md:w-12 md:h-12 bg-blue-50 rounded-2xl">
                            <i class="text-base md:text-xl fa-solid"
                                :class="step === 1 ? 'fa-circle-info' : 'fa-clipboard-check'"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black md:text-xl text-slate-800"
                                x-text="step === 1 ? 'Review Parameters' : 'Quality Assurance'"></h3>
                            <p id="headerBookingCode"
                                class="text-[10px] font-black tracking-widest text-blue-600 uppercase">Loading...</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeBatchDetailModal()"
                        class="flex items-center justify-center w-8 h-8 transition-colors rounded-xl bg-slate-50 text-slate-400 hover:text-red-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Progress Bar --}}
                <div class="flex items-center gap-2 mt-6">
                    <div class="flex-1 h-1.5 rounded-full transition-all duration-500"
                        :class="step >= 1 ? 'bg-blue-600' : 'bg-slate-100'"></div>
                    <div class="flex-1 h-1.5 rounded-full transition-all duration-500"
                        :class="step >= 2 ? 'bg-blue-600' : 'bg-slate-100'"></div>
                </div>
            </div>

            <form id="detailFinishForm" method="POST" class="flex flex-col flex-1 m-0 overflow-hidden">
                @csrf
                @method('PUT')

                {{-- Scrollable Content --}}
                <div class="flex-1 p-6 overflow-y-auto md:p-8">

                    {{-- STEP 1: REVIEW --}}
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0" class="space-y-6">

                        {{-- 1. Full Width Timer --}}
                        <div class="p-5 border border-blue-100 bg-blue-50/40 rounded-[1.5rem] shadow-sm shadow-blue-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-wider mb-1">Current
                                        Irradiation Duration</p>
                                    <div class="flex items-baseline gap-2">
                                        <p id="realtimeTimer" class="text-3xl font-black tracking-tighter text-blue-700">
                                            00:00:00</p>
                                        <span class="text-[10px] font-bold text-blue-400 uppercase">Elapsed</span>
                                    </div>
                                </div>
                                <div
                                    class="flex items-center justify-center bg-white border-4 shadow-sm w-14 h-14 border-blue-50 rounded-2xl">
                                    <i class="text-2xl text-blue-600 fa-solid fa-stopwatch animate-pulse"></i>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Two Columns Info --}}
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            {{-- Left: Customer & Product --}}
                            <div class="px-1 space-y-5">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1 tracking-widest">Customer
                                    </p>
                                    <p id="detailCompanyName" class="text-sm font-black leading-tight text-slate-700">-
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1 tracking-widest">Product
                                        Item</p>
                                    <p id="detailProductName" class="text-sm font-bold text-slate-600">-</p>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase mb-1 tracking-widest">
                                        Processing Quantity</p>
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <p id="detailBatchInfo" class="text-sm font-black text-blue-600">-</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Parameters Card --}}
                            <div class="p-5 space-y-3.5 border bg-slate-50/50 border-slate-100 rounded-[1.5rem]">
                                <p class="text-[9px] font-black text-slate-400 uppercase mb-2 tracking-widest text-center">
                                    Process Parameters</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-bold uppercase text-[9px]">Target Dose</span>
                                    <span id="detailTargetDose"
                                        class="px-2.5 py-1 text-xs font-black bg-white border border-slate-100 text-slate-800 rounded-lg">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-bold uppercase text-[9px]">Beam Speed</span>
                                    <span id="detailBeamSpeed" class="text-xs font-black text-slate-800">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500 font-bold uppercase text-[9px]">Frequency</span>
                                    <span id="detailFrequency" class="text-xs font-black text-slate-800">-</span>
                                </div>
                                <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                                    <span class="text-slate-500 font-bold uppercase text-[9px]">Loading Mode</span>
                                    <span id="detailLoadingMode"
                                        class="text-[10px] font-black text-blue-600 uppercase">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- STEP 2: QA FORM (Sudah cukup rapi, hanya sedikit spacing tweak) --}}
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-4"
                        x-transition:enter-end="opacity-100 translate-x-0" class="space-y-5">
                        <div class="p-4 mb-6 border bg-emerald-50/30 rounded-2xl border-emerald-100">
                            <label
                                class="block mb-2 text-[10px] font-black uppercase text-emerald-600 tracking-wider text-center">Actual
                                Dose Measured (kGy)</label>
                            <input type="number" step="0.1" name="actual_dose" required placeholder="0.0"
                                class="w-full px-5 py-4 text-2xl font-black text-center transition-all bg-white border-2 outline-none border-emerald-100 rounded-xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 text-emerald-700">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block px-1 text-[10px] font-black uppercase text-slate-400">Visual
                                    Check</label>
                                <select name="visual_check"
                                    class="w-full px-4 py-3.5 text-sm font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all">
                                    <option value="pass">PASS (OK)</option>
                                    <option value="fail">FAIL</option>
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block px-1 text-[10px] font-black uppercase text-slate-400">Indicator
                                    Change</label>
                                <select name="indicator_check"
                                    class="w-full px-4 py-3.5 text-sm font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500 transition-all">
                                    <option value="changed">CHANGED (OK)</option>
                                    <option value="no_change">NO CHANGE</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-5 border-2 border-dashed border-slate-200 rounded-[1.5rem] bg-slate-50/50">
                            <label
                                class="block mb-4 text-[10px] font-black uppercase text-slate-500 text-center tracking-widest">Package
                                Integrity Assessment</label>
                            <div class="flex justify-center gap-8">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative flex items-center justify-center">
                                        <input type="radio" name="is_damaged" value="no" x-model="hasDamage"
                                            class="w-5 h-5 text-blue-600 peer border-slate-300 focus:ring-blue-500">
                                    </div>
                                    <span class="text-sm font-bold text-slate-600 peer-checked:text-blue-600">No
                                        Damage</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="is_damaged" value="yes" x-model="hasDamage"
                                        class="w-5 h-5 text-red-600 peer border-slate-300 focus:ring-red-500">
                                    <span class="text-sm font-bold text-slate-600 peer-checked:text-red-600">Yes,
                                        Damaged</span>
                                </label>
                            </div>

                            <div x-show="hasDamage === 'yes'" x-collapse
                                class="grid grid-cols-4 gap-3 pt-5 mt-5 border-t border-slate-200">
                                <div class="col-span-1">
                                    <label class="block mb-1.5 text-[9px] font-black uppercase text-red-500">Qty</label>
                                    <input type="number" name="damaged_qty" placeholder="0"
                                        class="w-full p-3 text-xs font-bold bg-white border border-red-100 outline-none rounded-xl focus:ring-2 focus:ring-red-500">
                                </div>
                                <div class="col-span-3">
                                    <label class="block mb-1.5 text-[9px] font-black uppercase text-red-500">Damage
                                        Explanation</label>
                                    <input type="text" name="damage_description"
                                        placeholder="e.g. Box ripped during loading"
                                        class="w-full p-3 text-xs font-bold bg-white border border-red-100 outline-none rounded-xl focus:ring-2 focus:ring-red-500">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="block px-1 text-[10px] font-black uppercase text-slate-400">General Production
                                Notes</label>
                            <textarea name="qa_notes" rows="2" placeholder="Add any additional observations..."
                                class="w-full px-5 py-3 text-sm font-bold transition-all border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="flex items-center justify-between p-6 border-t md:p-8 bg-slate-50 shrink-0 border-slate-100">
                    <div x-show="step === 1">
                        <button type="button" onclick="closeBatchDetailModal()"
                            class="px-5 py-3 text-xs font-black uppercase transition-all text-slate-400 hover:text-red-500">Cancel</button>
                    </div>
                    <div x-show="step === 2">
                        <button type="button" @click="step = 1"
                            class="flex items-center gap-2 px-5 py-3 text-xs font-black uppercase transition-all text-slate-400 hover:text-slate-600">
                            <i class="fa-solid fa-arrow-left"></i> Parameters
                        </button>
                    </div>

                    <div x-show="step === 1">
                        <button type="button" @click="step = 2"
                            class="flex items-center gap-2 px-8 py-4 text-xs font-black text-white uppercase transition-all shadow-xl bg-slate-900 rounded-2xl hover:bg-blue-600 active:scale-95 shadow-slate-200">
                            Open QA Form <i class="fa-solid fa-chevron-right text-[8px]"></i>
                        </button>
                    </div>
                    <div x-show="step === 2">
                        <button type="submit"
                            class="flex items-center gap-2 px-8 py-4 text-xs font-black text-white uppercase transition-all shadow-xl bg-emerald-600 rounded-2xl hover:bg-emerald-700 active:scale-95 shadow-emerald-100">
                            <i class="fa-solid fa-circle-check"></i> Complete Process
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let irradiationInterval; // Variabel global untuk menyimpan interval
        function openBatchDetailModal(button) {
            const modal = document.getElementById('batchDetailModal');
            const content = document.getElementById('modalContent');
            const data = button.dataset;

            // Mapping Data
            modal.querySelector('#headerBookingCode').textContent = `Order #${data.bookingCode}`;
            modal.querySelector('#detailCompanyName').textContent = data.companyName;
            modal.querySelector('#detailProductName').textContent = data.productName;
            modal.querySelector('#detailBatchInfo').textContent = `${parseFloat(data.quantity)} ${data.unit}`;

            modal.querySelector('#detailTargetDose').textContent = `${data.targetDose} kGy`;
            modal.querySelector('#detailBeamSpeed').textContent = `${data.beamSpeed} m/s`;
            modal.querySelector('#detailFrequency').textContent = `${data.frequency} Hz`;
            modal.querySelector('#detailLoadingMode').textContent = data.loadingMode;

            // Set Form Action
            document.getElementById('detailFinishForm').action = `/admin/production/batches/${data.batchId}/finish`;

            // --- LOGIKA TIMER REALTIME ---
            const offlineAt = data.offlineAt; // Format: YYYY-MM-DD HH:mm:ss
            const timerDisplay = document.getElementById('realtimeTimer');
            // Bersihkan interval sebelumnya jika ada
            if (irradiationInterval) clearInterval(irradiationInterval);

            if (offlineAt) {
                const startTime = new Date(offlineAt).getTime();

                irradiationInterval = setInterval(() => {
                    const now = new Date().getTime();
                    const distance = now - startTime;

                    // Hitung jam, menit, detik
                    const hours = Math.floor(distance / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    // Format menjadi 00:00:00
                    const display =
                        (hours < 10 ? "0" + hours : hours) + ":" +
                        (minutes < 10 ? "0" + minutes : minutes) + ":" +
                        (seconds < 10 ? "0" + seconds : seconds);

                    timerDisplay.textContent = display;
                }, 1000);
            } else {
                timerDisplay.textContent = "Time not recorded";
            }

            // Set Form Action & Show Modal
            document.getElementById('detailFinishForm').action = `/admin/production/batches/${data.batchId}/finish`;

            // Reset Alpine State
            try {
                const alpineData = Alpine.$data(modal);
                alpineData.step = 1;
                alpineData.hasDamage = 'no';
            } catch (e) {}

            // Show with Animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeBatchDetailModal() {
            const modal = document.getElementById('batchDetailModal');
            const content = document.getElementById('modalContent');

            // Hentikan timer saat modal ditutup agar tidak memakan RAM
            if (irradiationInterval) {
                clearInterval(irradiationInterval);
            }

            content.classList.remove('scale-100', 'opacity-100');
            setTimeout(() => {
                modal.classList.replace('flex', 'hidden');
            }, 300);
        }
    </script>
@endpush
