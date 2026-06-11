# ⚠️ URGENT: Rate Limiting Not Working - Fix Required

## Problem
Rate limiting is not working on your deployed site because the cache driver is not configured.

## Root Cause
Laravel's rate limiting requires a cache driver to store rate limit counters. Your production environment is missing:
```
CACHE_DRIVER=database
```

## ✅ Solution

### Step 1: Add Environment Variables in Vercel

Go to your Vercel dashboard and add these environment variables:

1. Go to: https://vercel.com/[your-project]/settings/environment-variables
2. Add the following variables:

```
CACHE_DRIVER=database
CACHE_PREFIX=campfix_cache
```

### Step 2: Run Migration (If Not Already Run)

The cache table migration should already exist, but verify by checking your database for these tables:
- `cache`
- `cache_locks`

If they don't exist, you need to run migrations on your production database.

### Step 3: Redeploy

After adding the environment variables:
1. Go to Vercel Deployments
2. Click on the latest deployment
3. Click "Redeploy" button
4. Wait for deployment to complete

### Step 4: Clear Cache (Optional)

If you have access to run artisan commands on Vercel, run:
```bash
php artisan cache:clear
php artisan config:clear
```

## Testing After Fix

Once deployed with the cache configuration:

1. **Test Submission Limit**:
   - Try to submit 6 concerns
   - The 6th submission should be blocked with error message:
     ```
     "You have reached your daily submission limit of 5. Please try again tomorrow."
     ```

2. **Test Login Limit**:
   - Try to login with wrong password 6 times
   - After 5 attempts, you should see:
     ```
     "Too many login attempts. Please try again later."
     ```

## Alternative: Use Redis (Recommended for Production)

For better performance, consider using Redis:

### Option A: Vercel KV (Redis)
1. Go to Storage tab in Vercel
2. Create a KV Database
3. Connect it to your project
4. Update environment variables:
   ```
   CACHE_DRIVER=redis
   REDIS_URL=[provided by Vercel KV]
   ```

### Option B: External Redis (Upstash, Redis Labs)
1. Sign up for Upstash: https://upstash.com/
2. Create a Redis database
3. Get connection details
4. Add to Vercel environment variables:
   ```
   CACHE_DRIVER=redis
   REDIS_HOST=[your-host]
   REDIS_PASSWORD=[your-password]
   REDIS_PORT=6379
   ```

## Why Database Cache Works

Using `CACHE_DRIVER=database`:
- ✅ No additional services needed
- ✅ Works with existing PostgreSQL database
- ✅ Sufficient for most applications
- ✅ Free (uses existing database)
- ⚠️ Slightly slower than Redis
- ⚠️ Adds some load to database

## Current Rate Limits (After Fix)

Once cache is configured, these limits will be active:

| Operation | Limit | Period |
|-----------|-------|--------|
| Login attempts | 5 | per minute |
| OTP verification | 3 | per minute |
| Concern submissions | 5 | per day |
| Report submissions | 5 | per day |
| Event submissions | 5 | per day |
| File uploads | 5 | per day |
| Password changes | 10 | per hour |
| Delete operations | 30 | per hour |
| Batch operations | 10 | per hour |
| Exports (PDF/CSV) | 20 | per hour |

## Verification Checklist

- [ ] Added `CACHE_DRIVER=database` to Vercel environment variables
- [ ] Added `CACHE_PREFIX=campfix_cache` to Vercel environment variables
- [ ] Redeployed application
- [ ] Verified cache tables exist in database
- [ ] Tested submission limit (should block after 5)
- [ ] Tested login limit (should block after 5)
- [ ] Checked logs for rate limit events

## Expected Error Messages

### When limit is exceeded (API/JSON):
```json
{
  "error": "You have reached your daily submission limit of 5. Please try again tomorrow.",
  "retry_after": 86400,
  "retry_after_hours": 24
}
```

### When limit is exceeded (Web):
User will see the custom 429 error page with explanation.

## Support

If rate limiting still doesn't work after adding cache configuration:

1. Check Vercel deployment logs
2. Verify environment variables are set correctly
3. Check database for cache tables
4. Try clearing browser cache
5. Test with a different user account

## Quick Fix Summary

**Add to Vercel Environment Variables:**
```
CACHE_DRIVER=database
CACHE_PREFIX=campfix_cache
```

**Then redeploy!**

---

**Last Updated**: June 11, 2026
**Status**: Awaiting Vercel configuration update
