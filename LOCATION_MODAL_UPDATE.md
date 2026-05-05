# Location Modal Update - Separate Items

## Changes Made

### What Changed:
Instead of grouping all repairs by location only, the modal now shows **each individual item/issue as a separate row**.

### Example:
**Before:**
- Room 304 → 5 repairs (all items combined)

**After:**
- Broken Chair → Room 304 → 2 repairs
- Damaged Table → Room 304 → 3 repairs

### Table Structure:

| Column | Description |
|--------|-------------|
| **Item Fixed** | The specific item/issue that was repaired (e.g., "Broken Chair", "Leaking Faucet") |
| **Location** | The room/area where the repair was done |
| **Total Repairs** | Number of times THIS SPECIFIC ITEM was repaired at THIS LOCATION |
| **Total Cost** | Total cost for repairing THIS SPECIFIC ITEM at THIS LOCATION |

### Backend Changes:

**File:** `app/Http/Controllers/AdminController.php`

1. **New Query - `$locationStats`:**
   - Groups by BOTH `location` AND `title` (item/issue)
   - Shows each location-item combination as a separate record
   - Filters out records with no title
   - Returns: location, title, count, total_cost

2. **New Query - `$locationChartStats`:**
   - Groups by `location` only (for the pie chart)
   - Aggregates all items per location
   - Used for chart visualization only

### Frontend Changes:

**File:** `resources/views/admin/analytics.blade.php`
- Added `window.locationDetailedStats` with all location-item combinations
- Removed `window.locationItems` (no longer needed)

**File:** `public/js/analytics-modals.js`
- Updated `showLocationDetailsModal()` function
- Now uses `window.locationDetailedStats` array
- Each row shows a unique location-item combination
- Sorted by count (most repaired items first)

### How It Works:

1. **Database Query:**
   ```sql
   SELECT location, title, COUNT(*) as count, SUM(cost) as total_cost
   FROM reports
   WHERE status = 'Resolved' 
     AND location IS NOT NULL 
     AND title IS NOT NULL
   GROUP BY location, title
   ORDER BY count DESC
   ```

2. **Data Structure:**
   ```javascript
   [
     { location: "Room 304", title: "Broken Chair", count: 2, total_cost: 500 },
     { location: "Room 304", title: "Damaged Table", count: 3, total_cost: 1200 },
     { location: "Room 203", title: "Leaking Faucet", count: 1, total_cost: 300 }
   ]
   ```

3. **Modal Display:**
   - Each array item becomes a table row
   - No "Various Issues" grouping
   - Each item is listed separately
   - Total Repairs = count for that specific item at that location

### Benefits:

✅ **Detailed Breakdown:** See exactly which items are being repaired most often
✅ **No Grouping:** Each item-location combination is visible
✅ **Accurate Counts:** "Total Repairs" shows repairs for that specific item only
✅ **Better Analysis:** Identify which specific items need attention

### Example Output:

```
Item Fixed          | Location              | Total Repairs | Total Cost
--------------------|----------------------|---------------|------------
Broken Chair        | Room 304             |       2       | ₱500.00
Damaged Table       | Room 304             |       3       | ₱1,200.00
Leaking Faucet      | Room 203             |       1       | ₱300.00
Broken Window       | Computer Lab 212     |       1       | ₱800.00
Damaged Door        | Room 301             |       1       | ₱500.00
--------------------|----------------------|---------------|------------
TOTAL               |                      |       8       | ₱3,300.00
```

### Testing:

1. Clear browser cache: `Ctrl + Shift + R`
2. Navigate to: `http://127.0.0.1:8000/admin/analytics`
3. Click on "Repairs by Location" pie chart
4. Verify:
   - ✅ Each item appears as a separate row
   - ✅ Same location can appear multiple times (with different items)
   - ✅ Total Repairs shows count for that specific item
   - ✅ No "Various Issues" entries
   - ✅ All items are listed individually

### Notes:

- Reports without a `title` field are excluded from the detailed view
- The pie chart still shows aggregated data by location (all items combined)
- The modal table shows the detailed breakdown by item
- Sorted by repair count (most frequently repaired items appear first)
