<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (auth()->check()) {
            $user = auth()->user();
            
            // If user needs to change password, redirect to first-login-password page
            // Allow access only to first-login-password, logout, and profile routes
            if ($user->force_password_change) {
                $allowedRoutes = [
                    'auth.first-login-password',
                    'auth.first-login-password.update',
                    'logout',
                ];
                
                // Allow these specific paths
                $allowedPaths = [
                    '/first-login-password',
                    '/logout',
                ];
                
                // Check if current route is allowed
                $currentRoute = $request->route() ? $request->route()->getName() : null;
                $currentPath = $request->path();
                
                // If not on allowed route/path, redirect to first-login-password
                if (!in_array($currentRoute, $allowedRoutes) && !in_array('/' . $currentPath, $allowedPaths)) {
                    return redirect()->route('auth.first-login-password')
                        ->with('warning', 'Please complete your profile setup before continuing.');
                }
            }
        }
        
        return $next($request);
    }
}
