<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Inyecta headers de seguridad HTTP en todas las respuestas.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevenir ataques de clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevenir MIME-type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Activar protección XSS del navegador
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Controlar qué información de referencia se envía
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Prevenir que la app se use en contextos inseguros
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Solo en producción: forzar HTTPS
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
