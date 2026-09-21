<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'BeamApp') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-50 p-6 font-sans text-slate-900 antialiased">
    <main class="w-full max-w-3xl rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50 sm:p-12">
        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-lg font-black text-white">B</div>
        <p class="mt-6 text-[10px] font-extrabold uppercase tracking-[0.18em] text-blue-600">E-Beam Operations Platform</p>
        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-950 sm:text-5xl">{{ config('app.name', 'BeamApp') }}</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-500 sm:text-base">Operational workspace for sterilization orders, production monitoring, product testing, dosimetry, warehouse logistics, and technical reporting.</p>
        <div class="mt-8 flex flex-wrap gap-3">
            @if (Route::has('admin.login'))
                <a href="{{ route('admin.login') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">Admin Login</a>
            @endif
            @if (Route::has('customer.login'))
                <a href="{{ route('customer.login') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Customer Portal</a>
            @endif
        </div>
    </main>
</body>
</html>
