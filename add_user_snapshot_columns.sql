-- Add user snapshot columns to reports and concerns tables
-- Run this on your Supabase SQL Editor

-- Add snapshot fields to reports table
ALTER TABLE reports 
ADD COLUMN IF NOT EXISTS reporter_email VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reporter_role VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reporter_department VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reporter_phone VARCHAR(20) NULL,
ADD COLUMN IF NOT EXISTS reporter_student_id VARCHAR(255) NULL;

-- Add snapshot fields to concerns table
ALTER TABLE concerns 
ADD COLUMN IF NOT EXISTS reporter_name VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reporter_email VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reporter_role VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reporter_department VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS reporter_phone VARCHAR(20) NULL,
ADD COLUMN IF NOT EXISTS reporter_student_id VARCHAR(255) NULL;

-- Add comments for documentation
COMMENT ON COLUMN reports.reporter_email IS 'Snapshot of reporter email at time of report creation (immutable)';
COMMENT ON COLUMN reports.reporter_role IS 'Snapshot of reporter role at time of report creation (immutable)';
COMMENT ON COLUMN reports.reporter_department IS 'Snapshot of reporter department at time of report creation (immutable)';
COMMENT ON COLUMN reports.reporter_phone IS 'Snapshot of reporter phone at time of report creation (immutable)';
COMMENT ON COLUMN reports.reporter_student_id IS 'Snapshot of reporter student ID at time of report creation (immutable)';

COMMENT ON COLUMN concerns.reporter_name IS 'Snapshot of reporter name at time of concern creation (immutable)';
COMMENT ON COLUMN concerns.reporter_email IS 'Snapshot of reporter email at time of concern creation (immutable)';
COMMENT ON COLUMN concerns.reporter_role IS 'Snapshot of reporter role at time of concern creation (immutable)';
COMMENT ON COLUMN concerns.reporter_department IS 'Snapshot of reporter department at time of concern creation (immutable)';
COMMENT ON COLUMN concerns.reporter_phone IS 'Snapshot of reporter phone at time of concern creation (immutable)';
COMMENT ON COLUMN concerns.reporter_student_id IS 'Snapshot of reporter student ID at time of concern creation (immutable)';

-- Verify columns were added
SELECT 'reports' as table_name, column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'reports' 
AND column_name LIKE 'reporter_%'
UNION ALL
SELECT 'concerns' as table_name, column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'concerns' 
AND column_name LIKE 'reporter_%'
ORDER BY table_name, column_name;
