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

        // Con sesión iniciada el límite es por usuario (token), no por IP: en la universidad todos
        // salen a internet con la misma IP pública (NAT) y la actualización automática de las
        // pantallas haría que el límite de una IP se agotara para todo el campus a la vez.
        $token = $request->bearerToken();
        $key = $token ? 'rate_limit:token:' . hash('sha256', $token) : "rate_limit:{$ip}";
        $threshold = (int) env('RATE_LIMIT_REQUESTS_PER_MINUTE', 500);

        // Tope por IP mucho más alto: frena a quien envíe muchas peticiones con tokens inventados
        $ipKey = "rate_limit_ip:{$ip}";
        $ipThreshold = (int) env('RATE_LIMIT_IP_REQUESTS_PER_MINUTE', 5000);

        $requests = $this->hit($key);
        $ipRequests = $token ? $this->hit($ipKey) : $requests;

        // Check if threshold exceeded
        if ($requests > $threshold || $ipRequests > $ipThreshold) {
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

    /** Suma una petición a la ventana fija de 60 segundos de $key y devuelve el total. */
    private function hit(string $key): int
    {
        if (Cache::add($key, 1, 60)) {
            return 1;
        }

        return (int) Cache::increment($key);
    }
}
