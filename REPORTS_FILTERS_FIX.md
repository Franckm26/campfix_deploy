# Reports Filters - Fix Implementation

## Problem
The filter dropdowns on the Reports page were not filtering the data. The filters were visible but selecting options had no effect on the displayed reports.

## Root Cause
The backend `reports()` method in `AdminController` was not applying the filter parameters from the request to the database queries.

## Solution

### 1. Frontend Changes (resources/views/admin/reports.blade.php)
- Added form ID: `id="reportsFilterForm"`
- Added CSS class to dropdowns: `class="auto-submit-filter"`
- Added JavaScript to auto-submit form when dropdowns change
- Fixed reset button to preserve view type

### 2. Backend Changes (app/Http/Controllers/AdminController.php)

#### Active Reports Section
**Before:**
```php
$reports = Report::with('user', 'category')
    ->where('is_deleted', false)
    ->where('status', '!=', 'Resolved')
    ->where(function ($query) {
        $query->where('building_admin_archived', false)
            ->where('mis_archived', false)
            ->where('school_admin_archived', false)
            ->where('admin_archived', false);
    })
    ->orderBy('created_at', 'desc')
    ->get();
```

**After:**
```php
$query = Report::with('user', 'category')
    ->where('is_deleted', false)
    ->where('status', '!=', 'Resolved')
    ->where(function ($q) {
        $q->where('building_admin_archived', false)
            ->where('mis_archived', false)
            ->where('school_admin_archived', false)
            ->where('admin_archived', false);
    });

// Apply filters
if ($request->filled('status')) {
    $query->where('status', $request->input('status'));
}

if ($request->filled('priority')) {
    $query->where('severity', $request->input('priority'));
}

if ($request->filled('category')) {
    $query->where('category_id', $request->input('category'));
}

if ($request->filled('search')) {
    $search = $request->input('search');
    $query->where(function ($q) use ($search) {
        $q->where('title', 'ILIKE', "%{$search}%")
          ->orWhere('description', 'ILIKE', "%{$search}%")
          ->orWhere('location', 'ILIKE', "%{$search}%");
    });
}

$reports = $query->orderBy('created_at', 'desc')->get();
```

#### Resolved Reports Section
Added the same filter logic to the resolved reports query:
- Priority filter (severity field)
- Category filter
- Search filter (title, description, location)

## Filter Functionality

### Auto-Submit Filters
The following filters auto-submit the form when changed:
- ✅ **Archived dropdown** - Active Concerns, Archived, All Concerns
- ✅ **Status dropdown** - All Status, Pending, Assigned, In Progress, Resolved
- ✅ **Priority dropdown** - All Priority, Low, Medium, High, Urgent
- ✅ **Category dropdown** - All Categories, [Category List]

### Manual Submit
- **Search field** - Requires clicking "Filter" button or pressing Enter

### Filter Mapping
| Filter | Database Field | Search Type |
|--------|---------------|-------------|
| Status | `status` | Exact match |
| Priority | `severity` | Exact match |
| Category | `category_id` | Exact match |
| Search | `title`, `description`, `location` | ILIKE (case-insensitive) |

## Database Compatibility
- Uses PostgreSQL `ILIKE` for case-insensitive search
- Properly handles NULL values in search fields

## Testing Checklist
- [x] Status filter works on Active Reports tab
- [x] Priority filter works on Active Reports tab
- [x] Category filter works on Active Reports tab
- [x] Search filter works on Active Reports tab
- [x] Multiple filters work together
- [x] Priority filter works on Resolved Reports tab
- [x] Category filter works on Resolved Reports tab
- [x] Search filter works on Resolved Reports tab
- [x] Filters auto-submit on dropdown change
- [x] Reset button clears all filters
- [x] Export PDF respects active filters

## Files Modified
1. `resources/views/admin/reports.blade.php` - Added form ID, CSS classes, and JavaScript
2. `app/Http/Controllers/AdminController.php` - Added filter logic to active and resolved reports queries

## Benefits
✅ Filters now work as expected
✅ Auto-submit on dropdown change for better UX
✅ Search works across multiple fields
✅ Filters work with PDF export
✅ Case-insensitive search
✅ Multiple filters can be combined
