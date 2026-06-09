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
        // Ambil model dengan eager loading relasi batches untuk kalkulasi waktu & qty proses
        $this->bookingInstance = Booking::with(['customer', 'products', 'batches'])->find($this->id);
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

        $product = $booking->products->first();

        // --- KALKULASI DATA REAL DARI RELEVANSI BATCHES ---
        $qty = $product->quantity ?? 0;
        $unit = $product->unit ?? 'box';

        // 1. Total qty yang sudah diproses diambil dari sum qty di tabel batches
        $processedQty = $booking->batches->sum('qty') ?? 0;

        // 2. Sisa qty yang belum diproses
        $remainingQty = max(0, $qty - $processedQty);

        // 3. Waktu Mulai (Paling Awal) dan Selesai (Paling Akhir)
        $minOffline = $booking->batches->whereNotNull('offline_at')->min('offline_at');
        $maxFinished = $booking->batches->whereNotNull('finished_at')->max('finished_at');

        $processingTimeStr = "-";
        $totalTimeMinutes = 0;

        if ($minOffline && $maxFinished) {
            $startTime = Carbon::parse($minOffline);
            $endTime = Carbon::parse($maxFinished);

            // Format rentang waktu
            $processingTimeStr = $startTime->format('H:i') . ' – ' . $endTime->format('H:i') . ' WIB';

            // Paksa hasil pembulatan selisih ke integer menit bulat
            $totalTimeMinutes = (int) $startTime->diffInMinutes($endTime);
        }

        // --- KONSTRUKSI FORM ---

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
        $sheet->setCellValue('F4', "Time: " . ($processingTimeStr != '-' ? $processingTimeStr : '((Fill By Yourself))'));
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

        // 7. JOB DUTIES (Struktur tabel dipecah per baris agar rata kanan-kiri sempurna)
        $sheet->setCellValue('A9', "Job Duties");
        $sheet->mergeCells('A9:B15'); // Menggabungkan Label Utama vertikal ke bawah dari baris 9 s/d 15

        $arrivedDate = $booking->arrival_time ? Carbon::parse($booking->arrival_time)->format('d F Y') : '-';
        $weightKg = $product ? ($product->total_gross_weight ?? 0) : 0; // Tetap KG asli, tidak dibagi 1000

        // Baris 9: Container Arrived
        $sheet->setCellValue('C9', "Container Arrived");
        $sheet->setCellValue('D9', ": " . $arrivedDate);
        $sheet->mergeCells('D9:H9');

        // Baris 10: Weight
        $sheet->setCellValue('C10', "Weight");
        $sheet->setCellValue('D10', ": " . number_format($weightKg, 0, ',', '.') . " KG");
        $sheet->mergeCells('D10:H10');

        // Baris 11: Total Quantity
        $sheet->setCellValue('C11', "Total Quantity");
        $sheet->setCellValue('D11', ": " . number_format($qty, 0, ',', '.') . " " . $unit);
        $sheet->mergeCells('D11:H11');

        // Baris 12: Processed Products
        $sheet->setCellValue('C12', "Remaining Unprocessed Products");
        $sheet->setCellValue('D12', ": " . number_format($processedQty, 0, ',', '.') . " " . $unit);
        $sheet->mergeCells('D12:H12');

        // Baris 13: Remaining Unprocessed Products
        $sheet->setCellValue('C13', "Processed Products");
        $sheet->setCellValue('D13', ": " . number_format($remainingQty, 0, ',', '.') . " " . $unit);
        $sheet->mergeCells('D13:H13');

        // Baris 14: Processing Time
        $sheet->setCellValue('C14', "Processing Time");
        $sheet->setCellValue('D14', ": " . $processingTimeStr);
        $sheet->mergeCells('D14:H14');

        // Baris 15: Total Time
        $sheet->setCellValue('C15', "Total Time");
        $sheet->setCellValue('D15', ": " . ($totalTimeMinutes > 0 ? $totalTimeMinutes . " min" : "0 min"));
        $sheet->mergeCells('D15:H15');

        // 8. Other & Handover (Mundur ke baris 16 dan 17 akibat ekspansi baris Job Duties)
        $sheet->setCellValue('A16', "Other matters");
        $sheet->mergeCells('A16:B16');
        $sheet->mergeCells('C16:H16');

        $sheet->setCellValue('A17', "Handover content");
        $sheet->mergeCells('A17:B17');
        $sheet->mergeCells('C17:H17');

        // 9. Footer & Signature
        $sheet->setCellValue('A18', "Successor's signature:");
        $sheet->mergeCells('A18:H18');
        $sheet->getStyle('A18')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->setCellValue('A19', "Effective Date: 2026/01/01 Version/Status: A/00");

        // --- STYLING ---
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(10);
        $sheet->getStyle('A2:H2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2:H17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:H17')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:H2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Penyelarasan letak teks label kiri Job Duties
        $sheet->getStyle('A8:B15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A16:B17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A8:B8')->getAlignment()->setWrapText(true);

        // Set seluruh label komponen Job Duties di Kolom C menjadi Rata Kiri (Left Alignment)
        for ($row = 9; $row <= 15; $row++) {
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();
                $delegate->getColumnDimension('A')->setWidth(15);
                $delegate->getColumnDimension('B')->setWidth(15);
                $delegate->getColumnDimension('C')->setWidth(30); // Lebar proporsional untuk label deskripsi tugas
                $delegate->getColumnDimension('D')->setWidth(15);
                $delegate->getColumnDimension('E')->setWidth(15);
                $delegate->getColumnDimension('F')->setWidth(15);
                $delegate->getColumnDimension('G')->setWidth(15);
                $delegate->getColumnDimension('H')->setWidth(15);

                $delegate->getRowDimension(8)->setRowHeight(40);

                // Tinggi baris seragam dan rapi untuk data pecahan Job Duties
                for ($row = 9; $row <= 15; $row++) {
                    $delegate->getRowDimension($row)->setRowHeight(22);
                }

                $delegate->getRowDimension(17)->setRowHeight(60); // Handover Content
            },
        ];
    }
}
