# OTP Verification Rate Limit Fix

## Problem
Users were getting "Too many requests" error when verifying OTP, even when logging in with different accounts. This happened because the rate limiting was based on IP address, affecting all users from the same network (office, school, etc.).

## Root Cause
1. **IP-based rate limiting**: The `RateLimitMiddleware` was using IP address as the rate limit key for OTP verification
2. **Shared IP addresses**: Multiple users from the same network share the same public IP
3. **Low threshold**: 5 attempts per 15 minutes per IP was too restrictive for shared networks

## Solution

### 1. Removed Route-Level Throttle
**File**: `routes/web.php`

**Before:**
```php
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,10');
```

**After:**
```php
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
```

**Reason**: The built-in per-user OTP attempt limiting (5 attempts per OTP) is sufficient.

### 2. Updated Rate Limit Middleware
**File**: `app/Http/Middleware/RateLimitMiddleware.php`

**Changed**: `resolveRequestSignature()` method

**Before:**
- Used IP address for all login-related routes
- All users from same IP shared the rate limit

**After:**
- For OTP verification: Uses `user_id` from session
- Each user gets their own rate limit counter
- Falls back to IP only if no user in session

**Implementation:**
```php
protected function resolveRequestSignature(Request $request): string
{
    // For OTP verification, use session-based rate limiting (per user)
    if (str_contains($request->path(), 'verify-otp')) {
        $userId = session('otp_user');
        if ($userId) {
            return 'otp_verify:user_' . $userId;
        }
        // Fallback to IP if no user in session
        return 'otp_verify:' . $request->ip();
    }

    // Use email if provided, otherwise use IP
    $email = $request->get('email');
    if ($email) {
        return 'login:'.strtolower($email);
    }

    return 'login:'.$request->ip();
}
```

## Security Maintained

The fix maintains security through multiple layers:

1. **Per-User OTP Attempts**: Each user still has max 5 attempts per OTP (in `AuthController`)
2. **OTP Expiration**: OTPs expire after a set time
3. **Per-User Rate Limiting**: Each user has their own rate limit (5 attempts per 15 minutes)
4. **Session-Based**: Uses secure session data to identify users

## Benefits

✅ **Multiple users can verify OTP simultaneously** from the same network
✅ **Each user has independent rate limits**
✅ **Security is maintained** with per-user attempt tracking
✅ **No more false "Too many requests" errors** for different accounts
✅ **Better user experience** in shared network environments (schools, offices)

## Testing

### Before Fix:
1. User A tries OTP 5 times from IP 192.168.1.1
2. User B tries OTP from same IP 192.168.1.1
3. ❌ User B gets "Too many requests" error

### After Fix:
1. User A tries OTP 5 times (user_id: 123)
2. User B tries OTP (user_id: 456)
3. ✅ User B can verify OTP successfully
4. Each user has their own 5-attempt limit

## Rollback (if needed)

If you need to revert:

1. **Restore route throttle**:
```php
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,10');
```

2. **Revert middleware**:
```php
protected function resolveRequestSignature(Request $request): string
{
    $email = $request->get('email');
    if ($email) {
        return 'login:'.strtolower($email);
    }
    return 'login:'.$request->ip();
}
```

## Notes

- The fix is backward compatible
- No database changes required
- No impact on other authentication flows
- Login rate limiting still uses email/IP as before
