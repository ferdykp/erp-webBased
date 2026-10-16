<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['sometimes', 'boolean'],
        ]);

        if (!Auth::guard('admin')->attempt(collect($credentials)->except('remember')->all(), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Unable to sign in with these credentials.',
            ])->onlyInput('email');
        }

        // Keep staff and customer identities mutually exclusive in one browser.
        Auth::guard('customer')->logout();
        Auth::guard('web')->logout();
        $request->session()->forget('url.intended');
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
