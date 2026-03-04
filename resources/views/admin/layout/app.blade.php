<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beam Admin - @yield('title')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <script src="https://unpkg.com/html5-qrcode"></script>

    <link rel="stylesheet" href="/build/assets/app-BOOA11dg.css">


    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Custom Scrollbar untuk tampilan lebih modern */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar Section --}}
        @include('admin.layout.aside')

        {{-- Main Content Section --}}
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">

            {{-- Navbar --}}
            @include('admin.layout.navbar')

            {{-- Main Content --}}
            <main class="p-4 md:p-8">
                <div class="mx-auto max-w-7xl">
                    @include('admin.layout.notif')

                    @yield('content')
                </div>
            </main>

        </div>
    </div>
    @stack('scripts')
</body>

</html>
