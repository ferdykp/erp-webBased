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
                {{-- JANGAN pakai @method('PUT') di sini karena ini adalah fungsi POST (Create) --}}

                <h4 class="flex items-center gap-2 mb-8 text-lg font-black text-gray-900">
                    <i class="text-sm text-blue-600 fa-solid fa-user-plus"></i> Informasi Akun Baru
                </h4>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        @error('name')
                            <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Email
                            Address</label>
                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        @error('email')
                            <p class="mt-1 ml-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Password</label>
                        <input type="password" name="password" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                    </div>
                    <div>
                        <label class="text-[11px] font-black text-gray-400 uppercase tracking-widest ml-1">Confirm
                            Password</label>
                        <input type="password" name="password_confirmation" required
                            class="w-full px-5 py-4 mt-2 font-bold text-gray-700 transition-all border-transparent outline-none bg-gray-50 rounded-2xl focus:bg-white focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="w-full px-10 py-4 text-sm font-black text-white transition-all bg-blue-600 shadow-lg md:w-fit rounded-2xl shadow-blue-200 hover:bg-blue-700 active:scale-95">
                        Daftarkan Admin
                    </button>
                    <a href="{{ route('admin.profile') }}" class="ml-4 text-sm font-bold text-gray-500">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
