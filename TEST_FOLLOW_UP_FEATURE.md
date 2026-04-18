# Testing the Concern Follow-Up Feature

## Quick Test Guide

### Option 1: Create Test Data via Database

Run this SQL to create a test concern that should trigger a follow-up:

```sql
-- First, get a valid user ID and category ID
SELECT id, name, email FROM users WHERE role != 'maintenance' LIMIT 1;
SELECT id, name FROM categories LIMIT 1;

-- Create a test concern (replace USER_ID and CATEGORY_ID with actual values)
INSERT INTO concerns (
    user_id,
    category_id,
    description,
    location,
    status,
    priority,
    created_at,
    updated_at,
    follow_up_sent,
    is_anonymous,
    is_archived,
    is_deleted
) VALUES (
    USER_ID,  -- Replace with actual user ID
    CATEGORY_ID,  -- Replace with actual category ID
    'Test concern for follow-up notification',
    'Test Location',
    'Pending',
    'medium',
    DATE_SUB(NOW(), INTERVAL 4 DAY),  -- 4 days ago
    NOW(),
    0,  -- follow_up_sent = false
    0,  -- not anonymous
    0,  -- not archived
    0   -- not deleted
);
```

### Option 2: Use Tinker

```bash
php artisan tinker
```

Then run:

```php
// Get a test user (not maintenance)
$user = App\Models\User::where('role', '!=', 'maintenance')->first();

// Get a category
$category = App\Models\Category::first();

// Create a test concern
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Test concern for follow-up notification',
    'location' => 'Test Location',
    'status' => 'Pending',
    'priority' => 'medium',
    'created_at' => now()->subDays(4),  // 4 days ago
    'updated_at' => now(),
    'follow_up_sent' => false,
    'is_anonymous' => false,
    'is_archived' => false,
    'is_deleted' => false,
]);

echo "Created concern ID: " . $concern->id . "\n";
echo "User: " . $user->name . " (" . $user->email . ")\n";
```

### Step 2: Run the Follow-Up Command

```bash
php artisan concerns:send-follow-ups
```

Expected output:
```
Sent follow-up for concern ID: X (waiting Y days)
Sent 1 follow-up notification(s).
```

### Step 3: Verify the Results

#### Check Database Notification:
```sql
SELECT * FROM notifications 
WHERE type = 'App\\Notifications\\ConcernFollowUpNotification' 
ORDER BY created_at DESC 
LIMIT 1;
```

#### Check Concern Updated:
```sql
SELECT id, status, follow_up_sent, follow_up_sent_at, created_at 
FROM concerns 
WHERE follow_up_sent = 1 
ORDER BY follow_up_sent_at DESC 
LIMIT 1;
```

#### Check User's Email:
- Log into the email account of the test user
- Look for email with subject: "Follow-up: Concern Still Pending Assignment"

### Step 4: Verify No Duplicate Notifications

Run the command again:
```bash
php artisan concerns:send-follow-ups
```

Expected output:
```
Sent 0 follow-up notification(s).
```

This confirms that concerns already marked with `follow_up_sent = true` are not notified again.

## Testing Different Scenarios

### Scenario 1: Concern Less Than 3 Days Old
```php
// In tinker
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Recent concern',
    'location' => 'Test Location',
    'status' => 'Pending',
    'priority' => 'medium',
    'created_at' => now()->subDays(2),  // Only 2 days ago
    'follow_up_sent' => false,
]);
```
**Expected**: No notification sent (not old enough)

### Scenario 2: Concern Already Assigned
```php
// In tinker
$maintenanceUser = App\Models\User::where('role', 'maintenance')->first();
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Assigned concern',
    'location' => 'Test Location',
    'status' => 'Assigned',
    'priority' => 'medium',
    'assigned_to' => $maintenanceUser->id,
    'created_at' => now()->subDays(4),
    'follow_up_sent' => false,
]);
```
**Expected**: No notification sent (already assigned)

### Scenario 3: Concern Not Pending
```php
// In tinker
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'In progress concern',
    'location' => 'Test Location',
    'status' => 'In Progress',
    'priority' => 'medium',
    'created_at' => now()->subDays(4),
    'follow_up_sent' => false,
]);
```
**Expected**: No notification sent (not in Pending status)

## Monitoring in Production

### Check Scheduled Tasks:
```bash
php artisan schedule:list
```

Look for:
```
0 0 * * *  php artisan concerns:send-follow-ups  Next Due: X hours from now
```

### View Command Output:
```bash
php artisan schedule:work
```

This will show output when the command runs.

### Check Logs:
```bash
tail -f storage/logs/laravel.log
```

Look for any errors related to the follow-up command.

## Troubleshooting

### No Notifications Sent?

1. **Check if concerns exist:**
   ```sql
   SELECT COUNT(*) FROM concerns 
   WHERE status = 'Pending' 
   AND assigned_to IS NULL 
   AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)
   AND follow_up_sent = 0;
   ```

2. **Check mail configuration:**
   ```bash
   php artisan tinker
   ```
   ```php
   Mail::raw('Test email', function($message) {
       $message->to('test@example.com')->subject('Test');
   });
   ```

3. **Check notification settings:**
   ```php
   // In tinker
   $user = App\Models\User::find(USER_ID);
   $user->notify(new App\Notifications\ConcernFollowUpNotification(
       App\Models\Concern::find(CONCERN_ID),
       3
   ));
   ```

### Command Fails?

Check:
- Database connection
- User model relationships
- Concern model relationships
- Mail driver configuration in `.env`

## Clean Up Test Data

After testing, remove test concerns:

```sql
DELETE FROM concerns WHERE description LIKE '%Test concern for follow-up%';
DELETE FROM notifications WHERE type = 'App\\Notifications\\ConcernFollowUpNotification';
```

Or in tinker:
```php
App\Models\Concern::where('description', 'like', '%Test concern for follow-up%')->delete();
```
