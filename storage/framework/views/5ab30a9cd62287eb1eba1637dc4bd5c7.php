<?php $__env->startSection('title', 'Warehouse PIC Directory'); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full pb-20 space-y-6">

        
        <div class="flex flex-col gap-4 pb-6 border-b sm:flex-row sm:items-center sm:justify-between border-slate-100">
            <div>
                <nav class="flex mb-1.5 text-[10px] font-bold tracking-widest text-slate-400 uppercase gap-2">
                    <span>Facility Oversight</span>
                    <span>&middot;</span>
                    <span class="text-blue-600">Personnel Operations</span>
                </nav>
                <h2 class="text-2xl font-black tracking-tight text-slate-800 md:text-3xl">
                    Warehouse <span class="text-blue-600">PIC Management</span>
                </h2>
                <p class="text-xs text-slate-400 mt-0.5">Manage, monitor, and assign officers in charge of warehouse
                    installations.</p>
            </div>

            <div>
                <a href="<?php echo e(route('admin.warehouse-pics.create')); ?>"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 active:scale-[0.98] rounded-xl shadow-sm transition-all whitespace-nowrap">
                    <i class="fa-solid fa-plus text-[10px]"></i>
                    <span>Register New PIC</span>
                </a>
            </div>
        </div>

        
        <div class="overflow-hidden bg-white border shadow-sm border-slate-200/70 rounded-2xl">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr
                            class="text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/70 border-b border-slate-100">
                            <th class="py-3.5 px-6">Personnel Info</th>
                            <th class="py-3.5 px-6">Email Address</th>
                            <th class="py-3.5 px-6">Phone Number</th>
                            <th class="py-3.5 px-6">Work Shift</th>
                            <th class="py-3.5 px-6">Account Status</th>
                            <th class="py-3.5 px-6 text-center">Operational Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__empty_1 = true; $__currentLoopData = $pics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="transition-colors hover:bg-slate-50/40 group">
                                
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200/60 flex items-center justify-center font-bold text-slate-600 text-[11px] uppercase group-hover:bg-blue-50 group-hover:text-blue-600 group-hover:border-blue-100 transition-colors">
                                            <?php echo e(substr($pic->name, 0, 2)); ?>

                                        </div>
                                        <div>
                                            <p class="font-bold tracking-tight uppercase text-slate-800"><?php echo e($pic->name); ?>

                                            </p>
                                            <p class="text-[10px] text-slate-400 mt-0.5">ID:
                                                PIC-<?php echo e(str_pad($pic->id, 4, '0', STR_PAD_LEFT)); ?></p>
                                        </div>
                                    </div>
                                </td>

                                
                                <td class="px-6 py-4 font-medium text-slate-600">
                                    <?php echo e($pic->email); ?>

                                </td>

                                
                                <td class="px-6 py-4 font-mono font-medium text-slate-500">
                                    <?php echo e($pic->phone ?? '—'); ?>

                                </td>

                                
                                <td class="px-6 py-4">
                                    <?php if($pic->shift): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200/60 uppercase tracking-wide">
                                            <?php echo e($pic->shift); ?>

                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/40 uppercase tracking-wide">
                                            Unassigned
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4">
                                    <?php if($pic->is_active): ?>
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60 uppercase tracking-wide">
                                            <span class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200 uppercase tracking-wide">
                                            <span class="w-1 h-1 rounded-full bg-slate-400"></span> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>

                                
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="<?php echo e(route('admin.warehouse-pics.edit', $pic->id)); ?>"
                                            class="flex items-center justify-center transition-all bg-white border rounded-lg w-7 h-7 text-slate-400 border-slate-200 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50/50 shadow-2xs"
                                            title="Edit Profile">
                                            <i class="fa-solid fa-pen text-[10px]"></i>
                                        </a>

                                        <form action="<?php echo e(route('admin.warehouse-pics.destroy', $pic->id)); ?>" method="POST"
                                            onsubmit="return confirm('Revoke this PIC profile permanently?');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="flex items-center justify-center transition-all bg-white border rounded-lg w-7 h-7 text-slate-400 border-slate-200 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50/50 shadow-2xs"
                                                title="Delete Profile">
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div
                                        class="flex items-center justify-center w-12 h-12 mx-auto mb-3 border rounded-full bg-slate-50 border-slate-200/60 text-slate-300">
                                        <i class="text-base fa-solid fa-user-shield"></i>
                                    </div>
                                    <h4 class="text-xs font-bold tracking-wider uppercase text-slate-800">No PIC Personnel
                                        Found</h4>
                                    <p class="text-[11px] text-slate-400 mt-1">Please add a warehouse supervisor profile to
                                        map operating logs.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            
            <?php if($pics->hasPages()): ?>
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    <?php echo e($pics->links()); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/warehouse-pic/index.blade.php ENDPATH**/ ?>