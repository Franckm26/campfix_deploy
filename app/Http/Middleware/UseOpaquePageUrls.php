<?php

namespace App\Http\Middleware;

use App\Support\OpaquePageUrl;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UseOpaquePageUrls
{
    public function __construct(private readonly OpaquePageUrl $opaquePageUrl) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldRedirect($request)) {
            return redirect()->to($this->opaquePageUrl->url($request->getRequestUri()));
        }

        return $next($request);
    }

    private function shouldRedirect(Request $request): bool
    {
        if (! $request->isMethod('GET')
            || ! $request->user()
            || $request->attributes->getBoolean('opaque.internal')
            || $request->ajax()
            || $request->expectsJson()
            || ! $request->acceptsHtml()) {
            return false;
        }

        return ! $request->is([
            '/',
            'hash/*',
            'api/*',
            'auth/*',
            'login',
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
