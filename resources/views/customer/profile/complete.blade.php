@extends('layouts.master')

@section('title', 'Complete Profile')

@section('content')

    <section class="flex items-center justify-center min-h-screen px-4 py-12 bg-gradient-to-br from-slate-100 to-slate-200"
        x-data="{
            step: 1,
            totalSteps: 2,
            validateAndNext() {
                const inputs = document.querySelectorAll('#step-' + this.step + ' [required]');
                let isValid = true;
                inputs.forEach(input => {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        isValid = false;
                    }
                });
                if (isValid) this.step++;
            }
        }">

        <div class="w-full max-w-2xl overflow-hidden bg-white shadow-2xl rounded-[2.5rem] border border-white">

            {{-- STEP TRACKER --}}
            <div class="px-10 pt-12 pb-8 bg-gradient-to-b from-slate-50 to-white">
                <div class="relative flex items-center justify-between">
                    {{-- Background Line --}}
                    <div class="absolute top-5 left-0 w-full h-[2px] bg-slate-100 -z-0">
                        <div class="h-full transition-all duration-700 bg-blue-500"
                            :style="`width: ${step === 1 ? '0%' : '100%'}`"></div>
                    </div>

                    {{-- Step 1 Indicator --}}
                    <div class="relative z-10 flex flex-col items-start pr-4 bg-white">
                        <div :class="step >= 1 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' :
                            'bg-white border-slate-200 text-slate-400'"
                            class="flex items-center justify-center w-10 h-10 transition-all duration-500 border-2 rounded-2xl">
                            <template x-if="step > 1">
                                <i class="text-xs fa-solid fa-check"></i>
                            </template>
                            <template x-if="step === 1">
                                <span class="text-xs font-black">01</span>
                            </template>
                        </div>
                        <div class="mt-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em]"
                                :class="step >= 1 ? 'text-blue-600' : 'text-slate-400'">Step 1</p>
                            <p class="text-sm font-bold text-slate-800">Company</p>
                        </div>
                    </div>

                    {{-- Step 2 Indicator --}}
                    <div class="relative z-10 flex flex-col items-end pl-4 text-right bg-white">
                        <div :class="step >= 2 ? 'bg-blue-600 border-blue-600 text-white shadow-lg shadow-blue-200' :
                            'bg-white border-slate-200 text-slate-400'"
                            class="flex items-center justify-center w-10 h-10 ml-auto transition-all duration-500 border-2 rounded-2xl">
                            <span class="text-xs font-black">02</span>
                        </div>
                        <div class="mt-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em]"
                                :class="step >= 2 ? 'text-blue-600' : 'text-slate-400'">Step 2</p>
                            <p class="text-sm font-bold text-slate-800">Contact</p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Letakkan di bawah header, sebelum tag <form> --}}
            @if ($errors->any())
                <div class="px-10 mb-4">
                    <div class="p-4 border-l-4 border-red-500 bg-red-50 rounded-r-2xl">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="text-red-500 fa-solid fa-circle-exclamation"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-xs font-bold tracking-widest text-red-800 uppercase">Gagal Menyimpan:</p>
                                <ul class="mt-1 text-xs text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- FORM CONTENT --}}
            <form method="POST" action="{{ route('customer.profile.complete.store') }}" class="px-10 pb-12">
                @csrf

                {{-- STEP 1: COMPANY DATA & ADDRESS --}}
                <div x-show="step === 1" id="step-1" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
                    class="space-y-6">

                    <div>
                        <label class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Company
                            Name</label>
                        <input type="text" name="company_name" required value="{{ old('company_name') }}"
                            placeholder="Enter legal company name"
                            class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">
                    </div>
                    <div>
                        <label class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Company
                            Company Email</label>
                        <input type="email" name="email" required value="{{ old('email') }}"
                            placeholder="example@company.com"
                            class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">
                        @error('email')
                            <p class="mt-2 text-xs italic font-bold text-red-500">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label
                                class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Industry</label>
                            <input type="text" name="industry" required value="{{ old('industry') }}"
                                placeholder="e.g. Technology"
                                class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">
                        </div>
                        <div class="col-span-1">
                            <label
                                class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp') }}" placeholder="00.000..."
                                class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="col-span-1">
                            <label
                                class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">City</label>
                            <input type="text" name="city" value="{{ old('city') }}" required
                                class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                        </div>
                        <div class="col-span-1">
                            <label class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Postal
                                Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Full
                            Address</label>
                        <textarea name="address_line" rows="3" required placeholder="Street, City, Province, and Postal Code"
                            class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">{{ old('address_line') }}</textarea>
                    </div>
                </div>

                {{-- STEP 2: PIC & CONTACT --}}
                <div x-show="step === 2" id="step-2" x-transition:enter="transition ease-out duration-500"
                    x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
                    class="space-y-6">

                    <div>
                        <label class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">PIC Name
                            (Full Name)</label>
                        <input type="text" name="pic_name" required value="{{ old('pic_name') }}"
                            placeholder="Person in charge name"
                            class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">
                    </div>
                    <div class="col-span-1">
                        <label class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">PIC
                            Email</label>
                        <input type="email" name="pic_email" value="{{ old('pic_email') }}" required
                            class="w-full px-5 py-3.5 text-sm font-medium bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-1">
                            <label
                                class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">Phone/WhatsApp</label>
                            <input type="text" name="phone" required value="{{ old('phone') }}"
                                placeholder="0812..."
                                class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">
                        </div>
                        <div class="col-span-1">
                            <label class="block mb-2 text-[11px] font-black uppercase tracking-widest text-slate-400">PIC
                                Position</label>
                            <input type="text" name="position" placeholder="e.g. Manager"
                                class="w-full px-5 py-3.5 text-sm font-medium transition-all bg-slate-50 border-none rounded-2xl focus:ring-[6px] focus:ring-blue-500/10 focus:bg-white outline-none">
                        </div>
                    </div>

                    <div class="p-5 rounded-[2rem] bg-indigo-50 border border-indigo-100">
                        <div class="flex gap-4">
                            <i class="mt-1 text-indigo-500 fa-solid fa-circle-info"></i>
                            <p class="text-xs leading-relaxed text-indigo-700/80">
                                <strong>Note:</strong> Pastikan data yang dimasukkan sudah benar. Data ini akan digunakan
                                untuk keperluan verifikasi akun dan komunikasi bisnis ke depannya.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- BUTTONS --}}
                <div class="flex items-center justify-between mt-12">
                    <button type="button" x-show="step > 1" @click="step--"
                        class="flex items-center gap-2 px-6 py-3 text-sm font-bold transition-all text-slate-400 hover:text-slate-800 group">
                        <i
                            class="fa-solid fa-chevron-left text-[10px] group-hover:-translate-x-1 transition-transform"></i>
                        Back
                    </button>
                    <div x-show="step === 1"></div>

                    <div class="flex items-center gap-3">
                        <button type="button" x-show="step < totalSteps" @click="validateAndNext()"
                            class="px-10 py-4 text-xs font-black tracking-widest text-white uppercase transition-all shadow-xl bg-slate-900 rounded-2xl hover:bg-blue-600 hover:shadow-blue-200 active:scale-95">
                            Next Step
                        </button>

                        <button type="submit" x-show="step === totalSteps"
                            class="px-10 py-4 text-xs font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl rounded-2xl shadow-blue-200 hover:bg-blue-700 active:scale-95">
                            Save & Complete
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>

@endsection
