<aside id="sidebar"
    :class="sidebarOpen ? 'w-72 opacity-100 translate-x-0 md:m-4' : 'w-0 -translate-x-full opacity-0 md:m-0'"
    class="fixed inset-y-0 left-0 z-50 transition-all duration-300 ease-in-out
    text-slate-300 bg-gray-900 shadow-2xl
    rounded-none md:rounded-2xl border border-gray-800 overflow-y-auto
    md:sticky md:top-4 h-full md:h-[calc(100vh-2rem)]">

    {{-- SIDEBAR HEADER --}}
    <div
        class="sticky top-0 z-10 flex items-center justify-between px-6 py-6 bg-gray-900 border-b border-gray-800/60 rounded-t-2xl">
        <div class="flex items-center gap-3">
            <div class="p-2.5 bg-blue-600 rounded-xl shadow-lg shadow-blue-600/20">
                <i class="text-lg text-white fas fa-bolt animate-pulse"></i>
            </div>
            <div>
                <h2 class="text-base font-black leading-none tracking-wider text-white uppercase">
                    Beam <span class="text-blue-500">Admin</span>
                </h2>
                <p class="text-[10px] text-slate-500 font-medium mt-1 tracking-tight">E-Beam Control Console</p>
            </div>
        </div>

        <button @click="sidebarOpen = false"
            class="p-2 text-gray-400 transition-colors rounded-xl hover:bg-gray-800 hover:text-white md:hidden">
            <i class="text-base fas fa-bars"></i>
        </button>
    </div>

    {{-- NAVIGATION MENU --}}
    <nav class="px-4 py-6 space-y-6 whitespace-nowrap">

        {{-- SECTION 1: CORE OPERATIONS --}}
        <div class="space-y-1.5">
            <p class="px-4 mb-2 text-[10px] font-bold tracking-widest text-slate-500 uppercase">Core Operations</p>

            {{-- Dashboard --}}
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 font-bold' : 'hover:bg-gray-800 hover:text-white group text-slate-400' }}">
                <i class="w-5 text-sm text-center transition-transform fas fa-chart-pie group-hover:scale-110"></i>
                <span class="text-sm font-medium">Main Dashboard</span>
            </a>

            {{-- Customer Management --}}
            <a href="{{ route('admin.customerList.index') }}"
                class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.customerList.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 font-bold' : 'hover:bg-gray-800 hover:text-white group text-slate-400' }}">
                <i class="w-5 text-sm text-center transition-transform fas fa-users group-hover:scale-110"></i>
                <span class="text-sm font-medium">Customer Directory</span>
            </a>

            {{-- Order Management Dropdown --}}
            <div x-data="{ open: {{ request()->routeIs('admin.bookings*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.bookings*') ? 'bg-gray-800/70 text-white' : 'hover:bg-gray-800/50 hover:text-white text-slate-400 group' }}">
                    <div class="flex items-center gap-3">
                        <i
                            class="w-5 text-sm text-center text-blue-400 transition-transform fas fa-book-bookmark group-hover:scale-110"></i>
                        <span class="text-sm font-medium">Order Management</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @php $pendingCount = \App\Models\Booking::where('status', 'pending')->count(); @endphp
                        @if ($pendingCount > 0)
                            <span
                                class="bg-rose-500/20 text-rose-400 text-[10px] font-black px-2 py-0.5 rounded-md border border-rose-500/30">{{ $pendingCount }}</span>
                        @endif
                        <i class="fas fa-chevron-down text-[9px] text-slate-500 transition-transform duration-300"
                            :class="open ? 'rotate-180 text-white' : ''"></i>
                    </div>
                </button>

                <div x-show="open" x-cloak x-collapse class="pl-4 mt-1 ml-4 space-y-1 border-l border-gray-800">
                    <a href="{{ route('admin.bookings') }}"
                        class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.bookings') && !request()->route('status') ? 'text-blue-400 font-bold bg-blue-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                        All Orders
                    </a>
                    <a href="{{ route('admin.bookings.status', 'pending') }}"
                        class="flex items-center justify-between px-4 py-2 text-xs rounded-lg transition-all {{ request()->route('status') == 'pending' ? 'text-amber-400 font-bold bg-amber-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                        <span>Incoming Request</span>
                        @if ($pendingCount > 0)
                            <span class="w-1.5 h-1.5 mr-1 rounded-full bg-amber-400 animate-pulse"></span>
                        @endif
                    </a>
                    <a href="{{ route('admin.bookings.status', 'processing') }}"
                        class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->route('status') == 'processing' ? 'text-blue-400 font-bold bg-blue-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                        On Progress
                    </a>
                    <a href="{{ route('admin.bookings.status', 'completed') }}"
                        class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->route('status') == 'completed' ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                        Completed
                    </a>
                </div>
            </div>

            {{-- Production Management Dropdown --}}
            @if (in_array(auth()->user()->role, ['superadmin', 'production']))
                <div x-data="{ open: {{ request()->routeIs('admin.production*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.production*') ? 'bg-gray-800/70 text-white' : 'hover:bg-gray-800/50 hover:text-white text-slate-400 group' }}">
                        <div class="flex items-center gap-3">
                            <i
                                class="w-5 text-sm text-center text-purple-400 transition-transform fas fa-industry group-hover:scale-110"></i>
                            <span class="text-sm font-medium">Production Management</span>
                        </div>
                        <i class="fas fa-chevron-down text-[9px] text-slate-500 transition-transform duration-300"
                            :class="open ? 'rotate-180 text-white' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak x-collapse class="pl-4 mt-1 ml-4 space-y-1 border-l border-gray-800">
                        <a href="{{ route('admin.production.index') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.production.index') ? 'text-purple-400 font-bold bg-purple-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            Overview Board
                        </a>
                        <a href="{{ route('admin.production.parameter') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.production.parameter') ? 'text-purple-400 font-bold bg-purple-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            1. Process Parameter
                        </a>
                        <a href="{{ route('admin.production.batch-queue') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.production.batch-queue') ? 'text-purple-400 font-bold bg-purple-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            2. Queue Task
                        </a>
                        <a href="{{ route('admin.production.offline') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.production.offline') ? 'text-purple-400 font-bold bg-purple-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            3. In Irradiation
                        </a>
                        <a href="{{ route('admin.production.finish') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.production.finish') ? 'text-purple-400 font-bold bg-purple-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            4. Finish Irradiation
                        </a>
                    </div>
                </div>
            @endif

            {{-- Dosimeter Management --}}
            @if (in_array(auth()->user()->role, ['superadmin', 'production']))
                <a href="{{ route('admin.dosimeter.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.dosimeter.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 font-bold' : 'hover:bg-gray-800 hover:text-white group text-slate-400' }}">
                    <i
                        class="w-5 text-sm text-center transition-transform fa-solid fa-flask text-sky-400 group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Dosimeter Analytics</span>
                </a>
            @endif
        </div>

        {{-- SECTION 2: LOGISTICS & INVENTORY --}}
        @if (in_array(auth()->user()->role, ['superadmin', 'cargo_admin']))
            <div class="space-y-1.5 pt-2 border-t border-gray-800/40">
                <p class="px-4 mb-2 text-[10px] font-bold tracking-widest text-slate-500 uppercase">Logistics &
                    Inventory</p>

                <a href="{{ route('admin.pallets.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.pallets.index') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 font-bold' : 'hover:bg-gray-800 hover:text-white group text-slate-400' }}">
                    <i
                        class="w-5 text-sm text-center transition-transform fas fa-boxes-stacked group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Pallet Control</span>
                </a>

                <a href="{{ route('admin.porter.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.porter.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 font-bold' : 'hover:bg-gray-800 hover:text-white group text-slate-400' }}">
                    <i
                        class="w-5 text-sm text-center transition-transform fas fa-people-carry-box group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Porter Assignment</span>
                </a>

                <a href="{{ route('admin.warehouse-pics.index') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('admin.warehouse-pics.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20 font-bold' : 'hover:bg-gray-800 hover:text-white group text-slate-400' }}">
                    <i
                        class="w-5 text-sm text-center transition-transform fas fa-user-shield group-hover:scale-110"></i>
                    <span class="text-sm font-medium">Warehouse PIC</span>
                </a>
            </div>
        @endif

        {{-- SECTION 3: OPERATIONAL REPORTS --}}
        @if (in_array(auth()->user()->role, ['superadmin', 'manager', 'production']))
            <div class="space-y-1.5 pt-2 border-t border-gray-800/40">
                <p class="px-4 mb-2 text-[10px] font-bold tracking-widest text-slate-500 uppercase">Operational Reports
                </p>

                {{-- Nuctech Reporting (Technical) --}}
                <div x-data="{ open: {{ request()->is('admin/report/nuctech*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('admin/report/nuctech*') ? 'bg-gray-800/70 text-white' : 'hover:bg-gray-800/50 hover:text-white text-slate-400 group' }}">
                        <div class="flex items-center gap-3">
                            <i
                                class="w-5 text-sm text-center text-indigo-400 transition-transform fas fa-file-contract group-hover:scale-110"></i>
                            <span class="text-sm font-medium">Technical (Nuctech)</span>
                        </div>
                        <i class="fas fa-chevron-down text-[9px] text-slate-500 transition-transform duration-200"
                            :class="open ? 'rotate-180 text-white' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak x-collapse
                        class="pl-4 mt-1 ml-4 space-y-1 border-l border-indigo-900/40">

                        <a href="{{ route('admin.report.nuctech', 'daily-work') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'daily-work' ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            1. Workshop Daily Work
                        </a>
                        <a href="{{ route('admin.report.nuctech', 'daily-schedule') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'daily-schedule' ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            2. Daily Processing Schedule
                        </a>
                        <a href="{{ route('admin.report.nuctech', 'delivery-form') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'delivery-form' ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            3. Processing & Delivery Form
                        </a>
                        <a href="{{ route('admin.report.nuctech', 'processing-record') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'processing-record' ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            4. Irradiation Process Log
                        </a>
                        <a href="{{ route('admin.report.nuctech', 'equipment-record') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'equipment-record' ? 'text-indigo-400 font-bold bg-indigo-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            5. Machine Operation Log
                        </a>
                    </div>
                </div>

                {{-- JTS Reporting (Logistics) --}}
                <div x-data="{ open: {{ request()->is('admin/report/jts*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('admin/report/jts*') ? 'bg-gray-800/70 text-white' : 'hover:bg-gray-800/50 hover:text-white text-slate-400 group' }}">
                        <div class="flex items-center gap-3">
                            <i
                                class="w-5 text-sm text-center transition-transform fas fa-file-invoice text-emerald-400 group-hover:scale-110"></i>
                            <span class="text-sm font-medium">Logistics (JTS)</span>
                        </div>
                        <i class="fas fa-chevron-down text-[9px] text-slate-500 transition-transform duration-200"
                            :class="open ? 'rotate-180 text-white' : ''"></i>
                    </button>

                    <div x-show="open" x-cloak x-collapse
                        class="pl-4 mt-1 ml-4 space-y-1 border-l border-emerald-900/40">

                        <a href="{{ route('admin.report.jts', 'unirradiated-card') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'unirradiated-card' ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            1. Unirradiated Material Card
                        </a>
                        <a href="{{ route('admin.report.jts', 'delivery-outbound') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'delivery-outbound' ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            2. Outbound Delivery Slip
                        </a>
                        <a href="{{ route('admin.report.jts', 'delivery-inbound') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'delivery-inbound' ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            3. Inbound Delivery Slip
                        </a>
                        <a href="{{ route('admin.report.jts', 'irradiated-card') }}"
                            class="block px-4 py-2 text-xs rounded-lg transition-all {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'irradiated-card' ? 'text-emerald-400 font-bold bg-emerald-500/5' : 'text-slate-500 hover:text-slate-200 hover:bg-gray-800/30' }}">
                            4. Irradiated Material Card
                        </a>
                    </div>
                </div>
            </div>
        @endif

    </nav>
</aside>
