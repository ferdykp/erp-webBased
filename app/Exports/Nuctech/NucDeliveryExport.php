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

class NucDeliveryExport implements FromCollection, WithStyles, WithEvents, WithMapping
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        // Ambil satu model instance dengan eager loading agar sinkron dengan data pilihan user
        $this->bookingInstance = Booking::with(['customer.contacts', 'products', 'batches'])->find($this->id);
    }

    public function collection()
    {
        return collect([$this->bookingInstance]);
    }

    public function map($row): array
    {
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
        $products = $booking->products;
        $companyName = $booking->customer->company_name ?? '-';


        // --- LOGIKA KALKULASI WAKTU (PROCESSING TIME) ---
        // Kita ambil offline_at terkecil (paling awal) dan finished_at terbesar (paling akhir)
        $startTime = $booking->batches->min('offline_at');
        $endTime = $booking->batches->max('finished_at');

        $durationText = '-';
        if ($startTime && $endTime) {
            $start = Carbon::parse($startTime);
            $end = Carbon::parse($endTime);

            // Gunakan round() untuk membulatkan desimal ke angka bulat terdekat
            $diffInMinutes = round($start->diffInMinutes($end));

            // Output akan bersih: 15:33 - 15:36 (3 min)
            $durationText = $start->format('H:i') . ' - ' . $end->format('H:i') . ' (' . $diffInMinutes . ' min)';
        }

        // --- KONSTRUKSI FORM (Berdasarkan Gambar: Irradiated Product Processing and Delivery Order) ---

        // 1. Judul Utama (Baris 2)
        $sheet->setCellValue('A2', 'Irradiated Product Processing and Delivery Order');
        $sheet->mergeCells('A2:H2');

        // 2. Processing Task Order Number (Baris 3)
        $sheet->setCellValue('A3', 'Processing task order number: ' . ($booking->booking_code ?? '-'));
        $sheet->mergeCells('A3:H3');

        // 3. Header Info Customer & Processing Date (Baris 4 & 5)
        $sheet->setCellValue('A4', 'Customer Name');
        $sheet->mergeCells('A4:D4');
        $sheet->setCellValue('E4', 'Processing date');
        $sheet->mergeCells('E4:G4');

        $sheet->setCellValue('A5', ($contactName ?? '-') . ($companyName ? " ({$companyName})" : ""));
        $sheet->mergeCells('A5:D5');
        $sheet->setCellValue('E5', $booking->arrival_time ? Carbon::parse($booking->arrival_time)->format('d/m/Y') : '-');
        $sheet->mergeCells('E5:G5');

        // 4. Header Tabel Utama (Baris 6)
        $headers = [
            'A6' => 'Serial Number',
            'B6' => 'Product Name',
            'C6' => 'Quantity',
            'D6' => 'Weight ( kg )',
            'E6' => 'Dosage ( kGy )',
            'F6' => 'Processing Time (minutes)',
            'G6' => 'Remark',

            // 'D6' => 'quantity',
            // 'E6' => 'Weight ( kg )',
            // 'F6' => 'Dosage ( kGy )',
            // 'G6' => 'Processing Time (minutes)',
            // 'G6' => 'Remark'
        ];
        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // 5. Data Loop Grid (Sesuai Gambar ada 3 baris slot data utama: Baris 7, 8, 9)
        $startRow = 7;
        $maxRows = 3;

        for ($i = 0; $i < $maxRows; $i++) {
            $currentRow = $startRow + $i;
            $sheet->setCellValue('A' . $currentRow, $i + 1); // Serial Number 1, 2, 3

            if ($i === 0 && $products->count() > 0) {
                // Isi data riil item pertama di baris nomor 1
                $product = $products->first();
                $sheet->setCellValue('B' . $currentRow, $product->product_name ?? '-');
                $sheet->setCellValue('C' . $currentRow, ($product->quantity ?? 0) . ' ' . ($product->unit ?? ''));                // $sheet->setCellValue('D' . $currentRow, $product->quantity ?? 0);
                $sheet->setCellValue('D' . $currentRow, $product->gross_weight_per_pcs ?? 0);
                $sheet->setCellValue('E' . $currentRow, ($product->dmin ?? '') . '-' . ($product->dmax ?? ''));
                $sheet->setCellValue('F' . $currentRow, $durationText ?? '-'); // Processing Time (minutes)
                $sheet->setCellValue('G' . $currentRow, $booking->notes ?? '-');
            } else {
                // Slot kosong nomor 2 dan 3 untuk kebutuhan tulis tangan/template fisik
                $sheet->setCellValue('B' . $currentRow, '');
                $sheet->setCellValue('C' . $currentRow, '');
                $sheet->setCellValue('D' . $currentRow, '');
                $sheet->setCellValue('E' . $currentRow, '');
                $sheet->setCellValue('F' . $currentRow, '');
                $sheet->setCellValue('G' . $currentRow, '');
                // $sheet->setCellValue('H' . $currentRow, '');
            }
        }

        // 6. Other matters (Baris 10 & 11)
        $sheet->setCellValue('A10', 'Other matters:');
        $sheet->mergeCells('A10:G10');
        $sheet->mergeCells('A11:G11'); // Digabung jadi ruang kosong ketikan

        // 7. Kolom Tanda Tangan / Persetujuan (Baris 12 & 13)
        $sheet->setCellValue('A12', 'Shift Leader');
        $sheet->mergeCells('A12:B12', 'Bagaswara');
        $sheet->mergeCells('A13:B13'); // Kolom TTD kosong

        $sheet->setCellValue('C12', 'Production Manager');
        $sheet->mergeCells('C12:E12', 'Irwinsyah');
        $sheet->mergeCells('C13:E13'); // Kolom TTD kosong

        $sheet->setCellValue('F12', 'Marketing Department');
        $sheet->mergeCells('F12:G12');
        $sheet->mergeCells('F13:G13'); // Kolom TTD kosong

        // 8. Teks Vertikal Distribusi Dokumen (Kolom I disamping tabel)
        // Disimulasikan rapi di kolom sebelah kanan tabel utama
        $sheet->setCellValue('H4', "① Production Department (white)  ② Marketing Department (yellow)  ③ Finance Department (yellow)");
        $sheet->mergeCells('H4:I13');

        // 9. Footer Dokumen (Baris 15)
        $sheet->setCellValue('A15', 'Document Number: QR / PD-034');
        $sheet->setCellValue('D15', 'Effective Date: 2026/01/01');
        $sheet->setCellValue('F15', 'Version / Status : A /00');
        $sheet->mergeCells('F15:G15');


        // --- PROSES STYLING & FORMATTING ---
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');

        // Style Judul Utama
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style Sub-judul Nomor Order
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);

        // Aplikasikan Borders Tipis hanya ke area Grid Tabel Utama (A4 sampai G13)
        $sheet->getStyle('A4:G13')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A4:G13')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Style Label info customer & ttd (Bold judul cell)
        $sheet->getStyle('A4:G4')->getFont()->setBold(true);
        $sheet->getStyle('A4:G5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A6:G6')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('A6:G6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        // Alignment isi data grid tabel
        $sheet->getStyle('A7:A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B7:C9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D7:F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Style area TTD bawah
        $sheet->getStyle('A12:G12')->getFont()->setBold(true);
        $sheet->getStyle('A12:G12')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        // $sheet->getStyle('F7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('F7')->getAlignment()->setWrapText(true);




        // Style untuk teks vertikal / distribusi arsip di sebelah kanan (Kolom I)
        $sheet->getStyle('H4')->getFont()->setSize(9)->setBold(true);
        $sheet->getStyle('H4')->getAlignment()->setWrapText(true);
        $sheet->getStyle('H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('H4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('H4')->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM); // pemisah garis vertikal tebal

        // Style Footer Metada Dokumen paling bawah
        $sheet->getStyle('A15:G15')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('F15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();

                // Mengatur Lebar Kolom (Width) agar pas dengan susunan teks gambar ke-2
                $delegate->getColumnDimension('A')->setWidth(10);  // Serial Number
                $delegate->getColumnDimension('B')->setWidth(20);  // Product Name
                $delegate->getColumnDimension('C')->setWidth(22);  // Packaging specifications
                $delegate->getColumnDimension('D')->setWidth(12);  // quantity
                $delegate->getColumnDimension('E')->setWidth(14);  // Weight (kg)
                $delegate->getColumnDimension('F')->setWidth(14);  // Dosage (kGy)
                $delegate->getColumnDimension('G')->setWidth(20);  // Processing time (minutes)
                // $delegate->getColumnDimension('H')->setWidth(16);  // Remark
                $delegate->getColumnDimension('H')->setWidth(6);   // Kolom Samping Teks Vertikal

                // Mengatur Tinggi Baris (Row Height) agar proporsional dan longgar
                $delegate->getRowDimension(2)->setRowHeight(30);   // Judul
                $delegate->getRowDimension(3)->setRowHeight(22);   // Task Number
                $delegate->getRowDimension(4)->setRowHeight(20);   // Label Cust
                $delegate->getRowDimension(5)->setRowHeight(24);   // Isi Cust Name
                $delegate->getRowDimension(6)->setRowHeight(35);   // Header Tabel (Wrap Text)

                // Tinggi grid data utama 1, 2, 3
                $delegate->getRowDimension(7)->setRowHeight(30);
                $delegate->getRowDimension(8)->setRowHeight(30);
                $delegate->getRowDimension(9)->setRowHeight(30);

                // Baris Other Matters & Area Tanda Tangan
                $delegate->getRowDimension(10)->setRowHeight(20);
                $delegate->getRowDimension(11)->setRowHeight(35);  // Space kosong other matters
                $delegate->getRowDimension(12)->setRowHeight(22);  // Baris Nama Jabatan TTD
                $delegate->getRowDimension(13)->setRowHeight(55);  // Space kosong untuk tanda tangan fisik
            },
        ];
    }
}
