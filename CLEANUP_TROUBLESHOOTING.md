# ðŸ”§ Cleanup Script Troubleshooting

## Issue: 500 Internal Server Error

### What Was Fixed (Commit 8f09db4)

1. âœ… Changed to POST method for Supabase Storage API list operation
2. âœ… Added graceful handling for missing backup bucket
3. âœ… Fixed default retention to 168 backups (7 days)
4. âœ… Improved error messages with cURL errors
5. âœ… Fixed file path handling in delete operation

### Wait for New Deployment

**Status:** Pushed to master (commit `8f09db4`)
**Wait time:** 2-3 minutes for Vercel to redeploy

### Test After Deployment

#### Option 1: Test via cron-job.org
1. Go back to cron-job.org
2. Click "Test Run" button again
3. Should now return 200 OK

#### Option 2: Test via curl
```bash
curl https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

### Expected Responses

#### If No Backups Exist Yet:
```json
{
  "success": true,
  "message": "No backups found or backup bucket not created yet",
  "total_backups": 0,
  "deleted_backups": 0,
  "space_freed": "0 B",
  "timestamp": "2026-06-16 12:00:00"
}
```

#### If Backups Exist:
```json
{
  "success": true,
  "message": "Backup cleanup completed",
  "total_backups": 50,
  "kept_backups": 7,
  "deleted_backups": 43,
  "space_freed": "180.5 MB",
  "deleted_files": ["backup1.json.gz", "backup2.json.gz", ...],
  "retention_policy": "Keep last 168 backups",
  "timestamp": "2026-06-16 12:00:00"
}
```

### If Still Getting Errors

#### Check Environment Variables in Vercel

Go to Vercel Dashboard â†’ Your Project â†’ Settings â†’ Environment Variables

Verify these are set:
- âœ… `SUPABASE_URL` (without https://)
- âœ… `SUPABASE_KEY` (service_role key, not anon key)
- âœ… `CRON_SECRET` (your secret)
- âš¡ `BACKUP_RETENTION_COUNT` = `168` (optional, defaults to 168)
- âš¡ `SUPABASE_BACKUP_BUCKET` = `backups` (optional, defaults to backups)

#### Create Backup Bucket in Supabase

If you haven't created a backup bucket yet:

1. Go to Supabase Dashboard
2. Click "Storage" in sidebar
3. Click "Create a new bucket"
4. Name: `backups`
5. Public: âŒ No (keep private)
6. Click "Create bucket"

#### Test Storage Report First

This will show if your Supabase connection is working:

```bash
curl https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Common Error Messages

### "Unauthorized - Invalid or missing CRON_SECRET"
**Problem:** CRON_SECRET doesn't match
**Solution:** Check your secret in .env.vercel or Vercel dashboard

### "SUPABASE_URL or SUPABASE_KEY not configured"
**Problem:** Environment variables missing in Vercel
**Solution:** Add them in Vercel dashboard â†’ Environment Variables

### "Failed to list backups: HTTP 404"
**Problem:** Backup bucket doesn't exist yet
**Solution:** Create 'backups' bucket in Supabase or run a backup first

### "Failed to list backups: HTTP 400"
**Problem:** API request format issue
**Solution:** Fixed in commit 8f09db4, redeploy

## Next Steps After It Works

### 1. Save the Cron Job
Once the test passes (200 OK):
- Click "SAVE" button in cron-job.org
- The job will now run daily at 3:00 AM

### 2. Add Storage Report Job (Optional)
Create another cron job for monitoring:
- Title: `CampFix Storage Report`
- URL: `https://www.campfixsti.com/api/cron/storage-report`
- Schedule: `0 2 * * 1` (Monday 2 AM)
- Header: `Authorization: Bearer YOUR_CRON_SECRET`

### 3. Update Existing Backup Job
Edit your existing "BACKUP" job:
- Change schedule from `*/15 * * * *` to `0 * * * *` (hourly)
- This reduces backups from 96/day to 24/day

## Summary of Cron Jobs

After setup, you should have 3 cron jobs:

| Job | URL | Schedule | Purpose |
|-----|-----|----------|---------|
| BACKUP | `/api/cron/backup-supabase` | `0 * * * *` | Hourly backup |
| CLEANUP | `/api/cron/cleanup-old-backups` | `0 3 * * *` | Daily cleanup |
| REPORT | `/api/cron/storage-report` | `0 2 * * 1` | Weekly monitoring |

## Testing Checklist

- [ ] Wait 2-3 minutes for Vercel deployment
- [ ] Test cleanup endpoint (should return 200 OK)
- [ ] Create backups bucket in Supabase if needed
- [ ] Run a test backup to verify it works
- [ ] Run cleanup again to test with actual backups
- [ ] Save cron job in cron-job.org
- [ ] Update backup job to hourly schedule
- [ ] Verify all 3 jobs are saved and scheduled

---

**Fix Version:** Commit 8f09db4
**Deployment:** In progress
**ETA:** 2-3 minutes
