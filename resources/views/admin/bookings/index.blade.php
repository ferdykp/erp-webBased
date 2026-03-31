@extends('admin.layout.app')

@section('title', 'Bookings Management')

@section('content')
    <div id="bookingDataSource" class="hidden">
        @foreach ($bookings as $b)
            @php $product = $b->products->first(); @endphp
            <div data-code="{{ $b->booking_code }}" data-id="{{ $b->id }}"
                data-name="{{ $product->product_name ?? '-' }}" data-type="{{ $product->product_type ?? '-' }}"
                data-qty="{{ $product->quantity ?? 0 }}" data-unit="{{ $product->unit ?? '' }}"
                data-temp="{{ $product->expect_temp ?? '-' }}" data-dmin="{{ $product->dmin ?? 0 }}"
                data-dmax="{{ $product->dmax ?? 0 }}" data-dimension="{{ $product->dimension_pack ?? '-' }}"
                data-vol-pcs="{{ $product->vol_per_pcs ?? 0 }}" data-vol-total="{{ $product->vol_total ?? 0 }}"
                data-net-pcs="{{ $product->net_weight_pcs ?? 0 }}" data-net-total="{{ $product->total_net_weight ?? 0 }}"
                data-gross-pcs="{{ $product->gross_weight_per_pcs ?? 0 }}"
                data-gross-total="{{ $product->total_gross_weight ?? 0 }}">
            </div>
        @endforeach
    </div>

    <div id="porterDataSource" class="hidden">
        @foreach ($porters as $p)
            <div data-name="{{ $p->name }}"></div>
        @endforeach
    </div>

    <div id="palletInventoryData" class="hidden">
        @foreach ($pallets as $p)
            <div data-line="{{ $p->line }}" data-petak="{{ $p->slot_section }}" data-status="{{ $p->status }}">
            </div>
        @endforeach
    </div>

    <div class="w-full pb-10 space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">{{ $pageTitle ?? 'All Bookings' }}</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Monitoring flow & inventory distribution.</p>
            </div>
            <div class="flex items-center gap-3 px-6 py-3 bg-white border shadow-sm border-slate-100 rounded-2xl">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="text-xs font-black uppercase text-slate-400">Total: {{ $bookings->total() }}</span>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[3rem] overflow-hidden">
            <div class="flex items-center justify-between p-6">
                <h3 class="text-lg font-semibold text-slate-800">Daftar Order</h3>

                <a href="{{ route('admin.bookings.create') }}"
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-bold transition-all rounded-xl bg-blue-600 text-white shadow-lg shadow-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Add Order</span>
                </a>
            </div>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] font-black tracking-[0.2em] text-slate-600 uppercase">
                            <th class="px-8 py-4">Customer Details</th>
                            <th class="px-6 py-4 text-center">Create Booking</th>
                            <th class="px-6 py-4 text-center">Product</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-8 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr class="transition-all duration-300 bg-white group hover:bg-slate-50/50">
                                <td
                                    class="px-8 py-6 rounded-l-[2rem] border-y border-l border-transparent group-hover:border-slate-100">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex items-center justify-center w-12 h-12 font-black text-blue-700 bg-blue-50 rounded-2xl">
                                            {{ strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-slate-800">
                                                {{ $booking->customer->contacts->first()->name ?? 'Guest' }}
                                            </p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase">
                                                #{{ $booking->booking_code }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ $booking->created_at->format('d M Y') }}</p>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <p class="text-sm font-black text-slate-700">
                                        {{ $booking->products->first()->product_name }}</p>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <x-status-badge :status="$booking->status" />
                                </td>
                                <td
                                    class="px-8 py-6 rounded-r-[2rem] border-y border-r border-transparent group-hover:border-slate-100">
                                    <div class="flex items-center justify-end gap-3">
                                        {{-- Tombol Detail --}}
                                        {{-- Tombol Detail --}}
                                        <button type="button" onclick="toggleDetailModal('{{ $booking->id }}', true)"
                                            class="flex items-center justify-center w-10 h-10 text-blue-600 transition-all bg-blue-50 rounded-xl hover:bg-blue-600 hover:text-white">
                                            <i class="text-xs fa-solid fa-eye"></i>
                                        </button>

                                        @if ($booking->arrival_time)
                                            <a href="{{ route('admin.bookings.invoice', $booking->id) }}" target="_blank"
                                                class="flex items-center gap-2 px-5 py-2.5 text-[11px] font-black text-white bg-emerald-500 rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100">
                                                <i class="fa-solid fa-file-invoice"></i>
                                                DOWNLOAD INVOICE
                                            </a>
                                        @else
                                            <span class="text-[10px] font-bold text-gray-400 italic">
                                                <i class="mr-1 fa-solid fa-clock"></i> Waiting for Check-in
                                            </span>
                                        @endif

                                        @if ($booking->status == 'pending')
                                            <button onclick="openWarehouseModal('{{ $booking->booking_code }}')"
                                                class="flex items-center gap-2 px-4 py-2 text-[10px] font-black uppercase bg-blue-600 text-white rounded-xl hover:bg-blue-700 active:scale-95 transition-all">
                                                <i class="fa-solid fa-qrcode"></i> Check-in
                                            </button>
                                            {{-- @elseif($booking->status == 'approved')
                                            <button onclick="openQCModal({{ json_encode($booking) }}, 'processing')"
                                                class="flex items-center gap-2 px-4 py-2 text-[10px] font-black uppercase bg-purple-600 text-white rounded-xl hover:bg-purple-700">
                                                <i class="fa-solid fa-spinner"></i> Start Process
                                            </button>
                                        @elseif($booking->status == 'processing')
                                            <button onclick="openQCModal({{ json_encode($booking) }}, 'completed')"
                                                class="flex items-center gap-2 px-4 py-2 text-[10px] font-black uppercase bg-emerald-600 text-white rounded-xl hover:bg-emerald-700">
                                                <i class="fa-solid fa-check-double"></i> Finish
                                            </button>
                                        @else
                                            <span
                                                class="text-[10px] font-black text-slate-400 uppercase bg-slate-100 px-3 py-2 rounded-xl">
                                                <i class="mr-1 fa-solid fa-lock"></i> Locked
                                            </span> --}}
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @push('modals')
                                @include('admin.bookings.partials.detail-modal', ['booking' => $booking])
                            @endpush
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-10 py-8">{{ $bookings->links() }}</div>
        </div>
    </div>

    {{-- MODAL AREA --}}
    {{-- @include('admin.bookings.partials.detail-modal') --}}
    @include('admin.bookings.partials.preRad')
    @stack('modals')

    @include('admin.bookings.partials.checkin-modal')

@endsection

@push('scripts')
    {{-- Script untuk modal detail --}}
    {{-- <script src="{{ asset('js/admin/booking-details.js') }}"></script> --}}
    <script>
        function toggleDetailModal(id, show) {
            const modal = document.getElementById(`modal-detail-${id}`);
            if (!modal) return; // Guard clause jika modal tidak ditemukan

            const card = modal.querySelector('.modal-card');

            if (show) {
                modal.classList.remove('pointer-events-none');
                modal.classList.add('opacity-100');
                setTimeout(() => {
                    card.classList.remove('scale-95', 'opacity-0');
                    card.classList.add('scale-100', 'opacity-100');
                }, 50);
            } else {
                card.classList.add('scale-95', 'opacity-0');
                card.classList.remove('scale-100', 'opacity-100');
                setTimeout(() => {
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    modal.classList.remove('opacity-100');
                }, 300);
            }
        }
    </script>

    <script src="{{ asset('js/admin/prerad.js') }}"></script>
    <script src="{{ asset('js/admin/checkin.js') }}"></script>
@endpush
