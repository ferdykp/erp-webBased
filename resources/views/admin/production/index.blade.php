@extends('admin.layout.app')

@section('title', 'Production Dashboard')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Production Dashboard</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Kelola proses penyinaran, parameter mesin, dan status batch.</p>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <i class="text-blue-600 fa-regular fa-calendar"></i>
                <span class="text-sm font-bold text-gray-600">{{ now()->format('d F Y') }}</span>
            </div>
        </div>

        {{-- ═══ STATS CARDS ═══ --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            @php
                $statCards = [
                    ['label' => 'Waiting', 'count' => $stats['waiting'], 'color' => 'amber', 'icon' => 'fa-clock'],
                    ['label' => 'In Irradiation', 'count' => $stats['processing'], 'color' => 'blue', 'icon' => 'fa-radiation'],
                    ['label' => 'Done', 'count' => $stats['done'], 'color' => 'emerald', 'icon' => 'fa-circle-check'],
                ];
            @endphp
            @foreach ($statCards as $stat)
                <div class="p-6 transition-all bg-white border border-gray-100 shadow-sm group rounded-3xl hover:shadow-xl hover:-translate-y-1">
                    <div class="p-2 w-10 h-10 flex items-center justify-center bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 rounded-xl group-hover:bg-{{ $stat['color'] }}-600 group-hover:text-white transition-colors">
                        <i class="fa-solid {{ $stat['icon'] }}"></i>
                    </div>
                    <h3 class="mt-4 text-xs font-bold tracking-widest text-gray-400 uppercase">{{ $stat['label'] }}</h3>
                    <p class="mt-1 text-3xl font-black text-gray-800">{{ $stat['count'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ═══ BOOKING CARDS ═══ --}}
        @forelse ($bookings as $booking)
            @php $product = $booking->products->first(); @endphp
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] overflow-hidden" x-data="{ expanded: true }">

                {{-- Booking Header --}}
                <div class="flex flex-col gap-4 p-8 cursor-pointer md:flex-row md:items-center md:justify-between hover:bg-slate-50/50"
                    @click="expanded = !expanded">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center justify-center w-12 h-12 font-black text-blue-700 bg-blue-50 rounded-2xl">
                            {{ strtoupper(substr($booking->customer->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-lg font-black text-slate-800">{{ $booking->customer->name ?? 'Guest' }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 font-mono text-xs font-bold rounded-lg">
                                    #{{ $booking->booking_code }}
                                </span>
                                @php
                                    $statusColors = [
                                        'approved' => 'bg-sky-100 text-sky-700',
                                        'processing' => 'bg-purple-100 text-purple-700',
                                    ];
                                @endphp
                                <span class="px-3 py-1 text-xs font-black uppercase rounded-lg {{ $statusColors[$booking->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $booking->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Produk</p>
                            <p class="text-sm font-bold text-slate-700">{{ $product->product_name ?? '-' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Total Qty</p>
                            <p class="text-sm font-bold text-slate-700">{{ $product->quantity ?? 0 }} {{ $product->unit ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase">Batch</p>
                            <p class="text-sm font-bold text-slate-700">{{ $booking->batches->count() }}</p>
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200"
                            :class="expanded ? 'rotate-180' : ''"></i>
                    </div>
                </div>

                {{-- Expanded Content --}}
                <div x-show="expanded" x-cloak x-collapse>
                    <div class="px-8 pb-8 space-y-6">

                        {{-- ── Batch Progress Bar ── --}}
                        @php
                            $totalProductQty = $booking->products->sum('quantity');
                            $totalBatchQty = $booking->batches->sum('quantity');
                            $remaining = $totalProductQty - $totalBatchQty;
                            $pct = $totalProductQty > 0 ? round(($totalBatchQty / $totalProductQty) * 100) : 0;
                        @endphp
                        <div class="p-4 border bg-slate-50 border-slate-100 rounded-2xl">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-black text-slate-500">Kapasitas Batch</span>
                                <span class="text-xs font-bold text-slate-400">{{ $totalBatchQty }} / {{ $totalProductQty }} {{ $product->unit ?? '' }} ({{ $pct }}%)</span>
                            </div>
                            <div class="w-full h-2 overflow-hidden bg-slate-200 rounded-full">
                                <div class="h-full transition-all bg-blue-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            @if ($remaining > 0)
                                <p class="mt-2 text-xs font-bold text-amber-600">
                                    <i class="fa-solid fa-circle-info mr-1"></i>
                                    Sisa {{ $remaining }} {{ $product->unit ?? '' }} belum terbagi ke batch.
                                </p>
                            @endif
                        </div>

                        {{-- ── Existing Batches ── --}}
                        @foreach ($booking->batches as $batch)
                            <div class="p-6 bg-white border-2 rounded-[2rem] {{ $batch->status === 'done' ? 'border-emerald-100' : ($batch->status === 'processing' ? 'border-blue-100' : 'border-slate-100') }}">

                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    {{-- Batch Info --}}
                                    <div class="flex items-center gap-4">
                                        @php
                                            $batchStatusIcon = ['waiting' => 'fa-clock text-amber-500', 'processing' => 'fa-radiation text-blue-500 animate-pulse', 'done' => 'fa-check-circle text-emerald-500'];
                                            $batchStatusBg = ['waiting' => 'bg-amber-50', 'processing' => 'bg-blue-50', 'done' => 'bg-emerald-50'];
                                        @endphp
                                        <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ $batchStatusBg[$batch->status] ?? 'bg-slate-50' }}">
                                            <i class="fa-solid {{ $batchStatusIcon[$batch->status] ?? 'fa-question text-slate-400' }}"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-black text-slate-800">Batch #{{ $batch->batch_number }}</span>
                                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-md
                                                    {{ $batch->status === 'done' ? 'bg-emerald-100 text-emerald-700' : ($batch->status === 'processing' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                                    {{ $batch->status }}
                                                </span>
                                            </div>
                                            <p class="text-xs font-bold text-slate-400">{{ $batch->quantity }} {{ $batch->unit }}</p>
                                        </div>
                                    </div>

                                    {{-- Status Actions --}}
                                    <div class="flex items-center gap-2">
                                        @if ($batch->status === 'waiting')
                                            <form action="{{ route('admin.production.batches.status', $batch->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="processing">
                                                <button type="submit"
                                                    class="flex items-center gap-2 px-4 py-2 text-[10px] font-black uppercase bg-blue-600 text-white rounded-xl hover:bg-blue-700 active:scale-95 transition-all">
                                                    <i class="fa-solid fa-play"></i> Start Irradiation
                                                </button>
                                            </form>
                                        @elseif ($batch->status === 'processing')
                                            <form action="{{ route('admin.production.batches.status', $batch->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="done">
                                                <button type="submit"
                                                    class="flex items-center gap-2 px-4 py-2 text-[10px] font-black uppercase bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 active:scale-95 transition-all">
                                                    <i class="fa-solid fa-check-double"></i> Finish
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-4 py-2 text-[10px] font-black text-emerald-600 uppercase bg-emerald-50 rounded-xl">
                                                <i class="fa-solid fa-check mr-1"></i> Completed
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Parameter Form --}}
                                <div class="mt-4 pt-4 border-t border-slate-50">
                                    <form action="{{ route('admin.production.batches.parameters', $batch->id) }}" method="POST"
                                        class="grid grid-cols-1 gap-4 md:grid-cols-5 items-end">
                                        @csrf
                                        @method('PUT')

                                        <div>
                                            <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Mesin</label>
                                            <select name="production_line_id"
                                                class="w-full px-3 py-2.5 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                                                <option value="">-- Pilih Mesin --</option>
                                                @foreach ($productionLines as $machine)
                                                    <option value="{{ $machine->id }}" {{ $batch->production_line_id == $machine->id ? 'selected' : '' }}>
                                                        {{ $machine->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Target Dose (kGy)</label>
                                            <input type="number" step="0.0001" name="target_dose" value="{{ $batch->target_dose }}"
                                                placeholder="0.0000"
                                                class="w-full px-3 py-2.5 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Beam Speed (m/min)</label>
                                            <input type="number" step="0.0001" name="beam_speed" value="{{ $batch->beam_speed }}"
                                                placeholder="0.0000"
                                                class="w-full px-3 py-2.5 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <div>
                                            <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">Loading Mode</label>
                                            <input type="text" name="loading_mode" value="{{ $batch->loading_mode }}"
                                                placeholder="e.g. single-side"
                                                class="w-full px-3 py-2.5 text-xs font-bold border-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                                        </div>

                                        <button type="submit"
                                            class="px-4 py-2.5 text-[10px] font-black text-white uppercase bg-slate-700 rounded-xl hover:bg-slate-800 transition-all active:scale-95">
                                            <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach

                        {{-- ── Add New Batch ── --}}
                        @if ($remaining > 0)
                            <div class="p-6 border-2 border-dashed border-slate-200 rounded-[2rem] bg-slate-50/50">
                                <h4 class="mb-4 text-sm font-black text-slate-700">
                                    <i class="fa-solid fa-plus mr-2 text-blue-600"></i>Tambah Batch Baru
                                </h4>
                                <form action="{{ route('admin.production.batches.store') }}" method="POST"
                                    class="flex flex-col gap-4 md:flex-row md:items-end">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                                    <div class="flex-1">
                                        <label class="block mb-1 text-[9px] font-black text-slate-400 uppercase">
                                            Quantity (maks. {{ $remaining }} {{ $product->unit ?? '' }})
                                        </label>
                                        <input type="number" name="quantity" required min="1" max="{{ $remaining }}"
                                            step="any" placeholder="Masukkan qty..."
                                            class="w-full px-4 py-3 text-sm font-bold border-none bg-white rounded-xl focus:ring-2 focus:ring-blue-500">
                                    </div>

                                    <button type="submit"
                                        class="px-6 py-3 text-xs font-black text-white uppercase bg-blue-600 rounded-xl hover:bg-blue-700 transition-all active:scale-95 shadow-lg shadow-blue-100">
                                        <i class="fa-solid fa-plus mr-1"></i> Buat Batch
                                    </button>
                                </form>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-16 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                        <i class="text-3xl fa-solid fa-radiation text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-600">Belum Ada Booking Aktif</h3>
                    <p class="text-sm text-slate-400 max-w-md">
                        Booking dengan status <strong>Approved</strong> atau <strong>Processing</strong> akan muncul di sini
                        untuk dikelola proses penyinarannya.
                    </p>
                </div>
            </div>
        @endforelse

    </div>

@endsection
