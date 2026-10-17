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
        \Illuminate\Support\Facades\Gate::before(fn ($user) => $user instanceof Admin && $user->role === 'superadmin' ? true : null);
        $this->app->scoped(\App\Services\AdminRecordHistory::class);
        \Illuminate\Support\Facades\Event::listen('eloquent.created: *', function ($event, $payload) {
            if (request()->attributes->has('superadmin.created') && ($payload[0] ?? null) instanceof \Illuminate\Database\Eloquent\Model) {
                $models = request()->attributes->get('superadmin.created', []);
                $models[] = $payload[0];
                request()->attributes->set('superadmin.created', $models);
            }
        });
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
