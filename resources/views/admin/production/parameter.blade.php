@extends('admin.layout.app')

@section('title', 'Process Parameter')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Process Parameter</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Step 1: Process Set &amp; split batch sebelum masuk ke tahap
                    <span class="font-semibold">In Irradiation</span>.</p>
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
                                <th class="px-6 py-3 text-center">Sisa</th>
                                <th class="px-6 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bookings as $booking)
                                @php
                                    $product = $booking->products->first();
                                    $totalProductQty = $booking->products->sum('quantity');
                                    $totalBatchQty = $booking->batches->sum('quantity');
                                    $remaining = $totalProductQty - $totalBatchQty;
                                    $unit = $product->unit ?? '';
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
                                            {{ $totalBatchQty }} {{ $unit }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <p
                                            class="text-sm font-bold {{ $remaining > 0 ? 'text-amber-600' : 'text-emerald-600' }}">
                                            {{ max($remaining, 0) }} {{ $unit }}
                                        </p>
                                    </td>
                                    <td class="px-6 py-4 text-right align-middle">
                                        <button
                                            onclick="openUpdateProcessModal(this)"
                                            data-booking-id="{{ $booking->id }}"
                                            data-booking-code="{{ $booking->booking_code }}"
                                            data-customer-name="{{ $booking->customer->name ?? 'Guest' }}"
                                            data-product-name="{{ $product->product_name ?? '-' }}"
                                            data-remaining="{{ max($remaining, 0) }}"
                                            data-unit="{{ $unit }}"
                                            @if($remaining <= 0) disabled @endif
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-black uppercase rounded-xl border
                                                {{ $remaining > 0
                                                    ? 'border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white cursor-pointer'
                                                    : 'border-slate-300 text-slate-300 cursor-not-allowed' }}
                                                transition-all active:scale-95">
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

            <form action="{{ route('admin.production.process') }}" method="POST" class="space-y-6">
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
                    <div class="md:col-span-2">
                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Loading Mode</label>
                        <input type="text" name="loading_mode" placeholder="Isi mode loading (misal: single-side)"
                            class="w-full px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>

                    <div>
                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Quantity Untuk Batch
                            Baru</label>
                        <input type="number" name="quantity" min="0.01" step="any" placeholder="Qty..."
                            class="w-full px-4 py-3 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500"
                            required>
                    </div>
                </div>

                <div class="flex flex-col gap-3 mt-4 md:flex-row md:justify-end">
                    <button type="button" onclick="closeUpdateProcessModal()"
                        class="px-6 py-3 text-xs font-black uppercase rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-3 text-xs font-black uppercase rounded-xl bg-blue-600 text-white hover:bg-blue-700 active:scale-95 transition shadow-lg shadow-blue-100">
                        <i class="fa-solid fa-play mr-2"></i>
                        Process &amp; In Irradiation
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
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

            const modal = document.getElementById('updateProcessModal');
            modal.querySelector('#processBookingCode').textContent = `#${bookingCode}`;
            modal.querySelector('#processCustomerName').textContent = customerName;
            modal.querySelector('#processProductName').textContent = productName;
            modal.querySelector('#processRemainingInfo').textContent =
                `${remaining} ${unit} tersisa untuk di-split ke batch.`;

            const qtyInput = modal.querySelector('input[name="quantity"]');
            qtyInput.max = remaining;
            qtyInput.value = remaining > 0 ? remaining : '';

            modal.querySelector('input[name="booking_id"]').value = bookingId;

            modal.classList.replace('hidden', 'flex');
        }

        function closeUpdateProcessModal() {
            document.getElementById('updateProcessModal').classList.replace('flex', 'hidden');
        }
    </script>
@endpush