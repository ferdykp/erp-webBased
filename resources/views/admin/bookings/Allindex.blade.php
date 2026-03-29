@extends('admin.layout.app')

@section('title', 'Bookings Management')

@section('content')
    <div class="w-full pb-10 space-y-8">

        {{-- HEADER SECTION --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">{{ $pageTitle ?? 'All Bookings' }}</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Monitoring and managing warehouse reservation flow.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 px-6 py-3 bg-white border shadow-sm border-slate-100 rounded-2xl">
                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-black tracking-widest uppercase text-slate-400">Total Bookings:</span>
                    <span class="font-black text-slate-800">{{ $bookings->total() }}</span>
                </div>
            </div>
        </div>

        {{-- NOTIFICATION ALERT --}}
        @if (session('success'))
            <div id="status-alert" class="fixed z-[100] top-10 right-10 animate-in slide-in-from-right-10 duration-500">
                <div
                    class="flex items-center gap-4 px-8 py-5 bg-white border-l-4 shadow-2xl border-emerald-500 rounded-2xl">
                    <div class="flex items-center justify-center w-10 h-10 bg-emerald-50 rounded-xl">
                        <i class="fa-solid fa-check text-emerald-600"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black tracking-widest uppercase text-slate-400">Success</p>
                        <p class="font-bold text-slate-700">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- TABLE CARD --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[3rem] overflow-hidden">
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">
                            <th class="px-8 py-4">Customer Details</th>
                            <th class="px-6 py-4 text-center">Schedule</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Arrival Log</th>
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
                                            class="flex items-center justify-center w-12 h-12 font-black text-blue-700 transition-all duration-300 bg-blue-50 rounded-2xl group-hover:bg-blue-600 group-hover:text-white">
                                            {{ strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-black tracking-tight text-slate-800">
                                                {{ $booking->customer->contacts->first()->name ?? 'Guest' }}</p>
                                            <p
                                                class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-0.5">
                                                #BOK-{{ $booking->id }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <p class="text-sm font-black text-slate-700">{{ $booking->created_at->format('d M Y') }}
                                    </p>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mt-1">
                                        {{ $booking->created_at->format('H:i') }} WIB</p>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    @php
                                        $statusConfig = [
                                            'pending' => [
                                                'bg' => 'bg-amber-50',
                                                'text' => 'text-amber-600',
                                                'dot' => 'bg-amber-500',
                                                'label' => 'Unarrived',
                                            ],
                                            'approved' => [
                                                'bg' => 'bg-emerald-50',
                                                'text' => 'text-emerald-600',
                                                'dot' => 'bg-emerald-500',
                                                'label' => 'Arrived',
                                            ],
                                            'processing' => [
                                                'bg' => 'bg-blue-50',
                                                'text' => 'text-blue-600',
                                                'dot' => 'bg-blue-500',
                                                'label' => 'Processing',
                                            ],
                                            'completed' => [
                                                'bg' => 'bg-slate-100',
                                                'text' => 'text-slate-600',
                                                'dot' => 'bg-slate-500',
                                                'label' => 'Completed',
                                            ],
                                        ][$booking->status] ?? [
                                            'bg' => 'bg-gray-50',
                                            'text' => 'text-gray-600',
                                            'dot' => 'bg-gray-500',
                                            'label' => 'Unknown',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest {{ $statusConfig['bg'] }} {{ $statusConfig['text'] }}">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full {{ $statusConfig['dot'] }} {{ $booking->status == 'pending' ? 'animate-pulse' : '' }}"></span>
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    @if ($booking->arrival_time)
                                        <p class="text-sm font-black text-blue-600">
                                            {{ \Carbon\Carbon::parse($booking->arrival_time)->format('H:i') }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">
                                            {{ \Carbon\Carbon::parse($booking->arrival_time)->format('d M Y') }}</p>
                                    @else
                                        <span
                                            class="text-[10px] italic font-bold text-slate-300 uppercase tracking-widest">Waiting...</span>
                                    @endif
                                </td>
                                <td
                                    class="px-8 py-6 rounded-r-[2rem] border-y border-r border-transparent group-hover:border-slate-100">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button"
                                            onclick="openDetailModal({{ $booking->load(['pallets', 'customer', 'products', 'batches'])->toJson() }})"
                                            class="flex items-center justify-center w-10 h-10 text-blue-600 transition-all duration-300 bg-blue-50 rounded-xl hover:bg-blue-600 hover:text-white">
                                            <i class="text-xs fa-solid fa-eye"></i>
                                        </button>
                                        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST"
                                            class="flex items-center gap-2">
                                            @csrf @method('PUT')
                                            <div class="relative">
                                                <select name="status"
                                                    class="py-2.5 pl-4 pr-10 text-[10px] font-black uppercase tracking-widest border border-slate-100 bg-slate-50 rounded-xl focus:ring-4 focus:ring-blue-50 outline-none appearance-none cursor-pointer transition-all">
                                                    <option value="pending"
                                                        {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending
                                                    </option>
                                                    <option value="approved"
                                                        {{ $booking->status == 'approved' ? 'selected' : '' }}>Approved
                                                    </option>
                                                    <option value="processing"
                                                        {{ $booking->status == 'processing' ? 'selected' : '' }}>Process
                                                    </option>
                                                    <option value="completed"
                                                        {{ $booking->status == 'completed' ? 'selected' : '' }}>Done
                                                    </option>
                                                </select>
                                                <i
                                                    class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[8px] text-slate-400 pointer-events-none"></i>
                                            </div>
                                            <button
                                                class="flex items-center justify-center w-10 h-10 text-white transition-all bg-slate-900 rounded-xl hover:bg-black active:scale-90">
                                                <i class="text-xs fa-solid fa-rotate"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($bookings->hasPages())
                <div class="px-10 py-8 border-t bg-slate-50/50 border-slate-100">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL DETAIL BOOKING --}}
    <div id="detailModal"
        class="fixed inset-0 z-[150] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-500 p-4">
        <div id="detailCard"
            class="relative w-full max-w-5xl max-h-[95vh] overflow-hidden bg-white shadow-2xl rounded-[3.5rem] transform scale-95 opacity-0 transition-all duration-500 flex flex-col">

            {{-- Header --}}
            <div class="flex items-start justify-between px-12 pt-12 pb-6">
                <div>
                    <h2 class="text-3xl font-black tracking-tighter text-slate-800">Reservation Details</h2>
                    <div class="flex items-center gap-3 mt-2">
                        <span id="detail_booking_code"
                            class="px-3 py-1 text-[10px] font-black tracking-widest text-blue-600 bg-blue-50 rounded-lg uppercase"></span>
                        <span id="detail_status_badge"
                            class="px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg"></span>
                    </div>
                </div>
                <button onclick="closeDetailModal()"
                    class="flex items-center justify-center w-12 h-12 transition-all bg-slate-50 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-2xl">
                    <i class="text-xl fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="flex-1 px-12 pb-12 space-y-10 overflow-y-auto scrollbar-hide">
                {{-- INFO GRID --}}
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="p-8 bg-slate-50 border border-slate-100 rounded-[2.5rem]">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Client Information
                        </p>
                        <h4 class="text-xl font-black text-slate-800" id="detail_customer_name">-</h4>
                        <p class="mt-1 text-sm font-medium text-slate-500" id="detail_customer_email">-</p>
                    </div>
                    <div class="p-8 bg-indigo-50 border border-indigo-100 rounded-[2.5rem]">
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-4">Scheduling</p>
                        <div class="space-y-2">
                            <p class="flex justify-between text-xs font-bold text-slate-600">Booked: <span
                                    class="text-indigo-700" id="detail_booking_date">-</span></p>
                            <p class="flex justify-between text-xs font-bold text-slate-600">Arrived: <span
                                    class="text-indigo-700" id="detail_arrival_time">-</span></p>
                        </div>
                    </div>
                </div>

                {{-- MINIMALIST RESOURCE & PALLET SUMMARY --}}
                <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                    {{-- Resource Section --}}
                    <div class="md:col-span-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">Warehouse Resource
                        </p>
                        <div class="flex items-center gap-4 group">
                            <div
                                class="flex items-center justify-center w-12 h-12 transition-colors bg-white border shadow-sm border-slate-100 rounded-2xl group-hover:bg-blue-50">
                                <i class="text-blue-600 fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">PIC Warehouse</p>
                                <p class="text-sm font-black text-slate-800" id="detail_pic_warehouse">-</p>
                            </div>
                        </div>
                    </div>

                    {{-- Pallet Section --}}
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Allocated Pallets
                            </p>
                            <span
                                class="text-[9px] font-bold text-blue-500 bg-blue-50 px-2 py-0.5 rounded-md uppercase tracking-tighter"
                                id="pallet_count_text">0 Pallets</span>
                        </div>
                        <div id="detail_pallets_list" class="flex flex-wrap gap-2 pt-1">
                            {{-- Pallets injected here --}}
                        </div>
                    </div>
                </div>

                {{-- BATCH DISTRIBUTION TABLE --}}
                <section id="batch_result_section" class="pt-8 space-y-4 border-t border-slate-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex items-center justify-center w-10 h-10 bg-emerald-50 rounded-xl text-emerald-600">
                                <i class="fa-solid fa-layer-group"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-emerald-500 uppercase tracking-widest">Production
                                    Distribution</p>
                                <h4 class="text-sm font-black text-slate-800">Detail Hasil Pembagian Batch & Produk</h4>
                            </div>
                        </div>
                        <span id="batch_count_badge"
                            class="px-4 py-1.5 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-full uppercase tracking-widest">0
                            Batches</span>
                    </div>

                    <div class="overflow-hidden border border-emerald-100 rounded-[2.5rem] shadow-sm">
                        <table class="w-full text-left">
                            <thead class="bg-emerald-50/50 text-[9px] font-black text-emerald-600 uppercase">
                                <tr>
                                    <th class="px-8 py-5">Batch ID</th>
                                    <th class="px-6 py-5">Porter Specification</th>
                                    <th class="px-6 py-5">Product Details</th>
                                    <th class="px-6 py-5 text-center">Target Dose</th>
                                    <th class="px-8 py-5 text-right">Quantity</th>
                                </tr>
                            </thead>
                            <tbody id="detail_batch_table_body" class="divide-y divide-emerald-50/50"></tbody>
                            <tfoot class="font-black bg-slate-50 text-slate-800">
                                <tr>
                                    <td colspan="4" class="px-8 py-5 text-xs tracking-widest uppercase text-slate-400">
                                        Total Accumulation</td>
                                    <td id="batch_total_sum" class="px-8 py-5 text-sm text-right text-emerald-700">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            </div>

            <div class="flex justify-end px-12 py-8 border-t bg-slate-50 border-slate-100">
                <button onclick="closeDetailModal()"
                    class="px-10 py-4 bg-slate-900 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-2xl hover:bg-black transition-all">Close
                    Details</button>
            </div>
        </div>
    </div>

    <script>
        function openDetailModal(booking) {
            const modal = document.getElementById('detailModal');
            const card = document.getElementById('detailCard');

            // 1. Informasi Dasar & Identitas
            document.getElementById('detail_booking_code').innerText = booking.booking_code || `#BOK-${booking.id}`;

            const statusBadge = document.getElementById('detail_status_badge');
            const statusClasses = {
                'pending': 'bg-amber-100 text-amber-700',
                'approved': 'bg-emerald-100 text-emerald-700',
                'processing': 'bg-blue-100 text-blue-700',
                'completed': 'bg-slate-200 text-slate-700'
            };
            statusBadge.innerText = booking.status.toUpperCase();
            statusBadge.className =
                `px-3 py-1 text-[10px] font-black uppercase tracking-widest rounded-lg ${statusClasses[booking.status] || 'bg-gray-100'}`;

            document.getElementById('detail_customer_name').innerText = booking.customer?.name || 'Guest';
            document.getElementById('detail_customer_email').innerText = booking.customer?.email || '-';

            const formatDate = (dateStr) => {
                if (!dateStr) return '-';
                const d = new Date(dateStr);
                return d.toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) + ' ' +
                    d.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
            };

            document.getElementById('detail_booking_date').innerText = formatDate(booking.created_at);
            document.getElementById('detail_arrival_time').innerText = booking.arrival_time ? formatDate(booking
                .arrival_time) + ' WIB' : 'Waiting...';
            document.getElementById('detail_pic_warehouse').innerText = booking.pic_warehouse || 'Not Assigned';

            // 2. Render Palet (Minimalis)
            const palletContainer = document.getElementById('detail_pallets_list');
            const palletCountText = document.getElementById('pallet_count_text');
            palletContainer.innerHTML = '';

            if (booking.pallets && booking.pallets.length > 0) {
                palletCountText.innerText = `${booking.pallets.length} Pallets`;
                booking.pallets.forEach(p => {
                    palletContainer.innerHTML += `
                    <div class="flex items-center gap-2 px-3 py-2 transition-all border bg-slate-50 border-slate-100 rounded-xl hover:border-blue-200">
                        <i class="text-[10px] text-blue-500 fa-solid fa-box-archive"></i>
                        <span class="text-[10px] font-black text-slate-700 uppercase tracking-tighter">${p.pallet_number}</span>
                    </div>`;
                });
            } else {
                palletCountText.innerText = `0 Pallets`;
                palletContainer.innerHTML = '<p class="text-xs italic font-medium text-slate-300">No pallets assigned</p>';
            }

            // 3. Render Batch (Porter Spesifik dari Tiap Batch)
            const batchTableBody = document.getElementById('detail_batch_table_body');
            batchTableBody.innerHTML = '';

            const mainProduct = (booking.products && booking.products.length > 0) ? booking.products[0] : {};
            const pUnit = mainProduct.unit || 'Unit';

            if (booking.batches && booking.batches.length > 0) {
                let totalQty = 0;
                booking.batches.forEach((batch, i) => {
                    const qty = parseFloat(batch.quantity || 0);
                    totalQty += qty;

                    batchTableBody.innerHTML += `
                    <tr class="transition-colors hover:bg-emerald-50/30">
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-[10px] font-black rounded-lg uppercase tracking-tighter">BTCH-${String(i+1).padStart(2, '0')}</span>
                        </td>
                        <td class="px-6 py-6">
                            <div class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                                <div>
                                    <p class="text-xs font-black text-slate-800">${batch.porter_name || 'Not Assigned'}</p>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">In Charge</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-6">
                            <p class="text-sm font-black text-slate-800">${mainProduct.product_name || 'N/A'}</p>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">${mainProduct.product_type || '-'}</p>
                        </td>
                        <td class="px-6 py-6 text-center">
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-black rounded-lg uppercase">${mainProduct.target_dose || '0'} <span class="text-[8px] opacity-70">kGy</span></span>
                        </td>
                        <td class="px-8 py-6 font-black text-right text-slate-800">
                            ${qty.toLocaleString()} <span class="text-[9px] text-slate-400 font-bold ml-1">${pUnit}</span>
                        </td>
                    </tr>`;
                });

                document.getElementById('batch_total_sum').innerText = `${totalQty.toLocaleString()} ${pUnit}`;
                document.getElementById('batch_count_badge').innerText = `${booking.batches.length} Batches`;
                document.getElementById('batch_result_section').classList.remove('hidden');
            } else {
                document.getElementById('batch_result_section').classList.add('hidden');
            }

            // Tampilkan Modal dengan Animasi
            modal.classList.replace('opacity-0', 'opacity-100');
            modal.classList.replace('pointer-events-none', 'pointer-events-auto');
            setTimeout(() => {
                card.classList.replace('scale-95', 'scale-100');
                card.classList.replace('opacity-0', 'opacity-100');
            }, 50);
        }

        function closeDetailModal() {
            const modal = document.getElementById('detailModal');
            const card = document.getElementById('detailCard');
            card.classList.replace('scale-100', 'scale-95');
            card.classList.replace('opacity-100', 'opacity-0');
            setTimeout(() => {
                modal.classList.replace('opacity-100', 'opacity-0');
                modal.classList.replace('pointer-events-auto', 'pointer-events-none');
            }, 300);
        }

        // Auto-hide alert
        setTimeout(() => {
            const alert = document.getElementById('status-alert');
            if (alert) alert.style.display = 'none';
        }, 5000);
    </script>
@endsection
