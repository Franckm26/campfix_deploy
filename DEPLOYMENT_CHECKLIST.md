# Rate Limiting Deployment Checklist

## Pre-Deployment

- [x] Rate Limit Service Provider created
- [x] Routes updated with rate limit middleware
- [x] Bootstrap configured
- [x] Error page created (429.blade.php)
- [x] Documentation written
- [ ] Local testing completed
- [ ] Staging deployment tested
- [ ] Production deployment approved

## Testing Steps

### 1. Local Environment Testing

```bash
# Clear cache
php artisan cache:clear

# Test application boots
php artisan about

# Run test script (Windows)
.\test-rate-limits-simple.bat

# OR Run PowerShell test (Windows)
.\test-rate-limiting.ps1
```

### 2. Manual Testing Checklist

Test each rate limit tier:

#### Authentication (5 per minute)
- [ ] Login endpoint (`/login`)
- [ ] API login endpoint (`/api/login`)
- [ ] Verify lockout after 5 attempts
- [ ] Verify unlock after 1 minute

#### OTP (3 per minute)
- [ ] OTP delivery (`/otp-delivery`)
- [ ] OTP resend (`/resend-otp`)
- [ ] Verify lockout after 3 attempts
- [ ] Verify unlock after 1 minute

#### Submissions (20 per hour)
- [ ] Concern submission (`/submit-concern`)
- [ ] Report submission (`/reports`)
- [ ] Event submission (`/events`)
- [ ] Verify lockout after 20 submissions
- [ ] Verify unlock after 1 hour

#### Deletes (30 per hour)
- [ ] Concern delete
- [ ] Report delete
- [ ] Event delete
- [ ] Verify lockout after 30 deletes

#### Batch Operations (10 per hour)
- [ ] Batch archive
- [ ] Batch delete
- [ ] Batch restore
- [ ] Verify lockout after 10 operations

#### Exports (20 per hour)
- [ ] CSV export
- [ ] PDF export
- [ ] Analytics export
- [ ] Verify lockout after 20 exports

### 3. Verify Error Responses

- [ ] 429 status code returned
- [ ] JSON error message for API endpoints
- [ ] HTML error page for web endpoints
- [ ] `Retry-After` header included
- [ ] Clear error message displayed

### 4. Check Logs

```bash
# View logs in real-time
php artisan pail

# OR check log file
type storage\logs\laravel.log
```

Look for:
- Rate limit exceeded events
- No unexpected errors
- Proper rate limiter identification

## Staging Deployment

### Before Deployment
- [ ] Backup database
- [ ] Review staging environment configuration
- [ ] Ensure `.env` is properly configured
- [ ] Check cache driver (recommend Redis for staging/production)

### Deployment Steps
```bash
# Pull latest code
git pull origin main

# Install/update dependencies
composer install --no-dev --optimize-autoloader

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (if any)
php artisan migrate --force
```

### Post-Deployment Testing
- [ ] Test authentication rate limits
- [ ] Test submission rate limits
- [ ] Test API endpoints
- [ ] Monitor logs for 24 hours
- [ ] Check for false positives (legitimate users blocked)

## Production Deployment

### Pre-Production Checklist
- [ ] Staging tested successfully for 1 week minimum
- [ ] No rate limit issues found in staging
- [ ] Performance impact assessed
- [ ] Monitoring/alerting configured
- [ ] Rollback plan prepared

### Redis Configuration (Recommended)
```bash
# Install Redis (if not already)
composer require predis/predis
```

Update `.env`:
```
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Deployment Steps
```bash
# Same as staging deployment
# + additional monitoring setup
```

### Monitoring Setup
- [ ] Set up alerts for high 429 response rates
- [ ] Monitor average response times
- [ ] Track rate limit violations by endpoint
- [ ] Set up dashboard for rate limit metrics

### Post-Deployment (First 24 Hours)
- [ ] Monitor logs continuously
- [ ] Check for unexpected 429 responses
- [ ] Verify no performance degradation
- [ ] Collect user feedback
- [ ] Review rate limit hit patterns

### Post-Deployment (First Week)
- [ ] Analyze rate limit violation patterns
- [ ] Adjust limits if needed (based on data)
- [ ] Document any changes made
- [ ] Review with team

## Rollback Plan

If issues occur:

```bash
# Quick rollback
git revert <commit-hash>
php artisan cache:clear
php artisan config:cache
php artisan route:cache

# Emergency: Disable rate limiting temporarily
# Edit RateLimitServiceProvider.php and increase all limits 10x
```

## Configuration Adjustments

If legitimate users are getting rate limited:

1. Check logs for patterns
2. Identify problematic tier
3. Edit `app/Providers/RateLimitServiceProvider.php`
4. Increase limit for that tier
5. Deploy change
6. Monitor

Example:
```php
// BEFORE
RateLimiter::for('submissions', function (Request $request) {
    return Limit::perHour(20)
        ->by($request->user()?->id ?: $request->ip());
});

// AFTER (increase to 50)
RateLimiter::for('submissions', function (Request $request) {
    return Limit::perHour(50)
        ->by($request->user()?->id ?: $request->ip());
});
```

## Performance Optimization

### With Redis (Recommended)
- Rate limiting operations are O(1)
- Scales horizontally
- Shared across application instances
- Better for production

### Without Redis (File Cache)
- Rate limiting uses file cache
- Works for single-server setups
- May be slower under high load
- Good for small deployments

## Success Metrics

After 1 month in production:

- [ ] No legitimate users reporting blocks
- [ ] Brute force attempts successfully blocked
- [ ] System resource usage stable
- [ ] No performance degradation
- [ ] Rate limits adjusted based on real usage

## Documentation Updates

- [ ] Update API documentation with rate limits
- [ ] Add rate limit info to user guide
- [ ] Document any custom adjustments made
- [ ] Update team wiki/knowledge base

## Sign-Off

- [ ] Developer tested: _________________ Date: _______
- [ ] QA approved: _________________ Date: _______
- [ ] Security reviewed: _________________ Date: _______
- [ ] Production deployed: _________________ Date: _______

## Notes

Use this space to document any issues, adjustments, or observations:

___________________________________________________________
___________________________________________________________
___________________________________________________________
___________________________________________________________
