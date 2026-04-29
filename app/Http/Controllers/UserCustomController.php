<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
// use App\Model\User;

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

    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'username' => 'required|string|max:255',
    //         'email' => 'required|email|unique:customers,email',
    //         'password' => 'nullable|min:6',

    //     ]);

    //     User::create([
    //         'username' => $request->username,
    //         'email' => $request->email,
    //         'password'          => Hash::make($request->password ?? 'password123'),
    //     ]);
    // }

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
        // Ambil user yang sedang login menggunakan auth standar
        $user = auth()->user();

        // Pastikan user tidak null sebelum mengambil ID
        if (!$user) {
            return redirect()->route('customer.login');
        }

        $history = \App\Models\Booking::where('customer_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->with(['products'])
            ->latest()
            ->paginate(10);

        return view('customer.booking.history', compact('history', 'user'));
    }
}
