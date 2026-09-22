@extends('admin.layout.app')

@section('title', 'Product Test Report')

@section('content')
@php
    $format = fn($value, $decimals = 4) => \App\Support\NumberFormatter::smart($value, $decimals);
@endphp
<div class="mx-auto w-full max-w-none space-y-5 print:m-0 print:max-w-none print:space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-4 print:hidden sm:flex-row sm:items-end sm:justify-between">
        <div>
            <a href="{{ route('admin.testing.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition-colors hover:text-blue-600"><i class="fa-solid fa-arrow-left"></i> Product Testing</a>
            <p class="mt-3 text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-600">{{ $test->test_code }}</p>
            <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Product Test Report</h1>
            <p class="mt-2 text-sm leading-6 text-slate-500">Technical record, process parameters, dosimeter absorbance, and calculated dose.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.testing.edit', $test) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]"><i class="fa-solid fa-pen"></i> Edit Data</a>
            <a href="{{ route('admin.testing.parameters', $test) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]"><i class="fa-solid fa-sliders"></i> Process Parameter</a>
            <button onclick="window.print()" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]"><i class="fa-solid fa-print"></i> Print / Save PDF</button>
        </div>
    </div>

    <div class="flex items-center overflow-x-auto rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm print:hidden sm:px-5">
        <div class="flex shrink-0 items-center gap-2.5 text-slate-700"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-500 bg-emerald-500 text-[11px] font-bold text-white"><i class="fa-solid fa-check"></i></span><div><b class="block text-[11px] font-bold text-slate-700">Test Data</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Saved</small></div></div>
        <div class="mx-3 h-px min-w-8 flex-1 bg-emerald-300 sm:mx-5"></div>
        <div class="flex shrink-0 items-center gap-2.5 text-slate-700"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-500 bg-emerald-500 text-[11px] font-bold text-white"><i class="fa-solid fa-check"></i></span><div><b class="block text-[11px] font-bold text-slate-700">Process Parameter</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Completed</small></div></div>
        <div class="mx-3 h-px min-w-8 flex-1 bg-emerald-300 sm:mx-5"></div>
        <div class="flex shrink-0 items-center gap-2.5 text-slate-900"><span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-600 bg-blue-600 text-[11px] font-bold text-white">3</span><div><b class="block text-[11px] font-bold text-slate-900">Report</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Result & dosimeter</small></div></div>
    </div>

    <div class="hidden items-center justify-between border-b-2 border-slate-900 pb-5 print:flex">
        <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">E-Beam Technical Report</p><h1 class="mt-1 text-2xl font-black">Product Testing / Qualification</h1></div>
        <div class="text-right"><p class="font-black">{{ $test->test_code }}</p><p class="text-xs text-slate-500">{{ $test->processed_at?->format('d M Y H:i') ?: $test->updated_at->format('d M Y H:i') }}</p></div>
    </div>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div><p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">Test Information</p><h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">{{ $test->sample_name }}</h2></div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-bold text-emerald-700"><i class="fa-solid fa-circle-check"></i> Completed</span>
            </div>
            <dl class="grid grid-cols-1 gap-x-6 sm:grid-cols-2">
                @foreach ([
                    ['Test Code', $test->test_code],
                    ['Requester', $test->requester_name ?: '-'],
                    ['Institution / Company', $test->requester_organization ?: '-'],
                    ['Contact', $test->requester_contact ?: '-'],
                    ['Quantity', $test->quantity !== null ? \App\Support\NumberFormatter::integer($test->quantity).' '.($test->unit ?: '') : '-'],
                    ['Reference Dose', ($test->dmin !== null ? $format($test->dmin) : '-').($test->dmax !== null ? ' – '.$format($test->dmax) : '').(($test->dmin !== null || $test->dmax !== null) ? ' kGy' : '')],
                    ['Dimension P × L × T', $test->dimension_label],
                    ['Expected Temperature', $test->expected_temperature ?: '-'],
                    ['Net Weight', $test->net_weight_kg !== null ? $format($test->net_weight_kg).' kg' : '-'],
                    ['Gross Weight', $test->gross_weight_kg !== null ? $format($test->gross_weight_kg).' kg' : '-'],
                ] as [$label, $value])
                    <div class="border-b border-slate-100 py-3"><dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</dt><dd class="mt-1 text-xs font-semibold leading-5 text-slate-700">{{ $value }}</dd></div>
                @endforeach
            </dl>
            @if($test->notes)
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4"><span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Notes</span><p class="mt-1.5 whitespace-pre-line text-xs leading-5 text-slate-600">{{ $test->notes }}</p></div>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div><p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">Irradiation Setup</p><h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">Process Parameter</h2></div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500"><i class="fa-solid fa-sliders"></i></div>
            </div>
            <dl class="grid grid-cols-1 gap-x-6 sm:grid-cols-2">
                @foreach ([
                    ['E-Beam Unit', $test->productionLine?->name ?: '-'],
                    ['Beam Speed', $test->beam_speed !== null ? $format($test->beam_speed).' m/s' : '-'],
                    ['Target Dose', $test->target_dose !== null ? $format($test->target_dose).' kGy' : 'Not specified'],
                    ['Loading Mode', $test->loading_mode ? ucwords(str_replace('-', ' ', $test->loading_mode)) : '-'],
                    ['Frequency', $test->freq !== null ? $format($test->freq).' Hz' : '-'],
                    ['Scan Gear', $test->scan_gear !== null ? $format($test->scan_gear) : '-'],
                    ['Processed At', $test->processed_at?->format('d M Y, H:i') ?: '-'],
                ] as [$label, $value])
                    <div class="border-b border-slate-100 py-3"><dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">{{ $label }}</dt><dd class="mt-1 text-xs font-semibold leading-5 text-slate-700">{{ $value }}</dd></div>
                @endforeach
            </dl>
            @if($test->process_notes)
                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4"><span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Process Notes</span><p class="mt-1.5 whitespace-pre-line text-xs leading-5 text-slate-600">{{ $test->process_notes }}</p></div>
            @endif
        </div>
    </section>

    @if($test->dosimeters->count())
    <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="flex min-h-24 items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/30 print:break-inside-avoid print:shadow-none sm:p-5"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"><i class="fa-solid fa-arrow-down"></i></div><div><span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Minimum Measured Dose</span><strong class="mt-1 block text-xl font-extrabold tracking-tight text-slate-900">{{ $doseStats['min'] !== null ? $format($doseStats['min'], 4).' kGy' : '-' }}</strong></div></div>
        <div class="flex min-h-24 items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/30 print:break-inside-avoid print:shadow-none sm:p-5"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fa-solid fa-chart-line"></i></div><div><span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Average Measured Dose</span><strong class="mt-1 block text-xl font-extrabold tracking-tight text-slate-900">{{ $doseStats['avg'] !== null ? $format($doseStats['avg'], 4).' kGy' : '-' }}</strong></div></div>
        <div class="flex min-h-24 items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm shadow-slate-200/30 print:break-inside-avoid print:shadow-none sm:p-5"><div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600"><i class="fa-solid fa-arrow-up"></i></div><div><span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Maximum Measured Dose</span><strong class="mt-1 block text-xl font-extrabold tracking-tight text-slate-900">{{ $doseStats['max'] !== null ? $format($doseStats['max'], 4).' kGy' : '-' }}</strong></div></div>
    </section>
    @endif

    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6" x-data="dosimeterEditor(@js($test->dosimeters->map(fn($d) => ['dosimeter_number' => $d->dosimeter_number, 'position' => $d->position, 'absorbance' => $d->absorbance])->values()))">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div><p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">Measurement Result</p><h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">Dosimeter & Absorbance</h2><p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">Optional. Tambahkan hanya bila pengujian memakai dosimeter. Dose dihitung otomatis menggunakan kurva kalibrasi yang sama dengan modul Dosimeter.</p></div>
            <button type="button" @click="addRow()" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99] print:hidden"><i class="fa-solid fa-plus"></i> Add Reading</button>
        </div>

        <form method="POST" action="{{ route('admin.testing.dosimeters.update', $test) }}">
            @csrf
            @method('PUT')
            <div class="overflow-x-auto rounded-2xl border border-slate-200">
                <table class="min-w-[760px] w-full border-collapse bg-white text-left">
                    <thead class="bg-slate-50"><tr><th class="w-16 border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">#</th><th class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Dosimeter ID</th><th class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Position</th><th class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Absorbance</th><th class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">Calculated Dose (kGy)</th><th class="w-16 border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400 print:hidden"></th></tr></thead>
                    <tbody>
                        <template x-for="(row, index) in rows" :key="row.key">
                            <tr>
                                <td class="border-b border-slate-100 px-4 py-3 text-xs font-bold text-slate-500" x-text="index + 1"></td>
                                <td class="border-b border-slate-100 px-4 py-3 text-xs text-slate-600"><input :name="`readings[${index}][dosimeter_number]`" x-model="row.dosimeter_number" class="w-full rounded-lg border border-transparent bg-slate-50 px-3 py-2.5 text-base font-medium text-slate-700 outline-none placeholder:text-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/10 sm:text-xs" placeholder="ID / serial"></td>
                                <td class="border-b border-slate-100 px-4 py-3 text-xs text-slate-600"><input :name="`readings[${index}][position]`" x-model="row.position" class="w-full rounded-lg border border-transparent bg-slate-50 px-3 py-2.5 text-base font-medium text-slate-700 outline-none placeholder:text-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/10 sm:text-xs" placeholder="e.g. Front / Center"></td>
                                <td class="border-b border-slate-100 px-4 py-3 text-xs text-slate-600"><input type="number" min="0" max="5" step="0.0001" :name="`readings[${index}][absorbance]`" x-model="row.absorbance" class="w-full rounded-lg border border-transparent bg-slate-50 px-3 py-2.5 text-base font-medium text-slate-700 outline-none placeholder:text-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/10 sm:text-xs" placeholder="0.0000"></td>
                                <td class="border-b border-slate-100 px-4 py-3 text-xs font-bold text-slate-800" x-text="dose(row.absorbance)"></td>
                                <td class="border-b border-slate-100 px-4 py-3 text-xs text-slate-600 print:hidden"><button type="button" @click="removeRow(index)" class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-xs text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"><i class="fa-solid fa-xmark"></i></button></td>
                            </tr>
                        </template>
                        <tr x-show="rows.length === 0"><td colspan="6" class="border-b-0 px-4 py-10 text-center text-sm text-slate-400">No dosimeter reading. This section is optional.</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-end print:hidden"><button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]"><i class="fa-solid fa-floppy-disk"></i> Save Dosimeter Data</button></div>
        </form>
    </section>

    <footer class="mt-7 hidden border-t border-slate-200 pt-5 text-xs text-slate-500 print:block">
        Generated from Beam Admin · Product Testing module · {{ now()->format('d M Y H:i') }}
    </footer>
</div>
@endsection

@push('scripts')
<script>
function dosimeterEditor(initialRows) {
    return {
        rows: (initialRows || []).map((row, i) => ({ ...row, key: Date.now() + i })),
        addRow() { this.rows.push({ dosimeter_number: '', position: '', absorbance: '', key: Date.now() + Math.random() }); },
        removeRow(index) { this.rows.splice(index, 1); },
        dose(value) {
            if (value === '' || value === null || value === undefined || Number.isNaN(Number(value))) return '-';
            const x = Number(value);
            const dose = (13.099 * Math.pow(x, 3)) + (8.7891 * Math.pow(x, 2)) + (57.786 * x) - 2.423;
            return window.formatSmartNumber(dose, 4, '-');
        }
    }
}
</script>
@endpush
