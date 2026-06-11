# Rate Limiting Implementation Guide

## Overview
Comprehensive rate limiting has been added to protect the Campfix application from abuse, brute force attacks, and resource exhaustion.

## Rate Limit Tiers

### 1. Authentication Endpoints (Very Strict)
- **Limiter**: `throttle:auth`
- **Limit**: 5 requests per minute
- **Applied to**:
  - `/login` - User login
  - `/api/login` - API login
  - `/verify-access-password` - Security verification

### 2. OTP Verification (Very Strict)
- **Limiter**: `throttle:otp`
- **Limit**: 3 requests per minute per user
- **Applied to**:
  - `/otp-delivery` - OTP delivery
  - `/resend-otp` - OTP resend

### 3. Password Operations (Strict)
- **Limiter**: `throttle:password`
- **Limit**: 10 requests per hour
- **Applied to**:
  - `/first-login-password` - First login password change
  - `/profile/password` - Password update

### 4. Submissions (Strict)
- **Limiter**: `throttle:submissions`
- **Limit**: 5 requests per day
- **Applied to**:
  - `/submit-concern` - Concern submission
  - `/reports` (POST) - Report submission
  - `/events` (POST) - Event request submission
  - API equivalents

### 5. File Uploads (Strict)
- **Limiter**: `throttle:uploads`
- **Limit**: 5 requests per day
- **Applied to**:
  - `/profile/upload-picture` - Profile picture upload
  - `/events-import` - Event import

### 6. Status Updates (Moderate)
- **Limiter**: `throttle:status-updates`
- **Limit**: 60 requests per hour
- **Applied to**:
  - `/update-status/{id}` - Concern status update
  - `/update-report-status/{id}` - Report status update
  - `/events/{id}/approve` - Event approval
  - `/events/{id}/reject` - Event rejection

### 7. Delete Operations (Strict)
- **Limiter**: `throttle:deletes`
- **Limit**: 30 requests per hour
- **Applied to**:
  - `/concerns/{id}` (DELETE)
  - `/concerns/{id}/soft-delete`
  - `/concerns/{id}/permanent-delete`
  - `/reports/{report}` (DELETE)
  - `/events/{id}/delete`
  - `/profile/remove-picture`

### 8. Batch Operations (Very Strict)
- **Limiter**: `throttle:batch`
- **Limit**: 10 requests per hour
- **Applied to**:
  - `/concerns/batch-*` - All batch operations on concerns
  - `/events/batch-*` - All batch operations on events
  - `/admin/users/batch-*` - All batch operations on users
  - `/admin/users/archive-all`
  - `/admin/users/delete-all`

### 9. Export Operations (Strict)
- **Limiter**: `throttle:exports`
- **Limit**: 20 requests per hour
- **Applied to**:
  - `/admin/export` - CSV export
  - `/admin/export-pdf` - PDF export
  - `/events/{id}/pdf` - Event PDF generation
  - All analytics PDF exports

### 10. Notifications (Moderate)
- **Limiter**: `throttle:notifications`
- **Limit**: 60 requests per minute
- **Applied to**:
  - All `/notifications/*` endpoints

### 11. General API (Moderate)
- **Limiter**: `throttle:api`
- **Limit**: 100 requests per minute
- **Applied to**:
  - All API routes requiring JWT authentication
  - `/api/concerns/*`
  - `/api/events/*`

### 12. General Web (Generous)
- **Limiter**: `throttle:web`
- **Limit**: 200 requests per minute
- **Applied to**:
  - Most web routes
  - Profile pages
  - Settings pages
  - Concern listing pages
  - Event listing pages

### 13. Admin Operations (Moderate)
- **Limiter**: `throttle:admin`
- **Limit**: 120 requests per minute
- **Applied to**:
  - All `/admin/*` routes (general browsing)

### 14. User Management (Strict)
- **Limiter**: `throttle:user-management`
- **Limit**: 30 requests per hour
- **Applied to**:
  - `/admin/users` (POST) - Create user

## Key Features

### 1. User-Based vs IP-Based Limiting
- Authenticated users: Rate limited by user ID
- Unauthenticated users: Rate limited by IP address
- OTP verification: Rate limited by user session

### 2. Response Headers
All rate-limited responses include:
- `Retry-After`: Time in seconds until the next request is allowed
- `X-RateLimit-Limit`: Maximum number of requests allowed
- `X-RateLimit-Remaining`: Number of requests remaining

### 3. Error Responses
```json
{
  "error": "Too many requests. Please slow down.",
  "retry_after": 60
}
```

## Configuration Location

- **Service Provider**: `app/Providers/RateLimitServiceProvider.php`
- **Middleware**: `app/Http/Middleware/RateLimitMiddleware.php`
- **Bootstrap**: `bootstrap/app.php`
- **Route Definitions**: `routes/web.php`, `routes/api.php`

## Testing Rate Limits

### Using cURL:
```bash
# Test login rate limit (should fail after 5 attempts)
for i in {1..6}; do
  curl -X POST http://localhost/login \
    -H "Content-Type: application/json" \
    -d '{"email":"test@example.com","password":"wrong"}' \
    -v
done
```

### Using Postman:
1. Create a request to a rate-limited endpoint
2. Use the Collection Runner to run it multiple times
3. Observe the 429 response when limit is exceeded

## Monitoring

### Laravel Logs
Rate limit violations are logged automatically. Check:
- `storage/logs/laravel.log`

### Custom Monitoring
You can add custom monitoring in `RateLimitServiceProvider.php`:

```php
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(5)
        ->by($request->input('email') ?: $request->ip())
        ->response(function (Request $request, array $headers) {
            // Log to monitoring service
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'endpoint' => $request->path()
            ]);
            
            return response()->json([...], 429);
        });
});
```

## Adjusting Rate Limits

To adjust a rate limit, edit `app/Providers/RateLimitServiceProvider.php`:

```php
// Change from 5 per minute to 10 per minute
RateLimiter::for('auth', function (Request $request) {
    return Limit::perMinute(10)  // Changed from 5
        ->by($request->input('email') ?: $request->ip());
});

// Change from per minute to per hour
RateLimiter::for('submissions', function (Request $request) {
    return Limit::perHour(50)  // Changed from 20
        ->by($request->user()?->id ?: $request->ip());
});
```

## Bypassing Rate Limits (Development Only)

To bypass rate limits in local development, modify `config/app.php`:

```php
'rate_limiting_enabled' => env('RATE_LIMITING_ENABLED', true),
```

Then in `.env`:
```
RATE_LIMITING_ENABLED=false
```

## Security Best Practices

1. **Never disable rate limiting in production**
2. **Monitor rate limit violations** - Frequent violations may indicate attacks
3. **Adjust limits based on actual usage patterns**
4. **Use stricter limits for sensitive operations** (auth, password changes)
5. **Use more generous limits for read operations**
6. **Consider using Redis** for better performance in production:

```php
// In config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),
```

## Cache Configuration

Rate limiting uses Laravel's cache. For better performance in production:

1. Install Redis:
```bash
composer require predis/predis
```

2. Update `.env`:
```
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

3. Rate limits will automatically use Redis for storage

## Common Issues

### Issue: Legitimate users getting rate limited
**Solution**: Increase the limit for that specific tier

### Issue: Rate limits not working
**Solution**: 
1. Check if cache is configured correctly
2. Clear cache: `php artisan cache:clear`
3. Ensure RateLimitServiceProvider is registered in `bootstrap/app.php`

### Issue: Different responses between web and API
**Solution**: Web routes return HTML error pages, API routes return JSON. This is intentional.

## References

- [Laravel Rate Limiting Documentation](https://laravel.com/docs/routing#rate-limiting)
- [OWASP API Security Top 10 - API4:2023 Unrestricted Resource Consumption](https://owasp.org/API-Security/editions/2023/en/0xa4-unrestricted-resource-consumption/)
