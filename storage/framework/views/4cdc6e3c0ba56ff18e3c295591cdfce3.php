<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; background: #fff; }

        .page { padding: 40px 50px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 30px; }
        .header-left { display: table-cell; vertical-align: top; }
        .header-right { display: table-cell; vertical-align: top; text-align: right; }
        .report-title { font-size: 26px; font-weight: 700; color: #1e293b; }
        .report-date { font-size: 11px; color: #64748b; margin-top: 4px; }
        .school-name { font-size: 14px; font-weight: 700; color: #1e293b; }
        .school-sub { font-size: 11px; color: #64748b; }

        /* Info box */
        .info-box { background: #f1f5f9; border-radius: 6px; padding: 14px 18px; margin-bottom: 24px; display: table; width: 100%; }
        .info-col { display: table-cell; width: 33%; vertical-align: top; }
        .info-label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 3px; }
        .info-value { font-size: 12px; font-weight: 700; color: #1e293b; }

        /* Summary row */
        .summary-row { display: table; width: 100%; margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; }
        .summary-col { display: table-cell; font-size: 11px; color: #475569; padding-right: 20px; }
        .summary-col strong { color: #1e293b; }

        /* Table */
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        thead tr { background: #334155; color: #fff; }
        thead th { padding: 9px 10px; text-align: left; font-size: 11px; font-weight: 600; letter-spacing: 0.03em; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody tr:nth-child(odd) { background: #fff; }
        tbody td { padding: 8px 10px; font-size: 11px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }

        /* Badges */
        .badge { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-info    { background: #dbeafe; color: #2563eb; }
        .badge-secondary { background: #f1f5f9; color: #64748b; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-primary { background: #dbeafe; color: #1d4ed8; }

        /* Totals */
        .totals { display: table; width: 100%; margin-top: 10px; }
        .totals-spacer { display: table-cell; width: 60%; }
        .totals-box { display: table-cell; width: 40%; }
        .totals-row { display: table; width: 100%; padding: 5px 0; border-bottom: 1px solid #e2e8f0; }
        .totals-label { display: table-cell; font-size: 11px; color: #64748b; }
        .totals-value { display: table-cell; font-size: 11px; text-align: right; font-weight: 600; color: #1e293b; }
        .totals-total { border-top: 2px solid #334155; margin-top: 4px; }
        .totals-total .totals-label,
        .totals-total .totals-value { font-size: 13px; font-weight: 700; color: #1e293b; padding-top: 6px; }

        /* Footer */
        .footer { margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 12px; display: table; width: 100%; }
        .footer-left { display: table-cell; font-size: 10px; color: #94a3b8; }
        .footer-right { display: table-cell; text-align: right; font-size: 10px; color: #94a3b8; }

        .divider { border: none; border-top: 1px solid #e2e8f0; margin: 20px 0; }
    </style>
</head>
<body>
<div class="page">

    
    <div class="header">
        <div class="header-left">
            <div class="report-title">Reports Export</div>
            <div class="report-date">Generated: <?php echo e(now()->format('F d, Y g:i A')); ?></div>
        </div>
        <div class="header-right">
            <div class="school-name">CampFix</div>
            <div class="school-sub">STI College Novaliches</div>
            <div class="school-sub">Campus Facility Management System</div>
        </div>
    </div>

    
    <div class="info-box">
        <div class="info-col">
            <div class="info-label">Total Reports</div>
            <div class="info-value"><?php echo e($reports->count()); ?></div>
        </div>
        <div class="info-col">
            <div class="info-label">Total Resolved</div>
            <div class="info-value"><?php echo e($resolvedReports->count()); ?></div>
        </div>
        <div class="info-col">
            <div class="info-label">Total Repair Cost</div>
            <div class="info-value">₱<?php echo e(number_format($resolvedReports->sum('cost'), 2)); ?></div>
        </div>
    </div>

    
    <div class="summary-row">
        <div class="summary-col"><strong>Pending:</strong> <?php echo e($pendingReports->count()); ?></div>
        <div class="summary-col"><strong>Assigned:</strong> <?php echo e($assignedReports->count()); ?></div>
        <div class="summary-col"><strong>In Progress:</strong> <?php echo e($inProgressReports->count()); ?></div>
        <div class="summary-col"><strong>Resolved:</strong> <?php echo e($resolvedReports->count()); ?></div>
        <div class="summary-col"><strong>Exported by:</strong> <?php echo e(auth()->user()->name); ?></div>
    </div>

    
    <?php if($resolvedReports->count() > 0): ?>
    <div style="font-size:14px; font-weight:700; color:#16a34a; margin-bottom:12px; margin-top:20px; text-transform:uppercase; letter-spacing:0.05em;">
        ✓ Resolved Reports (<?php echo e($resolvedReports->count()); ?>)
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Issue</th>
                <th>Category</th>
                <th>Location</th>
                <th>Priority</th>
                <th>Reported By</th>
                <th>Date</th>
                <th>Cost</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $resolvedReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $priorityClass = match($report->severity) {
                    'critical', 'urgent' => 'badge-danger',
                    'high'   => 'badge-warning',
                    'medium' => 'badge-info',
                    default  => 'badge-secondary',
                };
            ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($report->title ?? \Illuminate\Support\Str::limit($report->description, 35)); ?></td>
                <td><?php echo e($report->category->name ?? 'N/A'); ?></td>
                <td><?php echo e($report->location); ?></td>
                <td><span class="badge <?php echo e($priorityClass); ?>"><?php echo e(ucfirst($report->severity)); ?></span></td>
                <td><?php echo e($report->user->name ?? 'Unknown'); ?></td>
                <td><?php echo e($report->created_at->format('M d, Y')); ?></td>
                <td><?php echo e($report->cost ? '₱'.number_format($report->cost, 2) : '-'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if($inProgressReports->count() > 0): ?>
    <div style="font-size:14px; font-weight:700; color:#d97706; margin-bottom:12px; margin-top:20px; text-transform:uppercase; letter-spacing:0.05em;">
        ⚙ In Progress Reports (<?php echo e($inProgressReports->count()); ?>)
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Issue</th>
                <th>Category</th>
                <th>Location</th>
                <th>Priority</th>
                <th>Reported By</th>
                <th>Date</th>
                <th>Assigned To</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $inProgressReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $priorityClass = match($report->severity) {
                    'critical', 'urgent' => 'badge-danger',
                    'high'   => 'badge-warning',
                    'medium' => 'badge-info',
                    default  => 'badge-secondary',
                };
            ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($report->title ?? \Illuminate\Support\Str::limit($report->description, 35)); ?></td>
                <td><?php echo e($report->category->name ?? 'N/A'); ?></td>
                <td><?php echo e($report->location); ?></td>
                <td><span class="badge <?php echo e($priorityClass); ?>"><?php echo e(ucfirst($report->severity)); ?></span></td>
                <td><?php echo e($report->user->name ?? 'Unknown'); ?></td>
                <td><?php echo e($report->created_at->format('M d, Y')); ?></td>
                <td><?php echo e($report->assignedTo->name ?? 'Unassigned'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if($pendingReports->count() > 0): ?>
    <div style="font-size:14px; font-weight:700; color:#64748b; margin-bottom:12px; margin-top:20px; text-transform:uppercase; letter-spacing:0.05em;">
        ⏳ Pending Reports (<?php echo e($pendingReports->count()); ?>)
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Issue</th>
                <th>Category</th>
                <th>Location</th>
                <th>Priority</th>
                <th>Reported By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $pendingReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $priorityClass = match($report->severity) {
                    'critical', 'urgent' => 'badge-danger',
                    'high'   => 'badge-warning',
                    'medium' => 'badge-info',
                    default  => 'badge-secondary',
                };
            ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($report->title ?? \Illuminate\Support\Str::limit($report->description, 35)); ?></td>
                <td><?php echo e($report->category->name ?? 'N/A'); ?></td>
                <td><?php echo e($report->location); ?></td>
                <td><span class="badge <?php echo e($priorityClass); ?>"><?php echo e(ucfirst($report->severity)); ?></span></td>
                <td><?php echo e($report->user->name ?? 'Unknown'); ?></td>
                <td><?php echo e($report->created_at->format('M d, Y')); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if($assignedReports->count() > 0): ?>
    <div style="font-size:14px; font-weight:700; color:#1d4ed8; margin-bottom:12px; margin-top:20px; text-transform:uppercase; letter-spacing:0.05em;">
        📋 Assigned Reports (<?php echo e($assignedReports->count()); ?>)
    </div>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Issue</th>
                <th>Category</th>
                <th>Location</th>
                <th>Priority</th>
                <th>Reported By</th>
                <th>Date</th>
                <th>Assigned To</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $assignedReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $priorityClass = match($report->severity) {
                    'critical', 'urgent' => 'badge-danger',
                    'high'   => 'badge-warning',
                    'medium' => 'badge-info',
                    default  => 'badge-secondary',
                };
            ?>
            <tr>
                <td><?php echo e($i + 1); ?></td>
                <td><?php echo e($report->title ?? \Illuminate\Support\Str::limit($report->description, 35)); ?></td>
                <td><?php echo e($report->category->name ?? 'N/A'); ?></td>
                <td><?php echo e($report->location); ?></td>
                <td><span class="badge <?php echo e($priorityClass); ?>"><?php echo e(ucfirst($report->severity)); ?></span></td>
                <td><?php echo e($report->user->name ?? 'Unknown'); ?></td>
                <td><?php echo e($report->created_at->format('M d, Y')); ?></td>
                <td><?php echo e($report->assignedTo->name ?? 'Unassigned'); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>
    <?php endif; ?>

    
    <?php if($resolvedReports->sum('cost') > 0): ?>
    <div class="totals">
        <div class="totals-spacer"></div>
        <div class="totals-box">
            <div class="totals-row">
                <div class="totals-label">Resolved Reports</div>
                <div class="totals-value"><?php echo e($resolvedReports->count()); ?></div>
            </div>
            <div class="totals-row">
                <div class="totals-label">Total Repair Cost</div>
                <div class="totals-value">₱<?php echo e(number_format($resolvedReports->sum('cost'), 2)); ?></div>
            </div>
            <div class="totals-row">
                <div class="totals-label">Avg Cost per Repair</div>
                <div class="totals-value">
                    ₱<?php echo e($resolvedReports->count() > 0 ? number_format($resolvedReports->sum('cost') / $resolvedReports->count(), 2) : '0.00'); ?>

                </div>
            </div>
            <div class="totals-row totals-total">
                <div class="totals-label">Grand Total Cost</div>
                <div class="totals-value">₱<?php echo e(number_format($resolvedReports->sum('cost'), 2)); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($costByRoom->count() > 0): ?>
    <hr class="divider">
    <div style="font-size:13px; font-weight:700; color:#1e293b; margin-bottom:12px; text-transform:uppercase; letter-spacing:0.05em;">
        Combined Cost by Issue &amp; Room
    </div>
    <table>
        <thead>
            <tr>
                <th>Issue</th>
                <th>Room / Location</th>
                <th style="text-align:center;">Repairs</th>
                <th style="text-align:right;">Total Cost</th>
                <th style="text-align:right;">Avg Cost</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $costByRoom; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td style="font-weight:600;"><?php echo e($row['issue']); ?></td>
                <td><?php echo e($row['location']); ?></td>
                <td style="text-align:center;"><?php echo e($row['count']); ?></td>
                <td style="text-align:right; font-weight:600;">₱<?php echo e(number_format($row['total_cost'], 2)); ?></td>
                <td style="text-align:right;">₱<?php echo e(number_format($row['avg_cost'], 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-spacer"></div>
        <div class="totals-box">
            <div class="totals-row">
                <div class="totals-label">Unique Issue + Room Combinations</div>
                <div class="totals-value"><?php echo e($costByRoom->count()); ?></div>
            </div>
            <div class="totals-row">
                <div class="totals-label">Total Repairs</div>
                <div class="totals-value"><?php echo e($costByRoom->sum('count')); ?></div>
            </div>
            <div class="totals-row totals-total">
                <div class="totals-label">Grand Total Cost</div>
                <div class="totals-value">₱<?php echo e(number_format($costByRoom->sum('total_cost'), 2)); ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="footer">
        <div class="footer-left">CampFix — STI College Novaliches &bull; </div>
        <div class="footer-right">Page 1 of 1 &bull; <?php echo e(now()->format('Y')); ?></div>
    </div>

</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/reports-pdf.blade.php ENDPATH**/ ?>