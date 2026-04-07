<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerDashboardController extends Controller
{
    // public function index(Request $request)
    // {
    //     $customer = Auth::guard('customer')->user();

    //     // Mulai query dari relasi bookings milik customer
    //     $query = $customer->bookings()->with(['products']);

    //     // Jika ada input search
    //     if ($request->filled('search')) {
    //         $searchTerm = $request->search;
    //         $query->where(function ($q) use ($searchTerm) {
    //             $q->where('booking_code', 'like', '%' . $searchTerm . '%')
    //                 ->orWhereHas('products', function ($pq) use ($searchTerm) {
    //                     $pq->where('product_name', 'like', '%' . $searchTerm . '%');
    //                 });
    //         });
    //     }

    //     $data = $query->latest()->paginate(10);

    //     // KUNCI: Jika request berasal dari AJAX (pencarian), kembalikan HANYA view tabel
    //     if ($request->ajax()) {
    //         return view('customer.dashboard.table', compact('data'))->render();
    //     }

    //     // Tampilan normal saat pertama kali load
    //     return view('customer.dashboard.index', compact('data'));
    // }
    public function index(Request $request)
    {
        // 1. Ambil User yang sedang login (Guard default)
        $user = Auth::user();

        // 2. Ambil profil customernya
        $customer = $user->customer;

        // 3. Validasi jika profil customer tidak ada
        if (!$customer) {
            return redirect()->route('customer.login')->withErrors(['error' => 'Profil customer tidak ditemukan.']);
        }

        // 4. Ambil bookings lewat relasi di model Customer
        $query = $customer->bookings()->with(['products']);

        // ... sisa kode pencarian (search) tetap sama ...
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

        if ($request->ajax()) {
            return view('customer.dashboard.table', compact('data'))->render();
        }

        return view('customer.dashboard.index', compact('data'));
    }
}
