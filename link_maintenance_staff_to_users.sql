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
    id,
    name,
    email,
    contact_number,
    user_id,
    CASE 
        WHEN user_id IS NULL THEN 'NOT LINKED ❌'
        ELSE 'LINKED ✅'
    END as status
FROM maintenance_staff
WHERE deleted_at IS NULL
ORDER BY user_id IS NULL DESC, name;

-- To create maintenance staff properly going forward, ensure they have a user account first!
