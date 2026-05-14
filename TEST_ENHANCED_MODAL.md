# Testing Guide - Enhanced Alert Modal

## Prerequisites
- Laravel application running locally (`php artisan serve`)
- Database populated with resolved reports that have:
  - `location` field filled
  - `title` field filled (issue type)
  - `damaged_part` field filled (optional but recommended for best display)
  - `status` = 'Resolved'
  - `resolved_at` timestamp
  - `cost` values

## Test Steps

### 1. Access Analytics Page
```
Navigate to: http://127.0.0.1:8000/admin/analytics
```

### 2. Locate Alerts & Notifications Section
- Scroll down to find "Alerts & Notifications" card
- Should see alert cards with colored borders (red, orange, or yellow)
- Each alert shows:
  - Alert title (e.g., "High Frequency Issue")
  - Issue type and location (e.g., "Aircon on Room 101")
  - Time ago (e.g., "2 days ago")

### 3. Click an Alert Card
**Expected Behavior:**
1. Loading modal appears immediately with:
   - Title: "Loading..."
   - Message: "Fetching detailed breakdown..."
   - Spinning loader icon

2. After 1-2 seconds, modal transitions to full content showing:
   - Alert severity indicator at top
   - Location name
   - Three summary cards (Total Repairs, Total Cost, Avg Cost/Repair)

### 4. Verify Damaged Parts Breakdown

**Check Table Structure:**
- [ ] Table has 4 columns: Damaged Part, Times Fixed, Total Cost, Tickets
- [ ] Header is sticky (stays visible when scrolling)
- [ ] Parts are sorted by frequency (most fixed first)

**Check Each Row:**
- [ ] Part name displays correctly (or "Not Specified" if null)
- [ ] Times Fixed shows count with blue badge
- [ ] Total Cost shows ₱ symbol with 2 decimals
- [ ] Tickets section shows expandable list

**Check Ticket Details:**
- [ ] Ticket number format: #0001, #0123 (4 digits, zero-padded)
- [ ] Cost per ticket: ₱3,000.00 (2 decimals)
- [ ] Date format: "May 10, 2026 02:30 PM" (12-hour with AM/PM)
- [ ] Calendar icon (📅) appears before date

**Test Scrolling:**
- [ ] If more than ~5 parts, table scrolls vertically
- [ ] Header stays fixed at top while scrolling
- [ ] Scroll is smooth and contained within table

### 5. Verify Monthly Cost Breakdown

**Check Table Structure:**
- [ ] Table has 3 columns: Month, Repairs, Cost
- [ ] Header is sticky
- [ ] Footer is sticky (shows totals)
- [ ] Shows last 12 months of data

**Check Data:**
- [ ] Month format: "May 2026", "Apr 2026" (3-letter month + year)
- [ ] Repair count is numeric
- [ ] Cost shows ₱ symbol with 2 decimals
- [ ] Footer totals match sum of all rows

**Test Scrolling:**
- [ ] If more than ~6 months, table scrolls vertically
- [ ] Header and footer stay fixed while scrolling

### 6. Test Modal Interactions

**Size:**
- [ ] Modal is very wide (95% of screen width)
- [ ] Content is readable and not cramped
- [ ] Tables don't overflow horizontally

**Closing:**
- [ ] Click "Close" button → Modal dismisses
- [ ] Click outside modal (on backdrop) → Modal dismisses
- [ ] Press ESC key → Modal dismisses

**Responsiveness:**
- [ ] Resize browser window → Modal adjusts width
- [ ] Summary cards stack properly on smaller screens

### 7. Test Error Handling

**Simulate Error:**
1. Open browser DevTools (F12)
2. Go to Network tab
3. Click an alert card
4. Cancel the network request before it completes

**Expected Behavior:**
- [ ] Error modal appears with:
  - Red error icon
  - Title: "Error"
  - Message: "Failed to load alert details: [error message]"
  - OK button to dismiss

### 8. Test with Different Data Scenarios

**Scenario A: Alert with Multiple Parts**
- [ ] Click alert for location with 5+ different damaged parts
- [ ] Verify all parts display correctly
- [ ] Verify scrolling works

**Scenario B: Alert with Single Part**
- [ ] Click alert with only 1 damaged part
- [ ] Verify display is clean (no scrollbars needed)
- [ ] Verify ticket list shows all repairs for that part

**Scenario C: Alert with Missing Damaged Parts**
- [ ] Click alert where some reports have null `damaged_part`
- [ ] Verify "Not Specified" appears as part name
- [ ] Verify tickets still display correctly

**Scenario D: Alert with Many Tickets**
- [ ] Click alert with 10+ tickets for a single part
- [ ] Verify ticket list scrolls within its container
- [ ] Verify all tickets are accessible

### 9. Verify Data Accuracy

**Cross-check with Database:**
1. Note the location and issue from an alert
2. Run SQL query:
```sql
SELECT damaged_part, COUNT(*) as count, SUM(cost) as total_cost
FROM reports
WHERE location = 'Room 101' 
  AND title = 'Aircon'
  AND status = 'Resolved'
GROUP BY damaged_part
ORDER BY count DESC;
```
3. Compare results with modal display
- [ ] Counts match
- [ ] Costs match
- [ ] Parts match

### 10. Performance Testing

**Load Time:**
- [ ] Modal opens within 2 seconds
- [ ] No visible lag or freezing
- [ ] Smooth transitions

**Multiple Opens:**
- [ ] Click different alerts in sequence
- [ ] Each opens correctly with fresh data
- [ ] No data from previous alert persists

## Common Issues & Solutions

### Issue: Modal doesn't open
**Check:**
- Browser console for JavaScript errors
- Network tab for failed AJAX requests
- Verify SweetAlert2 is loaded (check page source)

### Issue: "No data available" message
**Check:**
- Database has resolved reports for that location/issue
- Reports have `resolved_at` timestamp
- Reports have `status = 'Resolved'`

### Issue: Damaged parts show as "Not Specified"
**Check:**
- Database `damaged_part` column has values
- Reports were created with damaged_part filled in

### Issue: Dates show incorrectly
**Check:**
- `resolved_at` column has valid timestamps
- Server timezone is configured correctly
- Carbon date formatting is working

### Issue: Costs show as ₱0.00
**Check:**
- Reports have `cost` values in database
- Cost column is not null
- Cost values are numeric (not strings)

## Browser Compatibility

Test in multiple browsers:
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari (if available)

## Mobile Testing (Optional)

If testing on mobile:
- [ ] Modal is responsive
- [ ] Tables scroll properly
- [ ] Touch interactions work
- [ ] Text is readable

## Success Criteria

✅ All checkboxes above are checked
✅ No console errors
✅ Data matches database
✅ Modal is visually appealing
✅ User experience is smooth

## Report Issues

If you find any issues, note:
1. What you were doing
2. What you expected to happen
3. What actually happened
4. Browser console errors (if any)
5. Screenshot (if visual issue)
