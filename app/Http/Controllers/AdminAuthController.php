<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route('admin.dashboard');
        }
        // Contoh di AdminLoginController
        // if (!Auth::guard('admin')->attempt($credentials)) {
        //     return back()->withErrors([
        //         'email' => 'Credentials do not match our secure records.',
        //     ])->withInput($request->only('email'));
        // }

        return back()->withErrors([
            'email' => 'Email or password is incorrect.',
        ])->withInput($request->only('email'));
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
