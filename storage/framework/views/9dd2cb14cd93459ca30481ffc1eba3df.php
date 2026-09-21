<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<?php echo $__env->make('layouts.head', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php echo $__env->yieldPushContent('head'); ?>

<body class="min-h-[100dvh] font-sans antialiased text-slate-900" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <?php $plainPage = request()->routeIs(['landing', 'customer.login', 'customer.register', 'admin.login']); ?>

    <?php if($plainPage): ?>
        <?php echo $__env->yieldContent('content'); ?>
    <?php else: ?>
        <div class="flex min-h-[100dvh] bg-slate-50 lg:h-[100dvh] lg:overflow-hidden">
            <?php echo $__env->make('layouts.aside', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="flex min-w-0 flex-1 flex-col lg:h-[100dvh] lg:overflow-hidden">
                <?php echo $__env->make('layouts.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <main class="relative flex-1 overflow-y-auto">
                    <div class="mx-auto w-full max-w-[1440px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                        <?php echo $__env->yieldContent('content'); ?>
                    </div>
                    <footer class="border-t border-slate-200 bg-white py-5 text-center text-[11px] text-slate-400">
                        &copy; <?php echo e(date('Y')); ?> BeamApp · Customer Portal
                    </footer>
                </main>
            </div>
            <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-950/45 backdrop-blur-[2px] lg:hidden"></div>
        </div>
    <?php endif; ?>

    <?php echo $__env->make('layouts.notif', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/layouts/master.blade.php ENDPATH**/ ?>