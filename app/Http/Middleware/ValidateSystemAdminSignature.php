<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Response;

class ValidateSystemAdminSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        // Search, filters, pagination, and display options do not identify the
        // protected page. The URL path and route parameters remain signed.
        $ignoredQueryParameters = array_values(array_diff(
            array_keys($request->query()),
            ['signature', 'expires']
        ));

        if (! $request->hasValidSignatureWhileIgnoring($ignoredQueryParameters)) {
            throw new InvalidSignatureException;
        }

        return $next($request);
    }
}
