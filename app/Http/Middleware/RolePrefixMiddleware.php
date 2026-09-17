<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RolePrefixMiddleware
{
    /**
     * Role → URL prefix mapping.
     * When a user hits /admin/*, they get redirected to their role prefix.
     */
    private const ROLE_PREFIXES = [
        'mis'                 => 'mis',
        'building_admin'      => 'building-admin',
        'school_admin'        => 'school-admin',
        'academic_head'       => 'academic-head',
        'program_head'        => 'program-head',
        'principal_assistant' => 'principal-assistant',
        'admin'               => 'system-admin',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();
        $role = $user->role;

        // Skip for superadmin — already handled by system-admin prefix
        if ($user->is_superadmin || $role === 'superadmin') {
            return $next($request);
        }

        $rolePrefix = self::ROLE_PREFIXES[$role] ?? null;
        if (! $rolePrefix) {
            return $next($request);
        }

        $path = $request->path(); // e.g. "admin" or "admin/users"

        // If the request is on /admin/* but should be on /{role-prefix}/*,
        // redirect to the role-prefixed URL.
        if (preg_match('#^admin(/.*)?$#', $path, $m)) {
            $suffix  = $m[1] ?? '';           // e.g. "" or "/users"
            $query   = $request->getQueryString();
            $newPath = '/' . $rolePrefix . $suffix . ($query ? '?' . $query : '');
            return redirect($newPath);
        }

        return $next($request);
    }
}
