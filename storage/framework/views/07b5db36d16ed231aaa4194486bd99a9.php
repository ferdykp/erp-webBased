<?php $__env->startSection('title', 'Review Order #' . $booking->booking_code); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full pb-10 space-y-6 md:space-y-8">
        
        <div class="flex flex-col gap-6 px-2 md:flex-row md:items-center md:justify-between">
            <div class="text-center md:text-left">
                <a href="<?php echo e(route('admin.business.index')); ?>"
                    class="inline-flex items-center gap-2 mb-4 text-[10px] font-black text-indigo-500 uppercase transition-all hover:text-indigo-700 tracking-widest">
                    <i class="fa-solid fa-arrow-left"></i> Back to Monitoring
                </a>
                <h2 class="text-2xl font-black tracking-tighter md:text-3xl text-slate-800">Order
                    #<?php echo e($booking->booking_code); ?></h2>
                <p class="mt-1 text-xs font-medium text-slate-500">
                    PIC: <span class="font-bold text-slate-700"><?php echo e($booking->customer->contacts->first()->name); ?></span>
                    • <?php echo e($booking->created_at->format('d M Y')); ?>

                </p>
            </div>

            <div class="flex justify-center md:block">
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
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">

            
            <div class="space-y-6 lg:col-span-2">

                
                <?php if($isSpecial): ?>
                    <div
                        class="p-6 md:p-8 bg-rose-50 border border-rose-100 rounded-[2rem] md:rounded-[2.5rem] shadow-sm relative overflow-hidden">
                        <i
                            class="absolute top-0 right-0 p-6 opacity-5 fa-solid fa-triangle-exclamation text-8xl text-rose-600"></i>
                        <div class="relative z-10">
                            <h4
                                class="flex items-center gap-2 text-rose-800 font-black uppercase text-[10px] tracking-[0.2em] mb-4">
                                <i class="fa-solid fa-circle-exclamation text-rose-500"></i>
                                Anomaly Detection
                            </h4>
                            <p class="mb-6 text-sm font-semibold leading-relaxed text-left text-rose-700/80">
                                Order ini memerlukan verifikasi manual karena melebihi parameter standar:
                            </p>

                            <div class="grid grid-cols-1 gap-3">
                                <?php $__currentLoopData = $reasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="flex items-center gap-3 px-5 py-4 text-xs font-black border shadow-sm bg-white/80 border-rose-200 rounded-2xl text-rose-800">
                                        <i class="fa-solid fa-shield-virus text-rose-500"></i>
                                        <?php echo e($reason); ?>

                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div
                        class="p-6 bg-emerald-50 border border-emerald-100 rounded-[2rem] flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                        <div class="flex items-center justify-center w-12 h-12 bg-white shadow-sm rounded-2xl shrink-0">
                            <i class="fa-solid fa-check-double text-emerald-500"></i>
                        </div>
                        <div>
                            <h5 class="font-black text-emerald-800 uppercase text-[10px] tracking-widest">Safe Standard
                                Order</h5>
                            <p class="text-xs font-medium text-emerald-600">All technical parameters are within normal
                                operating range.</p>
                        </div>
                    </div>
                <?php endif; ?>

                
                <div class="p-6 md:p-8 bg-white border border-slate-100 rounded-[2rem] md:rounded-[2.5rem] shadow-sm">
                    <h4 class="flex items-center gap-3 mb-8 text-base font-black md:text-lg text-slate-800">
                        <i class="text-indigo-500 fa-solid fa-flask-vial"></i>
                        Technical Specifications
                    </h4>

                    <?php $__currentLoopData = $booking->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 md:grid-cols-4">
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Product</p>
                                <p class="font-bold break-words text-slate-800"><?php echo e($product->product_name); ?></p>
                            </div>
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Dose Target
                                </p>
                                <p class="font-black text-indigo-600"><?php echo e(\App\Support\NumberFormatter::smart($product->dmin, 4)); ?> - <?php echo e(\App\Support\NumberFormatter::smart($product->dmax, 4)); ?> kGy</p>
                            </div>
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Exp. Temp</p>
                                <p class="font-bold text-slate-800"><?php echo e($product->expect_temp ?? 'Ambient'); ?>°C</p>
                            </div>
                            <div class="p-4 border bg-slate-50/50 rounded-2xl border-slate-50">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total
                                    Quantity</p>
                                <p class="font-bold text-slate-800"><?php echo e($booking->total_qty); ?> <?php echo e($product->unit); ?></p>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div class="space-y-6">
                <div class="p-8 bg-slate-900 rounded-[2.5rem] shadow-2xl text-white relative overflow-hidden">
                    <div class="absolute w-32 h-32 rounded-full -top-10 -left-10 bg-indigo-500/10 blur-2xl"></div>

                    <h4 class="text-xs font-black uppercase tracking-[0.2em] mb-8 text-slate-500 text-center">Admin Approval
                        Center</h4>

                    <?php if($booking->status == 'pending'): ?>
                        <div class="space-y-4">
                            <form action="<?php echo e(route('admin.business.approve', $booking->id)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <?php echo method_field('PUT'); ?>
                                <button type="submit"
                                    class="w-full py-4 text-[11px] font-black tracking-[0.15em] uppercase transition-all shadow-xl bg-emerald-500 hover:bg-emerald-600 rounded-2xl shadow-emerald-500/20 active:scale-95">
                                    Approve & Process
                                </button>
                            </form>

                            <button
                                class="w-full py-4 bg-white/5 hover:bg-white/10 border border-white/10 rounded-2xl font-black text-[10px] uppercase tracking-widest transition-all">
                                Request Revision
                            </button>
                        </div>
                        <p class="mt-6 text-[10px] text-center text-slate-500 font-medium leading-relaxed italic">
                            Approving will forward this order to the warehouse operational team.
                        </p>
                    <?php else: ?>
                        <div class="py-10 text-center border border-white/5 rounded-[2rem] bg-white/5">
                            <div
                                class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-emerald-500/20">
                                <i class="text-xl fa-solid fa-check text-emerald-400"></i>
                            </div>
                            <p class="mb-1 text-xs font-black tracking-widest uppercase">Process Completed</p>
                            <p class="text-[10px] text-slate-500 uppercase tracking-tighter">Order status is
                                <?php echo e($booking->status); ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                
                <div
                    class="p-8 bg-white border border-slate-100 rounded-[2.5rem] shadow-sm flex flex-col items-center text-center">
                    <h4 class="mb-6 text-[10px] font-black tracking-widest uppercase text-slate-400">Requestor Entity</h4>
                    <div
                        class="flex items-center justify-center w-16 h-16 mb-4 text-xl font-black rounded-[1.5rem] bg-indigo-50 text-indigo-600 shadow-inner">
                        <?php echo e(substr($booking->customer->contacts->first()->name, 0, 1)); ?>

                    </div>
                    <p class="font-black leading-tight text-slate-800"><?php echo e($booking->customer->contacts->first()->name); ?>

                    </p>
                    <p class="mt-1 text-xs font-bold text-slate-400">
                        <?php echo e($booking->customer->company_name ?? 'Individual Entity'); ?></p>

                    <div class="w-full h-px my-6 bg-slate-50"></div>

                    <a href="mailto:<?php echo e($booking->customer->email); ?>"
                        class="text-[11px] font-black text-indigo-500 uppercase hover:underline">
                        Send Message <i class="ml-1 fa-solid fa-paper-plane"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/business/detail.blade.php ENDPATH**/ ?>