@extends('admin.layout.app')

@section('title', 'Process Parameter')

@section('content')

    {{-- Data Source: Hidden data untuk referensi JavaScript --}}
    <div id="porterDataSource" class="hidden">
        @foreach ($porters as $p)
            <div data-name="{{ $p->name }}"></div>
        @endforeach
    </div>

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Process Parameter</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Step 1: Process Set &amp; split batch sebelum masuk ke
                    tahap
                    <span class="font-semibold">In Irradiation</span>.
                </p>
            </div>
            <button onclick="document.getElementById('createMachineModal').classList.replace('hidden','flex')"
                class="flex items-center gap-2 px-6 py-3 text-sm font-black text-white bg-blue-600 shadow-lg rounded-2xl hover:bg-blue-700 active:scale-95 transition-all shadow-blue-100">
                <i class="fa-solid fa-plus"></i>
                Tambah Mesin Baru
            </button>
        </div>

        {{-- ═══ MASTER MESIN (Production Lines) ═══ --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-8">
            <h3 class="mb-4 text-lg font-black text-slate-700">
                <i class="fa-solid fa-gear mr-2 text-blue-600"></i>Daftar Mesin (Production Lines)
            </h3>
            <div class="flex flex-wrap gap-3">
                @forelse($productionLines as $line)
                    <div class="flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-100 rounded-xl group">
                        <span class="text-sm font-bold text-slate-700">{{ $line->name }}</span>
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button onclick="openEditMachineModal({{ $line->id }}, '{{ addslashes($line->name) }}')"
                                class="w-6 h-6 flex items-center justify-center text-amber-600 bg-amber-50 rounded-md hover:bg-amber-600 hover:text-white transition-all text-[10px]">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <form action="{{ route('admin.production-lines.destroy', $line) }}" method="POST"
                                onsubmit="return confirm('Hapus mesin {{ $line->name }}?')" class="flex items-center m-0">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-6 h-6 flex items-center justify-center text-red-600 bg-red-50 rounded-md hover:bg-red-600 hover:text-white transition-all text-[10px]">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 italic">Belum ada mesin. Klik "Tambah Mesin Baru" untuk menambahkan.</p>
                @endforelse
            </div>
        </div>

        {{-- ═══ PROCESS PARAMETER TABLE (PER BOOKING) ═══ --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-8">
            <h3 class="mb-4 text-lg font-black text-slate-700">
                <i class="fa-solid fa-sliders mr-2 text-blue-600"></i>Daftar Booking Untuk Process Set
            </h3>

            @if ($bookings->isEmpty())
                <div class="py-12 text-center">
                    <p class="text-sm text-slate-400">Belum ada booking dengan status <span class="font-semibold">Approved /
                            Processing</span>.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-3">
                        <thead>
                            <tr class="text-[10px] font-black tracking-[0.18em] text-slate-500 uppercase">
                                <th class="px-6 py-3">Booking</th>
                                <th class="px-6 py-3">Customer</th>
                                <th class="px-6 py-3">Product</th>
                                <th class="px-6 py-3 text-center">Total Qty</th>
                                <th class="px-6 py-3 text-center">Sudah Dibatch</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                @php
                                    $product = $booking->products->first();
                                    $totalProductQty = $booking->products->sum('quantity');

                                    // Sudah Dibatch: Gabungan batch pending (dari check-in) dan batch yang sudah final (waiting/processing/done)
                                    $totalSplitQty = $booking->batches->sum('quantity');

                                    // Sisa: Benar-benar sisa barang yang belum masuk ke batch manapun
                                    $remainingToSplit = max($totalProductQty - $totalSplitQty, 0);

                                    // Manageable Qty: Keseluruhan qty yang siap difinalisasi parameternya (termasuk yang masih pending)
                                    $finalizedBatchQty = $booking->batches->where('status', '!=', 'pending')->sum('quantity');
                                    $manageableQty = max($totalProductQty - $finalizedBatchQty, 0);

                                    $unit = $product->unit ?? '';

                                    // Ambil batch pending untuk pre-fill
                                    $pendingBatches = $booking->batches->where('status', 'pending')->map(function ($b) {
                                        return ['quantity' => $b->quantity, 'porter' => $b->porter_name];
                                    })->values();
                                @endphp
                                <tr class="bg-white rounded-2xl shadow-sm border border-slate-100">
                                    <td class="px-6 py-4 align-middle">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex items-center justify-center w-9 h-9 rounded-xl bg-blue-50 text-blue-700 font-black text-xs">
                                                {{ strtoupper(substr($booking->customer->name ?? '?', 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-black text-slate-800">#{{ $booking->booking_code }}</p>
                                                <p class="text-[11px] font-semibold text-slate-400">{{ ucfirst($booking->status) }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $booking->customer->name ?? 'Guest' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $product->product_name ?? '-' }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-slate-700">
                                            {{ $totalProductQty }} {{ $unit }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p class="text-sm font-bold text-blue-600">
                                            {{ $totalSplitQty }} {{ $unit }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right align-middle">
                                        <button onclick="openUpdateProcessModal(this)" data-booking-id="{{ $booking->id }}"
                                            data-booking-code="{{ $booking->booking_code }}"
                                            data-customer-name="{{ $booking->customer->name ?? 'Guest' }}"
                                            data-product-name="{{ $product->product_name ?? '-' }}"
                                            data-remaining="{{ $manageableQty }}" data-unit="{{ $unit }}"
                                            data-pending-batches='@json($pendingBatches)'
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-black uppercase rounded-xl border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white cursor-pointer transition-all active:scale-95">
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

    {{-- ═══ CREATE MACHINE MODAL ═══ --}}
    <div id="createMachineModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-10">
            <h3 class="mb-6 text-2xl font-black text-slate-800">Tambah Mesin Baru</h3>
            <form action="{{ route('admin.production-lines.store') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block mb-2 text-xs font-black text-slate-400 uppercase">Nama Mesin</label>
                    <input type="text" name="name" required placeholder='Contoh: "Mesin 1"'
                        class="w-full px-6 py-4 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-3">
                    <button type="button"
                        onclick="document.getElementById('createMachineModal').classList.replace('flex','hidden')"
                        class="flex-1 py-4 text-sm font-black bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-4 text-sm font-black text-white bg-blue-600 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ EDIT MACHINE MODAL ═══ --}}
    <div id="editMachineModal"
        class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div class="bg-white w-full max-w-lg rounded-[2.5rem] shadow-2xl p-10">
            <h3 class="mb-6 text-2xl font-black text-slate-800">Edit Mesin</h3>
            <form id="editMachineForm" method="POST">
                @csrf @method('PUT')
                <div class="mb-6">
                    <label class="block mb-2 text-xs font-black text-slate-400 uppercase">Nama Mesin</label>
                    <input type="text" name="name" id="editMachineName" required
                        class="w-full px-6 py-4 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-3">
                    <button type="button"
                        onclick="document.getElementById('editMachineModal').classList.replace('flex','hidden')"
                        class="flex-1 py-4 text-sm font-black bg-slate-100 text-slate-600 rounded-2xl hover:bg-slate-200 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-4 text-sm font-black text-white bg-blue-600 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══ UPDATE PROCESS MODAL (PROCESS SET + SPLIT BATCH) ═══ --}}
    <div id="updateProcessModal"
        class="fixed inset-0 z-[160] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-6">
        <div class="bg-white w-full max-w-3xl rounded-[2.5rem] shadow-2xl p-10 space-y-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-black text-slate-800">Update Process</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Step 1: isi parameter produksi, lalu split quantity ke batch baru.
                        Setelah submit, batch akan masuk status <span class="font-semibold text-blue-600">In
                            Irradiation</span>.
                    </p>
                </div>
                <button type="button" onclick="closeUpdateProcessModal()"
                    class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Booking Summary --}}
            <div class="p-4 border border-slate-100 rounded-2xl bg-slate-50/60 flex flex-wrap gap-4 items-center">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase">Booking</p>
                    <p id="processBookingCode" class="text-sm font-black text-slate-800">#-</p>
                </div>
                <div class="h-10 w-px bg-slate-200 hidden md:block"></div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase">Customer</p>
                    <p id="processCustomerName" class="text-sm font-bold text-slate-700">-</p>
                </div>
                <div class="h-10 w-px bg-slate-200 hidden md:block"></div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase">Product</p>
                    <p id="processProductName" class="text-sm font-bold text-slate-700">-</p>
                </div>
                <div class="h-10 w-px bg-slate-200 hidden md:block"></div>
                <div class="flex-1">
                    <p id="processRemainingInfo" class="text-[11px] font-semibold text-amber-600"></p>
                </div>
            </div>

            <form action="{{ route('admin.production.process') }}" method="POST" id="processForm" class="space-y-6">
                @csrf
                <input type="hidden" name="booking_id" value="">

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="md:col-span-2">
                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Production Line</label>
                        <select name="production_line_id"
                            class="w-full px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500"
                            required>
                            <option value="">-- Pilih Mesin --</option>
                            @foreach ($productionLines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Target Dose (kGy)</label>
                        <input type="number" step="0.0001" name="target_dose" placeholder="0.0000"
                            class="w-full px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Beam Speed (m/s)</label>
                        <input type="number" step="0.0001" name="beam_speed" placeholder="0.0000"
                            class="w-full px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3 items-end">
                    <div class="md:col-span-3">
                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Loading Mode</label>
                        <input type="text" name="loading_mode" placeholder="Isi mode loading (misal: single-side)"
                            class="w-full px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 mt-6 border-t border-slate-100 pt-6">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="text-sm font-black text-slate-800">Pembagian Batch & Porter</h4>
                        <div class="flex items-center gap-4">
                            <span id="cap_badge"
                                class="px-3 py-1.5 text-[10px] font-black bg-slate-100 rounded-xl text-slate-600">
                                Total: <span id="current_total_display">0</span> / <span id="total_qty_display">0</span>
                            </span>
                            <button type="button" onclick="addBatchField()"
                                class="px-4 py-1.5 bg-slate-800 text-white text-[10px] font-black uppercase rounded-lg hover:bg-slate-900 transition-colors">
                                + Add Batch
                            </button>
                        </div>
                    </div>
                    <div id="batchContainer" class="space-y-3">
                        <!-- Batch rows will be added here -->
                    </div>
                </div>

                <div class="flex flex-col gap-3 mt-8 md:flex-row md:justify-end">
                    <button type="button" onclick="closeUpdateProcessModal()"
                        class="px-6 py-3 text-xs font-black uppercase rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit" id="submitProcessBtn"
                        class="px-6 py-3 text-xs font-black uppercase rounded-xl bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-play mr-2"></i>
                        Process &amp; Masuk Queue Task
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let maxQty = 0;

        function addBatchField(qty = '', porter = '') {
            const container = document.getElementById('batchContainer');
            const porterData = document.querySelectorAll('#porterDataSource div');

            let porterOptions = '<option value="">Pilih Porter</option>';
            porterData.forEach(p => {
                const isSelected = p.dataset.name === porter ? 'selected' : '';
                porterOptions += `<option value="${p.dataset.name}" ${isSelected}>${p.dataset.name}</option>`;
            });

            const div = document.createElement('div');
            div.className =
                "batch-row p-4 bg-slate-50/50 border border-slate-100 rounded-2xl grid grid-cols-1 md:grid-cols-3 gap-3 items-end mb-2";
            div.innerHTML = `
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase mb-1.5 block">Qty Batch</label>
                            <input type="number" name="batch_quantities[]" oninput="updateBatchTotal()" step="any" required 
                                value="${qty}"
                                class="w-full px-4 py-2.5 text-xs font-bold bg-white border border-slate-200 batch-input rounded-xl focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-slate-400 uppercase mb-1.5 block">Porter Penanggung Jawab</label>
                            <select name="batch_porters[]" required 
                                class="w-full px-4 py-2.5 text-xs font-bold bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                                ${porterOptions}
                            </select>
                        </div>
                        <button type="button" onclick="this.parentElement.remove(); updateBatchTotal();" 
                            class="pb-2.5 text-xs font-bold text-red-500 hover:text-red-700 md:text-left text-center">
                            <i class="fa-solid fa-trash-can mr-1"></i> Hapus
                        </button>
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

            // Use toFixed to avoid JS floating point errors (assuming 2 decimal places max)
            const safeTotal = parseFloat(total.toFixed(4));
            const safeMax = parseFloat(maxQty.toFixed(4));

            if (inputs.length > 0 && safeTotal === safeMax) {
                capBadge.className = "px-3 py-1.5 text-[10px] font-black bg-emerald-100 rounded-xl text-emerald-700";
                if (submitBtn) submitBtn.disabled = false;
            } else {
                capBadge.className = "px-3 py-1.5 text-[10px] font-black bg-amber-100 rounded-xl text-amber-700";
                if (submitBtn) submitBtn.disabled = true;
            }
        }

        // Form validation on submit
        document.getElementById('processForm').addEventListener('submit', function (e) {
            const inputs = document.querySelectorAll('#updateProcessModal .batch-input');
            let total = 0;
            let allFilled = true;

            inputs.forEach(i => {
                const val = parseFloat(i.value) || 0;
                total += val;
                if (val <= 0) allFilled = false;
            });

            if (inputs.length === 0) {
                e.preventDefault();
                alert("Harap tambahkan minimal 1 batch!");
                return;
            }

            if (!allFilled) {
                e.preventDefault();
                alert("Semua Qty Batch harus diisi dengan angka positif!");
                return;
            }

            const safeTotal = parseFloat(total.toFixed(4));
            const safeMax = parseFloat(maxQty.toFixed(4));

            if (safeTotal !== safeMax) {
                e.preventDefault();
                alert(`Total batch (${safeTotal}) belum sesuai dengan qty tersisa (${safeMax})!`);
                return;
            }
        });
        function openEditMachineModal(id, name) {
            document.getElementById('editMachineName').value = name;
            document.getElementById('editMachineForm').action = `/admin/production-lines/${id}`;
            document.getElementById('editMachineModal').classList.replace('hidden', 'flex');
        }

        function openUpdateProcessModal(button) {
            const bookingId = button.getAttribute('data-booking-id');
            const bookingCode = button.getAttribute('data-booking-code');
            const customerName = button.getAttribute('data-customer-name');
            const productName = button.getAttribute('data-product-name');
            const remaining = button.getAttribute('data-remaining');
            const unit = button.getAttribute('data-unit');

            const pendingBatches = JSON.parse(button.getAttribute('data-pending-batches') || '[]');

            const modal = document.getElementById('updateProcessModal');
            modal.querySelector('#processBookingCode').textContent = `#${bookingCode}`;
            modal.querySelector('#processCustomerName').textContent = customerName;
            modal.querySelector('#processProductName').textContent = productName;
            modal.querySelector('#processRemainingInfo').textContent =
                `${remaining} ${unit} siap untuk diproses & diantrekan ke queue.`;

            maxQty = parseFloat(remaining) || 0;
            document.getElementById('total_qty_display').textContent = maxQty;

            // Clear and populate batch rows
            document.getElementById('batchContainer').innerHTML = '';

            if (pendingBatches.length > 0) {
                pendingBatches.forEach(b => {
                    addBatchField(b.quantity, b.porter);
                });
            } else {
                addBatchField();
            }

            modal.querySelector('input[name="booking_id"]').value = bookingId;

            modal.classList.replace('hidden', 'flex');
        }

        function closeUpdateProcessModal() {
            document.getElementById('updateProcessModal').classList.replace('flex', 'hidden');
        }
    </script>
@endpush