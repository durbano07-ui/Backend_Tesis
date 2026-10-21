<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

class ExpireInactiveTokens
{
    /**
     * Handle an incoming request.
     * Invalidates the token if there's been no activity for the configured time
     * (OWASP ASVS V3.3.2).
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Este middleware corre en el grupo "api", antes que auth:sanctum: hay que pedir
        // explícitamente el guard de Sanctum (el guard por defecto "web" devuelve null aquí).
        $user = Auth::guard('sanctum')->user();
        $token = $user?->currentAccessToken();

        // Solo tokens persistentes (no TransientToken de sesiones de navegador)
        if ($token && method_exists($token, 'update')) {
            if ($token->last_activity_at) {
                $inactiveMinutes = (int) config('sanctum.inactivity', 30);
                $lastActivity = Carbon::parse($token->last_activity_at);

                if ($lastActivity->diffInMinutes(now()) >= $inactiveMinutes) {
                    // Token is inactive, revoke it
                    $token->delete();

                    return response()->json([
                        'message' => 'Sesión expirada por inactividad. Por favor inicie sesión nuevamente.',
                    ], 401);
                }
            }

            // Update last activity time
            $token->forceFill(['last_activity_at' => now()])->save();
        }

        return $next($request);
    }
}
