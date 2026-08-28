@extends('layouts.app')

@section('styles')
@include('dashboard.partials.inline-admin-css')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.analytics-card {
    background: var(--card-bg, #fff);
    border: 1px solid #dce3eb;
    border-radius: 7px;
    padding: 16px;
    box-shadow: 0 2px 8px rgba(15, 35, 58, 0.06);
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
    height: 220px;
    width: 100%;
}

.dashboard-analytics-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 5px 0 10px; }
.dashboard-analytics-heading h3 { margin: 0; color: #132b4d; font-size: 18px; }
.dashboard-analytics-heading p { margin: 2px 0 0; color: #6c7c91; font-size: 12px; }
.dashboard-analytics-heading .btn { border-radius: 5px; }
.analytics-subtitle { margin: -5px 0 10px; color: #738198; font-size: 11px; }
.analytics-interpretation { min-height: 50px; margin: 10px -16px -16px; padding: 10px 16px; border-top: 1px solid #e3e8ee; color: #51647d; background: #f8fafc; font-size: 11px; line-height: 1.45; }
.analytics-interpretation strong { color: #172d4d; }
.decision-snapshot { display: grid; }
.decision-snapshot-item { display: grid; grid-template-columns: 28px minmax(0, 1fr); gap: 9px; padding: 11px 0; border-bottom: 1px solid #e4e9ef; color: inherit; text-decoration: none; }
.decision-snapshot-item:last-child { border-bottom: 0; }
.decision-snapshot-item:hover strong { color: #1769e0; }
.decision-snapshot-item i { display: grid; width: 26px; height: 26px; place-items: center; border-radius: 50%; color: #fff; background: #1769e0; font-size: 11px; }
.decision-snapshot-item.warning i { background: #e99a00; }
.decision-snapshot-item.critical i { background: #d93645; }
.decision-snapshot-item strong { display: block; color: #182f50; font-size: 12px; }
.decision-snapshot-item span { display: block; margin-top: 2px; color: #6a7b91; font-size: 10px; line-height: 1.4; }
.dss-health-row { display: flex; gap: 10px; margin-bottom: 11px; }
.dss-health-row span { flex: 1 1 0; padding: 7px 9px; border-left: 3px solid #1769e0; color: #64758b; background: #f5f8fc; font-size: 10px; }
.dss-health-row strong { display: block; color: #142b4c; font-size: 15px; }

[data-theme="dark"] .analytics-card {
    background: #1a1a2e !important;
}

[data-theme="dark"] .analytics-title {
    color: #e0e0e0 !important;
}
</style>
@endsection

@section('page_title')
<div style="display:flex;align-items:center;gap:12px">
    @include('dashboard.partials.sti-logo')
    <h2 style="margin:0">Home</h2>
</div>
@endsection

@section('content')
<div class="container-fluid px-3">
    <!-- Quick Stats -->
    <div class="row mb-3 g-2">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-1">Pending Approval</h6>
                    <h3 class="mb-1">{{ $pendingEvents }}</h3>
                    <a href="{{ route('admin.events') }}" class="text-white text-decoration-underline small">Review Now</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-1">Upcoming Events</h6>
                    <h3 class="mb-1">{{ $approvedEvents }}</h3>
                    <a href="{{ route('events.calendar') }}" class="text-white text-decoration-underline small">View Calendar</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-1">Total Concerns</h6>
                    <h3 class="mb-1">{{ $totalConcerns }}</h3>
                    <a href="{{ route('admin.reports') }}" class="text-white text-decoration-underline small">View Reports</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3 px-3">
                    <h6 class="mb-2">Campus Overview</h6>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Total Concerns Reported</small>
                        <span class="badge bg-white text-info">{{ $totalConcerns }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small>Unresolved Concerns</small>
                        <span class="badge bg-white text-warning">{{ $pendingConcerns }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small>Upcoming Approved Events</small>
                        <span class="badge bg-white text-success">{{ $approvedEvents }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-analytics-heading">
        <div><h3><i class="fas fa-chart-line me-1"></i> Reports Decision Support</h3><p>Current workload, completion performance, and operational priorities</p></div>
        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.analytics') }}"><i class="fas fa-arrow-up-right-from-square me-1"></i> Full Analytics</a>
    </div>

    <div class="row mb-3 g-2">
        <div class="col-lg-4">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title"><i class="fas fa-chart-pie"></i> Status Distribution</div>
                </div>
                <div class="analytics-subtitle">Current report workload by workflow status</div>
                <div class="chart-container"><canvas id="dashboardStatusChart"></canvas></div>
                <div class="analytics-interpretation"><strong>Interpretation:</strong> {{ number_format($dashboardAnalytics['open']) }} open report(s); {{ number_format($dashboardAnalytics['resolution_rate'], 1) }}% resolved against the {{ $dashboardAnalytics['target_rate'] }}% target.</div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title"><i class="fas fa-chart-line"></i> Reports and Resolutions</div>
                </div>
                <div class="analytics-subtitle">Six-month submitted workload and completed work</div>
                <div class="dss-health-row">
                    <span>Total Reports<strong>{{ number_format($dashboardAnalytics['total']) }}</strong></span>
                    <span>Safety Hazards<strong>{{ number_format($dashboardAnalytics['hazards']) }}</strong></span>
                </div>
                <div class="chart-container" style="height: 180px;"><canvas id="dashboardTrendChart"></canvas></div>
                <div class="analytics-interpretation"><strong>Decision use:</strong> Compare incoming demand with completed work to determine whether staffing or escalation is needed.</div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title"><i class="fas fa-list-check"></i> Decision Priorities</div>
                </div>
                <div class="analytics-subtitle">Evidence requiring administrative attention</div>
                <div class="decision-snapshot">
                    @if($dashboardAnalytics['top_location'])
                        <a class="decision-snapshot-item {{ $dashboardAnalytics['top_location']['risk'] >= 12 ? 'critical' : ($dashboardAnalytics['top_location']['risk'] >= 6 ? 'warning' : '') }}" href="{{ route('admin.analytics') }}#locationRiskTable"><i class="fas fa-location-dot"></i><div><strong>Prioritize {{ $dashboardAnalytics['top_location']['location'] }}</strong><span>Risk {{ $dashboardAnalytics['top_location']['risk'] }}: {{ $dashboardAnalytics['top_location']['open'] }} open and {{ $dashboardAnalytics['top_location']['hazards'] }} hazard report(s).</span></div></a>
                    @endif
                    @if($dashboardAnalytics['resolution_rate'] < $dashboardAnalytics['target_rate'])
                        <a class="decision-snapshot-item warning" href="{{ route('admin.reports') }}"><i class="fas fa-gauge-high"></i><div><strong>Resolution is below target</strong><span>{{ number_format($dashboardAnalytics['resolution_rate'], 1) }}% resolved versus {{ $dashboardAnalytics['target_rate'] }}%. Review ageing open work.</span></div></a>
                    @endif
                    @if($dashboardAnalytics['top_category'])
                        <a class="decision-snapshot-item" href="{{ route('admin.analytics') }}"><i class="fas fa-boxes-stacked"></i><div><strong>Plan for {{ $dashboardAnalytics['top_category']['category'] }}</strong><span>{{ $dashboardAnalytics['top_category']['total'] }} report(s), with {{ $dashboardAnalytics['top_category']['open'] }} still open.</span></div></a>
                    @endif
                    @if($dashboardAnalytics['hazards'] > 0)
                        <a class="decision-snapshot-item critical" href="{{ route('admin.reports') }}"><i class="fas fa-shield-halved"></i><div><strong>Review safety hazards first</strong><span>{{ $dashboardAnalytics['hazards'] }} hazard-related report(s) require priority inspection.</span></div></a>
                    @endif
                    @if($dashboardAnalytics['total'] === 0)
                        <div class="decision-snapshot-item"><i class="fas fa-circle-info"></i><div><strong>No report evidence yet</strong><span>Decision priorities will appear when reports are submitted.</span></div></div>
                    @endif
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
                        <button type="button" class="btn btn-sm btn-primary me-2" onclick="openEventRequestModal()">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                        <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-calendar"></i> Full Calendar
                        </a>
                    </div>
                </div>
                <div class="card-body p-3" style="max-height: 400px; overflow-y: auto;">
                    @if($upcomingEventsList->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcomingEventsList->take(5) as $event)
                            <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-2">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold text-primary" style="font-size: 0.9rem;">{{ $event->location }} - {{ \Carbon\Carbon::parse($event->event_date)->format('m/d/Y') }}</div>
                                    <div class="text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $event->location }}
                                        @if($event->department)
                                            <span class="ms-2"><i class="fas fa-building me-1"></i>{{ $event->department }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-success mb-1" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($event->event_date)->format('m/d/Y') }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($approvedEvents > 5)
                            <div class="text-center mt-2">
                                <a href="{{ route('events.calendar') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-calendar"></i> View All Events ({{ $approvedEvents }} total)
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                            <h6 class="text-muted">No Upcoming Events</h6>
                            <p class="text-muted small mb-2">There are no approved events scheduled for the coming days.</p>
                            <a href="{{ route('events.calendar') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-calendar"></i> View Events Calendar
                            </a>
                        </div>
                    @endif
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
                        <a href="{{ route('admin.reports') }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-file-alt me-2"></i> Reports
                        </a>
                        <a href="{{ route('admin.analytics') }}" class="btn btn-outline-info btn-sm text-start">
                            <i class="fas fa-chart-line me-2"></i> Analytics
                        </a>
                        <a href="{{ route('admin.events') }}" class="btn btn-outline-warning btn-sm text-start">
                            <i class="fas fa-calendar-alt me-2"></i> Events
                        </a>
                        <a href="{{ route('events.my') }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="fas fa-calendar me-2"></i> My Events
                        </a>
                        <a href="{{ route('events.calendar') }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="fas fa-calendar-check me-2"></i> Upcoming Events
                        </a>
                        <a href="{{ route('admin.management') }}" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="fas fa-tools me-2"></i> Management
                        </a>
                        <a href="{{ route('history.index') }}" class="btn btn-outline-dark btn-sm text-start">
                            <i class="fas fa-history me-2"></i> My History
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
    if (typeof Chart === 'undefined') return;
    var status = @json($dashboardAnalytics['status']);
    var trend = @json($dashboardAnalytics['trend']);
    var reportsUrl = @json(route('admin.reports'));
    var statusColors = { 'Pending': '#1769e0', 'Assigned': '#e99a00', 'In Progress': '#10a6a6', 'Resolved': '#148a58' };

    var statusCanvas = document.getElementById('dashboardStatusChart');
    if (statusCanvas) new Chart(statusCanvas, {
        type: 'doughnut',
        data: {
            labels: status.map(function (item) { return item.status; }),
            datasets: [{
                data: status.map(function (item) { return Number(item.count); }),
                backgroundColor: status.map(function (item) { return statusColors[item.status] || '#7557c5'; }),
                borderColor: '#fff',
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 12, font: { size: 10 } } } },
            onClick: function (event, elements, chart) {
                if (!elements.length) return;
                window.location.href = reportsUrl + '?status=' + encodeURIComponent(chart.data.labels[elements[0].index]);
            }
        }
    });

    var trendCanvas = document.getElementById('dashboardTrendChart');
    if (trendCanvas) new Chart(trendCanvas, {
        type: 'line',
        data: {
            labels: trend.map(function (item) { return item.label; }),
            datasets: [
                { label: 'Reports', data: trend.map(function (item) { return Number(item.reports); }), borderColor: '#1769e0', backgroundColor: 'rgba(23,105,224,.10)', fill: true, tension: .3, pointRadius: 3, borderWidth: 2 },
                { label: 'Resolved', data: trend.map(function (item) { return Number(item.resolved); }), borderColor: '#148a58', backgroundColor: 'transparent', tension: .3, pointRadius: 3, borderWidth: 2 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 10 } } } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e7ebf0' } }, x: { grid: { display: false }, ticks: { font: { size: 9 } } } }
        }
    });

    // Monthly Trend Chart
    var monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx) {
        // Process monthly stats data
        var monthlyData = @json($monthlyStats ?? []);
        var months = [];
        var categories = {};
        var statusBreakdown = {}; // Store status breakdown for tooltips
        
        // Extract unique months and categories
        monthlyData.forEach(function(item) {
            if (!months.includes(item.month)) {
                months.push(item.month);
            }
            if (!categories[item.title]) {
                categories[item.title] = {};
                statusBreakdown[item.title] = {};
            }
            if (!categories[item.title][item.month]) {
                categories[item.title][item.month] = 0;
                statusBreakdown[item.title][item.month] = {};
            }
            categories[item.title][item.month] += item.count;
            
            // Store status breakdown
            if (!statusBreakdown[item.title][item.month][item.status]) {
                statusBreakdown[item.title][item.month][item.status] = 0;
            }
            statusBreakdown[item.title][item.month][item.status] += item.count;
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
                fill: true,
                statusData: statusBreakdown[category] // Attach status breakdown
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
                        intersect: false,
                        callbacks: {
                            label: function(ctx) {
                                var dataset = ctx.dataset;
                                var month = ctx.label;
                                var total = ctx.parsed.y;
                                var statusData = dataset.statusData[month] || {};
                                
                                var lines = [dataset.label + ': ' + total + (total === 1 ? ' report' : ' reports')];
                                
                                // Add status breakdown if there are multiple statuses
                                var statuses = Object.keys(statusData);
                                if (statuses.length > 0) {
                                    var resolved = statusData['Resolved'] || 0;
                                    var pending = statusData['Pending'] || 0;
                                    var inProgress = statusData['In Progress'] || 0;
                                    
                                    if (resolved > 0) lines.push('  ✓ Resolved: ' + resolved);
                                    if (inProgress > 0) lines.push('  ⟳ In Progress: ' + inProgress);
                                    if (pending > 0) lines.push('  ⏱ Pending: ' + pending);
                                }
                                
                                return lines;
                            }
                        }
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

@endsection
