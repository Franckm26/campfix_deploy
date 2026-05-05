<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Trend Report - {{ $dateRange }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
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
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 16px;
            color: #666;
            font-weight: normal;
        }
        .date-range {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-muted {
            color: #6c757d;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-box h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #333;
        }
        .summary-item {
            display: inline-block;
            width: 24%;
            margin-bottom: 5px;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
        }
        .summary-value {
            color: #333;
            font-size: 13px;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
            background-color: #007bff;
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
        <h1>Monthly Trend - Detailed Report</h1>
        <h2>CampFix Analytics</h2>
    </div>

    <div class="date-range">
        <strong>Report Period:</strong> {{ $dateRange }}
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-item">
            <span class="summary-label">Total Months:</span>
            <span class="summary-value">6</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Peak Month:</span>
            <span class="summary-value">{{ $peakMonth ?? 'N/A' }} ({{ $peakCount }} repairs)</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Lowest Month:</span>
            <span class="summary-value">{{ $lowestMonth ?? 'N/A' }} ({{ $lowestCount }} repairs)</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Avg per Month:</span>
            <span class="summary-value">{{ number_format($avgPerMonth, 1) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Issue Type</th>
                <th class="text-center">Count</th>
                <th class="text-center">Trend</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trendData as $item)
            <tr>
                @if($item['is_first_row'])
                <td rowspan="{{ $item['rowspan'] }}"><strong>{{ $item['month_label'] }}</strong></td>
                @endif
                @if($item['issue_type'])
                <td>{{ $item['issue_type'] }}</td>
                <td class="text-center"><span class="badge">{{ $item['count'] }}</span></td>
                <td class="text-center">{{ $item['trend'] }}</td>
                @else
                <td colspan="3" class="text-center text-muted">No repairs recorded</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No data available for the selected period</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('F d, Y g:i A') }} | CampFix - Facility Management System
    </div>
</body>
</html>
