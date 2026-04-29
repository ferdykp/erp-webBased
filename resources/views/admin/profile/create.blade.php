@extends('admin.layout.app')

@section('content')
    <div class="mx-auto space-y-6 full-width">
        {{-- HEADER --}}
        <div class="py-5 px-7 shadow-lg bg-white border-gray-100 rounded-[2.5rem]">
            <h3 class="text-2xl font-black tracking-tight text-gray-900">Tambah Admin Baru</h3>
            <p class="mt-1 text-sm text-gray-800">Daftarkan akun administrator baru ke dalam sistem.</p>
        </div>

        <div class="space-y-6">
            {{-- FORM TAMBAH ADMIN --}}
            <form action="{{ route('admin.profile.store') }}" method="POST"
                class="bg-white p-8 md:p-10 border border-gray-100 shadow-sm rounded-[2.5rem]">
                @csrf

                <h4 class="flex items-center gap-2 mb-8 text-lg font-black text-gray-900">
                    <i class="text-sm text-blue-600 fa-solid fa-user-plus"></i> Informasi Akun Baru
                </h4>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    {{-- Nama --}}
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        @error('name')
                            <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Email
                            Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        @error('email')
                            <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Confirm
                            Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                    </div>

                    {{-- KOLOM ROLE (BARU) --}}
                    <div class="md:col-span-2">
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Akses
                            Role</label>
                        <select name="role" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none appearance-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                            <option value="" disabled selected>Pilih Role Administrator</option>
                            <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>Superadmin (Semua
                                Akses)</option>
                            <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager (Bisnis &
                                Report)</option>
                            <option value="cargo_admin" {{ old('role') == 'cargo_admin' ? 'selected' : '' }}>Admin Cargo
                                (Manajemen Order)</option>
                            <option value="production" {{ old('role') == 'production' ? 'selected' : '' }}>Staff Produksi
                                (Operasional)</option>
                        </select>
                        @error('role')
                            <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-8">
                    <button type="submit"
                        class="w-full px-10 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-lg md:w-fit rounded-2xl shadow-blue-200 hover:bg-blue-700 active:scale-95">
                        Daftarkan Admin
                    </button>
                    <a href="{{ route('admin.profile') }}"
                        class="ml-4 text-sm font-bold text-gray-500 hover:text-gray-700">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
