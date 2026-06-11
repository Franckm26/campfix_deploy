# Microsoft OAuth 2.0 - Quick Start Guide

## 🚀 Quick Setup (5 Minutes)

### 1. Register App in Azure (2 minutes)
1. Go to https://portal.azure.com
2. Navigate to: **Azure Active Directory** → **App registrations** → **New registration**
3. Fill in:
   - Name: `Campfix`
   - Account types: `Accounts in any organizational directory and personal Microsoft accounts`
   - Redirect URI: `http://localhost/auth/microsoft/callback`
4. Click **Register**

### 2. Get Credentials (1 minute)
1. Copy **Application (client) ID** from overview page
2. Go to **Certificates & secrets** → **New client secret**
3. Copy the **secret value** immediately (shown only once!)

### 3. Configure App (2 minutes)
Edit `.env` file:

```env
MICROSOFT_CLIENT_ID=paste_your_client_id_here
MICROSOFT_CLIENT_SECRET=paste_your_secret_here
MICROSOFT_REDIRECT_URI="${APP_URL}/auth/microsoft/callback"
MICROSOFT_TENANT_ID=common
```

Clear cache:
```bash
php artisan config:clear
```

### 4. Test It! ✅
1. Go to your app homepage
2. Click **Login**
3. Click **Sign in with Microsoft**
4. Done! 🎉

---

## 📋 Production Checklist

Before deploying to production:

- [ ] Update redirect URI to production URL in `.env`
- [ ] Add production redirect URI in Azure Portal → Authentication
- [ ] Ensure APP_URL is set to production domain
- [ ] Test OAuth flow on production
- [ ] Use HTTPS (required for production)

**Production .env example:**
```env
APP_URL=https://www.campfixsti.com
MICROSOFT_REDIRECT_URI=https://www.campfixsti.com/auth/microsoft/callback
```

---

## 🔧 Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| "Invalid client secret" | Check `MICROSOFT_CLIENT_SECRET` in `.env` |
| "Redirect URI mismatch" | Add exact URI in Azure Portal → Authentication |
| "Application not found" | Verify `MICROSOFT_CLIENT_ID` is correct |
| Button not showing | Clear browser cache, check JavaScript enabled |

---

## 💰 Cost

✅ **100% FREE** - No credit card needed!

Azure AD Free Tier includes 50,000 monthly active users.

---

## 📚 Full Documentation

See `MICROSOFT_OAUTH_SETUP.md` for detailed instructions.

---

**Need Help?**
- Check logs: `storage/logs/laravel.log`
- Azure Portal: https://portal.azure.com
- Laravel Socialite: https://laravel.com/docs/socialite
