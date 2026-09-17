<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAudit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Only log mutations (POST, PUT, PATCH, DELETE)
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return;
        }

        // Skip logging for certain endpoints
        if ($request->is('api/v1/auth/*')) {
            return;
        }

        $user = $request->user();

        // Log mutation if user is authenticated and model was affected
        // This is a lightweight approach - full model audit is handled by observers
        if ($user && $response->isSuccessful()) {
            // The actual model audit is handled by Eloquent observers
            // This middleware is here for additional custom audit logging if needed
        }
    }
}
