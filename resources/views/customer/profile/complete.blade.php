@extends('layouts.master')

@section('title', 'Complete Profile')

@section('content')

    <section class="flex items-center justify-center min-h-screen px-4 py-10 bg-gradient-to-br from-slate-100 to-slate-200">

        <div class="w-full max-w-2xl p-8 bg-white shadow-2xl rounded-2xl">

            {{-- HEADER --}}
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-bold text-gray-800">
                    Complete Your Profile
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Please fill in the company information below
                </p>
            </div>

            {{-- ERROR --}}
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
            <form method="POST" action="{{ route('customer.profile.complete.store') }}" class="space-y-6">
                @csrf

                {{-- Company Name --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Company Name
                    </label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}"
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="Enter company name">
                </div>

                {{-- PIC Name --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        PIC Name
                    </label>
                    <input type="text" name="pic_name" value="{{ old('pic_name') }}"
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="Person in charge">
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Phone Number
                    </label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="08xxxxxxxxxx">
                </div>

                {{-- Address --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-700">
                        Address
                    </label>
                    <textarea name="address" rows="4"
                        class="w-full px-4 py-3 transition border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-600 focus:outline-none"
                        placeholder="Full company address">{{ old('address') }}</textarea>
                </div>

                {{-- BUTTON --}}
                <div class="pt-4">
                    <button type="submit"
                        class="w-full py-3 font-semibold text-white transition shadow-lg rounded-xl bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900">
                        Save Profile
                    </button>
                </div>

            </form>

        </div>

    </section>

@endsection
