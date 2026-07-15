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
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'tablet_quantity' => 'required|integer|min:1|max:50',
        ]);

        DB::beginTransaction();
        try {
            $record = DosimeterRecord::updateOrCreate(
                ['booking_id' => $request->booking_id],
                ['tablet_quantity' => $request->tablet_quantity]
            );

            $record->details()->delete();

            for ($i = 1; $i <= $request->tablet_quantity; $i++) {
                DosimeterDetail::create([
                    'dosimeter_record_id' => $record->id,
                    'tablet_number' => $i,
                    'absorbance' => null,
                    'dose_kgy' => null
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Kolom input absorbance berhasil dibuat.',
                'data' => $record->load('details')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * STEP 2 API: Menyimpan Nilai Absorbance, Hitung Dose Otomatis, & Upload 1 Gambar Global
     */
    public function storeAbsorbance(Request $request, $recordId)
    {
        $request->validate([
            'dosimeter_number' => 'required|array',
            'dosimeter_number.*' => 'required|string',
            'absorbance' => 'required|array',
            'absorbance.*' => 'required|numeric|min:0|max:5',
            'global_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $record = DosimeterRecord::findOrFail($recordId);

        DB::beginTransaction();
        try {
            $uploadedImagePath = null;
            if ($request->hasFile('global_image')) {
                if ($record->image && Storage::disk('public')->exists($record->image)) {
                    Storage::disk('public')->delete($record->image);
                }
                $uploadedImagePath = $request->file('global_image')->store('dosimeter_images', 'public');
            }

            foreach ($request->absorbance as $tabletNumber => $value) {
                $x = (float)$value;

                $calibratedDose = (13.099 * pow($x, 3)) + (8.7891 * pow($x, 2)) + (57.786 * $x) - 2.423;
                $dosimeterNum = $request->dosimeter_number[$tabletNumber] ?? null;

                $updateData = [
                    'dosimeter_number' => $dosimeterNum,
                    'absorbance' => $value,
                    'dose_kgy' => $calibratedDose
                ];

                if ($uploadedImagePath !== null && (int)$tabletNumber === 1) {
                    $oldDetail = DosimeterDetail::where('dosimeter_record_id', $record->id)
                        ->where('tablet_number', 1)
                        ->first();

                    if ($oldDetail && $oldDetail->image && Storage::disk('public')->exists($oldDetail->image)) {
                        Storage::disk('public')->delete($oldDetail->image);
                    }

                    $updateData['image'] = $uploadedImagePath;
                }

                DosimeterDetail::where('dosimeter_record_id', $record->id)
                    ->where('tablet_number', $tabletNumber)
                    ->update($updateData);
            }

            DB::commit();

            session()->flash('success', 'Dosimeter data and image successfully saved.');

            return response()->json([
                'status' => 'success',
                'redirect' => route('admin.dosimeter.show', $record->booking_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
