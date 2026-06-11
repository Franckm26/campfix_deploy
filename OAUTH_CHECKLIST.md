# ✅ Microsoft OAuth 2.0 Setup Checklist

Use this checklist to ensure your Microsoft OAuth implementation is complete and working correctly.

---

## 📋 Initial Setup

### Azure Portal Configuration
- [ ] Created Azure account at https://portal.azure.com
- [ ] Navigated to Azure Active Directory → App registrations
- [ ] Created new app registration named "Campfix"
- [ ] Selected correct account types (recommended: "Accounts in any organizational directory and personal Microsoft accounts")
- [ ] Copied Application (client) ID
- [ ] Copied Directory (tenant) ID (optional)
- [ ] Created client secret under "Certificates & secrets"
- [ ] Copied client secret VALUE immediately
- [ ] Added redirect URI: `http://localhost/auth/microsoft/callback`
- [ ] Enabled ID tokens and Access tokens
- [ ] Added API permissions: User.Read (default)
- [ ] Granted admin consent (if required by organization)

### Local Development Configuration
- [ ] Updated `.env` with MICROSOFT_CLIENT_ID
- [ ] Updated `.env` with MICROSOFT_CLIENT_SECRET
- [ ] Verified MICROSOFT_REDIRECT_URI matches Azure Portal
- [ ] Set MICROSOFT_TENANT_ID (default: common)
- [ ] Ran `composer install` to install dependencies
- [ ] Ran `php artisan migrate` to add database columns
- [ ] Ran `php artisan config:clear` to clear config cache
- [ ] Ran `php artisan cache:clear` to clear application cache

---

## 🧪 Testing

### Functionality Tests
- [ ] Homepage loads correctly
- [ ] Login modal appears when clicking "Login"
- [ ] "Sign in with Microsoft" button is visible
- [ ] Clicking button redirects to Microsoft login page
- [ ] Can login with Microsoft account credentials
- [ ] Can complete MFA if enabled on Microsoft account
- [ ] Redirected back to Campfix after authentication
- [ ] User is logged in successfully
- [ ] Redirected to correct dashboard based on role
- [ ] User data stored correctly in database
- [ ] Microsoft ID is saved
- [ ] Avatar URL is saved (if available)
- [ ] Email is verified automatically

### Edge Case Tests
- [ ] **New User**: Creates account with default role
- [ ] **Existing User**: Links Microsoft ID to existing account
- [ ] **Archived Account**: Shows error and blocks login
- [ ] **Locked Account**: Shows error and blocks login
- [ ] **Cancelled OAuth**: Redirects to homepage gracefully
- [ ] **Invalid Credentials**: Shows Microsoft error
- [ ] **First Login**: Prompts for password setup (if force_password_change=true)
- [ ] **Second Login**: Skips OTP (direct login)

### Error Handling Tests
- [ ] Invalid client ID shows appropriate error
- [ ] Invalid client secret shows appropriate error
- [ ] Redirect URI mismatch shows appropriate error
- [ ] Network timeout handled gracefully
- [ ] Database connection error handled
- [ ] Session timeout handled

---

## 🔒 Security Verification

### Authentication Security
- [ ] CSRF protection enabled on routes
- [ ] Rate limiting active (20 req/min per IP)
- [ ] Session validation working
- [ ] Single-session enforcement working
- [ ] Failed login attempts reset on OAuth success
- [ ] Account lockout enforced
- [ ] Archive status checked
- [ ] OAuth state parameter validated (automatic with Socialite)

### Configuration Security
- [ ] `.env` file not committed to Git
- [ ] `.gitignore` includes `.env*` files
- [ ] Client secret is kept private
- [ ] HTTPS enabled in production
- [ ] Secure cookie settings configured
- [ ] CORS settings reviewed

### Azure Security
- [ ] Redirect URIs limited to actual domains
- [ ] Client secret expiration noted (set reminder)
- [ ] API permissions reviewed and minimized
- [ ] Admin consent granted (if needed)
- [ ] Audit logs enabled in Azure Portal

---

## 🚀 Production Deployment

### Pre-Deployment
- [ ] Tested thoroughly in development
- [ ] Tested in staging environment
- [ ] Updated `.env.production` with production credentials
- [ ] Set `APP_ENV=production` in production `.env`
- [ ] Set `APP_DEBUG=false` in production `.env`
- [ ] Updated `APP_URL` to production domain
- [ ] Updated `MICROSOFT_REDIRECT_URI` to production URL
- [ ] Added production redirect URI in Azure Portal
- [ ] Verified HTTPS is enabled
- [ ] Verified SSL certificate is valid

### Deployment
- [ ] Deployed code to production server
- [ ] Ran migrations on production database
- [ ] Cleared production caches (`config:clear`, `cache:clear`)
- [ ] Verified environment variables are set
- [ ] Checked file permissions
- [ ] Verified storage directory is writable

### Post-Deployment
- [ ] Tested OAuth flow on production
- [ ] Verified new user creation works
- [ ] Verified existing user linking works
- [ ] Checked error logging
- [ ] Monitored for any errors
- [ ] Verified email notifications work
- [ ] Tested on multiple devices/browsers
- [ ] Performance tested under load

---

## 📊 Monitoring

### Regular Checks
- [ ] Monitor OAuth success rate weekly
- [ ] Review error logs in Laravel
- [ ] Review sign-in logs in Azure Portal
- [ ] Check client secret expiration date
- [ ] Monitor user feedback
- [ ] Track OAuth vs traditional login usage

### Metrics to Track
- [ ] Total OAuth logins per day
- [ ] OAuth failure rate
- [ ] Average login time
- [ ] New users via OAuth
- [ ] Existing users linked to OAuth
- [ ] Most common errors

---

## 📝 Documentation

### Internal Documentation
- [ ] Team members know where to find Azure credentials
- [ ] Setup instructions documented (this checklist ✅)
- [ ] Troubleshooting guide available
- [ ] Runbook for common issues created
- [ ] Client secret renewal process documented

### User Documentation
- [ ] Help article on "How to login with Microsoft"
- [ ] FAQ section updated
- [ ] Support team trained on OAuth issues
- [ ] Known issues documented

---

## 🔄 Maintenance

### Monthly Tasks
- [ ] Review Azure Portal sign-in logs
- [ ] Check for any deprecated APIs
- [ ] Test OAuth flow
- [ ] Review error rates
- [ ] Update dependencies if needed

### Quarterly Tasks
- [ ] Review and update OAuth scopes
- [ ] Security audit of implementation
- [ ] Performance review
- [ ] Update documentation
- [ ] Review user feedback

### Yearly Tasks
- [ ] Rotate client secret (or before expiration)
- [ ] Review Azure Portal configuration
- [ ] Update redirect URIs if needed
- [ ] Comprehensive security review

---

## 🎯 Optional Enhancements

### Future Improvements
- [ ] Add Google OAuth
- [ ] Add GitHub OAuth
- [ ] Add Facebook OAuth
- [ ] Sync Microsoft profile regularly
- [ ] Integrate Microsoft Calendar
- [ ] Integrate Microsoft Teams
- [ ] Add email via Microsoft Graph
- [ ] Implement SSO for related apps
- [ ] Add "Link Microsoft Account" in settings

### Advanced Features
- [ ] Admin consent workflow
- [ ] Conditional access policies
- [ ] Multi-tenant support
- [ ] Custom claims in tokens
- [ ] Token refresh implementation
- [ ] Logout from Microsoft on app logout

---

## 🐛 Known Issues

Document any known issues here:

- [ ] None currently

---

## 📞 Support Contacts

- **Azure Portal**: https://portal.azure.com
- **Microsoft Support**: [Link to support]
- **Team Lead**: [Name/Email]
- **Documentation**: See `MICROSOFT_OAUTH_*.md` files

---

## ✨ Completion

### Sign-off
Once all items are checked:

- **Completed by**: ___________________
- **Date**: ___________________
- **Verified by**: ___________________
- **Date**: ___________________

### Status

Current implementation status:

```
✅ Code Implementation: Complete
✅ Database Migration: Complete
✅ Documentation: Complete
⚠️  Azure Configuration: Pending (requires credentials)
⚠️  Testing: Pending (after Azure setup)
⚠️  Production Deployment: Pending
```

---

## 📄 Related Documentation

- 📖 [`MICROSOFT_OAUTH_QUICK_START.md`](MICROSOFT_OAUTH_QUICK_START.md) - Quick setup guide
- 📖 [`MICROSOFT_OAUTH_SETUP.md`](MICROSOFT_OAUTH_SETUP.md) - Detailed setup instructions
- 📖 [`MICROSOFT_OAUTH_FLOW.md`](MICROSOFT_OAUTH_FLOW.md) - OAuth flow diagram
- 📖 [`README_OAUTH.md`](README_OAUTH.md) - Developer guide

---

**Checklist Version**: 1.0  
**Last Updated**: June 11, 2026  
**Next Review**: When setting up Azure credentials

---

## 🎉 Success Criteria

Your OAuth implementation is successful when:

✅ Users can login with Microsoft account  
✅ New accounts are created automatically  
✅ Existing accounts link seamlessly  
✅ No errors in production logs  
✅ Security checks all pass  
✅ Performance is acceptable  
✅ Users are satisfied  

**Good luck with your implementation!** 🚀
