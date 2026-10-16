@extends('admin.layout.app')

@section('title', 'Batch Queue')

@section('content')

    {{-- Root wrapper dengan Alpine.js untuk kontrol modal --}}
    <div class="w-full pb-10 space-y-6 md:space-y-8" >

        {{-- HEADER --}}
        <div class="flex flex-col gap-4 px-2 md:flex-row md:items-center md:justify-between">

            <div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 mb-3 text-xs font-semibold tracking-widest text-purple-600 uppercase border border-purple-100 rounded-full bg-purple-50">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                    Step 2 of 4
                </div>
                <h2 class="text-3xl font-black tracking-tighter md:text-4xl text-slate-800">Batch Queue</h2>
                <p class="mt-1 text-xs font-medium md:text-sm text-slate-500">Batches ready to start</p>
            </div>
        </div>

        {{-- ═══ BOOKING CARDS ═══ --}}
        <div class="grid grid-cols-1 gap-6">
            @forelse ($bookings as $booking)
                @php
                    $product = $booking->products->first();
                    $totalProductQty = $booking->products->sum('quantity');
                    $totalBatchQty = $booking->batches->sum('quantity');
                    $remaining = $totalProductQty - $totalBatchQty;
                    $pct = $totalProductQty > 0 ? round(($totalBatchQty / $totalProductQty) * 100) : 0;
                @endphp
                <div class="bg-white border border-slate-100 shadow-sm rounded-[2rem] md:rounded-[2.5rem] overflow-hidden transition-all"
                    x-data="{ expanded: true }">

                    {{-- Booking Header --}}
                    <div class="flex flex-col gap-6 p-6 cursor-pointer md:p-8 lg:flex-row lg:items-center lg:justify-between hover:bg-slate-50/50"
                        @click="expanded = !expanded">

                        <div class="flex items-center gap-4">
                            <div
                                class="flex items-center justify-center flex-shrink-0 w-12 h-12 text-lg font-black text-blue-700 bg-blue-50 md:w-14 md:h-14 rounded-2xl">
                                {{ strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-base font-black truncate md:text-lg text-slate-800">
                                    {{ $booking->customer->contacts->first()->name ?? 'Guest' }}
                                </p>
                                <span
                                    class="inline-block px-2 py-1 mt-1 font-mono text-[10px] font-bold rounded-lg bg-slate-100 text-slate-500">
                                    #{{ $booking->booking_code }}
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 md:gap-8 lg:justify-end">
                            <div class="flex-1 min-w-[100px] lg:flex-none lg:text-right">
                                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    Product</p>
                                <p class="text-xs md:text-sm font-bold text-slate-700 truncate max-w-[150px]">
                                    {{ $product->product_name ?? '-' }}</p>
                            </div>
                            <div class="flex-1 min-w-[80px] lg:flex-none lg:text-right">
                                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    Total Qty</p>
                                <p class="text-xs font-bold md:text-sm text-slate-700">{{ $totalProductQty }}
                                    {{ $product->unit ?? '' }}</p>
                            </div>
                            <div class="flex-shrink-0 lg:text-right">
                                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                    Batch</p>
                                <p class="text-xs font-bold md:text-sm text-slate-700">{{ $booking->batches->count() }}</p>
                            </div>
                            <div class="flex items-center justify-center w-8 h-8 ml-2 rounded-full bg-slate-50 lg:ml-4">
                                <i class="text-xs transition-transform duration-300 fa-solid fa-chevron-down text-slate-400"
                                    :class="expanded ? 'rotate-180' : ''"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Expanded Content --}}
                    <div x-cloak x-show="expanded" x-transition.opacity.duration.150ms>
                        <div class="px-6 pb-6 space-y-4 md:px-8 md:pb-8">

                            <div class="p-5 border bg-slate-50/50 border-slate-100 rounded-[1.5rem] md:rounded-2xl">
                                <div class="flex flex-col gap-2 mb-3 sm:flex-row sm:items-center sm:justify-between">
                                    <span
                                        class="text-[10px] md:text-xs font-black text-slate-500 uppercase tracking-widest">Kapasitas
                                        Batching</span>
                                    <span class="text-[10px] md:text-xs font-bold text-slate-400">
                                        {{ $totalBatchQty }} / {{ $totalProductQty }} {{ $product->unit ?? '' }}
                                        ({{ $pct }}%)
                                    </span>
                                </div>
                                <progress value="{{ $pct }}" max="100"
                                    class="h-2.5 w-full overflow-hidden rounded-full bg-slate-200 accent-blue-500"></progress>
                            </div>

                            <div class="grid grid-cols-1 gap-3">
                                @foreach ($booking->batches as $batch)
                                    @if ($batch->status === 'waiting')
                                        <div
                                            class="p-4 bg-white border border-amber-100 rounded-[1.25rem] md:rounded-[1.5rem] flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                                            <div class="flex items-center gap-4">
                                                <div
                                                    class="flex items-center justify-center flex-shrink-0 w-10 h-10 md:w-11 md:h-11 rounded-xl bg-amber-50">
                                                    <i class="text-amber-500 fa-solid fa-clock"></i>
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="block text-sm font-black text-slate-800">Batch
                                                        #{{ $batch->batch_number }}</span>
                                                    <div
                                                        class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px] font-bold text-slate-400">
                                                        <span>{{ \App\Support\NumberFormatter::integer($batch->quantity) }} {{ $batch->unit }}</span>
                                                        @if ($batch->productionLine)
                                                            <span class="text-slate-300">•</span>
                                                            <span
                                                                class="text-blue-500">{{ $batch->productionLine->name }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div
                                                class="flex items-center justify-between gap-3 pt-3 border-t sm:pt-0 sm:border-t-0 sm:justify-end">
                                                <span
                                                    class="px-2.5 py-1 text-[9px] font-black text-amber-600 uppercase bg-amber-50/50 border border-amber-100 rounded-lg tracking-wider">
                                                    Waiting
                                                </span>

                                                {{-- Trigger Modal Konfirmasi --}}
                                                <a href="{{ route('admin.workspace.production-board', ['search' => $batch->booking?->booking_code]) }}"
                                                    class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 text-[10px] font-black uppercase bg-blue-600 text-white rounded-xl hover:bg-blue-700 active:scale-95 shadow-lg shadow-blue-100 transition-all">
                                                    <i class="fa-solid fa-play text-[8px]"></i>
                                                    <span>Open Production Center</span>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-10 md:p-20 text-center">
                    <div class="flex flex-col items-center max-w-xs gap-4 mx-auto">
                        <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-50 text-slate-200">
                            <i class="text-4xl fa-solid fa-layer-group"></i>
                        </div>
                        <h3 class="text-xl font-black text-slate-700">No Queue Found</h3>
                    </div>
                </div>
            @endforelse
        </div>


    </div>

@endsection
