<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * OWASP A9: Security Logging and Monitoring Failures
 * Centralized security logging service
 */
class SecurityLogger
{
    /**
     * Resolve the preferred security log channel with fallback.
     */
    protected static function channel()
    {
        try {
            return Log::channel('security');
        } catch (\Throwable $e) {
            return Log::channel(config('logging.default'));
        }
    }

    /**
     * Log suspicious login activity
     */
    public static function logSuspiciousLogin(array $data): void
    {
        static::channel()->warning('Suspicious login attempt', [
            'ip' => $data['ip'] ?? request()->ip(),
            'email' => $data['email'] ?? null,
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'reason' => $data['reason'] ?? 'Unknown',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log unauthorized access attempt
     */
    public static function logUnauthorizedAccess(array $data): void
    {
        static::channel()->warning('Unauthorized access attempt', [
            'user_id' => $data['user_id'] ?? auth()->id(),
            'ip' => $data['ip'] ?? request()->ip(),
            'url' => $data['url'] ?? request()->fullUrl(),
            'method' => $data['method'] ?? request()->method(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'reason' => $data['reason'] ?? 'Access denied',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log potential security breach
     */
    public static function logSecurityBreach(array $data): void
    {
        static::channel()->error('Potential security breach', [
            'type' => $data['type'] ?? 'Unknown',
            'user_id' => $data['user_id'] ?? auth()->id(),
            'ip' => $data['ip'] ?? request()->ip(),
            'details' => $data['details'] ?? [],
            'severity' => $data['severity'] ?? 'medium',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log rate limit exceeded
     */
    public static function logRateLimitExceeded(array $data): void
    {
        static::channel()->warning('Rate limit exceeded', [
            'ip' => $data['ip'] ?? request()->ip(),
            'url' => $data['url'] ?? request()->fullUrl(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'attempts' => $data['attempts'] ?? 0,
            'limit' => $data['limit'] ?? 0,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log file upload security event
     */
    public static function logFileUploadSecurity(array $data): void
    {
        static::channel()->warning('File upload security event', [
            'user_id' => $data['user_id'] ?? auth()->id(),
            'filename' => $data['filename'] ?? 'Unknown',
            'filesize' => $data['filesize'] ?? 0,
            'mimetype' => $data['mimetype'] ?? 'Unknown',
            'ip' => $data['ip'] ?? request()->ip(),
            'reason' => $data['reason'] ?? 'Security check failed',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log admin action for audit trail
     */
    public static function logAdminAction(array $data): void
    {
        static::channel()->info('Admin action performed', [
            'user_id' => $data['user_id'] ?? auth()->id(),
            'action' => $data['action'] ?? 'Unknown',
            'resource_type' => $data['resource_type'] ?? 'Unknown',
            'resource_id' => $data['resource_id'] ?? null,
            'ip' => $data['ip'] ?? request()->ip(),
            'details' => $data['details'] ?? [],
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log input validation failure
     */
    public static function logValidationFailure(array $data): void
    {
        static::channel()->warning('Input validation failure', [
            'user_id' => $data['user_id'] ?? auth()->id(),
            'ip' => $data['ip'] ?? request()->ip(),
            'url' => $data['url'] ?? request()->fullUrl(),
            'validation_errors' => $data['errors'] ?? [],
            'input_data' => $data['input'] ?? [],
            'timestamp' => now()->toISOString(),
        ]);
    }
}