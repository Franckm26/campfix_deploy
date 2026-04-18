<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure rate limiting for API
        $this->configureRateLimiting();

        if (auth()->check()) {
            $user = auth()->user();

            App::setLocale($user->language ?: config('app.locale', 'en'));
            config(['app.timezone' => $user->timezone ?: config('app.timezone')]);
            date_default_timezone_set($user->timezone ?: config('app.timezone'));
            Carbon::setLocale($user->language ?: config('app.locale', 'en'));
            Date::use(Carbon::class);
            Paginator::defaultView('pagination::bootstrap-5');
        }

        // Share notifications with all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();

                App::setLocale($user->language ?: config('app.locale', 'en'));
                config(['app.timezone' => $user->timezone ?: config('app.timezone')]);
                date_default_timezone_set($user->timezone ?: config('app.timezone'));
                Carbon::setLocale($user->language ?: config('app.locale', 'en'));

                $perPage = (int) ($user->items_per_page ?: 10);
                $view->with('notifications', $user->notifications()->latest()->take(20)->get());
                $view->with('unread_count', $user->unreadNotifications()->count());
                $view->with('userDateFormat', $user->date_format ?: 'Y-m-d');
                $view->with('userItemsPerPage', max(5, min(100, $perPage)));
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * OWASP A5: Security Misconfiguration - Rate limiting to prevent brute force attacks
     */
    protected function configureRateLimiting(): void
    {
        // API rate limit: 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Login rate limit: relaxed since we handle progressive lockout in AuthController
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // OTP rate limit: 10 requests per minute
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // General web: 100 requests per minute
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(100)->by($request->ip());
        });
    }
}
