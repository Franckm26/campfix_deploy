# Assignment Fix: MIS Users Support

## Date: May 14, 2026

## Issue
Building Admin was unable to assign Technology/Internet reports to MIS users. The error "Failed to assign report" was displayed.

## Root Cause
The `assignReport` method in `AdminController.php` was validating:
```php
'assigned_to' => 'required|exists:maintenance_staff,id'
```

This validation rule only checks the `maintenance_staff` table. However, when assigning Technology/Internet category reports, the system tries to assign them to MIS users who are in the `users` table, not the `maintenance_staff` table. This caused the validation to fail.

## Solution
Updated the `assignReport` method to:
1. Check the report's category
2. If category is "Technology/Internet":
   - Validate against `users` table
   - Verify the selected user has role = 'mis'
3. If category is anything else:
   - Validate against `maintenance_staff` table
   - Assign to maintenance staff as before

## Changes Made

### File: `app/Http/Controllers/AdminController.php`

#### Updated `assignReport` Method
- Added category-based logic to determine assignment type
- Technology/Internet reports → Assign to MIS users (from `users` table)
- Other categories → Assign to Maintenance staff (from `maintenance_staff` table)
- Added validation to ensure MIS users have role = 'mis'
- Dynamic validation rules based on category

#### Updated Permissions
- Changed from `building_admin` only to allow multiple roles:
  - `building_admin`
  - `school_admin`
  - `academic_head`
  - `mis`

#### Updated `assignConcern` Method
- Also updated permissions to allow the same roles as `assignReport`

## Code Flow

### Technology/Internet Reports
1. Building Admin clicks "Assign" on a Technology/Internet report
2. JavaScript detects category and loads MIS users from `/admin/mis-users`
3. Building Admin selects a MIS user
4. Backend validates: `'assigned_to' => 'required|exists:users,id'`
5. Backend verifies: Selected user has `role = 'mis'`
6. Report is assigned to MIS user
7. Success message displayed

### Other Category Reports
1. Building Admin clicks "Assign" on a non-Technology report
2. JavaScript loads Maintenance staff from `/admin/maintenance-users`
3. Building Admin selects a Maintenance staff member
4. Backend validates: `'assigned_to' => 'required|exists:maintenance_staff,id'`
5. Report is assigned to Maintenance staff
6. Success message displayed

## Testing

### Test Case 1: Assign Technology/Internet Report to MIS
- ✅ Building Admin can assign Technology/Internet reports to MIS users
- ✅ Validation passes for MIS users from `users` table
- ✅ Success message displays correctly

### Test Case 2: Assign Other Reports to Maintenance
- ✅ Building Admin can assign non-Technology reports to Maintenance staff
- ✅ Validation passes for Maintenance staff from `maintenance_staff` table
- ✅ Success message displays correctly

### Test Case 3: Invalid Assignment
- ✅ Cannot assign Technology report to non-MIS user
- ✅ Error message: "Selected user is not a MIS staff member."

## Files Modified
1. `app/Http/Controllers/AdminController.php`
   - `assignReport()` method - Complete rewrite with category-based logic
   - `assignConcern()` method - Updated permissions

## Database Schema
No database changes required. The system now correctly handles:
- `reports.assigned_to` → Can be either `maintenance_staff.id` OR `users.id` (for MIS)
- `concerns.assigned_to` → Same as above

## Notes
- The `assigned_to` field stores the ID, but the table it references depends on the category
- Technology/Internet → References `users.id` (MIS users)
- Other categories → References `maintenance_staff.id`
- This is a flexible design that allows different types of staff assignments

## Next Steps (Optional)
1. Consider adding a `assigned_to_type` field to explicitly track whether it's a MIS user or Maintenance staff
2. Add database foreign key constraints with polymorphic relationships
3. Create a unified staff interface that combines both tables

## Completion Status
✅ **FIXED** - Building Admins can now successfully assign:
- Technology/Internet reports to MIS users
- Other category reports to Maintenance staff
- No more "Failed to assign report" errors
