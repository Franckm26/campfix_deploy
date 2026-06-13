# Vercel Environment Variables Setup

## Add These to Your Vercel Dashboard

Go to: https://vercel.com/your-project/settings/environment-variables

Add these 4 environment variables:

### 1. MICROSOFT_CLIENT_ID
```
bf0facf2-f1d8-418c-8d55-43a98a9ce3d5
```

### 2. MICROSOFT_CLIENT_SECRET
```
05b44b48-ce54-40d5-90c0-6f7ecdfa5f4e
```

### 3. MICROSOFT_REDIRECT_URI
```
https://www.campfixsti.com/auth/microsoft/callback
```

### 4. MICROSOFT_TENANT_ID
```
common
```

## Steps:

1. Go to Vercel Dashboard
2. Select your Campfix project
3. Go to **Settings** → **Environment Variables**
4. Click **Add New**
5. Add each variable above (Name and Value)
6. Select **Production, Preview, and Development** for each
7. Click **Save**
8. **Redeploy** your application

## After Adding Variables:

Your Microsoft OAuth will work on production at:
https://www.campfixsti.com

Users can click "Sign in with Microsoft" and authenticate!

---

**⚠️ IMPORTANT**: After adding these variables, you MUST redeploy your application for the changes to take effect.
