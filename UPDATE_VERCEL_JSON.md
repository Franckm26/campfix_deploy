# How to Update vercel.json for Hourly Backups

## Quick Instructions

Open your `vercel.json` file and add the `crons` section after the `regions` line:

### Before:
```json
{
  "$schema": "https://openapi.vercel.sh/vercel.json",
  "version": 2,
  "regions": ["sin1"],
  "builds": [
```

### After:
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
```

## Complete Example

Here's the complete `crons` section to add:

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

## What Each Cron Does

1. **backup-supabase** (`0 * * * *`)
   - Runs: Every hour at minute 0
   - Purpose: Creates compressed database backup
   - Example times: 1:00 AM, 2:00 AM, 3:00 AM...

2. **cleanup-old-backups** (`0 3 * * *`)
   - Runs: Daily at 3:00 AM
   - Purpose: Deletes old backups, keeps most recent ones
   - Frees up storage automatically

3. **storage-report** (`0 2 * * 1`)
   - Runs: Every Monday at 2:00 AM
   - Purpose: Generates storage usage report
   - Helps monitor your storage usage

## Important Notes

⚠️ **Vercel Cron Jobs require:**
- Vercel Pro plan ($20/month) OR
- Hobby plan with cron enabled

If you don't have this, use the **External Cron Service** method in `HOURLY_BACKUP_SETUP.md` instead (it's free).

## After Adding

1. Save the file
2. Commit and push:
   ```bash
   git add vercel.json
   git commit -m "feat: add hourly backup cron schedule"
   git push
   ```

3. Verify in Vercel Dashboard:
   - Project → Settings → Cron Jobs
   - You should see 3 jobs listed

## Alternative: Use External Cron (Free)

If you can't use Vercel Cron, configure cron-job.org instead:

1. Go to https://cron-job.org/
2. Update your backup job:
   - Change schedule from `*/15 * * * *` to `0 * * * *`
3. Add new cleanup job:
   - Schedule: `0 3 * * *`
   - URL: `https://www.campfixsti.com/api/cron/cleanup-old-backups`

See `HOURLY_BACKUP_SETUP.md` for detailed external cron setup.
