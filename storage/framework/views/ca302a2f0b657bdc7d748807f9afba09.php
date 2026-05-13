<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Status Distribution & Response Time Report - <?php echo e($dateRange); ?></title>
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
            width: 32%;
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
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #333;
            margin-top: 15px;
            margin-bottom: 8px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 3px;
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
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 7px;
        }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-assigned { background-color: #17a2b8; color: #fff; }
        .badge-inprogress { background-color: #007bff; color: #fff; }
        .badge-resolved { background-color: #28a745; color: #fff; }
        .ticket-list {
            font-size: 7px;
            line-height: 1.5;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
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
        <h1>Status Distribution & Response Time Analysis</h1>
        <h2>CampFix Analytics</h2>
    </div>

    <div class="date-range">
        <strong>Report Period:</strong> <?php echo e($dateRange); ?>

    </div>

    <div class="summary-box">
        <h3>Response Time Summary</h3>
        <div class="summary-item">
            <span class="summary-label">Avg Submit to Assign:</span>
            <span class="summary-value"><?php echo e($avgSubmittedToAssigned); ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Avg Assign to Resolve:</span>
            <span class="summary-value"><?php echo e($avgAssignedToResolved); ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Avg Total Time:</span>
            <span class="summary-value"><?php echo e($avgTotalTime); ?></span>
        </div>
    </div>

    <div class="section-title">Status Distribution (<?php echo e($totalTickets); ?> Total Tickets)</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Status</th>
                <th style="width: 10%;" class="text-center">Count</th>
                <th style="width: 75%;">Tickets</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $statusData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td>
                    <span class="status-badge badge-<?php echo e(strtolower(str_replace(' ', '', $status['status']))); ?>">
                        <?php echo e($status['status']); ?>

                    </span>
                </td>
                <td class="text-center"><strong><?php echo e($status['count']); ?></strong></td>
                <td>
                    <div class="ticket-list">
                        <?php $__currentLoopData = $status['tickets']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php echo e($ticket); ?><?php if(!$loop->last): ?>, <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="3" class="text-center">No data available for the selected period</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Response Time Details</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Ticket #</th>
                <th style="width: 20%;">Issue</th>
                <th style="width: 12%;">Location</th>
                <th style="width: 13%;">Submit→Assign</th>
                <th style="width: 13%;">Assign→Resolve</th>
                <th style="width: 12%;">Total Time</th>
                <th style="width: 12%;">Staff</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $responseTimeStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <td><?php echo e($data['id']); ?></td>
                <td><?php echo e($data['title']); ?></td>
                <td><?php echo e($data['location']); ?></td>
                <td><?php echo e($data['submitted_to_assigned']); ?></td>
                <td><?php echo e($data['assigned_to_resolved']); ?></td>
                <td><strong><?php echo e($data['total_time']); ?></strong></td>
                <td><?php echo e($data['staff']); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="7" class="text-center">No response time data available</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Generated on <?php echo e(now()->format('F d, Y g:i A')); ?> | CampFix - Facility Management System
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/analytics-status-distribution-pdf.blade.php ENDPATH**/ ?>