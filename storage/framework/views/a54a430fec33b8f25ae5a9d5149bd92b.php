

<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.analytics-card {
    background: var(--card-bg, #fff);
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    height: 100%;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.analytics-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--text-color, #333);
}

.analytics-title i {
    font-size: 0.85rem;
    margin-right: 5px;
}

.chart-container {
    position: relative;
    height: 200px;
    width: 100%;
}

[data-theme="dark"] .analytics-card {
    background: #1a1a2e !important;
}

[data-theme="dark"] .analytics-title {
    color: #e0e0e0 !important;
}
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title'); ?>
<div style="display:flex;align-items:center;gap:12px">
    <img src="<?php echo e(asset('Campfix/Images/images.png')); ?>" alt="STI Logo" style="height:40px">
    <h2 style="margin:0">Home</h2>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3">
    <!-- Quick Stats -->
    <div class="row mb-3 g-2">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-1">Pending Approval</h6>
                    <h3 class="mb-1"><?php echo e($pendingEvents); ?></h3>
                    <a href="<?php echo e(route('admin.events')); ?>" class="text-white text-decoration-underline small">Review Now</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-1">Upcoming Events</h6>
                    <h3 class="mb-1"><?php echo e($approvedEvents); ?></h3>
                    <a href="<?php echo e(route('events.calendar')); ?>" class="text-white text-decoration-underline small">View Calendar</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-1">Total Concerns</h6>
                    <h3 class="mb-1"><?php echo e($totalConcerns); ?></h3>
                    <a href="<?php echo e(route('admin.reports')); ?>" class="text-white text-decoration-underline small">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-2">Campus Overview</h6>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Total Concerns Reported</small>
                        <span class="badge bg-white text-info"><?php echo e($totalConcerns); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Unresolved Concerns</small>
                        <span class="badge bg-white text-warning"><?php echo e($pendingConcerns); ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small>Upcoming Approved Events</small>
                        <span class="badge bg-white text-success"><?php echo e($approvedEvents); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Graphs Section -->
    <div class="row mb-3 g-2">
        <div class="col-md-4">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-pie"></i> Repairs by Location
                    </div>
                </div>
                <div class="chart-container" style="height: 200px;">
                    <canvas id="locationPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-bar"></i> Cost by Location
                    </div>
                </div>
                <div class="chart-container" style="height: 200px;">
                    <canvas id="locationBarChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-area"></i> Monthly Trend
                    </div>
                </div>
                <div class="chart-container" style="height: 200px;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Events List and Quick Actions -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="mb-0"><i class="fas fa-calendar-check me-1"></i> Upcoming Approved Events</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#eventRequestModal">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                        <a href="<?php echo e(route('events.calendar')); ?>" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-calendar"></i> Full Calendar
                        </a>
                    </div>
                </div>
                <div class="card-body p-3" style="max-height: 400px; overflow-y: auto;">
                    <?php if($upcomingEventsList->count() > 0): ?>
                        <div class="list-group list-group-flush">
                            <?php $__currentLoopData = $upcomingEventsList->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-2">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold text-primary" style="font-size: 0.9rem;"><?php echo e($event->location); ?> - <?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></div>
                                    <div class="text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo e($event->location); ?>

                                        <?php if($event->department): ?>
                                            <span class="ms-2"><i class="fas fa-building me-1"></i><?php echo e($event->department); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-success mb-1" style="font-size: 0.75rem;"><?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - 
                                        <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?>

                                    </div>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php if($approvedEvents > 5): ?>
                            <div class="text-center mt-2">
                                <a href="<?php echo e(route('events.calendar')); ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-calendar"></i> View All Events (<?php echo e($approvedEvents); ?> total)
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                            <h6 class="text-muted">No Upcoming Events</h6>
                            <p class="text-muted small mb-2">There are no approved events scheduled for the coming days.</p>
                            <a href="<?php echo e(route('events.calendar')); ?>" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-calendar"></i> View Events Calendar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header py-2 px-3">
                    <span class="mb-0"><i class="fas fa-bolt me-1"></i> Quick Actions</span>
                </div>
                <div class="card-body p-2">
                    <div class="d-grid gap-2">
                        <a href="<?php echo e(route('admin.reports')); ?>" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-file-alt me-2"></i> Reports
                        </a>
                        <a href="<?php echo e(route('admin.analytics')); ?>" class="btn btn-outline-info btn-sm text-start">
                            <i class="fas fa-chart-line me-2"></i> Analytics
                        </a>
                        <a href="<?php echo e(route('admin.events')); ?>" class="btn btn-outline-warning btn-sm text-start">
                            <i class="fas fa-calendar-alt me-2"></i> Events
                        </a>
                        <a href="<?php echo e(route('events.my')); ?>" class="btn btn-outline-success btn-sm text-start">
                            <i class="fas fa-calendar me-2"></i> My Events
                        </a>
                        <a href="<?php echo e(route('events.calendar')); ?>" class="btn btn-outline-success btn-sm text-start">
                            <i class="fas fa-calendar-check me-2"></i> Upcoming Events
                        </a>
                        <a href="<?php echo e(route('admin.management')); ?>" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="fas fa-tools me-2"></i> Management
                        </a>
                        <a href="/admin/logs" class="btn btn-outline-dark btn-sm text-start">
                            <i class="fas fa-history me-2"></i> Audit Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Request Modal is defined in layouts/app.blade.php and reused here -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Location Pie Chart (Repairs by Location)
    var locationPieCtx = document.getElementById('locationPieChart');
    if (locationPieCtx) {
        new Chart(locationPieCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($chartLocations ?? [], 15, 512) ?>,
                datasets: [{
                    data: <?php echo json_encode($chartCounts ?? [], 15, 512) ?>,
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#FF9F40'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Location Bar Chart (Cost by Location)
    var locationBarCtx = document.getElementById('locationBarChart');
    if (locationBarCtx) {
        new Chart(locationBarCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chartLocations ?? [], 15, 512) ?>,
                datasets: [{
                    label: 'Total Cost',
                    data: <?php echo json_encode($chartCosts ?? [], 15, 512) ?>,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Monthly Trend Chart
    var monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx) {
        // Process monthly stats data
        var monthlyData = <?php echo json_encode($monthlyStats ?? [], 15, 512) ?>;
        var months = [];
        var categories = {};
        
        // Extract unique months and categories
        monthlyData.forEach(function(item) {
            if (!months.includes(item.month)) {
                months.push(item.month);
            }
            if (!categories[item.title]) {
                categories[item.title] = {};
            }
            categories[item.title][item.month] = item.total_count;
        });
        
        // Sort months chronologically
        months.sort();
        
        // Prepare datasets for each category
        var datasets = [];
        var colors = [
            { border: '#36A2EB', bg: 'rgba(54, 162, 235, 0.1)' },  // Blue - Aircon
            { border: '#FF6384', bg: 'rgba(255, 99, 132, 0.1)' },  // Pink - null
            { border: '#FFCE56', bg: 'rgba(255, 206, 86, 0.1)' },  // Yellow - Window
            { border: '#4BC0C0', bg: 'rgba(75, 192, 192, 0.1)' },  // Teal - Door
            { border: '#9966FF', bg: 'rgba(153, 102, 255, 0.1)' }, // Purple
            { border: '#FF9F40', bg: 'rgba(255, 159, 64, 0.1)' }   // Orange
        ];
        
        var colorIndex = 0;
        for (var category in categories) {
            var data = months.map(function(month) {
                return categories[category][month] || 0;
            });
            
            var color = colors[colorIndex % colors.length];
            datasets.push({
                label: category || 'Uncategorized',
                data: data,
                borderColor: color.border,
                backgroundColor: color.bg,
                tension: 0.4,
                fill: true
            });
            colorIndex++;
        }
        
        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/dashboard/building-admin.blade.php ENDPATH**/ ?>