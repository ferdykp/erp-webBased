<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- IMPORT INI

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Jika environment BUKAN local (artinya production/staging), paksa URL menggunakan HTTPS
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
