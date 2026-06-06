<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
// use App\Exports\BookingDetailExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Jts\UnirradiatedExport;
use App\Exports\Jts\DeliveryOutboundExport;
use App\Exports\Jts\DeliveryInboundExport;
use App\Exports\Jts\IrradiatedExport;
use Illuminate\Support\Str;
use App\Exports\Nuctech\DailyWorkExport;
use App\Exports\Nuctech\ProcessingRecordExport;
use App\Exports\Nuctech\NucDeliveryExport;
use App\Exports\Nuctech\ScheduleExport;
use App\Exports\Nuctech\EquipmentExport;


class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data bookings untuk tabel (Eager Loading agar cepat)
        $reports = Booking::with(['customer.contacts', 'products'])
            ->latest()
            ->paginate(10); // Ini yang dicari oleh Blade

        // 2. Data untuk statistik (Cards)
        $totalBookings = Booking::count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $totalCustomers = Customer::count();

        // 3. Kirim SEMUA variabel ke view
        return view('admin.report.index', compact(
            'reports',
            'totalBookings',
            'completedBookings',
            'totalCustomers'
        ));
    }

    public function exportExcel($id, $type)
    {
        // Load booking beserta relasi product, customer, dan contact-nya
        $report = Booking::with(['products', 'customer.contacts'])->findOrFail($id);

        // 1. Ambil tanggal dengan format rapi (misal: 2026-04-11)
        $createdAt = $report->created_at ? $report->created_at->format('Y-m-d') : 'NoDate';

        // 2. Ambil nama produk pertama
        $productName = $report->products->first()?->product_name ?? 'NoProduct';

        // 3. Ambil nama perusahaan
        $companyName = $report->customer?->company_name ?? 'NoCompany';

        // 4. Ambil nama kontak
        $contactName = $report->customer?->contacts->first()?->name ?? 'NoContact';

        //--- PROSES MERAPIKAN TEKS ---
        // Membersihkan karakter ilegal untuk nama file windows/linux (\ / : * ? " < > |)
        $cleanProduct = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $productName);
        $cleanCompany = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $companyName);
        $cleanContact = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $contactName);

        // Gabungkan dengan pembatas yang jelas (misal: Tanggal dikurung, antar data dipisah dengan underscore)
        $details = "[{$createdAt}] {$cleanProduct}_{$cleanCompany}_{$cleanContact}";

        // Bersihkan spasi ganda jika ada
        $details = preg_replace('/\s+/', ' ', $details);

        // Potong string jika terlalu panjang (maksimal 120 karakter)
        $details = Str::limit($details, 120, '');

        return match ($type) {
            // JTS Groups
            'jts_unirradiated_card' => Excel::download(new UnirradiatedExport($id), "JTS Unirradiated {$details} {$report->booking_code}.xlsx"),
            'jts_delivery_outbound' => Excel::download(new DeliveryOutboundExport($id), "JTS Outbound {$details} {$report->booking_code}.xlsx"),
            'jts_delivery_inbound'  => Excel::download(new DeliveryInboundExport($id), "JTS Inbound {$details} {$report->booking_code}.xlsx"),
            'jts_irradiated_card'   => Excel::download(new IrradiatedExport($id), "JTS Irradiated {$details} {$report->booking_code}.xlsx"),

            // Nuctech Groups
            'nuc_daily_work'        => Excel::download(new DailyWorkExport($id), "Nuc Daily Work {$details} {$report->booking_code}.xlsx"),
            'nuc_processing_record' => Excel::download(new ProcessingRecordExport($id), "Nuc Processing {$details} {$report->booking_code}.xlsx"),
            'nuc_delivery_form'     => Excel::download(new NucDeliveryExport($id), "Nuc Delivery {$details} {$report->booking_code}.xlsx"),
            'nuc_daily_schedule'    => Excel::download(new ScheduleExport($id), "Nuc Schedule {$details} {$report->booking_code}.xlsx"),
            'nuc_equipment_record'  => Excel::download(new EquipmentExport($id), "Nuc Equipment {$details} {$report->booking_code}.xlsx"),

            default => abort(404, "Format laporan tidak terdaftar"),
        };
    }
    public function jtsView($type)
    {
        $reports = Booking::with(['customer', 'products'])->latest()->paginate(10);

        // Mapping slug ke Judul Asli
        $titles = [
            'unirradiated-card' => 'Unirradiated Material Identification Card',
            'delivery-outbound' => 'Product Delivery Slip Outbound',
            'delivery-inbound'  => 'Product Delivery Slip Inbound',
            'irradiated-card'   => 'Irradiated Material Identification Card',
        ];

        return view('admin.report.index', [
            'reports' => $reports,
            'pageTitle' => $titles[$type] ?? 'Master Reporting',
            'activeType' => $type,
            'category' => 'jts'
        ]);
    }

    public function nuctechView($type)
    {
        $reports = Booking::with(['customer', 'products'])->latest()->paginate(10);

        $titles = [
            'daily-work'        => 'Workshop Team Daily Work Record Form',
            'processing-record' => 'Irradiation Processing Record Form',
            'delivery-form'     => 'Irradiated Product Processing and Delivery Form',
            'daily-schedule'    => 'Daily Processing Schedule',
            'equipment-record'  => 'Equipment Operation Record',
        ];

        return view('admin.report.index', [
            'reports' => $reports,
            'pageTitle' => $titles[$type] ?? 'Master Reporting',
            'activeType' => $type,
            'category' => 'nuctech'
        ]);
    }
}
