<!doctype html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>@yield('code', 'Error') — BeamCustom</title>
    {{-- Static Tailwind build: the error page must work when Vite or its manifest is unavailable. --}}
    @if (file_exists(public_path('css/error-tailwind.css')))
        <link rel="stylesheet" href="{{ asset('css/error-tailwind.css') }}">
    @elseif (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/app.css')
    @endif
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased">
    <main role="main" class="flex min-h-screen items-center justify-center px-5 py-8">
        <section class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-7 shadow-sm sm:p-10">
            <div class="text-base font-bold tracking-tight text-slate-900">BeamCustom <span class="font-normal text-slate-500">· Operations</span></div>
            <h1 class="mt-6 text-6xl font-bold tracking-tight text-slate-900">@yield('code', 'Error')</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600">@yield('message', 'Halaman tidak dapat ditampilkan. Silakan coba kembali.')</p>
            <div class="mt-7 flex flex-wrap gap-3">
                <button class="cursor-pointer rounded-lg border border-slate-900 bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500" type="button" onclick="window.location.reload()">Coba Lagi</button>
                <a class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500" href="{{ url('/') }}">Kembali ke Beranda</a>
            </div>
        </section>
    </main>
</body>
</html>
