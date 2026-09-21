<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="color-scheme" content="light">
    <title>Beam Admin - <?php echo $__env->yieldContent('title'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="min-h-[100dvh] overflow-x-hidden bg-slate-50 font-sans text-slate-900 antialiased lg:overflow-hidden" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">
    <div class="flex min-h-[100dvh] w-full print:block print:h-auto print:min-h-0 lg:h-[100dvh] lg:overflow-hidden">
        <?php echo $__env->make('admin.layout.aside', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <div class="relative flex min-w-0 flex-1 flex-col bg-slate-50 print:block print:h-auto print:overflow-visible lg:h-[100dvh] lg:overflow-y-auto lg:overscroll-contain">
            <?php echo $__env->make('admin.layout.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <main class="w-full flex-1 px-3 py-5 print:p-0 sm:px-5 sm:py-6 lg:px-8 lg:py-7">
                <div class="mx-auto w-full max-w-[1540px] print:max-w-none">
                    <div class="mb-4 print:hidden"><?php echo $__env->make('admin.layout.notif', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </main>
        </div>

        <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-[2px] print:hidden lg:hidden"></div>
    </div>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/layout/app.blade.php ENDPATH**/ ?>