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
            'email' => 'required|email|max:255|unique:admins,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:superadmin,manager,production,cargo_admin'
        ]);

        Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'admin',
        ]);

        return redirect()->route('admin.profile')->with('success', 'Add Admin Success');
    }

    public function edit(int $id)
    {
        $user = Admin::findOrFail($id);
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $user = Admin::findOrFail($id);
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
            'current_password' => ['required', 'current_password:admin'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::guard('admin')->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated.');
    }

    public function destroy(int $id)
    {
        $admin = Admin::findOrFail($id);
        if ((int) $admin->id === (int) Auth::guard('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        if ($admin->role === 'superadmin' && Admin::where('role', 'superadmin')->count() <= 1) {
            return back()->with('error', 'The last superadmin account cannot be deleted.');
        }
        foreach (['operational_audit_events' => 'admin_id', 'irradiation_sessions' => 'recorded_by', 'order_dispatches' => 'dispatched_by', 'quality_reviews' => 'reviewed_by'] as $table => $column) {
            if (\Illuminate\Support\Facades\DB::table($table)->where($column, $id)->exists()) {
                return back()->with('error', 'This staff account is referenced by operational history and cannot be deleted.');
            }
        }
        $admin->delete();

        return redirect()->route('admin.profile.profileList')->with('success', 'Staff account deleted.');
    }
}
