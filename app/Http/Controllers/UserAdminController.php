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
        $this->authorizeProfile($id);
        $user = Admin::findOrFail($id);
        return view('admin.profile.edit', compact('user'));
    }

    public function update(Request $request, int $id)
    {
        $this->authorizeProfile($id);
        $user = Admin::findOrFail($id);
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins,email,' . $user->id],
        ]);

        $changes = ['name'=>$request->name, 'email'=>$request->email];
        if (\App\Services\SuperadminAccess::isSuperadmin() && $request->has('role')) {
            $role = $request->validate(['role'=>'required|in:superadmin,manager,production,cargo_admin'])['role'];
            if ($role !== $user->role && !\App\Services\SuperadminAccess::confirmedAccountChange()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['role'=>'Confirm the account change and provide a reason before changing a staff role.']);
            }
            $changes['role']=$role;
        }
        if (\App\Services\SuperadminAccess::isSuperadmin() && $request->filled('password')) {
            $password = $request->validate(['password'=>['required','confirmed',Password::defaults()]])['password'];
            if (!\App\Services\SuperadminAccess::confirmedAccountChange()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['password'=>'A password reset requires confirmation and a reason.']);
            }
            $changes['password']=Hash::make($password);
            $changes['remember_token']=\Illuminate\Support\Str::random(60);
        }
        $user->forceFill($changes)->save();
        if ((int) Auth::guard('admin')->id() === $id) Auth::guard('admin')->setUser($user);

        // return back()->with('success', 'Changed');
        return redirect()->route((int) Auth::guard('admin')->id() === $id ? 'admin.profile' : 'admin.profile.profileList')->with('success', 'Profile updated.');
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

    private function authorizeProfile(int $id): void
    {
        $actor = Auth::guard('admin')->user();
        abort_unless($actor && ((int) $actor->id === $id || $actor->role === 'superadmin'), 403);
    }

    public function destroy(int $id)
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($id) {
            $actorId = (int) Auth::guard('admin')->id();
            // Serialize competing deletions, including requests authenticated before another deletion commits.
            $accounts = Admin::where('role', 'superadmin')->orWhereIn('id', [$id, $actorId])->orderBy('id')->lockForUpdate()->get();
            $actor = $accounts->firstWhere('id', $actorId);
            abort_unless($actor && $actor->role === 'superadmin', 403);
            $admin = $accounts->firstWhere('id', $id);
            abort_unless($admin, 404);
            if (\App\Services\SuperadminAccess::overriding()) {
                app(\App\Services\AdminRecordHistory::class)->delete($admin);
                if ($id === $actorId) { Auth::guard('admin')->logout(); return redirect()->route('admin.login'); }
                return redirect()->route('admin.profile.profileList')->with('success','Staff account removed with an audit snapshot.');
            }
            if ($id === $actorId) return back()->with('error', 'You cannot delete your own account.');
            if ($admin->role === 'superadmin' && $accounts->where('role', 'superadmin')->count() <= 1) {
                return back()->with('error', 'The last superadmin account cannot be deleted.');
            }
            $references = [
                ['operational_audit_events', 'admin_id'], ['irradiation_sessions', 'recorded_by'],
                ['irradiation_sessions', 'voided_by'], ['order_dispatches', 'dispatched_by'],
                ['order_dispatches', 'approved_by'], ['quality_reviews', 'reviewed_by'],
                ['production_parameter_templates', 'created_by'], ['production_parameter_templates', 'approved_by'],
                ['order_document_drafts', 'created_by'], ['pallet_movements', 'moved_by'],
            ];
            foreach ($references as [$table, $column]) {
                if (\Illuminate\Support\Facades\DB::table($table)->where($column, $id)->exists()) {
                    return back()->with('error', 'This staff account is referenced by operational history and cannot be deleted.');
                }
            }
            $admin->delete();
            return redirect()->route('admin.profile.profileList')->with('success', 'Staff account moved to Trash.');
        }, 3);
    }
}
