<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderProcessReportController extends Controller
{
    public function download(Booking $booking)
    {
        abort_unless($booking->booking_type === 'regular' && $booking->status === 'completed', 404);

        $booking->load(['customer.contacts', 'products', 'batches.productionLine', 'batches.qa', 'placementDetails']);
        $records = \App\Models\DosimeterRecord::with('details')
            ->where('booking_id', $booking->id)->get();
        $measurements = $records->flatMap(fn($record) => $record->details);
        $doses = $measurements->pluck('dose_kgy')->filter(fn($dose) => is_numeric($dose))->map(fn($dose) => (float) $dose);
        $doseStats = [
            'min' => $doses->isNotEmpty() ? $doses->min() : null,
            'max' => $doses->isNotEmpty() ? $doses->max() : null,
            'average' => $doses->isNotEmpty() ? $doses->avg() : null,
        ];
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $booking->booking_code) . '-Irradiation-Process-Report.pdf';
        return Pdf::loadView('admin.bookings.process-report-pdf', compact('booking', 'measurements', 'doseStats'))
            ->setPaper('a4')
            ->download($filename);
    }

    public function printPreview(Booking $booking)
    {
        abort_unless(
            $booking->booking_type === 'regular' &&
                $booking->status === 'completed',
            404
        );

        $booking->load([
            'customer.contacts',
            'products',
            'batches.productionLine',
            'batches.qa',
            'placementDetails',
        ]);

        $records = \App\Models\DosimeterRecord::with('details')
            ->where('booking_id', $booking->id)
            ->get();

        $measurements = $records->flatMap(
            fn($record) => $record->details
        );

        $doses = $measurements
            ->pluck('dose_kgy')
            ->filter(fn($dose) => is_numeric($dose))
            ->map(fn($dose) => (float) $dose);

        $doseStats = [
            'min' => $doses->isNotEmpty() ? $doses->min() : null,
            'max' => $doses->isNotEmpty() ? $doses->max() : null,
            'average' => $doses->isNotEmpty() ? $doses->avg() : null,
        ];

        return view(
            'admin.bookings.process-report-pdf',
            compact('booking', 'measurements', 'doseStats')
        );
    }

    public function preview(Booking $booking)
    {
        abort_unless(
            $booking->booking_type === 'regular' &&
                $booking->status === 'completed',
            404
        );

        $booking->load([
            'customer.contacts',
            'products',
            'batches.productionLine',
            'batches.qa',
            'placementDetails',
        ]);

        $records = \App\Models\DosimeterRecord::with('details')
            ->where('booking_id', $booking->id)
            ->get();

        $measurements = $records->flatMap(
            fn($record) => $record->details
        );

        $doses = $measurements
            ->pluck('dose_kgy')
            ->filter(fn($dose) => is_numeric($dose))
            ->map(fn($dose) => (float) $dose);

        $doseStats = [
            'min' => $doses->isNotEmpty() ? $doses->min() : null,
            'max' => $doses->isNotEmpty() ? $doses->max() : null,
            'average' => $doses->isNotEmpty() ? $doses->avg() : null,
        ];

        $filename = preg_replace(
            '/[^A-Za-z0-9_-]+/',
            '-',
            $booking->booking_code
        ) . '-Irradiation-Process-Report.pdf';

        return Pdf::loadView(
            'admin.bookings.process-report-pdf',
            compact('booking', 'measurements', 'doseStats')
        )
            ->setPaper('a4')
            ->stream($filename);
    }
}
