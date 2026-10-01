<?php
    $product = $booking->products->first();
    $contact = $booking->customer?->contacts?->first();
?>

<div class="space-y-5">
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-4 bg-slate-50 rounded-2xl">
            <p class="text-[9px] font-black tracking-widest uppercase text-slate-400">Booking Code</p>
            <p class="mt-1 text-sm font-black text-slate-800">#<?php echo e($booking->booking_code); ?></p>
        </div>
        <div class="p-4 bg-slate-50 rounded-2xl">
            <p class="text-[9px] font-black tracking-widest uppercase text-slate-400">Type</p>
            <p class="mt-1 text-sm font-black text-slate-800"><?php echo e($booking->booking_type === 'test' ? 'Product Test' : 'Regular Order'); ?></p>
        </div>
        <div class="p-4 bg-slate-50 rounded-2xl">
            <p class="text-[9px] font-black tracking-widest uppercase text-slate-400">Status</p>
            <p class="mt-1 text-sm font-black capitalize text-slate-800"><?php echo e($booking->status); ?></p>
        </div>
        <div class="p-4 bg-slate-50 rounded-2xl">
            <p class="text-[9px] font-black tracking-widest uppercase text-slate-400">Arrival</p>
            <p class="mt-1 text-sm font-black text-slate-800"><?php echo e($booking->arrival_time?->format('d M Y H:i') ?? '-'); ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <section class="p-5 border border-slate-100 rounded-2xl">
            <h3 class="text-xs font-black tracking-widest uppercase text-slate-500">Customer</h3>
            <p class="mt-3 font-black text-slate-800"><?php echo e($booking->customer?->company_name ?? '-'); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e($contact?->name ?? '-'); ?> · <?php echo e($contact?->phone ?? '-'); ?></p>
        </section>
        <section class="p-5 border border-slate-100 rounded-2xl">
            <h3 class="text-xs font-black tracking-widest uppercase text-slate-500">Product</h3>
            <p class="mt-3 font-black text-slate-800"><?php echo e($product?->product_name ?? '-'); ?></p>
            <p class="mt-1 text-xs text-slate-500"><?php echo e(\App\Support\NumberFormatter::integer($product?->quantity ?? 0)); ?> <?php echo e($product?->unit ?? ''); ?> · <?php echo e($product?->dmin !== null ? \App\Support\NumberFormatter::smart($product->dmin, 4) : '-'); ?>–<?php echo e($product?->dmax !== null ? \App\Support\NumberFormatter::smart($product->dmax, 4) : '-'); ?> kGy</p>
        </section>
    </div>

    <?php if($booking->booking_type === 'test'): ?>
        <section class="p-5 border border-violet-100 bg-violet-50/50 rounded-2xl">
            <h3 class="text-xs font-black tracking-widest uppercase text-violet-600">Test Objective</h3>
            <p class="mt-2 text-sm leading-6 text-slate-700"><?php echo e($booking->test_objective ?: '-'); ?></p>
            <?php if($booking->test_notes): ?>
                <p class="mt-3 text-xs leading-5 text-slate-500"><?php echo e($booking->test_notes); ?></p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</div>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/admin/bookings/partials/detail-content.blade.php ENDPATH**/ ?>