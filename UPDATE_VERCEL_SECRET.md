# 🔑 Update Microsoft Client Secret in Vercel

## New Secret Created
Your new Microsoft client secret is: `29aa845d-df8d-44bf-abae-39b852045a30`

I've updated the `.env.vercel` file locally, but you MUST also update it in the Vercel dashboard.

---

## ⚠️ IMPORTANT: Update Vercel Dashboard

### Step 1: Update Secret in Vercel
1. Go to: https://vercel.com/dashboard
2. Select your **Campfix** project
3. Click **Settings** → **Environment Variables**
4. Find `MICROSOFT_CLIENT_SECRET`
5. Click **Edit** (pencil icon) or **Remove** then **Add New**
6. Paste the new value: `29aa845d-df8d-44bf-abae-39b852045a30`
7. Make sure to select all environments: **Production**, **Preview**, **Development**
8. Click **Save**

### Step 2: Redeploy Vercel
1. Go to **Deployments** tab
2. Click **⋮** (three dots) on latest deployment
3. Click **Redeploy**
4. **Uncheck** "Use existing Build Cache"
5. Click **Redeploy**
6. Wait for deployment to complete (1-2 minutes)

---

## 🧪 Test After Deployment

### Before Testing:
**Clear browser cookies** or use incognito mode!

### Test Steps:
1. Go to: https://www.campfixsti.com/
2. Click **"Sign in with Microsoft"**
3. Complete Microsoft authentication
4. Should redirect to **dashboard** ✅

### Verify:
Visit: https://www.campfixsti.com/test-oauth-status

Should show:
```json
{
  "session_driver": "database",
  "authenticated": true,
  "user_id": 123,
  "user_role": "student"
}
```

---

## 📋 Updated Configuration

### Microsoft OAuth Credentials:
- **Client ID**: `bf0facf2-f1d8-418c-8d55-43a98a9ce3d5` (unchanged)
- **Client Secret**: `29aa845d-df8d-44bf-abae-39b852045a30` ✅ (NEW)
- **Redirect URI**: `https://www.campfixsti.com/auth/microsoft/callback` (unchanged)
- **Tenant**: `common` (unchanged)

### Files Updated Locally:
- `.env.vercel` - Updated with new secret

### Still Need to Update:
- ⚠️ **Vercel Dashboard** - Environment Variables (DO THIS NOW!)

---

## 🚀 Quick Steps Summary

1. ✅ Created new secret in Azure
2. ✅ Updated `.env.vercel` locally
3. ⏳ **Update Vercel dashboard** (YOU NEED TO DO THIS)
4. ⏳ Redeploy Vercel
5. ⏳ Clear cookies and test

---

## ⚠️ Don't Forget!

The new secret `29aa845d-df8d-44bf-abae-39b852045a30` must be added to:
1. **Vercel Environment Variables** ← DO THIS NOW
2. Then redeploy

Without updating Vercel dashboard, it will still use the old expired secret!

---

**Next Step**: Go to Vercel dashboard and update `MICROSOFT_CLIENT_SECRET` with the new value!
