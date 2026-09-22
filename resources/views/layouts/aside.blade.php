<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-[min(288px,calc(100vw-16px))] flex-col border-r border-slate-200 bg-white shadow-2xl shadow-slate-900/10 transition-transform duration-300 print:hidden lg:sticky lg:top-0 lg:h-[100dvh] lg:w-[268px] lg:shrink-0 lg:shadow-none xl:w-[276px] 2xl:w-[288px] 3xl:w-[300px]">
    <div
        class="flex h-[68px] items-center justify-between border-b border-slate-200 px-4 sm:h-[72px] sm:px-5 lg:h-[76px] 2xl:h-[80px] 2xl:px-6">
        <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-3">
            <div class="flex items-center justify-center w-10 h-10 text-white bg-blue-600 rounded-xl"><i
                    class="fa-solid fa-bolt"></i></div>
            <div>
                <p class="text-[15px] font-extrabold tracking-tight text-slate-900">BeamApp</p>
                <p class="mt-0.5 text-[9px] font-bold uppercase tracking-[0.16em] text-slate-400">Customer Portal</p>
            </div>
        </a>
        <button @click="sidebarOpen = false"
            class="inline-flex items-center justify-center text-sm transition-colors bg-white border h-9 w-9 shrink-0 rounded-xl border-slate-200 text-slate-600 hover:bg-slate-50 lg:hidden">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="flex-1 p-3 pt-5 overflow-y-auto">
        <p class="mb-2 px-3 text-[9px] font-extrabold uppercase tracking-[0.16em] text-slate-400">Services</p>
        <div class="space-y-1">
            <a href="{{ route('customer.dashboard') }}"
                class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-colors {{ request()->routeIs('customer.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                <i
                    class="fa-solid fa-house w-5 shrink-0 text-center text-[13px] {{ request()->routeIs('customer.dashboard') ? 'text-blue-600' : 'text-slate-400' }}"></i><span>Dashboard</span>
            </a>
            <a href="{{ route('customer.booking.create') }}"
                class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-colors {{ request()->routeIs('customer.booking.create') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                <i
                    class="fa-solid fa-plus w-5 shrink-0 text-center text-[13px] {{ request()->routeIs('customer.booking.create') ? 'text-blue-600' : 'text-slate-400' }}"></i><span>New
                    Sterilization Order</span>
            </a>
            <a href="{{ route('customer.history') }}"
                class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-colors {{ request()->routeIs('customer.history') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                <i
                    class="fa-solid fa-clock-rotate-left w-5 shrink-0 text-center text-[13px] {{ request()->routeIs('customer.history') ? 'text-blue-600' : 'text-slate-400' }}"></i><span>Order
                    History</span>
            </a>
        </div>

        <p class="mb-2 mt-7 px-3 text-[9px] font-extrabold uppercase tracking-[0.16em] text-slate-400">Account</p>
        <div class="space-y-1">
            <a href="{{ route('customer.profile') }}"
                class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-semibold transition-colors {{ request()->routeIs('customer.profile*') ? 'bg-blue-50 text-blue-700' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' }}">
                <i
                    class="fa-solid fa-user w-5 shrink-0 text-center text-[13px] {{ request()->routeIs('customer.profile*') ? 'text-blue-600' : 'text-slate-400' }}"></i><span>My
                    Profile</span>
            </a>
        </div>
    </nav>

    <div class="p-3 border-t border-slate-200">
        <div class="p-3 rounded-xl bg-slate-50">
            <p class="text-xs font-semibold truncate text-slate-700">{{ auth('customer')->user()->name ?? 'Customer' }}
            </p>
            <p class="mt-1 truncate text-[10px] text-slate-400">{{ auth('customer')->user()->email ?? '' }}</p>
            <form action="{{ route('customer.logout') }}" method="POST" class="mt-3">
                @csrf
                <button
                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-2 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50"><i
                        class="fa-solid fa-right-from-bracket"></i> Sign Out</button>
            </form>
        </div>
    </div>
</aside>
