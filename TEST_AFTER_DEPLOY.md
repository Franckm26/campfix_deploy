# ðŸ§ª Testing After Deployment

## What Was Deployed

âœ… **Pushed to Git:** commit `09e2674`
- Backup compression (gzip)
- Hourly backup configuration
- Automatic cleanup script
- Storage monitoring
- 7-day retention policy
- Complete documentation

## Testing Steps

### 1. Wait for Vercel Deployment (2-3 minutes)

Check deployment status:
- Go to: https://vercel.com/dashboard
- Or check: https://www.campfixsti.com

### 2. Test Storage Report

```bash
curl https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

**Expected Response:**
```json
{
  "success": true,
  "storage_report": {
    "backups": {
      "file_count": X,
      "total_size_formatted": "XX MB"
    },
    "concerns": { ... },
    "profile_pictures": { ... }
  },
  "summary": {
    "total_size_formatted": "XX MB",
    "usage_percent": "X%",
    "remaining_space": "XXX MB"
  }
}
```

### 3. Run Initial Cleanup

```bash
curl https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

**Expected Response:**
```json
{
  "success": true,
  "total_backups": 50,
  "kept_backups": 7,
  "deleted_backups": 43,
  "space_freed": "180 MB"
}
```

### 4. Test New Backup (Compressed)

```bash
curl https://www.campfixsti.com/api/cron/backup-supabase \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

**Expected Response:**
```json
{
  "success": true,
  "filename": "supabase-backup-2026-06-16-HHMMSS.json.gz",
  "tables_backed_up": 3,
  "total_records": XXXX,
  "size_formatted": "1-2 MB",
  "compression_ratio": "85%+"
}
```

### 5. Verify Storage Savings

Run storage report again:
```bash
curl https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

Compare the `usage_percent` before and after cleanup.

## Next Steps

### A. Update Your Cron Schedule (IMPORTANT!)

**If using cron-job.org:**
1. Login to https://cron-job.org/
2. Find your backup job
3. Change schedule from `*/15 * * * *` to `0 * * * *`
4. Save

**If using other cron service:**
- Update schedule to `0 * * * *` (hourly)

### B. Add Cleanup & Report Cron Jobs

**Cleanup Job:**
- URL: `https://www.campfixsti.com/api/cron/cleanup-old-backups`
- Schedule: `0 3 * * *` (Daily at 3 AM)
- Header: `Authorization: Bearer YOUR_CRON_SECRET`

**Storage Report Job (Optional):**
- URL: `https://www.campfixsti.com/api/cron/storage-report`
- Schedule: `0 2 * * 1` (Monday at 2 AM)
- Header: `Authorization: Bearer YOUR_CRON_SECRET`

### C. Monitor for 24 Hours

Check tomorrow to verify:
- âœ… Hourly backups are being created
- âœ… Old backups are being deleted
- âœ… Storage usage is stable/decreasing

## Troubleshooting

### "401 Unauthorized" Error

Check your CRON_SECRET:
```bash
# In .env.vercel file OR
# Vercel Dashboard â†’ Settings â†’ Environment Variables â†’ CRON_SECRET
```

### "Failed to list backups"

Verify environment variables are deployed:
- SUPABASE_URL
- SUPABASE_KEY (service_role key)
- SUPABASE_BACKUP_BUCKET

### No Backups Showing

Check if backups bucket exists in Supabase:
1. Go to Supabase Dashboard
2. Storage â†’ Buckets
3. Look for "backups" bucket

### Still High Storage Usage

1. Run cleanup multiple times
2. Reduce retention: Set `BACKUP_RETENTION_COUNT=24` in Vercel env vars
3. Check which bucket is large: run storage-report

## Expected Results

After 24 hours:
- âœ… 24 new compressed backups created
- âœ… Old uncompressed backups deleted
- âœ… Storage usage: 5-20% of 1 GB
- âœ… Backup coverage: Last 7 days

## Quick Reference Commands

### Get Your CRON_SECRET
Check `.env.vercel` or Vercel dashboard

### Storage Report
```bash
curl https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

### Cleanup Old Backups
```bash
curl https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

### Test Backup
```bash
curl https://www.campfixsti.com/api/cron/backup-supabase \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Success Indicators

âœ… Storage report shows <20% usage
âœ… Cleanup deletes old backups successfully
âœ… New backups are .json.gz (compressed)
âœ… Backup files are 1-2 MB (not 5-20 MB)
âœ… Hourly cron job is running

## Documentation

For detailed information:
- **ACTION_ITEMS.md** - What to do next
- **HOURLY_BACKUP_SUMMARY.md** - Quick summary
- **STORAGE_CLEANUP_INSTRUCTIONS.md** - Management guide
- **README_STORAGE_OPTIMIZATION.md** - Complete overview

## Support

If something isn't working:
1. Check Vercel deployment logs
2. Test endpoints manually with curl
3. Verify environment variables are set
4. Check documentation files listed above

---

**Deployment Status:** âœ… Pushed to master (commit 09e2674)
**Next Action:** Test the endpoints above after Vercel deployment completes
**Time Required:** 5-10 minutes
