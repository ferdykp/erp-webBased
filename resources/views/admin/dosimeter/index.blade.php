@extends('admin.layout.app')

@section('title', 'Dosimeter Queue List')

@section('content')
    <div class="w-full p-4 mx-auto bg-white border border-gray-200 shadow-sm max-w-none rounded-2xl sm:p-6 md:p-8 2xl:p-10">

        {{-- Header & Search Section --}}
        <div class="flex flex-col gap-4 pb-4 mb-6 border-b border-gray-200 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dosimeter Task List</h1>
                <p class="mt-1 text-sm text-gray-500">Select a booking task below to input or update absorbance data.</p>
            </div>

            {{-- Input Pencarian AJAX --}}
            <div class="w-full md:w-80">
                <div class="relative">
                    <input type="text" id="search-input" value="{{ request('search') }}"
                        placeholder="Search booking, customer, product..."
                        class="w-full py-2.5 pl-4 pr-10 text-sm font-medium transition-all bg-gray-50 border border-gray-200 outline-none rounded-xl focus:bg-white focus:ring-4 focus:ring-blue-50 focus:border-blue-500">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass" id="search-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Container Tabel Utama (Akan Diperbarui oleh AJAX) --}}
        <div id="dosimeter-table-container" data-async-table="{{ route('admin.dosimeter.index') }}"
            data-search-input="search-input">
            @include('admin.dosimeter.table')
        </div>
    </div>

@endsection
