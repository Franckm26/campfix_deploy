@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        /* Hide tables on mobile - more specific selectors */
        .table-responsive,
        .table-responsive table,
        .card-body .table-responsive,
        div[class*="table-responsive"] {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
        }
        
        /* Show mobile cards */
        .mobile-report-cards {
            display: block !important;
        }
        
        /* Report card styling */
        .report-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .report-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .report-card-header div {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .report-card-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .report-card-ticket {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        
        .report-card-body {
            margin-bottom: 12px;
        }
        
        .report-card-field {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .report-card-label {
            font-weight: 600;
            min-width: 100px;
            color: #666;
        }
        
        .report-card-value {
            color: #333;
            flex: 1;
            word-break: break-word;
        }
        
        .report-card-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
            flex-wrap: wrap;
        }
        
        .report-card-actions .btn {
            flex: 1;
            font-size: 12px;
        }
        
        /* Dark mode support */
        [data-bs-theme="dark"] .report-card {
            background: #2d3238;
            border-color: #495057;
        }
        
        [data-bs-theme="dark"] .report-card-header {
            border-bottom-color: #495057;
        }
        
        [data-bs-theme="dark"] .report-card-ticket,
        [data-bs-theme="dark"] .report-card-value {
            color: #e9ecef;
        }
        
        [data-bs-theme="dark"] .report-card-label {
            color: #adb5bd;
        }
        
        [data-bs-theme="dark"] .report-card-actions {
            border-top-color: #495057;
        }
    }
    
    /* Hide mobile cards on desktop */
    .mobile-report-cards {
        display: none;
    }
</style>
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
                </ul>
                <a href="{{ route('admin.export.pdf', array_filter([
                    'view' => $viewType ?? 'active',
                    'archived' => request('archived'),
                    'status' => request('status'),
                    'priority' => request('priority'),
                    'category' => request('category'),
                    'search' => request('search')
                ])) }}" class="btn btn-danger btn-sm" target="_blank">
                    <i class="fas fa-file-pdf"></i> Export PDF
                </a>
            </div>
            <form method="GET" action="{{ route('admin.reports') }}" id="reportsFilterForm">
                <input type="hidden" name="view" value="{{ $viewType ?? 'active' }}">
                <div class="row g-2">
                    <div class="col-6 col-md">
                        <select name="status" class="form-select form-select-sm auto-submit-filter">
                            <option value="">All Status</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                            <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        </select>
                    </div>
                    <div class="col-6 col-md">
                        <select name="priority" class="form-select form-select-sm auto-submit-filter">
                            <option value="">All Priority</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="col-6 col-md">
                        <select name="category" class="form-select form-select-sm auto-submit-filter">
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
                            value="{{ request('search') }}" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="{{ route('admin.reports', ['view' => $viewType ?? 'active']) }}" class="btn btn-secondary btn-sm ms-1"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(($viewType ?? 'active') == 'active')
    <!-- Summary Cards -->
    @if(auth()->user()->role !== 'building_admin')
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
    @endif

    <!-- Reports Table -->
    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">
            <!-- Bulk Actions for Active Reports -->
            <div class="bulk-actions mb-3" id="activeBulkActions" style="display: none;">
                <div class="btn-group">
                    <button type="button" class="btn btn-warning btn-sm" onclick="batchArchiveSelected()">
                        <i class="fas fa-archive"></i> Archive Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="batchSoftDeleteSelected()">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
                <span class="ms-2 text-muted" id="activeSelectedCount">0 selected</span>
            </div>

            <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                <table class="table table-hover" style="display: table !important;">
                    <thead>
                        <tr>
                            <th class="checkbox-col"><input type="checkbox" id="selectAllReports" onclick="toggleAllReports(this)"></th>
                            <th class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center;">Ticket</th>
                            <th>Issue</th>
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
                                <td class="checkbox-col"><input type="checkbox" class="report-checkbox active-checkbox" value="{{ $report->id }}" onchange="updateActiveBulkActions()"></td>
                                <td class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center; padding: 4px;">#{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $report->title ?? \Illuminate\Support\Str::limit($report->description, 40) }}</td>
                                        <td>{{ $report->category->name ?? 'N/A' }}</td>
                                        <td>{{ $report->location }}</td>
                                        <td>
                                            @if($report->is_safety_hazard)
                                                <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white; border: 1px solid #8b0000; font-weight: bold;">
                                                    <i class="fas fa-exclamation-triangle"></i> Safety Hazard
                                                </span>
                                            @else
                                                <span class="badge bg-{{
                                                    $report->severity == 'critical' || $report->severity == 'urgent' ? 'danger' :
                                                    ($report->severity == 'high' ? 'warning' :
                                                    ($report->severity == 'medium' ? 'info' : 'secondary'))
                                                }}">
                                                    {{ ($report->severity ? ucfirst($report->severity) : 'Not Set') }}
                                                </span>
                                            @endif
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
                                            {{ $report->reported_by_name ?? ($report->user ? $report->user->name : 'Unknown') }}
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
                                                <button type="button" class="btn btn-sm btn-info bg-transparent border-0" onclick="viewReportProgress({{ $report->id }})" title="View Progress">
                                                    <i class="fas fa-tasks"></i>
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
                                <td colspan="11" class="text-center">No reports found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout for Active Reports -->
            <div class="mobile-report-cards">
                @forelse($reports as $report)
                    <div class="report-card" data-id="{{ $report->id }}">
                        <div class="report-card-header">
                            <div>
                                <input type="checkbox" class="report-card-checkbox report-checkbox active-checkbox" value="{{ $report->id }}" onchange="updateActiveBulkActions()">
                                <span class="report-card-ticket">#{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <span class="badge bg-{{
                                $report->status == 'Resolved' ? 'success' :
                                ($report->status == 'In Progress' ? 'warning' :
                                ($report->status == 'Assigned' ? 'primary' : 'secondary'))
                            }}">
                                {{ $report->status }}
                            </span>
                        </div>
                        <div class="report-card-body">
                            <div class="report-card-field">
                                <span class="report-card-label">Issue:</span>
                                <span class="report-card-value">{{ $report->title ?? \Illuminate\Support\Str::limit($report->description, 40) }}</span>
                            </div>
                            <div class="report-card-field">
                                <span class="report-card-label">Category:</span>
                                <span class="report-card-value">{{ $report->category->name ?? 'N/A' }}</span>
                            </div>
                            <div class="report-card-field">
                                <span class="report-card-label">Location:</span>
                                <span class="report-card-value">{{ $report->location }}</span>
                            </div>
                            <div class="report-card-field">
                                <span class="report-card-label">Priority:</span>
                                <span class="report-card-value">
                                    @if($report->is_safety_hazard)
                                        <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white;">
                                            <i class="fas fa-exclamation-triangle"></i> Safety Hazard
                                        </span>
                                    @else
                                        <span class="badge bg-{{
                                            $report->severity == 'critical' || $report->severity == 'urgent' ? 'danger' :
                                            ($report->severity == 'high' ? 'warning' :
                                            ($report->severity == 'medium' ? 'info' : 'secondary'))
                                        }}">
                                            {{ ($report->severity ? ucfirst($report->severity) : 'Not Set') }}
                                        </span>
                                    @endif
                                </span>
                            </div>
                            <div class="report-card-field">
                                <span class="report-card-label">Reported By:</span>
                                <span class="report-card-value">{{ $report->reported_by_name ?? ($report->user ? $report->user->name : 'Unknown') }}</span>
                            </div>
                            <div class="report-card-field">
                                <span class="report-card-label">Created:</span>
                                <span class="report-card-value">{{ $report->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        <div class="report-card-actions">
                            <button type="button" class="btn btn-sm btn-info" onclick="viewReport({{ $report->id }})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="assignReport({{ $report->id }})">
                                <i class="fas fa-user-plus"></i> Assign
                            </button>
                            @if(!$report->isArchivedByUser(auth()->id()))
                                <button type="button" class="btn btn-sm btn-secondary" onclick="showReportArchiveModal({{ $report->id }})">
                                    <i class="fas fa-archive"></i> Archive
                                </button>
                            @endif
                            @if(!$report->assigned_to || $report->status === 'Resolved')
                            <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal({{ $report->id }})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <h4 class="text-muted">No reports found</h4>
                    </div>
                @endforelse
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
            
            <!-- Bulk Actions for Resolved Reports -->
            <div class="bulk-actions mb-3" id="resolvedBulkActions" style="display: none;">
                <div class="btn-group">
                    <button type="button" class="btn btn-warning btn-sm" onclick="batchArchiveResolved()">
                        <i class="fas fa-archive"></i> Archive Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="batchSoftDeleteResolved()">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
                <span class="ms-2 text-muted" id="resolvedSelectedCount">0 selected</span>
            </div>
            
            @if(isset($resolvedReports) && $resolvedReports->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;">
                        <thead>
                            <tr>
                                <th class="checkbox-col"><input type="checkbox" id="selectAllResolvedReports" onclick="toggleAllResolvedReports(this)"></th>
                                <th class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center;">Ticket</th>
                                <th>Issue</th>
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
                                    <td class="checkbox-col"><input type="checkbox" class="resolved-report-checkbox resolved-checkbox" value="{{ $report->id }}" onchange="updateResolvedBulkActions()"></td>
                                    <td class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center; padding: 4px;">#{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $report->title ?? \Illuminate\Support\Str::limit($report->description, 40) }}</td>
                                    <td>{{ $report->category->name ?? 'N/A' }}</td>
                                    <td>{{ $report->location }}</td>
                                    <td>
                                        @if($report->is_safety_hazard)
                                            <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white; border: 1px solid #8b0000; font-weight: bold;">
                                                <i class="fas fa-exclamation-triangle"></i> Safety Hazard
                                            </span>
                                        @else
                                            <span class="badge bg-{{
                                                $report->severity == 'critical' || $report->severity == 'urgent' ? 'danger' :
                                                ($report->severity == 'high' ? 'warning' :
                                                ($report->severity == 'medium' ? 'info' : 'secondary'))
                                            }}">
                                                {{ ($report->severity ? ucfirst($report->severity) : 'Not Set') }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $report->reported_by_name ?? ($report->user ? $report->user->name : 'Unknown') }}</td>
                                    <td>{{ $report->created_at->format('M d, Y') }}</td>
                                    <td>{{ $report->resolved_at ? $report->resolved_at->format('M d, Y g:i A') : '-' }}</td>
                                    <td>PHP{{ number_format($report->cost ?? 0, 2) }}</td>
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

                <!-- Mobile Card Layout for Resolved Reports -->
                <div class="mobile-report-cards">
                    @foreach($resolvedReports as $report)
                        <div class="report-card">
                            <div class="report-card-header">
                                <div>
                                    <input type="checkbox" class="report-card-checkbox resolved-report-checkbox resolved-checkbox" value="{{ $report->id }}" onchange="updateResolvedBulkActions()">
                                    <span class="report-card-ticket">#{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <span class="badge bg-success">Resolved</span>
                            </div>
                            <div class="report-card-body">
                                <div class="report-card-field">
                                    <span class="report-card-label">Issue:</span>
                                    <span class="report-card-value">{{ $report->title ?? \Illuminate\Support\Str::limit($report->description, 40) }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Category:</span>
                                    <span class="report-card-value">{{ $report->category->name ?? 'N/A' }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Location:</span>
                                    <span class="report-card-value">{{ $report->location }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Priority:</span>
                                    <span class="report-card-value">
                                        @if($report->is_safety_hazard)
                                            <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white;">
                                                <i class="fas fa-exclamation-triangle"></i> Safety Hazard
                                            </span>
                                        @else
                                            <span class="badge bg-{{
                                                $report->severity == 'critical' || $report->severity == 'urgent' ? 'danger' :
                                                ($report->severity == 'high' ? 'warning' :
                                                ($report->severity == 'medium' ? 'info' : 'secondary'))
                                            }}">
                                                {{ ($report->severity ? ucfirst($report->severity) : 'Not Set') }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Reported By:</span>
                                    <span class="report-card-value">{{ $report->reported_by_name ?? ($report->user ? $report->user->name : 'Unknown') }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Resolved:</span>
                                    <span class="report-card-value">{{ $report->resolved_at ? $report->resolved_at->format('M d, Y g:i A') : '-' }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Cost:</span>
                                    <span class="report-card-value">PHP{{ number_format($report->cost ?? 0, 2) }}</span>
                                </div>
                            </div>
                            <div class="report-card-actions">
                                <button type="button" class="btn btn-sm btn-info" onclick="viewReport({{ $report->id }})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="showReportArchiveModal({{ $report->id }})">
                                    <i class="fas fa-archive"></i> Archive
                                </button>
                                @if(!$report->assigned_to || $report->status === 'Resolved')
                                <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal({{ $report->id }})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
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
                    <!-- Bulk Actions for Archived Reports -->
                    <div class="bulk-actions mb-3" id="archiveBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreArchived()">
                                <i class="fas fa-trash-restore"></i> Restore Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchSoftDeleteArchived()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="archiveSelectedCount">0 selected</span>
                    </div>

                    @if(isset($archivedConcerns) && $archivedConcerns->count() > 0)
                        <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                            <table class="table table-hover" style="display: table !important;">
                                <thead>
                                    <tr>
                                        <th class="checkbox-col"><input type="checkbox" id="selectAllArchived" onclick="toggleAllArchived(this)"></th>
                                        <th class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center;">Ticket</th>
                                        <th>Issue</th>
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
                                            <td class="checkbox-col"><input type="checkbox" class="archive-checkbox" value="{{ $concern->id }}" onchange="updateArchiveBulkActions()"></td>
                                            <td class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center; padding: 4px;">#{{ str_pad($concern->id, 4, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $concern->title ?? \Illuminate\Support\Str::limit($concern->description, 40) }}</td>
                                            <td>{{ $concern->categoryRelation->name ?? 'N/A' }}</td>
                                            <td>{{ $concern->location }}</td>
                                            <td>
                                                <span class="badge bg-{{
                                                    $concern->priority == 'urgent' ? 'danger' :
                                                    ($concern->priority == 'high' ? 'warning' :
                                                    ($concern->priority == 'medium' ? 'info' : 'secondary'))
                                                }}">
                                                    {{ ($concern->priority ? ucfirst($concern->priority) : 'Not Set') }}
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

                        <!-- Mobile Card Layout for Archived Reports -->
                        <div class="mobile-report-cards">
                            @foreach($archivedConcerns as $concern)
                                <div class="report-card">
                                    <div class="report-card-header">
                                        <div>
                                            <input type="checkbox" class="report-card-checkbox archive-checkbox" value="{{ $concern->id }}" onchange="updateArchiveBulkActions()">
                                            <span class="report-card-ticket">#{{ str_pad($concern->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <span class="badge bg-{{
                                            $concern->status == 'Resolved' ? 'success' :
                                            ($concern->status == 'In Progress' ? 'warning' :
                                            ($concern->status == 'Assigned' ? 'primary' : 'secondary'))
                                        }}">
                                            {{ $concern->status }}
                                        </span>
                                    </div>
                                    <div class="report-card-body">
                                        <div class="report-card-field">
                                            <span class="report-card-label">Issue:</span>
                                            <span class="report-card-value">{{ $concern->title ?? \Illuminate\Support\Str::limit($concern->description, 40) }}</span>
                                        </div>
                                        <div class="report-card-field">
                                            <span class="report-card-label">Category:</span>
                                            <span class="report-card-value">{{ $concern->categoryRelation->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="report-card-field">
                                            <span class="report-card-label">Location:</span>
                                            <span class="report-card-value">{{ $concern->location }}</span>
                                        </div>
                                        <div class="report-card-field">
                                            <span class="report-card-label">Priority:</span>
                                            <span class="report-card-value">
                                                <span class="badge bg-{{
                                                    $concern->priority == 'urgent' ? 'danger' :
                                                    ($concern->priority == 'high' ? 'warning' :
                                                    ($concern->priority == 'medium' ? 'info' : 'secondary'))
                                                }}">
                                                    {{ ($concern->priority ? ucfirst($concern->priority) : 'Not Set') }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="report-card-field">
                                            <span class="report-card-label">Reported By:</span>
                                            <span class="report-card-value">{{ $concern->is_anonymous ? 'Anonymous' : ($concern->user->name ?? 'Unknown') }}</span>
                                        </div>
                                        <div class="report-card-field">
                                            <span class="report-card-label">Created:</span>
                                            <span class="report-card-value">{{ $concern->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="report-card-actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewConcern({{ $concern->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <form method="POST" action="{{ route('admin.archive.restore') }}" style="flex: 1;">
                                            @csrf
                                            <input type="hidden" name="type" value="report">
                                            <input type="hidden" name="id" value="{{ $concern->id }}">
                                            <button type="submit" class="btn btn-sm btn-success w-100">
                                                <i class="fas fa-trash-restore"></i> Restore
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal({{ $concern->id }})">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            @endforeach
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
            <!-- Bulk Actions for Deleted Reports -->
            <div class="bulk-actions mb-3" id="deletedBulkActions" style="display: none;">
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreDeleted()">
                        <i class="fas fa-trash-restore"></i> Restore Selected
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="batchPermanentDelete()">
                        <i class="fas fa-ban"></i> Permanently Delete Selected
                    </button>
                </div>
                <span class="ms-2 text-muted" id="deletedSelectedCount">0 selected</span>
            </div>

            @if(isset($deletedReports) && $deletedReports->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;" id="deletedReportsTable">
                        <thead>
                            <tr>
                                <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="deletedReportsSelectAll" onchange="deletedReportsToggleSelectAll()"></th>
                                <th class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center;">Ticket</th>
                                <th>Issue</th>
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
                                    <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-report-checkbox deleted-checkbox" value="{{ $report->id }}" onchange="deletedReportsUpdateSelectedCount(); updateDeletedBulkActions()"></td>
                                    <td class="ticket-col" style="width: 60px; max-width: 60px; min-width: 60px; white-space: nowrap; text-align: center; padding: 4px;">#{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $report->title ?? \Illuminate\Support\Str::limit($report->description, 40) }}</td>
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
                                    <td>{{ $report->reported_by_name ?? ($report->user ? $report->user->name : 'Unknown') }}</td>
                                    <td>{{ $report->updated_at->format('M d, Y h:i A') }}</td>
                                    <td>{{ $report->deletedBy ? $report->deletedBy->name : 'System' }}</td>
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
                                    <td colspan="12" class="text-center p-4">
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

                <!-- Mobile Card Layout for Deleted Reports -->
                <div class="mobile-report-cards">
                    @forelse($deletedReports as $report)
                        <div class="report-card" data-id="{{ $report->id }}">
                            <div class="report-card-header">
                                <div>
                                    <input type="checkbox" class="report-card-checkbox deleted-report-checkbox deleted-checkbox" value="{{ $report->id }}" onchange="deletedReportsUpdateSelectedCount(); updateDeletedBulkActions()">
                                    <span class="report-card-ticket">#{{ str_pad($report->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </div>
                                <span class="badge bg-{{
                                    $report->status == 'Resolved' ? 'success' :
                                    ($report->status == 'In Progress' ? 'warning' :
                                    ($report->status == 'Assigned' ? 'primary' : 'secondary'))
                                }}">
                                    {{ $report->status }}
                                </span>
                            </div>
                            <div class="report-card-body">
                                <div class="report-card-field">
                                    <span class="report-card-label">Issue:</span>
                                    <span class="report-card-value">{{ $report->title ?? \Illuminate\Support\Str::limit($report->description, 40) }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Category:</span>
                                    <span class="report-card-value">{{ $report->category->name ?? 'N/A' }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Location:</span>
                                    <span class="report-card-value">{{ $report->location }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Priority:</span>
                                    <span class="report-card-value">
                                        @if($report->is_safety_hazard)
                                            <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white;">
                                                <i class="fas fa-exclamation-triangle"></i> Safety Hazard
                                            </span>
                                        @else
                                            <span class="badge bg-{{
                                                $report->severity == 'critical' || $report->severity == 'urgent' ? 'danger' :
                                                ($report->severity == 'high' ? 'warning' :
                                                ($report->severity == 'medium' ? 'info' : 'secondary'))
                                            }}">
                                                {{ ($report->severity ? ucfirst($report->severity) : 'Not Set') }}
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Reported By:</span>
                                    <span class="report-card-value">{{ $report->reported_by_name ?? ($report->user ? $report->user->name : 'Unknown') }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Deleted Date:</span>
                                    <span class="report-card-value">{{ $report->updated_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="report-card-field">
                                    <span class="report-card-label">Deleted By:</span>
                                    <span class="report-card-value">{{ $report->deletedBy ? $report->deletedBy->name : 'System' }}</span>
                                </div>
                            </div>
                            <div class="report-card-actions">
                                <form action="{{ route('admin.deletedReports.restore', $report->id) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="fas fa-trash-restore"></i> Restore
                                    </button>
                                </form>
                                <form action="{{ route('admin.deletedReports.permanentDelete', $report->id) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Are you sure you want to permanently delete this report?')">
                                        <i class="fas fa-times-circle"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-check-circle fa-2x d-block mb-3"></i>
                                <h5>No Deleted Concerns</h5>
                                <p class="mb-0">Deleted concerns will appear here. You can delete concerns from the Reports page.</p>
                                <a href="{{ route('admin.reports') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-file-alt"></i> Go to Reports
                                </a>
                            </div>
                        </div>
                    @endforelse
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

<style>
/* Ticket column styling - minimal width, centered - OVERRIDE admin.css */
.ticket-col {
    width: 60px !important;
    max-width: 60px !important;
    min-width: 60px !important;
    white-space: nowrap !important;
    text-align: center !important;
    padding-left: 4px !important;
    padding-right: 4px !important;
    font-size: 0.9em !important;
    overflow: hidden !important;
}

/* Override the nth-child rules from admin.css for ticket column */
.table th.ticket-col,
.table td.ticket-col {
    width: 60px !important;
    min-width: 60px !important;
    max-width: 60px !important;
}
</style>
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
            details += '<tr><td>' + report.location + '</td><td>' + (report.damaged_part || 'N/A') + '</td><td>' + dateFixed + '</td><td>PHP' + (report.cost ? parseFloat(report.cost).toFixed(2) : '0.00') + '</td></tr>';
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
        isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => $s->month, 'title' => $s->title, 'status' => $s->status, 'count' => $s->count, 'ticket_ids' => $s->ticket_ids ?? '', 'damaged_parts' => $s->damaged_parts ?? ''])->values() : [],
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
                    label: 'Total Cost (PHP‚±)',
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
                                return 'PHP‚±' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'PHP‚±' + context.parsed.y.toLocaleString();
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

    // Line Chart for Monthly Trend PHP€” one line per issue type
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx) {
        // Build 6-month label range
        const monthLabels = [];
        for (let i = 5; i >= 0; i--) {
            const d = new Date();
            d.setDate(1);
            d.setMonth(d.getMonth() - i);
            const key = d.toISOString().slice(0, 7); // YYYY-MM
            const label = d.toLocaleDateString('en-PH', { month: 'short', year: 'numeric' });
            monthLabels.push({ key, label });
        }

        // Group data by issue title and keep status breakdown
        const issueMap = {};
        const statusBreakdown = {}; // Store status breakdown for tooltips
        
        window.monthlyData.forEach(item => {
            if (!issueMap[item.title]) {
                issueMap[item.title] = {};
                statusBreakdown[item.title] = {};
            }
            if (!issueMap[item.title][item.month]) {
                issueMap[item.title][item.month] = 0;
                statusBreakdown[item.title][item.month] = {};
            }
            issueMap[item.title][item.month] += item.count;
            
            // Store status breakdown
            if (!statusBreakdown[item.title][item.month][item.status]) {
                statusBreakdown[item.title][item.month][item.status] = 0;
            }
            statusBreakdown[item.title][item.month][item.status] += item.count;
        });

        // Color palette
        const palette = [
            '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0',
            '#9966FF', '#FF9F40', '#22C55E', '#F97316',
            '#7BC8A4', '#EC4899'
        ];

        const datasets = Object.entries(issueMap).map(([title, monthData], idx) => ({
            label: title,
            data: monthLabels.map(m => monthData[m.key] || 0),
            borderColor: palette[idx % palette.length],
            backgroundColor: palette[idx % palette.length] + '22',
            borderWidth: 2.5,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: palette[idx % palette.length],
            tension: 0.3,
            fill: false,
            statusData: statusBreakdown[title] // Attach status breakdown
        }));

        // Plugin to draw issue name label at the last non-zero point of each line
        const endLabelPlugin = {
            id: 'endLabel',
            afterDatasetsDraw(chart) {
                const ctx = chart.ctx;
                chart.data.datasets.forEach((dataset, i) => {
                    const meta = chart.getDatasetMeta(i);
                    if (meta.hidden) return;

                    // Find last point with value > 0
                    let lastIdx = -1;
                    for (let j = dataset.data.length - 1; j >= 0; j--) {
                        if (dataset.data[j] > 0) { lastIdx = j; break; }
                    }
                    if (lastIdx === -1) return;

                    const point = meta.data[lastIdx];
                    const x = point.x + 8;
                    const y = point.y;

                    ctx.save();
                    ctx.font = 'bold 11px sans-serif';
                    ctx.fillStyle = dataset.borderColor;
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(dataset.label, x, y);
                    ctx.restore();
                });
            }
        };

        new Chart(monthlyTrendCtx, {
            type: 'line',
            plugins: [endLabelPlugin],
            data: {
                labels: monthLabels.map(m => m.label),
                datasets: datasets
            },
            options: {
                responsive: true,
                layout: { padding: { right: 90 } }, // space for end labels
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: {
                        ticks: { font: { size: 11 } },
                        grid: { display: false }
                    },
                    y: {
                        min: 0,
                        title: { display: true, text: 'Reports' },
                        ticks: {
                            stepSize: 1,
                            callback: v => v
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,
                            font: { size: 13, weight: 'bold' },
                            generateLabels: chart => chart.data.datasets.map((ds, i) => ({
                                text: ds.label,
                                fillStyle: ds.borderColor,
                                strokeStyle: ds.borderColor,
                                pointStyle: 'circle',
                                hidden: chart.getDatasetMeta(i).hidden,
                                datasetIndex: i
                            }))
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const dataset = ctx.dataset;
                                const monthKey = monthLabels[ctx.dataIndex].key;
                                const total = ctx.parsed.y;
                                const statusData = dataset.statusData[monthKey] || {};
                                
                                const lines = [dataset.label + ': ' + total + (total === 1 ? ' report' : ' reports')];
                                
                                // Add status breakdown if there are multiple statuses
                                const statuses = Object.keys(statusData);
                                if (statuses.length > 0) {
                                    const resolved = statusData['Resolved'] || 0;
                                    const pending = statusData['Pending'] || 0;
                                    const inProgress = statusData['In Progress'] || 0;
                                    
                                    if (resolved > 0) lines.push('  PHPœ“ Resolved: ' + resolved);
                                    if (inProgress > 0) lines.push('  PHPŸ³ In Progress: ' + inProgress);
                                    if (pending > 0) lines.push('  PHP± Pending: ' + pending);
                                }
                                
                                return lines;
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
}); // Close first DOMContentLoaded

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

    fetch('/admin/reports/' + id + '/edit-data', {
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
function confirmModalAction() {
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
    updateDeletedBulkActions();
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
    confirmRestore({
        title: 'Restore Report?',
        text: 'This will move the report back to active reports.',
        confirmButtonText: '<i class="fas fa-trash-restore me-1"></i> Restore'
    }).then(result => {
        if (result.isConfirmed) {
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
    });
}

function deletedReportsContextPermanentDelete() {
    hideDeletedReportsContextMenu();
    confirmPermanentDelete({
        title: 'Permanently Delete Report?',
        html: '<p class="mb-2"><strong>Warning:</strong> This action cannot be undone!</p><p class="text-muted">The report will be permanently removed from the database.</p>',
        confirmButtonText: '<i class="fas fa-ban me-1"></i> Delete Forever'
    }).then(result => {
        if (result.isConfirmed) {
            const modalId = 'deletedReportsPermanentDeleteModal' + currentDeletedReportId;
            const modal = new bootstrap.Modal(document.getElementById(modalId));
            modal.show();
        }
    });
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
    confirmArchive({
        title: 'Archive Report?',
        text: 'This report will be archived and hidden from your active list.',
        confirmButtonText: '<i class="fas fa-archive me-1"></i> Archive'
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Archiving...',
                html: '<div class="spinner-border text-primary"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            fetch('/reports/' + reportId + '/archive', {
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
                    swalToast('Report archived successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    swalAlert(data.error || 'Failed to archive report', 'error', 'Error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                swalAlert('An error occurred while archiving the report', 'error', 'Error');
            });
        }
    });
}

// Confirm Archive (legacy - redirects to new function)
function confirmReportArchive() {
    if (!currentReportId) return;
    showReportArchiveModal(currentReportId);
}

// Show Delete Modal
function showReportDeleteModal(reportId) {
    confirmDelete({
        title: 'Delete Report?',
        text: 'This report will be moved to deleted. You can restore it later.',
        confirmButtonText: '<i class="fas fa-trash me-1"></i> Delete'
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Deleting...',
                html: '<div class="spinner-border text-danger"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            fetch('/reports/' + reportId, {
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
                    swalToast('Report deleted successfully!', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    swalAlert(data.error || 'Failed to delete report', 'error', 'Error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                swalAlert('An error occurred while deleting the report', 'error', 'Error');
            });
        }
    });
}

// Confirm Delete (legacy - redirects to new function)
function confirmReportDelete() {
    if (!currentReportId || isNaN(currentReportId)) {
        swalAlert('Invalid report ID', 'error', 'Error');
        return;
    }
    showReportDeleteModal(currentReportId);
}

// Store current concern ID for assignment
let currentConcernId = null;

// Assign Report directly from table button
window.assignReport = function(id) {
    window.currentReportId = id;
    window.currentConcernId = null;
    window.startAssignWizard();
}

// Single-step assign wizard
window.startAssignWizard = async function() {
    const reportId  = window.currentReportId;
    const concernId = window.currentConcernId;

    if (!reportId && !concernId) return;

    const itemId   = reportId || concernId;
    const itemType = reportId ? 'report' : 'concern';

    // First, fetch the report/concern details to check category
    let reportCategory = null;
    let isTechnologyCategory = false;
    
    try {
        const detailsRes = await fetch('/admin/reports/' + itemId + '/modal-data', {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const detailsData = await detailsRes.json();
        if (detailsData.report && detailsData.report.category) {
            reportCategory = detailsData.report.category.name;
            isTechnologyCategory = reportCategory === 'Technology/Internet';
        }
    } catch(e) {
        console.error('Error fetching report details:', e);
    }

    // Determine which endpoint to use based on category
    const staffEndpoint = isTechnologyCategory ? '/admin/mis-users' : '/admin/maintenance-users';
    const staffLabel = isTechnologyCategory ? 'MIS Staff' : 'Maintenance Staff';

    // Load staff list
    let staffOptions = '<option value="">Loading...</option>';
    try {
        const res   = await fetch(staffEndpoint, {
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            credentials: 'same-origin'
        });
        const data  = await res.json();
        if (data.users && data.users.length) {
            staffOptions = `<option value="">-- Select ${staffLabel.toLowerCase()} --</option>`
                + data.users.map(u => `<option value="${u.id}">${u.name}</option>`).join('');
        } else {
            staffOptions = `<option value="">No ${staffLabel.toLowerCase()} found</option>`;
        }
    } catch(e) {
        staffOptions = '<option value="">Error loading staff</option>';
    }

    // Show single-step assign dialog
    const result = await getSwal().fire({
        title: `Assign ${itemType.charAt(0).toUpperCase() + itemType.slice(1)}`,
        html: `
            <div class="text-start">
                <p class="mb-3">Assign to ${staffLabel}</p>
                <select id="swal-staff-select" class="form-select" style="width:100%;">
                    ${staffOptions}
                </select>
            </div>`,
        confirmButtonText: '<i class="fas fa-user-plus me-1"></i> Assign',
        cancelButtonText: 'Cancel',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        width: 500,
        preConfirm: () => {
            const val = document.getElementById('swal-staff-select').value;
            if (!val) {
                Swal.showValidationMessage('Please select a staff member');
                return false;
            }
            return val;
        }
    });

    if (!result.isConfirmed) return;
    
    const selectedStaffId = result.value;
    const selectedStaffName = document.getElementById('swal-staff-select').options[
        document.getElementById('swal-staff-select').selectedIndex
    ].text;

    // Show loading
    getSwal().fire({
        title: 'Assigning...',
        html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            getSwal().showLoading();
        }
    });

    // Submit assignment
    const formData = new FormData();
    formData.append('assigned_to', selectedStaffId);
    formData.append('notes', '');
    formData.append('_token', '{{ csrf_token() }}');

    try {
        const res  = await fetch('/admin/' + itemType + '/' + itemId + '/assign', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            // Ask building admin to set priority
            let selectedPriority = null;

            await getSwal().fire({
                title: 'Set Priority',
                html: `
                    <p class="mb-3">${itemType.charAt(0).toUpperCase() + itemType.slice(1)} assigned to <strong>${selectedStaffName}</strong>.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-sm swal-priority-btn" data-priority="safety_hazard" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white; border: 2px solid #8b0000; font-weight: bold;">
                            <i class="fas fa-exclamation-triangle me-1"></i> Safety Hazard
                        </button>
                        <button type="button" class="btn btn-danger btn-sm swal-priority-btn" data-priority="urgent">
                            <i class="fas fa-exclamation-circle me-1"></i> Urgent
                        </button>
                        <button type="button" class="btn btn-warning btn-sm swal-priority-btn" data-priority="high">
                            <i class="fas fa-arrow-up me-1"></i> High
                        </button>
                        <button type="button" class="btn btn-info btn-sm swal-priority-btn text-white" data-priority="medium">
                            <i class="fas fa-minus me-1"></i> Medium
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm swal-priority-btn" data-priority="low">
                            <i class="fas fa-arrow-down me-1"></i> Low
                        </button>
                    </div>`,
                showConfirmButton: false,
                showCancelButton: false,
                allowOutsideClick: false,
                didOpen: (popup) => {
                    popup.querySelectorAll('.swal-priority-btn').forEach(btn => {
                        btn.addEventListener('click', () => {
                            selectedPriority = btn.getAttribute('data-priority');
                            getSwal().close();
                        });
                    });
                }
            });

            const priority = selectedPriority || 'medium';

            // Save priority to backend
            const pResponse = await fetch('/admin/' + itemType + '/' + itemId + '/priority', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ priority: priority })
            });
            const pData = await pResponse.json();
            console.log('Priority save result:', pData, 'priority sent:', priority);

            // Format priority display text
            let priorityText = priority;
            if (priority === 'safety_hazard') {
                priorityText = 'Safety Hazard (Urgent)';
            }

            await getSwal().fire({
                icon: 'success',
                title: 'Done!',
                text: 'Assigned to ' + selectedStaffName + ' with ' + priorityText + ' priority.',
                confirmButtonColor: '#198754',
                timer: 2000,
                showConfirmButton: false
            });
            location.reload();
        } else {
            await getSwal().fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Failed to assign ' + itemType,
                confirmButtonColor: '#dc3545'
            });
        }
    } catch(e) {
        await getSwal().fire({
            icon: 'error',
            title: 'Error',
            text: 'Error assigning ' + itemType,
            confirmButtonColor: '#dc3545'
        });
    }
}

// Date formatting function
function formatDate(dateString) {
    const date = new Date(dateString);
    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const month = months[date.getMonth()];
    const day = date.getDate();
    const year = date.getFullYear();
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
}

// View task progress via SweetAlert2 Queue
window.viewReportProgress = async function(id) {
    // Show loading state in queue
    getSwal().fire({
        title: 'Loading Progress...',
        html: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            getSwal().showLoading();
        }
    });

    try {
        const res = await fetch('/admin/reports/' + id + '/modal-data', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        
        if (data.error) {
            // Use queue to show error
            await getSwal().fire({
                icon: 'error',
                title: 'Error',
                text: data.error,
                confirmButtonColor: '#dc3545'
            });
            return;
        }

        const r = data.report;

        const statusColor = r.status === 'Resolved'    ? '#198754' :
                            r.status === 'In Progress'  ? '#ffc107' :
                            r.status === 'Assigned'     ? '#0d6efd' : '#6c757d';

        const steps = [
            { label: 'Submitted',    done: true,                          icon: 'fa-file-alt',    date: r.created_at },
            { label: 'Assigned',     done: !!r.assigned_to,               icon: 'fa-user-plus',   date: r.assigned_at || null, detail: r.assigned_user_name || null },
            { label: 'In Progress',  done: ['In Progress','Resolved'].includes(r.status), icon: 'fa-spinner', date: null },
            { label: 'Resolved',     done: r.status === 'Resolved',       icon: 'fa-check-circle', date: r.resolved_at || null },
        ];

        const timeline = steps.map((s, i) => {
            const color  = s.done ? '#0d6efd' : '#dee2e6';
            const txtCol = s.done ? '#0d6efd' : '#aaa';
            const line   = i < steps.length - 1
                ? `<div style="width:2px;height:28px;background:${s.done && steps[i+1].done ? '#0d6efd' : '#dee2e6'};margin:0 auto;"></div>`
                : '';
            return `
                <div class="d-flex align-items-start gap-3 mb-1">
                    <div style="display:flex;flex-direction:column;align-items:center;min-width:36px;">
                        <div style="width:36px;height:36px;border-radius:50%;background:${color};color:${s.done?'#fff':'#aaa'};display:flex;align-items:center;justify-content:center;font-size:15px;">
                            <i class="fas ${s.icon}"></i>
                        </div>
                        ${line}
                    </div>
                    <div class="text-start pt-1">
                        <div style="font-weight:600;color:${txtCol};">${s.label}</div>
                        ${s.detail ? `<div style="font-size:12px;color:#666;">PHP†’ ${s.detail}</div>` : ''}
                        ${s.date   ? `<div style="font-size:11px;color:#999;">${s.date}</div>` : ''}
                    </div>
                </div>`;
        }).join('');

        // Determine next action based on current status
        let nextAction = null;
        let showProceedButton = false;
        
        if (r.status === 'Assigned') {
            nextAction = { status: 'In Progress', label: 'Start Working', icon: 'fa-play', color: '#ffc107' };
            showProceedButton = true;
        } else if (r.status === 'In Progress') {
            nextAction = { status: 'Resolved', label: 'Mark as Resolved', icon: 'fa-check', color: '#198754' };
            showProceedButton = true;
        }

        // Build buttons configuration
        const buttons = {};
        if (showProceedButton && nextAction) {
            buttons.cancel = {
                text: 'Close',
                value: null,
                visible: true,
                className: 'swal2-cancel',
                closeModal: true
            };
            buttons.confirm = {
                text: `<i class="fas ${nextAction.icon} me-1"></i> ${nextAction.label}`,
                value: 'proceed',
                visible: true,
                className: 'swal2-confirm',
                closeModal: false
            };
        }

        // Use queue to show progress
        const result = await getSwal().fire({
            title: `<span style="font-size:16px;color:#666;">Report #${r.id}</span><br><span style="font-size:20px;">${r.title || (r.description ? r.description.substring(0, 40) : 'No Title')}</span>`,
            html: `
                <div class="mb-3">
                    <span class="badge" style="background:${statusColor};font-size:13px;">${r.status}</span>
                    ${r.assigned_user_name ? `<span class="ms-2 text-muted" style="font-size:13px;"><i class="fas fa-user me-1"></i>${r.assigned_user_name}</span>` : ''}
                </div>
                <div class="p-3 rounded" style="background:#f8f9fa;text-align:left;">
                    ${timeline}
                </div>
                ${r.notes ? `<div class="mt-3 text-start"><small class="text-muted"><strong>Notes:</strong> ${r.notes}</small></div>` : ''}`,
            showCancelButton: showProceedButton,
            confirmButtonText: showProceedButton ? `<i class="fas ${nextAction.icon} me-1"></i> ${nextAction.label}` : 'Close',
            cancelButtonText: 'Close',
            confirmButtonColor: showProceedButton ? nextAction.color : '#6c757d',
            cancelButtonColor: '#6c757d',
            width: 420,
        });

        // Handle proceed action
        if (result.isConfirmed && showProceedButton && nextAction) {
            await proceedReportToNextLevel(id, nextAction.status, r.title || (r.description ? r.description.substring(0, 40) : 'Report #' + r.id));
        } else if (result.isDismissed || !result.isConfirmed) {
            // Reload page when user clicks Close or dismisses the modal
            location.reload();
        }
    } catch (error) {
        // Use queue to show error
        await getSwal().fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load report progress. Please try again.',
            confirmButtonColor: '#dc3545'
        });
        console.error('Error loading report progress:', error);
    }
};

// Function to proceed report to next level
async function proceedReportToNextLevel(reportId, newStatus, reportTitle) {
    // Show confirmation based on status
    let confirmTitle = '';
    let confirmText = '';
    let confirmIcon = '';
    let confirmColor = '';
    
    if (newStatus === 'In Progress') {
        confirmTitle = 'Start Working on Report?';
        confirmText = `This will mark "${reportTitle}" as In Progress.`;
        confirmIcon = 'question';
        confirmColor = '#ffc107';
        
        const confirm = await getSwal().fire({
            title: confirmTitle,
            text: confirmText,
            icon: confirmIcon,
            showCancelButton: true,
            confirmButtonText: 'Yes, Proceed',
            cancelButtonText: 'Cancel',
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d'
        });

        if (!confirm.isConfirmed) {
            // Re-open the progress view if user cancels
            await viewReportProgress(reportId);
            return;
        }
        
        // Proceed with status update (no additional data needed)
        await updateReportStatus(reportId, newStatus, null, reportTitle);
        
    } else if (newStatus === 'Resolved') {
        // Show resolution form for collecting details
        const result = await getSwal().fire({
            title: 'Mark Report as Resolved',
            html: `
                <div class="text-start">
                    <p class="mb-3">Please provide resolution details for "${reportTitle}":</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cost (PHP‚±)</label>
                        <input type="number" id="swal-cost" class="form-control" placeholder="0.00" step="0.01" min="0">
                        <small class="text-muted">Enter the total cost of repair/replacement</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Damaged Part</label>
                        <input type="text" id="swal-damaged-part" class="form-control" placeholder="e.g., Door hinge">
                        <small class="text-muted">What was damaged or broken?</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Replaced With</label>
                        <input type="text" id="swal-replaced-part" class="form-control" placeholder="e.g., New stainless steel hinge">
                        <small class="text-muted">What part was used for replacement?</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Resolution Notes</label>
                        <textarea id="swal-resolution-notes" class="form-control" rows="3" placeholder="Additional details about the resolution..."></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check me-1"></i> Mark as Resolved',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            width: 600,
            preConfirm: () => {
                const cost = document.getElementById('swal-cost').value;
                const damagedPart = document.getElementById('swal-damaged-part').value;
                const replacedPart = document.getElementById('swal-replaced-part').value;
                const resolutionNotes = document.getElementById('swal-resolution-notes').value;
                
                return {
                    cost: cost ? parseFloat(cost) : null,
                    damaged_part: damagedPart.trim() || null,
                    replaced_part: replacedPart.trim() || null,
                    resolution_notes: resolutionNotes.trim() || null
                };
            }
        });

        if (!result.isConfirmed) {
            // Re-open the progress view if user cancels
            await viewReportProgress(reportId);
            return;
        }
        
        // Proceed with status update including resolution data
        await updateReportStatus(reportId, newStatus, result.value, reportTitle);
    }
}

// Helper function to update report status
async function updateReportStatus(reportId, newStatus, resolutionData, reportTitle) {
    // Show loading
    getSwal().fire({
        title: 'Updating Status...',
        html: '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            getSwal().showLoading();
        }
    });

    try {
        const requestBody = { status: newStatus };
        
        // Add resolution data if provided
        if (resolutionData) {
            Object.assign(requestBody, resolutionData);
        }
        
        const response = await fetch(`/reports/${reportId}/update-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestBody)
        });

        const data = await response.json();

        if (data.success) {
            // Show success message briefly
            await getSwal().fire({
                icon: 'success',
                title: 'Success!',
                text: `Report has been updated to ${newStatus}.`,
                confirmButtonColor: '#198754',
                timer: 1500,
                showConfirmButton: false
            });
            
            // Refresh the progress view to show updated status
            await viewReportProgress(reportId);
        } else {
            await getSwal().fire({
                icon: 'error',
                title: 'Error',
                text: data.error || 'Failed to update report status.',
                confirmButtonColor: '#dc3545'
            });
            // Re-open the progress view after error
            await viewReportProgress(reportId);
        }
    } catch (error) {
        await getSwal().fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to update report status. Please try again.',
            confirmButtonColor: '#dc3545'
        });
        console.error('Error updating report status:', error);
        // Re-open the progress view after error
        await viewReportProgress(reportId);
    }
}

// View report function for Building Admin
// View Report Modal using SweetAlert2
function viewReport(id) {
    window.currentReportId = id; // Store the current report ID
    
    // Show loading state
    Swal.fire({
        title: '<i class="fas fa-file-alt me-2"></i>Loading Report Details...',
        html: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false,
        width: '900px'
    });

    fetch('/admin/reports/' + id + '/modal-data', {
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
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error
            });
            return;
        }

        const report = data.report;
        
        // Check if it's a safety hazard
        let priorityBadge = '';
        if (report.is_safety_hazard) {
            priorityBadge = '<span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white; border: 1px solid #8b0000; font-weight: bold;"><i class="fas fa-exclamation-triangle"></i> Safety Hazard</span>';
        } else {
            const severityClass = report.severity === 'critical' || report.severity === 'urgent' ? 'danger' :
                (report.severity === 'high' ? 'warning' :
                (report.severity === 'medium' ? 'info' : 'secondary'));
            const severityText = (report.severity && report.severity !== '') ? report.severity.charAt(0).toUpperCase() + report.severity.slice(1) : 'Not Set';
            priorityBadge = '<span class="badge bg-' + severityClass + '">' + severityText + ' Priority</span>';
        }

        const statusClass = report.status === 'Resolved' ? 'success' :
            (report.status === 'In Progress' ? 'warning' :
            (report.status === 'Assigned' ? 'primary' : 'secondary'));

        let imageHtml = '';
        if (report.photo_path) {
            imageHtml = `
                <div class="mb-3 text-center">
                    <p class="text-start"><strong>Photo:</strong></p>
                    <img src="${report.photo_path}" alt="Report photo" class="img-fluid rounded" style="max-width: 100%; max-height: 400px;">
                </div>
            `;
        }

        let resolutionHtml = '';
        if (report.resolution_notes) {
            resolutionHtml = `
                <div class="alert alert-success mt-3 text-start">
                    <h6><strong>Resolution Notes:</strong></h6>
                    <p class="mb-1">${report.resolution_notes}</p>
                </div>
            `;
        }

        const htmlContent = `
            <div style="text-align: left; max-height: 70vh; overflow-y: auto; padding: 10px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">${report.title || (report.description ? report.description.substring(0, 40) + '...' : 'No Title')}</h5>
                    <div>
                        ${priorityBadge}
                        <span class="badge bg-${statusClass} ms-2">${report.status}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Report #:</strong> #${String(report.id).padStart(4, '0')}</p>
                        <p><strong>Category:</strong> ${report.category ? report.category.name : 'N/A'}</p>
                        <p><strong>Location:</strong> ${report.location || 'N/A'}</p>
                        ${report.damaged_part ? '<p><strong>Damaged Part:</strong> ' + report.damaged_part + '</p>' : ''}
                    </div>
                    <div class="col-md-6">
                        <p><strong>Reported by:</strong> ${report.reported_by_name || 'Unknown'}</p>
                        <p><strong>Date Submitted:</strong> ${report.created_at || 'N/A'}</p>
                        ${report.assigned_at ? '<p><strong>Date Assigned:</strong> ' + report.assigned_at + '</p>' : ''}
                        ${report.in_progress_at ? '<p><strong>Date In Progress:</strong> ' + report.in_progress_at + '</p>' : ''}
                        ${report.resolved_at ? '<p><strong>Date Resolved:</strong> ' + report.resolved_at + '</p>' : ''}
                        ${report.assigned_to ? '<p><strong>Assigned to:</strong> ' + (report.assigned_user_name || 'Unknown') + '</p>' : ''}
                        ${report.cost ? '<p><strong>Cost:</strong> ₱' + parseFloat(report.cost).toLocaleString('en-PH', {minimumFractionDigits: 2}) + '</p>' : ''}
                        ${report.replaced_part ? '<p><strong>Replaced With:</strong> ' + report.replaced_part + '</p>' : ''}
                    </div>
                </div>
                <hr>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Description:</strong></p>
                        <p style="white-space: pre-wrap;">${report.description || 'No description provided'}</p>
                        ${imageHtml}
                        ${resolutionHtml}
                    </div>
                </div>
            </div>
        `;
        
        Swal.fire({
            title: '<i class="fas fa-file-alt me-2"></i>Report #' + String(report.id).padStart(4, '0') + ' Details',
            html: htmlContent,
            width: '900px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            confirmButtonColor: '#6c757d',
            customClass: {
                popup: 'swal-wide-popup',
                htmlContainer: 'swal2-html-container'
            }
        });
    })
    .catch(error => {
        console.error('viewReport error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error loading report details: ' + error.message
        });
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
</script>

<script>
// Checkbox functions for bulk actions
function toggleAllReports(checkbox) {
    const checkboxes = document.querySelectorAll('.report-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateActiveBulkActions();
}

function getSelectedReports() {
    const checkboxes = document.querySelectorAll('.report-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Checkbox functions for resolved reports
function toggleAllResolvedReports(checkbox) {
    const checkboxes = document.querySelectorAll('.resolved-report-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateResolvedBulkActions();
}

function getSelectedResolvedReports() {
    const checkboxes = document.querySelectorAll('.resolved-report-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Update bulk actions visibility for Active Reports
function updateActiveBulkActions() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const bulkActions = document.getElementById('activeBulkActions');
    const countSpan = document.getElementById('activeSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Update bulk actions visibility for Resolved Reports
function updateResolvedBulkActions() {
    const selected = document.querySelectorAll('.resolved-checkbox:checked');
    const bulkActions = document.getElementById('resolvedBulkActions');
    const countSpan = document.getElementById('resolvedSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Batch archive selected active reports
function batchArchiveSelected() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to archive ' + ids.length + ' report(s)?')) {
        fetch('/concerns/batch-archive', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error archiving reports');
        });
    }
}

// Batch soft delete selected active reports
function batchSoftDeleteSelected() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to delete ' + ids.length + ' report(s)?')) {
        fetch('/concerns/batch-soft-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting reports');
        });
    }
}

// Batch archive resolved reports
function batchArchiveResolved() {
    const selected = document.querySelectorAll('.resolved-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to archive ' + ids.length + ' resolved report(s)?')) {
        fetch('/concerns/batch-archive', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while archiving reports.');
        });
    }
}

// Batch soft delete resolved reports
function batchSoftDeleteResolved() {
    const selected = document.querySelectorAll('.resolved-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to delete ' + ids.length + ' resolved report(s)? They will be moved to the deleted folder.')) {
        fetch('/concerns/batch-soft-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting reports.');
        });
    }
}

// Update bulk actions visibility for Archived Reports
function updateArchiveBulkActions() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const bulkActions = document.getElementById('archiveBulkActions');
    const countSpan = document.getElementById('archiveSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Toggle all archived checkboxes
function toggleAllArchived(checkbox) {
    const checkboxes = document.querySelectorAll('.archive-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateArchiveBulkActions();
}

// Batch restore archived reports
function batchRestoreArchived() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to restore ' + ids.length + ' archived report(s)?')) {
        fetch('/concerns/batch-restore', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while restoring reports.');
        });
    }
}

// Batch soft delete archived reports
function batchSoftDeleteArchived() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to delete ' + ids.length + ' archived report(s)? They will be moved to the deleted folder.')) {
        fetch('/concerns/batch-soft-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting reports.');
        });
    }
}

// Update bulk actions visibility for Deleted Reports
function updateDeletedBulkActions() {
    const selected = document.querySelectorAll('.deleted-checkbox:checked');
    const bulkActions = document.getElementById('deletedBulkActions');
    const countSpan = document.getElementById('deletedSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Batch restore deleted reports
function batchRestoreDeleted() {
    const selected = document.querySelectorAll('.deleted-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to restore ' + ids.length + ' deleted report(s)?')) {
        fetch('/concerns/batch-restore-deleted', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while restoring reports.');
        });
    }
}

// Batch permanent delete reports
function batchPermanentDelete() {
    const selected = document.querySelectorAll('.deleted-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('⚠️ WARNING: Are you sure you want to PERMANENTLY DELETE ' + ids.length + ' report(s)? This action CANNOT be undone!')) {
        if (confirm('This is your final warning. The selected reports will be permanently deleted. Continue?')) {
            fetch('/concerns/batch-permanent-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            }).then(response => response.json())
            .then(data => {
                alert(data.message);
                location.reload();
            }).catch(error => {
                console.error('Error:', error);
                alert('An error occurred while permanently deleting reports.');
            });
        }
    }
}

// View Report from table button using SweetAlert2
function viewReport(id) {
    window.currentReportId = id;
    
    // Show loading state
    Swal.fire({
        title: '<i class="fas fa-file-alt me-2"></i>Loading Report Details...',
        html: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false,
        width: '900px'
    });

    fetch('/admin/reports/' + id + '/modal-data', {
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
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.error
            });
            return;
        }
        const report = data.report;
        
        // Check if it's a safety hazard
        let priorityBadge2 = '';
        if (report.is_safety_hazard) {
            priorityBadge2 = '<span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #8b0000 100%); color: white; border: 1px solid #8b0000; font-weight: bold;"><i class="fas fa-exclamation-triangle"></i> Safety Hazard</span>';
        } else {
            const severityClass = report.severity === 'critical' || report.severity === 'urgent' ? 'danger' :
                (report.severity === 'high' ? 'warning' :
                (report.severity === 'medium' ? 'info' : 'secondary'));
            const severityText = (report.severity && report.severity !== '') ? report.severity.charAt(0).toUpperCase() + report.severity.slice(1) : 'Not Set';
            priorityBadge2 = '<span class="badge bg-' + severityClass + '">' + severityText + ' Priority</span>';
        }
        
        const statusClass = report.status === 'Resolved' ? 'success' :
            (report.status === 'In Progress' ? 'warning' :
            (report.status === 'Assigned' ? 'primary' : 'secondary'));
        
        let imageHtml = '';
        if (report.photo_path) {
            imageHtml = `
                <div class="mb-3 text-center">
                    <p class="text-start"><strong>Photo:</strong></p>
                    <img src="${report.photo_path}" alt="Report photo" class="img-fluid rounded" style="max-width: 100%; max-height: 400px;">
                </div>
            `;
        }

        let resolutionHtml = '';
        if (report.resolution_notes) {
            resolutionHtml = `
                <div class="alert alert-success mt-3 text-start">
                    <h6><strong>Resolution Notes:</strong></h6>
                    <p class="mb-1">${report.resolution_notes}</p>
                </div>
            `;
        }

        const htmlContent = `
            <div style="text-align: left; max-height: 70vh; overflow-y: auto; padding: 10px;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">${report.title || (report.description ? report.description.substring(0, 40) + '...' : 'No Title')}</h5>
                    <div>
                        ${priorityBadge2}
                        <span class="badge bg-${statusClass} ms-2">${report.status}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Report #:</strong> #${String(report.id).padStart(4, '0')}</p>
                        <p><strong>Category:</strong> ${report.category ? report.category.name : 'N/A'}</p>
                        <p><strong>Location:</strong> ${report.location || 'N/A'}</p>
                        ${report.damaged_part ? '<p><strong>Damaged Part:</strong> ' + report.damaged_part + '</p>' : ''}
                    </div>
                    <div class="col-md-6">
                        <p><strong>Reported by:</strong> ${report.reported_by_name || 'Unknown'}</p>
                        <p><strong>Date Submitted:</strong> ${report.created_at || 'N/A'}</p>
                        ${report.assigned_at ? '<p><strong>Date Assigned:</strong> ' + report.assigned_at + '</p>' : ''}
                        ${report.in_progress_at ? '<p><strong>Date In Progress:</strong> ' + report.in_progress_at + '</p>' : ''}
                        ${report.resolved_at ? '<p><strong>Date Resolved:</strong> ' + report.resolved_at + '</p>' : ''}
                        ${report.assigned_to ? '<p><strong>Assigned to:</strong> ' + (report.assigned_user_name || 'Unknown') + '</p>' : ''}
                        ${report.cost ? '<p><strong>Cost:</strong> ₱' + parseFloat(report.cost).toLocaleString('en-PH', {minimumFractionDigits: 2}) + '</p>' : ''}
                        ${report.replaced_part ? '<p><strong>Replaced With:</strong> ' + report.replaced_part + '</p>' : ''}
                    </div>
                </div>
                <hr>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Description:</strong></p>
                        <p style="white-space: pre-wrap;">${report.description || 'No description provided'}</p>
                        ${imageHtml}
                        ${resolutionHtml}
                    </div>
                </div>
            </div>
        `;
        
        Swal.fire({
            title: '<i class="fas fa-file-alt me-2"></i>Report #' + String(report.id).padStart(4, '0') + ' Details',
            html: htmlContent,
            width: '900px',
            showCloseButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Close',
            confirmButtonColor: '#6c757d',
            customClass: {
                popup: 'swal-wide-popup',
                htmlContainer: 'swal2-html-container'
            }
        });
    })
    .catch(function(error) {
        console.error('viewReport error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error loading report details: ' + error.message
        });
    });
}

// Auto-submit filter form when dropdowns change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('reportsFilterForm');
    const autoSubmitFilters = document.querySelectorAll('.auto-submit-filter');
    
    autoSubmitFilters.forEach(function(filter) {
        filter.addEventListener('change', function() {
            filterForm.submit();
        });
    });
});
</script>

@endsection
