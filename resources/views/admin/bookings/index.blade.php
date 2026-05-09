@extends('admin.layout.app')

@section('title', 'Order Management')

@section('content')
    {{-- Data Sources (Keep hidden) --}}
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

    <div class="w-full pb-10 space-y-6 md:space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <h2 class="text-3xl font-black tracking-tighter md:text-4xl text-slate-800">
                    {{ $pageTitle ?? 'All Bookings' }}</h2>
                <p class="text-xs font-medium md:text-sm text-slate-500">Monitoring flow & inventory distribution.</p>
            </div>
            <div
                class="flex items-center self-start gap-3 px-5 py-3 bg-white border shadow-sm lg:self-center border-slate-100 rounded-2xl">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Total:
                    {{ $bookings->total() }}</span>
            </div>
        </div>

        {{-- MAIN CONTAINER --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2rem] md:rounded-[3rem] overflow-hidden">
            <div
                class="flex flex-col items-start justify-between gap-4 p-6 border-b sm:flex-row sm:items-center border-slate-50">
                <h3 class="text-lg font-bold text-slate-800">Order List</h3>
                <a href="{{ route('admin.bookings.create') }}"
                    class="flex items-center justify-center w-full gap-2 px-6 py-3 text-sm font-black text-white transition-all bg-blue-600 shadow-lg sm:w-auto rounded-xl shadow-blue-100 active:scale-95">
                    <i class="fa-solid fa-plus"></i>
                    <span>Add New Order</span>
                </a>
            </div>

            {{-- DESKTOP TABLE VIEW (Visible on MD and up) --}}
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black tracking-[0.15em] text-slate-400 uppercase bg-slate-50/50">
                            <th class="px-8 py-5">Customer</th>
                            <th class="px-6 py-5 text-center">Created At</th>
                            <th class="px-6 py-5 text-center">Product</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-8 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach ($bookings as $booking)
                            <tr class="transition-colors group hover:bg-slate-50/50">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4 text-left">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 font-black text-blue-600 bg-blue-50 rounded-xl shrink-0">
                                            {{ strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-sm font-bold truncate text-slate-800">
                                                {{ $booking->customer->contacts->first()->name ?? 'Guest' }}
                                            </p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase">
                                                #{{ $booking->booking_code }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="text-xs font-bold text-slate-600">{{ $booking->created_at->format('d M Y') }}</span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-xs font-bold text-slate-600 truncate max-w-[150px] inline-block">
                                        {{ $booking->products->first()->product_name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <x-status-badge :status="$booking->status" />
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Desktop Actions Group --}}
                                        <button onclick="toggleDetailModal('{{ $booking->id }}', true)"
                                            class="p-2 text-blue-600 transition-colors rounded-lg bg-blue-50 hover:bg-blue-600 hover:text-white">
                                            <i class="text-xs fa-solid fa-eye"></i>
                                        </button>
                                        <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                            class="p-2 transition-colors rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-500 hover:text-white">
                                            <i class="text-xs fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button
                                            onclick="confirmDelete('{{ $booking->id }}', '{{ $booking->booking_code }}')"
                                            class="p-2 transition-colors rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white">
                                            <i class="text-xs fa-solid fa-trash"></i>
                                        </button>

                                        <div class="w-px h-6 mx-1 bg-slate-100"></div>

                                        @if ($booking->status == 'pending')
                                            <button onclick="openWarehouseModal('{{ $booking->booking_code }}')"
                                                class="px-4 py-2 text-[10px] font-black uppercase bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                                                Check-in
                                            </button>
                                        @elseif($booking->arrival_time)
                                            <a href="{{ route('admin.bookings.invoice', $booking->id) }}" target="_blank"
                                                class="px-4 py-2 text-[10px] font-black text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-sm transition-all">
                                                Invoice
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- MOBILE/CARD VIEW (Visible on SM and below) --}}
            <div class="p-4 space-y-4 md:hidden bg-slate-50/50">
                @foreach ($bookings as $booking)
                    <div class="p-5 space-y-4 bg-white border shadow-sm border-slate-100 rounded-2xl">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center justify-center w-10 h-10 font-black text-blue-600 bg-blue-50 rounded-xl">
                                    {{ strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">
                                        {{ $booking->customer->contacts->first()->name ?? 'Guest' }}</p>
                                    <p class="text-[10px] font-bold text-slate-400">#{{ $booking->booking_code }}</p>
                                </div>
                            </div>
                            <x-status-badge :status="$booking->status" />
                        </div>

                        <div class="grid grid-cols-2 gap-4 py-3 border-y border-slate-50">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Date</p>
                                <p class="text-xs font-bold text-slate-700">{{ $booking->created_at->format('d M Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Product</p>
                                <p class="text-xs font-bold truncate text-slate-700">
                                    {{ $booking->products->first()->product_name ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                            <div class="flex gap-2">
                                <button onclick="toggleDetailModal('{{ $booking->id }}', true)"
                                    class="p-2.5 text-blue-600 bg-blue-50 rounded-xl">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                    class="p-2.5 text-amber-600 bg-amber-50 rounded-xl">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <button onclick="confirmDelete('{{ $booking->id }}', '{{ $booking->booking_code }}')"
                                    class="p-2.5 text-rose-600 bg-rose-50 rounded-xl">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>

                            @if ($booking->status == 'pending')
                                <button onclick="openWarehouseModal('{{ $booking->booking_code }}')"
                                    class="px-5 py-2.5 text-[10px] font-black uppercase bg-blue-600 text-white rounded-xl grow sm:grow-0">
                                    Check-in
                                </button>
                            @elseif($booking->arrival_time)
                                <a href="{{ route('admin.bookings.invoice', $booking->id) }}" target="_blank"
                                    class="px-5 py-2.5 text-[10px] font-black text-white bg-emerald-500 rounded-xl text-center grow sm:grow-0">
                                    Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-6 border-t md:px-10 border-slate-50">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>

    {{-- DELETE MODAL --}}
    <div id="deleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div
            class="relative w-full max-w-sm p-8 transition-all scale-95 opacity-0 bg-white shadow-2xl rounded-[2.5rem] modal-card">
            <div class="flex flex-col items-center text-center">
                <div class="flex items-center justify-center w-16 h-16 mb-6 rounded-2xl bg-rose-50 text-rose-500">
                    <i class="text-2xl fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="mb-2 text-xl font-black text-slate-800">Delete Booking?</h3>
                <p class="mb-8 text-xs font-medium leading-relaxed text-slate-500">
                    Booking <span id="deleteBookingCode" class="font-bold text-slate-800"></span> will be permanently
                    removed.
                </p>
                <div class="flex flex-col w-full gap-3">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full py-4 text-xs font-black tracking-widest text-white uppercase shadow-lg bg-rose-600 rounded-2xl active:scale-95 shadow-rose-100">Confirm
                            Delete</button>
                    </form>
                    <button onclick="closeDeleteModal()"
                        class="w-full py-4 text-xs font-black tracking-widest uppercase text-slate-400 bg-slate-50 rounded-2xl active:scale-95">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    @include('admin.bookings.partials.preRad')
    @include('admin.bookings.partials.checkin-modal')

    @foreach ($bookings as $booking)
        @include('admin.bookings.partials.detail-modal', ['booking' => $booking])
    @endforeach

@endsection

@push('scripts')
    <script>
        function toggleDetailModal(id, show) {
            const modal = document.getElementById(`modal-detail-${id}`);
            if (!modal) return;
            const card = modal.querySelector('.modal-card');
            if (show) {
                modal.classList.remove('hidden', 'pointer-events-none');
                modal.classList.add('flex', 'opacity-100');
                setTimeout(() => card.classList.add('scale-100', 'opacity-100'), 10);
            } else {
                card.classList.remove('scale-100', 'opacity-100');
                card.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden', 'pointer-events-none');
                    modal.classList.remove('flex', 'opacity-100');
                }, 300);
            }
        }

        function confirmDelete(id, code) {
            const modal = document.getElementById('deleteModal');
            const card = modal.querySelector('.modal-card');
            document.getElementById('deleteForm').action = `/admin/bookings/${id}`;
            document.getElementById('deleteBookingCode').innerText = code;
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const card = modal.querySelector('.modal-card');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('absolute')) closeDeleteModal();
        });
    </script>
    <script src="{{ asset('js/admin/prerad.js') }}"></script>
    <script src="{{ asset('js/admin/checkin.js') }}"></script>
@endpush
