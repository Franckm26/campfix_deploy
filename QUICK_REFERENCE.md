# Follow-Up Feature - Quick Reference

## 🎯 What Changed?

1. **Delay**: 3 days → **1 day**
2. **New Feature**: Manual follow-up button added
3. **User Control**: Users can now trigger follow-ups themselves

## 🔔 Manual Follow-Up Button

### Where to Find It:
- Navigate to: **My Concerns** page
- Look in: **Actions column** (rightmost column)
- Icon: **🔔 Bell icon**

### When It Appears:
The button shows ONLY when:
- ✅ Status is "Pending"
- ✅ Not assigned to anyone
- ✅ Created 1+ days ago
- ✅ No follow-up sent yet

### How to Use:
1. Click the bell icon (🔔)
2. Confirm the action
3. Wait for success message
4. Page reloads, button disappears

## ⚙️ Automatic Follow-Up

### Schedule:
- Runs: **Daily at midnight**
- Checks: Concerns pending 1+ days
- Action: Sends notifications automatically

### Command:
```bash
# Run manually
php artisan concerns:send-follow-ups

# Check schedule
php artisan schedule:list
```

## 📧 What Users Receive

### Email:
- Subject: "Follow-up: Concern Still Pending Assignment"
- Content: Concern details + days waiting
- Action: Link to view concern

### In-App Notification:
- Bell icon in navbar
- Same information as email
- Clickable to view concern

## 🛠️ For Developers

### Key Files:
```
app/Console/Commands/SendConcernFollowUps.php    - Command
app/Http/Controllers/ConcernController.php       - Controller method
app/Notifications/ConcernFollowUpNotification.php - Notification
resources/views/concerns/my.blade.php            - UI & JavaScript
routes/web.php                                   - Route
```

### Database Fields:
```sql
concerns.follow_up_sent      BOOLEAN
concerns.follow_up_sent_at   TIMESTAMP
```

### Route:
```
POST /concerns/{id}/send-follow-up
```

### JavaScript Function:
```javascript
sendFollowUp(concernId)
```

## 🧪 Quick Test

### Create Test Concern:
```bash
php artisan tinker
```

```php
$user = App\Models\User::where('role', '!=', 'maintenance')->first();
$category = App\Models\Category::first();

App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Test follow-up',
    'location' => 'Test Location',
    'status' => 'Pending',
    'priority' => 'medium',
    'created_at' => now()->subDays(2),
    'follow_up_sent' => false,
]);
```

### Test Button:
1. Login as the user
2. Go to My Concerns
3. See the bell icon
4. Click it
5. Verify notification sent

### Clean Up:
```php
App\Models\Concern::where('description', 'Test follow-up')->delete();
```

## 📊 Monitoring

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

### Check Database:
```sql
-- Recent follow-ups
SELECT id, status, follow_up_sent, follow_up_sent_at 
FROM concerns 
WHERE follow_up_sent = 1 
ORDER BY follow_up_sent_at DESC 
LIMIT 10;

-- Pending concerns eligible for follow-up
SELECT id, status, created_at, DATEDIFF(NOW(), created_at) as days_old
FROM concerns 
WHERE status = 'Pending' 
AND assigned_to IS NULL 
AND follow_up_sent = 0
AND DATEDIFF(NOW(), created_at) >= 1;
```

### Check Notifications:
```sql
SELECT * FROM notifications 
WHERE type = 'App\\Notifications\\ConcernFollowUpNotification' 
ORDER BY created_at DESC 
LIMIT 10;
```

## ⚠️ Troubleshooting

### Button Not Showing?
Check:
- [ ] Concern status is "Pending"
- [ ] Concern is not assigned
- [ ] Concern is 1+ days old
- [ ] `follow_up_sent` is false
- [ ] You are the concern owner

### Notification Not Sent?
Check:
- [ ] Mail configuration in `.env`
- [ ] User has valid email
- [ ] Check `storage/logs/laravel.log`
- [ ] Test mail: `php artisan tinker` → `Mail::raw('test', fn($m) => $m->to('email@test.com')->subject('Test'));`

### Scheduler Not Running?
Check:
- [ ] Cron job configured
- [ ] Run: `php artisan schedule:work` (dev)
- [ ] Check: `php artisan schedule:list`

## 📞 Quick Commands

```bash
# Test follow-up command
php artisan concerns:send-follow-ups

# Check scheduled tasks
php artisan schedule:list

# Run scheduler (development)
php artisan schedule:work

# Check routes
php artisan route:list --name=concerns

# View logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## ✅ Success Indicators

Feature is working when:
- ✅ Bell button appears for eligible concerns
- ✅ Clicking button sends notification
- ✅ User receives email
- ✅ User sees in-app notification
- ✅ Button disappears after sending
- ✅ No duplicate notifications
- ✅ Automatic scheduler runs daily

## 📝 User Instructions

**To send a follow-up for your pending concern:**

1. Go to **My Concerns** page
2. Find your pending concern (must be 1+ days old)
3. Look for the **bell icon (🔔)** in the Actions column
4. Click the bell icon
5. Confirm when prompted
6. You'll receive a notification about your concern

**Note:** You can only send one follow-up per concern. The button will disappear after sending.
