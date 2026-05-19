@extends('admin.layout.app')

@section('content')
    <div class="w-full px-2 py-4 mx-auto space-y-6 sm:px-4 md:px-6 lg:py-8">

        {{-- HEADER RINGKAS --}}
        <div class="px-6 py-6 bg-white border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem] md:px-10">
            <h3 class="text-2xl font-black tracking-tighter text-slate-800 md:text-3xl">
                Edit <span class="text-blue-600">Profile</span>
            </h3>
            <p class="mt-1 text-xs font-bold tracking-widest uppercase text-slate-400">
                Perbarui informasi akun dan keamanan data Anda
            </p>
        </div>

        {{-- CONTAINER UTAMA --}}
        <div class="space-y-6">

            {{-- FORM INFORMASI PRIBADI --}}
            <form action="{{ route('admin.profile.update', $user->id) }}" method="POST"
                class="bg-white p-6 sm:p-8 md:p-10 border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem]">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center justify-center w-10 h-10 text-blue-600 bg-blue-50 rounded-xl">
                        <i class="text-sm fa-solid fa-user-gear"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-800">Informasi Akun</h4>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Full
                            Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                        @error('name')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Email
                            Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                        @error('email')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <div class="pt-8">
                    <button type="submit"
                        class="w-full px-10 py-4 text-sm font-black tracking-widest text-white uppercase transition-all bg-blue-600 shadow-xl shadow-blue-100 rounded-2xl md:w-fit hover:bg-blue-700 active:scale-95">
                        Simpan Perubahan
                    </button>
                </div>
            </form>

            {{-- FORM GANTI PASSWORD --}}
            <form action="{{ route('admin.profile.password') }}" method="POST"
                class="bg-white p-6 sm:p-8 md:p-10 border border-slate-100 shadow-sm rounded-[1.5rem] sm:rounded-[2.5rem]">
                @csrf
                @method('PUT')

                <div class="flex items-center gap-3 mb-8">
                    <div class="flex items-center justify-center w-10 h-10 bg-amber-50 rounded-xl text-amber-600">
                        <i class="text-sm fa-solid fa-lock"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-800">Keamanan Password</h4>
                </div>

                <div class="space-y-5 sm:space-y-6">
                    <div class="group">
                        <label
                            class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-amber-600">Password
                            Saat Ini</label>
                        <input type="password" name="current_password" required
                            class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-amber-100/50 focus:border-amber-500">
                        @error('current_password')
                            <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                                <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:gap-6 md:grid-cols-2">
                        <div class="group">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Password
                                Baru</label>
                            <input type="password" name="password" required
                                class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                        </div>
                        <div class="group">
                            <label
                                class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-1 transition-colors group-focus-within:text-blue-600">Konfirmasi
                                Password</label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-5 py-4 mt-2 font-bold transition-all border-2 border-transparent outline-none text-slate-700 bg-slate-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100/50 focus:border-blue-600">
                        </div>
                    </div>
                    @error('password')
                        <p class="flex items-center gap-1 mt-2 ml-1 text-xs font-bold text-red-500">
                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                        </p>
                    @enderror

                    <div class="flex flex-col-reverse gap-4 pt-4 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.profile') }}"
                            class="flex items-center justify-center px-8 py-4 text-sm font-black tracking-widest uppercase transition-all text-slate-400 hover:text-slate-600">
                            Batal
                        </a>
                        <button type="submit"
                            class="w-full px-10 py-4 text-sm font-black tracking-widest text-white uppercase transition-all shadow-xl bg-slate-900 rounded-2xl md:w-fit hover:bg-black active:scale-95 shadow-slate-200">
                            Update Password
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="pt-4 text-center">
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">
                Sistem Keamanan Terenkripsi • SSL Active
            </p>
        </div>
    </div>
@endsection
