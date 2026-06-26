@extends('admin.layout.app')

@section('title', 'Production Dashboard')

@section('content')

    <div class="w-full pb-10 space-y-8">

        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Production Dashboard</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Monitor order progress, track batch milestones, and
                    oversee the execution flow.</p>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-100 shadow-sm rounded-2xl">
                <i class="text-blue-600 fa-regular fa-calendar"></i>
                <span class="text-sm font-bold text-gray-600">{{ now()->format('d F Y') }}</span>
            </div>
        </div>

        {{-- ═══ E-WORKFLOW GUIDE SECTION ═══ --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] p-6 md:p-8 space-y-6">
            <div>
                <h3 class="text-base font-black tracking-wider uppercase text-slate-800">
                    <i class="mr-2 text-indigo-500 fa-solid fa-route"></i>Standard Operating Procedure (SOP) Flow
                </h3>
                <p class="mt-1 text-xs text-slate-400">Follow these sequential steps in the sidebar navigation to process
                    incoming consignments:</p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="relative p-4 overflow-hidden border rounded-2xl bg-slate-50 border-slate-100 group">
                    <div
                        class="absolute text-4xl font-black transition-colors select-none right-3 top-2 text-slate-100 group-hover:text-slate-200/50">
                        01</div>
                    <span
                        class="px-2 py-0.5 text-[9px] font-black tracking-wider uppercase rounded-md bg-purple-100 text-purple-700">Step
                        1</span>
                    <h4 class="mt-2 text-xs font-black text-slate-800">1. Process Parameter</h4>
                    <p class="mt-1 text-[11px] font-medium leading-relaxed text-slate-500">
                        Configure baseline controls including production line allocation, target dose ($kGy$), beam speed
                        ($m/s$), loading mode, and frequency ($Hz$).
                    </p>
                </div>

                <div class="relative p-4 overflow-hidden border rounded-2xl bg-slate-50 border-slate-100 group">
                    <div
                        class="absolute text-4xl font-black transition-colors select-none right-3 top-2 text-slate-100 group-hover:text-slate-200/50">
                        02</div>
                    <span
                        class="px-2 py-0.5 text-[9px] font-black tracking-wider uppercase rounded-md bg-amber-100 text-amber-700">Step
                        2</span>
                    <h4 class="mt-2 text-xs font-black text-slate-800">2. Queue Task</h4>
                    <p class="mt-1 text-[11px] font-medium leading-relaxed text-slate-500">
                        The holding bay queue. Review pending jobs and click <strong class="text-blue-600">"Start
                            Irradiation"</strong> once pallets are physically staged to dispatch them.
                    </p>
                </div>

                <div class="relative p-4 overflow-hidden border rounded-2xl bg-slate-50 border-slate-100 group">
                    <div
                        class="absolute text-4xl font-black transition-colors select-none right-3 top-2 text-slate-100 group-hover:text-slate-200/50">
                        03</div>
                    <span
                        class="px-2 py-0.5 text-[9px] font-black tracking-wider uppercase rounded-md bg-blue-100 text-blue-700">Step
                        3</span>
                    <h4 class="mt-2 text-xs font-black text-slate-800">3. In Irradiation</h4>
                    <p class="mt-1 text-[11px] font-medium leading-relaxed text-slate-500">
                        Live monitoring console. Lists all active runs currently executing inside the Electron Beam machine
                        room in real-time.
                    </p>
                </div>

                <div class="relative p-4 overflow-hidden border rounded-2xl bg-slate-50 border-slate-100 group">
                    <div
                        class="absolute text-4xl font-black transition-colors select-none right-3 top-2 text-slate-100 group-hover:text-slate-200/50">
                        04</div>
                    <span
                        class="px-2 py-0.5 text-[9px] font-black tracking-wider uppercase rounded-md bg-emerald-100 text-emerald-700">Step
                        4</span>
                    <h4 class="mt-2 text-xs font-black text-slate-800">4. Finish Irradiation</h4>
                    <p class="mt-1 text-[11px] font-medium leading-relaxed text-slate-500">
                        Conclude the run by clicking <strong class="text-emerald-600">"Finish"</strong>. Complete the
                        required Quality Assurance logs (actual dose metrics, indicator changes, and defects).
                    </p>
                </div>
            </div>
        </div>

        {{-- ═══ STATS CARDS ═══ --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            @php
                $statCards = [
                    [
                        'label' => 'Batches In Queue',
                        'count' => $stats['waiting'] ?? 0,
                        'color' => 'amber',
                        'icon' => 'fa-clock',
                    ],
                    [
                        'label' => 'On Progresss',
                        'count' => $stats['processing'] ?? 0,
                        'color' => 'blue',
                        'icon' => 'fa-radiation',
                    ],
                    [
                        'label' => 'Completed (QA Passed)',
                        'count' => $stats['done'] ?? 0,
                        'color' => 'emerald',
                        'icon' => 'fa-circle-check',
                    ],
                ];
            @endphp
            @foreach ($statCards as $stat)
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

        {{-- ═══ BOOKING CARDS (PURE ORDER STATUS TRACKING) ═══ --}}
        @forelse ($bookings as $booking)
            @php
                $product = $booking->products->first();
                $totalProductQty = $booking->products->sum('quantity') ?? 0;
                $unit = $product->unit ?? '';
            @endphp
            <div class="bg-white border border-slate-100 shadow-sm rounded-[2.5rem] overflow-hidden">

                {{-- Booking Card Main Info Row --}}
                <div class="flex flex-col gap-4 p-8 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex items-center justify-center w-12 h-12 font-black text-blue-700 bg-blue-50 rounded-2xl">
                            {{ strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-lg font-black text-slate-800">
                                {{ $booking->customer->contacts->first()->name ?? 'Guest Client' }}
                            </p>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="px-3 py-1 font-mono text-xs font-bold rounded-lg bg-slate-100 text-slate-600">
                                    #{{ $booking->booking_code }}
                                </span>
                                @php
                                    $statusColors = [
                                        'approved' => 'bg-amber-100 text-amber-700',
                                        'processing' => 'bg-blue-100 text-blue-700',
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                    ];
                                @endphp
                                <span
                                    class="px-3 py-1 text-xs font-black uppercase rounded-lg {{ $statusColors[$booking->status] ?? 'bg-slate-100 text-slate-600' }}">
                                    @if ($booking->status === 'approved')
                                        Batches In Queue
                                    @elseif($booking->status === 'processing')
                                        On Progress
                                    @elseif($booking->status === 'completed')
                                        Completed
                                    @else
                                        {{ $booking->status }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Product Metadata & Milestone Status Tracking --}}
                    <div class="flex flex-wrap items-center gap-6 pr-2 md:gap-8">
                        <div class="text-left md:text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Product Description
                            </p>
                            <p class="text-sm font-bold text-slate-700">{{ $product->product_name ?? '-' }}</p>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Total Mass/Qty</p>
                            <p class="text-sm font-bold text-slate-700">
                                {{ $totalProductQty }} {{ $unit }}
                            </p>
                        </div>
                        <div class="text-left md:text-right">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Inward Date</p>
                            <p class="text-sm font-bold text-slate-700">
                                {{ $booking->created_at ? $booking->created_at->format('d/m/Y') : '-' }}
                            </p>
                        </div>

                        {{-- Visual Indicator Based on Status --}}
                        <div class="flex items-center pl-2 border-l border-slate-100">
                            @if ($booking->status === 'approved')
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-black tracking-wider text-amber-600 uppercase bg-amber-50 rounded-xl">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Batches In Queue
                                </span>
                            @elseif($booking->status === 'processing')
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-black tracking-wider text-blue-600 uppercase bg-blue-50 rounded-xl">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    On Progress
                                </span>
                            @else
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-[11px] font-black tracking-wider text-emerald-600 uppercase bg-emerald-50 rounded-xl">
                                    <i class="fa-solid fa-circle-check text-[10px]"></i>
                                    Completed (QA Passed)
                                </span>
                            @endif
                        </div>
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
                    <h3 class="text-xl font-black text-slate-600">No Active Orders Found</h3>
                    <p class="max-w-md text-sm text-slate-400">
                        Consignments with an <strong>Approved</strong> or <strong>Processing</strong> lifecycle status will
                        appear here for oversight.
                    </p>
                </div>
            </div>
        @endforelse

    </div>

@endsection
