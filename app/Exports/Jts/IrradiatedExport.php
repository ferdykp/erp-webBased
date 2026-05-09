<?php

namespace App\Exports\Jts;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping; // Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
// use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class UnirradiatedExport implements WithMapping, FromCollection, WithStyles, WithEvents
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        // Pastikan kita mendapatkan model tunggal
        $this->bookingInstance = Booking::with(['customer.contacts', 'products'])->find($this->id);
    }

    public function collection()
    {
        // Membungkus model ke dalam collection agar sesuai interface
        return collect([$this->bookingInstance]);
    }
    public function map($row): array
    {
        return [];
    }
    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // LOGIKA PERBAIKAN: Pastikan kita mengambil item pertama jika $this->bookingInstance 
        // entah bagaimana berubah menjadi collection di tengah jalan
        $booking = ($this->bookingInstance instanceof \Illuminate\Support\Collection)
            ? $this->bookingInstance->first()
            : $this->bookingInstance;

        // 1. Validasi keberadaan data
        if (!$booking) {
            $sheet->setCellValue('A1', 'Booking Data Not Found');
            return [];
        }

        // 2. Akses produk secara aman
        $product = $booking->products->first();

        // --- KONSTRUKSI FORM (Sesuai Gambar Anda) ---

        // Judul Utama
        $sheet->setCellValue('A2', 'Material identification card');
        $sheet->mergeCells('A2:F2');

        // Sidebar Label "Unirradiated"
        $sheet->setCellValue('A3', 'Irradiated');
        $sheet->mergeCells('A3:B11');

        // Label Baris
        $sheet->setCellValue('C3', 'Company Name');
        $sheet->setCellValue('C4', 'Product Name');
        $sheet->setCellValue('C5', 'Task Number');
        $sheet->setCellValue('C6', 'Completion Date');
        $sheet->setCellValue('C7', 'Processing quantity');
        $sheet->setCellValue('C8', 'Shipping date');
        $sheet->setCellValue('C9', 'Quantity of shipments');
        $sheet->setCellValue('C10', 'Balance');
        $sheet->setCellValue('C11', 'Remark');

        // Isi Data dari Database
        $sheet->setCellValue('E3', $booking->customer->company_name ?? '-');
        $sheet->setCellValue('E4', $product->product_name ?? '-');
        $sheet->setCellValue('E5', $booking->booking_code ?? '-');
        $sheet->setCellValue('E6', $booking->created_at ? $booking->created_at->format('d-M-y') : '-');
        $sheet->setCellValue('E7', ($product->quantity ?? 0) . ' ' . ($product->unit ?? 'box'));

        // Shipping Date (Arrival Time)
        $shippingDate = $booking->arrival_time ? Carbon::parse($booking->arrival_time)->format('d-M-y') : '-';
        $sheet->setCellValue('E8', $shippingDate);

        $sheet->setCellValue('E9', ($product->quantity ?? 0) . ' ' . ($product->unit ?? 'box'));
        $sheet->setCellValue('E10', '0'); // Sesuai mockup gambar
        $sheet->setCellValue('E11', '?');

        // Merge Value Cells (C-D dan E-F)
        for ($i = 3; $i <= 11; $i++) {
            $sheet->mergeCells("C$i:D$i");
            $sheet->mergeCells("E$i:F$i");
        }

        // Footer
        // $sheet->setCellValue('A12', 'Prepared by: ', $booking->pic_warehouse);
        $sheet->setCellValue('A12', 'Prepared by: ' . ($booking->pic_warehouse ?? '-'));
        $sheet->mergeCells('A12:B12');
        $sheet->setCellValue('C12', 'Date of Completion: ' . ($shippingDate ?? '-'));
        $sheet->mergeCells('C12:F12');
        $sheet->setCellValue('A13', 'Effective Date: ' . ($booking->created_at ?? '-'));

        // --- STYLING ---
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('C3:C11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E3:E11')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3:F11')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);

        // Fill Kuning (Balance, Remark, dan Prepared By)
        // $yellow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]];
        // $sheet->getStyle('E10:F11')->applyFromArray($yellow);
        // $sheet->getStyle('A12:B12')->applyFromArray($yellow);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();
                $delegate->getColumnDimension('A')->setWidth(15);
                $delegate->getColumnDimension('B')->setWidth(15);
                $delegate->getColumnDimension('C')->setWidth(25);
                $delegate->getColumnDimension('D')->setWidth(5);
                $delegate->getColumnDimension('E')->setWidth(35);
                $delegate->getColumnDimension('F')->setWidth(10);

                for ($i = 3; $i <= 11; $i++) {
                    $delegate->getRowDimension($i)->setRowHeight(25);
                }
            },
        ];
    }
}
