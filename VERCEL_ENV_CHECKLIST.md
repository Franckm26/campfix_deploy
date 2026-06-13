# ✅ Vercel Environment Variables Checklist

## Microsoft OAuth Variables (Already Set)
- [x] `MICROSOFT_CLIENT_ID` = `bf0facf2-f1d8-418c-8d55-43a98a9ce3d5`
- [x] `MICROSOFT_CLIENT_SECRET` = `05b44b48-ce54-40d5-90c0-6f7ecdfa5f4e`
- [x] `MICROSOFT_REDIRECT_URI` = `https://www.campfixsti.com/auth/microsoft/callback`
- [x] `MICROSOFT_TENANT_ID` = `common`

## Session Configuration Variables

### ❌ DELETE THIS VARIABLE
- [ ] **DELETE** `SESSION_DRIVER` (don't change it - DELETE it entirely!)

### ✅ KEEP/ADD THESE VARIABLES
- [ ] `SESSION_CONNECTION` = `pgsql`
- [ ] `SESSION_TABLE` = `sessions`
- [ ] `SESSION_SAME_SITE` = `lax`
- [ ] `SESSION_SECURE_COOKIE` = `true`
- [ ] `SESSION_ENCRYPT` = `true`
- [ ] `SESSION_LIFETIME` = `120`

## Database Variables (Already Set)
- [x] `DB_CONNECTION` = `pgsql`
- [x] `DB_HOST` = `aws-1-ap-southeast-1.pooler.supabase.com`
- [x] `DB_PORT` = `5432`
- [x] `DB_DATABASE` = `postgres`
- [x] `DB_USERNAME` = `postgres.pclfaksjjprickgppnus`
- [x] `DB_PASSWORD` = `myUiyaAg0DYwDgrI`
- [x] `DB_SCHEMA` = `public`

## Application Variables (Already Set)
- [x] `APP_ENV` = `production`
- [x] `APP_KEY` = `base64:7VlEL0FKZJkz8lR4rhbE2K1jXiawHjYkEcxV3CDCqQc=`
- [x] `APP_DEBUG` = `false`
- [x] `APP_URL` = `https://www.campfixsti.com`
- [x] `ASSET_URL` = `https://www.campfixsti.com`

---

## Step-by-Step Action Plan

### 1. Delete SESSION_DRIVER
1. Open Vercel Dashboard
2. Go to your Campfix project
3. Settings → Environment Variables
4. Find `SESSION_DRIVER`
5. Click the 3 dots (⋮) → **Remove**
6. Confirm deletion

### 2. Verify/Add Session Variables
Check each variable in the "Session Configuration" section above:

**If missing, add it:**
- Click "Add New" in Environment Variables
- Enter the name (e.g., `SESSION_CONNECTION`)
- Enter the value (e.g., `pgsql`)
- Select environment: **Production**, **Preview**, **Development** (all)
- Click "Save"

**If exists with wrong value:**
- Click "Edit" (pencil icon)
- Update the value
- Click "Save"

### 3. Redeploy
1. Go to Deployments tab
2. Click 3 dots on latest deployment
3. Select "Redeploy"
4. **Uncheck** "Use existing Build Cache"
5. Click "Redeploy"

### 4. Test
After deployment completes:
1. Visit: https://www.campfixsti.com/test-oauth-status
2. Check `session_driver` is `"database"`
3. Clear browser cookies completely
4. Test Microsoft login

---

## Common Mistakes to Avoid

❌ **Wrong**: Changing `SESSION_DRIVER` from `cookie` to `database`  
✅ **Right**: **Deleting** `SESSION_DRIVER` entirely

❌ **Wrong**: Keeping browser cookies from old sessions  
✅ **Right**: Clear ALL cookies or use Incognito

❌ **Wrong**: Redeploying WITH build cache  
✅ **Right**: Redeploy WITHOUT build cache (uncheck the box)

❌ **Wrong**: Only adding variables to "Production" environment  
✅ **Right**: Add to all environments (Production, Preview, Development)

---

## Verification Commands

### Check session driver in production:
```
https://www.campfixsti.com/test-oauth-status
```

Expected: `"session_driver": "database"`

### Check Socialite config:
```
https://www.campfixsti.com/test-socialite-config
```

Expected:
```json
{
  "microsoft_client_id": "Set",
  "microsoft_secret": "Set",
  "microsoft_redirect": "https://www.campfixsti.com/auth/microsoft/callback"
}
```

### Check authentication after login:
```
https://www.campfixsti.com/test-oauth-status
```

Expected:
```json
{
  "authenticated": true,
  "user_id": 123,
  "session_driver": "database"
}
```

---

## What Success Looks Like

✅ Microsoft login button works  
✅ Redirects to dashboard (not homepage)  
✅ User stays logged in on page navigation  
✅ Role and permissions preserved  
✅ `/test-oauth-status` shows authenticated: true  
✅ Sessions stored in Supabase `sessions` table  

---

## Need Help?

If after following all steps it still doesn't work:

1. Check Vercel Runtime Logs for errors
2. Check Supabase logs for database errors
3. Verify `sessions` table exists: `SELECT * FROM sessions LIMIT 1;`
4. Try completely different browser/device
5. Check if firewall/antivirus is blocking session cookies

---

**Remember**: The key fix is **DELETING** `SESSION_DRIVER` from Vercel, not just changing it!
