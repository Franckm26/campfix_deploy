# Microsoft OAuth Setup - Final Checklist

## ✅ Completed

- [x] Installed Laravel Socialite packages
- [x] Created Azure app registration
- [x] Got Client ID: `bf0facf2-f1d8-418c-8d55-43a98a9ce3d5`
- [x] Got Client Secret: `05b44b48-ce54-40d5-90c0-6f7ecdfa5f4e`
- [x] Updated local `.env` file
- [x] Updated `.env.vercel` file
- [x] Cleared Laravel cache

## 🔄 Remaining Tasks

### 1. Add Localhost Redirect URI in Azure (Optional - for local testing)

1. Go to Azure Portal: https://portal.azure.com
2. Navigate to your Campfix app
3. Click **Authentication** in left sidebar
4. Under "Platform configurations" → "Web"
5. Click **Add URI**
6. Add: `http://localhost/auth/microsoft/callback`
7. Click **Save**

### 2. Add Environment Variables to Vercel (Required)

1. Go to: https://vercel.com/dashboard
2. Select your Campfix project
3. Go to **Settings** → **Environment Variables**
4. Add these 4 variables:

   | Name | Value |
   |------|-------|
   | `MICROSOFT_CLIENT_ID` | `bf0facf2-f1d8-418c-8d55-43a98a9ce3d5` |
   | `MICROSOFT_CLIENT_SECRET` | `05b44b48-ce54-40d5-90c0-6f7ecdfa5f4e` |
   | `MICROSOFT_REDIRECT_URI` | `https://www.campfixsti.com/auth/microsoft/callback` |
   | `MICROSOFT_TENANT_ID` | `common` |

5. For each variable, select: **Production, Preview, and Development**
6. Click **Save** for each

### 3. Redeploy on Vercel

After adding environment variables:
1. Go to **Deployments** tab
2. Click the **...** menu on latest deployment
3. Click **Redeploy**
4. Or push a new commit to trigger deployment

### 4. Test It!

**Local Testing:**
1. Make sure XAMPP is running
2. Visit: http://localhost
3. Click "Login"
4. Click "Sign in with Microsoft"
5. You should be redirected to Microsoft login

**Production Testing:**
1. Visit: https://www.campfixsti.com
2. Click "Login"
3. Click "Sign in with Microsoft"
4. Authenticate with your Microsoft account
5. You should be redirected back and logged in!

---

## 📋 Quick Commands

```bash
# Clear cache (already done)
php artisan config:clear
php artisan cache:clear

# Test routes are registered
php artisan route:list --name=auth.microsoft

# Start local server
php artisan serve
```

---

## 🐛 Troubleshooting

### If OAuth button doesn't work locally:
```bash
php artisan config:clear
# Refresh browser
```

### If OAuth button doesn't work on production:
1. Check Vercel environment variables are saved
2. Redeploy the application
3. Check Azure redirect URI matches exactly

### If you get "Redirect URI mismatch" error:
1. Check the exact URL in the error message
2. Add that exact URL to Azure Portal → Authentication

---

## 🎉 Success Criteria

Microsoft OAuth is working when:
- ✅ Users can click "Sign in with Microsoft"
- ✅ Users are redirected to Microsoft login page
- ✅ After login, users are redirected back to your app
- ✅ Users are logged in and see dashboard

---

## 📞 Need Help?

Check these docs:
- `MICROSOFT_OAUTH_SETUP.md` - Full setup guide
- `MICROSOFT_OAUTH_QUICK_START.md` - Quick reference
- `VERCEL_OAUTH_SETUP.md` - Vercel-specific instructions
- Laravel logs: `storage/logs/laravel.log`
