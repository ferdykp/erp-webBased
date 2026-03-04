@extends('layouts.master')

@section('content')
    <div class="w-full space-y-6">

        {{-- WELCOME STATS --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div
                class="bg-gradient-to-br from-blue-600 to-indigo-700 p-6 rounded-[2rem] text-white shadow-xl shadow-blue-200 relative overflow-hidden group">
                <i
                    class="fa-solid fa-calendar-check absolute right-[-10%] bottom-[-10%] text-9xl opacity-10 group-hover:scale-110 transition-transform"></i>
                <p class="text-sm font-bold tracking-wider text-blue-100 uppercase">Total Bookings</p>
                <h3 class="mt-2 text-4xl font-black">{{ $data->total() }}</h3>
                <p class="inline-block px-3 py-1 mt-4 text-xs rounded-full bg-white/20 backdrop-blur-sm">Active Sessions</p>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex flex-col justify-center">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center w-12 h-12 text-green-600 bg-green-50 rounded-2xl">
                        <i class="text-xl fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase">Latest Ticket</p>
                        <p class="font-black text-gray-900">{{ $data->first()->booking_code ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-[2rem] border border-gray-100 shadow-sm flex flex-col justify-center">
                <a href="{{ route('customer.booking.create') }}"
                    class="flex items-center justify-between transition-colors group hover:text-blue-600">
                    <div>
                        <p class="text-lg font-black text-gray-900">Need a service?</p>
                        <p class="text-sm text-gray-400">Create new booking now</p>
                    </div>
                    <div
                        class="flex items-center justify-center w-12 h-12 text-white transition-transform bg-blue-600 shadow-lg rounded-2xl shadow-blue-200 group-hover:rotate-12">
                        <i class="text-xl fa-solid fa-plus"></i>
                    </div>
                </a>
            </div>
        </div>

        {{-- MAIN TABLE CARD --}}
        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">

            <div class="flex flex-col justify-between gap-4 p-8 border-b border-gray-50 md:flex-row md:items-center">
                <div>
                    <h2 class="text-2xl font-black tracking-tight text-gray-900">Your History</h2>
                    <p class="mt-1 text-sm text-gray-400">Manage and track your service tickets</p>
                </div>

                {{-- SEARCH --}}
                <div class="relative group">
                    <i
                        class="absolute text-gray-400 transition-colors -translate-y-1/2 fa-solid fa-magnifying-glass left-4 top-1/2 group-focus-within:text-blue-600"></i>
                    <input type="text" id="search" name="search" placeholder="Search by ticket code..."
                        class="w-full md:w-80 pl-12 pr-4 py-3.5 bg-gray-50 border-transparent rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600 transition-all outline-none text-sm">
                </div>
            </div>

            {{-- TABLE AREA --}}
            <div id="table-container">
                @include('customer.dashboard.table', ['data' => $data])
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let timeout = null;

            function fetchBookings(query = '') {
                $.ajax({
                    url: "{{ route('customer.dashboard') }}",
                    type: "GET",
                    data: {
                        'search': query
                    },
                    // Header ini wajib supaya Laravel mendeteksi $request->ajax()
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    beforeSend: function() {
                        $('#table-container').css('opacity', '0.5');
                    },
                    success: function(response) {
                        // response hanya berisi HTML dari table.blade.php
                        $('#table-container').html(response);
                        $('#table-container').css('opacity', '1');
                    },
                    error: function(xhr) {
                        console.log("Error: ", xhr.responseText);
                    }
                });
            }

            $('#search').on('keyup', function() {
                clearTimeout(timeout);
                let query = $(this).val();

                timeout = setTimeout(function() {
                    fetchBookings(query);
                }, 300); // Debounce 300ms agar tidak spam request
            });
        });
    </script>
@endsection
