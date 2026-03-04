<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;


class UserAdminController extends Controller
{
    public function index()
    {
        $user = Auth::guard('admin')->user();

        return view('admin.profile.index', compact('user'));
    }

    public function profileList()
    {
        $admins = Admin::latest()->get();

        return view('admin.profile.profileList', compact('admins'));
    }

    public function create()
    {
        return view('admin.profile.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'password' => 'required|min:8|confirmed',
            'role' => 'nullable|string'
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'admin',
        ]);

        return redirect()->route('admin.profile')->with('success', 'Add Admin Success');
    }

    public function edit()
    {
        $user = Auth::guard('admin')->user();
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::guard('admin')->user();

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email,' . $user->id],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // return back()->with('success', 'Changed');
        return redirect()->route('admin.profile')->with('success', 'Profile Change');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::guard('admin')->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }

    public function destroy()
    {
        $admins = Admin::findOrFail();

        $admins = delete();

        return redirect()->route('admin.profile');
    }
}
