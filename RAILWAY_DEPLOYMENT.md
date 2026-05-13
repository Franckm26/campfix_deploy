# Deploy CampFix to Railway

## Prerequisites
- GitHub account with your repository
- Railway account (sign up at https://railway.app)

## Step-by-Step Deployment

### 1. Sign Up for Railway
1. Go to https://railway.app
2. Click "Login" and sign in with GitHub
3. Authorize Railway to access your repositories

### 2. Create New Project
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose your `campfix_deploy` repository
4. Railway will automatically detect it's a Laravel app

### 3. Add PostgreSQL Database
1. In your project dashboard, click "New"
2. Select "Database" → "Add PostgreSQL"
3. Railway will automatically create a database and set environment variables

### 4. Configure Environment Variables
In your Railway project settings, add these variables:

```
APP_NAME=CampFix
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.up.railway.app

DB_CONNECTION=pgsql
DB_HOST=${{Postgres.PGHOST}}
DB_PORT=${{Postgres.PGPORT}}
DB_DATABASE=${{Postgres.PGDATABASE}}
DB_USERNAME=${{Postgres.PGUSER}}
DB_PASSWORD=${{Postgres.PGPASSWORD}}

SESSION_DRIVER=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@campfix.com
MAIL_FROM_NAME=CampFix

PHILSMS_API_KEY=your-philsms-key
PHILSMS_SENDER_ID=your-sender-id

JWT_SECRET=your-jwt-secret
```

**Important:** Railway will auto-populate database variables using `${{Postgres.VARIABLE}}` syntax.

### 5. Generate APP_KEY
1. In Railway dashboard, go to your service
2. Click "Settings" → "Variables"
3. Add `APP_KEY` with value from running: `php artisan key:generate --show`

### 6. Run Migrations
After first deployment:
1. Go to your service in Railway
2. Click "Settings" → "Deploy"
3. In the deployment logs, you can run commands
4. Or use Railway CLI:
   ```bash
   railway run php artisan migrate --force
   railway run php artisan db:seed --force
   ```

### 7. Set Up Storage
Railway provides ephemeral storage. For persistent file uploads, use:

**Option A: AWS S3 (Recommended)**
1. Create S3 bucket
2. Add to Railway environment:
   ```
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=your-key
   AWS_SECRET_ACCESS_KEY=your-secret
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=your-bucket-name
   ```

**Option B: Cloudinary (Free tier)**
1. Sign up at cloudinary.com
2. Install package: `composer require cloudinary-labs/cloudinary-laravel`
3. Add credentials to Railway

### 8. Set Up Scheduled Jobs (Optional)
For Laravel scheduler:
1. In Railway, add a new service
2. Choose "Cron Job"
3. Set schedule: `* * * * *` (every minute)
4. Command: `php artisan schedule:run`

### 9. Custom Domain (Optional)
1. In Railway project settings
2. Go to "Settings" → "Domains"
3. Click "Generate Domain" for free subdomain
4. Or add custom domain

## Post-Deployment Checklist

- [ ] Database migrations completed
- [ ] APP_KEY generated and set
- [ ] File uploads working (S3/Cloudinary configured)
- [ ] Email sending working
- [ ] SMS notifications working
- [ ] Scheduled jobs running
- [ ] SSL certificate active (automatic on Railway)
- [ ] Test all major features

## Troubleshooting

### Build Fails
- Check `nixpacks.toml` has correct PHP extensions
- Verify `composer.json` has all dependencies
- Check Railway build logs for errors

### Database Connection Issues
- Verify PostgreSQL service is running
- Check environment variables are set correctly
- Ensure `DB_CONNECTION=pgsql` in .env

### File Upload Issues
- Configure S3 or Cloudinary
- Update `config/filesystems.php`
- Set `FILESYSTEM_DISK` environment variable

### 500 Errors
- Set `APP_DEBUG=true` temporarily to see errors
- Check Railway logs: `railway logs`
- Verify all environment variables are set

## Railway CLI (Optional)

Install Railway CLI for easier management:

```bash
# Install
npm i -g @railway/cli

# Login
railway login

# Link to project
railway link

# View logs
railway logs

# Run commands
railway run php artisan migrate
railway run php artisan cache:clear

# Open in browser
railway open
```

## Cost Estimate

Railway Free Tier:
- $5 credit per month
- 512MB RAM
- 1GB storage
- Shared CPU
- Usually sufficient for small to medium apps

If you exceed free tier, Railway charges $0.000231/GB-second for usage.

## Alternative: Render.com

If Railway doesn't work, try Render:
1. Sign up at render.com
2. Create "New Web Service"
3. Connect GitHub repo
4. Build command: `composer install && php artisan config:cache`
5. Start command: `php artisan serve --host=0.0.0.0 --port=$PORT`
6. Add PostgreSQL database
7. Set environment variables

## Support

- Railway Docs: https://docs.railway.app
- Railway Discord: https://discord.gg/railway
- Laravel Deployment: https://laravel.com/docs/deployment
