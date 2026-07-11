-- ========================================
-- DELETE UNAUTHORIZED USER
-- ========================================
-- Student ID: REDACTED_STUDENT_ID
-- Email: unauthorized.user@example.edu
-- Reason: Created via OAuth vulnerability
-- ========================================

-- STEP 1: Verify this is the unauthorized user
SELECT
    id,
    uuid,
    name,
    email,
    student_id,
    microsoft_id,
    created_at,
    last_login_at
FROM users
WHERE student_id = 'REDACTED_STUDENT_ID';

-- STEP 2: Check if they created any content (should be 0)
SELECT
    (SELECT COUNT(*) FROM concerns WHERE user_id = (SELECT id FROM users WHERE student_id = 'REDACTED_STUDENT_ID')) as concerns_count,
    (SELECT COUNT(*) FROM event_requests WHERE user_id = (SELECT id FROM users WHERE student_id = 'REDACTED_STUDENT_ID')) as events_count,
    (SELECT COUNT(*) FROM reports WHERE user_id = (SELECT id FROM users WHERE student_id = 'REDACTED_STUDENT_ID')) as reports_count;

-- STEP 3: Delete the unauthorized user (RUN THIS)
DELETE FROM users
WHERE student_id = 'REDACTED_STUDENT_ID'
  AND email = 'unauthorized.user@example.edu';

-- STEP 4: Log the security cleanup
INSERT INTO activity_logs (
    action,
    description,
    created_at,
    updated_at,
    metadata,
    ip_address
)
VALUES (
    'security_cleanup',
    'Deleted unauthorized user created via OAuth vulnerability: [redacted] (unauthorized.user@example.edu)',
    NOW(),
    NOW(),
    jsonb_build_object(
        'reason', 'oauth_bypass_vulnerability',
        'student_id', 'REDACTED_STUDENT_ID',
        'email', 'unauthorized.user@example.edu',
        'name', '[redacted]',
        'domain', '@example.edu',
        'deleted_by', 'security_audit'
    ),
    '127.0.0.1'
);

-- STEP 5: Check for other unauthorized users
SELECT
    id,
    name,
    email,
    student_id,
    microsoft_id,
    created_at,
    last_login_at,
    CASE
        WHEN email NOT LIKE '%@novaliches.sti.edu.ph' THEN 'UNAUTHORIZED DOMAIN'
        ELSE 'OK'
    END as status
FROM users
WHERE email NOT LIKE '%@novaliches.sti.edu.ph'
  AND email NOT LIKE '%@gmail.com' -- Admin emails
  AND is_deleted = false
ORDER BY created_at DESC;

-- STEP 6: Verify deletion
SELECT COUNT(*) as remaining_count
FROM users
WHERE student_id = 'REDACTED_STUDENT_ID';
-- Should return 0

-- ========================================
-- DONE!
-- ========================================
