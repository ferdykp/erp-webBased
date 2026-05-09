@extends('admin.layout.app')

@section('title', 'Dashboard')

@section('content')

    {{-- Hidden Data Sources --}}
    <div id="bookingDataSource" class="hidden">
        @foreach (\App\Models\Booking::where('status', 'pending')->with('products')->get() as $b)
            @php $product = $b->products->first(); @endphp
            <div data-code="{{ $b->booking_code }}" data-name="{{ $product->product_name ?? '-' }}"
                data-type="{{ $product->product_type ?? '-' }}" data-qty="{{ $product->quantity ?? 0 }}"
                data-unit="{{ $product->unit ?? '' }}" data-dose="{{ $product->target_dose ?? '-' }}">
            </div>
        @endforeach
    </div>

    <div id="porterDataSource" class="hidden">
        @foreach ($porters as $p)
            <div data-name="{{ $p->name }}"></div>
        @endforeach
    </div>

    {{-- ═══ PAGE HEADER ═══ --}}
    <div class="flex flex-col gap-4 mb-8 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-400">Monitoring warehouse operations and bookings.</p>
        </div>
        <div
            class="inline-flex items-center self-start gap-2 px-4 py-2 bg-white border border-gray-100 shadow-sm rounded-xl sm:self-auto">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <span class="text-sm font-medium text-gray-600">{{ now()->format('d F Y') }}</span>
        </div>
    </div>

    {{-- ═══ STATS GRID ═══ --}}
    @php
        $stats = [
            [
                'label' => 'Total Bookings',
                'count' => \App\Models\Booking::count(),
                'color' => 'blue',
                'bg' => 'bg-blue-50',
                'text' => 'text-blue-600',
                'icon' =>
                    'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
            ],
            [
                'label' => 'Pending',
                'count' => \App\Models\Booking::where('status', 'pending')->count(),
                'color' => 'amber',
                'bg' => 'bg-amber-50',
                'text' => 'text-amber-600',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'Approved',
                'count' => \App\Models\Booking::where('status', 'approved')->count(),
                'color' => 'sky',
                'bg' => 'bg-sky-50',
                'text' => 'text-sky-600',
                'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            [
                'label' => 'On Process',
                'count' => \App\Models\Booking::where('status', 'processing')->count(),
                'color' => 'violet',
                'bg' => 'bg-violet-50',
                'text' => 'text-violet-600',
                'icon' =>
                    'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99',
            ],
            [
                'label' => 'Completed',
                'count' => \App\Models\Booking::where('status', 'completed')->count(),
                'color' => 'emerald',
                'bg' => 'bg-emerald-50',
                'text' => 'text-emerald-600',
                'icon' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
        ];
    @endphp

    <div class="grid grid-cols-2 gap-4 mb-8 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($stats as $stat)
            <div
                class="p-5 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-9 h-9 {{ $stat['bg'] }} rounded-xl">
                        <svg class="w-4 h-4 {{ $stat['text'] }}" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $stat['icon'] }}" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl">{{ $stat['count'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ═══ WORKFLOW GUIDE ═══ --}}
    <div class="mb-8">
        <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">Operational Workflow</h2>
                <p class="text-xs text-gray-400 mt-0.5">Follow these steps to process orders end-to-end.</p>
            </div>
            <span
                class="hidden sm:inline-flex items-center px-3 py-1 text-[11px] font-semibold text-gray-400 bg-gray-100 rounded-full uppercase tracking-wider">
                SOP
            </span>
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $steps = [
                    [
                        'num' => '1',
                        'title' => 'Onboarding',
                        'desc' =>
                            'Register new clients in <b>Add Company</b>. Ensure all profile forms are fully completed.',
                        'accent' => 'blue',
                        'path' =>
                            'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z',
                    ],
                    [
                        'num' => '2',
                        'title' => 'Cargo Management',
                        'desc' =>
                            'Go to <b>Add Order</b>, select the registered company, and fill in cargo specification forms.',
                        'accent' => 'amber',
                        'path' =>
                            'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z',
                    ],
                    [
                        'num' => '3',
                        'title' => 'Irradiation & QC',
                        'desc' =>
                            'Set <b>Process Parameters</b>, monitor the <b>Queue</b>, and complete <b>QC Details</b>.',
                        'accent' => 'violet',
                        'path' =>
                            'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5',
                    ],
                    [
                        'num' => '4',
                        'title' => 'Billing & Release',
                        'desc' =>
                            'Check the <b>Finish</b> menu. Update payment to enable the <b>Certificate Download</b>.',
                        'accent' => 'emerald',
                        'path' =>
                            'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                    ],
                ];
                $accentMap = [
                    'blue' => [
                        'bg' => 'bg-blue-50',
                        'text' => 'text-blue-600',
                        'hover' => 'hover:border-blue-200',
                        'num' => 'bg-blue-600 text-white',
                    ],
                    'amber' => [
                        'bg' => 'bg-amber-50',
                        'text' => 'text-amber-600',
                        'hover' => 'hover:border-amber-200',
                        'num' => 'bg-amber-500 text-white',
                    ],
                    'violet' => [
                        'bg' => 'bg-violet-50',
                        'text' => 'text-violet-600',
                        'hover' => 'hover:border-violet-200',
                        'num' => 'bg-violet-600 text-white',
                    ],
                    'emerald' => [
                        'bg' => 'bg-emerald-50',
                        'text' => 'text-emerald-600',
                        'hover' => 'hover:border-emerald-200',
                        'num' => 'bg-emerald-600 text-white',
                    ],
                ];
            @endphp

            @foreach ($steps as $step)
                @php $a = $accentMap[$step['accent']]; @endphp
                <div
                    class="relative p-5 bg-white border border-gray-100 rounded-2xl shadow-sm {{ $a['hover'] }} transition-all duration-200 overflow-hidden group">
                    <div
                        class="absolute -right-2 -top-1 text-6xl font-bold text-gray-50 group-hover:text-{{ $step['accent'] }}-50 transition-colors select-none">
                        {{ $step['num'] }}
                    </div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center justify-center w-9 h-9 {{ $a['bg'] }} rounded-xl">
                                <svg class="w-4 h-4 {{ $a['text'] }}" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $step['path'] }}" />
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold {{ $a['num'] }}">
                                {{ $step['num'] }}
                            </span>
                        </div>
                        <h3 class="mb-1 text-sm font-semibold text-gray-800">{{ $step['title'] }}</h3>
                        <p class="text-xs leading-relaxed text-gray-400">{!! $step['desc'] !!}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ═══ SCANNER + RECENT ARRIVALS ═══ --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- QR Scanner --}}
        <div class="lg:col-span-1">
            <div class="flex flex-col h-full gap-5 p-6 text-white bg-gray-900 rounded-2xl">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-9 h-9 bg-blue-500/20 rounded-xl">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold">QR Check-in</h3>
                        <p class="text-xs text-gray-500">Scan or enter booking code</p>
                    </div>
                </div>

                <div id="reader" class="overflow-hidden border border-gray-700 rounded-xl bg-gray-800/60"></div>

                <div class="space-y-3">
                    <div class="relative">
                        <input type="text" id="manual_booking_input" placeholder="Enter booking code..."
                            class="w-full h-10 pl-10 pr-4 text-sm font-medium text-white placeholder-gray-500 bg-gray-800 border border-gray-700 outline-none rounded-xl focus:ring-2 focus:ring-blue-500">
                        <svg class="absolute w-4 h-4 text-gray-500 -translate-y-1/2 pointer-events-none left-3 top-1/2"
                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 0z" />
                        </svg>
                    </div>

                    @php $isFull = \App\Models\Pallet::where('status', 'empty')->count() === 0; @endphp
                    <button type="button" onclick="{{ $isFull ? 'alertFull()' : 'handleManualInput()' }}"
                        class="w-full h-10 text-sm font-medium rounded-xl transition-all active:scale-[0.98] {{ $isFull ? 'bg-gray-700 text-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-500 text-white' }}">
                        {{ $isFull ? 'Warehouse Full' : 'Process Check-in' }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Recent Arrivals --}}
        <div class="lg:col-span-2">
            <div class="flex flex-col h-full bg-white border border-gray-100 shadow-sm rounded-2xl">
                <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">Recent Arrivals</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Latest check-ins today</p>
                    </div>
                    <span
                        class="inline-flex items-center gap-1.5 text-[11px] font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>
                        Live
                    </span>
                </div>

                {{-- Desktop Table --}}
                <div class="flex-1 hidden overflow-x-auto sm:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-50 bg-gray-50/60">
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Code</th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Customer</th>
                                <th
                                    class="px-6 py-3 text-left text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Product</th>
                                <th
                                    class="px-6 py-3 text-right text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                    Time</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse (\App\Models\Booking::whereNotNull('arrival_time')->latest('arrival_time')->take(6)->get() as $recent)
                                <tr class="transition-colors hover:bg-gray-50/60">
                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-semibold text-blue-700 bg-blue-50 rounded-lg">
                                            #{{ $recent->booking_code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-sm font-medium text-gray-700">
                                        {{ $recent->customer->contacts->first()->name ?? 'Guest' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-sm text-gray-500">
                                        {{ $recent->products->first()?->product_name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-3.5 text-sm font-semibold text-gray-700 text-right">
                                        {{ $recent->arrival_time->format('H:i') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-sm text-center text-gray-400">No recent
                                        arrivals.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card List --}}
                <div class="flex-1 divide-y divide-gray-100 sm:hidden">
                    @forelse (\App\Models\Booking::whereNotNull('arrival_time')->latest('arrival_time')->take(6)->get() as $recent)
                        <div class="flex items-center justify-between px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-[11px] font-mono font-semibold text-blue-700 bg-blue-50 rounded-lg">
                                    #{{ $recent->booking_code }}
                                </span>
                                <div>
                                    <p class="text-xs font-semibold text-gray-700">
                                        {{ $recent->customer->contacts->first()->name ?? 'Guest' }}</p>
                                    <p class="text-[11px] text-gray-400">
                                        {{ $recent->products->first()?->product_name ?? '-' }}</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-semibold text-gray-500">{{ $recent->arrival_time->format('H:i') }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-10 text-sm text-center text-gray-400">No recent arrivals.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ WAREHOUSE / CHECK-IN MODAL ═══ --}}
    <div id="warehouseModal"
        class="fixed inset-0 z-50 items-center justify-center hidden p-4 bg-gray-900/50 backdrop-blur-sm">
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-xl flex flex-col max-h-[90vh] overflow-hidden">

            {{-- Modal Header --}}
            <div class="px-6 pt-6 pb-5 border-b border-gray-100">
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Check-in Process</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Code: <span id="display_booking_code" class="font-semibold text-blue-600"></span>
                        </p>
                    </div>
                    <button onclick="closeWarehouseModal()"
                        class="flex items-center justify-center flex-shrink-0 w-8 h-8 text-gray-400 transition-colors rounded-lg hover:bg-red-50 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Step Tracker --}}
                <div class="flex items-center max-w-sm">
                    @foreach ([['1', 'Verify'], ['2', 'Batching'], ['3', 'Placement']] as [$n, $lbl])
                        @if (!$loop->first)
                            <div class="flex-1 h-px mx-2 bg-gray-100"></div>
                        @endif
                        <div class="flex flex-col items-center gap-1 step-item" data-step="{{ $n }}">
                            <div
                                class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold step-circle {{ $loop->first ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                {{ $n }}
                            </div>
                            <span
                                class="text-[10px] font-medium text-gray-400 uppercase tracking-wider">{{ $lbl }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <form action="{{ route('admin.bookings.checkin') }}" method="POST" id="checkInForm"
                class="flex flex-col flex-1 overflow-hidden">
                @csrf
                <input type="hidden" name="booking_code" id="modal_booking_code">

                <div class="flex-1 px-6 py-6 overflow-y-auto">

                    {{-- STEP 1: VERIFICATION --}}
                    <div class="step-content" id="step1">
                        <div class="p-5 mb-5 border border-blue-100 bg-blue-50/50 rounded-xl">
                            <p class="text-[11px] font-semibold text-blue-500 uppercase tracking-wider mb-4">Booking
                                Details</p>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                        Product</p>
                                    <p id="check_product_name" class="text-sm font-semibold text-gray-800">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">
                                        Category</p>
                                    <p id="check_product_type" class="text-sm font-medium text-gray-600">-</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Target
                                        Dose</p>
                                    <p class="text-sm font-semibold text-emerald-600"><span id="check_dose">-</span> kGy
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1">Qty
                                        Booked</p>
                                    <p class="text-sm font-semibold text-gray-800"><span id="check_qty">0</span> <span
                                            id="check_unit"></span></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <label
                                class="flex items-start gap-3 p-4 transition-all border border-gray-200 cursor-pointer rounded-xl hover:border-blue-400 hover:bg-blue-50/30">
                                <input type="checkbox" required
                                    class="mt-0.5 w-4 h-4 rounded text-blue-600 border-gray-300 flex-shrink-0">
                                <span class="text-sm text-gray-600">Saya mengonfirmasi bahwa data fisik yang datang sesuai
                                    dengan data booking di atas.</span>
                            </label>
                            <input type="text" name="pic_warehouse" required
                                placeholder="Nama PIC Warehouse penanggung jawab"
                                class="w-full h-10 px-4 text-sm transition-all border border-gray-200 outline-none rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                        </div>
                    </div>

                    {{-- STEP 2: BATCHING --}}
                    <div class="hidden step-content" id="step2">
                        <div class="flex flex-col gap-3 mb-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800">Pembagian Batch & Porter</h4>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Total: <span id="current_total_display" class="font-semibold text-blue-600">0</span>
                                    / <span id="total_qty_display" class="font-semibold text-gray-700">0</span>
                                </p>
                            </div>
                            <button type="button" onclick="addBatchField()"
                                class="inline-flex items-center self-start gap-2 px-4 text-xs font-medium text-white transition-colors bg-blue-600 h-9 hover:bg-blue-700 rounded-xl sm:self-auto">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                Tambah Batch
                            </button>
                        </div>
                        <div id="batchContainer" class="space-y-3"></div>
                    </div>

                    {{-- STEP 3: PLACEMENT --}}
                    <div class="hidden step-content" id="step3">
                        <div class="flex items-start gap-3 p-4 mb-5 border bg-amber-50 border-amber-100 rounded-xl">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <p class="text-xs text-amber-700">Input kode palet atau lokasi secara manual sesuai posisi yang
                                diletakkan oleh porter di lapangan.</p>
                        </div>
                        <div id="placementContainer" class="space-y-3"></div>
                    </div>

                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center gap-3 px-6 py-5 border-t border-gray-100 bg-gray-50/60">
                    <button type="button" id="prevBtn" onclick="changeStep(-1)"
                        class="hidden h-10 px-5 text-sm font-medium text-gray-500 transition-colors bg-white border border-gray-200 rounded-xl hover:bg-gray-100">
                        Previous
                    </button>
                    <button type="button" id="nextBtn" onclick="changeStep(1)"
                        class="flex-1 h-10 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors active:scale-[0.98]">
                        Continue
                    </button>
                    <button type="submit" id="finalSubmitBtn"
                        class="hidden flex-1 h-10 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors active:scale-[0.98]">
                        Confirm & Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let currentStep = 1;
        let maxQty = 0;

        function getInventoryData() {
            const rawInventory = document.querySelectorAll('#palletInventoryData div');
            return Array.from(rawInventory).map(el => ({
                line: el.dataset.line,
                petak: el.dataset.petak,
                pallet: el.dataset.pallet
            }));
        }
        window.currentInventory = getInventoryData();

        function openWarehouseModal(code) {
            const dataSource = document.querySelector(`#bookingDataSource [data-code="${code}"]`);
            if (!dataSource) return alert('Kode Booking tidak valid atau sudah diproses!');

            maxQty = parseFloat(dataSource.getAttribute('data-qty')) || 0;

            document.getElementById('check_product_name').innerText = dataSource.getAttribute('data-name');
            document.getElementById('check_product_type').innerText = dataSource.getAttribute('data-type');
            document.getElementById('check_qty').innerText = maxQty;
            document.getElementById('check_dose').innerText = dataSource.getAttribute('data-dose');
            document.getElementById('check_unit').innerText = dataSource.getAttribute('data-unit');
            document.getElementById('total_qty_display').innerText = maxQty;
            document.getElementById('display_booking_code').innerText = code;
            document.getElementById('modal_booking_code').value = code;

            currentStep = 1;
            document.getElementById('batchContainer').innerHTML = '';
            addBatchField();
            updateStepUI();

            const modal = document.getElementById('warehouseModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function changeStep(n) {
            if (n === 1 && !validateCurrentStep()) return;
            currentStep += n;
            updateStepUI();
            if (currentStep === 3) preparePlacementFields();
        }

        function validateCurrentStep() {
            if (currentStep === 1) {
                const pic = document.querySelector('[name="pic_warehouse"]').value.trim();
                const check = document.querySelector('#step1 input[type="checkbox"]').checked;
                if (!pic || !check) {
                    alert('Mohon isi nama PIC dan centang konfirmasi data!');
                    return false;
                }
            }
            if (currentStep === 2) {
                const inputs = document.querySelectorAll('.batch-input');
                let total = 0,
                    allFilled = true;
                inputs.forEach(i => {
                    const v = parseFloat(i.value) || 0;
                    total += v;
                    if (v <= 0) allFilled = false;
                });
                if (inputs.length === 0 || !allFilled) {
                    alert('Semua Qty Batch harus diisi dengan angka positif!');
                    return false;
                }
                if (Math.abs(total - maxQty) > 0.001) {
                    alert(`Total batch (${total}) belum sesuai dengan qty booking (${maxQty})!`);
                    return false;
                }
                let porterFilled = true;
                document.querySelectorAll('[name="batch_porters[]"]').forEach(p => {
                    if (!p.value) porterFilled = false;
                });
                if (!porterFilled) {
                    alert('Pilih porter untuk setiap batch!');
                    return false;
                }
            }
            return true;
        }

        function updateStepUI() {
            document.querySelectorAll('.step-content').forEach((el, idx) => {
                el.classList.toggle('hidden', idx + 1 !== currentStep);
            });

            document.querySelectorAll('.step-item').forEach((el, idx) => {
                const circle = el.querySelector('.step-circle');
                const stepNum = idx + 1;
                if (stepNum < currentStep) {
                    circle.className =
                        'step-circle w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold';
                    circle.innerHTML =
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
                } else if (stepNum === currentStep) {
                    circle.className =
                        'step-circle w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold';
                    circle.innerText = stepNum;
                } else {
                    circle.className =
                        'step-circle w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs font-bold';
                    circle.innerText = stepNum;
                }
            });

            document.getElementById('prevBtn').classList.toggle('hidden', currentStep === 1);
            document.getElementById('nextBtn').classList.toggle('hidden', currentStep === 3);
            document.getElementById('finalSubmitBtn').classList.toggle('hidden', currentStep !== 3);
        }

        function addBatchField() {
            const container = document.getElementById('batchContainer');
            const porterData = document.querySelectorAll('#porterDataSource div');

            let porterOptions = '<option value="">Pilih Porter</option>';
            porterData.forEach(p => {
                porterOptions += `<option value="${p.dataset.name}">${p.dataset.name}</option>`;
            });

            const div = document.createElement('div');
            div.className =
                'batch-row grid grid-cols-1 gap-3 sm:grid-cols-3 items-end p-4 bg-gray-50 border border-gray-100 rounded-xl';
            div.innerHTML = `
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Qty Batch</label>
                    <input type="number" name="batch_quantities[]" oninput="updateBatchTotal()" step="any" required
                        placeholder="0"
                        class="w-full h-10 px-4 text-sm font-medium transition-all bg-white border border-gray-200 outline-none rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 batch-input">
                </div>
                <div>
                    <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Porter</label>
                    <select name="batch_porters[]" required
                        class="w-full h-10 px-4 text-sm font-medium transition-all bg-white border border-gray-200 outline-none cursor-pointer rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                        ${porterOptions}
                    </select>
                </div>
                <div>
                    <button type="button" onclick="this.closest('.batch-row').remove(); updateBatchTotal();"
                        class="inline-flex items-center gap-1.5 h-10 px-4 text-xs font-medium text-red-500 hover:bg-red-50 border border-red-100 rounded-xl transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                        Hapus
                    </button>
                </div>
            `;
            container.appendChild(div);
            updateBatchTotal();
        }

        function updateBatchTotal() {
            let total = 0;
            document.querySelectorAll('.batch-input').forEach(i => total += parseFloat(i.value) || 0);
            document.getElementById('current_total_display').innerText = total.toLocaleString();
        }

        function preparePlacementFields() {
            const container = document.getElementById('placementContainer');
            container.innerHTML = '';
            const batchInputs = document.querySelectorAll('.batch-input');
            const porterInputs = document.querySelectorAll('[name="batch_porters[]"]');

            batchInputs.forEach((input, idx) => {
                const qty = input.value;
                const porter = porterInputs[idx].value || 'Unknown';
                const div = document.createElement('div');
                div.className =
                    'p-4 border border-gray-100 rounded-xl bg-white flex flex-col lg:flex-row gap-4 items-start lg:items-center';
                div.innerHTML = `
                    <div class="flex-shrink-0">
                        <span class="inline-flex items-center px-2 py-1 text-[10px] font-bold text-blue-600 bg-blue-50 rounded-lg">Batch ${idx + 1}</span>
                        <p class="mt-1 text-sm font-semibold text-gray-800">${porter}</p>
                        <p class="text-xs text-gray-400">${qty} unit</p>
                    </div>
                    <div class="grid flex-1 w-full grid-cols-3 gap-2">
                        <select onchange="updatePetakOptions(${idx})" id="line_${idx}" required
                            class="h-10 px-3 text-xs font-medium transition-all border border-gray-200 outline-none bg-gray-50 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                            <option value="">Line</option>
                        </select>
                        <select onchange="updatePalletOptions(${idx})" id="petak_${idx}" required
                            class="h-10 px-3 text-xs font-medium transition-all border border-gray-200 outline-none bg-gray-50 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                            <option value="">Petak</option>
                        </select>
                        <select name="pallet_ids[]" id="pallet_${idx}" required
                            class="h-10 px-3 text-xs font-medium transition-all border border-gray-200 outline-none bg-gray-50 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400">
                            <option value="">Palet</option>
                        </select>
                    </div>
                `;
                container.appendChild(div);

                const lineSelect = document.getElementById(`line_${idx}`);
                const uniqueLines = [...new Set(window.currentInventory.map(i => i.line))];
                uniqueLines.forEach(l => {
                    lineSelect.innerHTML += `<option value="${l}">Line ${l}</option>`;
                });
            });
        }

        function updatePetakOptions(idx) {
            const line = document.getElementById(`line_${idx}`).value;
            const petakSelect = document.getElementById(`petak_${idx}`);
            const palletSelect = document.getElementById(`pallet_${idx}`);

            petakSelect.innerHTML = '<option value="">Petak</option>';
            palletSelect.innerHTML = '<option value="">Palet</option>';

            if (!line) return;
            [...new Set(window.currentInventory.filter(i => i.line === line).map(i => i.petak))]
            .forEach(p => {
                petakSelect.innerHTML += `<option value="${p}">Petak ${p}</option>`;
            });
        }

        function updatePalletOptions(idx) {
            const line = document.getElementById(`line_${idx}`).value;
            const petak = document.getElementById(`petak_${idx}`).value;
            const palletSelect = document.getElementById(`pallet_${idx}`);

            palletSelect.innerHTML = '<option value="">Palet</option>';
            if (!petak) return;

            window.currentInventory.filter(i => i.line === line && i.petak === petak)
                .forEach(p => {
                    palletSelect.innerHTML += `<option value="${p.pallet}">${p.pallet}</option>`;
                });
        }

        function closeWarehouseModal() {
            if (confirm('Batalkan proses check-in? Data yang diisi akan hilang.')) {
                const modal = document.getElementById('warehouseModal');
                modal.classList.replace('flex', 'hidden');
            }
        }

        function handleManualInput() {
            const input = document.getElementById('manual_booking_input');
            if (input.value.trim()) {
                openWarehouseModal(input.value.trim());
                input.value = '';
            }
        }

        function onScanSuccess(code) {
            new Audio('https://www.soundjay.com/buttons/beep-07a.mp3').play().catch(() => {});
            openWarehouseModal(code);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner('reader', {
            fps: 10,
            qrbox: 250
        });
        html5QrcodeScanner.render(onScanSuccess);
    </script>

@endsection
