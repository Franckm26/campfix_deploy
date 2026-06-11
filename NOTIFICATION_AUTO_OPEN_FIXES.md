# Notification Auto-Open Modal Fixes

## Date: June 8, 2026

## Issues Fixed

### 1. API Unread Count Error (500 Internal Server Error)
**Problem**: The `/api/notifications/unread-count` endpoint was returning HTML (500 error page) instead of JSON, causing "Unexpected token '<'" errors in the browser console.

**Root Cause**: The endpoint didn't handle authentication failures or exceptions properly, causing it to return HTML error pages when the user wasn't authenticated or when an error occurred.

**Solution**: Added proper error handling and authentication check to the route in `routes/web.php`:
```php
Route::get('/api/notifications/unread-count', function () {
    try {
        if (!auth()->check()) {
            return response()->json(['count' => 0], 401);
        }
        return response()->json(['count' => auth()->user()->unreadNotifications()->count()]);
    } catch (\Exception $e) {
        return response()->json(['count' => 0, 'error' => $e->getMessage()], 500);
    }
});
```

### 2. Auto-Open Modal Enhancement
**Problem**: The auto-open script wasn't showing console logs, and there was uncertainty about whether the modal was opening correctly when redirected from notifications.

**Solution**: Enhanced the auto-open scripts in both `concerns/my.blade.php` and `events/my.blade.php` with:

#### Improvements Made:
1. **Better Logging**: Added comprehensive console logging to track the execution flow
2. **Retry Mechanism**: Implemented a more robust retry system (up to 5 retries with 500ms intervals)
3. **Reduced Initial Delay**: Reduced the initial modal opening delay from 800ms to 300ms for faster response
4. **Debug Information**: Added logging of URL parameters and function availability status

#### Key Features:
- Logs when DOMContentLoaded fires
- Logs the full URL search parameters
- Logs whether concern_id/event_id was found
- Logs whether viewConcern/viewEvent function exists
- Retries up to 5 times if function not immediately available
- Shows retry count in console
- Logs success when modal opens and URL is cleaned

## Files Modified

1. **routes/web.php**
   - Fixed `/api/notifications/unread-count` endpoint with proper error handling

2. **resources/views/concerns/my.blade.php**
   - Enhanced auto-open script with better logging and retry mechanism
   - Lines: ~3761-3800

3. **resources/views/events/my.blade.php**
   - Enhanced auto-open script with better logging and retry mechanism
   - Lines: ~2373-2412

## Testing Instructions

### Test 1: API Endpoint
1. Open browser console
2. Navigate to any page
3. Check for "Unexpected token '<'" errors - should be gone
4. Notification badge should update correctly

### Test 2: Auto-Open from Notification
1. Open browser console (F12)
2. Click on a notification's "View Details" button in the notification modal
3. Should redirect to `/my-concerns?concern_id=X` or `/my-events?event_id=X`
4. **Expected Console Output**:
   ```
   [Auto-open] DOMContentLoaded fired
   [Auto-open] URL params: ?concern_id=123
   [Auto-open] Concern ID: 123
   [Auto-open] Found concern_id: 123
   [Auto-open] viewConcern function exists? true
   [Auto-open] Opening concern modal for ID: 123
   [Auto-open] Modal opened and URL cleaned
   ```
5. The concern/event modal should open automatically
6. The URL should be cleaned to just `/my-concerns` or `/my-events`

### Test 3: No URL Parameters
1. Navigate directly to `/my-concerns` or `/my-events`
2. **Expected Console Output**:
   ```
   [Auto-open] DOMContentLoaded fired
   [Auto-open] URL params: 
   [Auto-open] Concern ID: null
   [Auto-open] No concern_id in URL
   ```
3. No modal should open (normal behavior)

## Debugging

If the modal still doesn't open:

1. **Check Console Logs**: Look for the `[Auto-open]` messages
2. **Verify URL**: Ensure the URL contains `?concern_id=X` or `?event_id=X` after redirect
3. **Check Function Availability**: Look for "viewConcern function exists? false" - means function not loaded
4. **Check Retry Messages**: If you see retry count increasing, the function is loading slowly
5. **Check for JavaScript Errors**: Any errors before the auto-open script will prevent it from working

## Related Components

### Notification Modal (in layouts/app.blade.php)
The notification modal's "View Details" button redirects to:
- `/my-concerns?concern_id=X` for concern notifications
- `/my-events?event_id=X` for event notifications

### Notification Controller
The `show()` method in `NotificationController.php` generates the correct URLs with query parameters.

## Known Limitations

1. The auto-open script requires the `viewConcern()` or `viewEvent()` function to be defined before it runs
2. If JavaScript is disabled, the auto-open won't work (user must manually click)
3. If the page takes very long to load, the 5 retry limit might be reached

## Future Enhancements

1. Consider moving viewConcern/viewEvent functions to a separate JS file that loads earlier
2. Add a loading indicator while waiting for the modal to open
3. Implement a fallback UI if auto-open fails after retries
