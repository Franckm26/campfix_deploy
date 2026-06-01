# OneSignal Quick Start - 5 Minutes Setup

## Step 1: Create OneSignal Account (2 min)
1. Go to https://onesignal.com/
2. Sign up free
3. Create new app: **CampFix**
4. Select **Web Push**

## Step 2: Configure Web Push (1 min)
- Site Name: `CampFix`
- Site URL: `https://yourdomain.com` (or `http://localhost` for local)
- Icon URL: `https://yourdomain.com/favicon.ico`
- Click **Save**

## Step 3: Get API Keys (1 min)
Go to **Settings** → **Keys & IDs**, copy:
- App ID
- REST API Key

## Step 4: Add to .env (1 min)
```env
ONESIGNAL_APP_ID=your_app_id_here
ONESIGNAL_REST_API_KEY=your_rest_api_key_here
```

## Step 5: Test (30 seconds)
1. Reload your app
2. Click "Allow" when prompted
3. Go to Settings → Enable Push Notifications
4. Create a test concern

## ✅ Done!

Notifications now work in background!

## 🔍 Verify It Works

**In OneSignal Dashboard:**
- Go to **Audience** → **All Users**
- You should see yourself subscribed
- External User ID = Your Laravel user ID

**In Your App:**
- Create a concern and assign it
- Notification appears even if browser is closed!

## 🚀 Deploy to Production

**Vercel:**
1. Add env vars in Vercel dashboard
2. Update OneSignal site URL to your Vercel domain
3. Deploy

**Other Platforms:**
1. Add env vars to your hosting platform
2. Update OneSignal site URL
3. Deploy

## 📚 Full Documentation

See `ONESIGNAL_SETUP.md` for complete guide.

## 🆘 Troubleshooting

**Not receiving notifications?**
1. Check browser allowed notifications
2. Check user enabled push in Settings
3. Check OneSignal dashboard shows user subscribed
4. Check Laravel logs: `storage/logs/laravel.log`

**Permission prompt not showing?**
- Clear browser cache and reload
- Check browser console for errors
- Verify ONESIGNAL_APP_ID in .env

## 💡 Pro Tip

OneSignal dashboard shows:
- Who's subscribed
- Delivery rates
- Click rates
- User engagement

Much better than Firebase! 🎉
