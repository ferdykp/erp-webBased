@extends('admin.layout.app')

@section('content')
    <div class="w-full px-2 py-4 mx-auto space-y-6 sm:px-4 md:px-6 lg:py-8">

        {{-- HEADER --}}
        <div class="px-6 py-6 bg-white border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem] md:px-10">
            <h3 class="text-2xl font-black tracking-tighter text-slate-800 md:text-3xl">
                Tambah <span class="text-blue-600">Admin Baru</span>
            </h3>
            <p class="mt-1 text-xs font-bold tracking-widest uppercase text-slate-400">
                Daftarkan akun administrator baru ke dalam sistem
            </p>
        </div>

        <div class="space-y-6">
            {{-- FORM TAMBAH ADMIN --}}
            <form action="{{ route('admin.profile.store') }}" method="POST"
                class="bg-white p-6 sm:p-8 md:p-10 border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem]">
                @csrf

                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center justify-center w-10 h-10 text-blue-600 bg-blue-50 rounded-xl">
                        <i class="text-sm fa-solid fa-user-plus"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-800">Informasi Akun Baru</h4>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">
                    {{-- Nama --}}
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Full
                            Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Masukkan nama lengkap"
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600 placeholder:text-slate-300 placeholder:font-medium">
                        @error('name')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Email
                            Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            placeholder="admin@example.com"
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600 placeholder:text-slate-300 placeholder:font-medium">
                        @error('email')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Password</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                    </div>

                    {{-- Confirm Password --}}
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Confirm
                            Password</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                    </div>

                    {{-- KOLOM ROLE --}}
                    <div class="md:col-span-2 group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Akses
                            Role</label>
                        <div class="relative">
                            <select name="role" required
                                class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none appearance-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                                <option value="" disabled selected>Pilih Role Administrator</option>
                                <option value="superadmin" {{ old('role') == 'superadmin' ? 'selected' : '' }}>Superadmin
                                    (Semua Akses)</option>
                                <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager (Bisnis &
                                    Report)</option>
                                <option value="cargo_admin" {{ old('role') == 'cargo_admin' ? 'selected' : '' }}>Admin Cargo
                                    (Manajemen Order)</option>
                                <option value="production" {{ old('role') == 'production' ? 'selected' : '' }}>Staff
                                    Produksi (Operasional)</option>
                            </select>
                            <div class="absolute inset-y-0 flex items-center mt-2 pointer-events-none right-5">
                                <i class="text-xs fa-solid fa-chevron-down text-slate-400"></i>
                            </div>
                        </div>
                        @error('role')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- BUTTONS --}}
                <div class="flex flex-col-reverse gap-4 pt-10 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.profile') }}"
                        class="flex items-center justify-center px-8 py-4 text-sm font-black tracking-widest uppercase transition-all text-slate-400 hover:text-slate-600">
                        Batal
                    </a>
                    <button type="submit"
                        class="flex-1 px-10 py-4 text-sm font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl sm:flex-none shadow-blue-100 rounded-2xl hover:bg-blue-700 active:scale-95">
                        Daftarkan Admin
                    </button>
                </div>
            </form>
        </div>

        {{-- INFO FOOTER --}}
        <div class="text-center">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">
                Pastikan data yang dimasukkan sudah valid & sesuai prosedur perusahaan
            </p>
        </div>
    </div>
@endsection
