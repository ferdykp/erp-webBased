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

    // --- STEP 2 API: Simpan Nilai Absorbance, Hitung Dose & Upload Gambar ---
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
            // 1. Proses upload file gambar global jika ada
            $uploadedImagePath = null;
            if ($request->hasFile('global_image')) {
                if ($record->image && Storage::disk('public')->exists($record->image)) {
                    Storage::disk('public')->delete($record->image);
                }
                $uploadedImagePath = $request->file('global_image')->store('dosimeter_images', 'public');
            }

            // 2. Loop simpan nilai absorbance & hitung dose per tablet ke detail table
            foreach ($request->absorbance as $tabletNumber => $value) {
                $x = (float)$value;

                // Rumus Kalibrasi Cubic
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

            // Set notifikasi sukses bawaan Laravel ke session flash sebelum reload
            session()->flash('success', 'Dosimeter data and image successfully saved.');

            return response()->json([
                'status' => 'success',
                'redirect' => route('admin.dosimeter.show', $bookingId ?? $record->booking_id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
