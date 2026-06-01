# 🔔 Push Notifications with OneSignal

CampFix now supports **background push notifications** using OneSignal!

## ✨ What's New

Users will receive real-time notifications even when:
- ❌ Browser is closed
- ❌ App is minimized
- ❌ User is on a different tab
- ❌ Computer is locked (on some browsers)

## 🚀 Quick Setup

### For Developers

1. **Create OneSignal account**: https://onesignal.com/
2. **Create new app** → Select "Web Push"
3. **Get API keys** from Settings → Keys & IDs
4. **Add to .env**:
   ```env
   ONESIGNAL_APP_ID=your_app_id
   ONESIGNAL_REST_API_KEY=your_api_key
   ```
5. **Done!** No database migrations needed.

📖 **Full guide**: See `ONESIGNAL_SETUP.md`  
⚡ **Quick start**: See `ONESIGNAL_QUICK_START.md`

### For Users

1. **Allow notifications** when prompted
2. **Enable in Settings** → Notifications → Push Notifications
3. **Done!** You'll receive notifications even when app is closed

## 📱 Notifications You'll Receive

- 🔧 **Concern Assigned** - When a concern is assigned to you
- ✅ **Concern Resolved** - When your concern is resolved
- 📋 **Event Request Update** - When your event is approved/rejected
- 🆕 **New Event Request** - When someone requests an event (for approvers)
- 📊 **Report Updates** - When reports are assigned or resolved

## 🎯 Features

- ✅ Works in background (browser closed)
- ✅ Click to open relevant page
- ✅ User preference controls
- ✅ Multi-device support
- ✅ Beautiful analytics dashboard
- ✅ No database required
- ✅ Free tier (10,000 subscribers)

## 🔧 Technical Details

### Architecture

```
Laravel App → OneSignal API → User's Browser
     ↓
Notification Class
     ↓
OneSignalChannel
     ↓
OneSignal REST API
     ↓
User receives notification
```

### Files Added

- `app/Channels/OneSignalChannel.php` - Custom notification channel
- `app/Notifications/TestPushNotification.php` - Test notification
- `ONESIGNAL_SETUP.md` - Complete setup guide
- `ONESIGNAL_QUICK_START.md` - 5-minute quick start

### Files Modified

- `app/Notifications/*Notification.php` - Added `toOneSignal()` method
- `app/Providers/AppServiceProvider.php` - Registered OneSignal channel
- `resources/views/layouts/app.blade.php` - Added OneSignal SDK
- `config/services.php` - Added OneSignal config
- `.env.example` - Added OneSignal variables

### No Database Changes

Unlike Firebase FCM, OneSignal doesn't require:
- ❌ Database migrations
- ❌ Token storage tables
- ❌ Token cleanup jobs
- ❌ Service workers (OneSignal provides it)

Everything is managed by OneSignal! 🎉

## 🌐 Browser Support

- ✅ Chrome (Desktop & Mobile)
- ✅ Firefox (Desktop & Mobile)
- ✅ Edge
- ✅ Opera
- ✅ Safari (with additional setup)
- ❌ Internet Explorer (not supported)

## 🚀 Deployment

### Vercel

1. Add environment variables in Vercel dashboard:
   ```
   ONESIGNAL_APP_ID=your_app_id
   ONESIGNAL_REST_API_KEY=your_api_key
   ```

2. Update OneSignal site URL to your Vercel domain

3. Deploy

### Other Platforms

Same process - add env vars and update site URL in OneSignal dashboard.

## 📊 Analytics

OneSignal dashboard provides:
- 📈 Delivery rates
- 👥 Subscriber count
- 🖱️ Click-through rates
- 🌍 Geographic distribution
- 📱 Device breakdown
- ⏰ Best send times

## 🔒 Privacy & Security

- Users must explicitly allow notifications
- Users can disable anytime in Settings
- No sensitive data in push notifications
- GDPR compliant
- Secure HTTPS connections
- User IDs are hashed

## 🆘 Troubleshooting

### Not receiving notifications?

1. **Check browser permissions**: Settings → Notifications → Allow
2. **Check app settings**: CampFix → Settings → Enable Push Notifications
3. **Check OneSignal dashboard**: Audience → All Users → Verify subscribed
4. **Check Laravel logs**: `storage/logs/laravel.log`

### Permission prompt not showing?

1. Clear browser cache and reload
2. Check browser console for errors
3. Verify `ONESIGNAL_APP_ID` in `.env`

### User not in OneSignal dashboard?

1. Reload the app
2. Check browser console for OneSignal errors
3. Verify app ID is correct
4. Try incognito mode

## 📚 Resources

- [OneSignal Documentation](https://documentation.onesignal.com/)
- [Web Push Quickstart](https://documentation.onesignal.com/docs/web-push-quickstart)
- [REST API Reference](https://documentation.onesignal.com/reference/create-notification)
- [Browser Support](https://documentation.onesignal.com/docs/web-push-setup#browser-support)

## 💡 Why OneSignal Over Firebase?

| Feature | OneSignal | Firebase FCM |
|---------|-----------|--------------|
| Setup Time | 5 minutes | 30+ minutes |
| Database Required | ❌ No | ✅ Yes |
| Token Management | Automatic | Manual |
| Analytics Dashboard | ✅ Beautiful | ❌ Basic |
| Free Tier | 10,000 subscribers | Unlimited |
| Multi-Platform | ✅ Easy | ❌ Complex |
| A/B Testing | ✅ Built-in | ❌ No |
| Segmentation | ✅ Advanced | ❌ Basic |
| Scheduling | ✅ Yes | ❌ No |

## 🎉 Conclusion

OneSignal makes push notifications **simple and powerful**. No complex setup, no database migrations, just add your API keys and you're done!

---

**Need help?** Check the full setup guide in `ONESIGNAL_SETUP.md` or contact support.
