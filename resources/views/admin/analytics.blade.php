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
            <div class="chart-legend" aria-label="Chart legend">
                <span><i style="background:#1769e0"></i> Reports</span>
                <span><i style="background:#148a58"></i> Resolved</span>
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
    </section>

    <div class="analytics-grid-two">
        <section class="analytics-panel">
            <header class="analytics-panel-header">
                <div>
                    <h3>Status Distribution</h3>
                    <p>Current workload by report status</p>
                </div>
            </header>
            @if($statusStats->isNotEmpty())
                <div class="analytics-small-chart"><canvas id="statusChart" aria-label="Report status chart"></canvas></div>
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
            </header>
            @if($categoryStats->isNotEmpty())
                <div class="analytics-small-chart"><canvas id="categoryChart" aria-label="Report category chart"></canvas></div>
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
        </header>
        @if($locationStats->isNotEmpty())
            <div class="risk-table-wrap">
                <table class="risk-table">
                    <thead><tr><th>Rank</th><th>Location</th><th>Total</th><th>Open</th><th>Hazards</th><th>Cost</th><th>Risk</th><th>Interpretation</th></tr></thead>
                    <tbody>
                        @foreach($locationStats->take(10) as $index => $location)
                            @php $riskClass = $location['risk_score'] >= 12 ? 'high' : ($location['risk_score'] >= 6 ? 'medium' : 'low'); @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td><strong>{{ $location['location'] }}</strong></td>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const historicalLabels = @json($trendStats->pluck('label')->values());
    const reportValues = @json($trendStats->pluck('reports')->values());
    const resolvedValues = @json($trendStats->pluck('resolved')->values());

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
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } } }
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
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e7ebf0' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
@endsection
