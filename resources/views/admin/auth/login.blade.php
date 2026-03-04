@extends('layouts.master')

@section('content')
    <section class="flex items-center justify-center min-h-screen bg-[#0f172a] px-4 overflow-hidden relative">

        {{-- Ambient Light Effects --}}
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-blue-600/10 rounded-full blur-[120px]"></div>

        <div class="relative w-full max-w-[450px]">
            {{-- Card --}}
            <div class="bg-white/5 backdrop-blur-xl border border-white/10 p-10 rounded-[2.5rem] shadow-2xl">

                {{-- Header --}}
                <div class="mb-10 text-center">
                    <div
                        class="inline-flex items-center justify-center w-16 h-16 mb-6 bg-indigo-600 shadow-xl rounded-2xl shadow-indigo-500/20">
                        <i class="text-2xl text-white fa-solid fa-shield-halved"></i>
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-white">Admin Gateway</h2>
                    <p class="mt-2 text-sm text-indigo-300/60">Internal Access Only</p>
                </div>

                <form action="{{ route('admin.login') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] ml-1">Admin
                            Identifier</label>
                        <div class="relative group">
                            <i
                                class="absolute -translate-y-1/2 fa-solid fa-user left-5 top-1/2 text-indigo-300/30 group-focus-within:text-indigo-400"></i>
                            <input type="email" name="email" required
                                class="w-full py-4 pl-12 pr-5 text-white transition-all border bg-white/5 border-white/10 rounded-2xl placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:bg-white/10">
                        </div>
                    </div>

                    <div>
                        <label class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.2em] ml-1">Security
                            Key</label>
                        <div class="relative group">
                            <i
                                class="absolute -translate-y-1/2 fa-solid fa-lock left-5 top-1/2 text-indigo-300/30 group-focus-within:text-indigo-400"></i>
                            <input type="password" name="password" id="admin_password" required
                                class="w-full py-4 pl-12 pr-12 text-white transition-all border bg-white/5 border-white/10 rounded-2xl placeholder-white/20 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:bg-white/10">
                            <button type="button" onclick="togglePassword('admin_password', 'admin_eye')"
                                class="absolute -translate-y-1/2 right-5 top-1/2 text-white/20 hover:text-white">
                                <i id="admin_eye" class="text-sm fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-4 bg-indigo-600 text-white font-black rounded-2xl hover:bg-indigo-500 shadow-lg shadow-indigo-900/20 transition-all active:scale-[0.97] tracking-widest text-sm uppercase">
                        Authenticate
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="mt-8 text-center">
                <p class="text-indigo-300/30 text-[10px] uppercase tracking-widest italic">&copy; {{ date('Y') }}
                    Terminal System v2.0</p>
            </div>
        </div>
    </section>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>
@endsection
