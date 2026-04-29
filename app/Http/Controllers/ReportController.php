<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\BookingDetailExport; // Pastikan class export yang dibuat di awal sudah di-import
use Maatwebsite\Excel\Facades\Excel;

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
    public function exportExcel($id) // Tambahkan parameter $id
    {
        $booking = Booking::findOrFail($id);
        $fileName = 'Report_' . $booking->booking_code . '.xlsx';

        return Excel::download(new BookingDetailExport($id), $fileName);
    }
}
