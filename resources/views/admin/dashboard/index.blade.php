@extends('admin.layout.app')

@section('title', 'Dashboard')

@section('content')

    {{-- Data Source: Hidden data untuk referensi JavaScript --}}
    <div id="bookingDataSource" class="hidden">
        @foreach (\App\Models\Booking::where('status', 'pending')->with('products')->get() as $b)
            @php $product = $b->products->first(); @endphp
            <div data-code="{{ $b->booking_code }}" data-name="{{ $product->product_name ?? '-' }}"
                data-type="{{ $product->product_type ?? '-' }}" data-qty="{{ $product->quantity ?? 0 }}"
                data-unit="{{ $product->unit ?? '' }}" data-dose="{{ $product->target_dose ?? '-' }}">
            </div>
        @endforeach
    </div>
    {{-- Letakkan di dekat bookingDataSource --}}
    <div id="porterDataSource" class="hidden">
        @foreach ($porters as $p)
            <div data-name="{{ $p->name }}"></div>
        @endforeach
    </div>
    {{-- Data Inventory: Palet yang berstatus 'empty' --}}
    {{-- <div id="palletInventoryData" class="hidden">
        @foreach (\App\Models\Pallet::where('status', 'empty')->orderBy('line')->orderBy('slot_section')->orderBy('pallet_number')->get() as $p)
            <div data-line="{{ $p->line }}" data-petak="{{ $p->slot_section }}" data-pallet="{{ $p->pallet_number }}">
            </div>
        @endforeach
    </div> --}}

    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-800">System Overview</h2>
            <p class="text-sm font-medium text-gray-500">Monitoring warehouse operations and bookings.</p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-100 shadow-sm rounded-2xl">
            <i class="text-blue-600 fa-regular fa-calendar"></i>
            <span class="text-sm font-bold text-gray-600">{{ now()->format('d F Y') }}</span>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        @php
            $stats = [
                [
                    'label' => 'Total Bookings',
                    'count' => \App\Models\Booking::count(),
                    'color' => 'blue',
                    'icon' => 'fa-list-check',
                ],
                [
                    'label' => 'Pending',
                    'count' => \App\Models\Booking::where('status', 'pending')->count(),
                    'color' => 'amber',
                    'icon' => 'fa-clock',
                ],
                [
                    'label' => 'Approved',
                    'count' => \App\Models\Booking::where('status', 'approved')->count(),
                    'color' => 'sky',
                    'icon' => 'fa-circle-check',
                ],
                [
                    'label' => 'On Process',
                    'count' => \App\Models\Booking::where('status', 'processing')->count(),
                    'color' => 'purple',
                    'icon' => 'fa-spinner',
                ],
                [
                    'label' => 'Completed',
                    'count' => \App\Models\Booking::where('status', 'completed')->count(),
                    'color' => 'emerald',
                    'icon' => 'fa-box-check',
                ],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div
                class="p-6 transition-all bg-white border border-gray-100 shadow-sm group rounded-3xl hover:shadow-xl hover:-translate-y-1">
                <div
                    class="p-2 w-10 h-10 flex items-center justify-center bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 rounded-xl group-hover:bg-{{ $stat['color'] }}-600 group-hover:text-white transition-colors">
                    <i class="fa-solid {{ $stat['icon'] }}"></i>
                </div>
                <h3 class="mt-4 text-xs font-bold tracking-widest text-gray-400 uppercase">{{ $stat['label'] }}</h3>
                <p class="mt-1 text-3xl font-black text-gray-800">{{ $stat['count'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-8 mt-10 lg:grid-cols-3">
        {{-- SCANNER SECTION --}}
        <div class="lg:col-span-1">
            <div class="p-6 bg-gray-900 border-2 border-gray-800 rounded-[2.5rem] text-white shadow-2xl">
                <h3 class="flex items-center gap-3 mb-6 text-xl font-bold">
                    <span class="flex items-center justify-center w-8 h-8 bg-blue-500 rounded-lg">
                        <i class="text-sm fa-solid fa-qrcode"></i>
                    </span>
                    QR Check-in
                </h3>
                <div id="reader"
                    class="mb-6 overflow-hidden border-2 border-gray-700 border-dashed bg-gray-800/50 rounded-3xl"></div>
                <div class="space-y-4">
                    <input type="text" id="manual_booking_input" placeholder="Enter Booking Code..."
                        class="w-full py-4 pl-12 pr-4 text-sm font-bold text-white bg-gray-800 border border-gray-700 outline-none rounded-2xl focus:ring-2 focus:ring-blue-500">
                    @php $isFull = \App\Models\Pallet::where('status', 'empty')->count() === 0; @endphp
                    <button type="button" onclick="{{ $isFull ? 'alertFull()' : 'handleManualInput()' }}"
                        class="w-full py-4 text-sm font-black text-white {{ $isFull ? 'bg-gray-700' : 'bg-blue-600 hover:bg-blue-700' }} rounded-2xl transition-all active:scale-95">
                        {{ $isFull ? 'WAREHOUSE FULL' : 'PROCESS CHECK-IN' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- RECENT ACTIVITY TABLE --}}
        <div class="lg:col-span-2">
            <div class="p-8 bg-white border border-gray-100 shadow-sm rounded-[2.5rem] h-full">
                <h3 class="mb-6 text-xl font-bold text-gray-800">Recent Arrivals</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b">
                                <th class="pb-4">Ticket Code</th>
                                <th class="pb-4">Customer</th>
                                <th class="pb-4 text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse (\App\Models\Booking::whereNotNull('arrival_time')->latest('arrival_time')->take(6)->get() as $recent)
                                <tr class="group hover:bg-gray-50/50">
                                    <td class="py-4">
                                        <span
                                            class="px-3 py-1.5 bg-gray-100 text-gray-700 font-mono text-xs font-bold rounded-lg group-hover:bg-blue-50 group-hover:text-blue-700">
                                            #{{ $recent->booking_code }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-sm font-bold text-gray-800">
                                        {{ $recent->customer->contacts->first()->name ?? 'Guest' }}</td>
                                    <td class="py-4 text-sm font-black text-right text-gray-700">
                                        {{ $recent->arrival_time->format('H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-10 italic text-center text-gray-400">No recent arrivals.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- WAREHOUSE MODAL --}}
    {{-- WAREHOUSE MODAL (MULTI-STEP VERSION) --}}
    <div id="warehouseModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div
            class="bg-white w-full max-w-5xl rounded-[3.5rem] shadow-2xl relative max-h-[90vh] flex flex-col overflow-hidden">

            {{-- Header & Progress Indicator --}}
            <div class="px-12 pt-12 pb-6 border-b border-slate-50">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-3xl font-black text-slate-800">Check-in Process</h3>
                        <p class="text-sm font-medium tracking-widest uppercase text-slate-500">Code: <span
                                id="display_booking_code" class="text-blue-600"></span></p>
                    </div>
                    <button onclick="closeWarehouseModal()"
                        class="p-4 transition-all bg-slate-50 hover:bg-red-50 rounded-2xl group">
                        <i class="fa-solid fa-xmark text-slate-400 group-hover:text-red-500"></i>
                    </button>
                </div>

                {{-- Step Tracker --}}
                <div class="flex items-center justify-between max-w-2xl mx-auto">
                    <div class="flex flex-col items-center gap-2 step-item active" data-step="1">
                        <div
                            class="flex items-center justify-center w-10 h-10 font-bold text-white bg-blue-600 rounded-full shadow-lg step-circle shadow-blue-100">
                            1</div>
                        <span class="text-[10px] font-black uppercase text-slate-400">Verify</span>
                    </div>
                    <div class="flex-1 h-px mx-4 bg-slate-100"></div>
                    <div class="flex flex-col items-center gap-2 step-item" data-step="2">
                        <div
                            class="flex items-center justify-center w-10 h-10 font-bold rounded-full step-circle bg-slate-100 text-slate-400">
                            2</div>
                        <span class="text-[10px] font-black uppercase text-slate-400">Batching</span>
                    </div>
                    <div class="flex-1 h-px mx-4 bg-slate-100"></div>
                    <div class="flex flex-col items-center gap-2 step-item" data-step="3">
                        <div
                            class="flex items-center justify-center w-10 h-10 font-bold rounded-full step-circle bg-slate-100 text-slate-400">
                            3</div>
                        <span class="text-[10px] font-black uppercase text-slate-400">Placement</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.bookings.checkin') }}" method="POST" id="checkInForm"
                class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="booking_code" id="modal_booking_code">

                <div class="flex-1 px-12 py-10 overflow-y-auto scrollbar-hide">

                    {{-- STEP 1: VERIFICATION --}}
                    <div class="step-content" id="step1">
                        <div class="p-10 bg-blue-50/40 border border-blue-100 rounded-[3rem] mb-8">
                            <h4 class="mb-6 text-lg font-black text-slate-800">Mencocokkan Data Aktual</h4>
                            <div class="grid grid-cols-2 gap-10 md:grid-cols-4">
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Product</p>
                                    <p id="check_product_name" class="font-black text-slate-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Category</p>
                                    <p id="check_product_type" class="font-bold text-slate-600">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Target Dose</p>
                                    <p class="font-black text-emerald-600"><span id="check_dose">-</span> kGy</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black text-slate-400 uppercase">Booked Qty</p>
                                    <p class="font-black text-slate-800"><span id="check_qty">0</span> <span
                                            id="check_unit"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <label
                                class="flex items-center gap-4 p-6 transition-all border-2 cursor-pointer border-slate-100 rounded-3xl hover:border-blue-500">
                                <input type="checkbox" required class="w-6 h-6 text-blue-600 rounded-lg border-slate-200">
                                <span class="text-sm font-bold text-slate-600">Saya mengonfirmasi bahwa data fisik yang
                                    datang sesuai dengan data booking di atas.</span>
                            </label>
                            <input type="text" name="pic_warehouse" required
                                placeholder="Nama PIC Warehouse Penanggung Jawab"
                                class="w-full px-8 py-5 text-sm font-bold border-none bg-slate-50 rounded-3xl focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    {{-- STEP 2: BATCHING & STAFF --}}
                    <div class="hidden step-content" id="step2">
                        <div class="flex items-center justify-between mb-6">
                            <h4 class="text-lg font-black text-slate-800">Pembagian Batch & Porter</h4>
                            <div class="flex items-center gap-4">
                                <span id="cap_badge"
                                    class="px-5 py-2 text-xs font-black bg-slate-100 rounded-2xl text-slate-600">
                                    Total: <span id="current_total_display">0</span> / <span
                                        id="total_qty_display">0</span>
                                </span>
                                <button type="button" onclick="addBatchField()"
                                    class="px-6 py-2 bg-blue-600 text-white text-[10px] font-black uppercase rounded-xl hover:bg-blue-700">+
                                    Add Batch</button>
                            </div>
                        </div>
                        <div id="batchContainer" class="space-y-4">
                        </div>
                    </div>

                    {{-- STEP 3: MANUAL PLACEMENT --}}
                    <div class="hidden step-content" id="step3">
                        <h4 class="mb-6 text-lg font-black text-slate-800">Input Lokasi Penempatan</h4>
                        <div class="flex gap-4 p-6 mb-8 border bg-amber-50 border-amber-100 rounded-3xl">
                            <i class="mt-1 fa-solid fa-circle-info text-amber-500"></i>
                            <p class="text-xs font-bold leading-relaxed text-amber-700">Silahkan input kode palet atau
                                lokasi secara manual sesuai dengan posisi yang diletakkan oleh porter di lapangan.</p>
                        </div>
                        <div id="placementContainer" class="space-y-4">
                        </div>
                    </div>

                </div>

                {{-- Navigation Buttons --}}
                <div class="flex gap-4 px-12 py-8 border-t border-slate-50">
                    <button type="button" id="prevBtn" onclick="changeStep(-1)"
                        class="flex-1 hidden py-5 text-xs font-black uppercase text-slate-400 bg-slate-50 rounded-3xl hover:bg-slate-100">Previous</button>
                    <button type="button" id="nextBtn" onclick="changeStep(1)"
                        class="flex-1 py-5 text-xs font-black text-white uppercase bg-blue-600 shadow-xl rounded-3xl hover:bg-blue-700 shadow-blue-100">Continue</button>
                    <button type="submit" id="finalSubmitBtn"
                        class="flex-1 hidden py-5 text-xs font-black text-white uppercase shadow-xl bg-emerald-500 rounded-3xl hover:bg-emerald-600 shadow-emerald-100">Confirm
                        & Save Arrival</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let currentStep = 1;
        let maxQty = 0;

        // 1. DATA INITIALIZATION
        function getInventoryData() {
            const rawInventory = document.querySelectorAll('#palletInventoryData div');
            return Array.from(rawInventory).map(el => ({
                line: el.dataset.line,
                petak: el.dataset.petak,
                pallet: el.dataset.pallet
            }));
        }
        window.currentInventory = getInventoryData();

        function openWarehouseModal(code) {
            const dataSource = document.querySelector(`#bookingDataSource [data-code="${code}"]`);
            if (!dataSource) return alert("🚨 Kode Booking tidak valid atau sudah diproses!");

            maxQty = parseFloat(dataSource.getAttribute('data-qty')) || 0;

            // Populate Modal Header & Info
            document.getElementById('check_product_name').innerText = dataSource.getAttribute('data-name');
            document.getElementById('check_product_type').innerText = dataSource.getAttribute('data-type');
            document.getElementById('check_qty').innerText = maxQty;
            document.getElementById('check_dose').innerText = dataSource.getAttribute('data-dose');
            document.getElementById('check_unit').innerText = dataSource.getAttribute('data-unit');
            document.getElementById('total_qty_display').innerText = maxQty;
            document.getElementById('display_booking_code').innerText = code;
            document.getElementById('modal_booking_code').value = code;

            // Reset Steps
            currentStep = 1;
            document.getElementById('batchContainer').innerHTML = '';
            addBatchField(); // Start with 1 batch
            updateStepUI();

            document.getElementById('warehouseModal').classList.replace('hidden', 'flex');
        }

        // 2. NAVIGATION & VALIDATION
        function changeStep(n) {
            if (n === 1 && !validateCurrentStep()) return;
            currentStep += n;
            updateStepUI();
            if (currentStep === 3) preparePlacementFields();
        }

        function validateCurrentStep() {
            if (currentStep === 1) {
                const pic = document.querySelector('[name="pic_warehouse"]').value.trim();
                const check = document.querySelector('#step1 input[type="checkbox"]').checked;
                if (!pic || !check) {
                    alert("Mohon isi nama PIC dan centang konfirmasi data!");
                    return false;
                }
            }
            if (currentStep === 2) {
                const inputs = document.querySelectorAll('.batch-input');
                let total = 0;
                let allFilled = true;

                inputs.forEach(i => {
                    const val = parseFloat(i.value) || 0;
                    total += val;
                    if (val <= 0) allFilled = false;
                });

                if (inputs.length === 0 || !allFilled) {
                    alert("Semua Qty Batch harus diisi dengan angka positif!");
                    return false;
                }
                // Use epsilon for float comparison
                if (Math.abs(total - maxQty) > 0.001) {
                    alert(`Total batch (${total}) belum sesuai dengan qty booking (${maxQty})!`);
                    return false;
                }

                // Validate Porter selections
                const porters = document.querySelectorAll('[name="batch_porters[]"]');
                let porterFilled = true;
                porters.forEach(p => {
                    if (!p.value) porterFilled = false;
                });
                if (!porterFilled) {
                    alert("Pilih porter untuk setiap batch!");
                    return false;
                }
            }
            return true;
        }

        function updateStepUI() {
            document.querySelectorAll('.step-content').forEach((el, idx) => {
                el.classList.toggle('hidden', idx + 1 !== currentStep);
            });

            document.querySelectorAll('.step-item').forEach((el, idx) => {
                const circle = el.querySelector('.step-circle');
                const stepNum = idx + 1;
                if (stepNum < currentStep) {
                    circle.className =
                        "step-circle w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold";
                    circle.innerHTML = '<i class="fa-solid fa-check"></i>';
                } else if (stepNum === currentStep) {
                    circle.className =
                        "step-circle w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold shadow-lg shadow-blue-100";
                    circle.innerText = stepNum;
                } else {
                    circle.className =
                        "step-circle w-10 h-10 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center font-bold";
                    circle.innerText = stepNum;
                }
            });

            document.getElementById('prevBtn').classList.toggle('hidden', currentStep === 1);
            document.getElementById('nextBtn').classList.toggle('hidden', currentStep === 3);
            document.getElementById('finalSubmitBtn').classList.toggle('hidden', currentStep !== 3);
        }

        // 3. BATCH MANAGEMENT
        function addBatchField() {
            const container = document.getElementById('batchContainer');
            const porterData = document.querySelectorAll('#porterDataSource div');

            let porterOptions = '<option value="">Pilih Porter</option>';
            porterData.forEach(p => {
                porterOptions += `<option value="${p.dataset.name}">${p.dataset.name}</option>`;
            });

            const div = document.createElement('div');
            div.className =
                "batch-row p-6 bg-slate-50 border border-slate-100 rounded-[2rem] grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-4 animate-in fade-in zoom-in duration-300";
            div.innerHTML = `
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase mb-2 block">Qty Batch</label>
                <input type="number" name="batch_quantities[]" oninput="updateBatchTotal()" step="any" required 
                    class="w-full px-6 py-3 font-bold bg-white border-none batch-input rounded-xl focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="text-[9px] font-black text-slate-400 uppercase mb-2 block">Porter Penanggung Jawab</label>
                <select name="batch_porters[]" required 
                    class="w-full px-6 py-3 font-bold bg-white border-none rounded-xl focus:ring-2 focus:ring-blue-500">
                    ${porterOptions}
                </select>
            </div>
            <button type="button" onclick="this.parentElement.remove(); updateBatchTotal();" 
                class="pb-4 text-xs font-bold text-red-500 hover:text-red-700">
                <i class="fa-solid fa-trash-can"></i> Hapus
            </button>
        `;
            container.appendChild(div);
            updateBatchTotal();
        }

        function updateBatchTotal() {
            const inputs = document.querySelectorAll('.batch-input');
            let total = 0;
            inputs.forEach(input => total += parseFloat(input.value) || 0);

            // Hitung estimasi palet yang dibutuhkan
            let totalPalletsNeeded = 0;
            inputs.forEach(input => {
                totalPalletsNeeded += Math.ceil((parseFloat(input.value) || 0) / 10);
            });

            document.getElementById('current_total_display').innerText = total.toLocaleString();

            // Tambahkan info palet di UI jika Anda punya elemennya
            const infoPalet = document.getElementById('pallet_needed_info');
            if (infoPalet) infoPalet.innerText = `Estimasi palet dibutuhkan: ${totalPalletsNeeded}`;
        }

        // 4. PLACEMENT LOGIC
        function preparePlacementFields() {
            const container = document.getElementById('placementContainer');
            container.innerHTML = '';
            const batchInputs = document.querySelectorAll('.batch-input');
            const porterInputs = document.querySelectorAll('[name="batch_porters[]"]');

            batchInputs.forEach((input, idx) => {
                const qty = input.value;
                const porter = porterInputs[idx].value || 'Unknown';
                const div = document.createElement('div');
                div.className =
                    "p-6 border-2 border-slate-50 rounded-3xl flex flex-col lg:flex-row gap-6 items-center bg-white shadow-sm mb-4";

                div.innerHTML = `
                <div class="flex-1">
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-600 text-[8px] font-black rounded-md uppercase">Batch ${idx + 1}</span>
                    <h5 class="text-sm font-bold text-slate-800">${porter} (${qty} Unit)</h5>
                </div>
                <div class="grid grid-cols-3 gap-3 flex-[2]">
                    <select onchange="updatePetakOptions(${idx})" id="line_${idx}" required 
                        class="px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl">
                        <option value="">Line</option>
                    </select>
                    <select onchange="updatePalletOptions(${idx})" id="petak_${idx}" required 
                        class="px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl">
                        <option value="">Petak</option>
                    </select>
                    <select name="pallet_ids[]" id="pallet_${idx}" required 
                        class="px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl">
                        <option value="">Palet</option>
                    </select>
                </div>
            `;
                container.appendChild(div);

                // Init Lines for this batch
                const lineSelect = document.getElementById(`line_${idx}`);
                const uniqueLines = [...new Set(window.currentInventory.map(i => i.line))];
                uniqueLines.forEach(l => {
                    lineSelect.innerHTML += `<option value="${l}">Line ${l}</option>`;
                });
            });
        }

        function updatePetakOptions(idx) {
            const line = document.getElementById(`line_${idx}`).value;
            const petakSelect = document.getElementById(`petak_${idx}`);
            const palletSelect = document.getElementById(`pallet_${idx}`);

            petakSelect.innerHTML = '<option value="">Petak</option>';
            palletSelect.innerHTML = '<option value="">Palet</option>';

            if (!line) return;

            const filteredPetak = [...new Set(window.currentInventory
                .filter(i => i.line === line)
                .map(i => i.petak))];

            filteredPetak.forEach(p => {
                petakSelect.innerHTML += `<option value="${p}">Petak ${p}</option>`;
            });
        }

        function updatePalletOptions(idx) {
            const line = document.getElementById(`line_${idx}`).value;
            const petak = document.getElementById(`petak_${idx}`).value;
            const palletSelect = document.getElementById(`pallet_${idx}`);

            palletSelect.innerHTML = '<option value="">Palet</option>';

            if (!petak) return;

            const filteredPallets = window.currentInventory
                .filter(i => i.line === line && i.petak === petak);

            filteredPallets.forEach(p => {
                palletSelect.innerHTML += `<option value="${p.pallet}">${p.pallet}</option>`;
            });
        }

        function closeWarehouseModal() {
            if (confirm("Batalkan proses check-in? Data yang diisi akan hilang.")) {
                document.getElementById('warehouseModal').classList.replace('flex', 'hidden');
            }
        }

        // 5. SCANNER SETUP
        function onScanSuccess(code) {
            // Play success sound
            new Audio('https://www.soundjay.com/buttons/beep-07a.mp3').play().catch(() => {});
            openWarehouseModal(code);
        }

        function handleManualInput() {
            const input = document.getElementById('manual_booking_input');
            if (input.value.trim()) {
                openWarehouseModal(input.value.trim());
                input.value = '';
            }
        }

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: 250
        });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
@endsection
