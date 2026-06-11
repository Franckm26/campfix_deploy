# Microsoft OAuth 2.0 Authentication Setup Guide

## Overview
Microsoft OAuth 2.0 authentication has been successfully integrated into the Campfix application. Users can now sign in using their Microsoft accounts in addition to traditional email/password authentication.

## Features Implemented

✅ **Microsoft OAuth 2.0 Integration**
- Sign in with Microsoft button on login page
- Automatic user creation for new Microsoft accounts
- Automatic linking of existing accounts with Microsoft ID
- Seamless authentication flow with OTP bypass for OAuth users
- Single-session enforcement for OAuth logins
- Profile picture sync from Microsoft account

✅ **Security Features**
- Account lockout check for OAuth users
- Archived account prevention
- Session management
- JWT token support for API authentication
- Rate limiting on OAuth routes

## Installation Complete

The following components have been installed and configured:

1. **Composer Packages**
   - `laravel/socialite` v5.27.0
   - `socialiteproviders/microsoft` v4.9.1

2. **Database Changes**
   - Added `microsoft_id` column to users table (unique, nullable)
   - Added `avatar` column to users table (nullable)

3. **Configuration Files**
   - `.env` - Microsoft OAuth environment variables
   - `config/services.php` - Microsoft OAuth service configuration

4. **Code Changes**
   - `AuthController.php` - OAuth methods added
   - `AppServiceProvider.php` - Microsoft Socialite provider configured
   - `User.php` model - Microsoft OAuth fields added to fillable
   - `routes/web.php` - OAuth routes added
   - `home.blade.php` - Sign in with Microsoft button added

## Setup Instructions

### Step 1: Register Your Application in Azure Portal

1. Go to [Azure Portal](https://portal.azure.com)
2. Navigate to **Azure Active Directory** → **App registrations**
3. Click **New registration**
4. Fill in the following:
   - **Name**: Campfix (or your preferred app name)
   - **Supported account types**: 
     - Choose "Accounts in any organizational directory and personal Microsoft accounts" for maximum compatibility
     - Or choose specific tenant option if you want to restrict access
   - **Redirect URI**: 
     - Platform: **Web**
     - URL: `http://localhost/auth/microsoft/callback` (for local development)

5. Click **Register**

### Step 2: Get Your Credentials

After registration, you'll see your application overview page:

1. **Copy the Application (client) ID**
   - This is your `MICROSOFT_CLIENT_ID`

2. **Copy the Directory (tenant) ID**
   - This is your `MICROSOFT_TENANT_ID` (optional, defaults to 'common')

3. **Create a Client Secret**
   - Go to **Certificates & secrets** → **Client secrets**
   - Click **New client secret**
   - Add a description (e.g., "Campfix Production")
   - Choose expiration period (recommended: 24 months)
   - Click **Add**
   - **Copy the secret VALUE immediately** (you won't see it again!)
   - This is your `MICROSOFT_CLIENT_SECRET`

### Step 3: Configure Redirect URIs

In Azure Portal, go to your app → **Authentication**:

1. Add redirect URIs for all environments:
   ```
   Local:       http://localhost/auth/microsoft/callback
   Production:  https://yourdomain.com/auth/microsoft/callback
   ```

2. Enable **ID tokens** and **Access tokens** under "Implicit grant and hybrid flows"

3. Save changes

### Step 4: Update Environment Variables

Edit your `.env` file and add the credentials:

```env
# Microsoft OAuth Configuration
MICROSOFT_CLIENT_ID=your_application_client_id_here
MICROSOFT_CLIENT_SECRET=your_client_secret_value_here
MICROSOFT_REDIRECT_URI="${APP_URL}/auth/microsoft/callback"
MICROSOFT_TENANT_ID=common
```

**Important**: Replace the placeholder values with your actual credentials from Azure Portal.

### Tenant ID Options:
- `common` - Allows both organizational and personal Microsoft accounts (recommended)
- `organizations` - Only organizational accounts (work/school)
- `consumers` - Only personal Microsoft accounts
- `{tenant-id}` - Specific Azure AD tenant only

### Step 5: Configure API Permissions (Optional but Recommended)

In Azure Portal → Your App → **API permissions**:

1. Click **Add a permission**
2. Select **Microsoft Graph**
3. Select **Delegated permissions**
4. Add these permissions:
   - `User.Read` (already added by default)
   - `email` (optional, for email access)
   - `profile` (optional, for profile access)
5. Click **Add permissions**
6. If required by your organization, click **Grant admin consent**

### Step 6: Test the Integration

1. Clear your application cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Visit your application homepage
3. Click "Login" button
4. Click "Sign in with Microsoft"
5. You should be redirected to Microsoft login page
6. After successful authentication, you'll be redirected back to your dashboard

## Usage

### For End Users

1. **New Users with Microsoft Account**
   - Click "Sign in with Microsoft" on login page
   - Authenticate with Microsoft
   - Account will be automatically created
   - Default role: Student

2. **Existing Users**
   - First time: Login with email/password and OTP, then future logins can use Microsoft OAuth
   - Or: Click "Sign in with Microsoft" and your existing account will be linked automatically

3. **Benefits of Microsoft OAuth**
   - No OTP required
   - Single sign-on (SSO)
   - Automatic profile picture sync
   - More secure authentication

### Security Features

- **Account Protection**: Archived and locked accounts cannot login via OAuth
- **Session Management**: Single-session enforcement prevents multiple concurrent logins
- **Role-Based Access**: OAuth users receive appropriate role-based redirects
- **Rate Limiting**: OAuth routes are protected by rate limiting

## API Endpoints

### OAuth Routes (Web)
```
GET  /auth/microsoft          → Redirect to Microsoft login
GET  /auth/microsoft/callback → Handle OAuth callback
```

Both routes are protected by the `auth` rate limiter (20 requests/minute per IP).

## Troubleshooting

### Error: "Invalid client secret"
- **Solution**: Verify your `MICROSOFT_CLIENT_SECRET` in `.env` is correct
- The secret is only shown once when created, if lost, create a new one

### Error: "Redirect URI mismatch"
- **Solution**: Ensure the redirect URI in Azure Portal exactly matches your app URL
- Check for `http` vs `https` and trailing slashes

### Error: "AADSTS700016: Application not found"
- **Solution**: Verify your `MICROSOFT_CLIENT_ID` is correct
- Check you're using the Application (client) ID, not Object ID

### Error: "User not found" or "Session expired"
- **Solution**: Clear application cache with `php artisan config:clear`

### OAuth button not showing
- **Solution**: Clear browser cache and check that JavaScript is enabled

### Error: "The provided authorization grant is invalid"
- **Solution**: Usually a timing issue, try logging in again
- Check that your server time is synchronized

## Production Deployment

### Checklist:

1. ✅ Update `MICROSOFT_REDIRECT_URI` to your production URL
2. ✅ Add production redirect URI in Azure Portal
3. ✅ Ensure `APP_ENV=production` in production `.env`
4. ✅ Use HTTPS for production (Microsoft OAuth requires HTTPS for production)
5. ✅ Test OAuth flow on production environment
6. ✅ Set appropriate secret expiration reminder
7. ✅ Document client secret expiration date for renewal

### Environment-Specific Configs:

**Local Development:**
```env
APP_URL=http://localhost
MICROSOFT_REDIRECT_URI=http://localhost/auth/microsoft/callback
```

**Production (Vercel/Render/etc):**
```env
APP_URL=https://www.campfixsti.com
MICROSOFT_REDIRECT_URI=https://www.campfixsti.com/auth/microsoft/callback
```

## File Changes Summary

### New Files:
- `database/migrations/2026_06_11_113520_add_microsoft_oauth_to_users_table.php`
- `MICROSOFT_OAUTH_SETUP.md` (this file)

### Modified Files:
- `.env` - Added Microsoft OAuth variables
- `.env.example` - Added Microsoft OAuth variables template
- `config/services.php` - Added Microsoft service configuration
- `app/Providers/AppServiceProvider.php` - Added Microsoft Socialite provider
- `app/Http/Controllers/AuthController.php` - Added OAuth methods
- `app/Models/User.php` - Added microsoft_id and avatar to fillable
- `routes/web.php` - Added OAuth routes
- `resources/views/home.blade.php` - Added Microsoft sign-in button

## Cost Information

✅ **Completely FREE** for the use case described:

- **Azure AD Free Tier**: Includes 50,000 monthly active users
- **Microsoft Identity Platform**: Free OAuth 2.0 authentication
- **No credit card required** for basic authentication
- **Development & Production**: Both free with standard quotas

The free tier is more than sufficient for educational institutions and most applications.

## Additional Resources

- [Microsoft Identity Platform Documentation](https://docs.microsoft.com/en-us/azure/active-directory/develop/)
- [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
- [Microsoft Graph API](https://docs.microsoft.com/en-us/graph/)
- [Azure Portal](https://portal.azure.com)

## Support

For issues or questions:
1. Check troubleshooting section above
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check Azure Portal app registration settings
4. Verify all environment variables are set correctly

## Security Notes

- **Never commit** `.env` file to version control
- **Rotate client secrets** before they expire (set reminder)
- **Use HTTPS** in production
- **Limit redirect URIs** to only your actual domains
- **Review OAuth permissions** regularly
- **Monitor authentication logs** in Azure Portal

---

**Setup completed successfully!** 🎉

Your Campfix application now supports Microsoft OAuth 2.0 authentication.
