# 🚀 QUICK FIX: OAuth Session Not Persisting

## The Problem
After Microsoft OAuth login, you're redirected to homepage instead of dashboard. Session not persisting.

## The Solution (3 Steps)

### 1️⃣ Delete SESSION_DRIVER from Vercel
- Go to Vercel Dashboard → Settings → Environment Variables
- Find `SESSION_DRIVER`
- Click **⋮** → **Remove** (don't just change - DELETE it!)

### 2️⃣ Redeploy Without Cache
- Vercel Dashboard → Deployments
- Click **⋮** → **Redeploy**
- **Uncheck** "Use existing Build Cache"
- Click **Redeploy**

### 3️⃣ Clear Browser Cookies & Test
- Open DevTools (F12) → Application → Cookies
- Delete ALL cookies for `www.campfixsti.com`
- OR use Incognito/Private window
- Go to https://www.campfixsti.com/
- Click "Sign in with Microsoft"
- Should redirect to dashboard ✅

## Verify It Worked
Visit: https://www.campfixsti.com/test-oauth-status

Should show:
```json
{
  "session_driver": "database",    // ✅ Was "cookie", now "database"
  "authenticated": true,            // ✅ Was false, now true
  "user_id": 123,                   // ✅ Your user ID shows up
  "user_role": "student"            // ✅ Your role preserved
}
```

## Why This Works
- Deleting (not changing) the env var forces Vercel to use your config file
- Config file (`config/session.php`) already has `'database'` as default
- Database sessions work on Vercel's serverless architecture
- Cookie sessions don't survive across different serverless functions

## If Still Not Working
1. Check `sessions` table exists in Supabase database
2. Verify `SESSION_CONNECTION=pgsql` is set in Vercel
3. Make sure you **deleted** (not changed) `SESSION_DRIVER`
4. Try completely different browser or device

---

**TL;DR**: Delete `SESSION_DRIVER` from Vercel → Redeploy → Clear cookies → Test
