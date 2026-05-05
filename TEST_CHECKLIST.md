# Analytics Modal Testing Checklist

## Pre-Test Setup
- [ ] Clear browser cache (Ctrl + Shift + R or Cmd + Shift + R)
- [ ] Open browser DevTools (F12)
- [ ] Navigate to Console tab to watch for errors
- [ ] Navigate to: `http://127.0.0.1:8000/admin/analytics`

## Test 1: Repairs by Location Modal (Pie Chart)
- [ ] Click on "Repairs by Location" pie chart
- [ ] Modal opens and is full-screen (takes up ~98% of viewport)
- [ ] Pie chart is visible at the top (600px height)
- [ ] Chart shows different colored segments for each location
- [ ] Hover over chart segments shows tooltips
- [ ] Table below chart shows:
  - [ ] Rank column with badges
  - [ ] Location names
  - [ ] Total repairs count
  - [ ] Total cost in Philippine Pesos (₱)
  - [ ] Average cost per repair
  - [ ] Percentage progress bars
- [ ] Footer shows total repairs and total cost
- [ ] Close button (X) works
- [ ] Clicking outside modal closes it
- [ ] No console errors

## Test 2: Cost by Location Modal (Bar Chart)
- [ ] Click on "Cost by Location" bar chart
- [ ] Modal opens and is full-screen
- [ ] Bar chart is visible at the top (600px height)
- [ ] Chart shows bars for each location
- [ ] Hover over bars shows cost tooltips
- [ ] Three summary cards display:
  - [ ] Highest Cost Location (red card)
  - [ ] Lowest Cost Location (green card)
  - [ ] Average Cost (blue card)
- [ ] Table below shows:
  - [ ] Location names
  - [ ] Number of repairs
  - [ ] Total cost
  - [ ] Average per repair
  - [ ] Cost level badges (Very High/High/Medium/Low)
- [ ] Close button (X) works
- [ ] Clicking outside modal closes it
- [ ] No console errors

## Test 3: Status Distribution Modal (Doughnut Chart)
- [ ] Click on "Status Distribution" doughnut chart
- [ ] Modal opens and is full-screen
- [ ] Doughnut chart is visible at the top (600px height)
- [ ] Chart shows different colored segments for each status
- [ ] Hover over segments shows tooltips
- [ ] Table below shows:
  - [ ] Status badges (colored by status type)
  - [ ] Count for each status
  - [ ] Percentage of total
  - [ ] Progress bars showing percentage
- [ ] Footer shows total count (100%)
- [ ] Close button (X) works
- [ ] Clicking outside modal closes it
- [ ] No console errors

## Test 4: Monthly Trend Modal (Line Chart)
- [ ] Click on "Monthly Trend" line chart
- [ ] Modal opens and is full-screen
- [ ] Line chart is visible at the top (650px height)
- [ ] Chart shows multiple colored lines (one per issue type)
- [ ] Hover over points shows tooltips
- [ ] Four summary cards display:
  - [ ] Total Months (6)
  - [ ] Peak Month with count
  - [ ] Lowest Month with count
  - [ ] Average per Month
- [ ] Table below shows:
  - [ ] Month names
  - [ ] Issue types for each month
  - [ ] Count badges
  - [ ] Trend indicators (High/Medium/Low with icons)
- [ ] Close button (X) works
- [ ] Clicking outside modal closes it
- [ ] No console errors

## Browser Console Checks
- [ ] No 404 errors for `analytics-modals.js`
- [ ] No JavaScript errors
- [ ] No Chart.js errors
- [ ] File `analytics-modals.js` loads successfully (check Network tab)

## Visual Quality Checks
- [ ] Modal title has gradient background (purple)
- [ ] Modal title has icon
- [ ] Charts are crisp and clear
- [ ] Tables are properly formatted
- [ ] Colors are consistent with design
- [ ] Text is readable (proper font sizes)
- [ ] Spacing and padding look professional
- [ ] Modal is scrollable if content exceeds viewport

## Responsive Design (Optional)
- [ ] Test on smaller screen (resize browser window)
- [ ] Modal still displays properly
- [ ] Charts resize appropriately
- [ ] Tables remain readable

## Performance Checks
- [ ] Modals open quickly (< 1 second)
- [ ] Charts render smoothly
- [ ] No lag when hovering over charts
- [ ] Closing modal is instant

## Final Verification
- [ ] All 4 modals work correctly
- [ ] No console errors throughout testing
- [ ] User experience is smooth and professional
- [ ] Ready to commit and push changes

---

## If Any Test Fails

### Modal doesn't open:
1. Check browser console for errors
2. Verify `analytics-modals.js` is loaded (Network tab)
3. Check if onclick handlers are present in HTML

### Chart doesn't render:
1. Verify Chart.js is loaded
2. Check if global variables are set (`window.chartLocations`, etc.)
3. Look for Chart.js errors in console

### Modal is small (not full-screen):
1. Verify SweetAlert2 CSS is loaded
2. Check custom CSS in analytics.blade.php
3. Ensure old Bootstrap modals are not interfering

### Data is incorrect:
1. Check controller is passing correct data
2. Verify PHP variables in blade template
3. Check JSON encoding in global variables

---

## Success Criteria
✅ All 4 modals open full-screen
✅ All charts render inside modals
✅ All data displays correctly
✅ No console errors
✅ Professional appearance
✅ Smooth user experience
