<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only MIS can access routes with this middleware
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect('/')->with('error', 'Please login first.');
        }

        $user = auth()->user();

        // Allow operational roles to use their role-prefixed admin pages.
        if (! $user->isSystemAdministrator() && ! in_array($user->role, [
            'mis',
            'school_admin',
            'building_admin',
            'academic_head',
            'program_head',
            'principal_assistant',
        ], true)) {
            SecurityLogger::logUnauthorizedAccess([
                'user_id' => $user->id,
                'reason' => 'Insufficient role for admin access',
                'url' => $request->fullUrl(),
            ]);

            return redirect('/dashboard')->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
