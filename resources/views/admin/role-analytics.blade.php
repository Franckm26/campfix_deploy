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
    .ra-executive { display:grid; grid-template-columns:minmax(0,1.8fr) minmax(280px,1fr); gap:18px; padding:20px; }
    .ra-eyebrow { margin-bottom:6px; color:#1769e0; font-size:11px; font-weight:850; letter-spacing:.08em; text-transform:uppercase; }
    .ra-executive h3 { margin:0 0 7px; color:var(--ra-ink); font-size:22px; }
    .ra-executive p { margin:0; color:#607188; line-height:1.55; }
    .ra-insights { display:grid; gap:8px; margin:16px 0 0; padding:0; list-style:none; }
    .ra-insights li { display:flex; gap:9px; color:#314967; font-size:13px; }
    .ra-insights i { margin-top:4px; color:#148a58; }
    .ra-decision { align-self:stretch; padding:17px; border:1px solid #cfe0fa; border-radius:9px; background:#f3f7fe; }
    .ra-decision strong { display:block; margin-bottom:6px; color:#164f9e; }
    .ra-decision p { color:#304e75; font-size:13px; }
    .ra-alerts { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; padding:18px 20px; }
    .ra-alert { padding:15px; border:1px solid var(--ra-border); border-left:5px solid var(--alert); border-radius:8px; background:#fff; text-align:left; transition:.16s ease; }
    .ra-alert:hover { transform:translateY(-1px); box-shadow:0 5px 16px rgba(17,43,77,.09); }
    .ra-alert strong { display:block; margin-bottom:5px; color:var(--ra-ink); }
    .ra-alert span { display:block; color:#64758b; font-size:12px; line-height:1.45; }
    .ra-alert-critical { --alert:#dc3545; }.ra-alert-warning { --alert:#e99a00; }.ra-alert-info { --alert:#1769e0; }.ra-alert-success { --alert:#148a58; }
    .ra-operations { display:grid; grid-template-columns:270px minmax(0,1fr); min-height:610px; }
    .ra-operation-nav { padding:14px; border-right:1px solid var(--ra-border); background:#f8fafc; }
    .ra-operation-nav button { width:100%; margin-bottom:8px; padding:12px; border:1px solid transparent; border-radius:8px; color:#314967; background:transparent; text-align:left; }
    .ra-operation-nav button.active { border-color:#b9d2f7; color:#125bbc; background:#eaf2ff; }
    .ra-operation-nav strong,.ra-operation-nav small { display:block; }
    .ra-operation-nav small { margin-top:3px; color:#718096; }
    .ra-operation-body { min-width:0; padding:20px; }
    .ra-operation-top { display:flex; align-items:flex-start; justify-content:space-between; gap:15px; margin-bottom:16px; }
    .ra-operation-top h4 { margin:0 0 4px; color:var(--ra-ink); font-size:21px; }
    .ra-operation-top p { margin:0; color:#6c7c91; font-size:13px; }
    .ra-operation-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:10px; margin-bottom:18px; }
    .ra-operation-stat { padding:12px; border:1px solid var(--ra-border); border-radius:8px; background:#fbfcfe; }
    .ra-operation-stat span { display:block; color:#75849a; font-size:11px; font-weight:750; text-transform:uppercase; }
    .ra-operation-stat strong { display:block; margin-top:3px; color:var(--ra-ink); font-size:23px; }
    .ra-operation-grid { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(310px,.8fr); gap:16px; }
    .ra-operation-chart { position:relative; height:245px; padding:12px; border:1px solid var(--ra-border); border-radius:8px; }
    .ra-ticket-pane { min-width:0; border:1px solid var(--ra-border); border-radius:8px; overflow:hidden; }
    .ra-ticket-tools { display:flex; gap:8px; padding:10px; border-bottom:1px solid var(--ra-border); background:#f8fafc; }
    .ra-ticket-tools input,.ra-ticket-tools select { min-width:0; font-size:12px; }
    .ra-ticket-list { max-height:315px; overflow:auto; }
    .ra-ticket { width:100%; padding:12px; border:0; border-bottom:1px solid var(--ra-border); background:#fff; text-align:left; }
    .ra-ticket.active { background:#edf5ff; box-shadow:inset 4px 0 #1769e0; }
    .ra-ticket-head { display:flex; justify-content:space-between; gap:8px; color:#1e3a5c; font-weight:750; }
    .ra-ticket-meta { margin-top:5px; color:#718096; font-size:11px; }
    .ra-ticket-detail { grid-column:1/-1; padding:16px; border:1px solid var(--ra-border); border-radius:8px; background:#fbfcfe; }
    .ra-detail-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; margin:13px 0; }
    .ra-detail-grid span { display:block; color:#7a899d; font-size:10px; font-weight:750; text-transform:uppercase; }
    .ra-detail-grid strong { display:block; margin-top:3px; color:#29425f; overflow-wrap:anywhere; }
    .ra-detail-description { margin:0 0 14px; color:#53667e; line-height:1.5; white-space:pre-wrap; }
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
    @media (max-width:1100px) { .ra-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.ra-grid,.ra-executive{grid-template-columns:1fr}.ra-alerts{grid-template-columns:repeat(2,minmax(0,1fr))}.ra-operations{grid-template-columns:220px minmax(0,1fr)}.ra-operation-grid{grid-template-columns:1fr}.ra-summary-button{margin-left:0} }
    @media (max-width:760px) { .ra-operations{grid-template-columns:1fr}.ra-operation-nav{display:flex; gap:8px; overflow:auto; border-right:0;border-bottom:1px solid var(--ra-border)}.ra-operation-nav button{min-width:190px;margin:0}.ra-operation-stats,.ra-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ra-alerts{grid-template-columns:1fr} }
    @media (max-width:640px) { .role-analytics{padding:12px}.ra-kpis{grid-template-columns:1fr}.ra-filter>*{width:100%}.ra-filter .form-control,.ra-filter .btn{width:100%}.ra-chart{height:275px}.ra-bar-row{grid-template-columns:105px 1fr 36px}.ra-operation-body{padding:12px}.ra-ticket-tools{flex-direction:column} }
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

    <section class="ra-panel ra-executive">
        <div>
            <div class="ra-eyebrow">Executive brief</div>
            <h3>{{ $summary['title'] }}</h3>
            <p>{{ $summary['scope'] }}</p>
            <ul class="ra-insights">
                @foreach(array_slice($summary['items'], 0, 3) as $item)
                    <li><i class="fas fa-circle-check"></i><span>{{ $item }}</span></li>
                @endforeach
            </ul>
        </div>
        <aside class="ra-decision">
            <strong><i class="fas fa-compass me-1"></i> Recommended decision</strong>
            <p>{{ $summary['decision'] }}</p>
            <button class="btn btn-sm btn-primary mt-3" id="roleExecutiveSummaryInline" type="button">View complete summary</button>
        </aside>
    </section>

    <section class="ra-panel">
        <header class="ra-header"><div><h3>Decision Alerts</h3><p>Exceptions that need attention, with evidence and recommended next steps</p></div></header>
        <div class="ra-alerts">
            @foreach($decisionAlerts as $index => $alert)
                <button class="ra-alert ra-alert-{{ $alert['level'] }}" type="button" data-alert-index="{{ $index }}">
                    <strong>{{ $alert['title'] }}</strong><span>{{ $alert['body'] }}</span>
                </button>
            @endforeach
        </div>
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

    <section class="ra-panel">
        <header class="ra-header">
            <div><h3>Operations Analytics</h3><p>Select an operation, inspect its trend and tickets, then manage the {{ $mode === 'mis' ? 'maintenance' : 'approval' }} workflow.</p></div>
        </header>
        <div class="ra-operations">
            <nav class="ra-operation-nav" id="roleOperationNav" aria-label="Analytics operations"></nav>
            <div class="ra-operation-body" id="roleOperationBody"></div>
        </div>
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
    const operations = @json($operations->values());
    const decisionAlerts = @json($decisionAlerts->values());
    const mode = @json($mode);
    let activeOperationIndex = 0;
    let activeTicketId = null;
    let operationChart = null;

    const escapeHtml = value => String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
    const labelize = value => String(value || 'Not set').replaceAll('_', ' ').replace(/\b\w/g, character => character.toUpperCase());
    const chart = document.getElementById('roleTrendChart');
    if (chart && window.Chart) {
        new Chart(chart, { type:'line', data:{ labels:trend.map(i=>i.label), datasets:[
            {label:'Submitted',data:trend.map(i=>i.total),borderColor:'#1769e0',backgroundColor:'rgba(23,105,224,.12)',fill:true,tension:.35},
            {label:'{{ $mode === 'mis' ? 'Resolved' : 'Approved' }}',data:trend.map(i=>i.completed),borderColor:'#148a58',backgroundColor:'rgba(20,138,88,.08)',fill:false,tension:.35}
        ]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}} });
    }

    function showExecutiveSummary() {
        const summary = @json($summary);
        Swal.fire({
            title: summary.title,
            width: 720,
            html: `<div class="role-summary-swal"><div class="summary-scope">${escapeHtml(summary.scope)}</div><ol>${summary.items.map(item => `<li>${escapeHtml(item)}</li>`).join('')}</ol><div class="ra-decision"><strong>Recommended decision</strong><p>${escapeHtml(summary.decision)}</p></div></div>`,
            confirmButtonText: 'Close',
            confirmButtonColor: '#1769e0'
        });
    }
    document.getElementById('roleExecutiveSummary')?.addEventListener('click', showExecutiveSummary);
    document.getElementById('roleExecutiveSummaryInline')?.addEventListener('click', showExecutiveSummary);

    function renderOperationNavigation() {
        const navigation = document.getElementById('roleOperationNav');
        if (!navigation) return;
        if (!operations.length) {
            navigation.innerHTML = '<div class="ra-empty">No operations available.</div>';
            return;
        }
        navigation.innerHTML = operations.map((operation, index) => `
            <button type="button" class="${index === activeOperationIndex ? 'active' : ''}" data-operation-index="${index}">
                <strong>${escapeHtml(operation.name)}</strong>
                <small>${operation.stats.open} open · ${operation.stats.total} total</small>
            </button>`).join('');
        navigation.querySelectorAll('[data-operation-index]').forEach(button => button.addEventListener('click', function () {
            activeOperationIndex = Number(this.dataset.operationIndex);
            activeTicketId = null;
            renderOperationNavigation();
            renderOperation();
        }));
    }

    function renderOperation() {
        const body = document.getElementById('roleOperationBody');
        if (!body) return;
        if (!operations.length) {
            body.innerHTML = '<div class="ra-empty">No matching records exist in the selected period.</div>';
            return;
        }
        const operation = operations[activeOperationIndex];
        activeTicketId ??= operation.tickets[0]?.id ?? null;
        body.innerHTML = `
            <div class="ra-operation-top"><div><h4>${escapeHtml(operation.name)}</h4><p>${escapeHtml(operation.description)}</p></div><span class="ra-badge">${operation.stats.open} open</span></div>
            <div class="ra-operation-stats">
                <div class="ra-operation-stat"><span>Total</span><strong>${operation.stats.total}</strong></div>
                <div class="ra-operation-stat"><span>${mode === 'mis' ? 'Open' : 'Pending'}</span><strong>${operation.stats.open}</strong></div>
                <div class="ra-operation-stat"><span>${mode === 'mis' ? 'Resolved' : 'Approved'}</span><strong>${operation.stats.completed}</strong></div>
                <div class="ra-operation-stat"><span>${mode === 'mis' ? 'High priority' : 'Rejected'}</span><strong>${operation.stats.urgent}</strong></div>
            </div>
            <div class="ra-operation-grid">
                <div class="ra-operation-chart"><canvas id="roleOperationChart"></canvas></div>
                <div class="ra-ticket-pane">
                    <div class="ra-ticket-tools"><input class="form-control" id="roleTicketSearch" type="search" placeholder="Search tickets"><select class="form-select" id="roleTicketStatus"><option value="">All statuses</option>${[...new Set(operation.tickets.map(ticket => ticket.status))].map(status => `<option value="${escapeHtml(status)}">${escapeHtml(status)}</option>`).join('')}</select></div>
                    <div class="ra-ticket-list" id="roleTicketList"></div>
                </div>
                <div class="ra-ticket-detail" id="roleTicketDetail"></div>
            </div>`;
        renderOperationChart(operation);
        renderTickets(operation);
        document.getElementById('roleTicketSearch')?.addEventListener('input', () => renderTickets(operation));
        document.getElementById('roleTicketStatus')?.addEventListener('change', () => renderTickets(operation));
    }

    function renderOperationChart(operation) {
        operationChart?.destroy();
        const canvas = document.getElementById('roleOperationChart');
        if (!canvas || !window.Chart) return;
        operationChart = new Chart(canvas, { type:'line', data:{ labels:operation.trend.map(item => item.label), datasets:[
            {label:'Submitted',data:operation.trend.map(item => item.total),borderColor:'#1769e0',backgroundColor:'rgba(23,105,224,.1)',fill:true,tension:.35},
            {label:mode === 'mis' ? 'Resolved' : 'Approved',data:operation.trend.map(item => item.completed),borderColor:'#148a58',tension:.35}
        ]}, options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'top'}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}} });
    }

    function renderTickets(operation) {
        const list = document.getElementById('roleTicketList');
        if (!list) return;
        const search = document.getElementById('roleTicketSearch')?.value.trim().toLowerCase() || '';
        const status = document.getElementById('roleTicketStatus')?.value || '';
        const tickets = operation.tickets.filter(ticket => (!status || ticket.status === status) && (!search || `${ticket.reference} ${ticket.title} ${ticket.location} ${ticket.requester}`.toLowerCase().includes(search)));
        if (tickets.length && !tickets.some(ticket => Number(ticket.id) === Number(activeTicketId))) activeTicketId = tickets[0].id;
        list.innerHTML = tickets.length ? tickets.map(ticket => `
            <button type="button" class="ra-ticket ${Number(ticket.id) === Number(activeTicketId) ? 'active' : ''}" data-ticket-id="${ticket.id}">
                <span class="ra-ticket-head"><span>${escapeHtml(ticket.reference)}</span><span class="ra-badge">${escapeHtml(ticket.status)}</span></span>
                <span class="d-block mt-1">${escapeHtml(ticket.title)}</span>
                <span class="ra-ticket-meta">${escapeHtml(ticket.assignee)} · ${ticket.age_days} day(s) old</span>
            </button>`).join('') : '<div class="ra-empty">No tickets match the filters.</div>';
        list.querySelectorAll('[data-ticket-id]').forEach(button => button.addEventListener('click', function () {
            activeTicketId = Number(this.dataset.ticketId);
            renderTickets(operation);
        }));
        renderTicketDetail(tickets.find(ticket => Number(ticket.id) === Number(activeTicketId)) || null);
    }

    function renderTicketDetail(ticket) {
        const detail = document.getElementById('roleTicketDetail');
        if (!detail) return;
        if (!ticket) {
            detail.innerHTML = '<div class="ra-empty">Select a ticket to inspect its workflow details.</div>';
            return;
        }
        detail.innerHTML = `
            <div class="ra-ticket-head"><strong>${escapeHtml(ticket.reference)} · ${escapeHtml(ticket.title)}</strong><span class="ra-badge">${escapeHtml(ticket.status)}</span></div>
            <div class="ra-detail-grid">
                <div><span>Requester</span><strong>${escapeHtml(ticket.requester)}</strong></div>
                <div><span>Location</span><strong>${escapeHtml(ticket.location)}</strong></div>
                <div><span>${mode === 'mis' ? 'Assigned to' : 'Current approver'}</span><strong>${escapeHtml(ticket.assignee)}</strong></div>
                <div><span>Priority</span><strong>${escapeHtml(labelize(ticket.priority))}</strong></div>
            </div>
            <p class="ra-detail-description">${escapeHtml(ticket.description)}</p>
            <a class="btn btn-primary btn-sm" href="${escapeHtml(ticket.url)}"><i class="fas fa-arrow-up-right-from-square me-1"></i> Open ${mode === 'mis' ? 'MIS Task' : 'Event Request'} Workflow</a>`;
    }

    document.querySelectorAll('[data-alert-index]').forEach(button => button.addEventListener('click', function () {
        const alert = decisionAlerts[Number(this.dataset.alertIndex)];
        const tickets = alert.tickets.length
            ? `<div style="margin-top:15px"><strong>Related records</strong>${alert.tickets.map(ticket => `<a href="${escapeHtml(ticket.url)}" style="display:block;margin-top:8px;padding:9px;border:1px solid #dce3ed;border-radius:6px;text-decoration:none"><b>${escapeHtml(ticket.reference)}</b> · ${escapeHtml(ticket.title)}<br><small>${escapeHtml(ticket.status)} · ${escapeHtml(ticket.owner)}</small></a>`).join('')}</div>`
            : '';
        Swal.fire({
            icon: alert.level === 'critical' ? 'error' : alert.level,
            title: escapeHtml(alert.title), width: 700,
            html: `<div class="role-summary-swal"><p>${escapeHtml(alert.body)}</p><p><b>Why it matters:</b> ${escapeHtml(alert.why)}</p><p><b>Recommended action:</b> ${escapeHtml(alert.impact)}</p>${tickets}</div>`,
            confirmButtonText: 'Close', confirmButtonColor: '#1769e0'
        });
    }));

    renderOperationNavigation();
    renderOperation();
});
</script>
@endsection
