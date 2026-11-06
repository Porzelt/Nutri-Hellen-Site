<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cabeçalhos das páginas do paciente (contrato, seção 4): sem indexação, sem cache,
 * sem scripts de terceiros (CSP só com o próprio site) e HTTPS obrigatório.
 *
 * Livewire 3 / Alpine precisam de scripts inline e de 'unsafe-eval'.
 */
class CabecalhosPaciente
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $script = "'self' 'unsafe-inline' 'unsafe-eval'";
        $connect = "'self'";
        if (app()->environment('local')) {
            // Servidor do Vite em desenvolvimento (npm run dev)
            $script .= ' http://localhost:* ws://localhost:*';
            $connect .= ' http://localhost:* ws://localhost:*';
        }

        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "script-src $script",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src $connect",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
        ]));
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
