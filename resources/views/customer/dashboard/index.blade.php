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
            <div id="table-container" data-async-table="{{ route('customer.dashboard') }}" data-search-input="search">
                @include('customer.dashboard.table', ['data' => $data])
            </div>
        </div>
    </div>

<div data-ui-modal role="dialog" aria-modal="true" tabindex="-1" id="detailModal"
    class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-2xl bg-white shadow-2xl rounded-[2.5rem] overflow-hidden transform transition-all scale-95 opacity-0"
        id="modalContainer">

        <div class="flex items-center justify-between p-8 border-b border-gray-50">
            <div>
                <h3 class="text-xl font-black text-gray-900">Booking Summary</h3>
                {{-- <p class="mt-1 text-xs font-bold tracking-widest text-gray-400 uppercase" id="modalTicketCode">Ticket:
                    Loading...</p> --}}
            </div>
            <button type="button" data-modal-dismiss onclick="closeModal()"
                class="flex items-center justify-center w-10 h-10 text-gray-400 transition-colors rounded-full bg-gray-50 hover:text-red-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="modalContent" class="p-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
            {{-- Content from AJAX --}}
        </div>

        <div class="flex items-center justify-between p-8 bg-gray-50">
            <button type="button" data-modal-dismiss onclick="closeModal()" class="text-sm font-bold text-gray-500 hover:text-gray-700">Close
                Window</button>
            <a id="printBtn" href="#" target="_blank"
                class="flex items-center gap-2 px-8 py-3 text-sm font-black text-white transition-all bg-blue-600 shadow-lg rounded-2xl shadow-blue-200 hover:bg-blue-700 active:scale-95">
                <i class="fa-solid fa-print"></i>
                Download Ticket
            </a>
        </div>
    </div>
</div>

<script>
    let detailRequest;
    let closeTimer;
    function openModal(id) {
        clearTimeout(closeTimer);
        detailRequest?.abort();
        const request = detailRequest = new AbortController();
        const modal = document.getElementById('detailModal');
        const container = document.getElementById('modalContainer');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Modal Animation
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);

        const content = document.getElementById('modalContent');
        const print = document.getElementById('printBtn');
        content.textContent = 'Loading...';
        print.classList.add('hidden');
        fetch(`/customer/booking/${id}`, { signal: request.signal })
            .then(res => {
                if (!res.ok || res.redirected) throw new Error('Request failed');
                return res.text();
            })
            .then(html => {
                if (request.signal.aborted) return;
                content.innerHTML = html;
                print.classList.remove('hidden');
                document.getElementById('printBtn').href = `/customer/booking/${id}/print`;
                // If you want to show the ticket code in title
                // document.getElementById('modalTicketCode').innerText = 'Ticket: ' + code;
            }).catch(error => {
                if (error.name !== 'AbortError' && !request.signal.aborted) {
                    content.textContent = 'Detail gagal dimuat. Tutup dan coba lagi.';
                }
            });
    }

    function closeModal() {
        detailRequest?.abort();
        const modal = document.getElementById('detailModal');
        const container = document.getElementById('modalContainer');

        container.classList.add('scale-95', 'opacity-0');
        container.classList.remove('scale-100', 'opacity-100');

        closeTimer = setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }
</script>

@endsection
