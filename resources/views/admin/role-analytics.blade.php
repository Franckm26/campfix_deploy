@extends('layouts.app')

@section('page_title')
<div>
    <h2 class="mb-0"><i class="fas fa-chart-line me-2"></i>{{ $pageTitle }}</h2>
    <p class="text-muted mb-0">{{ $pageSubtitle }}</p>
</div>
@endsection

@section('styles')
<style>
    .role-analytics { --ra-border:#dce3ed; --ra-ink:#132b4d; display:grid; gap:18px; padding:20px; background:#f5f7fa; min-height:calc(100vh - 90px); }
    .ra-panel { background:#fff; border:1px solid var(--ra-border); border-radius:10px; box-shadow:0 2px 8px rgba(17,43,77,.05); }
    .ra-filter { display:flex; flex-wrap:wrap; align-items:end; gap:12px; padding:16px; }
    .ra-filter label { display:block; margin-bottom:5px; color:#52647d; font-size:12px; font-weight:700; }
    .ra-filter .form-control { min-width:180px; height:42px; }
    .ra-filter .btn { height:42px; }
    .ra-summary-button { margin-left:auto; }
    .ra-kpis { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
    .ra-kpi { position:relative; min-height:126px; padding:18px 20px; overflow:hidden; }
    .ra-kpi::before { position:absolute; inset:0 auto 0 0; width:5px; background:var(--accent); content:''; }
    .ra-kpi-head { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .ra-kpi-label { color:#65758b; font-size:12px; font-weight:800; text-transform:uppercase; }
    .ra-kpi-icon { display:grid; width:38px; height:38px; place-items:center; border-radius:50%; color:var(--accent); background:#f2f6fb; }
    .ra-kpi-value { margin-top:8px; color:var(--ra-ink); font-size:30px; font-weight:800; line-height:1; }
    .ra-kpi-context { margin-top:7px; color:#6c7c91; font-size:12px; }
    .ra-grid { display:grid; grid-template-columns:minmax(0,2fr) minmax(300px,1fr); gap:18px; }
    .ra-header { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:17px 20px; border-bottom:1px solid var(--ra-border); }
    .ra-header h3 { margin:0; color:var(--ra-ink); font-size:19px; font-weight:750; }
    .ra-header p { margin:3px 0 0; color:#6c7c91; font-size:12px; }
    .ra-chart { position:relative; height:330px; padding:18px; }
    .ra-bars { display:grid; gap:13px; padding:20px; }
    .ra-bar-row { display:grid; grid-template-columns:minmax(115px,1fr) 2fr 44px; align-items:center; gap:10px; }
    .ra-bar-label { overflow:hidden; color:#334a68; font-weight:650; text-overflow:ellipsis; white-space:nowrap; }
    .ra-bar-track { height:12px; overflow:hidden; border-radius:20px; background:#edf1f6; }
    .ra-bar-fill { height:100%; min-width:4px; border-radius:20px; background:#1769e0; }
    .ra-bar-count { color:#132b4d; font-weight:800; text-align:right; }
    .ra-table-wrap { overflow:auto; }
    .ra-table { width:100%; min-width:780px; margin:0; border-collapse:collapse; }
    .ra-table th,.ra-table td { padding:14px 16px; border-bottom:1px solid var(--ra-border); vertical-align:middle; }
    .ra-table th { color:#52647d; background:#f8fafc; font-size:12px; text-transform:uppercase; }
    .ra-table td { color:#263d5c; }
    .ra-empty { padding:35px; color:#718096; text-align:center; }
    .ra-badge { display:inline-block; padding:5px 9px; border-radius:20px; color:#28425f; background:#edf3fa; font-size:12px; font-weight:750; }
    .role-summary-swal { text-align:left; }
    .role-summary-swal .summary-scope { margin-bottom:14px; padding:10px 12px; border-left:4px solid #1769e0; color:#52647d; background:#f5f8fc; }
    .role-summary-swal li { margin-bottom:10px; color:#263d5c; line-height:1.5; }
    @media (max-width:1100px) { .ra-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.ra-grid{grid-template-columns:1fr}.ra-summary-button{margin-left:0} }
    @media (max-width:640px) { .role-analytics{padding:12px}.ra-kpis{grid-template-columns:1fr}.ra-filter>*{width:100%}.ra-filter .form-control,.ra-filter .btn{width:100%}.ra-chart{height:275px}.ra-bar-row{grid-template-columns:105px 1fr 36px} }
</style>
@endsection

@section('content')
<main class="role-analytics">
    <form class="ra-panel ra-filter" method="GET" action="{{ route('role.analytics') }}">
        <div><label for="date_from">From</label><input class="form-control" id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"></div>
        <div><label for="date_to">To</label><input class="form-control" id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i> Apply</button>
        <a class="btn btn-outline-secondary" href="{{ route('role.analytics') }}">Reset</a>
        <button class="btn btn-outline-primary ra-summary-button" id="roleExecutiveSummary" type="button"><i class="fas fa-file-lines me-1"></i> Executive Summary</button>
    </form>

    <section class="ra-kpis">
        @foreach($metrics as $metric)
            <article class="ra-panel ra-kpi" style="--accent:{{ $metric['color'] }}">
                <div class="ra-kpi-head"><span class="ra-kpi-label">{{ $metric['label'] }}</span><span class="ra-kpi-icon"><i class="fas {{ $metric['icon'] }}"></i></span></div>
                <div class="ra-kpi-value">{{ number_format($metric['value']) }}</div>
                <div class="ra-kpi-context">{{ $metric['context'] }}</div>
            </article>
        @endforeach
    </section>

    <section class="ra-grid">
        <article class="ra-panel">
            <header class="ra-header"><div><h3>Six-Month Trend</h3><p>{{ $mode === 'mis' ? 'Technology/Internet tasks submitted and resolved' : 'Relevant event requests submitted and fully approved' }}</p></div></header>
            <div class="ra-chart"><canvas id="roleTrendChart"></canvas></div>
        </article>
        <article class="ra-panel">
            <header class="ra-header"><div><h3>Status Distribution</h3><p>Current workload by status</p></div></header>
            <div class="ra-bars">
                @php $statusMax = max(1, (int) $statusStats->max('count')); @endphp
                @forelse($statusStats as $stat)
                    <div class="ra-bar-row"><span class="ra-bar-label">{{ $stat['label'] }}</span><span class="ra-bar-track"><span class="ra-bar-fill" style="display:block;width:{{ ($stat['count'] / $statusMax) * 100 }}%"></span></span><span class="ra-bar-count">{{ $stat['count'] }}</span></div>
                @empty
                    <div class="ra-empty">No activity in this period.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="ra-grid">
        <article class="ra-panel">
            <header class="ra-header"><div><h3>{{ $mode === 'mis' ? 'Technology/Internet Task Details' : 'Event Approval Details' }}</h3><p>The latest records within your role scope</p></div></header>
            <div class="ra-table-wrap">
                <table class="ra-table">
                    <thead><tr>
                        @if($mode === 'mis')<th>Ticket</th><th>Issue</th><th>Location</th><th>Assigned To</th><th>Status</th><th>Created</th>
                        @else<th>Request</th><th>Requester</th><th>Type</th><th>Current Approver</th><th>Status</th><th>Date</th>@endif
                    </tr></thead>
                    <tbody>
                    @forelse($recentItems as $item)
                        <tr>
                            @if($mode === 'mis')
                                <td>RPT-{{ str_pad((string)$item->id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ $item->title ?: 'Untitled report' }}</td><td>{{ $item->location ?: 'Not specified' }}</td><td>{{ $item->assigned_to ? ($item->assigned_to === auth()->id() ? 'You' : ($misAssignees->get($item->assigned_to) ?: 'Assigned MIS user')) : 'Unassigned' }}</td><td><span class="ra-badge">{{ $item->status }}</span></td><td>{{ optional($item->created_at)->format('m/d/Y') }}</td>
                            @else
                                <td>EVT-{{ str_pad((string)$item->id, 5, '0', STR_PAD_LEFT) }}</td><td>{{ optional($item->user)->name ?: 'Deleted user' }}</td><td>{{ $item->request_type ?: 'Unspecified' }}</td><td>{{ $item->requiredApprovalRole() ? str($item->requiredApprovalRole())->replace('_',' ')->title() : 'Complete' }}</td><td><span class="ra-badge">{{ $item->status }}</span></td><td>{{ optional($item->event_date)->format('m/d/Y') }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ra-empty">No matching records for this period.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </article>
        <article class="ra-panel">
            <header class="ra-header"><div><h3>{{ $secondaryTitle }}</h3><p>Distribution within the selected period</p></div></header>
            <div class="ra-bars">
                @php $secondaryMax = max(1, (int) $secondaryStats->max('count')); @endphp
                @forelse($secondaryStats as $stat)
                    <div class="ra-bar-row"><span class="ra-bar-label">{{ $stat['label'] }}</span><span class="ra-bar-track"><span class="ra-bar-fill" style="display:block;width:{{ ($stat['count'] / $secondaryMax) * 100 }}%"></span></span><span class="ra-bar-count">{{ $stat['count'] }}</span></div>
                @empty<div class="ra-empty">No data available.</div>@endforelse
            </div>
        </article>
    </section>
</main>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trend = @json($trendStats->values());
    const chart = document.getElementById('roleTrendChart');
    if (chart && window.Chart) {
        new Chart(chart, { type:'line', data:{ labels:trend.map(i=>i.label), datasets:[
            {label:'Submitted',data:trend.map(i=>i.total),borderColor:'#1769e0',backgroundColor:'rgba(23,105,224,.12)',fill:true,tension:.35},
            {label:'{{ $mode === 'mis' ? 'Resolved' : 'Approved' }}',data:trend.map(i=>i.completed),borderColor:'#148a58',backgroundColor:'rgba(20,138,88,.08)',fill:false,tension:.35}
        ]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}} });
    }

    document.getElementById('roleExecutiveSummary')?.addEventListener('click', function () {
        const summary = @json($summary);
        Swal.fire({
            title: summary.title,
            width: 720,
            html: `<div class="role-summary-swal"><div class="summary-scope">${summary.scope}</div><ol>${summary.items.map(item => `<li>${item}</li>`).join('')}</ol></div>`,
            confirmButtonText: 'Close',
            confirmButtonColor: '#1769e0'
        });
    });
});
</script>
@endsection
