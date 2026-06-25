-- Security Investigation: Check Suspicious User
-- Student ID: 2472104030345
-- Name: Nguyễn Duy Khiêm

-- ============================================
-- 1. GET USER DETAILS
-- ============================================
SELECT 
    id,
    uuid,
    name,
    email,
    student_id,
    role,
    department,
    phone,
    microsoft_id,
    google_id,
    created_by,
    created_at,
    updated_at,
    last_login_at,
    email_verified_at,
    login_attempts,
    is_deleted,
    is_archived,
    force_password_change
FROM users
WHERE student_id = '2472104030345';

-- ============================================
-- 2. CHECK WHO CREATED THIS USER
-- ============================================
SELECT 
    u.id as user_id,
    u.name as user_name,
    u.email as user_email,
    u.created_at as user_created_at,
    creator.id as creator_id,
    creator.name as creator_name,
    creator.email as creator_email,
    creator.role as creator_role
FROM users u
LEFT JOIN users creator ON u.created_by = creator.id
WHERE u.student_id = '2472104030345';

-- ============================================
-- 3. CHECK ACTIVITY LOGS
-- ============================================
SELECT 
    id,
    action,
    description,
    user_id,
    ip_address,
    user_agent,
    created_at,
    metadata
FROM activity_logs
WHERE description LIKE '%2472104030345%'
   OR description LIKE '%Nguyễn Duy Khiêm%'
   OR description LIKE '%khiem.2472104030345%'
   OR user_id = (SELECT id FROM users WHERE student_id = '2472104030345')
ORDER BY created_at DESC
LIMIT 100;

-- ============================================
-- 4. CHECK LOGIN HISTORY
-- ============================================
SELECT 
    id,
    action,
    description,
    ip_address,
    user_agent,
    created_at
FROM activity_logs
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345')
  AND (
      action LIKE '%login%' 
      OR action IN ('login', 'login_success', 'login_failed', 'microsoft_login', 'google_login', 'logout')
  )
ORDER BY created_at DESC;

-- ============================================
-- 5. CHECK USER ACTIVITY (Concerns, Events, Reports)
-- ============================================
-- Concerns
SELECT 
    'concern' as type,
    id,
    title,
    status,
    created_at
FROM concerns
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345')
ORDER BY created_at DESC;

-- Event Requests
SELECT 
    'event' as type,
    id,
    title,
    status,
    created_at
FROM event_requests
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345')
ORDER BY created_at DESC;

-- Reports
SELECT 
    'report' as type,
    id,
    title,
    status,
    created_at
FROM reports
WHERE user_id = (SELECT id FROM users WHERE student_id = '2472104030345')
ORDER BY created_at DESC;

-- ============================================
-- 6. CHECK IF MICROSOFT OAUTH USER
-- ============================================
SELECT 
    id,
    name,
    email,
    microsoft_id,
    CASE 
        WHEN microsoft_id IS NOT NULL THEN 'Created via Microsoft OAuth'
        WHEN google_id IS NOT NULL THEN 'Created via Google OAuth'
        WHEN created_by IS NOT NULL THEN 'Created by Admin'
        ELSE 'Manual Registration or Unknown'
    END as creation_method,
    created_at
FROM users
WHERE student_id = '2472104030345';

-- ============================================
-- 7. CHECK RECENT USERS WITH SIMILAR PATTERN
-- ============================================
-- Check if there are other suspicious users created around the same time
SELECT 
    id,
    name,
    email,
    student_id,
    microsoft_id,
    created_by,
    created_at,
    last_login_at
FROM users
WHERE created_at >= (
    SELECT created_at - INTERVAL '1 hour' 
    FROM users 
    WHERE student_id = '2472104030345'
)
AND created_at <= (
    SELECT created_at + INTERVAL '1 hour' 
    FROM users 
    WHERE student_id = '2472104030345'
)
ORDER BY created_at DESC;

-- ============================================
-- 8. CHECK ALL OAUTH USERS (MICROSOFT)
-- ============================================
-- List all users created via Microsoft OAuth
SELECT 
    id,
    name,
    email,
    student_id,
    microsoft_id,
    created_at,
    last_login_at
FROM users
WHERE microsoft_id IS NOT NULL
ORDER BY created_at DESC
LIMIT 20;

-- ============================================
-- 9. SECURITY CHECK: USERS WITHOUT ACTIVITY
-- ============================================
-- Find all users who have never logged in or created anything
SELECT 
    u.id,
    u.name,
    u.email,
    u.student_id,
    u.created_at,
    u.last_login_at,
    CASE 
        WHEN u.microsoft_id IS NOT NULL THEN 'Microsoft OAuth'
        WHEN u.created_by IS NOT NULL THEN 'Admin Created'
        ELSE 'Unknown'
    END as source,
    (SELECT COUNT(*) FROM concerns WHERE user_id = u.id) as concerns_count,
    (SELECT COUNT(*) FROM event_requests WHERE user_id = u.id) as events_count
FROM users u
WHERE u.last_login_at IS NULL
  AND u.created_at < NOW() - INTERVAL '7 days'
  AND u.is_deleted = false
ORDER BY u.created_at DESC
LIMIT 50;

-- ============================================
-- INTERPRETATION GUIDE
-- ============================================
/*
WHAT TO LOOK FOR:

1. If microsoft_id IS NOT NULL:
   - User was created via Microsoft OAuth login
   - This is normal if they used their @vanlanguni.vn email
   - Check if student_id format is valid

2. If created_by IS NOT NULL:
   - User was manually created by an admin
   - Check who the creator is (should be a known admin)

3. If microsoft_id IS NULL AND created_by IS NULL:
   - User might have registered manually
   - OR could be a security issue
   - Check activity_logs for clues

4. If last_login_at IS NULL AND created_at > 24 hours ago:
   - User never logged in after creation
   - Could be:
     * OAuth test account
     * Bot registration
     * Legitimate user who hasn't logged in yet

5. If no activity_logs found:
   - OAuth users might not have creation logs
   - Check for ANY activity at all
   - If zero activity + old account = suspicious

NORMAL SCENARIOS:
- Microsoft OAuth with @vanlanguni.vn email = LEGITIMATE
- Admin created with valid creator = LEGITIMATE
- Has login history + activity = LEGITIMATE

SUSPICIOUS SCENARIOS:
- No microsoft_id, no created_by, no activity = INVESTIGATE
- Strange email domain = INVESTIGATE
- Created recently but no login = WAIT AND MONITOR
- Multiple similar accounts in short time = POSSIBLE ATTACK

RECOMMENDED ACTION:
1. If microsoft_id exists + valid email domain = SAFE
2. If suspicious = Delete or archive the user
3. Update Microsoft OAuth to restrict tenant
*/
