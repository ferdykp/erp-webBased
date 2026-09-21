<nav class="sticky top-0 z-30 flex h-[76px] items-center justify-between gap-4 border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur-xl print:hidden sm:px-6 lg:px-8">
    <div class="flex min-w-0 items-center gap-3">
        <button @click="sidebarOpen = true"
            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-sm text-slate-600 transition-colors hover:bg-slate-50 lg:hidden"
            aria-label="Open navigation">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="min-w-0">
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">E-Beam Operations</p>
            <h1 class="mt-0.5 truncate text-base font-bold tracking-tight text-slate-900 sm:text-lg"><?php echo $__env->yieldContent('title', 'Dashboard'); ?></h1>
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-2 sm:gap-3">
        <div class="hidden items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-[11px] font-semibold text-slate-500 md:flex">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            System Online
        </div>

        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.outside="open = false"
                class="flex items-center gap-2 rounded-xl p-1.5 transition-colors hover:bg-slate-100">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white shadow-sm">
                    <?php echo e(strtoupper(substr(auth('admin')->user()->name, 0, 1))); ?>

                </div>
                <div class="hidden max-w-36 text-left sm:block">
                    <p class="truncate text-xs font-semibold text-slate-800"><?php echo e(auth('admin')->user()->name); ?></p>
                    <p class="mt-0.5 truncate text-[10px] capitalize text-slate-400"><?php echo e(str_replace('_', ' ', auth('admin')->user()->role ?? 'administrator')); ?></p>
                </div>
                <i class="fa-solid fa-chevron-down hidden text-[9px] text-slate-400 transition-transform sm:block" :class="open ? 'rotate-180' : ''"></i>
            </button>

            <div x-show="open" x-transition.origin.top.right
                class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-200/60">
                <div class="border-b border-slate-100 px-3 py-2.5">
                    <p class="text-[10px] uppercase tracking-wider text-slate-400">Signed in as</p>
                    <p class="mt-1 truncate text-xs font-semibold text-slate-700"><?php echo e(auth('admin')->user()->email); ?></p>
                </div>
                <div class="py-1">
                    <a href="<?php echo e(route('admin.profile')); ?>"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-50 hover:text-blue-600">
                        <i class="fa-solid fa-user-gear w-4 text-center"></i> Account Settings
                    </a>
                    <form action="<?php echo e(route('admin.logout')); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <button class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50 hover:text-rose-700">
                            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/layout/navbar.blade.php ENDPATH**/ ?>