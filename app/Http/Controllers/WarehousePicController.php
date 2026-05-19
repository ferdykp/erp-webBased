<?php

namespace App\Http\Controllers;

use App\Models\WarehousePic;
use Illuminate\Http\Request;

class WarehousePicController extends Controller
{
    public function index()
    {
        $pics = WarehousePic::latest()->paginate(10);
        return view('admin.warehouse-pic.index', compact('pics'));
    }

    public function create()
    {
        return view('admin.warehouse-pic.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:warehouse_pics,email',
            'phone' => 'nullable|string|max:20',
            'shift' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        WarehousePic::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'shift' => $request->shift,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.warehouse-pics.index')->with('success', 'PIC Warehouse berhasil ditambahkan.');
    }

    public function edit(WarehousePic $warehousePic)
    {
        return view('admin.warehouse-pic.edit', compact('warehousePic'));
    }

    public function update(Request $request, WarehousePic $warehousePic)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:warehouse_pics,email,' . $warehousePic->id,
            'phone' => 'nullable|string|max:20',
            'shift' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        $warehousePic->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'shift' => $request->shift,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.warehouse-pics.index')->with('success', 'Data PIC Warehouse berhasil diperbarui.');
    }

    public function destroy(WarehousePic $warehousePic)
    {
        $warehousePic->delete();
        return redirect()->route('admin.warehouse-pics.index')->with('success', 'PIC Warehouse berhasil dihapus.');
    }
}
