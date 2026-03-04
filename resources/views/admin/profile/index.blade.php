@extends('admin.layout.app')

@section('title', 'Profile')

@section('content')
    <div class="mx-auto space-y-6 full-width">
        {{-- HEADER PROFILE --}}
        <div class="bg-white border border-gray-100 shadow-sm rounded-[2.5rem] overflow-hidden">
            <div class="h-32 bg-gray-900 bg-gradient-to-r"></div>
            <div class="px-8 pb-8">
                <div class="relative flex items-end justify-between mb-6 -mt-12">
                    <div class="relative">
                        <div class="w-32 h-32 bg-white p-2 rounded-[2rem] shadow-xl">
                            <div
                                class="w-full h-full bg-blue-100 rounded-[1.5rem] flex items-center justify-center text-4xl font-black text-blue-600">
                                {{ substr(auth('admin')->user()->name, 0, 1) }}
                            </div>
                        </div>
                        <div class="absolute w-6 h-6 bg-green-500 border-4 border-white rounded-full bottom-2 right-2"></div>
                    </div>
                    <div class="flex flex-col gap-3 mt-20">
                        {{-- <a href="{{ route('admin.profile.create') }}"
                            class="px-6 py-3 text-sm font-bold text-gray-700 transition-all border border-gray-100 bg-gray-50 rounded-2xl hover:bg-gray-100">
                            <i class="mr-2 fa-solid fa-pen-to-square"></i> Create Profile
                        </a>

                        <a href="{{ route('admin.profile.edit') }}"
                            class="px-6 py-3 text-sm font-bold text-gray-700 transition-all border border-gray-100 bg-gray-50 rounded-2xl hover:bg-gray-100">
                            <i class="mr-2 fa-solid fa-pen-to-square"></i> Edit Profile
                        </a> --}}
                        <a href="{{ route('admin.profile.profileList') }}"
                            class="px-6 py-3 text-sm font-bold text-gray-700 transition-all border border-gray-100 bg-gray-50 rounded-2xl hover:bg-gray-100">
                            <i class="mr-2 fa-solid fa-pen-to-square"></i> Profile List
                        </a>
                    </div>
                </div>

                <div>
                    <h2 class="text-3xl font-black tracking-tight text-gray-900">{{ auth('admin')->user()->name }}</h2>
                    <p class="italic font-medium text-gray-400">admin ID:
                        #{{ str_pad(auth('admin')->id(), 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>
        {{-- Notifikasi Sukses --}}
        {{-- @if (session('success'))
            <div
                class="p-4 mx-4 text-sm font-bold border shadow-sm bg-emerald-100 border-emerald-200 text-emerald-700 rounded-2xl shadow-emerald-100">
                <i class="mr-2 fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif --}}

        {{-- ACCOUNT DETAILS --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="bg-white p-8 border border-gray-100 shadow-sm rounded-[2rem] space-y-6">
                <h3 class="flex items-center gap-2 text-lg font-black text-gray-900">
                    <i class="text-blue-600 fa-solid fa-user"></i> Informasi Pribadi
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email Address</label>
                        <p class="mt-1 font-bold text-gray-800">{{ auth('admin')->user()->email }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Phone Number</label>
                        <p class="mt-1 font-bold text-gray-800">{{ auth('admin')->user()->phone ?? '+62 812-3456-7890' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 border border-gray-100 shadow-sm rounded-[2rem] space-y-6">
                <h3 class="flex items-center gap-2 text-lg font-black text-gray-900">
                    <i class="text-blue-600 fa-solid fa-shield-halved"></i> Keamanan
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Password</label>
                        <p class="mt-1 font-bold text-gray-800">••••••••••••</p>
                    </div>
                    <a class="text-sm font-bold text-blue-600 hover:text-blue-700"
                        href="{{ route('admin.profile.edit') }}">Ganti Password</a>
                    {{-- <button class="text-sm font-bold text-blue-600 hover:text-blue-700">Ganti Password &rarr;</button> --}}
                </div>
            </div>
        </div>
    </div>

@endsection
