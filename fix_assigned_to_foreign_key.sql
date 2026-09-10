-- Fix assigned_to foreign key constraint
-- This allows reports and concerns to be assigned to maintenance_staff.id
-- without requiring them to be in the users table

-- Fix reports table
ALTER TABLE reports 
DROP CONSTRAINT IF EXISTS reports_assigned_to_foreign;

-- Fix concerns table (if constraint exists)
ALTER TABLE concerns 
DROP CONSTRAINT IF EXISTS concerns_assigned_to_foreign;

-- Verify the constraints are removed
SELECT 
    conname as constraint_name,
    conrelid::regclass as table_name
FROM pg_constraint
WHERE conname LIKE '%assigned_to%';

-- Should return empty or no assigned_to constraints
