# 🔧 Fix Submission Error - Missing Rate Limit Table

## ❌ The Error

When submitting a concern, you got:
```
SQLSTATE[42P01]: Undefined table: 7 ERROR: relation "user_rate_limits" does not exist
```

This means the `user_rate_limits` table is missing from your database.

---

## ✅ The Fix

### Step 1: Create the Missing Table in Supabase

1. Go to: https://supabase.com/dashboard
2. Select your project
3. Click **SQL Editor** (left sidebar)
4. Click **New query**
5. Copy and paste this SQL:

```sql
-- Create user_rate_limits table for custom rate limiting
CREATE TABLE IF NOT EXISTS user_rate_limits (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    count INTEGER NOT NULL DEFAULT 0,
    date DATE NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS user_rate_limits_user_id_action_type_date_index 
ON user_rate_limits(user_id, action_type, date);

CREATE UNIQUE INDEX IF NOT EXISTS user_rate_limits_user_id_action_type_date_unique 
ON user_rate_limits(user_id, action_type, date);

-- Add foreign key constraint
ALTER TABLE user_rate_limits
ADD CONSTRAINT user_rate_limits_user_id_foreign 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;

-- Verify table was created
SELECT table_name FROM information_schema.tables 
WHERE table_name = 'user_rate_limits';
```

6. Click **Run** (or press `Ctrl+Enter`)
7. You should see `user_rate_limits` in the results

---

## 🧪 Test Submission Again

After creating the table:

1. Go to: https://www.campfixsti.com/my-concerns
2. Click **+ New Concern**
3. Fill in the form:
   - Title: "Electric Outlet"
   - Category: Any category
   - Location: "Room 301"
   - Description: "Test submission"
   - Image: (optional)
4. Click **Submit**
5. Should work now! ✅

---

## 📊 What This Table Does

The `user_rate_limits` table tracks:
- **user_id**: Which user made the action
- **action_type**: Type of action ('submission' or 'upload')
- **count**: How many times they've done it
- **date**: On which date
- **Limit**: 5 submissions per day, 5 uploads per day

### Example Data:
| user_id | action_type | count | date |
|---------|-------------|-------|------|
| 3917 | submission | 3 | 2026-06-14 |
| 3917 | upload | 2 | 2026-06-14 |

When you try to submit a 6th concern in one day, you'll get:
```
"You have reached your daily submission limit of 5."
```

---

## 🔒 Why Rate Limiting?

This protects your system from:
- ✅ **Spam** - Users can't flood the system with concerns
- ✅ **Abuse** - Prevents malicious mass submissions
- ✅ **Resource protection** - Database and storage aren't overwhelmed
- ✅ **Fair usage** - Everyone gets equal access

**Limits:**
- 5 concerns per day per user
- 5 file uploads per day per user

---

## ⚠️ About the aria-hidden Warning

The console also shows:
```
Blocked aria-hidden on an element because its descendant retained focus
```

This is just an **accessibility warning** (not an error). It happens when Bootstrap modals close. It doesn't break functionality, but we can fix it later if you want.

**Impact**: None - everything still works  
**Priority**: Low - cosmetic warning only

---

## ✅ Verification Checklist

- [ ] Ran SQL in Supabase SQL Editor
- [ ] Verified `user_rate_limits` table exists
- [ ] Tested submitting a concern
- [ ] Submission successful ✅
- [ ] No more 500 errors

---

## 📝 Summary

**Problem**: Missing `user_rate_limits` table  
**Solution**: Created table in Supabase  
**Status**: ✅ FIXED (after you run the SQL)  
**Next**: Submit concerns normally - rate limiting now protects your app!

---

**Run the SQL in Supabase, then try submitting a concern again!** 🚀
