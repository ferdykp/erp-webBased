@extends('admin.layout.app')

@php
    $isEdit = $record->exists;
    $readings = old('readings', $record->readings ?? []);
    $components = [
        'klystron' => 'Klystron',
        'electron_gun' => 'Electron gun',
        'drift_segment_1' => 'Drift segment 1',
        'drift_segment_2' => 'Drift segment 2',
        'scanning_box_1' => 'Scanning box 1',
        'scanning_box_2' => 'Scanning box 2',
    ];
    $value = fn ($path) => data_get($readings, $path, '');
    $equipmentTime = $record->equipment_operating_time;
@endphp

@section('title', $isEdit ? 'Edit Weekly Record' : 'Buat Weekly Record')

@section('content')
<div class="mx-auto max-w-6xl space-y-5">
    <div class="page-heading-card flex flex-wrap items-end justify-between gap-3">
        <div>
            <a href="{{ route('admin.ebeam-weekly.index') }}" class="text-xs font-bold text-blue-700 hover:underline">← Weekly Monitoring</a>
            <h1 class="mt-2 text-2xl font-black tracking-tight text-slate-900">{{ $isEdit ? 'Weekly Record' : 'Buat Weekly Record' }}</h1>
            <p class="mt-1 text-sm text-slate-500">Isi data secara manual. Tanggal dapat dipilih untuk mencatat minggu sebelumnya.</p>
        </div>
        @if($isEdit)
            <a href="{{ route('admin.ebeam-weekly.download', $record) }}" onclick="return confirm('Are you sure you want to download this Weekly Record PDF?')" class="inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-bold text-blue-700 hover:bg-blue-50"><i class="fa-solid fa-download"></i> Download PDF</a>
        @endif
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.ebeam-weekly.update', $record) : route('admin.ebeam-weekly.store') }}" class="space-y-5">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <section class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 sm:grid-cols-2">
            <label class="text-xs font-extrabold uppercase tracking-wide text-slate-600">Mesin <span class="text-rose-500">*</span>
                <select name="production_line_id" required class="mt-2 w-full rounded-xl border-slate-300 text-sm font-medium normal-case tracking-normal text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Pilih mesin</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}" @selected(old('production_line_id', $record->production_line_id) == $machine->id)>{{ $machine->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs font-extrabold uppercase tracking-wide text-slate-600">Tanggal Weekly Record <span class="text-rose-500">*</span>
                <input id="recordDate" type="date" name="record_date" required value="{{ old('record_date', $record->record_date?->format('Y-m-d') ?? now()->toDateString()) }}" class="mt-2 w-full rounded-xl border-slate-300 text-sm font-medium normal-case tracking-normal text-slate-800 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span id="weekRange" class="mt-1 block text-[11px] font-medium normal-case tracking-normal text-slate-500"></span>
            </label>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <div class="mb-4"><h2 class="font-black text-slate-900">1. Vacuum parameters</h2><p class="mt-1 text-xs text-slate-500">Isi kedua tabel pengukuran sesuai template.</p></div>
            @foreach([['vacuum_before', 'Pembacaan 1'], ['vacuum_after', 'Pembacaan 2']] as [$group, $label])
                <div class="mb-5 last:mb-0">
                    <p class="mb-2 text-xs font-bold text-slate-600">{{ $label }}</p>
                    <div class="overflow-x-auto rounded-xl border border-slate-200">
                        <table class="app-data-table min-w-[760px] w-full table-fixed text-center text-xs">
                            <thead class="bg-slate-50 text-slate-600"><tr><th class="w-28 p-2 text-left">Parameter</th>@foreach($components as $component)<th class="p-2">{{ $component }}</th>@endforeach</tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach(['current' => 'Current (µA)', 'voltage' => 'Voltage (kV)'] as $metric => $metricLabel)
                                    <tr><th class="p-2 text-left font-bold text-slate-700">{{ $metricLabel }}</th>@foreach($components as $key => $component)<td class="p-1"><input type="number" step="any" name="readings[{{ $group }}][{{ $metric }}][{{ $key }}]" value="{{ $value("{$group}.{$metric}.{$key}") }}" class="w-full rounded-lg border-slate-200 px-2 py-2 text-center text-xs focus:border-blue-500 focus:ring-blue-500" aria-label="{{ $label }} {{ $component }} {{ $metricLabel }}"></td>@endforeach</tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($group === 'vacuum_before')
                    <div class="mb-5 grid gap-3 sm:grid-cols-2">
                        <label class="text-xs font-bold text-slate-600">Beam state · Frequency (Hz)<input type="number" step="any" name="readings[beam_state][frequency]" value="{{ $value('beam_state.frequency') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm"></label>
                        <label class="text-xs font-bold text-slate-600">Beam state · Beam power (kW)<input type="number" step="any" name="readings[beam_state][power]" value="{{ $value('beam_state.power') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm"></label>
                    </div>
                @endif
            @endforeach
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-black text-slate-900">2. Filament parameters</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="text-xs font-bold text-slate-600">Electron gun filament current (A)<input type="number" step="any" name="readings[filament][electron_gun_current]" value="{{ $value('filament.electron_gun_current') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm"></label>
                <label class="text-xs font-bold text-slate-600">Klystron filament current (A)<input type="number" step="any" name="readings[filament][klystron_current]" value="{{ $value('filament.klystron_current') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm"></label>
            </div>
            <p class="mt-3 text-xs text-slate-500">Catatan: After preheating, read on the touch screen instrument interface.</p>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-black text-slate-900">3. Beam exit parameters</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    'dc_voltage' => 'DC Voltage (V)', 'pulse_current' => 'Pulse current (A)',
                    'gun_firing' => 'Gun firing (A)', 'pulsed_beam_current' => 'Pulsed beam current (A)',
                    'average_beam_current' => 'Average beam current (mA)', 'frequency' => 'Beam frequency (Hz)',
                ] as $key => $label)
                    <label class="text-xs font-bold text-slate-600">{{ $label }}<input type="number" step="any" name="readings[beam_exit][{{ $key }}]" value="{{ $value("beam_exit.{$key}") }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm"></label>
                @endforeach
            </div>
            <p class="mt-3 text-xs leading-relaxed text-slate-500">Note: Pulse current, gun emission, and pulse beam current are observed and measured using an oscilloscope. Pulse current 1V corresponds to 10A, while gun emission and pulse beam current 1V correspond to 1A; view the touch screen instrument interface with average beam current.</p>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-black text-slate-900">4. Equipment operating time</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                @foreach([['turned_on', 'Time when the device is turned on'], ['shut_down', 'Time when the device shuts down']] as [$event, $label])
                    <fieldset class="min-w-0 space-y-3 rounded-xl border border-slate-200 bg-slate-50/60 p-4">
                        <legend class="px-1 text-xs font-bold text-slate-600">{{ $label }}</legend>
                        <label class="block text-xs font-semibold text-slate-600">Hour meter (decimal)
                            <input type="number" min="0" step="any" name="readings[equipment_time][{{ $event }}_meter]" value="{{ old("readings.equipment_time.{$event}_meter", $equipmentTime["{$event}_meter"] ?? '') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm">
                        </label>
                        <label class="block text-xs font-semibold text-slate-600">Time
                            <input type="time" step="60" name="readings[equipment_time][{{ $event }}_time]" value="{{ old("readings.equipment_time.{$event}_time", $equipmentTime["{$event}_time"] ?? '') }}" class="mt-1.5 w-full rounded-xl border-slate-300 text-sm shadow-sm">
                        </label>
                    </fieldset>
                @endforeach
            </div>
        </section>

        <div class="flex flex-wrap justify-end gap-3 pb-4">
            <a href="{{ route('admin.ebeam-weekly.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700">Kembali</a>
            <button class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700"><i class="fa-solid fa-floppy-disk mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Weekly Record' }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const input = document.getElementById('recordDate');
        const output = document.getElementById('weekRange');
        if (!input || !output) return;
        const updateRange = () => {
            if (!input.value) { output.textContent = ''; return; }
            const selected = new Date(`${input.value}T12:00:00`);
            const monday = new Date(selected);
            const weekday = (selected.getDay() + 6) % 7;
            monday.setDate(selected.getDate() - weekday);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            const format = date => new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }).format(date);
            output.textContent = `Laporan untuk minggu ${format(monday)} – ${format(sunday)}`;
        };
        input.addEventListener('input', updateRange);
        updateRange();
    })();
</script>
@endpush
