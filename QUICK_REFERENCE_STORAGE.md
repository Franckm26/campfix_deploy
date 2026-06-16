# 🚨 Quick Reference: Storage Commands

## Check Storage Usage
```bash
curl https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Clean Up Old Backups
```bash
curl https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Get Your CRON_SECRET

From your `.env.vercel` file or Vercel environment variables.

## What to Expect

### Storage Report Output
```json
{
  "summary": {
    "total_size_formatted": "45 MB",
    "usage_percent": "4.4%",
    "remaining_space": "979 MB"
  }
}
```

### Cleanup Output
```json
{
  "success": true,
  "deleted_backups": 15,
  "space_freed": "180 MB",
  "kept_backups": 7
}
```

## Emergency: Out of Storage?

1. **Immediate action:**
   ```bash
   # Clean up backups now
   curl https://www.campfixsti.com/api/cron/cleanup-old-backups \
     -H "Authorization: Bearer YOUR_CRON_SECRET"
   ```

2. **If still full, reduce retention:**
   - Add `BACKUP_RETENTION_COUNT=3` to environment variables
   - Run cleanup again

3. **Nuclear option (keep only 1 backup):**
   - Set `BACKUP_RETENTION_COUNT=1`
   - Run cleanup

## Files Created
- ✅ `/api/cron/cleanup-old-backups.php` - Cleanup script
- ✅ `/api/cron/storage-report.php` - Storage monitor
- ✅ `/STORAGE_CLEANUP_INSTRUCTIONS.md` - Detailed guide
- ✅ `/SUPABASE_STORAGE_OPTIMIZATION.md` - Technical docs
- ✅ `/STORAGE_CHANGES_SUMMARY.md` - Complete summary

## Files Modified
- 🔧 `/api/cron/backup-supabase.php` - Now compresses backups
- 🔧 `/vercel.json` - Added cleanup routes

## Deploy Command
```bash
git add .
git commit -m "feat: optimize supabase storage"
git push
```

---
**Keep this file handy for quick reference!**
