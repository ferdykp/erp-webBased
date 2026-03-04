<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerProfileController extends Controller
{
    public function showCompleteProfile()
    {
        return view('customer.profile.complete');
    }

    public function completeProfile(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string',
            'pic_name' => 'required|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string'
        ]);

        $customer = Auth::guard('customer')->user();

        $customer->update([
            'company_name' => $request->company_name,
            'pic_name' => $request->pic_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'profile_completed' => true
        ]);

        return redirect()->route('customer.dashboard');
    }
}
