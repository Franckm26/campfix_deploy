@extends('layouts.app')

@section('page_title')
<h2><i class="fas fa-trash"></i> Deleted Reports</h2>
<p>View and manage deleted reports</p>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-4">
        <div class="col-md-6">
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Controls -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.deleted') }}" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <label class="form-label">Show reports deleted for:</label>
                    <select name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="3" {{ $days == 3 ? 'selected' : '' }}>3 days</option>
                        <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 days</option>
                        <option value="15" {{ $days == 15 ? 'selected' : '' }}>15 days</option>
                        <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 days</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="severity" class="form-select form-select-sm">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('reports.deleted') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
                </div>
            </form>
        </div>
    </div>

    @if($reports->count() > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td>
                                        <a href="{{ route('reports.show', $report) }}" class="text-decoration-none">
                                            {{ $report->title }}
                                        </a>
                                    </td>
                                    <td>{{ $report->category->name ?? 'N/A' }}</td>
                                    <td>{{ $report->location }}</td>
                                    <td>
                                        <span class="badge bg-{{ $report->severity === 'critical' ? 'danger' : ($report->severity === 'high' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($report->severity) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $report->status === 'Resolved' ? 'success' : ($report->status === 'In Progress' ? 'info' : 'primary') }}">
                                            {{ $report->status }}
                                        </span>
                                    </td>
                                    <td>{{ $report->deletedBy->name ?? 'Unknown' }}</td>
                                    <td>{{ $report->deleted_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <form action="{{ route('reports.restore', $report) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to restore this report?')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-trash-restore"></i> Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($reports->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $reports->appends(request()->query())->links('pagination::bootstrap-4') }}
                </div>
                @endif
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center">
                <h4 class="text-muted">No deleted reports found</h4>
                <p class="text-muted">Reports that have been deleted for more than {{ $days }} days will be shown here.</p>
            </div>
        </div>
    @endif
</div>
@endsection