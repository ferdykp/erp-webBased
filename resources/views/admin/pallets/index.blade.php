@extends('admin.layout.app')
@section('title', 'Pallet Management')

@section('content')
    <div class="w-full pb-20 space-y-6 md:space-y-10">

        {{-- ═══ HEADER SECTION ═══ --}}
        <div class="flex flex-col gap-6 px-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-3xl font-black tracking-tighter md:text-5xl text-slate-800">
                    Pallet <span class="text-blue-600">Inventory</span>
                </h2>
                <p class="mt-2 text-xs font-bold uppercase tracking-[0.2em] text-slate-400">
                    Visualisasi Tata Letak: Line & Petak Gudang
                </p>
            </div>

            {{-- FORMS CONTAINER --}}
            <div class="flex flex-col gap-4 sm:flex-row lg:items-end">
                {{-- Form Tambah Line --}}
                <form action="{{ route('admin.pallets.add-layout') }}" method="POST"
                    class="flex flex-wrap items-end gap-3 p-4 bg-white border shadow-sm rounded-3xl border-slate-100">
                    @csrf
                    <div class="flex flex-col">
                        <label class="mb-1 text-[9px] font-black tracking-widest text-slate-400 uppercase">No. Line</label>
                        <input type="number" name="line_number" placeholder="0" required
                            class="w-16 px-3 py-2 text-sm font-bold text-center transition-all border-none outline-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-1 text-[9px] font-black tracking-widest text-slate-400 uppercase">Petak</label>
                        <input type="number" name="slot_count" placeholder="0" required
                            class="w-16 px-3 py-2 text-sm font-bold text-center transition-all border-none outline-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 text-[10px] font-black text-white bg-emerald-500 rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">
                        + LINE
                    </button>
                </form>

                {{-- Form Generate --}}
                <form action="{{ route('admin.pallets.generate') }}" method="POST"
                    class="flex flex-wrap items-end gap-3 p-4 bg-white border shadow-sm rounded-3xl border-slate-100">
                    @csrf
                    <div class="flex flex-col">
                        <label class="mb-1 text-[9px] font-black tracking-widest text-slate-400 uppercase">Lines</label>
                        <input type="number" name="lines" value="2"
                            class="w-16 px-3 py-2 text-sm font-bold text-center border-none outline-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-1 text-[9px] font-black tracking-widest text-slate-400 uppercase">Slots</label>
                        <input type="number" name="slots" value="5"
                            class="w-16 px-3 py-2 text-sm font-bold text-center border-none outline-none bg-slate-50 rounded-xl focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit"
                        class="px-4 py-2 text-[10px] font-black text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                        GENERATE
                    </button>
                </form>
            </div>
        </div>

        <div x-data="{ activeLine: 1 }" class="space-y-8">
            @php
                $lines = \App\Models\Pallet::whereNotNull('line')->groupBy('line')->pluck('line')->sort();
            @endphp

            {{-- ═══ TABS NAVIGATION (Responsive Scroll) ═══ --}}
            @if ($lines->count() > 0)
                <div
                    class="flex items-center gap-2 pb-2 overflow-x-auto no-scrollbar scroll-smooth whitespace-nowrap lg:justify-center">
                    @foreach ($lines as $lineNav)
                        <button @click="activeLine = {{ $lineNav }}"
                            :class="activeLine === {{ $lineNav }} ? 'bg-blue-600 text-white shadow-xl shadow-blue-100' :
                                'bg-white text-slate-400 hover:text-slate-600 border border-slate-100'"
                            class="px-8 py-3 text-xs font-black tracking-widest uppercase transition-all duration-300 rounded-2xl">
                            Line {{ $lineNav }}
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- ═══ VISUALISASI GUDANG ═══ --}}
            @forelse ($lines as $line)
                <div x-show="activeLine === {{ $line }}" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                    class="p-4 md:p-10 bg-white border border-slate-100 shadow-sm rounded-[2rem] md:rounded-[3.5rem]">

                    <div class="flex items-center gap-4 mb-8 md:mb-12">
                        <div
                            class="flex items-center justify-center w-12 h-12 text-white bg-blue-600 shadow-xl md:w-16 md:h-16 rounded-2xl md:rounded-3xl shadow-blue-100">
                            <i class="text-xl md:text-2xl fa-solid fa-warehouse"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-black tracking-tighter md:text-4xl text-slate-800">LINE
                                {{ $line }}</h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest italic">Warehouse
                                Occupancy Map</p>
                        </div>
                    </div>

                    {{-- GRID PETAK (Responsive: 1 col HP, 2 col Tab, 3 col Desktop) --}}
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 md:gap-8">
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
                            @endphp

                            <div class="relative transition-all duration-500 group hover:-translate-y-2">
                                <div
                                    class="h-full p-6 md:p-8 bg-white border-2 rounded-[2rem] md:rounded-[2.5rem] transition-all {{ $totalBoxes > 0 ? 'border-blue-50 shadow-md shadow-blue-50/50' : 'border-slate-50 shadow-sm' }}">

                                    <div class="flex items-center justify-between mb-6">
                                        <div class="px-3 py-1 rounded-lg bg-slate-100">
                                            <h4 class="text-[10px] font-black tracking-widest text-slate-500 uppercase">
                                                Petak {{ $slot }}</h4>
                                        </div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-2xl font-black text-blue-600">{{ $totalBoxes }}</span>
                                            <span class="text-[10px] font-bold text-slate-400 uppercase">Box</span>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        @forelse ($contents as $item)
                                            <div
                                                class="p-4 transition-all border border-slate-50 bg-slate-50/50 rounded-2xl hover:bg-blue-50 hover:border-blue-100">
                                                <p class="text-xs font-black leading-tight uppercase text-slate-800">
                                                    {{ $item->product_name }}</p>
                                                <div class="flex items-center justify-between mt-2">
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase">ID:
                                                        {{ $item->booking->booking_code }}</span>
                                                    <span
                                                        class="px-2 py-0.5 text-[10px] font-black bg-white text-blue-600 rounded-md shadow-sm border border-blue-50">{{ $item->quantity }}
                                                        Box</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div
                                                class="flex flex-col items-center justify-center py-10 border-2 border-dashed border-slate-100 rounded-3xl">
                                                <i class="mb-2 text-slate-200 fa-solid fa-box-open"></i>
                                                <p class="text-[10px] font-bold text-slate-300 uppercase">Petak Kosong</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @empty
                    <div
                        class="flex flex-col items-center justify-center p-20 text-center bg-white border-2 border-dashed border-slate-200 rounded-[3rem] md:rounded-[4rem]">
                        <div class="flex items-center justify-center w-20 h-20 mb-6 rounded-full bg-slate-50 text-slate-200">
                            <i class="text-4xl fa-solid fa-map-location-dot"></i>
                        </div>
                        <h4 class="text-xl font-black text-slate-800 md:text-2xl">Layout Belum Dibuat</h4>
                        <p class="mt-2 text-sm font-medium text-slate-400">Gunakan form di atas untuk menata layout gudang Anda.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- ═══ SUMMARY TABLES SECTION (Responsive Layout) ═══ --}}
            <div class="grid grid-cols-1 gap-8 xl:grid-cols-2">

                {{-- RINGKASAN PRODUK --}}
                <div class="bg-white p-6 md:p-10 rounded-[2.5rem] md:rounded-[3.5rem] border border-slate-100 shadow-sm">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-2xl font-black tracking-tighter text-slate-800">Ringkasan <span
                                    class="text-blue-600">Produk</span></h3>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Detail posisi & total
                                stok</p>
                        </div>
                        <i class="text-2xl text-slate-100 fa-solid fa-boxes-stacked"></i>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-3">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="pb-2 pl-4">Produk</th>
                                    <th class="pb-2">Lokasi</th>
                                    <th class="pb-2 pr-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $productSummaries = \App\Models\PalletContent::with('pallet')
                                        ->get()
                                        ->groupBy('product_name');
                                @endphp
                                @forelse ($productSummaries as $productName => $items)
                                    <tr class="group">
                                        <td class="py-4 pl-4 bg-slate-50 rounded-l-2xl">
                                            <p class="text-xs font-black uppercase text-slate-800">{{ $productName }}</p>
                                            <p class="text-[9px] font-bold text-slate-400 italic">{{ $items->count() }} Palet
                                            </p>
                                        </td>
                                        <td class="py-4 bg-slate-50">
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($items->groupBy('pallet_id') as $pId => $gItems)
                                                    @php $p = $gItems->first()->pallet; @endphp
                                                    <span
                                                        class="px-2 py-0.5 text-[8px] font-black bg-white border border-slate-100 text-indigo-600 rounded-md">L{{ $p->line }}-P{{ $p->slot_section }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="py-4 pr-4 text-right bg-slate-50 rounded-r-2xl">
                                            <span class="text-xs font-black text-blue-600">{{ $items->sum('quantity') }}
                                                BOX</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3"
                                            class="py-12 text-center text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                                            Data produk kosong</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- RINGKASAN LOKASI --}}
                <div class="bg-white p-6 md:p-10 rounded-[2.5rem] md:rounded-[3.5rem] border border-slate-100 shadow-sm"
                    x-data="{ tableFilter: 'all' }">
                    <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-2xl font-black tracking-tighter text-slate-800">Ringkasan <span
                                    class="text-slate-400">Lokasi</span></h3>
                        </div>
                        <div class="flex p-1 overflow-x-auto bg-slate-100 rounded-xl no-scrollbar shrink-0">
                            <button @click="tableFilter = 'all'"
                                :class="tableFilter === 'all' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-400'"
                                class="px-4 py-2 text-[9px] font-black uppercase rounded-lg transition-all">All</button>
                            @foreach ($lines as $lNum)
                                <button @click="tableFilter = '{{ $lNum }}'"
                                    :class="tableFilter === '{{ $lNum }}' ? 'bg-white shadow-sm text-blue-600' :
                                        'text-slate-400'"
                                    class="px-4 py-2 text-[9px] font-black uppercase rounded-lg transition-all whitespace-nowrap">Line
                                    {{ $lNum }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-separate border-spacing-y-3">
                            <thead>
                                <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                    <th class="pb-2 pl-4">Posisi</th>
                                    <th class="pb-2">Isi Produk</th>
                                    <th class="pb-2 pr-4 text-right">Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $allLocations =
                                        $allLocations ??
                                        \App\Models\Pallet::orderBy('line')->orderBy('slot_section')->get();
                                @endphp
                                @foreach ($allLocations as $loc)
                                    @php
                                        $contents = \App\Models\PalletContent::where('pallet_id', $loc->id)->get();
                                        $totalBoxes = $contents->sum('quantity');
                                    @endphp
                                    <tr x-show="tableFilter === 'all' || tableFilter === '{{ $loc->line }}'"
                                        class="group">
                                        <td class="py-4 pl-4 bg-slate-50 rounded-l-2xl">
                                            <span
                                                class="text-xs font-black text-slate-800">L{{ $loc->line }}-P{{ $loc->slot_section }}</span>
                                        </td>
                                        <td class="py-4 bg-slate-50">
                                            @if ($contents->count() > 0)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($contents as $item)
                                                        <span
                                                            class="px-2 py-0.5 text-[8px] font-bold bg-blue-100 text-blue-700 rounded-md">{{ $item->product_name }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-[9px] font-bold text-slate-300 uppercase italic">Empty</span>
                                            @endif
                                        </td>
                                        <td class="py-4 pr-4 text-right bg-slate-50 rounded-r-2xl">
                                            <span
                                                class="text-xs font-black {{ $totalBoxes > 0 ? 'text-blue-600' : 'text-slate-300' }}">{{ $totalBoxes ?: '-' }}</span>
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
