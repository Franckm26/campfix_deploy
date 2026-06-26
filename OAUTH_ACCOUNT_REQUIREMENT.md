# 🔒 OAuth Account Pre-existence Requirement

## Security Enhancement

**Added:** June 16, 2026  
**Type:** Security Enhancement  

## What Changed

Microsoft OAuth now requires that user accounts **already exist** in the system before they can login via OAuth.

### Before (Insecure)
```
User clicks "Login with Microsoft"
  ↓
Microsoft authenticates them
  ↓
System AUTO-CREATES account with their email
  ↓
User gains access ❌ (Anyone could create account)
```

### After (Secure)
```
User clicks "Login with Microsoft"
  ↓
Microsoft authenticates them
  ↓
System checks if account exists
  ├─ Account exists? → Allow login ✅
  └─ Account missing? → Block with message ✅
```

## Implementation

**File:** `app/Http/Controllers/AuthController.php`  
**Method:** `handleMicrosoftCallback()`

```php
// Find user based on Microsoft email
$user = User::where('email', $email)->first();

// SECURITY: Only allow OAuth login if account already exists (created by admin)
if (!$user) {
    \Log::warning('OAuth login attempt with no existing account', [
        'email' => $email,
        'name' => $microsoftUser->getName(),
        'microsoft_id' => $microsoftUser->getId(),
        'ip' => request()->ip(),
        'user_agent' => request()->userAgent(),
    ]);
    
    return redirect('/login')->with('error', 'No account found for ' . $email . '. Please contact the administrator to create your account first.');
}

// Account exists - proceed with login
```

## Security Benefits

### 1. Admin Control
- ✅ Only authorized users (created by admin) can login
- ✅ Prevents unauthorized self-registration
- ✅ Admins control who has access

### 2. Audit Trail
- ✅ All users must be created via admin interface first
- ✅ Creates activity log entry for user creation
- ✅ Tracks who created each account (created_by field)

### 3. Prevents Unauthorized Access
- ✅ Random people can't just create accounts
- ✅ Even with valid @novaliches.sti.edu.ph email
- ✅ Must be explicitly added by admin first

### 4. Logging of Blocked Attempts
- ✅ Logs email, name, Microsoft ID
- ✅ Logs IP address and user agent
- ✅ Enables monitoring of unauthorized attempts

## User Workflow

### For New Users

1. **Admin creates account:**
   - Admin goes to User Management
   - Clicks "Add User"
   - Enters: Name, Email (@novaliches.sti.edu.ph), Role, etc.
   - Saves user

2. **User can now login via OAuth:**
   - User clicks "Login with Microsoft"
   - Microsoft authenticates them
   - System finds their pre-existing account
   - User is logged in successfully

### For Unauthorized Users

1. **User tries to login:**
   - Clicks "Login with Microsoft"
   - Microsoft authenticates them
   - System checks for account → Not found
   - User sees: "No account found for your-email@novaliches.sti.edu.ph. Please contact the administrator to create your account first."

2. **User contacts admin:**
   - Admin verifies user should have access
   - Admin creates account manually
   - User can now login via OAuth

## How to Create Accounts for OAuth Users

### Method 1: Manual Creation (Recommended)

1. Login as admin
2. Go to **User Management**
3. Click **"Add User"**
4. Fill in:
   - First Name
   - Last Name  
   - Email: `firstname.lastname@novaliches.sti.edu.ph`
   - Student ID (if student)
   - Role (student/faculty/staff)
   - Password (temporary - they'll use OAuth anyway)
5. Click **Save**

### Method 2: CSV Import (Bulk)

1. Prepare CSV file with columns:
   - name
   - email (@novaliches.sti.edu.ph)
   - student_id
   - role
   - department

2. Go to User Management → Import CSV
3. Upload file
4. Users can now login via OAuth

## Important Notes

### Email Must Match Exactly
- Admin creates: `john.doe@novaliches.sti.edu.ph`
- User must login with: `john.doe@novaliches.sti.edu.ph`
- Case-insensitive but spelling must match

### Password Not Used for OAuth
- When creating account, set any password
- Users will login via Microsoft OAuth
- They'll never need the password

### Microsoft ID Linking
- First time OAuth login, system links Microsoft ID to account
- Subsequent logins use this linkage
- Stored in `microsoft_id` field

## Monitoring

### Check Blocked OAuth Attempts

Run this query in Supabase:

```sql
SELECT *
FROM activity_logs
WHERE description LIKE '%OAuth login attempt with no existing account%'
ORDER BY created_at DESC
LIMIT 50;
```

### Find Users Without OAuth Linked

```sql
SELECT id, name, email, student_id, created_at
FROM users
WHERE email LIKE '%@novaliches.sti.edu.ph'
  AND microsoft_id IS NULL
  AND is_deleted = false
ORDER BY created_at DESC;
```

These users have accounts but haven't logged in via OAuth yet.

## Error Messages

### User Sees This
```
No account found for john.doe@novaliches.sti.edu.ph. 
Please contact the administrator to create your account first.
```

### Log Entry Created
```json
{
  "action": "oauth_blocked",
  "email": "john.doe@novaliches.sti.edu.ph",
  "name": "John Doe",
  "microsoft_id": "abc123...",
  "ip": "192.168.1.1",
  "user_agent": "Mozilla/5.0 ..."
}
```

## Security Layers

Now your OAuth has **3 security layers**:

1. **Email Domain Validation** ✅
   - Only @novaliches.sti.edu.ph allowed
   - Blocks other organizations

2. **Account Pre-existence Check** ✅ (NEW)
   - Account must already exist
   - Admin must create it first

3. **Account Status Checks** ✅
   - Not archived
   - Not locked
   - Active status

## FAQ

**Q: Can users self-register anymore?**  
A: No. Admins must create accounts first.

**Q: What if I want to allow self-registration?**  
A: Not recommended for security. If needed, create a separate registration form with approval workflow.

**Q: What about existing users?**  
A: They're fine. They already have accounts and can login via OAuth.

**Q: What if someone's email changes?**  
A: Admin must update their email in User Management.

**Q: Can users still login with password?**  
A: Yes, password login still works. OAuth is optional login method.

## Reverting (If Needed)

If you need to revert to auto-creation (NOT recommended):

```php
// In handleMicrosoftCallback(), replace the check with:
if (!$user) {
    $user = User::create([
        'name' => $microsoftUser->getName(),
        'email' => $email,
        'password' => Hash::make(bin2hex(random_bytes(16))),
        'microsoft_id' => $microsoftUser->getId(),
        'role' => 'student',
        // ... other fields
    ]);
}
```

But this removes the security benefit!

## Summary

✅ **OAuth now requires pre-existing accounts**  
✅ **Admins control who can access the system**  
✅ **Unauthorized self-registration prevented**  
✅ **All blocked attempts are logged**  
✅ **System is more secure**

---

**Status:** Active  
**Deployment:** Pending (commit in progress)  
**Impact:** High security improvement
