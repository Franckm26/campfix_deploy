# ✅ Supabase-Native Backup Solution (Works on Vercel!)

## 🎯 The Solution

Since Vercel's serverless environment doesn't have PostgreSQL's `pg_dump` tool, we're using **Supabase's native backup approach** instead.

## 🆚 Two Backup Options

### **Option 1: Supabase Dashboard Backups** (Easiest - Recommended)

**✅ Pros:**
- Completely automatic
- No setup needed
- Most reliable
- Point-in-Time Recovery (on paid plans)

**How to use:**
1. Go to: https://supabase.com/dashboard
2. Your project → **Database** → **Backups**
3. Free tier: Daily automatic backups
4. Pro plan ($25/mo): Point-in-Time Recovery

**💡 This is the BEST solution for production!**

---

### **Option 2: Custom Supabase API Backup** (We just created this)

**✅ Pros:**
- Works on Vercel serverless
- Customizable backup frequency
- Uses Supabase REST API (no pg_dump needed)
- FREE

**❌ Cons:**
- Backs up data only (not schema/structure)
- Slower for large databases
- JSON format (not SQL)

---

## 🚀 Setup Custom Backup (Option 2)

### Step 1: Update cron-job.org

1. Go to: https://cron-job.org/
2. Edit your **BACKUP** job
3. Change URL to:
   ```
   https://www.campfixsti.com/api/cron/backup-supabase
   ```
4. Keep the Authorization header the same
5. Save

### Step 2: Wait for Vercel Deployment

Wait for deployment to complete, then test:

```
https://www.campfixsti.com/api/cron/backup-supabase
```

### Step 3: Test in cron-job.org

Click **▶️ Execute now**

Should show:
```json
{
  "success": true,
  "message": "Backup completed successfully",
  "filename": "supabase-backup-2026-06-03-110509.json",
  "tables_backed_up": 5,
  "total_records": 1250,
  "size_formatted": "2.5 MB"
}
```

---

## 📊 What Gets Backed Up?

Currently configured tables:
- `users`
- `concerns`
- `event_requests`
- `notifications`
- `sessions`

### Add More Tables

Edit `api/cron/backup-supabase.php` and add your tables:

```php
$tables = [
    'users',
    'concerns',
    'event_requests',
    'notifications',
    'sessions',
    'your_other_table_here',  // Add more here
];
```

---

## 💾 Where Are Backups Stored?

**Supabase Storage**:
1. Go to: https://supabase.com/dashboard
2. Your project → **Storage**
3. **backups** bucket → **database-backups/**
4. Download `.json` files

---

## 🔄 Restore from Backup

### Download Backup

1. Supabase Dashboard → Storage → backups → database-backups/
2. Download the `.json` file
3. Open in text editor

### Restore Data

**Option A: Manual (Small databases)**
1. Open Supabase SQL Editor
2. For each table, use INSERT statements

**Option B: Script (Recommended)**
Create a restore script that reads the JSON and inserts data back via Supabase API.

---

## ⚙️ Configuration

### Current Settings

- **Frequency**: Every 15 minutes
- **Format**: JSON
- **Storage**: Supabase Storage
- **Tables**: 5 tables (add more as needed)

### Adjust Frequency

In cron-job.org, change schedule:
- Every 15 min: `*/15 * * * *`
- Every 30 min: `*/30 * * * *`
- Hourly: `0 * * * *`
- Daily: `0 2 * * *` (2 AM)

---

## 📈 Comparison

| Feature | pg_dump (Original) | Supabase API (New) | Dashboard |
|---------|-------------------|-------------------|-----------|
| Works on Vercel | ❌ No | ✅ Yes | ✅ Yes |
| Setup complexity | Medium | Easy | None |
| Backup speed | Fast | Slower | N/A |
| Schema backup | ✅ Yes | ❌ No | ✅ Yes |
| Data backup | ✅ Yes | ✅ Yes | ✅ Yes |
| Restore ease | Easy | Manual | Easy |
| Free | ✅ | ✅ | ✅ Daily |
| Frequency | Any | Any | Daily (free) |

---

## 💡 Recommendation

**For Most Users:**
Use **Supabase Dashboard Backups** (Option 1) for simplicity and reliability.

**For Custom Frequency:**
Use **Supabase API Backup** (Option 2) for every 15-minute backups.

**For Production:**
Upgrade to Supabase Pro ($25/mo) for Point-in-Time Recovery.

---

## 🆘 Troubleshooting

### "No tables found" error

Check your table names in `backup-supabase.php`:
```php
$tables = [
    'users',  // Make sure these match your actual table names
    'concerns',
];
```

### "403 Forbidden" on upload

1. Check SUPABASE_KEY in Vercel env vars
2. Use **service_role_key** (not anon key)
3. Create `backups` bucket in Supabase Storage

### "Timeout" error

- Reduce number of tables
- Increase timeout in cron-job.org to 60 seconds
- Consider daily backups instead of 15-min

---

## 📝 Summary

✅ **New backup endpoint**: `/api/cron/backup-supabase`  
✅ **Works on Vercel**: No pg_dump needed  
✅ **Uses Supabase API**: Native integration  
✅ **Completely FREE**: No paid services  
✅ **Easy to customize**: Add/remove tables  

---

## 🎯 Next Steps

1. ⏱️ Wait for Vercel deployment to finish
2. 🔄 Update cron-job.org URL to use `/backup-supabase`
3. ▶️ Test it with "Execute now"
4. ✅ Verify in Supabase Storage
5. 📅 Set to run every 15 minutes (or adjust as needed)

---

**Created**: June 3, 2026  
**Version**: 2.0 - Supabase Native Edition  
**Cost**: $0/month  
**Works on**: Vercel Free Tier ✅
