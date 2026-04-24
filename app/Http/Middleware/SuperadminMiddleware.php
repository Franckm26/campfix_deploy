<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperadminMiddleware
{
    /**
     * Handle an incoming request.
     * Only superadmin users can access routes protected by this middleware.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect('/')->with('error', 'Please login first.');
        }

        $user = auth()->user();

        if (! ($user->is_superadmin || $user->role === 'superadmin')) {
            SecurityLogger::logUnauthorizedAccess([
                'user_id' => $user->id,
                'reason'  => 'Superadmin access attempted by non-superadmin',
                'url'     => $request->fullUrl(),
            ]);

            // Return 404 to hide the existence of superadmin routes
            abort(404);
        }

        return $next($request);
    }
}
