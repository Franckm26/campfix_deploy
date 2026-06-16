# ⏰ Hourly Backup Configuration Guide

## Overview
This guide shows you how to configure your backups to run once per hour instead of every 15 minutes, which will significantly reduce storage usage.

## Why Hourly Backups?

**Current (15 minutes):**
- 96 backups per day
- With 7-day retention: 672 backups stored
- Storage usage: Very high

**Hourly (Recommended):**
- 24 backups per day
- With 7-day retention: 168 backups stored
- **Storage savings: 75% reduction** (4x less backups)

## Option 1: Vercel Cron Jobs (Recommended)

### Requirements
- Vercel Pro plan ($20/month) OR Hobby plan with cron enabled

### Setup Instructions

1. **Update your `vercel.json` file**

Add this `crons` section at the top (after `regions`):

```json
{
  "$schema": "https://openapi.vercel.sh/vercel.json",
  "version": 2,
  "regions": ["sin1"],
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
  "builds": [
    ...
  ]
}
```

**Schedule Explanation:**
- `"0 * * * *"` = Every hour at minute 0 (1:00, 2:00, 3:00, etc.)
- `"0 3 * * *"` = Daily at 3:00 AM (cleanup old backups)
- `"0 2 * * 1"` = Weekly on Monday at 2:00 AM (storage report)

2. **Deploy to Vercel**
```bash
git add vercel.json
git commit -m "feat: configure hourly backup schedule"
git push
```

3. **Verify in Vercel Dashboard**
- Go to your project in Vercel
- Click "Settings" → "Cron Jobs"
- You should see 3 cron jobs listed with their schedules

### Note on Vercel Cron
Vercel Cron automatically handles authentication - you don't need to pass `Authorization` headers. The `CRON_SECRET` check in your PHP files will need to be adjusted to allow Vercel's internal requests.

## Option 2: External Cron Service (Free Alternative)

If you're on Vercel's free plan, use an external cron service:

### A. Using cron-job.org (Free, Recommended)

1. **Login to cron-job.org**
   - Go to: https://cron-job.org/
   - Login to your account

2. **Update Existing Job or Create New**
   
   **For Backup Job:**
   - Title: `CampFix Hourly Backup`
   - URL: `https://www.campfixsti.com/api/cron/backup-supabase`
   - Schedule: `0 * * * *` (Every hour)
   - Request Method: GET
   - Custom Headers:
     ```
     Authorization: Bearer YOUR_CRON_SECRET
     ```

   **For Cleanup Job:**
   - Title: `CampFix Daily Cleanup`
   - URL: `https://www.campfixsti.com/api/cron/cleanup-old-backups`
   - Schedule: `0 3 * * *` (Daily at 3 AM)
   - Request Method: GET
   - Custom Headers:
     ```
     Authorization: Bearer YOUR_CRON_SECRET
     ```

   **For Storage Report:**
   - Title: `CampFix Weekly Storage Report`
   - URL: `https://www.campfixsti.com/api/cron/storage-report`
   - Schedule: `0 2 * * 1` (Monday at 2 AM)
   - Request Method: GET
   - Custom Headers:
     ```
     Authorization: Bearer YOUR_CRON_SECRET
     ```

3. **Delete/Disable Old 15-minute Jobs**
   - Find your old backup jobs with `*/15 * * * *` schedule
   - Delete or disable them

### B. Using EasyCron (Free)

1. Go to: https://www.easycron.com/
2. Edit your existing backup cron job
3. Change Cron Expression to: `0 * * * *`
4. Save

### C. Using GitHub Actions (Free)

Create `.github/workflows/backup-hourly.yml`:

```yaml
name: Hourly Database Backup

on:
  schedule:
    - cron: '0 * * * *'  # Every hour
  workflow_dispatch:  # Manual trigger

jobs:
  backup:
    runs-on: ubuntu-latest
    steps:
      - name: Trigger Backup
        run: |
          curl -X GET https://www.campfixsti.com/api/cron/backup-supabase \
            -H "Authorization: Bearer ${{ secrets.CRON_SECRET }}"
      
      - name: Check Response
        if: failure()
        run: echo "Backup failed!"

  cleanup:
    runs-on: ubuntu-latest
    if: github.event.schedule == '0 3 * * *'  # Only at 3 AM
    steps:
      - name: Cleanup Old Backups
        run: |
          curl -X GET https://www.campfixsti.com/api/cron/cleanup-old-backups \
            -H "Authorization: Bearer ${{ secrets.CRON_SECRET }}"
```

## Adjust Retention Count for Hourly Backups

Since you're now doing hourly backups, you can keep more backups with the same storage:

### Current Setting (with 15-min backups):
```env
BACKUP_RETENTION_COUNT=7
```
This keeps 7 backups = ~1.75 hours of history

### Recommended for Hourly Backups:
```env
BACKUP_RETENTION_COUNT=24
```
This keeps 24 backups = 1 day of hourly history

### Or Keep 7 Days of Backups:
```env
BACKUP_RETENTION_COUNT=168
```
This keeps 168 backups = 7 days of hourly history

**Add this to your `.env.vercel` file:**

```env
# Backup retention - number of backups to keep
# Hourly backups: 24 = 1 day, 168 = 7 days, 336 = 2 weeks
BACKUP_RETENTION_COUNT=168
```

Then deploy:
```bash
# Update in Vercel Dashboard
# Settings → Environment Variables → Edit BACKUP_RETENTION_COUNT
```

## Cron Schedule Reference

```
┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of week (0 - 6) (Sunday to Saturday)
│ │ │ │ │
│ │ │ │ │
* * * * *
```

**Common Schedules:**

| Schedule | Cron Expression | Description |
|----------|----------------|-------------|
| Every 15 minutes | `*/15 * * * *` | 96 times/day |
| Every 30 minutes | `*/30 * * * *` | 48 times/day |
| **Every hour** | `0 * * * *` | **24 times/day** ⭐ |
| Every 2 hours | `0 */2 * * *` | 12 times/day |
| Every 6 hours | `0 */6 * * *` | 4 times/day |
| Daily at 3 AM | `0 3 * * *` | Once per day |
| Weekly Monday 2 AM | `0 2 * * 1` | Once per week |

## Testing Your New Schedule

### Test Backup Manually
```bash
curl -X GET https://www.campfixsti.com/api/cron/backup-supabase \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

Expected response:
```json
{
  "success": true,
  "message": "Backup completed successfully",
  "filename": "supabase-backup-2026-06-16-120000.json.gz",
  "tables_backed_up": 3,
  "total_records": 1250,
  "size_formatted": "1.2 MB",
  "compression_ratio": "85.3%"
}
```

### Check Next Scheduled Run

**For Vercel Cron:**
- Dashboard → Your Project → Settings → Cron Jobs
- Shows next scheduled execution time

**For cron-job.org:**
- Dashboard → Your Cron Jobs
- Shows "Next Execution" time

**For GitHub Actions:**
- Repository → Actions → Workflows
- Shows schedule and last run

## Storage Impact Calculation

### Before (15-minute backups):
- Frequency: 96 backups/day
- Retention: 7 backups
- Storage: ~7 × 1.5 MB = **10.5 MB**

### After (hourly backups with 1 week retention):
- Frequency: 24 backups/day
- Retention: 168 backups (7 days)
- Storage: ~168 × 1.5 MB = **252 MB**

### Recommended (hourly with 1 day retention):
- Frequency: 24 backups/day
- Retention: 24 backups (1 day)
- Storage: ~24 × 1.5 MB = **36 MB**

**💡 Best Practice:** Keep 24-48 hourly backups (1-2 days) for good balance between safety and storage.

## Troubleshooting

### Cron Not Running

1. **Check cron service logs**
   - Vercel: Check deployment logs
   - cron-job.org: Check execution history
   - GitHub Actions: Check workflow runs

2. **Verify CRON_SECRET**
   ```bash
   echo $CRON_SECRET  # Should match your .env value
   ```

3. **Test endpoint manually**
   ```bash
   curl -v https://www.campfixsti.com/api/cron/backup-supabase \
     -H "Authorization: Bearer YOUR_CRON_SECRET"
   ```

### Backups Not Being Created

Check backup script logs:
```bash
curl https://www.campfixsti.com/api/cron/backup-status \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

### Old Backups Not Getting Deleted

Run cleanup manually:
```bash
curl https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Summary Checklist

- [ ] Update cron schedule to hourly (`0 * * * *`)
- [ ] Update cleanup to daily (`0 3 * * *`)
- [ ] Set BACKUP_RETENTION_COUNT appropriately
- [ ] Test backup manually
- [ ] Test cleanup manually
- [ ] Verify storage report
- [ ] Monitor for 24 hours to ensure it's working
- [ ] Delete/disable old 15-minute cron jobs

## Expected Results

After implementing hourly backups:

✅ **75% fewer backups** created per day  
✅ **Significantly reduced storage usage**  
✅ **Still maintains good backup coverage** (24 restore points per day)  
✅ **Automatic cleanup** keeps storage under control  
✅ **Weekly monitoring** via storage report  

---

**Implementation Date:** June 16, 2026  
**Backup Frequency:** Hourly (24 times/day)  
**Retention:** Configurable (recommended: 24-168 backups)
