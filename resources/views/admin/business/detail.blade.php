@extends('admin.layout.app')

@section('title', 'Review Order #' . $booking->booking_code)

@section('content')
<div class="w-full pb-10 space-y-8">
    {{-- HEADER --}}
    <div class="flex items-center justify-between px-2">
        <div>
            <a href="{{ route('admin.business.index') }}" class="flex items-center gap-2 mb-2 text-xs font-bold text-indigo-500 uppercase hover:text-indigo-700 transition-all">
                <i class="fa-solid fa-arrow-left"></i> Back to Monitoring
            </a>
            <h2 class="text-3xl font-black text-slate-800 tracking-tighter">Review Order #{{ $booking->booking_code }}</h2>
            <p class="text-sm text-slate-500">Submitted by <span class="font-bold text-slate-700">{{ $booking->customer->name }}</span> on {{ $booking->created_at->format('d F Y') }}</p>
        </div>
        
        <div class="hidden md:block">
             <x-status-badge :status="$booking->status" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- KOLOM KIRI: TECHNICAL SPECS & ANOMALY --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- ANOMALY CARD --}}
            @if($isSpecial)
            <div class="p-8 bg-rose-50 border border-rose-100 rounded-[2.5rem] shadow-sm relative overflow-hidden">
                <div class="absolute top-0 right-0 p-6 opacity-10">
                    <i class="fa-solid fa-triangle-exclamation text-7xl text-rose-600"></i>
                </div>
                <div class="relative z-10">
                    <h4 class="flex items-center gap-2 text-rose-800 font-black uppercase text-xs tracking-[0.2em] mb-4">
                        <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                        Anomaly Detection System
                    </h4>
                    <p class="text-sm text-rose-700/80 mb-6 font-medium">Order ini memerlukan perhatian khusus karena ditemukan parameter yang tidak sesuai standar operasional rutin:</p>
                    
                    <ul class="space-y-3">
                        @foreach($reasons as $reason)
                        <li class="flex items-center gap-3 px-4 py-3 bg-white/60 border border-rose-200 rounded-2xl text-rose-800 text-sm font-bold">
                            <i class="fa-solid fa-check-double text-rose-500"></i>
                            {{ $reason }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @else
            <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-[2.5rem] flex items-center gap-4">
                <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-check text-emerald-500"></i>
                </div>
                <div>
                    <h5 class="font-black text-emerald-800 uppercase text-[10px] tracking-widest">Standard Order</h5>
                    <p class="text-xs text-emerald-600 font-medium">Semua parameter teknis berada dalam batas normal operasional.</p>
                </div>
            </div>
            @endif

            {{-- PRODUCT DETAIL --}}
            <div class="p-8 bg-white border border-slate-100 rounded-[2.5rem] shadow-sm">
                <h4 class="text-lg font-black text-slate-800 mb-8 flex items-center gap-3">
                    <i class="fa-solid fa-microscope text-indigo-500"></i>
                    Technical Specifications
                </h4>

                @foreach($booking->products as $product)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Product Name</p>
                        <p class="font-bold text-slate-800">{{ $product->product_name }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Dose Target</p>
                        <p class="font-black text-indigo-600">{{ $product->dmin }} - {{ $product->dmax }} kGy</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Exp. Temp</p>
                        <p class="font-bold text-slate-800">{{ $product->expect_temp ?? 'Ambient' }}°C</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Box</p>
                        <p class="font-bold text-slate-800">{{ $booking->total_qty }} {{ $product->unit }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- KOLOM KANAN: DECISION PANEL --}}
        <div class="space-y-6">
            <div class="p-8 bg-slate-900 rounded-[3rem] shadow-2xl text-white relative overflow-hidden">
                <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                
                <h4 class="text-sm font-black uppercase tracking-[0.2em] mb-8 text-slate-400">Decision Center</h4>
                
                @if($booking->status == 'pending')
                <div class="space-y-4">
                    <form action="{{ route('admin.business.approve', $booking->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <button type="submit" class="w-full py-4 bg-emerald-500 hover:bg-emerald-600 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-lg shadow-emerald-500/20 active:scale-95">
                            Approve For Processing
                        </button>
                    </form>
                    
                    <button class="w-full py-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                        Request Revision
                    </button>
                </div>
                <p class="mt-6 text-[10px] text-center text-slate-500 font-medium leading-relaxed">
                    Dengan menekan Approve, order ini akan diteruskan ke tim operasional gudang untuk proses Check-In.
                </p>
                @else
                <div class="p-6 border border-white/10 rounded-2xl bg-white/5 text-center">
                    <i class="fa-solid fa-circle-check text-emerald-400 mb-2 text-xl"></i>
                    <p class="text-xs font-bold uppercase tracking-widest">Action Completed</p>
                    <p class="text-[10px] text-slate-400 mt-1 uppercase">Order has been {{ $booking->status }}</p>
                </div>
                @endif
            </div>

            {{-- CUSTOMER INFO --}}
            <div class="p-8 bg-white border border-slate-100 rounded-[2.5rem] shadow-sm">
                <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6 text-center">Contact Entity</h4>
                <div class="flex flex-col items-center text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4 text-xl font-black text-slate-400">
                        {{ substr($booking->customer->name, 0, 1) }}
                    </div>
                    <p class="font-black text-slate-800">{{ $booking->customer->name }}</p>
                    <p class="text-xs text-slate-500 font-medium">{{ $booking->customer->company_name ?? 'Individual' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection