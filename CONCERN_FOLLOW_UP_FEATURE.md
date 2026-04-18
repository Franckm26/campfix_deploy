# Concern Follow-Up Feature

## Overview
This feature automatically sends follow-up notifications to users whose concerns have been pending without assignment for 1 day. Users can also manually trigger follow-up notifications using a button in the UI.

## Components

### 1. Database Migration
**File:** `database/migrations/2026_04_16_100000_add_follow_up_fields_to_concerns_table.php`

Adds two new fields to the `concerns` table:
- `follow_up_sent` (boolean): Tracks whether a follow-up notification has been sent
- `follow_up_sent_at` (timestamp): Records when the follow-up was sent

### 2. Notification Class
**File:** `app/Notifications/ConcernFollowUpNotification.php`

Sends notifications via:
- **Database**: Stored in the notifications table for in-app display
- **Email**: Sent to the user's email address

The notification includes:
- Number of days the concern has been waiting
- Concern details (location, category, description, priority)
- Direct link to view the concern
- Apology message for the delay

### 3. Console Command
**File:** `app/Console/Commands/SendConcernFollowUps.php`

**Command:** `php artisan concerns:send-follow-ups`

**Functionality:**
- Finds concerns that are:
  - Still in "Pending" status
  - Not assigned to anyone (`assigned_to` is null)
  - Created at least 1 day ago (changed from 3 days)
  - Haven't received a follow-up notification yet
- Sends notification to the user who submitted the concern
- Marks the concern as having received a follow-up

**Scheduled:** Runs daily (configured in `bootstrap/app.php`)

### 4. Manual Follow-Up Button
**File:** `resources/views/concerns/my.blade.php`

**Features:**
- Bell icon button in the actions column
- Only visible for concerns that are:
  - In "Pending" status
  - Not assigned
  - At least 1 day old
  - Haven't received a follow-up yet
- Sends AJAX request to trigger follow-up
- Shows loading state during request
- Disappears after follow-up is sent

**JavaScript Function:** `sendFollowUp(id)`

### 5. Controller Method
**File:** `app/Http/Controllers/ConcernController.php`

**Method:** `sendFollowUp($id)`

**Validations:**
- User must be the owner of the concern
- Concern must be in "Pending" status
- Concern must not be assigned
- Concern must be at least 1 day old
- Follow-up must not have been sent already

**Actions:**
- Sends notification to the user
- Updates `follow_up_sent` and `follow_up_sent_at`
- Logs activity

### 6. Route
**File:** `routes/web.php`

**Route:** `POST /concerns/{id}/send-follow-up`
**Name:** `concerns.sendFollowUp`

### 7. Model Updates
**File:** `app/Models/Concern.php`

Updated to include:
- `follow_up_sent` in `$fillable` array
- `follow_up_sent_at` in `$fillable` array
- `follow_up_sent` in `$casts` array (as boolean)
- `follow_up_sent_at` in `$casts` array (as datetime)

### 8. Scheduler Configuration
**File:** `bootstrap/app.php`

Added scheduled task:
```php
$schedule->command('concerns:send-follow-ups')->daily();
```

## Installation

1. **Run the migration:**
   ```bash
   php artisan migrate
   ```

2. **Test the command manually:**
   ```bash
   php artisan concerns:send-follow-ups
   ```

3. **Ensure the scheduler is running:**
   
   For development:
   ```bash
   php artisan schedule:work
   ```
   
   For production (add to crontab):
   ```
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

## How It Works

### Automatic Follow-Up:
1. **Day 0**: User submits a concern → Status is "Pending"
2. **Day 1**: Scheduled command runs daily and detects the concern
3. **Action**: System sends follow-up notification to the user
4. **Tracking**: `follow_up_sent` is set to `true` and `follow_up_sent_at` is recorded
5. **Prevention**: The same concern won't receive another follow-up notification

### Manual Follow-Up:
1. User views their concerns in "My Concerns" page
2. For eligible concerns (pending, unassigned, 1+ days old), a bell icon button appears
3. User clicks the bell icon
4. System validates the request
5. Follow-up notification is sent immediately
6. Button disappears (concern marked as followed up)

## Notification Content

The user receives:
- **Subject**: "Follow-up: Concern Still Pending Assignment"
- **Message**: Details about their concern and how long it's been waiting
- **Action Button**: Direct link to view their concern
- **Reassurance**: Message that their concern is important and being processed

## Testing

See `TEST_MANUAL_FOLLOW_UP.md` for comprehensive testing instructions.

## Future Enhancements

Potential improvements:
- Send multiple follow-ups (e.g., at 1 day, 3 days, 7 days)
- Notify administrators about concerns pending too long
- Add configurable delay period (instead of hardcoded 1 day)
- Include statistics in follow-up (e.g., "X concerns resolved this week")
- Add SMS notifications for urgent concerns
- Allow users to customize follow-up frequency
