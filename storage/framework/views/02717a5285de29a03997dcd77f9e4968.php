<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>
<body class="flex min-h-full items-center justify-center bg-slate-50 p-6 font-sans text-slate-700 antialiased">
    <main class="w-full max-w-lg text-center">
        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl shadow-slate-200/50">
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900"><?php echo $__env->yieldContent('code'); ?></h1>
            <div class="mt-3 text-sm leading-6 text-slate-500"><?php echo $__env->yieldContent('message'); ?></div>
        </div>
    </main>
</body>
</html>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/errors/layout.blade.php ENDPATH**/ ?>