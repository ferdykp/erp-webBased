@extends('layouts.master')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        {{-- HEADER RINGKAS --}}
        <div class="px-4">
            <h3 class="text-2xl font-black tracking-tight text-gray-900">Edit Profile</h3>
            <p class="mt-1 text-sm text-gray-400">Perbarui informasi akun dan keamanan data Anda.</p>
        </div>

        {{-- Notifikasi Sukses --}}
        {{-- @if (session('success'))
            <div
                class="p-4 mx-4 text-sm font-bold border shadow-sm bg-emerald-100 border-emerald-200 text-emerald-700 rounded-2xl shadow-emerald-100">
                <i class="mr-2 fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif --}}

        <div class="space-y-6">

            {{-- FORM INFORMASI PRIBADI --}}
            <form action="{{ route('customer.profile.update') }}" method="POST"
                class="bg-white p-8 md:p-10 border border-gray-100 shadow-sm rounded-[2.5rem]">
                @csrf
                @method('PUT')

                <h4 class="flex items-center gap-2 mb-8 text-lg font-black text-gray-900">
                    <i class="text-sm text-blue-600 fa-solid fa-user-gear"></i> Informasi Akun
                </h4>

                <div class="space-y-6">
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Nama
                            Lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        @error('name')
                            <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Email
                                Address</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                            @error('email')
                                <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Nomor
                                Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                placeholder="+62..."
                                class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                            @error('phone')
                                <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full px-10 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-lg md:w-fit rounded-2xl shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 active:scale-95">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>

            {{-- FORM GANTI PASSWORD --}}
            <form action="{{ route('customer.profile.password') }}" method="POST"
                class="bg-white p-8 md:p-10 border border-gray-100 shadow-sm rounded-[2.5rem]">
                @csrf
                @method('PUT')

                <h4 class="flex items-center gap-2 mb-8 text-lg font-black text-gray-900">
                    <i class="text-sm text-blue-600 fa-solid fa-lock"></i> Keamanan Password
                </h4>

                <div class="space-y-6">
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Password Saat
                            Ini</label>
                        <input type="password" name="current_password"
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        @error('current_password')
                            <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Password
                                Baru</label>
                            <input type="password" name="password"
                                class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        </div>
                        <div>
                            <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Konfirmasi
                                Password</label>
                            <input type="password" name="password_confirmation"
                                class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        </div>
                    </div>
                    @error('password')
                        <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror

                    <div class="pt-4">
                        <button type="submit"
                            class="w-full px-10 py-4 text-sm font-black text-white transition-all bg-gray-900 md:w-fit rounded-2xl hover:bg-black active:scale-95">
                            Update Password
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection
