<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();

                // Set application locale based on user's language preference
                $locale = $this->mapLanguageToLocale($user->language ?? 'en');
                if (file_exists(resource_path('lang/'.$locale))) {
                    app()->setLocale($locale);
                }

                // Set Carbon's default timezone for this request
                try {
                    \Carbon\Carbon::setDefaultTimezone($user->timezone ?? 'Asia/Shanghai');
                } catch (\Exception $e) {
                    // Fallback to UTC if invalid timezone
                    \Carbon\Carbon::setDefaultTimezone('UTC');
                }
            }
        } catch (\Exception $e) {
            // If anything fails, continue without setting preferences
            // This prevents 500 errors if there are issues with user attributes
        }

        return $next($request);
    }

    /**
     * Map language code to Laravel locale
     */
    private function mapLanguageToLocale($language)
    {
        $mappings = [
            'en' => 'en',
            'tl' => 'tl', // Tagalog
            // Add more mappings as needed
        ];

        return $mappings[$language] ?? 'en';
    }
}
