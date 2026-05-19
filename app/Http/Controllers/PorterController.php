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
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.porter.index')->with('success', 'Porter berhasil ditambahkan.');
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
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.porter.index')->with('success', 'Porter berhasil diperbarui.');
    }

    public function destroy(Porter $porter)
    {
        $porter->delete();
        return redirect()->route('admin.porter.index')->with('success', 'Porter berhasil dihapus.');
    }
}
