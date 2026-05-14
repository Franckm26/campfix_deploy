<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Alert Detail Report - {{ $issue }} at {{ $location }}</title>
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
        
        /* Severity Alert Box */
        .alert-box {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            border-left: 5px solid;
        }
        
        .alert-critical {
            background: #fef2f2;
            border-left-color: #ef4444;
        }
        
        .alert-warning {
            background: #fff7ed;
            border-left-color: #f97316;
        }
        
        .alert-info {
            background: #fffbeb;
            border-left-color: #f59e0b;
        }
        
        .alert-box h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: bold;
        }
        
        .alert-box p {
            margin: 0;
            font-size: 10px;
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
        
        .ticket-item {
            padding: 4px 8px;
            margin-bottom: 3px;
            background: #f8f9fa;
            border-radius: 3px;
            border-left: 3px solid #0d6efd;
            font-size: 8px;
        }
        
        .ticket-number {
            font-weight: bold;
            color: #003087;
        }
        
        .ticket-cost {
            color: #28a745;
            font-weight: bold;
        }
        
        .ticket-date {
            color: #666;
            font-size: 7px;
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
        <h2>Alert Detail Report</h2>
        <p><strong>Issue:</strong> {{ $issue }} | <strong>Location:</strong> {{ $location }}</p>
        <p><strong>Period:</strong> {{ $dateRange }}</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <!-- Severity Alert Box -->
    <div class="alert-box alert-{{ $severity }}">
        <h3>{{ $alertTitle }}</h3>
        <p>This issue has been flagged for attention based on repair frequency and cost analysis.</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3>Executive Summary</h3>
        <div class="summary-grid">
            <div class="summary-item-3">
                <span class="summary-value">{{ number_format($totalRepairs) }}</span>
                <span class="summary-label">Total Repairs</span>
            </div>
            <div class="summary-item-3">
                <span class="summary-value">PHP {{ number_format($totalCost, 2) }}</span>
                <span class="summary-label">Total Cost</span>
            </div>
            <div class="summary-item-3">
                <span class="summary-value">PHP {{ number_format($avgCostPerRepair, 2) }}</span>
                <span class="summary-label">Average Cost per Repair</span>
            </div>
        </div>
    </div>

    <!-- 1. Damaged Parts Breakdown -->
    <h3 class="section-title">1. Damaged Parts Breakdown</h3>
    @if(count($partBreakdown) > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Damaged Part</th>
                <th style="width: 10%;" class="text-center">Times Fixed</th>
                <th style="width: 15%;" class="text-right">Total Cost</th>
                <th style="width: 55%;">Repair Tickets</th>
            </tr>
        </thead>
        <tbody>
            @foreach($partBreakdown as $part)
            <tr>
                <td style="vertical-align: top;"><strong>{{ $part['part_name'] }}</strong></td>
                <td class="text-center" style="vertical-align: top;">
                    <span style="background: #0d6efd; color: white; padding: 2px 6px; border-radius: 3px; font-weight: bold;">
                        {{ $part['count'] }}
                    </span>
                </td>
                <td class="text-right" style="vertical-align: top;"><strong>PHP {{ number_format($part['total_cost'], 2) }}</strong></td>
                <td style="vertical-align: top;">
                    @foreach($part['tickets'] as $ticket)
                        <div class="ticket-item">
                            <span class="ticket-number">{{ $ticket['ticket_number'] }}</span> - 
                            <span class="ticket-cost">PHP {{ number_format($ticket['cost'], 2) }}</span>
                            <br>
                            <span class="ticket-date">📅 {{ $ticket['date_fixed'] }}</span>
                        </div>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td style="padding: 8px;">TOTAL</td>
                <td class="text-center" style="padding: 8px;">{{ array_sum(array_column($partBreakdown, 'count')) }}</td>
                <td class="text-right" style="padding: 8px;">PHP {{ number_format(array_sum(array_column($partBreakdown, 'total_cost')), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No damaged parts data available for this alert.</div>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- 2. Monthly Cost Breakdown -->
    <h3 class="section-title">2. Monthly Cost Breakdown (Last 12 Months)</h3>
    @if($monthlyCosts && $monthlyCosts->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width: 40%;">Month</th>
                <th style="width: 30%;" class="text-center">Repairs</th>
                <th style="width: 30%;" class="text-right">Cost</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalMonthlyCount = 0;
                $totalMonthlyCost = 0;
            @endphp
            @foreach($monthlyCosts as $monthly)
                @php
                    $totalMonthlyCount += $monthly['count'];
                    $totalMonthlyCost += $monthly['cost'];
                @endphp
                <tr>
                    <td><strong>{{ $monthly['month'] }}</strong></td>
                    <td class="text-center">{{ $monthly['count'] }}</td>
                    <td class="text-right">PHP {{ number_format($monthly['cost'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td style="padding: 8px;">TOTAL</td>
                <td class="text-center" style="padding: 8px;">{{ $totalMonthlyCount }}</td>
                <td class="text-right" style="padding: 8px;">PHP {{ number_format($totalMonthlyCost, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @else
    <div class="no-data">No monthly data available for the last 12 months.</div>
    @endif

    <!-- Recommendations Section -->
    <div class="summary-section" style="margin-top: 30px;">
        <h3>Recommendations</h3>
        <div style="font-size: 10px; line-height: 1.6;">
            @if($severity === 'critical')
                <p><strong>⚠️ Critical Issue Detected:</strong> This issue has occurred {{ $totalRepairs }} times with a total cost of PHP {{ number_format($totalCost, 2) }}.</p>
                <p><strong>Recommended Actions:</strong></p>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>Consider replacement instead of continued repairs if cost exceeds asset value</li>
                    <li>Conduct root cause analysis to prevent recurring issues</li>
                    <li>Schedule preventive maintenance to reduce failure frequency</li>
                    <li>Evaluate vendor/contractor performance for quality of repairs</li>
                </ul>
            @elseif($severity === 'warning')
                <p><strong>⚠️ Recurring Issue:</strong> This issue requires attention to prevent escalation.</p>
                <p><strong>Recommended Actions:</strong></p>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>Monitor repair frequency over the next 3 months</li>
                    <li>Review maintenance procedures for this asset type</li>
                    <li>Consider upgrading to more durable components</li>
                </ul>
            @else
                <p><strong>ℹ️ Issue Detected:</strong> Continue monitoring this issue for trends.</p>
                <p><strong>Recommended Actions:</strong></p>
                <ul style="margin: 5px 0; padding-left: 20px;">
                    <li>Maintain current maintenance schedule</li>
                    <li>Document repair procedures for future reference</li>
                    <li>Track costs to identify budget trends</li>
                </ul>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>© {{ date('Y') }} STI College Novaliches - CampFix Facility Management System</p>
    </div>
</body>
</html>
