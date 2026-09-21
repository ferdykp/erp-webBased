<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title'); ?> - Beam Admin</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body class="flex items-center justify-center min-h-screen p-4 font-sans antialiased bg-slate-50">

    <div class="max-w-md w-full bg-white border border-slate-100 shadow-2xl rounded-[2rem] p-8 text-center space-y-6">

        
        <div class="flex items-center justify-center w-16 h-16 mx-auto rounded-2xl">
            <?php switch(trim($__env->yieldContent('code'))):
                case ('404'): ?>
                    <div class="flex items-center justify-center w-16 h-16 text-blue-600 bg-blue-50 rounded-2xl animate-bounce">
                        <i class="text-2xl fa-solid fa-compass"></i>
                    </div>
                <?php break; ?>

                <?php case ('403'): ?>
                <?php case ('401'): ?>
                    <div class="flex items-center justify-center w-16 h-16 bg-amber-50 rounded-2xl text-amber-500">
                        <i class="text-2xl fa-solid fa-user-shield"></i>
                    </div>
                <?php break; ?>

                <?php case ('419'): ?>
                    <div class="flex items-center justify-center w-16 h-16 text-orange-500 bg-orange-50 rounded-2xl">
                        <i class="text-2xl fa-solid fa-clock-rotate-left"></i>
                    </div>
                <?php break; ?>

                <?php default: ?>
                    <div class="flex items-center justify-center w-16 h-16 bg-rose-50 rounded-2xl text-rose-500">
                        <i class="text-2xl fa-solid fa-triangle-exclamation"></i>
                    </div>
            <?php endswitch; ?>
        </div>

        
        <div class="space-y-2">
            <h1 class="text-6xl font-black tracking-tight text-slate-800"><?php echo $__env->yieldContent('code'); ?></h1>
            <h2 class="text-xl font-extrabold text-slate-700"><?php echo $__env->yieldContent('title'); ?></h2>

            <p class="px-2 text-sm font-medium leading-relaxed text-slate-400">
                <?php if (! empty(trim($__env->yieldContent('custom_message')))): ?>
                    <?php echo $__env->yieldContent('custom_message'); ?>
                <?php else: ?>
                    <?php echo $__env->yieldContent('message'); ?>. Please verify the action or return to the main operational control dashboard.
                <?php endif; ?>
            </p>
        </div>

        
        
    </div>

</body>

</html>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/errors/minimal.blade.php ENDPATH**/ ?>