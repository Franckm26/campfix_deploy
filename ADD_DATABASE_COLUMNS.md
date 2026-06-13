# 🗄️ Add Microsoft OAuth Columns to Database

## ✅ Great News!

OAuth authentication is **working**! The error shows Microsoft successfully authenticated you, but the database is missing the `microsoft_id` and `avatar` columns.

---

## 🔧 Fix: Add Columns to Supabase Database

### Step 1: Go to Supabase SQL Editor

1. Go to: https://supabase.com/dashboard
2. Select your project: **pclfaksjjprickgppnus**
3. Click **SQL Editor** (left sidebar - looks like `</>`)
4. Click **New query**

### Step 2: Run This SQL

Copy and paste this SQL into the editor:

```sql
-- Add Microsoft OAuth columns to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS microsoft_id VARCHAR(255) NULL,
ADD COLUMN IF NOT EXISTS avatar VARCHAR(255) NULL;

-- Add unique constraint to microsoft_id
ALTER TABLE users 
ADD CONSTRAINT users_microsoft_id_unique UNIQUE (microsoft_id);

-- Verify the columns were added
SELECT column_name, data_type, is_nullable 
FROM information_schema.columns 
WHERE table_name = 'users' 
AND column_name IN ('microsoft_id', 'avatar');
```

### Step 3: Execute the Query

1. Click the **"Run"** button (or press `Ctrl+Enter`)
2. You should see a success message
3. At the bottom, you should see the verification results showing the two new columns

---

## 🧪 Test OAuth Again

After running the SQL:

1. Go to: https://www.campfixsti.com/
2. Clear cookies or use incognito
3. Click **"Sign in with Microsoft"**
4. Complete authentication
5. You should be redirected to **dashboard** ✅
6. Your Microsoft account will be linked to your existing user account!

---

## 📊 What These Columns Do

- **`microsoft_id`**: Stores your unique Microsoft account ID (UUID format)
- **`avatar`**: Stores your Microsoft profile picture URL

These allow:
- ✅ Linking Microsoft accounts to existing users via email
- ✅ Storing Microsoft profile pictures
- ✅ Preventing duplicate Microsoft accounts (unique constraint)
- ✅ Faster OAuth login (no password needed)

---

## ✅ Expected Result

After adding the columns and testing:

1. You click "Sign in with Microsoft"
2. Microsoft authenticates you
3. System finds your existing account by email
4. Adds your `microsoft_id` to your account
5. Logs you in
6. Redirects to dashboard
7. You can now login with either:
   - Microsoft OAuth (no password) ✅
   - Email + Password + OTP (traditional) ✅

---

## 🔍 Verify After Testing

Visit: https://www.campfixsti.com/test-oauth-status

Should show:
```json
{
  "session_driver": "database",
  "authenticated": true,
  "user_id": 3917,
  "user_email": "your@email.com",
  "user_role": "student",
  "session_user_id": 3917
}
```

---

## 📝 Files Created

- `add_oauth_columns.sql` - SQL script to add columns
- `ADD_DATABASE_COLUMNS.md` - This instruction file

---

**Go ahead and run the SQL in Supabase, then test OAuth login again!** 🚀
