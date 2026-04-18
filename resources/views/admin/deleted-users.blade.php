@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-trash-alt"></i> Deleted Users</h2>
<p>Users in this folder can be restored or permanently deleted.</p>
@endsection

@section('content')
<div class="container-fluid px-3">
    
    <!-- Context Menu (Right-Click) -->
    <div id="contextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" onclick="contextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" onclick="contextPermanentDelete()"><i class="fas fa-times-circle"></i> Permanently Delete</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Info Card -->
    <div class="card mb-4 border-warning">
        <div class="card-body bg-warning bg-opacity-10">
            <div class="row align-items-center">
                <div class="col-12">
                    <h5 class="mb-1"><i class="fas fa-info-circle"></i> About Deleted Users</h5>
                    <p class="mb-0 text-muted">Users that have been deleted are moved here. You can restore them to their original state or permanently delete them. Once permanently deleted, users cannot be recovered.</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-warning fs-5">{{ $users->count() }} users</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($users->count() > 0)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <form id="bulkRestoreForm" method="POST" action="{{ route('admin.deletedUsers.restoreSelected') }}">
                        @csrf
                        <input type="hidden" name="user_ids" id="selectedUserIds">
                        <button type="button" class="btn btn-success" onclick="bulkRestore()">
                            <i class="fas fa-trash-restore"></i> Restore Selected
                        </button>
                    </form>
                </div>
                <div class="col-md-4 text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        <label for="retentionDays" class="me-2 mb-0">Auto-filter after:</label>
                        <select id="retentionDays" class="form-select form-select-sm" style="width: auto;">
                            <option value="3" {{ $days == 3 ? 'selected' : '' }}>3 days</option>
                            <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 days</option>
                            <option value="15" {{ $days == 15 ? 'selected' : '' }}>15 days</option>
                            <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 days</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#permanentDeleteAllModal">
                        <i class="fas fa-times-circle"></i> Permanently Delete All
                    </button>
                    <span id="selectedCount" class="text-muted ms-3">0 users selected</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Deleted Users Table -->
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
                            <th>Deleted Date</th>
                            <th>Deleted By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr data-id="{{ $user->id }}">
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="user-checkbox" value="{{ $user->id }}" onchange="updateSelectedCount()"></td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($user->role) {
                                            'admin' => 'danger',
                                            'school_admin' => 'dark',
                                            'building_admin' => 'secondary',
                                            'academic_head' => 'warning',
                                            'program_head' => 'info',
                                            'maintenance' => 'warning',
                                            'faculty' => 'info',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>
                                <td>{{ $user->department ?? 'N/A' }}</td>
                                <td>{{ $user->updated_at->format('M d, Y h:i A') }}</td>
                                <td>{{ $user->deletedBy ? $user->deletedBy->name : 'System' }}</td>
                                <td>
                                    <div class="action-icons">
                                        <form action="{{ route('admin.deletedUsers.restore', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.deletedUsers.permanentDelete', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="return confirm('Are you sure you want to permanently delete this user?')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Permanent Delete Confirmation Modal -->
                            <div class="modal fade" id="permanentDeleteModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Permanently Delete User</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to permanently delete <strong>{{ $user->name }}</strong>?</p>
                                            <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. The user will be permanently removed from the system.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('admin.deletedUsers.permanentDelete', $user->id) }}" method="POST" class="d-inline">
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
                                <td colspan="9" class="text-center p-4">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-check-circle fa-2x d-block mb-3"></i>
                                        <h5>No Deleted Users</h5>
                                        <p class="mb-0">Deleted users will appear here. You can delete users from the User Management page.</p>
                                        <a href="{{ route('admin.users') }}" class="btn btn-primary mt-3">
                                            <i class="fas fa-users"></i> Go to User Management
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Permanent Delete All Confirmation Modal -->
<div class="modal fade" id="permanentDeleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Permanently Delete All Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete all <strong>{{ $users->count() }}</strong> users?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. All users will be permanently removed from the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.deletedUsers.permanentDeleteAll') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Permanently Delete All</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Context menu variables
    let contextMenu = document.getElementById('contextMenu');
    let currentUserId = null;

    // Toggle select all checkboxes
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const countEl = document.getElementById('selectedCount');
        const userIdsEl = document.getElementById('selectedUserIds');
        
        // Return early if elements don't exist (no users)
        if (!countEl || !userIdsEl) {
            return;
        }
        
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        const count = checkboxes.length;
        countEl.textContent = count + ' user' + (count !== 1 ? 's' : '') + ' selected';
        
        // Update hidden input for bulk form
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        userIdsEl.value = JSON.stringify(selectedIds);
    }

    // Bulk restore function
    function bulkRestore() {
        const checkboxes = document.querySelectorAll('.user-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one user to restore.');
            return;
        }
        
        if (confirm('Are you sure you want to restore ' + checkboxes.length + ' user(s)?')) {
            document.getElementById('bulkRestoreForm').submit();
        }
    }

    // Context menu functions
    function showContextMenu(e, userId) {
        e.preventDefault();
        currentUserId = userId;
        
        contextMenu.style.display = 'block';
        contextMenu.style.left = e.pageX + 'px';
        contextMenu.style.top = e.pageY + 'px';
    }

    function hideContextMenu() {
        contextMenu.style.display = 'none';
    }

    function contextView() {
        hideContextMenu();
        // View functionality - can be implemented
        alert('View functionality for user ' + currentUserId);
    }

    function contextRestore() {
        hideContextMenu();
        if (confirm('Are you sure you want to restore this user?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/deleted-users/' + currentUserId + '/restore';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function contextPermanentDelete() {
        hideContextMenu();
        const modalId = 'permanentDeleteModal' + currentUserId;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    // Add right-click listeners to table rows
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('contextmenu', (e) => {
            const userId = row.getAttribute('data-id');
            if (userId) {
                showContextMenu(e, userId);
            }
        });
    });

    // Hide context menu on click outside
    document.addEventListener('click', (e) => {
        if (!contextMenu.contains(e.target)) {
            hideContextMenu();
        }
    });

    // Handle retention days dropdown change (only if element exists)
    document.addEventListener('DOMContentLoaded', function() {
        const retentionDaysElement = document.getElementById('retentionDays');
        if (retentionDaysElement) {
            retentionDaysElement.addEventListener('change', function() {
                const days = this.value;
                if (confirm(`Set auto-filter to show users deleted more than ${days} days ago?`)) {
                    // Show loading indicator
                    this.disabled = true;

                    // Make AJAX request to save preference
                    fetch('{{ route("saveAutoDeletePreference") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ days: parseInt(days), module: 'users' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show filtered results
                            window.location.href = '{{ route("admin.deletedUsers") }}?days=' + days;
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
                    this.value = '{{ $days }}';
                }
            });
        }

        updateSelectedCount();
    });
</script>
@endsection
