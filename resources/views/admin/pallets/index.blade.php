@extends('admin.layout.app')
@section('title', 'Pallet Control System')

@section('content')
    <div class="w-full pb-20 space-y-8" x-data="{ activeLine: 1, searchQuery: '', showConfig: false }">

        {{-- ═══ TOP BAR: BRANDING & SEARCH ═══ --}}
        <div class="flex flex-col gap-4 pb-6 border-b lg:flex-row lg:items-center lg:justify-between border-slate-100">
            <div>
                <nav class="flex mb-2 text-[10px] font-bold tracking-widest text-slate-400 uppercase gap-2">
                    <span>Logistics</span>
                    <span>&middot;</span>
                    <span class="text-blue-600">Inventory Management</span>
                </nav>
                <h2 class="text-2xl font-black tracking-tight text-slate-800 md:text-3xl">
                    Pallet <span class="text-blue-600">Control Center</span>
                </h2>
            </div>

            {{-- LIVE SEARCH & CONFIG TOGGLE --}}
            <div class="flex items-center w-full gap-3 lg:w-auto">
                <div class="relative flex-grow lg:w-72">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <i class="text-xs fa-solid fa-magnifying-glass text-slate-400"></i>
                    </span>
                    <input x-model="searchQuery" type="text" placeholder="Filter product or booking code..."
                        class="w-full py-2 pr-4 text-xs font-medium transition-all bg-white border shadow-sm pl-9 border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:outline-none">
                </div>

                <button @click="showConfig = !showConfig"
                    :class="showConfig ? 'bg-slate-800 text-white' :
                        'bg-white text-slate-700 border border-slate-200 hover:bg-slate-50'"
                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold transition-all shadow-sm rounded-xl whitespace-nowrap">
                    <i class="fa-solid fa-sliders text-[11px]"></i>
                    <span>Layout Settings</span>
                </button>
            </div>
        </div>

        {{-- ═══ COLLAPSIBLE CONFIGURATION SUITE ═══ --}}
        <div x-show="showConfig" x-cloak x-collapse
            class="grid grid-cols-1 gap-6 p-6 border bg-slate-50/50 border-slate-200/60 rounded-2xl md:grid-cols-3">
            {{-- Form 1: Add/Modify Single Line --}}
            <div class="p-4 bg-white border shadow-sm border-slate-100 rounded-xl">
                <h4 class="text-xs font-black uppercase text-slate-700 mb-3 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Create / Update Line
                </h4>
                <form action="{{ route('admin.pallets.add-layout') }}" method="POST" class="flex items-end gap-2">
                    @csrf
                    <div class="flex-grow">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Line No.</label>
                        <input type="number" name="line_number" placeholder="e.g., 1" required
                            class="w-full px-3 py-1.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="flex-grow">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Total Slots</label>
                        <input type="number" name="slot_count" placeholder="e.g., 10" required
                            class="w-full px-3 py-1.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <button type="submit"
                        class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors">Apply</button>
                </form>
            </div>

            {{-- Form 2: Batch Generator --}}
            <div class="p-4 bg-white border shadow-sm border-slate-100 rounded-xl">
                <h4 class="text-xs font-black uppercase text-slate-700 mb-3 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Batch Auto-Generator
                </h4>
                <form action="{{ route('admin.pallets.generate') }}" method="POST" class="flex items-end gap-2">
                    @csrf
                    <div class="flex-grow">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Lines Qty</label>
                        <input type="number" name="lines" value="2"
                            class="w-full px-3 py-1.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <div class="flex-grow">
                        <label class="block text-[9px] font-bold text-slate-400 uppercase mb-1">Slots/Line</label>
                        <input type="number" name="slots" value="5"
                            class="w-full px-3 py-1.5 text-xs font-semibold bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                    </div>
                    <button type="submit"
                        class="px-3 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Generate</button>
                </form>
            </div>

            {{-- Form 3: Danger & Maintenance --}}
            <div class="flex flex-col justify-between p-4 bg-white border shadow-sm border-slate-100 rounded-xl">
                <div>
                    <h4 class="text-xs font-black uppercase text-rose-700 mb-1 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Danger Zone
                    </h4>
                    <p class="text-[10px] text-slate-400 leading-tight mb-3">Deletes the current selected line structure if
                        there are no items staged inside.</p>
                </div>
                <form :action="'/admin/pallets/line/' + activeLine" method="POST"
                    onsubmit="return confirm('Wipe entire Line? This action cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full py-1.5 text-xs font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-600 hover:text-white rounded-lg transition-all text-center">
                        <i class="fa-solid fa-trash-can mr-1.5 text-[10px]"></i> Wipe Active Line <span
                            x-text="activeLine"></span>
                    </button>
                </form>
            </div>
        </div>

        @php
            $lines = \App\Models\Pallet::whereNotNull('line')->groupBy('line')->pluck('line')->sort();
        @endphp

        {{-- ═══ LINE CONTROLLER NAV TABS ═══ --}}
        @if ($lines->count() > 0)
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl w-max max-w-full overflow-x-auto">
                @foreach ($lines as $lineNav)
                    <button @click="activeLine = {{ $lineNav }}"
                        :class="activeLine === {{ $lineNav }} ? 'bg-white text-slate-900 shadow-sm font-bold' :
                            'text-slate-500 hover:text-slate-800 font-medium'"
                        class="px-5 py-2 text-xs tracking-tight transition-all duration-150 rounded-lg whitespace-nowrap">
                        Bay Line {{ $lineNav }}
                    </button>
                @endforeach
            </div>
        @endif

        {{-- ═══ WAREHOUSE VISUAL MATRIX MAP ═══ --}}
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200/70 rounded-2xl">
            @forelse ($lines as $line)
                <div x-show="activeLine === {{ $line }}" x-transition:enter="transition ease-out duration-200"
                    class="p-6">

                    {{-- Grid Layout Matrix --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                        @php
                            $slotsInLine = \App\Models\Pallet::where('line', $line)
                                ->groupBy('slot_section')
                                ->pluck('slot_section')
                                ->sort();
                        @endphp

                        @foreach ($slotsInLine as $slot)
                            @php
                                $palletMaster = \App\Models\Pallet::where('line', $line)
                                    ->where('slot_section', $slot)
                                    ->first();
                                $contents = $palletMaster ? $palletMaster->contents : collect();
                                $totalBoxes = $contents->sum('quantity');
                                $searchData = $contents
                                    ->map(function ($c) {
                                        return strtolower($c->product_name . ' ' . $c->booking->booking_code);
                                    })
                                    ->implode(' ');
                            @endphp

                            <div x-show="searchQuery === '' || '{{ $searchData }}'.includes(searchQuery.toLowerCase())"
                                class="group flex flex-col justify-between border rounded-xl p-4 transition-all duration-200 min-h-[11rem]
                                 {{ $totalBoxes > 0 ? 'bg-gradient-to-b from-blue-50/10 to-white border-blue-200 shadow-sm hover:border-blue-400' : 'bg-slate-50/40 border-slate-200 border-dashed hover:border-slate-300' }}">

                                {{-- Slot Identifier Badge --}}
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100/70">
                                    <span
                                        class="text-[10px] font-bold px-2 py-0.5 rounded {{ $totalBoxes > 0 ? 'bg-blue-100 text-blue-700' : 'bg-slate-200 text-slate-600' }} tracking-wide">
                                        SLOT {{ $slot }}
                                    </span>
                                    <div class="text-right">
                                        <span
                                            class="text-base font-extrabold {{ $totalBoxes > 0 ? 'text-blue-600' : 'text-slate-400' }}">{{ $totalBoxes }}</span>
                                        <span class="text-[9px] font-medium text-slate-400 uppercase">ctn</span>
                                    </div>
                                </div>

                                {{-- Staged SKU List --}}
                                <div class="my-3 flex-grow overflow-y-auto max-h-20 space-y-1.5 pr-1 no-scrollbar">
                                    @forelse ($contents as $item)
                                        <div class="p-2 bg-white border rounded-lg border-slate-100 shadow-2xs">
                                            <p class="text-[11px] font-bold text-slate-800 truncate uppercase"
                                                title="{{ $item->product_name }}">
                                                {{ $item->product_name }}
                                            </p>
                                            <div
                                                class="flex items-center justify-between text-[9px] font-medium text-slate-400 mt-0.5">
                                                <span class="font-mono">#{{ $item->booking->booking_code }}</span>
                                                <span
                                                    class="px-1 font-bold rounded text-slate-600 bg-slate-50">{{ $item->quantity }}
                                                    ctn</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div
                                            class="flex flex-col items-center justify-center h-full py-4 text-center text-slate-300">
                                            <i class="text-[10px] fa-solid fa-box-open mb-1"></i>
                                            <span class="text-[9px] font-semibold tracking-wider uppercase">Empty
                                                Slot</span>
                                        </div>
                                    @endforelse
                                </div>

                                {{-- Slot Bottom Controls --}}
                                <div
                                    class="pt-2 border-t border-slate-100/70 flex items-center justify-between text-[10px]">
                                    <span class="text-[10px] font-medium text-slate-400">
                                        Status: <strong
                                            class="{{ $totalBoxes > 0 ? 'text-blue-600' : 'text-slate-500' }}">{{ $totalBoxes > 0 ? 'Staged' : 'Ready' }}</strong>
                                    </span>
                                    @if ($totalBoxes == 0)
                                        <form action="{{ route('admin.pallets.destroy', $palletMaster->id ?? 0) }}"
                                            method="POST" onsubmit="return confirm('Delete this slot?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="font-bold transition-colors text-slate-400 hover:text-rose-600">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @empty
                    <div class="flex flex-col items-center justify-center p-16 text-center">
                        <div class="flex items-center justify-center w-12 h-12 mb-3 rounded-full bg-slate-100 text-slate-400">
                            <i class="text-lg fa-solid fa-map-location-dot"></i>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800">No Inventory Footprint Configured</h4>
                        <p class="max-w-xs mt-1 text-xs text-slate-400">Click on 'Layout Settings' to initialize warehouse
                            allocations.</p>
                    </div>
                @endforelse
            </div>

            {{-- ═══ ANALYTICS / METRIC SUMMARY TABLES ═══ --}}
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

                {{-- SKU DISTRIBUTION SUMMARY --}}
                <div class="p-6 bg-white border shadow-sm border-slate-200/70 rounded-2xl">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-50">
                        <div>
                            <h3 class="text-sm font-black tracking-wider uppercase text-slate-800">Product Allocation</h3>
                            <p class="text-[11px] text-slate-400 mt-0.5">Summary of SKU locations and current volumes</p>
                        </div>
                        <i class="text-sm text-slate-300 fa-solid fa-boxes-stacked"></i>
                    </div>

                    <div class="overflow-x-auto max-h-[18rem] no-scrollbar">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr class="text-[10px] font-bold text-slate-400 uppercase border-b border-slate-100">
                                    <th class="pb-2 pl-2">Product Details</th>
                                    <th class="pb-2">Zone Locations</th>
                                    <th class="pb-2 pr-2 text-right">Volume</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $productSummaries = \App\Models\PalletContent::with('pallet')
                                        ->get()
                                        ->groupBy('product_name');
                                @endphp
                                @forelse ($productSummaries as $productName => $items)
                                    <tr class="transition-colors hover:bg-slate-50/50">
                                        <td class="py-3 pl-2">
                                            <p class="font-bold uppercase text-slate-800">{{ $productName }}</p>
                                            <p class="text-[10px] text-slate-400">{{ $items->count() }} active zones</p>
                                        </td>
                                        <td class="py-3">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($items->groupBy('pallet_id') as $pId => $gItems)
                                                    @php $p = $gItems->first()->pallet; @endphp
                                                    @if ($p)
                                                        <span
                                                            class="px-1.5 py-0.5 text-[9px] font-mono font-bold bg-slate-100 text-slate-700 rounded">L{{ $p->line }}-S{{ $p->slot_section }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-3 pr-2 font-extrabold text-right text-slate-900">
                                            {{ $items->sum('quantity') }} <span
                                                class="text-[9px] font-normal text-slate-400 uppercase">ctn</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="py-8 text-center text-[10px] text-slate-300 font-bold uppercase tracking-wider">
                                            No stock data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- POSITION INTEGRITY LOG --}}
                <div class="p-6 bg-white border shadow-sm border-slate-200/70 rounded-2xl" x-data="{ summaryFilter: 'all' }">
                    <div
                        class="flex flex-col gap-3 pb-3 mb-4 border-b sm:flex-row sm:items-center sm:justify-between border-slate-50">
                        <h3 class="text-sm font-black tracking-wider uppercase text-slate-800">Position Matrix Log</h3>
                        <div class="flex p-0.5 bg-slate-100 rounded-lg max-w-full overflow-x-auto">
                            <button @click="summaryFilter = 'all'"
                                :class="summaryFilter === 'all' ? 'bg-white shadow-xs text-slate-900 font-bold' :
                                    'text-slate-400'"
                                class="px-2.5 py-1 text-[9px] uppercase rounded-md transition-all">All</button>
                            @foreach ($lines as $lNum)
                                <button @click="summaryFilter = '{{ $lNum }}'"
                                    :class="summaryFilter === '{{ $lNum }}' ?
                                        'bg-white shadow-xs text-slate-900 font-bold' : 'text-slate-400'"
                                    class="px-2.5 py-1 text-[9px] uppercase rounded-md transition-all whitespace-nowrap">Line
                                    {{ $lNum }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="overflow-x-auto max-h-[18rem] no-scrollbar">
                        <table class="w-full text-xs text-left">
                            <thead>
                                <tr class="text-[10px] font-bold text-slate-400 uppercase border-b border-slate-100">
                                    <th class="pb-2 pl-2">Location ID</th>
                                    <th class="pb-2">Staged SKUs</th>
                                    <th class="pb-2 pr-2 text-right">Load</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @php
                                    $allLocations = \App\Models\Pallet::orderBy('line')->orderBy('slot_section')->get();
                                @endphp
                                @foreach ($allLocations as $loc)
                                    @php
                                        $contents = \App\Models\PalletContent::where('pallet_id', $loc->id)->get();
                                        $totalBoxes = $contents->sum('quantity');
                                    @endphp
                                    <tr x-show="summaryFilter === 'all' || summaryFilter === '{{ $loc->line }}'"
                                        class="transition-colors hover:bg-slate-50/50">
                                        <td class="py-3 pl-2 font-mono font-bold text-slate-700">
                                            L{{ $loc->line }}-S{{ $loc->slot_section }}
                                        </td>
                                        <td class="py-3">
                                            @if ($contents->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($contents as $item)
                                                        <span
                                                            class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-700 rounded border border-blue-100/40">{{ $item->product_name }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-[10px] text-slate-300 font-medium italic">Vacant</span>
                                            @endif
                                        </td>
                                        <td
                                            class="py-3 pr-2 text-right font-bold {{ $totalBoxes > 0 ? 'text-blue-600' : 'text-slate-300' }}">
                                            {{ $totalBoxes ?: '0' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    @endsection
