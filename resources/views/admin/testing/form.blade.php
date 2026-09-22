@extends('admin.layout.app')

@section('title', $test ? 'Edit Product Test' : 'New Product Test')

@section('content')
<div class="mx-auto w-full max-w-none space-y-5 sm:space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
        <div>
            <a href="{{ route('admin.testing.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition-colors hover:text-blue-600">
                <i class="fa-solid fa-arrow-left"></i>
                Product Testing
            </a>
            <div class="mt-3 flex items-start gap-4">
                <div class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-lg text-blue-600 sm:flex">
                    <i class="fa-solid fa-flask-vial"></i>
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-600">Technical Test Record</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">{{ $test ? 'Edit Product Test' : 'Create Product Test' }}</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Catat data minimum yang dibutuhkan untuk trial, research, dose mapping, atau Operational Qualification. Product Testing tidak mengikuti alur booking customer dan warehouse check-in.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="flex items-center overflow-x-auto rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm sm:px-5">
        <div class="flex shrink-0 items-center gap-2.5 text-slate-900">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-600 bg-blue-600 text-[11px] font-bold text-white">1</span>
            <div><b class="block text-[11px] font-bold text-slate-900">Test Data</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Sample & requester</small></div>
        </div>
        <div class="mx-3 h-px min-w-8 flex-1 bg-slate-200 sm:mx-5"></div>
        <div class="flex shrink-0 items-center gap-2.5 text-slate-400">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-bold">2</span>
            <div><b class="block text-[11px] font-bold text-slate-500">Process Parameter</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Unit & irradiation setting</small></div>
        </div>
        <div class="mx-3 h-px min-w-8 flex-1 bg-slate-200 sm:mx-5"></div>
        <div class="flex shrink-0 items-center gap-2.5 text-slate-400">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-[11px] font-bold">3</span>
            <div><b class="block text-[11px] font-bold text-slate-500">Report</b><small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">Absorbance & result</small></div>
        </div>
    </div>

    @if ($errors->any())
        <div class="flex gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs leading-5 text-rose-700">
            <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
            <div>
                <p class="font-bold">Please check the form.</p>
                <ul class="mt-1 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ $test ? route('admin.testing.update', $test) : route('admin.testing.store') }}" class="space-y-5">
        @csrf
        @if ($test) @method('PUT') @endif

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">01 · Identification</p>
                    <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">Requester & Test Identity</h2>
                    <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">Requester tidak harus terdaftar sebagai customer dan tidak harus berasal dari perusahaan.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5 2xl:grid-cols-4">
                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Requester / Researcher</span>
                    <input name="requester_name" value="{{ old('requester_name', $test?->requester_name) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Nama orang, PIC, atau internal team">
                    <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">Optional — dapat dikosongkan untuk internal OQ.</span>
                </label>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Institution / Company</span>
                    <input name="requester_organization" value="{{ old('requester_organization', $test?->requester_organization) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Universitas, lab, perusahaan, atau internal">
                    <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">Optional.</span>
                </label>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Contact</span>
                    <input name="requester_contact" value="{{ old('requester_contact', $test?->requester_contact) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Phone / email / reference">
                    <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">Optional.</span>
                </label>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Test / Sample Name <em class="not-italic text-rose-500">*</em></span>
                    <input name="sample_name" required value="{{ old('sample_name', $test?->sample_name) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="e.g. OQ Conveyor Speed / Packaging Sample A">
                    <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">Satu-satunya field wajib pada tahap ini.</span>
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">02 · Sample Data</p>
                    <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">Product / Sample Detail</h2>
                    <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">Seluruh data berikut optional. Kosongkan bila test hanya mencari parameter/dose atau untuk Operational Qualification.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 md:gap-5 2xl:grid-cols-4">
                <div class="grid grid-cols-[minmax(0,1fr)_minmax(110px,0.55fr)] gap-3">
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Quantity</span>
                        <input type="number" min="0.001" step="0.001" name="quantity" value="{{ old('quantity', $test?->quantity) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional">
                    </label>
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Unit</span>
                        <input name="unit" value="{{ old('unit', $test?->unit) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="pcs / box">
                    </label>
                </div>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Expected Temperature</span>
                    <input name="expected_temperature" value="{{ old('expected_temperature', $test?->expected_temperature) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="e.g. 25°C">
                </label>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Reference Minimum Dose (kGy)</span>
                    <input type="number" min="0" step="0.0001" name="dmin" value="{{ old('dmin', $test?->dmin) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional">
                </label>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Reference Maximum Dose (kGy)</span>
                    <input type="number" min="0" step="0.0001" name="dmax" value="{{ old('dmax', $test?->dmax) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional">
                </label>

                <div class="block min-w-0 md:col-span-2">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Dimension P × L × T (cm)</span>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <label>
                            <span class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">P</span>
                            <input type="number" min="0" step="0.001" name="length_cm" value="{{ old('length_cm', $test?->length_cm) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Length">
                        </label>
                        <label>
                            <span class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">L</span>
                            <input type="number" min="0" step="0.001" name="width_cm" value="{{ old('width_cm', $test?->width_cm) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Width">
                        </label>
                        <label>
                            <span class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">T</span>
                            <input type="number" min="0" step="0.001" name="height_cm" value="{{ old('height_cm', $test?->height_cm) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Height">
                        </label>
                    </div>
                </div>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Net Weight (kg)</span>
                    <input type="number" min="0" step="0.0001" name="net_weight_kg" value="{{ old('net_weight_kg', $test?->net_weight_kg) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional">
                </label>

                <label class="block min-w-0">
                    <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">Gross Weight (kg)</span>
                    <input type="number" min="0" step="0.0001" name="gross_weight_kg" value="{{ old('gross_weight_kg', $test?->gross_weight_kg) }}" class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional">
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm shadow-slate-200/30 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">03 · Notes</p>
                    <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">Additional Notes</h2>
                    <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500">Gunakan untuk tujuan singkat, kondisi khusus, setup awal, atau catatan penelitian. Optional.</p>
                </div>
            </div>
            <label class="block min-w-0">
                <textarea name="notes" rows="5" class="w-full min-w-0 resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm" placeholder="Optional notes...">{{ old('notes', $test?->notes) }}</textarea>
            </label>
        </section>

        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div class="text-xs text-slate-500">
                <i class="fa-solid fa-circle-info mr-1.5 text-blue-500"></i>
                Setelah disimpan, Anda langsung masuk ke Process Parameter — tanpa warehouse check-in.
            </div>
            <div class="flex w-full flex-col-reverse gap-2.5 sm:w-auto sm:flex-row">
                <a href="{{ route('admin.testing.index') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]">Cancel</a>
                <button class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]">
                    {{ $test ? 'Save & Continue' : 'Continue to Process Parameter' }}
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
