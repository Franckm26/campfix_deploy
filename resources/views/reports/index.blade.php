@extends('layouts.app')

@section('styles')
<style>
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        /* Hide tables on mobile */
        .card-body .table-responsive {
            display: none !important;
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
        
        .report-card-title {
            font-weight: 600;
            font-size: 14px;
            color: #333;
            flex: 1;
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
            min-width: 80px;
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
        
        .report-card-actions form {
            flex: 1;
            display: flex;
        }
        
        .report-card-actions form .btn {
            width: 100%;
        }
        
        /* Dark mode support */
        [data-bs-theme="dark"] .report-card {
            background: #2d3238;
            border-color: #495057;
        }
        
        [data-bs-theme="dark"] .report-card-header {
            border-bottom-color: #495057;
        }
        
        [data-bs-theme="dark"] .report-card-title,
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
<h2>Reports Management</h2>
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

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" id="reportTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="active-tab" data-bs-toggle="tab" data-bs-target="#active-reports"
                    type="button" role="tab" aria-controls="active-reports" aria-selected="true">
                <i class="fas fa-list"></i> Reported Issues
                @if(isset($reports) && $reports->count() > 0)
                    <span class="badge bg-primary ms-1">{{ $reports->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted-reports"
                    type="button" role="tab" aria-controls="deleted-reports" aria-selected="false">
                <i class="fas fa-trash"></i> Deleted Reports
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="reportTabContent">

        <!-- Active Reports Tab -->
        <div class="tab-pane fade show active" id="active-reports" role="tabpanel" aria-labelledby="active-tab">
            @if($reports->count() > 0)
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                     <tr>
                                         <th class="text-nowrap" style="min-width: 150px;">Title</th>
                                         <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                         <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                         <th class="text-nowrap" style="min-width: 100px;">Priority</th>
                                         <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                         <th class="text-nowrap" style="min-width: 100px;">Created</th>
                                         <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                     </tr>
                                 </thead>
                                <tbody>
                                    @foreach($reports as $report)
                                        <tr>
                                            <td>{{ $report->title }}</td>
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
                                            <td>{{ $report->created_at->format('m/d/Y') }}</td>
                                            <td>
                                                <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-info bg-transparent border-0">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if($report->status !== 'Resolved')
                                                    <a href="{{ route('reports.edit', $report) }}" class="btn btn-sm btn-warning bg-transparent border-0">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('reports.archive', $report) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to archive this report?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-secondary bg-transparent border-0">
                                                            <i class="fas fa-archive"></i> Archive
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('reports.destroy', $report) }}" method="POST" class="d-inline"
                                                          onsubmit="return confirm('Are you sure you want to delete this report?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card Layout for Reports -->
                        <div class="mobile-report-cards">
                            @foreach($reports as $report)
                                <div class="report-card">
                                    <div class="report-card-header">
                                        <span class="report-card-title">{{ $report->title }}</span>
                                        <span class="badge bg-{{ $report->status === 'Resolved' ? 'success' : ($report->status === 'In Progress' ? 'info' : 'primary') }}">
                                            {{ $report->status }}
                                        </span>
                                    </div>
                                    <div class="report-card-body">
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
                                                <span class="badge bg-{{ $report->severity === 'critical' ? 'danger' : ($report->severity === 'high' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($report->severity) }}
                                                </span>
                                            </span>
                                        </div>
                                        <div class="report-card-field">
                                            <span class="report-card-label">Created:</span>
                                            <span class="report-card-value">{{ $report->created_at->format('m/d/Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="report-card-actions">
                                        <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        @if($report->status !== 'Resolved')
                                            <a href="{{ route('reports.edit', $report) }}" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <form action="{{ route('reports.archive', $report) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this report?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-archive"></i> Archive
                                                </button>
                                            </form>
                                            <form action="{{ route('reports.destroy', $report) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this report?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center">
                        <p>No active reports found.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Deleted Reports Tab -->
        <div class="tab-pane fade" id="deleted-reports" role="tabpanel" aria-labelledby="deleted-tab">
            <!-- Dropdown for auto-delete period -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="mb-0">Auto-delete reports deleted for more than:</h6>
                        </div>
                        <div class="col-md-4">
                            <select id="autoDeleteDays" class="form-select" onchange="updateAutoDeletePeriod(this.value)">
                                <option value="3">3 days</option>
                                <option value="7">7 days</option>
                                <option value="15" selected>15 days</option>
                                <option value="30">30 days</option>
                            </select>
                        </div>
                    </div>
                    <small class="text-muted">Reports deleted for longer than the selected period will be automatically removed.</small>
                </div>
            </div>

            <!-- Deleted Reports Content will be loaded here -->
            <div id="deletedReportsContent">
                <div class="card">
                    <div class="card-body text-center">
                        <p>Loading deleted reports...</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function updateAutoDeletePeriod(days) {
    // Here you could implement AJAX call to update the auto-delete period setting
    // For now, just show a confirmation
    if (confirm('Set auto-delete period to ' + days + ' days? Reports deleted longer than this will be automatically removed.')) {
        // TODO: Implement AJAX call to save the setting
        alert('Auto-delete period updated to ' + days + ' days');
    } else {
        // Reset the dropdown
        document.getElementById('autoDeleteDays').value = '15';
    }
}

// Load deleted reports when tab is clicked
document.getElementById('deleted-tab').addEventListener('click', function() {
    // TODO: Implement AJAX loading of deleted reports
    document.getElementById('deletedReportsContent').innerHTML = `
        <div class="card">
            <div class="card-body text-center">
                <p>Deleted reports will be displayed here.</p>
                <p class="text-muted">Reports deleted for more than the selected period above will be automatically removed.</p>
            </div>
        </div>
    `;
});
</script>
@endsection
