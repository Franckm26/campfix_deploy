<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\RedirectResponse;

class ProtectedRoute
{
    private const SYSTEM_ADMIN_ROUTE_ALIASES = [
        'admin.users' => 'superadmin.users',
        'admin.reports' => 'superadmin.reports',
        'admin.events' => 'superadmin.events',
        'admin.logs' => 'superadmin.activity-logs',
        'admin.logs.folder' => 'superadmin.activity-logs.folder',
        'admin.management' => 'superadmin.management',
        'admin.analytics' => 'superadmin.analytics',
    ];

    /**
     * Generate a signed URL for System Administrator GET pages.
     * Mutation routes continue to rely on authentication, authorization, and CSRF.
     */
    public static function url(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $route = Route::getRoutes()->getByName($name);
        $parameters = self::normalizeParameters($parameters, $route?->parameterNames() ?? []);

        if (! str_starts_with($name, 'superadmin.') || ! $route || ! in_array('GET', $route->methods(), true)) {
            return route($name, $parameters, $absolute);
        }

        $routeParameterNames = $route->parameterNames();
        $pathParameters = Arr::only($parameters, $routeParameterNames);
        $queryParameters = Arr::except($parameters, $routeParameterNames);
        $signedUrl = URL::signedRoute($name, $pathParameters, null, $absolute);

        if ($queryParameters === []) {
            return $signedUrl;
        }

        return $signedUrl.'&'.http_build_query($queryParameters);
    }

    /**
     * Redirect operational controller actions back to a signed System
     * Administrator page without changing redirects for other roles.
     */
    public static function redirect(string $name, mixed $parameters = [], int $status = 302): RedirectResponse
    {
        if (auth()->check() && auth()->user()->isSystemAdministrator()) {
            $name = self::SYSTEM_ADMIN_ROUTE_ALIASES[$name] ?? $name;
        }

        return redirect()->to(self::url($name, $parameters), $status);
    }

    private static function normalizeParameters(mixed $parameters, array $routeParameterNames): array
    {
        if (! is_array($parameters)) {
            $parameters = [$parameters];
        }

        foreach ($parameters as $key => $value) {
            if (is_int($key) && isset($routeParameterNames[$key])) {
                unset($parameters[$key]);
                $parameters[$routeParameterNames[$key]] = $value;
            }
        }

        return $parameters;
    }
}
