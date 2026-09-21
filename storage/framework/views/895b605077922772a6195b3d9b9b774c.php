<?php $__env->startSection('title', 'Dashboard'); ?>

<?php $__env->startSection('content'); ?>

    
    <div id="bookingDataSource" class="hidden">
        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $product = $b->products->first(); ?>
            <div data-code="<?php echo e($b->booking_code); ?>" data-id="<?php echo e($b->id); ?>"
                data-name="<?php echo e($product->product_name ?? '-'); ?>" data-type="<?php echo e($product->product_type ?? '-'); ?>"
                data-qty="<?php echo e($product->quantity ?? 0); ?>" data-unit="<?php echo e($product->unit ?? ''); ?>"
                data-temp="<?php echo e($product->expect_temp ?? '-'); ?>" data-dmin="<?php echo e($product->dmin ?? 0); ?>"
                data-dmax="<?php echo e($product->dmax ?? 0); ?>" data-dimension="<?php echo e($product->dimension_pack ?? '-'); ?>"
                data-vol-pcs="<?php echo e($product->vol_per_pcs ?? 0); ?>" data-vol-total="<?php echo e($product->vol_total ?? 0); ?>"
                data-net-pcs="<?php echo e($product->net_weight_pcs ?? 0); ?>" data-net-total="<?php echo e($product->total_net_weight ?? 0); ?>"
                data-gross-pcs="<?php echo e($product->gross_weight_per_pcs ?? 0); ?>"
                data-gross-total="<?php echo e($product->total_gross_weight ?? 0); ?>">
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div id="porterDataSource" class="hidden">
        <?php $__currentLoopData = $porters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div data-name="<?php echo e($p->name); ?>"></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div id="palletInventoryData" class="hidden">
        <?php $__currentLoopData = $pallets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div data-line="<?php echo e($p->line); ?>" data-petak="<?php echo e($p->slot_section); ?>" data-status="<?php echo e($p->status); ?>"></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
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
            <span class="text-sm font-medium text-gray-600"><?php echo e(now()->format('d F Y')); ?></span>
        </div>
    </div>

    
    <?php
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
    ?>

    <div class="grid grid-cols-2 gap-4 mb-8 sm:grid-cols-3 lg:grid-cols-5">
        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div
                class="p-5 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center justify-center w-9 h-9 <?php echo e($stat['bg']); ?> rounded-xl">
                        <svg class="w-4 h-4 <?php echo e($stat['text']); ?>" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($stat['icon']); ?>" />
                        </svg>
                    </div>
                </div>
                <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider"><?php echo e($stat['label']); ?></p>
                <p class="mt-1 text-2xl font-bold text-gray-900 sm:text-3xl"><?php echo e($stat['count']); ?></p>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
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
            <?php
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
            ?>

            <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $a = $accentMap[$step['accent']]; ?>
                <div
                    class="relative p-5 bg-white border border-gray-100 rounded-2xl shadow-sm <?php echo e($a['hover']); ?> transition-all duration-200 overflow-hidden group">
                    <div
                        class="absolute -right-2 -top-1 text-6xl font-bold text-gray-50 group-hover:text-<?php echo e($step['accent']); ?>-50 transition-colors select-none">
                        <?php echo e($step['num']); ?>

                    </div>
                    <div class="relative">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center justify-center w-9 h-9 <?php echo e($a['bg']); ?> rounded-xl">
                                <svg class="w-4 h-4 <?php echo e($a['text']); ?>" fill="none" stroke="currentColor"
                                    stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($step['path']); ?>" />
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold <?php echo e($a['num']); ?>">
                                <?php echo e($step['num']); ?>

                            </span>
                        </div>
                        <h3 class="mb-1 text-sm font-semibold text-gray-800"><?php echo e($step['title']); ?></h3>
                        <p class="text-xs leading-relaxed text-gray-400"><?php echo $step['desc']; ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        
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

                    <?php $isFull = \App\Models\Pallet::where('status', 'empty')->count() === 0; ?>
                    <button type="button" onclick="<?php echo e($isFull ? 'alertFull()' : 'handleManualInput()'); ?>"
                        class="w-full h-10 text-sm font-medium rounded-xl transition-all active:scale-[0.98] <?php echo e($isFull ? 'bg-gray-700 text-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-500 text-white'); ?>">
                        <?php echo e($isFull ? 'Warehouse Full' : 'Process Check-in'); ?>

                    </button>
                </div>
            </div>
        </div>

        
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
                            <?php $__empty_1 = true; $__currentLoopData = \App\Models\Booking::whereNotNull('arrival_time')->latest('arrival_time')->take(6)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="transition-colors hover:bg-gray-50/60">
                                    <td class="px-6 py-3.5">
                                        <span
                                            class="inline-flex items-center px-2.5 py-1 text-xs font-mono font-semibold text-blue-700 bg-blue-50 rounded-lg">
                                            #<?php echo e($recent->booking_code); ?>

                                        </span>
                                    </td>
                                    <td class="px-6 py-3.5 text-sm font-medium text-gray-700">
                                        <?php echo e($recent->customer->contacts->first()->name ?? 'Guest'); ?>

                                    </td>
                                    <td class="px-6 py-3.5 text-sm text-gray-500">
                                        <?php echo e($recent->products->first()?->product_name ?? '-'); ?>

                                    </td>
                                    <td class="px-6 py-3.5 text-sm font-semibold text-gray-700 text-right">
                                        <?php echo e($recent->arrival_time->format('H:i')); ?>

                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-sm text-center text-gray-400">No recent
                                        arrivals.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="flex-1 divide-y divide-gray-100 sm:hidden">
                    <?php $__empty_1 = true; $__currentLoopData = \App\Models\Booking::whereNotNull('arrival_time')->latest('arrival_time')->take(6)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="flex items-center justify-between px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center px-2 py-1 text-[11px] font-mono font-semibold text-blue-700 bg-blue-50 rounded-lg">
                                    #<?php echo e($recent->booking_code); ?>

                                </span>
                                <div>
                                    <p class="text-xs font-semibold text-gray-700">
                                        <?php echo e($recent->customer->contacts->first()->name ?? 'Guest'); ?></p>
                                    <p class="text-[11px] text-gray-400">
                                        <?php echo e($recent->products->first()?->product_name ?? '-'); ?></p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-semibold text-gray-500"><?php echo e($recent->arrival_time->format('H:i')); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="px-5 py-10 text-sm text-center text-gray-400">No recent arrivals.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php echo $__env->make('admin.bookings.partials.checkin-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="<?php echo e(asset('js/admin/checkin.js')); ?>"></script>
    <script>
        function handleManualInput() {
            const input = document.getElementById('manual_booking_input');
            const code = input?.value?.trim();
            if (!code) return;
            openWarehouseModal(code);
            input.value = '';
        }

        function onScanSuccess(code) {
            if (!code) return;
            openWarehouseModal(String(code).trim());
        }

        document.addEventListener('DOMContentLoaded', () => {
            const reader = document.getElementById('reader');
            if (!reader || typeof Html5QrcodeScanner === 'undefined') return;

            const scanner = new Html5QrcodeScanner('reader', {
                fps: 10,
                qrbox: { width: 220, height: 220 },
            }, false);
            scanner.render(onScanSuccess, () => {});
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/dashboard/index.blade.php ENDPATH**/ ?>