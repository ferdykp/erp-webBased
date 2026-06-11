<?php

use App\Http\Middleware\NoCache;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpKernel\Exception\HttpException; // Tambahkan import ini

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Mendaftarkan alias middleware agar bisa dipanggil di web.php
        $middleware->alias([
            'nocache' => NoCache::class,
            'role' => CheckAdminRole::class,
        ]);

        // Proteksi Redirect jika user mencoba masuk url secure tanpa login
        $middleware->redirectGuestsTo(function ($request) {
            // Jika request mengarah ke area admin, lempar ke login admin
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }

            // Jika mengarah ke area customer/umum, lempar ke login biasa
            return route('customer.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // MENANGANI PAGE EXPIRED (419) AGAR LEBIH PROFESIONAL
        $exceptions->render(function (HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {

                // Jika yang expired adalah halaman admin, kembalikan ke login admin
                if ($request->is('admin') || $request->is('admin/*')) {
                    return redirect()
                        ->route('admin.login')
                        ->with('error', 'Your session has expired due to inactivity. Please log in again.');
                }

                // Jika selain admin (customer/umum), kembalikan ke login customer
                return redirect()
                    ->route('customer.login')
                    ->with('error', 'Your session has expired due to inactivity. Please log in again.');
            }
        });
    })->create();
