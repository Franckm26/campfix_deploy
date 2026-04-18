<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP A10: Unvalidated Redirects and Forwards
 * Validates redirect URLs to prevent open redirect vulnerabilities
 */
class ValidateRedirect
{
    /**
     * List of allowed domains for redirects
     */
    protected array $allowedDomains = [];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for redirect parameter in the request
        if ($request->has('redirect') || $request->has('url') || $request->has('destination')) {
            $redirectUrl = $request->get('redirect') ?? $request->get('url') ?? $request->get('destination');

            if (! $this->isValidRedirect($redirectUrl)) {
                // Log the attempt
                \Log::warning('Potential open redirect attempt detected', [
                    'ip' => $request->ip(),
                    'user_id' => $request->user()?->id,
                    'url' => $redirectUrl,
                    'request_uri' => $request->fullUrl(),
                ]);

                // Default to home page instead of allowing the redirect
                $request->merge([
                    'redirect' => null,
                    'url' => null,
                    'destination' => null,
                ]);
            }
        }

        return $next($request);
    }

    /**
     * Validate if the redirect URL is safe
     */
    protected function isValidRedirect(?string $url): bool
    {
        // If no URL provided, it's valid (will use default behavior)
        if (empty($url)) {
            return true;
        }

        // Check for protocol-relative URLs (//example.com)
        if (preg_match('/^\/\//', $url)) {
            return false;
        }

        // Check for data: URLs
        if (str_starts_with($url, 'data:')) {
            return false;
        }

        // Check for javascript: URLs
        if (str_starts_with($url, 'javascript:')) {
            return false;
        }

        // Check for file: URLs
        if (str_starts_with($url, 'file:')) {
            return false;
        }

        // Check for absolute URLs
        if (preg_match('/^https?:\/\//', $url)) {
            // For absolute URLs, validate the host
            $parsedUrl = parse_url($url);
            $host = $parsedUrl['host'] ?? '';

            // Allow localhost and localhost variants
            if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'])) {
                return true;
            }

            // Check against allowed domains list
            if (! empty($this->allowedDomains) && ! in_array($host, $this->allowedDomains)) {
                return false;
            }

            // Block private IP ranges to prevent SSRF
            if ($this->isPrivateIP($host)) {
                return false;
            }

            return true;
        }

        // For relative URLs (starting with /), allow them
        if (str_starts_with($url, '/')) {
            return true;
        }

        // Block all other URLs
        return false;
    }

    /**
     * Check if host is a private/internal IP
     */
    protected function isPrivateIP(string $host): bool
    {
        // Try to resolve hostname to IP
        $ip = gethostbyname($host);

        // If resolution failed (returns hostname), assume it's not a private IP
        if ($ip === $host) {
            return false;
        }

        // Check for private IP ranges (RFC 1918)
        // 10.0.0.0/8
        if (str_starts_with($ip, '10.')) {
            return true;
        }

        // 172.16.0.0/12
        if (preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip)) {
            return true;
        }

        // 192.168.0.0/16
        if (str_starts_with($ip, '192.168.')) {
            return true;
        }

        // 127.0.0.0/8 (localhost)
        if (str_starts_with($ip, '127.')) {
            return true;
        }

        // 169.254.0.0/16 (link-local)
        if (str_starts_with($ip, '169.254.')) {
            return true;
        }

        // Check for IPv6 private ranges
        if (str_starts_with($ip, 'fe80:') || str_starts_with($ip, 'fc') || str_starts_with($ip, 'fd')) {
            return true;
        }

        return false;
    }
}
