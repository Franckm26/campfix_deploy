# 🚀 Deploy OAuth Fix - Simple Guide

## What Was Fixed

Changed `vercel.json` to use database sessions instead of cookie sessions.

**File**: `vercel.json` (line 191)  
**Change**: `"SESSION_DRIVER": "cookie"` → `"SESSION_DRIVER": "database"`

---

## Deployment Steps

### Step 1: Push to Git (30 seconds)

Open your terminal in the project folder and run:

```bash
git add vercel.json
git commit -m "Fix: Change SESSION_DRIVER to database for OAuth persistence"
git push origin main
```

### Step 2: Wait for Vercel (1-2 minutes)

Vercel will automatically detect the push and deploy. You'll see the deployment progress at:
- https://vercel.com/dashboard (your project)

Wait until you see the green checkmark ✅

### Step 3: Clear Your Browser (10 seconds)

**Option A - Clear Cookies:**
1. Press F12 (open DevTools)
2. Go to Application → Cookies
3. Click on `www.campfixsti.com`
4. Click "Clear all cookies" or delete them individually
5. Close DevTools

**Option B - Use Incognito:**
- Chrome: Ctrl+Shift+N
- Firefox: Ctrl+Shift+P
- Edge: Ctrl+Shift+N

### Step 4: Test OAuth (1 minute)

1. Go to: https://www.campfixsti.com/
2. Click **"Sign in with Microsoft"**
3. Login with your Microsoft account
4. **Expected**: You should be redirected to the dashboard ✅
5. **Previous behavior**: Was redirected to homepage ❌

### Step 5: Verify (Optional)

Visit: https://www.campfixsti.com/test-oauth-status

Should show:
```json
{
  "session_driver": "database",    // ✅ Changed from "cookie"
  "authenticated": true,            // ✅ You're logged in
  "user_id": 123,                   // ✅ Your user ID
  "user_role": "student"            // ✅ Your role
}
```

---

## Commands Summary

```bash
# 1. Add the fixed file
git add vercel.json

# 2. Commit with message
git commit -m "Fix: Change SESSION_DRIVER to database for OAuth persistence"

# 3. Push to deploy
git push origin main
```

---

## What Happens After Deploy

✅ Microsoft OAuth login will work correctly  
✅ Users will be redirected to dashboard after login  
✅ Sessions will persist across page navigation  
✅ Database sessions will be stored in Supabase  
✅ Existing accounts will merge with Microsoft accounts  
✅ Roles and permissions will be preserved  

---

## If Something Goes Wrong

### OAuth still not working?
1. Check Vercel deployment completed successfully
2. Clear cookies completely (or use incognito)
3. Check browser console (F12) for errors
4. Check Vercel logs for PHP errors

### Still seeing "cookie" instead of "database"?
1. Make sure you pushed the changes: `git log -1`
2. Check Vercel deployed the latest commit
3. Hard refresh: Ctrl+Shift+R (or Cmd+Shift+R on Mac)

### Getting redirected to homepage?
1. Make sure deployment completed
2. Clear ALL cookies for the domain
3. Try a different browser or incognito mode
4. Check `/test-oauth-status` endpoint

---

## Success Checklist

- [ ] Pushed `vercel.json` changes to Git
- [ ] Vercel deployment completed (green checkmark)
- [ ] Cleared browser cookies OR using incognito
- [ ] Clicked "Sign in with Microsoft"
- [ ] Successfully redirected to dashboard
- [ ] Can navigate to other pages while staying logged in
- [ ] Role-based features work correctly

---

## Quick Test Commands

### Check Git status
```bash
git status
```

### View last commit
```bash
git log -1
```

### Check remote repository
```bash
git remote -v
```

---

**That's it!** Just 3 commands to deploy the fix. After Vercel deploys, OAuth login will work perfectly! 🎉
