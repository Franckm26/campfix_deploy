<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate Limit Service Provider
 * 
 * Defines rate limiting strategies for different parts of the application
 * to prevent abuse and ensure fair resource usage
 */
class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Authentication endpoints - Very strict (5 per minute)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->input('email') ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many login attempts. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // OTP verification - Very strict (3 per minute per user)
        RateLimiter::for('otp', function (Request $request) {
            $userId = session('otp_user') ?? $request->ip();
            return Limit::perMinute(3)
                ->by($userId)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many OTP verification attempts. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // Password reset/change - Strict (10 per hour)
        RateLimiter::for('password', function (Request $request) {
            return Limit::perHour(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many password change attempts. Please try again later.',
                        'retry_after' => $headers['Retry-After'] ?? 3600
                    ], 429);
                });
        });

        // Concern/Report submission - Strict (5 per day)
        RateLimiter::for('submissions', function (Request $request) {
            return Limit::perDay(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    $retryAfterHours = ceil(($headers['Retry-After'] ?? 86400) / 3600);
                    return response()->json([
                        'error' => 'You have reached your daily submission limit of 5. Please try again tomorrow.',
                        'retry_after' => $headers['Retry-After'] ?? 86400,
                        'retry_after_hours' => $retryAfterHours
                    ], 429);
                });
        });

        // File uploads - Strict (5 per day)
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perDay(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    $retryAfterHours = ceil(($headers['Retry-After'] ?? 86400) / 3600);
                    return response()->json([
                        'error' => 'You have reached your daily upload limit of 5. Please try again tomorrow.',
                        'retry_after' => $headers['Retry-After'] ?? 86400,
                        'retry_after_hours' => $retryAfterHours
                    ], 429);
                });
        });

        // Status updates (assign, resolve, approve) - Moderate (60 per hour)
        RateLimiter::for('status-updates', function (Request $request) {
            return Limit::perHour(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many status updates. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 3600
                    ], 429);
                });
        });

        // Delete operations - Strict (30 per hour)
        RateLimiter::for('deletes', function (Request $request) {
            return Limit::perHour(30)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many delete operations. Please wait.',
                        'retry_after' => $headers['Retry-After'] ?? 3600
                    ], 429);
                });
        });

        // Batch operations - Very strict (10 per hour)
        RateLimiter::for('batch', function (Request $request) {
            return Limit::perHour(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many batch operations. Please wait.',
                        'retry_after' => $headers['Retry-After'] ?? 3600
                    ], 429);
                });
        });

        // Export operations - Strict (20 per hour)
        RateLimiter::for('exports', function (Request $request) {
            return Limit::perHour(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many export requests. Please wait.',
                        'retry_after' => $headers['Retry-After'] ?? 3600
                    ], 429);
                });
        });

        // General API - Moderate (100 per minute)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many API requests. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // General web requests - Generous (200 per minute)
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(200)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'error' => 'Too many requests. Please slow down.',
                            'retry_after' => $headers['Retry-After'] ?? 60
                        ], 429);
                    }
                    return response()->view('errors.429', ['retryAfter' => $headers['Retry-After'] ?? 60], 429);
                });
        });

        // Admin operations - Moderate (120 per minute)
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(120)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many admin operations. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // Notification endpoints - Moderate (60 per minute)
        RateLimiter::for('notifications', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many notification requests.',
                        'retry_after' => $headers['Retry-After'] ?? 60
                    ], 429);
                });
        });

        // User management - Strict (30 per hour)
        RateLimiter::for('user-management', function (Request $request) {
            return Limit::perHour(30)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'error' => 'Too many user management operations.',
                        'retry_after' => $headers['Retry-After'] ?? 3600
                    ], 429);
                });
        });
    }
}
