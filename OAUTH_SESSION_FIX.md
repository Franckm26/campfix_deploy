# OAuth Session Fix - Redirect to Homepage Issue

## Problem Identified

After Microsoft OAuth login, users were redirected to homepage instead of dashboard because:

1. **SameSite Cookie Setting**: `SESSION_SAME_SITE=strict` blocks cookies during OAuth redirects
2. **Session Not Saving**: Session wasn't being explicitly saved after login

## Solution Applied

### Changes Made:

1. ✅ Changed `SESSION_SAME_SITE` from `strict` to `lax` in `config/session.php`
2. ✅ Added explicit `session()->save()` after login
3. ✅ Enhanced error logging with more details
4. ✅ Added better error handling for OAuth state exceptions

### Files Modified:

- `app/Http/Controllers/AuthController.php`
- `config/session.php`

### Git Commits:

- `d7bf1b7` - Fix OAuth session persistence
- `695097d` - Fix Microsoft OAuth authentication

---

## What Changed in Code:

### 1. Session Configuration (`config/session.php`)

**Before:**
```php
'same_site' => env('SESSION_SAME_SITE', 'strict'),
```

**After:**
```php
'same_site' => env('SESSION_SAME_SITE', 'lax'),
```

**Why**: `strict` blocks cookies during cross-site redirects (OAuth callback). `lax` allows cookies during safe redirects.

### 2. OAuth Callback (`AuthController.php`)

**Added:**
```php
// Force session save
session()->save();
```

**Why**: Ensures session is persisted to database before redirect.

---

## Testing After Deployment:

### Step 1: Wait for Vercel Deployment

Check Vercel dashboard - wait for deployment to complete.

### Step 2: Clear Browser Cookies

1. Open browser DevTools (F12)
2. Go to Application → Cookies
3. Clear cookies for `www.campfixsti.com`

### Step 3: Test OAuth Login

1. Visit: https://www.campfixsti.com
2. Click "Sign in with Microsoft"
3. Login with Microsoft account
4. **Expected**: Redirect to dashboard (not homepage)

### Step 4: Verify Session

After login, check browser DevTools:
- Application → Cookies → Should see `campfix-session` cookie
- Console → No errors

---

## If Still Not Working:

### Check 1: Vercel Environment Variables

Ensure these are set in Vercel:

```
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
```

### Check 2: Database Sessions Table

```sql
SELECT * FROM sessions WHERE user_id IS NOT NULL ORDER BY last_activity DESC LIMIT 5;
```

Should see new session entries after OAuth login.

### Check 3: Vercel Logs

1. Go to Vercel Dashboard → Your Project → Logs
2. Look for after OAuth login:
   - "Microsoft user data retrieved"
   - "User logged in via OAuth"
   - Check if authenticated=true

---

## Alternative: Use Cookie Driver (If Database Sessions Don't Work on Vercel)

If database sessions cause issues on Vercel, switch to cookie driver:

**Add to Vercel Environment Variables:**
```
SESSION_DRIVER=cookie
SESSION_SAME_SITE=lax
```

Cookie driver stores session in encrypted cookie (no database needed).

---

## Technical Explanation:

### SameSite Cookie Attribute:

- **`strict`**: Cookie sent only to same site (blocks OAuth redirects) ❌
- **`lax`**: Cookie sent on safe cross-site redirects (allows OAuth) ✅
- **`none`**: Cookie sent everywhere (requires HTTPS + Secure flag)

### OAuth Flow:

1. User clicks "Sign in with Microsoft"
2. Redirects to `login.microsoftonline.com` (different site)
3. Microsoft redirects back with auth code
4. **Problem**: With `strict`, session cookie not sent on step 3
5. **Solution**: With `lax`, session cookie sent on safe redirect

---

## Production Deployment Checklist:

- [x] Code pushed to GitHub
- [ ] Wait for Vercel automatic deployment
- [ ] Add `SESSION_SAME_SITE=lax` to Vercel env vars (optional, uses default)
- [ ] Clear browser cookies
- [ ] Test OAuth login
- [ ] Verify redirect to dashboard
- [ ] Check user role is preserved

---

## Success Criteria:

✅ User clicks "Sign in with Microsoft"  
✅ Redirects to Microsoft login  
✅ After login, redirects back to campfixsti.com  
✅ User is logged in (sees dashboard, not homepage)  
✅ User role and permissions preserved  
✅ Profile picture synced from Microsoft  

---

## Fallback Plan:

If still not working after this fix:

1. Switch to cookie-based sessions (instead of database)
2. Add explicit session configuration in Vercel
3. Check if Vercel is blocking session writes to database

---

**Deployed**: June 11, 2026  
**Commits**: d7bf1b7, 695097d  
**Status**: ⏳ Awaiting Vercel deployment and testing
