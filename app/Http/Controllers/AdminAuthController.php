<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// PENTING: Import model yang digunakan oleh guard admin kamu (misal: Admin atau User)
use App\Models\Admin;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 1. Check if the email exists in the admin database
        $adminExists = Admin::where('email', $credentials['email'])->exists();

        if (!$adminExists) {
            // Error jika akun belum terdaftar
            return back()->withErrors([
                'email' => 'Account not registered. Please contact the System Admin.',
            ])->withInput($request->only('email'));
        }

        // 2. If email exists, attempt to authenticate (verify password)
        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('admin.dashboard');
        }

        // 3. If attempt fails, it means the password is incorrect
        return back()->withErrors([
            'password' => 'Invalid password. Please try again.',
        ])->withInput($request->only('email'));
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
