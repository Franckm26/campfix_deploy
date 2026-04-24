@extends('superadmin.layout')

@section('page_title', 'Activity Logs')

@section('content')

<div class="sa-card mb-4" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
    <form method="GET" action="{{ route('superadmin.activity-logs') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;flex:1">
        <div style="flex:1;min-width:200px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Action, description…">
        </div>
        <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
        <a href="{{ route('superadmin.activity-logs') }}" class="sa-btn sa-btn-ghost">Reset</a>
    </form>
    <form method="POST" action="{{ route('superadmin.activity-logs.clear') }}"
          onsubmit="return confirm('Clear ALL activity logs? This cannot be undone.')">
        @csrf @method('DELETE')
        <button type="submit" class="sa-btn sa-btn-danger">
            <i class="fas fa-trash-can"></i> Clear All Logs
        </button>
    </form>
</div>

<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        {{ $logs->total() }} total log entries
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-size:12px">
                        @if($log->user)
                            <div style="font-weight:500">{{ $log->user->name }}</div>
                            <div style="color:var(--sa-muted)">{{ str_replace('_',' ',ucfirst($log->user->role ?? '')) }}</div>
                        @else
                            <span style="color:var(--sa-muted)">System</span>
                        @endif
                    </td>
                    <td>
                        <span class="sa-badge sa-badge-gray" style="font-size:11px">{{ $log->action }}</span>
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px;max-width:300px">
                        {{ Str::limit($log->description, 80) }}
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $log->ip_address ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px;white-space:nowrap">
                        {{ $log->created_at->format('M d, Y H:i') }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('superadmin.activity-logs.delete', $log->id) }}"
                              onsubmit="return confirm('Delete this log entry?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--sa-muted);padding:32px">No logs found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        {{ $logs->links('vendor.pagination.superadmin') }}
    </div>
    @endif
</div>
@endsection
