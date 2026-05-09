@extends('admin.layout.app')

@section('title', 'Profile')

@section('content')
    <div class="w-full px-2 py-4 mx-auto space-y-6 sm:px-4 md:px-6 lg:py-8">

        {{-- HEADER PROFILE CARD --}}
        <div class="bg-white border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem] overflow-hidden">
            {{-- Banner --}}
            <div class="h-24 bg-slate-900 bg-gradient-to-r from-slate-900 to-slate-800 sm:h-32"></div>

            <div class="px-4 pb-6 sm:px-8 sm:pb-8">
                <div class="relative flex flex-col items-center justify-between mb-6 -mt-12 sm:flex-row sm:items-end">
                    {{-- Profile Picture --}}
                    <div class="relative group">
                        <div
                            class="w-24 h-24 bg-white p-1.5 sm:p-2 rounded-[1.5rem] sm:w-32 sm:h-32 sm:rounded-[2rem] shadow-xl">
                            <div
                                class="w-full h-full bg-blue-100 rounded-[1rem] sm:rounded-[1.5rem] flex items-center justify-center text-3xl sm:text-4xl font-black text-blue-600 uppercase">
                                {{ substr(auth('admin')->user()->name, 0, 1) }}
                            </div>
                        </div>
                        {{-- Online Status Indicator --}}
                        <div
                            class="absolute w-5 h-5 bg-green-500 border-4 border-white rounded-full bottom-2 right-1 sm:bottom-2 sm:right-2">
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-col w-full gap-3 mt-6 sm:mt-0 sm:w-auto sm:flex-row">
                        <a href="{{ route('admin.profile.profileList') }}"
                            class="flex items-center justify-center px-6 py-3 text-sm font-bold transition-all border text-slate-700 border-slate-100 bg-slate-50 rounded-xl sm:rounded-2xl hover:bg-slate-100 hover:shadow-md">
                            <i class="mr-2 fa-solid fa-list-ul"></i> Profile List
                        </a>
                    </div>
                </div>

                {{-- User Basic Info --}}
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                        {{ auth('admin')->user()->name }}</h2>
                    <div class="flex flex-col items-center gap-1 sm:flex-row sm:gap-2">
                        <span
                            class="px-3 py-1 mt-1 text-[10px] font-black tracking-widest text-blue-600 uppercase bg-blue-50 rounded-full w-fit">Administrator</span>
                        <p class="text-xs italic font-medium text-slate-400">ID:
                            #{{ str_pad(auth('admin')->id(), 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACCOUNT DETAILS GRID --}}
        <div class="grid grid-cols-1 gap-4 sm:gap-6 lg:grid-cols-2">

            {{-- Personal Information Card --}}
            <div
                class="bg-white p-6 sm:p-8 border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2rem] space-y-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-50 rounded-xl">
                        <i class="text-blue-600 fa-solid fa-user"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Informasi Pribadi</h3>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:gap-6">
                    <div class="pb-4 border-b border-slate-50">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Email
                            Address</label>
                        <p class="mt-1 font-bold break-all text-slate-800">{{ auth('admin')->user()->email }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Phone
                            Number</label>
                        <p class="mt-1 font-bold text-slate-800">{{ auth('admin')->user()->phone ?? '+62 812-3456-7890' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Security Card --}}
            <div
                class="bg-white p-6 sm:p-8 border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2rem] space-y-6 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-amber-50 rounded-xl">
                        <i class="text-amber-600 fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900">Keamanan</h3>
                </div>

                <div class="space-y-4">
                    <div class="pb-4 border-b border-slate-50">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.15em]">Password</label>
                        <div class="flex items-center justify-between mt-1">
                            <p class="font-bold tracking-widest text-slate-800">••••••••••••</p>
                            <span
                                class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Secure</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a class="inline-flex items-center text-sm font-black text-blue-600 transition-all hover:text-blue-700 group"
                            href="{{ route('admin.profile.edit') }}">
                            Ganti Password
                            <i class="ml-2 transition-transform fa-solid fa-arrow-right group-hover:translate-x-1"></i>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- FOOTER / SYSTEM INFO (Optional) --}}
        <div class="text-center">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">
                Last login: {{ now()->format('d M Y, H:i') }} • Server Time (WIB)
            </p>
        </div>
    </div>
@endsection
