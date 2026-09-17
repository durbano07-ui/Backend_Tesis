<?php

namespace App\Http\Middleware;

use App\Models\LoginAttempt;
use App\Models\SecurityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RateLimitByIP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = "rate_limit:{$ip}";
        $threshold = (int) env('RATE_LIMIT_REQUESTS_PER_MINUTE', 500);

        if (!Cache::has($key)) {
            Cache::put($key, 1, 60); // Ventana fija de 60 segundos
            $requests = 1;
        } else {
            $requests = (int) Cache::increment($key);
        }

        // Check if threshold exceeded
        if ($requests > $threshold) {
            // Log the security event
            SecurityLog::logRateLimitExceeded(
                $ip,
                "Rate limit exceeded: {$requests} requests in the last minute. Threshold: {$threshold}"
            );

            return response()->json([
                'message' => 'Too many requests',
                'retry_after' => 60,
            ], 429);
        }

        return $next($request);
    }
}
