<?php

namespace App\Http\Controllers;

use App\Models\Porter;
use Illuminate\Http\Request;

class PorterController extends Controller
{
    public function index()
    {
        $porters = Porter::latest()->paginate(10);
        return view('admin.porter.index', compact('porters'));
    }

    public function create()
    {
        return view('admin.porter.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:porters,name',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean'
        ]);

        Porter::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.porter.index')->with('success', 'Porter added.');
    }

    public function edit(Porter $porter)
    {
        return view('admin.porter.edit', compact('porter'));
    }

    public function update(Request $request, Porter $porter)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:porters,name,' . $porter->id,
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean'
        ]);

        $porter->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.porter.index')->with('success', 'Porter updated.');
    }

    public function destroy(Porter $porter)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($porter) {
            $porter = Porter::whereKey($porter->id)->lockForUpdate()->firstOrFail();
            if (\App\Models\Booking::where('porter_name', $porter->name)->exists() || \App\Models\BookingBatch::where('porter_name', $porter->name)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['porter' => 'This porter is referenced by operational records. Deactivate the account instead.']);
            }
            $porter->delete();
        });
        return redirect()->route('admin.porter.index')->with('success', 'Porter deleted.');
    }
}
