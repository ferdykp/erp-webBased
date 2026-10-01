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

    <div class="w-full pb-10 space-y-6 md:space-y-8">
        
        <div class="flex flex-col gap-6 px-2 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <h2 class="text-3xl font-black tracking-tighter md:text-4xl text-slate-800">
                    <?php echo e($pageTitle ?? 'All Bookings'); ?></h2>
                <p class="text-xs font-medium md:text-sm text-slate-500">Monitoring flow & inventory distribution.</p>
            </div>
            <div
                class="flex items-center self-start gap-3 px-5 py-3 bg-white border shadow-sm lg:self-center border-slate-100 rounded-2xl">
                <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                <span class="text-[10px] font-black uppercase text-slate-400 tracking-widest">Total:
                    <?php echo e($bookings->total()); ?></span>
            </div>
        </div>

        
        <div class="bg-white border border-slate-100 shadow-sm rounded-[2rem] md:rounded-[3rem] overflow-hidden">

            
            <div
                class="flex flex-col items-stretch justify-between gap-4 p-6 border-b lg:flex-row lg:items-center border-slate-50">
                <div class="flex flex-col flex-1 gap-3 sm:flex-row sm:items-center">
                    <h3 class="text-lg font-bold text-slate-800 shrink-0">Order List</h3>

                    
                    <div class="relative w-full max-w-md">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                            <i class="text-sm fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="text" id="bookingSearchInput" onkeyup="filterBookingList()"
                            placeholder="Search by customer, code (#BK-xxxx), or product..."
                            class="w-full py-2.5 pl-11 pr-4 text-xs font-medium bg-slate-50 border border-slate-100 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 text-slate-700 placeholder-slate-400 transition-all">
                    </div>
                </div>

                <?php if(in_array(auth('admin')->user()->role, ['superadmin'])): ?>
                    <a href="<?php echo e(route('admin.bookings.create')); ?>"
                        class="flex items-center justify-center gap-2 px-6 py-3 text-sm font-black text-white transition-all bg-blue-600 shadow-lg rounded-xl shadow-blue-100 active:scale-95">
                        <i class="fa-solid fa-plus"></i>
                        <span>Add New Order</span>
                    </a>
                <?php endif; ?>
            </div>

            
            <div id="emptySearchState" class="flex-col items-center justify-center hidden p-12 text-center bg-white">
                <div class="flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-slate-50 text-slate-400">
                    <i class="text-xl fa-solid fa-box-open"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-700">No Orders Found</h4>
                <p class="max-w-xs mt-1 text-xs text-slate-400">We couldn't find any match for your keyword. Try checking
                    for typos or use different keywords.</p>
            </div>

            
            <div id="desktopTableContainer" class="hidden overflow-x-auto md:block">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-[10px] font-black tracking-[0.15em] text-slate-400 uppercase bg-slate-50/50">
                            <th class="px-8 py-5">Customer</th>
                            <th class="px-6 py-5 text-center">Created At</th>
                            <th class="px-6 py-5 text-center">Product</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-8 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="transition-colors group hover:bg-slate-50/50 booking-row-item"
                                data-search-customer="<?php echo e(strtolower($booking->customer->contacts->first()->name ?? 'guest')); ?>"
                                data-search-code="<?php echo e(strtolower($booking->booking_code)); ?>"
                                data-search-product="<?php echo e(strtolower($booking->products->first()->product_name ?? '-')); ?>">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4 text-left">
                                        <div
                                            class="flex items-center justify-center w-10 h-10 font-black text-blue-600 bg-blue-50 rounded-xl shrink-0">
                                            <?php echo e(strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1))); ?>

                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-sm font-bold truncate text-slate-800">
                                                <?php echo e($booking->customer->contacts->first()->name ?? 'Guest'); ?>

                                            </p>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase">
                                                #<?php echo e($booking->booking_code); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="text-xs font-bold text-slate-600"><?php echo e($booking->created_at->format('d M Y')); ?></span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="text-xs font-bold text-slate-600 truncate max-w-[150px] inline-block">
                                        <?php echo e($booking->products->first()->product_name ?? '-'); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $booking->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($booking->status)]); ?>
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
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex items-center justify-end gap-2">
                                        
                                        <button onclick="toggleDetailModal('<?php echo e($booking->id); ?>', true)"
                                            class="p-2 text-blue-600 transition-colors rounded-lg bg-blue-50 hover:bg-blue-600 hover:text-white">
                                            <i class="text-xs fa-solid fa-eye"></i>
                                        </button>
                                        <?php if(in_array(auth('admin')->user()->role, ['superadmin', 'production'])): ?>
                                            <a href="<?php echo e(route('admin.bookings.edit', $booking->id)); ?>"
                                                class="p-2 transition-colors rounded-lg text-amber-600 bg-amber-50 hover:bg-amber-500 hover:text-white">
                                                <i class="text-xs fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <button
                                                onclick="confirmDelete('<?php echo e($booking->id); ?>', '<?php echo e($booking->booking_code); ?>')"
                                                class="p-2 transition-colors rounded-lg text-rose-600 bg-rose-50 hover:bg-rose-600 hover:text-white">
                                                <i class="text-xs fa-solid fa-trash"></i>
                                            </button>

                                            

                                            <?php if($booking->status == 'pending'): ?>
                                                <button onclick="openWarehouseModal('<?php echo e($booking->booking_code); ?>')"
                                                    class="px-4 py-2 text-[10px] font-black uppercase bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">
                                                    Check-in
                                                </button>
                                            <?php elseif($booking->arrival_time): ?>
                                                <a href="<?php echo e(route('admin.bookings.invoice', $booking->id)); ?>"
                                                    target="_blank"
                                                    class="px-4 py-2 text-[10px] font-black text-white bg-emerald-500 rounded-lg hover:bg-emerald-600 shadow-sm transition-all">
                                                    Invoice
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            
            <div id="mobileCardContainer" class="p-4 space-y-4 md:hidden bg-slate-50/50">
                <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="p-5 space-y-4 bg-white border shadow-sm border-slate-100 rounded-2xl booking-card-item"
                        data-search-customer="<?php echo e(strtolower($booking->customer->contacts->first()->name ?? 'guest')); ?>"
                        data-search-code="<?php echo e(strtolower($booking->booking_code)); ?>"
                        data-search-product="<?php echo e(strtolower($booking->products->first()->product_name ?? '-')); ?>">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center justify-center w-10 h-10 font-black text-blue-600 bg-blue-50 rounded-xl">
                                    <?php echo e(strtoupper(substr($booking->customer->contacts->first()->name ?? '?', 0, 1))); ?>

                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">
                                        <?php echo e($booking->customer->contacts->first()->name ?? 'Guest'); ?></p>
                                    <p class="text-[10px] font-bold text-slate-400">#<?php echo e($booking->booking_code); ?></p>
                                </div>
                            </div>
                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $booking->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($booking->status)]); ?>
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
                        </div>

                        <div class="grid grid-cols-1 gap-4 py-3 min-[420px]:grid-cols-2 border-y border-slate-50">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Date</p>
                                <p class="text-xs font-bold text-slate-700"><?php echo e($booking->created_at->format('d M Y')); ?>

                                </p>
                            </div>
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Product</p>
                                <p class="text-xs font-bold truncate text-slate-700">
                                    <?php echo e($booking->products->first()->product_name ?? '-'); ?></p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                            <div class="flex gap-2">
                                <button onclick="toggleDetailModal('<?php echo e($booking->id); ?>', true)"
                                    class="p-2.5 text-blue-600 bg-blue-50 rounded-xl">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <?php if(in_array(auth('admin')->user()->role, ['superadmin', 'production'])): ?>
                                    <a href="<?php echo e(route('admin.bookings.edit', $booking->id)); ?>"
                                        class="p-2.5 text-amber-600 bg-amber-50 rounded-xl">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                    <button
                                        onclick="confirmDelete('<?php echo e($booking->id); ?>', '<?php echo e($booking->booking_code); ?>')"
                                        class="p-2.5 text-rose-600 bg-rose-50 rounded-xl">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>

                            <?php if($booking->status == 'pending'): ?>
                                <button onclick="openWarehouseModal('<?php echo e($booking->booking_code); ?>')"
                                    class="px-5 py-2.5 text-[10px] font-black uppercase bg-blue-600 text-white rounded-xl grow sm:grow-0">
                                    Check-in
                                </button>
                            <?php elseif($booking->arrival_time): ?>
                                <a href="<?php echo e(route('admin.bookings.invoice', $booking->id)); ?>" target="_blank"
                                    class="px-5 py-2.5 text-[10px] font-black text-white bg-emerald-500 rounded-xl text-center grow sm:grow-0">
                                    Invoice
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div id="paginationBlock" class="px-6 py-6 border-t md:px-10 border-slate-50">
                <?php echo e($bookings->links()); ?>

            </div>
        </div>
    </div>

    
    <div id="deleteModal" class="fixed inset-0 z-[9999] flex items-center justify-center hidden px-4">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <div
            class="relative w-full max-w-sm p-8 transition-all scale-95 opacity-0 bg-white shadow-2xl rounded-[2.5rem] modal-card">
            <div class="flex flex-col items-center text-center">
                <div class="flex items-center justify-center w-16 h-16 mb-6 rounded-2xl bg-rose-50 text-rose-500">
                    <i class="text-2xl fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3 class="mb-2 text-xl font-black text-slate-800">Delete Booking?</h3>
                <p class="mb-8 text-xs font-medium leading-relaxed text-slate-500">
                    Booking <span id="deleteBookingCode" class="font-bold text-slate-800"></span> will be permanently
                    removed.
                </p>
                <div class="flex flex-col w-full gap-3">
                    <form id="deleteForm" method="POST" action="">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit"
                            class="w-full py-4 text-xs font-black tracking-widest text-white uppercase shadow-lg bg-rose-600 rounded-2xl active:scale-95 shadow-rose-100">Confirm
                            Delete</button>
                    </form>
                    <button onclick="closeDeleteModal()"
                        class="w-full py-4 text-xs font-black tracking-widest uppercase text-slate-400 bg-slate-50 rounded-2xl active:scale-95">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <?php $__currentLoopData = $bookings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $booking): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo $__env->make('admin.bookings.partials.preRad', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('admin.bookings.partials.checkin-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->make('admin.bookings.partials.detail-modal', ['booking' => $booking], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        // CLIENT-SIDE SEARCH FUNCTION
        function filterBookingList() {
            const query = document.getElementById('bookingSearchInput').value.toLowerCase().trim();
            const desktopRows = document.querySelectorAll('.booking-row-item');
            const mobileCards = document.querySelectorAll('.booking-card-item');
            const emptyState = document.getElementById('emptySearchState');
            const desktopTable = document.getElementById('desktopTableContainer');
            const mobileContainer = document.getElementById('mobileCardContainer');
            const paginationBlock = document.getElementById('paginationBlock');

            let visibleCount = 0;

            // 1. Filter desktop rows
            desktopRows.forEach(row => {
                const customer = row.getAttribute('data-search-customer');
                const code = row.getAttribute('data-search-code');
                const product = row.getAttribute('data-search-product');

                if (customer.includes(query) || code.includes(query) || product.includes(query)) {
                    row.style.display = "";
                    visibleCount++;
                } else {
                    row.style.display = "none";
                }
            });

            // 2. Filter mobile cards
            let mobileVisibleCount = 0;
            mobileCards.forEach(card => {
                const customer = card.getAttribute('data-search-customer');
                const code = card.getAttribute('data-search-code');
                const product = card.getAttribute('data-search-product');

                if (customer.includes(query) || code.includes(query) || product.includes(query)) {
                    card.style.setProperty('display', '', 'important');
                    mobileVisibleCount++;
                } else {
                    card.style.setProperty('display', 'none', 'important');
                }
            });

            // Tentukan target visibility berdasarkan ukuran layar saat ini
            const totalVisible = window.innerWidth >= 768 ? visibleCount : mobileVisibleCount;

            // 3. Handle Empty State & Visibility Layout
            if (totalVisible === 0) {
                emptyState.classList.remove('hidden');
                emptyState.classList.add('flex');
                desktopTable.classList.add('md:hidden');
                mobileContainer.classList.add('hidden');
                if (paginationBlock) paginationBlock.classList.add('hidden');
            } else {
                emptyState.classList.add('hidden');
                emptyState.classList.remove('flex');
                desktopTable.classList.remove('md:hidden');
                mobileContainer.classList.remove('hidden');
                if (paginationBlock) paginationBlock.classList.remove('hidden');
            }

            // Kembalikan ke layout default jika input dikosongkan
            if (query === "") {
                if (paginationBlock) paginationBlock.classList.remove('hidden');
            }
        }

        function toggleDetailModal(id, show) {
            const modal = document.getElementById(`modal-detail-${id}`);
            if (!modal) return;
            const card = modal.querySelector('.modal-card');
            if (show) {
                modal.classList.remove('hidden', 'pointer-events-none');
                modal.classList.add('flex', 'opacity-100');
                setTimeout(() => card.classList.add('scale-100', 'opacity-100'), 10);
            } else {
                card.classList.remove('scale-100', 'opacity-100');
                card.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden', 'pointer-events-none');
                    modal.classList.remove('flex', 'opacity-100');
                }, 300);
            }
        }

        function confirmDelete(id, code) {
            const modal = document.getElementById('deleteModal');
            const card = modal.querySelector('.modal-card');
            document.getElementById('deleteForm').action = `/admin/bookings/${id}`;
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
    </script>
    <script src="<?php echo e(asset('js/admin/preRad.js')); ?>"></script>
    <script src="<?php echo e(asset('js/admin/checkin.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/bookings/index.blade.php ENDPATH**/ ?>