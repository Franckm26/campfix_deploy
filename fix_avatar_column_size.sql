-- Fix avatar column size error
-- Microsoft OAuth returns very long base64 image URLs that exceed VARCHAR(255)
-- Run this in Supabase SQL Editor

-- Change avatar column from VARCHAR(255) to TEXT
ALTER TABLE users ALTER COLUMN avatar TYPE TEXT;

-- Verify the change
SELECT 
    table_schema,
    table_name,
    column_name,
    data_type,
    character_maximum_length
FROM information_schema.columns 
WHERE table_name = 'users'
AND column_name = 'avatar';
