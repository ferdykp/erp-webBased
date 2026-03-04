@extends('layouts.master')

@section('content')
    <section class="flex items-center justify-center min-h-screen px-4 bg-gray-50">
        <div class="flex w-full max-w-4xl overflow-hidden bg-white shadow-xl rounded-[2rem] border border-gray-100">

            {{-- LEFT SIDE: Welcome Text --}}
            <div class="relative flex-col justify-center hidden w-1/2 p-12 overflow-hidden text-white bg-blue-600 md:flex">
                <div class="absolute top-0 right-0 w-64 h-64 -mt-20 -mr-20 bg-blue-500 rounded-full opacity-50"></div>
                <div class="relative z-10">
                    <h1 class="text-4xl font-bold leading-tight">Book Your<br>Session Easily.</h1>
                    <p class="mt-4 text-blue-100">Join our community and manage your bookings with just a few clicks.</p>
                </div>
                <div class="absolute bottom-10 left-12">
                    <img src="{{ asset('img/logo-txt-removebg.png') }}" class="h-10 brightness-0 invert opacity-80"
                        alt="Logo">
                </div>
            </div>

            {{-- RIGHT SIDE: Form --}}
            <div class="w-full p-8 md:w-1/2 sm:p-12">
                <div class="max-w-sm mx-auto">
                    <div class="mb-10 text-center md:text-left">
                        <h2 class="text-3xl font-extrabold text-gray-900">Sign In</h2>
                        <p class="mt-2 text-sm text-gray-500">Don't have an account? <a
                                href="{{ route('customer.register') }}"
                                class="font-bold text-blue-600 hover:underline">Register</a></p>
                    </div>

                    <form action="{{ route('customer.login') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="ml-1 text-xs font-bold tracking-widest text-gray-400 uppercase">Email
                                Address</label>
                            <input type="email" name="email" required placeholder="name@example.com"
                                class="w-full px-5 py-4 mt-1 transition-all border border-gray-200 outline-none bg-gray-50 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                        </div>

                        <div>
                            <div class="flex items-center justify-between ml-1">
                                <label class="text-xs font-bold tracking-widest text-gray-400 uppercase">Password</label>
                            </div>
                            <div class="relative mt-1">
                                <input type="password" name="password" id="password" required placeholder="••••••••"
                                    class="w-full px-5 py-4 transition-all border border-gray-200 outline-none bg-gray-50 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-600">
                                <button type="button" onclick="togglePassword('password', 'eyeIcon')"
                                    class="absolute text-gray-400 -translate-y-1/2 right-4 top-1/2">
                                    <i id="eyeIcon" class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full py-4 bg-blue-600 text-white font-bold rounded-2xl shadow-lg shadow-blue-200 hover:bg-blue-700 hover:shadow-blue-300 transition-all active:scale-[0.98]">
                            Sign In to Dashboard
                        </button>
                    </form>
                </div>
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
