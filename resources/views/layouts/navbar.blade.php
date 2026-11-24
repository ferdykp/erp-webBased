<nav class="sticky top-0 z-30 flex h-[72px] flex-none items-center justify-between gap-4 border-b border-slate-200 bg-white/95 px-[clamp(0.75rem,1.25vw,2rem)] backdrop-blur-xl print:hidden sm:gap-5 lg:h-20">
    <div class="flex min-w-0 items-center gap-4">
        <button @click="sidebarOpen = true"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm text-slate-600 transition-colors hover:bg-slate-50 lg:hidden">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="flex min-w-0 flex-col justify-center gap-1 leading-none">
            <p class="m-0 text-[10px] font-bold uppercase leading-3 tracking-[0.16em] text-slate-500">Customer Portal</p>
            <p class="m-0 text-base font-bold leading-6 tracking-tight text-slate-900">{{ request()->routeIs('customer.dashboard') ? 'Dashboard' : (request()->routeIs('customer.booking*') ? 'Sterilization Order' : (request()->routeIs('customer.history') ? 'Order History' : 'Account')) }}</p>
        </div>
    </div>

    <div x-data="{ open: false }" class="relative">
        <button type="button" @click="open = !open" @click.outside="open = false" :aria-expanded="open.toString()" aria-haspopup="menu" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 shadow-sm transition-colors hover:border-blue-200 hover:bg-blue-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-xs font-bold uppercase text-blue-700">{{ substr(auth('customer')->user()->name ?? 'C', 0, 1) }}</div>
            <div class="hidden max-w-36 text-left sm:block"><p class="truncate text-xs font-semibold text-slate-700">{{ auth('customer')->user()->name ?? 'Customer' }}</p><p class="mt-0.5 text-[10px] text-slate-500">Verified account</p></div>
            <i class="fa-solid fa-chevron-down hidden text-[9px] text-slate-400 transition-transform sm:block" :class="open ? 'rotate-180' : ''"></i>
        </button>
        <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-52 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-200/60">
            <a href="{{ route('customer.profile') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50 hover:text-blue-600"><i class="fa-solid fa-user-gear w-4 text-center"></i> My Profile</a>
            <form action="{{ route('customer.logout') }}" method="POST">@csrf<button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50 hover:text-rose-700"><i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Sign Out</button></form>
        </div>
    </div>
</nav>
