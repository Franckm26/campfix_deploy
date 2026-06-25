# 🚨 CRITICAL SECURITY BREACH REPORT

## Incident Summary

**Date Discovered:** June 16, 2026  
**Severity:** HIGH  
**Status:** PATCHED  

### Vulnerability

**Issue:** Microsoft OAuth callback did NOT validate email domain  
**Impact:** ANY Microsoft account could create user accounts  
**Affected:** Production system (www.campfixsti.com)

### Unauthorized User Found

**Student ID:** 2472104030345  
**Name:** Nguyễn Duy Khiêm  
**Email:** khiem.2472104030345@vanlanguni.vn  
**Domain:** @vanlanguni.vn (NOT authorized)  
**Expected Domain:** @novaliches.sti.edu.ph  

## Root Cause Analysis

### The Vulnerability

File: `app/Http/Controllers/AuthController.php`  
Method: `handleMicrosoftCallback()`

**BEFORE (Vulnerable Code):**
```php
public function handleMicrosoftCallback()
{
    $microsoftUser = Socialite::driver('microsoft')->user();
    
    // NO EMAIL VALIDATION HERE!
    $user = User::where('email', strtolower($microsoftUser->getEmail()))->first();
    
    if (!$user) {
        // Creates user with ANY email domain
        $user = User::create([
            'email' => strtolower($microsoftUser->getEmail()),
            // ...
        ]);
    }
    // ...
}
```

### What Was Missing

The function `hasAllowedEmailDomain()` exists and is used in:
- ✅ API Login (line 482)
- ✅ API Register (line 573)
- ❌ Microsoft OAuth Callback (MISSING!)

### How the Breach Happened

1. User from Van Lang University (different school) visited your site
2. Clicked "Login with Microsoft"
3. Used their @vanlanguni.vn email
4. OAuth callback created their account WITHOUT validation
5. No activity logs were created for OAuth registration
6. User appeared in your database with no audit trail

## The Fix

**Commit:** (to be pushed)  
**Files Modified:** `app/Http/Controllers/AuthController.php`

### Changes Made

1. **Added Email Domain Validation**
```php
// CRITICAL SECURITY: Validate email domain
$email = strtolower($microsoftUser->getEmail());
if (!$this->hasAllowedEmailDomain($email)) {
    \Log::warning('Unauthorized OAuth login attempt - invalid email domain', [
        'email' => $email,
        'microsoft_id' => $microsoftUser->getId(),
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
    
    return redirect('/login')->with('error', 'Only @novaliches.sti.edu.ph email addresses are allowed.');
}
```

2. **Added Activity Logging for OAuth**
```php
// Log activity for security audit
ActivityLog::log(
    'user_created_oauth',
    "New user created via Microsoft OAuth: {$user->name} ({$user->email})",
    $user->id,
    'user',
    null,
    [
        'name' => $user->name,
        'email' => $user->email,
        'microsoft_id' => $user->microsoft_id,
        'ip_address' => request()->ip(),
    ]
);
```

3. **Added Login Logging for OAuth**
```php
// Log successful OAuth login
ActivityLog::log(
    'microsoft_login',
    "Successful Microsoft OAuth login: {$user->name} ({$user->email})",
    $user->id,
    'user',
    null,
    [
        'email' => $user->email,
        'microsoft_id' => $user->microsoft_id,
        'ip_address' => request()->ip(),
    ]
);
```

## Immediate Actions Required

### 1. Delete Unauthorized User

Run this SQL in Supabase:

```sql
-- First, check what this user created
SELECT 
    (SELECT COUNT(*) FROM concerns WHERE user_id = u.id) as concerns,
    (SELECT COUNT(*) FROM event_requests WHERE user_id = u.id) as events,
    (SELECT COUNT(*) FROM reports WHERE user_id = u.id) as reports
FROM users u
WHERE student_id = '2472104030345';

-- If counts are 0, permanently delete the user
DELETE FROM users
WHERE student_id = '2472104030345';

-- Log the deletion
INSERT INTO activity_logs (action, description, created_at, updated_at, metadata)
VALUES (
    'security_cleanup',
    'Deleted unauthorized user created via OAuth vulnerability: khiem.2472104030345@vanlanguni.vn',
    NOW(),
    NOW(),
    '{"reason": "security_breach", "student_id": "2472104030345", "email": "khiem.2472104030345@vanlanguni.vn"}'
);
```

### 2. Check for Other Unauthorized Users

```sql
-- Find all users with non-STI email domains
SELECT id, name, email, student_id, microsoft_id, created_at, last_login_at
FROM users
WHERE email NOT LIKE '%@novaliches.sti.edu.ph'
  AND email NOT LIKE '%@gmail.com' -- Your admin emails
  AND is_deleted = false
ORDER BY created_at DESC;
```

### 3. Deploy the Fix

```bash
git add app/Http/Controllers/AuthController.php
git commit -m "CRITICAL SECURITY FIX: Add email domain validation to Microsoft OAuth

- Add hasAllowedEmailDomain() check in handleMicrosoftCallback()
- Block unauthorized email domains (@vanlanguni.vn, etc.)
- Add activity logging for OAuth user creation
- Add activity logging for OAuth login attempts
- Log unauthorized OAuth attempts with IP and user agent

Fixes: Unauthorized user creation via Microsoft OAuth bypass
Impact: Prevents users from other organizations accessing the system"

git push origin master
```

### 4. Monitor Logs

After deployment, monitor for unauthorized attempts:

```sql
-- Check for blocked OAuth attempts (after fix is deployed)
SELECT *
FROM activity_logs
WHERE description LIKE '%Unauthorized OAuth login attempt%'
ORDER BY created_at DESC;
```

## Security Audit Findings

### Timeline Reconstruction

**Question:** When was the unauthorized user created?

Run this query:
```sql
SELECT created_at, updated_at, last_login_at
FROM users
WHERE student_id = '2472104030345';
```

**Question:** Did they access any data?

Run these queries:
```sql
-- Check all their activity
SELECT COUNT(*) as total_concerns FROM concerns WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345');
SELECT COUNT(*) as total_events FROM event_requests WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345');
SELECT COUNT(*) as total_reports FROM reports WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345');

-- Check if they viewed anything
SELECT * FROM activity_logs
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345')
ORDER BY created_at DESC;
```

## Lessons Learned

### Why This Happened

1. **Inconsistent validation** - API routes had validation, OAuth didn't
2. **No OAuth audit logging** - User creation wasn't logged
3. **No monitoring** - Unauthorized user went unnoticed
4. **Overly permissive tenant** - `MICROSOFT_TENANT_ID=common` allows any Microsoft account

### Prevention Measures

1. ✅ Add email domain validation to OAuth callback
2. ✅ Add comprehensive activity logging
3. ⚠️ Consider restricting to your specific tenant (not "common")
4. ⚠️ Add automated alerts for new user registrations
5. ⚠️ Add daily security audits for unauthorized email domains

## Additional Recommendations

### 1. Restrict Microsoft Tenant (Optional but Recommended)

In `.env.vercel`, change:
```env
# From:
MICROSOFT_TENANT_ID=common

# To your actual STI Novaliches tenant ID:
MICROSOFT_TENANT_ID=your-actual-tenant-id
```

This prevents ANY Microsoft account from even reaching your callback.

### 2. Add Automated Security Checks

Create a daily cron job:
```php
// Check for unauthorized email domains
$unauthorizedUsers = User::where('email', 'NOT LIKE', '%@novaliches.sti.edu.ph')
    ->where('is_deleted', false)
    ->get();

if ($unauthorizedUsers->count() > 0) {
    // Send alert to admins
    Mail::to('admin@novaliches.sti.edu.ph')->send(new SecurityAlertMail($unauthorizedUsers));
}
```

### 3. Add User Registration Notifications

Notify admins when new users are created:
```php
// In handleMicrosoftCallback, after creating user:
Mail::to('admin@novaliches.sti.edu.ph')->send(
    new NewUserRegistrationMail($user, 'Microsoft OAuth')
);
```

## Status

- [x] Vulnerability identified
- [x] Fix implemented
- [ ] Fix deployed to production
- [ ] Unauthorized user deleted
- [ ] Other unauthorized users checked
- [ ] Monitoring implemented
- [ ] Incident report filed

## Contact

**Reported By:** Security Audit  
**Fixed By:** Development Team  
**Date Fixed:** June 16, 2026  
**Deployed:** Pending

---

**PRIORITY: DEPLOY IMMEDIATELY**
