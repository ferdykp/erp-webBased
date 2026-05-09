@extends('admin.layout.app')

@section('title', 'Process Parameter')

@section('content')

    {{-- Data Source: Hidden data untuk referensi JavaScript --}}
    <div id="porterDataSource" class="hidden">
        @foreach ($porters as $p)
            <div data-name="{{ $p->name }}"></div>
        @endforeach
    </div>

    <div class="w-full pb-10 space-y-6 md:space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <h2 class="text-3xl font-black tracking-tighter md:text-4xl text-slate-800">Process Parameter</h2>
                <p class="text-xs font-medium md:text-sm text-slate-500">Step 1: Process Set &amp; split batch sebelum masuk
                    ke tahap
                    <span class="font-semibold text-blue-600">In Irradiation</span>.
                </p>
            </div>
            <button onclick="document.getElementById('createMachineModal').classList.replace('hidden','flex')"
                class="flex items-center justify-center gap-2 px-6 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-lg rounded-2xl hover:bg-blue-700 active:scale-95 shadow-blue-100 sm:w-max">
                <i class="fa-solid fa-plus"></i>
                Add New Machine
            </button>
        </div>

        {{-- ═══ MASTER MESIN (Production Lines) ═══ --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2rem] md:rounded-[2.5rem] p-5 md:p-8">
            <h3 class="mb-5 text-base font-black md:text-lg text-slate-700">
                <i class="mr-2 text-blue-600 fa-solid fa-gear"></i>Machine List
            </h3>
            <div class="flex flex-wrap gap-2 md:gap-3">
                @forelse($productionLines as $line)
                    <div
                        class="flex items-center gap-3 px-4 py-2 transition-colors border bg-slate-50 border-slate-100 rounded-xl group hover:border-blue-200">
                        <span class="text-xs font-bold md:text-sm text-slate-700">{{ $line->name }}</span>
                        <div class="flex items-center gap-1.5">
                            <button onclick="openEditMachineModal({{ $line->id }}, '{{ addslashes($line->name) }}')"
                                class="w-7 h-7 flex items-center justify-center text-amber-600 bg-amber-50 rounded-lg hover:bg-amber-600 hover:text-white transition-all text-[10px]">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.production-lines.destroy', $line) }}" method="POST"
                                onsubmit="return confirm('Hapus mesin {{ $line->name }}?')" class="inline-block m-0">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 flex items-center justify-center text-red-600 bg-red-50 rounded-lg hover:bg-red-600 hover:text-white transition-all text-[10px]">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm italic text-slate-400">Belum ada mesin terdaftar.</p>
                @endforelse
            </div>
        </div>

        {{-- ═══ PROCESS PARAMETER TABLE (PER BOOKING) ═══ --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2rem] md:rounded-[2.5rem] p-5 md:p-8">
            <h3 class="mb-6 text-base font-black md:text-lg text-slate-700">
                <i class="mr-2 text-blue-600 fa-solid fa-sliders"></i>Irradiation List For Process Set
            </h3>

            @if ($bookings->isEmpty())
                <div class="py-12 text-center">
                    <p class="text-sm font-medium text-slate-400">Belum ada booking dengan status Approved.</p>
                </div>
            @else
                {{-- Mobile View: Cards --}}
                <div class="grid grid-cols-1 gap-4 lg:hidden">
                    @foreach ($bookings as $booking)
                        @php
                            $product = $booking->products->first();
                            $totalProductQty = $booking->products->sum('quantity');
                            $totalSplitQty = $booking->batches->sum('quantity');
                            $finalizedBatchQty = $booking->batches->where('status', '!=', 'pending')->sum('quantity');
                            $manageableQty = max($totalProductQty - $finalizedBatchQty, 0);
                            $unit = $product->unit ?? '';
                            $pendingBatches = $booking->batches
                                ->where('status', 'pending')
                                ->map(fn($b) => ['quantity' => $b->quantity])
                                ->values();
                        @endphp
                        <div class="p-5 space-y-4 border bg-slate-50/50 border-slate-100 rounded-2xl">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <div>
                                    <p class="text-xs font-black text-blue-600">#{{ $booking->booking_code }}</p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">
                                        {{ $booking->customer->contacts->first()->name ?? 'Guest' }}</p>
                                </div>
                                <span
                                    class="px-2 py-1 text-[9px] font-black bg-white border border-slate-200 rounded-lg text-slate-500 uppercase">{{ $booking->status }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase">Product</p>
                                    <p class="text-xs font-bold truncate text-slate-700">{{ $product->product_name ?? '-' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[9px] font-black text-slate-400 uppercase">Qty / Finished</p>
                                    <p class="text-xs font-bold text-slate-700">{{ $totalProductQty }} / <span
                                            class="text-blue-600">{{ $totalSplitQty }}</span> {{ $unit }}</p>
                                </div>
                            </div>
                            <button onclick="openUpdateProcessModal(this)" data-booking-id="{{ $booking->id }}"
                                data-booking-code="{{ $booking->booking_code }}"
                                data-customer-name="{{ $booking->customer->contacts->first()->name ?? 'Guest' }}"
                                data-product-name="{{ $product->product_name ?? '-' }}"
                                data-remaining="{{ $manageableQty }}" data-unit="{{ $unit }}"
                                data-pending-batches='@json($pendingBatches)'
                                class="flex items-center justify-center w-full gap-2 py-3 text-xs font-black text-blue-600 uppercase transition-all bg-white border-2 border-blue-600 rounded-xl hover:bg-blue-600 hover:text-white">
                                <i class="fa-solid fa-sliders"></i>
                                Update Process
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop View: Table --}}
                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[10px] font-black tracking-[0.18em] text-slate-400 uppercase">
                                <th class="px-6 py-3">Booking</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Total Qty</th>
                                <th class="px-6 py-3 text-center">Finished</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                @php
                                    $product = $booking->products->first();
                                    $totalProductQty = $booking->products->sum('quantity');
                                    $totalSplitQty = $booking->batches->sum('quantity');
                                    $finalizedBatchQty = $booking->batches
                                        ->where('status', '!=', 'pending')
                                        ->sum('quantity');
                                    $manageableQty = max($totalProductQty - $finalizedBatchQty, 0);
                                    $unit = $product->unit ?? '';
                                    $pendingBatches = $booking->batches
                                        ->where('status', 'pending')
                                        ->map(fn($b) => ['quantity' => $b->quantity])
                                        ->values();
                                @endphp
                                <tr class="transition-colors bg-white border shadow-sm group hover:bg-slate-50">
                                    <td class="px-6 py-4 border-l border-y border-slate-100 rounded-l-2xl">
                                        <p class="text-sm font-black text-slate-800">#{{ $booking->booking_code }}</p>
                                        <p class="text-[11px] font-bold text-slate-400 uppercase">{{ $booking->status }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 border-y border-slate-100">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $booking->customer->contacts->first()->name ?? 'Guest' }}</p>
                                    </td>
                                    <td class="px-6 py-4 border-y border-slate-100">
                                        <p class="text-sm font-bold text-slate-700 truncate max-w-[150px]">
                                            {{ $product->product_name ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center border-y border-slate-100">
                                        <p class="text-sm font-bold text-slate-700">{{ $totalProductQty }}
                                            {{ $unit }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-center border-y border-slate-100">
                                        <p class="text-sm font-black text-blue-600">{{ $totalSplitQty }}
                                            {{ $unit }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right border-r border-y border-slate-100 rounded-r-2xl">
                                        <button onclick="openUpdateProcessModal(this)"
                                            data-booking-id="{{ $booking->id }}"
                                            data-booking-code="{{ $booking->booking_code }}"
                                            data-customer-name="{{ $booking->customer->contacts->first()->name ?? 'Guest' }}"
                                            data-product-name="{{ $product->product_name ?? '-' }}"
                                            data-remaining="{{ $manageableQty }}" data-unit="{{ $unit }}"
                                            data-pending-batches='@json($pendingBatches)'
                                            class="inline-flex items-center gap-2 px-4 py-2.5 text-[10px] font-black text-blue-600 uppercase border-2 border-blue-600 rounded-xl hover:bg-blue-600 hover:text-white transition-all active:scale-95">
                                            <i class="fa-solid fa-sliders"></i>
                                            Update Process
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

    {{-- ═══ MODAL WRAPPER (COMMON) ═══ --}}
    {{-- Create & Edit Machine Modal --}}
    @foreach (['create' => 'Add New Machine', 'edit' => 'Edit Mesin'] as $key => $title)
        <div id="{{ $key }}MachineModal"
            class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-6 md:p-10">
                <h3 class="mb-6 text-xl font-black md:text-2xl text-slate-800">{{ $title }}</h3>
                <form id="{{ $key }}MachineForm"
                    action="{{ $key == 'create' ? route('admin.production-lines.store') : '#' }}" method="POST">
                    @csrf @if ($key == 'edit')
                        @method('PUT')
                    @endif
                    <div class="mb-6">
                        <label class="block mb-2 text-[10px] font-black uppercase text-slate-400">Nama Mesin</label>
                        <input type="text" name="name" id="{{ $key }}MachineName" required
                            placeholder='Contoh: "Mesin 1"'
                            class="w-full px-6 py-4 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button"
                            onclick="document.getElementById('{{ $key }}MachineModal').classList.replace('flex','hidden')"
                            class="flex-1 order-2 py-4 text-sm font-black uppercase transition-all sm:order-1 bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 order-1 py-4 text-sm font-black text-white uppercase transition-all bg-blue-600 shadow-lg sm:order-2 rounded-2xl hover:bg-blue-700 shadow-blue-100">
                            {{ $key == 'create' ? 'Simpan' : 'Update' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ═══ UPDATE PROCESS MODAL ═══ --}}
    <div id="updateProcessModal"
        class="fixed inset-0 z-[160] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white w-full max-w-3xl rounded-[2.5rem] shadow-2xl flex flex-col max-h-[90vh] overflow-hidden">
            {{-- Scrollable Content --}}
            <div class="flex-1 p-6 space-y-6 overflow-y-auto md:p-10 md:space-y-8 scrollbar-hide">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-black md:text-2xl text-slate-800">Update Process</h3>
                        <p class="mt-1 text-xs md:text-sm text-slate-500">Set parameter produksi dan pembagian batch.</p>
                    </div>
                    <button type="button" onclick="closeUpdateProcessModal()"
                        class="flex items-center justify-center w-10 h-10 transition rounded-xl bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-500">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                {{-- Summary --}}
                <div class="grid grid-cols-2 gap-4 p-5 border border-slate-100 rounded-2xl bg-slate-50/60 md:grid-cols-3">
                    <div class="col-span-2 md:col-span-1">
                        <p class="text-[9px] font-black text-slate-400 uppercase">Booking</p>
                        <p id="processBookingCode" class="text-xs font-black text-slate-800">#-</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase">Customer</p>
                        <p id="processCustomerName" class="text-xs font-bold truncate text-slate-700">-</p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black text-slate-400 uppercase">Product</p>
                        <p id="processProductName" class="text-xs font-bold truncate text-slate-700">-</p>
                    </div>
                    <div class="col-span-2 pt-3 border-t md:col-span-3 border-slate-200/50">
                        <p id="processRemainingInfo" class="text-[10px] font-bold text-amber-600"></p>
                    </div>
                </div>

                <form action="{{ route('admin.production.process') }}" method="POST" id="processForm"
                    class="space-y-6">
                    @csrf
                    <input type="hidden" name="booking_id" value="">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="sm:col-span-2">
                            <label class="block mb-1.5 text-[9px] font-black text-slate-400 uppercase">Production
                                Line</label>
                            <select name="production_line_id"
                                class="w-full px-4 py-3.5 text-xs font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="">-- Pilih Mesin --</option>
                                @foreach ($productionLines as $machine)
                                    <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[9px] font-black text-slate-400 uppercase">Target Dose
                                (kGy)</label>
                            <input type="number" step="0.0001" name="target_dose" placeholder="0.0000"
                                class="w-full px-4 py-3.5 text-xs font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[9px] font-black text-slate-400 uppercase">Beam Speed
                                (m/s)</label>
                            <input type="number" step="0.0001" name="beam_speed" placeholder="0.0000"
                                class="w-full px-4 py-3.5 text-xs font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block mb-1.5 text-[9px] font-black text-slate-400 uppercase">Loading Mode</label>
                            <select name="loading_mode"
                                class="w-full px-4 py-3.5 text-xs font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="" disabled selected>Pilih Mode Loading</option>
                                <option value="single-side">Single Side</option>
                                <option value="double-side">Double Side</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[9px] font-black text-slate-400 uppercase">Frequency</label>
                            <input type="number" step="0.0001" name="freq" placeholder="20"
                                class="w-full px-4 py-3.5 text-xs font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block mb-1.5 text-[9px] font-black text-slate-400 uppercase">Scan Gear</label>
                            <input type="number" step="1" name="scan_gear" placeholder="1"
                                class="w-full px-4 py-3.5 text-xs font-bold border-none bg-slate-100 rounded-xl focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                    </div>

                    <div class="pt-6 space-y-4 border-t border-slate-100">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <h4 class="text-sm font-black text-slate-800">Pembagian Batch Produksi</h4>
                            <div class="flex items-center gap-3">
                                <span id="cap_badge"
                                    class="px-3 py-1.5 text-[9px] font-black bg-slate-100 rounded-lg text-slate-600">
                                    Total: <span id="current_total_display">0</span> / <span
                                        id="total_qty_display">0</span>
                                </span>
                                <button type="button" onclick="addBatchField()"
                                    class="px-4 py-2 bg-slate-800 text-white text-[9px] font-black uppercase rounded-lg hover:bg-black transition-all">
                                    + Add Batch
                                </button>
                            </div>
                        </div>
                        <div id="batchContainer" class="space-y-3"></div>
                    </div>

                    <div class="flex flex-col gap-3 pt-4 sm:flex-row sm:justify-end">
                        <button type="button" onclick="closeUpdateProcessModal()"
                            class="px-8 py-4 text-[10px] font-black uppercase transition-all bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200">
                            Batal
                        </button>
                        <button type="submit" id="submitProcessBtn"
                            class="px-8 py-4 text-[10px] font-black text-white uppercase transition-all bg-blue-600 shadow-lg rounded-2xl hover:bg-blue-700 active:scale-95 shadow-blue-100 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="mr-2 fa-solid fa-play text-[8px]"></i>
                            Start Process
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let maxQty = 0;

        function addBatchField(qty = '') {
            const container = document.getElementById('batchContainer');
            const div = document.createElement('div');
            div.className =
                "batch-row p-4 bg-slate-50 border border-slate-100 rounded-2xl flex flex-col sm:flex-row gap-4 items-end transition-all";
            div.innerHTML = `
                <div class="flex-1 w-full">
                    <label class="text-[9px] font-black text-slate-400 uppercase mb-1.5 block">Qty Batch</label>
                    <input type="number" name="batch_quantities[]" oninput="updateBatchTotal()" step="any" required 
                        value="${qty}"
                        class="w-full px-4 py-3 text-xs font-bold bg-white border border-slate-200 batch-input rounded-xl focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="button" onclick="this.closest('.batch-row').remove(); updateBatchTotal();" 
                    class="flex items-center justify-center w-full h-10 px-4 text-xs font-bold text-red-500 transition-all sm:w-auto bg-red-50 rounded-xl hover:bg-red-500 hover:text-white">
                    <i class="mr-2 fa-solid fa-trash-can sm:mr-0"></i> <span class="sm:hidden">Hapus Batch</span>
                </button>
                <input type="hidden" name="batch_porters[]" value="-">
            `;
            container.appendChild(div);
            updateBatchTotal();
        }

        function updateBatchTotal() {
            const inputs = document.querySelectorAll('#updateProcessModal .batch-input');
            let total = 0;
            inputs.forEach(input => total += parseFloat(input.value) || 0);

            document.getElementById('current_total_display').innerText = total.toLocaleString();
            const submitBtn = document.getElementById('submitProcessBtn');
            const capBadge = document.getElementById('cap_badge');

            const safeTotal = parseFloat(total.toFixed(4));
            const safeMax = parseFloat(maxQty.toFixed(4));

            if (inputs.length > 0 && safeTotal === safeMax) {
                capBadge.className = "px-3 py-1.5 text-[9px] font-black bg-emerald-100 rounded-lg text-emerald-700";
                if (submitBtn) submitBtn.disabled = false;
            } else {
                capBadge.className = "px-3 py-1.5 text-[9px] font-black bg-amber-100 rounded-lg text-amber-700";
                if (submitBtn) submitBtn.disabled = true;
            }
        }

        function openEditMachineModal(id, name) {
            document.getElementById('editMachineName').value = name;
            document.getElementById('editMachineForm').action = `/admin/production-lines/${id}`;
            document.getElementById('editMachineModal').classList.replace('hidden', 'flex');
        }

        function openUpdateProcessModal(button) {
            const data = button.dataset;
            const modal = document.getElementById('updateProcessModal');

            modal.querySelector('#processBookingCode').textContent = `#${data.bookingCode}`;
            modal.querySelector('#processCustomerName').textContent = data.customerName;
            modal.querySelector('#processProductName').textContent = data.productName;
            modal.querySelector('#processRemainingInfo').textContent = `${data.remaining} ${data.unit} siap diproses.`;
            modal.querySelector('input[name="booking_id"]').value = data.bookingId;

            maxQty = parseFloat(data.remaining) || 0;
            document.getElementById('total_qty_display').textContent = maxQty;

            document.getElementById('batchContainer').innerHTML = '';
            const pending = JSON.parse(data.pendingBatches || '[]');
            if (pending.length > 0) pending.forEach(b => addBatchField(b.quantity));
            else addBatchField();

            modal.classList.replace('hidden', 'flex');
        }

        function closeUpdateProcessModal() {
            document.getElementById('updateProcessModal').classList.replace('flex', 'hidden');
        }
    </script>
@endpush
