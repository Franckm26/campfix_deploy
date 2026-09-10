-- Script to link existing maintenance_staff records to users table
-- Run this after adding the user_id column to maintenance_staff table

-- First, create user accounts for maintenance staff if they don't exist
-- You'll need to manually create these users in your application with role='maintenance'

-- Example: Link maintenance staff to users by matching email
-- UPDATE maintenance_staff 
-- SET user_id = (SELECT id FROM users WHERE email = maintenance_staff.email AND role = 'maintenance')
-- WHERE email IS NOT NULL AND email != '';

-- Or manually link by ID if you know the mappings:
-- UPDATE maintenance_staff SET user_id = 123 WHERE id = 1; -- Replace with actual IDs

-- Check which maintenance staff don't have linked user accounts:
SELECT 
    ms.id as staff_id,
    ms.name as staff_name,
    ms.email as staff_email,
    ms.contact_number,
    ms.user_id,
    u.id as user_account_id,
    u.email as user_email,
    u.role as user_role,
    CASE 
        WHEN ms.user_id IS NULL THEN '❌ NOT LINKED - CREATE USER ACCOUNT'
        WHEN u.id IS NULL THEN '⚠️ BROKEN LINK - USER DELETED'
        WHEN u.role != 'maintenance' THEN '⚠️ WRONG ROLE - Should be maintenance'
        ELSE '✅ LINKED CORRECTLY'
    END as status
FROM maintenance_staff ms
LEFT JOIN users u ON ms.user_id = u.id
WHERE ms.deleted_at IS NULL
ORDER BY ms.user_id IS NULL DESC, ms.name;

-- To link maintenance staff automatically by matching email:
-- Run this ONLY after creating user accounts with role='maintenance'
UPDATE maintenance_staff 
SET user_id = (
    SELECT id 
    FROM users 
    WHERE users.email = maintenance_staff.email 
    AND users.role = 'maintenance'
    LIMIT 1
)
WHERE user_id IS NULL 
AND email IS NOT NULL 
AND email != ''
AND EXISTS (
    SELECT 1 
    FROM users 
    WHERE users.email = maintenance_staff.email 
    AND users.role = 'maintenance'
);

-- Check results after linking:
SELECT COUNT(*) as linked_count FROM maintenance_staff WHERE user_id IS NOT NULL AND deleted_at IS NULL;
SELECT COUNT(*) as not_linked_count FROM maintenance_staff WHERE user_id IS NULL AND deleted_at IS NULL;

-- To create maintenance staff properly going forward:
-- 1. First create a user account with role='maintenance'
-- 2. Then create maintenance_staff record with user_id = user.id
