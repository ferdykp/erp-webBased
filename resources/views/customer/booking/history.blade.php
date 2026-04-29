@extends('layouts.master')

@section('content')
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- HEADER SECTION --}}
        <div class="flex flex-col justify-between gap-4 px-4 md:flex-row md:items-center">
            <div>
                <h3 class="text-2xl font-black tracking-tight text-gray-900">Booking History</h3>
                <p class="mt-1 text-sm text-gray-400">Monitoring Status and Detail Progress of the Product</p>
            </div>

            {{-- SEARCH/FILTER (Optional) --}}
            <div class="relative">
                <i class="absolute text-sm text-gray-400 -translate-y-1/2 fa-solid fa-magnifying-glass left-4 top-1/2"></i>
                <input type="text" placeholder="Cari Kode Booking..."
                    class="w-full py-3 pr-5 text-sm font-bold text-gray-700 transition-all bg-white border border-gray-100 shadow-sm outline-none pl-11 rounded-2xl focus:ring-4 focus:ring-blue-100 md:w-64">
            </div>
        </div>

        {{-- TABLE CARD --}}
        <div class="bg-white border border-gray-100 shadow-sm rounded-[2.5rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Info
                                Booking</th>
                            <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]"> PIC Name
                            </th>
                            {{-- <th class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">Jadwal
                                Sterilisasi</th> --}}
                            <th
                                class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">
                                Item</th>
                            <th
                                class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] text-center">
                                Status</th>
                            <th
                                class="px-8 py-5 text-[11px] font-black text-gray-400 uppercase tracking-[0.2em] text-right">
                                Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        {{-- Contoh Data --}}
                        @forelse ($history as $item)
                            <tr class="transition-colors group hover:bg-blue-50/30">
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-xs font-black tracking-wider text-blue-600 uppercase">#{{ $item->booking_code }}</span>
                                        <span
                                            class="text-[10px] font-bold text-gray-400 mt-1 uppercase">{{ $item->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <div class="flex flex-col items-center">
                                        @if ($item->customer && $item->customer->contacts->isNotEmpty())
                                            {{-- Mengambil nama dari kontak pertama milik customer --}}
                                            <span class="text-sm font-bold text-gray-700">
                                                {{ $item->customer->contacts->first()->name }}
                                            </span>
                                        @else
                                            <span class="text-sm font-bold text-gray-400">No PIC Detail</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 text-gray-400 transition-colors bg-gray-50 rounded-xl group-hover:bg-white">
                                            <i class="text-sm fa-solid fa-box-archive"></i>
                                        </div>
                                        <div class="flex flex-col">
                                            @foreach ($item->products as $product)
                                                <span class="text-sm font-bold text-gray-700">
                                                    {{ $product->product_name }}
                                                </span>
                                            @endforeach <span
                                                class="text-sm font-bold text-gray-700">{{ $item->products->count() }}
                                                Produk</span>
                                            <span
                                                class="text-[10px] font-bold text-gray-400 uppercase tracking-tighter">E-Beam
                                                Sterilization</span>
                                        </div>
                                    </div>
                                </td>
                                {{-- <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <span
                                            class="text-sm font-bold text-gray-700">{{ \Carbon\Carbon::parse($item->slot->date)->format('d M Y') }}</span>
                                        <span
                                            class="text-[10px] font-bold text-blue-500 uppercase italic">{{ $item->slot->start_time }}
                                            - {{ $item->slot->end_time }}</span>
                                    </div>
                                </td> --}}
                                <td class="px-8 py-6 text-center">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-amber-50 text-amber-600 border-amber-100',
                                            'approved' => 'bg-blue-50 text-blue-600 border-blue-100',
                                            'completed' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
                                            'rejected' => 'bg-red-50 text-red-600 border-red-100',
                                        ];
                                        $currentStatus = strtolower($item->status);
                                    @endphp
                                    <span
                                        class="px-4 py-1.5 rounded-full border text-[10px] font-black uppercase tracking-widest {{ $statusClasses[$currentStatus] ?? 'bg-gray-50 text-gray-500' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2">
                                        {{-- <a href="{{ route('customer.booking.show', $item->id) }}"
                                            class="p-2.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all"
                                            title="Detail">
                                            <i class="text-lg fa-solid fa-circle-info"></i>
                                        </a> --}}
                                        <a href="{{ route('customer.booking.print', $item->id) }}"
                                            class="p-2.5 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-xl transition-all"
                                            title="Cetak Tiket">
                                            <i class="text-lg fa-solid fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-20 text-center">
                                    <div class="flex flex-col items-center">
                                        <div
                                            class="flex items-center justify-center w-20 h-20 mb-4 rounded-full bg-gray-50">
                                            <i class="text-3xl text-gray-200 fa-solid fa-clock-rotate-left"></i>
                                        </div>
                                        <p class="text-xs font-bold tracking-widest text-gray-400 uppercase">Belum ada
                                            riwayat booking</p>
                                        {{-- <a href="{{ route('customer.booking.date') }}"
                                            class="mt-4 text-sm font-black text-blue-600 hover:underline">Mulai Booking
                                            Sekarang &rarr;</a> --}}
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER / PAGINATION --}}
            @if ($history->hasPages())
                <div class="px-8 py-6 border-t bg-gray-50/30 border-gray-50">
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
