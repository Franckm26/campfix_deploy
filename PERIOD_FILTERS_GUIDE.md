# Period Filters Guide - Monthly, Quarterly, Yearly

## New Feature: Period Dropdown

Added a smart "Period" dropdown that simplifies filtering by common time periods:
- **Monthly** - View data for a specific month
- **Quarterly** - View data for Q1, Q2, Q3, or Q4
- **Yearly** - View data for an entire year
- **Custom** - Use manual date range filters

## How It Works

### 1. Monthly Period
**Steps:**
1. Select "Monthly" from Period dropdown
2. Select a Month (e.g., "January")
3. Select a Year (e.g., "2026")
4. Click "Filter"

**Result:** Shows all data for January 2026

**UI Behavior:**
- Month dropdown: ✅ Visible
- Quarter dropdown: ❌ Hidden
- Date From/To: ❌ Hidden
- Year dropdown: ✅ Visible

### 2. Quarterly Period
**Steps:**
1. Select "Quarterly" from Period dropdown
2. Select a Quarter:
   - Q1 (Jan-Mar)
   - Q2 (Apr-Jun)
   - Q3 (Jul-Sep)
   - Q4 (Oct-Dec)
3. Select a Year (e.g., "2026")
4. Click "Filter"

**Result:** Shows all data for that quarter in 2026

**UI Behavior:**
- Month dropdown: ❌ Hidden
- Quarter dropdown: ✅ Visible
- Date From/To: ❌ Hidden
- Year dropdown: ✅ Visible

### 3. Yearly Period
**Steps:**
1. Select "Yearly" from Period dropdown
2. Select a Year (e.g., "2026")
3. Click "Filter"

**Result:** Shows all data for the entire year 2026

**UI Behavior:**
- Month dropdown: ❌ Hidden
- Quarter dropdown: ❌ Hidden
- Date From/To: ❌ Hidden
- Year dropdown: ✅ Visible

### 4. Custom Period
**Steps:**
1. Select "Custom" from Period dropdown (or leave default)
2. Use any combination of:
   - Month dropdown
   - Year dropdown
   - Date From field
   - Date To field
3. Click "Filter"

**Result:** Shows data based on your custom filters

**UI Behavior:**
- Month dropdown: ✅ Visible
- Quarter dropdown: ❌ Hidden
- Date From/To: ✅ Visible
- Year dropdown: ✅ Visible

## Filter Examples

### Example 1: January 2026 (Monthly)
```
Period: Monthly
Month: January
Year: 2026
```
**PDF Title:** "January 2026"

### Example 2: Q1 2026 (Quarterly)
```
Period: Quarterly
Quarter: Q1 (Jan-Mar)
Year: 2026
```
**PDF Title:** "Q1 2026"

### Example 3: Full Year 2025 (Yearly)
```
Period: Yearly
Year: 2025
```
**PDF Title:** "Year 2025"

### Example 4: Last Week of December (Custom)
```
Period: Custom
Date From: 2025-12-24
Date To: 2025-12-31
```
**PDF Title:** "From Dec 24, 2025 To Dec 31, 2025"

## Dynamic UI Behavior

The filter form automatically shows/hides relevant fields based on the selected period:

| Period | Month | Quarter | Year | Date From | Date To |
|--------|-------|---------|------|-----------|---------|
| Monthly | ✅ | ❌ | ✅ | ❌ | ❌ |
| Quarterly | ❌ | ✅ | ✅ | ❌ | ❌ |
| Yearly | ❌ | ❌ | ✅ | ❌ | ❌ |
| Custom | ✅ | ❌ | ✅ | ✅ | ✅ |

## Backend Logic

### Monthly Filter
```php
whereMonth('created_at', $month)
whereYear('created_at', $year)
```

### Quarterly Filter
**Q1 (Jan-Mar):**
```php
whereYear('created_at', $year)
whereMonth('created_at', '>=', 1)
whereMonth('created_at', '<=', 3)
```

**Q2 (Apr-Jun):**
```php
whereYear('created_at', $year)
whereMonth('created_at', '>=', 4)
whereMonth('created_at', '<=', 6)
```

**Q3 (Jul-Sep):**
```php
whereYear('created_at', $year)
whereMonth('created_at', '>=', 7)
whereMonth('created_at', '<=', 9)
```

**Q4 (Oct-Dec):**
```php
whereYear('created_at', $year)
whereMonth('created_at', '>=', 10)
whereMonth('created_at', '<=', 12)
```

### Yearly Filter
```php
whereYear('created_at', $year)
```

## PDF Export

The PDF export respects the selected period and displays it appropriately:

- **Monthly:** "January 2026"
- **Quarterly:** "Q1 2026"
- **Yearly:** "Year 2026"
- **Custom:** "From Jan 1, 2026 To Mar 31, 2026" or "All Time"

## Use Cases

### 1. Monthly Reports
**Scenario:** Generate monthly maintenance reports for management

**Steps:**
1. Period: Monthly
2. Month: Current month
3. Year: Current year
4. Export to PDF

**Result:** Professional monthly report ready for presentation

### 2. Quarterly Budget Review
**Scenario:** Review repair costs for budget planning

**Steps:**
1. Period: Quarterly
2. Quarter: Q1
3. Year: 2026
4. Export to PDF

**Result:** Q1 cost analysis for budget meetings

### 3. Annual Summary
**Scenario:** Year-end report for stakeholders

**Steps:**
1. Period: Yearly
2. Year: 2025
3. Export to PDF

**Result:** Complete annual maintenance summary

### 4. Custom Date Range
**Scenario:** Analyze repairs during a specific event or project

**Steps:**
1. Period: Custom
2. Date From: Project start date
3. Date To: Project end date
4. Export to PDF

**Result:** Targeted analysis for specific timeframe

## JavaScript Functionality

The page includes JavaScript that:
1. Listens for changes to the Period dropdown
2. Shows/hides relevant filter fields dynamically
3. Runs on page load to set initial state
4. No page refresh needed for UI updates

## Benefits

✅ **User-Friendly:** Common periods are one-click selections
✅ **Flexible:** Custom option still available for specific needs
✅ **Clean UI:** Only relevant fields are shown
✅ **Professional:** PDF titles match the selected period
✅ **Intuitive:** Similar to the Mammoth Mountain example you provided

## Testing Checklist

- [ ] Period dropdown displays all 4 options
- [ ] Selecting "Monthly" shows Month + Year only
- [ ] Selecting "Quarterly" shows Quarter + Year only
- [ ] Selecting "Yearly" shows Year only
- [ ] Selecting "Custom" shows all fields
- [ ] Monthly filter works correctly
- [ ] Quarterly filter works for all 4 quarters
- [ ] Yearly filter works correctly
- [ ] PDF export includes correct period in title
- [ ] Reset button clears all filters
- [ ] UI updates without page refresh

## Files Modified

1. **resources/views/admin/analytics.blade.php**
   - Added Period dropdown
   - Added Quarter dropdown
   - Added JavaScript for dynamic UI

2. **app/Http/Controllers/AdminController.php**
   - Updated `analytics()` method with period logic
   - Updated `locationReportPDF()` method with period logic

3. **public/js/analytics-modals.js**
   - Updated export function to pass period and quarter

## Browser Compatibility

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- JavaScript required for dynamic UI (gracefully degrades)
