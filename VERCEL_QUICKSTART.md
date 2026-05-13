# 🚀 Quick Start: Deploy to Vercel

## Before You Start - Important!

⚠️ **Vercel Limitations for Laravel:**
- No persistent file storage (need S3/Cloudinary)
- No background jobs/scheduler
- Must use external database
- Session must use cookies or database

**If these limitations are a problem, use Railway instead** (see RAILWAY_DEPLOYMENT.md)

## Quick Deploy Steps

### 1. Install Vercel CLI
```bash
npm install -g vercel
```

### 2. Login to Vercel
```bash
vercel login
```

### 3. Set Up Database First

**Option A: Vercel Postgres (Easiest)**
```bash
vercel postgres create
```

**Option B: Supabase (Free)**
1. Go to https://supabase.com
2. Create project
3. Get connection string from Settings → Database

### 4. Deploy
```bash
# From your project directory
vercel

# Answer prompts:
# - Set up and deploy? Yes
# - Which scope? Your account  
# - Link to existing project? No
# - Project name? campfix
# - Directory? ./
# - Override settings? No
```

### 5. Add Environment Variables

Go to Vercel Dashboard → Your Project → Settings → Environment Variables

**Required Variables:**
```env
APP_KEY=base64:... (run: php artisan key:generate --show)
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

SESSION_DRIVER=cookie
CACHE_DRIVER=array

PHILSMS_API_KEY=your-key
PHILSMS_SENDER_ID=your-sender
```

### 6. Deploy to Production
```bash
vercel --prod
```

### 7. Run Migrations

Connect to your database and run:
```bash
# Using your production database credentials locally
php artisan migrate --force
```

## That's It!

Your app should now be live at: `https://your-app.vercel.app`

## Next Steps

1. **Set up file storage** (S3 or Cloudinary) - See VERCEL_DEPLOYMENT.md
2. **Configure custom domain** - In Vercel dashboard
3. **Test all features** - Some may not work due to Vercel limitations

## Need Help?

- Full guide: See `VERCEL_DEPLOYMENT.md`
- Vercel docs: https://vercel.com/docs
- Issues? Consider Railway: See `RAILWAY_DEPLOYMENT.md`

## Common Issues

**500 Error?**
```bash
# Check logs
vercel logs

# Enable debug temporarily
vercel env add APP_DEBUG true
```

**Database connection failed?**
- Verify credentials in Vercel dashboard
- Check database allows external connections

**File uploads not working?**
- You MUST use S3 or Cloudinary
- See VERCEL_DEPLOYMENT.md for setup
