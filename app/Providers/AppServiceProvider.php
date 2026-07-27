<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

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
        // Regla global: devuelve verdadero si el usuario es administrador u admin_general
        Gate::define('es-administrador', function (User $user) {
            return $user->rol === 'administrador' || $user->rol === 'admin_general';
        });
    }
}