# Analytics Alert Modal - PDF Export Feature

## Summary
Added PDF export functionality to the enhanced Alert Detail modal, matching the design and format of the main comprehensive analytics PDF.

## Changes Made

### 1. Frontend - Export Button (`resources/views/admin/analytics.blade.php`)

**Added "Export PDF" Button to SweetAlert2 Modal**:
- Changed `showConfirmButton` to include both Close and Export PDF buttons
- Added `showDenyButton: true` with red color (#dc3545)
- Button text: `<i class="fas fa-file-pdf me-2"></i>Export PDF`
- Button triggers `exportAlertDetailPDF()` function when clicked

**New JavaScript Function** (`exportAlertDetailPDF`):
```javascript
function exportAlertDetailPDF(location, issue) {
    // Shows loading modal
    // Builds URL with location and issue parameters
    // Opens PDF in new window
    // Closes loading modal after 1 second
}
```

**Location**: Added after `showCostTrendModal()` function (~line 2400)

### 2. Backend - Controller Method (`app/Http/Controllers/AdminController.php`)

**New Method**: `alertDetailPDF(Request $request)`

**Functionality**:
1. Validates `location` and `issue` parameters
2. Fetches all resolved reports for the location/issue
3. Groups repairs by `damaged_part` field
4. Calculates statistics:
   - Total repairs count
   - Total cost
   - Average cost per repair
   - Repair count per damaged part
5. Gets monthly breakdown (last 12 months)
6. Determines severity level (critical/warning/info)
7. Generates PDF using `alert-detail-pdf.blade.php` view

**Location**: Added before `updateCost()` method (~line 6200)

### 3. Route (`routes/web.php`)

**New Route**:
```php
Route::get('/admin/analytics/alert-detail-pdf', [AdminController::class, 'alertDetailPDF'])
    ->name('admin.analytics.alert-detail-pdf');
```

**Location**: Added after `period-comparison-pdf` route (line 343)

### 4. PDF View (`resources/views/admin/alert-detail-pdf.blade.php`)

**Design Elements** (copied from comprehensive analytics PDF):
- **Letterhead**: STI College Novaliches header with both logos
- **Font**: Arial, sans-serif, 10px base size
- **Colors**: 
  - Primary: #003087 (STI Blue)
  - Success: #28a745 (Green)
  - Danger: #ef4444 (Red)
  - Warning: #f97316 (Orange)
  - Info: #f59e0b (Yellow)

**PDF Structure**:

#### Page 1: Overview & Damaged Parts
1. **Letterhead** - STI logos and school info
2. **Report Title** - Issue, Location, Period, Generated date
3. **Severity Alert Box** - Color-coded based on severity
4. **Executive Summary** - 3 cards showing:
   - Total Repairs
   - Total Cost
   - Average Cost per Repair
5. **Damaged Parts Breakdown Table**:
   - Columns: Damaged Part, Times Fixed, Total Cost, Repair Tickets
   - Each row shows all tickets for that part
   - Ticket details: Number, Cost, Date Fixed
   - Footer with totals

#### Page 2: Monthly Breakdown & Recommendations
6. **Monthly Cost Breakdown Table** (Last 12 Months):
   - Columns: Month, Repairs, Cost
   - Sorted by most recent first
   - Footer with totals
7. **Recommendations Section**:
   - Severity-based recommendations
   - Critical: Replacement consideration, root cause analysis
   - Warning: Monitoring, procedure review
   - Info: Continue current maintenance
8. **Footer** - Copyright and system info

**Styling Features**:
- Sticky headers on tables
- Alternating row colors (#f8f9fa)
- Color-coded severity boxes
- Ticket items with left border accent
- Professional spacing and padding
- Page breaks between sections

## User Experience Flow

1. User clicks alert card in "Alerts & Notifications"
2. Enhanced modal opens with detailed breakdown
3. User clicks **"Export PDF"** button (red, bottom-left)
4. Loading modal appears: "Generating PDF..."
5. PDF opens in new browser tab
6. User can:
   - View PDF in browser
   - Download PDF
   - Print PDF
7. Loading modal closes automatically

## PDF Filename Format

```
alert-detail-{location}-{issue}-{date}.pdf
```

**Examples**:
- `alert-detail-Room-101-Aircon-2026-05-14.pdf`
- `alert-detail-Library-Chair-2026-05-14.pdf`

## Data Included in PDF

### From Database (`reports` table):
- `location` - Location of the issue
- `title` - Issue type (used as "issue")
- `damaged_part` - Specific part damaged
- `cost` - Repair cost
- `status` - Must be "Resolved"
- `resolved_at` - Date/time fixed
- `description` - Issue description
- `created_at` - Ticket creation date

### Calculated Metrics:
- Total repairs count
- Total cost sum
- Average cost per repair
- Repairs per damaged part
- Monthly repair counts
- Monthly cost totals
- Severity level (critical/warning/info)

## Severity Determination

```php
$recentCount = reports in last 3 months;

if ($recentCount >= 3) {
    $severity = 'critical';  // Red
    $alertTitle = 'High Frequency Issue';
} elseif ($recentCount >= 2) {
    $severity = 'warning';   // Orange
    $alertTitle = 'Recurring Issue';
} else {
    $severity = 'info';      // Yellow
    $alertTitle = 'Issue Detected';
}
```

## Recommendations Logic

### Critical (≥3 repairs in 3 months):
- Consider replacement vs continued repairs
- Conduct root cause analysis
- Schedule preventive maintenance
- Evaluate vendor performance

### Warning (2 repairs in 3 months):
- Monitor frequency over next 3 months
- Review maintenance procedures
- Consider upgrading components

### Info (1 repair in 3 months):
- Maintain current schedule
- Document procedures
- Track cost trends

## Design Consistency

### Matches Comprehensive Analytics PDF:
✅ Same letterhead with STI logos
✅ Same font family and sizes
✅ Same color scheme (#003087 primary)
✅ Same table styling (headers, borders, alternating rows)
✅ Same footer format
✅ Same summary card layout
✅ Same section title styling
✅ Same page break handling

### Unique to Alert Detail PDF:
- Severity alert box (color-coded)
- Ticket items with left border
- Recommendations section
- Grouped by damaged part

## Technical Details

### PDF Generation:
- Uses `barryvdh/laravel-dompdf` package
- Method: `\PDF::loadView()`
- Output: `stream()` - opens in browser

### Image Handling:
- Logos embedded as base64 data URIs
- Paths: `public/Campfix/Images/images.png` (STI)
- Paths: `public/Campfix/Images/logo.png` (CampFix)

### Date Formatting:
- Report date: `F d, Y h:i A` (May 14, 2026 02:30 PM)
- Month labels: `M Y` (May 2026)
- Ticket dates: `M d, Y h:i A` (May 10, 2026 02:30 PM)

### Currency Formatting:
- Format: `PHP 1,234.56`
- Function: `number_format($value, 2)`
- Always 2 decimal places

## Files Modified/Created

### Modified:
1. `resources/views/admin/analytics.blade.php` - Added export button and function (~30 lines)
2. `app/Http/Controllers/AdminController.php` - Added alertDetailPDF method (~110 lines)
3. `routes/web.php` - Added route (1 line)

### Created:
4. `resources/views/admin/alert-detail-pdf.blade.php` - PDF view template (~450 lines)

## Testing Checklist

### Frontend:
- [ ] Export PDF button appears in modal (red, bottom-left)
- [ ] Button shows PDF icon and "Export PDF" text
- [ ] Clicking button shows loading modal
- [ ] Loading modal says "Generating PDF..."
- [ ] Loading modal closes after ~1 second

### PDF Generation:
- [ ] PDF opens in new browser tab
- [ ] Filename format is correct
- [ ] Letterhead displays with both logos
- [ ] Report title shows correct issue and location
- [ ] Severity alert box has correct color
- [ ] Summary cards show correct numbers

### PDF Content:
- [ ] Damaged parts table displays all parts
- [ ] Ticket numbers are zero-padded (#0001)
- [ ] Costs show PHP symbol and 2 decimals
- [ ] Dates are in 12-hour format with AM/PM
- [ ] Monthly breakdown shows last 12 months
- [ ] Totals in footers are correct
- [ ] Recommendations match severity level

### PDF Styling:
- [ ] Tables have blue headers (#003087)
- [ ] Alternating row colors work
- [ ] Text is readable (not too small)
- [ ] Page breaks work correctly
- [ ] Footer appears on all pages
- [ ] No content overflow or cut-off

### Error Handling:
- [ ] Missing location parameter shows error
- [ ] Missing issue parameter shows error
- [ ] No data shows "No data available" message
- [ ] PDF generation errors are logged

## Browser Compatibility

Tested PDF viewing in:
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari (if available)

## Print Quality

- [ ] PDF prints correctly on A4/Letter paper
- [ ] All content fits within margins
- [ ] Colors print correctly (or grayscale acceptable)
- [ ] Text is legible when printed

## Performance

- [ ] PDF generates in < 3 seconds
- [ ] No memory issues with large datasets
- [ ] Multiple exports work without issues

## Security

- [ ] Only authenticated users can access
- [ ] Route protected by admin middleware
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities in PDF content

## Known Limitations

1. **Logo Display**: Requires logo files to exist at specified paths
2. **Data Limit**: Very large datasets (100+ tickets per part) may cause layout issues
3. **Browser PDF Viewer**: Some browsers may download instead of displaying inline
4. **Mobile**: PDF best viewed on desktop/tablet

## Future Enhancements

Potential improvements:
- Add charts/graphs to PDF
- Include photos of damaged parts
- Add cost comparison charts
- Include staff performance metrics
- Add filtering options (date range, status)
- Email PDF directly from modal
- Save PDF to server for archiving

## Troubleshooting

### PDF doesn't open:
- Check browser popup blocker
- Verify route is registered (`php artisan route:list`)
- Check Laravel logs for errors

### Logos don't appear:
- Verify logo files exist in `public/Campfix/Images/`
- Check file permissions
- Verify base64 encoding works

### Data missing:
- Verify reports exist with `status = 'Resolved'`
- Check `damaged_part` field has values
- Verify `resolved_at` timestamps exist

### Styling issues:
- Clear browser cache
- Check CSS in blade file
- Verify dompdf package is installed

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check browser console for JavaScript errors
3. Verify database has required data
4. Test with different alerts to isolate issue
