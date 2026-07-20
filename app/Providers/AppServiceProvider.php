<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Microsoft\Provider as MicrosoftProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! $this->app->bound('redirect')) {
            $this->app->singleton('redirect', function ($app) {
                $redirector = new Redirector($app['url']);

                if ($app->bound('session.store')) {
                    $redirector->setSession($app['session.store']);
                }

                return $redirector;
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure Microsoft OAuth Provider
        $this->bootMicrosoftSocialite();

        // Force HTTPS URLs in production (for Vercel and other reverse proxies)
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
        
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

        // Share layout-only data once per page render.
        View::composer('layouts.app', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                $request = request();

                App::setLocale($user->language ?: config('app.locale', 'en'));
                config(['app.timezone' => $user->timezone ?: config('app.timezone')]);
                date_default_timezone_set($user->timezone ?: config('app.timezone'));
                Carbon::setLocale($user->language ?: config('app.locale', 'en'));

                $perPage = (int) ($user->items_per_page ?: 10);
                $showEventRequestModal = in_array($user->role, [
                    'faculty',
                    'building_admin',
                    'school_admin',
                    'academic_head',
                    'program_head',
                    'principal_assistant',
                ], true) && (
                    $request->routeIs('dashboard')
                    || $request->routeIs('events.my')
                    || $request->routeIs('events.calendar')
                    || $request->routeIs('admin.events')
                    || $request->boolean('open_modal')
                );

                $view->with('notifications', $user->notifications()
                    ->latest()
                    ->take(8)
                    ->get(['id', 'type', 'data', 'read_at', 'created_at']));
                $view->with('unread_count', $user->unreadNotifications()->count());
                $view->with('userDateFormat', $user->date_format ?: 'Y-m-d');
                $view->with('userItemsPerPage', max(5, min(100, $perPage)));
                $view->with('showEventRequestModal', $showEventRequestModal);

                if ($showEventRequestModal) {
                    $facilities = \App\Models\Facility::orderBy('type')->orderBy('name')->get();
                    $view->with('facilities', $facilities);
                }
            }
        });
    }

    /**
     * Configure Microsoft Socialite Provider
     */
    protected function bootMicrosoftSocialite(): void
    {
        Socialite::extend('microsoft', function ($app) {
            $config = $app['config']['services.microsoft'];
            return Socialite::buildProvider(MicrosoftProvider::class, $config);
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
