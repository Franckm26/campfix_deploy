@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        /* Hide tables on mobile */
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
        .mobile-archived-user-cards {
            display: block !important;
        }
        
        /* Archived user card styling */
        .archived-user-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .archived-user-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .archived-user-card-header div {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .archived-user-card-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .archived-user-card-name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        
        .archived-user-card-body {
            margin-bottom: 12px;
        }
        
        .archived-user-card-field {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .archived-user-card-label {
            font-weight: 600;
            min-width: 100px;
            color: #666;
        }
        
        .archived-user-card-value {
            color: #333;
            flex: 1;
            word-break: break-word;
        }
        
        .archived-user-card-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .archived-user-card-actions .btn {
            flex: 1;
            font-size: 12px;
        }
        
        /* Dark mode support */
        [data-bs-theme="dark"] .archived-user-card {
            background: #2d3238;
            border-color: #495057;
        }
        
        [data-bs-theme="dark"] .archived-user-card-header {
            border-bottom-color: #495057;
        }
        
        [data-bs-theme="dark"] .archived-user-card-name,
        [data-bs-theme="dark"] .archived-user-card-value {
            color: #e9ecef;
        }
        
        [data-bs-theme="dark"] .archived-user-card-label {
            color: #adb5bd;
        }
        
        [data-bs-theme="dark"] .archived-user-card-actions {
            border-top-color: #495057;
        }
        
        /* Pagination mobile styles */
        .pagination {
            flex-wrap: wrap;
            gap: 4px;
            justify-content: center;
        }
        
        .pagination .page-item {
            margin: 0;
        }
        
        .pagination .page-link {
            font-size: 12px;
            padding: 4px 8px;
            min-width: 32px;
            text-align: center;
        }
        
        .pagination .page-link svg {
            width: 10px;
            height: 10px;
        }
        
        /* Hide some page numbers on mobile */
        .pagination .page-item:not(.active):not(.disabled):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
            display: none;
        }
        
        .pagination .page-item.disabled {
            display: inline-block;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
            align-items: center !important;
        }
        
        .d-flex.justify-content-between > small {
            width: 100%;
            text-align: center;
        }
    }
    
    /* Hide mobile cards on desktop */
    .mobile-archived-user-cards {
        display: none;
    }
</style>
@endsection

@section('page_title')
<h2>Archive Folder: {{ $folder->name }}</h2>
<p>{{ $folder->user_count }} users archived</p>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-3">
        <div class="col-md-6">
            <form method="GET" action="{{ route('admin.archiveFolderUsers', $folder->id) }}" class="d-inline">
                <label class="form-label me-2 mb-0">Show:</label>
                <select name="per_page" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 per page</option>
                    <option value="20" {{ (!request('per_page') || request('per_page') == '20') ? 'selected' : '' }}>20 per page</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 per page</option>
                </select>
            </form>
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                <i class="fas fa-trash"></i> Delete All
            </button>
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

    <!-- Users Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Archived Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="user-checkbox" value="{{ $user->id }}" onchange="updateSelectedCount()"></td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $badgeColor = match($user->role) {
                                            'admin' => 'danger',
                                            'school_admin' => 'dark',
                                            'academic_head' => 'warning',
                                            'program_head' => 'info',
                                            'maintenance' => 'warning',
                                            'faculty' => 'info',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeColor }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>
                                <td>{{ $user->department ?? 'N/A' }}</td>
                                <td>{{ $user->updated_at->format('m/d/Y') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" title="Restore" onclick="showRestoreUserModal({{ $user->id }}, '{{ $user->name }}')">
                                        <i class="fas fa-trash-restore"></i> Restore
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="showDeleteUserModal('{{ $user->uuid }}', '{{ addslashes($user->name) }}')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No users in this archive folder</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout for Archived Users -->
            <div class="mobile-archived-user-cards">
                @forelse($users as $user)
                    <div class="archived-user-card">
                        <div class="archived-user-card-header">
                            <div>
                                <input type="checkbox" class="archived-user-card-checkbox user-checkbox" value="{{ $user->id }}" onchange="updateSelectedCount()">
                                <span class="archived-user-card-name">{{ $user->name }}</span>
                            </div>
                            @php
                                $badgeColor = match($user->role) {
                                    'admin' => 'danger',
                                    'school_admin' => 'dark',
                                    'academic_head' => 'warning',
                                    'program_head' => 'info',
                                    'maintenance' => 'warning',
                                    'faculty' => 'info',
                                    default => 'primary'
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeColor }}">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </div>
                        <div class="archived-user-card-body">
                            <div class="archived-user-card-field">
                                <span class="archived-user-card-label">Name:</span>
                                <span class="archived-user-card-value">{{ $user->name }}</span>
                            </div>
                            <div class="archived-user-card-field">
                                <span class="archived-user-card-label">Email:</span>
                                <span class="archived-user-card-value">{{ $user->email }}</span>
                            </div>
                            <div class="archived-user-card-field">
                                <span class="archived-user-card-label">Department:</span>
                                <span class="archived-user-card-value">{{ $user->department ?? 'N/A' }}</span>
                            </div>
                            <div class="archived-user-card-field">
                                <span class="archived-user-card-label">Archived:</span>
                                <span class="archived-user-card-value">{{ $user->updated_at->format('m/d/Y') }}</span>
                            </div>
                        </div>
                        <div class="archived-user-card-actions">
                            <button type="button" class="btn btn-sm btn-success" onclick="showRestoreUserModal({{ $user->id }}, '{{ $user->name }}')">
                                <i class="fas fa-trash-restore"></i> Restore
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteUserModal('{{ $user->uuid }}', '{{ addslashes($user->name) }}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <h5 class="text-muted">No users in this archive folder</h5>
                    </div>
                @endforelse
            </div>

            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                <small class="text-muted">Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} entries</small>
                {{ $users->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-1"></i> Delete All Users</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Are you sure you want to delete all {{ $folder->user_count }} users from this folder?</strong></p>
                <p class="text-danger">All users will be moved to the "Deleted Users" folder.</p>
                <p class="text-muted">They can be restored later from the Deleted Users folder.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.archiveFolderUsers.deleteAll', $folder->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Delete All</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Restore All Modal -->
<div class="modal fade" id="restoreAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore All Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to restore all {{ $folder->user_count }} users from this folder?</p>
                <p class="text-muted">All users will be restored to the active users list.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.users.restoreAllFolder', $folder->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success"><i class="fas fa-trash-restore"></i> Restore All</button>
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
                <h5 class="modal-title">Restore Selected Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.restoreSelected') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="user_ids" id="selectedUserIds">
                    <p><span id="selectedUsersCount">0</span> users will be restored.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Restore Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Restore User Modal -->
<div class="modal fade" id="restoreUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Restore User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="restoreUserForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to restore the user <strong id="restoreUserName"></strong>?</p>
                    <p class="text-muted">The user will be restored to the active users list.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-trash-restore"></i> Restore</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRestoreUserModal(userId, userName) {
    document.getElementById('restoreUserName').textContent = userName;
    document.getElementById('restoreUserForm').action = '/admin/users/' + userId + '/restore';
    var modal = new bootstrap.Modal(document.getElementById('restoreUserModal'));
    modal.show();
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const count = checkboxes.length;
    
    // Update count display if element exists
    const countEl = document.getElementById('selectedCount');
    if (countEl) {
        countEl.textContent = count + ' user' + (count !== 1 ? 's' : '') + ' selected';
    }
}

function prepareRestoreSelected() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = [];
    checkboxes.forEach(checkbox => { userIds.push(checkbox.value); });
    document.getElementById('selectedUserIds').value = JSON.stringify(userIds);
    document.getElementById('selectedUsersCount').textContent = userIds.length;
}

function showDeleteUserModal(uuid, name) {
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteUserForm').action = '/admin/users/' + uuid;
    var modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    modal.show();
}
</script>

<!-- Delete User Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-trash me-1"></i> Delete User</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                    <p class="text-muted">The user will be moved to the Deleted Users folder and can be restored later.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
