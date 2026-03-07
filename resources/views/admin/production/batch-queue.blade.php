@extends('admin.layout.app')

@section('title', 'Batch Queue')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Batch Queue</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Pecah quantity booking menjadi batch-batch, lalu mulai proses penyinaran.</p>
            </div>
        </div>

        {{-- ═══ BOOKING CARDS ═══ --}}
        @forelse ($bookings as $booking)
            @php
                $product = $booking->products->first();
                $totalProductQty = $booking->products->sum('quantity');
                $totalBatchQty = $booking->batches->sum('quantity');
                $remaining = $totalProductQty - $totalBatchQty;
                $pct = $totalProductQty > 0 ? round(($totalBatchQty / $totalProductQty) * 100) : 0;
            @endphp
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
                            <p class="text-sm font-bold text-slate-700">{{ $totalProductQty }} {{ $product->unit ?? '' }}</p>
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
                    <div class="px-8 pb-8 space-y-4">

                        {{-- Batch Progress Bar --}}
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
                            @elseif ($remaining == 0 && $totalBatchQty > 0)
                                <p class="mt-2 text-xs font-bold text-emerald-600">
                                    <i class="fa-solid fa-check-circle mr-1"></i>
                                    Semua quantity sudah terbagi ke batch.
                                </p>
                            @endif
                        </div>

                        {{-- Existing Batches --}}
                        @foreach ($booking->batches as $batch)
                            <div class="p-5 bg-white border rounded-[1.5rem] flex flex-col md:flex-row md:items-center md:justify-between gap-4
                                {{ $batch->status === 'processing' ? 'border-blue-200' : ($batch->status === 'done' ? 'border-emerald-200' : 'border-slate-100') }}">
                                <div class="flex items-center gap-3">
                                    @php
                                        $icons = ['waiting' => 'fa-clock text-amber-500', 'processing' => 'fa-spinner fa-spin text-blue-500', 'done' => 'fa-check-circle text-emerald-500'];
                                        $bgs = ['waiting' => 'bg-amber-50', 'processing' => 'bg-blue-50', 'done' => 'bg-emerald-50'];
                                    @endphp
                                    <div class="flex items-center justify-center w-9 h-9 rounded-xl {{ $bgs[$batch->status] ?? 'bg-slate-50' }}">
                                        <i class="fa-solid {{ $icons[$batch->status] ?? 'fa-question text-slate-400' }}"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm font-black text-slate-800">Batch #{{ $batch->batch_number }}</span>
                                        <p class="text-xs text-slate-400">{{ $batch->quantity }} {{ $batch->unit }}
                                            @if($batch->productionLine)
                                                · {{ $batch->productionLine->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if ($batch->status === 'waiting')
                                        <span class="px-3 py-1.5 text-[10px] font-black text-amber-700 uppercase bg-amber-50 rounded-lg">Waiting</span>
                                        <form action="{{ route('admin.production.batches.start', $batch->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <button type="submit"
                                                class="flex items-center gap-2 px-4 py-2 text-[10px] font-black uppercase bg-blue-600 text-white rounded-xl hover:bg-blue-700 active:scale-95 transition-all">
                                                <i class="fa-solid fa-play"></i> Start Irradiation
                                            </button>
                                        </form>
                                    @elseif ($batch->status === 'processing')
                                        <span class="px-3 py-1.5 text-[10px] font-black text-blue-700 uppercase bg-blue-50 rounded-lg">
                                            <i class="fa-solid fa-spinner fa-spin mr-1"></i> In Irradiation
                                        </span>
                                    @else
                                        <span class="px-3 py-1.5 text-[10px] font-black text-emerald-700 uppercase bg-emerald-50 rounded-lg">
                                            <i class="fa-solid fa-check mr-1"></i> Done
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        {{-- Add New Batch Form --}}
                        @if ($remaining > 0)
                            <div class="p-6 border-2 border-dashed border-slate-200 rounded-[2rem] bg-slate-50/50">
                                <h4 class="mb-4 text-sm font-black text-slate-700">
                                    <i class="fa-solid fa-plus mr-2 text-blue-600"></i>Tambah Batch
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
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-16 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                        <i class="text-3xl fa-solid fa-layer-group text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-600">Belum Ada Booking Aktif</h3>
                    <p class="text-sm text-slate-400">Booking dengan status Approved atau Processing akan muncul di sini.</p>
                </div>
            </div>
        @endforelse
    </div>

@endsection
