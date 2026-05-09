<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - @yield('title')</title>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite Assets (Tailwind included here) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- External Libraries -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <!-- AlpineJS Plugins -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

</head>

<body class="font-sans antialiased text-slate-900 bg-slate-50" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!--
            Sidebar Section
            Pastikan di aside.blade.php menggunakan class:
            - Mobile: fixed atau absolute dengan x-show="sidebarOpen"
            - Desktop: md:relative md:flex
        -->
        @include('admin.layout.aside')

        <!-- Main Content Area -->
        <div
            class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto scrollbar-thin scrollbar-thumb-slate-300 scrollbar-track-slate-100">

            <!-- Navbar -->
            @include('admin.layout.navbar')

            <!-- Main Content -->
            <main class="w-full p-4 transition-all duration-300 ease-in-out md:p-6 lg:p-8">
                <div class="mx-auto max-w-7xl">

                    <!-- Alert/Notification Area -->
                    <div class="mb-6">
                        @include('admin.layout.notif')
                    </div>

                    <!-- Dynamic Content -->
                    <div class="min-h-[calc(100vh-160px)]">
                        @yield('content')
                    </div>

                </div>
            </main>

        </div>

        <!-- Overlay untuk Mobile Sidebar -->
        <div x-show="sidebarOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="sidebarOpen = false"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden" x-cloak>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
