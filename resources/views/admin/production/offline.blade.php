@extends('admin.layout.app')

@section('title', 'Process Product Irradiation')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Process Product Irradiation</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">
                    Step 2: monitor batch with status <span class="font-semibold text-blue-600">In Irradiation</span> and
                    direct to step <span class="font-semibold text-emerald-600">Finish</span>.
                </p>
            </div>
        </div>

        {{-- ═══ TABEL BATCH IN IRRADIATION ═══ --}}
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

        <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-8">
            <h3 class="mb-4 text-lg font-black text-slate-700">
                <i class="mr-2 text-blue-600 fa-solid fa-radiation"></i>List Batch In Irradiation
            </h3>

            @if (empty($processingRows))
                <div class="py-12 text-center">
                    <div class="flex flex-col items-center gap-4">
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                            <i class="text-3xl fa-solid fa-flag-checkered text-slate-300"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-600">No Batch In Irradiation</h3>
                        <p class="text-sm text-slate-400">
                            Mulai proses dari menu <strong>Process Parameter</strong> untuk membuat batch baru.
                        </p>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[10px] font-black tracking-[0.18em] text-slate-500 uppercase">
                                <th class="px-6 py-3">Booking</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Batch</th>
                                <th class="px-6 py-3 text-center">Qty</th>
                                {{-- <th class="px-6 py-3 text-center">Line</th> --}}
                                <th class="px-6 py-3 text-center">Target Dose</th>
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
                                <tr class="bg-white border shadow-sm rounded-2xl border-slate-100">
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center text-xs font-black text-blue-700 w-9 h-9 rounded-xl bg-blue-50">
                                                {{ strtoupper(substr($booking->customer->company_name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-800">#{{ $booking->booking_code }}
                                                </p>
                                                <p class="text-[11px] font-semibold text-slate-400">Created
                                                    {{ $batch->created_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $booking->customer->company_name ?? '-' }}</p>
                                        <p class="text-[10px] text-slate-400 font-medium italic">
                                            {{ $booking->customer->contacts->first()->name ?? 'Guest' }}</p>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-bold text-slate-700">{{ $product->product_name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <span
                                            class="inline-flex items-center px-3 py-1 text-xs font-black text-blue-700 uppercase rounded-lg bg-blue-50">
                                            Batch #{{ $batch->batch_number }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-slate-700">{{ number_format($batch->quantity) }}
                                            {{ $batch->unit }}</p>
                                    </td>
                                    {{-- <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $batch->productionLine->name ?? '-' }}</p>
                                    </td> --}}
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-slate-700">{{ (int) $batch->target_dose }} kGy</p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <span
                                            class="inline-flex items-center px-3 py-1.5 text-[10px] font-black text-blue-700 uppercase bg-blue-50 rounded-lg">
                                            <i class="mr-1 fa-solid fa-spinner fa-spin"></i> In Irradiation
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right align-middle">
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
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-black uppercase transition-all border rounded-xl border-slate-300 text-slate-700 hover:bg-slate-900 hover:text-white active:scale-95">
                                            <i class="fa-solid fa-eye"></i>
                                            Finish
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

    {{-- DETAIL MULTISTEP MODAL --}}
    <div id="batchDetailModal" x-data="{ step: 1 }"
        class="fixed inset-0 z-[160] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 transition-all duration-300">

        <div id="modalContent"
            class="bg-white w-full max-w-3xl rounded-[2.5rem] shadow-2xl overflow-hidden transform transition-all scale-95 opacity-0 duration-300">

            {{-- Modal Header --}}
            <div class="px-10 pt-10 pb-6 bg-gradient-to-b from-slate-50 to-white">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-12 h-12 text-blue-600 bg-blue-100 shadow-inner rounded-2xl">
                            <i class="text-xl fa-solid" :class="step === 1 ? 'fa-circle-info' : 'fa-clipboard-check'"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black tracking-tight text-slate-800"
                                x-text="step === 1 ? 'Batch Information' : 'Quality Assurance'"></h3>
                            <p id="headerBookingCode"
                                class="text-[10px] font-black tracking-[0.2em] text-blue-600 uppercase">Loading...</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeBatchDetailModal()"
                        class="flex items-center justify-center w-10 h-10 transition-colors bg-white border shadow-sm rounded-xl border-slate-100 text-slate-400 hover:text-red-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Step Tracker --}}
                <div class="flex items-center gap-4 px-2">
                    <div class="flex-1 h-1.5 rounded-full transition-all duration-500"
                        :class="step >= 1 ? 'bg-blue-600' : 'bg-slate-100'"></div>
                    <div class="flex-1 h-1.5 rounded-full transition-all duration-500"
                        :class="step >= 2 ? 'bg-blue-600' : 'bg-slate-100'"></div>
                </div>
            </div>

            <form id="detailFinishForm" method="POST" class="m-0">
                @csrf
                @method('PUT')

                <div class="px-10 pb-10">
                    {{-- STEP 1: REVIEW PARAMETER --}}
                    <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-8">
                        <div class="grid grid-cols-1 gap-8 mb-8 md:grid-cols-2">
                            {{-- Info --}}
                            <div class="space-y-6">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Company
                                        </p>
                                        <p id="detailCompanyName" class="text-sm font-bold text-slate-700">-</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400">
                                        <i class="fa-solid fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Customer
                                            PIC</p>
                                        <p id="detailCustomerName" class="text-sm font-bold text-slate-700">-</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400">
                                        <i class="fa-solid fa-box"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Product
                                            Name</p>
                                        <p id="detailProductName" class="text-sm font-bold text-slate-700">-</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-4">
                                    <div
                                        class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 text-slate-400">
                                        <i class="fa-solid fa-box"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Batch
                                            Quantity</p>
                                        <p id="detailBatchInfo" class="text-sm font-bold text-slate-700">-</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Technical Card --}}
                            <div class="p-6 border border-slate-100 rounded-[2rem] bg-slate-50/50 space-y-4">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">Production Line</span>
                                    <span id="detailLine" class="text-sm font-black text-slate-800">-</span>
                                </div>
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">Target Dose</span>
                                    <span id="detailTargetDose" class="text-sm font-black text-slate-800">-</span>
                                </div>
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">Beam Speed</span>
                                    <span id="detailBeamSpeed" class="text-sm font-black text-slate-800">-</span>
                                </div>
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">Frequency</span>
                                    <span id="detailFrequency" class="text-sm font-black text-slate-800">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">Scan Gear</span>
                                    <span id="detailScanGear" class="text-sm font-black text-slate-800">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black text-slate-400 uppercase">Loading Mode</span>
                                    <span id="detailLoadingMode" class="text-sm font-black text-slate-800">-</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 pt-6 border-t border-slate-50">
                            <button type="button" onclick="closeBatchDetailModal()"
                                class="px-8 py-4 text-xs font-black uppercase transition-colors text-slate-400 hover:text-slate-600">Cancel</button>
                            <button type="button" @click="step = 2"
                                class="px-10 py-4 text-xs font-black text-white uppercase transition-all shadow-xl bg-slate-900 rounded-2xl hover:bg-blue-600 shadow-slate-200 active:scale-95">
                                Next: QA Form <i class="ml-2 fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 2: QUALITY ASSURANCE FORM --}}
                    {{-- STEP 2: QUALITY ASSURANCE FORM --}}
                    <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-x-8">
                        <div class="mb-8 space-y-6">
                            <div class="grid grid-cols-2 gap-5">
                                {{-- Dose Input --}}
                                <div class="col-span-2">
                                    <label
                                        class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Actual
                                        Absorbed Dose (kGy)</label>
                                    <input type="number" step="0.1" name="actual_dose" required
                                        placeholder="Measured dose..."
                                        class="w-full px-6 py-4 text-sm font-bold transition-all border-2 border-transparent outline-none bg-slate-50 rounded-2xl focus:border-blue-500 focus:bg-white">
                                </div>

                                {{-- Checkboxes --}}
                                <div>
                                    <label
                                        class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Visual
                                        Inspection</label>
                                    <select name="visual_check"
                                        class="w-full px-6 py-4 text-sm font-bold transition-all border-2 border-transparent outline-none bg-slate-50 rounded-2xl focus:border-blue-500 focus:bg-white">
                                        <option value="pass">PASS (OK)</option>
                                        <option value="fail">FAIL (REJECT)</option>
                                    </select>
                                </div>
                                <div>
                                    <label
                                        class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">Color
                                        Indicator</label>
                                    <select name="indicator_check"
                                        class="w-full px-6 py-4 text-sm font-bold transition-all border-2 border-transparent outline-none bg-slate-50 rounded-2xl focus:border-blue-500 focus:bg-white">
                                        <option value="changed">CHANGED (OK)</option>
                                        <option value="no_change">NO CHANGE</option>
                                    </select>
                                </div>

                                {{-- DAMAGE QUESTION (RADIO BUTTONS) --}}

                                <div class="col-span-2">
                                    <label
                                        class="block mb-2 text-[10px] font-black uppercase tracking-widest text-slate-400">QA
                                        Notes / General Remarks</label>
                                    <textarea name="qa_notes" rows="2" placeholder="Describe any additional issues..."
                                        class="w-full px-6 py-4 text-sm font-bold transition-all border-2 border-transparent outline-none bg-slate-50 rounded-2xl focus:border-blue-500 focus:bg-white"></textarea>
                                </div>
                                <div
                                    class="col-span-2 p-6 border-2 border-dashed rounded-3xl border-slate-100 bg-slate-50/30">
                                    <label
                                        class="block mb-4 text-[10px] font-black uppercase tracking-widest text-slate-500 italic">There
                                        is Packaging Damages ?</label>
                                    <div class="flex gap-6">
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="radio" name="is_damaged" value="no" x-model="hasDamage"
                                                class="w-5 h-5 text-blue-600 border-slate-300 focus:ring-blue-500">
                                            <span
                                                class="text-sm font-bold transition-colors text-slate-600 group-hover:text-slate-900">No
                                                Damage</span>
                                        </label>
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="radio" name="is_damaged" value="yes" x-model="hasDamage"
                                                class="w-5 h-5 text-red-600 border-slate-300 focus:ring-red-500">
                                            <span
                                                class="text-sm font-bold transition-colors text-slate-600 group-hover:text-red-600">Damaged</span>
                                        </label>
                                    </div>

                                    {{-- CONDITIONAL DAMAGE INPUTS --}}
                                    <div x-show="hasDamage === 'yes'" x-collapse x-cloak
                                        class="pt-6 mt-6 space-y-4 border-t border-slate-100">
                                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                                            <div class="md:col-span-1">
                                                <label
                                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-red-400">Amount
                                                </label>
                                                <input type="number" name="damaged_qty"
                                                    :required="hasDamage === 'yes'" placeholder="0"
                                                    class="w-full p-2 text-sm font-bold border-2 outline-none border-red-50/50 bg-red-50/30 rounded-xl focus:border-red-200">
                                            </div>
                                            <div class="md:col-span-3">
                                                <label
                                                    class="block mb-2 text-[10px] font-black uppercase tracking-widest text-red-400">Explanation</label>
                                                <input type="text" name="damage_description"
                                                    :required="hasDamage === 'yes'"
                                                    placeholder="Contoh: Box Ripped .."
                                                    class="w-full px-5 py-3 text-sm font-bold border-2 outline-none border-red-50/50 bg-red-50/30 rounded-xl focus:border-red-200">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                            <button type="button" @click="step = 1"
                                class="px-6 py-4 text-xs font-black uppercase transition-colors text-slate-400 hover:text-slate-800">
                                <i class="mr-2 fa-solid fa-chevron-left"></i> Back
                            </button>
                            <button type="submit"
                                class="px-10 py-4 text-xs font-black text-white uppercase transition-all shadow-lg rounded-2xl bg-emerald-600 hover:bg-emerald-700 shadow-emerald-100 active:scale-95">
                                <i class="mr-2 fa-solid fa-circle-check"></i> Complete Irradiation
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openBatchDetailModal(button) {
            const modal = document.getElementById('batchDetailModal');
            const content = document.getElementById('modalContent');
            const data = button.dataset;

            // Helper pembulatan
            const cleanRound = (val) => (val && !isNaN(val)) ? Math.round(val) : null;

            // Fill Header & Content
            modal.querySelector('#headerBookingCode').textContent = `ORDER #${data.bookingCode}`;
            modal.querySelector('#detailCompanyName').textContent = data.companyName;
            modal.querySelector('#detailCustomerName').textContent = data.customerName;
            modal.querySelector('#detailProductName').textContent = data.productName;
            // Cara yang benar menggunakan template literals
            modal.querySelector('#detailBatchInfo').textContent = `${cleanRound(data.quantity)} ${data.unit}`;

            // Technical
            modal.querySelector('#detailLine').textContent = data.line || '-';
            modal.querySelector('#detailTargetDose').textContent = cleanRound(data.targetDose) ?
                `${cleanRound(data.targetDose)} kGy` : '-';
            modal.querySelector('#detailBeamSpeed').textContent = cleanRound(data.beamSpeed) ?
                `${cleanRound(data.beamSpeed)} m/s` : '-';
            modal.querySelector('#detailFrequency').textContent = cleanRound(data.frequency) ?
                `${cleanRound(data.frequency)} Hz` : '-';
            modal.querySelector('#detailScanGear').textContent = data.scangear || '-';
            modal.querySelector('#detailLoadingMode').textContent = data.loadingMode || '-';


            // Form Action
            const form = modal.querySelector('#detailFinishForm');
            form.action = `/admin/production/batches/${data.batchId}/finish`;

            // Reset step Alpine ke 1 setiap kali modal dibuka
            // Kita gunakan __x.$data jika menggunakan Alpine.js manual
            // Tapi cara paling aman adalah memicu event atau membiarkan x-data init ulang.
            // Di sini kita gunakan selector Alpine:
            try {
                Alpine.$data(modal).step = 1;
                Alpine.$data(modal).hasDamage = 'no'; // Tambahkan ini
            } catch (e) {}

            // Show Animation
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeBatchDetailModal() {
            const modal = document.getElementById('batchDetailModal');
            const content = document.getElementById('modalContent');

            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.replace('flex', 'hidden');
            }, 300);
        }
    </script>
@endpush
