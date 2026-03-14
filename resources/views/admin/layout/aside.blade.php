<div id="overlay" x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak
    x-transition:enter="transition opacity duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition opacity duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden">
</div>

<aside id="sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 m-3 transition-transform duration-300 ease-in-out 
    text-slate-300 bg-gray-900 shadow-2xl rounded-2xl md:translate-x-0 md:sticky md:top-3 h-[calc(100vh-1.5rem)] overflow-y-auto border border-gray-800">

    <div class="flex items-center justify-center px-6 py-8 border-b border-gray-800/50">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-600 rounded-lg">
                <i class="text-xl text-white fas fa-bolt"></i>
            </div>
            <h2 class="text-xl font-extrabold tracking-tight text-white uppercase">
                Beam <span class="text-blue-500">Admin</span>
            </h2>
        </div>
    </div>

    <nav class="px-4 py-6 space-y-1.5">

        <p class="px-4 mb-2 text-xs font-semibold tracking-widest text-gray-500 uppercase">Main Menu</p>

        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'hover:bg-gray-800 hover:text-white' }}">
            <i class="w-5 text-center fas fa-chart-pie"></i>
            <span class="font-medium">Dashboard</span>
        </a>
        <a href="{{ route('admin.customerList.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.pallets.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'hover:bg-gray-800 hover:text-white' }}">
            <i class="w-5 text-center fas fa-boxes-stacked"></i>
            <span class="font-medium">Add Company</span>
        </a>

        {{-- NEW: BUSINESS MONITORING MENU --}}
        <a href="{{ route('admin.business.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.business.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/20' : 'hover:bg-gray-800 hover:text-white' }}">
            <i class="w-5 text-center fas fa-magnifying-glass-chart"></i>
            <span class="font-medium">Business Monitor</span>
            @php
                // Opsional: Hitung jumlah pending approval untuk badge
                $pendingCount = \App\Models\Booking::where('status', 'pending')->count();
            @endphp
            @if ($pendingCount > 0)
                <span
                    class="ml-auto bg-rose-500 text-[10px] px-2 py-0.5 rounded-full text-white">{{ $pendingCount }}</span>
            @endif
        </a>

        <div x-data="{ open: {{ request()->routeIs('admin.bookings*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.bookings*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i class="w-5 text-center fas fa-book-bookmark"></i>
                    <span class="font-medium">Cargo Management</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="open" x-cloak x-collapse class="pl-4 mt-1 ml-4 space-y-1 border-l border-gray-800">
                <a href="{{ route('admin.bookings') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.bookings') && !request()->route('status') ? 'text-blue-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    All Orders
                </a>
                <a href="{{ route('admin.bookings.status', 'pending') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->route('status') == 'pending' ? 'text-amber-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    Incoming Order
                </a>
                <a href="{{ route('admin.bookings.status', 'approved') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->route('status') == 'approved' ? 'text-emerald-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    Approved
                </a>
                {{-- <a href="{{ route('admin.bookings.status', 'processing') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->route('status') == 'processing' ? 'text-blue-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    On Process
                </a>
                <a href="{{ route('admin.bookings.status', 'completed') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->route('status') == 'completed' ? 'text-indigo-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    Completed
                </a> --}}
            </div>
        </div>


        {{-- <a href="{{ route('admin.slots.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.slots.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'hover:bg-gray-800 hover:text-white' }}">
            <i class="w-5 text-center fas fa-layer-group"></i>
            <span class="font-medium">Slot Management</span>
        </a> --}}

        {{-- ── Layer 3: Production Management ── --}}
        <div x-data="{ open: {{ request()->routeIs('admin.production*') ? 'true' : 'false' }} }">
            <button @click="open = !open"
                class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.production*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                <div class="flex items-center gap-3">
                    <i class="w-5 text-center fas fa-industry"></i>
                    <span class="font-medium">Production Management</span>
                </div>
                <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"
                    :class="open ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="open" x-cloak x-collapse class="pl-4 mt-1 ml-4 space-y-1 border-l border-gray-800">
                <a href="{{ route('admin.production.parameter') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.production.parameter') ? 'text-blue-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    Process Parameter
                </a>
                <a href="{{ route('admin.production.batch-queue') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.production.batch-queue') ? 'text-blue-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    Queue Task
                </a>
                <a href="{{ route('admin.production.offline') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.production.offline') ? 'text-blue-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    Process Product Irradiation
                </a>
                <a href="{{ route('admin.production.finish') }}"
                    class="block px-4 py-2 text-sm rounded-lg transition-all {{ request()->routeIs('admin.production.finish') ? 'text-blue-500 font-bold' : 'text-gray-500 hover:text-white' }}">
                    Product Finish
                </a>
            </div>
        </div>

        {{-- Ganti link Calendar dengan ini --}}
        <a href="{{ route('admin.pallets.index') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.pallets.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'hover:bg-gray-800 hover:text-white' }}">
            <i class="w-5 text-center fas fa-boxes-stacked"></i>
            <span class="font-medium">Pallet Management</span>
        </a>

    </nav>

    <div class="absolute w-full px-8 text-center bottom-6">
        <div class="p-4 border rounded-2xl bg-gray-800/40 border-gray-700/50">
            <p class="text-xs text-gray-400">Logged in as:</p>
            <p class="text-sm font-semibold text-white truncate">{{ auth('admin')->user()->name }}</p>
        </div>
    </div>

</aside>
