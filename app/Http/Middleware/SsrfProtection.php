<?php

namespace App\Http\Middleware;

use App\Services\ApiSecurityResponse;
use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP API7: Server Side Request Forgery (SSRF) Protection
 * Prevents attackers from making unauthorized requests to internal/external systems
 */
class SsrfProtection
{
    /**
     * Allowed domains for external requests (whitelist approach)
     *
     * @var array<int, string>
     */
    protected array $allowedDomains = [
        'api.sms-service.com', // Example SMS service
        'maps.googleapis.com', // For location services if needed
        // Add other trusted domains as needed
    ];

    /**
     * Blocked IP ranges (private/internal networks)
     *
     * @var array<int, string>
     */
    protected array $blockedIpRanges = [
        '127.0.0.0/8',    // localhost
        '10.0.0.0/8',     // private network
        '172.16.0.0/12',  // private network
        '192.168.0.0/16', // private network
        '169.254.0.0/16', // link-local
        '0.0.0.0/8',      // this network
        '224.0.0.0/4',    // multicast
        '240.0.0.0/4',    // reserved
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for suspicious URL parameters that could lead to SSRF
        $suspiciousParams = ['url', 'uri', 'link', 'redirect', 'callback', 'endpoint'];

        foreach ($suspiciousParams as $param) {
            if ($request->has($param)) {
                $url = $request->input($param);

                if ($this->isSuspiciousUrl($url)) {
                    SecurityLogger::logSecurityBreach([
                        'type' => 'ssrf_attempt',
                        'severity' => 'high',
                        'details' => [
                            'parameter' => $param,
                            'url' => $url,
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ],
                    ]);

                    return ApiSecurityResponse::reject(
                        $request,
                        'Invalid URL parameter detected',
                        'invalid_url_parameter'
                    );
                }
            }
        }

        // Check request body for URLs (JSON payloads)
        $body = $request->getContent();
        if ($this->containsSuspiciousUrl($body)) {
            SecurityLogger::logSecurityBreach([
                'type' => 'ssrf_attempt_body',
                'severity' => 'high',
                'details' => [
                    'body_length' => strlen($body),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            return ApiSecurityResponse::reject(
                $request,
                'Invalid content detected',
                'invalid_request_content'
            );
        }

        return $next($request);
    }

    /**
     * Check if URL is suspicious (could lead to SSRF)
     */
    protected function isSuspiciousUrl(?string $url): bool
    {
        if (empty($url)) {
            return false;
        }

        // Parse URL
        $parsed = parse_url($url);
        if (!$parsed) {
            return true; // Invalid URL format
        }

        // Check if it's an IP address and in blocked ranges
        if (filter_var($parsed['host'] ?? '', FILTER_VALIDATE_IP)) {
            return $this->isIpInBlockedRange($parsed['host']);
        }

        // For domain names, check against whitelist
        $host = strtolower($parsed['host'] ?? '');
        foreach ($this->allowedDomains as $allowed) {
            if (str_contains($host, $allowed) || str_contains($allowed, $host)) {
                return false; // Allowed
            }
        }

        // Check for localhost variants
        $localhostVariants = ['localhost', '127.0.0.1', '::1', '0.0.0.0'];
        if (in_array(strtolower($host), $localhostVariants)) {
            return true;
        }

        // Check for internal domain patterns
        $internalPatterns = [
            '/\.local$/',
            '/\.internal$/',
            '/^internal\./',
            '/^local\./',
        ];

        foreach ($internalPatterns as $pattern) {
            if (preg_match($pattern, $host)) {
                return true;
            }
        }

        // Default: allow external domains (but log for monitoring)
        // In production, you might want to be more restrictive
        SecurityLogger::logSecurityBreach([
            'type' => 'external_url_access',
            'severity' => 'low',
            'details' => ['url' => $url],
        ]);

        return false;
    }

    /**
     * Check if IP is in blocked ranges
     */
    protected function isIpInBlockedRange(string $ip): bool
    {
        foreach ($this->blockedIpRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        [$subnet, $mask] = explode('/', $range);

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = ~((1 << (32 - $mask)) - 1);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Check if request body contains suspicious URLs
     */
    protected function containsSuspiciousUrl(string $content): bool
    {
        // Look for URL patterns in JSON/content
        $urlPatterns = [
            '/"url"\s*:\s*"([^"]+)"/i',
            '/"uri"\s*:\s*"([^"]+)"/i',
            '/"link"\s*:\s*"([^"]+)"/i',
            '/"endpoint"\s*:\s*"([^"]+)"/i',
            '/"callback"\s*:\s*"([^"]+)"/i',
        ];

        foreach ($urlPatterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $url = $matches[1] ?? '';
                if ($this->isSuspiciousUrl($url)) {
                    return true;
                }
            }
        }

        return false;
    }
}