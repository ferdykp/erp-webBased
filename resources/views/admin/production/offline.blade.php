@extends('admin.layout.app')

@section('title', 'Offline / Finish')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Offline / Finish</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Tandai batch yang sudah selesai proses penyinaran.</p>
            </div>
        </div>

        {{-- ═══ BOOKING CARDS ═══ --}}
        @forelse ($bookings as $booking)
            @php $product = $booking->products->first(); @endphp
            @php $processingBatches = $booking->batches->where('status', 'processing'); @endphp

            @if($processingBatches->count() > 0)
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
                                <p class="text-[10px] font-black text-slate-400 uppercase">In Irradiation</p>
                                <p class="text-sm font-bold text-blue-600">{{ $processingBatches->count() }} batch</p>
                            </div>
                            <i class="fa-solid fa-chevron-down text-slate-400 transition-transform duration-200"
                                :class="expanded ? 'rotate-180' : ''"></i>
                        </div>
                    </div>

                    {{-- Batch List --}}
                    <div x-show="expanded" x-cloak x-collapse>
                        <div class="px-8 pb-8 space-y-4">
                            @foreach ($processingBatches as $batch)
                                <div
                                    class="p-5 bg-blue-50/50 border border-blue-100 rounded-[1.5rem] flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex items-center justify-center w-9 h-9 bg-blue-100 rounded-xl">
                                            <i class="fa-solid fa-spinner fa-spin text-blue-600"></i>
                                        </div>
                                        <div>
                                            <span class="text-sm font-black text-slate-800">Batch #{{ $batch->batch_number }}</span>
                                            <p class="text-xs text-slate-400">{{ $batch->quantity }} {{ $batch->unit }}
                                                @if($batch->productionLine) · {{ $batch->productionLine->name }} @endif
                                                @if($batch->target_dose) · {{ $batch->target_dose }} kGy @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1.5 text-[10px] font-black text-blue-700 uppercase bg-blue-100 rounded-lg">
                                            In Irradiation
                                        </span>
                                        <form action="{{ route('admin.production.batches.finish', $batch->id) }}" method="POST"
                                            onsubmit="return confirm('Tandai Batch #{{ $batch->batch_number }} sebagai selesai?')">
                                            @csrf @method('PUT')
                                            <button type="submit"
                                                class="flex items-center gap-2 px-4 py-2 text-[10px] font-black uppercase bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 active:scale-95 transition-all">
                                                <i class="fa-solid fa-check-double"></i> Offline / Finish
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Show done batches (info only) --}}
                            @php $doneBatches = $booking->batches->where('status', 'done'); @endphp
                            @if($doneBatches->count() > 0)
                                <div class="pt-4 border-t border-slate-100">
                                    <p class="mb-3 text-[10px] font-black text-slate-400 uppercase">Sudah Selesai</p>
                                    @foreach ($doneBatches as $doneBatch)
                                        <div class="flex items-center gap-3 px-4 py-2">
                                            <i class="fa-solid fa-check-circle text-emerald-500"></i>
                                            <span class="text-sm font-bold text-slate-500">Batch #{{ $doneBatch->batch_number }} —
                                                {{ $doneBatch->quantity }} {{ $doneBatch->unit }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-16 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                        <i class="text-3xl fa-solid fa-flag-checkered text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-600">Tidak Ada Batch In Irradiation</h3>
                    <p class="text-sm text-slate-400">Batch yang sedang dalam proses penyinaran akan muncul di sini.</p>
                </div>
            </div>
        @endforelse

        {{-- Show empty state if all bookings have no processing batches --}}
        @if($bookings->count() > 0 && $bookings->every(fn($b) => $b->batches->where('status', 'processing')->count() === 0))
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-16 text-center">
                <div class="flex flex-col items-center gap-4">
                    <div class="flex items-center justify-center w-20 h-20 rounded-full bg-slate-100">
                        <i class="text-3xl fa-solid fa-flag-checkered text-slate-300"></i>
                    </div>
                    <h3 class="text-xl font-black text-slate-600">Tidak Ada Batch In Irradiation</h3>
                    <p class="text-sm text-slate-400">Mulai proses batch terlebih dahulu di menu <strong>Batch Queue</strong>.
                    </p>
                </div>
            </div>
        @endif
    </div>

@endsection