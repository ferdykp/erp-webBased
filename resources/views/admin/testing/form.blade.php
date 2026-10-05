@extends('admin.layout.app')

@section('title', $test ? 'Edit Product Test' : 'New Product Test')

@section('content')
    <div class="w-full mx-auto space-y-5 max-w-none sm:space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:justify-between">
            <div>
                <a href="{{ route('admin.testing.index') }}"
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
                            {{ $test ? 'Edit Product Test' : 'Create Product Test' }}
                        </h1>

                        <p class="max-w-3xl mt-2 text-sm leading-6 text-slate-500">
                            Catat data minimum yang dibutuhkan untuk trial, research, dose mapping, atau Operational
                            Qualification. Product Testing tidak mengikuti alur booking customer dan warehouse check-in.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stepper --}}
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
                    <b class="block text-[11px] font-bold text-slate-500">
                        Process Parameter
                    </b>

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
                    <b class="block text-[11px] font-bold text-slate-500">
                        Report
                    </b>

                    <small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">
                        Absorbance & result
                    </small>
                </div>
            </div>
        </div>

        {{-- Validation Error --}}
        @if ($errors->any())
            <div class="flex gap-3 px-4 py-3 text-xs leading-5 border rounded-xl border-rose-200 bg-rose-50 text-rose-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

                <div>
                    <p class="font-bold">Please check the form.</p>

                    <ul class="pl-5 mt-1 space-y-1 list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ $test ? route('admin.testing.update', $test) : route('admin.testing.store') }}"
            class="space-y-5" enctype="multipart/form-data">

            @csrf

            @if ($test)
                @method('PUT')
            @endif

            {{-- =========================================================
                01. IDENTIFICATION
            ========================================================== --}}
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

                    {{-- Requester --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Requester / Researcher
                        </span>

                        <input name="requester_name" value="{{ old('requester_name', $test?->requester_name) }}"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Nama orang, PIC, atau internal team">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Optional — dapat dikosongkan untuk internal OQ.
                        </span>
                    </label>

                    {{-- Organization --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Institution / Company
                        </span>

                        <input name="requester_organization"
                            value="{{ old('requester_organization', $test?->requester_organization) }}"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Universitas, lab, perusahaan, atau internal">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Optional.
                        </span>
                    </label>

                    {{-- Contact --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Contact
                        </span>

                        <input name="requester_contact" value="{{ old('requester_contact', $test?->requester_contact) }}"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Phone / email / reference">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Optional.
                        </span>
                    </label>

                    {{-- Sample Name --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Test / Sample Name
                            <em class="not-italic text-rose-500">*</em>
                        </span>

                        <input name="sample_name" required value="{{ old('sample_name', $test?->sample_name) }}"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="e.g. OQ Conveyor Speed / Packaging Sample A">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Satu-satunya field wajib pada tahap ini.
                        </span>
                    </label>

                </div>
            </section>

            {{-- =========================================================
                02. SAMPLE DATA
            ========================================================== --}}
            <section class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 sm:p-6"
                x-data="doseDurCalculator()">
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

                    {{-- Quantity + Unit --}}
                    <div class="grid grid-cols-[minmax(0,1fr)_minmax(110px,0.55fr)] gap-3">

                        <label class="block min-w-0">
                            <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                                Quantity
                            </span>

                            <input type="number" min="1" step="1" name="quantity"
                                value="{{ old('quantity', $test?->quantity) }}"
                                class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                placeholder="Optional">
                        </label>

                        <label class="block min-w-0">
                            <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                                Unit
                            </span>

                            <input name="unit" value="{{ old('unit', $test?->unit) }}"
                                class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                placeholder="pcs / box">
                        </label>

                    </div>

                    {{-- Temperature --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Expected Temperature
                        </span>

                        <input name="expected_temperature"
                            value="{{ old('expected_temperature', $test?->expected_temperature) }}"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="e.g. 25°C">
                    </label>

                    {{-- Minimum Dose --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Reference Minimum Dose (kGy)
                        </span>

                        <input id="dmin" type="number" min="0" step="0.0001" name="dmin"
                            value="{{ old('dmin', $test?->dmin) }}" x-model="minDose" @input="calculate()"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Nilai minimum / trough.
                        </span>
                    </label>

                    {{-- Maximum Dose --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Reference Maximum Dose (kGy)
                        </span>

                        <input id="dmax" type="number" min="0" step="0.0001" name="dmax"
                            value="{{ old('dmax', $test?->dmax) }}" x-model="maxDose" @input="calculate()"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">

                        <span class="mt-1.5 block text-[11px] leading-4 text-slate-400">
                            Nilai maksimum / peak.
                        </span>
                    </label>

                    {{-- =================================================
    DUR CALCULATION
================================================== --}}
                    <div class="md:col-span-2 2xl:col-span-4">

                        <div
                            class="overflow-hidden border border-blue-100 rounded-2xl bg-gradient-to-br from-blue-50/80 to-white">

                            <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">

                                <div class="min-w-0">

                                    <div class="flex items-center gap-2">

                                        <div
                                            class="flex items-center justify-center text-blue-600 bg-blue-100 w-9 h-9 rounded-xl">
                                            <i class="fa-solid fa-scale-balanced"></i>
                                        </div>

                                        <div>
                                            <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                                                Dose Analysis
                                            </p>

                                            <h3 class="mt-0.5 text-sm font-bold text-slate-900">
                                                Dose Uniformity Ratio (DUR)
                                            </h3>
                                        </div>

                                    </div>

                                    <div class="mt-4">

                                        <p class="text-[11px] font-medium text-slate-500">
                                            Formula
                                        </p>

                                        <div
                                            class="inline-flex items-center gap-2 px-3 py-2 mt-1.5 font-mono text-sm font-semibold text-blue-700 border border-blue-100 rounded-lg bg-white">

                                            <span>Max Dose</span>

                                            <span class="text-slate-300">÷</span>

                                            <span>Min Dose</span>

                                        </div>

                                    </div>

                                </div>

                                {{-- DUR Result --}}
                                <div
                                    class="flex flex-col justify-center min-w-0 px-5 py-4 text-center bg-white border rounded-xl border-slate-200 sm:min-w-[220px]">

                                    <span class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-slate-400">
                                        Calculated DUR
                                    </span>

                                    <strong class="mt-1 text-2xl font-black tracking-tight text-slate-900"
                                        x-text="formattedDur">
                                        —
                                    </strong>

                                    <span class="mt-1 text-[10px] font-medium text-slate-400" x-text="durStatus">
                                        —
                                    </span>

                                </div>

                            </div>

                            {{-- Calculation Detail --}}
                            <div class="px-5 pb-5">

                                <div
                                    class="grid grid-cols-1 gap-3 p-4 border rounded-xl border-slate-200 bg-white/80 sm:grid-cols-3">

                                    {{-- Minimum Dose --}}
                                    <div>

                                        <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">
                                            Minimum Dose
                                        </span>

                                        <strong class="block mt-1 text-sm font-bold text-slate-800"
                                            x-text="formattedMinDose">
                                            —
                                        </strong>

                                    </div>

                                    {{-- Maximum Dose --}}
                                    <div>

                                        <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">
                                            Maximum Dose
                                        </span>

                                        <strong class="block mt-1 text-sm font-bold text-slate-800"
                                            x-text="formattedMaxDose">
                                            —
                                        </strong>

                                    </div>

                                    {{-- Result --}}
                                    <div>

                                        <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">
                                            DUR Result
                                        </span>

                                        <strong class="block mt-1 text-sm font-bold text-blue-700" x-text="formulaText">
                                            —
                                        </strong>

                                    </div>

                                </div>

                                <p class="mt-3 text-[10px] leading-5 text-slate-400">

                                    <i class="mr-1 text-blue-400 fa-solid fa-circle-info"></i>

                                    DUR dihitung otomatis berdasarkan Reference Minimum Dose dan
                                    Reference Maximum Dose menggunakan rasio Maximum Dose ÷ Minimum Dose.
                                    Nilai DUR tidak disimpan sebagai input terpisah karena merupakan
                                    hasil kalkulasi dari kedua nilai tersebut.

                                </p>

                            </div>

                        </div>

                    </div>

                    {{-- Dimension --}}
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
                                    value="{{ old('length_cm', $test?->length_cm) }}"
                                    class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                    placeholder="Length">
                            </label>

                            <label>
                                <span
                                    class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">
                                    L
                                </span>

                                <input type="number" min="0" step="0.001" name="width_cm"
                                    value="{{ old('width_cm', $test?->width_cm) }}"
                                    class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                    placeholder="Width">
                            </label>

                            <label>
                                <span
                                    class="mb-1.5 block text-center text-[9px] font-extrabold uppercase tracking-widest text-slate-400">
                                    T
                                </span>

                                <input type="number" min="0" step="0.001" name="height_cm"
                                    value="{{ old('height_cm', $test?->height_cm) }}"
                                    class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                                    placeholder="Height">
                            </label>

                        </div>
                    </div>

                    {{-- Net Weight --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Net Weight (kg)
                        </span>

                        <input type="number" min="0" step="0.0001" name="net_weight_kg"
                            value="{{ old('net_weight_kg', $test?->net_weight_kg) }}"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">
                    </label>

                    {{-- Gross Weight --}}
                    <label class="block min-w-0">
                        <span class="mb-2 block text-[10px] font-bold uppercase tracking-[0.11em] text-slate-500">
                            Gross Weight (kg)
                        </span>

                        <input type="number" min="0" step="0.0001" name="gross_weight_kg"
                            value="{{ old('gross_weight_kg', $test?->gross_weight_kg) }}"
                            class="w-full min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                            placeholder="Optional">
                    </label>

                </div>
            </section>

            {{-- =========================================================
                03. NOTES
            ========================================================== --}}
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
                            Gunakan untuk tujuan singkat, kondisi khusus, setup awal, atau catatan penelitian. Optional.
                        </p>
                    </div>
                </div>

                <label class="block min-w-0">
                    <textarea name="notes" rows="5"
                        class="w-full min-w-0 resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-base font-medium text-slate-800 outline-none placeholder:text-slate-300 hover:border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:text-sm"
                        placeholder="Optional notes...">{{ old('notes', $test?->notes) }}</textarea>
                </label>
            </section>

            {{-- =========================================================
                04. DOCUMENTATION
            ========================================================== --}}
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

                    @if ($test?->image)
                        <div class="flex items-center gap-4 p-3 border rounded-xl border-slate-200 bg-slate-50 w-fit">

                            <img src="{{ asset('storage/' . $test->image) }}" alt="Preview"
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
                    @endif

                    <input type="file" name="image" accept="image/png, image/jpeg, image/jpg, image/webp"
                        class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">

                </div>
            </section>

            {{-- Bottom Action --}}
            <div
                class="flex flex-col gap-4 p-4 bg-white border shadow-sm rounded-2xl border-slate-200 sm:flex-row sm:items-center sm:justify-between">

                <div class="text-xs text-slate-500">
                    <i class="fa-solid fa-circle-info mr-1.5 text-blue-500"></i>
                    Setelah disimpan, Anda langsung masuk ke Process Parameter — tanpa warehouse check-in.
                </div>

                <div class="flex w-full flex-col-reverse gap-2.5 sm:w-auto sm:flex-row">

                    <a href="{{ route('admin.testing.index') }}"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]">
                        Cancel
                    </a>

                    <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]">

                        {{ $test ? 'Save & Continue' : 'Continue to Process Parameter' }}

                        <i class="fa-solid fa-arrow-right"></i>
                    </button>

                </div>
            </div>

        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function doseDurCalculator() {
            return {
                minDose: @js(old('dmin', $test?->dmin)),
                maxDose: @js(old('dmax', $test?->dmax)),

                formattedDur: '—',
                durStatus: 'Masukkan Min & Max Dose',

                formattedMinDose: '—',
                formattedMaxDose: '—',

                formulaText: '—',

                init() {
                    this.calculate();
                },

                calculate() {
                    const min = parseFloat(this.minDose);
                    const max = parseFloat(this.maxDose);

                    this.formattedMinDose =
                        Number.isFinite(min) ?
                        `${this.formatNumber(min, 4)} kGy` :
                        '—';

                    this.formattedMaxDose =
                        Number.isFinite(max) ?
                        `${this.formatNumber(max, 4)} kGy` :
                        '—';

                    // Data belum lengkap
                    if (!Number.isFinite(min) || !Number.isFinite(max)) {
                        this.formattedDur = '—';
                        this.durStatus = 'Masukkan Min & Max Dose';
                        this.formulaText = '—';

                        return;
                    }

                    // Nilai tidak valid
                    if (min < 0 || max < 0) {
                        this.formattedDur = '—';
                        this.durStatus = 'Nilai dose tidak valid';
                        this.formulaText = '—';

                        return;
                    }

                    // Minimum dose tidak boleh 0
                    if (min === 0) {
                        this.formattedDur = '—';
                        this.durStatus = 'Min Dose tidak boleh 0';
                        this.formulaText = 'Max ÷ Min';

                        return;
                    }

                    // Max harus >= Min
                    if (max < min) {
                        this.formattedDur = '—';
                        this.durStatus = 'Max Dose harus ≥ Min Dose';
                        this.formulaText = 'Data dose tidak valid';

                        return;
                    }

                    // DUR = Max Dose / Min Dose
                    const dur = max / min;

                    this.formattedDur = this.formatNumber(dur, 4);

                    this.durStatus = 'Dose Uniformity Ratio';

                    this.formulaText =
                        `${this.formatNumber(max, 4)} ÷ ${this.formatNumber(min, 4)} = ${this.formatNumber(dur, 4)}`;
                },

                formatNumber(value, decimals = 4) {
                    if (!Number.isFinite(Number(value))) {
                        return '—';
                    }

                    return Number(value).toLocaleString('en-US', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: decimals
                    });
                }
            }
        }
    </script>
@endpush
