# 🎯 Setup Free Database Backup in 5 Minutes

## Quick Overview
✅ **100% FREE** - No credit card needed  
✅ **Every 15 minutes** - 96 backups per day  
✅ **Automatic cleanup** - Keeps 1 day of backups  
✅ **Cloud storage** - Supabase Storage  

---

## 🚀 Step-by-Step Setup

### Step 1: Generate Secret Key (30 seconds)

**Windows PowerShell**:
```powershell
[Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Minimum 0 -Maximum 256 }))
```

**Or use this online tool**:
https://www.random.org/strings/?num=1&len=32&digits=on&upperalpha=on&loweralpha=on&unique=on&format=html&rnd=new

📋 **Copy the result** (example: `x7K2mP9nQ4vL8wR3tY6uA5bC1dF0eH9j`)

---

### Step 2: Add to Vercel (1 minute)

1. Go to: https://vercel.com/dashboard
2. Click on **CampFix** project
3. Click **Settings** tab
4. Click **Environment Variables** in sidebar
5. Click **Add New** button
6. Enter:
   - **Key**: `CRON_SECRET`
   - **Value**: [paste your generated secret]
   - ✓ Check all environments (Production, Preview, Development)
7. Click **Save**

---

### Step 3: Deploy (1 minute)

Open terminal in your project:

```bash
git add .
git commit -m "Add free backup system"
git push
```

⏳ Wait for Vercel to finish deployment (~1-2 minutes)

---

### Step 4: Register Free Cron Service (2 minutes)

#### Go to: https://cron-job.org/

1. Click **"Sign Up"** (top right)
2. Enter:
   - Email
   - Password
   - ✓ Accept terms
3. Click **"Create account"**
4. ✉️ **Check your email** and click verification link

---

### Step 5: Create Cron Job (1 minute)

1. After login, click **"Create Cronjob"**

2. Fill in the form:

   **Title**: `CampFix Database Backup`
   
   **URL**: `https://www.campfixsti.com/api/cron/backup`
   
   **Schedule**: 
   - Click "Every 15 minutes"
   - Or enter manually: `*/15 * * * *`
   
   **Request Method**: `GET`
   
   **Custom request headers**:
   - Click "+ Add custom header"
   - **Name**: `Authorization`
   - **Value**: `Bearer YOUR_SECRET_KEY_HERE` 
     (Replace YOUR_SECRET_KEY_HERE with your actual key from Step 1)
   
   **Timeout**: `30` seconds
   
   **Enable notifications**: ✓ Check this box
   
   **Notification email**: [your email]

3. Click **"Create Cronjob"** button at bottom

---

### Step 6: Test It Now! (30 seconds)

1. In cron-job.org dashboard, find your new cronjob
2. Click the **▶️ Play button** (Execute now)
3. Wait 5-10 seconds
4. Check "**Last execution**" - should show ✅ Success (200 OK)

---

### Step 7: Verify Backup Status (30 seconds)

Open in browser:
```
https://www.campfixsti.com/api/cron/backup-status
```

You should see:
```json
{
  "status": "healthy",
  "message": "Backup system is running normally",
  "total_backups": 1,
  "last_backup": {
    "filename": "backup-2026-06-03-143500.sql.gz",
    "size_formatted": "5.2 MB",
    "created_at": "2026-06-03 14:35:00"
  }
}
```

---

## ✅ Done! Your System is Now Backing Up Every 15 Minutes

### What Happens Next?

- 🔄 **Every 15 minutes**: Automatic backup
- 📦 **Compressed**: ~5-10 MB per backup
- 🗑️ **Auto-cleanup**: Keeps last 96 backups (1 day)
- ☁️ **Cloud storage**: Uploads to Supabase
- 📧 **Email alerts**: You'll get notified if backup fails

---

## 📊 Monitor Your Backups

### Check Status Anytime
Visit: https://www.campfixsti.com/api/cron/backup-status

### cron-job.org Dashboard
- Login at: https://cron-job.org/
- View execution history
- See success/failure rates
- Download logs

### Supabase Storage
1. Go to: https://supabase.com/dashboard
2. Select your project
3. Click **Storage** in sidebar
4. Open **backups** bucket
5. View all backup files in `database-backups/` folder

---

## 🎛️ Adjust Settings (Optional)

### Change Backup Frequency

Edit `bootstrap/app.php` line with schedule:

```php
// Current: Every 15 minutes (96/day)
->everyFifteenMinutes()

// Options:
->everyFiveMinutes()      // 288/day - More frequent
->everyThirtyMinutes()    // 48/day - Less frequent
->hourly()                // 24/day - Minimal
```

**Also update in cron-job.org**:
- Settings → Edit cronjob → Schedule

### Change Retention (How many to keep)

Edit `app/Console/Commands/BackupDatabase.php` line 76:

```php
// Current: Keep 96 backups (1 day at 15-min intervals)
$this->cleanOldBackups($backupPath, 96);

// Options:
$this->cleanOldBackups($backupPath, 48);   // 12 hours
$this->cleanOldBackups($backupPath, 192);  // 2 days
$this->cleanOldBackups($backupPath, 672);  // 1 week
```

---

## 🆘 Troubleshooting

### ❌ "401 Unauthorized" in cron-job.org logs

**Problem**: Secret key doesn't match

**Fix**:
1. Check Vercel env variable: Settings → Environment Variables → CRON_SECRET
2. Check cron-job.org header: Authorization: Bearer YOUR_KEY
3. Make sure format is exactly: `Bearer YOUR_KEY` (with space)

### ❌ "No backups found" in status page

**Problem**: Backup hasn't run yet or failed

**Fix**:
1. Wait 15 minutes for first backup
2. Or click ▶️ in cron-job.org to test now
3. Check execution log in cron-job.org

### ❌ cron-job.org shows "Timeout"

**Problem**: Backup takes longer than timeout

**Fix**:
1. Edit cronjob in cron-job.org
2. Increase Timeout to 60 seconds
3. Save and test again

### ❌ Can't access backup status page

**Problem**: URL wrong or deployment failed

**Fix**:
1. Check Vercel deployment completed
2. Try: https://www.campfixsti.com/api/cron/backup-status
3. Check browser console for errors

---

## 🎁 Free Tier Limits

### cron-job.org
- ✅ **Unlimited** cron jobs
- ✅ **Unlimited** executions
- ✅ **Email notifications** included
- ✅ **No credit card** required
- ✅ **1-minute** minimum interval

### Vercel Free Tier
- ✅ **100 GB** bandwidth/month
- ✅ **Unlimited** API requests
- ✅ **Unlimited** deployments

### Supabase Free Tier
- ✅ **500 MB** database
- ✅ **1 GB** file storage
- ✅ **2 GB** bandwidth
- ℹ️ 96 backups × 5 MB = ~480 MB (within limit ✓)

**All well within limits!** ✅

---

## 💾 How to Restore a Backup

### Download from Supabase

1. Login: https://supabase.com/dashboard
2. Your project → **Storage**
3. **backups** bucket → **database-backups/**
4. Find your backup file
5. Click **⋯** → **Download**

### Restore to Database

```bash
# If compressed, extract first
gunzip backup-2026-06-03-143500.sql.gz

# Restore (replace with your credentials)
psql -h aws-1-ap-southeast-1.pooler.supabase.com \
     -p 5432 \
     -U postgres.pclfaksjjprickgppnus \
     -d postgres \
     -f backup-2026-06-03-143500.sql
```

Or use Supabase SQL Editor:
1. Dashboard → SQL Editor
2. Paste SQL content
3. Run

---

## 📞 Support

### Check Logs

**Vercel Logs**:
1. Dashboard → Your project → Logs
2. Filter: `backup`

**cron-job.org Logs**:
1. Dashboard → Your cronjob
2. View "Execution log"

**Laravel Logs**:
- File: `storage/logs/laravel.log`

### Still Need Help?

1. Read: `FREE_BACKUP_SOLUTION.md` (detailed guide)
2. Read: `DATABASE_BACKUP_SETUP.md` (complete docs)
3. Check: https://www.campfixsti.com/api/cron/backup-status

---

## 🎉 Congratulations!

Your database is now automatically backed up every 15 minutes, completely free!

**Next Steps**:
- ⏱️ Wait 15 minutes and check status again
- 📧 Check your email for cron notifications  
- 🔍 Explore backups in Supabase Storage
- 📖 Bookmark status page: https://www.campfixsti.com/api/cron/backup-status

---

**Setup Time**: 5 minutes  
**Monthly Cost**: $0  
**Peace of Mind**: Priceless ✨
