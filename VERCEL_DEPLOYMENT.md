# Deploy CampFix to Vercel

## ⚠️ Important Limitations

Vercel has significant limitations for Laravel:
- **No persistent file storage** (uploads will be lost on redeployment)
- **No background jobs/queues** (scheduled tasks won't work)
- **Limited database options** (must use external database)
- **Cold starts** (first request may be slow)
- **Session issues** (must use cookie or database sessions)

**Recommended:** Use Railway, Render, or Fly.io instead for full Laravel support.

## Prerequisites

1. **Vercel Account** - Sign up at https://vercel.com
2. **External PostgreSQL Database** - Options:
   - Vercel Postgres (Beta): https://vercel.com/docs/storage/vercel-postgres
   - Supabase (Free): https://supabase.com
   - Neon (Free): https://neon.tech
   - ElephantSQL (Free): https://www.elephantsql.com

3. **File Storage** (Required for uploads):
   - AWS S3
   - Cloudinary (Free tier)
   - Uploadcare

## Step-by-Step Deployment

### 1. Set Up External Database

**Option A: Vercel Postgres (Recommended)**
```bash
# Install Vercel CLI
npm i -g vercel

# Login
vercel login

# Link project
vercel link

# Create Postgres database
vercel postgres create
```

**Option B: Supabase (Free)**
1. Go to https://supabase.com
2. Create new project
3. Get connection string from Settings → Database
4. Format: `postgresql://postgres:[password]@[host]:5432/postgres`

### 2. Configure Environment Variables

In Vercel Dashboard → Your Project → Settings → Environment Variables, add:

```env
# App Configuration
APP_NAME=CampFix
APP_ENV=production
APP_KEY=base64:your-key-here
APP_DEBUG=false
APP_URL=https://your-app.vercel.app

# Database (from Vercel Postgres or Supabase)
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Session (MUST use cookie or database)
SESSION_DRIVER=cookie
SESSION_LIFETIME=120

# Cache (MUST use array or database)
CACHE_DRIVER=array

# Queue (Won't work on Vercel)
QUEUE_CONNECTION=sync

# File Storage (REQUIRED - use S3 or Cloudinary)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@campfix.com
MAIL_FROM_NAME=CampFix

# SMS
PHILSMS_API_KEY=your-key
PHILSMS_SENDER_ID=your-sender

# JWT
JWT_SECRET=your-jwt-secret

# Vercel-specific
VIEW_COMPILED_PATH=/tmp
CACHE_DRIVER=array
LOG_CHANNEL=stderr
```

### 3. Update Laravel Configuration

**Update `config/filesystems.php`** - Set default to S3:
```php
'default' => env('FILESYSTEM_DISK', 's3'),
```

**Update `config/session.php`** - Use cookie driver:
```php
'driver' => env('SESSION_DRIVER', 'cookie'),
```

**Update `config/cache.php`** - Use array driver:
```php
'default' => env('CACHE_DRIVER', 'array'),
```

### 4. Install Vercel CLI

```bash
npm i -g vercel
```

### 5. Deploy to Vercel

```bash
# Login to Vercel
vercel login

# Deploy (first time)
vercel

# Follow prompts:
# - Set up and deploy? Yes
# - Which scope? Your account
# - Link to existing project? No
# - Project name? campfix
# - Directory? ./
# - Override settings? No

# Deploy to production
vercel --prod
```

### 6. Run Database Migrations

**Option A: Using Vercel CLI**
```bash
# This won't work directly, you need to run locally against production DB
php artisan migrate --force
```

**Option B: Using Database Client**
1. Connect to your production database
2. Run migrations manually
3. Or use a migration tool like TablePlus

### 7. Set Up File Storage (S3)

**Install AWS SDK:**
```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

**Create S3 Bucket:**
1. Go to AWS Console → S3
2. Create new bucket
3. Set permissions (public read for uploads)
4. Get Access Key and Secret from IAM

**Update `.env` on Vercel:**
```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=campfix-uploads
```

### 8. Build Assets

```bash
# Build frontend assets
npm install
npm run build

# Commit built assets
git add public/build
git commit -m "Build assets for production"
git push
```

### 9. Configure Custom Domain (Optional)

1. Go to Vercel Dashboard → Your Project → Settings → Domains
2. Add your custom domain
3. Update DNS records as instructed
4. Update `APP_URL` in environment variables

## Post-Deployment Configuration

### Update File Upload Code

Since Vercel has no persistent storage, all uploads MUST go to S3:

```php
// In your controllers, use:
Storage::disk('s3')->put('path/file.jpg', $file);

// Get URL:
$url = Storage::disk('s3')->url('path/file.jpg');
```

### Disable Features That Won't Work

**Scheduled Jobs:**
- Laravel scheduler won't work on Vercel
- Use external cron service: https://cron-job.org
- Or use Vercel Cron (limited): https://vercel.com/docs/cron-jobs

**Queues:**
- Background jobs won't work
- Set `QUEUE_CONNECTION=sync` (runs immediately)
- Or use external queue service (Redis Cloud, AWS SQS)

## Troubleshooting

### 500 Internal Server Error
1. Check Vercel logs: `vercel logs`
2. Enable debug: Set `APP_DEBUG=true` temporarily
3. Check function logs in Vercel dashboard

### Database Connection Failed
1. Verify database credentials
2. Check if database allows external connections
3. Test connection locally with production credentials

### File Upload Fails
1. Verify S3 credentials
2. Check bucket permissions
3. Ensure `FILESYSTEM_DISK=s3` is set

### Session Issues
1. Use `SESSION_DRIVER=cookie`
2. Or use `SESSION_DRIVER=database` with migrations
3. Don't use `file` driver (won't work)

### Assets Not Loading
1. Run `npm run build` locally
2. Commit `public/build` directory
3. Push to GitHub
4. Redeploy on Vercel

## Vercel CLI Commands

```bash
# Deploy to preview
vercel

# Deploy to production
vercel --prod

# View logs
vercel logs

# View environment variables
vercel env ls

# Add environment variable
vercel env add

# Remove deployment
vercel remove [deployment-url]

# Open project in browser
vercel open
```

## Alternative: Hybrid Approach

If Vercel doesn't work well, consider:

1. **Frontend on Vercel** - Deploy static assets only
2. **Backend on Railway** - Deploy Laravel API
3. **Use Vercel rewrites** to proxy API calls

This gives you Vercel's CDN for frontend with proper Laravel hosting for backend.

## Cost Estimate

**Vercel:**
- Free tier: 100GB bandwidth, 100 serverless function executions
- Pro: $20/month for more resources

**External Services:**
- Database: Free tier on Supabase/Neon
- S3: ~$0.023/GB storage + transfer costs
- Total: Can stay free for small apps

## Support Resources

- Vercel Docs: https://vercel.com/docs
- Vercel PHP Runtime: https://github.com/vercel-community/php
- Laravel Deployment: https://laravel.com/docs/deployment
- Vercel Discord: https://vercel.com/discord

## Final Recommendation

If you experience issues with Vercel (which is likely for a full Laravel app), please consider:
- **Railway** - Best for Laravel, free tier
- **Render** - Good alternative, free tier
- **Fly.io** - Powerful free tier

These platforms are designed for full-stack apps and will save you many headaches.
