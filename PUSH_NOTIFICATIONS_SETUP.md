# Browser Push Notifications Setup Guide

## Overview
This guide explains how to set up and use native browser push notifications in CampFix.

## ✅ What's Been Implemented

### Backend (PHP/Laravel)
- ✅ `push_subscriptions` database table migration
- ✅ `PushSubscription` model
- ✅ `PushNotificationController` with API endpoints
- ✅ `PushNotificationService` for sending notifications
- ✅ `NotificationHelper` for easy integration
- ✅ API routes (`/api/push/subscribe`, `/api/push/unsubscribe`, `/api/push/status`)

### Frontend (JavaScript)
- ✅ Service Worker (`/sw.js`) for background notifications
- ✅ Push notifications JavaScript library (`/js/push-notifications.js`)
- ✅ Auto-initialization in `app.blade.php` layout

---

## 📋 Setup Instructions

### Step 1: Install Web Push Library (Required for Production)

Run this command in your project directory:

```bash
composer require minishlink/web-push
```

**If composer doesn't work:**
- Make sure PHP is in your PATH
- Or deploy to Vercel where it will auto-install from composer.json

---

### Step 2: Generate VAPID Keys

**Option A: Using the provided script**

Run this command:

```bash
php generate_vapid_keys.php
```

This will output your VAPID keys.

**Option B: Using web-push library (after installing)**

```bash
php artisan tinker
```

Then run:

```php
$keys = \Minishlink\WebPush\VAPID::createVapidKeys();
echo "Public Key: " . $keys['publicKey'] . "\n";
echo "Private Key: " . $keys['privateKey'] . "\n";
```

---

### Step 3: Update Environment Variables

**Local (.env file):**

```env
VAPID_PUBLIC_KEY=your_public_key_here
VAPID_PRIVATE_KEY=your_private_key_here
VAPID_SUBJECT=mailto:arjayquiopa9@gmail.com
```

**Vercel (Production):**

1. Go to Vercel Dashboard → Your Project → Settings → Environment Variables
2. Add these three variables:
   - `VAPID_PUBLIC_KEY`
   - `VAPID_PRIVATE_KEY`
   - `VAPID_SUBJECT`
3. Redeploy after adding

---

### Step 4: Run Database Migration

```bash
php artisan migrate
```

This creates the `push_subscriptions` table.

---

### Step 5: Push Changes to Git

```bash
git add .
git commit -m "Add browser push notifications support"
git push origin master
```

Vercel will auto-deploy with the new features.

---

## 🧪 Testing

### Test Subscription (Frontend Console)

Open your CampFix site and open browser console (F12), then:

```javascript
// Request permission and subscribe
PushNotifications.requestPermission().then(async (granted) => {
    if (granted) {
        await PushNotifications.subscribe();
        console.log('Subscribed!');
    }
});

// Check subscription status
PushNotifications.isSubscribed().then(subscribed => {
    console.log('Subscribed:', subscribed);
});
```

### Send Test Notification (Backend)

In Laravel Tinker or a controller:

```php
use App\Services\PushNotificationService;
use App\Models\User;

$pushService = new PushNotificationService();
$user = User::find(1); // Your user ID

$pushService->sendToUser(
    $user,
    'Test Notification',
    'This is a test push notification!',
    ['url' => '/dashboard']
);
```

Or use the helper:

```php
use App\Helpers\NotificationHelper;
use App\Models\User;

$user = User::find(1);

NotificationHelper::notify(
    $user,
    'Welcome!',
    'This is your first push notification',
    ['url' => '/dashboard']
);
```

---

## 📖 Usage Examples

### Send to Single User

```php
use App\Helpers\NotificationHelper;

$user = User::find($userId);

NotificationHelper::notify(
    $user,
    'Task Assigned',
    'You have been assigned a new task',
    ['url' => '/admin/mis-tasks']
);
```

### Send to Multiple Users

```php
NotificationHelper::notifyMultiple(
    [1, 2, 3], // User IDs
    'System Maintenance',
    'The system will be under maintenance tonight',
    ['url' => '/dashboard']
);
```

### Send to Users by Role

```php
NotificationHelper::notifyByRole(
    ['mis', 'admin'],
    'New Concern Submitted',
    'A new concern requires your attention',
    ['url' => '/admin/concerns']
);
```

### Broadcast to All Users

```php
NotificationHelper::broadcast(
    'Important Announcement',
    'All users please check the latest update',
    ['url' => '/announcements']
);
```

---

## 🔗 Integration with Existing Notifications

To add push notifications to existing features, update your notification code:

**Before:**
```php
// Only database notification
$user->notifications()->create([...]);
```

**After:**
```php
use App\Helpers\NotificationHelper;

NotificationHelper::notify(
    $user,
    'Notification Title',
    'Notification message',
    ['url' => '/relevant-page']
);
```

This sends **both** database notification (bell icon) **and** browser push notification.

---

## 🎯 Integration Points

### 1. MIS Task Assignments
File: `app/Http/Controllers/AdminController.php` or wherever tasks are assigned

```php
use App\Helpers\NotificationHelper;

// When assigning task to user
NotificationHelper::notify(
    $assignedUser,
    'Task Assigned',
    "You've been assigned: {$taskTitle}",
    ['url' => '/admin/mis-tasks']
);
```

### 2. Concern Status Updates
File: Concern update controller

```php
NotificationHelper::notify(
    $concernOwner,
    'Concern Updated',
    "Your concern status is now: {$newStatus}",
    ['url' => "/concerns/{$concernId}"]
);
```

### 3. Event Approvals
File: Event approval controller

```php
NotificationHelper::notify(
    $eventCreator,
    'Event Approved',
    "Your event '{$eventName}' has been approved",
    ['url' => '/my-events']
);
```

---

## 🔧 Troubleshooting

### Push notifications not working?

1. **Check VAPID keys are set:**
   ```bash
   php artisan tinker
   env('VAPID_PUBLIC_KEY') // Should not be empty
   ```

2. **Check service worker is registered:**
   - Open DevTools → Application → Service Workers
   - Should see `/sw.js` registered

3. **Check browser permission:**
   - Click the lock icon in address bar
   - Make sure Notifications are set to "Allow"

4. **Check subscription:**
   ```javascript
   PushNotifications.isSubscribed()
   ```

5. **Check database:**
   ```sql
   SELECT * FROM push_subscriptions;
   ```

### Browser Compatibility

- ✅ Chrome/Edge (v50+)
- ✅ Firefox (v44+)
- ✅ Safari (v16+ on macOS)
- ❌ iOS Safari (not supported yet)

---

## 🚀 Production Deployment

1. Generate VAPID keys (Step 2)
2. Add to Vercel environment variables (Step 3)
3. Commit all files to git
4. Push to master → Auto-deploys to Vercel
5. Run migration on production database
6. Test with a real user

---

## 📝 Files Created

### Backend
- `database/migrations/2026_09_10_000001_create_push_subscriptions_table.php`
- `app/Models/PushSubscription.php`
- `app/Http/Controllers/PushNotificationController.php`
- `app/Services/PushNotificationService.php`
- `app/Helpers/NotificationHelper.php`
- `app/Notifications/ExamplePushNotification.php`

### Frontend
- `public/sw.js` (Service Worker)
- `public/js/push-notifications.js` (JavaScript library)

### Routes
- `routes/api.php` (added push notification endpoints)

### Layout
- `resources/views/layouts/app.blade.php` (added push notification initialization)

---

## ❓ FAQ

**Q: Do users need to click "Allow" every time?**
A: No, once they allow, the permission is saved in their browser.

**Q: Will this work offline?**
A: Yes! The service worker allows notifications even when the browser is closed.

**Q: Can I customize the notification icon?**
A: Yes! Edit the `icon` and `badge` fields in `sw.js` or when calling the send methods.

**Q: How do I know if a user is subscribed?**
A: Check the `push_subscriptions` table or use the `/api/push/status` endpoint.

---

## 📞 Support

For issues or questions, contact the development team or check Laravel and Web Push API documentation.
