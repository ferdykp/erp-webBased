<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses berdasarkan role admin.
 *
 * Penggunaan di route:
 *   ->middleware('role:superadmin|manager|cargo_admin') ATAU ->middleware('role:superadmin,manager')
 *
 * Middleware ini mengecek kolom `role` pada tabel `admins`.
 */
class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Daftar role yang diizinkan
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $admin = auth('admin')->user();

        // Jika belum login sebagai admin, redirect ke halaman login
        if (!$admin) {
            return redirect()->route('admin.login');
        }

        // Normalisasi parameter jika route menggunakan pemisah pipa '|' (contoh: role:superadmin|manager)
        // diubah menjadi array bersih: ['superadmin', 'manager']
        $allowedRoles = [];
        foreach ($roles as $role) {
            $allowedRoles = array_merge($allowedRoles, explode('|', $role));
        }

        // Cek apakah role admin termasuk dalam daftar role yang diizinkan
        if (!in_array($admin->role, $allowedRoles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
