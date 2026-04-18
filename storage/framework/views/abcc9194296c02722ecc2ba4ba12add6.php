<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Request - <?php echo e($eventRequest->title); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            padding: 15px;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
        }
        
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
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>STI NOVALICHES COLLEGE</h1>
            <h2>EVENT REQUEST FORM</h2>
        </div>

        <!-- Request Details Table -->
        <table class="details-table">
            <tr>
                <th>Request No.:</th>
                <td><?php echo e($eventRequest->id); ?></td>
                <th>Date:</th>
                <td><?php echo e($eventRequest->created_at->format('m/d/Y')); ?></td>
            </tr>
            <tr>
                <th>Department:</th>
                <td><?php echo e($eventRequest->department ?? 'N/A'); ?></td>
                <th>Venue:</th>
                <td><?php echo e($eventRequest->location); ?></td>
            </tr>
            <tr>
                <th>Activity:</th>
                <td colspan="3"><?php echo e($eventRequest->title); ?></td>
            </tr>
            <tr>
                <th>Date Needed:</th>
                <td><?php echo e(\Carbon\Carbon::parse($eventRequest->event_date)->format('m/d/Y')); ?></td>
                <th>Time Needed:</th>
                <td><?php echo e($eventRequest->start_time); ?> - <?php echo e($eventRequest->end_time); ?></td>
            </tr>
            <tr>
                <th>Category:</th>
                <td colspan="3"><?php echo e(ucfirst($eventRequest->category)); ?></td>
            </tr>
        </table>

        <!-- Description -->
        <p style="margin-bottom: 5px; font-weight: bold;">Description:</p>
        <p style="margin-bottom: 20px; padding: 10px; border: 1px solid #333; min-height: 60px;">
            <?php echo e($eventRequest->description ?? 'N/A'); ?>

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
                <?php if($eventRequest->materials_needed && is_array($eventRequest->materials_needed)): ?>
                    <?php $__currentLoopData = $eventRequest->materials_needed; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $material): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($material['qty'] ?? '1'); ?></td>
                        <td><?php echo e($material['item'] ?? 'N/A'); ?></td>
                        <td><?php echo e($material['purpose'] ?? 'N/A'); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; color: #666;">No materials/equipment specified</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Approval History Table -->
        <p style="margin-bottom: 5px; font-weight: bold;">Approval History:</p>
        <table class="approvers-table">
            <thead>
                <tr>
                    <th>Approver</th>
                    <th>Role</th>
                    <th>Date/Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($requester): ?>
                <tr>
                    <td><?php echo e($requester->name); ?></td>
                    <td>Requester</td>
                    <td><?php echo e($eventRequest->created_at->format('m/d/Y h:i A')); ?></td>
                    <td>Submitted</td>
                </tr>
                <?php endif; ?>
                <?php $__currentLoopData = $approvers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approver): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($approver['name']); ?></td>
                    <td><?php echo e($approver['role']); ?></td>
                    <td><?php echo e($approver['date']); ?></td>
                    <td>Approved</td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php if($finalApprover): ?>
                <tr>
                    <td><?php echo e($finalApprover->name); ?></td>
                    <td>School Admin</td>
                    <td><?php echo e($eventRequest->approved_at ? \Carbon\Carbon::parse($eventRequest->approved_at)->format('m/d/Y h:i A') : 'N/A'); ?></td>
                    <td>Approved</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/events/pdf.blade.php ENDPATH**/ ?>