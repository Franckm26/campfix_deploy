-- Delete reports with titles "Door" and "Electrical outlet"
-- Make sure to backup your database first!

-- Check what will be deleted (run this first to verify)
SELECT id, title, location, status, created_at 
FROM campfix.reports 
WHERE title IN ('Door', 'Electrical outlet');

-- If the above looks correct, uncomment and run this:
-- DELETE FROM campfix.reports WHERE title IN ('Door', 'Electrical outlet');
