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

    <!-- Export Button and Date Range Filter -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="mainAnalyticsRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-calendar-alt me-2"></i>
                <span id="mainAnalyticsRangeLabel">
                    @if(request('date_from') && request('date_to'))
                        {{ \Carbon\Carbon::parse(request('date_from'))->format('M d, Y') }} - {{ \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') }}
                    @elseif(request('period') == 'monthly' && request('month') && request('year'))
                        {{ \Carbon\Carbon::create()->month(request('month'))->format('F') }} {{ request('year') }}
                    @elseif(request('period') == 'yearly' && request('year'))
                        Year {{ request('year') }}
                    @else
                        All Time
                    @endif
                </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="mainAnalyticsRangeDropdown" style="min-width: 250px;">
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('last7days', event)">Last 7 days</a></li>
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('last28days', event)">Last 28 days</a></li>
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('last90days', event)">Last 90 days</a></li>
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('last6months', event)">Last 6 months</a></li>
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('last12months', event)">Last 12 months</a></li>
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('thisyear', event)">This year</a></li>
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('lastyear', event)">Last year</a></li>
                <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRange('alltime', event)">All time</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="px-3 py-2">
                    <label class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600;">Custom Range</label>
                    <div class="mb-2">
                        <input type="date" id="mainAnalyticsCustomDateFrom" class="form-control form-control-sm" style="font-size: 0.85rem;">
                    </div>
                    <div class="mb-2">
                        <input type="date" id="mainAnalyticsCustomDateTo" class="form-control form-control-sm" style="font-size: 0.85rem;">
                    </div>
                    <button class="btn btn-primary btn-sm w-100" onclick="applyMainAnalyticsCustomRange(event)" style="font-size: 0.85rem;">Apply</button>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.analytics.export-pdf') }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Export to PDF
        </a>
    </div>

    <script>
    // Main Analytics Date Range Functions
    window.setMainAnalyticsRange = function(range, event) {
        if (event) event.preventDefault();
        
        var today = new Date();
        var dateFrom, dateTo;
        var url = new URL(window.location.href);
        
        // Clear existing date parameters
        url.searchParams.delete('period');
        url.searchParams.delete('month');
        url.searchParams.delete('month_from');
        url.searchParams.delete('month_to');
        url.searchParams.delete('year');
        url.searchParams.delete('date_from');
        url.searchParams.delete('date_to');
        
        if (range === 'alltime') {
            // No parameters needed for all time
            window.location.href = url.toString();
            return;
        }
        
        switch(range) {
            case 'last7days':
                dateFrom = new Date(today);
                dateFrom.setDate(today.getDate() - 7);
                dateTo = today;
                break;
            case 'last28days':
                dateFrom = new Date(today);
                dateFrom.setDate(today.getDate() - 28);
                dateTo = today;
                break;
            case 'last90days':
                dateFrom = new Date(today);
                dateFrom.setDate(today.getDate() - 90);
                dateTo = today;
                break;
            case 'last6months':
                dateFrom = new Date(today);
                dateFrom.setMonth(today.getMonth() - 6);
                dateTo = today;
                break;
            case 'last12months':
                dateFrom = new Date(today);
                dateFrom.setMonth(today.getMonth() - 12);
                dateTo = today;
                break;
            case 'thisyear':
                dateFrom = new Date(today.getFullYear(), 0, 1);
                dateTo = today;
                break;
            case 'lastyear':
                dateFrom = new Date(today.getFullYear() - 1, 0, 1);
                dateTo = new Date(today.getFullYear() - 1, 11, 31);
                break;
        }
        
        // Format dates as YYYY-MM-DD
        var dateFromStr = dateFrom.toISOString().split('T')[0];
        var dateToStr = dateTo.toISOString().split('T')[0];
        
        url.searchParams.set('date_from', dateFromStr);
        url.searchParams.set('date_to', dateToStr);
        
        window.location.href = url.toString();
    };
    
    window.applyMainAnalyticsCustomRange = function(event) {
        if (event) event.preventDefault();
        
        var dateFromInput = document.getElementById('mainAnalyticsCustomDateFrom').value;
        var dateToInput = document.getElementById('mainAnalyticsCustomDateTo').value;
        
        if (!dateFromInput || !dateToInput) {
            alert('Please select both start and end dates');
            return;
        }
        
        var dateFrom = new Date(dateFromInput);
        var dateTo = new Date(dateToInput);
        
        if (dateFrom > dateTo) {
            alert('Start date must be before end date');
            return;
        }
        
        var url = new URL(window.location.href);
        
        // Clear existing date parameters
        url.searchParams.delete('period');
        url.searchParams.delete('month');
        url.searchParams.delete('month_from');
        url.searchParams.delete('month_to');
        url.searchParams.delete('year');
        
        url.searchParams.set('date_from', dateFromInput);
        url.searchParams.set('date_to', dateToInput);
        
        window.location.href = url.toString();
    };
    </script>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-pie"></i> Repairs by Location
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
                    </div>
                    <div class="analytics-actions">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="locationRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <span id="locationRangeLabel">Last 6 months</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="locationRangeDropdown">
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('last7days', event)">Last 7 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('last28days', event)">Last 28 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('last90days', event)">Last 90 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('last6months', event)">Last 6 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('last12months', event)">Last 12 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('thisyear', event)">This year</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('lastyear', event)">Last year</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="px-3 py-2">
                                    <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Custom Range</label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="locationCustomDateFrom" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="locationCustomDateTo" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100" onclick="applyLocationCustomRange(event)" style="font-size: 0.75rem;">Apply</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="chart-container" style="cursor: pointer;" onclick="showLocationDetailsModal()">
                    <canvas id="locationPieChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-bar"></i> Period Comparison
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
                    </div>
                    <div class="analytics-actions">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="periodRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <span id="periodRangeLabel">Last 6 months</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="periodRangeDropdown">
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('last7days', event)">Last 7 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('last28days', event)">Last 28 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('last90days', event)">Last 90 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('last6months', event)">Last 6 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('last12months', event)">Last 12 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('thisyear', event)">This year</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('lastyear', event)">Last year</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="px-3 py-2">
                                    <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Custom Range</label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="customDateFrom" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="customDateTo" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100" onclick="applyCustomRange(event)" style="font-size: 0.75rem;">Apply</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="chart-container" style="cursor: pointer;" onclick="showPeriodComparisonModal()">
                    <canvas id="periodComparisonChart"></canvas>
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
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
                    </div>
                    <div class="analytics-actions">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="statusRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <span id="statusRangeLabel">Last 6 months</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="statusRangeDropdown">
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('last7days', event)">Last 7 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('last28days', event)">Last 28 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('last90days', event)">Last 90 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('last6months', event)">Last 6 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('last12months', event)">Last 12 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('thisyear', event)">This year</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('lastyear', event)">Last year</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="px-3 py-2">
                                    <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Custom Range</label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="statusCustomDateFrom" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="statusCustomDateTo" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100" onclick="applyStatusCustomRange(event)" style="font-size: 0.75rem;">Apply</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="chart-container" style="cursor: pointer;" onclick="showStatusDetailsModal()">
                    <canvas id="statusDoughnutChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="analytics-card">
                <div class="analytics-header">
                    <div class="analytics-title">
                        <i class="fas fa-chart-area"></i> Monthly Trend
                        <small class="text-muted" style="font-size: 0.7rem; font-weight: normal;">(Click for details)</small>
                    </div>
                    <div class="analytics-actions">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="trendRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <span id="trendRangeLabel">Last 6 months</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="trendRangeDropdown">
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('last7days', event)">Last 7 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('last28days', event)">Last 28 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('last90days', event)">Last 90 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('last6months', event)">Last 6 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('last12months', event)">Last 12 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('thisyear', event)">This year</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('lastyear', event)">Last year</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="px-3 py-2">
                                    <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Custom Range</label>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="trendCustomDateFrom" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <div class="d-flex gap-2 mb-2">
                                        <input type="date" id="trendCustomDateTo" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100" onclick="applyTrendCustomRange(event)" style="font-size: 0.75rem;">Apply</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="chart-container" style="cursor: pointer;" onclick="showMonthlyTrendModal()">
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
    window.monthlyCostData = {!! json_encode(isset($monthlyCostData) ? $monthlyCostData->map(fn($s) => ['month' => $s->month, 'count' => $s->count, 'total_cost' => $s->total_cost])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
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
        var locationPieChart = null;
        
        function buildLocationChart(data) {
            if (!pieEl) return;
            
            var locations = data.chartLocations || [];
            var counts = data.chartCounts || [];
            var costs = data.chartCosts || [];
            var locationDetails = data.locationStats || [];
            
            if (locationPieChart) {
                locationPieChart.destroy();
            }
            
            if (locations.length > 0) {
                locationPieChart = new Chart(pieEl, { 
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
                                        
                                        var items = locationDetails.filter(function(item) {
                                            return item.location === location;
                                        });
                                        
                                        var lines = ['Repairs: ' + totalRepairs];
                                        
                                        items.forEach(function(item) {
                                            lines.push(item.title + ' - ' + item.count);
                                        });
                                        
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
        }
        
        // Initialize location chart
        buildLocationChart({
            chartLocations: locations,
            chartCounts: counts,
            chartCosts: costs,
            locationStats: locationDetails
        });
        
        // Location date range functions
        window.setLocationRange = function(range, event) {
            if (event) event.preventDefault();
            
            var labels = {
                'last7days': 'Last 7 days',
                'last28days': 'Last 28 days',
                'last90days': 'Last 90 days',
                'last6months': 'Last 6 months',
                'last12months': 'Last 12 months',
                'thisyear': 'This year',
                'lastyear': 'Last year'
            };
            
            document.getElementById('locationRangeLabel').textContent = labels[range] || range;
            
            var today = new Date();
            var dateFrom, dateTo;
            
            switch(range) {
                case 'last7days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 7);
                    dateTo = today;
                    break;
                case 'last28days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 28);
                    dateTo = today;
                    break;
                case 'last90days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 90);
                    dateTo = today;
                    break;
                case 'last6months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 6);
                    dateTo = today;
                    break;
                case 'last12months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 12);
                    dateTo = today;
                    break;
                case 'thisyear':
                    dateFrom = new Date(today.getFullYear(), 0, 1);
                    dateTo = today;
                    break;
                case 'lastyear':
                    dateFrom = new Date(today.getFullYear() - 1, 0, 1);
                    dateTo = new Date(today.getFullYear() - 1, 11, 31);
                    break;
            }
            
            fetchAndUpdateChart('location', dateFrom, dateTo);
        };
        
        window.applyLocationCustomRange = function(event) {
            if (event) event.preventDefault();
            
            var dateFromInput = document.getElementById('locationCustomDateFrom').value;
            var dateToInput = document.getElementById('locationCustomDateTo').value;
            
            if (!dateFromInput || !dateToInput) {
                alert('Please select both start and end dates');
                return;
            }
            
            var dateFrom = new Date(dateFromInput);
            var dateTo = new Date(dateToInput);
            
            if (dateFrom > dateTo) {
                alert('Start date must be before end date');
                return;
            }
            
            var fromStr = dateFrom.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var toStr = dateTo.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('locationRangeLabel').textContent = fromStr + ' - ' + toStr;
            
            var dropdownEl = document.getElementById('locationRangeDropdown');
            var dropdown = bootstrap.Dropdown.getInstance(dropdownEl);
            if (dropdown) dropdown.hide();
            
            fetchAndUpdateChart('location', dateFrom, dateTo);
        };
        
        function fetchAndUpdateChart(chartType, dateFrom, dateTo) {
            var dateFromStr = dateFrom.toISOString().split('T')[0];
            var dateToStr = dateTo.toISOString().split('T')[0];
            
            var params = new URLSearchParams();
            params.append('ajax', '1');
            params.append('date_from', dateFromStr);
            params.append('date_to', dateToStr);
            
            fetch('/admin/analytics?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                window.chartLocations = data.chartLocations || [];
                window.chartCounts = data.chartCounts || [];
                window.chartCosts = data.chartCosts || [];
                window.chartStatuses = data.chartStatuses || [];
                window.chartStatusCounts = data.chartStatusCounts || [];
                window.monthlyStats = data.monthlyStats || [];
                window.monthlyCostData = data.monthlyCostData || [];
                window.locationDetailedStats = data.locationStats || [];
                
                if (chartType === 'location') {
                    buildLocationChart(data);
                } else if (chartType === 'status') {
                    buildStatusChart(data);
                } else if (chartType === 'trend') {
                    buildTrendChart(data);
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
        }

        // Bar — Period Comparison (YouTube Analytics Style)
        var periodComparisonEl = document.getElementById('periodComparisonChart');
        var periodComparisonChart = null;
        var currentPeriodRange = 'last6months';
        
        // Make functions global
        window.setPeriodRange = function(range, event) {
            if (event) event.preventDefault();
            currentPeriodRange = range;
            
            // Update label
            var labels = {
                'last7days': 'Last 7 days',
                'last28days': 'Last 28 days',
                'last90days': 'Last 90 days',
                'last6months': 'Last 6 months',
                'last12months': 'Last 12 months',
                'thisyear': 'This year',
                'lastyear': 'Last year'
            };
            
            document.getElementById('periodRangeLabel').textContent = labels[range] || range;
            
            // Calculate date range
            var today = new Date();
            var dateFrom, dateTo;
            
            switch(range) {
                case 'last7days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 7);
                    dateTo = today;
                    break;
                case 'last28days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 28);
                    dateTo = today;
                    break;
                case 'last90days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 90);
                    dateTo = today;
                    break;
                case 'last6months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 6);
                    dateTo = today;
                    break;
                case 'last12months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 12);
                    dateTo = today;
                    break;
                case 'thisyear':
                    dateFrom = new Date(today.getFullYear(), 0, 1);
                    dateTo = today;
                    break;
                case 'lastyear':
                    dateFrom = new Date(today.getFullYear() - 1, 0, 1);
                    dateTo = new Date(today.getFullYear() - 1, 11, 31);
                    break;
            }
            
            updatePeriodComparisonChart(dateFrom, dateTo);
        };
        
        window.applyCustomRange = function(event) {
            if (event) event.preventDefault();
            
            var dateFromInput = document.getElementById('customDateFrom').value;
            var dateToInput = document.getElementById('customDateTo').value;
            
            if (!dateFromInput || !dateToInput) {
                alert('Please select both start and end dates');
                return;
            }
            
            var dateFrom = new Date(dateFromInput);
            var dateTo = new Date(dateToInput);
            
            if (dateFrom > dateTo) {
                alert('Start date must be before end date');
                return;
            }
            
            // Update label
            var fromStr = dateFrom.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var toStr = dateTo.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('periodRangeLabel').textContent = fromStr + ' - ' + toStr;
            
            currentPeriodRange = 'custom';
            
            // Close dropdown
            var dropdownEl = document.getElementById('periodRangeDropdown');
            var dropdown = bootstrap.Dropdown.getInstance(dropdownEl);
            if (dropdown) dropdown.hide();
            
            updatePeriodComparisonChart(dateFrom, dateTo);
        };
        
        function updatePeriodComparisonChart(dateFrom, dateTo) {
            if (!periodComparisonEl) return;
            
            // Format dates for API
            var dateFromStr = dateFrom.toISOString().split('T')[0];
            var dateToStr = dateTo.toISOString().split('T')[0];
            
            // Fetch data from server
            var params = new URLSearchParams();
            params.append('ajax', '1');
            params.append('date_from', dateFromStr);
            params.append('date_to', dateToStr);
            
            fetch('/admin/analytics?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                var monthlyCostData = data.monthlyCostData || [];
                buildPeriodComparisonChart(monthlyCostData, dateFrom, dateTo);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
                // Use default data if fetch fails
                var monthlyCostData = @json($monthlyCostData ?? []);
                buildPeriodComparisonChart(monthlyCostData, dateFrom, dateTo);
            });
        }
        
        function buildPeriodComparisonChart(monthlyCostData, dateFrom, dateTo) {
            if (!periodComparisonEl) return;
            
            // Generate month labels between dateFrom and dateTo
            var monthLabels = [];
            var monthKeys = [];
            var monthCosts = [];
            var monthCounts = [];
            
            var currentMonth = new Date(dateFrom.getFullYear(), dateFrom.getMonth(), 1);
            var endMonth = new Date(dateTo.getFullYear(), dateTo.getMonth(), 1);
            
            while (currentMonth <= endMonth) {
                var monthKey = currentMonth.toISOString().slice(0, 7); // YYYY-MM
                var monthLabel = currentMonth.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                
                monthKeys.push(monthKey);
                monthLabels.push(monthLabel);
                monthCosts.push(0);
                monthCounts.push(0);
                
                currentMonth.setMonth(currentMonth.getMonth() + 1);
            }
            
            // Populate data
            monthlyCostData.forEach(function(item) {
                var monthIndex = monthKeys.indexOf(item.month);
                if (monthIndex !== -1) {
                    monthCosts[monthIndex] = parseFloat(item.total_cost) || 0;
                    monthCounts[monthIndex] = parseInt(item.count) || 0;
                }
            });
            
            // Destroy existing chart
            if (periodComparisonChart) {
                periodComparisonChart.destroy();
            }
            
            // Create new chart
            periodComparisonChart = new Chart(periodComparisonEl, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Total Cost (₱)',
                        data: monthCosts,
                        backgroundColor: '#36A2EB',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) {
                                    return '₱' + v.toLocaleString();
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
                                    var repairs = monthCounts[context.dataIndex] || 0;
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
        
        // Initialize with default range (last 6 months)
        if (periodComparisonEl) {
            window.setPeriodRange('last6months');
        }

        // Doughnut — Status Distribution
        var doughEl = document.getElementById('statusDoughnutChart');
        var statusDoughnutChart = null;
        
        function buildStatusChart(data) {
            if (!doughEl) return;
            
            var statuses = data.chartStatuses || [];
            var statusCounts = data.chartStatusCounts || [];
            
            if (statusDoughnutChart) {
                statusDoughnutChart.destroy();
            }
            
            if (statuses.length > 0) {
                statusDoughnutChart = new Chart(doughEl, { 
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
        }
        
        // Initialize status chart
        buildStatusChart({
            chartStatuses: statuses,
            chartStatusCounts: statusCounts
        });
        
        // Status date range functions
        window.setStatusRange = function(range, event) {
            if (event) event.preventDefault();
            
            var labels = {
                'last7days': 'Last 7 days',
                'last28days': 'Last 28 days',
                'last90days': 'Last 90 days',
                'last6months': 'Last 6 months',
                'last12months': 'Last 12 months',
                'thisyear': 'This year',
                'lastyear': 'Last year'
            };
            
            document.getElementById('statusRangeLabel').textContent = labels[range] || range;
            
            var today = new Date();
            var dateFrom, dateTo;
            
            switch(range) {
                case 'last7days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 7);
                    dateTo = today;
                    break;
                case 'last28days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 28);
                    dateTo = today;
                    break;
                case 'last90days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 90);
                    dateTo = today;
                    break;
                case 'last6months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 6);
                    dateTo = today;
                    break;
                case 'last12months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 12);
                    dateTo = today;
                    break;
                case 'thisyear':
                    dateFrom = new Date(today.getFullYear(), 0, 1);
                    dateTo = today;
                    break;
                case 'lastyear':
                    dateFrom = new Date(today.getFullYear() - 1, 0, 1);
                    dateTo = new Date(today.getFullYear() - 1, 11, 31);
                    break;
            }
            
            fetchAndUpdateChart('status', dateFrom, dateTo);
        };
        
        window.applyStatusCustomRange = function(event) {
            if (event) event.preventDefault();
            
            var dateFromInput = document.getElementById('statusCustomDateFrom').value;
            var dateToInput = document.getElementById('statusCustomDateTo').value;
            
            if (!dateFromInput || !dateToInput) {
                alert('Please select both start and end dates');
                return;
            }
            
            var dateFrom = new Date(dateFromInput);
            var dateTo = new Date(dateToInput);
            
            if (dateFrom > dateTo) {
                alert('Start date must be before end date');
                return;
            }
            
            var fromStr = dateFrom.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var toStr = dateTo.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('statusRangeLabel').textContent = fromStr + ' - ' + toStr;
            
            var dropdownEl = document.getElementById('statusRangeDropdown');
            var dropdown = bootstrap.Dropdown.getInstance(dropdownEl);
            if (dropdown) dropdown.hide();
            
            fetchAndUpdateChart('status', dateFrom, dateTo);
        };

        // Line — Monthly Trend (per issue type)
        var lineEl = document.getElementById('monthlyTrendChart');
        var monthlyTrendChart = null;
        
        function buildTrendChart(data) {
            if (!lineEl) return;
            
            var monthly = data.monthlyStats || [];
            
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
            
            if (monthlyTrendChart) {
                monthlyTrendChart.destroy();
            }

            monthlyTrendChart = new Chart(lineEl, {
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
        
        // Initialize trend chart
        buildTrendChart({ monthlyStats: monthly });
        
        // Trend date range functions
        window.setTrendRange = function(range, event) {
            if (event) event.preventDefault();
            
            var labels = {
                'last7days': 'Last 7 days',
                'last28days': 'Last 28 days',
                'last90days': 'Last 90 days',
                'last6months': 'Last 6 months',
                'last12months': 'Last 12 months',
                'thisyear': 'This year',
                'lastyear': 'Last year'
            };
            
            document.getElementById('trendRangeLabel').textContent = labels[range] || range;
            
            var today = new Date();
            var dateFrom, dateTo;
            
            switch(range) {
                case 'last7days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 7);
                    dateTo = today;
                    break;
                case 'last28days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 28);
                    dateTo = today;
                    break;
                case 'last90days':
                    dateFrom = new Date(today);
                    dateFrom.setDate(today.getDate() - 90);
                    dateTo = today;
                    break;
                case 'last6months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 6);
                    dateTo = today;
                    break;
                case 'last12months':
                    dateFrom = new Date(today);
                    dateFrom.setMonth(today.getMonth() - 12);
                    dateTo = today;
                    break;
                case 'thisyear':
                    dateFrom = new Date(today.getFullYear(), 0, 1);
                    dateTo = today;
                    break;
                case 'lastyear':
                    dateFrom = new Date(today.getFullYear() - 1, 0, 1);
                    dateTo = new Date(today.getFullYear() - 1, 11, 31);
                    break;
            }
            
            fetchAndUpdateChart('trend', dateFrom, dateTo);
        };
        
        window.applyTrendCustomRange = function(event) {
            if (event) event.preventDefault();
            
            var dateFromInput = document.getElementById('trendCustomDateFrom').value;
            var dateToInput = document.getElementById('trendCustomDateTo').value;
            
            if (!dateFromInput || !dateToInput) {
                alert('Please select both start and end dates');
                return;
            }
            
            var dateFrom = new Date(dateFromInput);
            var dateTo = new Date(dateToInput);
            
            if (dateFrom > dateTo) {
                alert('Start date must be before end date');
                return;
            }
            
            var fromStr = dateFrom.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var toStr = dateTo.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            document.getElementById('trendRangeLabel').textContent = fromStr + ' - ' + toStr;
            
            var dropdownEl = document.getElementById('trendRangeDropdown');
            var dropdown = bootstrap.Dropdown.getInstance(dropdownEl);
            if (dropdown) dropdown.hide();
            
            fetchAndUpdateChart('trend', dateFrom, dateTo);
        };
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
