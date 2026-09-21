<div id="warehouseModal"
    class="fixed inset-0 z-[150] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 md:p-6">
    <div
        class="bg-white w-full max-w-5xl rounded-[2rem] md:rounded-[3.5rem] shadow-2xl relative max-h-[95vh] flex flex-col overflow-hidden">

        
        <div class="px-6 py-6 border-b md:px-12 md:pt-12 md:pb-6 border-slate-50">
            <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between md:mb-8">
                <div>
                    <h3 class="text-xl font-black md:text-3xl text-slate-800">Check-in Process</h3>
                    <p class="text-[10px] md:text-xs font-medium tracking-widest uppercase text-slate-500">
                        Code: <span id="display_booking_code" class="text-blue-600"></span>
                    </p>
                </div>
                <button onclick="closeWarehouseModal()"
                    class="absolute p-3 transition-all top-6 right-6 md:static bg-slate-50 hover:bg-red-50 rounded-2xl group">
                    <i class="fa-solid fa-xmark text-slate-400 group-hover:text-red-500"></i>
                </button>
            </div>

            
            <div class="flex items-center justify-between max-w-2xl mx-auto">
                <?php $steps = [['Verify', 1], ['Batching', 2], ['Placement', 3]]; ?>
                <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex flex-col items-center gap-2 step-item <?php echo e($index == 0 ? 'active' : ''); ?>"
                        data-step="<?php echo e($step[1]); ?>">
                        <div
                            class="flex items-center justify-center w-8 h-8 text-xs font-bold transition-all rounded-full md:w-10 md:h-10 md:text-sm step-circle <?php echo e($index == 0 ? 'bg-blue-600 text-white shadow-lg shadow-blue-100' : 'bg-slate-100 text-slate-400'); ?>">
                            <?php echo e($step[1]); ?>

                        </div>
                        <span
                            class="text-[8px] md:text-[10px] font-black uppercase text-slate-400"><?php echo e($step[0]); ?></span>
                    </div>
                    <?php if(!$loop->last): ?>
                        <div class="flex-1 h-px mx-2 bg-slate-100 md:mx-4"></div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <form action="<?php echo e(route('admin.bookings.checkin')); ?>" method="POST" id="checkInForm"
            class="flex flex-col flex-1 overflow-hidden">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="booking_code" id="modal_booking_code">
            <input type="hidden" name="total_qty" id="hidden_total_qty">
            <input type="hidden" name="per_pallet" id="hidden_per_pallet">
            <input type="hidden" name="booking_id" id="modal_booking_id">

            <div class="flex-1 px-6 py-6 overflow-y-auto md:px-12 scrollbar-hide">

                
                <div class="step-content" id="step1">
                    <div class="p-6 md:p-10 bg-blue-50/40 border border-blue-100 rounded-[2rem] md:rounded-[3rem] mb-8">
                        <h4 class="mb-6 text-sm font-black md:text-lg text-slate-800">Matched Actual Data</h4>
                        <div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-4 md:gap-6">
                            <?php
                                $infoFields = [
                                    ['Product', 'check_product_name', 'text-slate-800'],
                                    ['Category', 'check_product_type', 'text-slate-600'],
                                    ['Booked Qty', 'check_qty', 'text-slate-800', 'check_unit'],
                                    ['Exp. Temp', 'check_temp', 'text-blue-600', '°C'],
                                ];
                            ?>
                            <?php $__currentLoopData = $infoFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div>
                                    <p
                                        class="text-[8px] md:text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                        <?php echo e($field[0]); ?></p>
                                    <p id="<?php echo e($field[1]); ?>"
                                        class="text-xs font-black md:text-base <?php echo e($field[2]); ?> truncate">-</p>
                                    <?php if(isset($field[3])): ?>
                                        <span id="<?php echo e($field[3]); ?>"
                                            class="text-[10px] font-bold text-slate-500"></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <div class="grid grid-cols-1 gap-4 pt-6 border-t border-blue-100/50 sm:grid-cols-2 md:gap-6">
                            <div>
                                <p
                                    class="text-[8px] md:text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Dose Range</p>
                                <p class="text-xs font-black md:text-sm text-emerald-600"><span id="check_dmin">-</span>
                                    - <span id="check_dmax">-</span> kGy</p>
                            </div>
                            <div>
                                <p
                                    class="text-[8px] md:text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                    Dimension / Pack</p>
                                <p class="text-xs font-bold md:text-sm text-slate-700"><span
                                        id="check_dimension">-</span> Cm</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 md:gap-6">
                        <?php
                            $inputs = [
                                [
                                    'vol_per_pcs',
                                    'Product Volume / pcs',
                                    'fa-box-open',
                                    'cm',
                                    'readonly',
                                    'ci_vol_per_pcs',
                                ],
                                ['vol_total', 'Total Volume', 'fa-tags', 'cm', 'readonly', 'ci_vol_total'],
                                [
                                    'net_weight_pcs',
                                    'Net Weight / pcs',
                                    'fa-weight-hanging',
                                    'kg',
                                    '',
                                    'ci_net_weight_pcs',
                                ],
                                [
                                    'total_net_weight',
                                    'Total Net Weight',
                                    'fa-calculator',
                                    'kg',
                                    'readonly',
                                    'ci_total_net_weight',
                                ],
                                [
                                    'gross_weight_pcs',
                                    'Gross Weight / pcs',
                                    'fa-weight-hanging',
                                    'kg',
                                    '',
                                    'ci_gross_weight_pcs',
                                ],
                                [
                                    'total_gross_weight',
                                    'Total Gross Weight',
                                    'fa-calculator',
                                    'kg',
                                    'readonly',
                                    'ci_total_gross_weight',
                                ],
                            ];
                        ?>
                        <?php $__currentLoopData = $inputs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $input): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="space-y-1.5">
                                <label
                                    class="text-[9px] md:text-[10px] font-black text-gray-500 uppercase tracking-widest ml-1"><?php echo e($input[1]); ?></label>
                                <div class="relative">
                                    <i
                                        class="absolute text-gray-400 -translate-y-1/2 left-4 top-1/2 fa-solid <?php echo e($input[2]); ?> text-xs"></i>
                                    <input type="text" name="<?php echo e($input[0]); ?>" id="<?php echo e($input[5]); ?>"
                                        <?php echo e($input[4]); ?>

                                        class="w-full pl-10 pr-10 py-3 md:py-3.5 text-sm bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-500 transition-all outline-none font-bold text-gray-700">
                                    <span
                                        class="absolute text-[10px] font-bold text-gray-400 -translate-y-1/2 right-4 top-1/2"><?php echo e($input[3]); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="mt-8">
                        <label
                            class="flex items-start gap-4 p-5 transition-all border-2 cursor-pointer border-slate-50 bg-slate-50/30 rounded-3xl hover:border-blue-500 group">
                            <input type="checkbox" required
                                class="w-5 h-5 mt-0.5 text-blue-600 transition-all border-gray-200 rounded-lg focus:ring-blue-500">
                            <span
                                class="text-[10px] md:text-xs font-bold leading-relaxed text-slate-600 group-hover:text-slate-800">
                                I confirm that the physical data that arrives is in accordance with the technical
                                specifications
                                above.
                            </span>
                        </label>
                    </div>
                </div>

                
                <div class="hidden step-content" id="step2">
                    <div class="grid grid-cols-1 gap-4 mb-8 sm:grid-cols-2 md:gap-6">
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">
                                PIC Warehouse
                            </label>
                            <select name="pic_warehouse" required
                                class="w-full px-5 py-3.5 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500 text-gray-700">
                                <option value="">Choose PIC Warehouse</option>
                                <?php $__currentLoopData = $warehousePics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($pic->name); ?>"><?php echo e($pic->name); ?> (Shift:
                                        <?php echo e($pic->shift ?? '-'); ?>)</option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-gray-500 ml-1">Porter
                                Team</label>
                            <div id="porterContainer" class="space-y-2">
                                <select name="porter_name"
                                    class="w-full px-5 py-3.5 text-sm font-bold border-none bg-slate-50 rounded-2xl focus:ring-2 focus:ring-blue-500">
                                    <option value="">Choose Porter</option>
                                    <?php $__currentLoopData = $porters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($p->name); ?>"><?php echo e($p->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div
                        class="p-6 md:p-8 mb-8 border border-slate-100 bg-slate-50/50 rounded-[2rem] md:rounded-[2.5rem]">
                        <h4 class="mb-6 text-[10px] md:text-sm font-black tracking-widest uppercase text-slate-400">
                            Pallet Planning (Auto Calculation)</h4>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 md:gap-6">
                            <div class="space-y-1">
                                <label class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase ml-1">Qty
                                    per Palet</label>
                                <input type="number" id="per_pallet" min="1"
                                    class="w-full px-5 py-3.5 text-sm font-bold bg-white border-none shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase ml-1">Number
                                    of Pallets</label>
                                <input type="number" id="pallet_count" min="1"
                                    class="w-full px-5 py-3.5 text-sm font-bold bg-white border-none shadow-sm rounded-2xl focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="text-[9px] md:text-[10px] font-black text-slate-500 uppercase ml-1">Remaining
                                    Qty</label>
                                <input type="number" id="pallet_remainder" readonly
                                    class="w-full px-5 py-3.5 text-sm font-bold border-none bg-slate-100 rounded-2xl text-slate-500">
                            </div>
                        </div>

                        <div id="pallet_summary"
                            class="hidden p-5 mt-8 bg-white border shadow-sm border-emerald-100 rounded-3xl">
                            <h5
                                class="text-[9px] md:text-xs font-black tracking-widest uppercase text-emerald-600 mb-4">
                                Distribution Logic</h5>
                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <?php $__currentLoopData = ['Total Qty' => 'sum_qty', 'Pallets' => 'sum_pallet', 'Isi/Pallet' => 'sum_per_pallet', 'Sisa' => 'sum_remainder']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $id): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="p-3 bg-slate-50 rounded-xl">
                                        <p class="text-[8px] font-black text-slate-400 uppercase mb-0.5">
                                            <?php echo e($label); ?></p>
                                        <p id="<?php echo e($id); ?>" class="text-xs font-black text-slate-800">0</p>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    </div>
                    <div id="batchContainer" class="space-y-3"></div>
                </div>

                
                

                
                <div class="hidden step-content" id="step3">
                    <div class="flex flex-col gap-4 mb-6 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h4 class="text-xs font-black tracking-widest uppercase md:text-sm text-slate-800">Assign
                                Location</h4>
                            <p class="mt-1 text-[10px] md:text-xs font-bold text-slate-400">Group placements
                                palettes based on Line and Grid.</p>
                        </div>
                        
                        <div class="p-3 text-right border border-blue-100 bg-blue-50 rounded-2xl">
                            <p class="text-[10px] font-black uppercase text-slate-400">Alocation Placement Status</p>
                            <p class="text-xs font-black text-slate-700">
                                <span id="allocated_pallets_display" class="text-blue-600">0</span> / <span
                                    id="total_pallets_needed_display">0</span> Palet
                            </p>
                        </div>
                    </div>

                    
                    <div id="placementContainer" class="space-y-3 md:space-y-4"></div>

                    
                    <div class="mt-4">
                        <button type="button" onclick="addPlacementRowGroup()"
                            class="px-4 py-2.5 text-xs font-black text-blue-600 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition-all">
                            <i class="mr-1 fa-solid fa-plus"></i> Add Location Placement
                        </button>
                    </div>
                </div>
            </div>

            
            <div class="flex flex-col gap-3 px-6 py-6 border-t md:px-12 bg-slate-50/50 sm:flex-row sm:gap-4 md:py-8">
                <button type="button" id="prevBtn" onclick="changeStep(-1)"
                    class="flex-1 order-2 py-4 text-[10px] md:text-xs font-black uppercase transition-all bg-white border sm:order-1 text-slate-500 border-slate-200 rounded-2xl md:rounded-3xl hover:bg-slate-100">
                    <i class="mr-2 fa-solid fa-arrow-left"></i> Previous
                </button>
                <button type="button" id="nextBtn" onclick="changeStep(1)"
                    class="flex-[2] order-1 py-4 text-[10px] md:text-xs font-black text-white uppercase bg-blue-600 shadow-xl shadow-blue-100 sm:order-2 rounded-2xl md:rounded-3xl hover:bg-blue-700 transition-all">
                    Continue <i class="ml-2 fa-solid fa-arrow-right"></i>
                </button>
                <button type="submit" id="finalSubmitBtn"
                    class="flex-[2] order-1 hidden py-4 text-[10px] md:text-xs font-black text-white uppercase bg-emerald-500 sm:order-2 rounded-2xl md:rounded-3xl shadow-xl shadow-emerald-100">
                    Confirm & Complete Check-in
                </button>
            </div>
        </form>
    </div>
</div>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/bookings/partials/checkin-modal.blade.php ENDPATH**/ ?>