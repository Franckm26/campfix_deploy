<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // If the stored session ID doesn't match the current one,
            // this session was displaced by a newer login on another device.
            if ($user->active_session_id && $user->active_session_id !== session()->getId()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/')->with('error', 'Your account was logged in on another device. You have been signed out.');
            }
        }

        return $next($request);
    }
}
