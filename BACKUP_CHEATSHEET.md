# 📋 Database Backup Cheat Sheet

## Quick Reference

### 🔗 Important URLs

| What | URL |
|------|-----|
| Backup Status | https://www.campfixsti.com/api/cron/backup-status |
| Trigger Backup | https://www.campfixsti.com/api/cron/backup (needs auth) |
| cron-job.org Dashboard | https://cron-job.org/ |
| Supabase Dashboard | https://supabase.com/dashboard |
| Vercel Dashboard | https://vercel.com/dashboard |

### 🔑 Environment Variables

```env
CRON_SECRET=your-random-secret-key-here
```

Add in: Vercel Dashboard → Settings → Environment Variables

### ⏰ Cron Schedule Syntax

| Frequency | Cron Expression |
|-----------|----------------|
| Every 5 minutes | `*/5 * * * *` |
| Every 15 minutes | `*/15 * * * *` |
| Every 30 minutes | `*/30 * * * *` |
| Every hour | `0 * * * *` |
| Every 6 hours | `0 */6 * * *` |
| Daily at 2 AM | `0 2 * * *` |

### 📦 Current Configuration

- **Frequency**: Every 15 minutes
- **Retention**: 96 backups (1 day)
- **Compression**: Enabled (gzip)
- **Storage**: Supabase Storage
- **Size**: ~5-10 MB per backup

### 📁 Files & Locations

```
Local Backups:
  storage/app/backups/backup-YYYY-MM-DD-HHMMSS.sql.gz

Cloud Backups (Supabase):
  backups/database-backups/backup-YYYY-MM-DD-HHMMSS.sql.gz

Logs:
  storage/logs/laravel.log
```

### 🛠️ Commands

```bash
# Manual backup
php artisan db:backup --compress

# Check if command exists
php artisan list | grep backup

# View logs
tail -f storage/logs/laravel.log

# List local backups
ls -lh storage/app/backups/

# Test cron endpoint
curl -X GET https://www.campfixsti.com/api/cron/backup \
  -H "Authorization: Bearer YOUR_SECRET"

# Check status
curl https://www.campfixsti.com/api/cron/backup-status
```

### 🔧 Quick Fixes

| Problem | Solution |
|---------|----------|
| 401 Unauthorized | Check CRON_SECRET matches in Vercel & cron-job.org |
| No backups found | Wait 15 min or manually trigger in cron-job.org |
| Timeout errors | Increase timeout to 60 seconds in cron service |
| Storage full | Reduce retention or backup frequency |
| pg_dump error | Check database credentials in .env |

### 🎯 Quick Settings Change

**Change backup frequency:**

1. Edit `bootstrap/app.php`:
```php
->everyFifteenMinutes()  // Change this line
```

2. Edit cron-job.org schedule: `*/15 * * * *`

3. Deploy:
```bash
git add bootstrap/app.php
git commit -m "Change backup frequency"
git push
```

**Change retention:**

Edit `app/Console/Commands/BackupDatabase.php` line 76:
```php
$this->cleanOldBackups($backupPath, 96);  // Change number
```

### 📊 Storage Calculator

| Frequency | Backups/Day | @ 5MB each | @ 10MB each |
|-----------|-------------|------------|-------------|
| Every 5 min | 288 | 1.4 GB | 2.8 GB |
| Every 15 min | 96 | 480 MB | 960 MB |
| Every 30 min | 48 | 240 MB | 480 MB |
| Hourly | 24 | 120 MB | 240 MB |

Supabase Free Tier: **1 GB storage**

### 🔒 Security Checklist

- [x] CRON_SECRET in Vercel environment variables
- [x] Backup files in .gitignore
- [x] Supabase bucket set to private
- [x] Authorization header on cron endpoint
- [ ] Rotate CRON_SECRET every 6 months
- [ ] Test restoration quarterly

### 📈 Monitoring

**Check daily:**
- Visit backup status page
- Check cron-job.org execution log

**Check weekly:**
- Review backup file sizes in Supabase
- Check storage usage

**Check monthly:**
- Test restoration procedure
- Review retention policy

### 🆘 Emergency Restore

```bash
# 1. Download from Supabase Storage
# 2. Extract if compressed
gunzip backup-file.sql.gz

# 3. Restore
psql -h aws-1-ap-southeast-1.pooler.supabase.com \
     -p 5432 \
     -U postgres.pclfaksjjprickgppnus \
     -d postgres \
     -f backup-file.sql
```

### 📞 Quick Help

| Issue | Document |
|-------|----------|
| Initial setup | SETUP_FREE_BACKUP_NOW.md |
| Free tier guide | FREE_BACKUP_SOLUTION.md |
| Complete docs | DATABASE_BACKUP_SETUP.md |
| Quick start | BACKUP_QUICK_START.md |
| This cheatsheet | BACKUP_CHEATSHEET.md |

### 🎯 Free Services Used

- ✅ Vercel Free Tier (hosting)
- ✅ Supabase Free Tier (database + storage)
- ✅ cron-job.org Free (cron service)
- ✅ Total Cost: **$0/month**

### 📱 One-Liner Status Check

```bash
curl -s https://www.campfixsti.com/api/cron/backup-status | jq '.status,.message,.total_backups,.last_backup.created_at'
```

(Requires jq: `winget install jqlang.jq`)

### 🔄 Update Checklist

When you update the code:

1. [ ] Git add & commit
2. [ ] Git push (triggers Vercel deploy)
3. [ ] Wait for deployment
4. [ ] Test backup endpoint
5. [ ] Check cron-job.org execution

---

**Last Updated**: June 3, 2026  
**Version**: 1.0 - Free Tier Edition
