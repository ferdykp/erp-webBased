<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.login');
    }

    public function showRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.dashboard');
        }

        return view('customer.auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Authenticate ONLY against the customer guard. Never use the default
        // `web` guard here because admin and customer sessions must not mix.
        $customerCredentials = array_merge($credentials, ['role' => 'customer']);

        if (!Auth::guard('customer')->attempt($customerCredentials)) {
            return back()
                ->withErrors(['email' => 'Email atau Password salah.'])
                ->onlyInput('email');
        }

        $user = Auth::guard('customer')->user();

        // The customer guard uses the users provider, therefore enforce the role
        // after authentication as an additional boundary.
        if (!$user || $user->role !== 'customer') {
            Auth::guard('customer')->logout();
            $request->session()->forget('url.intended');
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Ini bukan akun Customer.'])
                ->onlyInput('email');
        }

        // One browser session represents one application persona at a time.
        // If the same browser was previously logged in as an admin (or via the
        // legacy web guard), terminate those guard sessions before continuing.
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();

        // Never reuse an intended URL created by another guard. Without this,
        // a previous /admin request can become the post-login destination.
        $request->session()->forget('url.intended');
        $request->session()->regenerate();

        if (!$user->customer || !$user->customer->profile_completed) {
            return redirect()->route('customer.profile.complete');
        }

        // Deliberately use a fixed customer destination after login.
        return redirect()->route('customer.dashboard');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
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
                'status' => 'active',
            ]);

            DB::commit();

            return redirect()->route('customer.login')->with('success', 'Registrasi Berhasil!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors(['error' => 'Gagal: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        // Clean the old default guard as well so an installation upgraded from
        // the previous implementation cannot retain a stale customer identity.
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
