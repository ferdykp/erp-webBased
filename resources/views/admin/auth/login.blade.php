@extends('layouts.master')

@section('content')
    {{-- Container utama: h-screen menjaga tetap 1 layar, overflow-hidden mencegah scroll --}}
    <section class="h-screen w-full flex items-center justify-center bg-[#f1f5f9] px-4 font-sans relative overflow-hidden">
        @if (session('error'))
            <div class="flex items-center gap-2 p-4 mb-4 text-sm text-red-800 border border-red-100 bg-red-50 rounded-xl">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span class="font-bold">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Background Accents: Responsive sizing --}}
        <div
            class="absolute top-[-10%] left-[-5%] w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-indigo-300/30 rounded-full blur-[80px] md:blur-[100px] pointer-events-none">
        </div>
        <div
            class="absolute bottom-[-10%] right-[-5%] w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-purple-300/30 rounded-full blur-[80px] md:blur-[100px] pointer-events-none">
        </div>

        {{-- Wrapper: max-w dinamis berdasarkan ukuran layar --}}
        <div class="relative z-10 w-full max-w-[340px] sm:max-w-[380px] md:max-w-[400px]">

            {{-- Card --}}
            <div
                class="bg-white p-6 sm:p-8 rounded-[2rem] sm:rounded-[2.5rem] shadow-[0_20px_50px_rgba(15,23,42,0.1)] border border-slate-200">

                {{-- Header --}}
                <div class="flex flex-col items-center mb-6 sm:mb-8">
                    {{-- <div
                        class="flex items-center justify-center mb-4 transition-transform duration-500 shadow-lg cursor-default w-34 h-34 sm:w-16 sm:h-16 bg-gradient-to-tr from-indigo-600 to-purple-600 rounded-2xl rotate-6 shadow-indigo-200 group hover:rotate-0"> --}}
                    {{-- <svg xmlns="http://www.w3.org/2000/svg" class="text-white w-7 h-7 sm:w-8 sm:h-8" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A10.003 10.003 0 003.207 4c1.947 0 3.799.442 5.451 1.238m11.838 7.32a10.003 10.003 0 01-11.838 7.32c1.947 0 3.799-.442 5.451-1.238m0 0A10.003 10.003 0 0120.793 4c-1.947 0-3.799.442-5.451 1.238" />
                        </svg> --}}
                    {{-- <img src="{{ asset('img/logo-txt-removebg.png') }}" alt=""> --}}
                    {{-- </div> --}}
                    {{-- <div class="mb-5">
                        <a href="{{ route('landing') }}"> <img src="{{ asset('img/logo-txt-removebg.png') }}"
                                alt=""></a>
                    </div> --}}
                    <a href="{{ route('landing') }}" class="block max-w-sm cursor-pointer group rounded-xl">
                        <img src="{{ asset('img/logo-txt-removebg.png') }}" alt="Post Image"
                            class="object-cover px-8 transition-transform duration-500 ease-in-out group-hover:scale-110" />
                    </a>
                    <h2 class="text-xl font-black tracking-tight sm:text-2xl text-slate-950">Admin Console</h2>
                    <p class="text-[10px] sm:text-xs font-bold text-indigo-600 mt-1 uppercase tracking-[0.15em]">Security
                        Gate</p>
                </div>

                {{-- Validation Feedback: Menggunakan Arbitrary Animation Tailwind --}}
                @if ($errors->any())
                    <div class="p-3 mb-5 bg-rose-50 border border-rose-200 rounded-xl animate-[shake_0.2s_ease-in-out_2]">
                        <p
                            class="text-[10px] sm:text-[11px] text-rose-700 text-center font-bold leading-tight uppercase tracking-wide">
                            {{ $errors->first() }}
                        </p>
                    </div>
                @endif

                <form action="{{ route('admin.login') }}" method="POST" class="space-y-4 sm:space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div class="space-y-1.5">
                        <label
                            class="text-[10px] sm:text-[11px] font-extrabold text-slate-700 uppercase tracking-widest ml-1">Corporate
                            Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autoFocus
                            class="w-full px-4 sm:px-5 py-3 sm:py-3.5 transition-all border-2 bg-slate-50 border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 text-sm font-medium text-slate-900 placeholder:text-slate-400"
                            placeholder="name@company.com">
                    </div>

                    {{-- Password --}}
                    <div class="space-y-1.5">
                        <label
                            class="text-[10px] sm:text-[11px] font-extrabold text-slate-700 uppercase tracking-widest ml-1">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="admin_password" required
                                class="w-full px-4 sm:px-5 py-3 sm:py-3.5 transition-all border-2 bg-slate-50 border-slate-200 rounded-xl focus:outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 text-sm font-medium text-slate-900 placeholder:text-slate-400"
                                placeholder="••••••••">

                            <button type="button" onclick="togglePassword('admin_password', 'p-icon')"
                                class="absolute transition-colors -translate-y-1/2 right-4 top-1/2 text-slate-400 hover:text-indigo-600">
                                <svg id="p-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-3.5 sm:py-4 bg-slate-950 text-white text-[10px] sm:text-[11px] font-black uppercase tracking-[0.2em] rounded-xl shadow-xl shadow-indigo-100 hover:bg-indigo-600 transition-all active:scale-[0.97]">
                            Verify & Authenticate
                        </button>
                    </div>
                </form>
            </div>

            {{-- Footer Help --}}
            <p class="mt-6 text-center text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-tight">
                Access Issues? <a href="#"
                    class="text-indigo-700 underline hover:text-indigo-900 underline-offset-4 decoration-2">Contact System
                    Admin</a>
            </p>
        </div>
    </section>

    {{-- Script untuk Toggle Password --}}
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('text-slate-400', 'text-indigo-600');
            } else {
                input.type = 'password';
                icon.classList.replace('text-indigo-600', 'text-slate-400');
            }
        }
    </script>
@endsection
