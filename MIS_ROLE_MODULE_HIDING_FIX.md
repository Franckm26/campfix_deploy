# MIS Role Module Hiding Fix

## Problem
When selecting the MIS role in the user management interface, the following modules were visible in the UI but should have been hidden (automatically granted):

- ✅ MIS Tasks - Should be hidden (automatically granted)
- ✅ Module Access Control - Should be hidden (automatically granted)
- ✅ Categories - Should be hidden (automatically granted to all users)
- ✅ Settings - Should be hidden (automatically granted to all users)

## Root Cause
The JavaScript functions responsible for hiding modules were not properly filtering out the auto-granted modules for the MIS role. While the backend PHP code (`roleSpecificHiddenModules()`) correctly identified which modules should be hidden, the frontend JavaScript wasn't using this information consistently.

## Solution

### 1. Regular Add User Modal (`onRoleChange` function)
**Updated Logic:**
- Always hide `settings` and `categories` for all roles (auto-granted to everyone)
- For MIS role: Also hide `mis_tasks` and `module_access` (auto-granted to MIS only)
- For non-MIS roles: Show `mis_tasks` and `module_access` as unchecked options

### 2. Regular Add User Modal (`applyRoleDefaults` function)
**Updated Logic:**
- Determine which modules should be hidden based on role
- Hide module divs that should be auto-granted
- Auto-check hidden modules (they're granted behind the scenes)
- Only show and allow manual selection for non-hidden modules

### 3. Regular Add User Modal HTML
**Updated:**
- Added `data-module="{{ $key }}"` attribute to permission module containers
- Added `permission-module` class for easier JavaScript targeting
- This allows JavaScript to find and hide specific modules

### 4. Edit User Modal (SweetAlert - `editUser` function)
**Updated `buildModuleHtml` function:**
- Get role-specific hidden modules using backend data
- Skip hidden modules when building the HTML
- Add explanatory text: "Some modules are automatically granted and hidden"
- Ensure sub-permissions that are hidden are also skipped

### 5. Edit User Modal Role Change (`onSwalRoleChange` function)
**Updated Logic:**
- When role changes, recalculate which modules should be hidden
- Rebuild module list excluding hidden modules
- Preserve checked state for non-hidden modules during role change

### 6. JavaScript Hidden Modules Function (`getHiddenModulesForRole`)
**Added Comments:**
- Clarified that MIS role gets additional hidden modules
- Base hidden: `settings`, `categories` (all roles)
- MIS additional: `mis_tasks`, `module_access`

## Technical Details

### Backend (Already Working Correctly)
```php
// In User.php model
public static function hiddenModules(): array
{
    return ['settings', 'categories'];  // Always hidden for all roles
}

public static function roleSpecificHiddenModules(string $role): array
{
    $hidden = self::hiddenModules();
    
    if ($role === 'mis') {
        $hidden[] = 'mis_tasks';
        $hidden[] = 'module_access';
    }
    
    return $hidden;
}
```

### Frontend (Fixed)
The JavaScript now properly consumes the backend's hidden modules list and:
1. Filters them out of the UI during initial render
2. Filters them out when role changes
3. Auto-grants them in the background (via hidden inputs or backend logic)

## Testing
To verify the fix:

1. **Add New User (Regular Modal)**
   - Select MIS role
   - Verify that Settings, Categories, MIS Tasks, and Module Access Control are NOT visible
   - Select Student role
   - Verify that Settings and Categories are NOT visible, but MIS Tasks and Module Access Control remain hidden

2. **Add New User (SweetAlert Modal)**
   - Click "Add User" button
   - Select MIS role
   - Verify that the four modules are hidden
   - Change to another role
   - Verify modules visibility updates correctly

3. **Edit User (SweetAlert)**
   - Edit an existing MIS user
   - Verify the four modules are hidden
   - Change role to Faculty
   - Verify module visibility updates correctly

## Files Modified
- `resources/views/admin/users.blade.php`
  - Updated `onRoleChange()` function
  - Updated `applyRoleDefaults()` function
  - Updated `editUser()` async function's `buildModuleHtml()` closure
  - Updated `onSwalRoleChange()` function
  - Updated edit modal DOMContentLoaded handler (removed duplicate)
  - Added `permission-module` class and `data-module` attribute to HTML

## Result
✅ MIS role users now have the correct modules hidden from the UI
✅ These modules are automatically granted on the backend
✅ The UI properly reflects what users can manually configure
✅ Role changes properly update module visibility in real-time
