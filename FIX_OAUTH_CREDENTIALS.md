# 🔑 Fix Microsoft OAuth Credentials - Invalid Client Secret

## ✅ Good News
The session driver is now "database"! The first issue is fixed.

## ❌ New Issue
You're getting: **"Invalid client secret provided"**

This means the Microsoft client secret has expired or is incorrect in Vercel.

---

## 🔧 How to Fix: Generate New Client Secret

### Step 1: Go to Azure Portal
1. Open: https://portal.azure.com/
2. Sign in with your Microsoft account

### Step 2: Navigate to Your App Registration
1. Click **"Azure Active Directory"** (or search for it)
2. Click **"App registrations"** in the left menu
3. Click **"All applications"**
4. Find and click **"Campfix"** (your app)

### Step 3: Generate a New Client Secret
1. In the left menu, click **"Certificates & secrets"**
2. Under **"Client secrets"**, you'll see your old secret
3. Click **"+ New client secret"**
4. Add a description: `Campfix Production Secret`
5. Set expiration: **24 months** (recommended)
6. Click **"Add"**

### Step 4: Copy the New Secret
⚠️ **CRITICAL**: Copy the secret **immediately** - you won't be able to see it again!

1. You'll see a new secret with a **Value** column
2. Click the **copy icon** next to the Value
3. Save it somewhere temporarily (e.g., Notepad)

**Format**: It will look like this:
```
abc~XYZ123def456GHI789jkl~012MNO345
```

---

## 📝 Update Vercel Environment Variables

### Step 1: Go to Vercel Dashboard
1. Open: https://vercel.com/dashboard
2. Select your **Campfix** project
3. Go to **Settings** → **Environment Variables**

### Step 2: Update MICROSOFT_CLIENT_SECRET
1. Find the variable: `MICROSOFT_CLIENT_SECRET`
2. Click the **pencil icon** (Edit)
3. Paste the new secret you copied from Azure
4. Make sure it's set for all environments:
   - ✅ Production
   - ✅ Preview
   - ✅ Development
5. Click **"Save"**

### Step 3: Verify Other Variables Are Set
While you're here, verify these are also set correctly:

```
MICROSOFT_CLIENT_ID = bf0facf2-f1d8-418c-8d55-43a98a9ce3d5
MICROSOFT_REDIRECT_URI = https://www.campfixsti.com/auth/microsoft/callback
MICROSOFT_TENANT_ID = common
```

---

## 🚀 Redeploy Vercel

After updating the secret:

### Option 1: Trigger Redeploy from Dashboard
1. In Vercel Dashboard → Your Project
2. Go to **Deployments** tab
3. Click **⋮** (3 dots) next to latest deployment
4. Click **"Redeploy"**
5. **Uncheck** "Use existing Build Cache"
6. Click **"Redeploy"**

### Option 2: Push a Small Change
Or just make a small change and push:
```bash
# Add a comment or space to any file
git add .
git commit -m "Trigger redeploy after updating OAuth secret"
git push origin master
```

---

## 🧪 Test After Redeployment

### Step 1: Wait for Deployment
Wait 1-2 minutes for Vercel to finish deploying.

### Step 2: Clear Browser Cookies
- Press F12 → Application → Cookies
- Delete all cookies for `www.campfixsti.com`
- OR use Incognito mode

### Step 3: Test OAuth Login
1. Go to: https://www.campfixsti.com/
2. Click **"Sign in with Microsoft"**
3. Complete authentication
4. **Expected**: Redirected to dashboard ✅
5. **Previous error**: "Invalid client secret" ❌

### Step 4: Verify Success
Visit: https://www.campfixsti.com/test-oauth-status

Should show:
```json
{
  "session_driver": "database",    ✅
  "authenticated": true,            ✅
  "user_id": 123,                   ✅
  "user_role": "student"            ✅
}
```

---

## 🔍 Alternative: Check Current Secret in Azure

If you want to verify what secret Azure has:

1. Go to Azure Portal → App registrations → Campfix
2. Click **"Certificates & secrets"**
3. Look at the **"Client secrets"** section
4. Check the **"Expires"** column
5. If it says "Expired" - generate a new one
6. If it's valid - copy the secret ID and compare with Vercel

⚠️ **Note**: You can't see the actual secret value after creation, only the ID. If you're not sure, just generate a new one.

---

## 📋 Quick Checklist

- [ ] Go to Azure Portal
- [ ] Navigate to Campfix app registration
- [ ] Go to "Certificates & secrets"
- [ ] Generate new client secret
- [ ] Copy the secret value immediately
- [ ] Go to Vercel Dashboard
- [ ] Update `MICROSOFT_CLIENT_SECRET` variable
- [ ] Save changes
- [ ] Redeploy Vercel
- [ ] Clear browser cookies
- [ ] Test OAuth login
- [ ] Verify at `/test-oauth-status`

---

## ⚠️ Common Mistakes

1. **Not copying secret immediately** - You can only see it once when creating it
2. **Copying secret ID instead of secret value** - Make sure you copy the Value column
3. **Not redeploying after updating** - Vercel needs to redeploy to pick up the new secret
4. **Not clearing cookies** - Old cookies can interfere with testing
5. **Setting secret only for Production** - Set it for all environments

---

## 💡 Why Did This Happen?

Client secrets in Azure have an expiration date (usually 6-24 months). Your secret either:
1. Expired naturally
2. Was regenerated/rotated
3. Was never set correctly in Vercel
4. Got overwritten or corrupted

This is normal security practice - secrets should be rotated periodically.

---

## 🎯 Summary

**Issue**: Invalid client secret (401 Unauthorized)  
**Cause**: Azure client secret expired or incorrect in Vercel  
**Fix**: Generate new secret in Azure → Update in Vercel → Redeploy  
**Time**: 5-10 minutes  
**Difficulty**: Easy (just follow the steps)  

---

## 📸 Need Help?

If you're stuck:
1. Take a screenshot of the Azure "Certificates & secrets" page
2. Take a screenshot of Vercel environment variables (blur the actual values)
3. Check Vercel deployment logs for any errors

The OAuth should work perfectly once the secret is updated! 🎉
