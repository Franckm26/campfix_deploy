<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP A2: Broken Authentication and Session Management
 * Implements rate limiting to prevent brute force attacks
 */
class RateLimitMiddleware
{
    /**
     * Maximum number of attempts allowed
     */
    protected int $maxAttempts = 5;

    /**
     * Number of minutes to lock out after max attempts
     */
    protected int $lockoutMinutes = 15;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        // Apply stricter rate limiting to login routes
        if ($this->isLoginRoute($request)) {
            if (RateLimiter::tooManyAttempts($key, $this->maxAttempts)) {
                $seconds = RateLimiter::availableIn($key);

                SecurityLogger::logRateLimitExceeded([
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'attempts' => $this->maxAttempts,
                    'limit' => $this->maxAttempts,
                ]);

                return response()->json([
                    'error' => 'Too many login attempts. Please try again later.',
                    'retry_after' => ceil($seconds / 60).' minutes',
                ], 429);
            }

            RateLimiter::hit($key, $this->lockoutMinutes * 60);
        }
        // Apply rate limiting to sensitive API operations (OWASP API4)
        elseif ($this->isSensitiveApiOperation($request)) {
            $maxSensitiveAttempts = 10; // Allow more attempts for general API operations
            if (RateLimiter::tooManyAttempts($key.'_sensitive', $maxSensitiveAttempts)) {
                $seconds = RateLimiter::availableIn($key.'_sensitive');

                SecurityLogger::logRateLimitExceeded([
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                    'attempts' => $maxSensitiveAttempts,
                    'limit' => $maxSensitiveAttempts,
                    'type' => 'sensitive_api_operation',
                ]);

                return response()->json([
                    'error' => 'Too many requests. Please try again later.',
                    'retry_after' => ceil($seconds / 60).' minutes',
                ], 429);
            }

            RateLimiter::hit($key.'_sensitive', 60); // 1 minute decay
        }

        return $next($request);
    }

    /**
     * Determine if the request is a login route
     */
    protected function isLoginRoute(Request $request): bool
    {
        $loginRoutes = [
            'login',
            'api/login',
            'verify-otp',
            'api/verify-otp',
            'send-otp',
            'api/send-otp',
        ];

        $currentRoute = $request->route()?->getName() ?? $request->path();

        foreach ($loginRoutes as $route) {
            if (str_contains($currentRoute, $route) || str_contains($request->path(), $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request is a sensitive API operation that needs rate limiting
     */
    protected function isSensitiveApiOperation(Request $request): bool
    {
        // Rate limit sensitive operations like status changes, deletions, assignments
        $sensitiveOperations = [
            'PUT', 'PATCH', 'DELETE' // HTTP methods
        ];

        // Also rate limit specific API endpoints
        $sensitiveRoutes = [
            'api/concerns',
            'api/reports',
            'api/events',
            'assign',
            'resolve',
            'approve',
            'reject',
        ];

        $currentPath = $request->path();
        $method = $request->method();

        // Rate limit based on HTTP method
        if (in_array($method, $sensitiveOperations)) {
            return true;
        }

        // Rate limit based on path
        foreach ($sensitiveRoutes as $route) {
            if (str_contains($currentPath, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the request signature for rate limiting
     */
    protected function resolveRequestSignature(Request $request): string
    {
        // For OTP verification, use session-based rate limiting (per user)
        if (str_contains($request->path(), 'verify-otp')) {
            $userId = session('otp_user');
            if ($userId) {
                return 'otp_verify:user_' . $userId;
            }
            // Fallback to IP if no user in session
            return 'otp_verify:' . $request->ip();
        }

        // Use email if provided, otherwise use IP
        $email = $request->get('email');

        if ($email) {
            return 'login:'.strtolower($email);
        }

        return 'login:'.$request->ip();
    }

    /**
     * Clear rate limit for a specific user
     */
    public static function clearRateLimit(string $key): void
    {
        RateLimiter::clear($key);
    }

    /**
     * Get remaining attempts for a key
     */
    public static function remainingAttempts(string $key): int
    {
        return RateLimiter::remaining($key, 5);
    }
}
