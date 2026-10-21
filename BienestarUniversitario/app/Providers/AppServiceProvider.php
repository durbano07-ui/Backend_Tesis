<?php

namespace App\Providers;

use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        User::observe(UserObserver::class);

        // Política de contraseñas (OWASP ASVS V2.1):
        //  2.1.1 mínimo 12 caracteres · 2.1.2 se permiten hasta 128 · 2.1.9 sin reglas de composición
        //  2.1.7 se rechazan contraseñas filtradas (Have I Been Pwned, k-anonimato: solo se envía
        //        un prefijo del hash SHA-1). En pruebas automáticas se omite para no depender de la red.
        Password::defaults(function () {
            $rule = Password::min(12)->max(128);

            return $this->app->environment('testing') ? $rule : $rule->uncompromised();
        });
    }
}
