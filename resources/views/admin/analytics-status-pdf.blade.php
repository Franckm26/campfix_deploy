<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Status Report - {{ $dateRange }}</title>
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
        .footer-row {
            background-color: #e9ecef !important;
            font-weight: bold;
            font-size: 12px;
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
            width: 48%;
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
        }
        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .badge-danger { background-color: #dc3545; }
        .badge-secondary { background-color: #6c757d; }
        .progress-bar {
            background-color: #e9ecef;
            height: 20px;
            border-radius: 3px;
            position: relative;
        }
        .progress-fill {
            height: 100%;
            border-radius: 3px;
            text-align: center;
            line-height: 20px;
            color: white;
            font-weight: bold;
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
        <h1>Status Distribution - Detailed Report</h1>
        <h2>CampFix Analytics</h2>
    </div>

    <div class="date-range">
        <strong>Report Period:</strong> {{ $dateRange }}
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-item">
            <span class="summary-label">Total Reports:</span>
            <span class="summary-value">{{ $totalCount }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Unique Statuses:</span>
            <span class="summary-value">{{ count($statusData) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="text-center">Count</th>
                <th class="text-center">Percentage</th>
            </tr>
        </thead>
        <tbody>
            @forelse($statusData as $item)
            <tr>
                <td>
                    <span class="badge badge-{{ $item['badge_class'] }}">{{ $item['status'] }}</span>
                </td>
                <td class="text-center"><strong>{{ $item['count'] }}</strong></td>
                <td class="text-center">{{ $item['percentage'] }}%</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">No data available for the selected period</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ $totalCount }}</strong></td>
                <td class="text-center"><strong>100%</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on {{ now()->format('F d, Y g:i A') }} | CampFix - Facility Management System
    </div>
</body>
</html>
