<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP A6: Security Misconfiguration
 * Adds security headers to all HTTP responses
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // OWASP A6: Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        $allowedOrigins = $this->allowedOrigins();

        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self' {$allowedOrigins}; " .
            "script-src 'self' 'unsafe-inline' {$allowedOrigins} https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://vercel.live; " .
            "style-src 'self' 'unsafe-inline' {$allowedOrigins} https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
            "img-src 'self' data: blob: {$allowedOrigins} https://www.sti.edu; " .
            "font-src 'self' {$allowedOrigins} https://cdnjs.cloudflare.com https://fonts.googleapis.com https://fonts.gstatic.com; " .
            "connect-src 'self' {$allowedOrigins} https://cdn.jsdelivr.net https://vercel.live; " .
            "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'; upgrade-insecure-requests;"
        );

        // OWASP A6: Additional security headers
        // Strict-Transport-Security (HSTS) - enforce HTTPS
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        return $response;
    }

    private function allowedOrigins(): string
    {
        return collect([
            config('app.url'),
            config('app.asset_url'),
        ])
            ->filter()
            ->unique()
            ->implode(' ');
    }
}
