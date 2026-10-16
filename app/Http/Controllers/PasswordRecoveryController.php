<?php
namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordRecoveryController extends Controller
{
    private function broker(string $portal): string { return $portal === 'admin' ? 'admins' : 'users'; }
    private function verifyPortal(string $portal): void { abort_unless(in_array($portal, ['admin','customer'], true), 404); }

    public function requestForm(string $portal)
    {
        $this->verifyPortal($portal);
        return view('auth.recovery.request', compact('portal'));
    }
    public function sendLink(Request $request, string $portal)
    {
        $this->verifyPortal($portal);
        $data = $request->validate(['email' => ['required','email','max:255']]);
        // Generic feedback does not reveal whether an email address exists.
        $user = ($portal === 'admin' ? Admin::query() : User::query()->where('role','customer'))
            ->where('email', $data['email'])->first();
        if ($user) {
            Password::broker($this->broker($portal))->sendResetLink($data);
        }
        return back()->with('status', 'If the address is registered, a password recovery email will be sent.');
    }
    public function resetForm(Request $request, string $token, string $portal)
    {
        $this->verifyPortal($portal);
        return view('auth.recovery.reset', ['portal'=>$portal, 'token'=>$token, 'email'=>$request->query('email','')]);
    }
    public function reset(Request $request, string $portal)
    {
        $this->verifyPortal($portal);
        $data = $request->validate([
            'token'=>['required','string'], 'email'=>['required','email'],
            'password'=>['required','confirmed', PasswordRule::min(12)],
        ]);
        $status = Password::broker($this->broker($portal))->reset($data, function ($user, $password) use ($portal) {
            if ($portal === 'customer' && $user->role !== 'customer') { return; }
            $user->forceFill(['password'=>Hash::make($password), 'remember_token'=>Str::random(60)])->save();
        });
        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email'=>__($status)])->onlyInput('email');
        }
        return redirect()->route($portal.'.login')->with('status', 'Your password has been updated. You can now sign in.');
    }
}
