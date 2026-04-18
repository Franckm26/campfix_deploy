<?php

namespace App\Http\Middleware;

use App\Services\ApiSecurityResponse;
use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousQuery
{
    /**
     * Suspicious query patterns commonly used for probing or injection attempts.
     *
     * @var array<int, string>
     */
    protected array $patterns = [
        '/\bunion\b\s+\bselect\b/i',
        '/\bselect\b.+\bfrom\b/i',
        '/\binsert\b.+\binto\b/i',
        '/\bupdate\b.+\bset\b/i',
        '/\bdelete\b.+\bfrom\b/i',
        '/\bdrop\b\s+\btable\b/i',
        '/\bor\s+1=1\b/i',
        '/<script\b/i',
        '/javascript:/i',
        '/\.\.\//',
        '/%2e%2e%2f/i',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $candidates = [
            'query' => $request->query(),
            'path' => $request->path(),
        ];

        foreach ($candidates as $source => $value) {
            if ($this->containsSuspiciousPattern($value)) {
                SecurityLogger::logSecurityBreach([
                    'type' => 'suspicious_api_request',
                    'severity' => 'medium',
                    'details' => [
                        'source' => $source,
                        'path' => $request->path(),
                        'request_id' => $request->attributes->get('request_id'),
                    ],
                    'ip' => $request->ip(),
                    'user_id' => optional($request->user())->id,
                ]);

                return ApiSecurityResponse::error(
                    'The request could not be processed.',
                    400,
                    'suspicious_request_blocked',
                    ['source' => $source],
                    $this->securityHeaders($request)
                );
            }
        }

        return $next($request);
    }

    /**
     * Determine whether a value contains suspicious patterns.
     */
    protected function containsSuspiciousPattern(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->containsSuspiciousPattern($item)) {
                    return true;
                }
            }

            return false;
        }

        if (! is_scalar($value)) {
            return false;
        }

        $stringValue = (string) $value;

        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $stringValue) === 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Security response headers.
     */
    protected function securityHeaders(Request $request): array
    {
        $headers = [
            'X-Request-ID' => (string) $request->attributes->get('request_id', ''),
            'X-API-Inventory' => (string) $request->attributes->get('api_inventory_group', 'general'),
        ];

        return array_filter($headers, fn ($value) => $value !== '');
    }
}