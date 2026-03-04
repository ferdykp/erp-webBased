<nav
    class="relative z-30 mx-4 mt-4 transition-all border border-gray-100 shadow-sm md:ml-4 rounded-2xl bg-white/80 backdrop-blur-md">
    <div class="flex items-center justify-between px-6 py-3.5">

        <div class="flex items-center gap-4">
            {{-- TOMBOL HAMBURGER MOBILE --}}
            <button @click="sidebarOpen = true"
                class="p-2.5 text-gray-500 rounded-xl bg-gray-50 hover:bg-blue-50 hover:text-blue-600 md:hidden transition-all focus:outline-none border border-gray-100">
                <i class="text-xl fa-solid fa-bars-staggered"></i>
            </button>

            {{-- BREADCRUMB --}}
            <nav aria-label="Breadcrumb" class="items-center hidden space-x-2 text-sm text-gray-500 md:flex">
                <a href="{{ url('/dashboard') }}"
                    class="flex items-center font-medium transition-colors hover:text-blue-600">
                    <i class="fa-solid fa-house mr-2 text-[11px] text-gray-400"></i> Home
                </a>

                @foreach (request()->segments() as $segment)
                    <i class="fa-solid fa-chevron-right text-[9px] text-gray-300"></i>
                    <span
                        class="{{ $loop->last ? 'font-bold text-gray-900' : 'font-medium hover:text-gray-700 transition-colors' }}">
                        @if ($loop->last && isset($siteData))
                            {{ $siteData->machine_name }}
                        @else
                            {{ ucfirst(str_replace('-', ' ', $segment)) }}
                        @endif
                    </span>
                @endforeach
            </nav>
        </div>

        {{-- RIGHT AREA --}}
        <div class="flex items-center gap-4">
            {{-- User Dropdown --}}
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.outside="open = false"
                    class="flex items-center gap-3 px-3 py-2 text-sm font-bold text-gray-700 transition-all border border-transparent rounded-xl hover:bg-gray-50 group hover:border-gray-100">

                    <div class="relative">
                        <div
                            class="flex items-center justify-center text-xs font-black text-blue-600 uppercase border-2 border-white rounded-full shadow-sm bg-blue-50 w-9 h-9">
                            {{ substr(auth('customer')->user()->name ?? 'C', 0, 1) }}
                        </div>
                        <span
                            class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-500 border-2 border-white rounded-full shadow-sm"></span>
                    </div>

                    <div class="flex-col hidden leading-tight text-left sm:flex">
                        <span
                            class="text-gray-900 truncate max-w-[100px]">{{ auth('customer')->user()->name ?? 'Customer' }}</span>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Verified
                            Account</span>
                    </div>

                    <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform duration-300"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>

                {{-- DROPDOWN MENU --}}
                <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
                    x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
                    class="absolute right-0 z-50 p-2 mt-3 overflow-hidden bg-white border border-gray-100 shadow-2xl w-60 rounded-2xl">

                    <div class="px-4 py-4 mb-2 border-b border-gray-50 bg-gray-50/50 rounded-xl">
                        <p class="text-[10px] font-black tracking-widest text-gray-400 uppercase">Customer ID</p>
                        <p class="mt-1 font-mono text-sm font-bold text-blue-600">
                            #{{ str_pad(auth('customer')->id(), 5, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>

                    <a href="{{ route('customer.profile') }}"
                        class="flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-gray-600 hover:bg-blue-50 hover:text-blue-600 rounded-xl transition-all group">
                        <div
                            class="flex items-center justify-center w-8 h-8 transition-colors bg-white border border-gray-100 rounded-lg group-hover:border-blue-100">
                            <i class="text-blue-500 fa-solid fa-user-gear"></i>
                        </div>
                        My Profile
                    </a>

                    <form action="{{ route('customer.logout') }}" method="POST"
                        class="pt-2 mt-1 border-t border-gray-50">
                        @csrf
                        <button type="submit"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-semibold text-red-500 hover:bg-red-50 rounded-xl transition-all group">
                            <div
                                class="flex items-center justify-center w-8 h-8 transition-colors border border-transparent rounded-lg bg-red-50/50 group-hover:bg-white group-hover:border-red-100">
                                <i class="text-red-500 fa-solid fa-power-off"></i>
                            </div>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
