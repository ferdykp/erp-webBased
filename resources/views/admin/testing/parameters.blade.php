@extends('admin.layout.app')

@section('title', 'Process Parameter')

@section('content')
<div class="mx-auto w-full max-w-none space-y-5 sm:space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
        <div>
            <a href="{{ route('admin.testing.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition-colors hover:text-blue-600"><i class="fa-solid fa-arrow-left"></i> Product Testing</a>
            <div class="mt-3 flex items-start gap-4">
                <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-lg text-blue-600 sm:flex"><i class="fa-solid fa-sliders"></i></div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-600">{{ $test->test_code }}</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">Process Parameter</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">Masukkan actual irradiation setup untuk test ini. Tidak ada proses check-in, pallet, porter, atau warehouse placement.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center overflow-x-auto rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="flex shrink-0 items-center gap-2.5 text-slate-700">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-500 bg-emerald-500 text-[11px] font-bold text-white"><i class="fa-solid fa-check"></i></span>
            <div><b class="block text-[11px] font-bold text-slate-700">Test Data</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Saved</small></div>
        </div>
        <div class="mx-3 h-px min-w-8 flex-1 bg-emerald-300 sm:mx-5"></div>
        <div class="flex shrink-0 items-center gap-2.5 text-slate-900">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-600 bg-blue-600 text-[11px] font-bold text-white">2</span>
            <div><b class="block text-[11px] font-bold text-slate-900">Process Parameter</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Irradiation setting</small></div>
        </div>
        <div class="mx-3 h-px min-w-8 flex-1 bg-slate-200 sm:mx-5"></div>
        <div class="flex shrink-0 items-center gap-2.5 text-slate-400">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-bold">3</span>
            <div><b class="block text-[11px] font-bold text-slate-500">Report</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Absorbance & result</small></div>
        </div>
    </div>

    @if ($errors->any())
        <div class="flex gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs leading-5 text-rose-700"><i class="fa-solid fa-circle-exclamation mt-0.5"></i><div><p class="font-bold">Please check the process parameter.</p><ul class="mt-1 list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
    @endif

    <div class="grid grid-cols-1 gap-5 xl:grid-cols-[320px_minmax(0,1fr)] 2xl:grid-cols-[360px_minmax(0,1fr)] 3xl:grid-cols-[380px_minmax(0,1fr)] 3xl:gap-7">
        <aside class="h-fit rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6 xl:sticky xl:top-24">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div><p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">Test Summary</p><h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">{{ $test->sample_name }}</h2></div>
            </div>
            <dl class="divide-y divide-slate-100">
                <div class="flex items-start justify-between gap-4 py-3"><dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Requester</dt><dd class="max-w-[58%] text-right text-xs font-semibold text-slate-700">{{ $test->requester_name ?: 'Internal / not specified' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Institution</dt><dd class="max-w-[58%] text-right text-xs font-semibold text-slate-700">{{ $test->requester_organization ?: '-' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Quantity</dt><dd class="max-w-[58%] text-right text-xs font-semibold text-slate-700">{{ $test->quantity ? rtrim(rtrim(number_format((float)$test->quantity, 3, '.', ''), '0'), '.') . ' ' . ($test->unit ?: '') : '-' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Reference Dose</dt><dd class="max-w-[58%] text-right text-xs font-semibold text-slate-700">{{ $test->dmin !== null ? $test->dmin : '-' }}{{ $test->dmax !== null ? ' – '.$test->dmax : '' }}{{ $test->dmin !== null || $test->dmax !== null ? ' kGy' : '' }}</dd></div>
                <div class="flex items-start justify-between gap-4 py-3"><dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Dimension</dt><dd class="max-w-[58%] text-right text-xs font-semibold text-slate-700">{{ $test->dimension_label }}</dd></div>
            </dl>
            <a href="{{ route('admin.testing.edit', $test) }}" class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]"><i class="fa-solid fa-pen"></i> Edit Test Data</a>
        </aside>

        <form method="POST" action="{{ route('admin.testing.parameters.update', $test) }}" class="space-y-5" x-data="{ loadingMode: @js(old('loading_mode', in_array($test->loading_mode, ['single-side','double-side']) ? $test->loading_mode : ($test->loading_mode ? 'custom' : '')) ) }">
            @csrf
            @method('PUT')

            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">Machine Setup</p>
                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">Irradiation Process Parameter</h2>
                        <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">Production unit, beam speed, dan loading mode wajib. Target dose boleh kosong bila test bertujuan mencari nilai dosis.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5 2xl:grid-cols-3">
                    <label class="block min-w-0 md:col-span-2 2xl:col-span-3">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">E-Beam Unit / Production Line <em class="not-italic text-rose-500">*</em></span>
                        <select name="production_line_id" required class="w-full min-w-0 cursor-pointer rounded-xl border border-slate-200 bg-white px-3.5 py-3 pr-9 text-base font-medium text-slate-800 outline-none hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm">
                            <option value="">Select unit / machine</option>
                            @foreach ($productionLines as $line)
                                <option value="{{ $line->id }}" @selected(old('production_line_id', $test->production_line_id) == $line->id)>{{ $line->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Beam Speed (m/s) <em class="not-italic text-rose-500">*</em></span>
                        <input type="number" min="0.0001" step="0.0001" name="beam_speed" required value="{{ old('beam_speed', $test->beam_speed) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="e.g. 0.3500">
                    </label>

                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Target Dose (kGy)</span>
                        <input type="number" min="0" step="0.0001" name="target_dose" value="{{ old('target_dose', $test->target_dose) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional for dose finding test">
                    </label>

                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Loading Mode <em class="not-italic text-rose-500">*</em></span>
                        <select name="loading_mode" x-model="loadingMode" required class="w-full min-w-0 cursor-pointer rounded-xl border border-slate-200 bg-white px-3.5 py-3 pr-9 text-base font-medium text-slate-800 outline-none hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm">
                            <option value="">Select loading mode</option>
                            <option value="single-side">Single Side</option>
                            <option value="double-side">Double Side</option>
                            <option value="custom">Custom</option>
                        </select>
                    </label>

                    <label class="block min-w-0" x-show="loadingMode === 'custom'" x-transition.opacity>
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Custom Loading Mode <em class="not-italic text-rose-500">*</em></span>
                        <input name="custom_loading_mode" value="{{ old('custom_loading_mode', !in_array($test->loading_mode, ['single-side','double-side']) ? $test->loading_mode : '') }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Describe loading mode">
                    </label>

                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Frequency (Hz) <em class="not-italic text-rose-500">*</em></span>
                        <input type="number" min="0" step="0.0001" name="freq" required value="{{ old('freq', $test->freq) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="e.g. 20">
                    </label>

                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Scan Gear <em class="not-italic text-rose-500">*</em></span>
                        <input type="number" min="0" step="0.0001" name="scan_gear" required value="{{ old('scan_gear', $test->scan_gear) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="e.g. 1">
                    </label>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
                <div class="mb-5 flex items-start justify-between gap-4"><div><p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">Process Notes</p><h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">Run Notes</h2><p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">Optional notes for setup deviations, observations, or special conditions during the test.</p></div></div>
                <textarea name="process_notes" rows="5" class="w-full min-w-0 resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional process notes...">{{ old('process_notes', $test->process_notes) }}</textarea>
            </section>

            <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-slate-500"><i class="fa-solid fa-circle-check mr-1.5 text-emerald-500"></i>Saving this step marks the product test as completed and opens the report.</div>
                <div class="flex w-full flex-col-reverse gap-2.5 sm:w-auto sm:flex-row">
                    <a href="{{ route('admin.testing.edit', $test) }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]">Finish Test & Open Report <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
