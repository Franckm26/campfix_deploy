<?php

namespace App\Http\Middleware;

use App\Support\OpaquePageUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class UseOpaquePageUrls
{
    public function __construct(private readonly OpaquePageUrl $opaquePageUrl) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldRedirect($request)) {
            // Keep this redirect relative so an APP_URL/www mismatch cannot
            // move the browser to another host and discard the login session.
            return new RedirectResponse(
                $this->opaquePageUrl->path($request->getRequestUri()),
                Response::HTTP_FOUND,
                ['Cache-Control' => 'no-store']
            );
        }

        return $next($request);
    }

    private function shouldRedirect(Request $request): bool
    {
        if (! $request->isMethod('GET')
            || ! $request->user()
            || $request->attributes->getBoolean('opaque.internal')
            || $request->routeIs('opaque.page')
            || $request->ajax()
            || $request->expectsJson()
            || ! $request->acceptsHtml()) {
            return false;
        }

        return ! $request->is([
            '/',
            'api/*',
            'auth/*',
            'login',
            // This forced-password setup page must retain its real path. Turning
            // it into an opaque URL makes EnsurePasswordChanged redirect back to
            // this path before the opaque route can be decoded.
            'first-login-password',
            'otp-choice',
            'otp-delivery',
            'resend-otp',
            'sitemap.xml',
            'robots.txt',
            '.well-known/*',
            'up',
            'test',
            'test-auth',
        ]);
    }
}
