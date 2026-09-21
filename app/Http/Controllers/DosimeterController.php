<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\DosimeterRecord;
use App\Models\DosimeterDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
    public function show($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $record = DosimeterRecord::with('details')->where('booking_id', $bookingId)->first();

        return view('admin.dosimeter.show', compact('booking', 'record'));
    }

    /**
     * STEP 1 API: Membuat/Generate Kuantitas Baris Tablet Dosimeter
     */
    public function storeQuantity(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'tablet_quantity' => 'required|integer|min:1|max:50',
        ]);

        try {
            $record = DB::transaction(function () use ($validated) {
                $record = DosimeterRecord::updateOrCreate(
                    ['booking_id' => $validated['booking_id']],
                    ['tablet_quantity' => $validated['tablet_quantity']]
                );

                // Clean up images owned by details before regenerating rows.
                $record->load('details');
                foreach ($record->details as $detail) {
                    if ($detail->image && Storage::disk('public')->exists($detail->image)) {
                        Storage::disk('public')->delete($detail->image);
                    }
                }
                $record->details()->delete();

                for ($i = 1; $i <= $validated['tablet_quantity']; $i++) {
                    DosimeterDetail::create([
                        'dosimeter_record_id' => $record->id,
                        'tablet_number' => $i,
                    ]);
                }

                return $record->load('details');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Kolom input absorbance berhasil dibuat.',
                'data' => $record,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
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
            'global_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $record = DosimeterRecord::with('details')->findOrFail($recordId);
        $expectedNumbers = $record->details->pluck('tablet_number')->map(fn ($n) => (string) $n)->sort()->values();
        $submittedNumbers = collect(array_keys($validated['absorbance']))->map(fn ($n) => (string) $n)->sort()->values();

        if ($expectedNumbers->all() !== $submittedNumbers->all()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Daftar tablet tidak sesuai dengan record dosimeter. Reload halaman dan coba lagi.',
            ], 422);
        }

        if (count($validated['dosimeter_number']) !== count($validated['absorbance'])) {
            return response()->json(['status' => 'error', 'message' => 'Nomor dosimeter dan absorbance tidak lengkap.'], 422);
        }

        try {
            DB::transaction(function () use ($request, $validated, $record) {
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
                        'dosimeter_number' => $validated['dosimeter_number'][$tabletNumber] ?? null,
                        'absorbance' => $x,
                        'dose_kgy' => (13.099 * pow($x, 3)) + (8.7891 * pow($x, 2)) + (57.786 * $x) - 2.423,
                    ];

                    if ($uploadedImagePath !== null && (int) $tabletNumber === 1) {
                        if ($detail->image && Storage::disk('public')->exists($detail->image)) {
                            Storage::disk('public')->delete($detail->image);
                        }
                        $updateData['image'] = $uploadedImagePath;
                    }

                    $detail->update($updateData);
                }
            });

            session()->flash('success', 'Dosimeter data and image successfully saved.');
            return response()->json([
                'status' => 'success',
                'redirect' => route('admin.dosimeter.show', $record->booking_id),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
