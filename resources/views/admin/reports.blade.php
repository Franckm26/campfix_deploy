@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@if(($viewType ?? '') == 'analytics')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endif
@endsection

@section('page_title')
<h2>Reports</h2>
@endsection

@section('content')
<div class="container-fluid px-3">
    
    <!-- Context Menu -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li><a href="#" id="ctxView" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
        </ul>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewConcernModal" tabindex="-1" aria-labelledby="viewConcernModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewConcernModalLabel">Concern Details</h5>
                    @if(in_array(auth()->user()->role, ['building_admin', 'school_admin', 'academic_head']))
                        <button type="button" class="btn btn-primary btn-sm ms-2" onclick="assignReport(window.currentReportId)">
                            <i class="fas fa-user-plus"></i> Assign
                        </button>
                    @endif
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewConcernContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Assign Modal -->
    <div class="modal fade" id="assignConcernModal" tabindex="-1" aria-labelledby="assignConcernModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignConcernModalLabel">Assign Concern</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignConcernForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="assignConcernId" name="concern_id" value="">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign to Maintenance Staff</label>
                            <select class="form-select" id="assigned_to" name="assigned_to" required>
                                <option value="">Select maintenance staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editConcernModal" tabindex="-1" aria-labelledby="editConcernModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editConcernModalLabel">Edit Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editConcernForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-body" id="editConcernContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal fade" id="reportArchiveModal" tabindex="-1" aria-labelledby="reportArchiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportArchiveModalLabel">Archive Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to archive this report?</p>
                    <p class="text-muted">You can restore it later from the archive.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmReportArchive()">Archive</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="reportDeleteModal" tabindex="-1" aria-labelledby="reportDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="reportDeleteModalLabel"><i class="fas fa-exclamation-triangle"></i> Delete Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this report?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> This action will move the report to deleted. You can restore it later from the Deleted tab.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmReportDeleteButton" class="btn btn-danger" onclick="confirmReportDelete()"><i class="fas fa-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <ul class="nav nav-pills mb-0 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link {{ ($viewType ?? 'active') == 'active' ? 'active' : '' }}" href="{{ route('admin.reports', ['view' => 'active']) }}">
                            <i class="fas fa-clipboard-list"></i> Active Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ ($viewType ?? '') == 'resolved' ? 'active' : '' }}" href="{{ route('admin.reports', ['view' => 'resolved']) }}" style="color: #28a745;">
                            <i class="fas fa-check-circle"></i> Resolved Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ ($viewType ?? '') == 'archives' ? 'active' : '' }}" href="{{ route('admin.reports', ['view' => 'archives']) }}">
                            <i class="fas fa-archive"></i> Archived Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ ($viewType ?? '') == 'deleted' ? 'active' : '' }}" href="{{ route('admin.reports', ['view' => 'deleted']) }}" style="color: #dc3545;">
                            <i class="fas fa-trash-alt"></i> Deleted Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ ($viewType ?? '') == 'analytics' ? 'active' : '' }}" href="{{ route('admin.reports', ['view' => 'analytics']) }}" style="color: #17a2b8;">
                            <i class="fas fa-chart-line"></i> Analytics
                        </a>
                    </li>
                </ul>
                <a href="{{ route('admin.export') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
            <form method="GET" action="{{ route('admin.reports') }}">
                <input type="hidden" name="view" value="{{ $viewType ?? 'active' }}">
                <div class="row g-2">
                    <div class="col-6 col-md">
                        <select name="archived" class="form-select form-select-sm">
                            <option value="">Active Concerns</option>
                            <option value="1" {{ request('archived') == '1' ? 'selected' : '' }}>Archived</option>
                            <option value="all" {{ request('archived') == 'all' ? 'selected' : '' }}>All Concerns</option>
                        </select>
                    </div>
                    <div class="col-6 col-md">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <div class="col-6 col-md">
                        <select name="priority" class="form-select form-select-sm">
                            <option value="">All Priority</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="col-6 col-md">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search concerns..."
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('admin.reports') }}" class="btn btn-secondary btn-sm ms-1"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(($viewType ?? 'active') == 'active')
    <!-- Summary Cards -->
    <div class="row mb-4" style="display: flex !important;">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total</h5>
                    <h2>{{ $reports->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h2>{{ $reports->where('status', 'Pending')->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Resolved</h5>
                    <h2>{{ $reports->where('status', 'Resolved')->count() }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Critical</h5>
                    <h2>{{ $reports->where('severity', 'critical')->count() }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">
            <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                <table class="table table-hover" style="display: table !important;">
                    <thead>
                        <tr>
                            <th class="checkbox-col"><input type="checkbox" id="selectAllReports" onclick="toggleAllReports(this)"></th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Created</th>
                            <th>Resolved</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr data-id="{{ $report->id }}">
                                <td class="checkbox-col"><input type="checkbox" class="report-checkbox" value="{{ $report->id }}"></td>
                                <td>{{ $report->title ?? 'No Title' }}</td>
                                        <td>{{ $report->category->name ?? 'N/A' }}</td>
                                        <td>{{ $report->location }}</td>
                                        <td>
                                            <span class="badge bg-{{
                                                $report->severity == 'critical' ? 'danger' :
                                                ($report->severity == 'high' ? 'warning' :
                                                ($report->severity == 'medium' ? 'info' : 'secondary'))
                                            }}">
                                                {{ ucfirst($report->severity) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{
                                                $report->status == 'Resolved' ? 'success' :
                                                ($report->status == 'In Progress' ? 'warning' :
                                                ($report->status == 'Assigned' ? 'primary' : 'secondary'))
                                            }}">
                                                {{ $report->status }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $report->user->name ?? 'Unknown' }}
                                        </td>
                                        <td>{{ $report->created_at->format('M d, Y') }}</td>
                                        <td>{{ $report->resolved_at ? $report->resolved_at->format('M d, Y g:i A') : '-' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" onclick="viewReport({{ $report->id }})" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="assignReport({{ $report->id }})" title="Assign">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                                @if(!$report->isArchivedByUser(auth()->id()))
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="showReportArchiveModal({{ $report->id }})" title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                @else
                                                    <form method="POST" action="{{ route('reports.restore', $report) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if(!$report->assigned_to || $report->status === 'Resolved')
                                                <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal({{ $report->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete assigned reports until resolved">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No reports found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if(($viewType ?? '') == 'resolved')
    <!-- Resolved Reports Section -->
    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">
            <h5 class="card-title mb-3">
                <i class="fas fa-check-circle text-success"></i> Resolved Reports
                @if(isset($resolvedReports))
                    <span class="badge bg-success ms-2">{{ $resolvedReports->count() }}</span>
                @endif
            </h5>
            
            @if(isset($resolvedReports) && $resolvedReports->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Reported By</th>
                                <th>Created</th>
                                <th>Resolved</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resolvedReports as $report)
                                <tr data-id="{{ $report->id }}">
                                    <td>{{ $report->title ?? 'No Title' }}</td>
                                    <td>{{ $report->category->name ?? 'N/A' }}</td>
                                    <td>{{ $report->location }}</td>
                                    <td>
                                        <span class="badge bg-{{
                                            $report->severity == 'critical' ? 'danger' :
                                            ($report->severity == 'high' ? 'warning' :
                                            ($report->severity == 'medium' ? 'info' : 'secondary'))
                                        }}">
                                            {{ ucfirst($report->severity) }}
                                        </span>
                                    </td>
                                    <td>{{ $report->user->name ?? 'Unknown' }}</td>
                                    <td>{{ $report->created_at->format('M d, Y') }}</td>
                                    <td>{{ $report->resolved_at ? $report->resolved_at->format('M d, Y g:i A') : '-' }}</td>
                                    <td>₱{{ number_format($report->cost ?? 0, 2) }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info" onclick="viewReport({{ $report->id }})" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="showReportArchiveModal({{ $report->id }})" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            @if(!$report->assigned_to || $report->status === 'Resolved')
                                            <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal({{ $report->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete assigned reports until resolved">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No resolved reports found</h4>
                    <p class="text-muted">Resolved reports will appear here once maintenance staff completes their work.</p>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if(($viewType ?? '') == 'archives')
    <!-- Archived Concerns Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-archive"></i> Archived Reports</h5>
                </div>
                <div class="card-body">
                    @if(isset($archivedConcerns) && $archivedConcerns->count() > 0)
                        <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                            <table class="table table-hover" style="display: table !important;">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Reported By</th>
                                        <th>Created</th>
                                        <th>Resolved</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($archivedConcerns as $concern)
                                        <tr data-id="{{ $concern->id }}">
                                            <td>{{ $concern->title ?? 'No Title' }}</td>
                                            <td>{{ $concern->categoryRelation->name ?? 'N/A' }}</td>
                                            <td>{{ $concern->location }}</td>
                                            <td>
                                                <span class="badge bg-{{
                                                    $concern->priority == 'urgent' ? 'danger' :
                                                    ($concern->priority == 'high' ? 'warning' :
                                                    ($concern->priority == 'medium' ? 'info' : 'secondary'))
                                                }}">
                                                    {{ ucfirst($concern->priority) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{
                                                    $concern->status == 'Resolved' ? 'success' :
                                                    ($concern->status == 'In Progress' ? 'warning' :
                                                    ($concern->status == 'Assigned' ? 'primary' : 'secondary'))
                                                }}">
                                                    {{ $concern->status }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $concern->is_anonymous ? 'Anonymous' : ($concern->user->name ?? 'Unknown') }}
                                            </td>
                                            <td>{{ $concern->created_at->format('M d, Y') }}</td>
                                            <td>{{ $concern->resolved_at ? $concern->resolved_at->format('M d, Y') : '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewConcern({{ $concern->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if(in_array(auth()->user()->role, ['mis', 'school_admin', 'building_admin']))
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="editConcern({{ $concern->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.archive.restore') }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="type" value="report">
                                                        <input type="hidden" name="id" value="{{ $concern->id }}">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal({{ $concern->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-archive"></i> No archived reports found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(($viewType ?? '') == 'deleted')
    <!-- Deleted Concerns Section -->
    <div id="deletedReportsContextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" onclick="deletedReportsContextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" onclick="deletedReportsContextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" onclick="deletedReportsContextPermanentDelete()"><i class="fas fa-times-circle"></i> Permanently Delete</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-trash-alt"></i> Deleted Reports</h2>
            <p class="text-muted">Reports in this folder can be restored or permanently deleted.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Info + Auto-filter Card -->
    <div class="card mb-4 border-warning">
        <div class="card-body bg-warning bg-opacity-10 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong><i class="fas fa-info-circle"></i> About Deleted Reports</strong>
                    <p class="mb-0 text-muted small">Reports deleted are moved here. Restore or permanently delete them.</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-warning fs-6">{{ $deletedReports->count() ?? 0 }} reports</span>
                    <select id="retentionDays" class="form-select form-select-sm" style="width:120px;">
                        <option value="3" {{ ($days ?? 15) == 3 ? 'selected' : '' }}>3 days</option>
                        <option value="7" {{ ($days ?? 15) == 7 ? 'selected' : '' }}>7 days</option>
                        <option value="15" {{ ($days ?? 15) == 15 ? 'selected' : '' }}>15 days</option>
                        <option value="30" {{ ($days ?? 15) == 30 ? 'selected' : '' }}>30 days</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(($deletedReports->count() ?? 0) > 0)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form id="deletedReportsBulkRestoreForm" method="POST" action="{{ route('concerns.batchRestoreDeleted') }}">
                        @csrf
                        <div id="selectedDeletedReportIdsContainer"></div>
                        <button type="button" class="btn btn-success" onclick="deletedReportsBulkRestore()">
                            <i class="fas fa-trash-restore"></i> Restore Selected
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    {{-- <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletedReportsPermanentDeleteAllModal">
                        <i class="fas fa-times-circle"></i> Permanently Delete All
                    </button> --}}
                    <span id="deletedReportsSelectedCount" class="text-muted ms-3">0 concerns selected</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Deleted Reports Table -->
    <div class="card">
        <div class="card-body">
            @if(isset($deletedReports) && $deletedReports->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;" id="deletedReportsTable">
                        <thead>
                            <tr>
                                <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="deletedReportsSelectAll" onchange="deletedReportsToggleSelectAll()"></th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Reported By</th>
                                <th>Deleted Date</th>
                                <th>Deleted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deletedReports as $report)
                                <tr data-id="{{ $report->id }}">
                                    <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-report-checkbox" value="{{ $report->id }}" onchange="deletedReportsUpdateSelectedCount()"></td>
                                    <td>Report #{{ $report->id }}</td>
                                    <td>{{ $report->categoryRelation ? $report->categoryRelation->name : 'N/A' }}</td>
                                    <td>{{ $report->location }}</td>
                                    <td>
                                        @php
                                            $severityClass = match($report->priority) {
                                                'low' => 'success',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'urgent' => 'dark',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $severityClass }}">
                                            {{ ucfirst($report->priority ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($report->status) {
                                                'pending' => 'warning',
                                                'in_progress' => 'info',
                                                'resolved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $report->user ? $report->user->name : 'Unknown' }}</td>
                                    <td>{{ $report->updated_at->format('M d, Y h:i A') }}</td>
                                    <td>{{ $report->user ? $report->user->name : 'System' }}</td>
                                    <td>
                                        <div class="action-icons">
                                            <form action="{{ route('admin.deletedReports.restore', $report->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.deletedReports.permanentDelete', $report->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="return confirm('Are you sure you want to permanently delete this report?')">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Permanent Delete Confirmation Modal -->
                                <div class="modal fade" id="deletedReportsPermanentDeleteModal{{ $report->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Permanently Delete Concern</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to permanently delete <strong>Report #{{ $report->id }}</strong>?</p>
                                                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. The concern will be permanently removed from the system.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('admin.deletedReports.permanentDelete', $report->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Permanently Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center p-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-check-circle fa-2x d-block mb-3"></i>
                                            <h5>No Deleted Concerns</h5>
                                            <p class="mb-0">Deleted concerns will appear here. You can delete concerns from the Reports page.</p>
                                            <a href="{{ route('admin.reports') }}" class="btn btn-primary mt-3">
                                                <i class="fas fa-file-alt"></i> Go to Reports
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-2x d-block mb-3 text-success"></i>
                        <h5>No Deleted Concerns</h5>
                        <p class="mb-0 text-muted">Deleted concerns will appear here. You can delete concerns from the Reports page.</p>
                        <a href="{{ route('admin.reports') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-file-alt"></i> Go to Reports
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    @if(($viewType ?? '') == 'analytics')
    <!-- Analytics Section -->
    <div class="container-fluid">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-chart-line"></i> Analytics - Cost Tracking & Repair/Damage Analysis
            </div>
        </div>


        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">{{ $totalConcerns }}</div>
                <div class="stat-label">Total Repairs/Damages</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value">₱{{ number_format($totalCost, 2) }}</div>
                <div class="stat-label">
                    Total Cost
                    <a href="#" data-bs-toggle="modal" data-bs-target="#costModal" style="color: #fff; text-decoration: underline;">View Details</a>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value">{{ $locationStats->count() }}</div>
                <div class="stat-label">
                    Frequently Fixed Room
                    <a href="#" data-bs-toggle="modal" data-bs-target="#roomsModal" style="color: #fff; text-decoration: underline;">See Room</a>
                </div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-value">{{ $totalConcerns > 0 ? number_format($totalCost / $totalConcerns, 2) : 0 }}</div>
                <div class="stat-label">Average Cost per Repair</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="{{ route('admin.reports', ['view' => 'analytics']) }}" class="filter-form">
                <div class="filter-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="filter-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.reports', ['view' => 'analytics']) }}" class="btn-reset">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- ── TREND ALERTS ─────────────────────────────────────────────── -->
        @if(isset($trendAlerts) && $trendAlerts->count() > 0)
        <div class="analytics-card">

            {{-- ALERTS & NOTIFICATIONS --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="analytics-title" style="font-size:1rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                    <i class="fas fa-bell text-danger me-2"></i> Alerts &amp; Notifications
                    <span class="badge bg-danger ms-2">{{ $trendAlerts->count() }}</span>
                </div>
            </div>

            <div class="mb-4">
                @foreach($trendAlerts as $alert)
                @php
                    $borderColor = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                    $bgColor     = $alert['severity'] === 'critical' ? '#fef2f2' : ($alert['severity'] === 'warning' ? '#fff7ed' : '#fffbeb');
                    $iconColor   = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                    $timeAgo     = isset($alert['updated_at']) && $alert['updated_at'] ? \Carbon\Carbon::parse($alert['updated_at'])->diffForHumans(null, true, true) : 'recently';
                @endphp
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-left:4px solid {{ $borderColor }};background:{{ $bgColor }};border-radius:8px;margin-bottom:10px;cursor:pointer;"
                    onclick="showCostTrendModal({{ json_encode($alert) }})">
                    <div style="width:36px;height:36px;border-radius:50%;background:{{ $iconColor }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-triangle-exclamation" style="color:#fff;font-size:15px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:.95rem;color:#1e293b;">{{ $alert['alert_title'] ?? 'Trend Detected' }}</div>
                        <div style="font-size:.82rem;color:#64748b;">
                            @if(!empty($alert['top_issue'])){{ $alert['top_issue'] }} on {{ $alert['location'] }}@else{{ $alert['location'] }}@endif
                            &mdash; {{ $alert['severity'] === 'critical' ? 'Replacement recommended' : ($alert['severity'] === 'warning' ? 'Approaching threshold' : 'Trend detected') }}
                        </div>
                    </div>
                    <div style="font-size:.78rem;color:#94a3b8;white-space:nowrap;">{{ $timeAgo }}</div>
                </div>
                @endforeach
            </div>

            <hr style="border-color:#e2e8f0;margin:20px 0;">

            {{-- RECOMMENDATION ENGINE --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="analytics-title" style="font-size:1rem;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                    <i class="fas fa-lightbulb text-warning me-2"></i> Recommendation Engine
                </div>
            </div>

            <div>
                @foreach($trendAlerts as $alert)
                @php
                    $recColor = $alert['rec_color'] ?? 'info';
                    $recIcon  = $recColor === 'success' ? 'fa-check' : ($recColor === 'warning' ? 'fa-wrench' : 'fa-xmark');
                    $recBg    = $recColor === 'success' ? '#22c55e' : ($recColor === 'warning' ? '#f97316' : '#ef4444');
                    $recText  = $recColor === 'success' ? '#16a34a' : ($recColor === 'warning' ? '#ea580c' : '#dc2626');
                @endphp
                <div style="display:flex;align-items:center;gap:14px;padding:14px 16px;background:#fff;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:10px;cursor:pointer;transition:all 0.2s ease;"
                    onclick="showCostTrendModal({{ json_encode($alert) }})"
                    onmouseover="this.style.background='#f8fafc';this.style.borderColor='#cbd5e1';"
                    onmouseout="this.style.background='#fff';this.style.borderColor='#e2e8f0';">
                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $recBg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas {{ $recIcon }}" style="color:#fff;font-size:16px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-weight:700;font-size:.95rem;color:{{ $recText }};">{{ $alert['recommendation'] ?? 'Monitor' }}</div>
                        <div style="font-size:.82rem;color:#64748b;">@if(!empty($alert['top_issue'])){{ $alert['top_issue'] }} on {{ $alert['location'] }}@else{{ $alert['location'] }}@endif</div>
                    </div>
                    <div style="font-size:.82rem;color:#64748b;max-width:180px;text-align:right;">{{ $alert['rec_desc'] ?? '' }}</div>
                    <i class="fas fa-chevron-right" style="color:#cbd5e1;font-size:13px;"></i>
                </div>
                @endforeach
            </div>

        </div>
        @endif

        <!-- Combined Cost by Location -->
        <div class="analytics-card">
            <div class="analytics-header">
                <div class="analytics-title">
                    <i class="fas fa-map-marker-alt"></i> Combined Cost by Location (All Tickets)
                </div>
            </div>
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Total Tickets</th>
                            <th>Total Cost</th>
                            <th>Avg Cost per Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($combinedLocationStats ?? [] as $stat)
                        <tr>
                            <td>{{ $stat['location'] }}</td>
                            <td><span class="count-badge">{{ $stat['total_count'] }}</span></td>
                            <td><span class="cost-badge">₱{{ number_format($stat['total_cost'], 2) }}</span></td>
                            <td>₱{{ number_format($stat['total_count'] > 0 ? $stat['total_cost'] / $stat['total_count'] : 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No data found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Repair/Damage Details -->
        <div class="analytics-card">
            <div class="analytics-header">
                <div class="analytics-title">
                    <i class="fas fa-list"></i> Reports Details
                </div>
            </div>
            
            @if($reports->count() > 0)
            <div class="table-responsive">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Damage</th>
                            <th>Date and Time Fixed</th>
                            <th>Repair Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>{{ $report->location }}</td>
                            <td>{{ $report->damaged_part ?? 'N/A' }}</td>
                            <td>{{ $report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->format('M d, Y g:i A') : 'Not Fixed' }}</td>
                            <td><span class="cost-badge">₱{{ number_format($report->cost ?? 0, 2) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="alert-info">
                <i class="fas fa-info-circle"></i> No reports with location and date fixed data found for the selected period.
            </div>
            @endif
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-pie"></i> Repairs by Location
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="locationPieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-bar"></i> Cost by Location
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="locationBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-line"></i> Status Distribution
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="statusDoughnutChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-area"></i> Monthly Trend
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var locations = {!! json_encode($chartLocations ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
            var counts    = {!! json_encode($chartCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
            var costs     = {!! json_encode($chartCosts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
            var statuses  = {!! json_encode($chartStatuses ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
            var statusCounts = {!! json_encode($chartStatusCounts ?? [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};
            var monthly   = {!! json_encode(isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => \Carbon\Carbon::parse($s->month)->format('M Y'), 'count' => $s->total_count, 'cost' => $s->total_cost])->values() : [], JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) !!};

            var colors = ['#FF6384','#36A2EB','#FFCE56','#4BC0C0','#9966FF','#FF9F40','#C9CBCF','#4BC0C0'];

            function buildCharts() {
                if (typeof Chart === 'undefined') { setTimeout(buildCharts, 100); return; }

                // Pie — Repairs by Location
                var pieEl = document.getElementById('locationPieChart');
                if (pieEl && locations.length > 0) {
                    new Chart(pieEl, { type: 'pie', data: { labels: locations, datasets: [{ data: counts, backgroundColor: colors, borderWidth: 2 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
                }

                // Bar — Cost by Location
                var barEl = document.getElementById('locationBarChart');
                if (barEl && locations.length > 0) {
                    new Chart(barEl, { type: 'bar', data: { labels: locations, datasets: [{ label: 'Total Cost (₱)', data: costs, backgroundColor: '#36A2EB', borderWidth: 1 }] }, options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { callback: function(v){ return '₱'+v.toLocaleString(); } } } } } });
                }

                // Doughnut — Status Distribution
                var doughEl = document.getElementById('statusDoughnutChart');
                if (doughEl && statuses.length > 0) {
                    new Chart(doughEl, { type: 'doughnut', data: { labels: statuses, datasets: [{ data: statusCounts, backgroundColor: colors, borderWidth: 2 }] }, options: { responsive: true, plugins: { legend: { position: 'bottom' } } } });
                }

                // Line — Monthly Trend
                var lineEl = document.getElementById('monthlyTrendChart');
                if (lineEl && monthly.length > 0) {
                    var months = monthly.map(function(i){ return i.month; });
                    var mCounts = monthly.map(function(i){ return i.count; });
                    var mCosts  = monthly.map(function(i){ return i.cost; });
                    new Chart(lineEl, { type: 'line', data: { labels: months, datasets: [
                        { label: 'Repairs', data: mCounts, borderColor: '#36A2EB', backgroundColor: 'rgba(54,162,235,0.1)', tension: 0.4, yAxisID: 'y' },
                        { label: 'Cost (₱)', data: mCosts, borderColor: '#FF6384', backgroundColor: 'rgba(255,99,132,0.1)', tension: 0.4, yAxisID: 'y1' }
                    ]}, options: { responsive: true, scales: {
                        y: {
                            type: 'linear', position: 'left',
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0,
                                callback: function(v) {
                                    return Number.isInteger(v) ? v + (v === 1 ? ' repair' : ' repairs') : null;
                                }
                            }
                        },
                        y1: {
                            type: 'linear', position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            ticks: { callback: function(v){ return '₱' + v.toLocaleString(); } }
                        }
                    }}});
                }
            }
            buildCharts();
        })();
        </script>
    </div>

<!-- Rooms Modal -->
<div class="modal fade" id="roomsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Frequently Fixed Rooms</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @foreach($locationStats as $stat)
                <div class="room-item" onclick="showRoomDetails({{ json_encode($stat['location']) }})" style="cursor: pointer; padding: 10px; border-bottom: 1px solid #eee;">
                    <strong>{{ $stat['location'] }}</strong> - {{ $stat['count'] }} repairs, Total Cost: ₱{{ number_format($stat['total_cost'], 2) }}
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Room Details Modal -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Repairs for <span id="roomName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="roomDetailsBody">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Cost Modal -->
<div class="modal fade" id="costModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cost Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Total Repairs/Damages</h6>
                        <p class="h4 text-primary">{{ $totalConcerns }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Total Cost</h6>
                        <p class="h4 text-success">₱{{ number_format($totalCost, 2) }}</p>
                    </div>
                </div>
                <hr>
                <h6>Cost by Location</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Repairs</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($locationStats->sortByDesc('total_cost') as $stat)
                            <tr>
                                <td>{{ $stat['location'] }}</td>
                                <td>{{ $stat['count'] }}</td>
                                <td>₱{{ number_format($stat['total_cost'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    @endif
</div>

<!-- Permanent Delete All Confirmation Modal for Deleted Concerns -->
@if(($viewType ?? '') == 'deleted' && isset($deletedReports))
<div class="modal fade" id="deletedReportsPermanentDeleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Permanently Delete All Concerns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete all <strong>{{ $deletedReports->count() }}</strong> concerns?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. All concerns will be permanently removed from the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.deletedReports.permanentDeleteAll') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Permanently Delete All</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

<style>
.analytics-card {
    background: var(--card-bg, #fff);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.analytics-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--text-color, #333);
}

/* Trend Alerts */
.trend-alert-item { display:flex; align-items:center; gap:12px; padding:12px 16px; border-radius:8px; margin-bottom:10px; }
.trend-alert-item.critical { background:#fde8ea; border-left:4px solid #dc3545; }
.trend-alert-item.warning  { background:#fff8e1; border-left:4px solid #ffc107; }
.trend-alert-item.info     { background:#e8f4fd; border-left:4px solid #17a2b8; }
.trend-alert-icon { font-size:1.3rem; }
.trend-alert-text { flex:1; }
.trend-alert-text strong { display:block; font-size:.95rem; }
.trend-alert-text span   { font-size:.82rem; color:#666; }

/* Period Comparison */
.period-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:16px; }
.period-card { background:#fff; border-radius:10px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:4px solid #667eea; }
.period-card.up      { border-left-color:#dc3545; }
.period-card.down    { border-left-color:#28a745; }
.period-card.neutral { border-left-color:#6c757d; }
.period-label { font-size:.78rem; color:#888; margin-bottom:4px; }
.period-value { font-size:1.5rem; font-weight:700; }
.period-sub   { font-size:.8rem; color:#555; margin-top:4px; }
.chg-badge { display:inline-block; padding:1px 7px; border-radius:10px; font-size:.76rem; font-weight:600; }
.chg-badge.up      { background:#fde8ea; color:#dc3545; }
.chg-badge.down    { background:#e6f9f0; color:#28a745; }
.chg-badge.neutral { background:#f0f0f0; color:#6c757d; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.stat-card.green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card.orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card.yellow {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.filter-section {
    background: var(--card-bg, #fff);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-color, #333);
}

.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9rem;
}

.btn-reset {
    padding: 8px 15px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.9rem;
}

.btn-reset:hover {
    background: #5a6268;
    color: white;
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
}

.analytics-table th,
.analytics-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.analytics-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    text-align: center;
    white-space: nowrap;
}

.analytics-table tbody td {
    text-align: center;
}

.analytics-table tr:hover {
    background: #f8f9fa;
}

.cost-badge {
    background: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.85rem;
}
</style>

<style>
.dropdown-menu {
    z-index: 1050;
}
.dropdown-item {
    cursor: pointer;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}
.btn-group {
    position: relative;
}
</style>

<style>
.table .dropdown {
    position: static;
}
.table .dropdown-menu {
    position: absolute;
    z-index: 1000;
}
</style>

<style>
.context-menu {
    position: fixed;
    z-index: 1000;
    display: none;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
    min-width: 150px;
}
.context-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.context-menu ul li {
    border-bottom: 1px solid #eee;
}
.context-menu ul li:last-child {
    border-bottom: none;
}
.context-menu ul li a {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: #333;
}
.context-menu ul li a:hover {
    background: #f5f5f5;
}
.context-menu ul li a i {
    margin-right: 8px;
    width: 20px;
}
</style>

@section('scripts')
<script>
@if(isset($groupedReports))
window.groupedReports = {!! json_encode($groupedReports, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
@else
window.groupedReports = {};
@endif
function showRoomDetails(location) {
    document.getElementById('roomName').textContent = location;
    var details = '';
    if (window.groupedReports[location]) {
        window.groupedReports[location].forEach(function(report) {
            var dateFixed = report.resolved_at ? new Date(report.resolved_at).toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric'}) + ' ' + new Date(report.resolved_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'}) : 'Not Fixed';
            details += '<tr><td>' + report.location + '</td><td>' + (report.damaged_part || 'N/A') + '</td><td>' + dateFixed + '</td><td>₱' + (report.cost ? parseFloat(report.cost).toFixed(2) : '0.00') + '</td></tr>';
        });
    }
    document.getElementById('roomDetailsBody').innerHTML = '<table class="table table-striped"><thead><tr><th>Location</th><th>Damage</th><th>Date Fixed</th><th>Cost</th></tr></thead><tbody>' + details + '</tbody></table>';
    const modal = new bootstrap.Modal(document.getElementById('roomDetailsModal'));
    modal.show();
}
// Global variable for selected concern ID - defined outside DOMContentLoaded
window.selectedConcernId = null;

// Right-click handler
document.addEventListener('contextmenu', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        e.preventDefault();
        window.selectedConcernId = row.getAttribute('data-id');
        showContextMenu(e.pageX, e.pageY);
    }
});

// Long-press handler for mobile
let longPressTimer;
document.addEventListener('touchstart', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        longPressTimer = setTimeout(function() {
            window.selectedConcernId = row.getAttribute('data-id');
            const touch = e.touches[0];
            showContextMenu(touch.pageX, touch.pageY);
        }, 500);
    }
});

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    @if(($viewType ?? '') == 'analytics')
    window.chartLocations = {!! json_encode($chartLocations ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.chartCounts    = {!! json_encode($chartCounts ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.chartCosts     = {!! json_encode($chartCosts ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.chartStatuses  = {!! json_encode($chartStatuses ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.chartStatusCounts = {!! json_encode($chartStatusCounts ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!};
    window.monthlyData    = {!! json_encode(
        isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => \Carbon\Carbon::parse($s->month)->format('M Y'), 'count' => $s->total_count, 'cost' => $s->total_cost])->values() : [],
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) !!};
    @endif
    function initializeCharts() {
    // Pie Chart for Repairs by Location
    const locationPieCtx = document.getElementById('locationPieChart');
    if (locationPieCtx && window.chartLocations.length > 0) {
        new Chart(locationPieCtx, {
            type: 'pie',
            data: {
                labels: window.chartLocations,
                datasets: [{
                    data: window.chartCounts,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                        '#4BC0C0', '#FF6384'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' repairs';
                            }
                        }
                    }
                }
            }
        });
    }

    // Bar Chart for Cost by Location
    const locationBarCtx = document.getElementById('locationBarChart');
    if (locationBarCtx && window.chartLocations.length > 0) {
        new Chart(locationBarCtx, {
            type: 'bar',
            data: {
                labels: window.chartLocations,
                datasets: [{
                    label: 'Total Cost (₱)',
                    data: window.chartCosts,
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Doughnut Chart for Status Distribution
    const statusDoughnutCtx = document.getElementById('statusDoughnutChart');
    if (statusDoughnutCtx && window.chartStatuses.length > 0) {
        new Chart(statusDoughnutCtx, {
            type: 'doughnut',
            data: {
                labels: window.chartStatuses,
                datasets: [{
                    data: window.chartStatusCounts,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' reports';
                            }
                        }
                    }
                }
            }
        });
    }

    // Line Chart for Monthly Trend
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx && window.monthlyData.length > 0) {
        const months = window.monthlyData.map(item => item.month);
        const counts = window.monthlyData.map(item => item.count);
        const costs = window.monthlyData.map(item => item.cost);

        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Number of Repairs',
                    data: counts,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Total Cost (₱)',
                    data: costs,
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Repairs'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Total Cost (₱)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Repairs: ' + context.parsed.y;
                                } else {
                                    return 'Cost: ₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            }
        });
    }

}

@if(($viewType ?? '') == 'analytics')
    initializeCharts();
@endif

document.addEventListener('touchend', function() {
    clearTimeout(longPressTimer);
});

document.addEventListener('touchmove', function() {
    clearTimeout(longPressTimer);
});

function showContextMenu(x, y) {
    const menu = document.getElementById('contextMenu');
    menu.style.display = 'block';
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
    
    // Adjust if menu goes off screen
    const menuRect = menu.getBoundingClientRect();
    if (x + menuRect.width > window.innerWidth) {
        menu.style.left = (x - menuRect.width) + 'px';
    }
    if (y + menuRect.height > window.innerHeight) {
        menu.style.top = (y - menuRect.height) + 'px';
    }
}

// Hide context menu when clicking elsewhere
document.addEventListener('click', function() {
    const menu = document.getElementById('contextMenu');
    if (menu) {
        menu.style.display = 'none';
    }
});

// Context menu actions
function contextView() {
    if (window.selectedConcernId) {
        viewConcern(window.selectedConcernId);
    }
}

function contextEdit() {
    if (window.selectedConcernId) {
        editConcern(window.selectedConcernId);
    }
}

function contextArchive() {
    if (window.selectedConcernId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/concerns/' + window.selectedConcernId + '/archive';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}


// Edit Report Modal
function editReport(id) {
    const modal = new bootstrap.Modal(document.getElementById('editConcernModal'));
    const contentDiv = document.getElementById('editConcernContent');
    const form = document.getElementById('editConcernForm');

    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/api/reports/' + id + '/edit-data', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }

        form.action = '/reports/' + id;

        const categories = data.categories || [];
        const categoryOptions = categories.map(cat =>
            '<option value="' + cat.id + '" ' + (data.report.category_id == cat.id ? 'selected' : '') + '>' + cat.name + '</option>'
        ).join('');

        contentDiv.innerHTML = '<div class="mb-3">' +
            '<label class="form-label">Title</label>' +
            '<input type="text" name="title" class="form-control" value="' + (data.report.title || '') + '" required>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Category</label>' +
            '<select name="category_id" class="form-control" required>' +
            categoryOptions +
            '</select>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Location</label>' +
            '<input type="text" name="location" class="form-control" value="' + (data.report.location || '') + '" required>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Priority</label>' +
            '<select name="severity" class="form-control" required>' +
            '<option value="low" ' + (data.report.severity == 'low' ? 'selected' : '') + '>Low</option>' +
            '<option value="medium" ' + (data.report.severity == 'medium' ? 'selected' : '') + '>Medium</option>' +
            '<option value="high" ' + (data.report.severity == 'high' ? 'selected' : '') + '>High</option>' +
            '<option value="critical" ' + (data.report.severity == 'critical' ? 'selected' : '') + '>Critical</option>' +
            '</select>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Description</label>' +
            '<textarea name="description" class="form-control" rows="4" required>' + (data.report.description || '') + '</textarea>' +
            '</div>';
    })
    .catch(error => {
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading report details</div>';
    });
}

// Edit Concern Modal
function editConcern(id) {
    const modal = new bootstrap.Modal(document.getElementById('editConcernModal'));
    const contentDiv = document.getElementById('editConcernContent');
    const form = document.getElementById('editConcernForm');
    
    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    modal.show();
    
    fetch('/api/concerns/' + id + '/edit-data', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }
        
        form.action = '/concerns/' + id;
        
        const concern = data.concern;
        const categories = data.categories || [];

        contentDiv.innerHTML = '<div class="mb-3">' +
            '<label class="form-label">Title</label>' +
            '<input type="text" name="title" class="form-control" value="' + (concern.title || '') + '">' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label">Category</label>' +
            '<select name="category_id" class="form-select" required>' +
                categories.map(cat => '<option value="' + cat.id + '" ' + (concern.category_id == cat.id ? 'selected' : '') + '>' + cat.name + '</option>').join('') +
            '</select>' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label">Location</label>' +
            '<input type="text" name="location" class="form-control" value="' + (concern.location || '') + '" required>' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label">Description</label>' +
            '<textarea name="description" class="form-control" rows="4" required>' + (concern.description || '') + '</textarea>' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label fw-bold">Priority</label>' +
            '<select name="priority" class="form-select">' +
                '<option value="low" ' + (concern.priority === 'low' ? 'selected' : '') + '>Low - Minor issue, can wait</option>' +
                '<option value="medium" ' + (concern.priority === 'medium' ? 'selected' : '') + '>Medium - Needs attention</option>' +
                '<option value="high" ' + (concern.priority === 'high' ? 'selected' : '') + '>High - Affecting activities</option>' +
                '<option value="urgent" ' + (concern.priority === 'urgent' ? 'selected' : '') + '>Urgent - Emergency</option>' +
            '</select>' +
            '<small class="text-muted">Set the priority level for this concern.</small>' +
        '</div>';
    })
    .catch(error => {
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading concern details</div>';
    });
}

// Handle edit form submission
const editConcernForm = document.getElementById('editConcernForm');
if (editConcernForm) {
    editConcernForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = this.action.split('/').pop();
        
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        
        fetch('/concerns/' + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Concern updated successfully!');
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            alert('Error updating concern: ' + error.message);
        });
    });
}

// Function to archive directly from table button
function archiveConcern(id) {
    fetch('/concerns/' + id + '/archive', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Concern archived successfully!');
            location.reload();
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error archiving concern');
    });
}

// Global variables for modal actions
let currentActionType = null;
let currentActionId = null;

// Show modal based on action type
function showDeleteModal(type, id, name) {
    currentActionType = type;
    currentActionId = id;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));

    // Update modal content based on action type
    const modalTitle = document.getElementById('deleteModalLabel');
    const modalBody = document.querySelector('#deleteModal .modal-body');
    const modalHeader = document.querySelector('#deleteModal .modal-header');
    const closeButton = document.querySelector('#deleteModal .btn-close');

    const confirmButton = document.getElementById('confirmActionButton');

    if (type === 'archive') {
        modalTitle.innerHTML = '<i class="fas fa-archive"></i> Archive Concern';
        modalBody.innerHTML = `
            <p>Are you sure you want to archive <strong>${name}</strong>?</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You can restore it later from the Archives tab.
            </div>
        `;
        confirmButton.innerHTML = '<i class="fas fa-archive"></i> Archive';
        confirmButton.className = 'btn btn-secondary';
        modalHeader.classList.remove('bg-danger', 'text-white');
        modalHeader.classList.add('bg-secondary', 'text-white');
        closeButton.classList.remove('btn-close-white');
    } else {
        modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Delete Concern';
        modalBody.innerHTML = `
            <p>Are you sure you want to delete <strong>${name}</strong>?</p>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> This action will move the item to deleted. You can restore it later from the Deleted tab.
            </div>
        `;
        confirmButton.innerHTML = '<i class="fas fa-trash"></i> Delete';
        confirmButton.className = 'btn btn-danger';
        modalHeader.classList.add('bg-danger', 'text-white');
        closeButton.classList.add('btn-close-white');
    }
    
    modal.show();
}

// Confirm and execute the action
function confirmDelete() {
    if (currentActionType === 'archive') {
        // Archive action - submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/concerns/' + currentActionId + '/archive';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    } else if (currentActionType === 'delete') {
        // Delete action - submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/concerns/' + currentActionId + '/soft-delete';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }

    // Close modal
    const modalEl = document.getElementById('deleteModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}

// Deleted Reports JavaScript Functions
let currentDeletedReportId = null;

function deletedReportsToggleSelectAll() {
    const selectAll = document.getElementById('deletedReportsSelectAll');
    const checkboxes = document.querySelectorAll('.deleted-report-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    deletedReportsUpdateSelectedCount();
}

function deletedReportsUpdateSelectedCount() {
    const countEl = document.getElementById('deletedReportsSelectedCount');
    const reportIdsEl = document.getElementById('selectedDeletedReportIds');

    if (!countEl || !reportIdsEl) {
        return;
    }

    const checkboxes = document.querySelectorAll('.deleted-report-checkbox:checked');
    const count = checkboxes.length;
    countEl.textContent = count + ' concern' + (count !== 1 ? 's' : '') + ' selected';

    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    reportIdsEl.value = selectedIds.join(',');
}

function deletedReportsBulkRestore() {
    const checkboxes = document.querySelectorAll('.deleted-report-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one concern to restore.');
        return;
    }

    if (confirm('Are you sure you want to restore ' + checkboxes.length + ' concern(s)?')) {
        // Clear existing inputs
        const container = document.getElementById('selectedDeletedReportIdsContainer');
        container.innerHTML = '';

        // Add hidden inputs for each selected ID
        checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = checkbox.value;
            container.appendChild(input);
        });

        document.getElementById('deletedReportsBulkRestoreForm').submit();
    }
}

function showDeletedReportsContextMenu(e, reportId) {
    e.preventDefault();
    currentDeletedReportId = reportId;
    
    const menu = document.getElementById('deletedReportsContextMenu');
    menu.style.display = 'block';
    menu.style.left = e.pageX + 'px';
    menu.style.top = e.pageY + 'px';
}

function hideDeletedReportsContextMenu() {
    const menu = document.getElementById('deletedReportsContextMenu');
    if (menu) {
        menu.style.display = 'none';
    }
}

function deletedReportsContextView() {
    hideDeletedReportsContextMenu();
    alert('View functionality for report ' + currentDeletedReportId);
}

function deletedReportsContextRestore() {
    hideDeletedReportsContextMenu();
    if (confirm('Are you sure you want to restore this concern?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/deleted-reports/' + currentDeletedReportId + '/restore';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deletedReportsContextPermanentDelete() {
    hideDeletedReportsContextMenu();
    const modalId = 'deletedReportsPermanentDeleteModal' + currentDeletedReportId;
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

// Add right-click listeners to deleted reports table rows
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('deletedReportsTable');
    if (table) {
        table.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('contextmenu', (e) => {
                const reportId = row.getAttribute('data-id');
                if (reportId) {
                    showDeletedReportsContextMenu(e, reportId);
                }
            });
        });
    }
    
    // Hide context menu on click outside
    document.addEventListener('click', (e) => {
        const menu = document.getElementById('deletedReportsContextMenu');
        if (menu && !menu.contains(e.target)) {
            hideDeletedReportsContextMenu();
        }
    });
    
    deletedReportsUpdateSelectedCount();
});

// Store current report IDs for modals
let currentReportId = null;

// Show Archive Modal
function showReportArchiveModal(reportId) {
    currentReportId = reportId;
    const modal = new bootstrap.Modal(document.getElementById('reportArchiveModal'));
    modal.show();
}

// Confirm Archive
function confirmReportArchive() {
    if (!currentReportId) return;

    fetch('/reports/' + currentReportId + '/archive', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportArchiveModal'));
            modal.hide();
            // Reload page to update the table
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to archive report'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while archiving the report');
    });
}

// Show Delete Modal
function showReportDeleteModal(reportId) {
    currentReportId = reportId;
    const modal = new bootstrap.Modal(document.getElementById('reportDeleteModal'));
    modal.show();
}

// Confirm Delete
function confirmReportDelete() {
    if (!currentReportId || isNaN(currentReportId)) {
        alert('Invalid report ID');
        return;
    }

    fetch('/reports/' + currentReportId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportDeleteModal'));
            modal.hide();
            // Reload page to update the table
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete report'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the report');
    });
}

// Store current concern ID for assignment
let currentConcernId = null;

// Assign Report directly from table button
window.assignReport = function(id) {
    window.currentReportId = id;
    window.currentConcernId = null;
    window.showAssignModal();
}

// Show Assign Modal
window.showAssignModal = function showAssignModal() {
    const concernId = window.currentConcernId;
    const reportId = window.currentReportId;

    if (!concernId && !reportId) {
        alert('No item selected');
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('assignConcernModal'));
    const select = document.getElementById('assigned_to');
    const form = document.getElementById('assignConcernForm');

    // Determine if it's a concern or report
    const isReport = !!reportId;
    const itemId = isReport ? reportId : concernId;
    const itemType = isReport ? 'report' : 'concern';

    // Set the ID
    document.getElementById('assignConcernId').value = itemId;

    // Set data attribute for type
    form.setAttribute('data-type', itemType);

    // Set the form action for non-JS fallback
    form.action = '/admin/' + itemType + '/' + itemId + '/assign';
    
    // Load maintenance staff list
    select.innerHTML = '<option value="">Loading...</option>';
    
    fetch('/admin/maintenance-users', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            select.innerHTML = '<option value="">Error loading users</option>';
            return;
        }
        
        select.innerHTML = '<option value="">Select maintenance staff</option>';
        data.users.forEach(user => {
            select.innerHTML += `<option value="${user.id}">${user.name}</option>`;
        });
    })
    .catch(error => {
        console.error('Error:', error);
        select.innerHTML = '<option value="">Error loading users</option>';
    });
    
    modal.show();
}

// Handle assign form submission
const assignForm = document.getElementById('assignConcernForm');
if (assignForm) {
    assignForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const itemId = document.getElementById('assignConcernId').value;
        const assignedTo = document.getElementById('assigned_to').value;
        const itemType = this.getAttribute('data-type') || 'concern';

        if (!assignedTo) {
            alert('Please select a maintenance staff');
            return;
        }

        const formData = new FormData();
        formData.append('assigned_to', assignedTo);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('/admin/' + itemType + '/' + itemId + '/assign', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('assignConcernModal')).hide();
                alert(itemType.charAt(0).toUpperCase() + itemType.slice(1) + ' assigned successfully!');
                location.reload();
            } else {
                alert(data.error || 'Failed to assign ' + itemType);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error assigning ' + itemType);
        });
    });
}

// Date formatting function
function formatDate(dateString) {
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = months[date.getMonth()];
    const day = date.getDate();
    const year = date.getFullYear();
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
}

// View report function for Building Admin
function viewReport(id) {
    window.currentReportId = id; // Store the current report ID
    const modal = new bootstrap.Modal(document.getElementById('viewConcernModal'));
    const contentDiv = document.getElementById('viewConcernModalLabel');
    const bodyDiv = document.getElementById('viewConcernContent');

    contentDiv.textContent = 'Report Details';
    bodyDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/api/reports/' + id, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || 'HTTP ' + response.status); });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            bodyDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }

        const report = data.report;
        const severityClass = report.severity === 'critical' ? 'danger' :
            (report.severity === 'high' ? 'warning' :
            (report.severity === 'medium' ? 'info' : 'secondary'));

        const statusClass = report.status === 'Resolved' ? 'success' :
            (report.status === 'In Progress' ? 'warning' :
            (report.status === 'Assigned' ? 'primary' : 'secondary'));

        let imageHtml = '';
        if (report.photo_path) {
            imageHtml = '<div class="mb-3"><p><strong>Photo:</strong></p><img src="' + report.photo_path + '" alt="Report photo" class="img-fluid rounded" style="max-width: 400px;"></div>';
        }

        bodyDiv.innerHTML = '<div class="card">' +
            '<div class="card-header d-flex justify-content-between align-items-center">' +
                '<h4>Report #' + report.id + '</h4>' +
                '<div><span class="badge bg-' + severityClass + ' me-2">' + report.severity.charAt(0).toUpperCase() + report.severity.slice(1) + ' Priority</span><span class="badge bg-' + statusClass + '">' + report.status + '</span></div>' +
            '</div>' +
            '<div class="card-body">' +
                '<h5 class="card-title">' + (report.title || 'No Title') + '</h5>' +
                '<div class="row mb-3">' +
                    '<div class="col-md-6"><p><strong>Category:</strong> ' + (report.category ? report.category.name : 'N/A') + '</p><p><strong>Location:</strong> ' + report.location + '</p></div>' +
                    '<div class="col-md-6"><p><strong>Reported by:</strong> ' + (report.user ? report.user.name : 'Unknown') + '</p><p><strong>Date:</strong> ' + report.created_at + '</p></div>' +
                '</div>' +
                (report.assigned_to ? '<div class="mb-3"><p><strong>Assigned to:</strong> ' + (report.assigned_user_name || 'Unknown') + '</p></div>' : '') +
                (report.damaged_part ? '<div class="mb-3"><p><strong>Damaged Part:</strong> ' + report.damaged_part + '</p></div>' : '') +
                '<div class="mb-3"><p><strong>Description:</strong></p><p>' + report.description + '</p></div>' +
                imageHtml +
                (report.resolution_notes ? '<div class="mb-3"><p><strong>Resolution Notes:</strong></p><p>' + report.resolution_notes + '</p></div>' : '') +
                ((report.cost || report.replaced_part) ? '<div class="mb-3"><p><strong>Maintenance Details:</strong></p><div class="row"><div class="col-md-6">' + (report.cost ? '<p><strong>Cost:</strong> ₱' + parseFloat(report.cost).toFixed(2) + '</p>' : '') + '</div><div class="col-md-6">' + (report.replaced_part ? '<p><strong>Replaced With:</strong> ' + report.replaced_part + '</p>' : '') + '</div></div></div>' : '') +
            '</div>' +
        '</div>';
    })
    .catch(error => {
        console.error('viewReport error:', error);
        bodyDiv.innerHTML = '<div class="alert alert-danger">Error loading report details. Check console for details.</div>';
    });
}

// Acknowledge concern function for maintenance
function acknowledgeConcern(concernId) {
    if (!concernId) {
        alert('No concern ID provided');
        return;
    }

    fetch('/concerns/' + concernId + '/acknowledge', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Concern acknowledged! You can now work on it.');
            bootstrap.Modal.getInstance(document.getElementById('viewConcernModal')).hide();
            location.reload();
        } else {
            alert(data.error || 'Failed to acknowledge concern');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error acknowledging concern');
    });
}

    // Handle retention days dropdown change (only if element exists)
    document.addEventListener('DOMContentLoaded', function() {
        const retentionDaysElement = document.getElementById('retentionDays');
        if (retentionDaysElement) {
            retentionDaysElement.addEventListener('change', function() {
                const days = this.value;
                if (confirm(`Set auto-filter to show reports deleted more than ${days} days ago?`)) {
                    // Show loading indicator
                    this.disabled = true;

                    // Make AJAX request to save preference
                    fetch('{{ route("saveAutoDeletePreference") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ days: parseInt(days), module: 'reports' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show filtered results
                            window.location.href = '{{ route("admin.reports") }}?view=deleted&days=' + days;
                        } else {
                            alert('Error saving preference.');
                        }
                    })
                    .catch(error => {
                        alert('An error occurred while saving your preference.');
                        console.error(error);
                    })
                    .finally(() => {
                        // Restore dropdown
                        this.disabled = false;
                    });
                } else {
                    // Reset to previous value
                    this.value = '{{ $days ?? 15 }}';
                }
            });
        }
    });
</script>

<script>
// Checkbox functions for bulk actions
function toggleAllReports(checkbox) {
    const checkboxes = document.querySelectorAll('.report-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function getSelectedReports() {
    const checkboxes = document.querySelectorAll('.report-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Assign Report from table button
function assignReport(id) {
    window.currentReportId = id;
    window.currentConcernId = null;

    const modal = new bootstrap.Modal(document.getElementById('assignConcernModal'));
    const select = document.getElementById('assigned_to');
    const form = document.getElementById('assignConcernForm');

    document.getElementById('assignConcernId').value = id;
    form.setAttribute('data-type', 'report');
    form.action = '/admin/report/' + id + '/assign';

    select.innerHTML = '<option value="">Loading...</option>';

    fetch('/admin/maintenance-users', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            select.innerHTML = '<option value="">Error loading users</option>';
            return;
        }
        select.innerHTML = '<option value="">Select maintenance staff</option>';
        data.users.forEach(function(user) {
            select.innerHTML += '<option value="' + user.id + '">' + user.name + '</option>';
        });
    })
    .catch(function() {
        select.innerHTML = '<option value="">Error loading users</option>';
    });

    modal.show();
}

// View Report from table button
function viewReport(id) {
    window.currentReportId = id;
    const modal = new bootstrap.Modal(document.getElementById('viewConcernModal'));
    const contentDiv = document.getElementById('viewConcernModalLabel');
    const bodyDiv = document.getElementById('viewConcernContent');

    contentDiv.textContent = 'Report Details';
    bodyDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    modal.show();

    fetch('/api/reports/' + id, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => { throw new Error(err.error || 'HTTP ' + response.status); });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            bodyDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }
        const report = data.report;
        const severityClass = report.severity === 'critical' ? 'danger' :
            (report.severity === 'high' ? 'warning' :
            (report.severity === 'medium' ? 'info' : 'secondary'));
        const statusClass = report.status === 'Resolved' ? 'success' :
            (report.status === 'In Progress' ? 'warning' :
            (report.status === 'Assigned' ? 'primary' : 'secondary'));
        let imageHtml = '';
        if (report.photo_path) {
            imageHtml = '<div class="mb-3"><p><strong>Photo:</strong></p><img src="' + report.photo_path + '" alt="Report photo" class="img-fluid rounded" style="max-width:400px;"></div>';
        }
        bodyDiv.innerHTML = '<div class="card">' +
            '<div class="card-header d-flex justify-content-between align-items-center">' +
                '<h4>Report #' + report.id + '</h4>' +
                '<div><span class="badge bg-' + severityClass + ' me-2">' + report.severity.charAt(0).toUpperCase() + report.severity.slice(1) + ' Priority</span>' +
                '<span class="badge bg-' + statusClass + '">' + report.status + '</span></div>' +
            '</div>' +
            '<div class="card-body">' +
                '<h5 class="card-title">' + (report.title || 'No Title') + '</h5>' +
                '<div class="row mb-3">' +
                    '<div class="col-md-6"><p><strong>Category:</strong> ' + (report.category ? report.category.name : 'N/A') + '</p>' +
                    '<p><strong>Location:</strong> ' + report.location + '</p></div>' +
                    '<div class="col-md-6"><p><strong>Reported by:</strong> ' + (report.user ? report.user.name : 'Unknown') + '</p>' +
                    '<p><strong>Date:</strong> ' + report.created_at + '</p></div>' +
                '</div>' +
                (report.assigned_to ? '<div class="mb-3"><p><strong>Assigned to:</strong> ' + (report.assigned_user_name || 'Unknown') + '</p></div>' : '') +
                (report.damaged_part ? '<div class="mb-3"><p><strong>Damaged Part:</strong> ' + report.damaged_part + '</p></div>' : '') +
                '<div class="mb-3"><p><strong>Description:</strong></p><p>' + (report.description || '') + '</p></div>' +
                imageHtml +
                (report.resolution_notes ? '<div class="mb-3"><p><strong>Resolution Notes:</strong></p><p>' + report.resolution_notes + '</p></div>' : '') +
                ((report.cost || report.replaced_part) ? '<div class="mb-3"><p><strong>Maintenance Details:</strong></p><div class="row">' +
                    '<div class="col-md-6">' + (report.cost ? '<p><strong>Cost:</strong> ₱' + parseFloat(report.cost).toFixed(2) + '</p>' : '') + '</div>' +
                    '<div class="col-md-6">' + (report.replaced_part ? '<p><strong>Replaced With:</strong> ' + report.replaced_part + '</p>' : '') + '</div>' +
                '</div></div>' : '') +
            '</div></div>';
    })
    .catch(function(error) {
        console.error('viewReport error:', error);
        bodyDiv.innerHTML = '<div class="alert alert-danger">Error loading report details.</div>';
    });
}
</script>

<!-- Cost Trend Modal -->
<div class="modal fade" id="costTrendModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-chart-line me-2"></i><span id="ctm_title"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-6">
                        <div style="font-size:.8rem;color:#888;">Location</div>
                        <div style="font-weight:700;" id="ctm_location"></div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:.8rem;color:#888;">Total Repairs</div>
                        <div style="font-weight:700;color:#3b82f6;" id="ctm_repairs"></div>
                    </div>
                    <div class="col-3">
                        <div style="font-size:.8rem;color:#888;">Cumulative Cost</div>
                        <div style="font-weight:700;color:#22c55e;" id="ctm_total_cost"></div>
                    </div>
                </div>
                <div class="row mb-3" id="ctm_threshold_row">
                    <div class="col-6">
                        <div style="font-size:.8rem;color:#888;">Original Asset Price</div>
                        <div style="font-weight:700;" id="ctm_threshold"></div>
                    </div>
                    <div class="col-6">
                        <div style="font-size:.8rem;color:#888;">Cost vs Original Price</div>
                        <div class="progress mt-1" style="height:10px;">
                            <div class="progress-bar" id="ctm_progress_bar" style="width:0%"></div>
                        </div>
                        <div style="font-size:.78rem;color:#888;margin-top:3px;" id="ctm_progress_label"></div>
                    </div>
                </div>
                <hr>
                <h6 class="mb-3">Monthly Cost Breakdown</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-center">Repairs</th>
                                <th class="text-end">Cost</th>
                            </tr>
                        </thead>
                        <tbody id="ctm_monthly_rows"></tbody>
                        <tfoot>
                            <tr class="table-secondary fw-bold">
                                <td>Total</td>
                                <td class="text-center" id="ctm_total_count"></td>
                                <td class="text-end" id="ctm_total_cost_foot"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showCostTrendModal(alert) {
    document.getElementById('ctm_title').textContent = (alert.top_issue || 'Issue') + ' — ' + alert.location;
    document.getElementById('ctm_location').textContent = alert.location;
    document.getElementById('ctm_repairs').textContent = alert.recent + ' repair(s)';
    document.getElementById('ctm_total_cost').textContent = '₱' + parseFloat(alert.all_time_cost).toLocaleString('en-PH', {minimumFractionDigits:2});

    const threshold = parseFloat(alert.replacement_threshold || 0);
    const allTime   = parseFloat(alert.all_time_cost || 0);
    const threshRow = document.getElementById('ctm_threshold_row');
    if (threshold > 0) {
        threshRow.style.display = '';
        document.getElementById('ctm_threshold').textContent = '₱' + threshold.toLocaleString('en-PH', {minimumFractionDigits:2});
        const pct = Math.min(100, Math.round((allTime / threshold) * 100));
        const bar = document.getElementById('ctm_progress_bar');
        bar.style.width = pct + '%';
        bar.className = 'progress-bar ' + (pct >= 100 ? 'bg-danger' : pct >= 80 ? 'bg-warning' : 'bg-success');
        document.getElementById('ctm_progress_label').textContent = pct + '% of original price used in repairs';
    } else {
        threshRow.style.display = 'none';
    }

    const tbody = document.getElementById('ctm_monthly_rows');
    tbody.innerHTML = '';
    let totalCount = 0, totalCost = 0;
    (alert.monthly_costs || []).forEach(function(row) {
        totalCount += parseInt(row.count || 0);
        totalCost  += parseFloat(row.cost || 0);
        tbody.innerHTML += '<tr><td>' + row.month + '</td><td class="text-center">' + row.count + '</td><td class="text-end">₱' + parseFloat(row.cost).toLocaleString('en-PH', {minimumFractionDigits:2}) + '</td></tr>';
    });
    document.getElementById('ctm_total_count').textContent = totalCount;
    document.getElementById('ctm_total_cost_foot').textContent = '₱' + totalCost.toLocaleString('en-PH', {minimumFractionDigits:2});

    new bootstrap.Modal(document.getElementById('costTrendModal')).show();
}
</script>
@endsection

