<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\DosimeterRecord;
use App\Models\DosimeterDetail;
use Illuminate\Support\Facades\DB;

class DosimeterController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['customer', 'products'])->latest()->paginate(10);
        return view('admin.dosimeter.index', compact('bookings'));
    }

    public function show($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $record = DosimeterRecord::with('details')->where('booking_id', $bookingId)->first();

        return view('admin.dosimeter.show', compact('booking', 'record'));
    }

    // --- STEP 1 API: Submit Jumlah Kuantitas Tablet ---
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

    // --- STEP 2 API: Simpan Nilai Absorbance & Hitung Dose untuk Database ---
    public function storeAbsorbance(Request $request, $recordId)
    {
        $request->validate([
            'dosimeter_number' => 'required|array',
            'dosimeter_number.*' => 'required|string', // Validasi input nomor dosimeter
            'absorbance' => 'required|array',
            'absorbance.*' => 'required|numeric|min:0',
        ]);

        $record = DosimeterRecord::findOrFail($recordId);

        DB::beginTransaction();
        try {
            foreach ($request->absorbance as $tabletNumber => $value) {
                $x = (float)$value;

                // Rumus: y = 13.099x³ + 8.7891x² + 57.786x - 2.423
                $calibratedDose = (13.099 * pow($x, 3)) + (8.7891 * pow($x, 2)) + (57.786 * $x) - 2.423;

                // Ambil nilai dosimeter_number berdasarkan index tablet_number-nya
                $dosimeterNum = $request->dosimeter_number[$tabletNumber] ?? null;

                DosimeterDetail::where('dosimeter_record_id', $record->id)
                    ->where('tablet_number', $tabletNumber)
                    ->update([
                        'dosimeter_number' => $dosimeterNum, // SIMPAN KE DB
                        'absorbance' => $value,
                        'dose_kgy' => $calibratedDose
                    ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Data Dosimeter dan Absorbance berhasil disimpan ke database.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
