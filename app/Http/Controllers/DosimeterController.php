<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\DosimeterRecord;
use App\Models\DosimeterDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DosimeterController extends Controller
{
    /**
     * Menampilkan daftar seluruh booking dengan fitur pencarian.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $bookings = Booking::with(['customer', 'products'])
            ->when($search, function ($query, $search) {
                return $query->where(function ($subQuery) use ($search) {
                    // 1. Cari berdasarkan kode booking
                    $subQuery->where('bookings.booking_code', 'LIKE', "%{$search}%")

                        // 2. Cari berdasarkan data Customer
                        // PERBAIKAN: Menghapus kolom 'name' yang memicu crash jika tidak ada di database
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('company_name', 'LIKE', "%{$search}%");
                        })

                        // 3. Cari berdasarkan nama Produk
                        ->orWhereHas('products', function ($q) use ($search) {
                            $q->where('product_name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->orderBy('bookings.created_at', 'desc')
            ->paginate(10);

        // Respons untuk AJAX JavaScript
        if ($request->ajax()) {
            return response(view('admin.dosimeter.table', compact('bookings'))->render());
        }

        // Respons normal browser
        return view('admin.dosimeter.index', compact('bookings'));
    }

    /**
     * Menampilkan halaman detail input dosimeter berdasarkan ID Booking.
     */
    public function show(Request $request, $bookingId)
    {
        $booking = Booking::with(['products', 'customer', 'batches'])->findOrFail($bookingId);
        $records = DosimeterRecord::with('details')->where('booking_id', $bookingId)->orderBy('id')->get();
        $record = $request->filled('record') ? $records->firstWhere('id', $request->integer('record')) : $records->first();
        abort_if($request->filled('record') && !$record, 404);

        $evidenceLocked = $booking->status !== 'completed' || DB::table('order_dispatches')->where('booking_id', $bookingId)->where('status', 'dispatched')->exists();
        return view('admin.dosimeter.show', compact('booking', 'record', 'records', 'evidenceLocked'));
    }

    /**
     * STEP 1 API: Membuat/Generate Kuantitas Baris Tablet Dosimeter
     */
    public function storeQuantity(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer|exists:bookings,id',
            'tablet_quantity' => 'required|integer|min:1|max:50',
            'record_id' => 'nullable|integer',
        ]);

        // Resize without regenerating measurements. Recorded dosimeter IDs, absorbance,
        // dose calculations and evidence images must never be silently destroyed.
        $record = DB::transaction(function () use ($validated) {
            $booking = Booking::whereKey($validated['booking_id'])->lockForUpdate()->firstOrFail();
            $this->assertEditable($booking);
            if (!empty($validated['record_id'])) {
                $record = DosimeterRecord::where('booking_id', $booking->id)->find($validated['record_id']);
                if (!$record) throw \Illuminate\Validation\ValidationException::withMessages(['record_id' => 'Select a dosimetry set belonging to this order.']);
            } else {
                $record = DosimeterRecord::firstOrCreate(
                    ['booking_id' => $validated['booking_id']],
                    ['tablet_quantity' => 0]
                );
            }
            $record = DosimeterRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
            $quantity = (int) $validated['tablet_quantity'];

            $details = $record->details()->get()->keyBy('tablet_number');
            $tail = $details->filter(fn($detail) => (int) $detail->tablet_number > $quantity);
            $filled = $tail->filter(
                fn($detail) =>
                $detail->absorbance !== null ||
                    $detail->dose_kgy !== null ||
                    filled($detail->dosimeter_number) ||
                    filled($detail->position) ||
                    filled($detail->image)
            );
            if ($filled->isNotEmpty()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tablet_quantity' => 'Cannot reduce tablet quantity: measurements or evidence exist in the rows that would be removed. Retain the current quantity and review the records first.',
                ]);
            }
            if ($tail->isNotEmpty()) {
                $record->details()->whereIn('id', $tail->pluck('id'))->delete();
            }
            for ($i = 1; $i <= $quantity; $i++) {
                if (! $details->has($i)) {
                    $record->details()->create(['tablet_number' => $i]);
                }
            }
            $record->update(['tablet_quantity' => $quantity]);
            return $record->load('details');
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Dosimeter rows updated. Existing measurements were preserved.',
            'data' => $record,
        ]);
    }

    /**
     * STEP 2 API: Menyimpan Nilai Absorbance, Hitung Dose Otomatis, & Upload 1 Gambar Global
     */
    public function storeAbsorbance(Request $request, $recordId)
    {
        $validated = $request->validate([
            'dosimeter_number' => 'required|array',
            'dosimeter_number.*' => 'required|string|max:100',
            'absorbance' => 'required|array',
            'absorbance.*' => 'required|numeric|min:0|max:5',
            'batch_id' => 'nullable|array',
            'batch_id.*' => 'nullable|integer',
            'position' => ['nullable', 'array'],
            'position.*' => ['nullable', 'string', 'max:150'],
            'global_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $record = DosimeterRecord::with('details')->findOrFail($recordId);
        $expectedNumbers = $record->details->pluck('tablet_number')->map(fn($n) => (string) $n)->sort()->values();
        $submittedNumbers = collect(array_keys($validated['absorbance']))->map(fn($n) => (string) $n)->sort()->values();

        if ($expectedNumbers->all() !== $submittedNumbers->all()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dosimeter rows changed. Reload and try again.',
            ], 422);
        }

        $identifierNumbers = collect(array_keys($validated['dosimeter_number']))->map(fn($n) => (string) $n)->sort()->values();
        if ($identifierNumbers->all() !== $submittedNumbers->all()) {
            return response()->json(['status' => 'error', 'message' => 'Dosimeter identifiers and measurements are incomplete.'], 422);
        }

        try {
            DB::transaction(function () use ($request, $validated, $record) {
                $booking = Booking::whereKey($record->booking_id)->lockForUpdate()->firstOrFail();
                $this->assertEditable($booking);
                $locked = DosimeterRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
                if ($locked->details()->count() !== count($validated['absorbance'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages(['absorbance' => 'The dosimeter rows changed. Reload before saving.']);
                }
                $before = $locked->load('details')->toArray();
                $batches = $booking->batches()->get()->keyBy('id');
                foreach ($validated['absorbance'] as $tabletNumber => $value) {
                    $assigned = array_key_exists($tabletNumber, $validated['batch_id'] ?? [])
                        ? $validated['batch_id'][$tabletNumber]
                        : $locked->details->firstWhere('tablet_number', $tabletNumber)?->booking_batch_id;
                    if ($assigned && (!$batches->has($assigned) || $batches[$assigned]->status !== 'done')) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'batch_id' => 'Select a completed batch belonging to this order.',
                        ]);
                    }
                }
                $uploadedImagePath = $request->hasFile('global_image')
                    ? $request->file('global_image')->store('dosimeter_images', 'public')
                    : null;

                foreach ($validated['absorbance'] as $tabletNumber => $value) {
                    $detail = DosimeterDetail::where('dosimeter_record_id', $record->id)
                        ->where('tablet_number', $tabletNumber)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $x = (float) $value;
                    $updateData = [
                        'booking_batch_id' => array_key_exists($tabletNumber, $validated['batch_id'] ?? [])
                            ? $validated['batch_id'][$tabletNumber] : $detail->booking_batch_id,
                        'dosimeter_number' => $validated['dosimeter_number'][$tabletNumber] ?? null,
                        'position' => array_key_exists($tabletNumber, $validated['position'] ?? [])
                            ? $validated['position'][$tabletNumber]
                            : $detail->position,
                        'absorbance' => $x,
                        'dose_kgy' => (13.099 * pow($x, 3)) + (8.7891 * pow($x, 2)) + (57.786 * $x) - 2.423,

                    ];

                    if ($uploadedImagePath !== null && (int) $tabletNumber === 1) {
                        // Keep the previous evidence file for audit and correction history.
                        $updateData['image'] = $uploadedImagePath;
                    }

                    $detail->update($updateData);
                }
                \App\Services\WarehouseInventory::audit('dosimetry.saved', 'booking', $booking->id, ['before' => $before, 'after' => $locked->fresh()->load('details')->toArray()]);
            });

            session()->flash('success', 'Dosimeter data and image successfully saved.');
            return response()->json([
                'status' => 'success',
                'redirect' => route('admin.dosimeter.show', $record->booking_id),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Dosimetry save failed', ['record_id' => $recordId, 'exception' => $e]);
            return response()->json(['status' => 'error', 'message' => 'Measurements could not be saved. Reload and try again.'], 500);
        }
    }
    private function assertEditable(Booking $booking): void
    {
        if ($booking->status !== 'completed' || DB::table('order_dispatches')->where('booking_id', $booking->id)->where('status', 'dispatched')->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['dosimetry' => 'Dosimetry can be recorded after production is complete and before dispatch. Dispatched evidence is locked.']);
        }
    }

    public function destroyDetail(Request $request, DosimeterDetail $detail)
    {
        $validated = $request->validate([
            'confirm_delete' => ['required', 'accepted'],
        ]);

        $result = DB::transaction(function () use ($detail) {

            // Lock record induk terlebih dahulu.
            // Urutan locking mengikuti pola penyimpanan existing.
            $record = DosimeterRecord::whereKey(
                $detail->dosimeter_record_id
            )->firstOrFail();

            $booking = Booking::whereKey($record->booking_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertEditable($booking);

            $record = DosimeterRecord::whereKey($record->id)
                ->lockForUpdate()
                ->firstOrFail();

            $details = DosimeterDetail::where(
                'dosimeter_record_id',
                $record->id
            )
                ->orderBy('tablet_number')
                ->lockForUpdate()
                ->get();

            $target = $details->firstWhere('id', $detail->id);

            if (!$target) {
                throw ValidationException::withMessages([
                    'detail' => 'Dosimeter row no longer exists.',
                ]);
            }

            // Jangan hapus baris terakhir.
            // Set tetap harus memiliki minimal satu tablet.
            if ($details->count() <= 1) {
                throw ValidationException::withMessages([
                    'detail' => 'At least one dosimeter row must remain.',
                ]);
            }

            // Snapshot sebelum perubahan untuk audit.
            $before = $record->load('details')->toArray();

            $deletedDetail = $target->toArray();

            // Hapus baris yang dipilih.
            // File evidence sengaja tidak dihapus dari storage.
            $target->delete();

            $remaining = $details
                ->reject(fn($row) => $row->id === $target->id)
                ->values();

            /*
         * Reindex dalam dua tahap.
         *
         * Tahap 1: pindahkan nomor ke rentang sementara
         * agar tidak berbenturan dengan unique constraint
         * (dosimeter_record_id, tablet_number).
         *
         * Tahap 2: set nomor menjadi 1..N.
         *
         * Nilai sementara dibuat di atas nomor tertinggi
         * yang ada pada set tersebut.
         */
            $temporaryBase = max(
                (int) $details->max('tablet_number'),
                $details->count()
            ) + $remaining->count() + 1;

            foreach ($remaining as $index => $row) {
                $row->update([
                    'tablet_number' => $temporaryBase + $index,
                ]);
            }

            foreach ($remaining as $index => $row) {
                $row->update([
                    'tablet_number' => $index + 1,
                ]);
            }

            // Sinkronkan jumlah tablet dengan baris aktual.
            $record->update([
                'tablet_quantity' => $remaining->count(),
            ]);

            $after = $record->fresh()
                ->load('details')
                ->toArray();

            // Audit dilakukan dalam transaksi yang sama.
            \App\Services\WarehouseInventory::audit(
                'dosimetry.detail_deleted',
                'booking',
                $booking->id,
                [
                    'record_id' => $record->id,
                    'deleted_detail' => $deletedDetail,
                    'before' => $before,
                    'after' => $after,
                ]
            );

            return [
                'record_id' => $record->id,
                'tablet_quantity' => $remaining->count(),
                'booking_id' => $booking->id,
            ];
        }, 3);

        return response()->json([
            'status' => 'success',
            'message' => 'Dosimeter row deleted successfully.',
            'tablet_quantity' => $result['tablet_quantity'],
            'redirect' => route('admin.dosimeter.show', [
                'booking_id' => $result['booking_id'],
                'record' => $result['record_id'],
            ]),
        ]);
    }
}
