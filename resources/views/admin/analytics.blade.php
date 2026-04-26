@extends('layouts.app')

@section('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
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
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }
    
    .table-responsive {
        overflow-x: auto;
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
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    
    .count-badge {
        background: #007bff;
        color: white;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    
    .filter-section {
        background: var(--card-bg, #fff);
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .filter-form {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: flex-end;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .filter-group label {
        font-size: 0.85rem;
        color: #666;
    }
    
    .filter-group input {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 0.9rem;
    }
    
    .btn-filter {
        padding: 8px 20px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .btn-filter:hover {
        background: #5568d3;
    }
    
    .btn-reset {
        padding: 8px 20px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
    
    .btn-reset:hover {
        background: #5a6268;
    }
    
    .chart-container {
        margin-top: 30px;
    }
    
    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .charts-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .alert-info {
        background: #d1ecf1;
        border: 1px solid #bee5eb;
        color: #0c5460;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    /* Period Comparison */
    .comparison-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .comparison-card {
        background: #fff;
        border-radius: 10px;
        padding: 18px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        border-left: 4px solid #667eea;
    }
    .comparison-card.up   { border-left-color: #dc3545; }
    .comparison-card.down { border-left-color: #28a745; }
    .comparison-card.neutral { border-left-color: #6c757d; }
    .comparison-label { font-size: .8rem; color: #888; margin-bottom: 4px; }
    .comparison-value { font-size: 1.6rem; font-weight: 700; }
    .comparison-sub   { font-size: .82rem; color: #555; margin-top: 4px; }
    .change-badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: .78rem; font-weight: 600; }
    .change-badge.up   { background: #fde8ea; color: #dc3545; }
    .change-badge.down { background: #e6f9f0; color: #28a745; }
    .change-badge.neutral { background: #f0f0f0; color: #6c757d; }

    /* Budget */
    .budget-bar-wrap { background: #e9ecef; border-radius: 8px; height: 18px; overflow: hidden; margin: 8px 0; }
    .budget-bar-fill { height: 100%; border-radius: 8px; transition: width .4s; }
    .budget-bar-fill.safe     { background: linear-gradient(90deg,#28a745,#38ef7d); }
    .budget-bar-fill.warning  { background: linear-gradient(90deg,#ffc107,#fd7e14); }
    .budget-bar-fill.danger   { background: linear-gradient(90deg,#dc3545,#ff6b6b); }

    /* Trend Alerts */
    .alert-item { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 8px; margin-bottom: 10px; }
    .alert-item.critical { background: #fde8ea; border-left: 4px solid #dc3545; }
    .alert-item.warning  { background: #fff8e1; border-left: 4px solid #ffc107; }
    .alert-item.info     { background: #e8f4fd; border-left: 4px solid #17a2b8; }
    .alert-icon { font-size: 1.4rem; }
    .alert-text { flex: 1; }
    .alert-text strong { display: block; font-size: .95rem; }
    .alert-text span   { font-size: .82rem; color: #666; }

    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .filter-form { flex-direction: column; align-items: stretch; }
        .comparison-grid { grid-template-columns: 1fr 1fr; }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="analytics-header">
        <div class="analytics-title">
            <i class="fas fa-chart-line"></i> Analytics - Cost Tracking & Repair/Damage Analysis
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.analytics.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Export to CSV
            </a>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value">{{ $totalConcerns }}</div>
            <div class="stat-label">Total Repairs/Damages</div>
        </div>
        <div class="stat-card green">
            <div class="stat-value">₱{{ number_format($totalCost, 2) }}</div>
            <div class="stat-label">Total Cost</div>
        </div>
        <div class="stat-card orange">
            <div class="stat-value">{{ $uniqueLocations }}</div>
            <div class="stat-label">Unique Locations</div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-value">{{ $totalConcerns > 0 ? number_format($totalCost / $totalConcerns, 2) : 0 }}</div>
            <div class="stat-label">Average Cost per Repair</div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" action="{{ route('admin.analytics') }}" class="filter-form">
            <div class="filter-group">
                <label for="date_from">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label for="date_to">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn-filter">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="{{ route('admin.analytics') }}" class="btn-reset">
                <i class="fas fa-reset"></i> Reset
            </a>
        </form>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         TREND ALERTS
    ═══════════════════════════════════════════════════════════ -->
    @if(isset($trendAlerts) && $trendAlerts->count() > 0)
    <div class="analytics-card">

        {{-- ALERTS & NOTIFICATIONS --}}
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
                $timeAgo     = $alert['updated_at'] ? \Carbon\Carbon::parse($alert['updated_at'])->diffForHumans(null, true, true) : 'recently';
            @endphp
            <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-left:4px solid {{ $borderColor }};background:{{ $bgColor }};border-radius:8px;margin-bottom:10px;cursor:pointer;"
                 onclick="showCostTrendModal({{ json_encode($alert) }})">
                <div style="width:36px;height:36px;border-radius:50%;background:{{ $iconColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-triangle-exclamation" style="color:#fff;font-size:15px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:.95rem;color:#1e293b;">{{ $alert['alert_title'] }}</div>
                    <div style="font-size:.82rem;color:#64748b;">
                        @if($alert['top_issue']){{ $alert['top_issue'] }} on {{ $alert['location'] }}@else{{ $alert['location'] }}@endif
                        &mdash; {{ $alert['severity'] === 'critical' ? 'Replacement recommended' : ($alert['severity'] === 'warning' ? 'Approaching threshold' : 'Trend detected') }}
                    </div>
                </div>
                <div style="font-size:.78rem;color:#94a3b8;white-space:nowrap;">{{ $timeAgo }}</div>
            </div>
            @endforeach
        </div>

        <hr style="border-color:#e2e8f0;margin:20px 0;">

        {{-- RECOMMENDATION ENGINE --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="analytics-title" style="font-size:1rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                <i class="fas fa-lightbulb text-warning me-2"></i> Recommendation Engine
            </div>
        </div>

        <div>
            @foreach($trendAlerts as $alert)
            @php
                $recIcon  = $alert['rec_color'] === 'success' ? 'fa-check' : ($alert['rec_color'] === 'warning' ? 'fa-wrench' : 'fa-xmark');
                $recBg    = $alert['rec_color'] === 'success' ? '#22c55e' : ($alert['rec_color'] === 'warning' ? '#f97316' : '#ef4444');
                $recText  = $alert['rec_color'] === 'success' ? '#16a34a' : ($alert['rec_color'] === 'warning' ? '#ea580c' : '#dc2626');
            @endphp
            <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:10px;cursor:pointer;transition:all 0.2s ease;"
                onclick="showCostTrendModal({{ json_encode($alert) }})"
                onmouseover="this.style.background='#f8fafc';this.style.borderColor='#cbd5e1';"
                onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0';">
                <div style="width:40px;height:40px;border-radius:50%;background:{{ $recBg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas {{ $recIcon }}" style="color:#fff;font-size:16px;"></i>
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:.95rem;color:{{ $recText }};">{{ $alert['recommendation'] }}</div>
                    <div style="font-size:.82rem;color:#64748b;">@if($alert['top_issue']){{ $alert['top_issue'] }} on {{ $alert['location'] }}@else{{ $alert['location'] }}@endif</div>
                </div>
                <div style="font-size:.82rem;color:#64748b;max-width:180px;text-align:right;">{{ $alert['rec_desc'] }}</div>
                <i class="fas fa-chevron-right" style="color:#cbd5e1;font-size:13px;"></i>
            </div>
            @endforeach
        </div>

    </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════
         PERIOD COMPARISON
    ═══════════════════════════════════════════════════════════ -->
    @if(isset($periodComparison))
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-exchange-alt"></i> Period Comparison
            </div>
        </div>
        <p class="text-muted mb-3" style="font-size:.88rem;">
            {{ $periodComparison['this_month_label'] }} vs {{ $periodComparison['last_month_label'] }}
        </p>
        <div class="comparison-grid">
            {{-- Repairs this month --}}
            <div class="comparison-card {{ $periodComparison['count_change'] > 0 ? 'up' : ($periodComparison['count_change'] < 0 ? 'down' : 'neutral') }}">
                <div class="comparison-label">Repairs — {{ $periodComparison['this_month_label'] }}</div>
                <div class="comparison-value">{{ $periodComparison['this_month_count'] }}</div>
                <div class="comparison-sub">
                    Last month: {{ $periodComparison['last_month_count'] }}
                    <span class="change-badge {{ $periodComparison['count_change'] > 0 ? 'up' : ($periodComparison['count_change'] < 0 ? 'down' : 'neutral') }} ms-1">
                        {{ $periodComparison['count_change'] > 0 ? '▲' : ($periodComparison['count_change'] < 0 ? '▼' : '—') }}
                        {{ abs($periodComparison['count_change']) }}%
                    </span>
                </div>
            </div>
            {{-- Cost this month --}}
            <div class="comparison-card {{ $periodComparison['cost_change'] > 0 ? 'up' : ($periodComparison['cost_change'] < 0 ? 'down' : 'neutral') }}">
                <div class="comparison-label">Cost — {{ $periodComparison['this_month_label'] }}</div>
                <div class="comparison-value">₱{{ number_format($periodComparison['this_month_cost'], 2) }}</div>
                <div class="comparison-sub">
                    Last month: ₱{{ number_format($periodComparison['last_month_cost'], 2) }}
                    <span class="change-badge {{ $periodComparison['cost_change'] > 0 ? 'up' : ($periodComparison['cost_change'] < 0 ? 'down' : 'neutral') }} ms-1">
                        {{ $periodComparison['cost_change'] > 0 ? '▲' : ($periodComparison['cost_change'] < 0 ? '▼' : '—') }}
                        {{ abs($periodComparison['cost_change']) }}%
                    </span>
                </div>
            </div>
            {{-- Avg cost this month --}}
            @php
                $avgThis = $periodComparison['this_month_count'] > 0 ? $periodComparison['this_month_cost'] / $periodComparison['this_month_count'] : 0;
                $avgLast = $periodComparison['last_month_count'] > 0 ? $periodComparison['last_month_cost'] / $periodComparison['last_month_count'] : 0;
                $avgChange = $avgLast > 0 ? round((($avgThis - $avgLast) / $avgLast) * 100, 1) : ($avgThis > 0 ? 100 : 0);
            @endphp
            <div class="comparison-card {{ $avgChange > 0 ? 'up' : ($avgChange < 0 ? 'down' : 'neutral') }}">
                <div class="comparison-label">Avg Cost / Repair — {{ $periodComparison['this_month_label'] }}</div>
                <div class="comparison-value">₱{{ number_format($avgThis, 2) }}</div>
                <div class="comparison-sub">
                    Last month: ₱{{ number_format($avgLast, 2) }}
                    <span class="change-badge {{ $avgChange > 0 ? 'up' : ($avgChange < 0 ? 'down' : 'neutral') }} ms-1">
                        {{ $avgChange > 0 ? '▲' : ($avgChange < 0 ? '▼' : '—') }}
                        {{ abs($avgChange) }}%
                    </span>
                </div>
            </div>
        </div>

        {{-- 6-month bar chart comparison --}}
        @if(isset($budgetTrend) && count($budgetTrend) > 0)
        <div class="chart-wrapper mt-3" style="height:220px;">
            <canvas id="periodComparisonChart"></canvas>
        </div>
        @endif
    </div>
    @endif

    <!-- ══════════════════════════════════════════════════════════
         BUDGET TRACKING
    ═══════════════════════════════════════════════════════════ -->
    @if(isset($monthlyBudget))
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-wallet"></i> Budget vs Actual Cost
            </div>
        </div>
        <p class="text-muted mb-3" style="font-size:.88rem;">
            Monthly repair budget: <strong>₱{{ number_format($monthlyBudget, 2) }}</strong>
            &nbsp;|&nbsp; Set via <code>MONTHLY_REPAIR_BUDGET</code> in your <code>.env</code> file.
        </p>

        <div class="comparison-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px,1fr));">
            <div class="comparison-card {{ $budgetPercent >= 100 ? 'up' : ($budgetPercent >= 75 ? 'neutral' : 'down') }}">
                <div class="comparison-label">Budget (This Month)</div>
                <div class="comparison-value">₱{{ number_format($monthlyBudget, 2) }}</div>
            </div>
            <div class="comparison-card {{ $budgetPercent >= 100 ? 'up' : 'down' }}">
                <div class="comparison-label">Spent (This Month)</div>
                <div class="comparison-value">₱{{ number_format($budgetUsed, 2) }}</div>
                <div class="comparison-sub">{{ $budgetPercent }}% of budget used</div>
            </div>
            <div class="comparison-card {{ $budgetRemaining <= 0 ? 'up' : 'down' }}">
                <div class="comparison-label">Remaining</div>
                <div class="comparison-value {{ $budgetRemaining <= 0 ? 'text-danger' : 'text-success' }}">
                    {{ $budgetRemaining <= 0 ? '-' : '' }}₱{{ number_format(abs($budgetRemaining), 2) }}
                </div>
                <div class="comparison-sub">{{ $budgetRemaining <= 0 ? 'Over budget' : 'Available' }}</div>
            </div>
        </div>

        {{-- Progress bar --}}
        <div class="budget-bar-wrap">
            <div class="budget-bar-fill {{ $budgetPercent >= 100 ? 'danger' : ($budgetPercent >= 75 ? 'warning' : 'safe') }}"
                 style="width: {{ min(100, $budgetPercent) }}%"></div>
        </div>
        <div style="font-size:.8rem; color:#888; text-align:right;">{{ $budgetPercent }}% used</div>

        {{-- 6-month budget vs actual chart --}}
        @if(isset($budgetTrend) && count($budgetTrend) > 0)
        <div class="chart-wrapper mt-3" style="height:240px;">
            <canvas id="budgetTrendChart"></canvas>
        </div>
        <div class="table-responsive mt-3">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Budget</th>
                        <th>Actual Spent</th>
                        <th>Variance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budgetTrend as $row)
                    @php $variance = $row['budget'] - $row['actual']; @endphp
                    <tr>
                        <td><strong>{{ $row['month'] }}</strong></td>
                        <td>₱{{ number_format($row['budget'], 2) }}</td>
                        <td><span class="cost-badge">₱{{ number_format($row['actual'], 2) }}</span></td>
                        <td class="{{ $variance < 0 ? 'text-danger' : 'text-success' }}">
                            {{ $variance < 0 ? '-' : '+' }}₱{{ number_format(abs($variance), 2) }}
                        </td>
                        <td>
                            @if($row['actual'] > $row['budget'])
                                <span class="badge bg-danger">Over Budget</span>
                            @elseif($row['actual'] >= $row['budget'] * 0.75)
                                <span class="badge bg-warning text-dark">Near Limit</span>
                            @else
                                <span class="badge bg-success">Within Budget</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    <!-- Location-based Analytics -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-map-marker-alt"></i> Repair/Damage by Location
            </div>
        </div>
        
        @if($locationStats->count() > 0)
        <div class="charts-row">
            <div class="chart-wrapper">
                <canvas id="locationChart"></canvas>
            </div>
        </div>
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Times Reported</th>
                        <th>Total Cost</th>
                        <th>Average Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($locationStats as $stat)
                    <tr>
                        <td><strong>{{ $stat->location }}</strong></td>
                        <td><span class="count-badge">{{ $stat->total_count }}</span></td>
                        <td><span class="cost-badge">₱{{ number_format($stat->total_cost ?? 0, 2) }}</span></td>
                        <td>₱{{ number_format(($stat->total_count > 0) ? ($stat->total_cost ?? 0) / $stat->total_count : 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert-info">
            <i class="fas fa-info-circle"></i> No resolved concerns with location data found. Once concerns are resolved with cost information, they will appear here.
        </div>
        @endif
    </div>

    <!-- Category-based Analytics -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-tags"></i> Repair/Damage by Category
            </div>
        </div>
        
        @if($categoryStats->count() > 0)
        <div class="charts-row">
            <div class="chart-wrapper">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Times Reported</th>
                        <th>Total Cost</th>
                        <th>Average Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categoryStats as $stat)
                    <tr>
                        <td><strong>{{ $stat->categoryRelation->name ?? 'Unknown' }}</strong></td>
                        <td><span class="count-badge">{{ $stat->total_count }}</span></td>
                        <td><span class="cost-badge">₱{{ number_format($stat->total_cost ?? 0, 2) }}</span></td>
                        <td>₱{{ number_format(($stat->total_count > 0) ? ($stat->total_cost ?? 0) / $stat->total_count : 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert-info">
            <i class="fas fa-info-circle"></i> No resolved concerns with category data found.
        </div>
        @endif
    </div>

    <!-- Issue-based Analytics -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-wrench"></i> Repair/Damage by Issue Type
            </div>
        </div>

        @if(isset($issueStats) && $issueStats->count() > 0)
        <div class="charts-row">
            <div class="chart-wrapper">
                <canvas id="issueChart"></canvas>
            </div>
        </div>
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Issue</th>
                        <th>Times Reported</th>
                        <th>Total Cost</th>
                        <th>Average Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($issueStats as $stat)
                    <tr>
                        <td><strong>{{ $stat->title }}</strong></td>
                        <td><span class="count-badge">{{ $stat->total_count }}</span></td>
                        <td><span class="cost-badge">₱{{ number_format($stat->total_cost ?? 0, 2) }}</span></td>
                        <td>₱{{ number_format($stat->total_count > 0 ? ($stat->total_cost ?? 0) / $stat->total_count : 0, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert-info">
            <i class="fas fa-info-circle"></i> No resolved concerns with issue data found.
        </div>
        @endif
    </div>

    <!-- Monthly Trend -->
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-calendar-alt"></i> Monthly Trend (Last 12 Months)
            </div>
        </div>
        
        @if($monthlyStats->count() > 0)
        <div class="charts-row">
            <div class="chart-wrapper">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Times Reported</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monthlyStats as $stat)
                    <tr>
                        <td><strong>{{ \Carbon\Carbon::parse($stat->month)->format('F Y') }}</strong></td>
                        <td><span class="count-badge">{{ $stat->total_count }}</span></td>
                        <td><span class="cost-badge">₱{{ number_format($stat->total_cost ?? 0, 2) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="alert-info">
            <i class="fas fa-info-circle"></i> No monthly data available for the selected period.
        </div>
        @endif
    </div>

    <!-- Repeated Damage Tracking -->
    @if(isset($repeatedDamageStats) && $repeatedDamageStats->count() > 0)
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-exclamation-triangle"></i> Repeated Damage Alerts
                <span class="badge bg-danger ms-2">High Cost Locations</span>
            </div>
        </div>
        <p class="text-muted">These locations have multiple repair requests. Consider replacing the asset instead of repairing repeatedly.</p>
        
        <div class="charts-row">
            <div class="chart-wrapper">
                <canvas id="repeatedDamageChart"></canvas>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Location</th>
                        <th>Times Repaired</th>
                        <th>Total Cost</th>
                        <th>Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($repeatedDamageStats as $stat)
                    <tr>
                        <td><strong>{{ $stat->location }}</strong></td>
                        <td><span class="count-badge">{{ $stat->total_count }}</span></td>
                        <td><span class="cost-badge">₱{{ number_format($stat->total_cost ?? 0, 2) }}</span></td>
                        <td>
                            @if($stat->total_cost > 10000)
                                <span class="badge bg-danger">Consider Replacement</span>
                            @elseif($stat->total_cost > 5000)
                                <span class="badge bg-warning">Monitor Closely</span>
                            @else
                                <span class="badge bg-info">Continue Monitoring</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Damaged Parts Tracking -->
    @if(isset($damagedPartsStats) && $damagedPartsStats->count() > 0)
    <div class="analytics-card">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-tools"></i> Most Damaged Parts
            </div>
        </div>
        
        <div class="charts-row">
            <div class="chart-wrapper">
                <canvas id="damagedPartsChart"></canvas>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="analytics-table">
                <thead>
                    <tr>
                        <th>Damaged Part</th>
                        <th>Times Damaged</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($damagedPartsStats as $stat)
                    <tr>
                        <td><strong>{{ $stat->damaged_part }}</strong></td>
                        <td><span class="count-badge">{{ $stat->total_count }}</span></td>
                        <td><span class="cost-badge">₱{{ number_format($stat->total_cost ?? 0, 2) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Location Bar Chart
    const locationCtx = document.getElementById('locationChart').getContext('2d');
    new Chart(locationCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($locationStats->pluck('location')) !!},
            datasets: [{
                label: 'Times Reported',
                data: {!! json_encode($locationStats->pluck('total_count')) !!},
                backgroundColor: 'rgba(102, 126, 234, 0.8)',
                borderColor: 'rgba(102, 126, 234, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
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

    // Category Pie Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($categoryStats->map(fn($s) => $s->categoryRelation->name ?? 'Unknown')->toArray()) !!},
            datasets: [{
                label: 'Times Reported',
                data: {!! json_encode($categoryStats->pluck('total_count')) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(199, 199, 199, 0.8)',
                    'rgba(83, 102, 255, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(199, 199, 199, 1)',
                    'rgba(83, 102, 255, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'right'
                }
            }
        }
    });

    // Issue Horizontal Bar Chart
    @if(isset($issueStats) && $issueStats->count() > 0)
    const issueCtx = document.getElementById('issueChart').getContext('2d');
    new Chart(issueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($issueStats->pluck('title')) !!},
            datasets: [{
                label: 'Times Reported',
                data: {!! json_encode($issueStats->pluck('total_count')) !!},
                backgroundColor: 'rgba(255, 159, 64, 0.8)',
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
    @endif

    // Monthly Line Chart
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyStats->map(fn($s) => \Carbon\Carbon::parse($s->month)->format('M Y'))->toArray()) !!},
            datasets: [{
                label: 'Times Reported',
                data: {!! json_encode($monthlyStats->pluck('total_count')) !!},
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                tension: 0.4
            }, {
                label: 'Total Cost (₱)',
                data: {!! json_encode($monthlyStats->pluck('total_cost')) !!},
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                fill: true,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Times Reported'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Total Cost (₱)'
                    }
                }
            }
        }
    });

    // Repeated Damage Bar Chart
    @if(isset($repeatedDamageStats) && $repeatedDamageStats->count() > 0)
    const repeatedDamageCtx = document.getElementById('repeatedDamageChart').getContext('2d');
    new Chart(repeatedDamageCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($repeatedDamageStats->pluck('location')) !!},
            datasets: [{
                label: 'Times Repaired',
                data: {!! json_encode($repeatedDamageStats->pluck('total_count')) !!},
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }, {
                label: 'Total Cost (₱)',
                data: {!! json_encode($repeatedDamageStats->pluck('total_cost')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Times Repaired'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    },
                    title: {
                        display: true,
                        text: 'Total Cost (₱)'
                    }
                }
            }
        }
    });
    @endif

    // Damaged Parts Chart
    @if(isset($damagedPartsStats) && $damagedPartsStats->count() > 0)
    const damagedPartsCtx = document.getElementById('damagedPartsChart').getContext('2d');
    new Chart(damagedPartsCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($damagedPartsStats->pluck('damaged_part')) !!},
            datasets: [{
                label: 'Times Damaged',
                data: {!! json_encode($damagedPartsStats->pluck('total_count')) !!},
                backgroundColor: 'rgba(255, 206, 86, 0.8)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif

    // Period Comparison – 6-month bar chart
    @if(isset($budgetTrend) && count($budgetTrend) > 0)
    const periodCtx = document.getElementById('periodComparisonChart');
    if (periodCtx) {
        new Chart(periodCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(collect($budgetTrend)->pluck('month')) !!},
                datasets: [{
                    label: 'Repairs Cost (₱)',
                    data: {!! json_encode(collect($budgetTrend)->pluck('actual')) !!},
                    backgroundColor: 'rgba(102, 126, 234, 0.75)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Cost (₱)' } } }
            }
        });
    }

    // Budget vs Actual – 6-month line chart
    const budgetCtx = document.getElementById('budgetTrendChart');
    if (budgetCtx) {
        new Chart(budgetCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode(collect($budgetTrend)->pluck('month')) !!},
                datasets: [{
                    label: 'Actual Spent (₱)',
                    data: {!! json_encode(collect($budgetTrend)->pluck('actual')) !!},
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.15)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5
                }, {
                    label: 'Monthly Budget (₱)',
                    data: {!! json_encode(collect($budgetTrend)->pluck('budget')) !!},
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'transparent',
                    borderDash: [6, 4],
                    tension: 0,
                    pointRadius: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true, title: { display: true, text: 'Cost (₱)' } } }
            }
        });
    }
    @endif
</script>

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

<script>
function showCostTrendModal(alert) {
    document.getElementById('ctm_title').textContent = (alert.top_issue || 'Issue') + ' — ' + alert.location;
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
@endsection

