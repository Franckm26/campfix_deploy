# Debug Vercel Deployment

## Check Logs

```bash
# Install Vercel CLI if not installed
npm i -g vercel

# Login
vercel login

# Link to your project
vercel link

# View runtime logs
vercel logs

# View build logs
vercel logs --build
```

## Common Issues & Fixes

### 1. Blank Page = Missing Database

**Problem**: Laravel can't connect to database and fails silently.

**Solution**: Set up Vercel Postgres:
1. Go to Vercel Dashboard → Your Project
2. Click "Storage" tab
3. Click "Create Database" → "Postgres"
4. Vercel will auto-add DB environment variables
5. Redeploy

### 2. Check if Vendor Directory Exists

In Vercel Dashboard → Deployments → Latest → Source:
- Check if `vendor/` directory exists
- If missing, Composer didn't run

**Fix**: The PHP runtime should handle this automatically now.

### 3. Enable Debug Mode Temporarily

In Vercel Dashboard → Settings → Environment Variables:
- Change `APP_DEBUG` to `true`
- Redeploy
- Visit site to see actual error
- **Remember to set back to `false` after debugging!**

### 4. Check Function Logs

1. Go to Vercel Dashboard → Your Project
2. Click "Deployments"
3. Click on latest deployment
4. Click "Functions" tab
5. Click on `api/index.php`
6. View logs for errors

## Most Likely Issue: No Database

Laravel requires a database connection. Without it, the app won't work.

### Quick Fix: Add Vercel Postgres

```bash
# Using Vercel CLI
vercel postgres create

# This will:
# 1. Create a Postgres database
# 2. Automatically add DB environment variables
# 3. You just need to redeploy
```

### Or Use External Database (Supabase)

1. Go to https://supabase.com
2. Create new project
3. Get connection string from Settings → Database
4. Add to Vercel environment variables:
   - `DB_HOST`: `db.xxx.supabase.co`
   - `DB_PORT`: `5432`
   - `DB_DATABASE`: `postgres`
   - `DB_USERNAME`: `postgres`
   - `DB_PASSWORD`: `your-password`
5. Redeploy

## After Adding Database

Run migrations:
```bash
# Connect to your production database locally
# Update your local .env with production DB credentials temporarily

php artisan migrate --force
php artisan db:seed --force

# Then change .env back to local settings
```

## Still Not Working?

### Switch to Railway (Recommended)

Vercel simply isn't designed for Laravel. Railway is:

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login
railway login

# Initialize
railway init

# Add Postgres
railway add

# Deploy
railway up

# Run migrations
railway run php artisan migrate --force
```

Railway will work out of the box with Laravel!

## Comparison

| Feature | Vercel | Railway |
|---------|--------|---------|
| PHP Support | Limited | Full ✅ |
| Database | External only | Built-in ✅ |
| File Storage | No | Yes ✅ |
| Queues/Jobs | No | Yes ✅ |
| Scheduler | No | Yes ✅ |
| Setup Time | Hours | Minutes ✅ |

## Next Steps

1. **Check logs**: `vercel logs`
2. **Add database**: Vercel Postgres or Supabase
3. **Enable debug**: Set `APP_DEBUG=true` temporarily
4. **Or switch to Railway**: Much easier!

Need help? Let me know what the logs show!
