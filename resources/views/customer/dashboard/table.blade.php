<div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="text-left border-b border-gray-50">
                <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-widest">Ticket Information
                </th>
                <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-widest">Products & Details
                </th>
                {{-- <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">Schedule
                </th> --}}
                <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-widest text-center">Status
                </th>
                <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right">Actions
                </th>
            </tr>
        </thead>

        <tbody class="divide-y divide-gray-50">
            @forelse ($data as $booking)
                <tr class="transition-colors hover:bg-gray-50/50 group">
                    {{-- TICKET INFO --}}
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span
                                class="inline-block px-3 py-1 mb-1 text-sm font-black tracking-tight text-blue-600 rounded-lg bg-blue-50 w-fit">
                                #{{ $booking->booking_code }}
                            </span>
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Order ID:
                                00{{ $booking->id }}</span>
                        </div>
                    </td>

                    {{-- PRODUCTS --}}
                    <td class="px-8 py-6">
                        <div class="max-w-xs space-y-2">
                            @foreach ($booking->products as $product)
                                <div class="relative pl-4 border-l-2 border-blue-200">
                                    <p class="text-sm font-bold leading-tight text-gray-800">
                                        {{ $product->product_name }}</p>
                                    <p class="text-[11px] text-gray-500 font-medium">{{ $product->quantity }}
                                        {{ $product->unit }} • Min Dose: {{ $product->dmin }}• Max Dose:
                                        {{ $product->dmax }}</p>
                                </div>
                            @endforeach
                        </div>
                    </td>

                    {{-- SCHEDULE --}}
                    {{-- <td class="px-8 py-6">
                        @if ($booking->slot)
                            <div class="text-center">
                                <p class="text-sm font-bold text-gray-800">
                                    {{ \Carbon\Carbon::parse($booking->slot->date)->format('d M Y') }}</p>
                                <p class="text-[11px] text-gray-400 font-semibold">{{ $booking->slot->start_time }} -
                                    {{ $booking->slot->end_time }}</p>
                            </div>
                        @else
                            <p class="text-xs italic text-center text-gray-300">Waiting for slot...</p>
                        @endif
                    </td> --}}

                    {{-- STATUS --}}
                    <td class="px-8 py-6">
                        @php
                            $statusStyles = match ($booking->status) {
                                'pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                'approved' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                'processing' => 'bg-indigo-50 text-indigo-600 border-indigo-100',
                                'completed' => 'bg-blue-50 text-blue-600 border-blue-100',
                                default => 'bg-gray-50 text-gray-500 border-gray-100',
                            };

                            $statusLabel = match ($booking->status) {
                                'pending' => 'Product Has Not Arrived',
                                'approved' => 'Product Arrived',
                                'processing' => 'Processing',
                                'completed' => 'Completed',
                                default => ucfirst($booking->status),
                            };
                        @endphp

                        <div class="flex justify-center">
                            <span
                                class="px-4 py-1.5 text-[10px] font-black uppercase tracking-widest rounded-full border {{ $statusStyles }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                    </td>

                    {{-- ACTION --}}
                    <td class="px-8 py-6 text-right">
                        <button onclick="openModal({{ $booking->id }})"
                            class="p-2.5 bg-white border border-gray-100 text-blue-600 rounded-xl hover:bg-blue-600 hover:text-white hover:shadow-lg hover:shadow-blue-200 transition-all active:scale-95">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <i class="mb-4 text-5xl text-gray-100 fa-solid fa-inbox"></i>
                            <p class="font-bold text-gray-400">No bookings found</p>
                            <a href="{{ route('customer.booking.create') }}"
                                class="mt-2 text-sm font-bold text-blue-600 hover:underline">Start your first booking
                                &rarr;</a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="px-6 py-4 border-t border-gray-50">
    {{ $data->withQueryString()->links() }}
</div>

{{-- MODAL --}}
<div id="detailModal"
    class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-all duration-300">
    <div class="w-full max-w-2xl bg-white shadow-2xl rounded-[2.5rem] overflow-hidden transform transition-all scale-95 opacity-0"
        id="modalContainer">

        <div class="flex items-center justify-between p-8 border-b border-gray-50">
            <div>
                <h3 class="text-xl font-black text-gray-900">Booking Summary</h3>
                {{-- <p class="mt-1 text-xs font-bold tracking-widest text-gray-400 uppercase" id="modalTicketCode">Ticket:
                    Loading...</p> --}}
            </div>
            <button onclick="closeModal()"
                class="flex items-center justify-center w-10 h-10 text-gray-400 transition-colors rounded-full bg-gray-50 hover:text-red-500">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="modalContent" class="p-8 max-h-[60vh] overflow-y-auto custom-scrollbar">
            {{-- Content from AJAX --}}
        </div>

        <div class="flex items-center justify-between p-8 bg-gray-50">
            <button onclick="closeModal()" class="text-sm font-bold text-gray-500 hover:text-gray-700">Close
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
    function openModal(id) {
        const modal = document.getElementById('detailModal');
        const container = document.getElementById('modalContainer');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Modal Animation
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);

        fetch(`/customer/booking/${id}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById('modalContent').innerHTML = html;
                document.getElementById('printBtn').href = `/customer/booking/${id}/print`;
                // If you want to show the ticket code in title
                // document.getElementById('modalTicketCode').innerText = 'Ticket: ' + code;
            });
    }

    function closeModal() {
        const modal = document.getElementById('detailModal');
        const container = document.getElementById('modalContainer');

        container.classList.add('scale-95', 'opacity-0');
        container.classList.remove('scale-100', 'opacity-100');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    }
</script>

<script>
    // Menangkap klik pada link pagination agar tetap AJAX
    $(document).on('click', '.pagination a', function(event) {
        event.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        let query = $('#search').val();

        $.ajax({
            url: "{{ route('customer.dashboard') }}?page=" + page + "&search=" + query,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#table-container').html(response);
                // Scroll ke atas tabel agar user tahu konten berubah
                $('html, body').animate({
                    scrollTop: $("#table-container").offset().top - 100
                }, 200);
            }
        });
    });
</script>
