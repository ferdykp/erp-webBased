<?php

namespace App\Exports;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BookingDetailExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        // Load semua relasi agar data lengkap seperti di modal
        return Booking::with(['customer.contacts', 'products', 'batches'])
            ->where('id', $this->id)
            ->get();
    }

    public function headings(): array
    {
        return [
            // Header Utama
            "Booking Code",
            "Status",
            // Client Info
            "Client Name",
            "Client Company",
            "Client Email",
            // Scheduling
            "Date Booked",
            "Date Arrived",
            // Product Information
            "Product Name",
            "Product Type",
            "Dimension Pack (cm)",
            "Dose Min (kGy)",
            "Dose Max (kGy)",
            // Quantity & Volume
            "Total Quantity",
            "Unit",
            "Vol Per Pcs (cm³)",
            "Vol Total (cm³)",
            // Weights & Density
            "Net Weight/Pcs (kg)",
            "Total Net Weight (kg)",
            "Gross Weight/Pcs (kg)",
            "Total Gross Weight (kg)",
            "Density Nett",
            "Density Gross",
            // Production Summary
            // "Total Batches",
        ];
    }

    public function map($booking): array
    {
        $product = $booking->products->first();

        return [
            $booking->booking_code,
            strtoupper($booking->status),
            // Client Info
            $booking->customer->contacts->first()->name ?? 'Guest',
            $booking->customer->company_name,
            $booking->customer->email ?? '-',
            // Scheduling
            $booking->created_at->format('d/m/Y H:i'),
            $booking->arrival_time ? \Carbon\Carbon::parse($booking->arrival_time)->format('d/m/Y H:i') : 'Waiting',
            // Product Information
            $product->product_name ?? '-',
            $product->product_type ?? '-',
            $product->dimension_pack ?? '-',
            $product->dmin ?? 0,
            $product->dmax ?? 0,
            // Quantity & Volume
            $product->quantity ?? 0,
            $product->unit ?? '',
            $product->vol_per_pcs ?? 0,
            $product->vol_total ?? 0,
            // Weights & Density
            $product->net_weight_pcs ?? 0,
            $product->total_net_weight ?? 0,
            $product->gross_weight_per_pcs ?? 0,
            $product->total_gross_weight ?? 0,
            $product->density_nett ?? 0,
            $product->density_gross ?? 0,
            // Production Summary
            // $booking->batches->count() . " Batches",
        ];
    }

    // Memberikan style agar header terlihat lebih profesional
    public function styles(Worksheet $sheet)
    {
        return [
            // Style Baris 1 (Header)
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '10B981'] // Emerald 600
                ],
            ],
        ];
    }
}
