<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;


class CustomerProfileController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $customers = Customer::when($search, function ($query) use ($search) {
            $query->where('company_name', 'like', "%{$search}%")
                ->orWhere('pic_name', 'like', "%{$search}%");
        })->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.customerList.index', compact('customers', 'search'));
    }
    public function showCompleteProfile()
    {
        return view('customer.profile.complete');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'password' => 'nullable|min:6',
            'company_name' => 'required|string|max:255',
            'pic_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
        ]);

        Customer::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'password'          => Hash::make($request->password ?? 'password123'),
            'company_name'      => $request->company_name,
            'pic_name'          => $request->pic_name,
            'phone'             => $request->phone,
            'address'           => $request->address,
            'profile_completed' => false,
            'status'            => 'active'
        ]);

        return redirect()->route('admin.customerList')
            ->with('success', 'Customer berhasil dibuat');
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
