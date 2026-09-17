<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password) {
            // Allow password change endpoint to bypass this check
            if ($request->is('api/v1/auth/password') && $request->method() === 'PUT') {
                return $next($request);
            }

            return response()->json([
                'message' => 'Password change required',
            ], 403);
        }

        return $next($request);
    }
}
