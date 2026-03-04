@extends('layouts.master')

@section('content')
    <div class="w-full space-y-6">

        {{-- STEP INDICATOR (Opsional tapi bagus untuk UX) --}}
        <div class="flex items-center justify-center gap-4 mb-8">
            <div class="flex items-center gap-2">
                <span
                    class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white bg-blue-600 rounded-full shadow-lg shadow-blue-200">1</span>
                <span class="text-sm font-bold text-gray-900">Pilih Tanggal</span>
            </div>
            <div class="w-12 h-px bg-gray-200"></div>
            <div class="flex items-center gap-2">
                <span
                    class="flex items-center justify-center w-8 h-8 text-sm font-bold text-gray-400 bg-gray-100 rounded-full">2</span>
                <span class="text-sm font-bold text-gray-400">Pilih Sesi</span>
            </div>
            <div class="w-12 h-px bg-gray-200"></div>
            <div class="flex items-center gap-2">
                <span
                    class="flex items-center justify-center w-8 h-8 text-sm font-bold text-gray-400 bg-gray-100 rounded-full">3</span>
                <span class="text-sm font-bold text-gray-400">Isi Detail</span>
            </div>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">

            {{-- HEADER --}}
            <div class="px-8 py-8 border-b border-gray-50 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-2xl font-black tracking-tight text-gray-900">
                            Pilih Tanggal Booking
                        </h2>
                        <p class="mt-1 text-sm font-medium text-gray-400">
                            Silahkan pilih tanggal yang tersedia untuk sterilisasi E-Beam
                        </p>
                    </div>
                    <div class="hidden md:block">
                        <i class="text-4xl text-blue-100 fa-solid fa-calendar-days"></i>
                    </div>
                </div>
            </div>

            {{-- BODY --}}
            <div class="p-8">

                @if ($dates->count() > 0)
                    <div class="grid grid-cols-2 gap-6 md:grid-cols-4 lg:grid-cols-5">

                        @foreach ($dates as $item)
                            <a href="{{ route('customer.booking.session', $item->date) }}"
                                class="relative p-6 text-center transition-all duration-300 bg-white border border-gray-100 group rounded-3xl hover:shadow-2xl hover:shadow-blue-100 hover:border-blue-500 hover:-translate-y-1">

                                {{-- Aksen dekorasi --}}
                                <div
                                    class="absolute top-0 w-12 h-1 transition-colors -translate-x-1/2 bg-gray-100 rounded-b-full left-1/2 group-hover:bg-blue-500">
                                </div>

                                <div class="text-[11px] font-black text-blue-600 uppercase tracking-[0.2em] mb-2">
                                    {{ \Carbon\Carbon::parse($item->date)->format('F') }}
                                </div>

                                <div class="text-3xl font-black text-gray-900 transition-colors group-hover:text-blue-600">
                                    {{ \Carbon\Carbon::parse($item->date)->format('d') }}
                                </div>

                                <div class="mt-2 text-sm font-bold text-gray-400">
                                    {{ \Carbon\Carbon::parse($item->date)->format('l') }}
                                </div>

                                <div
                                    class="mt-4 py-2 px-3 bg-gray-50 rounded-xl text-[10px] font-bold text-gray-500 group-hover:bg-blue-600 group-hover:text-white transition-all uppercase tracking-wider">
                                    Pilih Tanggal
                                </div>
                            </a>
                        @endforeach

                    </div>
                @else
                    <div class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center w-20 h-20 mb-4 rounded-full bg-gray-50">
                                <i class="text-3xl text-gray-200 fa-solid fa-calendar-xmark"></i>
                            </div>
                            <p class="font-bold text-gray-400">Maaf, belum ada tanggal tersedia</p>
                            <p class="mt-1 text-xs text-gray-300">Silahkan hubungi admin atau cek kembali nanti.</p>
                        </div>
                    </div>
                @endif

            </div>

            {{-- FOOTER --}}
            <div class="flex items-center justify-between p-8 border-t border-gray-50 bg-gray-50/30">
                <a href="{{ route('customer.dashboard') }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-bold text-gray-500 transition-colors hover:text-gray-700 group">
                    <i class="transition-transform fa-solid fa-arrow-left group-hover:-translate-x-1"></i>
                    Kembali ke Dashboard
                </a>

                <p class="hidden sm:block text-[10px] font-bold text-gray-300 uppercase tracking-widest">
                    Step 1 of 3: Date Selection
                </p>
            </div>

        </div>
    </div>
@endsection
