<?php $__env->startSection('title', $test ? 'Edit Product Test' : 'New Product Test'); ?>

<?php $__env->startSection('content'); ?>
    <div class="w-full mx-auto space-y-5 max-w-none sm:space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
            <div>
                <a href="<?php echo e(route('admin.testing.index')); ?>"
                    class="inline-flex items-center gap-2 text-xs font-semibold transition-colors text-slate-500 hover:text-blue-600">
                    <i class="fa-solid fa-arrow-left"></i>
                    Product Testing
                </a>

                <div class="flex items-start gap-4 mt-3">
                    <div
                        class="items-center justify-center hidden w-12 h-12 text-lg text-blue-600 border border-blue-100 shrink-0 rounded-xl bg-blue-50 sm:flex">
                        <i class="fa-solid fa-flask-vial"></i>
                    </div>

                    <div>
                        <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-600">
                            Technical Test Record
                        </p>

                        <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                            <?php echo e($test ? 'Edit Product Test' : 'Create Product Test'); ?>

                        </h1>

                        <p class="max-w-3xl mt-2 text-sm leading-6 text-slate-500">
                            Catat data minimum yang dibutuhkan untuk trial, research, dose mapping, atau Operational
                            Qualification. Product Testing tidak mengikuti alur booking customer dan warehouse check-in.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        
        <div
            class="flex items-center px-4 py-3 overflow-x-auto bg-white border shadow-sm rounded-2xl border-slate-200 sm:px-5">

            <div class="flex shrink-0 items-center gap-2.5 text-slate-900">
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-600 bg-blue-600 text-[11px] font-bold text-white">
                    1
                </span>

                <div>
                    <b class="block text-[11px] font-bold text-slate-900">Test Data</b>
                    <small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">
                        Sample & requester
                    </small>
                </div>
            </div>

            <div class="flex-1 h-px mx-3 min-w-8 bg-slate-200 sm:mx-5"></div>

            <div class="flex shrink-0 items-center gap-2.5 text-slate-400">
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-bold">
                    2
                </span>

                <div>
                    <b class="block text-[11px] font-bold text-slate-500">Process Parameter</b>
                    <small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">
                        Unit & irradiation setting
                    </small>
                </div>
            </div>

            <div class="flex-1 h-px mx-3 min-w-8 bg-slate-200 sm:mx-5"></div>

            <div class="flex shrink-0 items-center gap-2.5 text-slate-400">
                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-bold">
                    3
                </span>

                <div>
                    <b class="block text-[11px] font-bold text-slate-500">Report</b>
                    <small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">
                        Absorbance & result
                    </small>
                </div>
            </div>
        </div>

        
        <?php if($errors->any()): ?>
            <div class="flex gap-3 px-4 py-3 text-xs leading-5 border rounded-xl border-rose-200 bg-rose-50 text-rose-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

                <div>
                    <p class="font-bold">Please check the form.</p>

                    <ul class="pl-5 mt-1 space-y-1 list-disc">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e($test ? route('admin.testing.update', $test) : route('admin.testing.store')); ?>"
            class="space-y-5" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            <?php if($test): ?>
                <?php echo method_field('PUT'); ?>
            <?php endif; ?>

            
            <section class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 sm:p-6">

                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                            01 · Identification
                        </p>

                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            Requester & Test Identity
                        </h2>

                        <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">
                            Requester tidak harus terdaftar sebagai customer dan tidak harus berasal dari perusahaan.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5 2xl:grid-cols-4">

                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Requester / Researcher
                        </span>

                        <input name="requester_name" value="<?php echo e(old('requester_name', $test?->requester_name)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Nama orang, PIC, atau internal team">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Optional — dapat dikosongkan untuk internal OQ.
                        </span>
                    </label>

                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Institution / Company
                        </span>

                        <input name="requester_organization"
                            value="<?php echo e(old('requester_organization', $test?->requester_organization)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Universitas, lab, perusahaan, atau internal">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Optional.
                        </span>
                    </label>

                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Contact
                        </span>

                        <input name="requester_contact" value="<?php echo e(old('requester_contact', $test?->requester_contact)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Phone / email / reference">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Optional.
                        </span>
                    </label>

                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Test / Sample Name
                            <em class="not-italic text-rose-500">*</em>
                        </span>

                        <input name="sample_name" required value="<?php echo e(old('sample_name', $test?->sample_name)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="e.g. OQ Conveyor Speed / Packaging Sample A">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Satu-satunya field wajib pada tahap ini.
                        </span>
                    </label>
                </div>
            </section>


            
            <section class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 sm:p-6">

                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                            02 · Sample Data
                        </p>

                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            Product / Sample Detail
                        </h2>

                        <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">
                            Seluruh data berikut optional. Kosongkan bila test hanya mencari parameter/dose
                            atau untuk Operational Qualification.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5 2xl:grid-cols-4">

                    
                    <div class="grid grid-cols-[minmax(0,1fr)_minmax(110px,0.55fr)] gap-3">
                        <label class="block min-w-0">
                            <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                                Quantity
                            </span>

                            <input type="number" min="1" step="1" name="quantity"
                                value="<?php echo e(old('quantity', $test?->quantity)); ?>"
                                class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                placeholder="Optional">
                        </label>

                        <label class="block min-w-0">
                            <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                                Unit
                            </span>

                            <input name="unit" value="<?php echo e(old('unit', $test?->unit)); ?>"
                                class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                placeholder="pcs / box">
                        </label>
                    </div>

                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Expected Temperature
                        </span>

                        <input name="expected_temperature"
                            value="<?php echo e(old('expected_temperature', $test?->expected_temperature)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="e.g. 25°C">
                    </label>


                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Reference Minimum Dose (kGy)
                        </span>

                        <input type="number" min="0" step="0.0001" name="dmin" id="dmin"
                            value="<?php echo e(old('dmin', $test?->dmin)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">
                    </label>


                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Reference Maximum Dose (kGy)
                        </span>

                        <input type="number" min="0" step="0.0001" name="dmax" id="dmax"
                            value="<?php echo e(old('dmax', $test?->dmax)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">
                    </label>


                    
                    <div class="p-4 border border-blue-100 rounded-xl bg-blue-50/50 md:col-span-2 2xl:col-span-4">

                        <div class="flex items-start gap-3">
                            <div
                                class="flex items-center justify-center text-blue-600 bg-white border border-blue-100 rounded-lg w-9 h-9 shrink-0">
                                <i class="fa-solid fa-calculator"></i>
                            </div>

                            <div class="min-w-0">
                                <p class="text-[10px] font-extrabold uppercase tracking-[0.11em] text-blue-600">
                                    Dose Fluctuation Calculation
                                </p>

                                <h3 class="mt-1 text-sm font-bold text-slate-900">
                                    Derajat Fluktuasi
                                </h3>

                                <p class="mt-1 text-[11px] leading-5 text-slate-500">
                                    Nilai dihitung secara otomatis berdasarkan Reference Minimum Dose
                                    dan Reference Maximum Dose.
                                </p>
                            </div>
                        </div>


                        <div class="grid grid-cols-1 gap-3 mt-4 lg:grid-cols-3">

                            
                            <div class="p-3 bg-white border rounded-xl border-slate-200">
                                <span class="block text-[9px] font-bold uppercase tracking-[0.11em] text-slate-400">
                                    Formula
                                </span>

                                <p class="mt-2 text-sm font-semibold leading-6 text-slate-700">
                                    <span class="font-bold">
                                        Fluctuation
                                    </span>

                                    <span class="mx-1">
                                        =
                                    </span>

                                    <span class="font-mono text-xs">
                                        (Dmax − Dmin) / (Dmax + Dmin)
                                    </span>
                                </p>
                            </div>


                            
                            <div class="p-3 bg-white border rounded-xl border-slate-200">
                                <span class="block text-[9px] font-bold uppercase tracking-[0.11em] text-slate-400">
                                    Fluctuation Index
                                </span>

                                <span id="fluctuation-result"
                                    class="block mt-1 text-2xl font-extrabold tracking-tight text-blue-600">
                                    —
                                </span>

                                <span class="block mt-1 text-[10px] text-slate-400">
                                    Dimensionless index
                                </span>
                            </div>


                            
                            <div class="p-3 bg-white border rounded-xl border-slate-200">
                                <span class="block text-[9px] font-bold uppercase tracking-[0.11em] text-slate-400">
                                    Fluctuation Percentage
                                </span>

                                <span id="fluctuation-percentage"
                                    class="block mt-1 text-2xl font-extrabold tracking-tight text-blue-600">
                                    —
                                </span>

                                <span class="block mt-1 text-[10px] text-slate-400">
                                    Relative variation
                                </span>
                            </div>
                        </div>


                        
                        <div id="fluctuation-status"
                            class="hidden p-3 mt-3 text-[10px] leading-5 border rounded-xl border-slate-200 bg-white text-slate-500">
                        </div>
                    </div>


                    
                    <div class="block min-w-0 md:col-span-2">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Dimension P × L × T (cm)
                        </span>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                            <label>
                                <span
                                    class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">
                                    P
                                </span>

                                <input type="number" min="0" step="0.001" name="length_cm"
                                    value="<?php echo e(old('length_cm', $test?->length_cm)); ?>"
                                    class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                    placeholder="Length">
                            </label>

                            <label>
                                <span
                                    class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">
                                    L
                                </span>

                                <input type="number" min="0" step="0.001" name="width_cm"
                                    value="<?php echo e(old('width_cm', $test?->width_cm)); ?>"
                                    class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                    placeholder="Width">
                            </label>

                            <label>
                                <span
                                    class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">
                                    T
                                </span>

                                <input type="number" min="0" step="0.001" name="height_cm"
                                    value="<?php echo e(old('height_cm', $test?->height_cm)); ?>"
                                    class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                    placeholder="Height">
                            </label>
                        </div>
                    </div>


                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Net Weight (kg)
                        </span>

                        <input type="number" min="0" step="0.0001" name="net_weight_kg"
                            value="<?php echo e(old('net_weight_kg', $test?->net_weight_kg)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">
                    </label>


                    
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Gross Weight (kg)
                        </span>

                        <input type="number" min="0" step="0.0001" name="gross_weight_kg"
                            value="<?php echo e(old('gross_weight_kg', $test?->gross_weight_kg)); ?>"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">
                    </label>
                </div>
            </section>


            
            <section class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 sm:p-6">

                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                            03 · Notes
                        </p>

                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            Additional Notes
                        </h2>

                        <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">
                            Gunakan untuk tujuan singkat, kondisi khusus, setup awal, atau catatan penelitian.
                            Optional.
                        </p>
                    </div>
                </div>

                <label class="block min-w-0">
                    <textarea name="notes" rows="5"
                        class="w-full min-w-0 resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                        placeholder="Optional notes..."><?php echo e(old('notes', $test?->notes)); ?></textarea>
                </label>
            </section>


            
            <section class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 sm:p-6">

                <div class="flex items-start justify-between gap-4 mb-5">
                    <div>
                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                            04 · Documentation
                        </p>

                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            Sample / Test Image
                        </h2>

                        <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">
                            Unggah foto sampel atau dokumentasi pengujian jika diperlukan.
                            Opsional (Format: JPG, PNG, WEBP, maks. 5MB).
                        </p>
                    </div>
                </div>

                <div class="space-y-4">

                    <?php if($test?->image): ?>
                        <div class="flex items-center gap-4 p-3 border rounded-xl border-slate-200 bg-slate-50 w-fit">

                            <img src="<?php echo e(asset('storage/' . $test->image)); ?>" alt="Preview"
                                class="object-cover w-16 h-16 border rounded-lg border-slate-200">

                            <div>
                                <span class="block text-xs font-bold text-slate-700">
                                    Current Image
                                </span>

                                <span class="text-[10px] text-slate-400">
                                    Gambar saat ini akan tetap digunakan jika tidak mengunggah yang baru.
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <input type="file" name="image" accept="image/png, image/jpeg, image/jpg, image/webp"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
            </section>


            
            <div
                class="flex flex-col gap-4 p-4 bg-white border shadow-sm rounded-2xl border-slate-200 sm:flex-row sm:items-center sm:justify-between">

                <div class="text-xs text-slate-500">
                    <i class="fa-solid fa-circle-info mr-1.5 text-blue-500"></i>
                    Setelah disimpan, Anda langsung masuk ke Process Parameter — tanpa warehouse check-in.
                </div>

                <div class="flex w-full flex-col-reverse gap-2.5 sm:w-auto sm:flex-row">

                    <a href="<?php echo e(route('admin.testing.index')); ?>"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]">
                        Cancel
                    </a>

                    <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]">
                        <?php echo e($test ? 'Save & Continue' : 'Continue to Process Parameter'); ?>


                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
<?php $__env->stopSection(); ?>


<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dminInput = document.getElementById('dmin');
            const dmaxInput = document.getElementById('dmax');

            const resultElement = document.getElementById('fluctuation-result');
            const percentageElement = document.getElementById('fluctuation-percentage');
            const statusElement = document.getElementById('fluctuation-status');

            if (
                !dminInput ||
                !dmaxInput ||
                !resultElement ||
                !percentageElement ||
                !statusElement
            ) {
                return;
            }

            function calculateFluctuation() {
                const dminRaw = dminInput.value.trim();
                const dmaxRaw = dmaxInput.value.trim();

                const dmin = parseFloat(dminRaw);
                const dmax = parseFloat(dmaxRaw);

                // Reset
                resultElement.textContent = '—';
                percentageElement.textContent = '—';

                statusElement.classList.add('hidden');
                statusElement.textContent = '';

                // Salah satu belum diisi
                if (dminRaw === '' || dmaxRaw === '') {
                    return;
                }

                // Nilai bukan angka
                if (!Number.isFinite(dmin) || !Number.isFinite(dmax)) {
                    return;
                }

                // Nilai negatif
                if (dmin < 0 || dmax < 0) {
                    statusElement.textContent =
                        'Nilai dose tidak boleh negatif.';

                    statusElement.classList.remove('hidden');
                    return;
                }

                const denominator = dmax + dmin;

                // Hindari division by zero
                if (denominator === 0) {
                    statusElement.textContent =
                        'Fluktuasi tidak dapat dihitung karena Dmax + Dmin = 0.';

                    statusElement.classList.remove('hidden');
                    return;
                }

                const fluctuation = (dmax - dmin) / denominator;
                const percentage = fluctuation * 100;

                // Hasil utama
                resultElement.textContent = fluctuation.toFixed(4);

                percentageElement.textContent =
                    percentage.toFixed(2) + '%';

                // Detail perhitungan
                statusElement.innerHTML = `
            <span class="font-bold text-slate-700">
                Calculation:
            </span>
            (${dmax} − ${dmin}) / (${dmax} + ${dmin})
            =
            <span class="font-bold text-blue-600">
                ${fluctuation.toFixed(4)}
            </span>
            =
            <span class="font-bold text-blue-600">
                ${percentage.toFixed(2)}%
            </span>
        `;

                statusElement.classList.remove('hidden');
            }

            dminInput.addEventListener('input', calculateFluctuation);
            dmaxInput.addEventListener('input', calculateFluctuation);

            // Hitung otomatis ketika edit form dibuka
            calculateFluctuation();
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.layout.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/testing/form.blade.php ENDPATH**/ ?>