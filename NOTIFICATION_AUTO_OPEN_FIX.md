# Notification Auto-Open & API Fix

## Issues Fixed

### 1. ✅ Notification API Endpoint (500 Error)

**Problem:** The `/api/notifications/unread-count` endpoint was returning a 500 error with HTML instead of JSON.

**Root Cause:** Using `/api/` prefix in web routes causes conflicts with Vercel's serverless function routing, which reserves the `/api/` namespace.

**Solution:**
- Moved endpoint from `/api/notifications/unread-count` to `/notifications/unread-count`
- Added better error logging with `\Log::error()`
- Added proper JSON headers and numeric check for count value
- Updated the fetch URL in `layouts/app.blade.php`

**Route Changes:**
```php
// OLD (in web.php)
Route::get('/api/notifications/unread-count', function () { ... });

// NEW (in web.php)
Route::get('/notifications/unread-count', function () {
    try {
        if (!auth()->check()) {
            return response()->json(['count' => 0], 401);
        }
        $count = auth()->user()->unreadNotifications()->count();
        return response()->json(['count' => $count], 200, [], JSON_NUMERIC_CHECK);
    } catch (\Exception $e) {
        \Log::error('Unread notification count error: ' . $e->getMessage());
        return response()->json(['count' => 0, 'error' => $e->getMessage()], 500);
    }
})->name('notifications.unreadCount');
```

### 2. 🔍 Auto-Open Modal Debug Console Logs

**Status:** Already implemented in previous commit (4c7e219)

**Features:**
- Console logging with `[Auto-open]` prefix for debugging
- Retry logic (up to 5 retries with 500ms delay between attempts)
- Initial delay of 800ms to ensure page is fully loaded
- URL cleanup after modal opens to remove query parameters

**How It Works:**
1. Page loads with URL parameter: `/my-concerns?concern_id=123`
2. Script detects the `concern_id` parameter
3. Waits 800ms for page to load
4. Checks if `viewConcern()` function exists
5. If not found, retries up to 5 times with 500ms delay
6. Once found, calls `viewConcern(123)` to open modal
7. Cleans URL to `/my-concerns` without reloading page

## Testing Instructions

### Test 1: Notification Bell Icon
1. Open your site in a browser
2. Open browser console (F12)
3. Click the notification bell icon
4. **Expected:** No more 500 errors, unread count should load properly

### Test 2: Auto-Open Concern Modal
1. Go to a concern and click "View Details" in a notification OR
2. Manually navigate to: `/my-concerns?concern_id=123` (replace 123 with actual ID)
3. **Expected Console Output:**
   ```
   [Auto-open] DOMContentLoaded fired
   [Auto-open] URL params: ?concern_id=123
   [Auto-open] Concern ID: 123
   [Auto-open] Found concern_id: 123
   [Auto-open] viewConcern function exists? true
   [Auto-open] Opening concern modal for ID: 123
   [Auto-open] Modal opened and URL cleaned
   ```
4. **Expected Behavior:** Concern modal should open automatically
5. **URL After:** Should be cleaned to just `/my-concerns`

### Test 3: Auto-Open Event Modal
1. Go to an event and click "View Details" in a notification OR
2. Manually navigate to: `/my-events?event_id=456` (replace 456 with actual ID)
3. **Expected Console Output:** Similar to Test 2 but for events
4. **Expected Behavior:** Event modal should open automatically

## Debugging

If the modal still doesn't auto-open:

1. **Check Console Logs** - Look for `[Auto-open]` messages:
   - If you see `viewConcern function not found` repeatedly → Script loading issue
   - If you see `No concern_id in URL` → URL parameter not being passed correctly
   - If you see `Found concern_id` but no modal → Check browser console for JavaScript errors

2. **Check Notification Links** - Verify notification redirects include query parameter:
   - Concerns: `/my-concerns?concern_id=123`
   - Events: `/my-events?event_id=456`

3. **Check Browser Network Tab** - Verify the new endpoint is being called:
   - Should see request to: `/notifications/unread-count` (not `/api/notifications/unread-count`)
   - Should return JSON: `{"count": 5}`

## Commits

- **4c7e219** - Improve auto-open modal with retry logic and console logging
- **7421210** - Fix notification unread-count API endpoint - move from /api/ to avoid Vercel routing conflicts

## Next Steps

1. Deploy to production/Vercel
2. Clear browser cache (Ctrl + Shift + Del)
3. Test notification bell icon (should no longer show 500 error)
4. Test clicking "View Details" in notifications (modal should auto-open)
5. Check browser console for `[Auto-open]` debug messages

## Files Modified

- `routes/web.php` - Fixed notification API endpoint route
- `resources/views/layouts/app.blade.php` - Updated fetch URL for notification count
- `resources/views/concerns/my.blade.php` - Auto-open script (previous commit)
- `resources/views/events/my.blade.php` - Auto-open script (previous commit)
