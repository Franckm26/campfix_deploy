# Free Database Backup Solution (No Paid Plans Required)

## Overview

Since Vercel Free plan doesn't support cron jobs, we'll use **free external cron services** to trigger your backups every 5 minutes.

## ✅ 100% Free Components

1. **Vercel Free Plan** - Your hosting (already using)
2. **Supabase Free Tier** - Database storage (already using)
3. **cron-job.org** - Free cron service (NEW)
4. **Your Laravel App** - Backup endpoint (already created)

---

## 🚀 Setup Instructions

### Step 1: Create Public Backup Endpoint

We'll create a publicly accessible backup endpoint (secured with a secret key).

Already created: `api/cron/backup.php` ✅

### Step 2: Generate Your Secret Key

Open PowerShell and run:

```powershell
# Generate a random secret key
[Convert]::ToBase64String((1..32 | ForEach-Object { Get-Random -Minimum 0 -Maximum 256 }))
```

Copy the output (example: `x7K2mP9nQ4vL8wR3tY6uA5bC1dF0eH9j`)

### Step 3: Add Secret to Vercel

1. Go to https://vercel.com/dashboard
2. Select your CampFix project
3. Settings → Environment Variables
4. Click "Add New"
   - **Name**: `CRON_SECRET`
   - **Value**: [paste your generated secret]
   - **Environments**: ✓ Production, ✓ Preview, ✓ Development
5. Click "Save"

### Step 4: Deploy to Vercel

```bash
git add .
git commit -m "Add backup system with external cron support"
git push
```

Wait for deployment to complete.

### Step 5: Register Free Cron Service

#### Option A: cron-job.org (Recommended - Most Reliable)

1. **Register Account**:
   - Go to: https://cron-job.org/
   - Click "Sign Up" (100% free, no credit card)
   - Verify your email

2. **Create Cron Job**:
   - Click "Create Cronjob"
   - **Title**: `CampFix Database Backup`
   - **URL**: `https://www.campfixsti.com/api/cron/backup`
   - **Schedule**: Every 5 minutes
     - Pattern: `*/5 * * * *`
   - **Request Method**: GET
   - **Headers**: Click "Add header"
     - Name: `Authorization`
     - Value: `Bearer YOUR_SECRET_KEY_HERE`
   - **Timeout**: 30 seconds
   - **Notifications**: Enable (to get alerts if backup fails)
   - Click "Create Cronjob"

3. **Test Immediately**:
   - Click the ▶️ button to test
   - Check "Execution History" for results

#### Option B: EasyCron.com

1. **Register Account**:
   - Go to: https://www.easycron.com/
   - Sign up (free plan: 100 executions/day)

2. **Create Cron Job**:
   - Click "+ Cron Job"
   - **URL**: `https://www.campfixsti.com/api/cron/backup`
   - **Cron Expression**: `*/5 * * * *`
   - **HTTP Method**: GET
   - **HTTP Headers**:
     ```
     Authorization: Bearer YOUR_SECRET_KEY_HERE
     ```
   - **Timeout**: 30 seconds
   - Click "Create"

3. **Test**: Click "Test" button

#### Option C: UptimeRobot.com (Monitor + Cron)

1. **Register Account**:
   - Go to: https://uptimerobot.com/
   - Sign up (free plan: 50 monitors)

2. **Create Monitor**:
   - Click "+ Add New Monitor"
   - **Monitor Type**: HTTP(s)
   - **Friendly Name**: CampFix Backup
   - **URL**: `https://www.campfixsti.com/api/cron/backup`
   - **Monitoring Interval**: 5 minutes
   - **Custom HTTP Headers**:
     ```
     Authorization: Bearer YOUR_SECRET_KEY_HERE
     ```
   - Click "Create Monitor"

Note: UptimeRobot also monitors uptime, so you get 2-in-1!

#### Option D: GitHub Actions (Free)

If your repo is on GitHub:

1. **Create Workflow File**:
   `.github/workflows/backup.yml`

```yaml
name: Database Backup

on:
  schedule:
    - cron: '*/5 * * * *'  # Every 5 minutes
  workflow_dispatch:  # Manual trigger

jobs:
  backup:
    runs-on: ubuntu-latest
    steps:
      - name: Trigger Backup
        run: |
          curl -X GET https://www.campfixsti.com/api/cron/backup \
            -H "Authorization: Bearer ${{ secrets.CRON_SECRET }}"
```

2. **Add Secret to GitHub**:
   - Repository → Settings → Secrets → Actions
   - New secret: `CRON_SECRET`

---

## 🎯 Recommended Free Setup

**Best Combination** (all free):

1. **Primary**: cron-job.org (every 5 minutes)
2. **Backup Monitor**: UptimeRobot (every 5 minutes + uptime monitoring)
3. **Manual Trigger**: GitHub Actions (on-demand backups)

This gives you redundancy in case one service fails!

---

## ✅ Verification

### Test Your Setup

1. **Check Backup Status**:
   Visit: https://www.campfixsti.com/api/cron/backup-status
   
   Should show:
   ```json
   {
     "status": "healthy",
     "total_backups": 12,
     "last_backup_age_minutes": 3.2
   }
   ```

2. **Manual Test**:
   ```bash
   curl -X GET https://www.campfixsti.com/api/cron/backup \
     -H "Authorization: Bearer YOUR_SECRET_KEY"
   ```

3. **Check Execution Logs**:
   - Go to your cron service dashboard
   - View execution history
   - Should show successful 200 responses

---

## 📊 Monitoring Your Free Backups

### Check Backup Health
Visit anytime: `https://www.campfixsti.com/api/cron/backup-status`

### cron-job.org Dashboard
- View all executions
- See success/failure rates
- Get email alerts on failures
- Download execution logs

### Supabase Storage
1. Go to: https://supabase.com/dashboard
2. Your project → Storage
3. `backups` bucket → `database-backups/`
4. View all uploaded backups

---

## 💾 Where Are Backups Stored?

### Supabase Storage (Persistent)
- **Location**: Supabase Dashboard → Storage → backups bucket
- **Free Tier**: 1 GB storage
- **Retention**: Manual (you control)
- **Access**: Download anytime from dashboard

### Calculate Storage Usage
- Each compressed backup: ~5-10 MB (depends on data)
- Every 5 minutes = 288 backups/day
- With 48 backup retention: ~240-480 MB used
- **Recommendation**: Increase retention cleanup or reduce frequency

---

## ⚙️ Adjust Backup Frequency (Free Tier Friendly)

Edit `bootstrap/app.php`:

```php
// Current: Every 5 minutes (288 backups/day)
$schedule->command('db:backup --compress')->everyFiveMinutes();

// Recommended for FREE tier:

// Every 15 minutes (96 backups/day) - RECOMMENDED
$schedule->command('db:backup --compress')->everyFifteenMinutes();

// Every 30 minutes (48 backups/day) - Good balance
$schedule->command('db:backup --compress')->everyThirtyMinutes();

// Every hour (24 backups/day) - Light usage
$schedule->command('db:backup --compress')->hourly();
```

Also update your cron service schedule accordingly:
- Every 15 min: `*/15 * * * *`
- Every 30 min: `*/30 * * * *`
- Every hour: `0 * * * *`

**💡 Recommendation for FREE plan**: Use **every 15 minutes** as a good balance between safety and resource usage.

---

## 🎁 Free Tier Limits

### cron-job.org (Free)
- ✅ Unlimited cron jobs
- ✅ 1-minute intervals
- ✅ Email notifications
- ✅ Execution history
- ✅ No credit card required

### EasyCron (Free)
- ✅ 100 executions/day
- ⚠️ Every 5 min = 288/day (exceeds limit)
- ✅ Use every 15 min = 96/day ✓

### UptimeRobot (Free)
- ✅ 50 monitors
- ✅ 5-minute intervals
- ✅ Unlimited checks
- ✅ Email alerts

### Supabase (Free Tier)
- ✅ 500 MB database
- ✅ 1 GB storage
- ✅ 2 GB bandwidth
- ⚠️ Monitor usage to avoid limits

### Vercel (Free Tier)
- ✅ 100 GB bandwidth/month
- ✅ Unlimited requests
- ❌ No cron jobs (solved with external service)

---

## 🔒 Security Notes

### Your Secret Key
- Never commit to Git ✅
- Store only in Vercel env variables ✅
- Use in cron service headers ✅
- Rotate every 3-6 months

### Backup Endpoint
- Protected by Authorization header
- Only responds to correct secret
- Returns 401 for unauthorized requests
- Logs all attempts

### Supabase Bucket
- Keep as PRIVATE (not public)
- Only you can access via dashboard
- No direct URL access

---

## 🆘 Troubleshooting

### "401 Unauthorized" Error
**Cause**: Secret key mismatch or missing

**Fix**:
1. Check Vercel env variable: `CRON_SECRET`
2. Verify header in cron service: `Authorization: Bearer YOUR_SECRET`
3. Ensure no extra spaces in secret

### "No backups found" Status
**Cause**: Backups not running yet or pg_dump not available

**Fix**:
1. Check cron execution history
2. View Vercel function logs
3. Test manually with curl command

### Cron Service Shows Errors
**Cause**: Timeout or endpoint unreachable

**Fix**:
1. Increase timeout to 60 seconds
2. Test URL in browser
3. Check Vercel deployment status

### Storage Running Out
**Cause**: Too many backups retained

**Fix**:
1. Reduce backup frequency
2. Increase cleanup retention
3. Manually delete old backups from Supabase

---

## 📈 Recommended FREE Configuration

```php
// In bootstrap/app.php
$schedule->command('db:backup --compress')
    ->everyFifteenMinutes()      // 96 backups/day
    ->withoutOverlapping()
    ->runInBackground();
```

```php
// In BackupDatabase.php
// Change cleanup retention
$this->cleanOldBackups($backupPath, 96);  // Keep 1 day of backups
```

---

## 🎯 Final Setup Checklist

- [ ] Generate secret key
- [ ] Add `CRON_SECRET` to Vercel
- [ ] Deploy to Vercel
- [ ] Register at cron-job.org
- [ ] Create cron job with Authorization header
- [ ] Test cron job execution
- [ ] Create Supabase `backups` bucket
- [ ] Verify backup status endpoint
- [ ] Set up email notifications
- [ ] Adjust frequency if needed (recommend 15 min)
- [ ] Update retention to 96 backups

---

## 💡 Cost Summary

| Service | Plan | Cost | What You Get |
|---------|------|------|--------------|
| Vercel | Free | $0 | Hosting + functions |
| Supabase | Free | $0 | Database + 1GB storage |
| cron-job.org | Free | $0 | Unlimited cron jobs |
| GitHub Actions | Free | $0 | 2000 minutes/month |
| UptimeRobot | Free | $0 | 50 monitors |

**Total Monthly Cost: $0** 🎉

---

## 🚀 Alternative: Supabase Database Webhooks (100% Free)

If you want to avoid external services entirely:

### Use Supabase Edge Functions

1. Create Edge Function in Supabase:
```javascript
// supabase/functions/backup-trigger/index.ts
import { serve } from 'https://deno.land/std@0.168.0/http/server.ts'

serve(async (req) => {
  const response = await fetch('https://www.campfixsti.com/api/cron/backup', {
    headers: {
      'Authorization': 'Bearer YOUR_SECRET'
    }
  })
  
  return new Response(JSON.stringify(await response.json()), {
    headers: { 'Content-Type': 'application/json' }
  })
})
```

2. Schedule with pg_cron (Supabase Pro only - $25/month) ❌

**Verdict**: This requires paid plan, so stick with free external cron services.

---

## 📞 Support & Next Steps

1. **Start with cron-job.org** (easiest and most reliable)
2. **Set frequency to 15 minutes** (free tier friendly)
3. **Monitor for first 24 hours**
4. **Adjust retention to keep 1 day** of backups
5. **Set up alerts** in cron-job.org

Ready to implement? Just follow Step 1-5 above!

---

**Total Setup Time**: ~15 minutes  
**Monthly Cost**: $0  
**Reliability**: High (multiple free services available)
