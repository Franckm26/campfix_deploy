<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OWASP A5: Security Misconfiguration & A6: Denial of Service Protection
 * Limits request size to prevent large payload attacks
 */
class RequestSizeLimit
{
    /**
     * Maximum request size in kilobytes (default 2MB)
     */
    protected int $maxSize = 2048;

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check Content-Length header
        $contentLength = $request->header('Content-Length');

        if ($contentLength && (int) $contentLength > $this->maxSize * 1024) {
            return response()->json([
                'error' => 'Request payload too large. Maximum size is '.$this->maxSize.'KB.',
            ], 413);
        }

        return $next($request);
    }
}
