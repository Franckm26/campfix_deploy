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

        // Allow MIS, School Admin, and Building Admin to access admin routes
        // School Administrator, Academic Head, and Program Head should use the principal dashboard
        if (! in_array($user->role, ['mis', 'school_admin', 'building_admin'])) {
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
