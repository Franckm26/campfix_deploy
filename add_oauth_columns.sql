-- Add Microsoft OAuth columns to users table
-- Run this in Supabase SQL Editor

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS microsoft_id VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL;

-- Add unique constraint to microsoft_id
ALTER TABLE users 
ADD CONSTRAINT users_microsoft_id_unique UNIQUE (microsoft_id);

-- Verify the columns were added
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'users' 
AND column_name IN ('microsoft_id', 'avatar');
