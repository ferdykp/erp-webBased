<?php
    $alerts = [
        'success' => 'bg-green-100 border-green-300 text-green-700',
        'error' => 'bg-red-100 border-red-300 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-300 text-yellow-800',
    ];
?>

<div class="fixed z-50 space-y-3 top-5 right-5">
    <?php $__currentLoopData = $alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $classes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(session($type)): ?>
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 50);
            setTimeout(() => show = false, 3500)" x-show="show"
                x-transition:enter="transition ease-out duration-500"
                x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-400"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-2 scale-95" 
                class="p-4 mx-4 text-sm font-bold border shadow-sm bg-emerald-100 border-emerald-200 text-emerald-700 rounded-2xl shadow-emerald-100">
                <i class="mr-2 fa-solid fa-circle-check"></i> <?php echo e(session($type)); ?>



            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<?php if($errors->any()): ?>
    <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 6000)" x-show="show" x-transition
        class="px-4 py-3 mb-4 text-red-800 bg-red-100 border border-red-300 rounded-lg">
        <ul class="text-sm list-disc list-inside">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/layouts/notif.blade.php ENDPATH**/ ?>