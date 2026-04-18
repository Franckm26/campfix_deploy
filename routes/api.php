<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConcernController;
use App\Http\Controllers\EventRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - JWT Authentication
|--------------------------------------------------------------------------
*/

Route::middleware(['api.context', 'api.query.guard', 'api.resource', 'api.security'])->group(function () {
    // Health check
    Route::get('/health', function () {
        return response()->json(['status' => 'healthy', 'timestamp' => now()->toIso8601String()]);
    });

    // Auth - Rate limited (OWASP A6: Rate Limiting - 5 attempts per minute)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/login', [AuthController::class, 'apiLogin']);
        Route::post('/auth/register', [AuthController::class, 'apiRegister']);
    });

    // Auth routes with JWT middleware
    Route::post('/auth/logout', [AuthController::class, 'apiLogout'])->middleware('jwt.auth');
    Route::get('/auth/user', [AuthController::class, 'apiUser'])->middleware('jwt.auth');
    Route::post('/auth/refresh', [AuthController::class, 'apiRefreshToken'])->middleware('jwt.auth');

    // Categories - Public read, Admin write
    Route::get('/categories', [CategoryController::class, 'apiIndex']);
    Route::middleware(['jwt.auth', 'role:mis,admin'])->group(function () {
        Route::post('/categories', [CategoryController::class, 'apiStore']);
        Route::delete('/categories/{category}', [CategoryController::class, 'apiDestroy']);
    });

    // Protected routes - JWT authentication with rate limiting
    Route::middleware(['jwt.auth', 'throttle:60,1'])->group(function () {

    // Concerns - User can only access their own
    Route::get('/concerns', [ConcernController::class, 'apiIndex']);
    Route::post('/concerns', [ConcernController::class, 'apiStore']);
    Route::get('/concerns/{id}', [ConcernController::class, 'apiShow']);
    Route::get('/concerns/{id}/edit-data', [ConcernController::class, 'apiEditData']);
    Route::put('/concerns/{id}', [ConcernController::class, 'apiUpdate']);
    Route::delete('/concerns/{id}', [ConcernController::class, 'apiDestroy']);

    // Events
    Route::get('/events', [EventRequestController::class, 'apiIndex']);
    Route::post('/events', [EventRequestController::class, 'apiStore']);
    Route::get('/events/{id}', [EventRequestController::class, 'apiShow']);

        // Admin routes - Additional role-based protection
        Route::middleware('role:mis,admin')->group(function () {
            Route::get('/admin/dashboard', [AuthController::class, 'apiDashboard']);
        });
    });
});
