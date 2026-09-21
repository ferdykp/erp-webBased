<?php

namespace App\Http\Controllers;

use App\Models\ProductTest;
use App\Models\ProductTestDosimeter;
use App\Models\ProductionLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductTestingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', ''));

        $tests = ProductTest::with(['productionLine', 'dosimeters'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('test_code', 'like', "%{$search}%")
                        ->orWhere('sample_name', 'like', "%{$search}%")
                        ->orWhere('requester_name', 'like', "%{$search}%")
                        ->orWhere('requester_organization', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['parameter_pending', 'completed'], true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => ProductTest::count(),
            'pending' => ProductTest::where('status', 'parameter_pending')->count(),
            'completed' => ProductTest::where('status', 'completed')->count(),
        ];

        return view('admin.testing.index', compact('tests', 'search', 'status', 'stats'));
    }

    public function create()
    {
        return view('admin.testing.form', ['test' => null]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateTestData($request);

        $test = DB::transaction(function () use ($validated) {
            return ProductTest::create([
                ...$validated,
                'test_code' => $this->nextCode(),
                'status' => 'parameter_pending',
            ]);
        });

        return redirect()
            ->route('admin.testing.parameters', $test)
            ->with('success', 'Data product test tersimpan. Lanjutkan dengan process parameter.');
    }

    public function edit(ProductTest $test)
    {
        return view('admin.testing.form', compact('test'));
    }

    public function update(Request $request, ProductTest $test)
    {
        $validated = $this->validateTestData($request);
        $test->update($validated);

        $nextRoute = $test->status === 'completed'
            ? route('admin.testing.report', $test)
            : route('admin.testing.parameters', $test);

        return redirect($nextRoute)->with('success', 'Data product test berhasil diperbarui.');
    }

    public function parameters(ProductTest $test)
    {
        $productionLines = ProductionLine::orderBy('name')->get();
        return view('admin.testing.parameters', compact('test', 'productionLines'));
    }

    public function storeParameters(Request $request, ProductTest $test)
    {
        $validated = $request->validate([
            'production_line_id' => 'required|exists:production_lines,id',
            'target_dose' => 'nullable|numeric|min:0',
            'beam_speed' => 'required|numeric|min:0.0001',
            'loading_mode' => 'required|string|in:single-side,double-side,custom',
            'custom_loading_mode' => 'nullable|required_if:loading_mode,custom|string|max:100',
            'freq' => 'required|numeric|min:0',
            'scan_gear' => 'required|numeric|min:0',
            'process_notes' => 'nullable|string|max:4000',
        ]);

        $loadingMode = $validated['loading_mode'] === 'custom'
            ? trim((string) $validated['custom_loading_mode'])
            : $validated['loading_mode'];

        $test->update([
            'production_line_id' => $validated['production_line_id'],
            'target_dose' => $validated['target_dose'] ?? null,
            'beam_speed' => $validated['beam_speed'],
            'loading_mode' => $loadingMode,
            'freq' => $validated['freq'] ?? null,
            'scan_gear' => $validated['scan_gear'] ?? null,
            'process_notes' => $validated['process_notes'] ?? null,
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        return redirect()
            ->route('admin.testing.report', $test)
            ->with('success', 'Process parameter tersimpan. Product test selesai dan report sudah siap.');
    }

    public function report(ProductTest $test)
    {
        if ($test->status !== 'completed') {
            return redirect()->route('admin.testing.parameters', $test)
                ->with('error', 'Lengkapi process parameter sebelum membuka report.');
        }

        $test->load(['productionLine', 'dosimeters']);

        $doseValues = $test->dosimeters->pluck('dose_kgy')->filter(fn ($value) => $value !== null)->map(fn ($value) => (float) $value);
        $doseStats = [
            'min' => $doseValues->isNotEmpty() ? $doseValues->min() : null,
            'max' => $doseValues->isNotEmpty() ? $doseValues->max() : null,
            'avg' => $doseValues->isNotEmpty() ? $doseValues->avg() : null,
        ];

        return view('admin.testing.report', compact('test', 'doseStats'));
    }

    public function storeDosimeters(Request $request, ProductTest $test)
    {
        if ($test->status !== 'completed') {
            return redirect()->route('admin.testing.parameters', $test)
                ->with('error', 'Lengkapi process parameter sebelum mengisi dosimeter.');
        }

        $validated = $request->validate([
            'readings' => 'nullable|array|max:50',
            'readings.*.dosimeter_number' => 'nullable|string|max:100',
            'readings.*.position' => 'nullable|string|max:100',
            'readings.*.absorbance' => 'nullable|numeric|min:0|max:5',
        ]);

        $rows = collect($validated['readings'] ?? [])
            ->map(fn ($row) => [
                'dosimeter_number' => trim((string) ($row['dosimeter_number'] ?? '')) ?: null,
                'position' => trim((string) ($row['position'] ?? '')) ?: null,
                'absorbance' => array_key_exists('absorbance', $row) && $row['absorbance'] !== null && $row['absorbance'] !== ''
                    ? (float) $row['absorbance']
                    : null,
            ])
            ->filter(fn ($row) => $row['dosimeter_number'] !== null || $row['position'] !== null || $row['absorbance'] !== null)
            ->values();

        DB::transaction(function () use ($test, $rows) {
            $test->dosimeters()->delete();

            foreach ($rows as $index => $row) {
                $absorbance = $row['absorbance'];
                $dose = $absorbance === null ? null : $this->calculateDose($absorbance);

                ProductTestDosimeter::create([
                    'product_test_id' => $test->id,
                    'sequence' => $index + 1,
                    'dosimeter_number' => $row['dosimeter_number'],
                    'position' => $row['position'],
                    'absorbance' => $absorbance,
                    'dose_kgy' => $dose,
                ]);
            }
        });

        return redirect()
            ->route('admin.testing.report', $test)
            ->with('success', 'Data dosimeter dan absorbance berhasil disimpan.');
    }

    public function destroy(ProductTest $test)
    {
        $test->delete();
        return redirect()->route('admin.testing.index')->with('success', 'Product test berhasil dihapus.');
    }

    private function validateTestData(Request $request): array
    {
        return $request->validate([
            'requester_name' => 'nullable|string|max:255',
            'requester_organization' => 'nullable|string|max:255',
            'requester_contact' => 'nullable|string|max:255',
            'sample_name' => 'required|string|max:255',
            'quantity' => 'nullable|numeric|min:0.001',
            'unit' => 'nullable|string|max:50',
            'dmin' => 'nullable|numeric|min:0',
            'dmax' => 'nullable|numeric|min:0|gte:dmin',
            'expected_temperature' => 'nullable|string|max:100',
            'length_cm' => 'nullable|numeric|min:0',
            'width_cm' => 'nullable|numeric|min:0',
            'height_cm' => 'nullable|numeric|min:0',
            'net_weight_kg' => 'nullable|numeric|min:0',
            'gross_weight_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:4000',
        ]);
    }

    private function nextCode(): string
    {
        $prefix = 'PT-' . now()->format('ymd') . '-';
        $lastCode = ProductTest::where('test_code', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('test_code')
            ->value('test_code');

        $lastSequence = $lastCode ? (int) substr($lastCode, strlen($prefix)) : 0;
        return $prefix . str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);
    }

    private function calculateDose(float $absorbance): float
    {
        return round((13.099 * pow($absorbance, 3)) + (8.7891 * pow($absorbance, 2)) + (57.786 * $absorbance) - 2.423, 4);
    }
}
