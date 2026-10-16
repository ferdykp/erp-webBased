<?php $__env->startSection('title', 'Order Management'); ?>
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
            <div data-line="<?php echo e($p->line); ?>" data-petak="<?php echo e($p->slot_section); ?>" data-status="<?php echo e($p->status); ?>">
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>


    <?php
        $tabs = [
            'all' => 'All Orders',
            'incoming' => 'Awaiting Goods',
            'progress' => 'In Progress',
            'completed' => 'Completed',
        ];
        $canManage = in_array(auth('admin')->user()->role, ['superadmin', 'production']);
        $canReceive = in_array(auth('admin')->user()->role, ['superadmin', 'cargo_admin']);
        $orderDetails = $bookings->getCollection()->mapWithKeys(function ($booking) {
            return [
                $booking->id => [
                    'id' => $booking->id,
                    'code' => $booking->booking_code,
                    'customer' => $booking->customer?->company_name ?: 'Company not specified',
                    'contact' =>
                        $booking->customer?->contacts->firstWhere('is_primary', true)?->name ??
                        ($booking->customer?->contacts->first()?->name ?? 'Contact not registered'),
                    'product' => $booking->products->first()?->product_name ?? 'Not specified',
                    'quantity' => $booking->products->sum('quantity'),
                    'status' => $booking->status,
                    'created' => $booking->created_at?->format('d M Y'),
                    'batchCount' => $booking->batches->count(),
                    'finishedCount' => $booking->batches->where('status', 'done')->count(),
                    'received' => $booking->arrival_time !== null || $booking->placementDetails->isNotEmpty(),
                    'workspace' => route('admin.workspace.show', ['booking' => $booking->id]),
                ],
            ];
        });
    ?>

    <div class="mx-auto max-w-[1680px] space-y-5 pb-10" x-data="{
        selectedId: null,
        details: <?php echo e(Js::from($orderDetails)); ?>,
        get selected() { return this.selectedId ? this.details[this.selectedId] : null },
        select(id) { this.selectedId = id; }
    }">
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-[.15em] text-slate-400">Operations / Workspace</p>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">Order Management</h1>
                <p class="mt-1 text-sm text-slate-500">Manage customer orders and operational progress in one workspace.</p>
            </div>
            <?php if(auth('admin')->user()->role === 'superadmin'): ?>
                <a href="<?php echo e(route('admin.bookings.create')); ?>"
                    class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-white bg-blue-600 shadow-sm rounded-xl hover:bg-blue-700">
                    <i class="fa-solid fa-plus"></i> New Order
                </a>
            <?php endif; ?>
        </header>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="Order status summary">
            <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route('admin.bookings', ['view' => $key, 'search' => $search ?: null])); ?>"
                    class="rounded-2xl border p-4 transition hover:border-blue-300 <?php echo e($view === $key ? 'border-blue-300 bg-blue-50/60' : 'border-slate-200 bg-white'); ?>"
                    <?php if($view === $key): ?> aria-current="page" <?php endif; ?>>
                    <p class="text-xs font-medium text-slate-500"><?php echo e($label); ?></p>
                    <p class="mt-2 text-2xl font-bold tabular-nums text-slate-900"><?php echo e($counts[$key]); ?></p>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>

        <section class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <nav class="flex gap-1 px-4 pt-3 overflow-x-auto border-b border-slate-100" aria-label="Order filters">
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('admin.bookings', ['view' => $key, 'search' => $search ?: null])); ?>"
                        class="whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition <?php echo e($view === $key ? 'border-blue-600 text-blue-700' : 'border-transparent text-slate-500 hover:text-slate-900'); ?>"
                        <?php if($view === $key): ?> aria-current="page" <?php endif; ?>><?php echo e($label); ?> <span
                            class="ml-1 text-xs opacity-60"><?php echo e($counts[$key]); ?></span></a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>
            <div class="p-4 border-b border-slate-100">
                <form method="GET" action="<?php echo e(route('admin.bookings')); ?>" class="flex flex-wrap gap-2">
                    <input type="hidden" name="view" value="<?php echo e($view); ?>">
                    <label for="order-search" class="sr-only">Search orders</label>
                    <div class="relative min-w-[200px] flex-1">
                        <i
                            class="absolute text-xs -translate-y-1/2 fa-solid fa-magnifying-glass left-4 top-1/2 text-slate-400"></i>
                        <input id="order-search" name="search" type="search" value="<?php echo e($search); ?>"
                            placeholder="Search orders, customers or products"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100">
                    </div>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white">Search</button>
                    <?php if($search !== ''): ?>
                        <a href="<?php echo e(route('admin.bookings', ['view' => $view])); ?>"
                            class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-600">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="grid min-h-[540px] grid-cols-1 xl:grid-cols-[minmax(0,1.5fr)_minmax(340px,1fr)]">
                <div class="min-w-0 border-r-0 border-slate-100 xl:border-r">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-slate-100">
                        <h2 class="text-sm font-semibold text-slate-800"><?php echo e($tabs[$view]); ?></h2>
                        <span class="text-xs text-slate-400"><?php echo e($bookings->total()); ?> results</span>
                    </div>
                    <div class="max-h-[780px] space-y-0 overflow-y-auto">
                        <?php $__empty_1 = true; $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $contact =
                                    $booking->customer?->contacts->firstWhere('is_primary', true)?->name ??
                                    $booking->customer?->contacts->first()?->name;
                                $customer = $booking->customer?->company_name ?: ($contact ?: 'Customer');
                            ?>
                            <div class="transition border-b border-slate-100 hover:bg-slate-50/80"
                                :class="selectedId === <?php echo e($booking->id); ?> ? 'bg-blue-50/60' : ''">
                                <button type="button" @click="select(<?php echo e($booking->id); ?>)"
                                    class="w-full px-5 py-4 text-left focus-visible:outline-blue-600"
                                    aria-label="Preview order <?php echo e($booking->booking_code); ?>">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0 space-y-1">
                                            <p class="text-sm font-bold text-slate-900"><?php echo e($booking->booking_code); ?></p>
                                            <p class="text-sm font-medium truncate text-slate-700"><?php echo e($customer); ?></p>
                                            <p class="text-xs truncate text-slate-500">Contact:
                                                <?php echo e($contact ?: 'Not registered'); ?></p>
                                            <p class="text-xs text-slate-400">
                                                <?php echo e($booking->products->first()?->product_name ?? 'No product'); ?> ·
                                                <?php echo e($booking->created_at->format('d M Y')); ?></p>
                                        </div>
                                        <div class="flex flex-col items-end gap-1.5">

                                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $booking->status,'received' => $booking->arrival_time !== null ||
                                                $booking->placementDetails->isNotEmpty()]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($booking->status),'received' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($booking->arrival_time !== null ||
                                                $booking->placementDetails->isNotEmpty())]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>

                                            <?php if($booking->arrival_time !== null || $booking->placementDetails->isNotEmpty()): ?>
                                                <span
                                                    class="inline-flex items-center gap-1.5
                   text-[11px] font-medium text-emerald-700">

                                                    <i class="fa-solid fa-check-circle"></i>
                                                    Goods Received
                                                </span>
                                            <?php elseif($booking->status === 'pending'): ?>
                                                <span class="text-[11px] font-medium text-amber-600">
                                                    Awaiting Goods
                                                </span>
                                            <?php endif; ?>

                                        </div>

                                    </div>
                                </button>
                                <div class="flex flex-wrap items-center gap-2 px-5 pb-4">
                                    <a href="<?php echo e(route('admin.workspace.show', ['booking' => $booking->id])); ?>"
                                        class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-white">Open
                                        Workflow</a>

                                    <?php
                                        $hasReceivingRecord =
                                            $booking->arrival_time !== null || $booking->placementDetails->isNotEmpty();

                                        $isReceivingAvailable =
                                            in_array($booking->status, ['pending', 'approved'], true) &&
                                            !$hasReceivingRecord;
                                    ?>

                                    <?php if($canReceive): ?>
                                        <?php if($isReceivingAvailable): ?>
                                            <a href="<?php echo e(route('admin.smart.receive', $booking->id)); ?>"
                                                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700">

                                                <i class="fa-solid fa-box-open"></i>
                                                Receive Goods
                                            </a>
                                        <?php elseif($hasReceivingRecord): ?>
                                            <span
                                                class="inline-flex items-center gap-2 px-3 py-2 text-xs font-medium rounded-lg bg-emerald-50 text-emerald-700">

                                                <i class="fa-solid fa-circle-check"></i>
                                                Goods Received
                                            </span>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if(
                                        $hasReceivingRecord &&
                                            in_array($booking->status, ['pending', 'approved', 'processing'], true) &&
                                            in_array(auth('admin')->user()->role, ['superadmin', 'production'], true)): ?>
                                        <a href="<?php echo e(route('admin.smart.batch-plan', $booking)); ?>"
                                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700"><i
                                                class="fa-solid fa-gears"></i> Process</a>
                                    <?php endif; ?>
                                    <?php if($booking->status === 'completed' && in_array(auth('admin')->user()->role, ['superadmin', 'manager', 'cargo_admin'])): ?>
                                        <a href="<?php echo e(route('admin.dispatch.show', $booking)); ?>"
                                            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-800">Goods
                                            Handover</a>
                                    <?php endif; ?>
                                    

                                    <?php if($booking->status === 'completed'): ?>
                                        <button type="button" onclick="printReport(<?php echo e($booking->id); ?>)"
                                            class="inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold border rounded-lg border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100">

                                            <i class="fa-solid fa-print"></i>
                                            Print / Save PDF
                                        </button>
                                    <?php endif; ?>

                                    <?php if($booking->arrival_time && $canManage): ?>
                                        <a target="_blank" rel="noopener"
                                            href="<?php echo e(route('admin.bookings.invoice', $booking->id)); ?>"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700">Invoice</a>
                                    <?php endif; ?>
                                    <?php if($canManage): ?>
                                        <a href="<?php echo e(route('admin.bookings.edit', $booking->id)); ?>"
                                            class="rounded-lg px-2 py-1.5 text-xs text-slate-500 hover:text-blue-600">Edit</a>
                                    <?php endif; ?>
                                    <?php if(auth('admin')->user()->role === 'superadmin' &&
                                            $booking->status === 'pending' &&
                                            !$booking->arrival_time &&
                                            !$booking->batches()->exists() &&
                                            !\App\Models\PalletContent::where('booking_id', $booking->id)->exists() &&
                                            !\App\Models\PlacementDetail::where('booking_id', $booking->id)->exists()): ?>
                                        <button type="button"
                                            onclick="confirmDelete(<?php echo e($booking->id); ?>, <?php echo \Illuminate\Support\Js::from($booking->booking_code)->toHtml() ?>)"
                                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-700 hover:bg-red-50">
                                            <i class="mr-1 fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    <?php elseif(auth('admin')->user()->role === 'superadmin'): ?>
                                        <span class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-400"
                                            title="Orders with warehouse or production records cannot be deleted">Delete
                                            locked</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="px-6 py-16 text-center"><i
                                    class="text-2xl fa-solid fa-box-open text-slate-300"></i>
                                <h3 class="mt-3 font-semibold text-slate-800">No orders found</h3>
                                <p class="mt-1 text-sm text-slate-500">Try a different search or filter.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="px-5 py-4 border-t border-slate-100"><?php echo e($bookings->links()); ?></div>
                </div>

                <aside class="min-w-0 self-start p-5 bg-slate-50/60 xl:sticky xl:top-4" aria-label="Selected order preview">
                    <div x-cloak x-show="!selected"
                        class="flex min-h-[280px] flex-col items-center justify-center text-center">
                        <i class="text-2xl fa-solid fa-arrow-pointer text-slate-300"></i>
                        <h3 class="mt-4 font-semibold text-slate-800">Select an order</h3>
                        <p class="max-w-xs mt-1 text-sm text-slate-500">Select an order from the list to review its details
                            and continue the workflow.</p>
                    </div>
                    <div x-show="selected" x-cloak class="space-y-5" aria-live="polite">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold tracking-wide uppercase text-slate-400">Overview Order</p>
                                <h3 class="mt-1 text-xl font-bold break-all text-slate-900" x-text="selected?.code"></h3>
                            </div>
                            <button type="button" @click="selectedId = null"
                                class="p-2 rounded-lg text-slate-500 hover:bg-slate-200" aria-label="Close preview"><i
                                    class="fa-solid fa-xmark"></i></button>
                        </div>
                        <div class="p-4 bg-white border rounded-xl border-slate-200">
                            <dl class="space-y-3 text-sm">
                                <div>
                                    <dt class="text-xs text-slate-400">Company</dt>
                                    <dd class="mt-1 font-semibold text-slate-800" x-text="selected?.customer"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Customer Contact</dt>
                                    <dd class="mt-1 text-slate-700" x-text="selected?.contact"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Product</dt>
                                    <dd class="mt-1 text-slate-700" x-text="selected?.product"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Status</dt>
                                    <dd class="mt-1 font-semibold capitalize text-slate-800"
                                        x-text="selected?.status?.replaceAll('_', ' ')"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Created</dt>
                                    <dd class="mt-1 text-slate-700" x-text="selected?.created"></dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-slate-400">Production batches</dt>
                                    <dd class="mt-1 text-slate-700"><span x-text="selected?.finishedCount"></span> / <span
                                            x-text="selected?.batchCount"></span> finished</dd>
                                </div>
                            </dl>
                        </div>
                        <div class="p-4 bg-white border rounded-xl border-slate-200">
                            <h4 class="font-semibold text-slate-800">Workflow Overview</h4>
                            <div class="mt-3 space-y-3 text-sm">
                                <div class="flex items-center justify-between"><span>Receiving</span><span
                                        class="font-medium"
                                        x-text="selected?.received ? 'Goods Received' : 'Awaiting Goods'"></span>
                                </div>
                                <div class="flex items-center justify-between"><span>Production</span><span
                                        class="font-medium"
                                        x-text="selected?.status === 'completed' ? 'Completed' : (selected?.status === 'processing' ? 'In progress' : 'Not completed')"></span>
                                </div>
                                <p class="text-xs leading-relaxed text-slate-500">Open the order workspace for batch,
                                    dosimetry, and document details.</p>
                            </div>
                        </div>
                        <a :href="selected?.workspace"
                            class="flex items-center justify-center w-full gap-2 px-4 py-3 text-sm font-semibold text-white bg-blue-600 rounded-xl hover:bg-blue-700">Continue
                            Workflow <i class="fa-solid fa-arrow-right"></i></a>
                    </div>
                </aside>
            </div>
        </section>
    </div>

    
    <div data-ui-modal role="dialog" aria-modal="true" aria-label="Confirm Deletion" tabindex="-1" id="deleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div
            class="relative w-full max-w-sm p-8 transition-all scale-95 opacity-0 bg-white shadow-2xl rounded-[2.5rem] modal-card">
            <div class="flex flex-col items-center text-center">
                <div class="flex items-center justify-center w-16 h-16 mb-6 rounded-2xl bg-rose-50 text-rose-500">
                    <i class="text-2xl fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="mb-2 text-xl font-black text-slate-800">Delete Order?</h3>
                <p class="mb-8 text-xs font-medium leading-relaxed text-slate-500">
                    Booking <span id="deleteBookingCode" class="font-bold text-slate-800"></span> will be permanently
                    deleted only if it has no operational history.
                </p>
                <div class="flex flex-col w-full gap-3">
                    <form id="deleteForm" data-delete-confirmed="true" method="POST" action="">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                            class="w-full py-4 text-xs font-black tracking-widest text-white uppercase shadow-lg bg-rose-600 rounded-2xl active:scale-95 shadow-rose-100">Yes,
                            Delete Order</button>
                    </form>
                    <button onclick="closeDeleteModal()"
                        class="w-full py-4 text-xs font-black tracking-widest uppercase text-slate-400 bg-slate-50 rounded-2xl active:scale-95">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('admin.bookings.partials.preRad', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        function confirmDelete(id, code) {
            const modal = document.getElementById('deleteModal');
            const card = modal.querySelector('.modal-card');
            document.getElementById('deleteForm').action = <?php echo json_encode(route('admin.bookings.destroy', ['id' => '__ID__']), 512) ?>.replace('__ID__',
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
            const urlTemplate = <?php echo json_encode(route('admin.orders.report.print', ['booking' => '__ID__']), 512) ?>;

            const url = urlTemplate.replace(
                '__ID__',
                encodeURIComponent(bookingId)
            );

            window.open(url, '_blank');
        }
    </script>
    <script src="<?php echo e(asset('js/admin/preRad.js')); ?>"></script>
    
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/bookings/index.blade.php ENDPATH**/ ?>