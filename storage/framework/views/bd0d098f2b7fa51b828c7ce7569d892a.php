<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Status Distribution & Response Time Report</title>
    <style>
        @page {
            margin: 100px 50px 80px 50px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            color: #333;
        }
        .header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #667eea;
            font-size: 20pt;
            margin: 0;
            font-weight: bold;
        }
        .header .subtitle {
            color: #666;
            font-size: 10pt;
            margin-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 9pt;
            color: #666;
        }
        .section-title {
            color: #667eea;
            font-size: 14pt;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f0f0f0;
            color: #333;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8pt;
        }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-assigned { background-color: #17a2b8; color: #fff; }
        .badge-progress { background-color: #007bff; color: #fff; }
        .badge-resolved { background-color: #28a745; color: #fff; }
        .ticket-list {
            font-size: 8pt;
            line-height: 1.4;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>STI College</h1>
        <div class="subtitle">Status Distribution & Response Time Analysis Report</div>
        <div class="subtitle"><?php echo e($dateRange); ?></div>
    </div>

    <div class="footer">
        <div>Generated on <?php echo e(now()->format('F d, Y h:i A')); ?></div>
        <div>Page <span class="pagenum"></span></div>
    </div>

    <div class="section-title">Status Distribution</div>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Status</th>
                <th style="width: 10%; text-align: center;">Count</th>
                <th style="width: 70%;">Tickets</th>
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
                <td style="text-align: center;"><strong><?php echo e($status['count']); ?></strong></td>
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
                <td colspan="3" style="text-align: center; color: #999;">No data available</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="section-title">Response Time Details</div>
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Ticket</th>
                <th style="width: 15%;">Issue</th>
                <th style="width: 12%;">Location</th>
                <th style="width: 15%;">Submit to Assign</th>
                <th style="width: 15%;">Assign to Resolve</th>
                <th style="width: 12%;">Total Time</th>
                <th style="width: 13%;">Staff</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $responseTimeData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                <td colspan="7" style="text-align: center; color: #999;">No response time data available</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/status-distribution-pdf.blade.php ENDPATH**/ ?>