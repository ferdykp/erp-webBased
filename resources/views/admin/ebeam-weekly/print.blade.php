<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $record->record_date->format('Ymd') }} Weekly Report E-Beam AEET BRIN</title>
    <style>
        @page { size: A4 portrait; margin: 22mm 12mm 10mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #222; font-family: "DejaVu Serif", serif; font-size: 9pt; line-height: 1.08; }
        .top-rule { border-top: 1px solid #555; margin: 0 8mm 4mm; }
        h1 { margin: 0; text-align: center; font-size: 14pt; line-height: 1.12; }
        .unit { margin: 1mm 7mm 1mm 0; text-align: right; font-size: 9pt; }
        .date-line { margin: 0 0 1mm; text-align: right; }
        .date-value { display: inline-block; min-width: 28mm; text-align: left; }
        .section-head { width: 100%; margin: 0.5mm 0 0; }
        .section-head .date { float: right; }
        table { border-collapse: collapse; }
        .parameter-table { width: 88%; margin: 0 0 1mm 0; table-layout: fixed; }
        .parameter-table th, .parameter-table td { border: 0.5pt solid #999; padding: 1mm 0.5mm; text-align: center; vertical-align: middle; font-weight: normal; }
        .parameter-table thead th { height: 11mm; }
        .parameter-table .row-label { width: 17%; text-align: left; padding-left: 1.5mm; }
        .beam-state-table { width: auto; margin: 0.5mm 0 1mm 2mm; border-collapse: collapse; table-layout: auto; }
        .beam-state-table td { border: 0; padding: 0.25mm 1mm; line-height: 1.12; white-space: nowrap; }
        .beam-state-table .state-label { width: 24mm; }
        .beam-state-table .state-value { min-width: 18mm; text-align: left; }
        .beam-state-table .state-unit { min-width: 10mm; }
        .filament-table { width: 62%; margin: 1mm auto 0.5mm; table-layout: fixed; font-size: 8pt; }
        .filament-table td { border: 0.5pt solid #999; padding: 1mm; text-align: center; }
        .note { margin: 0.5mm 0 2mm 2mm; font-size: 8pt; line-height: 1.1; }
        .beam-exit { width: 90%; margin: 0 0 0.5mm; table-layout: fixed; }
        .beam-exit th, .beam-exit td { border: 0.5pt solid #999; padding: 1mm 0.5mm; text-align: center; vertical-align: middle; font-weight: normal; }
        .beam-exit th { height: 12mm; }
        .equipment-time { width: 100%; margin: 2mm 0 2mm; table-layout: fixed; }
        .equipment-time td { border: 0.5pt solid #999; padding: 1mm; text-align: center; height: 6mm; }
        .signatures { width: 100%; table-layout: fixed; }
        .signatures td { border: 0.5pt solid #777; height: 25mm; padding: 1mm; vertical-align: top; }
        .signature-title { margin-bottom: 3mm; }
        .signature-lines { line-height: 1.35; }
        .blank { display: inline-block; min-width: 43mm; height: 2.5mm; }
    </style>
</head>
<body>
    @php
        $data = $record->readings ?? [];
        $components = [
            'klystron' => 'Klystron',
            'electron_gun' => 'Electron gun',
            'drift_segment_1' => 'Drift segment 1',
            'drift_segment_2' => 'Drift segment 2',
            'scanning_box_1' => 'Scanning box 1',
            'scanning_box_2' => 'Scanning box 2',
        ];
    @endphp
    <div class="top-rule"></div>
    <h1>Weekly Record of IS1020 Radiation Accelerator</h1>
    <div class="unit">Accelerator unit: {{ $record->productionLine?->name ?? '' }}</div>
    <div class="section-head">1. Vacuum parameters <span class="date">Date: <span class="date-value">{{ $record->record_date->format('d/m/Y') }}</span></span></div>
    @foreach(['vacuum_before', 'vacuum_after'] as $group)
        <table class="parameter-table">
            <thead><tr><th class="row-label"></th>@foreach($components as $label)<th>{{ $label }}</th>@endforeach</tr></thead>
            <tbody>
                <tr><th class="row-label">Current<br>(µA)</th>@foreach($components as $key => $label)<td>{{ data_get($data, "{$group}.current.{$key}", '') }}</td>@endforeach</tr>
                <tr><th class="row-label">Voltage<br>(kV)</th>@foreach($components as $key => $label)<td>{{ data_get($data, "{$group}.voltage.{$key}", '') }}</td>@endforeach</tr>
            </tbody>
        </table>
        @if($group === 'vacuum_before')
            <table class="beam-state-table">
                <tbody>
                    <tr><td colspan="4">Beam state,</td></tr>
                    <tr><td class="state-label">Frequency</td><td>:</td><td class="state-value">{{ data_get($data, 'beam_state.frequency', '') }}</td><td class="state-unit">Hz</td></tr>
                    <tr><td class="state-label">Beam power</td><td>:</td><td class="state-value">{{ data_get($data, 'beam_state.power', '') }}</td><td class="state-unit">kW</td></tr>
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="section-head">2. Filament parameters</div>
    <table class="filament-table"><thead><tr><th>Electron gun filament current A</th><th>Klystron filament current A</th></tr></thead>
        <tbody><tr><td>{{ data_get($data, 'filament.electron_gun_current', '') }}</td><td>{{ data_get($data, 'filament.klystron_current', '') }}</td></tr></tbody>
    </table>
    <div class="note">Note: After preheating, read on the touch screen instrument interface.</div>

    <div class="section-head">3. Beam exit parameters</div>
    <table class="beam-exit">
        <thead><tr><th>DC Voltage V</th><th>Pulse current A</th><th>Gun firing A</th><th>Pulsed beam current A</th><th>Average beam current mA</th><th>Beam frequency Hz</th></tr></thead>
        <tbody><tr>
            <td>{{ data_get($data, 'beam_exit.dc_voltage', '') }}</td><td>{{ data_get($data, 'beam_exit.pulse_current', '') }}</td>
            <td>{{ data_get($data, 'beam_exit.gun_firing', '') }}</td><td>{{ data_get($data, 'beam_exit.pulsed_beam_current', '') }}</td>
            <td>{{ data_get($data, 'beam_exit.average_beam_current', '') }}</td><td>{{ data_get($data, 'beam_exit.frequency', '') }}</td>
        </tr></tbody>
    </table>
    <div class="note">Note: Pulse current, gun emission, and pulse beam current are observed and measured using an oscilloscope.<br>Pulse current 1V corresponds to 10A, while gun emission and pulse beam current 1V correspond to 1A; View the touch screen instrument interface with average beam current.</div>

    <div class="section-head">4. Equipment operating time</div>
    @php
        $equipmentTime = $record->equipment_operating_time;
        $onTime = str_replace(':', '.', $equipmentTime['turned_on_time']);
        $offTime = str_replace(':', '.', $equipmentTime['shut_down_time']);
        $onValue = trim($equipmentTime['turned_on_meter'] . ' ' . $onTime);
        $offValue = trim($equipmentTime['shut_down_meter'] . ' ' . $offTime);
    @endphp
    <table class="equipment-time"><tr><td>Time when the device is turned on</td><td>Time when the device shuts down</td></tr><tr><td>{{ $onValue }}</td><td>{{ $offValue }}</td></tr></table>

    <table class="signatures"><tr>
        <td><div class="signature-title">Technical Engineer</div><div class="signature-lines">Signature: <span class="blank"></span><br>Name: <span class="blank"></span><br>Date: <span class="blank"></span></div></td>
        <td><div class="signature-title">Client Representative</div><div class="signature-lines">Signature: <span class="blank"></span><br>Name: <span class="blank"></span><br>Date: <span class="blank"></span></div></td>
    </tr></table>
</body>
</html>
