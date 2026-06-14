-- Create cache tables for rate limiting
-- Run this in Supabase SQL Editor

-- Check if cache table exists
SELECT 
    table_name, 
    table_type
FROM information_schema.tables 
WHERE table_name IN ('cache', 'cache_locks')
ORDER BY table_name;

-- Create cache table if it doesn't exist
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

-- Create index on expiration for faster cleanup
CREATE INDEX IF NOT EXISTS cache_expiration_index ON cache(expiration);

-- Create cache_locks table if it doesn't exist
CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- Verify tables were created
SELECT 
    table_name,
    (SELECT COUNT(*) FROM information_schema.columns WHERE table_name = t.table_name) as column_count
FROM information_schema.tables t
WHERE table_name IN ('cache', 'cache_locks')
ORDER BY table_name;

-- Show cache table structure
SELECT 
    column_name,
    data_type,
    is_nullable
FROM information_schema.columns
WHERE table_name = 'cache'
ORDER BY ordinal_position;

-- Show cache_locks table structure
SELECT 
    column_name,
    data_type,
    is_nullable
FROM information_schema.columns
WHERE table_name = 'cache_locks'
ORDER BY ordinal_position;
