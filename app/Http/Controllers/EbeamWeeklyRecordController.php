<?php

namespace App\Http\Controllers;

use App\Models\EbeamWeeklyRecord;
use App\Models\ProductionLine;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EbeamWeeklyRecordController extends Controller
{
    private const NUMERIC_FIELDS = [
        'beam_state.frequency', 'beam_state.power',
        'filament.electron_gun_current', 'filament.klystron_current',
        'beam_exit.dc_voltage', 'beam_exit.pulse_current', 'beam_exit.gun_firing',
        'beam_exit.pulsed_beam_current', 'beam_exit.average_beam_current',
        'beam_exit.frequency',
    ];

    private const COMPONENTS = [
        'klystron', 'electron_gun', 'drift_segment_1', 'drift_segment_2', 'scanning_box_1', 'scanning_box_2',
    ];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'week' => ['nullable', 'date'],
            'machine' => ['nullable', 'integer', 'exists:production_lines,id'],
        ]);
        $week = !empty($filters['week'])
            ? Carbon::parse($filters['week'])->startOfWeek(Carbon::MONDAY)->toDateString()
            : null;

        $records = EbeamWeeklyRecord::with('productionLine')
            ->when($week, fn ($query) => $query->where('week_start', $week))
            ->when(!empty($filters['machine']), fn ($query) => $query->where('production_line_id', $filters['machine']))
            ->orderByDesc('week_start')
            ->orderBy('production_line_id')
            ->paginate(15)
            ->withQueryString();

        $machines = ProductionLine::orderBy('name')->get();

        return view('admin.ebeam-weekly.index', compact('records', 'machines', 'week'));
    }

    public function create(Request $request)
    {
        $machines = ProductionLine::orderBy('name')->get();
        $record = new EbeamWeeklyRecord([
            'production_line_id' => $request->integer('machine') ?: null,
            'record_date' => now()->toDateString(),
            'readings' => [],
        ]);

        return view('admin.ebeam-weekly.form', compact('record', 'machines'));
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $recordDate = Carbon::parse($validated['record_date']);

        try {
            $record = EbeamWeeklyRecord::create([
                'production_line_id' => $validated['production_line_id'],
                'record_date' => $recordDate->toDateString(),
                'week_start' => $recordDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                'readings' => $validated['readings'] ?? [],
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                return back()->withInput()->withErrors([
                    'production_line_id' => 'Weekly Record untuk mesin ini pada minggu tersebut sudah ada. Buka laporan yang ada untuk mengubahnya.',
                ]);
            }
            throw $exception;
        }

        return redirect()->route('admin.ebeam-weekly.index')
            ->with('success', 'Weekly Record berhasil disimpan.');
    }

    public function edit(EbeamWeeklyRecord $record)
    {
        $record->load('productionLine');
        $machines = ProductionLine::orderBy('name')->get();

        return view('admin.ebeam-weekly.form', compact('record', 'machines'));
    }

    public function update(Request $request, EbeamWeeklyRecord $record)
    {
        $validated = $this->validated($request, $record);
        $recordDate = Carbon::parse($validated['record_date']);

        try {
            $record->update([
                'production_line_id' => $validated['production_line_id'],
                'record_date' => $recordDate->toDateString(),
                'week_start' => $recordDate->copy()->startOfWeek(Carbon::MONDAY)->toDateString(),
                'readings' => $validated['readings'] ?? [],
            ]);
        } catch (QueryException $exception) {
            if ($this->isUniqueViolation($exception)) {
                return back()->withInput()->withErrors([
                    'production_line_id' => 'Weekly Record untuk mesin ini pada minggu tersebut sudah ada.',
                ]);
            }
            throw $exception;
        }

        return redirect()->route('admin.ebeam-weekly.index')
            ->with('success', 'Weekly Record berhasil diperbarui.');
    }

    public function download(EbeamWeeklyRecord $record)
    {
        $record->load('productionLine');
        $filename = $record->record_date->format('Ymd') . ' Weekly Report E-Beam AEET BRIN.pdf';

        return Pdf::loadView('admin.ebeam-weekly.print', compact('record'))
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    private function validated(Request $request, ?EbeamWeeklyRecord $record = null): array
    {
        $rules = [
            'production_line_id' => ['required', 'integer', Rule::exists('production_lines', 'id')->whereNull('deleted_at')],
            'record_date' => ['required', 'date'],
            'readings' => ['nullable', 'array'],
            'readings.vacuum_before' => ['nullable', 'array'],
            'readings.vacuum_after' => ['nullable', 'array'],
            'readings.beam_state' => ['nullable', 'array'],
            'readings.filament' => ['nullable', 'array'],
            'readings.beam_exit' => ['nullable', 'array'],
            'readings.equipment_time' => ['nullable', 'array'],
            'readings.equipment_time.turned_on_meter' => ['nullable', 'numeric', 'gte:0'],
            'readings.equipment_time.turned_on_time' => ['nullable', 'date_format:H:i'],
            'readings.equipment_time.shut_down_meter' => ['nullable', 'numeric', 'gte:0'],
            'readings.equipment_time.shut_down_time' => ['nullable', 'date_format:H:i'],
        ];

        foreach (self::NUMERIC_FIELDS as $field) {
            $rules['readings.' . $field] = ['nullable', 'numeric'];
        }

        foreach (['vacuum_before', 'vacuum_after'] as $group) {
            foreach (['current', 'voltage'] as $metric) {
                foreach (self::COMPONENTS as $component) {
                    $rules["readings.{$group}.{$metric}.{$component}"] = ['nullable', 'numeric'];
                }
            }
        }

        return $request->validate($rules);
    }

    private function isUniqueViolation(QueryException $exception): bool
    {
        return in_array($exception->getCode(), ['23000', '23505'], true)
            || str_contains(strtolower($exception->getMessage()), 'ebeam_weekly_machine_week_unique');
    }
}
