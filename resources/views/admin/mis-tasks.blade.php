@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-tasks"></i> MIS Task Module</h2>
<p>Manage concerns assigned to MIS department</p>
@endsection

@section('content')
<div class="container-fluid px-2 px-md-3">
    <div class="row mb-3 mb-md-4">
        <div class="col-12">
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $viewType === 'active' ? 'active' : '' }}" href="{{ route('admin.mis-tasks', ['view' => 'active']) }}">
                <i class="fas fa-list"></i> Active
                @if($concerns->count() > 0)
                    <span class="badge bg-primary ms-1">{{ $concerns->count() }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $viewType === 'resolved' ? 'active' : '' }}" href="{{ route('admin.mis-tasks', ['view' => 'resolved']) }}">
                <i class="fas fa-check-circle"></i> Resolved
                @if(isset($resolvedConcerns) && $resolvedConcerns->count() > 0)
                    <span class="badge bg-success ms-1">{{ $resolvedConcerns->count() }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $viewType === 'archives' ? 'active' : '' }}" href="{{ route('admin.mis-tasks', ['view' => 'archives']) }}">
                <i class="fas fa-archive"></i> Archives
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $viewType === 'deleted' ? 'active' : '' }}" href="{{ route('admin.mis-tasks', ['view' => 'deleted']) }}">
                <i class="fas fa-trash"></i> Deleted
            </a>
        </li>
    </ul>

    <!-- Concerns Table -->
    <div class="card">
        <div class="card-header py-2 py-md-3">
            <h5 class="mb-0">Assigned Concerns</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="checkbox-col"><input type="checkbox" id="selectAllMisTasks" onclick="toggleAllMisTasks(this)"></th>
                            <th>Title</th>
                            <th>Requester</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            @if($viewType === 'resolved')
                                <th>Resolved At</th>
                            @elseif($viewType === 'archives')
                                <th>Archived At</th>
                            @elseif($viewType === 'deleted')
                                <th>Deleted At</th>
                            @endif
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($viewType === 'resolved' && isset($resolvedConcerns))
                            @forelse($resolvedConcerns as $concern)
                                <tr data-id="{{ $concern->id }}">
                                    <td class="checkbox-col"><input type="checkbox" class="mistask-checkbox" value="{{ $concern->id }}"></td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 120px;">
                                            {{ $concern->title ?? 'No Title' }}
                                        </span>
                                        @if($concern->image_path)
                                            <i class="fas fa-image text-muted ms-1" title="Has photo"></i>
                                        @endif
                                    </td>
                                    <td>{{ $concern->user->name ?? 'N/A' }}</td>
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
                                        <span class="badge bg-success">
                                            {{ $concern->status }}
                                        </span>
                                    </td>
                                    <td>{{ $concern->created_at->format('M d, Y') }}</td>
                                    <td>{{ $concern->updated_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info bg-transparent border-0" onclick="viewConcern({{ $concern->id }})" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" onclick="showArchiveModal({{ $concern->id }})" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            @if(!$concern->assigned_to || $concern->status === 'Resolved')
                                            <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" onclick="softDeleteConcern({{ $concern->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" disabled title="Cannot delete assigned concerns until resolved">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-check-circle fa-2x text-muted mb-2 d-block"></i>
                                        <h5 class="text-muted">No resolved concerns found</h5>
                                        <p class="text-muted mb-0">Resolved concerns will appear here once MIS staff completes their work.</p>
                                    </td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($concerns as $concern)
                            <tr data-id="{{ $concern->id }}">
                                <td class="checkbox-col"><input type="checkbox" class="mistask-checkbox" value="{{ $concern->id }}"></td>
                                <td>
                                    <span class="d-inline-block text-truncate" style="max-width: 120px;">
                                        {{ $concern->title ?? 'No Title' }}
                                    </span>
                                    @if($concern->image_path)
                                        <i class="fas fa-image text-muted ms-1" title="Has photo"></i>
                                    @endif
                                </td>
                                <td>{{ $concern->user->name ?? 'N/A' }}</td>
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
                                <td>{{ $concern->created_at->format('M d, Y') }}</td>
                                @if($viewType === 'archives')
                                    <td>{{ $concern->archivedByUsers->first()?->pivot->archived_at ? \Carbon\Carbon::parse($concern->archivedByUsers->first()->pivot->archived_at)->format('M d, Y g:i A') : $concern->updated_at->format('M d, Y') }}</td>
                                @elseif($viewType === 'deleted')
                                    <td>{{ $concern->deleted_at ? \Carbon\Carbon::parse($concern->deleted_at)->format('M d, Y g:i A') : $concern->updated_at->format('M d, Y') }}</td>
                                @endif
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info bg-transparent border-0" onclick="viewConcern({{ $concern->id }})" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($viewType === 'active')
                                            @if($concern->status === 'Assigned' && in_array($concern->assigned_to, $misUsers->toArray()))
                                                <form action="{{ route('concerns.mis-acknowledge', $concern->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success bg-transparent border-0" title="Acknowledge">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($concern->status === 'In Progress' && in_array($concern->assigned_to, $misUsers->toArray()))
                                                <button type="button" class="btn btn-sm btn-warning bg-transparent border-0" onclick="openResolveModal({{ $concern->id }})" title="Mark as Completed">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" onclick="showArchiveModal({{ $concern->id }})" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            @if(!$concern->assigned_to || $concern->status === 'Resolved')
                                            <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" onclick="softDeleteConcern({{ $concern->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" disabled title="Cannot delete assigned concerns until resolved">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        @elseif($viewType === 'archives')
                                            <form method="POST" action="{{ route('admin.mis-tasks.restore', $concern->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success bg-transparent border-0" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" onclick="softDeleteConcern({{ $concern->id }})" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @else
                                            <form method="POST" action="{{ route('admin.mis-tasks.restore-deleted', $concern->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success bg-transparent border-0" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" onclick="permanentDeleteConcern({{ $concern->id }})" title="Permanent Delete">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $viewType === 'resolved' ? '10' : (in_array($viewType, ['archives', 'deleted']) ? '9' : '8') }}" class="text-center">
                                    @if($viewType === 'archives')
                                        No archived concerns found
                                    @elseif($viewType === 'deleted')
                                        No deleted concerns found
                                    @else
                                        No concerns assigned to MIS department
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @if(($viewType === 'resolved' && isset($resolvedConcerns) && $resolvedConcerns->hasPages()) || ($viewType !== 'resolved' && $concerns->hasPages()))
        <div class="card-footer">
            @if($viewType === 'resolved' && isset($resolvedConcerns))
                {{ $resolvedConcerns->links() }}
            @else
                {{ $concerns->links() }}
            @endif
        </div>
        @endif
    </div>
</div>

<!-- View Concern Modal -->
<div class="modal fade" id="viewConcernModal" tabindex="-1" aria-labelledby="viewConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewConcernModalLabel">View Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewConcernContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <div id="viewConcernActions"></div>
            </div>
        </div>
    </div>
</div>

@foreach($concerns as $concern)
@endforeach

<!-- Resolve Modal (single dynamic instance) -->
<div class="modal fade" id="resolveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Concern as Completed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="resolveForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="Resolved">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Resolution Notes</label>
                                <textarea class="form-control" name="resolution_notes" rows="3" placeholder="Describe what was done to fix the issue..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cost (PHP)</label>
                                <input type="number" class="form-control" name="cost" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Damaged Part</label>
                                <input type="text" class="form-control" name="damaged_part" placeholder="What part was damaged?">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Replaced With</label>
                                <input type="text" class="form-control" name="replaced_part" placeholder="What was it replaced with?">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Mark as Completed
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Archive MIS Task Concern Modal -->
<div class="modal fade" id="archiveConcernModal" tabindex="-1" aria-labelledby="archiveConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveConcernModalLabel">Archive MIS Task Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="archiveConcernId" name="concern_id" value="">
                <div class="alert alert-warning">
                    <strong>Are you sure you want to archive this MIS task concern?</strong><br>
                    This will move the concern to your MIS archive folder. You can restore it later if needed. This will not affect the original concern for the user.
                </div>
            </div>
            <div class="modal-footer">
                <form id="archiveConcernForm" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-archive"></i> Archive MIS Task Concern
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// View Concern Modal
function viewConcern(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewConcernModal'));
    const contentDiv = document.getElementById('viewConcernContent');
    const actionsDiv = document.getElementById('viewConcernActions');

    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    actionsDiv.innerHTML = '';

    modal.show();

    fetch('/api/concerns/' + id, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || 'Request failed');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }

        const concern = data.concern;
        const categoryName = concern.categoryRelation ? concern.categoryRelation.name : 'N/A';
        const userName = concern.user ? concern.user.name : 'Unknown';
        const userRole = concern.user ? concern.user.role : '';

        const priorityClass = concern.priority === 'urgent' ? 'danger' :
            (concern.priority === 'high' ? 'warning' :
            (concern.priority === 'medium' ? 'info' : 'secondary'));

        const statusClass = concern.status === 'Resolved' ? 'success' :
            (concern.status === 'In Progress' ? 'warning' :
            (concern.status === 'Assigned' ? 'primary' : 'secondary'));

        let imageHtml = '';
        if (concern.image_path) {
            imageHtml = `
                <div class="mb-3">
                    <p><strong>Photo:</strong></p>
                    <img src="${concern.image_path}" alt="Concern photo" class="img-fluid rounded" style="max-width: 400px;">
                </div>
            `;
        }

        let resolutionHtml = '';
        if (concern.resolution_notes) {
            resolutionHtml = `
                <div class="alert alert-success mt-3">
                    <h5>Resolution Notes:</h5>
                    <p>${concern.resolution_notes}</p>
                    ${concern.resolved_at ? '<small>Resolved on: ' + concern.resolved_at + '</small>' : ''}
                </div>
            `;
        }

        contentDiv.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">${concern.title || 'No Title'}</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Category:</strong> ${categoryName}</p>
                            <p><strong>Location:</strong> ${concern.location || 'N/A'}</p>
                            <p><strong>Reported by:</strong> ${userName} ${userRole ? '(' + userRole + ')' : ''}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Priority:</strong> <span class="badge bg-${priorityClass}">${concern.priority ? concern.priority.charAt(0).toUpperCase() + concern.priority.slice(1) : 'N/A'}</span></p>
                            <p><strong>Status:</strong> <span class="badge bg-${statusClass}">${concern.status || 'N/A'}</span></p>
                            <p><strong>Created:</strong> ${concern.created_at || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p><strong>Description:</strong></p>
                        <p>${concern.description || 'No description provided'}</p>
                    </div>
                    ${imageHtml}
                    ${resolutionHtml}
                </div>
            </div>
        `;

        // Show acknowledge/update buttons based on status and assignment
        const currentUserId = {{ auth()->id() }};
        if (concern.status === 'Assigned' && concern.assigned_to == currentUserId) {
            actionsDiv.innerHTML = `
                <button type="button" class="btn btn-success" onclick="acknowledgeConcern(${concern.id})">
                    <i class="fas fa-check"></i> Acknowledge & Start Work
                </button>
            `;
        } else if (concern.status === 'In Progress' && concern.assigned_to == currentUserId) {
            actionsDiv.innerHTML = `
                <button type="button" class="btn btn-warning" onclick="openResolveModal(${concern.id})">
                    <i class="fas fa-check-circle"></i> Mark as Completed
                </button>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading concern details</div>';
    });
}

// Acknowledge a concern via AJAX
function acknowledgeConcern(id) {
    fetch('/concerns/' + id + '/mis-acknowledge', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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

// Open Resolve Modal
function openResolveModal(id) {
    // Close the view modal first
    const viewModal = bootstrap.Modal.getInstance(document.getElementById('viewConcernModal'));
    if (viewModal) viewModal.hide();

    // Store the concern id on the form
    document.getElementById('resolveForm').dataset.concernId = id;

    // Show the resolve modal
    const resolveModal = new bootstrap.Modal(document.getElementById('resolveModal'));
    resolveModal.show();
}

// Handle resolve form submission via AJAX
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('resolveForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const form = this;
        const id = form.dataset.concernId;
        const formData = new FormData(form);

        fetch('/update-status/' + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('resolveModal')).hide();
                // Update the status badge in the table row (6th td = status column)
                const row = document.querySelector('tr[data-id="' + id + '"]');
                if (row) {
                    const statusTd = row.querySelectorAll('td')[5];
                    if (statusTd) {
                        const badge = statusTd.querySelector('.badge');
                        if (badge) {
                            badge.className = 'badge bg-success';
                            badge.textContent = 'Resolved';
                        }
                    }
                    // Remove the Mark as Completed button
                    const resolveBtn = row.querySelector('button[onclick^="openResolveModal"]');
                    if (resolveBtn) resolveBtn.remove();
                }
                // Show success toast
                showToast(data.message || 'Concern marked as completed!', 'success');
                form.reset();
            } else {
                showToast(data.error || 'Failed to update status.', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error updating concern status.', 'danger');
        });
    });
});

function showToast(message, type) {
    const container = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.role = 'alert';
    toast.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
}

function createToastContainer() {
    const div = document.createElement('div');
    div.id = 'toastContainer';
    div.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;min-width:300px;';
    document.body.appendChild(div);
    return div;
}

// Show Archive Modal for MIS Tasks
function showArchiveModal(concernId) {
    document.getElementById('archiveConcernId').value = concernId;
    // Use MIS-specific archive route
    document.getElementById('archiveConcernForm').action = '/admin/mis-tasks/' + concernId + '/archive';

    const modal = new bootstrap.Modal(document.getElementById('archiveConcernModal'));
    modal.show();
}

// Soft Delete MIS Task Concern
function softDeleteConcern(id) {
    if (confirm('Are you sure you want to delete this MIS task concern?')) {
        // Use MIS-specific delete route
        fetch('/admin/mis-tasks/' + id + '/delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('MIS task concern deleted successfully!');
                location.reload();
            } else {
                alert(data.error || 'Failed to delete MIS task concern');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting MIS task concern');
        });
    }
}

// Permanent Delete MIS Task Concern
function permanentDeleteConcern(id) {
    if (confirm('Are you sure you want to permanently delete this MIS task concern? This action cannot be undone!')) {
        fetch('/concerns/' + id + '/permanent-delete', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('MIS task concern permanently deleted successfully!');
                location.reload();
            } else {
                alert(data.error || 'Failed to permanently delete MIS task concern');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error permanently deleting MIS task concern');
        });
    }
}

// Checkbox functions for bulk actions
function toggleAllMisTasks(checkbox) {
    const checkboxes = document.querySelectorAll('.mistask-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function getSelectedMisTasks() {
    const checkboxes = document.querySelectorAll('.mistask-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}
</script>
@endsection