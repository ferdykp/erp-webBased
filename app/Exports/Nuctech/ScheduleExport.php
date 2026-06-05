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

class ScheduleExport implements FromCollection, WithStyles, WithEvents, WithMapping
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        // Ambil satu model instance dengan eager loading sesuai dengan preferensi Anda
        $this->bookingInstance = Booking::with(['customer', 'products'])->find($this->id);
    }

    public function collection()
    {
        // Tetap kirim sebagai collection untuk memenuhi interface Laravel Excel
        return collect([$this->bookingInstance]);
    }

    public function map($row): array
    {
        // Return array kosong agar tidak ada data mentah tercetak di kolom default kanan
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Proteksi objek single model
        $booking = ($this->bookingInstance instanceof \Illuminate\Support\Collection)
            ? $this->bookingInstance->first()
            : $this->bookingInstance;

        if (!$booking) {
            $sheet->setCellValue('A1', 'Data Tidak Ditemukan');
            return [];
        }

        $contact = $booking->customer->contacts->first();
        $contactName = $contact ? $contact->name : '-';


        // --- KONSTRUKSI FORM (Berdasarkan Gambar: Daily processing schedule) ---

        // Kolom Mapping pada Gambar:
        // A = Date | B = Task Number | C = Customer Name | D = Goods Name | E = quantity | F = weight | G = Dosage (kGy) | H = Delivery period | I = Remark

        // 1. Garis Tipis Atas (Garis dekoratif sebelum judul pada dokumen asli)
        // Diimplementasikan sebagai border atas baris ke-2 atau dikosongkan.

        // 2. Judul Utama (Baris 3)
        $sheet->setCellValue('A3', 'Daily processing schedule');
        $sheet->mergeCells('A3:I3');

        // 3. Label Tanggal Cetak / Dokumen (Baris 4)
        $dateFormatted = $booking->created_at ? $booking->created_at->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $sheet->setCellValue('H4', 'date: ' . $dateFormatted);
        $sheet->mergeCells('H4:I4');

        // 4. Header Tabel Konten (Baris 5)
        $headers = [
            'A5' => 'Date',
            'B5' => "Task Number",
            'C5' => 'Customer Name',
            'D5' => 'Goods Name',
            'E5' => "quantity",
            'F5' => 'weight',
            'G5' => "Dosage (\nkGy )",
            'H5' => 'Delivery period',
            'I5' => 'Remark'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // 5. Isi Baris Data Berulang (Mulai dari Baris 6 hingga Baris 15 sesuai jumlah baris kosong di gambar)
        $startRow = 6;
        $maxRows = 10; // Membuat 10 baris tabel seperti di cetakan gambar

        // Ambil data produk-produk yang terikat di Booking ini
        $products = $booking->products;

        for ($i = 0; $i < $maxRows; $i++) {
            $currentRow = $startRow + $i;

            if ($i === 0) {
                // Baris Pertama diisi dengan data real dari Database
                $product = $products->first();

                $sheet->setCellValue('A' . $currentRow, $booking->arrival_time ? Carbon::parse($booking->arrival_time)->format('12/03/2026') : '-');
                $sheet->setCellValue('B' . $currentRow, $booking->booking_code ?? '-');
                $sheet->setCellValue('C' . $currentRow, $contactName ?? '-');
                $sheet->setCellValue('D' . $currentRow, $product->product_name ?? '-');
                $sheet->setCellValue('E' . $currentRow, $product->quantity ?? 0);
                $sheet->setCellValue('F' . $currentRow, $product->total_gross_weight ? (($product->total_gross_weight / 1) . ' Kg') : '0');
                $sheet->setCellValue('G' . $currentRow, $product->dmin . '-' . $product->dmax ?? ' - '); // Ambil kolom dosis jika ada di db anda
                $sheet->setCellValue('H' . $currentRow, '-'); // Delivery Period
                $sheet->setCellValue('I' . $currentRow, $booking->notes ?? '-'); // Remark
            } else {
                // Baris sisa dikosongkan (Zebra template grid sesuai gambar dokumen cetak)
                $sheet->setCellValue('A' . $currentRow, '');
            }
        }

        // 6. Footer Dokumen (2 Baris di bawah tabel)
        $footerRow = $startRow + $maxRows + 1; // Baris 17

        $sheet->setCellValue('A' . $footerRow, 'Effective Date : 2026/01/01');
        $sheet->mergeCells('A' . $footerRow . ':D' . $footerRow);

        $sheet->setCellValue('G' . $footerRow, 'Version/Status: A /00');
        $sheet->mergeCells('G' . $footerRow . ':I' . $footerRow);


        // --- PROSES STYLING & FORMATTING ---

        // Font Default seluruh sheet
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');

        // Style Judul
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style Tanggal Dokumen
        $sheet->getStyle('H4')->getFont()->setSize(10);
        $sheet->getStyle('H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Style Header Tabel (Baris 5)
        $tableHeaderStyle = $sheet->getStyle('A5:I5');
        $tableHeaderStyle->getFont()->setBold(true)->setSize(11);
        $tableHeaderStyle->getAlignment()->setWrapText(true);
        $tableHeaderStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $tableHeaderStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Style Grid Data Tabel (Baris 6 s/d 15)
        $endTableId = $startRow + $maxRows - 1;
        $tableGridStyle = $sheet->getStyle("A5:I{$endTableId}");

        // Aplikasikan Borders ke seluruh Tabel
        $tableGridStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $tableGridStyle->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Alignment per kolom data agar rapi
        $sheet->getStyle("A6:B{$endTableId}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("C6:D{$endTableId}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("E6:G{$endTableId}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("H6:I{$endTableId}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Style Footer (Baris 17)
        $sheet->getStyle('A' . $footerRow)->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('G' . $footerRow)->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('G' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();

                // Menyesuaikan Lebar Kolom (Width) agar proporsional mirip gambar Word/Excel target
                $delegate->getColumnDimension('A')->setWidth(14);  // Date
                $delegate->getColumnDimension('B')->setWidth(14);  // Task Number
                $delegate->getColumnDimension('C')->setWidth(24);  // Customer Name
                $delegate->getColumnDimension('D')->setWidth(22);  // Goods Name
                $delegate->getColumnDimension('E')->setWidth(12);  // Quantity
                $delegate->getColumnDimension('F')->setWidth(12);  // Weight
                $delegate->getColumnDimension('G')->setWidth(13);  // Dosage (kGy)
                $delegate->getColumnDimension('H')->setWidth(16);  // Delivery period
                $delegate->getColumnDimension('I')->setWidth(16);  // Remark

                // Set Tinggi Baris (Row Height)
                $delegate->getRowDimension(3)->setRowHeight(30);  // Baris Judul
                $delegate->getRowDimension(5)->setRowHeight(35);  // Baris Header Tabel (wrap text kGy aman)

                // Set Tinggi Baris data tabel kosong biar tinggi-tinggi/longgar seperti contoh gambar Anda (0.8 cm ~ 24-26 pt)
                for ($row = 6; $row <= 15; $row++) {
                    $delegate->getRowDimension($row)->setRowHeight(26);
                }
            },
        ];
    }
}
