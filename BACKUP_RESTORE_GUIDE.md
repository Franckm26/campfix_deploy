# 📥 Database Restore Guide

## How to Restore Your Database from Backup

### 🔍 Step 1: List Available Backups

**Using Browser:**
```
https://www.campfixsti.com/api/cron/list-backups
```

Add your CRON_SECRET as Authorization header, or use curl:

```bash
curl -H "Authorization: Bearer YOUR_CRON_SECRET" \
  https://www.campfixsti.com/api/cron/list-backups
```

**Response:**
```json
{
  "success": true,
  "total_backups": 10,
  "backups": [
    {
      "filename": "backup-2026-06-03-120240.json",
      "size_mb": 2.44,
      "created_at": "2026-06-03T12:02:40",
      "restore_url": "https://www.campfixsti.com/api/cron/restore-backup?filename=..."
    }
  ]
}
```

---

### 🔄 Step 2: Restore from a Backup

**Method 1: Using the restore_url from list**

```bash
curl -H "Authorization: Bearer YOUR_CRON_SECRET" \
  "https://www.campfixsti.com/api/cron/restore-backup?filename=backup-2026-06-03-120240.json"
```

**Method 2: Direct restore**

```bash
curl -H "Authorization: Bearer YOUR_CRON_SECRET" \
  "https://www.campfixsti.com/api/cron/restore-backup?filename=YOUR_BACKUP_FILENAME.json"
```

**Response:**
```json
{
  "success": true,
  "message": "Restore completed",
  "filename": "backup-2026-06-03-120240.json",
  "tables_restored": 8,
  "total_rows_restored": 1025,
  "details": [
    {
      "table": "users",
      "rows": 150,
      "errors": 0
    },
    {
      "table": "concerns",
      "rows": 850,
      "errors": 0
    }
  ]
}
```

---

## ⚠️ Important Notes

### Before Restoring:

1. **⚠️ WARNING**: Restore will UPDATE/OVERWRITE existing data
2. **Backup current data** before restoring (optional but recommended)
3. **Test on a staging environment** first if possible
4. **Notify users** if restoring production data

### What Happens During Restore:

- Reads backup JSON file from Supabase Storage
- For each table in backup:
  - Inserts/updates rows using `merge-duplicates` strategy
  - Existing rows with same ID will be updated
  - New rows will be inserted
- Returns summary of restored tables and rows

### Restore Behavior:

- **Duplicate rows**: Updates existing (based on primary key)
- **Missing rows**: Inserts new
- **Deleted data**: NOT restored (only what's in backup)
- **Schema changes**: May cause errors if structure changed

---

## 🛡️ Safe Restore Process

### Recommended Steps:

1. **Create a fresh backup first**:
   ```
   https://www.campfixsti.com/api/cron/supabase-backup
   ```

2. **List backups and identify the one you want**:
   ```
   https://www.campfixsti.com/api/cron/list-backups
   ```

3. **Put site in maintenance mode** (optional for production)

4. **Run restore**:
   ```
   https://www.campfixsti.com/api/cron/restore-backup?filename=BACKUP_FILE.json
   ```

5. **Verify data** after restore

6. **Remove maintenance mode**

---

## 💡 Common Use Cases

### Restore After Accidental Deletion

Find a backup from before the deletion:
```bash
# 1. List backups
curl -H "Authorization: Bearer SECRET" \
  https://www.campfixsti.com/api/cron/list-backups

# 2. Choose backup from before deletion (e.g., 2 hours ago)
# 3. Restore
curl -H "Authorization: Bearer SECRET" \
  "https://www.campfixsti.com/api/cron/restore-backup?filename=backup-2026-06-03-100000.json"
```

### Restore Specific Time Point

```bash
# Restore to 10:30 AM today
curl -H "Authorization: Bearer SECRET" \
  "https://www.campfixsti.com/api/cron/restore-backup?filename=backup-2026-06-03-103000.json"
```

### Clone Data to Another Environment

1. Download backup JSON from Supabase
2. Upload to another Supabase project
3. Run restore on that project

---

## 🔒 Security

### Authentication Required

All restore endpoints require:
```
Authorization: Bearer YOUR_CRON_SECRET
```

Without correct secret, returns:
```json
{
  "success": false,
  "error": "Unauthorized"
}
```

### Best Practices:

- ✅ Keep CRON_SECRET private
- ✅ Only restore from trusted backups
- ✅ Verify backup before restoring
- ✅ Test restore in staging first
- ✅ Monitor restore logs

---

## 🚨 Troubleshooting

### "Failed to download backup file"

**Cause**: Backup file doesn't exist or wrong filename

**Solution**:
1. List backups to get correct filename
2. Verify filename spelling

### "Invalid backup file format"

**Cause**: Corrupted or wrong file

**Solution**:
1. Try different backup file
2. Download and inspect JSON manually

### Rows not restoring

**Cause**: Schema mismatch or constraints

**Solution**:
1. Check `details` in response for error samples
2. Verify database schema matches backup
3. Check unique constraints and foreign keys

### Restore taking too long

**Cause**: Large backup file

**Solution**:
- Wait (can take 2-5 minutes for large databases)
- Increase timeout in code if needed
- Consider restoring table by table

---

## 📊 Restore Status Codes

| HTTP Code | Meaning |
|-----------|---------|
| 200 | Success - restore completed |
| 401 | Unauthorized - wrong/missing CRON_SECRET |
| 404 | Backup file not found |
| 500 | Server error - check error message |

---

## 🎯 Quick Reference

### List Backups
```
GET https://www.campfixsti.com/api/cron/list-backups
Header: Authorization: Bearer YOUR_SECRET
```

### Restore Backup
```
GET https://www.campfixsti.com/api/cron/restore-backup?filename=BACKUP_FILE.json
Header: Authorization: Bearer YOUR_SECRET
```

### Create New Backup
```
GET https://www.campfixsti.com/api/cron/supabase-backup
Header: Authorization: Bearer YOUR_SECRET
```

---

## 🔗 Related

- **Backup System**: See `BACKUP_SUPABASE_SOLUTION.md`
- **Free Setup**: See `FREE_BACKUP_SOLUTION.md`
- **Cheat Sheet**: See `BACKUP_CHEATSHEET.md`

---

**Last Updated**: June 3, 2026  
**System**: Vercel + Supabase  
**Cost**: $0/month (FREE)
