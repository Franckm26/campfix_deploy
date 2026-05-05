<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Location Report - <?php echo e($dateRange); ?></title>
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
    </style>
</head>
<body>
    
    <?php 
        $stiLogoPath = public_path('Campfix/Images/images.png');
        $campfixLogoPath = public_path('Campfix/Images/logo.png');
    ?>
    <div class="letterhead">
        <div class="letterhead-left-logo">
            <?php if(file_exists($stiLogoPath)): ?>
                <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents($stiLogoPath))); ?>" alt="STI Logo">
            <?php endif; ?>
        </div>
        <div class="letterhead-info">
            <div class="school-name">STI COLLEGE NOVALICHES</div>
            <div class="school-address">STI Academic Center, Diamond Avenue corner Quirino Highway, San Bartolome, Novaliches, Quezon City</div>
            <div class="school-tagline">Campus Facility Management System - CampFix</div>
        </div>
        <div class="letterhead-right-logo">
            <?php if(file_exists($campfixLogoPath)): ?>
                <img src="data:image/png;base64,<?php echo e(base64_encode(file_get_contents($campfixLogoPath))); ?>" alt="CampFix Logo">
            <?php endif; ?>
        </div>
    </div>

    <div class="header">
        <h1>Repairs by Location - Detailed Report</h1>
        <h2>CampFix Analytics</h2>
    </div>

    <div class="date-range">
        <strong>Report Period:</strong> <?php echo e($dateRange); ?>

    </div>

    <div class="summary-box">
        <h3>Summary</h3>
        <div class="summary-item">
            <span class="summary-label">Total Repairs:</span>
            <span class="summary-value"><?php echo e($totalRepairs); ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Cost:</span>
            <span class="summary-value">₱<?php echo e(number_format($totalCost, 2)); ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item Fixed</th>
                <th>Location</th>
                <th class="text-center">Total Repairs</th>
                <th class="text-end">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $locationStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><strong><?php echo e($stat->title); ?></strong></td>
                <td><?php echo e($stat->location); ?></td>
                <td class="text-center"><?php echo e($stat->count); ?></td>
                <td class="text-end">₱<?php echo e(number_format($stat->total_cost, 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="4" class="text-center">No data available for the selected period</td>
            </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr class="footer-row">
                <td colspan="2"><strong>TOTAL</strong></td>
                <td class="text-center"><strong><?php echo e($totalRepairs); ?></strong></td>
                <td class="text-end"><strong>₱<?php echo e(number_format($totalCost, 2)); ?></strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Generated on <?php echo e(now()->format('F d, Y g:i A')); ?> | CampFix - Facility Management System
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/analytics-location-pdf.blade.php ENDPATH**/ ?>