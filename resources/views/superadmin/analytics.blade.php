@extends('superadmin.layout')

@section('page_title', 'System Administrator Analytics')

@section('extra_styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection

@section('content')

<style>
    .sa-analytics-intro{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:18px;padding:18px 20px;border-left:4px solid var(--sa-accent);border-radius:8px;background:var(--sa-card);color:var(--sa-text)}
    .sa-analytics-intro h2{margin:0 0 5px;font-size:20px}.sa-analytics-intro p{margin:0;color:var(--sa-muted);font-size:13px}
    .sa-analytics-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}.sa-analytics-kpi{position:relative;padding:16px 18px;border-left:4px solid var(--metric-color)}
    .sa-analytics-kpi header{display:flex;align-items:center;justify-content:space-between;color:var(--sa-muted);font-size:11px;font-weight:700;text-transform:uppercase}.sa-analytics-kpi header i{color:var(--metric-color);font-size:17px}.sa-analytics-kpi strong{display:block;margin-top:7px;color:var(--sa-text);font-size:28px}.sa-analytics-kpi p{margin:4px 0 0;color:var(--sa-muted);font-size:11px}
    .sa-operations-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.sa-operation-link{display:flex;align-items:center;justify-content:space-between;padding:11px 13px;border:1px solid var(--sa-border);border-radius:7px;color:var(--sa-text);text-decoration:none}.sa-operation-link:hover{border-color:var(--sa-accent);color:var(--sa-accent)}.sa-operation-link strong{font-size:18px}
    .sa-summary-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin:18px 0}.sa-summary-metric{padding:12px;border:1px solid var(--sa-border);border-radius:7px;background:var(--sa-hover)}.sa-summary-metric span,.sa-summary-metric small{display:block;color:var(--sa-muted);font-size:11px}.sa-summary-metric strong{display:block;margin:4px 0;color:var(--sa-text);font-size:24px}.sa-summary-assessment{padding:14px 16px;border-left:4px solid var(--sa-accent);border-radius:6px;background:var(--sa-hover);color:var(--sa-text);line-height:1.55}.sa-summary-priority{display:grid;grid-template-columns:28px minmax(0,1fr) auto;align-items:start;gap:10px;padding:12px 0;border-bottom:1px solid var(--sa-border);color:var(--sa-text)}.sa-summary-priority:last-child{border-bottom:0}.sa-summary-priority>i{margin-top:3px}.sa-summary-priority strong,.sa-summary-priority span{display:block}.sa-summary-priority span{margin-top:2px;color:var(--sa-muted);font-size:12px}.sa-summary-priority.critical>i{color:#ef4444}.sa-summary-priority.warning>i{color:#f59e0b}.sa-summary-priority.info>i{color:#3b82f6}.sa-summary-priority.success>i{color:#22c55e}
    #systemExecutiveSummaryModal .modal-dialog{--bs-modal-width:min(calc(100vw - 48px),1440px);width:min(calc(100vw - 48px),1440px)!important;max-width:min(calc(100vw - 48px),1440px)!important}
    #systemExecutiveSummaryModal .modal-content{width:100%!important;max-width:none!important}
    #systemExecutiveSummaryModal .modal-body{overflow-x:hidden}
    .sa-summary-letterhead{display:grid;grid-template-columns:100px minmax(0,1fr) 100px;gap:20px;align-items:center;margin-bottom:20px;padding:18px;border-bottom:1px solid var(--sa-border);background:var(--sa-hover)}
    .sa-summary-letterhead img{display:block;width:88px;height:88px;margin:auto;object-fit:contain}.sa-summary-letterhead-copy{text-align:center}.sa-summary-letterhead-title{font-size:clamp(14px,1.25vw,18px);font-weight:800;line-height:1.3;color:var(--sa-text)}.sa-summary-letterhead-address{margin-top:7px;color:var(--sa-muted);font-size:11px;line-height:1.35}
    @media(max-width:1000px){.sa-analytics-kpis,.sa-summary-metrics{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:620px){.sa-analytics-intro{align-items:flex-start;flex-direction:column}.sa-analytics-kpis,.sa-operations-grid,.sa-summary-metrics{grid-template-columns:1fr}.sa-summary-priority{grid-template-columns:28px minmax(0,1fr)}.sa-summary-priority a{grid-column:2}#systemExecutiveSummaryModal .modal-dialog{width:calc(100vw - 16px)!important;max-width:calc(100vw - 16px)!important}.sa-summary-letterhead{grid-template-columns:52px minmax(0,1fr) 52px;gap:8px;padding:12px}.sa-summary-letterhead img{width:48px;height:48px}.sa-summary-letterhead-title{font-size:10px}.sa-summary-letterhead-address{font-size:8px}}
</style>

<div class="sa-analytics-intro">
    <div>
        <h2>Daily Operations Overview</h2>
        <p>{{ $executiveSummary }}</p>
    </div>
    <button class="sa-btn sa-btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#systemExecutiveSummaryModal">
        <i class="fas fa-file-lines"></i> Executive Summary
    </button>
</div>

<div class="sa-analytics-kpis">
    @foreach($systemMetrics as $metric)
        <article class="sa-card sa-analytics-kpi" style="--metric-color:{{ $metric['color'] }}">
            <header><span>{{ $metric['label'] }}</span><i class="fas {{ $metric['icon'] }}"></i></header>
            <strong>{{ number_format($metric['value']) }}</strong>
            <p>{{ $metric['context'] }}</p>
        </article>
    @endforeach
</div>

<div class="sa-card mb-4">
    <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px"><i class="fas fa-gauge-high me-2" style="color:var(--sa-accent)"></i>Operational Work Queues</div>
    <div class="sa-operations-grid">
        @foreach($operationsOverview as $operation)
            <a class="sa-operation-link" href="{{ $operation['url'] }}"><span>{{ $operation['label'] }}</span><strong>{{ number_format($operation['count']) }}</strong></a>
        @endforeach
    </div>
</div>

<div class="row g-4">
    {{-- Concerns 12-month --}}
    <div class="col-md-6">
        <div class="sa-card">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-triangle-exclamation me-2" style="color:var(--sa-accent2)"></i>Concerns — Last 12 Months
            </div>
            <canvas id="concernsChart" height="200"></canvas>
        </div>
    </div>

    {{-- Reports 12-month --}}
    <div class="col-md-6">
        <div class="sa-card">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-file-lines me-2" style="color:var(--sa-info)"></i>Reports — Last 12 Months
            </div>
            <canvas id="reportsChart" height="200"></canvas>
        </div>
    </div>

    {{-- User Growth --}}
    <div class="col-md-6">
        <div class="sa-card">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-users me-2" style="color:var(--sa-success)"></i>User Growth — Last 12 Months
            </div>
            <canvas id="userChart" height="200"></canvas>
        </div>
    </div>

    {{-- Concerns by Category --}}
    <div class="col-md-6">
        <div class="sa-card">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-tags me-2" style="color:var(--sa-warning)"></i>Concerns by Category (Top 10)
            </div>
            <canvas id="catChart" height="200"></canvas>
        </div>
    </div>

    {{-- Top Reporters --}}
    <div class="col-md-6">
        <div class="sa-card">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-trophy me-2" style="color:var(--sa-warning)"></i>Top Reporters
            </div>
            @forelse($topReporters as $i => $user)
            <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--sa-border)">
                <div style="width:22px;text-align:center;font-size:12px;color:var(--sa-muted);font-weight:600">#{{ $i+1 }}</div>
                <div class="sa-avatar" style="width:28px;height:28px;font-size:11px">{{ strtoupper(substr($user->name,0,1)) }}</div>
                <div style="flex:1">
                    <div style="font-size:13px;font-weight:500">{{ $user->name }}</div>
                    <div style="font-size:11px;color:var(--sa-muted)">{{ str_replace('_',' ',ucfirst($user->role ?? '')) }}</div>
                </div>
                <span class="sa-badge sa-badge-purple">{{ $user->concerns_count }} concerns</span>
            </div>
            @empty
            <p style="color:var(--sa-muted);font-size:13px">No data.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="systemExecutiveSummaryModal" tabindex="-1" aria-labelledby="systemExecutiveSummaryLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h3 class="modal-title fs-5" id="systemExecutiveSummaryLabel"><i class="fas fa-wand-magic-sparkles text-primary me-2"></i>System Administrator Executive Summary</h3>
                    <small class="text-muted">Generated {{ $executiveBrief['generated_at'] }}</small>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="systemExecutiveSummaryContent">
                <div class="sa-summary-letterhead">
                    <img src="{{ asset('Campfix/Images/logo.png') }}" alt="CampFix logo">
                    <div class="sa-summary-letterhead-copy">
                        <div class="sa-summary-letterhead-title">CampFix: A Web-Based Platform for Campus Facility Requests, Concern Reporting, and Data-Driven Decision Support for STI College Novaliches</div>
                        <div class="sa-summary-letterhead-address">STI Academic Center, Diamond Avenue corner Quirino Highway<br>San Bartolome, Novaliches, Quezon City, 1116 Metro Manila</div>
                    </div>
                    <img src="{{ asset('Campfix/Images/STI-Academic-Seal-One-Color_400.png') }}" alt="STI academic seal">
                </div>
                <div class="sa-summary-assessment">
                    <strong>Executive assessment</strong>
                    <div>{{ $executiveBrief['assessment'] }}</div>
                </div>

                <div class="sa-summary-metrics">
                    @foreach($executiveBrief['metrics'] as $metric)
                        <div class="sa-summary-metric">
                            <span>{{ $metric['label'] }}</span>
                            <strong>{{ number_format($metric['value']) }}</strong>
                            <small>{{ $metric['context'] }}</small>
                        </div>
                    @endforeach
                </div>

                <h4 class="fs-6 mb-2">Recommended priorities</h4>
                <div>
                    @foreach($executiveBrief['priorities'] as $priority)
                        <div class="sa-summary-priority {{ $priority['level'] }}">
                            <i class="fas {{ $priority['level'] === 'critical' ? 'fa-circle-exclamation' : ($priority['level'] === 'warning' ? 'fa-triangle-exclamation' : ($priority['level'] === 'success' ? 'fa-circle-check' : 'fa-circle-info')) }}"></i>
                            <div><strong>{{ $priority['title'] }}</strong><span>{{ $priority['detail'] }}</span></div>
                            <a class="sa-btn sa-btn-ghost sa-btn-sm" href="{{ $priority['url'] }}">Review</a>
                        </div>
                    @endforeach
                </div>

                <h4 class="fs-6 mt-4 mb-2">Operational workload</h4>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Queue</th><th class="text-end">Current count</th></tr></thead>
                        <tbody>
                            @foreach($operationsOverview as $operation)
                                <tr><td>{{ $operation['label'] }}</td><td class="text-end fw-semibold">{{ number_format($operation['count']) }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" type="button" id="printSystemExecutiveSummary"><i class="fas fa-file-pdf me-1"></i> Print / Save PDF</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function getChartColors() {
    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
    return {
        grid: isLight ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.05)',
        tick: isLight ? '#64748b' : '#8892a4',
    };
}

function makeOpts(extra = {}) {
    const c = getChartColors();
    return {
        responsive: true,
        plugins: { legend: { labels: { color: c.tick, font: { size: 11 } } } },
        scales: {
            x: { grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 10 } } },
            y: { grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 10 } }, beginAtZero: true }
        },
        ...extra
    };
}

const concernsData = @json($monthlyConcerns);
const c1 = new Chart(document.getElementById('concernsChart'), {
    type: 'bar',
    data: {
        labels: concernsData.map(d => d.month),
        datasets: [
            { label: 'Total', data: concernsData.map(d => d.total), backgroundColor: 'rgba(168,85,247,.5)', borderColor: '#a855f7', borderWidth: 1, borderRadius: 3 },
            { label: 'Resolved', data: concernsData.map(d => d.resolved), backgroundColor: 'rgba(34,197,94,.5)', borderColor: '#22c55e', borderWidth: 1, borderRadius: 3 },
        ]
    },
    options: makeOpts()
});

const reportsData = @json($monthlyReports);
const c2 = new Chart(document.getElementById('reportsChart'), {
    type: 'bar',
    data: {
        labels: reportsData.map(d => d.month),
        datasets: [
            { label: 'Total', data: reportsData.map(d => d.total), backgroundColor: 'rgba(59,130,246,.5)', borderColor: '#3b82f6', borderWidth: 1, borderRadius: 3 },
            { label: 'Resolved', data: reportsData.map(d => d.resolved), backgroundColor: 'rgba(34,197,94,.5)', borderColor: '#22c55e', borderWidth: 1, borderRadius: 3 },
        ]
    },
    options: makeOpts()
});

const userData = @json($userGrowth);
const c3 = new Chart(document.getElementById('userChart'), {
    type: 'line',
    data: {
        labels: userData.map(d => d.month),
        datasets: [{
            label: 'New Users',
            data: userData.map(d => d.count),
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34,197,94,.1)',
            borderWidth: 2, fill: true, tension: .4,
            pointBackgroundColor: '#22c55e', pointRadius: 4,
        }]
    },
    options: makeOpts()
});

const catData = @json($concernsByCategory);
const c4 = new Chart(document.getElementById('catChart'), {
    type: 'bar',
    data: {
        labels: catData.map(d => d.category || 'Uncategorized'),
        datasets: [{
            label: 'Concerns',
            data: catData.map(d => d.count),
            backgroundColor: 'rgba(245,158,11,.5)',
            borderColor: '#f59e0b',
            borderWidth: 1, borderRadius: 3,
        }]
    },
    options: makeOpts({ indexAxis: 'y' })
});

window.saCharts = [c1, c2, c3, c4];

document.getElementById('printSystemExecutiveSummary')?.addEventListener('click', function () {
    const content = document.getElementById('systemExecutiveSummaryContent');
    if (!content) return;
    const printableContent = content.cloneNode(true);
    const letterhead = printableContent.querySelector('.sa-summary-letterhead');
    if (letterhead) letterhead.remove();

    const printWindow = window.open('', '_blank', 'width=1000,height=760');
    if (!printWindow) return;

    printWindow.document.write(`<!doctype html><html><head><title>System Administrator Executive Summary</title><style>
        @page{size:A4 portrait;margin:10mm 14mm}*{box-sizing:border-box}body{font-family:Arial,sans-serif;color:#172033;padding:0;line-height:1.5}.sa-summary-letterhead{display:grid;grid-template-columns:30mm 1fr 30mm;gap:5mm;align-items:center;padding:0 4mm 5mm;border-bottom:1px solid #111}.sa-summary-letterhead img{display:block;width:24mm;height:24mm;margin:auto;object-fit:contain}.sa-summary-letterhead-copy{text-align:center}.sa-summary-letterhead-title{font-size:8.5pt;font-weight:700;line-height:1.25}.sa-summary-letterhead-address{margin-top:1mm;font-size:7.5pt;line-height:1.35}h1{font-size:18pt;margin:7mm 0 1mm}p{color:#5f6b7a}.sa-summary-assessment{padding:14px 16px;border-left:4px solid #6f42c1;background:#f6f3ff}.sa-summary-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:18px 0}.sa-summary-metric{padding:12px;border:1px solid #dce2ea}.sa-summary-metric span,.sa-summary-metric small{display:block;color:#66758a;font-size:11px}.sa-summary-metric strong{display:block;font-size:23px}.sa-summary-priority{display:grid;grid-template-columns:24px 1fr;gap:8px;padding:11px 0;border-bottom:1px solid #dce2ea;break-inside:avoid}.sa-summary-priority span{display:block;color:#66758a;font-size:12px}.sa-summary-priority a{display:none}table{width:100%;border-collapse:collapse}th,td{padding:8px;border:1px solid #dce2ea;text-align:left}.text-end{text-align:right!important}
    </style></head><body>${letterhead ? letterhead.outerHTML : ''}<h1>System Administrator Executive Summary</h1><p>Generated {{ $executiveBrief['generated_at'] }}</p>${printableContent.innerHTML}</body></html>`);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
});
</script>
@endsection
