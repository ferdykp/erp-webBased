<nav
    class="sticky z-30 flex items-center justify-between px-6 py-4 mx-4 border shadow-lg top-4 bg-white/80 backdrop-blur-md border-white/20 md:ml-0 rounded-2xl">

    <div class="flex items-center gap-4">
        {{-- Burger Menu for Mobile (Ensure sidebarOpen variable exists in your Alpine store/data) --}}
        <button @click="sidebarOpen = true" class="p-2 text-gray-600 rounded-lg md:hidden hover:bg-gray-100">
            <i class="text-xl fa-solid fa-bars-staggered"></i>
        </button>

        <div class="hidden sm:block">
            <h1 class="text-sm font-medium text-gray-400">Pages /</h1>
            <p class="text-base font-bold tracking-tight text-gray-800">
                @yield('title', 'Dashboard')
            </p>
        </div>
    </div>

    <div class="flex items-center gap-4">

        <button class="relative p-2 text-gray-400 transition-colors hover:text-blue-600">
            <i class="text-lg fa-solid fa-bell"></i>
            <span class="absolute w-2 h-2 bg-red-500 border-2 border-white rounded-full top-2 right-2"></span>
        </button>

        <div class="h-6 w-[1px] bg-gray-200 mx-1"></div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false"
                class="flex items-center gap-3 p-1 pr-3 transition-all rounded-full hover:bg-gray-50 group">

                {{-- User Avatar (Initial or Image) --}}
                <div
                    class="flex items-center justify-center font-bold text-white rounded-full shadow-md w-9 h-9 bg-gradient-to-tr from-blue-600 to-blue-400 shadow-blue-200">
                    {{ strtoupper(substr(auth('admin')->user()->name, 0, 1)) }}
                </div>

                <div class="hidden text-left sm:block">
                    <p class="text-xs font-semibold leading-none text-gray-800">
                        {{ auth('admin')->user()->name }}
                    </p>
                    <p class="text-[10px] font-medium text-gray-400 mt-1">Administrator</p>
                </div>

                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"
                    :class="open ? 'rotate-180' : ''"></i>
            </button>

            {{-- DROPDOWN MENU --}}
            <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                class="absolute right-0 z-50 mt-3 overflow-hidden bg-white border border-gray-100 shadow-2xl w-52 rounded-2xl">

                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50">
                    <p class="text-xs text-gray-400">Signed in as</p>
                    <p class="text-sm font-bold text-gray-800 truncate">{{ auth('admin')->user()->email }}</p>
                </div>

                <div class="p-2">
                    <a href="{{ route('admin.profile') }}"
                        class="flex items-center gap-3 px-3 py-2 text-sm text-gray-600 transition-colors rounded-xl hover:bg-blue-50 hover:text-blue-600">
                        <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100/50">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        Account Settings
                    </a>

                    <form action="{{ route('admin.logout') }}" method="POST" class="block">
                        @csrf
                        <button type="submit"
                            class="flex items-center w-full gap-3 px-3 py-2 text-sm text-red-500 transition-colors rounded-xl hover:bg-red-50">
                            <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-100/50">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </div>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
