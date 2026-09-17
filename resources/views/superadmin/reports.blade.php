@extends('superadmin.layout')

@section('page_title', 'All Reports')

@section('content')

<div class="sa-card mb-4">
    <form method="GET" action="{{ route('superadmin.reports') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:200px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Title, description, location…" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
        </div>
        <div style="min-width:150px">
            <label class="sa-label">Status</label>
            <select name="status" class="sa-input">
                <option value="all"        {{ $status === 'all'        ? 'selected' : '' }}>All</option>
                <option value="Pending"    {{ $status === 'Pending'    ? 'selected' : '' }}>Pending</option>
                <option value="Assigned"   {{ $status === 'Assigned'   ? 'selected' : '' }}>Assigned</option>
                <option value="In Progress"{{ $status === 'In Progress'? 'selected' : '' }}>In Progress</option>
                <option value="Resolved"   {{ $status === 'Resolved'   ? 'selected' : '' }}>Resolved</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('superadmin.reports') }}" class="sa-btn sa-btn-ghost">Reset</a>
        </div>
        <button type="button" class="sa-btn sa-btn-primary" style="margin-left:auto" onclick="openCreateReportModal()">
            <i class="fas fa-plus"></i> Create Report
        </button>
    </form>
</div>

<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        Showing {{ $reports->firstItem() }}–{{ $reports->lastItem() }} of {{ $reports->total() }} reports
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Submitted By</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $report)
                @php
                    $statusColors = [
                        'Pending'     => 'sa-badge-yellow',
                        'Assigned'    => 'sa-badge-blue',
                        'In Progress' => 'sa-badge-blue',
                        'Resolved'    => 'sa-badge-green',
                    ];
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:500;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $report->title ?? 'Untitled' }}
                        </div>
                        @if($report->is_deleted)
                            <span class="sa-badge sa-badge-red" style="font-size:10px">Deleted</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $report->reported_by_name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $report->category->name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ Str::limit($report->location ?? '—', 30) }}</td>
                    <td><span class="sa-badge {{ $statusColors[$report->status] ?? 'sa-badge-gray' }}">{{ $report->status }}</span></td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $report->created_at->format('m/d/Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.reports.force-delete', $report->id) }}"
                              onsubmit="return confirm('Permanently delete this report? Cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Force Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--sa-muted);padding:32px">No reports found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        {{ $reports->links('vendor.pagination.superadmin') }}
    </div>
    @endif
</div>
@endsection


<!-- Create Report Modal -->
<div class="modal fade" id="createReportModal" tabindex="-1" aria-labelledby="createReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: var(--sa-card); border: 1px solid var(--sa-border);">
            <div class="modal-header" style="background: var(--sa-accent); color: white; border-bottom: 1px solid var(--sa-border);">
                <h5 class="modal-title" id="createReportModalLabel">
                    <i class="fas fa-plus me-2"></i>Create New Report
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 0; min-height: 500px;">
                <iframe id="reportCreateFrame" src="" style="width: 100%; height: 600px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function openCreateReportModal() {
    const modal = new bootstrap.Modal(document.getElementById('createReportModal'));
    document.getElementById('reportCreateFrame').src = '{{ route('reports.create') }}';
    modal.show();
    
    // Reload page when modal is closed to show new report
    document.getElementById('createReportModal').addEventListener('hidden.bs.modal', function () {
        location.reload();
    }, { once: true });
}
</script>
