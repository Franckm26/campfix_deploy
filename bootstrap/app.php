<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SuperadminMiddleware;
use App\Http\Middleware\ApiRequestContext;
use App\Http\Middleware\ApiSecurityHeaders;
use App\Http\Middleware\BlockSuspiciousQuery;
use App\Http\Middleware\EnforceApiResourceLimits;
use App\Http\Middleware\EnforceSingleSession;
use App\Http\Middleware\JwtAuthenticate;
use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\RequestSizeLimit;
use App\Http\Middleware\SanitizeInput;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetUserLocale;
use App\Http\Middleware\SsrfProtection;
use App\Http\Middleware\ValidateRedirect;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('concerns:auto-resolve-rooms')->everyMinute();
        $schedule->command('concerns:send-follow-ups')->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Register security headers middleware globally (OWASP A6)
        $middleware->prependToGroup('web', SecurityHeaders::class);

        // Register input sanitization middleware globally (OWASP A1)
        $middleware->prependToGroup('web', SanitizeInput::class);

        // Register redirect validation middleware globally (OWASP A10)
        $middleware->prependToGroup('web', ValidateRedirect::class);

        // Register request size limit middleware (OWASP A5)
        $middleware->prependToGroup('web', RequestSizeLimit::class);

        // Register user locale middleware
        $middleware->appendToGroup('web', SetUserLocale::class);

        // Enforce single active session per user
        $middleware->appendToGroup('web', EnforceSingleSession::class);

        $middleware->prependToGroup('api', ApiRequestContext::class);
        $middleware->appendToGroup('api', BlockSuspiciousQuery::class);
        $middleware->appendToGroup('api', EnforceApiResourceLimits::class);
        $middleware->appendToGroup('api', ApiSecurityHeaders::class);

        $middleware->alias([
            'admin'      => AdminMiddleware::class,
            'superadmin' => SuperadminMiddleware::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'security' => SecurityHeaders::class,
            'sanitize' => SanitizeInput::class,
            'validate.redirect' => ValidateRedirect::class,
            'rate.limit' => RateLimitMiddleware::class,
            'request.size' => RequestSizeLimit::class,
            'jwt.auth' => JwtAuthenticate::class,
            'api.context' => ApiRequestContext::class,
            'api.security' => ApiSecurityHeaders::class,
            'api.query.guard' => BlockSuspiciousQuery::class,
            'api.resource' => EnforceApiResourceLimits::class,
            'ssrf.protect' => SsrfProtection::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'login',
            'register',
            'logout',
            'verify-otp',
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
