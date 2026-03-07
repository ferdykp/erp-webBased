@extends('admin.layout.app')

@section('title', 'Process Parameter Setting')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Process Parameter Setting</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Set parameter mesin penyinaran per batch: Production
                    Line, Target Dose, Beam Speed, Loading Mode.</p>
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

        {{-- ═══ BOOKING CARDS WITH PARAMETER FORMS ═══ --}}
        @forelse ($bookings as $booking)
            @php $product = $booking->products->first(); @endphp
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] overflow-hidden"
                x-data="{ expanded: true }">

                {{-- Booking Header --}}
                <div class="flex flex-col gap-4 p-8 cursor-pointer md:flex-row md:items-center md:justify-between hover:bg-slate-50/50"
                    @click="expanded = !expanded">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 font-black text-blue-700 bg-blue-50 rounded-2xl">
                            {{ strtoupper(substr($booking->customer->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-lg font-black text-slate-800">{{ $booking->customer->name ?? 'Guest' }}</p>
                            <span class="px-3 py-1 bg-slate-100 text-slate-600 font-mono text-xs font-bold rounded-lg">
                                #{{ $booking->booking_code }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Produk</p>
                            <p class="text-sm font-bold text-slate-700">{{ $product->product_name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Total Qty</p>
                            <p class="text-sm font-bold text-slate-700">{{ $product->quantity ?? 0 }} {{ $product->unit ?? '' }}
                            </p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200"
                            :class="expanded ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                {{-- Batch Parameter Forms --}}
                <div x-show="expanded" x-cloak x-collapse>
                    <div class="px-8 pb-8 space-y-4">
                        @forelse ($booking->batches as $batch)
                            <div class="p-6 bg-slate-50 border border-slate-100 rounded-[2rem]">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="px-3 py-1 text-xs font-black text-blue-700 bg-blue-100 rounded-lg">
                                        Batch #{{ $batch->batch_number }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-400">{{ $batch->quantity }} {{ $batch->unit }}</span>
                                    @if($batch->productionLine)
                                        <span class="px-2 py-0.5 text-[9px] font-bold text-emerald-700 bg-emerald-50 rounded-md">
                                            {{ $batch->productionLine->name }}
                                        </span>
                                    @endif
                                </div>

                                <form action="{{ route('admin.production.batches.parameter.update', $batch->id) }}" method="POST"
                                    class="grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                                    @csrf @method('PUT')

                                    <div>
                                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Production
                                            Line</label>
                                        <select name="production_line_id"
                                            class="w-full px-3 py-2.5 text-xs font-bold border-none bg-white rounded-xl focus:ring-2 focus:ring-blue-500">
                                            <option value="">-- Pilih Mesin --</option>
                                            @foreach ($productionLines as $machine)
                                                <option value="{{ $machine->id }}" {{ $batch->production_line_id == $machine->id ? 'selected' : '' }}>
                                                    {{ $machine->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Target Dose
                                            (kGy)</label>
                                        <input type="number" step="0.0001" name="target_dose" value="{{ $batch->target_dose }}"
                                            placeholder="0.0000"
                                            class="w-full px-3 py-2.5 text-xs font-bold border-none bg-white rounded-xl focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Beam Speed
                                            (m/s)</label>
                                        <input type="number" step="0.0001" name="beam_speed" value="{{ $batch->beam_speed }}"
                                            placeholder="0.0000"
                                            class="w-full px-3 py-2.5 text-xs font-bold border-none bg-white rounded-xl focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <div>
                                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Loading
                                            Mode</label>
                                        <input type="text" name="loading_mode" value="{{ $batch->loading_mode }}" placeholder="-"
                                            class="w-full px-3 py-2.5 text-xs font-bold border-none bg-white rounded-xl focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <button type="submit"
                                        class="px-4 py-2.5 text-[10px] font-black text-white uppercase bg-blue-600 rounded-xl hover:bg-blue-700 transition-all active:scale-95">
                                        <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <p class="text-sm text-slate-400 italic">Belum ada batch. Buat batch terlebih dahulu di menu
                                    <strong>Batch Queue</strong>.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-16 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                        <i class="text-3xl fa-solid fa-sliders text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-600">Belum Ada Booking Aktif</h3>
                    <p class="text-sm text-slate-400">Booking dengan status Approved atau Processing akan muncul di sini.</p>
                </div>
            </div>
        @endforelse
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

@endsection

@push('scripts')
    <script>     function openEditMachineModal(id, name) {         document.getElementById('editMachineName').value = name;         document.getElementById('editMachineForm').action = `/admin/production-lines/${id}`;         document.getElementById('editMachineModal').classList.replace('hidden', 'flex');     }
    </script>
@endpush