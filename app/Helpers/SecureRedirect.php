<?php

namespace App\Helpers;

use Illuminate\Http\Request;

/**
 * OWASP A10: Secure Redirect Helper
 * Provides safe redirect functionality throughout the application
 */
class SecureRedirect
{
    /**
     * Allowed domains for redirects (configure based on your needs)
     */
    protected static array $allowedDomains = [];

    /**
     * Safely redirect to a given URL if valid, otherwise redirect to default
     */
    public static function safe(Request $request, ?string $url, string $default = '/dashboard')
    {
        if (empty($url)) {
            return redirect($default);
        }

        if (! self::isValidRedirect($url)) {
            // Log suspicious redirect attempt
            \Log::warning('Blocked invalid redirect attempt', [
                'url' => $url,
                'ip' => $request->ip(),
                'user_id' => $request->user()?->id,
            ]);

            return redirect($default);
        }

        return redirect($url);
    }

    /**
     * Validate if redirect URL is safe
     */
    public static function isValidRedirect(?string $url): bool
    {
        if (empty($url)) {
            return true;
        }

        // Block protocol-relative URLs
        if (preg_match('/^\/\//', $url)) {
            return false;
        }

        // Block dangerous protocols
        if (preg_match('/^(javascript|data|vbscript):/i', $url)) {
            return false;
        }

        // Allow relative URLs starting with /
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        // For absolute URLs, validate the domain
        if (preg_match('/^https?:\/\//', $url)) {
            $parsed = parse_url($url);
            $host = $parsed['host'] ?? '';

            // Allow localhost
            if (in_array($host, ['localhost', '127.0.0.1', '::1'])) {
                return true;
            }

            // Check allowed domains
            if (! empty(self::$allowedDomains) && ! in_array($host, self::$allowedDomains)) {
                return false;
            }

            // Block private IPs
            return ! self::isPrivateIP($host);
        }

        return false;
    }

    /**
     * Check if host resolves to private IP
     */
    protected static function isPrivateIP(string $host): bool
    {
        $ip = gethostbyname($host);

        if ($ip === $host) {
            return false;
        }

        // RFC 1918 private ranges
        return (bool) preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.|127\.|169\.254\.)/', $ip);
    }

    /**
     * Set allowed domains for redirects
     */
    public static function setAllowedDomains(array $domains): void
    {
        self::$allowedDomains = $domains;
    }
}
