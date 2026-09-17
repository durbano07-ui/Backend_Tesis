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
     * Invalidates the token if there's been no activity for the configured time.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $request->user()?->currentAccessToken()) {
            $token = $request->user()->currentAccessToken();

            // Check if token has last_activity_at and if it's expired
            if ($token->last_activity_at) {
                $inactiveMinutes = (int) env('TOKEN_INACTIVITY_MINUTES', 60);
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
            $token->update(['last_activity_at' => now()]);
        }

        return $next($request);
    }
}