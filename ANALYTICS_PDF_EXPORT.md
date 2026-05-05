# Analytics PDF Export & Month/Year Filters

## Features Added

### 1. Export to PDF Button
- Added "Export to PDF" button in the Location Details modal
- Button appears above the table in the modal
- Exports the current filtered data to a professional PDF report

### 2. Month and Year Filters
- Added **Month** dropdown (January - December)
- Added **Year** dropdown (Current year - 5 years back)
- Works alongside existing Date From/To filters
- All filters can be combined

### 3. PDF Report Features
- Professional layout with header and branding
- Summary section showing total repairs and total cost
- Complete table with all location-item combinations
- Date range displayed based on selected filters
- Generated timestamp at bottom
- Proper formatting with borders and alternating row colors

## Files Modified

### 1. **routes/web.php**
- Added route: `/admin/analytics/location-report-pdf`

### 2. **app/Http/Controllers/AdminController.php**
- Updated `analytics()` method to handle month and year filters
- Added `locationReportPDF()` method for PDF generation

### 3. **resources/views/admin/analytics.blade.php**
- Added Month dropdown filter
- Added Year dropdown filter
- Added CSS for select dropdowns

### 4. **public/js/analytics-modals.js**
- Added Export to PDF button in modal
- Added `exportLocationReportToPDF()` function
- Passes all filter parameters to PDF endpoint

### 5. **resources/views/admin/analytics-location-pdf.blade.php** (NEW)
- PDF template with professional styling
- Displays filtered location report data

## How to Use

### Filtering Data:

1. **By Month:**
   - Select a month from the dropdown (e.g., "January")
   - Click "Filter"
   - Shows data for that month across all years (unless year is also selected)

2. **By Year:**
   - Select a year from the dropdown (e.g., "2026")
   - Click "Filter"
   - Shows data for that entire year

3. **By Month AND Year:**
   - Select both month and year
   - Click "Filter"
   - Shows data for that specific month in that specific year

4. **By Date Range:**
   - Use "Date From" and "Date To" fields
   - More precise than month/year filters
   - Can be used alone or combined with month/year

5. **Combined Filters:**
   - You can use month + year + date range together
   - All filters are applied cumulatively

### Exporting to PDF:

1. Apply your desired filters (month, year, date range)
2. Click on "Repairs by Location" pie chart to open modal
3. Click the "Export to PDF" button (red button with PDF icon)
4. PDF will open in a new browser tab
5. You can save or print the PDF from there

## Filter Examples

### Example 1: January 2026
```
Month: January
Year: 2026
Date From: (empty)
Date To: (empty)
```
Result: All repairs from January 1-31, 2026

### Example 2: All of 2025
```
Month: All Months
Year: 2025
Date From: (empty)
Date To: (empty)
```
Result: All repairs from 2025

### Example 3: Last Quarter of 2025
```
Month: All Months
Year: (empty)
Date From: 2025-10-01
Date To: 2025-12-31
```
Result: All repairs from Oct 1 - Dec 31, 2025

### Example 4: Specific Week in March 2026
```
Month: March
Year: 2026
Date From: 2026-03-15
Date To: 2026-03-21
```
Result: All repairs from March 15-21, 2026

## PDF Report Contents

The PDF includes:

1. **Header Section:**
   - Report title: "Repairs by Location - Detailed Report"
   - Subtitle: "CampFix Analytics"

2. **Date Range:**
   - Shows the selected filters (e.g., "January 2026" or "All Time")

3. **Summary Box:**
   - Total Repairs count
   - Total Cost amount

4. **Data Table:**
   - Item Fixed column
   - Location column
   - Total Repairs column
   - Total Cost column
   - Footer row with totals

5. **Footer:**
   - Generation timestamp
   - System branding

## Technical Details

### Filter Logic:
- Month filter: Uses `whereMonth()` on `created_at`
- Year filter: Uses `whereYear()` on `created_at`
- Date From: Uses `whereDate()` with `>=`
- Date To: Uses `whereDate()` with `<=`
- All filters are optional and can be combined

### PDF Generation:
- Uses Laravel PDF package (barryvdh/laravel-dompdf)
- Streams PDF directly to browser
- Filename format: `location-report-YYYY-MM-DD.pdf`

### Data Query:
- Groups by `location` AND `title` (item)
- Only includes resolved reports
- Filters out records with no title
- Sorted by repair count (descending)

## Testing Checklist

- [ ] Month dropdown displays all 12 months
- [ ] Year dropdown shows current year and 5 years back
- [ ] Selecting month only filters correctly
- [ ] Selecting year only filters correctly
- [ ] Selecting month + year filters correctly
- [ ] Date range filters still work
- [ ] Combined filters work together
- [ ] Reset button clears all filters
- [ ] Export button appears in modal
- [ ] Export button generates PDF
- [ ] PDF includes correct filtered data
- [ ] PDF displays date range correctly
- [ ] PDF totals are accurate
- [ ] PDF formatting looks professional

## Browser Compatibility

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- PDF opens in new tab in all browsers

## Notes

- The month/year filters are more user-friendly than date pickers for common use cases
- Date range filters provide more precision when needed
- All filters can be used together for maximum flexibility
- PDF respects all active filters
- Clear browser cache after updating: `Ctrl + Shift + R`
