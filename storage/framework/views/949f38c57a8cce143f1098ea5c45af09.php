<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status']));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $config = [
        'pending' => [
            'bg' => 'bg-amber-50',
            'text' => 'text-amber-600',
            'dot' => 'bg-amber-500',
            'label' => 'Unarrived',
        ],
        'approved' => [
            'bg' => 'bg-emerald-50',
            'text' => 'text-emerald-600',
            'dot' => 'bg-emerald-500',
            'label' => 'Arrived',
        ],
        'processing' => [
            'bg' => 'bg-blue-50',
            'text' => 'text-blue-600',
            'dot' => 'bg-blue-500',
            'label' => 'Processing',
        ],
        'completed' => [
            'bg' => 'bg-slate-100',
            'text' => 'text-slate-600',
            'dot' => 'bg-slate-500',
            'label' => 'Completed',
        ],
    ][$status] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'dot' => 'bg-gray-500', 'label' => 'Unknown'];
?>

<span
    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest <?php echo e($config['bg']); ?> <?php echo e($config['text']); ?>">
    <span class="w-1.5 h-1.5 rounded-full <?php echo e($config['dot']); ?> <?php echo e($status == 'pending' ? 'animate-pulse' : ''); ?>"></span>
    <?php echo e($config['label']); ?>

</span>
<?php /**PATH /Users/ferdy/project-fl/beamCustom/resources/views/components/status-badge.blade.php ENDPATH**/ ?>