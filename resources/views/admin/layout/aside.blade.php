<!-- Mobile Overlay: Muncul hanya di layar kecil (< md) saat sidebarOpen = true -->
<div x-show="sidebarOpen" @click="sidebarOpen = false" x-cloak x-transition:enter="transition opacity duration-300"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition opacity duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm md:hidden">
</div>

<!-- Sidebar Container -->
<aside id="sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-72 transition-transform duration-300 ease-in-out 
    text-slate-300 bg-gray-900 shadow-2xl 
    m-0 md:m-4 md:translate-x-0 md:sticky md:top-4 
    h-full md:h-[calc(100vh-2rem)] 
    rounded-none md:rounded-2xl border border-gray-800 overflow-y-auto">

    <!-- Brand Logo -->
    <div
        class="sticky top-0 z-10 flex items-center justify-center px-6 py-8 bg-gray-900 border-b border-gray-800/50 rounded-t-2xl">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-blue-600 rounded-lg shadow-lg shadow-blue-600/20">
                <i class="text-xl text-white fas fa-bolt"></i>
            </div>
            <h2 class="text-xl font-extrabold tracking-tight text-white uppercase">
                Beam <span class="text-blue-500">Admin</span>
            </h2>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="px-4 py-6 space-y-1.5">

        <p class="px-4 mb-2 text-[10px] font-bold tracking-widest text-gray-500 uppercase">Main Menu</p>

        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-gray-800 hover:text-white group' }}">
            <i class="w-5 text-center transition-transform fas fa-chart-pie group-hover:scale-110"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Customer Management -->
        @if (in_array(auth()->user()->role, ['superadmin', 'manager', 'cargo_admin']))
            <a href="{{ route('admin.customerList.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.customerList.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-gray-800 hover:text-white group' }}">
                <i class="w-5 text-center transition-transform fas fa-users group-hover:scale-110"></i>
                <span class="font-medium">Customer Management</span>
            </a>
        @endif

        <!-- Dropdown: Order Management -->
        @if (in_array(auth()->user()->role, ['superadmin', 'manager', 'cargo_admin']))
            <div x-data="{ open: {{ request()->routeIs('admin.bookings*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.bookings*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white group' }}">
                    <div class="flex items-center gap-3">
                        <i class="w-5 text-center text-blue-400 fas fa-book-bookmark"></i>
                        <span class="font-medium">Order Management</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @php $pendingCount = \App\Models\Booking::where('status', 'pending')->count(); @endphp
                        @if ($pendingCount > 0)
                            <span
                                class="bg-rose-500/10 text-rose-400 text-[10px] font-bold px-2 py-0.5 rounded-full border border-rose-500/20">{{ $pendingCount }}</span>
                        @endif
                        <i class="fas fa-chevron-down text-[10px] transition-transform duration-300"
                            :class="open ? 'rotate-180' : ''"></i>
                    </div>
                </button>

                <div x-show="open" x-cloak x-collapse class="pl-5 mt-1 ml-4 space-y-1 border-l border-gray-800">
                    <a href="{{ route('admin.bookings') }}"
                        class="block py-2 text-sm transition-colors {{ request()->routeIs('admin.bookings') && !request()->route('status') ? 'text-blue-200 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        All Orders
                    </a>
                    <a href="{{ route('admin.bookings.status', 'pending') }}"
                        class="flex items-center justify-between py-2 text-sm transition-colors {{ request()->route('status') == 'pending' ? 'text-amber-400 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        <span>Incoming Order</span>
                        @if ($pendingCount > 0)
                            <span class="w-2 h-2 mr-2 rounded-full bg-amber-400 animate-pulse"></span>
                        @endif
                    </a>
                    <a href="{{ route('admin.bookings.status', 'processing') }}"
                        class="block py-2 text-sm transition-colors {{ request()->route('status') == 'processing' ? 'text-blue-400 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        On Progress
                    </a>
                    <a href="{{ route('admin.bookings.status', 'completed') }}"
                        class="block py-2 text-sm transition-colors {{ request()->route('status') == 'completed' ? 'text-emerald-400 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        Completed
                    </a>
                </div>
            </div>
        @endif

        <!-- Dropdown: Production Management -->
        @if (in_array(auth()->user()->role, ['superadmin', 'manager', 'production']))
            <div x-data="{ open: {{ request()->routeIs('admin.production*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.production*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white group' }}">
                    <div class="flex items-center gap-3">
                        <i class="w-5 text-center text-purple-400 fas fa-industry"></i>
                        <span class="text-sm font-medium">Production Management</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-300"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-cloak x-collapse class="pl-5 mt-1 ml-4 space-y-1 border-l border-gray-800">
                    <a href="{{ route('admin.production.parameter') }}"
                        class="block py-2 text-sm transition-colors {{ request()->routeIs('admin.production.parameter') ? 'text-purple-400 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        1. Process Parameter
                    </a>
                    <a href="{{ route('admin.production.batch-queue') }}"
                        class="block py-2 text-sm transition-colors {{ request()->routeIs('admin.production.batch-queue') ? 'text-purple-400 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        2. Queue Task
                    </a>
                    <a href="{{ route('admin.production.offline') }}"
                        class="block py-2 text-sm transition-colors {{ request()->routeIs('admin.production.offline') ? 'text-purple-400 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        3. Start Irradiation
                    </a>
                    <a href="{{ route('admin.production.finish') }}"
                        class="block py-2 text-sm transition-colors {{ request()->routeIs('admin.production.finish') ? 'text-purple-400 font-semibold' : 'text-slate-500 hover:text-slate-200' }}">
                        4. Finish Irradiation
                    </a>
                </div>
            </div>
        @endif

        <!-- Master Logistics & Inventory Section -->
        @if (in_array(auth()->user()->role, ['superadmin', 'manager', 'cargo_admin']))
            <hr class="my-4 border-gray-800/50">
            <p class="px-4 mb-2 text-[10px] font-bold tracking-widest text-gray-500 uppercase">Logistics & Warehouse</p>

            <!-- Pallet -->
            <a href="{{ route('admin.pallets.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.pallets.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-gray-800 hover:text-white group' }}">
                <i class="w-5 text-center transition-transform fas fa-boxes-stacked group-hover:scale-110"></i>
                <span class="font-medium">Pallet Management</span>
            </a>

            <!-- Porter -->
            <a href="{{ route('admin.porter.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.porter.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-gray-800 hover:text-white group' }}">
                <i class="w-5 text-center transition-transform fas fa-people-carry-box group-hover:scale-110"></i>
                <span class="font-medium">Porter Management</span>
            </a>

            <!-- PIC Warehouse -->
            <a href="{{ route('admin.warehouse-pics.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.warehouse-pics.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'hover:bg-gray-800 hover:text-white group' }}">
                <i class="w-5 text-center transition-transform fas fa-user-shield group-hover:scale-110"></i>
                <span class="font-medium">PIC Warehouse</span>
            </a>
        @endif

        <!-- Reporting Section -->
        @if (in_array(auth()->user()->role, ['superadmin', 'manager']))
            <hr class="my-4 border-gray-800/50">
            <p class="px-4 mb-2 text-[10px] font-bold tracking-widest text-gray-500 uppercase">Reports</p>

            <!-- Layer 4: JTS Reporting (Gudang/Logistik) -->
            <div x-data="{ open: {{ request()->is('admin/report/jts*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('admin/report/jts*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="w-5 text-center fas fa-file-invoice text-emerald-500"></i>
                        <span class="font-medium">Reporting JTS</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-cloak x-collapse class="pl-2 mt-1 ml-4 space-y-1 border-l border-emerald-800/50">
                    <a href="{{ route('admin.report.jts', 'unirradiated-card') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.jts', 'unirradiated-card')) ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        1. Unirradiated Material ID Card
                    </a>
                    <a href="{{ route('admin.report.jts', 'delivery-outbound') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.jts', 'delivery-outbound')) ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        2. Product Delivery Slip Outbound
                    </a>
                    <a href="{{ route('admin.report.jts', 'delivery-inbound') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.jts', 'delivery-inbound')) ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        3. Product Delivery Slip Inbound
                    </a>
                    <a href="{{ route('admin.report.jts', 'irradiated-card') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.jts', 'irradiated-card')) ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        4. Irradiated Material ID Card
                    </a>
                </div>
            </div>
        @endif

        <!-- Layer 5: Nuctech Reporting (Teknis Produksi) -->
        @if (in_array(auth()->user()->role, ['superadmin', 'manager']))
            <div x-data="{ open: {{ request()->is('admin/report/nuctech*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('admin/report/nuctech*') ? 'bg-gray-800 text-white' : 'hover:bg-gray-800 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <i class="w-5 text-center text-indigo-400 fas fa-file-contract"></i>
                        <span class="font-medium">Reporting Nuctech</span>
                    </div>
                    <i class="fas fa-chevron-down text-[10px] transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>

                <div x-show="open" x-cloak x-collapse class="pl-2 mt-1 ml-4 space-y-1 border-l border-indigo-800/50">
                    <a href="{{ route('admin.report.nuctech', 'daily-work') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.nuctech', 'daily-work')) ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        1. Workshop Team Daily Work
                    </a>
                    <a href="{{ route('admin.report.nuctech', 'processing-record') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.nuctech', 'processing-record')) ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        2. Irradiation Processing Record
                    </a>
                    <a href="{{ route('admin.report.nuctech', 'delivery-form') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.nuctech', 'delivery-form')) ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        3. Product Processing & Delivery
                    </a>
                    <a href="{{ route('admin.report.nuctech', 'daily-schedule') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.nuctech', 'daily-schedule')) ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        4. Daily Processing Schedule
                    </a>
                    <a href="{{ route('admin.report.nuctech', 'equipment-record') }}"
                        class="block px-4 py-2 text-[11px] rounded-lg transition-all {{ request()->fullUrlIs(route('admin.report.nuctech', 'equipment-record')) ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-gray-500 hover:text-slate-200' }}">
                        5. Equipment Operation Record
                    </a>
                </div>
            </div>
        @endif
    </nav>
</aside>
