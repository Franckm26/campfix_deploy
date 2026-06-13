# Microsoft OAuth Debugging Guide

## Issue: Redirects to Login Instead of Dashboard

I've fixed the OAuth callback to properly:
1. ✅ Link Microsoft accounts to existing users
2. ✅ Preserve roles and permissions
3. ✅ Regenerate session properly
4. ✅ Add detailed logging

## What Was Fixed:

### 1. Added UUID Generation
New users created via OAuth now get a proper UUID (required field).

### 2. Session Regeneration
Added `request()->session()->regenerate()` to prevent session fixation.

### 3. Proper Field Initialization
Ensure all required fields are set:
- `failed_login_attempts` = 0
- `locked_until` = null
- `login_lockout_level` = 0

### 4. Enhanced Logging
Added detailed logs to track the OAuth flow.

### 5. Avatar Update
Now updates avatar for existing users too.

---

## How to Test:

### 1. Clear All Caches (Already Done)
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### 2. Check Logs
Before testing, open this file in a new terminal:
```bash
tail -f storage/logs/laravel.log
```

### 3. Test OAuth Login

**For Existing User:**
1. Make sure you have a user with email that matches your Microsoft account
2. Visit: http://localhost
3. Click "Sign in with Microsoft"
4. Login with your Microsoft account
5. Check logs - you should see "Existing user found" and "User logged in via OAuth"
6. You should be redirected to dashboard with your original role

**For New User:**
1. Use a Microsoft account NOT in your database
2. Visit: http://localhost
3. Click "Sign in with Microsoft"
4. Login with Microsoft
5. Check logs - you should see "New user created via Microsoft OAuth"
6. You should be redirected to dashboard with role=student

---

## Debug Checklist:

### If Still Redirecting to Login:

1. **Check Session Driver**
   ```bash
   # In .env, verify:
   SESSION_DRIVER=database
   ```

2. **Check Sessions Table**
   ```sql
   SELECT * FROM sessions ORDER BY last_activity DESC LIMIT 5;
   ```

3. **Check Auth After OAuth**
   Add this temporarily to `routes/web.php` after OAuth routes:
   ```php
   Route::get('/test-auth', function() {
       return response()->json([
           'authenticated' => auth()->check(),
           'user' => auth()->user(),
           'session_id' => session()->getId(),
       ]);
   });
   ```
   Visit: http://localhost/test-auth after OAuth login

4. **Check Middleware**
   ```bash
   php artisan route:list --name=dashboard
   ```
   Verify middleware is 'auth' not something else

---

## Common Issues and Solutions:

### Issue 1: Session Not Persisting
**Cause**: Session driver misconfiguration
**Solution**: 
```env
# In .env
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Issue 2: Auth Middleware Failing
**Cause**: Missing guard configuration
**Solution**: OAuth uses default 'web' guard which should work

### Issue 3: Redirect Loop
**Cause**: Dashboard route redirecting back
**Solution**: Check DashboardController@index for any redirects

---

## View Detailed Logs:

After attempting OAuth login, check:
```bash
tail -100 storage/logs/laravel.log
```

Look for these log entries:
- ✅ "Microsoft OAuth callback started"
- ✅ "Existing user found" OR "New user created"
- ✅ "User logged in via OAuth"
- ✅ "Redirecting to dashboard"

---

## Quick Test Command:

```bash
# Test if user was created/updated correctly
php artisan tinker
>>> User::where('microsoft_id', '!=', null)->latest()->first();
>>> auth()->check();
```

---

## If It Works:

You should see:
1. Microsoft login page
2. Redirect back to your app
3. Dashboard loads with your user profile
4. Your original role and permissions intact

---

## Next Steps After Fix Works:

1. Remove test route if added
2. Test on production (Vercel)
3. Verify roles are preserved
4. Test with multiple user types

---

**Last Updated**: June 11, 2026
