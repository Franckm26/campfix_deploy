# Fix Event Setup Tables

## Problem
The Event Setup tab in `/admin/management` shows a warning: 
> "Event Setup will be available after the latest database migration is applied."

This happens because the required database tables (`event_request_types`, `event_intended_users`, `event_departments`) don't exist yet.

## Solution

### Option 1: Run the SQL script directly (Quickest)
1. Go to your database management tool (phpMyAdmin, MySQL Workbench, etc.)
2. Select your campfix database
3. Run the SQL file: `create_event_setup_tables.sql`

This will create the three required tables and populate them with default data.

### Option 2: Run Laravel Migration (Recommended for production)
```bash
php artisan migrate
```

This will run all pending migrations including `2026_08_21_170000_create_event_configuration_tables.php`.

## What Gets Created

### Tables:
1. **event_request_types** - Stores types of event requests (Academic, Non-Academic) with approval roles
2. **event_intended_users** - Stores intended user categories (Faculty, Tertiary, SHS, Staff, Maintenance)
3. **event_departments** - Stores departments (GE, ICT, Business Management, THM)

### Column Addition:
- Adds `approval_route` JSON column to existing `event_requests` table

## After Running
1. Refresh the `/admin/management` page
2. Go to the "Event Setup" tab
3. The warning should be gone and you can now configure:
   - Request types and approval roles
   - Intended users
   - Departments

## Verification
To verify tables exist, run:
```sql
SHOW TABLES LIKE 'event_%';
```

You should see:
- event_departments
- event_intended_users
- event_request_types
- event_requests
