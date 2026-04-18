<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApiRequestContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = $request->headers->get('X-Request-ID');

        if (! is_string($requestId) || trim($requestId) === '' || strlen($requestId) > 120) {
            $requestId = (string) Str::uuid();
        }

        $requestId = preg_replace('/[^A-Za-z0-9\-\_.]/', '', $requestId) ?: (string) Str::uuid();

        $request->attributes->set('request_id', $requestId);
        $request->attributes->set('api_inventory_group', $this->resolveInventoryGroup($request));

        $response = $next($request);

        $response->headers->set('X-Request-ID', $requestId);
        $response->headers->set('X-API-Inventory', $request->attributes->get('api_inventory_group', 'general'));

        return $response;
    }

    /**
     * Resolve a stable inventory grouping header for the API route.
     */
    protected function resolveInventoryGroup(Request $request): string
    {
        $path = trim($request->path(), '/');

        if ($path === 'api/auth/login' || $path === 'api/auth/register' || str_starts_with($path, 'api/auth/')) {
            return 'auth';
        }

        if (str_starts_with($path, 'api/concerns')) {
            return 'concerns';
        }

        if (str_starts_with($path, 'api/events')) {
            return 'events';
        }

        if (str_starts_with($path, 'api/categories')) {
            return 'categories';
        }

        if (str_starts_with($path, 'api/admin')) {
            return 'admin';
        }

        return 'general';
    }
}