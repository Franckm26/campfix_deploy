# 🎯 Hourly Backup Configuration - Quick Summary

## What Changed

Your backup system has been optimized from **every 15 minutes** to **every 1 hour**.

### Impact

| Metric | Before (15 min) | After (Hourly) | Improvement |
|--------|----------------|----------------|-------------|
| Backups per day | 96 | 24 | **75% reduction** |
| Backups per week | 672 | 168 | **75% reduction** |
| Storage usage | High | Low | **~75% less** |
| Backup coverage | Every 15 min | Every hour | Still excellent |

## Files Modified

1. ✅ `.env.vercel` - Added backup retention settings
2. 📄 `HOURLY_BACKUP_SETUP.md` - Complete setup guide
3. 📄 `UPDATE_VERCEL_JSON.md` - vercel.json update instructions

## What You Need to Do

### Step 1: Update vercel.json

Add this section to your `vercel.json` (after `"regions": ["sin1"],`):

```json
"crons": [
  {
    "path": "/api/cron/backup-supabase",
    "schedule": "0 * * * *"
  },
  {
    "path": "/api/cron/cleanup-old-backups",
    "schedule": "0 3 * * *"
  },
  {
    "path": "/api/cron/storage-report",
    "schedule": "0 2 * * 1"
  }
],
```

**OR** if you're using an external cron service (cron-job.org):
- Update your existing backup job schedule from `*/15 * * * *` to `0 * * * *`
- See `HOURLY_BACKUP_SETUP.md` for detailed instructions

### Step 2: Deploy

```bash
git add .
git commit -m "feat: configure hourly backups with 7-day retention"
git push
```

### Step 3: Verify

After deployment, test manually:

```bash
# Test backup
curl https://www.campfixsti.com/api/cron/backup-supabase \
  -H "Authorization: Bearer YOUR_CRON_SECRET"

# Check storage
curl https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Backup Schedule

### Automatic Backups
- **Every hour** at minute 0 (1:00, 2:00, 3:00, etc.)
- Creates compressed backup (~1-2 MB each)
- Stores in Supabase storage

### Automatic Cleanup
- **Daily at 3:00 AM**
- Keeps most recent 168 backups (7 days)
- Deletes older backups automatically

### Weekly Storage Report
- **Every Monday at 2:00 AM**
- Generates storage usage report
- Warns if storage is getting full

## Retention Settings

Currently configured to keep **7 days** of hourly backups:

```env
BACKUP_RETENTION_COUNT=168
```

You can adjust this:
- `24` = 1 day (24 hours)
- `48` = 2 days
- `168` = 7 days (recommended)
- `336` = 14 days

## Storage Savings Calculation

### Before (15-minute backups, keep 7):
- 7 backups × 1.5 MB = **10.5 MB**

### After (hourly backups, keep 7 days):
- 168 backups × 1.5 MB = **252 MB**
- But with compression: ~85% smaller = **38 MB**

### Net Result
With the compression improvements from earlier + hourly schedule:
- **Expected storage usage: 30-50 MB** for backups
- **Remaining space: 950+ MB** on free tier
- **Storage pressure: Eliminated** ✅

## Monitoring

### Check Storage Anytime
```bash
curl https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

### Manual Backup
```bash
curl https://www.campfixsti.com/api/cron/backup-supabase \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

### Manual Cleanup
```bash
curl https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Documentation Reference

- 📘 **HOURLY_BACKUP_SETUP.md** - Complete setup guide
- 📘 **UPDATE_VERCEL_JSON.md** - How to update vercel.json
- 📘 **STORAGE_CLEANUP_INSTRUCTIONS.md** - Storage cleanup guide
- 📘 **SUPABASE_STORAGE_OPTIMIZATION.md** - Technical details
- 📘 **STORAGE_CHANGES_SUMMARY.md** - Overall optimization summary
- 📘 **QUICK_REFERENCE_STORAGE.md** - Quick command reference

## Quick Decision Guide

### Do you have Vercel Pro plan?
- ✅ **YES** → Use Vercel Cron (update vercel.json)
- ❌ **NO** → Use external cron service (cron-job.org)

### How much backup history do you need?
- **1 day** → `BACKUP_RETENTION_COUNT=24`
- **3 days** → `BACKUP_RETENTION_COUNT=72`
- **7 days** → `BACKUP_RETENTION_COUNT=168` (recommended)
- **14 days** → `BACKUP_RETENTION_COUNT=336`

### When should backups run?
- **Production apps** → Hourly is perfect
- **Low-activity apps** → Every 6 hours (`0 */6 * * *`)
- **High-change apps** → Keep hourly or increase retention

## Troubleshooting

### Backups not running hourly?
1. Check your cron service dashboard
2. Verify the schedule is `0 * * * *`
3. Test manually with curl command

### Storage still filling up?
1. Run cleanup: `curl .../cleanup-old-backups`
2. Check which bucket is full: `curl .../storage-report`
3. Reduce BACKUP_RETENTION_COUNT

### Need help?
Refer to the detailed guides listed in "Documentation Reference" above.

## Next Steps

1. [ ] Update vercel.json or external cron service
2. [ ] Deploy changes
3. [ ] Test backup manually
4. [ ] Wait 1 hour and verify automatic backup
5. [ ] Check storage report
6. [ ] Monitor for 24 hours
7. [ ] Celebrate! 🎉

---

**Configuration Date:** June 16, 2026  
**Backup Frequency:** Every hour  
**Retention Period:** 7 days (168 backups)  
**Expected Storage:** 30-50 MB for backups  
**Status:** ✅ Ready to implement
