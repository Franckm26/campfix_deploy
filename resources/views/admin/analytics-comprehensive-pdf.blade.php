<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprehensive Analytics Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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
        
        .summary-item-3 {
            display: table-cell;
            width: 33.33%;
            text-align: center;
            padding: 10px;
        }
        
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #003087;
            display: block;
            margin-bottom: 5px;
        }
        
        .summary-label {
            font-size: 9px;
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
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
        }
        
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
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
        
        .section-title {
            margin-top: 25px;
            margin-bottom: 10px;
            color: #003087;
            border-bottom: 2px solid #003087;
            padding-bottom: 5px;
            font-size: 14px;
            font-weight: bold;
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
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #999;
            font-style: italic;
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        
        .status-resolved {
            background: #d4edda;
            color: #155724;
        }
        
        .status-pending {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-assigned, .status-in-progress {
            background: #fff3cd;
            color: #856404;
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
        <h2>Comprehensive Analytics Report</h2>
        <p>Complete Cost Tracking & Repair/Damage Analysis</p>
        <p><strong>Period:</strong> {{ $dateRange }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Executive Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ number_format($totalConcerns) }}</span>
                <span class="summary-label">Total Tickets</span>
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
                <span class="summary-label">Average Cost</span>
            </div>
        </div>
    </div>

    <!-- 1. Combined Cost by Location -->
    <h3 class="section-title">1. Combined Cost by Location (All Tickets)</h3>
    @if($combinedLocationStats && $combinedLocationStats->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Location</th>
                <th style="width: 35%;">Damaged Parts (Tickets)</th>
                <th style="width: 15%;">Date Fixed</th>
                <th style="width: 10%;" class="text-center">Total Tickets</th>
                <th style="width: 12%;" class="text-right">Total Cost</th>
                <th style="width: 13%;" class="text-right">Avg Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($combinedLocationStats as $stat)
            <tr>
                <td style="vertical-align: top;"><strong>{{ $stat['location'] }}</strong></td>
                <td style="font-size: 9px;">
                    @foreach($stat['tickets'] as $ticket)
                        <div style="margin-bottom: 2px;">
                            <strong>{{ $ticket['ticket_number'] }}</strong>: {{ $ticket['damaged_part'] }} 
                            <span style="color: #28a745; font-weight: bold;">(PHP {{ number_format($ticket['cost'], 2) }})</span>
                        </div>
                    @endforeach
                </td>
                <td style="font-size: 9px; vertical-align: top;">
                    @foreach($stat['tickets'] as $ticket)
                        <div style="margin-bottom: 2px;">{{ $ticket['date_fixed'] }}</div>
                    @endforeach
                </td>
                <td class="text-center" style="vertical-align: top;"><strong>{{ $stat['total_count'] }}</strong></td>
                <td class="text-right" style="vertical-align: top;"><strong>PHP {{ number_format($stat['total_cost'], 2) }}</strong></td>
                <td class="text-right" style="vertical-align: top;">PHP {{ number_format($stat['avg_cost'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="3" class="text-right" style="padding: 8px;">TOTAL:</td>
                <td class="text-center" style="padding: 8px;">{{ $combinedLocationStats->sum('total_count') }}</td>
                <td class="text-right" style="padding: 8px;">PHP {{ number_format($combinedLocationStats->sum('total_cost'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No location data available for the selected period.</div>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 2. Repairs Breakdown by Category -->
    <h3 class="section-title">2. Repairs Breakdown by Category</h3>
    @if($costByCategory && $costByCategory->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">#</th>
                <th style="width: 42%;">Category</th>
                <th style="width: 18%;" class="text-center">Count</th>
                <th style="width: 16%;" class="text-right">Total Cost</th>
                <th style="width: 16%;" class="text-right">Avg Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($costByCategory as $index => $stat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $stat['category'] }}</td>
                <td class="text-center">{{ $stat['count'] }}</td>
                <td class="text-right">PHP {{ number_format($stat['total_cost'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($stat['avg_cost'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="3" class="text-right" style="padding: 8px;">TOTAL:</td>
                <td class="text-right" style="padding: 8px;">PHP {{ number_format($costByCategory->sum('total_cost'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No category data available for the selected period.</div>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 3. Period Comparison -->
    <h3 class="section-title">3. Period Comparison (Yearly Breakdown)</h3>
    @if($periodData && $periodData->count() > 0)
    <div class="summary-section" style="margin-bottom: 15px;">
        <div class="summary-grid">
            <div class="summary-item-3">
                <span class="summary-value">{{ $periodData[$highestIdx]['label'] ?? 'N/A' }}</span>
                <span class="summary-label">Highest Cost Year<br>PHP {{ number_format($periodData[$highestIdx]['cost'] ?? 0, 2) }}</span>
            </div>
            <div class="summary-item-3">
                <span class="summary-value">{{ $periodData[$lowestIdx]['label'] ?? 'N/A' }}</span>
                <span class="summary-label">Lowest Cost Year<br>PHP {{ number_format($periodData[$lowestIdx]['cost'] ?? 0, 2) }}</span>
            </div>
            <div class="summary-item-3">
                <span class="summary-value">PHP {{ number_format($avgCostPerYear, 2) }}</span>
                <span class="summary-label">Average Cost/Year</span>
            </div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 25%;">Period</th>
                <th style="width: 15%;" class="text-center">Repairs</th>
                <th style="width: 20%;" class="text-right">Total Cost</th>
                <th style="width: 20%;" class="text-right">Avg per Repair</th>
                <th style="width: 20%;" class="text-center">Trend</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periodData as $item)
            <tr>
                <td><strong>{{ $item['label'] }}</strong></td>
                <td class="text-center">{{ $item['count'] }}</td>
                <td class="text-right">PHP {{ number_format($item['cost'], 2) }}</td>
                <td class="text-right">PHP {{ number_format($item['avg_per_repair'], 2) }}</td>
                <td class="text-center">
                    @if($item['trend'] === 'up')
                        <span style="color: #dc3545;">▲ Increase</span>
                    @elseif($item['trend'] === 'down')
                        <span style="color: #28a745;">▼ Decrease</span>
                    @else
                        <span style="color: #6c757d;">— Stable</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td style="padding: 8px;">TOTAL</td>
                <td class="text-center" style="padding: 8px;">{{ $periodData->sum('count') }}</td>
                <td class="text-right" style="padding: 8px;">PHP {{ number_format($periodData->sum('cost'), 2) }}</td>
                <td class="text-right" style="padding: 8px;">PHP {{ number_format($avgCostPerRepair, 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No period comparison data available.</div>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 4. Status Distribution -->
    <h3 class="section-title">4. Status Distribution</h3>
    @if($statusStats && $statusStats->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">#</th>
                <th style="width: 60%;">Status</th>
                <th style="width: 30%;" class="text-center">Count</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statusStats as $index => $stat)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $stat->status }}</td>
                <td class="text-center"><strong>{{ $stat->count }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="2" class="text-right" style="padding: 8px;">TOTAL TICKETS:</td>
                <td class="text-center" style="padding: 8px;">{{ $statusStats->sum('count') }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No status data available for the selected period.</div>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 5. Response Time Analysis -->
    <h3 class="section-title">5. Response Time Analysis</h3>
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item-3">
                <span class="summary-value">
                    @php
                        $totalSeconds = floor($avgSubmittedToAssigned * 3600);
                        $hours = floor($totalSeconds / 3600);
                        $minutes = floor(($totalSeconds % 3600) / 60);
                        $seconds = $totalSeconds % 60;
                        echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    @endphp
                </span>
                <span class="summary-label">Avg Submit → Assign Time</span>
            </div>
            <div class="summary-item-3">
                <span class="summary-value">
                    @php
                        $totalSeconds = floor($avgAssignedToResolved * 3600);
                        $hours = floor($totalSeconds / 3600);
                        $minutes = floor(($totalSeconds % 3600) / 60);
                        $seconds = $totalSeconds % 60;
                        echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    @endphp
                </span>
                <span class="summary-label">Avg Assign → Resolve Time</span>
            </div>
            <div class="summary-item-3">
                <span class="summary-value">
                    @php
                        $totalSeconds = floor($avgTotalTime * 3600);
                        $hours = floor($totalSeconds / 3600);
                        $minutes = floor(($totalSeconds % 3600) / 60);
                        $seconds = $totalSeconds % 60;
                        echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                    @endphp
                </span>
                <span class="summary-label">Avg Total Resolution Time</span>
            </div>
        </div>
    </div>

    @if($responseTimeDetails && $responseTimeDetails->count() > 0)
    <h4 style="margin-top: 15px; margin-bottom: 10px; font-size: 12px; color: #003087;">Detailed Response Time Records</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Ticket #</th>
                <th style="width: 10%;">Location</th>
                <th style="width: 12%;">Created</th>
                <th style="width: 12%;">Assigned</th>
                <th style="width: 12%;">Resolved</th>
                <th style="width: 10%;" class="text-center">Submit→Assign</th>
                <th style="width: 10%;" class="text-center">Assign→Resolve</th>
                <th style="width: 10%;" class="text-center">Total Time</th>
                <th style="width: 16%;">Staff</th>
            </tr>
        </thead>
        <tbody>
            @foreach($responseTimeDetails->take(50) as $detail)
            <tr>
                <td class="text-center"><strong>{{ $detail['ticket_number'] }}</strong></td>
                <td>{{ $detail['location'] }}</td>
                <td style="font-size: 8px;">{{ $detail['created_at'] }}</td>
                <td style="font-size: 8px;">{{ $detail['assigned_at'] }}</td>
                <td style="font-size: 8px;">{{ $detail['resolved_at'] }}</td>
                <td class="text-center">{{ $detail['submitted_to_assigned_formatted'] }}</td>
                <td class="text-center">{{ $detail['assigned_to_resolved_formatted'] }}</td>
                <td class="text-center"><strong>{{ $detail['total_time_formatted'] }}</strong></td>
                <td>{{ $detail['assigned_to_name'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($responseTimeDetails->count() > 50)
    <p style="text-align: center; font-style: italic; color: #666; margin-top: 10px;">
        Showing first 50 of {{ $responseTimeDetails->count() }} records
    </p>
    @endif
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>© {{ date('Y') }} STI College Novaliches - CampFix Facility Management System</p>
    </div>
</body>
</html>
