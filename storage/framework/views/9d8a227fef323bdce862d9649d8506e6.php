<?php
    $alerts = [
        'success' => 'bg-green-100 border-green-300 text-green-700',
        'error' => 'bg-red-100 border-red-300 text-red-700',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
        'warning' => 'bg-yellow-100 border-yellow-300 text-yellow-800',
    ];
?>

<div class="pointer-events-none fixed z-[180] left-4 right-4 top-4 space-y-3 sm:left-auto sm:w-full sm:max-w-md">
    <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $classes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(session($type)): ?>
            <div x-data="{ show: false }" x-init="show = true; <?php if(in_array($type, ['success', 'info'])): ?> setTimeout(() => show = false, 7000); <?php endif; ?>" x-show="show"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-400"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                role="status" class="pointer-events-auto flex items-start gap-3 px-4 py-3 text-sm font-medium border shadow-lg rounded-xl <?php echo e($classes); ?>">
                <span class="min-w-0 flex-1 break-words"><?php echo e(session($type)); ?></span>
                <button type="button" @click="show = false" aria-label="Dismiss notification" class="shrink-0 rounded p-1 leading-none">✕</button>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<?php if($errors->any()): ?>
    <div x-data="{ show: true }"  x-cloak x-show="show" x-transition
        class="px-4 py-3 mb-4 text-red-800 bg-red-100 border border-red-300 rounded-lg">
        <ul class="text-sm list-disc list-inside">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/layout/notif.blade.php ENDPATH**/ ?>