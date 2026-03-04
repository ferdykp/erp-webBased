@extends('admin.layout.app')
@section('title', 'Pallet Management')

@section('content')
    <div class="space-y-6">
        {{-- HEADER --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-3xl font-extrabold tracking-tight text-gray-800">Pallet <span
                        class="text-blue-600">Inventory</span></h2>
                <p class="text-sm font-medium text-gray-500">Visualisasi Gudang: 1 Line > 5 Petak > 10 Palet</p>
            </div>
            <div class="flex gap-2">
                <form action="{{ route('admin.pallets.generate') }}" method="POST" class="flex items-end gap-3">
                    @csrf

                    {{-- Line --}}
                    <div class="flex flex-col">
                        <label class="mb-1 text-[10px] font-bold tracking-widest text-gray-400 uppercase">
                            Line
                        </label>
                        <input type="number" name="lines" value="2"
                            class="w-20 px-3 py-2 text-sm font-bold text-center bg-white border border-gray-200 shadow-sm outline-none rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    </div>

                    {{-- Petak --}}
                    <div class="flex flex-col">
                        <label class="mb-1 text-[10px] font-bold tracking-widest text-gray-400 uppercase">
                            Petak
                        </label>
                        <input type="number" name="slots" value="5"
                            class="w-20 px-3 py-2 text-sm font-bold text-center bg-white border border-gray-200 shadow-sm outline-none rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-400">
                    </div>

                    {{-- Button --}}
                    <button type="submit"
                        class="px-5 py-2 text-xs font-black text-white transition-all bg-blue-600 shadow-lg rounded-xl hover:bg-blue-700 shadow-blue-200">
                        GENERATE / UPDATE
                    </button>
                </form>
            </div>
        </div>

        {{-- VISUALISASI GUDANG --}}
        {{-- Container Utama dengan Alpine.js state --}}
        <div x-data="{ activeLine: 1 }" class="space-y-6">

            {{-- VISUALISASI GUDANG --}}
            @php
                $lines = \App\Models\Pallet::whereNotNull('line')->groupBy('line')->pluck('line')->sort();
            @endphp

            <div class="relative">
                {{-- TABS NAVIGATION (Pojok Kanan Atas) --}}
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
                        $themeColor = $line == 1 ? 'blue' : 'indigo';
                        $accentColor = $line == 1 ? 'bg-blue-600' : 'bg-indigo-600';
                    @endphp

                    {{-- Tampilan Line (Hanya muncul jika activeLine sesuai) --}}
                    <div x-show="activeLine === {{ $line }}" x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="p-8 bg-white border border-gray-100 shadow-sm rounded-[3rem] mb-10">

                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-4">
                                <div
                                    class="w-12 h-12 {{ $accentColor }} rounded-2xl flex items-center justify-center shadow-lg shadow-{{ $themeColor }}-200">
                                    <i class="text-xl text-white fa-solid fa-warehouse"></i>
                                </div>
                                <div>
                                    <h3 class="text-3xl font-black tracking-tighter text-gray-800">LINE {{ $line }}
                                    </h3>
                                    <p
                                        class="text-[10px] font-bold text-{{ $themeColor }}-500 uppercase tracking-[0.2em]">
                                        Kapasitas: 50 Palet / 500 Box</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
                            @for ($slot = 1; $slot <= 5; $slot++)
                                <div class="bg-gray-50/50 p-5 rounded-[2.5rem] border border-gray-200 shadow-inner">
                                    <div class="flex items-center justify-center gap-2 mb-4">
                                        <span class="w-4 h-px bg-gray-300"></span>
                                        <h4 class="text-[11px] font-black tracking-[0.3em] text-gray-400 uppercase">PETAK
                                            {{ $slot }}</h4>
                                        <span class="w-4 h-px bg-gray-300"></span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        @php
                                            $palletsInSlot = \App\Models\Pallet::where('line', $line)
                                                ->where('slot_section', $slot)
                                                ->orderBy('pallet_number')
                                                ->get();
                                        @endphp

                                        @foreach ($palletsInSlot as $pallet)
                                            <div class="relative cursor-pointer group">
                                                <div
                                                    class="flex flex-col items-center p-3 rounded-2xl border-2 transition-all duration-300 shadow-sm
                                            {{ $pallet->status == 'empty'
                                                ? 'border-white bg-white text-emerald-500 hover:border-emerald-200'
                                                : 'border-red-100 bg-red-50 text-red-600 hover:border-red-300' }}">

                                                    <i class="mb-1 text-xl fa-solid fa-pallet"></i>
                                                    <span
                                                        class="text-[10px] font-black tracking-tighter">{{ $pallet->pallet_number }}</span>

                                                    {{-- DOT INDICATOR --}}
                                                    <div
                                                        class="flex flex-wrap justify-center gap-0.5 mt-2 px-1 max-w-[40px]">
                                                        @for ($i = 1; $i <= 10; $i++)
                                                            <div
                                                                class="w-1.5 h-1.5 rounded-full shadow-sm transition-colors duration-500
                                                        {{ $i <= $pallet->filled_boxes ? 'bg-blue-500' : 'bg-gray-200' }}">
                                                            </div>
                                                        @endfor
                                                    </div>

                                                    <div
                                                        class="mt-2 text-[8px] font-bold {{ $pallet->filled_boxes > 0 ? 'text-gray-700' : 'text-gray-300' }}">
                                                        {{ $pallet->filled_boxes }}/10 BOX
                                                    </div>
                                                </div>

                                                {{-- TOOLTIP ON HOVER --}}
                                                <div
                                                    class="absolute z-20 hidden w-32 p-3 mb-2 text-white transform -translate-x-1/2 bg-gray-900 border border-gray-700 shadow-2xl rounded-xl group-hover:block bottom-full left-1/2">
                                                    <p
                                                        class="text-[9px] font-black mb-1 border-b border-gray-700 pb-1 uppercase">
                                                        Detail Palet</p>
                                                    <p class="text-[8px] text-gray-400">ID: <span
                                                            class="text-white">{{ $pallet->pallet_number }}</span></p>
                                                    <p class="text-[8px] text-gray-400">Box: <span
                                                            class="text-{{ $themeColor }}-400">{{ $pallet->filled_boxes }}
                                                            Terisi</span></p>
                                                    <p class="text-[8px] text-gray-400">Sisa: <span
                                                            class="text-emerald-400">{{ 10 - $pallet->filled_boxes }}
                                                            Slot</span></p>
                                                    <div
                                                        class="absolute w-2 h-2 transform rotate-45 -translate-x-1/2 bg-gray-900 left-1/2 -bottom-1">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                @empty
                    <div
                        class="flex flex-col items-center justify-center p-20 text-center bg-white border-2 border-dashed border-gray-200 rounded-[4rem]">
                        <div class="flex items-center justify-center w-20 h-20 mb-4 rounded-full bg-gray-50">
                            <i class="text-4xl text-gray-300 fa-solid fa-box-open"></i>
                        </div>
                        <h4 class="text-xl font-bold text-gray-800">Gudang Belum Terkonfigurasi</h4>
                        <p class="max-w-xs mx-auto mt-2 text-gray-500">Klik tombol "Auto Generate" untuk membuat struktur
                            Line 1 & Line 2 secara otomatis.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- INVENTORY LIST TABLE TETAP SAMA NAMUN SESUAIKAN STYLING --}}
        <div class="bg-white p-10 rounded-[3.5rem] border border-gray-100 shadow-sm" x-data="{ activeLine: 'all' }">
            <div class="flex flex-col gap-6 mb-8 md:flex-row md:items-center md:justify-between">
                <div>
                    <h3 class="text-2xl font-black tracking-tighter text-gray-800">Inventory <span
                            class="text-gray-400">Master List</span></h3>
                    <p class="text-xs font-medium text-gray-400">Manajemen data palet berdasarkan kategori Line</p>
                </div>

                {{-- TABS FILTER DINAMIS --}}
                <div class="flex p-1.5 bg-gray-100 rounded-2xl w-fit">
                    <button @click="activeLine = 'all'"
                        :class="activeLine === 'all' ? 'bg-white text-gray-800 shadow-sm' :
                            'text-gray-500 hover:text-gray-700'"
                        class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-200">
                        Show All
                    </button>

                    {{-- Loop berdasarkan Line yang tersedia di database --}}
                    @foreach ($lines as $lineNum)
                        <button @click="activeLine = '{{ $lineNum }}'"
                            :class="activeLine === '{{ $lineNum }}'
                                ?
                                '{{ $lineNum == 1 ? 'bg-blue-600 shadow-blue-200' : 'bg-indigo-600 shadow-indigo-200' }} text-white shadow-lg' :
                                'text-gray-500 hover:text-gray-700'"
                            class="px-5 py-2 text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-200">
                            Line {{ $lineNum }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[11px] font-black text-gray-400 uppercase tracking-widest pl-4">
                            <th class="w-16 pb-2 pl-6 text-center">ID</th>
                            <th class="pb-2">Pallet ID</th>
                            <th class="pb-2">Location</th>
                            <th class="pb-2">Capacity</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2 pr-6 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pallets as $p)
                            <tr x-show="activeLine === 'all' || activeLine === '{{ $p->line }}'"
                                x-transition:enter="transition ease-out duration-300"
                                x-transition:enter-start="opacity-0 transform -translate-y-2"
                                x-transition:enter-end="opacity-100 transform translate-y-0"
                                class="transition-all shadow-sm group hover:bg-gray-50">

                                <td
                                    class="py-4 pl-4 text-center border-l bg-gray-50 group-hover:bg-white rounded-l-2xl border-y border-gray-50 group-hover:border-gray-100">
                                    <span
                                        class="px-2 py-1 rounded-lg text-[9px] font-black {{ $p->line == 1 ? 'bg-blue-100 text-blue-600' : 'bg-indigo-100 text-indigo-600' }}">
                                        L{{ $p->line }}
                                    </span>
                                </td>

                                <td class="py-4 transition-all border-y border-gray-50 group-hover:border-gray-100">
                                    <span class="font-mono text-sm font-black text-gray-700">{{ $p->pallet_number }}</span>
                                </td>

                                <td class="py-4 transition-all border-y border-gray-50 group-hover:border-gray-100">
                                    <div class="flex flex-col">
                                        <span class="text-[10px] font-bold text-gray-400 uppercase">Petak
                                            {{ $p->slot_section }}</span>
                                    </div>
                                </td>

                                <td class="py-4 transition-all border-y border-gray-50 group-hover:border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-black text-gray-700">{{ $p->filled_boxes }}/10</span>
                                        <div class="flex w-20 h-1.5 overflow-hidden bg-gray-100 rounded-full">
                                            <div class="h-full transition-all duration-500 {{ $p->line == 1 ? 'bg-blue-500 shadow-[0_0_8px_rgba(59,130,246,0.5)]' : 'bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.5)]' }}"
                                                style="width: {{ ($p->filled_boxes / 10) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-4 transition-all border-y border-gray-50 group-hover:border-gray-100">
                                    <span
                                        class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest
                                {{ $p->status == 'empty' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $p->status }}
                                    </span>
                                </td>

                                <td
                                    class="py-4 pr-6 text-right transition-all border-r bg-gray-50 group-hover:bg-white rounded-r-2xl border-y border-gray-50 group-hover:border-gray-100">
                                    <form action="{{ route('admin.pallets.destroy', $p->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus palet ID {{ $p->pallet_number }}?')">
                                        @csrf @method('DELETE')
                                        <button
                                            class="p-2 text-gray-300 transition-all hover:text-red-600 hover:scale-125">
                                            <i class="text-sm fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-10 font-medium text-center text-gray-400">Belum ada data
                                    palet tersedia.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
