@extends('superadmin.layout')

@section('page_title', 'All Concerns')

@section('content')

<div class="sa-card mb-4">
    <form method="GET" action="{{ route('superadmin.concerns') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:200px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Title, description, location…">
        </div>
        <div style="min-width:150px">
            <label class="sa-label">Status</label>
            <select name="status" class="sa-input">
                <option value="all"        {{ $status === 'all'        ? 'selected' : '' }}>All</option>
                <option value="Pending"    {{ $status === 'Pending'    ? 'selected' : '' }}>Pending</option>
                <option value="Assigned"   {{ $status === 'Assigned'   ? 'selected' : '' }}>Assigned</option>
                <option value="In Progress"{{ $status === 'In Progress'? 'selected' : '' }}>In Progress</option>
                <option value="Resolved"   {{ $status === 'Resolved'   ? 'selected' : '' }}>Resolved</option>
                <option value="Closed"     {{ $status === 'Closed'     ? 'selected' : '' }}>Closed</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ route('superadmin.concerns') }}" class="sa-btn sa-btn-ghost">Reset</a>
        </div>
    </form>
</div>

<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        Showing {{ $concerns->firstItem() }}–{{ $concerns->lastItem() }} of {{ $concerns->total() }} concerns
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
                    <th>Assigned To</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($concerns as $concern)
                @php
                    $statusColors = [
                        'Pending'     => 'sa-badge-yellow',
                        'Assigned'    => 'sa-badge-blue',
                        'In Progress' => 'sa-badge-blue',
                        'Resolved'    => 'sa-badge-green',
                        'Closed'      => 'sa-badge-gray',
                    ];
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:500;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $concern->title ?? 'Untitled' }}
                        </div>
                        @if($concern->is_deleted)
                            <span class="sa-badge sa-badge-red" style="font-size:10px">Deleted</span>
                        @elseif($concern->is_archived)
                            <span class="sa-badge sa-badge-yellow" style="font-size:10px">Archived</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $concern->user->name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $concern->category ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ Str::limit($concern->location ?? '—', 30) }}</td>
                    <td><span class="sa-badge {{ $statusColors[$concern->status] ?? 'sa-badge-gray' }}">{{ $concern->status }}</span></td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $concern->assignedTo->name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $concern->created_at->format('M d, Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.concerns.force-delete', $concern->id) }}"
                              onsubmit="return confirm('Permanently delete this concern? Cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Force Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;color:var(--sa-muted);padding:32px">No concerns found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($concerns->hasPages())
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        {{ $concerns->links('vendor.pagination.superadmin') }}
    </div>
    @endif
</div>
@endsection
