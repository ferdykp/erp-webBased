<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Validasi Role
            if ($user->role !== 'customer') {
                Auth::logout();
                return back()->withErrors(['email' => 'Ini bukan akun Customer.']);
            }

            // Cek Profil
            if (!$user->customer || !$user->customer->profile_completed) {
                return redirect()->route('customer.profile.complete');
            }

            return redirect()->intended('customer/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau Password salah.'])->withInput();
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username' => strtolower(str_replace(' ', '', $request->name)) . rand(10, 99),
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'customer',
            ]);

            Customer::create([
                'user_id' => $user->id,
                'company_name' => $request->name,
                'email' => $request->email,
                'profile_completed' => false,
                'status' => 'active'
            ]);

            DB::commit();
            return redirect()->route('customer.login')->with('success', 'Registrasi Berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal: ' . $e->getMessage()]);
        }
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('customer.login');
    }
}
