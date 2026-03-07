@extends('admin.layout.app')

@section('title', 'Business Monitoring')

@section('content')
    <div class="w-full pb-10 space-y-8">
        {{-- HEADER SECTION --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-4xl font-black tracking-tighter text-slate-800">Business Monitoring</h2>
                <p class="mt-1 text-sm font-medium text-slate-500">Monitoring approval, anomaly detection, and business flow.</p>
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-3 px-6 py-3 bg-white border shadow-sm border-slate-100 rounded-2xl">
                    <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
                    <span class="text-xs font-black tracking-widest uppercase text-slate-400">Total Orders:</span>
                    <span class="font-black text-slate-800">{{ $bookings->total() }}</span>
                </div>
            </div>
        </div>

        {{-- ALERT NOTIFICATION --}}
        @if (session('success'))
            <div id="status-alert" class="fixed z-[100] top-10 right-10 animate-in slide-in-from-right-10 duration-500">
                <div class="flex items-center gap-4 px-8 py-5 bg-white border-l-4 shadow-2xl border-emerald-500 rounded-2xl">
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

        {{-- MONITORING TABLE --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[3rem] overflow-hidden">
            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-y-3">
                    <thead>
                        <tr class="text-[10px] font-black tracking-[0.2em] text-slate-400 uppercase">
                            <th class="px-8 py-4">Booking Reference</th>
                            <th class="px-6 py-4">Customer</th>
                            <th class="px-6 py-4 text-center">Tech Analysis</th>
                            <th class="px-6 py-4 text-center">Current Status</th>
                            <th class="px-8 py-4 text-right">Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            @php
                                $product = $booking->products->first();
                                // Logic Anomaly Check
                                $isAnomaly = ($product && ($product->dmax > 50 || !empty($product->expect_temp)));
                            @endphp
                            <tr class="transition-all duration-300 bg-white group hover:bg-slate-50/50">
                                <td class="px-8 py-6 rounded-l-[2rem] border-y border-l border-transparent group-hover:border-slate-100">
                                    <div class="flex flex-col">
                                        <span class="font-black tracking-tight text-slate-800">#{{ $booking->booking_code }}</span>
                                        <span class="text-[9px] font-bold text-slate-400 uppercase mt-1">{{ $booking->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-6 border-transparent border-y group-hover:border-slate-100">
                                    <p class="text-sm font-bold text-slate-700">{{ $booking->customer->name ?? 'Guest' }}</p>
                                    <p class="text-[10px] text-slate-400 font-medium">{{ $booking->customer->company_name ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    @if($isAnomaly)
                                        <span class="px-3 py-1.5 bg-rose-50 text-rose-600 text-[9px] font-black rounded-lg uppercase tracking-widest border border-rose-100">
                                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> Special Order
                                        </span>
                                    @else
                                        <span class="px-3 py-1.5 bg-slate-50 text-slate-400 text-[9px] font-black rounded-lg uppercase tracking-widest">
                                            Standard
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-6 text-center border-transparent border-y group-hover:border-slate-100">
                                    <x-status-badge :status="$booking->status" />
                                </td>
                                <td class="px-8 py-6 rounded-r-[2rem] border-y border-r border-transparent group-hover:border-slate-100">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.business.detail', $booking->id) }}" 
                                           class="px-5 py-2.5 text-[10px] font-black text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-600 hover:text-white transition-all">
                                            REVIEW DETAILS
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-10 py-8 border-t bg-slate-50/50 border-slate-100">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>
@endsection