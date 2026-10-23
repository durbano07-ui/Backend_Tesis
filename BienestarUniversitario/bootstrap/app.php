<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ExpireInactiveTokens;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\LogAudit;
use App\Http\Middleware\RateLimitByIP;
use App\Http\Middleware\RestrictPatientAccess;
use App\Http\Middleware\ApiSecurityHeaders;
use App\Http\Middleware\RestrictStaffModuleAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'force_password_change' => ForcePasswordChange::class,
            'log_audit' => LogAudit::class,
            'rate_limit_ip' => RateLimitByIP::class,
            'role' => CheckRole::class,
            'expire_inactive_tokens' => ExpireInactiveTokens::class,
        ]);

        $middleware->use([
            // El backend está detrás del proxy de Dokploy (Traefik): sin esto $request->ip() es la IP
            // del proxy, igual para todos, y los límites por IP se aplicaban a todo el sistema a la vez.
            \Illuminate\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        $middleware->trustProxies(at: '*');

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            RateLimitByIP::class,
            ExpireInactiveTokens::class,
        ]);

        $middleware->api(append: [
            // Cabeceras de seguridad (va primero para cubrir también las respuestas 403)
            ApiSecurityHeaders::class,
            // OWASP ASVS V4.1.5 / V4.2.1: los pacientes solo acceden a sus propios datos
            RestrictPatientAccess::class,
            // OWASP ASVS V4.1.3: cada profesional solo accede a su módulo y al resumen de los demás
            RestrictStaffModuleAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();