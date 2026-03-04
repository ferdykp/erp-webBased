@props(['status'])

@php
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
@endphp

<span
    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest {{ $config['bg'] }} {{ $config['text'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }} {{ $status == 'pending' ? 'animate-pulse' : '' }}"></span>
    {{ $config['label'] }}
</span>
