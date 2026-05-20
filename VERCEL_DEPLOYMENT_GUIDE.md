# CampFix Vercel Deployment Guide

## Current Status
✅ Your application is successfully deployed on Vercel
✅ The landing page is displaying correctly
❌ Database connection is not configured (login will not work)

## What You're Seeing
The landing page at `https://campfix-deploy-met7q0h7i-franckm26s-projects.vercel.app` is **correct behavior**. This is your home page where users can:
- View features
- Learn about CampFix
- Click "Login" to access the application

## Why Login Doesn't Work
Your `.env.vercel` file has empty database credentials. Vercel doesn't provide a database - you need to use an external PostgreSQL service.

## Steps to Fix

### 1. Set Up a PostgreSQL Database

Choose one of these free PostgreSQL hosting services:

#### Option A: Neon (Recommended)
1. Go to https://neon.tech
2. Sign up for free
3. Create a new project
4. Copy the connection string

#### Option B: Supabase
1. Go to https://supabase.com
2. Sign up for free
3. Create a new project
4. Go to Settings > Database
5. Copy the connection details

#### Option C: Railway
1. Go to https://railway.app
2. Sign up for free
3. Create a new PostgreSQL database
4. Copy the connection details

### 2. Update Vercel Environment Variables

1. Go to your Vercel dashboard: https://vercel.com/dashboard
2. Select your `campfix-deploy` project
3. Go to **Settings** > **Environment Variables**
4. Add these variables:

```
DB_CONNECTION=pgsql
DB_HOST=your-database-host.com
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Replace the values with your actual database credentials from step 1.

### 3. Run Database Migrations

After setting up the database, you need to run migrations. You have two options:

#### Option A: Run Locally Against Production Database
```bash
# Update .env.vercel with your database credentials
# Then run migrations
php artisan migrate --env=vercel
```

#### Option B: Use Database GUI
1. Connect to your database using the provider's web interface
2. Import your database schema manually

### 4. Redeploy on Vercel

After updating environment variables:
1. Go to your Vercel project
2. Click **Deployments**
3. Click the three dots on the latest deployment
4. Click **Redeploy**

Or push a new commit to trigger automatic deployment:
```bash
git add .
git commit -m "Update database configuration"
git push
```

## Testing the Deployment

1. Visit your Vercel URL
2. Click "Login" button
3. Try logging in with a valid user account
4. If successful, you'll be redirected to the dashboard

## Common Issues

### Issue: "SQLSTATE[08006] Connection refused"
**Solution**: Check that your database host, port, and credentials are correct in Vercel environment variables.

### Issue: "Base table or view not found"
**Solution**: Run database migrations against your production database.

### Issue: "Session not working"
**Solution**: This is expected with `SESSION_DRIVER=cookie` in serverless. Consider using database sessions:
```
SESSION_DRIVER=database
```
Then run: `php artisan session:table` and `php artisan migrate`

### Issue: "Storage/logs not writable"
**Solution**: This is expected on Vercel (read-only filesystem). Logs are sent to stderr (Vercel logs).

## Environment Variables Checklist

Make sure these are set in Vercel:

- ✅ `APP_NAME=CampFix`
- ✅ `APP_ENV=production`
- ✅ `APP_KEY=base64:...` (your app key)
- ✅ `APP_DEBUG=false`
- ✅ `APP_URL=https://your-vercel-url.vercel.app`
- ❌ `DB_HOST=` (needs to be set)
- ❌ `DB_DATABASE=` (needs to be set)
- ❌ `DB_USERNAME=` (needs to be set)
- ❌ `DB_PASSWORD=` (needs to be set)
- ✅ `SESSION_DRIVER=cookie`
- ✅ `CACHE_STORE=array`
- ✅ `LOG_CHANNEL=stderr`

## Quick Setup with Neon (Recommended)

1. **Create Neon Database**
   ```
   Visit: https://neon.tech
   Sign up > Create Project > Copy connection string
   ```

2. **Extract Connection Details**
   From connection string like:
   ```
   postgresql://user:password@host.neon.tech/dbname?sslmode=require
   ```
   
   Extract:
   - DB_HOST: `host.neon.tech`
   - DB_DATABASE: `dbname`
   - DB_USERNAME: `user`
   - DB_PASSWORD: `password`

3. **Add to Vercel**
   ```
   Vercel Dashboard > Settings > Environment Variables
   Add each variable above
   ```

4. **Run Migrations**
   ```bash
   # Update .env.vercel with Neon credentials
   php artisan migrate --env=vercel
   ```

5. **Redeploy**
   ```bash
   git commit --allow-empty -m "Trigger redeploy"
   git push
   ```

## Need Help?

If you encounter issues:
1. Check Vercel deployment logs: Dashboard > Deployments > Click deployment > View Function Logs
2. Check Vercel runtime logs for errors
3. Verify all environment variables are set correctly
4. Test database connection locally first

## Summary

Your Vercel deployment is **working correctly** - it's showing the landing page as designed. To make login work, you just need to:
1. Set up a PostgreSQL database (Neon recommended)
2. Add database credentials to Vercel environment variables
3. Run migrations
4. Redeploy

The landing page is the entry point - users click "Login" to access the full application.
