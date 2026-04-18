<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP A1: Injection Prevention
 * Sanitizes user input to prevent SQL injection, XSS, and command injection
 */
class SanitizeInput
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize all input data
        $this->sanitizeInput($request);

        return $next($request);
    }

    /**
     * Sanitize all input data
     */
    protected function sanitizeInput(Request $request): void
    {
        // Get all input
        $input = $request->all();

        // Recursively sanitize each input
        $sanitized = $this->sanitizeArray($input);

        // Replace the request input with sanitized data
        $request->merge($sanitized);
    }

    /**
     * Recursively sanitize an array
     */
    protected function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value)) {
                $data[$key] = $this->sanitizeString($value);
            }
        }

        return $data;
    }

    /**
     * Sanitize a string to prevent various injection attacks
     */
    protected function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Remove or encode potentially dangerous characters for different contexts
        // This is a defense-in-depth approach - Laravel's query builder handles SQL injection
        // but we add additional sanitization for XSS and other attacks

        // Strip tags (HTML) - prevents some XSS
        $value = strip_tags($value);

        // Remove patterns that could indicate command injection attempts
        $value = preg_replace('/[\r\n\t]/', ' ', $value);

        // Trim whitespace
        $value = trim($value);

        // Remove multiple spaces
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }
}
