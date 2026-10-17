<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure the authenticated customer guard really belongs to a customer account.
 *
 * This is intentionally separate from the admin guard so an admin session can
 * never satisfy customer routes (and vice versa).
 */
class EnsureCustomerAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('customer')->user();

        if (!$user || $user->role !== 'customer' || \App\Models\Customer::onlyTrashed()->where('user_id',$user->id)->exists()) {
            Auth::guard('customer')->logout();
            $request->session()->forget('url.intended');

            return redirect()
                ->route('customer.login')
                ->withErrors(['email' => 'Sign in with a customer account to continue.']);
        }

        return $next($request);
    }
}
