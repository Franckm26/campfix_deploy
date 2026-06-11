# 🔐 Microsoft OAuth 2.0 - Developer Guide

## Quick Links

📖 **Documentation Files**:
- [`MICROSOFT_OAUTH_QUICK_START.md`](MICROSOFT_OAUTH_QUICK_START.md) - Get started in 5 minutes
- [`MICROSOFT_OAUTH_SETUP.md`](MICROSOFT_OAUTH_SETUP.md) - Complete setup guide
- [`MICROSOFT_OAUTH_FLOW.md`](MICROSOFT_OAUTH_FLOW.md) - OAuth flow diagram
- [`MICROSOFT_OAUTH_IMPLEMENTATION_SUMMARY.md`](MICROSOFT_OAUTH_IMPLEMENTATION_SUMMARY.md) - Technical summary

---

## For New Developers

### First Time Setup

1. **Install dependencies** (if not already done):
   ```bash
   composer install
   ```

2. **Copy environment file**:
   ```bash
   copy .env.example .env
   ```

3. **Get Microsoft OAuth credentials**:
   - See [`MICROSOFT_OAUTH_QUICK_START.md`](MICROSOFT_OAUTH_QUICK_START.md)
   - Or ask team lead for credentials

4. **Update .env**:
   ```env
   MICROSOFT_CLIENT_ID=your_client_id
   MICROSOFT_CLIENT_SECRET=your_client_secret
   ```

5. **Run migrations**:
   ```bash
   php artisan migrate
   ```

6. **Clear cache**:
   ```bash
   php artisan config:clear
   ```

7. **Test it**:
   - Visit http://localhost
   - Click "Sign in with Microsoft"

---

## Code Structure

### Controller: `AuthController.php`

```php
// OAuth Methods
redirectToMicrosoft()         // Initiates OAuth flow
handleMicrosoftCallback()     // Processes OAuth response
```

### Routes: `web.php`

```php
GET  /auth/microsoft          // auth.microsoft
GET  /auth/microsoft/callback // auth.microsoft.callback
```

### Database: `users` table

```sql
microsoft_id VARCHAR(255) UNIQUE NULL
avatar VARCHAR(255) NULL
```

### Config: `config/services.php`

```php
'microsoft' => [
    'client_id' => env('MICROSOFT_CLIENT_ID'),
    'client_secret' => env('MICROSOFT_CLIENT_SECRET'),
    'redirect' => env('MICROSOFT_REDIRECT_URI'),
    'tenant' => env('MICROSOFT_TENANT_ID', 'common'),
]
```

---

## Development Workflow

### Making Changes to OAuth Flow

1. **Modify controller method**:
   ```php
   // app/Http/Controllers/AuthController.php
   public function handleMicrosoftCallback() {
       // Your changes here
   }
   ```

2. **Test locally**:
   ```bash
   php artisan config:clear
   # Visit /auth/microsoft
   ```

3. **Check logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Adding New OAuth Features

Example: Store additional user data

```php
// In handleMicrosoftCallback()
$user->update([
    'microsoft_id' => $microsoftUser->getId(),
    'avatar' => $microsoftUser->getAvatar(),
    // Add more fields:
    'job_title' => $microsoftUser->user['jobTitle'] ?? null,
    'department' => $microsoftUser->user['department'] ?? null,
]);
```

Don't forget to:
1. Add fields to database migration
2. Add fields to User model $fillable array
3. Update documentation

---

## Testing

### Manual Testing

```bash
# 1. Clear all caches
php artisan config:clear
php artisan cache:clear

# 2. Test OAuth flow
# - Visit homepage
# - Click "Sign in with Microsoft"
# - Complete authentication
# - Verify redirect to dashboard

# 3. Check database
# - User should have microsoft_id
# - Avatar URL should be stored
```

### Testing Different Scenarios

| Scenario | Expected Result |
|----------|----------------|
| New Microsoft user | Creates new account with role=student |
| Existing user (same email) | Links Microsoft ID to account |
| Archived account | Shows error, blocks login |
| Locked account | Shows error, blocks login |
| Cancel OAuth | Redirects to homepage |

### Debug Mode

Enable detailed OAuth debugging:

```php
// In AuthController@handleMicrosoftCallback()
\Log::info('Microsoft OAuth Data:', [
    'user' => $microsoftUser->user,
    'id' => $microsoftUser->getId(),
    'email' => $microsoftUser->getEmail(),
    'name' => $microsoftUser->getName(),
]);
```

---

## Common Tasks

### 1. Change Default Role for New OAuth Users

```php
// In AuthController@handleMicrosoftCallback()
$user = User::create([
    'name' => $microsoftUser->getName(),
    'email' => strtolower($microsoftUser->getEmail()),
    'role' => 'faculty', // Change from 'student' to 'faculty'
    // ...
]);
```

### 2. Request Additional Microsoft Permissions

```php
// In AuthController@redirectToMicrosoft()
return Socialite::driver('microsoft')
    ->scopes(['User.Read', 'Calendars.Read', 'Mail.Send'])
    ->redirect();
```

Also update Azure Portal → API permissions

### 3. Customize Redirect After Login

```php
// In AuthController@handleMicrosoftCallback()
// Add custom logic:
if ($user->role === 'faculty') {
    return redirect('/events');
}
return redirect('/dashboard');
```

### 4. Add OAuth Login to Other Pages

```html
<!-- In any Blade view -->
<a href="{{ route('auth.microsoft') }}" class="btn btn-primary">
    Sign in with Microsoft
</a>
```

### 5. Check if User Logged in via OAuth

```php
if (auth()->user()->microsoft_id) {
    // User logged in with Microsoft
}
```

---

## Environment Configuration

### Development (.env.local)

```env
APP_URL=http://localhost
MICROSOFT_REDIRECT_URI=http://localhost/auth/microsoft/callback
```

### Staging (.env.staging)

```env
APP_URL=https://staging.campfixsti.com
MICROSOFT_REDIRECT_URI=https://staging.campfixsti.com/auth/microsoft/callback
```

### Production (.env.production)

```env
APP_URL=https://www.campfixsti.com
MICROSOFT_REDIRECT_URI=https://www.campfixsti.com/auth/microsoft/callback
```

**Important**: Add all redirect URIs to Azure Portal!

---

## Troubleshooting

### Problem: "Invalid client secret"

```bash
# Solution:
1. Check MICROSOFT_CLIENT_SECRET in .env
2. Ensure no extra spaces/quotes
3. Generate new secret if expired (Azure Portal)
```

### Problem: "Redirect URI mismatch"

```bash
# Solution:
1. Check exact URL in error message
2. Add to Azure Portal → Authentication
3. Match protocol (http/https)
4. Check for trailing slashes
```

### Problem: OAuth button not showing

```bash
# Solution:
php artisan config:clear
php artisan view:clear
# Clear browser cache
# Check if JavaScript is enabled
```

### Problem: Session expired during OAuth

```bash
# Solution:
# Increase session lifetime in config/session.php
'lifetime' => 120, // minutes
```

---

## Security Checklist

- [ ] Never commit .env file
- [ ] Use HTTPS in production
- [ ] Set strong client secret
- [ ] Limit redirect URIs in Azure
- [ ] Enable rate limiting
- [ ] Review OAuth scopes
- [ ] Monitor failed attempts
- [ ] Rotate secrets regularly
- [ ] Use environment variables
- [ ] Validate user input

---

## Performance Tips

1. **Cache user profiles**:
   ```php
   Cache::remember("user.{$user->id}.avatar", 3600, function() use ($user) {
       return $user->avatar;
   });
   ```

2. **Async avatar downloads**:
   ```php
   dispatch(new DownloadAvatarJob($user));
   ```

3. **Index microsoft_id column**:
   ```php
   // Already indexed (unique constraint)
   ```

---

## Git Workflow

### Committing Changes

```bash
# Never commit these:
git add .env  # ❌ NO!

# Always commit these:
git add .env.example  # ✅ YES
git add app/Http/Controllers/AuthController.php
git add config/services.php
git add database/migrations/
```

### .gitignore (already set)

```
.env
.env.local
.env.production
```

---

## API Usage (Optional)

If you want to support OAuth in API:

```php
// In routes/api.php
Route::get('/auth/microsoft', [AuthController::class, 'apiRedirectToMicrosoft']);
Route::get('/auth/microsoft/callback', [AuthController::class, 'apiHandleMicrosoftCallback']);

// Return JWT token instead of session
return response()->json([
    'token' => JWTAuth::fromUser($user),
    'user' => $user
]);
```

---

## Monitoring

### Check OAuth Success Rate

```sql
SELECT 
    COUNT(*) as total_users,
    COUNT(microsoft_id) as oauth_users,
    (COUNT(microsoft_id) * 100.0 / COUNT(*)) as oauth_percentage
FROM users;
```

### Recent OAuth Logins

```sql
SELECT name, email, created_at
FROM users
WHERE microsoft_id IS NOT NULL
ORDER BY created_at DESC
LIMIT 10;
```

---

## Additional Resources

### Official Documentation
- Laravel Socialite: https://laravel.com/docs/socialite
- Microsoft Identity: https://docs.microsoft.com/en-us/azure/active-directory/develop/
- OAuth 2.0 Spec: https://oauth.net/2/

### Helpful Tools
- Azure Portal: https://portal.azure.com
- JWT Decoder: https://jwt.io
- Postman: For API testing

### Community
- Laravel Discord: https://discord.gg/laravel
- Stack Overflow: Tag [laravel-socialite]

---

## FAQ

**Q: Can users login with both email/password and Microsoft?**  
A: Yes! They can use either method after linking accounts.

**Q: What if client secret expires?**  
A: Create new one in Azure Portal, update .env, redeploy.

**Q: Can I use other OAuth providers?**  
A: Yes! Socialite supports Google, GitHub, Facebook, etc.

**Q: Is this production-ready?**  
A: Yes! Just configure Azure credentials and test thoroughly.

**Q: What about mobile apps?**  
A: Use OAuth 2.0 with PKCE flow, return JWT tokens.

---

## Contact

For questions about this implementation:
- Check documentation files first
- Review Laravel logs
- Check Azure Portal logs
- Ask team lead

---

**Last Updated**: June 11, 2026  
**Maintained By**: Development Team  
**Version**: 1.0
