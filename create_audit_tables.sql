-- Create immutable audit tables for reports and concerns
-- Run this on your Supabase SQL Editor
-- These tables are for viewing only - NO DELETES ALLOWED

-- Create audit_reports table
CREATE TABLE IF NOT EXISTS audit_reports (
    id BIGSERIAL PRIMARY KEY,
    original_report_id BIGINT NOT NULL,
    
    -- User info snapshot (immutable)
    user_id BIGINT NULL,
    reporter_name VARCHAR(255) NOT NULL,
    reporter_email VARCHAR(255) NOT NULL,
    reporter_role VARCHAR(255) NOT NULL,
    reporter_department VARCHAR(255) NULL,
    reporter_phone VARCHAR(20) NULL,
    reporter_student_id VARCHAR(255) NULL,
    
    -- Report details
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    details TEXT NULL,
    location VARCHAR(255) NULL,
    severity VARCHAR(50) NULL,
    is_safety_hazard BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) DEFAULT 'Pending',
    photo_path VARCHAR(500) NULL,
    
    -- Category snapshot
    category_id BIGINT NULL,
    category_name VARCHAR(255) NULL,
    
    -- Assignment info
    assigned_to BIGINT NULL,
    assigned_to_name VARCHAR(255) NULL,
    assigned_at TIMESTAMP NULL,
    
    -- Resolution info
    resolution_notes TEXT NULL,
    resolved_at TIMESTAMP NULL,
    cost DECIMAL(10, 2) NULL,
    damaged_part VARCHAR(255) NULL,
    replaced_part VARCHAR(255) NULL,
    
    -- Audit metadata
    action VARCHAR(50) DEFAULT 'created',
    action_by BIGINT NULL,
    action_by_name VARCHAR(255) NULL,
    action_at TIMESTAMP NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create audit_concerns table
CREATE TABLE IF NOT EXISTS audit_concerns (
    id BIGSERIAL PRIMARY KEY,
    original_concern_id BIGINT NOT NULL,
    
    -- User info snapshot (immutable)
    user_id BIGINT NULL,
    reporter_name VARCHAR(255) NOT NULL,
    reporter_email VARCHAR(255) NOT NULL,
    reporter_role VARCHAR(255) NOT NULL,
    reporter_department VARCHAR(255) NULL,
    reporter_phone VARCHAR(20) NULL,
    reporter_student_id VARCHAR(255) NULL,
    is_anonymous BOOLEAN DEFAULT FALSE,
    
    -- Concern details
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    details TEXT NULL,
    location VARCHAR(255) NULL,
    location_type VARCHAR(100) NULL,
    room_number VARCHAR(100) NULL,
    priority VARCHAR(50) NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    image_path VARCHAR(500) NULL,
    
    -- Category snapshot
    category_id BIGINT NULL,
    category_name VARCHAR(255) NULL,
    
    -- Assignment info
    assigned_to BIGINT NULL,
    assigned_to_name VARCHAR(255) NULL,
    assigned_at TIMESTAMP NULL,
    
    -- Resolution info
    resolution_notes TEXT NULL,
    resolved_at TIMESTAMP NULL,
    cost DECIMAL(10, 2) NULL,
    damaged_part VARCHAR(255) NULL,
    replaced_part VARCHAR(255) NULL,
    
    -- Audit metadata
    action VARCHAR(50) DEFAULT 'created',
    action_by BIGINT NULL,
    action_by_name VARCHAR(255) NULL,
    action_at TIMESTAMP NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for faster queries
CREATE INDEX IF NOT EXISTS idx_audit_reports_original_id ON audit_reports(original_report_id);
CREATE INDEX IF NOT EXISTS idx_audit_reports_user_id ON audit_reports(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_reports_status ON audit_reports(status);
CREATE INDEX IF NOT EXISTS idx_audit_reports_action_at ON audit_reports(action_at);

CREATE INDEX IF NOT EXISTS idx_audit_concerns_original_id ON audit_concerns(original_concern_id);
CREATE INDEX IF NOT EXISTS idx_audit_concerns_user_id ON audit_concerns(user_id);
CREATE INDEX IF NOT EXISTS idx_audit_concerns_status ON audit_concerns(status);
CREATE INDEX IF NOT EXISTS idx_audit_concerns_action_at ON audit_concerns(action_at);

-- Add table comments
COMMENT ON TABLE audit_reports IS 'Immutable audit log of all reports - NO DELETES ALLOWED - For record keeping and compliance only';
COMMENT ON TABLE audit_concerns IS 'Immutable audit log of all concerns - NO DELETES ALLOWED - For record keeping and compliance only';

-- Create function to prevent deletes on audit tables
CREATE OR REPLACE FUNCTION prevent_audit_delete()
RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'Deletion is not allowed on audit tables. These are immutable records for compliance.';
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Create triggers to prevent deletes
DROP TRIGGER IF EXISTS prevent_audit_reports_delete ON audit_reports;
CREATE TRIGGER prevent_audit_reports_delete
    BEFORE DELETE ON audit_reports
    FOR EACH ROW
    EXECUTE FUNCTION prevent_audit_delete();

DROP TRIGGER IF EXISTS prevent_audit_concerns_delete ON audit_concerns;
CREATE TRIGGER prevent_audit_concerns_delete
    BEFORE DELETE ON audit_concerns
    FOR EACH ROW
    EXECUTE FUNCTION prevent_audit_delete();

-- Verify tables were created
SELECT 
    'audit_reports' as table_name,
    COUNT(*) as column_count
FROM information_schema.columns 
WHERE table_name = 'audit_reports'
UNION ALL
SELECT 
    'audit_concerns' as table_name,
    COUNT(*) as column_count
FROM information_schema.columns 
WHERE table_name = 'audit_concerns';

-- Test delete prevention (should fail with error)
-- Uncomment to test:
-- DELETE FROM audit_reports WHERE id = 1;
