<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Period Comparison Report - {{ $dateRange }}</title>
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
        .text-end {
            text-align: right;
        }
        .footer-row {
            background-color: #e9ecef !important;
            font-weight: bold;
            font-size: 12px;
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
            width: 32%;
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
        .trend-icon {
            font-size: 12px;
        }
        .trend-up { color: #dc3545; }
        .trend-down { color: #28a745; }
        .trend-neutral { color: #6c757d; }
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
        <h1>Period Comparison - Detailed Report</h1>
        <h2>CampFix Analytics</h2>
    </div>

    <div class="date-range">
        <strong>Report Period:</strong> {{ $dateRange }}
    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-item">
            <span class="summary-label">Highest Cost Month:</span>
            <span class="summary-value">{{ $monthLabels[$highestIdx] ?? 'N/A' }} - PHP {{ number_format($monthCosts[$highestIdx] ?? 0, 2) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Lowest Cost Month:</span>
            <span class="summary-value">{{ $monthLabels[$lowestIdx] ?? 'N/A' }} - PHP {{ number_format($monthCosts[$lowestIdx] ?? 0, 2) }}</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Average Cost/Month:</span>
            <span class="summary-value">PHP {{ number_format($avgCostPerMonth, 2) }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Period</th>
                <th class="text-center">Repairs</th>
                <th class="text-end">Total Cost</th>
                <th class="text-end">Avg per Repair</th>
                <th class="text-center">% of Total</th>
                <th class="text-center">Trend</th>
            </tr>
        </thead>
        <tbody>
            @forelse($periodData as $item)
            <tr>
                <td><strong>{{ $item['label'] }}</strong></td>
                <td class="text-center">{{ $item['count'] }}</td>
                <td class="text-end">PHP {{ number_format($item['cost'], 2) }}</td>
                <td class="text-end">PHP {{ number_format($item['avg_per_repair'], 2) }}</td>
                <td class="text-center">{{ number_format($item['percent'], 1) }}%</td>
                <td class="text-center">
                    @if($item['trend'] === 'up')
                        <span class="trend-icon trend-up">▲</span>
                    @elseif($item['trend'] === 'down')
                        <span class="trend-icon trend-down">▼</span>
                    @elseif($item['trend'] === 'neutral')
                        <span class="trend-icon trend-neutral">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No data available for the selected period</td>
            </tr>
            @endforelse
            <tr class="footer-row">
                <td><strong>TOTAL</strong></td>
                <td class="text-center"><strong>{{ $totalCount }}</strong></td>
                <td class="text-end"><strong>PHP {{ number_format($totalCost, 2) }}</strong></td>
                <td class="text-end"><strong>PHP {{ number_format($avgCostPerRepair, 2) }}</strong></td>
                <td class="text-center"><strong>100%</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>

    {{-- Repair Breakdown for Each Period --}}
    @foreach($periodData as $item)
        @if($item['count'] > 0 && isset($item['repairs']) && count($item['repairs']) > 0)
        <div style="page-break-inside: avoid; margin-top: 30px;">
            <h3 style="font-size: 14px; color: #333; margin-bottom: 10px; border-bottom: 2px solid #003087; padding-bottom: 5px;">
                {{ $item['label'] }} - Repair Breakdown ({{ $item['count'] }} repairs)
            </h3>
            <table style="font-size: 10px;">
                <thead>
                    <tr>
                        <th style="width: 8%;">Ticket</th>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 25%;">Issue</th>
                        <th style="width: 15%;">Location</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 18%;">Damaged Part</th>
                        <th class="text-end" style="width: 12%;">Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item['repairs'] as $repair)
                    <tr>
                        <td>{{ $repair['ticket_number'] }}</td>
                        <td>{{ $repair['date'] }}</td>
                        <td>{{ $repair['title'] }}</td>
                        <td>{{ $repair['location'] }}</td>
                        <td class="text-center">
                            @php
                                $statusColors = [
                                    'Pending' => '#ffc107',
                                    'Assigned' => '#17a2b8',
                                    'In Progress' => '#007bff',
                                    'Resolved' => '#28a745'
                                ];
                                $bgColor = $statusColors[$repair['status']] ?? '#6c757d';
                            @endphp
                            <span style="background-color: {{ $bgColor }}; color: white; padding: 2px 6px; border-radius: 3px; font-size: 9px;">
                                {{ $repair['status'] }}
                            </span>
                        </td>
                        <td>{{ $repair['damaged_part'] }}</td>
                        <td class="text-end">{{ $repair['cost'] > 0 ? 'PHP ' . number_format($repair['cost'], 2) : 'N/A' }}</td>
                    </tr>
                    @endforeach
                    <tr class="footer-row">
                        <td colspan="6" class="text-end"><strong>Period Total:</strong></td>
                        <td class="text-end"><strong>PHP {{ number_format($item['cost'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    @endforeach

    <div class="footer">
        Generated on {{ now()->format('F d, Y g:i A') }} | CampFix - Facility Management System
    </div>
</body>
</html>
