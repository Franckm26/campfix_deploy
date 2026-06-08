<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $periodLabel }} - Repair Breakdown</title>
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
        
        tfoot tr {
            background: #e9ecef;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }
        
        .badge-warning { background: #ffc107; color: #000; }
        .badge-info { background: #17a2b8; }
        .badge-primary { background: #007bff; }
        .badge-success { background: #28a745; }
        
        .footer {
            margin-top: 50px;
            padding: 15px 20px;
            border-top: 2px solid #003087;
            text-align: center;
            font-size: 9px;
            color: #666;
            background: #f8f9fa;
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
        <h2>{{ $periodLabel }} - Repair Breakdown</h2>
        <p>Detailed Repair and Damage Report</p>
        <p><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</p>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ $totalRepairs }}</span>
                <span class="summary-label">Total Repairs</span>
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

    <!-- Repairs Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 10%;">Ticket</th>
                <th style="width: 18%;">Issue</th>
                <th style="width: 15%;">Location</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 15%;">Damaged Part</th>
                <th style="width: 13%;" class="text-right">Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($repairs as $index => $repair)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($repair->created_at)->format('M d, Y') }}</td>
                <td class="text-center">#{{ str_pad($repair->id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $repair->title ?: 'N/A' }}</td>
                <td>{{ $repair->location ?: 'N/A' }}</td>
                <td class="text-center">
                    @php
                        $statusClass = match($repair->status) {
                            'Pending' => 'badge-warning',
                            'Assigned' => 'badge-info',
                            'In Progress' => 'badge-primary',
                            'Resolved' => 'badge-success',
                            default => 'badge-info'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }}">{{ $repair->status }}</span>
                </td>
                <td>{{ $repair->damaged_part ?: 'N/A' }}</td>
                <td class="text-right">{{ $repair->cost > 0 ? 'PHP ' . number_format($repair->cost, 2) : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No repairs found for this period</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right">TOTAL:</td>
                <td class="text-right">PHP {{ number_format($totalCost, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>CampFix - Campus Facility Management System | STI College Novaliches</p>
        <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
    </div>
</body>
</html>
