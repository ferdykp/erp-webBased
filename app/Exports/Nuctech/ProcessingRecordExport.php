<?php

namespace App\Exports\Nuctech;

use App\Models\Booking;
use App\Models\DosimeterRecord;
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

        // --- PARSING NILAI DIMENSION PACK ---
        $height = '-';
        $width  = '-';
        $long   = '-';

        if ($product && !empty($product->dimension_pack)) {
            $cleanDimension = str_replace([' ', '*', 'X'], 'x', $product->dimension_pack);
            $dimensions = explode('x', $cleanDimension);

            if (count($dimensions) === 3) {
                $long   = trim($dimensions[0]); // Panjang
                $width  = trim($dimensions[1]); // Lebar
                $height = trim($dimensions[2]); // Tinggi
            }
        }

        // --- AMBIL DATA DOSIMETER REAL DARI DATABASE ---
        $record = DosimeterRecord::with('details')
            ->where('booking_id', $booking->id)
            ->first();

        // Siapkan variabel penampung default kosong/strip
        $abs1 = '-';
        $dose1 = '-';
        $abs2 = '-';
        $dose2 = '-';
        $abs3 = '-';
        $dose3 = '-';

        if ($record && $record->details->count() > 0) {
            // Filter hanya yang sudah diisi absorbance-nya dan urutkan berdasarkan nomor tablet
            $details = $record->details->whereNotNull('absorbance')->sortBy('tablet_number')->values();
            $totalTablet = $details->count();

            if ($totalTablet > 0) {
                if ($totalTablet <= 3) {
                    // KONDISI A: Jika data <= 3, masukkan berurutan apa adanya
                    if (isset($details[0])) {
                        $abs1  = (float)$details[0]->absorbance;
                        $dose1 = rtrim(rtrim(number_format($details[0]->dose_kgy, 4, ',', '.'), '0'), ',');
                    }
                    if (isset($details[1])) {
                        $abs2  = (float)$details[1]->absorbance;
                        $dose2 = rtrim(rtrim(number_format($details[1]->dose_kgy, 4, ',', '.'), '0'), ',');
                    }
                    if (isset($details[2])) {
                        $abs3  = (float)$details[2]->absorbance;
                        $dose3 = rtrim(rtrim(number_format($details[2]->dose_kgy, 4, ',', '.'), '0'), ',');
                    }
                } else {
                    // KONDISI B: Jika data > 3 (misal 9), ambil 3 sampel representatif (Awal, Tengah, Akhir)
                    // Titik 1: Tablet Pertama
                    $firstItem = $details->first();
                    $abs1  = (float)$firstItem->absorbance;
                    $dose1 = rtrim(rtrim(number_format($firstItem->dose_kgy, 4, ',', '.'), '0'), ',');

                    // Titik 2: Tablet Tengah (Median Indeks)
                    $midIndex = floor($totalTablet / 2);
                    $midItem  = $details[$midIndex];
                    $abs2  = (float)$midItem->absorbance;
                    $dose2 = rtrim(rtrim(number_format($midItem->dose_kgy, 4, ',', '.'), '0'), ',');

                    // Titik 3: Tablet Terakhir
                    $lastItem = $details->last();
                    $abs3  = (float)$lastItem->absorbance;
                    $dose3 = rtrim(rtrim(number_format($lastItem->dose_kgy, 4, ',', '.'), '0'), ',');
                }
            }
        }

        // --- KONSTRUKSI STRUKTUR MATRIKS FORM (KOLOM A s/d M) ---

        // Baris 2 - 5: Header Dokumen Utama
        $sheet->setCellValue('A2', 'Machine #1');
        $sheet->mergeCells('A2:C5');

        $sheet->setCellValue('D2', 'Irradiation Processing Record Form');
        $sheet->mergeCells('D2:M5');

        // Baris 6: Batch Number
        $sheet->setCellValue('A6', 'Batch number: ' . ($booking->booking_code ?? '-'));
        $sheet->mergeCells('A6:M6');

        // Baris 7 - 8: Data Customer & Produk
        $sheet->setCellValue('A7', 'Customer Name');
        $sheet->mergeCells('A7:B8');
        $sheet->setCellValue('C7', ($contactName ?? '-') . ($companyName ? " ({$companyName})" : ""));
        $sheet->mergeCells('C7:F8');

        $sheet->setCellValue('G7', 'Product Name');
        $sheet->mergeCells('G7:H8');
        $sheet->setCellValue('I7', $product->product_name ?? '-');
        $sheet->mergeCells('I7:M8');

        // Baris 9 - 10: Task Order & Tujuan Iradiasi
        $sheet->setCellValue('A9', 'Processing task order number');
        $sheet->mergeCells('A9:B10');
        $sheet->setCellValue('C9', $booking->booking_code ?? '-');
        $sheet->mergeCells('C9:F10');

        $sheet->setCellValue('G9', 'Irradiation purpose');
        $sheet->mergeCells('G9:H10');
        $sheet->setCellValue('I9', 'Sterilization [ √ ]      Modified [   ]');
        $sheet->mergeCells('I9:M10');

        // Baris 11 - 12: Packaging Specifications
        $sheet->setCellValue('A11', 'Packaging specifications');
        $sheet->mergeCells('A11:B12');

        $sheet->setCellValue('C11', $product->unit ?? 'Karton Dus');
        $sheet->mergeCells('C11:M11');

        $sheet->setCellValue('C12', 'Dimension');
        $sheet->setCellValue('D12', $product->dimension_pack ?? '-');
        $sheet->mergeCells('D12:F12');

        $sheet->setCellValue('G12', 'Weight (kg)');
        $sheet->setCellValue('H12', $product->gross_weight_per_pcs ?? '-');
        $sheet->mergeCells('H12:I12');

        $sheet->setCellValue('J12', 'Product Quantity');
        $sheet->setCellValue('K12', $product->quantity ?? '-');
        $sheet->mergeCells('K12:M12');

        // --- BLOK BARIS 13 - 16 (SUBCONTRACTING BLOCK) ---
        $sheet->setCellValue('A13', 'Subcontracting');
        $sheet->mergeCells('A13:B13');

        $sheet->setCellValue('C13', "Yes [   ]   No [ √ ]");
        $sheet->mergeCells('C13:D13');

        $sheet->setCellValue('E13', 'Specifications after subcontracting');
        $sheet->mergeCells('E13:F13');

        $sheet->setCellValue('G13', '');
        $sheet->mergeCells('G13:I13');

        $sheet->setCellValue('J13', 'Quantity after subcontracting');
        $sheet->mergeCells('J13:M13');

        // Isian Lajur Bawah
        $sheet->setCellValue('A14', 'Process dosage kGy');
        $sheet->mergeCells('A14:B16');
        $dmin = isset($product->dmin) ? (float)$product->dmin : '-';
        $sheet->setCellValue('C14', $dmin . ' - ' . ($product->dmax ?? '') . ' kGy');
        $sheet->mergeCells('C14:D16');

        $sheet->setCellValue('E14', 'Loading mode');
        $sheet->mergeCells('E14:F14');
        $sheet->setCellValue('E15', $firstBatch->loading_mode ?? '-');
        $sheet->mergeCells('E15:F16');

        // Dimensi Bertingkat 3 Baris Ke bawah
        $sheet->setCellValue('G14', 'Height (cm)');
        $sheet->setCellValue('H14', $height);
        $sheet->mergeCells('H14:I14');

        $sheet->setCellValue('G15', 'Width (cm)');
        $sheet->setCellValue('H15', $width);
        $sheet->mergeCells('H15:I15');

        $sheet->setCellValue('G16', 'Long (cm)');
        $sheet->setCellValue('H16', $long);
        $sheet->mergeCells('H16:I16');

        // Product Quantity & Pilihan Centang Single/Pair
        $sheet->setCellValue('J14', 'Product Quantity');
        $sheet->mergeCells('J14:K16');

        $sheet->setCellValue('L14', "Single  [ √ ]\nPair     [   ]");
        $sheet->mergeCells('L14:M16');
        $sheet->getStyle('L14')->getAlignment()->setWrapText(true);
        $sheet->getStyle('L14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Baris 17: Irradiation Process Basis
        $sheet->setCellValue('A17', 'Irradiation process basis');
        $sheet->mergeCells('A17:B17');
        $sheet->setCellValue('C17', 'other: ' . ($booking->notes ?? '-'));
        $sheet->mergeCells('C17:M17');

        // Baris 18: Section Header Parameter Mesin
        $sheet->setCellValue('A18', 'Equipment parameters');
        $sheet->mergeCells('A18:M18');

        // Baris 19: Power & Pulse Frequency
        $sheet->setCellValue('A19', 'Equipment power (KW)');
        $sheet->mergeCells('A19:B19');
        $sheet->setCellValue('C19', '20');
        $sheet->mergeCells('C19:F19');

        $sheet->setCellValue('G19', 'Pulse frequency HZ');
        $sheet->mergeCells('G19:I19');
        $sheet->setCellValue('J19', $firstBatch->freq ?? '-');
        $sheet->mergeCells('J19:M19');

        // Baris 20: Scanning Gear & Beam Velocity
        $sheet->setCellValue('A20', 'Scanning gear');
        $sheet->mergeCells('A20:B20');
        $sheet->setCellValue('C20', $firstBatch->scan_gear ?? '-');
        $sheet->mergeCells('C20:F20');

        $sheet->setCellValue('G20', 'Beam Velocity mm/s');
        $sheet->mergeCells('G20:I20');
        $sheet->setCellValue('J20', $firstBatch->beam_speed ?? '-');
        $sheet->mergeCells('J20:M20');

        // Baris 21: Log Waktu Start & Finish
        $startTime = $booking->batches->min('offline_at');
        $endTime = $booking->batches->max('finished_at');

        $sheet->setCellValue('A21', 'Irradiation start time');
        $sheet->mergeCells('A21:B21');
        $sheet->setCellValue('C21', $startTime ? Carbon::parse($startTime)->format('d/m/Y H:i') : '-');
        $sheet->mergeCells('C21:F21');

        $sheet->setCellValue('G21', 'Irradiation finish time');
        $sheet->mergeCells('G21:I21');
        $sheet->setCellValue('J21', $endTime ? Carbon::parse($endTime)->format('d/m/Y H:i') : '-');
        $sheet->mergeCells('J21:M21');

        // Baris 22: Section Header Quantity Proses
        $sheet->setCellValue('A22', 'Irradiation processing quantity');
        $sheet->mergeCells('A22:M22');

        // Baris 23 - 26: Bagian Input Data Dosimeter Dinamis
        $sheet->setCellValue('A23', 'Dosimeter data');
        $sheet->mergeCells('A23:B26');

        $sheet->setCellValue('C23', 'Dosimeter position');
        $sheet->mergeCells('C23:D23');
        $sheet->setCellValue('E23', 'Remove the product surface after two pass.');
        $sheet->mergeCells('E23:M23');

        $sheet->setCellValue('C24', 'Equipment Number');
        $sheet->mergeCells('C24:D24');
        $sheet->setCellValue('E24', $firstBatch->productionLine->name ?? '-');
        $sheet->mergeCells('E24:M24');

        // Baris 25: Cetak Nilai Absorbance Real Terpilih
        $sheet->setCellValue('C25', 'Absorbance');
        $sheet->mergeCells('C25:D25');
        $sheet->setCellValue('E25', $abs1);
        $sheet->mergeCells('E25:G25');
        $sheet->setCellValue('H25', $abs2);
        $sheet->mergeCells('H25:J25');
        $sheet->setCellValue('K25', $abs3);
        $sheet->mergeCells('K25:M25');

        // Baris 26: Cetak Nilai Dose (kGy) Sesuai Absorbed Dose Hasil Filter Dinamis
        $sheet->setCellValue('C26', 'Dose (kGy)');
        $sheet->mergeCells('C26:D26');
        $sheet->setCellValue('E26', $dose1);
        $sheet->mergeCells('E26:G26');
        $sheet->setCellValue('H26', $dose2);
        $sheet->mergeCells('H26:J26');
        $sheet->setCellValue('K26', $dose3);
        $sheet->mergeCells('K26:M26');

        // Baris 27: Hasil Analisa Pengujian
        $sheet->setCellValue('A27', 'Analyze');
        $sheet->mergeCells('A27:B27');
        $sheet->setCellValue('C27', 'Conforms to process standard specifications.');
        $sheet->mergeCells('C27:M27');

        // Baris 28 - 30: Blok Tanda Tangan Pengesahan Dokumen
        $sheet->setCellValue('A28', 'Process engineer: ');
        $sheet->mergeCells('A28:B28');
        $sheet->setCellValue('C28', 'Date: ' . Carbon::now()->format('Y/m/d'));
        $sheet->mergeCells('C28:F28');
        $sheet->setCellValue('G28', 'Organizer:');
        $sheet->mergeCells('G28:H28');
        $sheet->setCellValue('I28', 'Date of Compilation:');
        $sheet->mergeCells('I28:M28');

        $sheet->setCellValue('A29', "Operator's signature:");
        $sheet->mergeCells('A29:C29');
        $sheet->mergeCells('D29:F29');
        $sheet->mergeCells('G29:I29');
        $sheet->setCellValue('J29', 'Date:');
        $sheet->mergeCells('J29:M29');

        $sheet->setCellValue('A30', "Signature of person in charge:");
        $sheet->mergeCells('A30:C30');
        $sheet->mergeCells('D30:F30');
        $sheet->mergeCells('G30:I30');
        $sheet->setCellValue('J30', 'Date:');
        $sheet->mergeCells('J30:M30');

        // Baris 32: Penanda Lembar Kontrol Mutu Dokumen
        $sheet->setCellValue('A32', 'Effective Date: 2026/01/01   Version/Status: A/00');
        $sheet->mergeCells('A32:M32');

        // --- STYLING ATRIBUT DAN FORMAT GRID OUTLINE ---
        $sheet->getParent()->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(10);

        $sheet->getStyle('A2:M30')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A2:M30')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sections = ['A6', 'A18', 'A22'];
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
            'C12',
            'G12',
            'J12',
            'A13',
            'E13',
            'J13',
            'A14',
            'E14',
            'G14',
            'G15',
            'G16',
            'J14',
            'A17',
            'A19',
            'G19',
            'A20',
            'G20',
            'A21',
            'G21',
            'A22',
            'A23',
            'C23',
            'C24',
            'C25',
            'C26',
            'A27'
        ];
        foreach ($leftLabels as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        $centerValues = [
            'C13',
            'D12',
            'H12',
            'K12',
            'C14',
            'E15',
            'H14',
            'H15',
            'H16',
            'J15',
            'L14',
            'L15',
            'L16',
            'C19',
            'J19',
            'C20',
            'J20',
            'C21',
            'J21',
            'C22',
            'J22',
            'E23',
            'E24',
            'H24',
            'K24',
            'E25',
            'H25',
            'K25',
            'E26',
            'H26',
            'K26'
        ];
        foreach ($centerValues as $cell) {
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle('A32')->getFont()->setSize(9)->setItalic(true);
        $sheet->getStyle('A32')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $delegate = $event->sheet->getDelegate();

                $delegate->getColumnDimension('A')->setWidth(15);
                $delegate->getColumnDimension('B')->setWidth(14);
                $delegate->getColumnDimension('C')->setWidth(12);
                $delegate->getColumnDimension('D')->setWidth(12);
                $delegate->getColumnDimension('E')->setWidth(12);
                $delegate->getColumnDimension('F')->setWidth(12);
                $delegate->getColumnDimension('G')->setWidth(13);
                $delegate->getColumnDimension('H')->setWidth(12);
                $delegate->getColumnDimension('I')->setWidth(12);
                $delegate->getColumnDimension('J')->setWidth(13);
                $delegate->getColumnDimension('K')->setWidth(12);
                $delegate->getColumnDimension('L')->setWidth(12);
                $delegate->getColumnDimension('M')->setWidth(14);

                // Set Skala Tinggi Baris Form
                for ($i = 2; $i <= 5; $i++) {
                    $delegate->getRowDimension($i)->setRowHeight(15);
                }
                $delegate->getRowDimension(6)->setRowHeight(24);
                $delegate->getRowDimension(7)->setRowHeight(20);
                $delegate->getRowDimension(8)->setRowHeight(20);
                $delegate->getRowDimension(9)->setRowHeight(20);
                $delegate->getRowDimension(10)->setRowHeight(20);
                $delegate->getRowDimension(11)->setRowHeight(22);
                $delegate->getRowDimension(12)->setRowHeight(22);

                $delegate->getRowDimension(13)->setRowHeight(20);
                $delegate->getRowDimension(14)->setRowHeight(20);
                $delegate->getRowDimension(15)->setRowHeight(20);
                $delegate->getRowDimension(16)->setRowHeight(20);

                $delegate->getRowDimension(17)->setRowHeight(24);
                $delegate->getRowDimension(18)->setRowHeight(24);

                $delegate->getRowDimension(19)->setRowHeight(22);
                $delegate->getRowDimension(20)->setRowHeight(22);
                $delegate->getRowDimension(21)->setRowHeight(22);
                $delegate->getRowDimension(22)->setRowHeight(24);

                for ($r = 23; $r <= 27; $r++) {
                    $delegate->getRowDimension($r)->setRowHeight(20);
                }

                $delegate->getRowDimension(28)->setRowHeight(24);
                $delegate->getRowDimension(29)->setRowHeight(35);
                $delegate->getRowDimension(30)->setRowHeight(35);
                $delegate->getRowDimension(32)->setRowHeight(20);
            },
        ];
    }
}
