@extends('admin.layout.app')
@section('title', 'Order Management')
@section('content')
    {{-- Data Sources (Keep hidden) --}}
    <div id="bookingDataSource" class="hidden">
        @foreach ($bookings as $b)
            @php $product = $b->products->first(); @endphp
            <div data-code="{{ $b->booking_code }}" data-id="{{ $b->id }}"
                data-name="{{ $product->product_name ?? '-' }}" data-type="{{ $product->product_type ?? '-' }}"
                data-qty="{{ $product->quantity ?? 0 }}" data-unit="{{ $product->unit ?? '' }}"
                data-temp="{{ $product->expect_temp ?? '-' }}" data-dmin="{{ $product->dmin ?? 0 }}"
                data-dmax="{{ $product->dmax ?? 0 }}" data-dimension="{{ $product->dimension_pack ?? '-' }}"
                data-vol-pcs="{{ $product->vol_per_pcs ?? 0 }}" data-vol-total="{{ $product->vol_total ?? 0 }}"
                data-net-pcs="{{ $product->net_weight_pcs ?? 0 }}" data-net-total="{{ $product->total_net_weight ?? 0 }}"
                data-gross-pcs="{{ $product->gross_weight_per_pcs ?? 0 }}"
                data-gross-total="{{ $product->total_gross_weight ?? 0 }}">
            </div>
        @endforeach
    </div>

    <div id="porterDataSource" class="hidden">
        @foreach ($porters as $p)
            <div data-name="{{ $p->name }}"></div>
        @endforeach
    </div>

    <div id="palletInventoryData" class="hidden">
        @foreach ($pallets as $p)
            <div data-line="{{ $p->line }}" data-petak="{{ $p->slot_section }}" data-status="{{ $p->status }}">
            </div>
        @endforeach
    </div>


    @php
        $tabs = [
            'all' => 'All Orders',
            'incoming' => 'Awaiting Goods',
            'progress' => 'In Progress',
            'completed' => 'Completed',
        ];
        $canManage = in_array(auth('admin')->user()->role, ['superadmin', 'production']);
        $canReceive = in_array(auth('admin')->user()->role, ['superadmin', 'cargo_admin']);

        $orderDetails = $bookings->getCollection()->mapWithKeys(function ($booking) {
            $contact =
                $booking->customer?->contacts?->firstWhere('is_primary', true) ??
                $booking->customer?->contacts?->first();

            $products = $booking->products
                ->map(function ($product) {
                    return [
                        'name' => $product->product_name ?? 'Unnamed Product',
                        'type' => $product->product_type ?? null,
                        'quantity' => $product->quantity,
                        'unit' => $product->unit,
                        'temperature' => $product->expect_temp,
                        'dimension' => $product->dimension_pack,
                        'dmin' => $product->dmin,
                        'dmax' => $product->dmax,
                        'volumePcs' => $product->vol_per_pcs,
                        'volumeTotal' => $product->vol_total,
                        'netPcs' => $product->net_weight_pcs,
                        'netTotal' => $product->total_net_weight,
                        'grossPcs' => $product->gross_weight_per_pcs,
                        'grossTotal' => $product->total_gross_weight,
                        'densityNet' => $product->density_nett,
                        'densityGross' => $product->density_gross,
                    ];
                })
                ->values()
                ->all();

            $batches = $booking->batches
                ->map(function ($batch) {
                    return [
                        'id' => $batch->id,
                        'status' => $batch->status,
                        'quantity' => $batch->quantity,
                        'frequency' => $batch->freq,
                        'beamSpeed' => $batch->beam_speed,
                        'scanGear' => $batch->scan_gear,
                        'loadingMode' => $batch->loading_mode,
                    ];
                })
                ->values()
                ->all();

            return [
                $booking->id => [
                    'id' => $booking->id,
                    'code' => $booking->booking_code,
                    'customer' => $booking->customer?->company_name ?: 'Company not specified',
                    'contact' => $contact?->name ?? 'Not registered',
                    'email' => $booking->customer?->email,
                    'status' => $booking->status,
                    'created' => $booking->created_at?->format('d M Y H:i'),
                    'arrival' => $booking->arrival_time
                        ? \Carbon\Carbon::parse($booking->arrival_time)->format('d M Y H:i')
                        : null,
                    'quantity' => $booking->products->sum('quantity'),
                    'productCount' => $booking->products->count(),
                    'products' => $products,
                    'batches' => $batches,
                    'batchCount' => $booking->batches->count(),
                    'finishedCount' => $booking->batches->where('status', 'done')->count(),
                    'received' => $booking->arrival_time !== null || $booking->placementDetails->isNotEmpty(),
                    'handedOver' => \App\Services\WorkflowState::handedOver($booking),
                    'workspace' => route('admin.workspace.show', [
                        'booking' => $booking->id,
                    ]),
                ],
            ];
        });

    @endphp


    <div class="mx-auto max-w-[1680px] space-y-5 pb-10" x-data="{
        selectedId: null,
        activeTab: 'overview',
        details: {{ \Illuminate\Support\Js::from($orderDetails) }},

        get selected() {
            return this.selectedId ?
                this.details[this.selectedId] :
                null;
        },

        select(id) {
            this.selectedId = id;
            this.activeTab = 'overview';
        },

        number(value, decimals = 2) {
            if (value === null || value === undefined || value === '') {
                return '—';
            }

            const number = Number(value);

            if (!Number.isFinite(number)) return '—';

            return new Intl.NumberFormat('en-US', {
                maximumFractionDigits: decimals
            }).format(number);
        },

        display(value, suffix = '') {
            if (value === null || value === undefined || value === '') {
                return '—';
            }

            return String(value) + suffix;
        }
    }">

        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[.15em] text-slate-400">Operations / Workspace</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Order Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage customer orders and operational progress in one workspace.</p>
            </div>
            @if (auth('admin')->user()->role === 'superadmin')
                <a href="{{ route('admin.bookings.create') }}"
                    class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-white bg-blue-600 shadow-sm rounded-xl hover:bg-blue-700">
                    <i class="fa-solid fa-plus"></i> New Order
                </a>
            @endif
        </header>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Order status summary">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.bookings', ['view' => $key, 'search' => $search ?: null]) }}"
                    class="rounded-2xl border p-4 transition hover:border-blue-300 {{ $view === $key ? 'border-blue-300 bg-blue-50/60' : 'border-slate-200 bg-white' }}"
                    @if ($view === $key) aria-current="page" @endif>
                    <p class="text-xs font-medium text-slate-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900">{{ $counts[$key] }}</p>
                </a>
            @endforeach
        </section>

        <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <nav class="flex gap-1 px-4 pt-3 overflow-x-auto border-b border-slate-100" aria-label="Order filters">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.bookings', ['view' => $key, 'search' => $search ?: null]) }}"
                        class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition {{ $view === $key ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-900' }}"
                        @if ($view === $key) aria-current="page" @endif>{{ $label }} <span
                            class="ml-1 text-xs opacity-60">{{ $counts[$key] }}</span></a>
                @endforeach
            </nav>
            <div class="p-4 border-b border-slate-100">
                <form method="GET" action="{{ route('admin.bookings') }}" class="flex flex-wrap gap-2">
                    <input type="hidden" name="view" value="{{ $view }}">
                    <label for="order-search" class="sr-only">Search orders</label>
                    <div class="relative min-w-[200px] flex-1">
                        <i
                            class="absolute text-xs -translate-y-1/2 fa-solid fa-magnifying-glass left-4 top-1/2 text-slate-400"></i>
                        <input id="order-search" name="search" type="search" value="{{ $search }}"
                            placeholder="Search orders, customers or products"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Search</button>
                    @if ($search !== '')
                        <a href="{{ route('admin.bookings', ['view' => $view]) }}"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600">Clear</a>
                    @endif
                </form>
            </div>

            <div class="grid min-h-[540px] grid-cols-1 xl:grid-cols-[minmax(0,1.5fr)_minmax(340px,1fr)]">
                <div class="min-w-0 border-r-0 border-slate-100 xl:border-r">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                        <h2 class="text-sm font-semibold text-slate-800">{{ $tabs[$view] }}</h2>
                        <span class="text-xs text-slate-400">{{ $bookings->total() }} results</span>
                    </div>
                    <div class="max-h-[780px] space-y-0 overflow-y-auto">
                        @forelse($bookings as $booking)
                            @php
                                $contact =
                                    $booking->customer?->contacts->firstWhere('is_primary', true)?->name ??
                                    $booking->customer?->contacts->first()?->name;
                                $customer = $booking->customer?->company_name ?: ($contact ?: 'Customer');
                            @endphp
                            <div class="transition border-b border-slate-100 hover:bg-slate-50/80"
                                :class="selectedId === {{ $booking->id }} ? 'bg-blue-50/60' : ''">
                                <button type="button" @click="select({{ $booking->id }})"
                                    class="w-full px-5 py-4 text-left focus-visible:outline-blue-600"
                                    aria-label="Preview order {{ $booking->booking_code }}">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0 space-y-1">
                                            <p class="text-sm font-bold text-slate-900">{{ $booking->booking_code }}</p>
                                            <p class="text-sm font-medium truncate text-slate-700">{{ $customer }}</p>
                                            <p class="text-xs truncate text-slate-500">Contact:
                                                {{ $contact ?: 'Not registered' }}</p>
                                            <p class="text-xs text-slate-400">
                                                {{ $booking->products->first()?->product_name ?? 'No product' }} ·
                                                {{ $booking->created_at->format('d M Y') }}</p>
                                        </div>
                                        <div class="flex flex-col items-end gap-1.5">

                                            <x-status-badge :status="$booking->status" :received="$booking->arrival_time !== null ||
                                                $booking->placementDetails->isNotEmpty()" />

                                            @if (\App\Services\WorkflowState::handedOver($booking))
                                                <span
                                                    class="inline-flex items-center gap-1.5 text-[11px] font-medium text-emerald-700"><i
                                                        class="fa-solid fa-circle-check"></i> Goods Handed Over</span>
                                            @elseif ($booking->arrival_time !== null || $booking->placementDetails->isNotEmpty())
                                                <span
                                                    class="inline-flex items-center gap-1.5
                   text-[11px] font-medium text-emerald-700">

                                                    <i class="fa-solid fa-check-circle"></i>
                                                    Goods Received
                                                </span>
                                            @elseif ($booking->status === 'pending')
                                                <span class="text-[11px] font-medium text-amber-600">
                                                    Awaiting Goods
                                                </span>
                                            @endif

                                        </div>

                                    </div>
                                </button>
                                <div class="flex flex-wrap items-center gap-2 px-5 pb-4">
                                    <a href="{{ route('admin.workspace.show', ['booking' => $booking->id]) }}"
                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-white">Open
                                        Workflow</a>

                                    @php
                                        $hasReceivingRecord =
                                            $booking->arrival_time !== null || $booking->placementDetails->isNotEmpty();

                                        $isReceivingAvailable = \App\Services\WorkflowState::canReceive($booking);
                                    @endphp

                                    @if ($canReceive)
                                        @if ($isReceivingAvailable)
                                            <a href="{{ route('admin.smart.receive', $booking->id) }}"
                                                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">

                                                <i class="fa-solid fa-box-open"></i>
                                                Receive Goods
                                            </a>
                                        @endif
                                    @endif

                                    @if ($canManage && \App\Services\WorkflowState::canPlan($booking))
                                        <a href="{{ route('admin.smart.batch-plan', $booking) }}"
                                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700"><i
                                                class="fa-solid fa-gears"></i> Process</a>
                                    @endif
                                    @if (
                                        $booking->status === 'completed' &&
                                            !\App\Services\WorkflowState::handedOver($booking) &&
                                            in_array(auth('admin')->user()->role, ['superadmin', 'manager', 'cargo_admin']))
                                        <a href="{{ route('admin.dispatch.show', $booking) }}"
                                            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-800">Goods
                                            Handover</a>
                                    @endif
                                    {{-- @if ($booking->status === 'completed')
                                        <a href="{{ route('admin.orders.report.pdf', $booking->id) }}" target="_blank"
                                            rel="noopener"
                                            class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800">Download
                                            Process Report (PDF)</a>
                                    @endif --}}

                                    @if ($booking->status === 'completed')
                                        <button type="button" onclick="printReport({{ $booking->id }})"
                                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100">

                                            <i class="fa-solid fa-print"></i>
                                            Print / Save PDF
                                        </button>
                                    @endif

                                    @if ($booking->arrival_time && $canManage)
                                        <a target="_blank" rel="noopener"
                                            href="{{ route('admin.bookings.invoice', $booking->id) }}"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Invoice</a>
                                    @endif
                                    @if ($canManage && \App\Services\WorkflowState::canEdit($booking))
                                        <a href="{{ route('admin.bookings.edit', $booking->id) }}"
                                            class="rounded-lg px-2 py-1.5 text-xs text-slate-500 hover:text-blue-600">Edit</a>
                                    @endif
                                    @if (auth('admin')->user()->role === 'superadmin' && \App\Services\WorkflowState::canArchive($booking))
                                        <button type="button"
                                            onclick="confirmDelete({{ $booking->id }}, @js($booking->booking_code))"
                                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                            <i class="mr-1 fa-solid fa-trash-can"></i> Move to Trash
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-16 text-center"><i
                                    class="text-2xl fa-solid fa-box-open text-slate-300"></i>
                                <h3 class="mt-3 font-semibold text-slate-800">No orders found</h3>
                                <p class="mt-1 text-sm text-slate-500">Try a different search or filter.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="px-5 py-4 border-t border-slate-100">{{ $bookings->links() }}</div>
                </div>


                <aside class="self-start min-w-0 bg-white xl:sticky xl:top-4" aria-label="Order Details">

                    {{-- Empty state --}}
                    <div x-cloak x-show="!selected"
                        class="flex min-h-[480px] flex-col items-center justify-center px-6 text-center">

                        <div class="flex items-center justify-center h-14 w-14 rounded-2xl bg-slate-100">
                            <i class="text-xl fa-solid fa-file-lines text-slate-400"></i>
                        </div>

                        <h3 class="mt-5 text-base font-semibold text-slate-900">
                            Select an order
                        </h3>

                        <p class="max-w-xs mt-2 text-sm leading-6 text-slate-500">
                            Choose an order to view its specifications, production progress,
                            and operational details.
                        </p>
                    </div>

                    {{-- Selected order --}}
                    <div x-cloak x-show="selected">

                        {{-- Header --}}
                        <div class="px-5 py-5 border-b border-slate-100">

                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">
                                        Order Details
                                    </p>

                                    <h2 class="mt-1 text-xl font-bold tracking-tight break-all text-slate-900"
                                        x-text="selected?.code">
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500" x-text="selected?.customer">
                                    </p>
                                </div>

                                <button type="button" @click="selectedId = null"
                                    class="flex items-center justify-center border rounded-lg h-9 w-9 shrink-0 border-slate-200 text-slate-500 hover:bg-slate-50"
                                    aria-label="Close order details">

                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 mt-4">
                                <span
                                    class="rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-semibold capitalize text-blue-700"
                                    x-text="selected?.status?.replaceAll('_', ' ')">
                                </span>

                                <span x-show="selected?.handedOver"
                                    class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    Goods Handed Over
                                </span>
                            </div>

                            {{-- Summary --}}
                            <div class="grid grid-cols-2 gap-3 mt-5">
                                <div class="p-3 border rounded-xl border-slate-200">
                                    <p class="text-xs text-slate-500">Total Quantity</p>
                                    <p class="mt-1 text-xl font-bold tabular-nums text-slate-900"
                                        x-text="number(selected?.quantity, 0)">
                                    </p>
                                </div>

                                <div class="p-3 border rounded-xl border-slate-200">
                                    <p class="text-xs text-slate-500">Production Batches</p>

                                    <p class="mt-1 text-xl font-bold tabular-nums text-slate-900">
                                        <span x-text="selected?.finishedCount"></span>
                                        <span class="text-slate-300">/</span>
                                        <span x-text="selected?.batchCount"></span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Tabs --}}
                        <div class="px-4 border-b border-slate-100">
                            <nav class="flex gap-1 overflow-x-auto" aria-label="Order detail sections">

                                <template
                                    x-for="tab in [
                    {id: 'overview', label: 'Overview'},
                    {id: 'products', label: 'Products'},
                    {id: 'production', label: 'Production'}
                ]"
                                    :key="tab.id">

                                    <button type="button" @click="activeTab = tab.id"
                                        :class="activeTab === tab.id ?
                                            'border-blue-600 text-blue-700' :
                                            'border-transparent text-slate-500 hover:text-slate-900'"
                                        class="px-3 py-3 text-xs font-semibold transition border-b-2 whitespace-nowrap"
                                        x-text="tab.label">
                                    </button>
                                </template>

                            </nav>
                        </div>

                        <div class="max-h-[680px] space-y-5 overflow-y-auto p-5">

                            {{-- OVERVIEW --}}
                            <div x-show="activeTab === 'overview'" class="space-y-5">

                                <section class="space-y-3">
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Customer Information
                                    </h3>

                                    <div class="p-4 border rounded-xl border-slate-200">
                                        <dl class="space-y-4 text-sm">
                                            <div>
                                                <dt class="text-xs text-slate-400">Company</dt>
                                                <dd class="mt-1 font-semibold text-slate-800" x-text="selected?.customer">
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-xs text-slate-400">Contact Person</dt>
                                                <dd class="mt-1 text-slate-700" x-text="selected?.contact">
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-xs text-slate-400">Email</dt>
                                                <dd class="mt-1 break-all text-slate-700"
                                                    x-text="display(selected?.email)">
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </section>

                                <section class="space-y-3">
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Order Information
                                    </h3>

                                    <div class="p-4 border rounded-xl border-slate-200">
                                        <dl class="space-y-4 text-sm">
                                            <div class="flex justify-between gap-3">
                                                <dt class="text-slate-500">Booking Date</dt>
                                                <dd class="font-medium text-right text-slate-800"
                                                    x-text="display(selected?.created)">
                                                </dd>
                                            </div>

                                            <div class="flex justify-between gap-3">
                                                <dt class="text-slate-500">Check-in</dt>
                                                <dd class="font-medium text-right text-slate-800"
                                                    x-text="display(selected?.arrival, ' ')">
                                                </dd>
                                            </div>

                                            <div class="flex justify-between gap-3">
                                                <dt class="text-slate-500">Products</dt>
                                                <dd class="font-medium text-slate-800" x-text="selected?.productCount">
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </section>

                                <section class="space-y-3">
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Workflow Progress
                                    </h3>

                                    <div class="p-4 border rounded-xl border-slate-200">
                                        <div class="space-y-5">

                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                                    :class="selected?.received ?
                                                        'bg-emerald-50 text-emerald-600' :
                                                        'bg-slate-100 text-slate-400'">

                                                    <i class="text-xs fa-solid fa-box-open"></i>
                                                </div>

                                                <div>
                                                    <p class="text-sm font-semibold text-slate-800">
                                                        Goods Receiving
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500"
                                                        x-text="selected?.received
                                            ? 'Goods received'
                                            : 'Awaiting goods'">
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                                    :class="selected?.status === 'completed' ?
                                                        'bg-emerald-50 text-emerald-600' :
                                                        'bg-blue-50 text-blue-600'">

                                                    <i class="text-xs fa-solid fa-gears"></i>
                                                </div>

                                                <div>
                                                    <p class="text-sm font-semibold text-slate-800">
                                                        Production
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500">
                                                        <span x-text="selected?.finishedCount"></span>
                                                        of
                                                        <span x-text="selected?.batchCount"></span>
                                                        batches finished
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
                                                    :class="selected?.handedOver ?
                                                        'bg-emerald-50 text-emerald-600' :
                                                        'bg-slate-100 text-slate-400'">

                                                    <i class="text-xs fa-solid fa-truck-ramp-box"></i>
                                                </div>

                                                <div>
                                                    <p class="text-sm font-semibold text-slate-800">
                                                        Goods Handover
                                                    </p>

                                                    <p class="mt-0.5 text-xs text-slate-500"
                                                        x-text="selected?.handedOver
                                            ? 'Goods handed over'
                                            : 'Not yet handed over'">
                                                    </p>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </section>
                            </div>

                            {{-- PRODUCTS --}}
                            <div x-show="activeTab === 'products'" class="space-y-4">

                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Product Specifications
                                    </h3>

                                    <span class="text-xs text-slate-500" x-text="selected?.productCount + ' products'">
                                    </span>
                                </div>

                                <template x-for="(product, index) in (selected?.products || [])" :key="index">
                                    <section class="overflow-hidden border rounded-xl border-slate-200">

                                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                                                x-text="'Product ' + (index + 1)">
                                            </p>

                                            <h4 class="mt-1 text-sm font-bold break-words text-slate-900"
                                                x-text="product.name">
                                            </h4>

                                            <p class="mt-1 text-xs text-slate-500" x-text="display(product.type)">
                                            </p>
                                        </div>

                                        <div class="p-4 space-y-4">

                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <p class="text-xs text-slate-400">Quantity</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-900">
                                                        <span x-text="number(product.quantity, 0)"></span>
                                                        <span x-text="product.unit || ''"></span>
                                                    </p>
                                                </div>

                                                <div>
                                                    <p class="text-xs text-slate-400">Temperature</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-900"
                                                        x-text="display(product.temperature)">
                                                    </p>
                                                </div>

                                                <div>
                                                    <p class="text-xs text-slate-400">Minimum Dose</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-900"
                                                        x-text="product.dmin == null ? '—' : number(product.dmin, 4) + ' kGy'">
                                                    </p>
                                                </div>

                                                <div>
                                                    <p class="text-xs text-slate-400">Maximum Dose</p>
                                                    <p class="mt-1 text-sm font-semibold text-slate-900"
                                                        x-text="product.dmax == null ? '—' : number(product.dmax, 4) + ' kGy'">
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="pt-3 border-t border-slate-100">
                                                <p class="text-xs text-slate-400">Dimensions</p>
                                                <p class="mt-1 text-sm font-medium text-slate-800"
                                                    x-text="product.dimension ? product.dimension + ' cm' : '—'">
                                                </p>
                                            </div>

                                            <div class="pt-3 border-t border-slate-100">
                                                <h5 class="mb-3 text-xs font-semibold text-slate-700">
                                                    Volume
                                                </h5>

                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <p class="text-xs text-slate-400">Per Piece</p>
                                                        <p class="mt-1 text-sm font-medium"
                                                            x-text="product.volumePcs == null ? '—' : number(product.volumePcs, 4) + ' cm³'">
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <p class="text-xs text-slate-400">Total</p>
                                                        <p class="mt-1 text-sm font-medium"
                                                            x-text="product.volumeTotal == null ? '—' : number(product.volumeTotal, 4) + ' cm³'">
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pt-3 border-t border-slate-100">
                                                <h5 class="mb-3 text-xs font-semibold text-slate-700">
                                                    Weight
                                                </h5>

                                                <div class="grid grid-cols-2 gap-4">
                                                    <div class="space-y-3">
                                                        <p class="text-xs font-semibold text-slate-600">
                                                            Net Weight
                                                        </p>

                                                        <div>
                                                            <p class="text-xs text-slate-400">Per Piece</p>
                                                            <p class="text-sm font-medium"
                                                                x-text="product.netPcs == null ? '—' : number(product.netPcs, 4) + ' kg'">
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="text-xs text-slate-400">Total</p>
                                                            <p class="text-sm font-medium"
                                                                x-text="product.netTotal == null ? '—' : number(product.netTotal, 4) + ' kg'">
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="space-y-3">
                                                        <p class="text-xs font-semibold text-slate-600">
                                                            Gross Weight
                                                        </p>

                                                        <div>
                                                            <p class="text-xs text-slate-400">Per Piece</p>
                                                            <p class="text-sm font-medium"
                                                                x-text="product.grossPcs == null ? '—' : number(product.grossPcs, 4) + ' kg'">
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="text-xs text-slate-400">Total</p>
                                                            <p class="text-sm font-medium"
                                                                x-text="product.grossTotal == null ? '—' : number(product.grossTotal, 4) + ' kg'">
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pt-3 border-t border-slate-100">
                                                <h5 class="mb-3 text-xs font-semibold text-slate-700">
                                                    Density
                                                </h5>

                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <p class="text-xs text-slate-400">Net</p>
                                                        <p class="mt-1 text-sm font-medium"
                                                            x-text="number(product.densityNet, 6)">
                                                        </p>
                                                    </div>

                                                    <div>
                                                        <p class="text-xs text-slate-400">Gross</p>
                                                        <p class="mt-1 text-sm font-medium"
                                                            x-text="number(product.densityGross, 6)">
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </section>
                                </template>

                                <p x-show="!selected?.products?.length"
                                    class="p-6 text-sm text-center border border-dashed rounded-xl border-slate-200 text-slate-500">
                                    No product specifications available.
                                </p>
                            </div>

                            {{-- PRODUCTION --}}
                            <div x-show="activeTab === 'production'" class="space-y-4">

                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-slate-900">
                                        Production Batches
                                    </h3>

                                    <span class="text-xs text-slate-500" x-text="selected?.batchCount + ' batches'">
                                    </span>
                                </div>

                                <template x-for="batch in (selected?.batches || [])" :key="batch.id">
                                    <section class="p-4 border rounded-xl border-slate-200">

                                        <div class="flex items-center justify-between gap-3">
                                            <h4 class="text-sm font-bold text-slate-900" x-text="'Batch #' + batch.id">
                                            </h4>

                                            <span
                                                class="px-2 py-1 text-xs font-semibold text-blue-700 capitalize rounded-lg bg-blue-50"
                                                x-text="display(batch.status).replaceAll('_', ' ')">
                                            </span>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 mt-4">
                                            <div>
                                                <p class="text-xs text-slate-400">Quantity</p>
                                                <p class="mt-1 text-sm font-semibold" x-text="number(batch.quantity, 0)">
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs text-slate-400">Frequency</p>
                                                <p class="mt-1 text-sm font-semibold"
                                                    x-text="batch.frequency == null ? '—' : number(batch.frequency, 4) + ' Hz'">
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs text-slate-400">Beam Speed</p>
                                                <p class="mt-1 text-sm font-semibold"
                                                    x-text="batch.beamSpeed == null ? '—' : number(batch.beamSpeed, 4) + ' m/s'">
                                                </p>
                                            </div>

                                            <div>
                                                <p class="text-xs text-slate-400">Scan Gear</p>
                                                <p class="mt-1 text-sm font-semibold" x-text="number(batch.scanGear, 4)">
                                                </p>
                                            </div>

                                            <div class="col-span-2">
                                                <p class="text-xs text-slate-400">Loading Mode</p>
                                                <p class="mt-1 text-sm font-semibold" x-text="display(batch.loadingMode)">
                                                </p>
                                            </div>
                                        </div>
                                    </section>
                                </template>

                                <p x-show="!selected?.batches?.length"
                                    class="p-6 text-sm text-center border border-dashed rounded-xl border-slate-200 text-slate-500">
                                    No production batches have been planned.
                                </p>
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div class="p-4 bg-white border-t border-slate-100">
                            <a :href="selected?.workspace"
                                class="flex items-center justify-center w-full gap-2 px-4 py-3 text-sm font-semibold text-white transition bg-blue-600 rounded-xl hover:bg-blue-700">

                                Open Order Workspace
                                <i class="text-xs fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </div>

                    </div>
                </aside>

            </div>
        </section>
    </div>

    {{-- DELETE MODAL --}}
    <div data-ui-modal role="dialog" aria-modal="true" aria-label="Confirm Deletion" tabindex="-1" id="deleteModal"
        class="fixed inset-0 z-[9999] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div
            class="relative w-full max-w-sm p-8 transition-all scale-95 opacity-0 bg-white shadow-2xl rounded-[2.5rem] modal-card">
            <div class="flex flex-col items-center text-center">
                <div class="flex items-center justify-center w-16 h-16 mb-6 rounded-2xl bg-rose-50 text-rose-500">
                    <i class="text-2xl fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="mb-2 text-xl font-black text-slate-800">Move Order to Trash?</h3>
                <p class="mb-8 text-xs font-medium leading-relaxed text-slate-500">
                    Booking <span id="deleteBookingCode" class="font-bold text-slate-800"></span> will be moved to Trash.
                    Its history and documents will remain available for restoration. Complete goods handover before
                    archiving an operational order.
                </p>
                <div class="flex flex-col w-full gap-3">
                    <form id="deleteForm" data-delete-confirmed="true" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="w-full py-4 text-xs font-black tracking-widest text-white uppercase shadow-lg bg-rose-600 rounded-2xl active:scale-95 shadow-rose-100">Yes,
                            Move to Trash</button>
                    </form>
                    <button type="button" data-modal-dismiss onclick="closeDeleteModal()"
                        class="w-full py-4 text-xs font-black tracking-widest uppercase text-slate-400 bg-slate-50 rounded-2xl active:scale-95">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    @foreach ($bookings as $booking)
        @include('admin.bookings.partials.preRad')
        {{-- Legacy check-in modal replaced by Assisted Receiving Workspace. --}}
    @endforeach

@endsection

@push('scripts')
    <script>
        function confirmDelete(id, code) {
            const modal = document.getElementById('deleteModal');
            const card = modal.querySelector('.modal-card');
            document.getElementById('deleteForm').action = @json(route('admin.bookings.destroy', ['id' => '__ID__'])).replace('__ID__',
                encodeURIComponent(id));
            document.getElementById('deleteBookingCode').innerText = code;
            modal.classList.remove('hidden');
            setTimeout(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            const card = modal.querySelector('.modal-card');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this || e.target.classList.contains('absolute')) closeDeleteModal();
        });

        function printReport(bookingId) {
            const urlTemplate = @json(route('admin.orders.report.print', ['booking' => '__ID__']));

            const url = urlTemplate.replace(
                '__ID__',
                encodeURIComponent(bookingId)
            );

            window.open(url, '_blank');
        }
    </script>
    <script src="{{ asset('js/admin/preRad.js') }}"></script>
    {{-- Legacy check-in script intentionally disabled. --}}
@endpush
