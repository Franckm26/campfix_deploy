@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-clipboard-list"></i> My Assigned Reports</h2>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-4">
        <div class="col-md-6">
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Tabs -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link {{ ($viewType ?? 'active') == 'active' ? 'active' : '' }}" href="{{ route('reports.assigned', ['view' => 'active']) }}">
                        <i class="fas fa-list"></i> Active ({{ $activeCount ?? 0 }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ ($viewType ?? '') == 'archives' ? 'active' : '' }}" href="{{ route('reports.assigned', ['view' => 'archives']) }}">
                        <i class="fas fa-archive"></i> Archived ({{ $archiveCount ?? 0 }})
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.assigned') }}" class="row g-2 align-items-end">
                <input type="hidden" name="view" value="{{ $viewType ?? 'active' }}">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="severity" class="form-select">
                        <option value="">All Priority</option>
                        <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search reports..." value="{{ request('search') }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('reports.assigned') }}" class="btn btn-secondary"><i class="fas fa-times"></i> Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card">
        <div class="card-body">
            @if($reports->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-sm" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>
                                <th style="width: 50px; padding: 0.3rem;">Issue</th>
                                <th style="width: 65px; padding: 0.3rem;">Category</th>
                                <th style="width: 70px; padding: 0.3rem;">Location</th>
                                <th style="width: 65px; padding: 0.3rem;">Priority</th>
                                <th style="width: 70px; padding: 0.3rem;">Status</th>
                                <th style="width: 80px; padding: 0.3rem;">Reported By</th>
                                <th style="width: 110px; padding: 0.3rem;">Assigned At</th>
                                <th style="width: 80px; padding: 0.3rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reports as $report)
                                <tr>
                                    <td class="text-truncate" style="max-width: 50px; padding: 0.3rem;" title="{{ $report->title ?? 'No Issue' }}">{{ $report->title ?? 'No Issue' }}</td>
                                    <td class="text-truncate" style="max-width: 65px; padding: 0.3rem;" title="{{ $report->category->name ?? 'N/A' }}">{{ $report->category->name ?? 'N/A' }}</td>
                                    <td class="text-truncate" style="max-width: 70px; padding: 0.3rem;" title="{{ $report->location }}">{{ $report->location }}</td>
                                    <td style="padding: 0.3rem;">
                                        <span class="badge bg-{{
                                            $report->severity == 'critical' ? 'danger' :
                                            ($report->severity == 'high' ? 'warning' :
                                            ($report->severity == 'medium' ? 'info' : 'secondary'))
                                        }}" style="font-size: 0.65rem; padding: 0.2rem 0.3rem;">
                                            {{ ucfirst($report->severity) }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.3rem;">
                                        <span class="badge bg-{{
                                            $report->status == 'Resolved' ? 'success' :
                                            ($report->status == 'In Progress' ? 'warning' :
                                            ($report->status == 'Assigned' ? 'primary' : 'secondary'))
                                        }}" style="font-size: 0.65rem; padding: 0.2rem 0.3rem;">
                                            {{ $report->status }}
                                        </span>
                                    </td>
                                    <td class="text-truncate" style="max-width: 80px; padding: 0.3rem;" title="{{ $report->user->name ?? 'Unknown' }}">{{ $report->user->name ?? 'Unknown' }}</td>
                                    <td style="font-size: 0.75rem; padding: 0.3rem;">{{ $report->assigned_at ? $report->assigned_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                    <td style="padding: 0.3rem;">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info btn-sm" style="padding: 0.2rem 0.4rem;" onclick="viewReport({{ $report->id }})" title="View">
                                                <i class="fas fa-eye" style="font-size: 0.7rem;"></i>
                                            </button>
                                            @if($viewType === 'active')
                                                @if($report->status === 'In Progress')
                                                    <button type="button" class="btn btn-warning btn-sm" style="padding: 0.2rem 0.4rem;" onclick="updateStatus({{ $report->id }})" title="Update Status">
                                                        <i class="fas fa-edit" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                @endif
                                                <form action="{{ route('reports.archive', $report) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to archive this report?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary btn-sm" style="padding: 0.2rem 0.4rem;" title="Archive">
                                                        <i class="fas fa-archive" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                </form>
                                            @elseif($viewType === 'archives')
                                                <form action="{{ route('reports.restore', $report) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to restore this report?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.4rem;" title="Restore">
                                                        <i class="fas fa-undo" style="font-size: 0.7rem;"></i>
                                                    </button>
                                                </form>
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
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    @if($viewType === 'archives')
                        <h4>No Archived Reports</h4>
                        <p class="text-muted">You don't have any archived reports yet.</p>
                    @else
                        <h4>No Assigned Reports</h4>
                        <p class="text-muted">You don't have any reports assigned to you yet.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportModalLabel">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewReportContent">
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

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateStatusModalLabel">Update Report Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateStatusForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="statusReportId" name="id" value="">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="In Progress">In Progress</option>
                            <option value="Resolved">Resolved</option>
                        </select>
                    </div>
                    <div class="mb-3" id="resolutionNotesGroup" style="display: none;">
                        <label for="resolution_notes" class="form-label">Resolution Notes</label>
                        <textarea class="form-control" id="resolution_notes" name="resolution_notes" rows="3" placeholder="Describe how the issue was resolved..."></textarea>
                    </div>
                    <div id="maintenanceFields">
                        <div class="row">
                            <div class="col-md-6 mb-3" id="costField" style="display: none;">
                                <label for="cost" class="form-label">Cost (PHP)</label>
                                <input type="number" class="form-control" id="cost" name="cost" step="0.01" min="0" placeholder="0.00">
                            </div>
                            <div class="col-md-6 mb-3" id="damagedPartField" style="display: none;">
                                <label for="damaged_part" class="form-label">Damaged Part</label>
                                <input type="text" class="form-control" id="damaged_part" name="damaged_part" placeholder="What part was damaged?">
                            </div>
                        </div>
                        <div class="mb-3" id="replacedPartField" style="display: none;">
                            <label for="replaced_part" class="form-label">Replaced With</label>
                            <input type="text" class="form-control" id="replaced_part" name="replaced_part" placeholder="What was it replaced with?">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function viewReport(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewReportModal'));
    const contentDiv = document.getElementById('viewReportModalLabel');
    const bodyDiv = document.getElementById('viewReportContent');

    contentDiv.textContent = 'Report Details';
    bodyDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/api/reports/' + id, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
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
                    '<div class="col-md-6"><p><strong>Reported by:</strong> ' + (report.user ? report.user.name : 'Unknown') + '</p><p><strong>Date:</strong> ' + formatDate(report.created_at) + '</p></div>' +
                '</div>' +
                (report.assigned_to ? '<div class="mb-3"><p><strong>Assigned to:</strong> ' + (report.assigned_user_name || 'Unknown') + '</p></div>' : '') +
                '<div class="mb-3"><p><strong>Description:</strong></p><p>' + report.description + '</p></div>' +
                (report.damaged_part ? '<div class="mb-3"><p><strong>Damaged Part:</strong> ' + report.damaged_part + '</p></div>' : '') +
                imageHtml +
                (report.resolution_notes ? '<div class="mb-3"><p><strong>Resolution Notes:</strong></p><p>' + report.resolution_notes + '</p></div>' : '') +
                ((report.cost || report.damaged_part || report.replaced_part) ? '<div class="mb-3"><p><strong>Maintenance Details:</strong></p><div class="row"><div class="col-md-4">' + (report.cost ? '<p><strong>Cost:</strong> ₱' + parseFloat(report.cost).toFixed(2) + '</p>' : '') + '</div><div class="col-md-4">' + (report.damaged_part ? '<p><strong>Damaged Part:</strong> ' + report.damaged_part + '</p>' : '') + '</div><div class="col-md-4">' + (report.replaced_part ? '<p><strong>Replaced With:</strong> ' + report.replaced_part + '</p>' : '') + '</div></div></div>' : '') +
            '</div>' +
        '</div>';

        // Update modal footer with acknowledge button if status is Assigned
        const modalFooter = document.querySelector('#viewReportModal .modal-footer');
        if (report.status === 'Assigned') {
            modalFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>' +
                '<form action="/reports/' + report.id + '/acknowledge" method="POST" class="d-inline ms-2">' +
                '<input type="hidden" name="_token" value="{{ csrf_token() }}">' +
                '<button type="submit" class="btn btn-success">' +
                '<i class="fas fa-check"></i> Acknowledge' +
                '</button>' +
                '</form>';
        } else {
            modalFooter.innerHTML = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
        }
    })
    .catch(error => {
        bodyDiv.innerHTML = '<div class="alert alert-danger">Error loading report details</div>';
    });
}

function updateStatus(id) {
    document.getElementById('statusReportId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    modal.show();
    // Trigger field visibility check when modal opens
    updateFieldVisibility();
}

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

function updateFieldVisibility() {
    const statusSelect = document.getElementById('status');
    const resolutionNotesGroup = document.getElementById('resolutionNotesGroup');
    const costField = document.getElementById('costField');
    const damagedPartField = document.getElementById('damagedPartField');
    const replacedPartField = document.getElementById('replacedPartField');

    if (statusSelect.value === 'Resolved') {
        resolutionNotesGroup.style.display = 'block';
        costField.style.display = 'block';
        damagedPartField.style.display = 'none';
        replacedPartField.style.display = 'block';
    } else if (statusSelect.value === 'In Progress') {
        resolutionNotesGroup.style.display = 'none';
        costField.style.display = 'none';
        damagedPartField.style.display = 'block';
        replacedPartField.style.display = 'none';
    } else {
        resolutionNotesGroup.style.display = 'none';
        costField.style.display = 'none';
        damagedPartField.style.display = 'none';
        replacedPartField.style.display = 'none';
    }
}

document.getElementById('status').addEventListener('change', updateFieldVisibility);

document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const reportId = formData.get('id');

    fetch('/update-report-status/' + reportId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('updateStatusModal')).hide();
            location.reload();
        } else {
            alert(data.error || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
});
</script>
@endsection