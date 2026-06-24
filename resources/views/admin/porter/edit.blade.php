@extends('admin.layout.app')
@section('title', 'Modify Porter Dossier')

@section('content')
    <div class="max-w-xl pb-20 mx-auto space-y-6">

        {{-- BACK LINK & HEADER --}}
        <div>
            <a href="{{ route('admin.porter.index') }}"
                class="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-400 hover:text-indigo-600 uppercase tracking-widest transition-colors mb-3">
                <i class="fa-solid fa-arrow-left"></i> Back to Fleet Directory
            </a>
            <h2 class="text-2xl font-black tracking-tight text-slate-800">
                Modify <span class="text-indigo-600">Porter File</span>
            </h2>
            <p class="text-xs text-slate-400 mt-0.5">Modify deployment data and status values for existing personnel.</p>
        </div>

        {{-- FORM CARD --}}
        <div class="p-6 overflow-hidden bg-white border shadow-sm border-slate-200/70 rounded-2xl sm:p-8">
            <form action="{{ route('admin.porter.update', $porter->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Input: Name --}}
                <div class="space-y-1.5">
                    <label for="name" class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="fa-solid fa-user text-slate-400 text-[11px]"></i>
                        </span>
                        <input type="text" name="name" id="name" value="{{ old('name', $porter->name) }}"
                            required placeholder="e.g., John Doe"
                            class="w-full pl-9 pr-4 py-2 text-xs font-semibold border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-2xs @error('name') border-rose-400 bg-rose-50/10 focus:ring-rose-500/10 @else border-slate-200 @enderror">
                    </div>
                    @error('name')
                        <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p>
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
                        <input type="text" name="phone" id="phone" value="{{ old('phone', $porter->phone) }}"
                            placeholder="e.g., +628123456789"
                            class="w-full pl-9 pr-4 py-2 text-xs font-mono font-semibold border rounded-xl bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-2xs @error('phone') border-rose-400 bg-rose-50/10 focus:ring-rose-500/10 @else border-slate-200 @enderror">
                    </div>
                    @error('phone')
                        <p class="text-[10px] text-rose-500 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Checkbox Toggle: Active Status --}}
                <div class="p-4 bg-slate-50/60 border border-slate-100 rounded-xl flex items-start gap-3.5 group">
                    <div class="flex items-center h-5 mt-0.5">
                        <input id="is_active" name="is_active" type="checkbox" value="1"
                            {{ old('is_active', $porter->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-indigo-600 bg-white rounded border-slate-300 focus:ring-indigo-500/20 focus:ring-offset-0">
                    </div>
                    <div>
                        <label for="is_active" class="block text-xs font-bold cursor-pointer select-none text-slate-700">
                            Active Operational Status
                        </label>
                        <p class="text-[10px] text-slate-400 leading-normal mt-0.5">
                            Toggle whether this operator is actively available to accept staging manifests or temporarily
                            blacklisted/suspended due to maintenance leave.
                        </p>
                    </div>
                </div>

                {{-- FORM ACTIONS --}}
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('admin.porter.index') }}"
                        class="px-4 py-2 text-xs font-bold transition-colors bg-white border text-slate-600 border-slate-200 rounded-xl hover:bg-slate-50 shadow-2xs">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98] rounded-xl shadow-sm transition-all">
                        Update Ledger Record
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
