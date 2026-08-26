-- Add backup_email column to users table
-- Run this on your Supabase SQL Editor

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS backup_email VARCHAR(255) NULL;

-- Add comment for documentation
COMMENT ON COLUMN users.backup_email IS 'Alternative email for account recovery';

-- Optional: Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_users_backup_email ON users(backup_email);

-- Verify the column was added
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'users' 
AND column_name = 'backup_email';
