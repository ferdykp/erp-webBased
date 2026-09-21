<?php

namespace App\Http\Controllers;

use App\Exports\Jts\DeliveryInboundExport;
use App\Exports\Jts\DeliveryOutboundExport;
use App\Exports\Jts\IrradiatedExport;
use App\Exports\Jts\UnirradiatedExport;
use App\Exports\Nuctech\DailyWorkExport;
use App\Exports\Nuctech\EquipmentExport;
use App\Exports\Nuctech\NucDeliveryExport;
use App\Exports\Nuctech\ProcessingRecordExport;
use App\Exports\Nuctech\ScheduleExport;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private const NUCTECH_REPORTS = [
        'daily-work' => [
            'title' => 'Workshop Team Daily Work Record Form',
            'short' => 'Workshop Daily Work',
            'export' => 'nuc_daily_work',
            'description' => 'Daily workshop activity and operational work record.',
        ],
        'daily-schedule' => [
            'title' => 'Daily Processing Schedule',
            'short' => 'Daily Processing Schedule',
            'export' => 'nuc_daily_schedule',
            'description' => 'Daily irradiation processing plan and schedule.',
        ],
        'delivery-form' => [
            'title' => 'Irradiated Product Processing and Delivery Form',
            'short' => 'Processing & Delivery',
            'export' => 'nuc_delivery_form',
            'description' => 'Processing and delivery documentation for irradiated products.',
        ],
        'processing-record' => [
            'title' => 'Irradiation Processing Record Form',
            'short' => 'Irradiation Process Log',
            'export' => 'nuc_processing_record',
            'description' => 'Detailed irradiation process and parameter record.',
        ],
        'equipment-record' => [
            'title' => 'Equipment Operation Record',
            'short' => 'Machine Operation Log',
            'export' => 'nuc_equipment_record',
            'description' => 'Operational record for irradiation equipment.',
        ],
    ];

    private const JTS_REPORTS = [
        'unirradiated-card' => [
            'title' => 'Unirradiated Material Identification Card',
            'short' => 'Unirradiated Material Card',
            'export' => 'jts_unirradiated_card',
            'description' => 'Material identification before irradiation.',
        ],
        'delivery-outbound' => [
            'title' => 'Product Delivery Slip Outbound',
            'short' => 'Outbound Delivery Slip',
            'export' => 'jts_delivery_outbound',
            'description' => 'Outbound logistics and product handover record.',
        ],
        'delivery-inbound' => [
            'title' => 'Product Delivery Slip Inbound',
            'short' => 'Inbound Delivery Slip',
            'export' => 'jts_delivery_inbound',
            'description' => 'Inbound logistics and receiving record.',
        ],
        'irradiated-card' => [
            'title' => 'Irradiated Material Identification Card',
            'short' => 'Irradiated Material Card',
            'export' => 'jts_irradiated_card',
            'description' => 'Material identification after irradiation.',
        ],
    ];

    public function index(Request $request)
    {
        $reports = $this->regularBookings()->paginate(10);

        return view('admin.report.index', [
            'reports' => $reports,
            'totalBookings' => Booking::where('booking_type', 'regular')->count(),
            'completedBookings' => Booking::where('booking_type', 'regular')->where('status', 'completed')->count(),
            'totalCustomers' => Customer::count(),
            'nuctechReports' => self::NUCTECH_REPORTS,
            'jtsReports' => self::JTS_REPORTS,
        ]);
    }

    public function jtsView(string $type)
    {
        $definition = self::JTS_REPORTS[$type] ?? null;
        abort_unless($definition, 404, 'Jenis laporan JTS tidak ditemukan.');
        $this->authorizeReportFamily('jts');

        return view('admin.report.index', [
            'reports' => $this->regularBookings()->paginate(10),
            'pageTitle' => $definition['title'],
            'activeType' => $type,
            'activeExportType' => $definition['export'],
            'category' => 'jts',
            'nuctechReports' => self::NUCTECH_REPORTS,
            'jtsReports' => self::JTS_REPORTS,
        ]);
    }

    public function nuctechView(string $type)
    {
        $definition = self::NUCTECH_REPORTS[$type] ?? null;
        abort_unless($definition, 404, 'Jenis laporan Nuctech tidak ditemukan.');
        $this->authorizeReportFamily('nuctech');

        return view('admin.report.index', [
            'reports' => $this->regularBookings()->paginate(10),
            'pageTitle' => $definition['title'],
            'activeType' => $type,
            'activeExportType' => $definition['export'],
            'category' => 'nuctech',
            'nuctechReports' => self::NUCTECH_REPORTS,
            'jtsReports' => self::JTS_REPORTS,
        ]);
    }

    public function exportExcel(int $id, string $type)
    {
        $family = $this->reportFamilyFromExportType($type);
        abort_unless($family, 404, 'Format laporan tidak terdaftar.');
        $this->authorizeReportFamily($family);

        $report = Booking::with(['products', 'customer.contacts'])
            ->where('booking_type', 'regular')
            ->findOrFail($id);

        $createdAt = $report->created_at?->format('Y-m-d') ?? 'NoDate';
        $productName = $report->products->first()?->product_name ?? 'NoProduct';
        $companyName = $report->customer?->company_name ?? 'NoCompany';
        $contactName = $report->customer?->contacts->first()?->name ?? 'NoContact';

        $clean = static function (string $value): string {
            return str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $value);
        };

        $details = "[{$createdAt}] {$clean($productName)}_{$clean($companyName)}_{$clean($contactName)}";
        $details = preg_replace('/\s+/', ' ', $details) ?: $details;
        $details = Str::limit($details, 120, '');

        return match ($type) {
            'jts_unirradiated_card' => Excel::download(new UnirradiatedExport($id), "JTS Unirradiated {$details} {$report->booking_code}.xlsx"),
            'jts_delivery_outbound' => Excel::download(new DeliveryOutboundExport($id), "JTS Outbound {$details} {$report->booking_code}.xlsx"),
            'jts_delivery_inbound' => Excel::download(new DeliveryInboundExport($id), "JTS Inbound {$details} {$report->booking_code}.xlsx"),
            'jts_irradiated_card' => Excel::download(new IrradiatedExport($id), "JTS Irradiated {$details} {$report->booking_code}.xlsx"),
            'nuc_daily_work' => Excel::download(new DailyWorkExport($id), "Nuc Daily Work {$details} {$report->booking_code}.xlsx"),
            'nuc_processing_record' => Excel::download(new ProcessingRecordExport($id), "Nuc Processing {$details} {$report->booking_code}.xlsx"),
            'nuc_delivery_form' => Excel::download(new NucDeliveryExport($id), "Nuc Delivery {$details} {$report->booking_code}.xlsx"),
            'nuc_daily_schedule' => Excel::download(new ScheduleExport($id), "Nuc Schedule {$details} {$report->booking_code}.xlsx"),
            'nuc_equipment_record' => Excel::download(new EquipmentExport($id), "Nuc Equipment {$details} {$report->booking_code}.xlsx"),
        };
    }

    private function regularBookings()
    {
        return Booking::with(['customer.contacts', 'products'])
            ->where('booking_type', 'regular')
            ->latest();
    }

    private function reportFamilyFromExportType(string $type): ?string
    {
        if (Str::startsWith($type, 'nuc_')) {
            return collect(self::NUCTECH_REPORTS)->contains(fn ($report) => $report['export'] === $type)
                ? 'nuctech'
                : null;
        }

        if (Str::startsWith($type, 'jts_')) {
            return collect(self::JTS_REPORTS)->contains(fn ($report) => $report['export'] === $type)
                ? 'jts'
                : null;
        }

        return null;
    }

    private function authorizeReportFamily(string $family): void
    {
        $role = auth('admin')->user()?->role;

        $allowed = match ($family) {
            'nuctech' => ['superadmin', 'manager', 'production'],
            'jts' => ['superadmin', 'manager', 'cargo_admin'],
            default => [],
        };

        abort_unless(in_array($role, $allowed, true), 403, 'Anda tidak memiliki akses ke jenis laporan ini.');
    }
}
