-- Add Microsoft OAuth columns to users table
-- Run this in Supabase SQL Editor

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS microsoft_id VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS avatar TEXT NULL;

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_microsoft_id ON users(microsoft_id);

-- Verify columns were added
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'users' 
AND column_name IN ('microsoft_id', 'avatar');
