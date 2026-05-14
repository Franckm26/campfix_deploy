# Enhanced Alert Modal - Visual Preview

## Modal Layout (95% Screen Width)

```
┌─────────────────────────────────────────────────────────────────────────────────┐
│  ✖                        🔧 Aircon - Issue Details                             │
├─────────────────────────────────────────────────────────────────────────────────┤
│                                                                                   │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │  ⚠️  High Frequency Issue                                                │   │
│  │      📍 Room 101                                                          │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│                                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐             │
│  │  TOTAL REPAIRS   │  │   TOTAL COST     │  │  AVG COST/REPAIR │             │
│  │       15         │  │   ₱45,000.00     │  │    ₱3,000.00     │             │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘             │
│                                                                                   │
│  🔧 Damaged Parts Breakdown                                                      │
│  ─────────────────────────────────────────────────────────────────────────      │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │ Damaged Part    │ Times Fixed │ Total Cost  │ Tickets                   │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │ Compressor      │      5      │ ₱15,000.00  │ ┌─────────────────────┐  │   │
│  │                 │             │             │ │ #0123 - ₱3,000.00   │  │   │
│  │                 │             │             │ │ 📅 May 10, 2026     │  │   │
│  │                 │             │             │ │    02:30 PM         │  │   │
│  │                 │             │             │ ├─────────────────────┤  │   │
│  │                 │             │             │ │ #0145 - ₱3,000.00   │  │   │
│  │                 │             │             │ │ 📅 Apr 15, 2026     │  │   │
│  │                 │             │             │ │    10:15 AM         │  │   │
│  │                 │             │             │ └─────────────────────┘  │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │ Cooling Fan     │      3      │  ₱9,000.00  │ ┌─────────────────────┐  │   │
│  │                 │             │             │ │ #0156 - ₱3,000.00   │  │   │
│  │                 │             │             │ │ 📅 May 05, 2026     │  │   │
│  │                 │             │             │ │    03:45 PM         │  │   │
│  │                 │             │             │ └─────────────────────┘  │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │ Thermostat      │      2      │  ₱6,000.00  │ ┌─────────────────────┐  │   │
│  │                 │             │             │ │ #0178 - ₱3,000.00   │  │   │
│  │                 │             │             │ │ 📅 Apr 20, 2026     │  │   │
│  │                 │             │             │ │    11:30 AM         │  │   │
│  │                 │             │             │ └─────────────────────┘  │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│  ↕️ Scrollable if more parts                                                     │
│                                                                                   │
│  📅 Monthly Cost Breakdown (Last 12 Months)                                      │
│  ─────────────────────────────────────────────────────────────────────────      │
│  ┌─────────────────────────────────────────────────────────────────────────┐   │
│  │ Month      │ Repairs │    Cost     │                                     │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │ May 2026   │    3    │ ₱9,000.00   │                                     │   │
│  │ Apr 2026   │    5    │ ₱15,000.00  │                                     │   │
│  │ Mar 2026   │    2    │ ₱6,000.00   │                                     │   │
│  │ Feb 2026   │    1    │ ₱3,000.00   │                                     │   │
│  │ Jan 2026   │    4    │ ₱12,000.00  │                                     │   │
│  ├─────────────────────────────────────────────────────────────────────────┤   │
│  │ Total      │   15    │ ₱45,000.00  │                                     │   │
│  └─────────────────────────────────────────────────────────────────────────┘   │
│  ↕️ Scrollable if more months                                                    │
│                                                                                   │
│                                          [ ✖ Close ]                              │
└─────────────────────────────────────────────────────────────────────────────────┘
```

## Key Features Illustrated

### 1. **Alert Header with Severity**
- Color-coded background (red for critical, orange for warning, yellow for info)
- Icon indicator (⚠️)
- Location display with map marker icon

### 2. **Summary Cards**
- Three equal-width cards showing key metrics
- Large, bold numbers for easy reading
- Color-coded (blue for count, red for cost, green for average)

### 3. **Damaged Parts Breakdown**
- **Sortable by frequency** (most fixed parts first)
- **Expandable ticket lists** showing:
  - Ticket number with # prefix
  - Individual repair cost
  - Date fixed with calendar icon
  - Time in 12-hour format (AM/PM)
- **Scrollable container** (max 400px height)
- **Sticky header** stays visible while scrolling

### 4. **Monthly Breakdown**
- Last 12 months of data
- Shows repair count and cost per month
- **Sticky header and footer**
- **Footer shows totals**
- **Scrollable** (max 300px height)

## Color Scheme

### Severity Levels
- **Critical**: Red (#ef4444) - 3+ repairs in last 3 months
- **Warning**: Orange (#f97316) - 2 repairs in last 3 months  
- **Info**: Yellow (#f59e0b) - 1 repair in last 3 months

### UI Elements
- Primary Blue: #0d6efd (badges, counts)
- Success Green: #198754 (averages)
- Danger Red: #dc3545 (costs)
- Gray backgrounds: #f8f9fa (cards, table headers)
- Border colors: #e2e8f0 (section dividers)

## Responsive Behavior

- **Desktop**: Full 95% width, side-by-side layout
- **Tablet**: Maintains layout, slightly reduced padding
- **Mobile**: Stacks elements vertically (handled by SweetAlert2)

## Interaction

1. **Click alert card** → Modal opens with loading spinner
2. **Data loads** → Smooth transition to full content
3. **Scroll tables** → Headers stay fixed at top
4. **Click Close or outside** → Modal dismisses
5. **Error handling** → Shows error message if AJAX fails

## Data Accuracy

- All costs show **2 decimal places** (₱3,000.00)
- Dates use **12-hour format** with AM/PM
- Ticket numbers **zero-padded** to 4 digits (#0001, #0123)
- **"Not Specified"** shown for missing damaged_part values
- **Empty states** handled gracefully ("No data available")
