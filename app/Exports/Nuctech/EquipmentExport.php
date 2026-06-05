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

class EquipmentExport implements FromCollection, WithStyles, WithEvents, WithMapping
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        // Eager load batches untuk mengisi baris-baris log mesin secara detail
        $this->bookingInstance = Booking::with(['customer', 'products', 'batches.productionLine'])->find($this->id);
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
        $booking = ($this->bookingInstance instanceof \Illuminate\Support\Collection)
            ? $this->bookingInstance->first()
            : $this->bookingInstance;

        if (!$booking) {
            $sheet->setCellValue('A1', 'Data Tidak Ditemukan');
            return [];
        }

        $companyName = $booking->customer->company_name ?? '-';
        $product = $booking->products->first();
        $productName = $product ? $product->product_name : '-';
        $targetDose = $product ? ($product->dmin . '-' . $product->dmax) : '-';

        // --- BARIS 2: JUDUL UTAMA & EQUIPMENT SN ---
        $sheet->setCellValue('A2', 'Equipment Operation Record');
        $sheet->mergeCells('A2:N2');

        $firstBatch = $booking->batches->first();
        $equipmentSn = $firstBatch && $firstBatch->productionLine ? $firstBatch->productionLine->name : 'Machine 01';
        $sheet->setCellValue('O2', 'Equipment SN. : ' . $equipmentSn);
        $sheet->mergeCells('O2:R2');

        // --- BARIS 3 & 4: HEADER TABEL MULTI-LEVEL ---
        // Baris 3 (Header Atas)
        $sheet->setCellValue('A3', 'SN');
        $sheet->mergeCells('A3:A4');

        $sheet->setCellValue('B3', 'Date');
        $sheet->mergeCells('B3:B4');

        $sheet->setCellValue('C3', 'Customer Name');
        $sheet->mergeCells('C3:D4'); // Menggabungkan kolom C dan D untuk space Nama Customer

        $sheet->setCellValue('E3', 'Product Name');
        $sheet->mergeCells('E3:F4'); // Menggabungkan kolom E dan F untuk space Nama Produk

        $sheet->setCellValue('G3', "Frequency\n(Hz)");
        $sheet->mergeCells('G3:G4');

        $sheet->setCellValue('H3', "Dose\n(kGy)");
        $sheet->mergeCells('H3:H4');

        $sheet->setCellValue('I3', 'Gear');
        $sheet->mergeCells('I3:I4');

        $sheet->setCellValue('J3', "Speed\n(mm/s)");
        $sheet->mergeCells('J3:J4');

        $sheet->setCellValue('K3', 'Flip');
        $sheet->mergeCells('K3:K4');

        $sheet->setCellValue('L3', 'Processing time');
        $sheet->mergeCells('L3:N3'); // Rentang sub-header untuk waktu

        $sheet->setCellValue('O3', 'Operator');
        $sheet->mergeCells('O3:O4');

        $sheet->setCellValue('P3', 'Machine');
        $sheet->mergeCells('P3:R3'); // Rentang sub-header untuk status mesin

        $sheet->setCellValue('S3', 'Remark');
        $sheet->mergeCells('S3:S4');

        // Baris 4 (Sub-Header Bawah)
        $sheet->setCellValue('L4', 'Start Time');
        $sheet->setCellValue('M4', 'Stop Time');
        $sheet->setCellValue('N4', "Duration\n(minutes)");

        $sheet->setCellValue('P4', 'Power Up');
        $sheet->setCellValue('Q4', 'Power off');
        $sheet->setCellValue('R4', 'Condition');


        // --- BARIS 5 PASCA: POPULASI DATA BATCHES ---
        $startRow = 5;
        $totalRowsTemplate = 20; // Menyiapkan 20 baris log kosong sesuai tampilan fisik lembar kendali

        foreach ($booking->batches as $index => $batch) {
            $currentRow = $startRow + $index;

            // Hitung Durasi Menit
            $durationMinutes = '-';
            if ($batch->offline_at && $batch->finished_at) {
                $start = Carbon::parse($batch->offline_at);
                $end = Carbon::parse($batch->finished_at);
                $durationMinutes = round($start->diffInMinutes($end));
            }

            // Set Nilai Sel Baris Data
            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $batch->offline_at ? Carbon::parse($batch->offline_at)->format('d/m/Y') : '-');

            $sheet->setCellValue('C' . $currentRow, $companyName);
            $sheet->mergeCells("C{$currentRow}:D{$currentRow}");

            $sheet->setCellValue('E' . $currentRow, $productName);
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");

            $sheet->setCellValue('G' . $currentRow, $batch->freq ?? '-');
            $sheet->setCellValue('H' . $currentRow, $targetDose);
            $sheet->setCellValue('I' . $currentRow, $batch->scan_gear ?? '-');
            $sheet->setCellValue('J' . $currentRow, $batch->beam_speed ?? '-');
            $sheet->setCellValue('K' . $currentRow, 'Single'); // Default value sesuai jenis mesin

            $sheet->setCellValue('L' . $currentRow, $batch->offline_at ? Carbon::parse($batch->offline_at)->format('H:i') : '-');
            $sheet->setCellValue('M' . $currentRow, $batch->finished_at ? Carbon::parse($batch->finished_at)->format('H:i') : '-');
            $sheet->setCellValue('N' . $currentRow, $durationMinutes);

            $sheet->setCellValue('O' . $currentRow, $batch->porter_name ?? 'Operator 01');

            $sheet->setCellValue('P' . $currentRow, '[ √ ]');
            $sheet->setCellValue('Q' . $currentRow, '[   ]');
            $sheet->setCellValue('R' . $currentRow, 'Normal');
            $sheet->setCellValue('S' . $currentRow, $booking->notes ?? '');
        }

        // Buat sisa baris kosong agar grid tabel tetap presisi membentuk form cetak
        $recordedBatchesCount = $booking->batches->count();
        for ($i = $recordedBatchesCount; $i < $totalRowsTemplate; $i++) {
            $currentRow = $startRow + $i;
            $sheet->setCellValue('A' . $currentRow, $i + 1);
            $sheet->mergeCells("C{$currentRow}:D{$currentRow}");
            $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
        }

        // --- FORMATTING & STYLING ---
        $lastRow = $startRow + $totalRowsTemplate - 1;
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('SimSun')->setSize(10); // Menggunakan font oriental standard sesuai bawaan sistem Nuctech

        // Border & Alignment untuk Grid Utama
        $sheet->getStyle("A2:S{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A2:S{$lastRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A2:S{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Bold Header Spesifik
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('O2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle("A3:S4")->getFont()->setBold(true);
        $sheet->getStyle("A3:S4")->getAlignment()->setWrapText(true);

        // Custom Alignment Baris Data (Nama perusahaan & produk rata kiri agar terbaca rapi)
        $sheet->getStyle("C5:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();

                // Set Dimensi Lebar Kolom Pas dengan Struktur Header Bertingkat
                $delegate->getColumnDimension('A')->setWidth(5);   // SN
                $delegate->getColumnDimension('B')->setWidth(13);  // Date
                $delegate->getColumnDimension('C')->setWidth(14);  // Cust Name Part 1
                $delegate->getColumnDimension('D')->setWidth(14);  // Cust Name Part 2
                $delegate->getColumnDimension('E')->setWidth(13);  // Product Name Part 1
                $delegate->getColumnDimension('F')->setWidth(13);  // Product Name Part 2
                $delegate->getColumnDimension('G')->setWidth(11);  // Frequency
                $delegate->getColumnDimension('H')->setWidth(11);  // Dose
                $delegate->getColumnDimension('I')->setWidth(8);   // Gear
                $delegate->getColumnDimension('J')->setWidth(10);  // Speed
                $delegate->getColumnDimension('K')->setWidth(8);   // Flip
                $delegate->getColumnDimension('L')->setWidth(11);  // Start Time
                $delegate->getColumnDimension('M')->setWidth(11);  // Stop Time
                $delegate->getColumnDimension('N')->setWidth(11);  // Duration
                $delegate->getColumnDimension('O')->setWidth(12);  // Operator
                $delegate->getColumnDimension('P')->setWidth(11);  // Power Up
                $delegate->getColumnDimension('Q')->setWidth(11);  // Power Off
                $delegate->getColumnDimension('R')->setWidth(12);  // Condition
                $delegate->getColumnDimension('S')->setWidth(16);  // Remark

                // Set Tinggi Baris
                $delegate->getRowDimension(2)->setRowHeight(35); // Judul Form
                $delegate->getRowDimension(3)->setRowHeight(20); // Header Atas
                $delegate->getRowDimension(4)->setRowHeight(25); // Header Bawah (Wrap Text)

                // Tinggi Baris Baris Grid Pengisian Data Log (Baris 5 s/d 25+)
                for ($row = 5; $row <= 30; $row++) {
                    $delegate->getRowDimension($row)->setRowHeight(22);
                }
            },
        ];
    }
}
