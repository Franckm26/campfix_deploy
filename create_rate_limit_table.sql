-- Create user_rate_limits table for custom rate limiting
-- Run this in Supabase SQL Editor

-- Create the table (matching Laravel migration structure)
CREATE TABLE IF NOT EXISTS user_rate_limits (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    count INTEGER NOT NULL DEFAULT 0,
    date DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Create indexes for better performance (matching Laravel migration)
CREATE INDEX IF NOT EXISTS user_rate_limits_user_id_action_type_date_index 
ON user_rate_limits(user_id, action_type, date);

CREATE UNIQUE INDEX IF NOT EXISTS user_rate_limits_user_id_action_type_date_unique 
ON user_rate_limits(user_id, action_type, date);

-- Add foreign key constraint
ALTER TABLE user_rate_limits
ADD CONSTRAINT user_rate_limits_user_id_foreign 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Verify table was created
SELECT 
    table_name,
    (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = 'user_rate_limits') as column_count
FROM information_schema.tables
WHERE table_name = 'user_rate_limits';

-- Show table structure
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_name = 'user_rate_limits'
ORDER BY ordinal_position;
