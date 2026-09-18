@extends('superadmin.layout')

@section('page_title', 'System Administrator Analytics')

@section('extra_styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endsection

@section('content')

<style>
    .sa-analytics-intro{margin-bottom:18px;padding:18px 20px;border-left:4px solid var(--sa-accent);border-radius:8px;background:var(--sa-card);color:var(--sa-text)}
    .sa-analytics-intro h2{margin:0 0 5px;font-size:20px}.sa-analytics-intro p{margin:0;color:var(--sa-muted);font-size:13px}
    .sa-analytics-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}.sa-analytics-kpi{position:relative;padding:16px 18px;border-left:4px solid var(--metric-color)}
    .sa-analytics-kpi header{display:flex;align-items:center;justify-content:space-between;color:var(--sa-muted);font-size:11px;font-weight:700;text-transform:uppercase}.sa-analytics-kpi header i{color:var(--metric-color);font-size:17px}.sa-analytics-kpi strong{display:block;margin-top:7px;color:var(--sa-text);font-size:28px}.sa-analytics-kpi p{margin:4px 0 0;color:var(--sa-muted);font-size:11px}
    .sa-operations-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.sa-operation-link{display:flex;align-items:center;justify-content:space-between;padding:11px 13px;border:1px solid var(--sa-border);border-radius:7px;color:var(--sa-text);text-decoration:none}.sa-operation-link:hover{border-color:var(--sa-accent);color:var(--sa-accent)}.sa-operation-link strong{font-size:18px}
    @media(max-width:1000px){.sa-analytics-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:620px){.sa-analytics-kpis,.sa-operations-grid{grid-template-columns:1fr}}
</style>

<div class="sa-analytics-intro">
    <h2>Daily Operations Overview</h2>
    <p>{{ $executiveSummary }}</p>
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
</script>
@endsection
