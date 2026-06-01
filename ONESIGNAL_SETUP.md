# OneSignal Push Notification Setup Guide

This guide will help you set up OneSignal for push notifications in CampFix.

## 🚀 Why OneSignal?

- ✅ **Easier Setup** - No complex Firebase configuration
- ✅ **Better Dashboard** - Beautiful analytics and user management
- ✅ **Free Tier** - 10,000 subscribers free forever
- ✅ **Multi-Platform** - Web, iOS, Android from one dashboard
- ✅ **Advanced Features** - Segmentation, A/B testing, automation
- ✅ **No Database Required** - OneSignal manages user tokens

## 📋 Features Implemented

- ✅ Background push notifications (works when app is closed)
- ✅ Foreground notifications (when app is open)
- ✅ Automatic user identification (linked to Laravel user ID)
- ✅ Click handling (redirects to relevant pages)
- ✅ User preference controls (enable/disable in settings)
- ✅ No database migrations needed
- ✅ Works on HTTP and HTTPS (localhost friendly)

## 🔧 Setup Instructions

### Step 1: Create OneSignal Account

1. Go to [OneSignal.com](https://onesignal.com/)
2. Click "Get Started Free"
3. Sign up with email or Google account
4. Verify your email

### Step 2: Create a New App

1. Click "New App/Website"
2. Enter app name: **CampFix**
3. Select platform: **Web Push**
4. Click "Next: Configure Your Platform"

### Step 3: Configure Web Push

1. **Site Setup**:
   - Site Name: `CampFix`
   - Site URL: Your production URL (e.g., `https://campfix.vercel.app`)
   - For local development, use: `http://localhost` or `http://localhost:8000`
   - Auto Resubscribe: **Enabled** (recommended)
   - Default Notification Icon URL: `https://yourdomain.com/favicon.ico`

2. **Permission Prompt Setup**:
   - Choose "Slide Prompt" (recommended)
   - Customize text if desired
   - Click "Save"

3. **Advanced Settings** (optional):
   - Welcome Notification: Disable (we'll handle this in Laravel)
   - Persistence: Enable
   - Click "Save"

### Step 4: Get Your API Keys

1. After setup, go to **Settings** → **Keys & IDs**
2. Copy the following values:
   - **App ID** (looks like: `12345678-1234-1234-1234-123456789012`)
   - **REST API Key** (looks like: `YourRestApiKey`)
   - **User Auth Key** (optional, for advanced features)

3. If you need Safari support:
   - Go to **Settings** → **Platforms** → **Apple Safari**
   - Follow the instructions to get Safari Web ID
   - Copy the **Safari Web ID**

### Step 5: Configure Your Laravel Application

Add these variables to your `.env` file:

```env
# OneSignal Push Notifications
ONESIGNAL_APP_ID=your_app_id_here
ONESIGNAL_REST_API_KEY=your_rest_api_key_here
ONESIGNAL_USER_AUTH_KEY=your_user_auth_key_here
ONESIGNAL_SAFARI_WEB_ID=your_safari_web_id_here
```

**Example:**
```env
ONESIGNAL_APP_ID=12345678-1234-1234-1234-123456789012
ONESIGNAL_REST_API_KEY=YourRestApiKeyHere
ONESIGNAL_USER_AUTH_KEY=YourUserAuthKeyHere
ONESIGNAL_SAFARI_WEB_ID=web.onesignal.auto.12345678-1234-1234-1234-123456789012
```

### Step 6: Register the OneSignal Channel

Add the OneSignal channel to your `config/app.php` or register it in `AppServiceProvider`:

```php
// In app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Notification;
use App\Channels\OneSignalChannel;

public function boot(): void
{
    // Register OneSignal notification channel
    Notification::extend('onesignal', function ($app) {
        return new OneSignalChannel();
    });
}
```

### Step 7: Test Push Notifications

1. **Clear browser cache** and reload your application
2. **Log in** to your CampFix account
3. **Allow notifications** when prompted
4. Go to **Settings** → **Notifications**
5. Enable **Push Notifications**
6. Create a test concern or event to trigger a notification

### Step 8: Verify in OneSignal Dashboard

1. Go to OneSignal dashboard
2. Click **Audience** → **All Users**
3. You should see your user subscribed
4. The **External User ID** should match your Laravel user ID

## 🎯 How It Works

### User Flow

1. **First Visit**: User sees OneSignal's slide prompt
2. **Permission**: User clicks "Allow" to enable notifications
3. **Auto-Login**: OneSignal automatically links to Laravel user ID
4. **Receiving Notifications**:
   - **Background**: Notifications appear even when browser is closed
   - **Foreground**: Notifications appear as browser notifications
5. **Click Action**: Clicking opens the relevant page in CampFix

### Developer Flow

Notifications are automatically sent for:
- ✅ Concern assignments (to maintenance staff)
- ✅ Concern resolutions (to requesters)
- ✅ Event request approvals/rejections (to requesters)
- ✅ New event requests (to approvers)
- ✅ Report assignments (to maintenance staff)
- ✅ Report resolutions (to requesters)

## 📱 Adding Push to New Notifications

To add push notification support to any notification class:

1. **Add the `toOneSignal()` method**:

```php
public function toOneSignal(object $notifiable): array
{
    return [
        'title' => 'Notification Title',
        'body' => 'Notification message body',
        'icon' => '/favicon.ico',
        'badge' => '/favicon.ico',
        'url' => url('/target-page'),
        'data' => [
            'type' => 'notification_type',
            'id' => $this->id,
            'url' => '/target-page',
        ],
    ];
}
```

2. **Update the `via()` method**:

```php
public function via(object $notifiable): array
{
    $channels = ['database'];
    
    if ($notifiable->email_notifications ?? true) {
        $channels[] = 'mail';
    }
    
    if ($notifiable->push_notifications ?? false) {
        $channels[] = 'onesignal';
    }
    
    return $channels;
}
```

## 🔒 Security & Privacy

- ✅ Users must explicitly allow notifications
- ✅ Users can disable push notifications anytime in settings
- ✅ OneSignal uses secure HTTPS connections
- ✅ User IDs are hashed and encrypted
- ✅ No sensitive data is sent in push notifications
- ✅ GDPR compliant

## 🐛 Troubleshooting

### Notifications Not Appearing

**Check OneSignal Dashboard:**
1. Go to **Audience** → **All Users**
2. Verify your user is subscribed
3. Check if External User ID matches your Laravel user ID

**Check Browser:**
1. Ensure notifications are allowed in browser settings
2. Check browser console for JavaScript errors
3. Verify OneSignal SDK loaded (check Network tab)

**Check Laravel:**
1. Verify `.env` has correct OneSignal credentials
2. Check Laravel logs for errors: `storage/logs/laravel.log`
3. Verify user has `push_notifications` enabled in database

### Permission Prompt Not Showing

1. **Already Denied**: User may have previously denied notifications
   - Solution: User must manually enable in browser settings
2. **HTTP Site**: Some browsers require HTTPS for notifications
   - Solution: Use HTTPS or enable localhost exception
3. **Script Not Loaded**: OneSignal SDK failed to load
   - Solution: Check browser console and network tab

### User Not Subscribed

1. **Check OneSignal Dashboard**: Go to Audience → All Users
2. **Clear Browser Data**: Clear cache and cookies, try again
3. **Check Console**: Look for OneSignal initialization errors
4. **Verify App ID**: Ensure `ONESIGNAL_APP_ID` is correct in `.env`

## 📊 Monitoring & Analytics

### OneSignal Dashboard

1. **Delivery**: View notification delivery rates
2. **Audience**: See all subscribed users
3. **Messages**: View sent notifications and their performance
4. **Outcomes**: Track clicks and conversions

### Laravel Logs

Check `storage/logs/laravel.log` for:
- OneSignal API responses
- Notification send attempts
- Error messages

### Test Notification

Send a test notification to yourself:

```php
// In tinker or a controller
auth()->user()->notify(new \App\Notifications\TestPushNotification());
```

## 🚀 Production Deployment

### Checklist

- [ ] OneSignal app created
- [ ] Web push configured with production URL
- [ ] API keys added to production `.env`
- [ ] HTTPS enabled (required for most browsers)
- [ ] Tested on production domain
- [ ] Custom icon uploaded
- [ ] Welcome notification disabled
- [ ] Analytics enabled

### Vercel/Serverless Deployment

1. Add environment variables in Vercel dashboard:
   ```
   ONESIGNAL_APP_ID=your_app_id
   ONESIGNAL_REST_API_KEY=your_api_key
   ONESIGNAL_USER_AUTH_KEY=your_user_auth_key
   ```

2. Update OneSignal site URL to your Vercel domain

3. Deploy and test

### Performance Tips

- OneSignal SDK is loaded asynchronously (no performance impact)
- Notifications are sent via API (non-blocking)
- No database queries needed for push notifications
- OneSignal handles all token management

## 📚 Additional Resources

- [OneSignal Documentation](https://documentation.onesignal.com/)
- [Web Push Quickstart](https://documentation.onesignal.com/docs/web-push-quickstart)
- [OneSignal REST API](https://documentation.onesignal.com/reference/create-notification)
- [Browser Support](https://documentation.onesignal.com/docs/web-push-setup#browser-support)

## 🎉 You're Done!

Your CampFix application now supports push notifications via OneSignal! Users will receive real-time notifications even when the app is in the background.

## 💡 Pro Tips

1. **Segmentation**: Use OneSignal segments to target specific user groups
2. **Scheduling**: Schedule notifications for optimal engagement times
3. **A/B Testing**: Test different notification messages
4. **Rich Media**: Add images to notifications for better engagement
5. **Action Buttons**: Add custom action buttons to notifications
6. **Delivery Optimization**: OneSignal automatically optimizes delivery times

## 🆘 Need Help?

- OneSignal Support: [support@onesignal.com](mailto:support@onesignal.com)
- OneSignal Community: [community.onesignal.com](https://community.onesignal.com/)
- Documentation: [documentation.onesignal.com](https://documentation.onesignal.com/)
