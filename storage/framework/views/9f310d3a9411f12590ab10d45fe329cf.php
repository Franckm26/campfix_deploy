

<?php $__env->startSection('page_title'); ?>
<h2>Analytics</h2>
<p>Cost Tracking & Repair/Damage Analysis</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<style>
.analytics-card {
    background: var(--card-bg, #fff);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.analytics-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--text-color, #333);
}

/* Trend Alerts */
.trend-alert-item { display:flex; align-items:center; gap:12px; padding:12px 16px; border-radius:8px; margin-bottom:10px; }
.trend-alert-item.critical { background:#fde8ea; border-left:4px solid #dc3545; }
.trend-alert-item.warning  { background:#fff8e1; border-left:4px solid #ffc107; }
.trend-alert-item.info     { background:#e8f4fd; border-left:4px solid #17a2b8; }
.trend-alert-icon { font-size:1.3rem; }
.trend-alert-text { flex:1; }
.trend-alert-text strong { display:block; font-size:.95rem; }
.trend-alert-text span   { font-size:.82rem; color:#666; }

/* Period Comparison */
.period-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:16px; }
.period-card { background:#fff; border-radius:10px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:4px solid #667eea; }
.period-card.up      { border-left-color:#dc3545; }
.period-card.down    { border-left-color:#28a745; }
.period-card.neutral { border-left-color:#6c757d; }
.period-label { font-size:.78rem; color:#888; margin-bottom:4px; }
.period-value { font-size:1.5rem; font-weight:700; }
.period-sub   { font-size:.8rem; color:#555; margin-top:4px; }
.chg-badge { display:inline-block; padding:1px 7px; border-radius:10px; font-size:.76rem; font-weight:600; }
.chg-badge.up      { background:#fde8ea; color:#dc3545; }
.chg-badge.down    { background:#e6f9f0; color:#28a745; }
.chg-badge.neutral { background:#f0f0f0; color:#6c757d; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.stat-card.green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card.orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card.yellow {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.filter-section {
    background: var(--card-bg, #fff);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-color, #333);
}

.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9rem;
}

.btn-reset {
    padding: 8px 15px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.9rem;
}

.btn-reset:hover {
    background: #5a6268;
    color: white;
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
}

.analytics-table th,
.analytics-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.analytics-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    text-align: center;
    white-space: nowrap;
}

.analytics-table tbody td {
    text-align: center;
}

.analytics-table tr:hover {
    background: #f8f9fa;
}

.cost-badge {
    background: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.85rem;
}

.count-badge {
    background: #007bff;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.85rem;
}

.alert-info {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
    color: #0c5460;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .stats-grid { grid-template-columns: 1fr; }
    .filter-form { flex-direction: column; align-items: stretch; }
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo e($totalConcerns); ?></div>
            <div class="stat-label">Total Repairs/Damages</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value">₱<?php echo e(number_format($totalCost, 2)); ?></div>
            <div class="stat-label">
                Total Cost
                <a href="#" data-bs-toggle="modal" data-bs-target="#costModal" style="color: #fff; text-decoration: underline;">View Details</a>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-value"><?php echo e($locationStats->count()); ?></div>
            <div class="stat-label">
                Frequently Fixed Room
                <a href="#" data-bs-toggle="modal" data-bs-target="#roomsModal" style="color: #fff; text-decoration: underline;">See Room</a>
            </div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-value"><?php echo e($totalConcerns > 0 ? number_format($totalCost / $totalConcerns, 2) : 0); ?></div>
            <div class="stat-label">Average Cost per Repair</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="<?php echo e(route('admin.analytics')); ?>" class="filter-form">
            <div class="filter-group">
                <label for="date_from">Date From</label>
                <input type="date" name="date_from" id="date_from" value="<?php echo e(request('date_from')); ?>">
            </div>
            <div class="filter-group">
                <label for="date_to">Date To</label>
                <input type="date" name="date_to" id="date_to" value="<?php echo e(request('date_to')); ?>">
            </div>
            <div class="filter-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="<?php echo e(route('admin.analytics')); ?>" class="btn-reset">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- ── TREND ALERTS ─────────────────────────────────────────────── -->
    <?php if(isset($trendAlerts) && $trendAlerts->count() > 0): ?>
    <div class="analytics-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="analytics-title" style="font-size:1rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                <i class="fas fa-bell text-danger me-2"></i> Alerts &amp; Notifications
                <span class="badge bg-danger ms-2"><?php echo e($trendAlerts->count()); ?></span>
            </div>
        </div>

        <div class="mb-4">
            <?php $__currentLoopData = $trendAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $borderColor = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                $bgColor     = $alert['severity'] === 'critical' ? '#fef2f2' : ($alert['severity'] === 'warning' ? '#fff7ed' : '#fffbeb');
                $iconColor   = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                $timeAgo     = isset($alert['updated_at']) && $alert['updated_at'] ? \Carbon\Carbon::parse($alert['updated_at'])->diffForHumans(null, true, true) : 'recently';
            ?>
            <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-left:4px solid <?php echo e($borderColor); ?>;background:<?php echo e($bgColor); ?>;border-radius:8px;margin-bottom:10px;cursor:pointer;"
                onclick="showCostTrendModal(<?php echo e(json_encode($alert)); ?>)">
                <div style="width:36px;height:36px;border-radius:50%;background:<?php echo e($iconColor); ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-triangle-exclamation" style="color:#fff;font-size:15px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:.95rem;color:#1e293b;"><?php echo e($alert['alert_title'] ?? 'Trend Detected'); ?></div>
                    <div style="font-size:.82rem;color:#64748b;">
                        <?php if(!empty($alert['top_issue'])): ?><?php echo e($alert['top_issue']); ?> on <?php echo e($alert['location']); ?><?php else: ?><?php echo e($alert['location']); ?><?php endif; ?>
                    </div>
                </div>
                <div style="font-size:.78rem;color:#94a3b8;white-space:nowrap;"><?php echo e($timeAgo); ?></div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Combined Cost by Location -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-map-marker-alt"></i> Combined Cost by Location (All Tickets)
            </div>
        </div>
        <table class="analytics-table">
            <thead>
                <tr>
                    <th>Location</th>
                    <th>Total Tickets</th>
                    <th>Total Cost</th>
                    <th>Avg Cost per Ticket</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $combinedLocationStats ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td><?php echo e($stat['location']); ?></td>
                    <td><span class="count-badge"><?php echo e($stat['total_count']); ?></span></td>
                    <td><span class="cost-badge">₱<?php echo e(number_format($stat['total_cost'], 2)); ?></span></td>
                    <td>₱<?php echo e(number_format($stat['total_count'] > 0 ? $stat['total_cost'] / $stat['total_count'] : 0, 2)); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="text-center">No data found</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Repair/Damage Details -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-list"></i> Reports Details
            </div>
        </div>
        
        <?php if($reports->count() > 0): ?>
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Damage</th>
                        <th>Date and Time Fixed</th>
                        <th>Repair Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($report->location); ?></td>
                        <td><?php echo e($report->damaged_part ?? 'N/A'); ?></td>
                        <td><?php echo e($report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->format('M d, Y g:i A') : 'Not Fixed'); ?></td>
                        <td><span class="cost-badge">₱<?php echo e(number_format($report->cost ?? 0, 2)); ?></span></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert-info">
            <i class="fas fa-info-circle"></i> No reports with location and date fixed data found for the selected period.
        </div>
        <?php endif; ?>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-pie"></i> Repairs by Location
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="locationPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-bar"></i> Cost by Location
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="locationBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-pie"></i> Status Distribution
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="statusDoughnutChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-area"></i> Monthly Trend
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rooms Modal -->
<div class="modal fade" id="roomsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Frequently Fixed Rooms</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php $__currentLoopData = $locationStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="room-item" style="padding: 10px; border-bottom: 1px solid #eee;">
                    <strong><?php echo e($stat['location']); ?></strong> - <?php echo e($stat['count']); ?> repairs, Total Cost: ₱<?php echo e(number_format($stat['total_cost'], 2)); ?>

                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Cost Modal -->
<div class="modal fade" id="costModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cost Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Total Repairs/Damages</h6>
                        <p class="h4 text-primary"><?php echo e($totalConcerns); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Total Cost</h6>
                        <p class="h4 text-success">₱<?php echo e(number_format($totalCost, 2)); ?></p>
                    </div>
                </div>
                <hr>
                <h6>Cost by Location</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Repairs</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $locationStats->sortByDesc('total_cost'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($stat['location']); ?></td>
                                <td><?php echo e($stat['count']); ?></td>
                                <td>₱<?php echo e(number_format($stat['total_cost'], 2)); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cost Trend Modal -->
<div class="modal fade" id="costTrendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i><span id="ctm_title"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <div style="font-size:.8rem;color:#888;">Location</div>
                        <div style="font-weight:700;" id="ctm_location"></div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:.8rem;color:#888;">Total Repairs</div>
                        <div style="font-weight:700;color:#3b82f6;" id="ctm_repairs"></div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:.8rem;color:#888;">Cumulative Cost</div>
                        <div style="font-weight:700;color:#22c55e;" id="ctm_total_cost"></div>
                    </div>
                </div>
                <div class="row mb-3" id="ctm_threshold_row">
                    <div class="col-6">
                        <div style="font-size:.8rem;color:#888;">Original Asset Price</div>
                        <div style="font-weight:700;" id="ctm_threshold"></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.8rem;color:#888;">Cost vs Original Price</div>
                        <div class="progress mt-1" style="height:10px;">
                            <div class="progress-bar" id="ctm_progress_bar" style="width:0%"></div>
                        </div>
                        <div style="font-size:.78rem;color:#888;margin-top:3px;" id="ctm_progress_label"></div>
                    </div>
                </div>
                <hr>
                <h6 class="mb-3">Monthly Cost Breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Repairs</th>
                                <th class="text-end">Cost</th>
                            </tr>
                        </thead>
                        <tbody id="ctm_monthly_rows"></tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td>Total</td>
                                <td class="text-center" id="ctm_total_count"></td>
                                <td class="text-end" id="ctm_total_cost_foot"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
(function() {
    var locations = <?php echo json_encode($chartLocations ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
    var counts    = <?php echo json_encode($chartCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
    var costs     = <?php echo json_encode($chartCosts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
    var statuses  = <?php echo json_encode($chartStatuses ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
    var statusCounts = <?php echo json_encode($chartStatusCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;
    var monthly   = <?php echo json_encode(isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => $s->month, 'title' => $s->title, 'count' => $s->total_count])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP); ?>;

    var colors = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#4BC0C0'];

    function buildCharts() {
        if (typeof Chart === 'undefined') { setTimeout(buildCharts, 100); return; }

        // Pie — Repairs by Location
        var pieEl = document.getElementById('locationPieChart');
        if (pieEl && locations.length > 0) {
            new Chart(pieEl, { type: 'pie', data: { labels: locations, datasets: [{ data: counts, backgroundColor: colors, borderWidth: 2 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
        }

        // Bar — Cost by Location
        var barEl = document.getElementById('locationBarChart');
        if (barEl && locations.length > 0) {
            new Chart(barEl, { type: 'bar', data: { labels: locations, datasets: [{ label: 'Total Cost (₱)', data: costs, backgroundColor: '#36A2EB', borderWidth: 1 }] }, options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { callback: function(v){ return '₱'+v.toLocaleString(); } } } } } });
        }

        // Doughnut — Status Distribution
        var doughEl = document.getElementById('statusDoughnutChart');
        if (doughEl && statuses.length > 0) {
            new Chart(doughEl, { type: 'doughnut', data: { labels: statuses, datasets: [{ data: statusCounts, backgroundColor: colors, borderWidth: 2 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
        }

        // Line — Monthly Trend (per issue type)
        var lineEl = document.getElementById('monthlyTrendChart');
        if (lineEl) {
            // Build 6-month labels
            var monthLabels = [];
            for (var i = 5; i >= 0; i--) {
                var d = new Date();
                d.setDate(1);
                d.setMonth(d.getMonth() - i);
                var key = d.toISOString().slice(0, 7);
                var lbl = d.toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
                monthLabels.push({ key: key, label: lbl });
            }

            // Group by issue title
            var issueMap = {};
            monthly.forEach(function(item) {
                if (!issueMap[item.title]) issueMap[item.title] = {};
                issueMap[item.title][item.month] = item.count;
            });

            var palette = ['#36A2EB','#FF6384','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#22C55E','#F97316','#7BC8A4','#EC4899'];

            var datasets = Object.entries(issueMap).map(function([title, monthData], idx) {
                return {
                    label: title,
                    data: monthLabels.map(function(m) { return monthData[m.key] || 0; }),
                    borderColor: palette[idx % palette.length],
                    backgroundColor: palette[idx % palette.length] + '22',
                    borderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: palette[idx % palette.length],
                    tension: 0.3,
                    fill: false,
                };
            });

            // Plugin: draw issue name at last non-zero point
            var endLabelPlugin = {
                id: 'endLabelInline',
                afterDatasetsDraw: function(chart) {
                    var ctx = chart.ctx;
                    chart.data.datasets.forEach(function(dataset, i) {
                        var meta = chart.getDatasetMeta(i);
                        if (meta.hidden) return;
                        var lastIdx = -1;
                        for (var j = dataset.data.length - 1; j >= 0; j--) {
                            if (dataset.data[j] > 0) { lastIdx = j; break; }
                        }
                        if (lastIdx === -1) return;
                        var point = meta.data[lastIdx];
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = dataset.borderColor;
                        ctx.textAlign = 'left';
                        ctx.textBaseline = 'middle';
                        ctx.fillText(dataset.label, point.x + 8, point.y);
                        ctx.restore();
                    });
                }
            };

            new Chart(lineEl, {
                type: 'line',
                plugins: [endLabelPlugin],
                data: {
                    labels: monthLabels.map(function(m) { return m.label; }),
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    layout: { padding: { right: 90 } },
                    interaction: { mode: 'index', intersect: false },
                    scales: {
                        x: {
                            ticks: { font: { size: 11 } },
                            grid: { display: false }
                        },
                        y: {
                            min: 0,
                            title: { display: true, text: 'Reports' },
                            ticks: { stepSize: 1, callback: function(v) { return v; } },
                            grid: { color: 'rgba(0,0,0,0.05)' }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                pointStyle: 'circle',
                                padding: 20,
                                font: { size: 13, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ' + ctx.parsed.y + (ctx.parsed.y === 1 ? ' report' : ' reports');
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    buildCharts();
})();

// Show Cost Trend Modal function
function showCostTrendModal(alert) {
    document.getElementById('ctm_title').textContent = (alert.top_issue || 'Issue') + ' - ' + alert.location;
    document.getElementById('ctm_location').textContent = alert.location;
    document.getElementById('ctm_repairs').textContent = alert.recent + ' repair(s)';
    document.getElementById('ctm_total_cost').textContent = '₱' + parseFloat(alert.all_time_cost).toLocaleString('en-PH', {minimumFractionDigits:2});

    const threshold = parseFloat(alert.replacement_threshold || 0);
    const allTime   = parseFloat(alert.all_time_cost || 0);
    const threshRow = document.getElementById('ctm_threshold_row');
    if (threshold > 0) {
        threshRow.style.display = '';
        document.getElementById('ctm_threshold').textContent = '₱' + threshold.toLocaleString('en-PH', {minimumFractionDigits:2});
        const pct = Math.min(100, Math.round((allTime / threshold) * 100));
        const bar = document.getElementById('ctm_progress_bar');
        bar.style.width = pct + '%';
        bar.className = 'progress-bar ' + (pct >= 100 ? 'bg-danger' : pct >= 80 ? 'bg-warning' : 'bg-success');
        document.getElementById('ctm_progress_label').textContent = pct + '% of original price used in repairs';
    } else {
        threshRow.style.display = 'none';
    }

    const tbody = document.getElementById('ctm_monthly_rows');
    tbody.innerHTML = '';
    let totalCount = 0, totalCost = 0;
    (alert.monthly_costs || []).forEach(function(row) {
        totalCount += parseInt(row.count || 0);
        totalCost  += parseFloat(row.cost || 0);
        tbody.innerHTML += '<tr><td>' + row.month + '</td><td class="text-center">' + row.count + '</td><td class="text-end">₱' + parseFloat(row.cost).toLocaleString('en-PH', {minimumFractionDigits:2}) + '</td></tr>';
    });
    document.getElementById('ctm_total_count').textContent = totalCount;
    document.getElementById('ctm_total_cost_foot').textContent = '₱' + totalCost.toLocaleString('en-PH', {minimumFractionDigits:2});

    new bootstrap.Modal(document.getElementById('costTrendModal')).show();
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/analytics.blade.php ENDPATH**/ ?>