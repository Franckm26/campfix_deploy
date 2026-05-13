# Analytics Modal - Combined Location & Category Details

## ✅ Updated Modal

The modal now shows **BOTH** location and category details in a single comprehensive view.

## Modal Layout

```
╔═══════════════════════════════════════════════════════════════╗
║  📊 Repairs by Location & Category - Detailed Report    [✕]  ║
╠═══════════════════════════════════════════════════════════════╣
║  [Export Filter Options]                                      ║
║                                                                ║
║  ┌──────────────────────┐  ┌──────────────────────┐         ║
║  │  📍 By Location      │  │  🏷️ By Category      │         ║
║  │  [Location Pie Chart]│  │  [Category Doughnut] │         ║
║  └──────────────────────┘  └──────────────────────┘         ║
║                                                                ║
║  📍 Location Breakdown                                        ║
║  ┌────────────────────────────────────────────────────────┐  ║
║  │ Item Fixed │ Location │ Total Repairs │ Total Cost    │  ║
║  ├────────────────────────────────────────────────────────┤  ║
║  │ Aircon     │ Room 101 │      5        │ ₱12,500.00   │  ║
║  │ Chair      │ Room 102 │      3        │ ₱2,400.00    │  ║
║  │ ...        │ ...      │     ...       │ ...          │  ║
║  └────────────────────────────────────────────────────────┘  ║
║                                                                ║
║  🏷️ Category Breakdown                                        ║
║  ┌────────────────────────────────────────────────────────┐  ║
║  │ Category │ Tickets │ Total Cost │ Avg Cost │ %        │  ║
║  ├────────────────────────────────────────────────────────┤  ║
║  │ Aircon   │   15    │ ₱35,000   │ ₱2,333   │ 35.2%   │  ║
║  │ Furniture│   10    │ ₱23,400   │ ₱2,340   │ 23.5%   │  ║
║  │ ...      │  ...    │ ...       │ ...      │ ...     │  ║
║  └────────────────────────────────────────────────────────┘  ║
╚═══════════════════════════════════════════════════════════════╝
```

## What's Included

### 1. **Two Charts Side-by-Side**
   - **Left**: Location Pie Chart
   - **Right**: Category Doughnut Chart
   - Both charts are interactive with detailed tooltips

### 2. **Location Breakdown Table**
   - Item Fixed
   - Location
   - Total Repairs
   - Total Cost
   - Sorted by repair count (most frequent first)

### 3. **Category Breakdown Table**
   - Category name
   - Total tickets
   - Total cost
   - Average cost per ticket
   - Percentage of total cost
   - Sorted by total cost (highest first)

### 4. **Export Filter Options**
   - YouTube-style filter for exporting
   - PDF export functionality

## Features

✅ **Comprehensive View**: See all location AND category data in one modal
✅ **Visual Charts**: Both pie and doughnut charts for easy comparison
✅ **Detailed Tables**: Complete breakdown with all metrics
✅ **Interactive Tooltips**: Hover over charts for detailed information
✅ **Sortable Data**: Tables sorted by most relevant metrics
✅ **Export Ready**: Filter and export options included

## Chart Tooltips

### Location Chart Tooltip Shows:
- Location name
- Number of repairs
- Total cost

### Category Chart Tooltip Shows:
- Category name
- Number of tickets
- Total cost
- Average cost
- Percentage of total

## How to Access

1. Click anywhere on the "Repairs by Location & Category" chart
2. Modal opens with complete details
3. Scroll to see all data
4. Use export filters if needed

## Benefits

| Before | After |
|--------|-------|
| Only location details | Location + Category details |
| Single chart in modal | Two charts side-by-side |
| One table | Two comprehensive tables |
| Limited insights | Complete analytics view |

## Technical Details

- **Modal Width**: 90% of screen
- **Charts**: Side-by-side in responsive grid
- **Tables**: Full-width with Bootstrap styling
- **Data Source**: Uses `window.categoryData` and location data
- **Sorting**: Automatic sorting by relevance

## Result

When you click the chart, you now get a **complete analytics report** showing both location distribution and category breakdown in a single, comprehensive modal!
