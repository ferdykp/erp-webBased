<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        // Mulai query dari relasi bookings milik customer
        $query = $customer->bookings()->with(['products']);

        // Jika ada input search
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('booking_code', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('products', function ($pq) use ($searchTerm) {
                        $pq->where('product_name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        $data = $query->latest()->paginate(10);

        // KUNCI: Jika request berasal dari AJAX (pencarian), kembalikan HANYA view tabel
        if ($request->ajax()) {
            return view('customer.dashboard.table', compact('data'))->render();
        }

        // Tampilan normal saat pertama kali load
        return view('customer.dashboard.index', compact('data'));
    }
}
