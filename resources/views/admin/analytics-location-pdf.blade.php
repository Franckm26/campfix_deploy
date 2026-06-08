<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Location Report - {{ $dateRange }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 15px;
        }
        /* Letterhead */
        .letterhead {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 3px solid #003087;
            padding-bottom: 10px;
        }
        .letterhead-left-logo {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
        }
        .letterhead-left-logo img {
            width: 50px;
            height: 50px;
        }
        .letterhead-info {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            padding: 0 10px;
        }
        .letterhead-info .school-name {
            font-size: 14px;
            font-weight: 700;
            color: #003087;
            letter-spacing: 0.5px;
        }
        .letterhead-info .school-address {
            font-size: 8px;
            color: #555;
            margin-top: 2px;
        }
        .letterhead-info .school-tagline {
            font-size: 9px;
            color: #003087;
            font-style: italic;
            margin-top: 2px;
        }
        .letterhead-right-logo {
            display: table-cell;
            width: 60px;
            vertical-align: middle;
            text-align: right;
        }
        .letterhead-right-logo img {
            width: 50px;
            height: 50px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header h2 {
            margin: 3px 0 0 0;
            font-size: 12px;
            color: #666;
            font-weight: normal;
        }
        .date-range {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
            font-size: 8px;
        }
        td {
            padding: 5px 4px;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-end {
            text-align: right;
        }
        .footer-row {
            background-color: #e9ecef !important;
            font-weight: bold;
            font-size: 9px;
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
        .summary-box {
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .summary-box h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            color: #333;
        }
        .summary-item {
            display: inline-block;
            width: 23%;
            margin-bottom: 3px;
            font-size: 9px;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
        }
        .summary-value {
            color: #333;
            font-size: 10px;
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

    <div class="header">
        <h1>Repairs by Location - Detailed Report</h1>
        <h2>CampFix Analytics</h2>
    </div>

    <div class="date-range">
        <strong>Report Period:</strong> {{ $dateRange }}
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-item">
            <span class="summary-label">Total Locations:</span>
            <span class="summary-value">{{ $uniqueLocations }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Categories:</span>
            <span class="summary-value">{{ $uniqueCategories }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Tickets:</span>
            <span class="summary-value">{{ $totalRepairs }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Cost:</span>
            <span class="summary-value">PHP {{ number_format($totalCost, 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th>Category</th>
                <th class="text-center">Ticket #</th>
                <th>Issue</th>
                <th>Damaged Part</th>
                <th class="text-end">Cost</th>
                <th class="text-center">Date Fixed</th>
            </tr>
        </thead>
        <tbody>
            @forelse($locationStatsDetailed as $stat)
            <tr>
                <td><strong>{{ $stat['location'] }}</strong></td>
                <td>{{ $stat['category'] }}</td>
                <td class="text-center">#{{ str_pad($stat['id'], 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $stat['title'] }}</td>
                <td>{{ $stat['damaged_part'] }}</td>
                <td class="text-end">PHP {{ number_format($stat['cost'], 2) }}</td>
                <td class="text-center">{{ $stat['resolved_at'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No data available for the selected period</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td colspan="5"><strong>TOTAL ({{ $totalRepairs }} tickets)</strong></td>
                <td class="text-end"><strong>PHP {{ number_format($totalCost, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ now()->format('F d, Y g:i A') }} | CampFix - Facility Management System
    </div>
</body>
</html>
