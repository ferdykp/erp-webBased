<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <title>Beam Admin - @yield('title')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        window.formatSmartNumber = function (value, maxDecimals = 6, fallback = '-') {
            if (value === null || value === undefined || value === '') return fallback;
            const number = Number(value);
            if (!Number.isFinite(number)) return fallback;
            const decimals = Math.max(0, Math.min(Number(maxDecimals) || 0, 12));
            return new Intl.NumberFormat('en-US', {
                useGrouping: false,
                minimumFractionDigits: 0,
                maximumFractionDigits: decimals,
            }).format(number);
        };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode"></script>
    @stack('styles')
</head>
<body class="min-h-[100dvh] overflow-x-hidden bg-slate-50 font-sans text-slate-900 antialiased lg:overflow-hidden" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <div class="flex min-h-[100dvh] w-full print:block print:h-auto print:min-h-0 lg:h-[100dvh] lg:overflow-hidden">
        @include('admin.layout.aside')

        <div class="relative flex min-w-0 flex-1 flex-col bg-slate-50 print:block print:h-auto print:overflow-visible lg:h-[100dvh] lg:overflow-y-auto lg:overscroll-contain">
            @include('admin.layout.navbar')
            <main class="w-full flex-1 px-[clamp(0.75rem,1.25vw,2rem)] py-[clamp(1rem,1.25vw,2rem)] print:p-0">
                <div class="mx-auto min-h-[calc(100dvh-8rem)] w-full max-w-none print:min-h-0 print:max-w-none">
                    <div class="mb-4 print:hidden">@include('admin.layout.notif')</div>
                    @yield('content')
                </div>
            </main>
        </div>

        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-[2px] print:hidden lg:hidden"></div>
    </div>
    @stack('scripts')
</body>
</html>
