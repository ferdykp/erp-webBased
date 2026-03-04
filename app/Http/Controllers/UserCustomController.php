<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserCustomController extends Controller
{
    /**
     * Menampilkan halaman profil customer.
     */
    public function index()
    {
        // Mengambil data user yang sedang login melalui guard customer
        $user = Auth::guard('customer')->user();

        return view('customer.profile.myprofile', compact('user'));
    }

    /**
     * Update data profil (Nama, Email, Telepon).
     */
    public function update(Request $request)
    {
        $user = Auth::guard('customer')->user();

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        // return back()->with('success', 'Profil berhasil diperbarui!');
        return redirect()->route('customer.profile')->with('success', 'Profile Change');
    }

    public function edit()
    {
        $user = Auth::guard('customer')->user();
        return view('customer.profile.profile_edit', compact('user'));
    }

    /**
     * Update password customer.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::guard('customer')->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }

    public function history()
    {
        // Mengambil riwayat booking milik customer yang sedang login
        // Sertakan 'slot' dan 'products' agar tidak terjadi N+1 query (Eager Loading)
        $history = \App\Models\Booking::where('customer_id', auth('customer')->id())
            ->with(['products']) // Sesuaikan nama relasi di model Booking Anda
            ->latest()
            ->paginate(10); // Menggunakan pagination agar rapi

        return view('customer.booking.history', compact('history'));
    }
}
