<?php

namespace App\Services;

use App\Models\{Booking, DosimeterRecord};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DosimetryRelease
{
    public function validate(Booking $booking): void
    {
        $records = DosimeterRecord::with('details')->where('booking_id', $booking->id)->get();
        if ($records->isEmpty()) $this->fail('Complete dosimetry before dispatch.');
        foreach ($records as $record) {
            if ($record->tablet_quantity < 1 || $record->details->count() !== (int) $record->tablet_quantity) {
                $this->fail('Complete every required dosimeter row before dispatch.');
            }
            foreach ($record->details as $detail) {
                if (blank($detail->dosimeter_number) || $detail->absorbance === null || $detail->dose_kgy === null
                    || !is_finite((float) $detail->dose_kgy) || $detail->dose_kgy < 0) {
                    $this->fail('Dosimeter identifiers, absorbance and valid measured doses are required.');
                }
            }
        }
    }

    /** Differences are informational and never require an approval or block physical dispatch. */
    public function warnings(Booking $booking): array
    {
        $booking->loadMissing(['products', 'batches.qa']);
        $products = $booking->products->keyBy('id');
        $batches = $booking->batches->keyBy('id');
        $warnings = [];
        $covered = [];
        foreach ($batches as $batch) {
            $product = $products->get($batch->booking_product_id) ?? ($products->count() === 1 ? $products->first() : null);
            if (!$product) $warnings[] = "Batch {$batch->batch_number} has no verified product assignment; its dose limits cannot be compared.";
            elseif ($batch->qa && $batch->qa->actual_dose !== null && $this->outside($batch->qa->actual_dose, $product)) {
                $warnings[] = "Batch {$batch->batch_number}: recorded dose {$batch->qa->actual_dose} kGy differs from {$product->product_name} limits ({$this->limits($product)}).";
            }
        }
        $records = DosimeterRecord::with('details')->where('booking_id', $booking->id)->get();
        foreach ($records->flatMap->details as $detail) {
            $batch = $detail->booking_batch_id ? $batches->get($detail->booking_batch_id) : null;
            $product = $batch ? $products->get($batch->booking_product_id) : null;
            if (!$product && $products->count() === 1 && (!$detail->booking_batch_id || $batch)) $product = $products->first();
            if (!$product) {
                $warnings[] = "Dosimeter {$detail->dosimeter_number}: batch/product assignment is missing or unverified; applicable dose limits cannot be compared.";
                continue;
            }
            $covered[$product->id] = true;
            if ($detail->dose_kgy !== null && $this->outside($detail->dose_kgy, $product)) {
                $warnings[] = "Dosimeter {$detail->dosimeter_number}: measured dose {$detail->dose_kgy} kGy differs from {$product->product_name} limits ({$this->limits($product)}).";
            }
        }
        foreach ($products as $product) {
            if (!isset($covered[$product->id])) $warnings[] = "No product-specific dosimetry comparison is available for {$product->product_name}.";
        }
        $review = DB::table('quality_reviews')->where('booking_id', $booking->id)->latest('id')->first();
        if ($review && in_array($review->decision, ['hold', 'needs_correction'], true)) {
            $warnings[] = 'Existing quality note: '.str_replace('_', ' ', $review->decision).($review->notes ? ' — '.$review->notes : '').'. This note does not require a dispatch approval.';
        }
        return array_values(array_unique($warnings));
    }

    private function outside(float $dose, $product): bool
    {
        return $product->dmin === null || $dose < $product->dmin || ($product->dmax !== null && $dose > $product->dmax);
    }

    private function limits($product): string
    {
        return ($product->dmin ?? 'not recorded').'–'.($product->dmax ?? 'not specified').' kGy';
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['dispatch' => $message]);
    }
}
