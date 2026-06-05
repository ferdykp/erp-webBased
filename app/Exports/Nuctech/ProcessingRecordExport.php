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

class ProcessingRecordExport implements FromCollection, WithStyles, WithEvents, WithMapping
{
    protected $id;
    protected $bookingInstance;

    public function __construct($id)
    {
        $this->id = $id;
        $this->bookingInstance = Booking::with(['customer.contacts', 'products', 'batches.productionLine'])->find($this->id);
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

        $contact = $booking->customer->contacts->first();
        $contactName = $contact ? $contact->name : '-';
        $companyName = $booking->customer->company_name ?? '-';
        $product = $booking->products->first();
        $firstBatch = $booking->batches->first();

        // --- KONSTRUKSI FORM (Koordinat diperbaiki agar tidak bentrok) ---

        // Baris 2 - 5: Header Utama
        $sheet->setCellValue('A2', 'Machine #1');
        $sheet->mergeCells('A2:C5');

        $sheet->setCellValue('D2', 'Irradiation Processing Record Form');
        $sheet->mergeCells('D2:M5');

        // Baris 6: Batch Number
        $batchNumbers = $booking->batches->pluck('batch_number')->implode(', ');
        $sheet->setCellValue('A6', 'batch number: ' . ($batchNumbers ?: '-'));
        $sheet->mergeCells('A6:M6');

        // Baris 7 - 8: Customer & Product
        $sheet->setCellValue('A7', 'Customer Name');
        $sheet->mergeCells('A7:B8'); // Di-merge B8 agar simetris dengan data kanan
        $sheet->setCellValue('C7', $companyName . ' / ' . $contactName);
        $sheet->mergeCells('C7:F8');

        $sheet->setCellValue('G7', 'Product Name');
        $sheet->mergeCells('G7:H8');
        $sheet->setCellValue('I7', $product->product_name ?? '-');
        $sheet->mergeCells('I7:M8');

        // Baris 9 - 10: Task Order & Purpose
        $sheet->setCellValue('A9', 'Processing task order number');
        $sheet->mergeCells('A9:B10');
        $sheet->setCellValue('C9', $booking->booking_code ?? '-');
        $sheet->mergeCells('C9:F10');

        $sheet->setCellValue('G9', 'Irradiation purpose');
        $sheet->mergeCells('G9:H10');
        $sheet->setCellValue('I9', 'Sterilization [ √ ]      Modified [   ]');
        $sheet->mergeCells('I9:M10');

        // Baris 11 - 12: Packaging & Quantity
        $sheet->setCellValue('A11', 'Packaging specifications');
        $sheet->mergeCells('A11:B12');
        $sheet->setCellValue('C11', $product->packaging ?? 'The outer carton of the product is a two-layer corrugated cardboard box and the inner packaging is a PP plastic bag.');
        $sheet->mergeCells('C11:H12');

        $sheet->setCellValue('I11', 'Product Quantity');
        $sheet->mergeCells('I11:M11');
        $sheet->setCellValue('I12', ($product->quantity ?? 0) . ' ' . ($product->unit ?? 'Pcs'));
        $sheet->mergeCells('I12:M12');

        // Baris 13 - 14: Subcontracting
        $sheet->setCellValue('A13', 'Subcontracting');
        $sheet->mergeCells('A13:B14');
        $sheet->setCellValue('C13', 'Yes [   ]      No [ √ ]');
        $sheet->mergeCells('C13:D14');

        $sheet->setCellValue('E13', 'Specifications after subcontracting');
        $sheet->mergeCells('E13:F14');
        $sheet->setCellValue('G13', 'Height (cm): -');
        $sheet->mergeCells('G13:H13');
        $sheet->setCellValue('G14', 'Width (cm): -');
        $sheet->mergeCells('G14:H14');

        $sheet->setCellValue('I13', 'Quantity after subcontracting');
        $sheet->mergeCells('I13:M13');
        $sheet->setCellValue('I14', '-');
        $sheet->mergeCells('I14:M14');

        // Baris 15: Process Dosage, Loading Mode & Method
        $sheet->setCellValue('A15', 'Process dosage kGy');
        $sheet->mergeCells('A15:B15');
        $sheet->setCellValue('C15', ($product->dmin ?? '') . ' - ' . ($product->dmax ?? '') . ' kGy');
        $sheet->mergeCells('C15:D15');

        $sheet->setCellValue('E15', 'Loading mode');
        $sheet->mergeCells('E15:F15');
        $sheet->setCellValue('G15', $firstBatch->loading_mode ?? '-');
        $sheet->mergeCells('G15:H15');

        $sheet->setCellValue('I15', 'Irradiation method');
        $sheet->mergeCells('I15:J15');
        $sheet->setCellValue('K15', 'Single [ √ ]  Pair [   ]');
        $sheet->mergeCells('K15:L15');
        $sheet->setCellValue('M15', 'Order');

        // Baris 16: Basis
        $sheet->setCellValue('A16', 'Irradiation process basis');
        $sheet->mergeCells('A16:B16');
        $sheet->setCellValue('C16', 'other: ' . ($booking->notes ?? '-'));
        $sheet->mergeCells('C16:M16');

        // Baris 17: Equipment Parameters Header
        $sheet->setCellValue('A17', 'Equipment parameters');
        $sheet->mergeCells('A17:M17');

        // Baris 18: Power & Frequency
        $sheet->setCellValue('A18', 'Equipment power (KW)');
        $sheet->mergeCells('A18:B18');
        $sheet->setCellValue('C18', '-');
        $sheet->mergeCells('C18:F18');

        $sheet->setCellValue('G18', 'Pulse frequency HZ');
        $sheet->mergeCells('G18:H18');
        $sheet->setCellValue('I18', $firstBatch->freq ?? '-');
        $sheet->mergeCells('I18:M18');

        // Baris 19: Scanning gear & Beam Velocity
        $sheet->setCellValue('A19', 'Scanning gear');
        $sheet->mergeCells('A19:B19');
        $sheet->setCellValue('C19', $firstBatch->scan_gear ?? '-');
        $sheet->mergeCells('C19:F19');

        $sheet->setCellValue('G19', 'Beam Velocity mm/s');
        $sheet->mergeCells('G19:H19');
        $sheet->setCellValue('I19', $firstBatch->beam_speed ?? '-');
        $sheet->mergeCells('I19:M19');

        // Baris 20: Start Time
        $startTime = $booking->batches->min('offline_at');
        $sheet->setCellValue('A20', 'Irradiation start time');
        $sheet->mergeCells('A20:B20');
        $sheet->setCellValue('C20', $startTime ? Carbon::parse($startTime)->format('H:i A') : '-');
        $sheet->mergeCells('C20:M20');

        // Baris 21: Processing Quantity Header
        $sheet->setCellValue('A21', 'Irradiation processing quantity');
        $sheet->mergeCells('A21:M21');

        // Baris 22 - 25: Dosimeter Data (Kini diakhiri di Baris 25, tidak tabrakan lagi)
        $sheet->setCellValue('A22', 'Dosimeter data');
        $sheet->mergeCells('A22:B25');

        $sheet->setCellValue('C22', 'Dosimeter position');
        $sheet->mergeCells('C22:D22');
        $sheet->setCellValue('E22', 'Remove the product surface after one pass.');
        $sheet->mergeCells('E22:M22');

        $sheet->setCellValue('C23', 'Equipment Number');
        $sheet->mergeCells('C23:D23');
        $sheet->setCellValue('E23', $firstBatch->productionLine->name ?? '-');
        $sheet->mergeCells('E23:M23');

        $sheet->setCellValue('C24', 'absorbance');
        $sheet->mergeCells('C24:D24');
        $sheet->setCellValue('E24', '-');
        $sheet->mergeCells('E24:M24');

        $sheet->setCellValue('C25', 'dose');
        $sheet->mergeCells('C25:D25');
        $sheet->setCellValue('E25', ($product->target_dose ?? '-') . ' kGy');
        $sheet->mergeCells('E25:M25');

        // Baris 26: Analyze (Digeser penuh ke baris 26 murni tanpa tumpang tindih)
        $sheet->setCellValue('A26', 'analyze');
        $sheet->mergeCells('A26:B26');
        $sheet->setCellValue('C26', 'Conforms to process standard specifications.');
        $sheet->mergeCells('C26:M26');

        // Baris 27 - 29: Signatures
        $sheet->setCellValue('A27', 'Process engineer: Readi');
        $sheet->mergeCells('A27:B27');
        $sheet->setCellValue('C27', 'date: ' . Carbon::now()->format('d/m/Y'));
        $sheet->mergeCells('C27:F27');
        $sheet->setCellValue('G27', 'Organizer:');
        $sheet->mergeCells('G27:H27');
        $sheet->setCellValue('I27', 'Date of Compilation:');
        $sheet->mergeCells('I27:M27');

        $sheet->setCellValue('A28', "Operator's signature:");
        $sheet->mergeCells('A28:C28');
        $sheet->mergeCells('D28:F28');
        $sheet->mergeCells('G28:I28');
        $sheet->setCellValue('J28', 'date:');
        $sheet->mergeCells('J28:M28');

        $sheet->setCellValue('A29', "Signature of person in charge:");
        $sheet->mergeCells('A29:C29');
        $sheet->mergeCells('D29:F29');
        $sheet->mergeCells('G29:I29');
        $sheet->setCellValue('J29', 'date:');
        $sheet->mergeCells('J29:M29');

        // Baris 31: Footer
        $sheet->setCellValue('A31', 'Effective Date: 2026/01/01   Version/Status: A/00');
        $sheet->mergeCells('A31:M31');


        // --- PROSES GLOBAL STYLING & GRID BORDERS ---
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(10);

        $sheet->getStyle('A2:M29')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:M29')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sections = ['A6', 'A17', 'A21'];
        foreach ($sections as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $leftLabels = [
            'A7',
            'G7',
            'A9',
            'G9',
            'A11',
            'I11',
            'A13',
            'E13',
            'I13',
            'A15',
            'E15',
            'I15',
            'A16',
            'A18',
            'G18',
            'A19',
            'G19',
            'A20',
            'A22',
            'C22',
            'C23',
            'C24',
            'C25',
            'A26'
        ];
        foreach ($leftLabels as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $centerValues = ['C15', 'G15', 'K15', 'C18', 'I18', 'C19', 'I19', 'C20', 'E23', 'E24', 'E25'];
        foreach ($centerValues as $cell) {
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle('C11')->getAlignment()->setWrapText(true);
        $sheet->getStyle('K15')->getAlignment()->setWrapText(true);

        $sheet->getStyle('A31')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A31')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();

                $delegate->getColumnDimension('A')->setWidth(15);
                $delegate->getColumnDimension('B')->setWidth(15);
                $delegate->getColumnDimension('C')->setWidth(12);
                $delegate->getColumnDimension('D')->setWidth(12);
                $delegate->getColumnDimension('E')->setWidth(12);
                $delegate->getColumnDimension('F')->setWidth(12);
                $delegate->getColumnDimension('G')->setWidth(12);
                $delegate->getColumnDimension('H')->setWidth(12);
                $delegate->getColumnDimension('I')->setWidth(12);
                $delegate->getColumnDimension('J')->setWidth(10);
                $delegate->getColumnDimension('K')->setWidth(12);
                $delegate->getColumnDimension('L')->setWidth(10);
                $delegate->getColumnDimension('M')->setWidth(14);

                // Set Tinggi Baris
                $delegate->getRowDimension(2)->setRowHeight(15);
                $delegate->getRowDimension(3)->setRowHeight(15);
                $delegate->getRowDimension(4)->setRowHeight(15);
                $delegate->getRowDimension(5)->setRowHeight(15);

                $delegate->getRowDimension(6)->setRowHeight(24);
                $delegate->getRowDimension(7)->setRowHeight(20);
                $delegate->getRowDimension(8)->setRowHeight(20);
                $delegate->getRowDimension(9)->setRowHeight(20);
                $delegate->getRowDimension(10)->setRowHeight(20);
                $delegate->getRowDimension(11)->setRowHeight(22);
                $delegate->getRowDimension(12)->setRowHeight(22);

                $delegate->getRowDimension(13)->setRowHeight(20);
                $delegate->getRowDimension(14)->setRowHeight(20);
                $delegate->getRowDimension(15)->setRowHeight(26);
                $delegate->getRowDimension(16)->setRowHeight(24);
                $delegate->getRowDimension(17)->setRowHeight(24);

                $delegate->getRowDimension(18)->setRowHeight(22);
                $delegate->getRowDimension(19)->setRowHeight(22);
                $delegate->getRowDimension(20)->setRowHeight(22);
                $delegate->getRowDimension(21)->setRowHeight(24);

                for ($r = 22; $r <= 26; $r++) {
                    $delegate->getRowDimension($r)->setRowHeight(20);
                }

                $delegate->getRowDimension(27)->setRowHeight(24);
                $delegate->getRowDimension(28)->setRowHeight(35);
                $delegate->getRowDimension(29)->setRowHeight(35);
                $delegate->getRowDimension(31)->setRowHeight(20);
            },
        ];
    }
}
