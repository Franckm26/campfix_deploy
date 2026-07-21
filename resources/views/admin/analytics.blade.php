@extends('layouts.app')

@section('page_title')
<div class="analytics-page-title">
    <div>
        <h2><i class="fas fa-chart-line"></i> Reports Analytics</h2>
        <p>Decision support for maintenance planning and resource allocation</p>
    </div>
</div>

@endsection

@section('styles')
<style>
    .analytics-shell {
        --analytics-blue: #1769e0;
        --analytics-green: #148a58;
        --analytics-red: #d93645;
        --analytics-amber: #e99a00;
        --analytics-ink: #10233f;
        --analytics-muted: #66758a;
        --analytics-border: #dce2ea;
        display: grid;
        gap: 18px;
        padding: 20px;
        background: #f5f7fa;
        min-height: calc(100vh - 92px);
    }

    .analytics-page-title h2 {
        margin: 0;
        color: #10233f;
        font-size: 25px;
        font-weight: 700;
    }

    .analytics-page-title p {
        margin: 3px 0 0;
        color: #66758a;
        font-size: 14px;
    }

    .analytics-panel {
        background: #fff;
        border: 1px solid var(--analytics-border);
        border-radius: 8px;
        box-shadow: 0 2px 7px rgba(16, 35, 63, 0.05);
    }

    .analytics-filter {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(155px, .75fr) minmax(155px, .75fr) auto auto;
        gap: 12px;
        align-items: end;
        padding: 16px;
    }

    .analytics-field label {
        display: block;
        margin-bottom: 5px;
        color: #45566d;
        font-size: 12px;
        font-weight: 700;
    }

    .analytics-field .form-control,
    .analytics-field .form-select {
        height: 42px;
        border-color: #ccd4df;
        border-radius: 6px;
    }

    .analytics-filter .btn {
        height: 42px;
        border-radius: 6px;
        font-weight: 600;
    }

    .analytics-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
    }

    .analytics-kpi {
        position: relative;
        min-height: 132px;
        padding: 18px 20px;
        overflow: hidden;
    }

    .analytics-kpi::before {
        position: absolute;
        inset: 0 auto 0 0;
        width: 5px;
        background: var(--kpi-color);
        content: '';
    }

    .analytics-kpi-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .analytics-kpi-label {
        color: var(--analytics-muted);
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .analytics-kpi-icon {
        display: grid;
        width: 36px;
        height: 36px;
        place-items: center;
        border-radius: 50%;
        color: var(--kpi-color);
        background: color-mix(in srgb, var(--kpi-color) 12%, white);
    }

    .analytics-kpi-value {
        margin-top: 7px;
        color: var(--analytics-ink);
        font-size: 30px;
        font-weight: 750;
        line-height: 1.15;
    }

    .analytics-kpi-context {
        margin-top: 5px;
        color: var(--analytics-muted);
        font-size: 12px;
    }

    .analytics-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 17px 20px;
        border-bottom: 1px solid #e5e9ef;
    }

    .analytics-panel-header h3 {
        margin: 0;
        color: var(--analytics-ink);
        font-size: 17px;
        font-weight: 700;
    }

    .analytics-panel-header p {
        margin: 3px 0 0;
        color: var(--analytics-muted);
        font-size: 12px;
    }

    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .chart-legend span {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 9px;
        border: 1px solid #dce2ea;
        border-radius: 5px;
        color: #45566d;
        font-size: 11px;
        font-weight: 700;
    }

    .chart-legend i {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .analytics-header-tools,
    .analytics-chart-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 7px;
    }

    .analytics-chart-actions .btn,
    .analytics-header-tools .btn {
        min-height: 34px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 650;
    }

    .chart-selection {
        min-height: 48px;
        margin: 0 20px 17px;
        padding: 10px 13px;
        border: 1px solid #dce4ee;
        border-radius: 5px;
        color: #52647b;
        background: #f8fafc;
        font-size: 12px;
        line-height: 1.5;
    }

    .chart-selection strong {
        color: var(--analytics-ink);
    }

    .chart-selection a {
        margin-left: 6px;
        font-weight: 700;
        text-decoration: none;
    }

    .analytics-data-table {
        display: none;
        margin: 0 20px 20px;
        overflow-x: auto;
    }

    .analytics-data-table.is-visible {
        display: block;
    }

    .analytics-data-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .analytics-data-table th,
    .analytics-data-table td {
        padding: 9px 11px;
        border: 1px solid #e1e6ed;
        color: #45566d;
        font-size: 12px;
        text-align: left;
    }

    .analytics-data-table th {
        background: #f4f7fa;
        color: #283b55;
    }

    .location-tools {
        display: grid;
        grid-template-columns: minmax(180px, 1fr) minmax(150px, .45fr) auto;
        gap: 10px;
        padding: 13px 16px;
        border-bottom: 1px solid #e5e9ef;
        background: #fafbfc;
    }

    .location-tools .form-control,
    .location-tools .form-select {
        height: 38px;
        border-radius: 5px;
        font-size: 13px;
    }

    .risk-sort {
        padding: 0;
        border: 0;
        color: inherit;
        background: transparent;
        font: inherit;
        text-transform: inherit;
    }

    .risk-sort:hover,
    .risk-sort:focus {
        color: var(--analytics-blue);
    }

    .analytics-summary-metrics {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 15px 0;
    }

    .analytics-summary-metric {
        padding: 12px;
        border: 1px solid #e0e6ed;
        border-radius: 6px;
        background: #f8fafc;
    }

    .analytics-summary-metric span {
        display: block;
        color: #6b7a8e;
        font-size: 11px;
        text-transform: uppercase;
    }

    .analytics-summary-metric strong {
        display: block;
        margin-top: 3px;
        font-size: 18px;
    }

    .analytics-chart-body {
        height: 350px;
        padding: 18px 20px 8px;
    }

    .analytics-chart-body canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .analytics-interpretation {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin: 0 20px 18px;
        padding: 13px 15px;
        border-left: 4px solid var(--interpretation-color);
        border-radius: 4px;
        background: #f7f9fc;
        color: #31445e;
        font-size: 13px;
    }

    .analytics-interpretation strong {
        color: var(--analytics-ink);
    }

    .analytics-interpretation .btn {
        flex: 0 0 auto;
        border-radius: 5px;
    }

    .analytics-grid-two {
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
        gap: 18px;
    }

    .analytics-small-chart {
        height: 285px;
        padding: 17px 20px 20px;
    }

    .analytics-small-chart canvas {
        width: 100% !important;
        height: 100% !important;
    }

    .decision-list {
        display: grid;
        gap: 10px;
        padding: 16px 20px 20px;
    }

    .decision-item {
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        gap: 11px;
        align-items: start;
        padding: 12px;
        border: 1px solid #e2e7ee;
        border-left: 4px solid var(--decision-color);
        border-radius: 6px;
        background: #fafbfc;
    }

    .decision-icon {
        display: grid;
        width: 30px;
        height: 30px;
        place-items: center;
        color: var(--decision-color);
    }

    .decision-item h4 {
        margin: 0 0 3px;
        color: var(--analytics-ink);
        font-size: 14px;
        font-weight: 700;
    }

    .decision-item p {
        margin: 0;
        color: var(--analytics-muted);
        font-size: 12px;
        line-height: 1.45;
    }

    .risk-table-wrap {
        overflow-x: auto;
    }

    .risk-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .risk-table th,
    .risk-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e9ef;
        color: #31445e;
        font-size: 13px;
        text-align: left;
        vertical-align: middle;
    }

    .risk-table th {
        color: #627187;
        background: #f8fafc;
        font-size: 11px;
        text-transform: uppercase;
    }

    .risk-table tr:last-child td {
        border-bottom: 0;
    }

    .risk-score {
        display: inline-flex;
        min-width: 42px;
        justify-content: center;
        padding: 4px 8px;
        border-radius: 4px;
        color: #fff;
        font-weight: 700;
    }

    .risk-score.high { background: var(--analytics-red); }
    .risk-score.medium { background: var(--analytics-amber); }
    .risk-score.low { background: var(--analytics-green); }

    .location-detail-button {
        padding: 2px 0;
        border: 0;
        color: #2c4668;
        background: transparent;
        font: inherit;
        font-weight: 750;
        text-align: left;
        text-decoration: underline;
        text-decoration-color: #9ab2d0;
        text-underline-offset: 3px;
    }

    .location-detail-button:hover,
    .location-detail-button:focus {
        color: var(--analytics-blue);
        text-decoration-color: var(--analytics-blue);
    }

    .location-detail-metrics {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }

    .location-detail-metric {
        padding: 11px;
        border: 1px solid #dfe5ec;
        border-radius: 6px;
        background: #f8fafc;
    }

    .location-detail-metric span {
        display: block;
        color: #6b7a8e;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .location-detail-metric strong {
        display: block;
        margin-top: 3px;
        color: var(--analytics-ink);
        font-size: 18px;
    }

    .location-breakdowns {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
        margin: 16px 0;
    }

    .location-breakdown {
        padding: 13px;
        border: 1px solid #dfe5ec;
        border-radius: 6px;
    }

    .location-breakdown h4,
    .location-reports h4 {
        margin: 0 0 10px;
        color: var(--analytics-ink);
        font-size: 14px;
    }

    .location-breakdown-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 6px 0;
        border-bottom: 1px solid #edf0f4;
        color: #4d6078;
        font-size: 12px;
    }

    .location-breakdown-row:last-child { border-bottom: 0; }

    .location-reports {
        margin-top: 16px;
        overflow-x: auto;
    }

    .location-report-table {
        width: 100%;
        border-collapse: collapse;
    }

    .location-report-table th,
    .location-report-table td {
        padding: 8px 9px;
        border: 1px solid #e1e6ed;
        color: #45566d;
        font-size: 11px;
        text-align: left;
    }

    .location-report-table th { background: #f4f7fa; }

    .location-risk-callout {
        padding: 12px 14px;
        border-left: 4px solid var(--location-risk-color, #1769e0);
        border-radius: 4px;
        background: #f7f9fc;
        color: #42546c;
        font-size: 13px;
    }

    .empty-analytics {
        padding: 40px 20px;
        color: var(--analytics-muted);
        text-align: center;
    }

    .empty-analytics i {
        display: block;
        margin-bottom: 10px;
        color: #9aa7b8;
        font-size: 34px;
    }

    .executive-summary {
        color: #3c4d63;
        font-size: 14px;
        line-height: 1.7;
    }

    .executive-summary strong {
        color: #10233f;
    }

    .summary-callout {
        margin-top: 14px;
        padding: 13px 15px;
        border-left: 4px solid var(--analytics-blue);
        background: #f3f7fd;
    }

    @media (max-width: 1100px) {
        .analytics-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .analytics-filter { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .analytics-grid-two { grid-template-columns: 1fr; }
    }

    @media (max-width: 650px) {
        .analytics-shell { padding: 12px; }
        .analytics-kpis, .analytics-filter { grid-template-columns: 1fr; }
        .analytics-chart-body { height: 290px; padding-inline: 10px; }
        .analytics-panel-header, .analytics-interpretation { align-items: flex-start; flex-direction: column; }
        .analytics-interpretation { margin-inline: 12px; }
        .analytics-header-tools, .analytics-chart-actions { justify-content: flex-start; }
        .location-tools, .analytics-summary-metrics { grid-template-columns: 1fr; }
        .location-detail-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .location-breakdowns { grid-template-columns: 1fr; }
    }

    @media print {
        body * { visibility: hidden; }
        #executiveSummaryModal, #executiveSummaryModal * { visibility: visible; }
        #executiveSummaryModal { position: absolute; inset: 0; display: block !important; }
        #executiveSummaryModal .modal-dialog { max-width: 100%; margin: 0; }
        #executiveSummaryModal .modal-content { border: 0; box-shadow: none; }
        #executiveSummaryModal .btn-close, #executiveSummaryModal .modal-footer { display: none; }
    }
</style>
@endsection

@section('content')
@php
    $trendDelta = (float) ($executiveSummary['trend_delta'] ?? 0);
    $topLocation = $executiveSummary['top_location'] ?? null;
    $topCategory = $executiveSummary['top_category'] ?? null;
    $trendInterpretationColor = $totalReports === 0 ? '#66758a' : ($trendDelta > 0 ? '#d93645' : '#148a58');
    $dominantStatus = $statusStats->first();
    $dominantStatusShare = $dominantStatus && $totalReports > 0 ? round(($dominantStatus['count'] / $totalReports) * 100, 1) : 0;
    $topCategoryShare = $topCategory && $totalReports > 0 ? round(($topCategory['count'] / $totalReports) * 100, 1) : 0;
    $highRiskLocations = $locationStats->where('risk_score', '>=', 12);
    $mediumRiskLocations = $locationStats->whereBetween('risk_score', [6, 11]);
    $locationOpenWork = $locationStats->sum('open');
@endphp

<main class="analytics-shell">
    <form class="analytics-panel analytics-filter" method="GET" action="{{ route('admin.analytics') }}">
        <div class="analytics-field">
            <label for="location">Location</label>
            <select class="form-select" id="location" name="location">
                <option value="">All locations</option>
                @foreach($locations as $location)
                    <option value="{{ $location }}" @selected(request('location') === $location)>{{ $location }}</option>
                @endforeach
            </select>
        </div>
        <div class="analytics-field">
            <label for="date_from">From</label>
            <input class="form-control" id="date_from" name="date_from" type="date" value="{{ request('date_from') }}">
        </div>
        <div class="analytics-field">
            <label for="date_to">To</label>
            <input class="form-control" id="date_to" name="date_to" type="date" value="{{ request('date_to') }}">
        </div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-filter"></i> Apply</button>
        <a class="btn btn-outline-secondary" href="{{ route('admin.analytics') }}" title="Clear filters"><i class="fas fa-rotate-left"></i> Reset</a>
    </form>

    <section class="analytics-kpis" aria-label="Key performance indicators">
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #1769e0;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Total Reports</span>
                <span class="analytics-kpi-icon"><i class="fas fa-clipboard-list"></i></span>
            </div>
            <div class="analytics-kpi-value">{{ number_format($totalReports) }}</div>
            <div class="analytics-kpi-context">{{ number_format($hazardReports) }} marked as safety hazards</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #d93645;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Open Workload</span>
                <span class="analytics-kpi-icon"><i class="fas fa-triangle-exclamation"></i></span>
            </div>
            <div class="analytics-kpi-value">{{ number_format($openReports) }}</div>
            <div class="analytics-kpi-context">Reports still requiring action</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #148a58;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Resolution Rate</span>
                <span class="analytics-kpi-icon"><i class="fas fa-circle-check"></i></span>
            </div>
            <div class="analytics-kpi-value">{{ number_format($resolutionRate, 1) }}%</div>
            <div class="analytics-kpi-context">Target: {{ number_format($targetResolutionRate) }}%</div>
        </article>
        <article class="analytics-panel analytics-kpi" style="--kpi-color: #e99a00;">
            <div class="analytics-kpi-header">
                <span class="analytics-kpi-label">Recorded Repair Cost</span>
                <span class="analytics-kpi-icon"><i class="fas fa-coins"></i></span>
            </div>
            <div class="analytics-kpi-value">PHP {{ number_format($totalCost, 2) }}</div>
            <div class="analytics-kpi-context">
                {{ $avgResolutionHours !== null ? number_format($avgResolutionHours, 1).' average hours to resolve' : 'No resolution-time data yet' }}
            </div>
        </article>
    </section>

    <section class="analytics-panel">
        <header class="analytics-panel-header">
            <div>
                <h3>Reports Trend</h3>
                <p>Historical report volume and completed work</p>
            </div>
            <div class="analytics-header-tools">
                <div class="chart-legend" aria-label="Chart legend">
                    <span><i style="background:#1769e0"></i> Reports</span>
                    <span><i style="background:#148a58"></i> Resolved</span>
                </div>
                <button class="btn btn-outline-secondary" type="button" data-toggle-table="trendData"><i class="fas fa-table"></i> Data</button>
                <button class="btn btn-outline-secondary" type="button" data-download-chart="reportsTrendChart" data-file-name="reports-trend"><i class="fas fa-download"></i></button>
            </div>
        </header>
        <div class="analytics-chart-body">
            <canvas id="reportsTrendChart" aria-label="Reports trend chart"></canvas>
        </div>
        <div class="analytics-interpretation" style="--interpretation-color: {{ $trendInterpretationColor }};">
            <div>
                <strong>System interpretation:</strong>
                @if($totalReports === 0)
                    There is not enough report data in this filter period to identify a trend.
                @elseif($trendDelta > 0)
                    Recent report volume increased by {{ number_format(abs($trendDelta), 1) }} reports per month compared with the earlier period. Review staffing and preventive maintenance before demand rises further.
                @elseif($trendDelta < 0)
                    Recent report volume decreased by {{ number_format(abs($trendDelta), 1) }} reports per month. Continue the current maintenance approach and verify that reporting remains accessible.
                @else
                    Report volume is stable. Focus decisions on unresolved hazards and the locations with the highest risk scores.
                @endif
            </div>
            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#executiveSummaryModal">
                <i class="fas fa-file-lines"></i> Executive Summary
            </button>
        </div>
        <div class="analytics-data-table" id="trendData">
            <table><thead><tr><th>Month</th><th>Reports</th><th>Resolved</th><th>Completion Rate</th></tr></thead><tbody>
                @foreach($trendStats as $month)
                    <tr><td>{{ $month['label'] }}</td><td>{{ $month['reports'] }}</td><td>{{ $month['resolved'] }}</td><td>{{ $month['reports'] > 0 ? number_format(($month['resolved'] / $month['reports']) * 100, 1).'%' : 'No reports' }}</td></tr>
                @endforeach
            </tbody></table>
        </div>
    </section>

    <div class="analytics-grid-two">
        <section class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <h3>Status Distribution</h3>
                    <p>Current workload by report status</p>
                </div>
                <div class="analytics-chart-actions">
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#statusSummaryModal"><i class="fas fa-file-lines"></i> Summary</button>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-table="statusData"><i class="fas fa-table"></i> Data</button>
                    <button class="btn btn-outline-secondary" type="button" data-download-chart="statusChart" data-file-name="status-distribution" title="Download chart"><i class="fas fa-download"></i></button>
                </div>
            </header>
            @if($statusStats->isNotEmpty())
                <div class="analytics-small-chart"><canvas id="statusChart" aria-label="Report status chart"></canvas></div>
                <div class="chart-selection" id="statusInsight"><strong>Explore:</strong> Click a status segment to see its share and open the matching report list.</div>
                <div class="analytics-data-table" id="statusData">
                    <table><thead><tr><th>Status</th><th>Reports</th><th>Share</th></tr></thead><tbody>
                        @foreach($statusStats as $status)
                            <tr><td>{{ $status['status'] }}</td><td>{{ number_format($status['count']) }}</td><td>{{ $totalReports > 0 ? number_format(($status['count'] / $totalReports) * 100, 1) : 0 }}%</td></tr>
                        @endforeach
                    </tbody></table>
                </div>
            @else
                <div class="empty-analytics"><i class="fas fa-chart-pie"></i>No status data available.</div>
            @endif
        </section>

        <section class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <h3>Decision Alerts</h3>
                    <p>Data-backed findings that need administrative attention</p>
                </div>
            </header>
            <div class="decision-list">
                @forelse($decisionAlerts as $alert)
                    @php
                        $alertColor = match($alert['level']) {
                            'critical' => '#d93645',
                            'warning' => '#e99a00',
                            'success' => '#148a58',
                            default => '#1769e0',
                        };
                        $alertIcon = match($alert['level']) {
                            'critical' => 'fa-circle-exclamation',
                            'warning' => 'fa-triangle-exclamation',
                            'success' => 'fa-circle-check',
                            default => 'fa-circle-info',
                        };
                    @endphp
                    <article class="decision-item" style="--decision-color: {{ $alertColor }};">
                        <div class="decision-icon"><i class="fas {{ $alertIcon }}"></i></div>
                        <div><h4>{{ $alert['title'] }}</h4><p>{{ $alert['body'] }}</p></div>
                    </article>
                @empty
                    <div class="empty-analytics"><i class="fas fa-circle-info"></i>No decision alerts for this period.</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="analytics-grid-two">
        <section class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <h3>Leading Report Categories</h3>
                    <p>Use recurring categories to guide purchasing and preventive work</p>
                </div>
                <div class="analytics-chart-actions">
                    <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#categorySummaryModal"><i class="fas fa-file-lines"></i> Summary</button>
                    <button class="btn btn-outline-secondary" type="button" data-toggle-table="categoryData"><i class="fas fa-table"></i> Data</button>
                    <button class="btn btn-outline-secondary" type="button" data-download-chart="categoryChart" data-file-name="report-categories" title="Download chart"><i class="fas fa-download"></i></button>
                </div>
            </header>
            @if($categoryStats->isNotEmpty())
                <div class="analytics-small-chart"><canvas id="categoryChart" aria-label="Report category chart"></canvas></div>
                <div class="chart-selection" id="categoryInsight"><strong>Explore:</strong> Click a category bar to see its workload share and planning implication.</div>
                <div class="analytics-data-table" id="categoryData">
                    <table><thead><tr><th>Category</th><th>Reports</th><th>Share</th></tr></thead><tbody>
                        @foreach($categoryStats as $category)
                            <tr><td>{{ $category['category'] }}</td><td>{{ number_format($category['count']) }}</td><td>{{ $totalReports > 0 ? number_format(($category['count'] / $totalReports) * 100, 1) : 0 }}%</td></tr>
                        @endforeach
                    </tbody></table>
                </div>
            @else
                <div class="empty-analytics"><i class="fas fa-chart-column"></i>No category data available.</div>
            @endif
        </section>

        <section class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <h3>Recommended Actions</h3>
                    <p>Suggested next steps based on the current evidence</p>
                </div>
            </header>
            <div class="decision-list">
                @if($topLocation)
                    <article class="decision-item" style="--decision-color:#d93645;">
                        <div class="decision-icon"><i class="fas fa-location-dot"></i></div>
                        <div><h4>Prioritize {{ $topLocation['location'] }}</h4><p>{{ $topLocation['interpretation'] }}</p></div>
                    </article>
                @endif
                @if($hazardReports > 0)
                    <article class="decision-item" style="--decision-color:#e99a00;">
                        <div class="decision-icon"><i class="fas fa-shield-halved"></i></div>
                        <div><h4>Review safety hazards first</h4><p>Assign immediate inspection to the {{ number_format($hazardReports) }} hazard-related report(s) before routine repairs.</p></div>
                    </article>
                @endif
                @if($topCategory)
                    <article class="decision-item" style="--decision-color:#1769e0;">
                        <div class="decision-icon"><i class="fas fa-boxes-stacked"></i></div>
                        <div><h4>Plan for {{ $topCategory['category'] }}</h4><p>This is the leading category with {{ number_format($topCategory['count']) }} report(s). Review spare parts, vendors, and preventive maintenance coverage.</p></div>
                    </article>
                @endif
                @if($resolutionRate < $targetResolutionRate)
                    <article class="decision-item" style="--decision-color:#d93645;">
                        <div class="decision-icon"><i class="fas fa-list-check"></i></div>
                        <div><h4>Reduce the unresolved backlog</h4><p>Review ageing assignments and raise the resolution rate from {{ number_format($resolutionRate, 1) }}% toward the {{ number_format($targetResolutionRate) }}% target.</p></div>
                    </article>
                @endif
                @if(!$topLocation && !$topCategory && $hazardReports === 0)
                    <div class="empty-analytics"><i class="fas fa-lightbulb"></i>Recommendations will appear when report data is available.</div>
                @endif
            </div>
        </section>
    </div>

    <section class="analytics-panel">
        <header class="analytics-panel-header">
            <div>
                <h3>Location Risk Ranking</h3>
                <p>Risk score weighs unresolved reports, safety hazards, and recorded cost</p>
            </div>
            <div class="analytics-chart-actions">
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#locationSummaryModal"><i class="fas fa-file-lines"></i> Summary</button>
                <button class="btn btn-outline-secondary" id="exportRiskCsv" type="button"><i class="fas fa-file-csv"></i> Export CSV</button>
            </div>
        </header>
        @if($locationStats->isNotEmpty())
            <div class="location-tools">
                <input class="form-control" id="locationRiskSearch" type="search" placeholder="Search location..." aria-label="Search locations">
                <select class="form-select" id="locationRiskFilter" aria-label="Filter by risk">
                    <option value="all">All risk levels</option>
                    <option value="high">High risk</option>
                    <option value="medium">Medium risk</option>
                    <option value="low">Low risk</option>
                </select>
                <a class="btn btn-outline-primary" href="{{ route('admin.reports') }}"><i class="fas fa-list"></i> Open Reports</a>
            </div>
            <div class="risk-table-wrap">
                <table class="risk-table" id="locationRiskTable">
                    <thead><tr><th>Rank</th><th><button class="risk-sort" type="button" data-sort="location">Location <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="total">Total <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="open">Open <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="hazards">Hazards <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="cost">Cost <i class="fas fa-sort"></i></button></th><th><button class="risk-sort" type="button" data-sort="risk">Risk <i class="fas fa-sort"></i></button></th><th>Interpretation</th></tr></thead>
                    <tbody id="locationRiskBody">
                        @foreach($locationStats->take(10) as $index => $location)
                            @php $riskClass = $location['risk_score'] >= 12 ? 'high' : ($location['risk_score'] >= 6 ? 'medium' : 'low'); @endphp
                            <tr data-risk-level="{{ $riskClass }}" data-location="{{ strtolower($location['location']) }}" data-total="{{ $location['total'] }}" data-open="{{ $location['open'] }}" data-hazards="{{ $location['hazards'] }}" data-cost="{{ $location['cost'] }}" data-risk="{{ $location['risk_score'] }}">
                                <td>{{ $index + 1 }}</td>
                                <td><button class="location-detail-button" type="button" data-location-detail="{{ $location['location'] }}" title="View {{ $location['location'] }} details">{{ $location['location'] }}</button></td>
                                <td>{{ number_format($location['total']) }}</td>
                                <td>{{ number_format($location['open']) }}</td>
                                <td>{{ number_format($location['hazards']) }}</td>
                                <td>PHP {{ number_format($location['cost'], 2) }}</td>
                                <td><span class="risk-score {{ $riskClass }}">{{ $location['risk_score'] }}</span></td>
                                <td>{{ $location['interpretation'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-analytics"><i class="fas fa-location-dot"></i>No location data available for this period.</div>
        @endif
    </section>
</main>

<div class="modal fade" id="executiveSummaryModal" tabindex="-1" aria-labelledby="executiveSummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title fs-5" id="executiveSummaryLabel"><i class="fas fa-wand-magic-sparkles text-primary"></i> Executive Maintenance Summary</h3>
                    <small class="text-muted">{{ $executiveSummary['period'] }}</small>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body executive-summary">
                @if($totalReports > 0)
                    <p>
                        During the selected period, the system recorded <strong>{{ number_format($totalReports) }} reports</strong>.
                        <strong>{{ number_format($resolvedReports) }}</strong> were resolved and <strong>{{ number_format($openReports) }}</strong> remain open,
                        resulting in a <strong>{{ number_format($resolutionRate, 1) }}% resolution rate</strong> against the {{ number_format($targetResolutionRate) }}% operational target.
                    </p>
                    <p>
                        Recorded repair cost totals <strong>PHP {{ number_format($totalCost, 2) }}</strong>.
                        @if($avgResolutionHours !== null)
                            Completed assignments took an average of <strong>{{ number_format($avgResolutionHours, 1) }} hours</strong> from assignment to resolution.
                        @endif
                        The system identified <strong>{{ number_format($hazardReports) }} safety hazard report(s)</strong>, which should take priority over routine requests.
                    </p>
                    @if($topLocation || $topCategory)
                        <p>
                            @if($topLocation)
                                <strong>{{ $topLocation['location'] }}</strong> has the highest location risk score at {{ $topLocation['risk_score'] }}, with {{ $topLocation['open'] }} open report(s).
                            @endif
                            @if($topCategory)
                                The most frequent category is <strong>{{ $topCategory['category'] }}</strong> with {{ number_format($topCategory['count']) }} report(s).
                            @endif
                        </p>
                    @endif
                    <div class="summary-callout">
                        <strong>Decision:</strong>
                        @if($resolutionRate < $targetResolutionRate)
                            Allocate staff first to unresolved hazards and the highest-risk location, then review ageing work orders until the resolution rate reaches target.
                        @elseif($hazardReports > 0)
                            Current resolution performance is healthy, but outstanding safety hazards still require immediate inspection.
                        @else
                            Current performance is within target. Maintain staffing levels and use category trends to schedule preventive maintenance.
                        @endif
                    </div>
                @else
                    <p>No reports match the selected filters. Expand the date range or select all locations before making an operational decision.</p>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" type="button" onclick="window.print()"><i class="fas fa-print"></i> Print Summary</button>
            </div>
        </div>
    </div>
</div>

@include('admin.partials.analytics-summaries')
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const historicalLabels = @json($trendStats->pluck('label')->values());
    const reportValues = @json($trendStats->pluck('reports')->values());
    const resolvedValues = @json($trendStats->pluck('resolved')->values());
    const totalReportCount = {{ (int) $totalReports }};
    const reportsUrl = @json(route('admin.reports'));
    const locationDetails = @json($locationStats->keyBy('location')->all());

    Chart.defaults.font.family = "'Inter', 'Segoe UI', sans-serif";
    Chart.defaults.color = '#66758a';

    const trendCanvas = document.getElementById('reportsTrendChart');
    if (trendCanvas) {
        new Chart(trendCanvas, {
            type: 'line',
            data: {
                labels: historicalLabels,
                datasets: [
                    {
                        label: 'Reports',
                        data: reportValues,
                        borderColor: '#1769e0',
                        backgroundColor: 'rgba(23, 105, 224, .10)',
                        fill: true,
                        tension: .3,
                        pointRadius: 4,
                        borderWidth: 2
                    },
                    {
                        label: 'Resolved',
                        data: resolvedValues,
                        borderColor: '#148a58',
                        backgroundColor: 'transparent',
                        tension: .3,
                        pointRadius: 4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e7ebf0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        new Chart(statusCanvas, {
            type: 'doughnut',
            data: {
                labels: @json($statusStats->pluck('status')->values()),
                datasets: [{
                    data: @json($statusStats->pluck('count')->values()),
                    backgroundColor: ['#1769e0', '#148a58', '#e99a00', '#d93645', '#7557c5', '#5f6f82'],
                    borderColor: '#ffffff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } },
                onClick: function (event, elements, chart) {
                    if (!elements.length) return;
                    const index = elements[0].index;
                    const status = chart.data.labels[index];
                    const count = Number(chart.data.datasets[0].data[index]);
                    const share = totalReportCount > 0 ? ((count / totalReportCount) * 100).toFixed(1) : '0.0';
                    const insight = document.getElementById('statusInsight');
                    const reportLink = String(status).toLowerCase() === 'resolved'
                        ? ' Resolved items are represented here for performance analysis.'
                        : ' <a href="' + reportsUrl + '?status=' + encodeURIComponent(status) + '">Open matching reports</a>';
                    insight.innerHTML = '<strong>' + escapeHtml(status) + ':</strong> ' + count.toLocaleString() + ' reports (' + share + '% of the selected workload).' + reportLink;
                }
            }
        });
    }

    const categoryCanvas = document.getElementById('categoryChart');
    if (categoryCanvas) {
        new Chart(categoryCanvas, {
            type: 'bar',
            data: {
                labels: @json($categoryStats->pluck('category')->values()),
                datasets: [{
                    label: 'Reports',
                    data: @json($categoryStats->pluck('count')->values()),
                    backgroundColor: ['#1769e0', '#10a6a6', '#e99a00', '#7557c5', '#d93645', '#148a58'],
                    borderRadius: 4,
                    maxBarThickness: 42
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                onClick: function (event, elements, chart) {
                    if (!elements.length) return;
                    const index = elements[0].index;
                    const category = chart.data.labels[index];
                    const count = Number(chart.data.datasets[0].data[index]);
                    const share = totalReportCount > 0 ? ((count / totalReportCount) * 100).toFixed(1) : '0.0';
                    document.getElementById('categoryInsight').innerHTML = '<strong>' + escapeHtml(category) + ':</strong> ' + count.toLocaleString() + ' reports (' + share + '% of workload). Review recurring causes, parts availability, and preventive maintenance for this category.';
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e7ebf0' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    function escapeHtml(value) {
        const node = document.createElement('div');
        node.textContent = String(value);
        return node.innerHTML;
    }

    function renderLocationBreakdown(targetId, values) {
        const target = document.getElementById(targetId);
        if (!target) return;
        const entries = Object.entries(values || {});
        target.innerHTML = entries.length
            ? entries.map(function (entry) {
                return '<div class="location-breakdown-row"><span>' + escapeHtml(entry[0]) + '</span><strong>' + Number(entry[1]).toLocaleString() + '</strong></div>';
            }).join('')
            : '<div class="text-muted small">No breakdown data available.</div>';
    }

    document.querySelectorAll('[data-location-detail]').forEach(function (button) {
        button.addEventListener('click', function () {
            const location = locationDetails[button.dataset.locationDetail];
            if (!location) return;

            const riskLevel = Number(location.risk_score) >= 12 ? 'High' : (Number(location.risk_score) >= 6 ? 'Medium' : 'Low');
            const riskColor = riskLevel === 'High' ? '#d93645' : (riskLevel === 'Medium' ? '#e99a00' : '#148a58');
            document.getElementById('locationDetailName').textContent = location.location;
            document.getElementById('locationDetailTotal').textContent = Number(location.total).toLocaleString();
            document.getElementById('locationDetailOpen').textContent = Number(location.open).toLocaleString();
            document.getElementById('locationDetailResolved').textContent = Number(location.resolved).toLocaleString();
            document.getElementById('locationDetailHazards').textContent = Number(location.hazards).toLocaleString();
            document.getElementById('locationDetailRisk').textContent = Number(location.risk_score).toLocaleString();
            document.getElementById('locationDetailRate').textContent = Number(location.resolution_rate).toFixed(1) + '%';
            document.getElementById('locationDetailCost').textContent = 'PHP ' + Number(location.cost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('locationDetailPriority').textContent = riskLevel;
            document.getElementById('locationDetailPriority').style.color = riskColor;
            document.getElementById('locationDetailInterpretation').textContent = location.interpretation;
            document.getElementById('locationDetailCallout').style.setProperty('--location-risk-color', riskColor);
            document.getElementById('locationDetailReportsLink').href = reportsUrl + '?search=' + encodeURIComponent(location.location);

            renderLocationBreakdown('locationStatusBreakdown', location.status_breakdown);
            renderLocationBreakdown('locationCategoryBreakdown', location.category_breakdown);

            const recentBody = document.getElementById('locationRecentReports');
            const reports = Array.isArray(location.recent_reports) ? location.recent_reports : [];
            recentBody.innerHTML = reports.length
                ? reports.map(function (report) {
                    return '<tr>' +
                        '<td>' + escapeHtml(report.date || 'Unknown') + '</td>' +
                        '<td><strong>' + escapeHtml(report.title) + '</strong></td>' +
                        '<td>' + escapeHtml(report.category) + '</td>' +
                        '<td>' + escapeHtml(report.status) + '</td>' +
                        '<td>' + escapeHtml(report.severity) + '</td>' +
                        '<td>' + (report.is_hazard ? '<span class="text-danger fw-bold">Yes</span>' : 'No') + '</td>' +
                        '<td>PHP ' + Number(report.cost).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '</td>' +
                    '</tr>';
                }).join('')
                : '<tr><td colspan="7" class="text-center text-muted">No recent reports available.</td></tr>';

            bootstrap.Modal.getOrCreateInstance(document.getElementById('locationDetailModal')).show();
        });
    });

    document.querySelectorAll('[data-toggle-table]').forEach(function (button) {
        button.addEventListener('click', function () {
            const table = document.getElementById(button.dataset.toggleTable);
            if (!table) return;
            const visible = table.classList.toggle('is-visible');
            button.innerHTML = visible ? '<i class="fas fa-eye-slash"></i> Hide Data' : '<i class="fas fa-table"></i> Data';
        });
    });

    document.querySelectorAll('[data-download-chart]').forEach(function (button) {
        button.addEventListener('click', function () {
            const canvas = document.getElementById(button.dataset.downloadChart);
            if (!canvas) return;
            const link = document.createElement('a');
            link.download = (button.dataset.fileName || 'analytics-chart') + '.png';
            link.href = canvas.toDataURL('image/png', 1);
            link.click();
        });
    });

    document.querySelectorAll('[data-print-summary]').forEach(function (button) {
        button.addEventListener('click', function () {
            const content = document.getElementById(button.dataset.printSummary);
            if (!content) return;
            const printWindow = window.open('', '_blank', 'width=900,height=700');
            if (!printWindow) return;
            printWindow.document.write('<!doctype html><html><head><title>' + escapeHtml(button.dataset.title) + '</title><style>body{font-family:Arial,sans-serif;color:#23344d;padding:36px;line-height:1.65}h1{font-size:22px}.analytics-summary-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.analytics-summary-metric,.summary-callout{border:1px solid #dce2ea;padding:12px}.analytics-summary-metric span{display:block;color:#66758a;font-size:11px;text-transform:uppercase}.analytics-summary-metric strong{display:block;font-size:18px}.summary-callout{margin-top:14px;border-left:4px solid #1769e0}@media print{body{padding:0}}</style></head><body><h1>' + escapeHtml(button.dataset.title) + '</h1>' + content.innerHTML + '</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
        });
    });

    const riskBody = document.getElementById('locationRiskBody');
    const riskSearch = document.getElementById('locationRiskSearch');
    const riskFilter = document.getElementById('locationRiskFilter');

    function filterRiskRows() {
        if (!riskBody) return;
        const search = (riskSearch ? riskSearch.value : '').trim().toLowerCase();
        const level = riskFilter ? riskFilter.value : 'all';
        let rank = 1;
        Array.from(riskBody.rows).forEach(function (row) {
            const matchesSearch = !search || row.dataset.location.includes(search);
            const matchesLevel = level === 'all' || row.dataset.riskLevel === level;
            row.hidden = !(matchesSearch && matchesLevel);
            if (!row.hidden) row.cells[0].textContent = rank++;
        });
    }

    if (riskSearch) riskSearch.addEventListener('input', filterRiskRows);
    if (riskFilter) riskFilter.addEventListener('change', filterRiskRows);

    const sortDirections = {};
    document.querySelectorAll('.risk-sort').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!riskBody) return;
            const key = button.dataset.sort;
            sortDirections[key] = sortDirections[key] === 'asc' ? 'desc' : 'asc';
            const direction = sortDirections[key] === 'asc' ? 1 : -1;
            const rows = Array.from(riskBody.rows);
            rows.sort(function (left, right) {
                if (key === 'location') return left.dataset.location.localeCompare(right.dataset.location) * direction;
                return (Number(left.dataset[key]) - Number(right.dataset[key])) * direction;
            });
            rows.forEach(function (row) { riskBody.appendChild(row); });
            filterRiskRows();
        });
    });

    const exportRiskButton = document.getElementById('exportRiskCsv');
    if (exportRiskButton) {
        exportRiskButton.addEventListener('click', function () {
            if (!riskBody) return;
            const rows = [['Rank', 'Location', 'Total', 'Open', 'Hazards', 'Cost', 'Risk', 'Interpretation']];
            Array.from(riskBody.rows).filter(function (row) { return !row.hidden; }).forEach(function (row) {
                rows.push(Array.from(row.cells).map(function (cell) { return cell.innerText.trim(); }));
            });
            const csv = rows.map(function (row) {
                return row.map(function (value) { return '"' + String(value).replace(/"/g, '""') + '"'; }).join(',');
            }).join('\r\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'location-risk-ranking.csv';
            link.click();
            URL.revokeObjectURL(link.href);
        });
    }
});
</script>
@endsection
