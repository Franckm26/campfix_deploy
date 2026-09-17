@extends('superadmin.layout')

@section('page_title', 'All Event Requests')

@section('content')

<div class="sa-card mb-4">
    <form method="GET" action="{{ route('superadmin.events') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:200px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Title, description…" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
        </div>
        <div style="min-width:150px">
            <label class="sa-label">Status</label>
            <select name="status" class="sa-input">
                <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>All</option>
                <option value="Pending"  {{ $status === 'Pending'  ? 'selected' : '' }}>Pending</option>
                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="Cancelled"{{ $status === 'Cancelled'? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('superadmin.events') }}" class="sa-btn sa-btn-ghost">Reset</a>
        </div>
        <button type="button" class="sa-btn sa-btn-primary" style="margin-left:auto" onclick="openNewRequestModal()">
            <i class="fas fa-plus"></i> New Request
        </button>
    </form>
</div>

<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        Showing {{ $events->firstItem() }}–{{ $events->lastItem() }} of {{ $events->total() }} event requests
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Event Ticket</th>
                    <th>Requested By</th>
                    <th>Department</th>
                    <th>Event Date</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                @php
                    $statusColors = [
                        'Pending'   => 'sa-badge-yellow',
                        'Approved'  => 'sa-badge-green',
                        'Rejected'  => 'sa-badge-red',
                        'Cancelled' => 'sa-badge-gray',
                    ];
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:500;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $event->title ?? 'Untitled' }}
                        </div>
                        @if($event->is_deleted)
                            <span class="sa-badge sa-badge-red" style="font-size:10px">Deleted</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $event->user->name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $event->department ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">
                        {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('m/d/Y') : '—' }}
                    </td>
                    <td><span class="sa-badge {{ $statusColors[$event->status] ?? 'sa-badge-gray' }}">{{ $event->status }}</span></td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $event->created_at->format('m/d/Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.events.force-delete', $event->id) }}"
                              onsubmit="return confirm('Permanently delete this event request? Cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Force Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--sa-muted);padding:32px">No event requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        {{ $events->links('vendor.pagination.superadmin') }}
    </div>
    @endif
</div>
@endsection




<!-- New Request Modal -->
<div class="modal fade" id="newRequestModal" tabindex="-1" aria-labelledby="newRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background: var(--sa-card); border: 1px solid var(--sa-border);">
            <div class="modal-header" style="background: var(--sa-accent); color: white; border-bottom: 1px solid var(--sa-border);">
                <h5 class="modal-title" id="newRequestModalLabel">
                    <i class="fas fa-plus me-2"></i>New Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 0; min-height: 500px;">
                <iframe id="requestCreateFrame" src="" style="width: 100%; height: 700px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
function openNewRequestModal() {
    const modal = new bootstrap.Modal(document.getElementById('newRequestModal'));
    document.getElementById('requestCreateFrame').src = '{{ route('events.create') }}';
    modal.show();
    
    // Reload page when modal is closed to show new request
    document.getElementById('newRequestModal').addEventListener('hidden.bs.modal', function () {
        location.reload();
    }, { once: true });
}
</script>
