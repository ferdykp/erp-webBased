<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
@include('layouts.head')
@stack('head')

<body class="min-h-[100dvh] font-sans antialiased text-slate-900" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    @php $plainPage = request()->routeIs(['landing', 'customer.login', 'customer.register', 'admin.login']); @endphp

    @if($plainPage)
        @yield('content')
    @else
        <div class="flex min-h-[100dvh] bg-slate-50 lg:h-[100dvh] lg:overflow-hidden">
            @include('layouts.aside')
            <div class="flex min-w-0 flex-1 flex-col lg:h-[100dvh] lg:overflow-hidden">
                @include('layouts.navbar')
                <main class="relative flex-1 overflow-y-auto">
                    <div class="mx-auto w-full max-w-none px-3 py-4 sm:px-5 sm:py-5 md:px-6 md:py-6 lg:px-8 lg:py-7 xl:px-10 2xl:px-12 3xl:px-14 4xl:px-16">
                        @yield('content')
                    </div>
                    <footer class="border-t border-slate-200 bg-white py-5 text-center text-[11px] text-slate-400">
                        &copy; {{ date('Y') }} BeamApp · Customer Portal
                    </footer>
                </main>
            </div>
            <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-[2px] lg:hidden"></div>
        </div>
    @endif

    @include('layouts.notif')
    @stack('scripts')
</body>
</html>
