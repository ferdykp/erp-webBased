<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCache
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Cek apakah response memiliki method header sebelum memanggilnya
        // BinaryFileResponse (Excel) akan dilewati oleh pengecekan ini
        if (method_exists($response, 'header')) {
            return $response->header('Cache-Control', 'nocache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
        }

        // Jika itu BinaryFileResponse, langsung kembalikan tanpa memodifikasi header cache
        return $response;
    }
}
