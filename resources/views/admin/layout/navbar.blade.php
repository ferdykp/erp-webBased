<nav class="sticky top-0 z-30 flex h-[72px] flex-none items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-[clamp(0.75rem,1.25vw,2rem)] backdrop-blur-xl print:hidden sm:gap-5 lg:h-20">
    <div class="flex min-w-0 items-center gap-4">
        <button @click="sidebarOpen = true"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm text-slate-600 transition-colors hover:bg-slate-50 lg:hidden"
            aria-label="Open navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="flex min-w-0 flex-col justify-center gap-1 leading-none">
            <p class="m-0 text-[10px] font-bold uppercase leading-3 tracking-[0.16em] text-slate-500">E-Beam Operations</p>
            <h1 class="m-0 truncate text-base font-bold leading-6 tracking-tight text-slate-900 sm:text-lg">@yield('title', 'Dashboard')</h1>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-3 sm:gap-4">
        <div class="hidden items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-500 md:flex">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            System Online
        </div>

        <div x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false" class="relative">
            <button type="button" @click.stop="open = !open" :aria-expanded="open.toString()" aria-haspopup="menu"
                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 shadow-sm transition-colors hover:border-blue-200 hover:bg-blue-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                <div class="flex h-9 w-9 items-center 3xl:h-10 3xl:w-10 justify-center rounded-lg bg-blue-600 text-xs font-bold text-white shadow-sm">
                    {{ strtoupper(substr(auth('admin')->user()->name, 0, 1)) }}
                </div>
                <div class="hidden max-w-36 text-left sm:block">
                    <p class="truncate text-xs font-semibold text-slate-800">{{ auth('admin')->user()->name }}</p>
                    <p class="mt-0.5 truncate text-[10px] capitalize text-slate-500">{{ str_replace('_', ' ', auth('admin')->user()->role ?? 'administrator') }}</p>
                </div>
                <i class="fa-solid fa-chevron-down hidden text-[9px] text-slate-400 transition-transform sm:block" :class="open ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="open" x-cloak x-transition.origin.top.right
                class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-200/60">
                <div class="border-b border-slate-100 px-3 py-2.5">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">Signed in as</p>
                    <p class="mt-1 truncate text-xs font-semibold text-slate-700">{{ auth('admin')->user()->email }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('admin.profile') }}"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50 hover:text-blue-600">
                        <i class="fa-solid fa-user-gear w-4 text-center"></i> Account Settings
                    </a>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50 hover:text-rose-700">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
