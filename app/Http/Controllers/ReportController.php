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

    // Placeholder untuk fungsi export yang sebelumnya kita bahas
    // public function exportExcel($id) // Tambahkan parameter $id
    // {
    //     $booking = Booking::findOrFail($id);
    //     $fileName = 'Report_' . $booking->booking_code . '.xlsx';

    //     return Excel::download(new BookingDetailExport($id), $fileName);
    // }

    // public function exportExcel($id, $type)
    // {
    //     $report = Booking::with(['customer', 'products', 'pallets'])->findOrFail($id);

    //     // Seleksi class berdasarkan parameter type dari URL
    //     return match ($type) {
    //         // JTS Groups
    //         'jts_unirradiated_card' => Excel::download(new UnirradiatedExport($report), "JTS_Unirradiated_{$report->booking_code}.xlsx"),
    //         'jts_delivery_outbound' => Excel::download(new DeliveryOutboundExport($report), "JTS_Outbound_{$report->booking_code}.xlsx"),
    //         'jts_delivery_inbound'  => Excel::download(new DeliveryInboundExport($report), "JTS_Inbound_{$report->booking_code}.xlsx"),
    //         'jts_irradiated_card'   => Excel::download(new IrradiatedExport($report), "JTS_Irradiated_{$report->booking_code}.xlsx"),

    //         // Nuctech Groups
    //         'nuc_daily_work'        => Excel::download(new DailyWorkExport($report), "Nuc_Daily_Work_{$report->booking_code}.xlsx"),
    //         'nuc_processing_record' => Excel::download(new ProcessingRecordExport($report), "Nuc_Processing_{$report->booking_code}.xlsx"),
    //         'nuc_delivery_form'     => Excel::download(new NucDeliveryExport($report), "Nuc_Delivery_{$report->booking_code}.xlsx"),
    //         'nuc_daily_schedule'    => Excel::download(new ScheduleExport($report), "Nuc_Schedule_{$report->booking_code}.xlsx"),
    //         'nuc_equipment_record'  => Excel::download(new EquipmentExport($report), "Nuc_Equipment_{$report->booking_code}.xlsx"),

    //         default => abort(404, "Format laporan tidak terdaftar"),
    //     };
    // }
    public function exportExcel($id, $type)
    {
        // Cukup pastikan ID-nya ada di database terlebih dahulu
        $report = Booking::findOrFail($id);

        // Kirim $id (bukan $report) ke dalam class Export masing-masing
        return match ($type) {
            // JTS Groups
            'jts_unirradiated_card' => Excel::download(new UnirradiatedExport($id), "JTS_Unirradiated_{$report->booking_code}.xlsx"),
            'jts_delivery_outbound' => Excel::download(new DeliveryOutboundExport($id), "JTS_Outbound_{$report->booking_code}.xlsx"),
            'jts_delivery_inbound'  => Excel::download(new DeliveryInboundExport($id), "JTS_Inbound_{$report->booking_code}.xlsx"),
            'jts_irradiated_card'   => Excel::download(new IrradiatedExport($id), "JTS_Irradiated_{$report->booking_code}.xlsx"),

            // Nuctech Groups
            'nuc_daily_work'        => Excel::download(new DailyWorkExport($id), "Nuc_Daily_Work_{$report->booking_code}.xlsx"),
            'nuc_processing_record' => Excel::download(new ProcessingRecordExport($id), "Nuc_Processing_{$report->booking_code}.xlsx"),
            'nuc_delivery_form'     => Excel::download(new NucDeliveryExport($id), "Nuc_Delivery_{$report->booking_code}.xlsx"),
            'nuc_daily_schedule'    => Excel::download(new ScheduleExport($id), "Nuc_Schedule_{$report->booking_code}.xlsx"),
            'nuc_equipment_record'  => Excel::download(new EquipmentExport($id), "Nuc_Equipment_{$report->booking_code}.xlsx"),

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
