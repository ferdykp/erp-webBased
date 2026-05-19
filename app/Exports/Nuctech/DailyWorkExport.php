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
use Carbon\Carbon;

class DailyWorkExport implements FromCollection, WithStyles, WithEvents, WithMapping
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        // Ambil satu model instance dengan eager loading
        $this->bookingInstance = Booking::with(['customer', 'products'])->find($this->id);
    }

    public function collection()
    {
        // Tetap kirim sebagai collection untuk memenuhi interface
        return collect([$this->bookingInstance]);
    }

    public function map($row): array
    {
        // Return array kosong agar tidak ada data mentah di kolom kanan (G, H, I...)
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // PROTEKSI: Pastikan kita mendapatkan single model object
        $booking = ($this->bookingInstance instanceof \Illuminate\Support\Collection)
            ? $this->bookingInstance->first()
            : $this->bookingInstance;

        if (!$booking) {
            $sheet->setCellValue('A1', 'Data Tidak Ditemukan');
            return [];
        }

        // Akses produk pertama secara aman
        $product = $booking->products->first();

        // --- KONSTRUKSI FORM (Berdasarkan image_9d707f.png) ---

        // 1. Judul Utama
        $sheet->setCellValue('A2', 'Daily Work Record');
        $sheet->mergeCells('A2:H2');

        // 2. Serial & Date
        $sheet->setCellValue('A3', "serial number: " . ($booking->booking_code ?? '-'));
        $sheet->mergeCells('A3:C3');
        $sheet->setCellValue('D3', "date: " . ($booking->created_at ? $booking->created_at->format('d F Y') : '-'));
        $sheet->mergeCells('D3:H3');

        // 3. Petugas & Jam Kerja
        $sheet->setCellValue('A4', "Duty Officer: " . ($booking->pic_warehouse ?? '-'));
        $sheet->mergeCells('A4:E4');
        $sheet->setCellValue('F4', "((Fill By Yourself))");
        $sheet->mergeCells('F4:H4');

        // 4. Operator
        $sheet->setCellValue('A5', "Operator: ((Fill By Yourself))");
        $sheet->mergeCells('A5:H5');

        // 5. Supervisor & Staff (Baris 6 & 7)
        $sheet->setCellValue('A6', "Processing line supervisor: ((Fill By Yourself))");
        $sheet->mergeCells('A6:D6');
        $sheet->setCellValue('E6', "staff: ((Fill By Yourself))");
        $sheet->mergeCells('E6:H6');

        $sheet->setCellValue('A7', "Loading and unloading supervisor: Rusdi");
        $sheet->mergeCells('A7:D7');
        $sheet->setCellValue('E7', "staff: ((Fill By Yourself))");
        $sheet->mergeCells('E7:H7');

        // 6. Status Peralatan
        $sheet->setCellValue('A8', "Equipment operating status (accelerator, beam drop line)");
        $sheet->mergeCells('A8:B8');
        $sheet->setCellValue('C8', "Good");
        $sheet->mergeCells('C8:H8');

        // 7. JOB DUTIES (Area Teks Besar)
        $sheet->setCellValue('A9', "Job Duties");
        $sheet->mergeCells('A9:B9');

        $arrivedDate = $booking->arrival_time ? Carbon::parse($booking->arrival_time)->format('d F Y') : '-';
        $weight = $product ? (($product->total_gross_weight ?? 0) / 1000) : 0;
        $qty = $product->quantity ?? 0;
        $unit = $product->unit ?? 'box';

        $jobContent = "Container Arrived        : " . $arrivedDate . "\n" .
            "Weight                          : " . $weight . " Ton\n" .
            "Total Quantity              : " . $qty . " " . $unit . "\n\n" .
            "Processed Products                  : 720 " . $unit . "\n" .
            "Remaining Unprocessed Products      : 480 " . $unit . "\n" .
            "Processing Time                     : 08:00 – 14:30 WIB\n" .
            "Total Time                          : 390 min";

        $sheet->setCellValue('C9', $jobContent);
        $sheet->mergeCells('C9:H9');
        $sheet->getStyle('C9')->getAlignment()->setWrapText(true);

        // 8. Other & Handover
        $sheet->setCellValue('A10', "Other matters");
        $sheet->mergeCells('A10:B10');
        $sheet->mergeCells('C10:H10');

        $sheet->setCellValue('A11', "Handover content");
        $sheet->mergeCells('A11:B11');
        $sheet->mergeCells('C11:H11');

        // 9. Footer & Signature
        $sheet->setCellValue('A12', "Successor's signature:");
        $sheet->mergeCells('A12:H12');
        $sheet->getStyle('A12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->setCellValue('A13', "Effective Date: 2026/01/01 Version/Status: A/00");

        // --- STYLING ---
        $sheet->getStyle('A2:H2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:H11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:H11')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:B11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:B8')->getAlignment()->setWrapText(true);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();
                $delegate->getColumnDimension('A')->setWidth(15);
                $delegate->getColumnDimension('B')->setWidth(15);
                $delegate->getColumnDimension('C')->setWidth(20);
                $delegate->getColumnDimension('D')->setWidth(15);

                // Set Tinggi Baris Khusus
                $delegate->getRowDimension(8)->setRowHeight(50);
                $delegate->getRowDimension(9)->setRowHeight(200);
                $delegate->getRowDimension(11)->setRowHeight(80);
            },
        ];
    }
}
