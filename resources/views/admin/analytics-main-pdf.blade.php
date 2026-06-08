<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Analytics Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
            padding-bottom: 80px;
        }
        
        /* Letterhead */
        .letterhead {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-bottom: 3px solid #003087;
            padding-bottom: 12px;
        }
        .letterhead-left-logo {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
        }
        .letterhead-left-logo img {
            width: 60px;
            height: 60px;
        }
        .letterhead-info {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 15px;
        }
        .letterhead-info .school-name {
            font-size: 16px;
            font-weight: 700;
            color: #003087;
            letter-spacing: 0.5px;
        }
        .letterhead-info .school-address {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
        .letterhead-info .school-tagline {
            font-size: 10px;
            color: #003087;
            font-style: italic;
            margin-top: 2px;
        }
        .letterhead-right-logo {
            display: table-cell;
            width: 70px;
            vertical-align: middle;
            text-align: right;
        }
        .letterhead-right-logo img {
            width: 60px;
            height: 60px;
        }
        
        .report-title {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .report-title h2 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #003087;
        }
        
        .report-title p {
            margin: 0;
            font-size: 11px;
            color: #666;
        }
        
        .summary-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .summary-section h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #003087;
            border-bottom: 2px solid #003087;
            padding-bottom: 5px;
        }
        
        .summary-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }
        
        .summary-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
        }
        
        .summary-value {
            font-size: 20px;
            font-weight: bold;
            color: #003087;
            display: block;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 10px;
            color: #666;
            display: block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        th {
            background: #003087;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
        }
        
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 9px;
            color: #666;
            background: white;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    {{-- Letterhead with Logos --}}
    @php 
        $stiLogoPath = public_path('Campfix/Images/images.png');
        $campfixLogoPath = public_path('Campfix/Images/logo.png');
    @endphp
    <div class="letterhead">
        <div class="letterhead-left-logo">
            @if(file_exists($stiLogoPath))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($stiLogoPath)) }}" alt="STI Logo">
            @endif
        </div>
        <div class="letterhead-info">
            <div class="school-name">STI COLLEGE NOVALICHES</div>
            <div class="school-address">STI Academic Center, Diamond Avenue corner Quirino Highway, San Bartolome, Novaliches, Quezon City</div>
            <div class="school-tagline">Campus Facility Management System - CampFix</div>
        </div>
        <div class="letterhead-right-logo">
            @if(file_exists($campfixLogoPath))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($campfixLogoPath)) }}" alt="CampFix Logo">
            @endif
        </div>
    </div>

    <!-- Report Title -->
    <div class="report-title">
        <h2>Analytics Report</h2>
        <p>Cost Tracking & Repair/Damage Analysis</p>
        <p><strong>Period:</strong> {{ $dateRange }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Summary Statistics</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ $totalConcerns }}</span>
                <span class="summary-label">Total Repairs/Damages</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ $uniqueLocations }}</span>
                <span class="summary-label">Unique Locations</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">PHP {{ number_format($totalCost, 2) }}</span>
                <span class="summary-label">Total Cost</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">PHP {{ number_format($avgCost, 2) }}</span>
                <span class="summary-label">Average Cost per Repair</span>
            </div>
        </div>
    </div>

    <!-- 1. Repairs Breakdown by Location -->
    <h3 style="margin-top: 20px; color: #003087; border-bottom: 2px solid #003087; padding-bottom: 5px;">1. Repairs Breakdown by Location</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Location</th>
                <th style="width: 15%;">Category</th>
                <th style="width: 10%;">Ticket #</th>
                <th style="width: 25%;">Issue</th>
                <th style="width: 15%;">Damaged Part</th>
                <th style="width: 10%;" class="text-right">Cost</th>
                <th style="width: 10%;">Date Fixed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locationStatsDetailed as $stat)
            <tr>
                <td>{{ $stat['location'] }}</td>
                <td>{{ $stat['category'] }}</td>
                <td class="text-center">#{{ str_pad($stat['id'], 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $stat['title'] }}</td>
                <td>{{ $stat['damaged_part'] }}</td>
                <td class="text-right">PHP {{ number_format($stat['cost'], 2) }}</td>
                <td>{{ $stat['resolved_at'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No data available</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 2. Cost Breakdown by Category -->
    <h3 style="margin-top: 20px; color: #003087; border-bottom: 2px solid #003087; padding-bottom: 5px;">2. Cost Breakdown by Category</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 35%;">Category</th>
                <th style="width: 15%;" class="text-center">Count</th>
                <th style="width: 20%;" class="text-right">Total Cost</th>
                <th style="width: 15%;" class="text-right">Avg Cost</th>
                <th style="width: 10%;" class="text-right">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($costByCategory as $index => $stat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $stat['category'] }}</td>
                <td class="text-center">{{ $stat['count'] }}</td>
                <td class="text-right">PHP {{ number_format($stat['total_cost'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($stat['avg_cost'], 2) }}</td>
                <td class="text-right">{{ number_format($stat['percentage'], 1) }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No data available</td>
            </tr>
            @endforelse
        </tbody>
        @if($costByCategory->count() > 0)
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="3" class="text-right" style="padding: 10px;">TOTAL:</td>
                <td class="text-right" style="padding: 10px;">PHP {{ number_format($costByCategory->sum('total_cost'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- 3. Status Distribution -->
    <h3 style="margin-top: 30px; color: #003087; border-bottom: 2px solid #003087; padding-bottom: 5px;">3. Status Distribution</h3>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 60%;">Status</th>
                <th style="width: 30%;" class="text-center">Count</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statusStats as $index => $stat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $stat->status }}</td>
                <td class="text-center">{{ $stat->count }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">No data available</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 4. Response Time Analysis -->
    <h3 style="margin-top: 20px; color: #003087; border-bottom: 2px solid #003087; padding-bottom: 5px;">4. Response Time Analysis</h3>
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ number_format($avgSubmittedToAssigned, 2) }}h</span>
                <span class="summary-label">Avg Submit → Assign</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ number_format($avgAssignedToResolved, 2) }}h</span>
                <span class="summary-label">Avg Assign → Resolve</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ number_format($avgTotalTime, 2) }}h</span>
                <span class="summary-label">Avg Total Time</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>© {{ date('Y') }} STI College Novaliches - CampFix Facility Management System</p>
    </div>
</body>
</html>
