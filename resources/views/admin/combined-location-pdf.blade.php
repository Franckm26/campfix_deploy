<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Combined Cost by Location Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
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
            width: 33.33%;
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
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
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
        <h2>Combined Cost by Location Report</h2>
        <p>Detailed Breakdown by Location with Ticket Information</p>
        <p><strong>Period:</strong> {{ $dateRange }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Summary Statistics</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ number_format($totalTickets) }}</span>
                <span class="summary-label">Total Tickets</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">PHP {{ number_format($totalCost, 2) }}</span>
                <span class="summary-label">Total Cost</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">PHP {{ number_format($avgCostPerTicket, 2) }}</span>
                <span class="summary-label">Average Cost per Ticket</span>
            </div>
        </div>
    </div>

    <!-- Combined Cost by Location Table -->
    <h3 style="margin-top: 20px; color: #003087; border-bottom: 2px solid #003087; padding-bottom: 5px;">Combined Cost by Location</h3>
    
    @if($combinedLocationStats->count() > 0)
    <div class="location-table-container"><table>
        <thead>
            <tr>
                <th style="width: 15%;">Location</th>
                <th style="width: 12%;">Issue Type</th>
                <th style="width: 25%;">Damaged Parts (Tickets)</th>
                <th style="width: 13%;">Date Fixed</th>
                <th style="width: 10%;" class="text-center">Total Tickets</th>
                <th style="width: 13%;" class="text-right">Total Cost</th>
                <th style="width: 12%;" class="text-right">Avg Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($combinedLocationStats as $stat)
            <tr>
                <td style="vertical-align: top;"><strong>{{ $stat['location'] }}</strong></td>
                <td style="vertical-align: top;">{{ $stat['issue_type'] }}</td>
                <td style="font-size: 10px;">
                    @foreach($stat['tickets'] as $ticket)
                        <div style="margin-bottom: 3px;">
                            <strong>{{ $ticket['ticket_number'] }}</strong>: {{ $ticket['damaged_part'] }} 
                            <span style="color: #28a745; font-weight: bold;">(PHP {{ number_format($ticket['cost'], 2) }})</span>
                        </div>
                    @endforeach
                </td>
                <td style="font-size: 10px; vertical-align: top;">
                    @foreach($stat['tickets'] as $ticket)
                        <div style="margin-bottom: 3px;">
                            {{ $ticket['date_fixed'] }}
                        </div>
                    @endforeach
                </td>
                <td class="text-center" style="vertical-align: top;">
                    <strong>{{ $stat['total_count'] }}</strong>
                </td>
                <td class="text-right" style="vertical-align: top;">
                    <strong>PHP {{ number_format($stat['total_cost'], 2) }}</strong>
                </td>
                <td class="text-right" style="vertical-align: top;">PHP {{ number_format($stat['avg_cost'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table></div>
    @else
    <div class="no-data">
        <p>No data available for the selected date range.</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>© {{ date('Y') }} STI College Novaliches - CampFix Facility Management System</p>
    </div>
</body>
        </html>\n<!-- Note: PDF is server-rendered; ensure pagination is respected in data export -->
