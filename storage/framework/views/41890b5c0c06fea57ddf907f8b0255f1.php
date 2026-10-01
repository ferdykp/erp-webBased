<?php $__env->startSection('content'); ?>
    <div class="w-full space-y-6">

        
        <div class="flex items-center justify-center gap-4 mb-8">
            
            <div class="flex items-center gap-2">
                <span
                    class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white rounded-full shadow-lg bg-emerald-500 shadow-emerald-100">
                    <i class="fa-solid fa-check text-[10px]"></i>
                </span>
                <span class="text-sm font-bold text-gray-400">Pilih Tanggal</span>
            </div>

            <div class="w-12 h-px bg-emerald-200"></div>

            
            <div class="flex items-center gap-2">
                <span
                    class="flex items-center justify-center w-8 h-8 text-sm font-bold text-white bg-blue-600 rounded-full shadow-lg shadow-blue-200">2</span>
                <span class="text-sm font-bold text-gray-900">Pilih Sesi</span>
            </div>

            <div class="w-12 h-px bg-gray-200"></div>

            
            <div class="flex items-center gap-2">
                <span
                    class="flex items-center justify-center w-8 h-8 text-sm font-bold text-gray-400 bg-gray-100 rounded-full">3</span>
                <span class="text-sm font-bold text-gray-400">Isi Detail</span>
            </div>
        </div>

        <div class="bg-white shadow-sm border border-gray-100 rounded-[2rem] overflow-hidden">

            
            <div class="px-8 py-8 border-b border-gray-50 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h2 class="text-2xl font-black tracking-tight text-gray-900">
                            Pilih Sesi Sterilisasi
                        </h2>
                        <p class="mt-1 text-sm font-medium text-gray-400">
                            Tersedia untuk tanggal:
                            <span
                                class="font-black text-blue-600 uppercase"><?php echo e(\Carbon\Carbon::parse($date)->format('d F Y')); ?></span>
                        </p>
                    </div>
                    <div class="hidden md:block">
                        <i class="text-4xl text-blue-100 fa-solid fa-clock"></i>
                    </div>
                </div>
            </div>

            
            <div class="p-8">
                <?php if($slots->count() > 0): ?>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

                        <?php $__currentLoopData = $slots; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $available = $slot->capacity - $slot->booked_count;
                                $isFull = $available <= 0;
                            ?>

                            <a href="<?php echo e($isFull ? '#' : route('customer.booking.create', $slot->id)); ?>"
                                class="relative p-8 transition-all duration-300 bg-white border border-gray-100 group rounded-3xl 
                                <?php echo e($isFull ? 'opacity-50 cursor-not-allowed' : 'hover:shadow-2xl hover:shadow-blue-100 hover:border-blue-500 hover:-translate-y-1'); ?>">

                                
                                <div class="text-[11px] font-black text-blue-600 uppercase tracking-[0.2em] mb-2">
                                    Sesi <?php echo e($slot->session); ?>

                                </div>

                                
                                <div class="text-2xl font-black text-gray-900 transition-colors group-hover:text-blue-600">
                                    <?php echo e(\Carbon\Carbon::parse($slot->start_time)->format('H:i')); ?>

                                    <span class="text-sm font-bold text-gray-400 group-hover:text-blue-400">-</span>
                                    <?php echo e(\Carbon\Carbon::parse($slot->end_time)->format('H:i')); ?>

                                </div>

                                
                                <div class="flex items-center justify-between mt-6">
                                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kapasitas
                                        Sisa</span>
                                    <span
                                        class="px-3 py-1 text-[10px] font-black rounded-lg 
                                        <?php echo e($available > 5 ? 'bg-emerald-50 text-emerald-600' : ($available > 0 ? 'bg-amber-50 text-amber-600' : 'bg-red-50 text-red-600')); ?>">
                                        <?php echo e($available); ?> Slot
                                    </span>
                                </div>

                                <?php if($isFull): ?>
                                    <div
                                        class="absolute inset-0 flex items-center justify-center bg-white/20 backdrop-blur-[1px] rounded-3xl">
                                        <span
                                            class="px-4 py-1 text-[10px] font-black text-white uppercase bg-gray-900 rounded-lg">Full</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                    </div>
                <?php else: ?>
                    <div class="py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="flex items-center justify-center w-20 h-20 mb-4 rounded-full bg-gray-50">
                                <i class="text-3xl text-gray-200 fa-solid fa-hourglass-empty"></i>
                            </div>
                            <p class="font-bold text-gray-400">Tidak ada sesi tersedia untuk tanggal ini</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="flex items-center justify-between p-8 border-t border-gray-50 bg-gray-50/30">
                <a href="<?php echo e(route('customer.booking.date')); ?>"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-bold text-gray-500 transition-colors hover:text-gray-700 group">
                    <i class="transition-transform fa-solid fa-arrow-left group-hover:-translate-x-1"></i>
                    Ganti Tanggal
                </a>

                <p class="hidden sm:block text-[10px] font-bold text-gray-300 uppercase tracking-widest">
                    Step 2 of 3: Session Selection
                </p>
            </div>

        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/customer/booking/select_session.blade.php ENDPATH**/ ?>