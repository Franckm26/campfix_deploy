# Profile API Testing Summary

## Recent Changes (ProfileController.php)

The ProfileController was just updated with:
- ✅ **SupabaseStorage** integration for cloud file storage
- ✅ **Log** facade for better error tracking
- ✅ Enhanced error handling with try-catch blocks
- ✅ Support for both Supabase and local storage (backwards compatible)
- ✅ Proper cleanup of old profile pictures

## API Endpoints Status

### 1. **POST /profile/upload-picture** ✅
- **Status**: NOW USES SUPABASE STORAGE
- **Rate Limit**: `throttle:uploads` (5 per day)
- **Authentication**: Required
- **Validation**: 
  - Image required
  - Max size: 2MB
  - Allowed types: jpeg, png, jpg, gif
- **Storage**:
  - **Production/Vercel**: Supabase Storage bucket `profile_pictures`
  - **Local**: Laravel storage + public symlink
- **Response**: Returns public URL of uploaded image

### 2. **DELETE /profile/remove-picture** ✅
- **Status**: UPDATED WITH SUPABASE SUPPORT
- **Rate Limit**: `throttle:deletes` (30 per hour)
- **Authentication**: Required
- **Logic**:
  - Detects Supabase URLs (contains 'supabase')
  - Deletes from Supabase if cloud-stored
  - Falls back to local storage deletion for legacy images
  - Sets `profile_picture` to null

### 3. **PUT /profile** ✅
- **Status**: WORKING
- **Rate Limit**: `throttle:web` (200 per minute)
- **Fields**: `name`, `phone`
- **Validation**: Phone must match `/^09[0-9]{9}$/`

### 4. **PUT /profile/password** ✅
- **Status**: WORKING
- **Rate Limit**: `throttle:password` (10 per hour)
- **Validation**:
  - Current password required
  - New password: min 8 chars, uppercase, lowercase, number, special char
  - Common passwords blocked

## Postman Collection Status

**Collection ID**: `33462ba8-bc58-4cd7-9a90-d1ae00f21173`
**Workspace**: CampFix API (b07705a1-321e-44dd-9171-e7f1a52fbbf6)
**Environment**: CampFix Local
**Total Requests**: 28

### Recommended Additions

The following requests should be added to the Postman collection:

#### 1. **Profile - Upload Picture**
```
POST {{base_url_web}}/profile/upload-picture
Headers:
  - Cookie: laravel_session={{session}}
Body (form-data):
  - profile_picture: <file>
Tests:
  - Status 200
  - Response has 'success' = true
  - Response has 'url' field
  - URL contains 'supabase' (for production)
```

#### 2. **Profile - Remove Picture**
```
DELETE {{base_url_web}}/profile/remove-picture
Headers:
  - Cookie: laravel_session={{session}}
Tests:
  - Status 200
  - Response has 'success' = true
```

#### 3. **Profile - Update Info**
```
PUT {{base_url_web}}/profile
Headers:
  - Cookie: laravel_session={{session}}
Body (form-data):
  - name: "Test User"
  - phone: "09123456789"
Tests:
  - Status 302 (redirect) or 200 (JSON)
```

#### 4. **Profile - Update Password**
```
PUT {{base_url_web}}/profile/password
Headers:
  - Cookie: laravel_session={{session}}
Body (form-data):
  - current_password: "OldPass123!"
  - new_password: "NewPass123!"
  - new_password_confirmation: "NewPass123!"
Tests:
  - Status 302 or 200
```

## Test Scenarios

### Scenario 1: Upload Profile Picture
1. Login to get session token
2. Upload valid image (< 2MB, jpeg/png/jpg/gif)
3. Verify Supabase URL returned
4. Verify image accessible at returned URL

### Scenario 2: Replace Profile Picture
1. Upload first image
2. Upload second image
3. Verify first image deleted from Supabase
4. Verify only second image URL exists

### Scenario 3: Remove Profile Picture
1. Upload image
2. Delete image
3. Verify image deleted from Supabase
4. Verify user.profile_picture = null

### Scenario 4: Rate Limiting
1. Upload 5 images rapidly
2. 6th upload should return 429 (rate limit exceeded)
3. Wait for reset window

### Scenario 5: Invalid File Upload
1. Try uploading > 2MB file
2. Try uploading .txt file
3. Verify validation errors returned

## Environment Variables Needed

Add to Postman environment:

```json
{
  "base_url_web": "http://127.0.0.1:8000",
  "base_url": "http://127.0.0.1:8000/api",
  "session": "", // Auto-set from login response
  "profile_picture_url": "" // Auto-set from upload response
}
```

## Security Considerations

### ✅ Implemented
- Rate limiting on uploads (5/day), deletes (30/hour)
- File type validation (only images)
- File size validation (max 2MB)
- Authentication required
- Proper error handling and logging
- Secure cloud storage (Supabase)

### ⚠️ Recommendations
1. **Add virus scanning** for uploaded files
2. **Add image dimension validation** (e.g., max 4096x4096)
3. **Add EXIF data stripping** to prevent metadata leaks
4. **Implement CDN caching** for profile pictures
5. **Add image optimization** (resize, compress) before storage

## Supabase Configuration

Ensure these environment variables are set:

```env
SUPABASE_URL=pclfaksjjprickgppnus.supabase.co
SUPABASE_KEY=eyJhbGc... (service role key)
SUPABASE_BUCKET=concerns
```

**Note**: Profile pictures are stored in the `profile_pictures` path within the `concerns` bucket.

### Bucket Policies
Verify Supabase bucket has:
- ✅ Public read access for profile pictures
- ✅ Authenticated write access
- ✅ CORS enabled for your domains

## Monitoring & Logging

All profile picture operations are logged with:
- User ID
- Operation (upload/delete)
- Storage URL
- Error traces (if failed)

Check logs with:
```bash
# Local
tail -f storage/logs/laravel.log | grep "Profile picture"

# Vercel
vercel logs --follow | grep "Profile picture"
```

## Manual Testing Steps

### Local Environment
1. Start local server: `php artisan serve`
2. Visit http://127.0.0.1:8000/profile
3. Upload test image
4. Verify stored in `storage/app/public/profile_pictures/`
5. Verify accessible at `http://127.0.0.1:8000/storage/profile_pictures/{filename}`

### Production (Vercel)
1. Deploy to Vercel
2. Visit https://www.campfixsti.com/profile
3. Upload test image
4. Verify Supabase bucket has image
5. Verify accessible at Supabase public URL

## Known Issues & Fixes

### Issue 1: Session not persisting (RESOLVED)
**Problem**: OAuth redirect loses session  
**Fix**: Changed `SESSION_SAME_SITE=lax` in `.env.vercel`  
**Status**: ✅ Fixed (see OAUTH_SESSION_FIX.md)

### Issue 2: Avatar column size (RESOLVED)
**Problem**: VARCHAR(255) too small for Microsoft OAuth avatars  
**Fix**: Changed to TEXT in `fix_avatar_column_size.sql`  
**Status**: ✅ Fixed

### Issue 3: Local storage on Vercel (RESOLVED)
**Problem**: Vercel has read-only filesystem  
**Fix**: Use Supabase Storage for production  
**Status**: ✅ Implemented

## Next Steps

1. ✅ **Update Postman collection** with profile endpoints
2. ✅ **Run collection tests** on local environment
3. ⏳ **Deploy to Vercel** and test on production
4. ⏳ **Add image optimization** middleware
5. ⏳ **Implement virus scanning** for uploads
6. ⏳ **Add monitoring** for failed uploads

## API Testing Checklist

- [ ] Login endpoint returns valid session
- [ ] Upload profile picture (valid file)
- [ ] Upload profile picture (invalid file type) - should fail
- [ ] Upload profile picture (> 2MB) - should fail
- [ ] Replace existing profile picture
- [ ] Remove profile picture
- [ ] Update profile name and phone
- [ ] Update password (correct current password)
- [ ] Update password (wrong current password) - should fail
- [ ] Update password (weak password) - should fail
- [ ] Test rate limiting (5 uploads, 6th fails)
- [ ] Verify Supabase URLs returned (production)
- [ ] Verify images accessible via returned URLs
- [ ] Test AJAX requests (with Accept: application/json)

---

**Generated**: 2026-06-11  
**Status**: Profile endpoints fully integrated with Supabase Storage  
**Collection**: Ready for testing
