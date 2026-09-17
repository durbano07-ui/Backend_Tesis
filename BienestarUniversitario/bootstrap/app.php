<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ExpireInactiveTokens;
use App\Http\Middleware\ForcePasswordChange;
use App\Http\Middleware\LogAudit;
use App\Http\Middleware\RateLimitByIP;
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
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            RateLimitByIP::class,
            ExpireInactiveTokens::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();