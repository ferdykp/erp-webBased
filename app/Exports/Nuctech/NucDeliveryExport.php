<?php

namespace App\Exports\Nuctech;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class NucDeliveryExport implements FromCollection, WithStyles, WithEvents, WithMapping
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        // Menggunakan find() untuk memastikan kita mendapat model instance tunggal
        $this->bookingInstance = Booking::with(['customer', 'products'])->find($this->id);
    }

    public function collection()
    {
        return collect([$this->bookingInstance]);
    }

    public function map($row): array
    {
        // Mencegah data mentah muncul otomatis di kolom kanan
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Safe access model
        $booking = ($this->bookingInstance instanceof \Illuminate\Support\Collection)
            ? $this->bookingInstance->first()
            : $this->bookingInstance;

        if (!$booking) return [];

        $product = $booking->products->first();

        // --- HEADER ATAS ---
        $sheet->setCellValue('A1', 'Irradiated Product Processing and Delivery Order');
        $sheet->mergeCells('A1:I1');

        $sheet->setCellValue('A2', "Processing task order number: Number: " . ($booking->booking_code ?? ''));
        $sheet->mergeCells('A2:I2');

        // Baris Nama Customer & Tanggal
        $sheet->setCellValue('A3', 'Customer Name');
        $sheet->mergeCells('B3:E3');
        $sheet->setCellValue('B3', $booking->customer->company_name ?? '-');

        $sheet->setCellValue('F3', 'Processing date');
        $sheet->mergeCells('G3:I3');
        $sheet->setCellValue('G3', $booking->created_at ? $booking->created_at->format('d/m/Y') : '-');

        // --- TABLE HEADERS (Baris 4) ---
        $sheet->setCellValue('A4', "Serial\nNumber");
        $sheet->setCellValue('B4', 'Product Name');
        $sheet->setCellValue('C4', 'Packaging specifications');
        $sheet->setCellValue('D4', 'quantity');
        $sheet->setCellValue('E4', 'Weight ( kg )');
        $sheet->setCellValue('F4', 'Dosage ( kGy )');
        $sheet->setCellValue('G4', "Processing\nTime (minutes)");
        $sheet->setCellValue('H4', 'Remark');
        $sheet->mergeCells('H4:I4');

        // --- DATA ROWS (Baris 5, 6, 7) ---
        // Row 1
        $sheet->setCellValue('A5', '1');
        $sheet->setCellValue('B5', strtoupper($product->product_name ?? '-'));
        $sheet->setCellValue('C5', ($product->unit ?? 'none')); // Statis sesuai gambar
        $sheet->setCellValue('D5', ($product->quantity ?? 0));
        $sheet->setCellValue('E5', (($product->total_gross_weight ?? 0) / 1000) . ' ton');
        $sheet->setCellValue('F5', ($product->dmin ?? 0) . 'kGy');
        $sheet->setCellValue('G5', '((Fill By Yourself))');
        $sheet->mergeCells('H5:I5');

        // Row 2 & 3 (Contoh kosong sesuai gambar)
        $sheet->setCellValue('A6', '2');
        $sheet->mergeCells('H6:I6');
        $sheet->setCellValue('A7', '3');
        $sheet->mergeCells('H7:I7');

        // --- FOOTER SECTION ---
        // Other matters
        $sheet->setCellValue('A8', 'Other matters:');
        $sheet->mergeCells('A8:I8');

        // Signatures Row
        $sheet->setCellValue('A9', 'Shift Leader');
        $sheet->mergeCells('B9:C9');

        $sheet->setCellValue('D9', 'Production Manager');
        $sheet->mergeCells('E9:F9');

        $sheet->setCellValue('G9', "Marketing\nDepartment");
        $sheet->mergeCells('H9:I9');

        // --- STYLING ---
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Border Tabel (A3 sampai I9)
        $sheet->getStyle('A3:I9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Alignment
        $sheet->getStyle('A3:I9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3:I9')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A4:I4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G4')->getAlignment()->setWrapText(true);
        $sheet->getStyle('G9')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();

                // Column Widths
                $delegate->getColumnDimension('A')->setWidth(10);
                $delegate->getColumnDimension('B')->setWidth(20);
                $delegate->getColumnDimension('C')->setWidth(30);
                $delegate->getColumnDimension('D')->setWidth(15);
                $delegate->getColumnDimension('E')->setWidth(15);
                $delegate->getColumnDimension('F')->setWidth(15);
                $delegate->getColumnDimension('G')->setWidth(15);
                $delegate->getColumnDimension('H')->setWidth(10);
                $delegate->getColumnDimension('I')->setWidth(10);

                // Row Heights
                $delegate->getRowDimension(4)->setRowHeight(40); // Header
                $delegate->getRowDimension(5)->setRowHeight(40); // Data 1
                $delegate->getRowDimension(8)->setRowHeight(40); // Other matters
                $delegate->getRowDimension(9)->setRowHeight(50); // Signatures
            },
        ];
    }
}
