<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">

@include('layouts.head')
@stack('head')

<body class="h-full overflow-hidden font-sans antialiased text-gray-900" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen bg-gray-50">

        {{-- SIDEBAR --}}
        {{-- Hanya muncul jika bukan halaman auth --}}
        @if (!request()->routeIs(['landing', 'customer.login', 'customer.register', 'admin.login']))
            @include('layouts.aside')
        @endif

        {{-- MAIN CONTENT AREA --}}
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            {{-- NAVBAR --}}
            @if (!request()->routeIs(['landing', 'customer.login', 'customer.register', 'admin.login']))
                @include('layouts.navbar')
            @endif

            <main class="relative flex-1 overflow-y-auto focus:outline-none custom-scrollbar">

                {{-- Jika halaman landing/login, jangan kasih pembatas max-w-7xl dan padding --}}
                <div
                    class="{{ request()->routeIs(['landing', 'customer.login', 'customer.register', 'admin.login'])
                        ? ''
                        : 'px-4 py-8 mx-auto sm:px-6 lg:px-8 max-w-7xl' }}">

                    <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 50)" x-show="show"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">

                        @yield('content')

                    </div>
                </div>

                {{-- Footer ini juga mungkin mau disembunyikan di landing page karena landing sudah punya footer sendiri --}}
                @if (!request()->routeIs(['landing']))
                    <footer class="py-6 mt-10 text-xs text-center text-gray-400 border-t border-gray-100">
                        &copy; {{ date('Y') }} BeamApp Customer Portal. All rights reserved.
                    </footer>
                @endif
            </main>
        </div>
    </div>

    @include('layouts.notif')
    {{-- @stack('scripts') --}}

    {{-- Tambahan CSS dikit untuk scrollbar cantik tanpa file CSS luar --}}
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
    @stack('scripts') {{-- PASTIKAN BARIS INI ADA --}}
</body>

</html>
