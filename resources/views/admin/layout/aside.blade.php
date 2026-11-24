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

<aside data-app-sidebar id="sidebar" :data-collapsed="sidebarCollapsed ? 'true' : 'false'" :class="sidebarOpen ? 'translate-x-0 sidebar-visible' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-50 flex w-[min(288px,calc(100vw-16px))] flex-col border-r border-slate-800/80 bg-slate-950 text-slate-300 shadow-2xl shadow-slate-950/20 transition-[transform,width] duration-300 ease-in-out print:hidden lg:sticky lg:top-0 lg:h-[100dvh] lg:w-[272px] lg:shrink-0 lg:shadow-none xl:w-[280px] 2xl:w-[288px] 3xl:w-[320px] 4xl:w-[336px]">

    <div
        class="flex h-[68px] shrink-0 items-center justify-between border-b border-slate-800/80 px-4 sm:h-[72px] sm:px-5 lg:h-[76px] 2xl:h-[80px] 2xl:px-6 3xl:h-[84px] 3xl:px-7">
        <a href="{{ route('admin.dashboard') }}" title="Dashboard" class="flex items-center min-w-0 gap-3">
            <div
                class="flex items-center justify-center w-10 h-10 text-white bg-blue-600 shadow-lg shrink-0 rounded-xl 3xl:h-11 3xl:w-11 shadow-blue-950/30">
                <img src="{{ asset('img/ebeam-mark.svg') }}" alt="" aria-hidden="true" class="h-7 w-7">
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
        <button type="button" @click="sidebarCollapsed = !sidebarCollapsed; localStorage.setItem('beamCustom.sidebarCollapsed', sidebarCollapsed ? 'true' : 'false'); document.documentElement.classList.toggle('sidebar-collapsed', sidebarCollapsed)"
            :aria-pressed="sidebarCollapsed.toString()" :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            class="hidden h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs text-slate-500 transition-colors hover:bg-slate-800 hover:text-slate-100 lg:inline-flex">
            <i class="fa-solid" :class="sidebarCollapsed ? 'fa-angles-right' : 'fa-angles-left'"></i>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-5 3xl:px-4 3xl:py-6 [scrollbar-width:thin]">
        <div class="mb-6 space-y-1">
            <p class="mb-2 px-3 text-[9px] font-extrabold uppercase 3xl:text-[10px] tracking-[0.16em] text-slate-600">
                Workspace</p>

            <a href="{{ route('admin.dashboard') }}" title="Dashboard"
                data-compact-label="Home"
                class="{{ $navBase }} {{ request()->routeIs('admin.dashboard') ? $navActive : $navIdle }}">
                <i
                    class="fa-solid fa-table-cells-large w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-500' }}"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('admin.workspace.tasks') }}" title="Action Items" data-compact-label="Tasks" class="{{ $navBase }} {{ request()->routeIs('admin.workspace.tasks') ? $navActive : $navIdle }}"><i class="fa-solid fa-list-check w-5 shrink-0 text-center"></i><span>Action Items</span></a>
            <a href="{{ route('admin.bookings') }}" title="Order Management"
                data-compact-label="Orders"
                class="{{ $navBase }} {{ request()->routeIs('admin.bookings*', 'admin.workspace.show', 'admin.workspace.orders') ? $navActive : $navIdle }}">
                <i class="fa-solid fa-clipboard-list w-5 shrink-0 text-center text-[13px] 3xl:text-sm"></i>
                <span>Order Management</span>
                @if ($pendingOrders > 0)
                    <span class="ml-auto rounded-md bg-blue-500/15 px-1.5 py-0.5 text-[9px] font-bold text-blue-300">{{ $pendingOrders }}</span>
                @endif
            </a>

            @if(in_array($role, ['superadmin', 'manager', 'production']))
            <a href="{{ route('admin.testing.index') }}" title="Product Testing"
                data-compact-label="Testing"
                class="{{ $navBase }} {{ request()->routeIs('admin.testing.*') ? $navActive : $navIdle }}">
                <i
                    class="fa-solid fa-flask-vial w-5 shrink-0 text-center text-[13px] 3xl:text-sm {{ request()->routeIs('admin.testing.*') ? 'text-white' : 'text-slate-500' }}"></i>
                <span>Product Testing</span>
                @if ($pendingTests > 0)
                    <span
                        class="ml-auto rounded-md bg-amber-500/15 px-1.5 py-0.5 text-[9px] font-extrabold text-amber-300">{{ $pendingTests }}</span>
                @endif
            </a>
            @endif

            @if(in_array($role, ['superadmin','manager','production']))
                <a href="{{ route('admin.workspace.production-board') }}" title="Production" data-compact-label="Production" class="{{ $navBase }} {{ request()->routeIs('admin.workspace.production-board','admin.production.*') ? $navActive : $navIdle }}"><i class="fa-solid fa-industry w-5 text-center"></i><span>Production</span></a>
                <a href="{{ route('admin.ebeam-weekly.index') }}" title="Weekly Monitoring" data-compact-label="Weekly" class="{{ $navBase }} {{ request()->routeIs('admin.ebeam-weekly.*') ? $navActive : $navIdle }}"><i class="fa-solid fa-calendar-week w-5 text-center"></i><span>Weekly Monitoring</span></a>
            @endif

        </div>

        <div class="mb-6 space-y-1">
            <p class="mb-2 px-3 text-[9px] font-extrabold uppercase tracking-[0.16em] text-slate-600">Management</p>
            @if(in_array($role,['superadmin','cargo_admin']))
                <a href="{{ route('admin.pallets.index') }}" title="Warehouse" data-compact-label="Warehouse" class="{{ $navBase }} {{ request()->routeIs('admin.pallets.*') ? $navActive : $navIdle }}"><i class="fa-solid fa-warehouse w-5 text-center"></i><span>Warehouse</span></a>
            @endif
            <a href="{{ route('admin.report.index') }}" title="Documents" data-compact-label="Docs" class="{{ $navBase }} {{ request()->routeIs('admin.report.*') ? $navActive : $navIdle }}"><i class="fa-solid fa-file-lines w-5 text-center"></i><span>Documents</span></a>
            <a href="{{ route('admin.customerList.index') }}" title="Customers" data-compact-label="Customers" class="{{ $navBase }} {{ request()->routeIs('admin.customerList.*') ? $navActive : $navIdle }}"><i class="fa-solid fa-building-user w-5 text-center"></i><span>Customers</span></a>
            @if(in_array($role,['superadmin','manager']))
                <a href="{{ route('admin.business.index') }}" title="Order Analytics" data-compact-label="Analytics" class="{{ $navBase }} {{ request()->routeIs('admin.business.*') ? $navActive : $navIdle }}"><i class="fa-solid fa-chart-line w-5 text-center"></i><span>Order Analytics</span></a>
            @endif
            <a href="{{ route('admin.unified.settings') }}" title="Settings & Master Data" data-compact-label="Settings" class="{{ $navBase }} {{ request()->routeIs('admin.unified.settings','admin.porter.*','admin.warehouse-pics.*','admin.production-lines.*','admin.profile.profileList') ? $navActive : $navIdle }}"><i class="fa-solid fa-gear w-5 text-center"></i><span>Settings & Master Data</span></a>
            @if($role === 'superadmin')
                <a href="{{ route('admin.trash.index') }}" title="Trash" data-compact-label="Trash" class="{{ $navBase }} {{ request()->routeIs('admin.trash.*') ? $navActive : $navIdle }}"><i class="fa-solid fa-trash-can w-5 text-center"></i><span>Trash</span></a>
            @endif
        </div>

</nav>

</aside>
