# Database Backup Setup Guide

This guide explains how to set up automated database backups for your CampFix application every 5 minutes.

## Overview

The backup system uses:
- **Laravel Command**: `db:backup` - Creates PostgreSQL database backups
- **Laravel Scheduler**: Runs the backup command every 5 minutes
- **Local Storage**: Keeps last 48 backups (4 hours worth) in `storage/app/backups/`
- **Supabase Storage** (Optional): Uploads backups to cloud storage

## Prerequisites

### 1. Install PostgreSQL Client Tools

You need `pg_dump` installed on your server:

#### For Ubuntu/Debian:
```bash
sudo apt-get update
sudo apt-get install postgresql-client
```

#### For CentOS/RHEL:
```bash
sudo yum install postgresql
```

#### For Windows (XAMPP Development):
Download from: https://www.postgresql.org/download/windows/

After installation, add PostgreSQL bin folder to PATH:
```
C:\Program Files\PostgreSQL\16\bin
```

#### Verify Installation:
```bash
pg_dump --version
```

### 2. Create Backup Directory

```bash
mkdir -p storage/app/backups
chmod 755 storage/app/backups
```

## Configuration

### 1. Environment Variables

Ensure your `.env` file has the correct database configuration:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-southeast-1.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.pclfaksjjprickgppnus
DB_PASSWORD=your_password_here

# Optional: For Supabase backup uploads
SUPABASE_URL=pclfaksjjprickgppnus.supabase.co
SUPABASE_KEY=your_service_role_key_here
SUPABASE_BUCKET=backups
```

### 2. Create Supabase Backup Bucket (Optional)

1. Go to Supabase Dashboard: https://supabase.com/dashboard
2. Select your project
3. Go to Storage
4. Create a new bucket named `backups`
5. Set it to private for security

## Usage

### Manual Backup

Run a backup manually:

```bash
# Basic backup
php artisan db:backup

# Compressed backup (recommended)
php artisan db:backup --compress
```

### Automated Backup (Every 5 Minutes)

The backup is already configured in `bootstrap/app.php` to run every 5 minutes automatically.

#### On Your Server (Production)

Add this to your system crontab:

```bash
# Edit crontab
crontab -e

# Add this line:
* * * * * cd /path/to/your/campfix && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/path/to/your/campfix` with your actual application path.

#### On Vercel (Serverless)

**Important**: Vercel is a serverless platform and doesn't support traditional cron jobs. You need to use one of these alternatives:

**Option A: Vercel Cron Jobs** (Recommended)
1. Create `vercel.json` cron configuration:

```json
{
  "crons": [
    {
      "path": "/api/cron/backup",
      "schedule": "*/5 * * * *"
    }
  ]
}
```

2. Create the cron endpoint at `api/cron/backup.php`:

```php
<?php
// Verify cron secret
if ($_SERVER['HTTP_AUTHORIZATION'] !== 'Bearer ' . getenv('CRON_SECRET')) {
    http_response_code(401);
    exit('Unauthorized');
}

// Run backup command
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('db:backup', ['--compress' => true]);
exit($status);
```

3. Add `CRON_SECRET` to your Vercel environment variables

**Option B: External Cron Service**

Use a service like:
- **EasyCron**: https://www.easycron.com/
- **cron-job.org**: https://cron-job.org/
- **UptimeRobot**: https://uptimerobot.com/

Configure it to call your backup endpoint every 5 minutes.

**Option C: Supabase Database Webhooks**

Use Supabase's Edge Functions with cron triggers:
https://supabase.com/docs/guides/functions/schedule-functions

#### On Other Cloud Platforms

**Railway**: Supports cron jobs natively
```bash
# .railway.json
{
  "cron": {
    "enabled": true,
    "schedule": "*/5 * * * *",
    "command": "php artisan db:backup --compress"
  }
}
```

**Heroku**: Use Heroku Scheduler addon
```bash
heroku addons:create scheduler:standard
heroku addons:open scheduler
# Add job: php artisan db:backup --compress (every 5 minutes)
```

**DigitalOcean App Platform**: Add cron job in app spec
```yaml
jobs:
  - name: backup-database
    kind: CRON
    schedule: "*/5 * * * *"
    run_command: php artisan db:backup --compress
```

## Backup Features

### 1. Automatic Cleanup
- Keeps only the last 48 backups (4 hours worth)
- Older backups are automatically deleted
- Prevents disk space issues

### 2. Compression
- Use `--compress` flag to compress backups with gzip
- Reduces storage space by ~70-90%
- Recommended for production

### 3. Cloud Upload
- Automatically uploads to Supabase Storage
- Provides off-site backup
- Optional but recommended

### 4. Logging
- All backup operations are logged
- Check logs: `storage/logs/laravel.log`
- Success and failure notifications

## Backup Restoration

### Restore from Local Backup

```bash
# Extract if compressed
gunzip storage/app/backups/backup-2026-06-03-120000.sql.gz

# Restore to database
psql -h aws-1-ap-southeast-1.pooler.supabase.com \
     -p 5432 \
     -U postgres.pclfaksjjprickgppnus \
     -d postgres \
     -f storage/app/backups/backup-2026-06-03-120000.sql
```

### Restore from Supabase Storage

1. Download backup from Supabase Dashboard
2. Extract if compressed: `gunzip backup-file.sql.gz`
3. Restore using the command above

## Testing

Test the backup system:

```bash
# Run a test backup
php artisan db:backup --compress

# Check if backup was created
ls -lh storage/app/backups/

# Check logs
tail -f storage/logs/laravel.log
```

## Monitoring

### Check Backup Status

```bash
# List all backups with sizes
ls -lhS storage/app/backups/

# Count backups
ls storage/app/backups/ | wc -l

# Check last backup time
ls -lt storage/app/backups/ | head -n 2
```

### Set Up Alerts (Optional)

Create a monitoring command:

```php
// app/Console/Commands/CheckBackupHealth.php
// Checks if backups are running and alerts if not
```

## Troubleshooting

### Error: "pg_dump: command not found"

**Solution**: Install PostgreSQL client tools (see Prerequisites)

### Error: "Permission denied"

**Solution**: 
```bash
chmod 755 storage/app/backups
chown www-data:www-data storage/app/backups
```

### Error: "PGPASSWORD authentication failed"

**Solution**: Verify your database credentials in `.env`

### Backup file is empty or very small

**Solution**: 
- Check database connection
- Verify user has proper permissions
- Check error logs: `storage/logs/laravel.log`

### Cron not running

**Solution**:
```bash
# Check if cron is running
systemctl status cron

# Check crontab
crontab -l

# Test scheduler manually
php artisan schedule:run
```

## Best Practices

1. **Test Regularly**: Periodically test backup restoration
2. **Monitor Disk Space**: Ensure sufficient storage
3. **Off-site Backups**: Use Supabase Storage or S3
4. **Retention Policy**: Adjust backup retention based on needs
5. **Security**: Keep backups encrypted and private
6. **Documentation**: Document restoration procedures
7. **Alerts**: Set up notifications for backup failures

## Alternative: Use Supabase's Built-in Backups

For production, consider using Supabase's built-in backup features:

- **Daily Backups**: Automatic on all paid plans
- **Point-in-Time Recovery (PITR)**: Available on Pro+ plans
- **Manual Backups**: Trigger via Supabase Dashboard

This approach is more reliable and doesn't require server-side cron jobs.

## Security Considerations

1. **Never commit backups** to Git (already in .gitignore)
2. **Encrypt sensitive backups** before cloud upload
3. **Use private storage buckets** on Supabase
4. **Rotate backup credentials** regularly
5. **Limit access** to backup files
6. **Audit backup access** logs

## Adjustment for Different Intervals

To change backup frequency, edit `bootstrap/app.php`:

```php
// Every minute
$schedule->command('db:backup --compress')->everyMinute();

// Every 10 minutes
$schedule->command('db:backup --compress')->everyTenMinutes();

// Every 30 minutes
$schedule->command('db:backup --compress')->everyThirtyMinutes();

// Every hour
$schedule->command('db:backup --compress')->hourly();

// Daily at 2 AM
$schedule->command('db:backup --compress')->dailyAt('02:00');
```

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Review this documentation
3. Test manually: `php artisan db:backup --compress`
4. Verify prerequisites are installed

## Maintenance Schedule

- **Daily**: Check backup logs
- **Weekly**: Verify backup restoration
- **Monthly**: Review storage usage
- **Quarterly**: Test disaster recovery procedures
