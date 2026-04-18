@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-archive"></i> Archive Folders</h2>
<p>Organize archived concerns, reports, and facility requests into folders</p>
@endsection

@section('content')
<div class="container-fluid px-3">
    
    <!-- Context Menu -->
    <div id="contextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" id="ctxView" onclick="contextView()"><i class="fas fa-eye"></i> View Items</a></li>
            <li><a href="#" id="ctxDelete" onclick="contextDelete()"><i class="fas fa-folder-minus"></i> Delete Folder</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                <i class="fas fa-folder-plus"></i> Create Folder
            </button>
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

    <!-- Filter by Type -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('admin.archiveFolders') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <label class="form-label">Filter by Type</label>
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="concerns" {{ request('type') == 'concerns' ? 'selected' : '' }}>Concerns</option>
                        <option value="reports" {{ request('type') == 'reports' ? 'selected' : '' }}>Reports</option>
                        <option value="facilities" {{ request('type') == 'facilities' ? 'selected' : '' }}>Facility Requests</option>
                        <option value="mixed" {{ request('type') == 'mixed' ? 'selected' : '' }}>Mixed</option>
                    </select>
                </div>
                <div class="col-auto">
                    <a href="{{ route('admin.archiveFolders') }}" class="btn btn-secondary btn-sm">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Archive Folders Grid -->
    <div class="row">
        @forelse($folders as $folder)
            <div class="col-md-4 mb-4">
                <div class="card h-100 folder-card" data-id="{{ $folder->id }}" style="cursor: pointer;" onclick="viewFolder({{ $folder->id }})">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">
                                    <i class="fas fa-folder text-warning"></i> {{ $folder->name }}
                                </h5>
                                <p class="text-muted mb-2 small">{{ $folder->description ?? 'No description' }}</p>
                            </div>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info" onclick="event.stopPropagation(); viewFolder({{ $folder->id }})" title="View Items">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if(!$folder->is_system)
                                <button type="button" class="btn btn-sm btn-warning" onclick="event.stopPropagation(); editFolder({{ $folder->id }})" title="Edit Folder">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="event.stopPropagation(); deleteFolder({{ $folder->id }})" title="Delete Folder">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="badge bg-primary">{{ $folder->item_count }} items</span>
                            @if($folder->type)
                                <span class="badge bg-secondary">{{ ucfirst($folder->type) }}</span>
                            @endif
                            @if($folder->is_system)
                                <span class="badge bg-info">System</span>
                            @endif
                        </div>
                        <div class="mt-2 text-muted small">
                            <i class="fas fa-clock"></i> Created {{ $folder->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-folder-open text-muted" style="font-size: 3rem;"></i>
                        <h5 class="mt-3">No Archive Folders</h5>
                        <p class="text-muted">Create a folder to organize your archived items</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                            <i class="fas fa-folder-plus"></i> Create First Folder
                        </button>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Unorganized Archived Items -->
    @if($unorganizedConcerns->count() > 0 || $unorganizedReports->count() > 0 || $unorganizedFacilities->count() > 0)
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-box text-muted"></i> Unorganized Archived Items</h5>
            <span class="text-muted">{{ $unorganizedConcerns->count() + $unorganizedReports->count() + $unorganizedFacilities->count() }} items</span>
        </div>
        <div class="card-body">
            @if($unorganizedConcerns->count() > 0)
                <div class="mb-3">
                    <h6>Concerns ({{ $unorganizedConcerns->count() }})</h6>
                    <div class="list-group">
                        @foreach($unorganizedConcerns->take(5) as $concern)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-clipboard-list text-info"></i>
                                    <span>{{ $concern->title }}</span>
                                    <span class="badge bg-{{ $concern->priority == 'urgent' ? 'danger' : 'warning' }}">{{ $concern->priority }}</span>
                                </div>
                                <div>
                                    <form action="{{ route('admin.archive.toFolder') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="concern">
                                        <input type="hidden" name="id" value="{{ $concern->id }}">
                                        <select name="folder_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="">Move to folder...</option>
                                            @foreach($folders as $folder)
                                                <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($unorganizedReports->count() > 0)
                <div class="mb-3">
                    <h6>Reports ({{ $unorganizedReports->count() }})</h6>
                    <div class="list-group">
                        @foreach($unorganizedReports->take(5) as $report)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-exclamation-triangle text-warning"></i>
                                    <span>{{ $report->title }}</span>
                                    <span class="badge bg-{{ $report->severity == 'critical' ? 'danger' : 'warning' }}">{{ $report->severity }}</span>
                                </div>
                                <div>
                                    <form action="{{ route('admin.archive.toFolder') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="report">
                                        <input type="hidden" name="id" value="{{ $report->id }}">
                                        <select name="folder_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="">Move to folder...</option>
                                            @foreach($folders as $folder)
                                                <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($unorganizedFacilities->count() > 0)
                <div>
                    <h6>Facility Requests ({{ $unorganizedFacilities->count() }})</h6>
                    <div class="list-group">
                        @foreach($unorganizedFacilities->take(5) as $facility)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-building text-info"></i>
                                    <span>{{ $facility->event_title }}</span>
                                    <span class="badge bg-primary">{{ $facility->status }}</span>
                                </div>
                                <div>
                                    <form action="{{ route('admin.archive.toFolder') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="type" value="facility">
                                        <input type="hidden" name="id" value="{{ $facility->id }}">
                                        <select name="folder_id" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                            <option value="">Move to folder...</option>
                                            @foreach($folders as $folder)
                                                <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

</div>

<!-- Create Folder Modal -->
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Archive Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.archiveFolders.create') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Folder Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select">
                            <option value="mixed">Mixed</option>
                            <option value="concerns">Concerns Only</option>
                            <option value="reports">Reports Only</option>
                            <option value="facilities">Facility Requests Only</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Folder Modal -->
<div class="modal fade" id="editFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Archive Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFolderForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Folder Name</label>
                        <input type="text" name="name" id="editFolderName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editFolderDescription" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Folder Modal -->
<div class="modal fade" id="deleteFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Archive Folder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteFolderForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this folder? Items in the folder will become unorganized.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentFolderId = null;

    function viewFolder(id) {
        window.location.href = '/admin/archive-folders/' + id + '/items';
    }

    function editFolder(id) {
        currentFolderId = id;
        // Fetch folder data and show edit modal
        fetch('/admin/archive-folders/' + id + '/edit')
            .then(response => response.json())
            .then(data => {
                document.getElementById('editFolderName').value = data.name;
                document.getElementById('editFolderDescription').value = data.description || '';
                document.getElementById('editFolderForm').action = '/admin/archive-folders/' + id;
                var modal = new bootstrap.Modal(document.getElementById('editFolderModal'));
                modal.show();
            });
    }

    function deleteFolder(id) {
        currentFolderId = id;
        document.getElementById('deleteFolderForm').action = '/admin/archive-folders/' + id;
        var modal = new bootstrap.Modal(document.getElementById('deleteFolderModal'));
        modal.show();
    }

    // Context menu functions
    function contextView() {
        if (currentFolderId) {
            viewFolder(currentFolderId);
        }
        hideContextMenu();
    }

    function contextDelete() {
        if (currentFolderId) {
            deleteFolder(currentFolderId);
        }
        hideContextMenu();
    }

    function hideContextMenu() {
        document.getElementById('contextMenu').style.display = 'none';
    }

    // Right-click context menu
    document.querySelectorAll('.folder-card').forEach(card => {
        card.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            currentFolderId = this.dataset.id;
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
