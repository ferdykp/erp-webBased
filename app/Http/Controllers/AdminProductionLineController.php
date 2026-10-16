<?php

namespace App\Http\Controllers;

use App\Models\ProductionLine;
use Illuminate\Http\Request;

/**
 * ==========================================================================
 * AdminProductionLineController
 * ==========================================================================
 *
 * Controller untuk mengelola Master Data Mesin Penyinaran (Production Lines).
 * Menyediakan operasi CRUD (Create, Read, Update, Delete) sederhana.
 *
 * Route prefix : /admin/production-lines
 * Middleware   : auth:admin, role:technologist,production_engineer,admin
 * ==========================================================================
 */
class AdminProductionLineController extends Controller
{
    /**
     * ======================================================================
     * INDEX – Menampilkan daftar semua mesin penyinaran.
     * ======================================================================
     *
     * Method : GET
     * Route  : /admin/production-lines
     * Name   : admin.production-lines.index
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $productionLines = ProductionLine::latest()->get();

        return view('admin.production-lines.index', compact('productionLines'));
    }

    /**
     * ======================================================================
     * STORE – Menyimpan mesin penyinaran baru ke database.
     * ======================================================================
     *
     * Method : POST
     * Route  : /admin/production-lines
     * Name   : admin.production-lines.store
     *
     * Validasi:
     * - name: wajib diisi, string, maksimal 255 karakter, harus unik di tabel production_lines
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:production_lines,name',
        ]);

        ProductionLine::create([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Production unit added.');
    }

    /**
     * ======================================================================
     * UPDATE – Memperbarui nama mesin penyinaran yang sudah ada.
     * ======================================================================
     *
     * Method : PUT/PATCH
     * Route  : /admin/production-lines/{production_line}
     * Name   : admin.production-lines.update
     *
     * Validasi:
     * - name: wajib diisi, string, maksimal 255 karakter, unik kecuali untuk record ini sendiri
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductionLine  $productionLine
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, ProductionLine $productionLine)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:production_lines,name,' . $productionLine->id,
        ]);

        $productionLine->update([
            'name' => $request->name,
        ]);

        return back()->with('success', 'Production unit updated.');
    }

    /**
     * ======================================================================
     * DESTROY – Menghapus mesin penyinaran dari database.
     * ======================================================================
     *
     * Method : DELETE
     * Route  : /admin/production-lines/{production_line}
     * Name   : admin.production-lines.destroy
     *
     * Catatan:
     * - Jika mesin sedang digunakan oleh batch yang aktif (status != 'done'),
     *   penghapusan akan ditolak.
     * - Jika mesin berhasil dihapus, kolom production_line_id di booking_batches
     *   akan otomatis di-set NULL (karena nullOnDelete di migration).
     *
     * @param  \App\Models\ProductionLine  $productionLine
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ProductionLine $productionLine)
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($productionLine) {
            $line = ProductionLine::whereKey($productionLine->id)->lockForUpdate()->firstOrFail();
            if ($line->batches()->exists()
                || \App\Models\ProductTest::where('production_line_id', $line->id)->exists()
                || \Illuminate\Support\Facades\DB::table('production_parameter_templates')->where('production_line_id', $line->id)->exists()
                || \Illuminate\Support\Facades\DB::table('irradiation_sessions')->where('production_line_id', $line->id)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['machine' => 'This production unit is referenced by operational records and cannot be deleted.']);
            }
            $line->delete();
        }, 3);

        return back()->with('success', 'Production unit deleted.');
    }
}
