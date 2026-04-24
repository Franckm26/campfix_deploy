@extends('superadmin.layout')

@section('page_title', 'Superadmin Logs')

@section('content')

<div class="sa-alert sa-alert-info mb-4">
    <i class="fas fa-eye-slash me-2"></i>
    <strong>Hidden Logs:</strong> These logs are only visible to superadmins and track all superadmin actions. Regular admins cannot see these logs.
</div>

<div class="sa-card mb-4">
    <form method="GET" action="{{ route('superadmin.superadmin-logs') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:200px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Action, description…">
        </div>
        <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
        <a href="{{ route('superadmin.superadmin-logs') }}" class="sa-btn sa-btn-ghost">Reset</a>
    </form>
</div>

<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        {{ $logs->total() }} superadmin log entries
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Superadmin</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-size:12px">
                        @if($log->user)
                            <div style="font-weight:500">{{ $log->user->name }}</div>
                            <div style="color:var(--sa-muted)">{{ $log->user->email }}</div>
                        @else
                            <span style="color:var(--sa-muted)">Unknown</span>
                        @endif
                    </td>
                    <td>
                        <span class="sa-badge sa-badge-purple" style="font-size:11px">{{ $log->action }}</span>
                    </td>
                    <td style="color:var(--sa-text);font-size:12px;max-width:400px">
                        {{ $log->description }}
                        @if($log->old_values || $log->new_values)
                            <details style="margin-top:6px">
                                <summary style="cursor:pointer;color:var(--sa-accent2);font-size:11px">View Changes</summary>
                                <div style="background:rgba(0,0,0,.3);padding:8px;border-radius:6px;margin-top:6px;font-size:11px;font-family:monospace">
                                    @if($log->old_values)
                                        <div style="color:var(--sa-danger)">Old: {{ json_encode($log->old_values) }}</div>
                                    @endif
                                    @if($log->new_values)
                                        <div style="color:var(--sa-success)">New: {{ json_encode($log->new_values) }}</div>
                                    @endif
                                </div>
                            </details>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $log->ip_address ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px;white-space:nowrap">
                        {{ $log->created_at->format('M d, Y H:i:s') }}
                        <div style="font-size:10px;color:var(--sa-muted)">{{ $log->created_at->diffForHumans() }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:var(--sa-muted);padding:32px">No superadmin logs found.</td>
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
