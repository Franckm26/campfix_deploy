<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtAuthenticate
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (! $user) {
                return response()->json([
                    'error' => 'User not found',
                    'message' => 'Invalid token - user does not exist',
                ], 401);
            }

            // Check if user is archived
            if ($user->is_archived || $user->archive_folder_id) {
                return response()->json([
                    'error' => 'Account archived',
                    'message' => 'Your account has been archived',
                ], 403);
            }

        } catch (TokenExpiredException $e) {
            return response()->json([
                'error' => 'Token expired',
                'message' => 'Your session has expired. Please login again.',
            ], 401);

        } catch (TokenInvalidException $e) {
            return response()->json([
                'error' => 'Token invalid',
                'message' => 'Invalid token provided',
            ], 401);

        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token absent',
                'message' => 'Authorization token not provided',
            ], 401);
        }

        return $next($request);
    }
}
