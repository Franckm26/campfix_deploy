# Testing Manual Follow-Up Button Feature

## Overview
The follow-up feature now includes:
1. **Automatic follow-ups**: Runs daily for concerns pending 1+ days
2. **Manual follow-up button**: Users can manually trigger follow-ups for their pending concerns

## Changes Made

### 1. Updated Delay from 3 Days to 1 Day
- **File**: `app/Console/Commands/SendConcernFollowUps.php`
- Changed from `subDays(3)` to `subDays(1)`

### 2. Added Manual Follow-Up Button
- **File**: `resources/views/concerns/my.blade.php`
- Added bell icon button in actions column
- Shows only for pending, unassigned concerns that are 1+ days old

### 3. Added JavaScript Function
- **File**: `resources/views/concerns/my.blade.php`
- Function: `sendFollowUp(id)`
- Handles AJAX request with loading state

### 4. Added Controller Method
- **File**: `app/Http/Controllers/ConcernController.php`
- Method: `sendFollowUp($id)`
- Validates and sends follow-up notification

### 5. Added Route
- **File**: `routes/web.php`
- Route: `POST /concerns/{id}/send-follow-up`

## Testing the Manual Follow-Up Button

### Step 1: Create a Test Concern

Using Tinker:
```bash
php artisan tinker
```

```php
// Get a test user (not maintenance)
$user = App\Models\User::where('role', '!=', 'maintenance')->first();

// Get a category
$category = App\Models\Category::first();

// Create a test concern that's 2 days old
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Test concern for manual follow-up button',
    'location' => 'Test Location - Manual Button',
    'status' => 'Pending',
    'priority' => 'medium',
    'created_at' => now()->subDays(2),  // 2 days ago
    'updated_at' => now(),
    'follow_up_sent' => false,
    'is_anonymous' => false,
    'is_archived' => false,
    'is_deleted' => false,
]);

echo "Created concern ID: " . $concern->id . "\n";
echo "User: " . $user->name . " (" . $user->email . ")\n";
echo "Login as this user to test the button\n";
```

### Step 2: Test in Browser

1. **Login** as the user who created the concern
2. **Navigate** to "My Concerns" page
3. **Look for** the concern you just created
4. **Verify** you see a bell icon (🔔) button in the actions column
5. **Click** the bell icon button
6. **Confirm** the action when prompted
7. **Verify** success message appears
8. **Check** that the button disappears after sending (page reloads)

### Step 3: Verify Results

#### Check Database:
```sql
-- Verify follow_up_sent was updated
SELECT id, status, follow_up_sent, follow_up_sent_at, created_at 
FROM concerns 
WHERE description LIKE '%manual follow-up button%';

-- Check notification was created
SELECT * FROM notifications 
WHERE type = 'App\\Notifications\\ConcernFollowUpNotification' 
ORDER BY created_at DESC 
LIMIT 1;

-- Check activity log
SELECT * FROM activity_logs 
WHERE action = 'concern_follow_up_sent' 
ORDER BY created_at DESC 
LIMIT 1;
```

#### Check Email:
- Login to the user's email account
- Look for email with subject: "Follow-up: Concern Still Pending Assignment"

### Step 4: Test Button Visibility Conditions

The button should ONLY appear when ALL these conditions are met:

#### ✅ Should Show Button:
```php
// In tinker - create concern that meets all conditions
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Should show button',
    'location' => 'Test',
    'status' => 'Pending',  // ✓ Pending
    'assigned_to' => null,  // ✓ Not assigned
    'created_at' => now()->subDays(2),  // ✓ 2 days old
    'follow_up_sent' => false,
]);
```

#### ❌ Should NOT Show Button - Less than 1 day old:
```php
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Too recent',
    'location' => 'Test',
    'status' => 'Pending',
    'assigned_to' => null,
    'created_at' => now()->subHours(12),  // ✗ Only 12 hours old
    'follow_up_sent' => false,
]);
```

#### ❌ Should NOT Show Button - Already assigned:
```php
$maintenanceUser = App\Models\User::where('role', 'maintenance')->first();
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'Already assigned',
    'location' => 'Test',
    'status' => 'Assigned',
    'assigned_to' => $maintenanceUser->id,  // ✗ Already assigned
    'created_at' => now()->subDays(2),
    'follow_up_sent' => false,
]);
```

#### ❌ Should NOT Show Button - Not pending:
```php
$concern = App\Models\Concern::create([
    'user_id' => $user->id,
    'category_id' => $category->id,
    'description' => 'In progress',
    'location' => 'Test',
    'status' => 'In Progress',  // ✗ Not pending
    'assigned_to' => null,
    'created_at' => now()->subDays(2),
    'follow_up_sent' => false,
]);
```

### Step 5: Test Error Handling

#### Test 1: Try to send follow-up twice
1. Click the follow-up button
2. Wait for success message
3. Reload the page
4. Button should be gone (follow_up_sent = true)

#### Test 2: Try to send follow-up for someone else's concern
Use browser dev tools to manually call:
```javascript
sendFollowUp(SOMEONE_ELSES_CONCERN_ID)
```
Expected: Error message "You can only send follow-ups for your own concerns."

#### Test 3: Try to send for concern less than 1 day old
Create a concern that's only a few hours old, then try to manually trigger:
Expected: Error message "Follow-up can only be sent for concerns older than 1 day."

## Testing Automatic Follow-Ups

### Test the Scheduled Command:

```bash
php artisan concerns:send-follow-ups
```

Expected output:
```
Sent follow-up for concern ID: X (waiting Y days)
Sent N follow-up notification(s).
```

### Verify Scheduler:

```bash
php artisan schedule:list
```

Look for:
```
0 0 * * *  php artisan concerns:send-follow-ups  Next Due: X hours from now
```

## Visual Verification

When viewing "My Concerns" page, the actions column should look like:

```
[👁️ View] [✏️ Edit] [🔔 Follow-up] [📦 Archive] [🗑️ Delete]
```

The bell icon (🔔) only appears for eligible concerns.

## Clean Up Test Data

After testing:

```sql
DELETE FROM concerns WHERE description LIKE '%Test concern for manual follow-up%';
DELETE FROM concerns WHERE description LIKE '%manual follow-up button%';
DELETE FROM notifications WHERE type = 'App\\Notifications\\ConcernFollowUpNotification';
DELETE FROM activity_logs WHERE action = 'concern_follow_up_sent';
```

Or in tinker:
```php
App\Models\Concern::where('description', 'like', '%manual follow-up%')->delete();
```

## Summary of Changes

| Feature | Before | After |
|---------|--------|-------|
| Automatic delay | 3 days | 1 day |
| Manual trigger | ❌ No | ✅ Yes |
| Button visibility | N/A | Pending, unassigned, 1+ days old |
| User control | None | Can manually send follow-up |
| Duplicate prevention | ✅ Yes | ✅ Yes (still works) |

## Expected Behavior

1. **Day 0**: User submits concern → Status: Pending
2. **Day 1**: 
   - Automatic: Scheduler runs and sends follow-up
   - Manual: User sees bell button and can click to send
3. **After follow-up sent**: 
   - Button disappears
   - `follow_up_sent` = true
   - No duplicate notifications possible
