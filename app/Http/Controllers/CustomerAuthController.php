<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Validator;

use App\Models\User;


class CustomerAuthController extends Controller
{
    public function showRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.auth.register');
    }

    // public function register(Request $request)
    // {

    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|email|unique:customers,email',
    //         'password' => 'required|min:8|confirmed'
    //     ]);

    //     Customer::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'profile_completed' => false,
    //         'status' => 'active'
    //     ]);

    //     return redirect()->route('customer.login')->with('success', 'Regis Success');
    // }
    public function register(Request $request)
    {

        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:8|confirmed'
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('customer.login')->with('success', 'Regis Success');
    }
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }
        return view('customer.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Login menggunakan guard customer (yang merujuk ke model User)
        if (Auth::guard('customer')->attempt($credentials)) {
            $user = Auth::guard('customer')->user();

            // CEK: Apakah user ini sudah punya data di tabel customers?
            // Kita menggunakan relasi 'customerProfile' yang sudah kita buat di model User
            if (!$user->customerProfile) {
                return redirect()->route('customer.profile.complete');
            }

            return redirect()->route('customer.dashboard');
        }

        return back()->withErrors(['email' => 'Email Not Registered']);
    }
    // public function login(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required',
    //         'password' => 'required|min:6',
    //     ]);

    //     if ($validator->fails()) {
    //         return back()->withErrors($validator)->withInput();
    //     }

    //     if (Auth::guard('customer')->attempt($request->only('email', 'password'))) {

    //         $request->session()->regenerate();

    //         return redirect()->route('customer.dashboard')
    //             ->with('success', 'Login berhasil');
    //     }

    //     return back()
    //         ->withErrors(['email' => 'email atau password salah'])
    //         ->withInput();
    // }

    public function logout()
    {
        Auth::guard('customer')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
