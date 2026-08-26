-- Create immutable audit tables for reports and concerns
-- Run this on your Supabase SQL Editor
-- These tables are for viewing only - NO DELETES ALLOWED

-- Drop tables if they exist (for clean installation)
DROP TABLE IF EXISTS audit_reports CASCADE;
DROP TABLE IF EXISTS audit_concerns CASCADE;

-- Create audit_reports table
CREATE TABLE audit_reports (
    id BIGSERIAL PRIMARY KEY,
    original_report_id BIGINT NOT NULL,
    
    -- User info snapshot (immutable)
    user_id BIGINT,
    reporter_name VARCHAR(255) NOT NULL,
    reporter_email VARCHAR(255) NOT NULL,
    reporter_role VARCHAR(255) NOT NULL,
    reporter_department VARCHAR(255),
    reporter_phone VARCHAR(20),
    reporter_student_id VARCHAR(255),
    
    -- Report details
    title VARCHAR(255) NOT NULL,
    description TEXT,
    details TEXT,
    location VARCHAR(255),
    severity VARCHAR(50),
    is_safety_hazard BOOLEAN DEFAULT FALSE,
    status VARCHAR(50) DEFAULT 'Pending',
    photo_path VARCHAR(500),
    
    -- Category snapshot
    category_id BIGINT,
    category_name VARCHAR(255),
    
    -- Assignment info
    assigned_to BIGINT,
    assigned_to_name VARCHAR(255),
    assigned_at TIMESTAMP,
    
    -- Resolution info
    resolution_notes TEXT,
    resolved_at TIMESTAMP,
    cost DECIMAL(10, 2),
    damaged_part VARCHAR(255),
    replaced_part VARCHAR(255),
    
    -- Audit metadata
    action VARCHAR(50) DEFAULT 'created',
    action_by BIGINT,
    action_by_name VARCHAR(255),
    action_at TIMESTAMP NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create audit_concerns table
CREATE TABLE audit_concerns (
    id BIGSERIAL PRIMARY KEY,
    original_concern_id BIGINT NOT NULL,
    
    -- User info snapshot (immutable)
    user_id BIGINT,
    reporter_name VARCHAR(255) NOT NULL,
    reporter_email VARCHAR(255) NOT NULL,
    reporter_role VARCHAR(255) NOT NULL,
    reporter_department VARCHAR(255),
    reporter_phone VARCHAR(20),
    reporter_student_id VARCHAR(255),
    is_anonymous BOOLEAN DEFAULT FALSE,
    
    -- Concern details
    title VARCHAR(255) NOT NULL,
    description TEXT,
    details TEXT,
    location VARCHAR(255),
    location_type VARCHAR(100),
    room_number VARCHAR(100),
    priority VARCHAR(50),
    status VARCHAR(50) DEFAULT 'Pending',
    image_path VARCHAR(500),
    
    -- Category snapshot
    category_id BIGINT,
    category_name VARCHAR(255),
    
    -- Assignment info
    assigned_to BIGINT,
    assigned_to_name VARCHAR(255),
    assigned_at TIMESTAMP,
    
    -- Resolution info
    resolution_notes TEXT,
    resolved_at TIMESTAMP,
    cost DECIMAL(10, 2),
    damaged_part VARCHAR(255),
    replaced_part VARCHAR(255),
    
    -- Audit metadata
    action VARCHAR(50) DEFAULT 'created',
    action_by BIGINT,
    action_by_name VARCHAR(255),
    action_at TIMESTAMP NOT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes for faster queries
CREATE INDEX idx_audit_reports_original_id ON audit_reports(original_report_id);
CREATE INDEX idx_audit_reports_user_id ON audit_reports(user_id);
CREATE INDEX idx_audit_reports_status ON audit_reports(status);
CREATE INDEX idx_audit_reports_action_at ON audit_reports(action_at);

CREATE INDEX idx_audit_concerns_original_id ON audit_concerns(original_concern_id);
CREATE INDEX idx_audit_concerns_user_id ON audit_concerns(user_id);
CREATE INDEX idx_audit_concerns_status ON audit_concerns(status);
CREATE INDEX idx_audit_concerns_action_at ON audit_concerns(action_at);

-- Create function to prevent deletes on audit tables
CREATE OR REPLACE FUNCTION prevent_audit_delete()
RETURNS TRIGGER AS $$
BEGIN
    RAISE EXCEPTION 'Deletion is not allowed on audit tables. These are immutable records for compliance.';
END;
$$ LANGUAGE plpgsql;

-- Create triggers to prevent deletes
CREATE TRIGGER prevent_audit_reports_delete
    BEFORE DELETE ON audit_reports
    FOR EACH ROW
    EXECUTE FUNCTION prevent_audit_delete();

CREATE TRIGGER prevent_audit_concerns_delete
    BEFORE DELETE ON audit_concerns
    FOR EACH ROW
    EXECUTE FUNCTION prevent_audit_delete();

-- Verify tables were created
SELECT 
    table_name,
    (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name) as column_count
FROM (
    VALUES ('audit_reports'), ('audit_concerns')
) AS t(table_name)
WHERE EXISTS (
    SELECT 1 FROM information_schema.tables 
    WHERE table_name = t.table_name
);
