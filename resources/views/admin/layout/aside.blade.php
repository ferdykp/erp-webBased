@php
    $adminUser = auth('admin')->user();
    $role = $adminUser?->role;
    $pendingOrders = \App\Models\Booking::where('booking_type', 'regular')->where('status', 'pending')->count();
    $pendingTests = \Illuminate\Support\Facades\Schema::hasTable('product_tests')
        ? \App\Models\ProductTest::where('status', 'parameter_pending')->count()
        : 0;

    $navBase =
        'flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-xs font-semibold transition-colors duration-200 3xl:min-h-12 3xl:px-4 3xl:py-3 3xl:text-sm';
    $navIdle = 'text-slate-400 hover:bg-slate-900 hover:text-slate-100';
    $navActive = 'bg-blue-600 text-white shadow-lg shadow-blue-950/20';
    $navParent = 'bg-slate-900 text-slate-100';
    $subBase =
        'block rounded-lg px-3 py-2 text-[11px] font-medium transition-colors duration-200 3xl:px-4 3xl:py-2.5 3xl:text-xs';
    $subIdle = 'text-slate-500 hover:bg-slate-900 hover:text-slate-200';
    $subActive = 'bg-slate-900 text-blue-400';
@endphp

<aside id="sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-[min(288px,calc(100vw-16px))] flex-col border-r border-slate-800/80 bg-slate-950 text-slate-300 shadow-2xl shadow-slate-950/20 transition-transform duration-300 ease-out print:hidden lg:sticky lg:top-0 lg:h-[100dvh] lg:w-[272px] lg:shrink-0 lg:shadow-none xl:w-[280px] 2xl:w-[288px] 3xl:w-[320px] 4xl:w-[336px]">

    <div
        class="flex h-[68px] shrink-0 items-center justify-between border-b border-slate-800/80 px-4 sm:h-[72px] sm:px-5 lg:h-[76px] 2xl:h-[80px] 2xl:px-6 3xl:h-[84px] 3xl:px-7">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center min-w-0 gap-3">
            <div
                class="flex items-center justify-center w-10 h-10 text-white bg-blue-600 shadow-lg shrink-0 rounded-xl 3xl:h-11 3xl:w-11 shadow-blue-950/30">
                <i class="fa-solid fa-bolt"></i>
            </div>
            <div class="min-w-0">
                <p class="truncate text-[15px] font-extrabold 3xl:text-base tracking-tight text-white">Beam Admin</p>
                <p class="mt-0.5 truncate text-[10px] font-medium tracking-wide 3xl:text-[11px] text-slate-500">E-BEAM
                    OPERATIONS</p>
            </div>
        </a>
        <button @click="sidebarOpen = false"
            class="inline-flex items-center justify-center w-8 h-8 text-xs transition-colors rounded-lg shrink-0 text-slate-500 hover:bg-slate-800 hover:text-slate-200 lg:hidden"
            aria-label="Close navigation">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5 3xl:px-4 3xl:py-6 [scrollbar-width:thin]">
        <div class="mb-6 space-y-1">
            <p class="mb-2 px-3 text-[9px] font-extrabold uppercase 3xl:text-[10px] tracking-[0.16em] text-slate-600">
                Workspace</p>

            <a href="{{ route('admin.dashboard') }}"
                class="{{ $navBase }} {{ request()->routeIs('admin.dashboard') ? $navActive : $navIdle }}">
                <i
                    class="fa-solid fa-table-cells-large w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-500' }}"></i>
                <span>Dashboard</span>
            </a>

            <div x-data="{ open: {{ request()->routeIs('admin.bookings*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                    class="{{ $navBase }} {{ request()->routeIs('admin.bookings*') ? $navParent : $navIdle }}">
                    <i
                        class="fa-solid fa-clipboard-list w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.bookings*') ? 'text-blue-400' : 'text-slate-500' }}"></i>
                    <span>Order Management</span>
                    @if ($pendingOrders > 0)
                        <span
                            class="ml-auto rounded-md bg-blue-500/15 px-1.5 py-0.5 text-[9px] font-extrabold text-blue-300">{{ $pendingOrders }}</span>
                    @endif
                    <i class="fa-solid fa-chevron-down ml-1 text-[8px] text-slate-600 transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-transition.opacity.duration.150ms
                    class="ml-5 mt-1 space-y-0.5 border-l border-slate-800 pl-3">
                    <a href="{{ route('admin.bookings') }}"
                        class="{{ $subBase }} {{ request()->routeIs('admin.bookings') ? $subActive : $subIdle }}">All
                        Orders</a>
                    <a href="{{ route('admin.bookings.status', 'pending') }}"
                        class="{{ $subBase }} {{ request()->route('status') === 'pending' ? $subActive : $subIdle }}">Incoming
                        Request</a>
                    <a href="{{ route('admin.bookings.status', 'processing') }}"
                        class="{{ $subBase }} {{ request()->route('status') === 'processing' ? $subActive : $subIdle }}">On
                        Progress</a>
                    <a href="{{ route('admin.bookings.status', 'completed') }}"
                        class="{{ $subBase }} {{ request()->route('status') === 'completed' ? $subActive : $subIdle }}">Completed</a>
                </div>
            </div>

            <a href="{{ route('admin.testing.index') }}"
                class="{{ $navBase }} {{ request()->routeIs('admin.testing.*') ? $navActive : $navIdle }}">
                <i
                    class="fa-solid fa-flask-vial w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.testing.*') ? 'text-white' : 'text-slate-500' }}"></i>
                <span>Product Testing</span>
                @if ($pendingTests > 0)
                    <span
                        class="ml-auto rounded-md bg-amber-500/15 px-1.5 py-0.5 text-[9px] font-extrabold text-amber-300">{{ $pendingTests }}</span>
                @endif
            </a>

            @if (in_array($role, ['superadmin', 'production']))
                <div x-data="{ open: {{ request()->routeIs('admin.production*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="{{ $navBase }} {{ request()->routeIs('admin.production*') ? $navParent : $navIdle }}">
                        <i
                            class="fa-solid fa-industry w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.production*') ? 'text-blue-400' : 'text-slate-500' }}"></i>
                        <span>Production</span>
                        <i class="fa-solid fa-chevron-down ml-auto text-[8px] text-slate-600 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-transition.opacity.duration.150ms
                        class="ml-5 mt-1 space-y-0.5 border-l border-slate-800 pl-3">
                        <a href="{{ route('admin.production.index') }}"
                            class="{{ $subBase }} {{ request()->routeIs('admin.production.index') ? $subActive : $subIdle }}">Overview</a>
                        <a href="{{ route('admin.production.parameter') }}"
                            class="{{ $subBase }} {{ request()->routeIs('admin.production.parameter') ? $subActive : $subIdle }}">Process
                            Parameter</a>
                        <a href="{{ route('admin.production.batch-queue') }}"
                            class="{{ $subBase }} {{ request()->routeIs('admin.production.batch-queue') ? $subActive : $subIdle }}">Queue
                            Task</a>
                        <a href="{{ route('admin.production.offline') }}"
                            class="{{ $subBase }} {{ request()->routeIs('admin.production.offline') ? $subActive : $subIdle }}">In
                            Irradiation</a>
                        <a href="{{ route('admin.production.finish') }}"
                            class="{{ $subBase }} {{ request()->routeIs('admin.production.finish') ? $subActive : $subIdle }}">Finish
                            & QA</a>
                    </div>
                </div>

                <a href="{{ route('admin.dosimeter.index') }}"
                    class="{{ $navBase }} {{ request()->routeIs('admin.dosimeter.*') ? $navActive : $navIdle }}">
                    <i
                        class="fa-solid fa-wave-square w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.dosimeter.*') ? 'text-white' : 'text-slate-500' }}"></i>
                    <span>Dosimeter</span>
                </a>
            @endif
        </div>

        <div class="mb-6 space-y-1">
            <p class="mb-2 px-3 text-[9px] font-extrabold uppercase 3xl:text-[10px] tracking-[0.16em] text-slate-600">
                Data & Logistics</p>
            <a href="{{ route('admin.customerList.index') }}"
                class="{{ $navBase }} {{ request()->routeIs('admin.customerList.*') ? $navActive : $navIdle }}">
                <i
                    class="fa-solid fa-building-user w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.customerList.*') ? 'text-white' : 'text-slate-500' }}"></i>
                <span>Customer Directory</span>
            </a>

            @if (in_array($role, ['superadmin', 'cargo_admin']))
                <a href="{{ route('admin.pallets.index') }}"
                    class="{{ $navBase }} {{ request()->routeIs('admin.pallets.*') ? $navActive : $navIdle }}">
                    <i
                        class="fa-solid fa-boxes-stacked w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.pallets.*') ? 'text-white' : 'text-slate-500' }}"></i><span>Pallet
                        Control</span>
                </a>
                <a href="{{ route('admin.porter.index') }}"
                    class="{{ $navBase }} {{ request()->routeIs('admin.porter.*') ? $navActive : $navIdle }}">
                    <i
                        class="fa-solid fa-people-carry-box w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.porter.*') ? 'text-white' : 'text-slate-500' }}"></i><span>Porter</span>
                </a>
                <a href="{{ route('admin.warehouse-pics.index') }}"
                    class="{{ $navBase }} {{ request()->routeIs('admin.warehouse-pics.*') ? $navActive : $navIdle }}">
                    <i
                        class="fa-solid fa-user-shield w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.warehouse-pics.*') ? 'text-white' : 'text-slate-500' }}"></i><span>Warehouse
                        PIC</span>
                </a>
            @endif
        </div>

        @if (in_array($role, ['superadmin', 'manager', 'production', 'cargo_admin']))
            <div class="mb-6 space-y-1">
                <p
                    class="mb-2 px-3 text-[9px] font-extrabold uppercase 3xl:text-[10px] tracking-[0.16em] text-slate-600">
                    Operational Reports</p>

                <div x-data="{ open: {{ request()->routeIs('admin.report.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open"
                        class="{{ $navBase }} {{ request()->routeIs('admin.report.*') ? $navParent : $navIdle }}">
                        <i
                            class="fa-solid fa-file w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.report.*') ? 'text-blue-400' : 'text-slate-500' }}"></i>
                        <span>Reports</span>
                        <i class="fa-solid fa-chevron-down ml-auto text-[8px] text-slate-600 transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-transition.opacity.duration.150ms
                        class="ml-5 mt-1 space-y-0.5 border-l border-slate-800 pl-3">
                        <a href="{{ route('admin.report.index') }}"
                            class="{{ $subBase }} {{ request()->routeIs('admin.report.index') ? $subActive : $subIdle }}">Report
                            Center</a>

                        @if (in_array($role, ['superadmin', 'manager', 'production']))
                            <p
                                class="px-3 pb-1 pt-3 text-[9px] font-extrabold uppercase 3xl:text-[10px] tracking-[0.14em] text-slate-600">
                                Technical · Nuctech</p>
                            <a href="{{ route('admin.report.nuctech', 'daily-work') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'daily-work' ? $subActive : $subIdle }}">Workshop
                                Daily Work</a>
                            <a href="{{ route('admin.report.nuctech', 'daily-schedule') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'daily-schedule' ? $subActive : $subIdle }}">Daily
                                Processing Schedule</a>
                            <a href="{{ route('admin.report.nuctech', 'delivery-form') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'delivery-form' ? $subActive : $subIdle }}">Processing
                                & Delivery</a>
                            <a href="{{ route('admin.report.nuctech', 'processing-record') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'processing-record' ? $subActive : $subIdle }}">Irradiation
                                Process Log</a>
                            <a href="{{ route('admin.report.nuctech', 'equipment-record') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.nuctech') && request()->route('type') === 'equipment-record' ? $subActive : $subIdle }}">Machine
                                Operation Log</a>
                        @endif

                        @if (in_array($role, ['superadmin', 'manager', 'cargo_admin']))
                            <p
                                class="px-3 pb-1 pt-3 text-[9px] font-extrabold uppercase 3xl:text-[10px] tracking-[0.14em] text-slate-600">
                                Logistics · JTS</p>
                            <a href="{{ route('admin.report.jts', 'unirradiated-card') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'unirradiated-card' ? $subActive : $subIdle }}">Unirradiated
                                Material Card</a>
                            <a href="{{ route('admin.report.jts', 'delivery-outbound') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'delivery-outbound' ? $subActive : $subIdle }}">Outbound
                                Delivery Slip</a>
                            <a href="{{ route('admin.report.jts', 'delivery-inbound') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'delivery-inbound' ? $subActive : $subIdle }}">Inbound
                                Delivery Slip</a>
                            <a href="{{ route('admin.report.jts', 'irradiated-card') }}"
                                class="{{ $subBase }} {{ request()->routeIs('admin.report.jts') && request()->route('type') === 'irradiated-card' ? $subActive : $subIdle }}">Irradiated
                                Material Card</a>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (in_array($role, ['superadmin', 'manager']))
            <div class="mb-0 space-y-1">
                <p
                    class="mb-2 px-3 text-[9px] font-extrabold uppercase 3xl:text-[10px] tracking-[0.16em] text-slate-600">
                    Management</p>
                <a href="{{ route('admin.business.index') }}"
                    class="{{ $navBase }} {{ request()->routeIs('admin.business.*') ? $navActive : $navIdle }}">
                    <i
                        class="fa-solid fa-chart-line w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.business.*') ? 'text-white' : 'text-slate-500' }}"></i><span>Business
                        Monitoring</span>
                </a>
                <a href="{{ route('admin.production-lines.index') }}"
                    class="{{ $navBase }} {{ request()->routeIs('admin.production-lines.*') ? $navActive : $navIdle }}">
                    <i
                        class="fa-solid fa-microchip w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.production-lines.*') ? 'text-white' : 'text-slate-500' }}"></i><span>Production
                        Units</span>
                </a>
                <a href="{{ route('admin.profile.profileList') }}"
                    class="{{ $navBase }} {{ request()->routeIs('admin.profile.profileList') || request()->routeIs('admin.profile.edit') ? $navActive : $navIdle }}">
                    <i
                        class="fa-solid fa-user-gear w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.profile.profileList') || request()->routeIs('admin.profile.edit') ? 'text-white' : 'text-slate-500' }}"></i><span>Staff
                        Accounts</span>
                </a>
            </div>
        @endif
    </nav>

    <div class="p-3 border-t shrink-0 border-slate-800/80 3xl:p-4">
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-slate-900">
            <div
                class="flex items-center justify-center text-xs font-bold text-white rounded-lg h-9 w-9 shrink-0 bg-slate-800">
                {{ strtoupper(substr($adminUser?->name ?? 'A', 0, 1)) }}</div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold truncate text-slate-200 3xl:text-sm">{{ $adminUser?->name }}</p>
                <p class="mt-0.5 truncate text-[10px] capitalize text-slate-500 3xl:text-[11px]">
                    {{ str_replace('_', ' ', $role ?? 'admin') }}</p>
            </div>
            <a href="{{ route('admin.profile') }}"
                class="inline-flex items-center justify-center w-8 h-8 text-xs transition-colors rounded-lg shrink-0 text-slate-500 hover:bg-slate-800 hover:text-slate-200"
                title="Account settings">
                <i class="fa-solid fa-gear"></i>
            </a>
        </div>
    </div>
</aside>
