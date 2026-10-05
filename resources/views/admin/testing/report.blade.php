@extends('admin.layout.app')

@section('title', 'Product Test Report')

@section('content')

    @php

        $format = fn($value, $decimals = 4) => \App\Support\NumberFormatter::smart($value, $decimals);

        /*
        |--------------------------------------------------------------------------
        | Dose Uniformity Ratio (DUR)
        |--------------------------------------------------------------------------
        |
        | DUR dihitung dari HASIL MEASURED DOSE, bukan dari Reference Dose.
        |
        | Formula:
        | DUR = Maximum Measured Dose / Minimum Measured Dose
        |
        | Source:
        | $doseStats['max']
        | $doseStats['min']
        |
        | $test->dmin dan $test->dmax hanya merupakan Reference Dose
        | dan tidak digunakan dalam perhitungan DUR.
        |
        */

        $doseDur = null;

        if ($doseStats['min'] !== null && $doseStats['max'] !== null) {
            $minimumMeasuredDose = (float) $doseStats['min'];
            $maximumMeasuredDose = (float) $doseStats['max'];

            if ($minimumMeasuredDose > 0) {
                $doseDur = $maximumMeasuredDose / $minimumMeasuredDose;
            }
        }

    @endphp

    <div class="w-full mx-auto space-y-5 max-w-none print:m-0 print:max-w-none print:space-y-4 sm:space-y-6">

        {{-- =========================================================
            HEADER ACTIONS
        ========================================================== --}}

        <div class="flex flex-col gap-4 print:hidden sm:flex-row sm:items-end sm:justify-between">

            <div>

                <a href="{{ route('admin.testing.index') }}"
                    class="inline-flex items-center gap-2 text-xs font-semibold transition-colors text-slate-500 hover:text-blue-600">

                    <i class="fa-solid fa-arrow-left"></i>

                    Product Testing

                </a>

                <p class="mt-3 text-[10px] font-extrabold uppercase tracking-[0.16em] text-blue-600">

                    {{ $test->test_code }}

                </p>

                <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">

                    Product Test Report

                </h1>

                <p class="mt-2 text-sm leading-6 text-slate-500">

                    Technical record, process parameters, dosimeter absorbance, and calculated dose.

                </p>

            </div>

            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.testing.edit', $test) }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]">

                    <i class="fa-solid fa-pen"></i>

                    Edit Data

                </a>

                <a href="{{ route('admin.testing.parameters', $test) }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99]">

                    <i class="fa-solid fa-sliders"></i>

                    Process Parameter

                </a>

                <button type="button" onclick="printReport()"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]">

                    <i class="fa-solid fa-print"></i>

                    Print / Save PDF

                </button>

            </div>

        </div>


        {{-- =========================================================
            STEPPER
        ========================================================== --}}

        <div
            class="flex items-center px-4 py-3 overflow-x-auto bg-white border shadow-sm rounded-2xl border-slate-200 print:hidden sm:px-5">

            <div class="flex shrink-0 items-center gap-2.5 text-slate-700">

                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-500 bg-emerald-500 text-[11px] font-bold text-white">

                    <i class="fa-solid fa-check"></i>

                </span>

                <div>

                    <b class="block text-[11px] font-bold text-slate-700">
                        Test Data
                    </b>

                    <small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">
                        Saved
                    </small>

                </div>

            </div>

            <div class="flex-1 h-px mx-3 min-w-8 bg-emerald-300 sm:mx-5"></div>

            <div class="flex shrink-0 items-center gap-2.5 text-slate-700">

                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-emerald-500 bg-emerald-500 text-[11px] font-bold text-white">

                    <i class="fa-solid fa-check"></i>

                </span>

                <div>

                    <b class="block text-[11px] font-bold text-slate-700">
                        Process Parameter
                    </b>

                    <small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">
                        Completed
                    </small>

                </div>

            </div>

            <div class="flex-1 h-px mx-3 min-w-8 bg-emerald-300 sm:mx-5"></div>

            <div class="flex shrink-0 items-center gap-2.5 text-slate-900">

                <span
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-blue-600 bg-blue-600 text-[11px] font-bold text-white">

                    3

                </span>

                <div>

                    <b class="block text-[11px] font-bold text-slate-900">
                        Report
                    </b>

                    <small class="mt-0.5 hidden text-[9px] text-slate-400 sm:block">
                        Result & dosimeter
                    </small>

                </div>

            </div>

        </div>


        {{-- =========================================================
            PRINT HEADER
        ========================================================== --}}

        <div class="items-center justify-between hidden pb-5 border-b-2 border-slate-900 print:flex">

            <div>

                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">
                    E-Beam Technical Report
                </p>

                <h1 class="mt-1 text-2xl font-black">
                    Product Testing / Qualification
                </h1>

            </div>

            <div class="text-right">

                <p class="font-black">
                    {{ $test->test_code }}
                </p>

                <p class="text-xs text-slate-500">
                    {{ $test->processed_at?->format('d M Y H:i') ?: $test->updated_at->format('d M Y H:i') }}
                </p>

            </div>

        </div>


        {{-- =========================================================
            INFORMATION & SETUP
        ========================================================== --}}

        <section class="grid grid-cols-1 gap-5 xl:grid-cols-2">

            {{-- Test Information --}}

            <div
                class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6">

                <div class="flex items-start justify-between gap-4 mb-5">

                    <div>

                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                            Test Information
                        </p>

                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            {{ $test->sample_name }}
                        </h2>

                    </div>

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-bold text-emerald-700">

                        <i class="fa-solid fa-circle-check"></i>

                        Completed

                    </span>

                </div>

                <dl class="grid grid-cols-1 gap-x-6 sm:grid-cols-2">

                    @foreach ([['Test Code', $test->test_code], ['Requester', $test->requester_name ?: '-'], ['Institution / Company', $test->requester_organization ?: '-'], ['Contact', $test->requester_contact ?: '-'], ['Quantity', $test->quantity !== null ? \App\Support\NumberFormatter::integer($test->quantity) . ' ' . ($test->unit ?: '') : '-'], ['Reference Dose', ($test->dmin !== null ? $format($test->dmin) : '-') . ($test->dmax !== null ? ' – ' . $format($test->dmax) : '') . ($test->dmin !== null || $test->dmax !== null ? ' kGy' : '')], ['Dimension P × L × T', $test->dimension_label], ['Expected Temperature', $test->expected_temperature ?: '-'], ['Net Weight', $test->net_weight_kg !== null ? $format($test->net_weight_kg) . ' kg' : '-'], ['Gross Weight', $test->gross_weight_kg !== null ? $format($test->gross_weight_kg) . ' kg' : '-']] as [$label, $value])
                        <div class="py-3 border-b border-slate-100">

                            <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">
                                {{ $label }}
                            </dt>

                            <dd class="mt-1 text-xs font-semibold leading-5 text-slate-700">
                                {{ $value }}
                            </dd>

                        </div>
                    @endforeach

                </dl>

                @if ($test->notes)
                    <div class="p-4 mt-5 border rounded-xl border-slate-200 bg-slate-50">

                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">
                            Notes
                        </span>

                        <p class="mt-1.5 whitespace-pre-line text-xs leading-5 text-slate-600">
                            {{ $test->notes }}
                        </p>

                    </div>
                @endif

            </div>


            {{-- Irradiation Setup --}}

            <div
                class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6">

                <div class="flex items-start justify-between gap-4 mb-5">

                    <div>

                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                            Irradiation Setup
                        </p>

                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            Process Parameter
                        </h2>

                    </div>

                    <div class="flex items-center justify-center w-10 h-10 shrink-0 rounded-xl bg-slate-100 text-slate-500">

                        <i class="fa-solid fa-sliders"></i>

                    </div>

                </div>

                <dl class="grid grid-cols-1 gap-x-6 sm:grid-cols-2">

                    @foreach ([['E-Beam Unit', $test->productionLine?->name ?: '-'], ['Beam Speed', $test->beam_speed !== null ? $format($test->beam_speed) . ' m/s' : '-'], ['Target Dose', $test->target_dose !== null ? $format($test->target_dose) . ' kGy' : 'Not specified'], ['Loading Mode', $test->loading_mode ? ucwords(str_replace('-', ' ', $test->loading_mode)) : '-'], ['Frequency', $test->freq !== null ? $format($test->freq) . ' Hz' : '-'], ['Scan Gear', $test->scan_gear !== null ? $format($test->scan_gear) : '-'], ['Processed At', $test->processed_at?->format('d M Y, H:i') ?: '-']] as [$label, $value])
                        <div class="py-3 border-b border-slate-100">

                            <dt class="text-[9px] font-bold uppercase tracking-wider text-slate-400">
                                {{ $label }}
                            </dt>

                            <dd class="mt-1 text-xs font-semibold leading-5 text-slate-700">
                                {{ $value }}
                            </dd>

                        </div>
                    @endforeach

                </dl>

                @if ($test->process_notes)
                    <div class="p-4 mt-5 border rounded-xl border-slate-200 bg-slate-50">

                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400">
                            Process Notes
                        </span>

                        <p class="mt-1.5 whitespace-pre-line text-xs leading-5 text-slate-600">
                            {{ $test->process_notes }}
                        </p>

                    </div>
                @endif

            </div>

        </section>


        {{-- =========================================================
            DOSE UNIFORMITY RATIO
        ========================================================== --}}

        {{-- <section
            class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6">

            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">

                <div>

                    <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                        Dose Analysis
                    </p>

                    <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                        Dose Uniformity Ratio (DUR)
                    </h2>

                    <p class="max-w-2xl mt-1.5 text-xs leading-5 text-slate-500">

                        Perhitungan rasio keseragaman dosis berdasarkan
                        <strong class="font-semibold text-slate-700">
                            Minimum Measured Dose
                        </strong>
                        dan
                        <strong class="font-semibold text-slate-700">
                            Maximum Measured Dose
                        </strong>.

                    </p>

                </div>



                <div
                    class="flex flex-col items-center justify-center min-w-0 px-6 py-4 text-center border rounded-xl border-blue-100 bg-blue-50 sm:min-w-[190px]">

                    <span class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-500">
                        Dose Uniformity Ratio
                    </span>

                    @if ($doseDur !== null)
                        <strong class="mt-1 text-2xl font-black tracking-tight text-blue-700">
                            {{ $format($doseDur, 4) }}
                        </strong>

                        <span class="mt-1 text-[10px] font-medium text-blue-500">
                            DUR
                        </span>
                    @else
                        <strong class="mt-1 text-2xl font-black tracking-tight text-slate-400">
                            —
                        </strong>

                        <span class="mt-1 text-[10px] font-medium text-slate-400">
                            Data tidak lengkap
                        </span>
                    @endif

                </div>

            </div>



            <div class="grid grid-cols-1 gap-3 mt-5 sm:grid-cols-3">


                <div class="p-4 border rounded-xl border-slate-200 bg-slate-50">

                    <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">
                        Minimum Measured Dose
                    </span>

                    <strong class="block mt-1 text-lg font-extrabold text-slate-900">

                        {{ $doseStats['min'] !== null ? $format($doseStats['min'], 4) . ' kGy' : '—' }}

                    </strong>

                </div>



                <div class="p-4 border rounded-xl border-slate-200 bg-slate-50">

                    <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">
                        Maximum Measured Dose
                    </span>

                    <strong class="block mt-1 text-lg font-extrabold text-slate-900">

                        {{ $doseStats['max'] !== null ? $format($doseStats['max'], 4) . ' kGy' : '—' }}

                    </strong>

                </div>



                <div class="p-4 border rounded-xl border-slate-200 bg-slate-50">

                    <span class="block text-[9px] font-bold uppercase tracking-wider text-slate-400">
                        Formula
                    </span>

                    @if ($doseDur !== null)
                        <strong class="block mt-1 text-sm font-bold text-slate-800">

                            {{ $format($doseStats['max'], 4) }}

                            ÷

                            {{ $format($doseStats['min'], 4) }}

                        </strong>
                    @else
                        <strong class="block mt-1 text-sm font-bold text-slate-400">
                            —
                        </strong>
                    @endif

                </div>

            </div>



            @if ($doseDur !== null)
                <div class="p-4 mt-4 border border-blue-100 rounded-xl bg-blue-50/50">

                    <div class="flex items-start gap-3">

                        <div class="flex items-center justify-center w-8 h-8 text-blue-600 bg-blue-100 rounded-lg shrink-0">

                            <i class="fa-solid fa-calculator"></i>

                        </div>

                        <div class="min-w-0">

                            <p class="text-[9px] font-extrabold uppercase tracking-wider text-blue-600">
                                DUR Calculation
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-600">

                                DUR =

                                <strong class="text-slate-800">
                                    Maximum Measured Dose
                                </strong>

                                ÷

                                <strong class="text-slate-800">
                                    Minimum Measured Dose
                                </strong>

                                =

                                {{ $format($doseStats['max'], 4) }}

                                ÷

                                {{ $format($doseStats['min'], 4) }}

                                =

                                <strong class="text-blue-700">
                                    {{ $format($doseDur, 4) }}
                                </strong>

                            </p>

                        </div>

                    </div>

                </div>
            @else
                <div class="p-4 mt-4 border rounded-xl border-slate-200 bg-slate-50">

                    <div class="flex items-start gap-3">

                        <div
                            class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-400 shrink-0">

                            <i class="fa-solid fa-circle-info"></i>

                        </div>

                        <div>

                            <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500">
                                DUR Calculation
                            </p>

                            <p class="mt-1 text-xs leading-5 text-slate-500">

                                DUR belum dapat dihitung. Pastikan terdapat
                                <strong>Minimum Measured Dose</strong>
                                dan nilainya lebih besar dari 0.

                            </p>

                        </div>

                    </div>

                </div>
            @endif



            <div class="p-4 mt-4 border border-slate-200 rounded-xl bg-slate-50">

                <div class="flex items-start gap-3">

                    <div
                        class="flex items-center justify-center w-8 h-8 text-blue-500 bg-white border rounded-lg border-slate-200 shrink-0">

                        <i class="fa-solid fa-circle-info"></i>

                    </div>

                    <div>

                        <p class="text-[9px] font-extrabold uppercase tracking-wider text-slate-500">
                            Calculation Source
                        </p>

                        <p class="mt-1 text-xs leading-5 text-slate-500">

                            Nilai DUR dihitung langsung dari hasil pengukuran aktual.
                            <strong class="text-slate-700">
                                Reference Minimum Dose
                            </strong>
                            dan
                            <strong class="text-slate-700">
                                Reference Maximum Dose
                            </strong>
                            tidak digunakan dalam perhitungan DUR.

                        </p>

                    </div>

                </div>

            </div>

        </section> --}}


        {{-- =========================================================
            SAMPLE IMAGE
        ========================================================== --}}

        @if ($test->image)
            <section
                class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6">

                <div class="flex items-start justify-between gap-4 mb-4">

                    <div>

                        <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                            Visual Documentation
                        </p>

                        <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                            Sample Photo / Documentation
                        </h2>

                    </div>

                </div>

                <div
                    class="flex justify-center p-3 border rounded-xl border-slate-200 bg-slate-50 print:bg-white print:p-2">

                    <img src="{{ asset('storage/' . $test->image) }}" alt="Test Sample Image" loading="eager"
                        class="block w-auto max-w-full max-h-[400px] object-contain rounded-lg shadow-sm print:max-h-[280px] print:max-w-full print:rounded-md print:shadow-none">

                </div>

            </section>
        @endif


        {{-- =========================================================
            DOSE STATISTICS
        ========================================================== --}}

        @if ($test->dosimeters->count())
            <section class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                {{-- Minimum --}}

                <div
                    class="flex items-center gap-4 p-4 bg-white border shadow-sm min-h-24 rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:shadow-none sm:p-5">

                    <div
                        class="flex items-center justify-center h-11 w-11 shrink-0 rounded-xl bg-emerald-50 text-emerald-600">

                        <i class="fa-solid fa-arrow-down"></i>

                    </div>

                    <div>

                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Minimum Measured Dose
                        </span>

                        <strong class="block mt-1 text-xl font-extrabold tracking-tight text-slate-900">

                            {{ $doseStats['min'] !== null ? $format($doseStats['min'], 4) . ' kGy' : '-' }}

                        </strong>

                    </div>

                </div>


                {{-- Average --}}

                <div
                    class="flex items-center gap-4 p-4 bg-white border shadow-sm min-h-24 rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:shadow-none sm:p-5">

                    <div class="flex items-center justify-center text-blue-600 h-11 w-11 shrink-0 rounded-xl bg-blue-50">

                        <i class="fa-solid fa-chart-line"></i>

                    </div>

                    <div>

                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Dose Uniformity Ratio (DUR)
                        </span>

                        <strong class="block mt-1 text-xl font-extrabold tracking-tight text-slate-900">

                            {{-- {{ $doseStats['avg'] !== null ? $format($doseDur['avg'], 4) . ' DUR' : '-' }} --}}
                            {{ $format($doseDur, 4) }}


                        </strong>

                    </div>

                </div>


                {{-- Maximum --}}

                <div
                    class="flex items-center gap-4 p-4 bg-white border shadow-sm min-h-24 rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:shadow-none sm:p-5">

                    <div class="flex items-center justify-center h-11 w-11 shrink-0 rounded-xl bg-amber-50 text-amber-600">

                        <i class="fa-solid fa-arrow-up"></i>

                    </div>

                    <div>

                        <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">
                            Maximum Measured Dose
                        </span>

                        <strong class="block mt-1 text-xl font-extrabold tracking-tight text-slate-900">

                            {{ $doseStats['max'] !== null ? $format($doseStats['max'], 4) . ' kGy' : '-' }}

                        </strong>

                    </div>

                </div>

            </section>
        @endif


        {{-- =========================================================
            DOSIMETER RESULT
        ========================================================== --}}

        <section
            class="p-5 bg-white border shadow-sm rounded-2xl border-slate-200/80 shadow-slate-200/30 print:break-inside-avoid print:border-slate-300 print:p-4 print:shadow-none sm:p-6"
            x-data="dosimeterEditor(@js(
    $test->dosimeters
        ->map(
            fn($d) => [
                'dosimeter_number' => $d->dosimeter_number,
                'position' => $d->position,
                'absorbance' => $d->absorbance,
            ],
        )
        ->values(),
))">

            <div class="flex items-start justify-between gap-4 mb-5">

                <div>

                    <p class="text-[9px] font-extrabold uppercase tracking-[0.15em] text-blue-600">
                        Measurement Result
                    </p>

                    <h2 class="mt-1 text-base font-bold tracking-tight text-slate-900 sm:text-lg">
                        Dosimeter & Absorbance
                    </h2>

                    <p class="mt-1.5 max-w-3xl text-xs leading-5 text-slate-500 print:hidden">

                        Optional. Tambahkan hanya bila pengujian memakai dosimeter.
                        Dose dihitung otomatis menggunakan kurva kalibrasi yang sama dengan modul Dosimeter.

                    </p>

                </div>

                <button type="button" @click="addRow()"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-bold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50 active:scale-[0.99] print:hidden">

                    <i class="fa-solid fa-plus"></i>

                    Add Reading

                </button>

            </div>


            <form method="POST" action="{{ route('admin.testing.dosimeters.update', $test) }}">

                @csrf

                @method('PUT')

                <div class="overflow-x-auto border print:overflow-visible rounded-2xl border-slate-200">

                    <table class="w-full text-left bg-white border-collapse">

                        <thead class="bg-slate-50">

                            <tr>

                                <th
                                    class="w-16 border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">

                                    #

                                </th>

                                <th
                                    class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">

                                    Dosimeter ID

                                </th>

                                <th
                                    class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">

                                    Position

                                </th>

                                <th
                                    class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">

                                    Absorbance

                                </th>

                                <th
                                    class="border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400">

                                    Calculated Dose (kGy)

                                </th>

                                <th
                                    class="w-16 border-b border-slate-200 px-4 py-3 text-[9px] font-extrabold uppercase tracking-wider text-slate-400 print:hidden">

                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <template x-for="(row, index) in rows" :key="row.key">

                                <tr>

                                    <td class="px-4 py-3 text-xs font-bold border-b border-slate-100 text-slate-500"
                                        x-text="index + 1">

                                    </td>


                                    {{-- Dosimeter ID --}}

                                    <td class="px-4 py-3 text-xs border-b border-slate-100 text-slate-600">

                                        <input :name="`readings[${index}][dosimeter_number]`"
                                            x-model="row.dosimeter_number"
                                            class="w-full rounded-lg border border-transparent bg-slate-50 px-3 py-2.5 text-base font-medium text-slate-700 outline-none placeholder:text-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/10 sm:text-xs print:hidden"
                                            placeholder="ID / serial">

                                        <span class="hidden font-medium print:inline text-slate-800"
                                            x-text="row.dosimeter_number || '-'">

                                        </span>

                                    </td>


                                    {{-- Position --}}

                                    <td class="px-4 py-3 text-xs border-b border-slate-100 text-slate-600">

                                        <input :name="`readings[${index}][position]`" x-model="row.position"
                                            class="w-full rounded-lg border border-transparent bg-slate-50 px-3 py-2.5 text-base font-medium text-slate-700 outline-none placeholder:text-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/10 sm:text-xs print:hidden"
                                            placeholder="e.g. Front / Center">

                                        <span class="hidden font-medium print:inline text-slate-800"
                                            x-text="row.position || '-'">

                                        </span>

                                    </td>


                                    {{-- Absorbance --}}

                                    <td class="px-4 py-3 text-xs border-b border-slate-100 text-slate-600">

                                        <input type="number" min="0" max="5" step="0.0001"
                                            :name="`readings[${index}][absorbance]`" x-model="row.absorbance"
                                            class="w-full rounded-lg border border-transparent bg-slate-50 px-3 py-2.5 text-base font-medium text-slate-700 outline-none placeholder:text-slate-300 focus:border-blue-400 focus:bg-white focus:ring-2 focus:ring-blue-500/10 sm:text-xs print:hidden"
                                            placeholder="0.0000">

                                        <span class="hidden font-medium print:inline text-slate-800"
                                            x-text="row.absorbance || '-'">

                                        </span>

                                    </td>


                                    {{-- Calculated Dose --}}

                                    <td class="px-4 py-3 text-xs font-bold border-b border-slate-100 text-slate-800"
                                        x-text="dose(row.absorbance)">

                                    </td>


                                    {{-- Remove --}}

                                    <td class="px-4 py-3 text-xs border-b border-slate-100 text-slate-600 print:hidden">

                                        <button type="button" @click="removeRow(index)"
                                            class="inline-flex items-center justify-center text-xs transition bg-white border h-9 w-9 shrink-0 rounded-xl border-slate-200 text-slate-500 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600">

                                            <i class="fa-solid fa-xmark"></i>

                                        </button>

                                    </td>

                                </tr>

                            </template>


                            <tr x-show="rows.length === 0">

                                <td colspan="6" class="px-4 py-10 text-sm text-center border-b-0 text-slate-400">

                                    No dosimeter reading.
                                    This section is optional.

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <div class="flex justify-end mt-4 print:hidden">

                    <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm shadow-blue-200 transition hover:bg-blue-700 active:scale-[0.99]">

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save Dosimeter Data

                    </button>

                </div>

            </form>

        </section>


        {{-- =========================================================
            PRINT FOOTER
        ========================================================== --}}

        <footer class="hidden pt-5 text-xs border-t mt-7 border-slate-200 text-slate-500 print:block">

            Generated from Beam Admin · Product Testing module ·

            {{ now()->format('d M Y H:i') }}

        </footer>

    </div>

@endsection


@push('scripts')
    <script>
        function dosimeterEditor(initialRows) {

            return {

                rows: (initialRows || []).map((row, i) => ({

                    ...row,

                    key: Date.now() + i

                })),

                addRow() {

                    this.rows.push({

                        dosimeter_number: '',

                        position: '',

                        absorbance: '',

                        key: Date.now() + Math.random()

                    });

                },

                removeRow(index) {

                    this.rows.splice(index, 1);

                },

                dose(value) {

                    if (
                        value === '' ||
                        value === null ||
                        value === undefined ||
                        Number.isNaN(Number(value))
                    ) {

                        return '-';

                    }

                    const x = Number(value);

                    const dose =
                        (13.099 * Math.pow(x, 3)) +
                        (8.7891 * Math.pow(x, 2)) +
                        (57.786 * x) -
                        2.423;

                    return window.formatSmartNumber(dose, 4, '-');

                }

            }

        }


        function printReport() {

            const images = Array.from(document.images);

            const pendingImages = images.filter(image => !image.complete);

            if (pendingImages.length === 0) {

                window.print();

                return;

            }

            let remaining = pendingImages.length;

            const printWhenReady = () => {

                remaining--;

                if (remaining <= 0) {

                    setTimeout(() => {

                        window.print();

                    }, 150);

                }

            };

            pendingImages.forEach(image => {

                image.addEventListener('load', printWhenReady, {
                    once: true
                });

                image.addEventListener('error', printWhenReady, {
                    once: true
                });

            });

            setTimeout(() => {

                window.print();

            }, 3000);

        }
    </script>
@endpush
