<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Request - {{ $eventRequest->location }}</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body, div, p, h1, h2, h3, table, td, th { margin: 0; padding: 0; }
        
        @page {
            margin: 45mm 14mm 30mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.4;
            color: #111;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 0;
        }

        /* Match the Executive Summary letterhead using PDF-compatible tables. */
        .letterhead {
            position: fixed;
            top: -37mm;
            left: 0;
            right: 0;
            height: 33mm;
            padding: 0 8mm;
            border-bottom: 1px solid #111;
            font-family: Arial, sans-serif;
            color: #111;
        }
        .letterhead-table, .footer-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .letterhead-table td { vertical-align: middle; height: 32mm; }
        .letterhead-logo { width: 26mm; text-align: center; }
        .letterhead-logo img { width: 25mm; height: 25mm; }
        .letterhead-info { text-align: center; padding: 0 2mm; }
        .letterhead-info .report-title { font-size: 8.5pt; line-height: 1.25; font-weight: bold; }
        .letterhead-info .address { margin-top: 4px; font-size: 7.5pt; line-height: 1.35; }
        .logo-fallback { font-size: 9pt; font-weight: bold; }
        .pdf-footer {
            position: fixed;
            bottom: -22mm;
            left: 0;
            right: 0;
            height: 17mm;
            padding: 3mm 5mm 0;
            border-top: 1px solid #111;
            font-family: Arial, sans-serif;
            color: #111;
        }
        .footer-table td { vertical-align: middle; }
        .footer-logo { width: 18mm; text-align: left; }
        .footer-logo img { width: 14mm; height: 14mm; }
        .footer-copy { font-size: 6.7pt; line-height: 1.4; }
        .footer-copy strong { font-size: 7pt; }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            margin-bottom: 5px;
        }
        
        .header h3 {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
        }
        
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .details-table th,
        .details-table td {
            padding: 8px;
            border: 1px solid #333;
            text-align: left;
        }
        
        .details-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 150px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            padding: 8px;
            border: 1px solid #333;
            text-align: center;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .items-table .col-qty {
            width: 60px;
        }
        
        .items-table .col-item {
            width: 200px;
        }
        
        .items-table .col-purpose {
            width: auto;
        }
        
        .signature-section {
            margin-top: 30px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 10px;
            text-align: center;
            vertical-align: top;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            margin-bottom: 5px;
            height: 40px;
        }
        
        .signature-label {
            font-size: 11px;
            color: #333;
            font-weight: bold;
        }
        
        .signature-name {
            font-size: 11px;
            color: #333;
            margin-top: 5px;
        }
        
        .signature-date {
            font-size: 11px;
            color: #333;
        }
        
        .approvers-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .approvers-table th,
        .approvers-table td {
            padding: 8px;
            border: 1px solid #333;
            text-align: left;
        }
        
        .approvers-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        @media print {
            body {
                padding: 0;
            }
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    @php
        $campfixLogoPath = public_path('Campfix/Images/logo.png');
        $stiLogoPath = public_path('Campfix/Images/STI-Academic-Seal-One-Color_400.png');
    @endphp
    <header class="letterhead">
        <table class="letterhead-table"><tr>
            <td class="letterhead-logo">
                @if(file_exists($campfixLogoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($campfixLogoPath)) }}" alt="CampFix logo">
                @else
                    <span class="logo-fallback">CampFix</span>
                @endif
            </td>
            <td class="letterhead-info">
                <div class="report-title">CampFix: A Web-Based Platform for Campus Facility Requests, Concern Reporting, and Data-Driven Decision Support for STI College Novaliches</div>
                <div class="address">STI Academic Center, Diamond Avenue corner Quirino Highway<br>San Bartolome, Novaliches, Quezon City, 1116 Metro Manila</div>
            </td>
            <td class="letterhead-logo">
                @if(file_exists($stiLogoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($stiLogoPath)) }}" alt="STI academic seal">
                @else
                    <span class="logo-fallback">STI</span>
                @endif
            </td>
        </tr></table>
    </header>
    <footer class="pdf-footer">
        <table class="footer-table"><tr>
            <td class="footer-logo">
                @if(file_exists($stiLogoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($stiLogoPath)) }}" alt="STI academic seal">
                @else
                    <span class="logo-fallback">STI</span>
                @endif
            </td>
            <td class="footer-copy">
                <strong>STI COLLEGE NOVALICHES</strong><br>
                STI Academic Center, Diamond Avenue corner Quirino Highway, San Bartolome, Novaliches, Quezon City, 1116 Metro Manila<br>
                www.sti.edu &nbsp;|&nbsp; CampFix Decision Support System &nbsp;|&nbsp; Generated by {{ auth()->user()->name ?? 'CampFix User' }}
            </td>
        </tr></table>
    </footer>
    <div class="container">
        <!-- Title -->
        <div class="header">
            <h2>FACILITY REQUEST FORM</h2>
        </div>

        <!-- Request Details Table -->
        <table class="details-table">
            <tr>
                <th>Date Requested:</th>
                <td colspan="3">{{ $eventRequest->created_at->copy()->timezone('Asia/Manila')->format('m/d/Y') }}</td>
            </tr>
            <tr>
                <th>Department:</th>
                <td>{{ $eventRequest->department ?? 'N/A' }}</td>
                <th>Venue:</th>
                <td>{{ $eventRequest->location }}</td>
            </tr>
            <tr>
                <th>Date Needed:</th>
                <td>{{ \Carbon\Carbon::parse($eventRequest->event_date)->format('m/d/Y') }}</td>
                <th>Time Needed:</th>
                <td>{{ \Carbon\Carbon::parse($eventRequest->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($eventRequest->end_time)->format('g:i A') }}</td>
            </tr>
            
        </table>

        <!-- Description -->
        <p style="margin-bottom: 5px; font-weight: bold;">Description:</p>
        <p style="margin-bottom: 20px; padding: 10px; border: 1px solid #333; min-height: 60px; white-space:pre-line;">
            {{ $eventRequest->description ?? 'N/A' }}
        </p>

        <!-- Items/Materials Table -->
        <p style="margin-bottom: 5px; font-weight: bold;">Materials/Equipment Needed:</p>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">Qty</th>
                    <th class="col-item">Item</th>
                    <th class="col-purpose">Purpose</th>
                </tr>
            </thead>
            <tbody>
                @if($eventRequest->materials_needed && is_array($eventRequest->materials_needed))
                    @foreach($eventRequest->materials_needed as $material)
                    <tr>
                        <td>{{ $material['qty'] ?? '1' }}</td>
                        <td>{{ $material['item'] ?? 'N/A' }}</td>
                        <td>{{ $material['purpose'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                @else
                <tr>
                    <td colspan="3" style="text-align: center; color: #666;">No materials/equipment specified</td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Approval History Table -->
        <p style="margin-bottom: 5px; font-weight: bold;">Approval History:</p>
        <table class="approvers-table">
            <thead>
                <tr>
                    <th>Approved By</th>
                    <th>Position</th>
                    <th>Date/Time (PHT, UTC+08:00)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if($requester)
                <tr>
                    <td>{{ $requester->name }}</td>
                    <td>Requester</td>
                    <td>{{ $eventRequest->created_at->copy()->timezone('Asia/Manila')->format('m/d/Y h:i A') }}</td>
                    <td>Submitted</td>
                </tr>
                @endif
                @foreach($approvers as $approver)
                <tr>
                    <td>{{ $approver['name'] }}</td>
                    <td>{{ $approver['role'] }}</td>
                    <td>{{ $approver['date'] }}</td>
                    <td>Approved</td>
                </tr>
                @endforeach
                @if($finalApprover)
                <tr>
                    <td>{{ $finalApprover->name }}</td>
                    <td>School Admin</td>
                    <td>{{ $eventRequest->approved_at ? $eventRequest->approved_at->copy()->timezone('Asia/Manila')->format('m/d/Y h:i A') : 'N/A' }}</td>
                    <td>Approved</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>
