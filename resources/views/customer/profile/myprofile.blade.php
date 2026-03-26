@extends('layouts.master')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- HEADER PROFILE --}}
        <div class="bg-white border border-gray-100 shadow-sm rounded-[2.5rem] overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
            <div class="px-8 pb-8">
                <div class="relative flex items-end justify-between mb-6 -mt-12">
                    <div class="relative">
                        <div class="w-32 h-32 bg-white p-2 rounded-[2rem] shadow-xl">
                            <div
                                class="w-full h-full bg-blue-100 rounded-[1.5rem] flex items-center justify-center text-4xl font-black text-blue-600">
                                {{ substr(auth('customer')->user()->username, 0, 1) }}
                            </div>
                        </div>
                        <div class="absolute w-6 h-6 bg-green-500 border-4 border-white rounded-full bottom-2 right-2"></div>
                    </div>
                    <a href="{{ route('customer.profile.edit') }}"
                        class="px-6 py-3 text-sm font-bold text-gray-700 transition-all border border-gray-100 bg-gray-50 rounded-2xl hover:bg-gray-100">
                        <i class="mr-2 fa-solid fa-pen-to-square"></i> Edit Profile
                    </a>
                </div>

                <div>
                    <h2 class="text-3xl font-black tracking-tight text-gray-900">{{ auth('customer')->user()->name }}</h2>
                    <p class="italic font-medium text-gray-400">Customer ID:
                        #{{ str_pad(auth('customer')->id(), 5, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div>

        {{-- ACCOUNT DETAILS --}}
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="bg-white p-8 border border-gray-100 shadow-sm rounded-[2rem] space-y-6">
                <h3 class="flex items-center gap-2 text-lg font-black text-gray-900">
                    <i class="text-blue-600 fa-solid fa-user"></i> Informasi Pribadi
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Email Address</label>
                        <p class="mt-1 font-bold text-gray-800">{{ auth('customer')->user()->email }}</p>
                    </div>
                    <div>
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Phone Number</label>
                        <p class="mt-1 font-bold text-gray-800">{{ auth('customer')->user()->phone ?? '+62 812-3456-7890' }}
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
                    <button class="text-sm font-bold text-blue-600 hover:text-blue-700">Ganti Password &rarr;</button>
                </div>
            </div>
        </div>
    </div>
@endsection
