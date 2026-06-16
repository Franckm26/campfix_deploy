# Select All Checkbox Fix

## Issue
When clicking "Select All" checkbox in the Deleted tab, it was also selecting checkboxes in the Active tab (and vice versa for other tabs).

## Root Cause
The `toggleSelectAll()` function was using `document.querySelectorAll('.' + className)` which selects ALL checkboxes with that class across the entire page, including hidden tabs.

## Solution
Modified the function to scope the checkbox selection to only the current tab by:
1. Getting the specific tab pane container ID for each tab
2. Using `tabPane.querySelectorAll()` instead of `document.querySelectorAll()`
3. This ensures only checkboxes within the visible tab are affected

## Code Changes

### Before:
```javascript
function toggleSelectAll(type) {
    const className = type === 'active' ? 'active-checkbox' : 
                     (type === 'resolved' ? 'resolved-checkbox' : 
                     (type === 'archive' ? 'archive-checkbox' : 'deleted-checkbox'));
    const checkboxes = document.querySelectorAll('.' + className); // ❌ Selects from ALL tabs
    const selectAll = document.getElementById(checkboxId);
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    // ...
}
```

### After:
```javascript
function toggleSelectAll(type) {
    const className = type === 'active' ? 'active-checkbox' : 
                     (type === 'resolved' ? 'resolved-checkbox' : 
                     (type === 'archive' ? 'archive-checkbox' : 'deleted-checkbox'));
    
    // Get the container for the specific tab to limit selection scope
    const tabPaneId = type === 'active' ? 'active-concerns' : 
                     (type === 'resolved' ? 'resolved-concerns' : 
                     (type === 'archive' ? 'archived-concerns' : 'deleted-concerns'));
    const tabPane = document.getElementById(tabPaneId);
    
    // Only select checkboxes within the visible tab ✅
    const checkboxes = tabPane ? tabPane.querySelectorAll('.' + className) : [];
    const selectAll = document.getElementById(checkboxId);
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    // ...
}
```

## Tab Pane IDs
- Active: `active-concerns`
- Resolved: `resolved-concerns`
- Archives: `archived-concerns`
- Deleted: `deleted-concerns`

## How It Works Now

### Select All Flow:
1. User clicks "Select All" in Deleted tab
2. Function identifies tab type as 'deleted'
3. Gets tab pane container: `document.getElementById('deleted-concerns')`
4. Scopes checkbox selection: `tabPane.querySelectorAll('.deleted-checkbox')`
5. Only checkboxes within the Deleted tab pane are selected
6. Checkboxes in other tabs remain unaffected

## Testing Checklist
- [x] Select All in Active tab (should only select Active concerns)
- [ ] Select All in Resolved tab (should only select Resolved concerns)
- [ ] Select All in Archives tab (should only select Archived concerns)
- [ ] Select All in Deleted tab (should only select Deleted concerns)
- [ ] Switch between tabs and verify selections remain isolated

## Files Changed
- `resources/views/concerns/my.blade.php`

## Deployment
Commit: `46d9e0a` - Fix select all checkbox to only affect visible tab, not all tabs
Deployed to: https://www.campfixsti.com

## Next Steps
1. Wait for Vercel deployment (~1-2 minutes)
2. Test "Select All" in each tab
3. Verify checkboxes in other tabs are not affected
4. Test batch operations work correctly with the scoped selection
