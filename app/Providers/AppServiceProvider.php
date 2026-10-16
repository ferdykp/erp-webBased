<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\Admin;
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
        ResetPassword::createUrlUsing(function ($user, string $token) {
            if ($user instanceof Admin) {
                return route('admin.password.reset', ['token' => $token, 'email' => $user->email]);
            }
            return route('customer.password.reset', ['token' => $token, 'email' => $user->email]);
        });

        // Jika environment BUKAN local (artinya production/staging), paksa URL menggunakan HTTPS
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}
