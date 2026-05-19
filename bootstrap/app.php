<?php

use App\Http\Middleware\NoCache;
use App\Http\Middleware\CheckAdminRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
            // Seringkali nama routenya hanya 'login', sesuaikan dengan milik Anda
            return route('customer.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
