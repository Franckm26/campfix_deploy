@extends('layouts.app')

@section('page_title')
<h2>Analytics</h2>
<p>Cost Tracking & Repair/Damage Analysis</p>
@endsection

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
/* SweetAlert2 Custom Styles for Modals */
.swal-analytics-modal {
    padding: 0 !important;
}

.swal-analytics-modal .swal2-popup {
    max-height: 90vh !important;
    margin: 0 !important;
    padding: 0 !important;
}

.swal-wide-popup {
    border-radius: 10px !important;
    box-shadow: 0 10px 40px rgba(0,0,0,0.4) !important;
    display: flex !important;
    flex-direction: column !important;
}

.swal2-title {
    font-size: 1.5rem !important;
    padding: 1rem 1.5rem !important;
    margin: 0 !important;
    border-bottom: 3px solid #e9ecef;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white !important;
    border-radius: 10px 10px 0 0 !important;
}

.swal2-html-container {
    margin: 0 !important;
    padding: 1.5rem !important;
    max-height: 75vh !important;
    overflow-y: auto !important;
    flex: 1 !important;
}

.swal2-close {
    font-size: 2rem !important;
    width: 3rem !important;
    height: 3rem !important;
    color: white !important;
    opacity: 0.9 !important;
}

.swal2-close:hover {
    opacity: 1 !important;
    color: white !important;
}

/* Make tables more readable in modals */
.swal2-popup table {
    font-size: 0.95rem !important;
}

.swal2-popup .table th {
    padding: 0.75rem !important;
}

.swal2-popup .table td {
    padding: 0.6rem !important;
}

.swal2-popup .card {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border: none;
}

.swal2-popup .card-body h3,
.swal2-popup .card-body h4,
.swal2-popup .card-body h5,
.swal2-popup .card-body h6 {
    font-weight: 700;
}

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
    flex-wrap: nowrap;
    overflow-x: auto;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 140px;
    flex-shrink: 0;
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

.filter-group select,
.filter-group .form-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9rem;
    background-color: white;
    cursor: pointer;
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
@endsection

@section('content')
<div class="container-fluid">
    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $totalConcerns }}</div>
            <div class="stat-label">Total Repairs/Damages</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value">₱{{ number_format($totalCost, 2) }}</div>
            <div class="stat-label">
                Total Cost
                <a href="#" data-bs-toggle="modal" data-bs-target="#costModal" style="color: #fff; text-decoration: underline;">View Details</a>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-value">{{ $locationStats->count() }}</div>
            <div class="stat-label">
                Frequently Fixed Room
                <a href="#" data-bs-toggle="modal" data-bs-target="#roomsModal" style="color: #fff; text-decoration: underline;">See Room</a>
            </div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-value">{{ $totalConcerns > 0 ? number_format($totalCost / $totalConcerns, 2) : 0 }}</div>
            <div class="stat-label">Average Cost per Repair</div>
        </div>
    </div>

    <!-- Export Button -->
    <div class="mb-3 text-end">
        <a href="{{ route('admin.analytics.export-pdf') }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Export to PDF
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('admin.analytics') }}" id="analyticsFilterForm" class="filter-form">
            <div class="filter-group">
                <label for="period">Period</label>
                <select name="period" id="period" class="form-select" onchange="document.getElementById('analyticsFilterForm').submit()">
                    <option value="">Custom</option>
                    <option value="monthly" {{ request('period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="quarterly" {{ request('period') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                    <option value="yearly" {{ request('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
            </div>
            <div class="filter-group" id="month-group">
                <label for="month">Month</label>
                <select name="month" id="month" class="form-select" onchange="document.getElementById('analyticsFilterForm').submit()">
                    <option value="">All Months</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="filter-group" id="month-from-group" style="display: none;">
                <label for="month_from">From Month</label>
                <select name="month_from" id="month_from" class="form-select" onchange="document.getElementById('analyticsFilterForm').submit()">
                    <option value="">Select Month</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month_from') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="filter-group" id="month-to-group" style="display: none;">
                <label for="month_to">To Month</label>
                <select name="month_to" id="month_to" class="form-select" onchange="document.getElementById('analyticsFilterForm').submit()">
                    <option value="">Select Month</option>
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ request('month_to') == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="filter-group">
                <label for="year">Year</label>
                <select name="year" id="year" class="form-select" onchange="document.getElementById('analyticsFilterForm').submit()">
                    <option value="">All Years</option>
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="filter-group" id="date-from-group">
                <label for="date_from">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" onchange="document.getElementById('analyticsFilterForm').submit()">
            </div>
            <div class="filter-group" id="date-to-group">
                <label for="date_to">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" onchange="document.getElementById('analyticsFilterForm').submit()">
            </div>
            <div class="filter-group" style="align-self: flex-end;">
                <a href="{{ route('admin.analytics') }}" class="btn btn-secondary" style="padding: 8px 15px; text-decoration: none; display: inline-block;">
                    <i class="fas fa-times"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <script>
    // Handle period selection to show/hide relevant filters
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.getElementById('period');
        const monthGroup = document.getElementById('month-group');
        const monthFromGroup = document.getElementById('month-from-group');
        const monthToGroup = document.getElementById('month-to-group');
        const dateFromGroup = document.getElementById('date-from-group');
        const dateToGroup = document.getElementById('date-to-group');
        
        function updateFilterVisibility() {
            const period = periodSelect.value;
            
            if (period === 'monthly') {
                monthGroup.style.display = 'flex';
                monthFromGroup.style.display = 'none';
                monthToGroup.style.display = 'none';
                dateFromGroup.style.display = 'none';
                dateToGroup.style.display = 'none';
            } else if (period === 'quarterly') {
                monthGroup.style.display = 'none';
                monthFromGroup.style.display = 'flex';
                monthToGroup.style.display = 'flex';
                dateFromGroup.style.display = 'none';
                dateToGroup.style.display = 'none';
            } else if (period === 'yearly') {
                monthGroup.style.display = 'none';
                monthFromGroup.style.display = 'none';
                monthToGroup.style.display = 'none';
                dateFromGroup.style.display = 'none';
                dateToGroup.style.display = 'none';
            } else {
                // Custom - show all
                monthGroup.style.display = 'flex';
                monthFromGroup.style.display = 'none';
                monthToGroup.style.display = 'none';
                dateFromGroup.style.display = 'flex';
                dateToGroup.style.display = 'flex';
            }
        }
        
        periodSelect.addEventListener('change', updateFilterVisibility);
        updateFilterVisibility(); // Initialize on page load
    });
    </script>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="analytics-card" style="cursor: pointer;" onclick="showLocationDetailsModal()">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-pie"></i> Repairs by Location
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="locationPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card" style="cursor: pointer;" onclick="showCostDetailsModal()">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-bar"></i> Cost by Location
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
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
            <div class="analytics-card" style="cursor: pointer;" onclick="showStatusDetailsModal()">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-pie"></i> Status Distribution
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="statusDoughnutChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card" style="cursor: pointer;" onclick="showMonthlyTrendModal()">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-area"></i> Monthly Trend
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

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
                @forelse($combinedLocationStats ?? [] as $stat)
                <tr>
                    <td>{{ $stat['location'] }}</td>
                    <td><span class="count-badge">{{ $stat['total_count'] }}</span></td>
                    <td><span class="cost-badge">₱{{ number_format($stat['total_cost'], 2) }}</span></td>
                    <td>₱{{ number_format($stat['total_count'] > 0 ? $stat['total_cost'] / $stat['total_count'] : 0, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No data found</td>
                </tr>
                @endforelse
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
        
        @if($reports->count() > 0)
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
                    @foreach($reports as $report)
                    <tr>
                        <td>{{ $report->location }}</td>
                        <td>{{ $report->damaged_part ?? 'N/A' }}</td>
                        <td>{{ $report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->format('M d, Y g:i A') : 'Not Fixed' }}</td>
                        <td><span class="cost-badge">₱{{ number_format($report->cost ?? 0, 2) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert-info">
            <i class="fas fa-info-circle"></i> No reports with location and date fixed data found for the selected period.
        </div>
        @endif
    </div>

    <!-- ── TREND ALERTS ─────────────────────────────────────────────── -->
    @if(isset($trendAlerts) && $trendAlerts->count() > 0)
    <div class="analytics-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="analytics-title" style="font-size:1rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                <i class="fas fa-bell text-danger me-2"></i> Alerts &amp; Notifications
                <span class="badge bg-danger ms-2">{{ $trendAlerts->count() }}</span>
            </div>
        </div>

        <div class="mb-4">
            @foreach($trendAlerts as $alert)
            @php
                $borderColor = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                $bgColor     = $alert['severity'] === 'critical' ? '#fef2f2' : ($alert['severity'] === 'warning' ? '#fff7ed' : '#fffbeb');
                $iconColor   = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                $timeAgo     = isset($alert['updated_at']) && $alert['updated_at'] ? \Carbon\Carbon::parse($alert['updated_at'])->diffForHumans(null, true, true) : 'recently';
            @endphp
            <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-left:4px solid {{ $borderColor }};background:{{ $bgColor }};border-radius:8px;margin-bottom:10px;cursor:pointer;"
                onclick="showCostTrendModal({{ json_encode($alert) }})">
                <div style="width:36px;height:36px;border-radius:50%;background:{{ $iconColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-triangle-exclamation" style="color:#fff;font-size:15px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:.95rem;color:#1e293b;">{{ $alert['alert_title'] ?? 'Trend Detected' }}</div>
                    <div style="font-size:.82rem;color:#64748b;">
                        @if(!empty($alert['top_issue'])){{ $alert['top_issue'] }} on {{ $alert['location'] }}@else{{ $alert['location'] }}@endif
                    </div>
                </div>
                <div style="font-size:.78rem;color:#94a3b8;white-space:nowrap;">{{ $timeAgo }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<!-- Cost Trend Modal (for alerts) -->
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

<!-- Rooms Modal -->
<div class="modal fade" id="roomsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Frequently Fixed Rooms</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($locationStats as $stat)
                <div class="room-item" style="padding: 10px; border-bottom: 1px solid #eee;">
                    <strong>{{ $stat['location'] }}</strong> - {{ $stat['count'] }} repairs, Total Cost: ₱{{ number_format($stat['total_cost'], 2) }}
                </div>
                @endforeach
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
                        <p class="h4 text-primary">{{ $totalConcerns }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Total Cost</h6>
                        <p class="h4 text-success">₱{{ number_format($totalCost, 2) }}</p>
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
                            @foreach($locationStats->sortByDesc('total_cost') as $stat)
                            <tr>
                                <td>{{ $stat['location'] }}</td>
                                <td>{{ $stat['count'] }}</td>
                                <td>₱{{ number_format($stat['total_cost'], 2) }}</td>
                            </tr>
                            @endforeach
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
@endsection

@section('scripts')
<script src="{{ asset('js/analytics-modals.js') }}?v={{ time() }}"></script>
<script>
(function() {
    // Set global variables for modal functions
    window.chartLocations = {!! json_encode($chartLocations ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.chartCounts = {!! json_encode($chartCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.chartCosts = {!! json_encode($chartCosts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.chartStatuses = {!! json_encode($chartStatuses ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.chartStatusCounts = {!! json_encode($chartStatusCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.monthlyStats = {!! json_encode(isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => $s->month, 'title' => $s->title, 'count' => $s->total_count])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.locationDetailedStats = {!! json_encode($locationStats ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};

    var locations = window.chartLocations;
    var counts    = {!! json_encode($chartCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var costs     = {!! json_encode($chartCosts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var statuses  = {!! json_encode($chartStatuses ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var statusCounts = {!! json_encode($chartStatusCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var monthly   = {!! json_encode(isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => $s->month, 'title' => $s->title, 'count' => $s->total_count])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var locationDetails = {!! json_encode($locationStats ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};

    var colors = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#4BC0C0'];

    function buildCharts() {
        if (typeof Chart === 'undefined') { setTimeout(buildCharts, 100); return; }

        // Pie — Repairs by Location
        var pieEl = document.getElementById('locationPieChart');
        if (pieEl && locations.length > 0) {
            new Chart(pieEl, { 
                type: 'pie', 
                data: { 
                    labels: locations, 
                    datasets: [{ 
                        data: counts, 
                        backgroundColor: colors, 
                        borderWidth: 2 
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    var location = context.label || '';
                                    var totalRepairs = context.parsed || 0;
                                    
                                    // Get item breakdown for this location
                                    var items = locationDetails.filter(function(item) {
                                        return item.location === location;
                                    });
                                    
                                    var lines = ['Repairs: ' + totalRepairs];
                                    
                                    // Add each item breakdown
                                    items.forEach(function(item) {
                                        lines.push(item.title + ' - ' + item.count);
                                    });
                                    
                                    // Add total cost
                                    lines.push('Cost: ₱' + (costs[context.dataIndex] || 0).toLocaleString('en-PH', {minimumFractionDigits: 2}));
                                    
                                    return lines;
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            padding: 12,
                            displayColors: true
                        }
                    } 
                } 
            });
        }

        // Bar — Cost by Location
        var barEl = document.getElementById('locationBarChart');
        if (barEl && locations.length > 0) {
            new Chart(barEl, { 
                type: 'bar', 
                data: { 
                    labels: locations, 
                    datasets: [{ 
                        label: 'Total Cost (₱)', 
                        data: costs, 
                        backgroundColor: '#36A2EB', 
                        borderWidth: 1 
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    scales: { 
                        y: { 
                            beginAtZero: true, 
                            ticks: { 
                                callback: function(v){ 
                                    return '₱'+v.toLocaleString(); 
                                } 
                            } 
                        } 
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return context[0].label;
                                },
                                label: function(context) {
                                    var value = context.parsed.y || 0;
                                    var repairs = counts[context.dataIndex] || 0;
                                    var avgCost = repairs > 0 ? (value / repairs) : 0;
                                    return [
                                        'Total Cost: ₱' + value.toLocaleString('en-PH', {minimumFractionDigits: 2}),
                                        'Total Repairs: ' + repairs,
                                        'Avg Cost: ₱' + avgCost.toLocaleString('en-PH', {minimumFractionDigits: 2})
                                    ];
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            padding: 12,
                            displayColors: true
                        }
                    }
                } 
            });
        }

        // Doughnut — Status Distribution
        var doughEl = document.getElementById('statusDoughnutChart');
        if (doughEl && statuses.length > 0) {
            new Chart(doughEl, { 
                type: 'doughnut', 
                data: { 
                    labels: statuses, 
                    datasets: [{ 
                        data: statusCounts, 
                        backgroundColor: colors, 
                        borderWidth: 2 
                    }] 
                }, 
                options: { 
                    responsive: true, 
                    plugins: { 
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    return context[0].label + ' Status';
                                },
                                label: function(context) {
                                    var value = context.parsed || 0;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = ((value / total) * 100).toFixed(1);
                                    return [
                                        'Count: ' + value + ' reports',
                                        'Percentage: ' + percentage + '%',
                                        'Total Reports: ' + total
                                    ];
                                }
                            },
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            padding: 12,
                            displayColors: true
                        }
                    } 
                } 
            });
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

// Modal functions are now loaded from analytics-modals.js (SweetAlert2 versions)
</script>
@endsection
