@extends('admin.layout.app')
@section('title', 'Pallet Management')

@section('content')
    <div class="space-y-6">
        {{-- HEADER --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-800">Pallet <span
                        class="text-blue-600">Inventory</span></h2>
                <p class="text-sm font-medium text-gray-500">Visualisasi Tata Letak: Line & Petak Gudang</p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('admin.pallets.generate') }}" method="POST" class="flex items-end gap-3">
                    @csrf
                    <div class="flex flex-col">
                        <label class="mb-1 text-[10px] font-bold tracking-widest text-gray-400 uppercase">Line</label>
                        <input type="number" name="lines" value="2"
                            class="w-20 px-3 py-2 text-sm font-bold text-center bg-white border border-gray-200 shadow-sm outline-none rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    </div>
                    <div class="flex flex-col">
                        <label class="mb-1 text-[10px] font-bold tracking-widest text-gray-400 uppercase">Petak</label>
                        <input type="number" name="slots" value="5"
                            class="w-20 px-3 py-2 text-sm font-bold text-center bg-white border border-gray-200 shadow-sm outline-none rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    </div>
                    <button type="submit"
                        class="px-5 py-2 text-xs font-black text-white transition-all bg-blue-600 shadow-lg rounded-xl hover:bg-blue-700 shadow-blue-200">
                        GENERATE / UPDATE
                    </button>
                </form>
            </div>
        </div>

        <div x-data="{ activeLine: 1 }" class="space-y-6">
            @php
                $lines = \App\Models\Pallet::whereNotNull('line')->groupBy('line')->pluck('line')->sort();
            @endphp

            <div class="relative">
                {{-- TABS NAVIGATION --}}
                @if ($lines->count() > 0)
                    <div
                        class="absolute right-6 top-8 z-10 flex p-1.5 bg-gray-100 rounded-2xl shadow-inner border border-gray-200">
                        @foreach ($lines as $lineNav)
                            <button @click="activeLine = {{ $lineNav }}"
                                :class="activeLine === {{ $lineNav }} ? 'bg-white text-blue-600 shadow-sm' :
                                    'text-gray-500 hover:text-gray-700'"
                                class="px-6 py-2 text-xs font-black tracking-widest uppercase transition-all duration-300 rounded-xl">
                                Line {{ $lineNav }}
                            </button>
                        @endforeach
                    </div>
                @endif

                @forelse ($lines as $line)
                    @php
                        $accentColor = $line == 1 ? 'bg-blue-600' : 'bg-indigo-600';
                        $shadowColor = $line == 1 ? 'shadow-blue-100' : 'shadow-indigo-100';
                    @endphp

                    {{-- VISUALISASI GUDANG --}}
                    <div x-show="activeLine === {{ $line }}"
                        class="p-8 bg-white border border-gray-100 shadow-sm rounded-[3rem] mb-10">

                        <div class="flex items-center gap-4 mb-10">
                            <div
                                class="w-12 h-12 {{ $accentColor }} rounded-2xl flex items-center justify-center shadow-lg {{ $shadowColor }}">
                                <i class="text-xl text-white fa-solid fa-warehouse"></i>
                            </div>
                            <div>
                                <h3 class="text-3xl font-black tracking-tighter text-gray-800">LINE {{ $line }}</h3>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Ringkasan Okupansi
                                    per Petak</p>
                            </div>
                        </div>

                        {{-- GRID PETAK --}}
                        <div class="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3">
                            @php
                                $slotsInLine = \App\Models\Pallet::where('line', $line)
                                    ->groupBy('slot_section')
                                    ->pluck('slot_section')
                                    ->sort();
                            @endphp

                            @foreach ($slotsInLine as $slot)
                                @php
                                    // 1. Ambil data Palet master berdasarkan koordinat
                                    $palletMaster = \App\Models\Pallet::where('line', $line)
                                        ->where('slot_section', $slot)
                                        ->first();

                                    // 2. Ambil isi dari tabel PalletContent (Multi-Product)
                                    $contents = $palletMaster ? $palletMaster->contents : collect();
                                    $totalBoxes = $contents->sum('quantity');
                                @endphp

                                <div class="relative transition-all duration-300 hover:scale-[1.02]">
                                    <div
                                        class="h-full p-6 bg-white border-2 rounded-[2.5rem] shadow-sm {{ $totalBoxes > 0 ? 'border-blue-100' : 'border-gray-100' }}">

                                        <div class="flex items-center justify-between mb-6">
                                            <h4 class="text-xs font-black tracking-[0.2em] text-gray-400 uppercase">Petak
                                                {{ $slot }}</h4>
                                            <p class="text-lg font-black text-blue-600">{{ $totalBoxes }} <span
                                                    class="text-[10px]">BOX</span></p>
                                        </div>

                                        <div class="space-y-3">
                                            @forelse ($contents as $item)
                                                <div
                                                    class="flex items-center justify-between p-3 border bg-slate-50 border-slate-100 rounded-2xl">
                                                    <div>
                                                        <p class="text-[10px] font-black text-slate-800 uppercase">
                                                            {{ $item->product_name }}</p>
                                                        <p class="text-[9px] font-bold text-slate-400">Booking:
                                                            {{ $item->booking->booking_code }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <span
                                                            class="block text-[10px] font-black text-blue-600">{{ $item->quantity }}
                                                            Box</span>
                                                    </div>
                                                </div>
                                            @empty
                                                <div
                                                    class="py-6 text-center border-2 border-gray-100 border-dashed rounded-2xl">
                                                    <p class="text-[10px] font-bold text-gray-300 uppercase">Petak Kosong
                                                    </p>
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
                            class="flex flex-col items-center justify-center p-20 text-center bg-white border-2 border-dashed border-gray-200 rounded-[4rem]">
                            <i class="mb-4 text-5xl text-gray-200 fa-solid fa-map-location-dot"></i>
                            <h4 class="text-xl font-bold text-gray-800">Layout Belum Dibuat</h4>
                            <p class="mt-1 text-sm text-gray-400">Silahkan generate Line dan Petak menggunakan form di atas.</p>
                        </div>
                    @endforelse
                </div>
            </div>
            {{-- TABEL LIST PRODUCTS --}}
            <div class="bg-white p-10 rounded-[3.5rem] border border-gray-100 shadow-sm mt-10">
                <div class="mb-8">
                    <h3 class="text-2xl font-black tracking-tighter text-gray-800">Ringkasan <span
                            class="text-blue-600">Produk</span></h3>
                    <p class="text-xs font-bold text-gray-400 uppercase">Detail posisi dan total stok per produk</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                <th class="pb-4 pl-6">Nama Produk</th>
                                <th class="pb-4">Lokasi (Line-Petak)</th>
                                <th class="pb-4 text-center">Total Palet</th>
                                <th class="pb-4 pr-6 text-right">Total Box</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Ambil semua data produk unik yang ada di PalletContent
                                $productSummaries = \App\Models\PalletContent::with('pallet')
                                    ->get()
                                    ->groupBy('product_name');
                            @endphp

                            @forelse ($productSummaries as $productName => $items)
                                <tr class="transition-all hover:bg-slate-50">
                                    <td
                                        class="py-4 pl-6 font-black text-gray-800 border-l border-y border-gray-50 rounded-l-2xl">
                                        {{ $productName }}
                                    </td>
                                    <td class="py-4 border-y border-gray-50">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach ($items->groupBy('pallet_id') as $palletId => $groupedItems)
                                                @php $p = $groupedItems->first()->pallet; @endphp
                                                <span
                                                    class="px-2 py-1 text-[9px] font-bold bg-indigo-50 text-indigo-600 rounded-lg">
                                                    Line {{ $p->line }}-Petak {{ $p->slot_section }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="py-4 font-black text-center text-gray-600 border-y border-gray-50">
                                        {{ $items->count() }} Palet
                                    </td>
                                    <td
                                        class="py-4 pr-6 font-black text-right text-blue-600 border-r border-y border-gray-50 rounded-r-2xl">
                                        {{ $items->sum('quantity') }} Box
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-10 text-xs italic text-center text-gray-400">
                                        Belum ada produk tersimpan di gudang
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TABLE LIST MODIFIKASI --}}
            <div class="bg-white p-10 rounded-[3.5rem] border border-gray-100 shadow-sm" x-data="{ tableFilter: 'all' }">
                <div class="flex flex-col gap-6 mb-8 md:flex-row md:items-center md:justify-between">
                    <h3 class="text-2xl font-black tracking-tighter text-gray-800">Ringkasan <span
                            class="text-gray-400">Lokasi</span></h3>

                    <div class="flex p-1 bg-gray-100 rounded-xl">
                        <button @click="tableFilter = 'all'"
                            :class="tableFilter === 'all' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-400'"
                            class="px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all">All</button>
                        @foreach ($lines as $lNum)
                            <button @click="tableFilter = '{{ $lNum }}'"
                                :class="tableFilter === '{{ $lNum }}' ? 'bg-white shadow-sm text-blue-600' :
                                    'text-gray-400'"
                                class="px-4 py-2 text-[10px] font-black uppercase rounded-lg transition-all">Line
                                {{ $lNum }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                <th class="pb-4 pl-6">Line</th>
                                <th class="pb-4">Petak</th>
                                <th class="pb-4">Produk Tersimpan</th>
                                <th class="pb-4">Total Palet</th>
                                <th class="pb-4 pr-6 text-right">Total Box</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                // Pastikan variabel ada, jika tidak, fetch dari model
                                $allLocations =
                                    $allLocations ??
                                    \App\Models\Pallet::select('line', 'slot_section')
                                        ->distinct()
                                        ->orderBy('line')
                                        ->orderBy('slot_section')
                                        ->get();
                            @endphp
                            @foreach ($allLocations as $loc)
                                @php
                                    // 1. Ambil semua konten di petak ini berdasarkan pallet_id
                                    $contents = \App\Models\PalletContent::where('pallet_id', $loc->id)->get();

                                    // 2. Hitung jumlah palet unik (jika satu petak bisa diisi beberapa baris konten)
                                    // Kita hitung berdasarkan jumlah record di PalletContent
                                    $totalPallets = $contents->count();

                                    // 3. Hitung total box dari semua konten di petak ini
                                    $totalBoxes = $contents->sum('quantity');
                                @endphp

                                <tr x-show="tableFilter === 'all' || tableFilter === '{{ $loc->line }}'"
                                    class="transition-all hover:bg-slate-50">

                                    <td
                                        class="py-4 pl-6 font-black text-gray-600 border-l border-y border-gray-50 rounded-l-2xl">
                                        L{{ $loc->line }}
                                    </td>
                                    <td class="py-4 font-black text-gray-700 uppercase border-y border-gray-50">
                                        Petak {{ $loc->slot_section }}
                                    </td>

                                    {{-- PRODUK TERSIMPAN --}}
                                    <td class="py-4 border-y border-gray-50">
                                        @if ($contents->count() > 0)
                                            <div class="flex flex-wrap gap-2">
                                                @foreach ($contents as $item)
                                                    <span
                                                        class="px-2 py-0.5 text-[9px] font-bold bg-blue-50 text-blue-600 rounded-md">
                                                        {{ $item->product_name }} ({{ $item->quantity }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-[10px] text-gray-300 italic uppercase">Kosong</span>
                                        @endif
                                    </td>

                                    {{-- TOTAL PALET --}}
                                    <td class="py-4 font-black text-gray-600 border-y border-gray-50">
                                        {{ $totalPallets > 0 ? $totalPallets . ' Palet' : '-' }}
                                    </td>

                                    {{-- TOTAL BOX --}}
                                    <td
                                        class="py-4 pr-6 font-black text-right text-blue-600 border-r border-y border-gray-50 rounded-r-2xl">
                                        {{ $totalBoxes > 0 ? $totalBoxes . ' Box' : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endsection
