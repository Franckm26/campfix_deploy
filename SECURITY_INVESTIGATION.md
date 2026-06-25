# 🔒 Security Investigation: Suspicious User

## Suspicious User Details

**Student ID:** 2472104030345  
**Name:** Nguyễn Duy Khiêm  
**Class:** 72K30TKDH01  
**Email:** khiem.2472104030345@vanlanguni.vn

## Investigation Steps

### Step 1: Check User Creation Method

The user could have been created through:
1. ✅ **Microsoft OAuth** - If they logged in with @vanlanguni.vn email
2. ❌ **Manual Admin Creation** - If an admin added them
3. ❌ **CSV Import** - If they were bulk imported
4. ❌ **API Registration** - If created via API (unlikely with throttling)
5. ⚠️ **Unauthorized Access** - Security breach

### Step 2: Check Activity Logs

Run this SQL query in your Supabase dashboard:

```sql
-- Check user creation
SELECT *
FROM users  
WHERE student_id = '2472104030345'
ORDER BY created_at DESC;

-- Check activity logs for this user
SELECT *
FROM activity_logs
WHERE description LIKE '%2472104030345%'
   OR description LIKE '%Nguyễn Duy Khiêm%'
   OR description LIKE '%khiem.2472104030345%'
ORDER BY created_at DESC
LIMIT 50;

-- Check who created this user (if created_by is set)
SELECT u.*, creator.name as created_by_name, creator.email as created_by_email
FROM users u
LEFT JOIN users creator ON u.created_by = creator.id
WHERE u.student_id = '2472104030345';

-- Check login history
SELECT *
FROM activity_logs
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345')
  AND action IN ('login', 'login_success', 'microsoft_login', 'google_login')
ORDER BY created_at DESC;
```

### Step 3: Check Microsoft OAuth Logs

If this user was created via Microsoft OAuth:

```sql
SELECT *
FROM users
WHERE student_id = '2472104030345'
  AND microsoft_id IS NOT NULL;
```

**This is LIKELY the case** because:
- Email domain: `@vanlanguni.vn` (matches your Microsoft OAuth setup)
- Student ID format: Standard university format
- Class code: `72K30TKDH01` (looks like a real class)

### Step 4: Verify User Activity

Check if they've done anything:

```sql
-- Check concerns created
SELECT COUNT(*) as concerns_count
FROM concerns
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345');

-- Check event requests
SELECT COUNT(*) as events_count
FROM event_requests
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345');

-- Check reports
SELECT COUNT(*) as reports_count
FROM reports
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345');

-- Check last login
SELECT last_login_at, login_attempts
FROM users
WHERE student_id = '2472104030345';
```

## Most Likely Scenario

### Microsoft OAuth Auto-Registration

**Why this is most likely:**

1. ✅ Email domain `@vanlanguni.vn` matches your Microsoft tenant
2. ✅ Student ID format looks legitimate  
3. ✅ Name is Vietnamese (matches your university)
4. ✅ Class code format matches your system
5. ✅ Your Microsoft OAuth is configured with `common` tenant

**What happened:**
1. Student logged in with their Van Lang University Microsoft account
2. Your OAuth callback automatically created their user account
3. No activity logs appear because OAuth creation doesn't always log to activity_logs table

### Check Microsoft OAuth Configuration

Look at your `.env.vercel`:
```env
MICROSOFT_TENANT_ID=common
```

The `common` tenant allows **ANY** Microsoft account to authenticate, including personal accounts and accounts from other organizations.

## Security Recommendations

### Option 1: Restrict to Your Organization Only (Recommended)

Update `.env.vercel`:

```env
# Change from:
MICROSOFT_TENANT_ID=common

# Change to your actual tenant ID:
MICROSOFT_TENANT_ID=your-actual-tenant-id
```

**How to find your tenant ID:**
1. Go to Azure Portal: https://portal.azure.com
2. Azure Active Directory → Overview
3. Copy "Tenant ID"

### Option 2: Add Email Domain Validation

In `AuthController.php`, add domain validation:

```php
// After Microsoft OAuth callback
if (!str_ends_with($email, '@vanlanguni.vn')) {
    return redirect('/login')->with('error', 'Only Van Lang University emails are allowed');
}
```

### Option 3: Add Manual Approval for New Users

Add a field `is_approved` to users table and require admin approval for OAuth users.

## Immediate Actions

### 1. Check if User is Legitimate

Ask yourself:
- Is this a real student at your university?
- Does the class code `72K30TKDH01` exist?
- Can you verify this student ID in your student database?

### 2. If Legitimate - No Action Needed

This is just a student who logged in with their university Microsoft account.

### 3. If Suspicious - Delete User

```sql
-- Soft delete
UPDATE users
SET is_deleted = true, deleted_at = NOW()
WHERE student_id = '2472104030345';

-- Or permanent delete
DELETE FROM users
WHERE student_id = '2472104030345';
```

### 4. Prevent Future Unauthorized Access

Implement one of the security recommendations above.

## Check Current OAuth Settings

Run this to see your current Microsoft OAuth config:

```bash
# In your .env.vercel file
grep MICROSOFT .env.vercel
```

**Expected output:**
```
MICROSOFT_CLIENT_ID=bf0facf2-f1d8-418c-8d55-43a98a9ce3d5
MICROSOFT_CLIENT_SECRET=***REDACTED***
MICROSOFT_REDIRECT_URI=https://www.campfixsti.com/auth/microsoft/callback
MICROSOFT_TENANT_ID=common  ⚠️ THIS ALLOWS ANY MICROSOFT ACCOUNT
```

## Security Checklist

- [ ] Check Supabase activity_logs table for user creation
- [ ] Verify if user has `microsoft_id` field populated
- [ ] Check if user has logged in (last_login_at)
- [ ] Check if user has created any concerns/events/reports
- [ ] Verify if student ID is legitimate
- [ ] Check if class code exists in your system
- [ ] Review Microsoft OAuth tenant configuration
- [ ] Consider restricting to your organization tenant
- [ ] Add email domain validation if needed
- [ ] Document finding for future reference

## Expected Finding

**Most Likely Result:**
- ✅ User created via Microsoft OAuth
- ✅ Legitimate Van Lang University student
- ✅ Auto-registered when they logged in
- ✅ No security breach

**Action:** Update OAuth configuration to restrict to your tenant only.

## SQL Query to Run Now

Run this in Supabase SQL Editor:

```sql
SELECT 
    id,
    name,
    email,
    student_id,
    role,
    microsoft_id,
    google_id,
    created_by,
    created_at,
    last_login_at,
    email_verified_at,
    is_deleted,
    is_archived
FROM users
WHERE student_id = '2472104030345';
```

This will show you:
- When the user was created
- If they have a microsoft_id (proves OAuth creation)
- If they've ever logged in
- Who created them (if created_by is set)

---

**Status:** Investigation in progress  
**Risk Level:** Low (likely legitimate OAuth registration)  
**Action Required:** Verify student legitimacy + restrict OAuth tenant
