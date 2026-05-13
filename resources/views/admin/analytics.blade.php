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
    padding: 0.7rem 0.5rem !important;
    font-size: 0.9rem !important;
}

.swal2-popup .table td {
    padding: 0.6rem 0.5rem !important;
    word-wrap: break-word !important;
    overflow-wrap: break-word !important;
    font-size: 0.9rem !important;
}

/* Modal-specific table styles */
.modal-compact-table {
    width: 100% !important;
    table-layout: fixed !important;
    font-size: 0.75rem !important;
    margin-bottom: 0 !important;
}

.modal-compact-table thead {
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
    background: #f8f9fa !important;
}

.modal-compact-table th {
    padding: 8px 4px !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
    white-space: nowrap !important;
    border-bottom: 2px solid #dee2e6 !important;
}

.modal-compact-table td {
    padding: 6px 4px !important;
    font-size: 0.7rem !important;
    vertical-align: middle !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
}

.modal-compact-table tbody tr:hover {
    background-color: #f8f9fa !important;
}

/* Remove table-responsive scroll for modal tables */
.modal-table-wrapper {
    overflow: visible !important;
    max-height: none !important;
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
    height: 350px;
    width: 100%;
}

.chart-container canvas {
    max-height: 350px;
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
    border-radius: 12px;
    font-size: 0.8rem;
    white-space: nowrap;
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

    <!-- Export Button, Room Filter, and Date Range Filter -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <div class="d-flex gap-2">
            <!-- Room Filter -->
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="mainAnalyticsRoomDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-door-open me-2"></i>
                    <span id="mainAnalyticsRoomLabel">
                        @if(request('room_filter'))
                            {{ request('room_filter') }}
                        @else
                            All Rooms
                        @endif
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-start" aria-labelledby="mainAnalyticsRoomDropdown" style="max-height: 300px; overflow-y: auto;">
                    <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRoom('all', event)">All Rooms</a></li>
                    <li><hr class="dropdown-divider"></li>
                    @foreach($combinedLocationStats ?? [] as $stat)
                    <li><a class="dropdown-item" href="#" onclick="setMainAnalyticsRoom('{{ addslashes($stat['location']) }}', event)">{{ $stat['location'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            
            <!-- Date Range Filter -->
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
        </div>
        <a href="{{ route('admin.analytics.export-pdf') }}?{{ http_build_query(request()->all()) }}" class="btn btn-danger" target="_blank">
            <i class="fas fa-file-pdf"></i> Export to PDF
        </a>
    </div>

    <script>
    // Main Analytics Room Filter Function
    window.setMainAnalyticsRoom = function(room, event) {
        if (event) event.preventDefault();
        
        var url = new URL(window.location.href);
        
        if (room === 'all') {
            // Remove room filter
            url.searchParams.delete('room_filter');
        } else {
            // Set room filter
            url.searchParams.set('room_filter', room);
        }
        
        // Reload the page with new parameters
        window.location.href = url.toString();
    };
    
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
            // No parameters needed for all time - reload page without date filters
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
        
        // Reload the page with new parameters
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
                        <i class="fas fa-chart-pie"></i> Repairs Breakdown
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
                                <li><a class="dropdown-item" href="#" onclick="setLocationRange('alltime', event)">All time</a></li>
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
                
                <!-- Category Breakdown Metrics -->
                <div class="row mb-3">
                    @php
                        $topCategories = $costByCategory->take(3);
                    @endphp
                    @foreach($topCategories as $index => $category)
                    <div class="col-4">
                        <div class="text-center p-2" style="background: {{ ['#f0f4ff', '#fff8f0', '#f0fff4'][$index] }}; border-radius: 8px; border-left: 3px solid {{ ['#667eea', '#f39c12', '#27ae60'][$index] }};">
                            <div style="font-size: 0.7rem; color: #666; margin-bottom: 4px;">{{ $category['category'] }}</div>
                            <div style="font-size: 1.1rem; font-weight: bold; color: {{ ['#667eea', '#f39c12', '#27ae60'][$index] }};">
                                ₱{{ number_format($category['total_cost'], 0) }}
                            </div>
                            <div style="font-size: 0.65rem; color: #888;">{{ $category['count'] }} tickets • {{ number_format($category['percentage'], 1) }}%</div>
                        </div>
                    </div>
                    @endforeach
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
                                <li><a class="dropdown-item" href="#" onclick="setPeriodRange('allyears', event)">All years</a></li>
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
                        <i class="fas fa-chart-pie"></i> Status Distribution & Response Time
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
                                <li><a class="dropdown-item" href="#" onclick="setStatusRange('alltime', event)">All time</a></li>
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
                
                <!-- Response Time Metrics -->
                <div class="row mb-3">
                    <div class="col-4">
                        <div class="text-center p-2" style="background: #f0f7ff; border-radius: 8px; border-left: 3px solid #3498db;">
                            <div style="font-size: 0.7rem; color: #666; margin-bottom: 4px;">Submit to Assign</div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: #3498db;">
                                @php
                                    $totalSeconds = floor($avgSubmittedToAssigned * 3600);
                                    $hours = floor($totalSeconds / 3600);
                                    $minutes = floor(($totalSeconds % 3600) / 60);
                                    $seconds = $totalSeconds % 60;
                                    echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                @endphp
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2" style="background: #fff8f0; border-radius: 8px; border-left: 3px solid #f39c12;">
                            <div style="font-size: 0.7rem; color: #666; margin-bottom: 4px;">Assign to Resolve</div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: #f39c12;">
                                @php
                                    $totalSeconds = floor($avgAssignedToResolved * 3600);
                                    $hours = floor($totalSeconds / 3600);
                                    $minutes = floor(($totalSeconds % 3600) / 60);
                                    $seconds = $totalSeconds % 60;
                                    echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                @endphp
                            </div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-center p-2" style="background: #f0fff4; border-radius: 8px; border-left: 3px solid #27ae60;">
                            <div style="font-size: 0.7rem; color: #666; margin-bottom: 4px;">Total Time</div>
                            <div style="font-size: 1.2rem; font-weight: bold; color: #27ae60;">
                                @php
                                    $totalSeconds = floor($avgTotalTime * 3600);
                                    $hours = floor($totalSeconds / 3600);
                                    $minutes = floor(($totalSeconds % 3600) / 60);
                                    $seconds = $totalSeconds % 60;
                                    echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                @endphp
                            </div>
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
                                <li><a class="dropdown-item" href="#" onclick="setTrendRange('alltime', event)">All time</a></li>
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
            <div class="analytics-actions d-flex gap-2">
                <!-- Export PDF Button -->
                <a href="{{ route('admin.analytics.combined-location-pdf') }}?{{ http_build_query(request()->all()) }}" 
                   class="btn btn-sm btn-danger" target="_blank" style="font-size: 0.85rem;">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
                
                <!-- Room Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="locationTableRoomDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                        <i class="fas fa-door-open me-1"></i>
                        <span id="locationTableRoomLabel">All Rooms</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="locationTableRoomDropdown" style="max-height: 300px; overflow-y: auto;">
                        <li><a class="dropdown-item" href="#" onclick="filterLocationTable('all', event)">All Rooms</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @foreach($combinedLocationStats ?? [] as $stat)
                        <li><a class="dropdown-item" href="#" onclick="filterLocationTable('{{ addslashes($stat['location']) }}', event)">{{ $stat['location'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                
                <!-- Date Range Filter -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="locationTableRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem;">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <span id="locationTableRangeLabel">
                            @if(request('date_from') && request('date_to'))
                                {{ \Carbon\Carbon::parse(request('date_from'))->format('M d') }} - {{ \Carbon\Carbon::parse(request('date_to'))->format('M d, Y') }}
                            @else
                                All Time
                            @endif
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="locationTableRangeDropdown" style="min-width: 250px;">
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('last7days', event)">Last 7 days</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('last28days', event)">Last 28 days</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('last90days', event)">Last 90 days</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('last6months', event)">Last 6 months</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('last12months', event)">Last 12 months</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('thisyear', event)">This year</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('lastyear', event)">Last year</a></li>
                        <li><a class="dropdown-item" href="#" onclick="setLocationTableRange('alltime', event)">All time</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="px-3 py-2">
                            <label class="form-label mb-1" style="font-size: 0.85rem; font-weight: 600;">Custom Range</label>
                            <div class="mb-2">
                                <input type="date" id="locationTableCustomDateFrom" class="form-control form-control-sm" style="font-size: 0.85rem;">
                            </div>
                            <div class="mb-2">
                                <input type="date" id="locationTableCustomDateTo" class="form-control form-control-sm" style="font-size: 0.85rem;">
                            </div>
                            <button class="btn btn-primary btn-sm w-100" onclick="applyLocationTableCustomRange(event)" style="font-size: 0.85rem;">Apply</button>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <table class="analytics-table" id="locationCostTable">
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
                <tr style="cursor: pointer; transition: all 0.2s;" 
                    data-location="{{ $stat['location'] }}"
                    onclick="showLocationTicketsModal('{{ addslashes($stat['location']) }}', {{ $stat['total_count'] }}, {{ $stat['total_cost'] }})"
                    onmouseover="this.style.backgroundColor='#f0f4ff'"
                    onmouseout="this.style.backgroundColor=''">
                    <td><strong>{{ $stat['location'] }}</strong></td>
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

    <!-- ========== ADVANCED ANALYTICS SECTION ========== -->
    <div class="row mb-4 mt-5">
        <div class="col-12">
            <h3 style="color: #667eea; font-weight: 700; border-bottom: 3px solid #667eea; padding-bottom: 10px; margin-bottom: 30px;">
                <i class="fas fa-chart-line"></i> Advanced Analytics
            </h3>
        </div>
    </div>

    <!-- Staff Performance Metrics -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-users"></i> Staff Performance Metrics
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Staff Member</th>
                        <th class="text-center">Tickets Resolved</th>
                        <th class="text-end">Total Cost</th>
                        <th class="text-end">Avg Resolution Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffPerformance as $staff)
                    <tr>
                        <td><strong>{{ $staff['staff_name'] }}</strong></td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $staff['tickets_resolved'] }}</span>
                        </td>
                        <td class="text-end">PHP {{ number_format($staff['total_cost'], 2) }}</td>
                        <td class="text-end">{{ $staff['avg_resolution_time'] }} hrs</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cost Trend Analysis -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-chart-area"></i> Cost Trend Analysis (Last 6 Months)
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <canvas id="costTrendChart" height="80"></canvas>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Tickets</th>
                                <th class="text-end">Total Cost</th>
                                <th class="text-end">Avg Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($costTrendData as $trend)
                            <tr>
                                <td><strong>{{ $trend['month'] }}</strong></td>
                                <td class="text-center">{{ $trend['count'] }}</td>
                                <td class="text-end">PHP {{ number_format($trend['total_cost'], 2) }}</td>
                                <td class="text-end">PHP {{ number_format($trend['avg_cost'], 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
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
    window.statusReportIds = {!! json_encode($statusReportIds ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.monthlyStats = {!! json_encode(isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => $s->month, 'title' => $s->title, 'status' => $s->status, 'count' => $s->count])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.monthlyCostData = {!! json_encode(isset($monthlyCostData) ? $monthlyCostData->map(fn($s) => ['month' => $s->month, 'count' => $s->count, 'total_cost' => $s->total_cost])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.locationDetailedStats = {!! json_encode($locationStatsDetailed ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.responseTimeStats = {!! json_encode($responseTimeStats ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    window.avgSubmittedToAssigned = {{ $avgSubmittedToAssigned ?? 0 }};
    window.avgAssignedToResolved = {{ $avgAssignedToResolved ?? 0 }};
    window.avgTotalTime = {{ $avgTotalTime ?? 0 }};

    var locations = window.chartLocations;
    var counts    = {!! json_encode($chartCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var costs     = {!! json_encode($chartCosts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var statuses  = {!! json_encode($chartStatuses ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var statusCounts = {!! json_encode($chartStatusCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var monthly   = {!! json_encode(isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => $s->month, 'title' => $s->title, 'status' => $s->status, 'count' => $s->count])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
    var locationDetails = {!! json_encode($locationStats ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};

    var colors = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#4BC0C0'];

    function buildCharts() {
        if (typeof Chart === 'undefined') { setTimeout(buildCharts, 100); return; }

        // Pie — Repairs by Location (with Category metrics above)
        var pieEl = document.getElementById('locationPieChart');
        var locationPieChart = null;
        
        // Store category data globally for modal use
        window.categoryData = {
            categories: @json($costByCategory->pluck('category')),
            counts: @json($costByCategory->pluck('count')),
            costs: @json($costByCategory->pluck('total_cost')),
            avgCosts: @json($costByCategory->pluck('avg_cost')),
            percentages: @json($costByCategory->pluck('percentage'))
        };
        
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
                'lastyear': 'Last year',
                'alltime': 'All time'
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
                case 'alltime':
                    // Get all data from the beginning (2020 or earliest data)
                    dateFrom = new Date(2020, 0, 1);
                    dateTo = today;
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
                window.locationDetailedStats = data.locationStatsDetailed || [];
                
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
                'lastyear': 'Last year',
                'allyears': 'All years',
                'alltime': 'All time'
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
                case 'allyears':
                    // Get all data from the beginning (2020 or earliest data)
                    dateFrom = new Date(2020, 0, 1);
                    dateTo = today;
                    break;
                case 'alltime':
                    // Get all data from the beginning (2020 or earliest data)
                    dateFrom = new Date(2020, 0, 1);
                    dateTo = today;
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
            
            // Check if we're in "All years" mode (range spans multiple years)
            var yearSpan = dateTo.getFullYear() - dateFrom.getFullYear();
            var isAllYears = currentPeriodRange === 'allyears' || yearSpan > 2;
            
            var labels = [];
            var keys = [];
            var costs = [];
            var counts = [];
            
            if (isAllYears) {
                // Group by year for "All years" view
                var startYear = dateFrom.getFullYear();
                var endYear = dateTo.getFullYear();
                
                for (var year = startYear; year <= endYear; year++) {
                    keys.push(year.toString());
                    labels.push(year.toString());
                    costs.push(0);
                    counts.push(0);
                }
                
                // Aggregate monthly data into yearly data
                monthlyCostData.forEach(function(item) {
                    var itemYear = item.month.substring(0, 4); // Extract year from YYYY-MM
                    var yearIndex = keys.indexOf(itemYear);
                    if (yearIndex !== -1) {
                        costs[yearIndex] += parseFloat(item.total_cost) || 0;
                        counts[yearIndex] += parseInt(item.count) || 0;
                    }
                });
            } else {
                // Group by month for other views
                var currentMonth = new Date(dateFrom.getFullYear(), dateFrom.getMonth(), 1);
                var endMonth = new Date(dateTo.getFullYear(), dateTo.getMonth(), 1);
                
                while (currentMonth <= endMonth) {
                    var monthKey = currentMonth.toISOString().slice(0, 7); // YYYY-MM
                    var monthLabel = currentMonth.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    
                    keys.push(monthKey);
                    labels.push(monthLabel);
                    costs.push(0);
                    counts.push(0);
                    
                    currentMonth.setMonth(currentMonth.getMonth() + 1);
                }
                
                // Populate data
                monthlyCostData.forEach(function(item) {
                    var monthIndex = keys.indexOf(item.month);
                    if (monthIndex !== -1) {
                        costs[monthIndex] = parseFloat(item.total_cost) || 0;
                        counts[monthIndex] = parseInt(item.count) || 0;
                    }
                });
            }
            
            // Destroy existing chart
            if (periodComparisonChart) {
                periodComparisonChart.destroy();
            }
            
            // Create new chart
            periodComparisonChart = new Chart(periodComparisonEl, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total Cost (₱)',
                        data: costs,
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
        
        // Initialize Period Comparison chart on page load
        // Determine the date range from URL parameters or use default (all time)
        (function() {
            var urlParams = new URLSearchParams(window.location.search);
            var dateFrom = urlParams.get('date_from');
            var dateTo = urlParams.get('date_to');
            
            if (dateFrom && dateTo) {
                // Use the date range from URL
                var fromDate = new Date(dateFrom);
                var toDate = new Date(dateTo);
                updatePeriodComparisonChart(fromDate, toDate);
            } else {
                // No date filter = All time (from 2020 to now)
                var today = new Date();
                var allTimeFrom = new Date(2020, 0, 1);
                updatePeriodComparisonChart(allTimeFrom, today);
            }
        })();

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
                                        return [
                                            'Count: ' + value + ' reports',
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
                'lastyear': 'Last year',
                'alltime': 'All time'
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
                case 'alltime':
                    // Get all data from the beginning (2020 or earliest data)
                    dateFrom = new Date(2020, 0, 1);
                    dateTo = today;
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

            // Group by issue title and month, keeping status breakdown
            var issueMap = {};
            var statusBreakdown = {}; // Store status breakdown for tooltips
            
            monthly.forEach(function(item) {
                if (!issueMap[item.title]) {
                    issueMap[item.title] = {};
                    statusBreakdown[item.title] = {};
                }
                if (!issueMap[item.title][item.month]) {
                    issueMap[item.title][item.month] = 0;
                    statusBreakdown[item.title][item.month] = {};
                }
                issueMap[item.title][item.month] += item.count;
                
                // Store status breakdown
                if (!statusBreakdown[item.title][item.month][item.status]) {
                    statusBreakdown[item.title][item.month][item.status] = 0;
                }
                statusBreakdown[item.title][item.month][item.status] += item.count;
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
                    statusData: statusBreakdown[title] // Attach status breakdown to dataset
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
                    maintainAspectRatio: false,
                    layout: { padding: { right: 90, bottom: 10 } },
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
                                padding: 12,
                                boxWidth: 10,
                                boxHeight: 10,
                                font: { size: 12 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    var dataset = ctx.dataset;
                                    var monthKey = monthLabels[ctx.dataIndex].key;
                                    var total = ctx.parsed.y;
                                    var statusData = dataset.statusData[monthKey] || {};
                                    
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
                'lastyear': 'Last year',
                'alltime': 'All time'
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
                case 'alltime':
                    // Get all data from the beginning (2020 or earliest data)
                    dateFrom = new Date(2020, 0, 1);
                    dateTo = today;
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
    
    // Sync individual chart filters with the main filter on page load
    // This reads the URL parameters and sets all chart filters accordingly
    (function() {
        var urlParams = new URLSearchParams(window.location.search);
        var dateFrom = urlParams.get('date_from');
        var dateTo = urlParams.get('date_to');
        
        if (dateFrom && dateTo) {
            // Custom date range - update all chart labels
            var fromDate = new Date(dateFrom);
            var toDate = new Date(dateTo);
            var fromStr = fromDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var toStr = toDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var rangeLabel = fromStr + ' - ' + toStr;
            
            document.getElementById('locationRangeLabel').textContent = rangeLabel;
            document.getElementById('periodRangeLabel').textContent = rangeLabel;
            document.getElementById('statusRangeLabel').textContent = rangeLabel;
            document.getElementById('trendRangeLabel').textContent = rangeLabel;
        } else {
            // No date filter = All time
            document.getElementById('locationRangeLabel').textContent = 'All time';
            document.getElementById('periodRangeLabel').textContent = 'All time';
            document.getElementById('statusRangeLabel').textContent = 'All time';
            document.getElementById('trendRangeLabel').textContent = 'All time';
        }
    })();
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

// Show Location Tickets Modal function
function showLocationTicketsModal(location, totalCount, totalCost) {
    // Show loading state
    Swal.fire({
        title: 'Loading...',
        html: 'Fetching ticket details...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Build URL with current filters
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.set('location_filter', location);
    urlParams.set('ajax', '1');
    
    const baseUrl = '{{ route("admin.analytics") }}';
    const fetchUrl = baseUrl + '?' + urlParams.toString();
    
    // Fetch tickets for this location via AJAX
    fetch(fetchUrl)
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Fetch URL:', fetchUrl);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            console.log('Tickets count:', data.tickets ? data.tickets.length : 0);
            console.log('Debug info:', data.debug);
            
            const tickets = data.tickets || [];
            
            let tableRows = '';
            if (tickets.length > 0) {
                console.log('First ticket:', tickets[0]);
                tickets.forEach(ticket => {
                    // Handle different status formats
                    const status = (ticket.status || '').toLowerCase();
                    const statusBadge = status === 'resolved' 
                        ? '<span class="badge bg-success">Resolved</span>' 
                        : status === 'in_progress' || status === 'in progress'
                        ? '<span class="badge bg-warning">In Progress</span>'
                        : status === 'pending'
                        ? '<span class="badge bg-secondary">Pending</span>'
                        : '<span class="badge bg-info">' + ticket.status + '</span>';
                    
                    const cost = ticket.cost ? '₱' + parseFloat(ticket.cost).toLocaleString('en-PH', {minimumFractionDigits:2}) : 'N/A';
                    const resolvedDate = ticket.resolved_at 
                        ? new Date(ticket.resolved_at).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit'})
                        : 'Not Fixed';
                    
                    const damagePart = ticket.damaged_part || 'N/A';
                    const issue = ticket.title || 'N/A';
                    // Format ticket ID with leading zeros (e.g., #0019)
                    const ticketId = ticket.id ? '#' + String(ticket.id).padStart(4, '0') : 'N/A';
                    
                    tableRows += '<tr>' +
                        '<td class="text-center" style="width: 80px; font-size: 0.85rem;"><strong>' + ticketId + '</strong></td>' +
                        '<td style="width: 150px; font-size: 0.9rem;">' + damagePart + '</td>' +
                        '<td style="width: 180px; font-size: 0.9rem;">' + issue + '</td>' +
                        '<td class="text-center" style="width: 100px;">' + statusBadge + '</td>' +
                        '<td class="text-center" style="width: 150px; font-size: 0.85rem;">' + resolvedDate + '</td>' +
                        '<td class="text-end" style="width: 110px;"><span class="cost-badge">' + cost + '</span></td>' +
                        '</tr>';
                });
            } else {
                tableRows = '<tr><td colspan="6" class="text-center text-muted">No tickets found for this location</td></tr>';
            }
            
            const avgCost = totalCount > 0 ? (totalCost / totalCount).toFixed(2) : '0.00';
            
            // Export PDF function for location
            window.exportLocationPDF = function() {
                const url = new URL(window.location.href);
                const dateFrom = url.searchParams.get('date_from');
                const dateTo = url.searchParams.get('date_to');
                
                let pdfUrl = '{{ route("admin.analytics.location-detail-pdf") }}';
                const params = new URLSearchParams();
                
                params.append('location', location);
                if (dateFrom) params.append('date_from', dateFrom);
                if (dateTo) params.append('date_to', dateTo);
                
                if (params.toString()) {
                    pdfUrl += '?' + params.toString();
                }
                
                window.open(pdfUrl, '_blank');
            };
            
            Swal.fire({
                title: '<i class="fas fa-map-marker-alt me-2"></i>' + location,
                html: '<div style="text-align: left;">' +
                    '<div class="mb-3 d-flex justify-content-end">' +
                        '<button onclick="exportLocationPDF()" class="btn btn-danger btn-sm">' +
                            '<i class="fas fa-file-pdf me-1"></i>Export PDF' +
                        '</button>' +
                    '</div>' +
                    '<div class="row mb-4">' +
                        '<div class="col-4">' +
                            '<div class="card border-primary">' +
                                '<div class="card-body text-center">' +
                                    '<h3 class="text-primary mb-0">' + totalCount + '</h3>' +
                                    '<small class="text-muted">Total Tickets</small>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-4">' +
                            '<div class="card border-success">' +
                                '<div class="card-body text-center">' +
                                    '<h3 class="text-success mb-0">₱' + parseFloat(totalCost).toLocaleString('en-PH', {minimumFractionDigits:2}) + '</h3>' +
                                    '<small class="text-muted">Total Cost</small>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                        '<div class="col-4">' +
                            '<div class="card border-info">' +
                                '<div class="card-body text-center">' +
                                    '<h3 class="text-info mb-0">₱' + parseFloat(avgCost).toLocaleString('en-PH', {minimumFractionDigits:2}) + '</h3>' +
                                    '<small class="text-muted">Avg per Ticket</small>' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<h5 class="mb-3"><i class="fas fa-list me-2"></i>Ticket Details</h5>' +
                    '<div class="table-responsive" style="max-height: 400px; overflow-y: auto;">' +
                        '<table class="table table-hover table-sm" style="table-layout: fixed; width: 100%;">' +
                            '<thead class="table-light sticky-top">' +
                                '<tr>' +
                                    '<th class="text-center" style="width: 80px; font-size: 0.85rem;">Ticket #</th>' +
                                    '<th style="width: 150px; font-size: 0.9rem;">Damaged Part</th>' +
                                    '<th style="width: 180px; font-size: 0.9rem;">Issue</th>' +
                                    '<th class="text-center" style="width: 100px; font-size: 0.9rem;">Status</th>' +
                                    '<th class="text-center" style="width: 150px; font-size: 0.85rem;">Date Fixed</th>' +
                                    '<th class="text-end" style="width: 110px; font-size: 0.9rem;">Cost</th>' +
                                '</tr>' +
                            '</thead>' +
                            '<tbody>' +
                                tableRows +
                            '</tbody>' +
                        '</table>' +
                    '</div>' +
                '</div>',
                width: '900px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-wide-popup',
                    container: 'swal-analytics-modal'
                }
            });
        })
        .catch(error => {
            console.error('Error fetching location tickets:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load ticket details. Please try again.',
                confirmButtonColor: '#667eea'
            });
        });
}

// Location Table Filter Functions
function filterLocationTable(room, event) {
    if (event) event.preventDefault();
    
    const table = document.getElementById('locationCostTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    // Update label
    document.getElementById('locationTableRoomLabel').textContent = room === 'all' ? 'All Rooms' : room;
    
    // Filter rows
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const location = row.getAttribute('data-location');
        
        if (room === 'all' || location === room) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

function setLocationTableRange(range, event) {
    if (event) event.preventDefault();
    
    const today = new Date();
    let dateFrom, dateTo;
    const url = new URL(window.location.href);
    
    // Clear existing date parameters
    url.searchParams.delete('period');
    url.searchParams.delete('month');
    url.searchParams.delete('month_from');
    url.searchParams.delete('month_to');
    url.searchParams.delete('year');
    url.searchParams.delete('date_from');
    url.searchParams.delete('date_to');
    
    if (range === 'alltime') {
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
    
    const dateFromStr = dateFrom.toISOString().split('T')[0];
    const dateToStr = dateTo.toISOString().split('T')[0];
    
    url.searchParams.set('date_from', dateFromStr);
    url.searchParams.set('date_to', dateToStr);
    
    window.location.href = url.toString();
}

function applyLocationTableCustomRange(event) {
    if (event) event.preventDefault();
    
    const dateFromInput = document.getElementById('locationTableCustomDateFrom').value;
    const dateToInput = document.getElementById('locationTableCustomDateTo').value;
    
    if (!dateFromInput || !dateToInput) {
        alert('Please select both start and end dates');
        return;
    }
    
    const dateFrom = new Date(dateFromInput);
    const dateTo = new Date(dateToInput);
    
    if (dateFrom > dateTo) {
        alert('Start date must be before end date');
        return;
    }
    
    const url = new URL(window.location.href);
    
    // Clear existing date parameters
    url.searchParams.delete('period');
    url.searchParams.delete('month');
    url.searchParams.delete('month_from');
    url.searchParams.delete('month_to');
    url.searchParams.delete('year');
    
    url.searchParams.set('date_from', dateFromInput);
    url.searchParams.set('date_to', dateToInput);
    
    window.location.href = url.toString();
}

// Modal functions are now loaded from analytics-modals.js (SweetAlert2 versions)

// ========== ADVANCED ANALYTICS CHARTS ==========

// Cost Trend Chart
const costTrendCtx = document.getElementById('costTrendChart');
if (costTrendCtx) {
    new Chart(costTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($costTrendData->pluck('month')) !!},
            datasets: [
                {
                    label: 'Total Cost',
                    data: {!! json_encode($costTrendData->pluck('total_cost')) !!},
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Ticket Count',
                    data: {!! json_encode($costTrendData->pluck('count')) !!},
                    borderColor: '#f39c12',
                    backgroundColor: 'rgba(243, 156, 18, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: { display: true, text: 'Cost (PHP)' }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: { display: true, text: 'Ticket Count' },
                    grid: { drawOnChartArea: false }
                }
            }
        }
    });
}

// Status Distribution & Response Time Modal
function showStatusDetailsModal() {
    const responseTimeData = @json($responseTimeStats);
    
    // Build response time table with timestamps and HH:MM:SS format
    let responseTimeTable = '<div class="modal-table-wrapper mt-3"><table class="table table-sm table-hover modal-compact-table"><thead class="table-light"><tr><th style="width: 100px;">Ticket</th><th style="width: 70px;">Room</th><th style="width: 95px;">Created</th><th style="width: 95px;">Assigned</th><th style="width: 95px;">Resolved</th><th style="width: 65px;">Submit→Assign</th><th style="width: 65px;">Assign→Resolve</th><th style="width: 60px;">Total</th><th style="width: 80px;">Staff</th></tr></thead><tbody>';
    
    responseTimeData.forEach(function(item) {
        const ticketIssue = '#' + String(item.id).padStart(4, '0') + ' - ' + item.title;
        responseTimeTable += '<tr>' +
            '<td title="' + ticketIssue + '">' + ticketIssue + '</td>' +
            '<td title="' + item.location + '">' + item.location + '</td>' +
            '<td>' + item.created_at + '</td>' +
            '<td>' + item.assigned_at + '</td>' +
            '<td>' + item.resolved_at + '</td>' +
            '<td>' + item.submitted_to_assigned_formatted + '</td>' +
            '<td>' + item.assigned_to_resolved_formatted + '</td>' +
            '<td><strong>' + item.total_time_formatted + '</strong></td>' +
            '<td title="' + item.assigned_to_name + '">' + item.assigned_to_name + '</td>' +
            '</tr>';
    });
    
    responseTimeTable += '</tbody></table></div>';
    
    // Build status distribution table
    const statuses = @json($chartStatuses);
    const statusCounts = @json($chartStatusCounts);
    const statusReportIds = @json($statusReportIds);
    
    console.log('Status Distribution Data:', { statuses, statusCounts, statusReportIds });
    
    // Define the desired order
    const statusOrder = ['Pending', 'Assigned', 'In Progress', 'Resolved'];
    const itemsPerPage = 5; // Show 5 tickets initially
    
    let statusTable = '<div class="table-responsive mt-3"><table class="table table-sm table-hover"><thead class="table-light"><tr><th>Status</th><th class="text-center">Count</th><th>Issue</th></tr></thead><tbody>';
    
    // Sort statuses according to the defined order
    statusOrder.forEach(function(orderedStatus) {
        const index = statuses.indexOf(orderedStatus);
        if (index !== -1) {
            const status = statuses[index];
            const issuesString = statusReportIds[status] || 'N/A';
            const issuesArray = issuesString.split(', ');
            
            let issuesList = '<div id="issues-' + status.replace(/\s+/g, '-') + '">';
            
            // Show first 5 items
            issuesArray.slice(0, itemsPerPage).forEach(function(issue) {
                issuesList += '<div style="padding: 2px 0; font-size: 0.85rem;">' + issue + '</div>';
            });
            
            // Hidden items
            if (issuesArray.length > itemsPerPage) {
                issuesList += '<div id="hidden-issues-' + status.replace(/\s+/g, '-') + '" style="display: none;">';
                issuesArray.slice(itemsPerPage).forEach(function(issue) {
                    issuesList += '<div style="padding: 2px 0; font-size: 0.85rem;">' + issue + '</div>';
                });
                issuesList += '</div>';
                
                // Show More button
                issuesList += '<button class="btn btn-sm btn-link p-0 mt-1" onclick="toggleIssues(\'' + status.replace(/\s+/g, '-') + '\')" id="toggle-btn-' + status.replace(/\s+/g, '-') + '" style="font-size: 0.8rem;">Show More (' + (issuesArray.length - itemsPerPage) + ')</button>';
            }
            
            issuesList += '</div>';
            
            statusTable += '<tr>' +
                '<td><strong>' + status + '</strong></td>' +
                '<td class="text-center"><span class="badge bg-primary">' + statusCounts[index] + '</span></td>' +
                '<td>' + issuesList + '</td>' +
                '</tr>';
        }
    });
    
    statusTable += '</tbody></table></div>';
    
    // Add toggle function
    window.toggleIssues = function(statusId) {
        const hiddenDiv = document.getElementById('hidden-issues-' + statusId);
        const toggleBtn = document.getElementById('toggle-btn-' + statusId);
        
        if (hiddenDiv.style.display === 'none') {
            hiddenDiv.style.display = 'block';
            toggleBtn.textContent = 'Show Less';
        } else {
            hiddenDiv.style.display = 'none';
            const hiddenCount = hiddenDiv.querySelectorAll('div').length;
            toggleBtn.textContent = 'Show More (' + hiddenCount + ')';
        }
    };
    
    // Export Status PDF function
    window.exportStatusPDF = function() {
        const url = new URL(window.location.href);
        const dateFrom = url.searchParams.get('date_from');
        const dateTo = url.searchParams.get('date_to');
        
        let pdfUrl = '{{ route("admin.analytics.status-pdf") }}';
        const params = new URLSearchParams();
        
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        
        if (params.toString()) {
            pdfUrl += '?' + params.toString();
        }
        
        window.open(pdfUrl, '_blank');
    };
    
    Swal.fire({
        title: '<i class="fas fa-chart-pie me-2"></i>Status Distribution & Response Time Analysis',
        html: `
            <div style="height: 100%; overflow-y: auto;">
                <div class="mb-3 p-3 bg-light border-bottom d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex gap-2">
                        <!-- Room Filter -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="statusModalRoomDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem;">
                                <i class="fas fa-door-open me-1"></i>
                                <span id="statusModalRoomLabel">All Rooms</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="statusModalRoomDropdown" style="max-height: 300px; overflow-y: auto;">
                                <li><a class="dropdown-item" href="#" onclick="setModalRoomFilter('status', 'all', event)">All Rooms</a></li>
                                <li><hr class="dropdown-divider"></li>
                                @foreach($combinedLocationStats ?? [] as $stat)
                                <li><a class="dropdown-item" href="#" onclick="setModalRoomFilter('status', '{{ addslashes($stat['location']) }}', event)">{{ $stat['location'] }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        
                        <!-- Date Range Filter -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="statusModalRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.9rem;">
                                <i class="fas fa-calendar-alt me-1"></i>
                                <span id="statusModalRangeLabel">Last 6 months</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="statusModalRangeDropdown" style="min-width: 220px;">
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'last7days', event)">Last 7 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'last28days', event)">Last 28 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'last90days', event)">Last 90 days</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'last6months', event)">Last 6 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'last12months', event)">Last 12 months</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'thisyear', event)">This year</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'lastyear', event)">Last year</a></li>
                                <li><a class="dropdown-item" href="#" onclick="setModalRange('status', 'allyears', event)">All years</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li class="px-3 py-2">
                                    <label class="form-label mb-1" style="font-size: 0.75rem; font-weight: 600;">Custom Range</label>
                                    <div class="mb-2">
                                        <input type="date" id="statusModalCustomDateFrom" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <div class="mb-2">
                                        <input type="date" id="statusModalCustomDateTo" class="form-control form-control-sm" style="font-size: 0.75rem;">
                                    </div>
                                    <button class="btn btn-primary btn-sm w-100" onclick="applyModalCustomRange('status', event)" style="font-size: 0.75rem;">Apply</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <button onclick="exportStatusPDF()" class="btn btn-danger btn-sm" style="white-space: nowrap; font-size: 0.9rem;">
                        <i class="fas fa-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
                <h5 class="mt-3 mb-3" style="color: #667eea;"><i class="fas fa-chart-pie"></i> Status Distribution</h5>
                ` + statusTable + `
                <h5 class="mt-4 mb-3" style="color: #667eea;"><i class="fas fa-clock"></i> Response Time Details</h5>
                <div class="row mb-3">
                    <div class="col-4 text-center">
                        <div style="background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 3px solid #3498db;">
                            <div style="font-size: 0.8rem; color: #666;">Avg Submit to Assign</div>
                            <div style="font-size: 1.5rem; font-weight: bold; color: #3498db;">
                                @php
                                    $totalSeconds = floor($avgSubmittedToAssigned * 3600);
                                    $hours = floor($totalSeconds / 3600);
                                    $minutes = floor(($totalSeconds % 3600) / 60);
                                    $seconds = $totalSeconds % 60;
                                    echo sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                @endphp
                            </div>
                        </div>
                    </div>
                    <div class="col-4 text-center">
                        <div style="background: #fff8f0; padding: 15px; border-radius: 8px; border-left: 3px solid #f39c12;">` +
                        '<div style="font-size: 0.8rem; color: #666;">Avg Assign to Resolve</div>' +
                        '<div style="font-size: 1.5rem; font-weight: bold; color: #f39c12;">@php $totalSeconds = floor($avgAssignedToResolved * 3600); $hours = floor($totalSeconds / 3600); $minutes = floor(($totalSeconds % 3600) / 60); $seconds = $totalSeconds % 60; echo sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds); @endphp</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-4 text-center">' +
                    '<div style="background: #f0fff4; padding: 15px; border-radius: 8px; border-left: 3px solid #27ae60;">' +
                        '<div style="font-size: 0.8rem; color: #666;">Avg Total Time</div>' +
                        '<div style="font-size: 1.5rem; font-weight: bold; color: #27ae60;">@php $totalSeconds = floor($avgTotalTime * 3600); $hours = floor($totalSeconds / 3600); $minutes = floor(($totalSeconds % 3600) / 60); $seconds = $totalSeconds % 60; echo sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds); @endphp</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            responseTimeTable +
            '</div>',
        width: '95%',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            popup: 'swal-wide-popup',
            htmlContainer: 'swal2-html-container'
        }
    });
}

</script>
@endsection

