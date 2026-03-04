@extends('admin.layout.app')

@section('title', 'Dashboard')

@section('content')

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
                <div class="flex items-center justify-between mb-4">
                    <div
                        class="p-2 bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 rounded-xl group-hover:bg-{{ $stat['color'] }}-600 group-hover:text-white transition-colors">
                        <i class="fa-solid {{ $stat['icon'] }} text-lg"></i>
                    </div>
                </div>
                <h3 class="text-xs font-bold tracking-widest text-gray-400 uppercase">{{ $stat['label'] }}</h3>
                <p class="mt-1 text-3xl font-black text-gray-800">{{ $stat['count'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-8 mt-10 lg:grid-cols-3">
        {{-- SCANNER SECTION --}}
        <div class="lg:col-span-1">
            <div class="p-6 bg-gray-100 shadow-2xl border-2 rounded-[2.5rem] text-black">
                <h3 class="flex items-center gap-3 mb-6 text-xl font-bold">
                    <span class="flex items-center justify-center w-8 h-8 bg-blue-500 rounded-lg">
                        <i class="text-sm fa-solid fa-qrcode"></i>
                    </span>
                    QR Check-in
                </h3>

                <div id="reader"
                    class="mb-6 overflow-hidden border-2 border-gray-700 border-dashed bg-gray-800/50 rounded-3xl"></div>

                <div class="space-y-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="text-xs text-gray-500 fa-solid fa-keyboard"></i>
                        </div>
                        <input type="text" id="manual_booking_input" placeholder="Manual Booking Code..."
                            class="w-full py-4 pl-10 pr-4 text-sm font-bold text-white transition-all bg-gray-800 border border-gray-700 outline-none rounded-2xl focus:ring-2 focus:ring-blue-500 placeholder:text-gray-600">
                    </div>

                    {{-- <button type="button" onclick="handleManualInput()"
                        class="w-full py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-lg rounded-2xl hover:bg-blue-700 shadow-blue-900/20 active:scale-95">
                        PROCESS CHECK-IN
                    </button> --}}
                    @php
                        $isFull = \App\Models\Pallet::where('status', 'empty')->count() === 0;
                    @endphp

                    <button type="button" onclick="{{ $isFull ? 'alertFull()' : 'handleManualInput()' }}"
                        class="w-full py-4 text-sm font-black text-white {{ $isFull ? 'bg-gray-600 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700' }} rounded-2xl transition-all shadow-lg active:scale-95">
                        {{ $isFull ? 'WAREHOUSE FULL' : 'PROCESS CHECK-IN' }}
                    </button>

                    <script>
                        function alertFull() {
                            alert(
                                "🚨 GUDANG PENUH! Tidak ada palet tersedia. Harap selesaikan (Complete) booking lama untuk mengosongkan palet."
                            );
                        }
                    </script>
                </div>
            </div>
        </div>

        {{-- RECENT ACTIVITY TABLE --}}
        <div class="lg:col-span-2">
            <div class="p-8 bg-white border border-gray-100 shadow-sm rounded-[2.5rem] h-full">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Recent Arrivals</h3>
                    <a href="{{ route('admin.bookings') }}" class="text-xs font-bold text-blue-600 hover:underline">View
                        All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="px-2 pb-4">Ticket Code</th>
                                <th class="pb-4">Customer</th>
                                <th class="pb-4 text-right">Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse (\App\Models\Booking::whereNotNull('arrival_time')->latest('arrival_time')->take(6)->get() as $recent)
                                <tr class="transition-colors hover:bg-gray-50/50 group">
                                    <td class="px-2 py-4">
                                        <span
                                            class="px-3 py-1.5 bg-gray-100 text-gray-700 font-mono text-xs font-bold rounded-lg group-hover:bg-blue-50 group-hover:text-blue-700 transition-colors">
                                            #{{ $recent->booking_code }}
                                        </span>
                                    </td>
                                    <td class="py-4">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-bold text-gray-800">{{ $recent->customer->name ?? 'Guest' }}</span>
                                            <span class="text-[10px] text-gray-400 font-medium italic">Verified
                                                Arrival</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="flex flex-col">
                                            <span
                                                class="text-sm font-black text-gray-700">{{ $recent->arrival_time->format('H:i') }}</span>
                                            <span
                                                class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">WIB</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-sm italic text-center text-gray-400">No recent
                                        arrivals recorded today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- PALLET STORAGE MONITOR --}}
    {{-- Container Utama dengan Alpine.js --}}
    <div x-data="{ mapLine: 1 }" class="p-8 mt-10 bg-white border border-gray-100 shadow-sm rounded-[3rem]">

        {{-- HEADER & TABS --}}
        <div class="flex flex-col gap-6 mb-10 md:flex-row md:items-center md:justify-between">
            <div>
                <h3 class="text-2xl italic font-black tracking-tighter text-gray-800">Warehouse Map</h3>
                <p class="text-xs font-medium tracking-widest text-gray-400 uppercase">Visual Alokasi Real-time</p>
            </div>

            <div class="flex items-center gap-6">
                {{-- Navigasi Line (Hanya muncul jika ada line di DB) --}}
                @php $mapLines = \App\Models\Pallet::groupBy('line')->pluck('line')->sort(); @endphp
                <div class="flex p-1.5 bg-gray-100 rounded-2xl border border-gray-200 shadow-inner">
                    @foreach ($mapLines as $ln)
                        <button @click="mapLine = {{ $ln }}"
                            :class="mapLine === {{ $ln }} ? 'bg-white text-blue-600 shadow-sm' :
                                'text-gray-400 hover:text-gray-600'"
                            class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-300">
                            Line {{ $ln }}
                        </button>
                    @endforeach
                </div>

                {{-- Info Ringkas --}}
                <div class="flex items-center gap-3 px-5 py-2 border border-gray-100 bg-gray-50 rounded-2xl">
                    <div class="text-right">
                        <p class="text-[9px] font-black text-gray-400 uppercase leading-none">Status</p>
                        <p class="text-sm font-black text-gray-700">Live View</p>
                    </div>
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                </div>
            </div>
        </div>

        {{-- MAP CONTENT --}}
        @foreach ($mapLines as $line)
            <div x-show="mapLine === {{ $line }}" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                class="space-y-8">

                {{-- Statistik Line Tertentu --}}
                @php
                    $linePallets = \App\Models\Pallet::where('line', $line)->get();
                    $lineEmpty = $linePallets->where('status', 'empty')->count();
                    $lineTotal = $linePallets->count();
                @endphp

                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">
                    @for ($slot = 1; $slot <= 5; $slot++)
                        <div class="p-4 rounded-[2rem] bg-gray-50/50 border border-gray-100">
                            <h4 class="mb-4 text-[9px] font-black text-center text-gray-400 uppercase tracking-[0.2em]">
                                Petak {{ $slot }}</h4>

                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($linePallets->where('slot_section', $slot)->sortBy('pallet_number') as $pallet)
                                    <div class="relative group">
                                        <div
                                            class="flex flex-col items-center justify-center p-3 rounded-2xl border-2 transition-all duration-300 shadow-sm
                                    {{ $pallet->status == 'empty'
                                        ? 'border-white bg-white text-emerald-500 hover:border-emerald-200 hover:shadow-emerald-100'
                                        : 'border-red-100 bg-red-50 text-red-600 hover:border-red-200' }}">

                                            <i
                                                class="mb-1 text-lg transition-transform fa-solid fa-pallet group-hover:scale-110"></i>
                                            <span
                                                class="text-[8px] font-black tracking-tighter bg-gray-100 group-hover:bg-white px-1.5 py-0.5 rounded-md transition-colors">
                                                {{ str_replace(['PLT-L1-', 'PLT-L2-', 'PLT-'], '', $pallet->pallet_number) }}
                                            </span>

                                            {{-- Mini Indicator --}}
                                            <div class="mt-2 flex gap-0.5">
                                                @php $filledDots = floor($pallet->filled_boxes / 2); @endphp
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <div
                                                        class="w-1 h-1 rounded-full {{ $i <= $filledDots ? 'bg-blue-400' : 'bg-gray-200' }}">
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        {{-- Tooltip --}}
                                        @if ($pallet->status == 'filled' && $pallet->booking)
                                            <div
                                                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 hidden group-hover:block w-40 p-3 bg-gray-900 text-white text-[9px] rounded-2xl z-30 shadow-2xl">
                                                <div class="space-y-1.5">
                                                    <div class="flex justify-between pb-1 mb-1 border-b border-gray-800">
                                                        <span class="font-black text-blue-400 uppercase">Pallet Info</span>
                                                        <span class="text-gray-500">#{{ $pallet->id }}</span>
                                                    </div>
                                                    <p class="flex justify-between">Code: <span
                                                            class="font-bold text-gray-200">{{ $pallet->booking->booking_code }}</span>
                                                    </p>
                                                    <p class="flex justify-between">Client: <span
                                                            class="ml-2 font-bold text-gray-200 truncate">{{ $pallet->booking->customer->name ?? 'Guest' }}</span>
                                                    </p>
                                                    <p class="flex justify-between">Qty: <span
                                                            class="font-bold text-emerald-400">{{ $pallet->filled_boxes }}/10
                                                            Box</span></p>
                                                </div>
                                                <div
                                                    class="absolute -translate-x-1/2 border-8 border-transparent top-full left-1/2 border-t-gray-900">
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endfor
                </div>

                {{-- Footer Map Info --}}
                <div
                    class="flex items-center justify-center gap-8 py-4 border-t border-gray-50 text-[10px] font-black uppercase tracking-widest text-gray-400">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-white border-2 rounded-md border-emerald-100"></div>
                        <span>Empty ({{ $lineEmpty }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 border-2 border-red-100 rounded-md bg-red-50"></div>
                        <span>Occupied ({{ $lineTotal - $lineEmpty }})</span>
                    </div>
                    <div class="px-4 py-1 ml-4 text-gray-600 bg-gray-100 rounded-full">
                        Total Capacity: {{ $lineTotal * 10 }} Boxes
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- WAREHOUSE MODAL --}}
    <div id="warehouseModal"
        class="fixed inset-0 z-[80] hidden items-center justify-center bg-gray-900/60 backdrop-blur-md transition-all p-4">
        <div class="bg-white w-full max-w-md p-10 rounded-[3rem] shadow-2xl relative">
            <button onclick="closeWarehouseModal()" class="absolute text-gray-400 top-6 right-6 hover:text-gray-600">
                <i class="text-2xl fa-solid fa-circle-xmark"></i>
            </button>

            <div class="mb-8 text-center">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-blue-600 bg-blue-50 rounded-3xl">
                    <i class="text-2xl fa-solid fa-truck-ramp-box"></i>
                </div>
                <h3 class="text-2xl font-black text-gray-800">Check-in Details</h3>
                <p class="mt-1 text-sm font-medium text-gray-500">Booking: <span id="display_booking_code"
                        class="font-bold text-blue-600 uppercase"></span></p>
            </div>

            <form action="{{ route('admin.bookings.checkin') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="booking_code" id="modal_booking_code">

                <div class="space-y-4">
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 group-focus-within:text-blue-600 transition-colors">Warehouse
                            PIC</label>
                        <input type="text" name="pic_warehouse" required placeholder="Name of PIC..."
                            class="w-full px-5 py-4 mt-1 font-bold transition-all border-2 border-gray-100 outline-none bg-gray-50 rounded-2xl focus:border-blue-500 focus:bg-white">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="group">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 group-focus-within:text-blue-600">Porter
                                1</label>
                            <input type="text" name="porter_1" required placeholder="..."
                                class="w-full px-5 py-4 mt-1 font-bold text-center transition-all border-2 border-gray-100 outline-none bg-gray-50 rounded-2xl focus:border-blue-500 focus:bg-white">
                        </div>
                        <div class="group">
                            <label
                                class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1 group-focus-within:text-blue-600">Porter
                                2</label>
                            <input type="text" name="porter_2" required placeholder="..."
                                class="w-full px-5 py-4 mt-1 font-bold text-center transition-all border-2 border-gray-100 outline-none bg-gray-50 rounded-2xl focus:border-blue-500 focus:bg-white">
                        </div>
                    </div>
                </div>

                <div class="pt-6 mt-6 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Pembagian
                                Batch</label>
                            <p id="batch_info" class="text-[10px] font-bold text-blue-500 ml-1">Total: <span
                                    id="total_qty_display">0</span> <span id="unit_display"></span></p>
                        </div>
                        <button type="button" onclick="addBatchField()"
                            class="flex items-center justify-center w-8 h-8 text-white transition-all shadow-lg bg-emerald-500 rounded-xl hover:bg-emerald-600 active:scale-90 shadow-emerald-100">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                        </button>
                    </div>

                    {{-- Container untuk list Batch --}}
                    <div id="batchContainer" class="space-y-3">
                        {{-- Row batch pertama (default) --}}
                        <div class="flex items-center gap-3 p-3 border bg-blue-50/50 rounded-2xl border-blue-100/30">
                            <div class="flex-1">
                                <input type="number" name="batch_quantities[]" placeholder="Quantity..." required
                                    class="w-full px-4 py-2 text-sm font-black bg-white border border-gray-200 outline-none rounded-xl focus:border-blue-500">
                            </div>
                            <span class="text-[10px] font-black text-blue-400 uppercase w-16">Batch 1</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 pt-4">
                    <button type="submit"
                        class="w-full py-5 text-xs font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl shadow-blue-200 rounded-2xl hover:bg-blue-700 hover:shadow-blue-300 active:scale-95">
                        Confirm Arrival & Assign Pallets
                    </button>
                    <button type="button" onclick="closeWarehouseModal()"
                        class="w-full py-4 text-xs font-bold text-gray-400 uppercase hover:text-gray-600">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>

    {{-- <script>
        function onScanSuccess(decodedText, decodedResult) {
            let audio = new Audio('https://www.soundjay.com/buttons/beep-07a.mp3');
            audio.play();

            document.getElementById('modal_booking_code').value = decodedText;
            document.getElementById('display_booking_code').innerText = decodedText;

            openWarehouseModal();
            html5QrcodeScanner.clear();
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            }, false);
        html5QrcodeScanner.render(onScanSuccess);

        function handleManualInput() {
            const inputVal = document.getElementById('manual_booking_input').value;
            if (!inputVal) {
                alert("Please enter booking code!");
                return;
            }
            document.getElementById('modal_booking_code').value = inputVal;
            document.getElementById('display_booking_code').innerText = inputVal;
            openWarehouseModal();
        }

        function openWarehouseModal() {
            const modal = document.getElementById('warehouseModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeWarehouseModal() {
            const modal = document.getElementById('warehouseModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            // Re-render scanner if needed
            location.reload();
        }
    </script> --}}

    <script>
        let currentBatchIndex = 1;

        // SATU FUNGSI UNTUK SEMUA (Manual, Tabel, atau QR)
        function openWarehouseModal(code, totalQty = 0, unit = '') {
            // Jika dipanggil dari QR/Manual Input tanpa Qty, kita cari datanya di tabel
            if (totalQty === 0 || unit === '') {
                // Mencari elemen di tabel yang memiliki data tersebut
                const row = document.querySelector(`[data-code="${code}"]`);
                if (row) {
                    totalQty = row.getAttribute('data-qty');
                    unit = row.getAttribute('data-unit');
                }
            }

            // Set value ke elemen modal
            document.getElementById('modal_booking_code').value = code;
            document.getElementById('display_booking_code').innerText = code;
            document.getElementById('total_qty_display').innerText = totalQty;
            document.getElementById('unit_display').innerText = unit;

            // Reset Batch Container ke kondisi awal (Batch 1)
            resetBatchContainer();

            const modal = document.getElementById('warehouseModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function resetBatchContainer() {
            currentBatchIndex = 1;
            document.getElementById('batchContainer').innerHTML = `
            <div class="flex items-center gap-3 p-3 border bg-blue-50/50 rounded-2xl border-blue-100/30">
                <div class="flex-1">
                    <input type="number" name="batch_quantities[]" placeholder="Quantity..." required
                        class="w-full px-4 py-2 text-sm font-black bg-white border border-gray-200 outline-none rounded-xl focus:border-blue-500">
                </div>
                <span class="text-[10px] font-black text-blue-400 uppercase w-16">Batch 1</span>
            </div>
        `;
        }

        function closeWarehouseModal() {
            const modal = document.getElementById('warehouseModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Fungsi scanner memanggil openWarehouseModal
        function onScanSuccess(decodedText, decodedResult) {
            let audio = new Audio('https://www.soundjay.com/buttons/beep-07a.mp3');
            audio.play();
            openWarehouseModal(decodedText); // Mencoba mencari qty otomatis
            html5QrcodeScanner.clear();
        }
    </script>
@endsection
