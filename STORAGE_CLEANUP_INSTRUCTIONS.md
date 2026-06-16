# 🚀 Quick Storage Cleanup Instructions

## Immediate Actions to Free Up Space

### Step 1: Check Your Current Storage Usage

Open your terminal or use a tool like Postman/curl:

```bash
curl -X GET https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

Replace `YOUR_CRON_SECRET` with your actual CRON_SECRET value from your environment variables.

**What to look for:**
- Total storage usage
- Which bucket is using the most space (backups, concerns, or profile_pictures)
- Warning messages if over 80% capacity

### Step 2: Clean Up Old Backups (RECOMMENDED)

```bash
curl -X GET https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

This will:
- Keep only the 7 most recent backups
- Delete all older backups
- Report how much space was freed

**Expected result:** Should free up 50-80% of backup storage

### Step 3: Verify the Cleanup

Run the storage report again to confirm space was freed:

```bash
curl -X GET https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## What Changed in Your Project

### ✅ New Features

1. **Compressed Backups** (70-90% smaller)
   - Backups are now gzip compressed automatically
   - Filename changed from `.json` to `.json.gz`

2. **Reduced Backup Scope**
   - Excluded temporary data (sessions, notifications)
   - Only backs up essential data (users, concerns, event_requests)

3. **Automatic Cleanup Script**
   - Keeps only recent backups (configurable, default: 7)
   - Deletes old backups automatically

4. **Storage Monitoring**
   - Real-time storage usage reports
   - Warning alerts when storage is high
   - Per-bucket breakdown

### 📁 New Files Created

1. `/api/cron/cleanup-old-backups.php` - Automatic backup cleanup
2. `/api/cron/storage-report.php` - Storage usage monitoring
3. `/SUPABASE_STORAGE_OPTIMIZATION.md` - Complete optimization guide
4. `/STORAGE_CLEANUP_INSTRUCTIONS.md` - This file

### 🔧 Modified Files

1. `/api/cron/backup-supabase.php` - Now compresses backups and excludes temporary data
2. `/vercel.json` - Added routes for new cleanup endpoints

## Testing (Optional)

You can test locally before deploying:

### Test Storage Report Locally

1. Set up your environment variables in `.env`:
   ```
   SUPABASE_URL=pclfaksjjprickgppnus.supabase.co
   SUPABASE_KEY=your_service_role_key
   CRON_SECRET=your_secret
   ```

2. Run: `php api/cron/storage-report.php`

### Test Cleanup Locally

1. Run: `php api/cron/cleanup-old-backups.php`

## Deployment

### Deploy to Vercel

```bash
# Commit changes
git add .
git commit -m "feat: add supabase storage optimization and cleanup"

# Push to trigger Vercel deployment
git push origin main
```

### Set Environment Variable (If Not Set)

In Vercel Dashboard:
1. Go to your project
2. Settings → Environment Variables
3. Add: `BACKUP_RETENTION_COUNT` = `7` (or your preferred number)

## Ongoing Maintenance

### Option 1: Manual Cleanup (Run When Needed)

When you notice storage getting full, run the cleanup script manually.

### Option 2: Scheduled Cleanup (Recommended)

Set up a cron job or scheduled task:

**Using cron-job.org (Free):**
1. Go to https://cron-job.org
2. Create new job
3. URL: `https://www.campfixsti.com/api/cron/cleanup-old-backups`
4. Schedule: Daily at 2:00 AM
5. Add header: `Authorization: Bearer YOUR_CRON_SECRET`

**Using GitHub Actions:**
Create `.github/workflows/cleanup-storage.yml`:

```yaml
name: Cleanup Old Backups
on:
  schedule:
    - cron: '0 2 * * *' # Daily at 2 AM UTC
  workflow_dispatch: # Manual trigger

jobs:
  cleanup:
    runs-on: ubuntu-latest
    steps:
      - name: Cleanup Old Backups
        run: |
          curl -X GET https://www.campfixsti.com/api/cron/cleanup-old-backups \
            -H "Authorization: Bearer ${{ secrets.CRON_SECRET }}"
```

## Troubleshooting

### "Unauthorized" Error
- Check that you're using the correct CRON_SECRET
- Make sure the Authorization header is properly formatted: `Bearer YOUR_SECRET`

### "Failed to list backups" Error
- Verify SUPABASE_URL is correct (without https://)
- Verify SUPABASE_KEY is the service_role key, not anon key
- Check that the 'backups' bucket exists in Supabase

### Not Freeing Enough Space
1. Reduce BACKUP_RETENTION_COUNT to 3 or 5
2. Check if concerns/profile_pictures are using more space
3. Consider implementing image compression

## Expected Storage Savings

After implementing these optimizations:

**Before:**
- Uncompressed backups: ~5-20 MB each
- Multiple old backups: 50-200 MB total
- Total storage: 200-500 MB

**After:**
- Compressed backups: ~0.5-2 MB each
- Only 7 recent backups: 3.5-14 MB total
- **Savings: 80-95% reduction in backup storage**

## Need More Space?

If you're still running out:

1. **Check image storage:**
   ```bash
   curl https://www.campfixsti.com/api/cron/storage-report \
     -H "Authorization: Bearer YOUR_CRON_SECRET"
   ```

2. **Consider Supabase Pro:**
   - $25/month
   - 8 GB storage (8x more)
   - Better performance

3. **Move backups to external storage:**
   - AWS S3, Backblaze B2, or Google Cloud Storage
   - Keep only 1-2 backups in Supabase

## Questions?

Refer to `SUPABASE_STORAGE_OPTIMIZATION.md` for detailed technical information.

---

**Created:** June 16, 2026  
**For:** Campfix Project
