<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeceras de seguridad para las respuestas de la API (OWASP ASVS V14.4 y V8.2.1).
 * HSTS, X-Content-Type-Options y X-Frame-Options ya los añade el servidor web.
 */
class ApiSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // V14.4.3: la API solo devuelve JSON; no debe cargar ni ser incrustada en nada
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none'");
        // V8.2.1: los datos clínicos no deben quedar en cachés del navegador ni de proxies
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Pragma', 'no-cache');
        // V14.4.6 / buena práctica: no exponer la URL de origen a terceros
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-site');

        return $response;
    }
}
