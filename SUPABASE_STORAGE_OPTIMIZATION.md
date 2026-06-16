# Supabase Storage Optimization Guide

## Problem Overview

Your Campfix project was experiencing storage issues on Supabase's free tier (1 GB limit) due to:

1. **Database backups accumulating** - Each backup contains full database snapshots
2. **No automatic cleanup** - Old backups were never deleted
3. **User-uploaded images** - Concerns and profile pictures also consume storage

## Solutions Implemented

### 1. Automatic Backup Cleanup Script

**File:** `api/cron/cleanup-old-backups.php`

**What it does:**
- Lists all backup files in your Supabase storage
- Keeps only the most recent backups (default: 7)
- Deletes older backups automatically
- Reports how much space was freed

**How to use:**

```bash
# Check your current backups and clean up
curl -X GET https://www.campfixsti.com/api/cron/cleanup-old-backups \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

**Configuration:**
- Set `BACKUP_RETENTION_COUNT` environment variable to change how many backups to keep (default: 7)

### 2. Storage Usage Report

**File:** `api/cron/storage-report.php`

**What it does:**
- Scans all your Supabase storage buckets (backups, concerns, profile_pictures)
- Reports total file count and size per bucket
- Shows percentage of free tier used
- Provides warnings if storage is above 80%
- Suggests optimization actions

**How to use:**

```bash
# Check your storage usage
curl -X GET https://www.campfixsti.com/api/cron/storage-report \
  -H "Authorization: Bearer YOUR_CRON_SECRET"
```

## Recommended Actions

### Immediate Actions (Do This Now)

1. **Check your current storage usage:**
   ```bash
   curl -X GET https://www.campfixsti.com/api/cron/storage-report \
     -H "Authorization: Bearer YOUR_CRON_SECRET"
   ```

2. **Clean up old backups:**
   ```bash
   curl -X GET https://www.campfixsti.com/api/cron/cleanup-old-backups \
     -H "Authorization: Bearer YOUR_CRON_SECRET"
   ```

3. **Set up automatic cleanup** (if you have cron job access):
   - Schedule `cleanup-old-backups.php` to run daily or weekly
   - Schedule `storage-report.php` to run weekly for monitoring

### Long-term Optimizations

#### Option 1: Reduce Backup Frequency (Recommended)
Instead of backing up frequently, consider:
- Daily backups instead of hourly
- Weekly backups for production
- Use Supabase's built-in backup feature (Point-in-Time Recovery on paid plans)

#### Option 2: Compress Backups
Modify `backup-supabase.php` to compress JSON files:
```php
// Instead of: json_encode($backupData, JSON_PRETTY_PRINT)
// Use: gzencode(json_encode($backupData))
```

#### Option 3: Selective Backups
Instead of backing up ALL data, backup only critical tables:
```php
// In backup-supabase.php, reduce the tables array
$tables = [
    'users',        // Keep
    'concerns',     // Keep
    'event_requests', // Keep
    // 'sessions',  // Remove - these are temporary
    // 'notifications', // Remove - can be regenerated
];
```

#### Option 4: Move to External Backup Storage
Store backups outside Supabase:
- AWS S3 (free tier available)
- Backblaze B2 (10 GB free)
- Google Cloud Storage (5 GB free)

#### Option 5: Image Optimization
For user-uploaded images, implement:

1. **Image compression on upload:**
```php
// In SupabaseStorage.php upload() method
// Add image compression before upload
if (in_array($file->getMimeType(), ['image/jpeg', 'image/png'])) {
    $image = imagecreatefromstring(file_get_contents($file->getRealPath()));
    // Resize to max 1200px width
    // Compress with quality 75-85%
}
```

2. **Cleanup deleted concern images:**
Currently, when concerns are deleted, images might remain in storage. Ensure `ConcernController.php` properly deletes images.

3. **Image format conversion:**
Convert PNG to JPEG where possible (smaller file sizes)

## Environment Variables

Add these to your `.env.vercel` file:

```env
# Backup retention (how many backups to keep)
BACKUP_RETENTION_COUNT=7

# Alternative backup bucket (if you create a separate one)
SUPABASE_BACKUP_BUCKET=backups
```

## Monitoring Best Practices

1. **Weekly Storage Checks**
   Run storage-report.php weekly to monitor usage

2. **Set Up Alerts**
   Create a simple alert system:
   - If storage > 80%, send email notification
   - If storage > 90%, send urgent notification

3. **Track Storage Trends**
   Log storage usage over time to predict when you'll hit limits

## Supabase Free Tier Limits

- **Storage:** 1 GB
- **Bandwidth:** 2 GB/month
- **File uploads:** 50 MB max per file

## Cost of Upgrading

If optimizations aren't enough, consider Supabase Pro:
- **Cost:** $25/month
- **Storage:** 8 GB included
- **Additional storage:** ~$0.021 per GB
- **Bandwidth:** 50 GB included

## Troubleshooting

### "Still running out of storage after cleanup"

1. Check which bucket is using the most space:
   ```bash
   curl https://www.campfixsti.com/api/cron/storage-report \
     -H "Authorization: Bearer YOUR_CRON_SECRET"
   ```

2. If it's the `concerns` bucket:
   - Implement image compression
   - Reduce image dimensions on upload
   - Clean up orphaned images (images from deleted concerns)

3. If it's the `backups` bucket:
   - Reduce BACKUP_RETENTION_COUNT to 3-5
   - Implement backup compression
   - Move backups to external storage

### "Cleanup script not deleting files"

Check:
1. SUPABASE_KEY has proper permissions (service_role key)
2. Bucket name is correct in environment variables
3. Files exist in the expected path (database-backups/)

### "403 Forbidden errors"

Your SUPABASE_KEY might be the anon key instead of service_role key. Use the service_role key for storage operations.

## Support

If you need help:
1. Run the storage report to get detailed information
2. Check Supabase dashboard for actual storage usage
3. Review logs in Vercel deployment logs

---

**Last Updated:** June 16, 2026
