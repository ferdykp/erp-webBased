@extends('admin.layout.app')

@section('title', 'Review Order #' . $booking->booking_code)

@section('content')
    <div class="w-full pb-10 space-y-6 md:space-y-8">
        {{-- HEADER --}}
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div class="text-center md:text-left">
                <a href="{{ route('admin.business.index') }}"
                    class="inline-flex items-center gap-2 mb-4 text-[10px] font-black text-indigo-500 uppercase transition-all hover:text-indigo-700 tracking-widest">
                    <i class="fa-solid fa-arrow-left"></i> Back to Monitoring
                </a>
                <h2 class="text-2xl font-black tracking-tighter md:text-3xl text-slate-800">Order
                    #{{ $booking->booking_code }}</h2>
                <p class="mt-1 text-xs font-medium text-slate-500">
                    PIC: <span class="font-bold text-slate-700">{{ $booking->customer->contacts->first()->name }}</span>
                    • {{ $booking->created_at->format('d M Y') }}
                </p>
            </div>

            <div class="flex justify-center md:block">
                <x-status-badge :status="$booking->status" />
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">

            {{-- LEFT COLUMN: SPECS & ANOMALY --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- ANOMALY CARD --}}
                @if ($isSpecial)
                    <div
                        class="p-6 md:p-8 bg-rose-50 border border-rose-100 rounded-[2rem] md:rounded-[2.5rem] shadow-sm relative overflow-hidden">
                        <i
                            class="absolute top-0 right-0 p-6 opacity-5 fa-solid fa-triangle-exclamation text-8xl text-rose-600"></i>
                        <div class="relative z-10">
                            <h4
                                class="flex items-center gap-2 text-rose-800 font-black uppercase text-[10px] tracking-[0.2em] mb-4">
                                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                                Anomaly Detection
                            </h4>
                            <p class="mb-6 text-sm font-semibold leading-relaxed text-left text-rose-700/80">
                                Order ini memerlukan verifikasi manual karena melebihi parameter standar:
                            </p>

                            <div class="grid grid-cols-1 gap-3">
                                @foreach ($reasons as $reason)
                                    <div
                                        class="flex items-center gap-3 px-5 py-4 text-xs font-black border shadow-sm bg-white/80 border-rose-200 rounded-2xl text-rose-800">
                                        <i class="fa-solid fa-shield-virus text-rose-500"></i>
                                        {{ $reason }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div
                        class="p-6 bg-emerald-50 border border-emerald-100 rounded-[2rem] flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                        <div class="flex items-center justify-center w-12 h-12 bg-white shadow-sm rounded-2xl shrink-0">
                            <i class="fa-solid fa-check-double text-emerald-500"></i>
                        </div>
                        <div>
                            <h5 class="font-black text-emerald-800 uppercase text-[10px] tracking-widest">Safe Standard
                                Order</h5>
                            <p class="text-xs font-medium text-emerald-600">All technical parameters are within normal
                                operating range.</p>
                        </div>
                    </div>
                @endif

                {{-- PRODUCT DETAIL --}}
                <div class="p-6 md:p-8 bg-white border border-slate-100 rounded-[2rem] md:rounded-[2.5rem] shadow-sm">
                    <h4 class="flex items-center gap-3 mb-8 text-base font-black md:text-lg text-slate-800">
                        <i class="text-indigo-500 fa-solid fa-flask-vial"></i>
                        Technical Specifications
                    </h4>

                    @foreach ($booking->products as $product)
                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-4">
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Product</p>
                                <p class="font-bold break-words text-slate-800">{{ $product->product_name }}</p>
                            </div>
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Dose Target
                                </p>
                                <p class="font-black text-indigo-600">{{ $product->dmin }} - {{ $product->dmax }} kGy</p>
                            </div>
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Exp. Temp</p>
                                <p class="font-bold text-slate-800">{{ $product->expect_temp ?? 'Ambient' }}°C</p>
                            </div>
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total
                                    Quantity</p>
                                <p class="font-bold text-slate-800">{{ $booking->total_qty }} {{ $product->unit }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- RIGHT COLUMN: ACTION PANEL --}}
            <div class="space-y-6">
                <div class="p-8 bg-slate-900 rounded-[2.5rem] shadow-2xl text-white relative overflow-hidden">
                    <div class="absolute w-32 h-32 rounded-full -top-10 -left-10 bg-indigo-500/10 blur-2xl"></div>

                    <h4 class="text-xs font-black uppercase tracking-[0.2em] mb-8 text-slate-500 text-center">Admin Approval
                        Center</h4>

                    @if ($booking->status == 'pending')
                        <div class="space-y-4">
                            <form action="{{ route('admin.business.approve', $booking->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit"
                                    class="w-full py-4 text-[11px] font-black tracking-[0.15em] uppercase transition-all shadow-xl bg-emerald-500 hover:bg-emerald-600 rounded-2xl shadow-emerald-500/20 active:scale-95">
                                    Approve & Process
                                </button>
                            </form>

                            <button
                                class="w-full py-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                                Request Revision
                            </button>
                        </div>
                        <p class="mt-6 text-[10px] text-center text-slate-500 font-medium leading-relaxed italic">
                            Approving will forward this order to the warehouse operational team.
                        </p>
                    @else
                        <div class="py-10 text-center border border-white/5 rounded-[2rem] bg-white/5">
                            <div
                                class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-emerald-500/20">
                                <i class="text-xl fa-solid fa-check text-emerald-400"></i>
                            </div>
                            <p class="mb-1 text-xs font-black tracking-widest uppercase">Process Completed</p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-tighter">Order status is
                                {{ $booking->status }}</p>
                        </div>
                    @endif
                </div>

                {{-- CUSTOMER CARD --}}
                <div
                    class="p-8 bg-white border border-slate-100 rounded-[2.5rem] shadow-sm flex flex-col items-center text-center">
                    <h4 class="mb-6 text-[10px] font-black tracking-widest uppercase text-slate-400">Requestor Entity</h4>
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-4 text-xl font-black rounded-[1.5rem] bg-indigo-50 text-indigo-600 shadow-inner">
                        {{ substr($booking->customer->contacts->first()->name, 0, 1) }}
                    </div>
                    <p class="font-black leading-tight text-slate-800">{{ $booking->customer->contacts->first()->name }}
                    </p>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        {{ $booking->customer->company_name ?? 'Individual Entity' }}</p>

                    <div class="w-full h-px my-6 bg-slate-50"></div>

                    <a href="mailto:{{ $booking->customer->email }}"
                        class="text-[11px] font-black text-indigo-500 uppercase hover:underline">
                        Send Message <i class="ml-1 fa-solid fa-paper-plane"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
