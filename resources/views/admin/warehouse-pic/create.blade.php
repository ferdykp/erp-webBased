@extends('admin.layout.app')
@section('title', 'Onboard Warehouse PIC')

@section('content')
    <div class="max-w-xl pb-20 mx-auto space-y-6">

        {{-- BACK LINK & HEADER --}}
        <div>
            <a href="{{ route('admin.warehouse-pics.index') }}"
                class="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 hover:text-blue-600 uppercase tracking-widest transition-colors mb-3">
                <i class="fa-solid fa-arrow-left"></i> Back to PIC Directory
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-800">
                Register <span class="text-blue-600">New PIC</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Enlist a new supervising officer authorized for facility and manifest
                signature clearances.</p>
        </div>

        {{-- FORM CARD --}}
        <div class="p-6 overflow-hidden bg-white border shadow-sm border-slate-200/70 rounded-2xl sm:p-8">
            <form action="{{ route('admin.warehouse-pics.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Input: Name --}}
                <div class="space-y-1.5">
                    <label for="name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="fa-solid fa-user text-slate-400 text-[11px]"></i>
                        </span>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            placeholder="e.g., Alexander Wright"
                            class="w-full pl-9 pr-4 py-2 text-xs font-semibold border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-2xs {{ $errors->has('name') ? 'border-rose-400 bg-rose-50/10 focus:ring-rose-500/10' : 'border-slate-200' }}">
                    </div>
                    @error('name')
                        <p class="text-[10px] text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input: Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Email Address <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="fa-solid fa-envelope text-slate-400 text-[11px]"></i>
                        </span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            placeholder="e.g., email@company.com"
                            class="w-full pl-9 pr-4 py-2 text-xs font-semibold border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-2xs {{ $errors->has('email') ? 'border-rose-400 bg-rose-50/10 focus:ring-rose-500/10' : 'border-slate-200' }}">
                    </div>
                    @error('email')
                        <p class="text-[10px] text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input: Phone --}}
                <div class="space-y-1.5">
                    <label for="phone" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Phone Number
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="fa-solid fa-phone text-slate-400 text-[11px]"></i>
                        </span>
                        <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                            placeholder="e.g., +628123456789"
                            class="w-full pl-9 pr-4 py-2 text-xs font-mono font-semibold border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 transition-all shadow-2xs {{ $errors->has('phone') ? 'border-rose-400 bg-rose-50/10 focus:ring-rose-500/10' : 'border-slate-200' }}">
                    </div>
                    @error('phone')
                        <p class="text-[10px] text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Input: Shift Kerja (Select) --}}
                <div class="space-y-1.5">
                    <label for="shift" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Operational Work Shift
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none z-10">
                            <i class="fa-solid fa-clock-rotate-left text-slate-400 text-[11px]"></i>
                        </span>
                        <select name="shift" id="shift"
                            class="w-full py-2 pr-4 text-xs font-semibold transition-all bg-white border appearance-none pl-9 border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/10 focus:border-blue-500 shadow-2xs">
                            <option value="" disabled selected>Select Duty Shift</option>
                            <option value="Morning" {{ old('shift') == 'Morning' ? 'selected' : '' }}>Morning (06:00 -
                                14:00)</option>
                            <option value="Afternoon" {{ old('shift') == 'Afternoon' ? 'selected' : '' }}>Afternoon (14:00
                                - 22:00)</option>
                            <option value="Night" {{ old('shift') == 'Night' ? 'selected' : '' }}>Night (22:00 - 06:00)
                            </option>
                        </select>
                        <span
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-slate-400 text-[10px]">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </div>
                    @error('shift')
                        <p class="text-[10px] text-rose-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Checkbox Toggle: Active Status --}}
                <div class="p-4 bg-slate-50/60 border border-slate-100 rounded-xl flex items-start gap-3.5 group">
                    <div class="flex items-center h-5 mt-0.5">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                            {{ old('is_active', true) ? 'checked' : '' }}
                            class="w-4 h-4 text-blue-600 bg-white rounded border-slate-300 focus:ring-blue-500/20 focus:ring-offset-0">
                    </div>
                    <div>
                        <label for="is_active" class="block text-xs font-bold cursor-pointer select-none text-slate-700">
                            Immediate Account Activation
                        </label>
                        <p class="text-[10px] text-slate-400 leading-normal mt-0.5">
                            If enabled, this supervisor will be eligible immediately to log authorizations and accept
                            accountability protocols for incoming freight tasks.
                        </p>
                    </div>
                </div>

                {{-- FORM ACTIONS --}}
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.warehouse-pics.index') }}"
                        class="px-4 py-2 text-xs font-bold transition-colors bg-white border text-slate-600 border-slate-200 rounded-xl hover:bg-slate-50 shadow-2xs">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.98] rounded-xl shadow-sm transition-all">
                        Onboard Officer
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
