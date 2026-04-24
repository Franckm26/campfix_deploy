@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-folder text-warning"></i> {{ $folder->name }}</h2>
<p>{{ $folder->description ?? 'No description' }}</p>
@endsection

@section('content')
<div class="container-fluid px-3">
    
    <!-- Context Menu (Right-Click) -->
    <div id="contextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" onclick="contextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" onclick="contextMove()"><i class="fas fa-folder"></i> Move to Folder</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#restoreAllModal">
                <i class="fas fa-trash-restore"></i> Restore All
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#restoreSelectedModal" onclick="prepareRestoreSelected()">
                <i class="fas fa-check-circle"></i> Restore Selected
            </button>
            <a href="{{ route('admin.users', ['view' => 'archives']) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Folders
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Filter by Type -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.archiveFolderItems', $folder->id) }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label">Filter by Type</label>
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="concerns" {{ request('type') == 'concerns' ? 'selected' : '' }}>Concerns</option>
                        <option value="reports" {{ request('type') == 'reports' ? 'selected' : '' }}>Reports</option>
                        <option value="facilities" {{ request('type') == 'facilities' ? 'selected' : '' }}>Facility Requests</option>
                    </select>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.archiveFolderItems', $folder->id) }}" class="btn btn-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <span class="badge bg-primary fs-6">Total: {{ $concerns->count() + $reports->count() + $facilities->count() }} items</span>
        </div>
    </div>

    <!-- Archived Concerns -->
    @if(request('type') == '' || request('type') == 'concerns')
    @if($concerns->count() > 0)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Archived Concerns</h5>
            <span class="text-muted">{{ $concerns->count() }} items</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAllConcerns" onchange="toggleSelectAll('concerns')"></th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($concerns as $concern)
                            <tr data-id="{{ $concern->id }}" data-type="concern">
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="concern-checkbox" value="{{ $concern->id }}" onchange="updateSelectedCount()"></td>
                                <td>{{ $concern->title }}</td>
                                <td>{{ $concern->categoryRelation->name ?? 'N/A' }}</td>
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
                                        ($concern->status == 'In Progress' ? 'info' : 'warning')
                                    }}">
                                        {{ $concern->status }}
                                    </span>
                                </td>
                                <td>{{ $concern->user->name ?? 'N/A' }}</td>
                                <td>{{ $concern->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" title="Restore" onclick="showRestoreItemModal('concern', {{ $concern->id }}, '{{ $concern->title }}')">
                                        <i class="fas fa-trash-restore"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.concerns.softDelete', $concern->id) }}" class="d-inline"
                                          onsubmit="return confirm('Move this concern to deleted? It can be restored from the deleted folder.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Archived Reports -->
    @if(request('type') == '' || request('type') == 'reports')
    @if($reports->count() > 0)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Archived Reports</h5>
            <span class="text-muted">{{ $reports->count() }} items</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAllReports" onchange="toggleSelectAll('reports')"></th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                            <tr data-id="{{ $report->id }}" data-type="report">
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="report-checkbox" value="{{ $report->id }}" onchange="updateSelectedCount()"></td>
                                <td>{{ $report->title }}</td>
                                <td>{{ $report->category->name ?? 'N/A' }}</td>
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
                                        ($report->status == 'In Progress' ? 'info' : 'warning')
                                    }}">
                                        {{ $report->status }}
                                    </span>
                                </td>
                                <td>{{ $report->user->name ?? 'N/A' }}</td>
                                <td>{{ $report->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" title="Restore" onclick="showRestoreItemModal('report', {{ $report->id }}, '{{ $report->title }}')">
                                        <i class="fas fa-trash-restore"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.reports.softDelete', $report->id) }}" class="d-inline"
                                          onsubmit="return confirm('Move this report to deleted? It can be restored from the deleted folder.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif

    <!-- Archived Facility Requests -->
    @if(request('type') == '' || request('type') == 'facilities')
    @if($facilities->count() > 0)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-building"></i> Archived Facility Requests</h5>
            <span class="text-muted">{{ $facilities->count() }} items</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAllFacilities" onchange="toggleSelectAll('facilities')"></th>
                            <th>Event Title</th>
                            <th>Facility</th>
                            <th>Event Date</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facilities as $facility)
                            <tr data-id="{{ $facility->id }}" data-type="facility">
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="facility-checkbox" value="{{ $facility->id }}" onchange="updateSelectedCount()"></td>
                                <td>{{ $facility->event_title }}</td>
                                <td>{{ $facility->facility }}</td>
                                <td>{{ $facility->event_date }}</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $facility->status == 'Approved' ? 'success' : 
                                        ($facility->status == 'Pending' ? 'warning' : 
                                        ($facility->status == 'Rejected' ? 'danger' : 'info'))
                                    }}">
                                        {{ $facility->status }}
                                    </span>
                                </td>
                                <td>{{ $facility->user->name ?? 'N/A' }}</td>
                                <td>{{ $facility->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" title="Restore" onclick="showRestoreItemModal('facility', {{ $facility->id }}, '{{ $facility->event_title }}')">
                                        <i class="fas fa-trash-restore"></i>
                                    </button>
                                    <form method="POST" action="{{ route('admin.facilities.softDelete', $facility->id) }}" class="d-inline"
                                          onsubmit="return confirm('Move this facility request to deleted? It can be restored from the deleted folder.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif

    @if($concerns->count() == 0 && $reports->count() == 0 && $facilities->count() == 0)
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
            <h5 class="mt-3">No Items in This Folder</h5>
            <p class="text-muted">This folder is empty</p>
        </div>
    </div>
    @endif

</div>

<!-- Restore All Modal -->
<div class="modal fade" id="restoreAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore All Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore all items in this folder? They will be removed from the archive.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.archiveFolder.restoreAll', $folder->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Restore All</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Restore Selected Modal -->
<div class="modal fade" id="restoreSelectedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Selected Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="restoreSelectedForm" action="{{ route('admin.archiveFolder.restoreSelected', $folder->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to restore the selected items? They will be removed from the archive.</p>
                    <input type="hidden" name="item_ids" id="selectedItemIds">
                    <input type="hidden" name="item_types" id="selectedItemTypes">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Restore Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restore Single Item Modal -->
<div class="modal fade" id="restoreItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="restoreItemForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to restore <strong id="restoreItemName"></strong>?</p>
                    <p class="text-muted">The item will be restored to its original location.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-trash-restore"></i> Restore</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showRestoreItemModal(type, id, name) {
    document.getElementById('restoreItemName').textContent = name;
    var form = document.getElementById('restoreItemForm');
    form.action = '{{ route("admin.archive.restore") }}';
    
    // Clear existing hidden inputs except CSRF
    var existingInputs = form.querySelectorAll('input[name="type"], input[name="id"]');
    existingInputs.forEach(input => input.remove());
    
    // Add type and id inputs
    var typeInput = document.createElement('input');
    typeInput.type = 'hidden';
    typeInput.name = 'type';
    typeInput.value = type;
    form.appendChild(typeInput);
    
    var idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = id;
    form.appendChild(idInput);
    
    var modal = new bootstrap.Modal(document.getElementById('restoreItemModal'));
    modal.show();
}

let selectedItems = [];
let selectedTypes = [];

    function updateSelectedCount() {
        selectedItems = [];
        selectedTypes = [];
        
        document.querySelectorAll('.concern-checkbox:checked').forEach(cb => {
            selectedItems.push(cb.value);
            selectedTypes.push('concern');
        });
        
        document.querySelectorAll('.report-checkbox:checked').forEach(cb => {
            selectedItems.push(cb.value);
            selectedTypes.push('report');
        });
        
        document.querySelectorAll('.facility-checkbox:checked').forEach(cb => {
            selectedItems.push(cb.value);
            selectedTypes.push('facility');
        });
        
        document.getElementById('selectedCount').textContent = selectedItems.length + ' items selected';
    }

    function toggleSelectAll(type) {
        const checkboxes = document.querySelectorAll('.' + type + '-checkbox');
        const masterCheckbox = document.getElementById('selectAll' + type.charAt(0).toUpperCase() + type.slice(1));
        
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
        
        updateSelectedCount();
    }

    function prepareRestoreSelected() {
        document.getElementById('selectedItemIds').value = selectedItems.join(',');
        document.getElementById('selectedItemTypes').value = selectedTypes.join(',');
    }

    // Context menu functions
    let currentItemId = null;
    let currentItemType = null;

    function contextView() {
        // Could open a view modal
        hideContextMenu();
    }

    function contextRestore() {
        if (currentItemId && currentItemType) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("admin.archive.restore") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const typeInput = document.createElement('input');
            typeInput.type = 'hidden';
            typeInput.name = 'type';
            typeInput.value = currentItemType;
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            idInput.value = currentItemId;
            
            form.appendChild(csrfToken);
            form.appendChild(typeInput);
            form.appendChild(idInput);
            
            document.body.appendChild(form);
            form.submit();
        }
        hideContextMenu();
    }

    function contextMove() {
        // Could show a modal to move to another folder
        hideContextMenu();
    }

    function hideContextMenu() {
        document.getElementById('contextMenu').style.display = 'none';
    }

    // Right-click context menu
    document.querySelectorAll('tr[data-id]').forEach(row => {
        row.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            currentItemId = this.dataset.id;
            currentItemType = this.dataset.type;
            
            const menu = document.getElementById('contextMenu');
            menu.style.display = 'block';
            menu.style.left = e.pageX + 'px';
            menu.style.top = e.pageY + 'px';
        });
    });

    document.addEventListener('click', function() {
        hideContextMenu();
    });
</script>
@endpush
