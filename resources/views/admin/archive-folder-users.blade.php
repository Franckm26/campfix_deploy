@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2>Archive Folder: {{ $folder->name }}</h2>
<p>{{ $folder->user_count }} users archived</p>
@endsection

@section('content')
<div class="container-fluid px-3">
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
                                <td>{{ $user->updated_at->format('M d, Y') }}</td>
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
