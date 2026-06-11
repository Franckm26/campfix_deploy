# Microsoft OAuth 2.0 Authentication Flow

## Visual Flow Diagram

```
┌─────────────┐
│   User      │
│  (Browser)  │
└──────┬──────┘
       │
       │ 1. Clicks "Sign in with Microsoft"
       ▼
┌─────────────────────┐
│  Campfix App        │
│  GET /auth/microsoft │
└──────┬──────────────┘
       │
       │ 2. Redirects to Microsoft
       ▼
┌──────────────────────────┐
│  Microsoft Login Page    │
│  (login.microsoftonline) │
└──────┬───────────────────┘
       │
       │ 3. User enters credentials
       │    & grants permissions
       ▼
┌──────────────────────────┐
│  Microsoft OAuth Server  │
│  Generates auth code     │
└──────┬───────────────────┘
       │
       │ 4. Redirects back with code
       ▼
┌─────────────────────────────────┐
│  Campfix Callback               │
│  GET /auth/microsoft/callback   │
│  ?code=xxx                      │
└──────┬──────────────────────────┘
       │
       │ 5. Exchanges code for token
       ▼
┌──────────────────────────┐
│  Microsoft OAuth Server  │
│  Returns access token    │
│  & user info             │
└──────┬───────────────────┘
       │
       │ 6. User data returned
       ▼
┌─────────────────────────────────┐
│  AuthController                 │
│  handleMicrosoftCallback()      │
│                                 │
│  • Gets user data from MS       │
│  • Finds or creates user        │
│  • Links Microsoft ID           │
│  • Logs user in                 │
│  • Sets session                 │
└──────┬──────────────────────────┘
       │
       │ 7. Redirects to dashboard
       ▼
┌─────────────┐
│  Dashboard  │
│  (Logged In)│
└─────────────┘
```

## Detailed Steps

### Step 1: Initiation
**User Action**: Clicks "Sign in with Microsoft" button
**Route**: `GET /auth/microsoft`
**Controller**: `AuthController@redirectToMicrosoft()`
**Action**: Generates OAuth authorization URL and redirects

### Step 2: Microsoft Authentication
**Location**: Microsoft's login page
**User Action**: Enters Microsoft credentials (email/password)
**Additional**: May require MFA if enabled
**Microsoft Action**: Validates user identity

### Step 3: Permission Consent
**User Action**: Grants requested permissions (User.Read)
**Microsoft Action**: Generates authorization code
**Redirect**: Back to Campfix with code parameter

### Step 4: Token Exchange
**Route**: `GET /auth/microsoft/callback?code=xxx`
**Controller**: `AuthController@handleMicrosoftCallback()`
**Action**: Exchanges authorization code for access token
**Request to Microsoft**: Includes client_id, client_secret, code

### Step 5: User Data Retrieval
**Action**: Uses access token to fetch user profile
**Data Retrieved**:
- Name
- Email
- Microsoft ID
- Avatar URL

### Step 6: User Processing
**Check 1**: User exists by email?
- **Yes**: Link Microsoft ID to existing account
- **No**: Create new user account

**Check 2**: Account status
- Is archived? → Deny login
- Is locked? → Deny login
- First login? → Redirect to password setup
- All good → Proceed

**Actions**:
- Reset failed login attempts
- Set session
- Enforce single-session
- Log authentication

### Step 7: Redirection
**Based on Role**:
- Superadmin → `superadmin.dashboard`
- MIS → `/admin`
- Others → `/dashboard`

**Session**: User is now authenticated

## Data Flow

### From Microsoft (OAuth Response)
```json
{
  "id": "unique_microsoft_id",
  "displayName": "John Doe",
  "userPrincipalName": "john.doe@outlook.com",
  "mail": "john.doe@outlook.com"
}
```

### Stored in Database
```php
User {
  email: "john.doe@outlook.com",
  name: "John Doe",
  microsoft_id: "unique_microsoft_id",
  avatar: "https://graph.microsoft.com/...",
  email_verified_at: "2026-06-11 11:35:20",
  role: "student"
}
```

## Security Checks

### Pre-Login Validation
1. ✅ Email verification (automatically verified for OAuth)
2. ✅ Account archived check
3. ✅ Account locked check
4. ✅ Microsoft ID uniqueness

### Session Security
1. ✅ Single-session enforcement
2. ✅ CSRF protection
3. ✅ Rate limiting (20 req/min per IP)
4. ✅ Failed login attempt reset

### OAuth Security
1. ✅ State parameter validation (handled by Laravel Socialite)
2. ✅ Token encryption
3. ✅ HTTPS in production (required)
4. ✅ Client secret protection

## Comparison: Traditional vs OAuth Login

| Feature | Email/Password | Microsoft OAuth |
|---------|----------------|-----------------|
| Authentication | Email + Password | Microsoft Account |
| OTP Required | ✅ Yes (SMS/Email) | ❌ No |
| MFA | Via OTP | Via Microsoft |
| Account Creation | Manual | Automatic |
| Profile Picture | Manual upload | Auto-synced |
| Password Management | User manages | Microsoft manages |
| Login Speed | 2 steps (login + OTP) | 1 step (OAuth) |
| User Experience | Multiple clicks | Single sign-on |

## Error Handling

### Microsoft Errors
| Error | Cause | Solution |
|-------|-------|----------|
| `AADSTS700016` | Invalid client ID | Check MICROSOFT_CLIENT_ID |
| `AADSTS7000215` | Invalid client secret | Check MICROSOFT_CLIENT_SECRET |
| `AADSTS50011` | Redirect URI mismatch | Add URI in Azure Portal |
| `AADSTS50020` | User account not found | User needs Microsoft account |

### Application Errors
| Error | Cause | Solution |
|-------|-------|----------|
| Session expired | Timeout during OAuth | Retry login |
| Account archived | User deactivated | Contact admin |
| Account locked | Too many failed attempts | Contact MIS admin |

## Configuration Reference

### Environment Variables
```env
MICROSOFT_CLIENT_ID=        # From Azure Portal
MICROSOFT_CLIENT_SECRET=    # From Azure Portal (expires!)
MICROSOFT_REDIRECT_URI=     # Your callback URL
MICROSOFT_TENANT_ID=        # common/organizations/consumers
```

### Tenant Types
- `common` - Any Microsoft account (personal + organizational)
- `organizations` - Work/school accounts only
- `consumers` - Personal accounts only
- `{guid}` - Specific Azure AD tenant

### Scopes Requested
- `User.Read` - Basic profile information
- `email` - Email address (implicit)
- `profile` - Profile data (implicit)

## Testing Checklist

### Local Testing
- [ ] Click "Sign in with Microsoft" button
- [ ] Redirected to Microsoft login page
- [ ] Can login with Microsoft account
- [ ] Redirected back to Campfix
- [ ] User created or linked correctly
- [ ] Redirected to appropriate dashboard
- [ ] Profile picture loaded (if available)

### Edge Cases
- [ ] Existing user can link Microsoft account
- [ ] New user is created with correct defaults
- [ ] Archived account is blocked
- [ ] Locked account is blocked
- [ ] First-time login prompts password setup
- [ ] Single-session works correctly

### Error Scenarios
- [ ] Invalid credentials show error
- [ ] Cancelled OAuth returns to homepage
- [ ] Network error handled gracefully
- [ ] Invalid state parameter rejected

## Performance Considerations

### Typical Response Times
- Redirect to Microsoft: < 100ms
- Microsoft authentication: 1-3 seconds (user input)
- Token exchange: 200-500ms
- Callback processing: 100-300ms
- **Total**: ~2-4 seconds (mostly user input time)

### Optimization Tips
1. Cache user profile pictures
2. Async session storage
3. Database indexing on microsoft_id
4. CDN for static assets

## Maintenance

### Regular Tasks
- [ ] Monitor OAuth success rate
- [ ] Review error logs weekly
- [ ] Check client secret expiration
- [ ] Update redirect URIs for new environments
- [ ] Test OAuth flow monthly

### Client Secret Renewal
1. Create new secret in Azure Portal
2. Update MICROSOFT_CLIENT_SECRET in .env
3. Deploy to production
4. Monitor for issues
5. Delete old secret after confirmation

**Reminder**: Set calendar reminder 1 month before secret expires!

---

## Next Steps

After basic OAuth works:

### Optional Enhancements
1. **Additional Scopes**: Request more Microsoft Graph permissions
2. **Profile Sync**: Periodic profile picture updates
3. **Calendar Integration**: Access user's Outlook calendar
4. **Email Integration**: Send emails via Microsoft Graph
5. **Team Integration**: Microsoft Teams integration

### Advanced Security
1. **Admin Consent**: Pre-approve permissions org-wide
2. **Conditional Access**: Enforce Microsoft policies
3. **Token Refresh**: Long-lived sessions
4. **Logout Propagation**: Sign out from Microsoft too

---

**Documentation Version**: 1.0
**Last Updated**: June 11, 2026
