@extends('layouts.master')

@section('content')
    <div class="max-w-5xl px-4 py-8 mx-auto bg-white shadow-lg sm:px-6 rounded-xl">
        <form action="{{ route('customer.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Header Ringkas --}}
            <div class="flex flex-col justify-between gap-4 mb-8 md:flex-row md:items-center">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">Profil Akun</h1>
                    <p class="text-sm text-gray-500">Perbarui informasi perusahaan dan kontak utama Anda.</p>
                </div>
                <div class="flex gap-3">
                    <button type="button"
                        class="px-5 py-2 text-sm font-semibold text-gray-600 transition-all bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-8 py-2 text-sm font-semibold text-white transition-all bg-blue-600 rounded-lg shadow-sm hover:bg-blue-700">
                        Simpan Perubahan
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">

                {{-- Kolom Kiri: Informasi Login & PIC --}}
                <div class="space-y-6 lg:col-span-1">
                    {{-- Section: Akun --}}
                    <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                        <h2 class="mb-4 text-xs font-bold tracking-wider text-blue-600 uppercase">Informasi Login</h2>
                        <div class="space-y-4">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Username</label>
                                <input type="text" name="username" value="{{ old('username', $user->username) }}"
                                    class="w-full px-3 py-2 text-sm transition-all border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Email Address</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="w-full px-3 py-2 text-sm transition-all border border-gray-200 rounded-lg outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    {{-- Section: PIC --}}
                    <div class="p-5 bg-white border border-gray-200 shadow-sm rounded-xl">
                        <h2 class="mb-4 text-xs font-bold tracking-wider text-blue-600 uppercase">Kontak PIC</h2>
                        <div class="grid grid-cols-1 gap-4">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Nama & Jabatan</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="contact_name" placeholder="Nama"
                                        value="{{ old('contact_name', $user->customer->contacts->where('is_primary', true)->first()->name ?? '') }}"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                                    <input type="text" name="contact_position" placeholder="Jabatan"
                                        value="{{ old('contact_position', $user->customer->contacts->where('is_primary', true)->first()->position ?? '') }}"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Telepon / WA</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="text" name="contact_phone" placeholder="Telepon"
                                        value="{{ old('contact_phone', $user->customer->contacts->where('is_primary', true)->first()->phone ?? '') }}"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                                    <input type="text" name="contact_whatsapp" placeholder="WhatsApp"
                                        value="{{ old('contact_whatsapp', $user->customer->contacts->where('is_primary', true)->first()->whatsapp ?? '') }}"
                                        class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan: Data Perusahaan --}}
                <div class="lg:col-span-2">
                    <div class="h-full p-6 bg-white border border-gray-200 shadow-sm rounded-xl">
                        <h2 class="mb-5 text-xs font-bold tracking-wider text-blue-600 uppercase">Data Perusahaan</h2>
                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Nama Perusahaan</label>
                                <input type="text" name="company_name"
                                    value="{{ old('company_name', $user->customer->company_name ?? '') }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Industri</label>
                                <input type="text" name="industry"
                                    value="{{ old('industry', $user->customer->industry ?? '') }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                            </div>
                            <div class="space-y-1 md:col-span-2">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Alamat Kantor</label>
                                <textarea name="address_line" rows="3"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">{{ old('address_line', $user->customer->addresses->where('type', 'office')->first()->address_line ?? '') }}</textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">Kota</label>
                                <input type="text" name="city"
                                    value="{{ old('city', $user->customer->addresses->where('type', 'office')->first()->city ?? '') }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[11px] font-bold text-gray-400 uppercase">NPWP</label>
                                <input type="text" name="npwp" value="{{ old('npwp', $user->customer->npwp ?? '') }}"
                                    class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:border-blue-500">
                            </div>
                        </div>

                        <div class="pt-6 mt-8 border-t border-gray-100">
                            <button type="button"
                                class="text-xs font-semibold text-red-400 transition-colors hover:text-red-600">
                                Hapus Akun?
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
@endsection
