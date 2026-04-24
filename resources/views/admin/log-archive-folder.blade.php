@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    .diff-old { color: #dc3545; text-decoration: line-through; }
    .diff-new { color: #198754; font-weight: 600; }
    .diff-field { font-weight: 600; color: #495057; }
    [data-theme="dark"] .diff-field { color: #aaa; }
</style>
@endsection

@section('page_title')
<h2><i class="fas fa-folder-open text-warning me-2"></i>{{ $folder->name }}</h2>
<p>{{ $folder->log_count }} archived logs &mdash; {{ $folder->description }}</p>
@endsection

@section('content')
<div class="container-fluid px-3">

    <div class="row mb-3 align-items-center">
        <div class="col">
        </div>
        <div class="col-auto d-flex gap-2">
            <form method="POST" action="{{ route('admin.logs.folder.delete', $folder->id) }}"
                  onsubmit="return confirm('Permanently delete this folder and all {{ $folder->log_count }} logs inside?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash me-1"></i> Delete Folder
                </button>
            </form>
            <a href="{{ route('admin.logs', ['view' => 'archived']) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Folders
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:13px">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:14%">Action</th>
                            <th>Description</th>
                            <th style="width:16%">Performed By</th>
                            <th style="width:12%">IP Address</th>
                            <th style="width:14%">Date / Time</th>
                            <th style="width:6%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php
                                $changes = $log->changes ?? [];
                                $badgeColor = str_contains($log->action, 'created') || str_contains($log->action, 'imported') ? 'success' :
                                             (str_contains($log->action, 'deleted') || str_contains($log->action, 'permanent') ? 'danger' :
                                             (str_contains($log->action, 'updated') || str_contains($log->action, 'restored') ? 'warning' :
                                             (str_contains($log->action, 'archived') ? 'secondary' : 'info')));
                            @endphp
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $badgeColor }}" style="font-size:11px">
                                        {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $log->description }}</div>
                                    @if(!empty($changes))
                                        <div class="mt-1" style="font-size:11px">
                                            @foreach($changes as $field => $diff)
                                                @if($field === 'is_admin') @continue @endif
                                                <span class="diff-field">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                <span class="diff-old">{{ $diff['old'] ?? '—' }}</span>
                                                <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:9px"></i>
                                                <span class="diff-new">{{ $diff['new'] ?? '—' }}</span>
                                                @if(!$loop->last) &nbsp;&bull;&nbsp; @endif
                                            @endforeach
                                        </div>
                                    @elseif($log->new_values && !$log->old_values)
                                        <div class="mt-1 text-muted" style="font-size:11px">
                                            @foreach($log->new_values as $field => $value)
                                                <span class="diff-field">{{ ucfirst(str_replace('_', ' ', $field)) }}:</span>
                                                <span>{{ $value ?? '—' }}</span>
                                                @if(!$loop->last) &nbsp;&bull;&nbsp; @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $log->user->name ?? 'System' }}</div>
                                    @if($log->user)
                                        <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $log->user->role ?? '')) }}</small>
                                    @endif
                                </td>
                                <td class="text-muted font-monospace" style="font-size:11px">{{ $log->ip_address ?? '—' }}</td>
                                <td class="text-muted">
                                    {{ $log->created_at->format('M d, Y') }}<br>
                                    <small>{{ $log->created_at->format('h:i:s A') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <form method="POST" action="{{ route('admin.logs.restore', $log->id) }}"
                                              onsubmit="return confirm('Restore this log entry back to active logs?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.logs.delete', $log->id) }}"
                                              onsubmit="return confirm('Delete this log entry?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No logs in this folder</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                <small class="text-muted">Showing {{ $logs->firstItem() ?? 0 }} – {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries</small>
                {{ $logs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
