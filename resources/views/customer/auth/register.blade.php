@extends('layouts.master')

@section('title', 'Customer Register')

@section('content')

    <section
        class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#0f2027] via-[#203a43] to-[#2c5364] px-4 py-10">

        <div class="w-full max-w-md p-8 bg-white shadow-2xl rounded-3xl">

            {{-- HEADER --}}
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-gray-800">
                    Create Account
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Register to start booking services
                </p>
            </div>

            {{-- ERROR MESSAGE --}}
            @if ($errors->any())
                <div class="p-4 mb-6 text-sm text-red-700 bg-red-100 rounded-lg">
                    <ul class="pl-5 space-y-1 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- FORM --}}
            <form method="POST" action="{{ route('customer.register') }}" class="space-y-5">
                @csrf

                {{-- Full Name --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Full Name
                    </label>
                    <input type="text" name="username" value="{{ old('username') }}" required
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="Enter your full name">
                </div>

                {{-- Email --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Email Address
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="Enter your email">
                </div>

                {{-- Password --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="Minimum 8 characters">
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Confirm Password
                    </label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="Re-enter password">
                </div>

                {{-- BUTTON --}}
                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 font-semibold text-white transition shadow-lg rounded-xl bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900">
                        Register
                    </button>
                </div>

            </form>

            {{-- FOOTER --}}
            <p class="mt-6 text-sm text-center text-gray-500">
                Already have an account?
                <a href="{{ route('customer.login') }}" class="font-semibold text-blue-600 hover:underline">
                    Login here
                </a>
            </p>

        </div>

    </section>

@endsection
