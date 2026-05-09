<?php

namespace App\Exports\Jts;

use App\Models\Booking;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class DeliveryOutboundExport implements FromCollection, WithStyles, WithEvents, WithMapping
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        // Pastikan mengambil satu model instance
        $this->bookingInstance = Booking::with(['customer.contacts', 'products'])->find($this->id);
    }

    public function collection()
    {
        // Bungkus ke collection untuk memenuhi interface
        return collect([$this->bookingInstance]);
    }

    public function map($row): array
    {
        // Return kosong untuk mencegah kebocoran data di kolom kanan
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Proteksi: Pastikan kita bekerja dengan Model, bukan Collection
        $booking = ($this->bookingInstance instanceof \Illuminate\Support\Collection)
            ? $this->bookingInstance->first()
            : $this->bookingInstance;

        // Ambil kontak pertama atau yang primary
        $contact = $booking->customer->contacts->first();
        $contactName = $contact ? $contact->name : '-';
        $companyName = $booking->customer->company_name ?? '-';


        if (!$booking) {
            $sheet->setCellValue('A1', 'Data Not Found');
            return [];
        }

        $product = $booking->products->first();

        // --- KONSTRUKSI HEADER (image_a3db2e.png) ---
        $sheet->setCellValue('G1', "List of Irradiated Goods Outbond No." . ($booking->booking_code ?? ''));
        $sheet->getStyle('G1')->getFont()->setBold(true);

        $sheet->setCellValue('B2', "Customer Name: " . $companyName . " (" . $contactName . ")");
        $sheet->setCellValue('G2', "Date: " . ($booking->created_at ? $booking->created_at->format('Y/m/d') : ''));

        // --- TABLE STRUCTURE ---
        $sheet->setCellValue('A3', 'No');
        $sheet->mergeCells('A3:A4');
        $sheet->setCellValue('B3', 'Goods Name');
        $sheet->mergeCells('B3:B4');
        $sheet->setCellValue('C3', 'Quantity');
        $sheet->mergeCells('C3:C4');
        $sheet->setCellValue('D3', "Gross Weight Per\nPieces (Kg)");
        $sheet->mergeCells('D3:D4');
        $sheet->setCellValue('E3', "Total Gross Weigth\n(Kg)");
        $sheet->mergeCells('E3:E4');
        $sheet->setCellValue('F3', 'unit');
        $sheet->mergeCells('F3:F4');
        $sheet->setCellValue('G3', 'Goods handover');
        $sheet->mergeCells('G3:I3');

        // --- DATA BINDING ---
        $sheet->setCellValue('A5', '1');
        $sheet->setCellValue('B5', $product->product_name ?? '-');
        $sheet->setCellValue('C5', $product->quantity ?? 0);
        $sheet->setCellValue('D5', $product->gross_weight_per_pcs ?? 0);
        $sheet->setCellValue('E5', $product->total_gross_weight ?? 0);
        $sheet->setCellValue('F5', $product->unit ?? 'box');

        // Consignee & Driver Info (Sisi Kanan)
        $sheet->setCellValue('G5', 'Consignee Information');
        $sheet->mergeCells('G5:G7');
        $sheet->setCellValue('H5', "Driver's name");
        $sheet->setCellValue('H6', 'Telephone');
        $sheet->setCellValue('H7', 'license plate number');

        // Area Kuning (Input Manual)
        $sheet->setCellValue('I5', '?');
        $sheet->setCellValue('I6', '?');
        $sheet->setCellValue('I7', '?');

        // Total Row
        $sheet->setCellValue('B8', 'total');
        $sheet->setCellValue('C8', $product->quantity ?? 0);
        $sheet->setCellValue('E8', $product->total_gross_weight ?? 0);
        $sheet->setCellValue('G8', "Shipper's signature");
        $sheet->mergeCells('G8:H8');
        $sheet->setCellValue('I8', '?');

        // Remark Row
        $sheet->setCellValue('B9', 'Remark');
        $sheet->setCellValue('C9', '?');
        $sheet->mergeCells('C9:I9');

        // --- STYLING ---
        $sheet->getStyle('A3:I9')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A3:I4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('D3:E3')->getAlignment()->setWrapText(true);

        // Warna Kuning sesuai image_a3db2e.png
        $yellow = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]];
        $sheet->getStyle('I5:I8')->applyFromArray($yellow);
        $sheet->getStyle('C9:I9')->applyFromArray($yellow);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(20);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(25);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(15);
            },
        ];
    }
}
