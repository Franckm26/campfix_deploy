# Database Backup - Quick Start Guide

## ⚡ FASTEST: Free Solution (Recommended for Vercel Free Tier)

**📖 See detailed guide**: `SETUP_FREE_BACKUP_NOW.md`

### 5-Minute Setup (100% Free):

1. **Generate secret key**: Run in PowerShell
   ```powershell
   [Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Minimum 0 -Maximum 256 }))
   ```

2. **Add to Vercel**: Dashboard → Settings → Environment Variables
   - Name: `CRON_SECRET`
   - Value: [your generated key]

3. **Deploy**:
   ```bash
   git add .
   git commit -m "Add free backup system"
   git push
   ```

4. **Register at cron-job.org**: https://cron-job.org/ (free account)

5. **Create cron job**:
   - URL: `https://www.campfixsti.com/api/cron/backup`
   - Schedule: `*/15 * * * *` (every 15 minutes)
   - Header: `Authorization: Bearer YOUR_SECRET_KEY`

6. **Verify**: https://www.campfixsti.com/api/cron/backup-status

✅ **Done!** Your database backs up every 15 minutes, completely free.

**💡 Total Cost**: $0/month (uses cron-job.org free service)

---

## For Vercel Deployment (With Paid Plan)

### Step 1: Add Environment Variable
Go to your Vercel Dashboard → Your Project → Settings → Environment Variables

Add:
- **Name**: `CRON_SECRET`
- **Value**: Generate a random string (e.g., `your-secret-key-here-make-it-long-and-random`)
- **Environments**: Production, Preview, Development

### Step 2: Update vercel.json
Replace your current `vercel.json` with `vercel.json.backup` file:

```bash
# Backup current version
copy vercel.json vercel.json.old

# Use the new version with cron
copy vercel.json.backup vercel.json
```

Or manually add this to your `vercel.json` after `"regions": ["sin1"],`:

```json
"crons": [
  {
    "path": "/api/cron/backup",
    "schedule": "*/5 * * * *"
  }
],
```

And add this route at the beginning of your routes array:

```json
{
  "src": "/api/cron/backup",
  "dest": "/api/cron/backup.php"
},
```

### Step 3: Deploy to Vercel

```bash
git add .
git commit -m "Add automated database backup every 5 minutes"
git push
```

### Step 4: Test the Backup

After deployment, test manually:

```bash
curl -X GET https://www.campfixsti.com/api/cron/backup \
  -H "Authorization: Bearer your-secret-key-here"
```

Or visit: https://vercel.com/dashboard → Your Project → Cron Jobs

### Step 5: Monitor Backups

Check Vercel logs to see backup execution:
- Go to Vercel Dashboard → Your Project → Logs
- Filter by `/api/cron/backup`

---

## For Traditional Server (VPS, DigitalOcean, etc.)

### Step 1: Install PostgreSQL Client

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install postgresql-client

# CentOS/RHEL
sudo yum install postgresql

# Verify
pg_dump --version
```

### Step 2: Create Backup Directory

```bash
cd /path/to/your/campfix
mkdir -p storage/app/backups
chmod 755 storage/app/backups
```

### Step 3: Test Manual Backup

```bash
php artisan db:backup --compress
```

Check if backup was created:
```bash
ls -lh storage/app/backups/
```

### Step 4: Set Up Cron Job

```bash
# Edit crontab
crontab -e

# Add this line (replace path with your actual path)
* * * * * cd /var/www/campfix && php artisan schedule:run >> /dev/null 2>&1
```

### Step 5: Verify Cron is Working

Wait 5 minutes and check:
```bash
ls -lt storage/app/backups/ | head -n 5
```

---

## For Local Development (XAMPP)

### Step 1: Install PostgreSQL

Download and install PostgreSQL:
https://www.postgresql.org/download/windows/

Add to PATH: `C:\Program Files\PostgreSQL\16\bin`

### Step 2: Test Backup Manually

Open Command Prompt in your project directory:

```cmd
cd C:\xampp\htdocs\Campfix
php artisan db:backup --compress
```

### Step 3: Check Backup

```cmd
dir storage\app\backups
```

### Step 4: Schedule with Windows Task Scheduler (Optional)

1. Open Task Scheduler
2. Create Basic Task
3. Name: "CampFix Database Backup"
4. Trigger: Daily, repeat every 5 minutes
5. Action: Start a program
   - Program: `C:\xampp\php\php.exe`
   - Arguments: `artisan db:backup --compress`
   - Start in: `C:\xampp\htdocs\Campfix`

---

## Troubleshooting

### ❌ Error: "pg_dump: command not found"
**Solution**: Install PostgreSQL client tools (see Step 1 above)

### ❌ Error: "Permission denied"
**Solution**: 
```bash
chmod 755 storage/app/backups
chown www-data:www-data storage/app/backups
```

### ❌ Error: "401 Unauthorized" on Vercel
**Solution**: 
- Verify `CRON_SECRET` is set in Vercel environment variables
- Make sure you're using the correct secret in the Authorization header

### ❌ Backup file is empty
**Solution**:
- Check database connection in `.env`
- Verify database credentials are correct
- Check logs: `storage/logs/laravel.log`

### ❌ Cron not running on Vercel
**Solution**:
- Verify cron is configured in `vercel.json`
- Check Vercel dashboard → Cron Jobs section
- Cron jobs require Hobby plan or higher

---

## What Happens Next?

✅ **Every 5 minutes**: Automatic backup of your PostgreSQL database
✅ **Compression**: Backups are compressed to save space (~70-90% smaller)
✅ **Auto-cleanup**: Only keeps last 48 backups (4 hours worth)
✅ **Cloud upload**: Optionally uploads to Supabase Storage
✅ **Logging**: All operations logged to `storage/logs/laravel.log`

---

## Viewing Backup Files

### On Vercel (Ephemeral Storage)
⚠️ **Important**: Vercel has ephemeral storage - files are deleted on each deployment!

**Solution**: Use Supabase Storage to persist backups
1. Create `backups` bucket in Supabase
2. Backups will be automatically uploaded there
3. Access via Supabase Dashboard → Storage → backups → database-backups/

### On Traditional Server
```bash
# List all backups
ls -lh storage/app/backups/

# Check backup size
du -sh storage/app/backups/

# View latest backups
ls -lt storage/app/backups/ | head -n 5
```

---

## Adjusting Backup Frequency

Edit `bootstrap/app.php` and change the schedule:

```php
// Current: Every 5 minutes
$schedule->command('db:backup --compress')->everyFiveMinutes();

// Options:
->everyMinute()           // Every minute
->everyTenMinutes()       // Every 10 minutes  
->everyFifteenMinutes()   // Every 15 minutes
->everyThirtyMinutes()    // Every 30 minutes
->hourly()                // Every hour
->daily()                 // Once daily
->dailyAt('02:00')        // Daily at 2 AM
```

For Vercel cron, also update `vercel.json`:
```json
"schedule": "0 2 * * *"  // Daily at 2 AM
"schedule": "0 * * * *"  // Every hour
"schedule": "*/15 * * * *"  // Every 15 minutes
```

---

## Next Steps

1. ✅ Set up the backup system (follow steps above)
2. ✅ Test manually: `php artisan db:backup --compress`
3. ✅ Wait 5 minutes and verify automatic backup
4. ✅ Set up Supabase Storage (optional but recommended)
5. ✅ Test restoration procedure (see DATABASE_BACKUP_SETUP.md)
6. ✅ Set up monitoring/alerts (optional)

---

## Need More Help?

- **Full Documentation**: See `DATABASE_BACKUP_SETUP.md`
- **Laravel Scheduler**: https://laravel.com/docs/scheduling
- **Vercel Cron**: https://vercel.com/docs/cron-jobs
- **Check Logs**: `storage/logs/laravel.log`

---

## Important Notes

⚠️ **Vercel Limitation**: Vercel has ephemeral storage. Use Supabase Storage for persistent backups.

⚠️ **Vercel Plan**: Cron jobs require Hobby plan ($20/month) or higher.

⚠️ **5-Minute Interval**: Very frequent! Consider hourly or daily for production.

✅ **Recommended**: Use Supabase's built-in PITR (Point-in-Time Recovery) on Pro plan for production.
