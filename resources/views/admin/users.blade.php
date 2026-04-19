@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2>User Management</h2>
@endsection

@section('content')
<div class="container-fluid px-3">
    
    <!-- Context Menu (Right-Click) -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li><a href="#" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" onclick="contextEdit()"><i class="fas fa-edit"></i> Edit</a></li>
            <li><a href="#" onclick="contextArchive()"><i class="fas fa-archive"></i> Archive</a></li>
            <li><a href="#" onclick="contextDelete()"><i class="fas fa-trash"></i> Delete</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
        </div>
        <div class="col-md-6 text-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-plus"></i> Add User
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-upload"></i> Import CSV
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <ul class="nav nav-pills mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ ($viewType ?? 'active') == 'active' ? 'active' : '' }}" href="{{ route('admin.users', ['view' => 'active']) }}">
                                <i class="fas fa-users"></i> Active Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($viewType ?? '') == 'archives' ? 'active' : '' }}" href="{{ route('admin.users', ['view' => 'archives']) }}">
                                <i class="fas fa-folder"></i> Archive Folders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($viewType ?? '') == 'deleted' ? 'active' : '' }}" href="{{ route('admin.users', ['view' => 'deleted']) }}" style="color: #dc3545;">
                                <i class="fas fa-trash-alt"></i> Deleted Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($viewType ?? '') == 'locked' ? 'active' : '' }}" href="{{ route('admin.users', ['view' => 'locked']) }}" style="color: #fd7e14;">
                                <i class="fas fa-lock"></i> Locked Users
                                @if(isset($lockedCount) && $lockedCount > 0)
                                    <span class="badge bg-danger ms-1">{{ $lockedCount }}</span>
                                @endif
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-7">
                    <form method="GET" action="{{ route('admin.users') }}" class="row g-2 align-items-center" id="userFilterForm">
                        <input type="hidden" name="view" value="{{ $viewType ?? 'active' }}">
                        <div class="col-auto">
                            <input type="text" name="search" id="searchInput" class="form-control form-control-sm" placeholder="Search Name, Email..." 
                                value="{{ request('search') }}" onkeyup="filterTable()">
                        </div>
                        <div class="col-auto">
                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Roles</option>
                                <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="faculty" {{ request('role') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                <option value="maintenance" {{ request('role') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                <option value="mis" {{ request('role') == 'mis' ? 'selected' : '' }}>MIS</option>
                                <option value="school_admin" {{ request('role') == 'school_admin' ? 'selected' : '' }}>School Administrator</option>
                                <option value="building_admin" {{ request('role') == 'building_admin' ? 'selected' : '' }}>Building Administrator</option>
                                <option value="academic_head" {{ request('role') == 'academic_head' ? 'selected' : '' }}>Academic Head</option>
                                <option value="program_head" {{ request('role') == 'program_head' ? 'selected' : '' }}>Program Head</option>
                                <option value="principal_assistant" {{ request('role') == 'principal_assistant' ? 'selected' : '' }}>Principal Assistant</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="20" {{ (!request('per_page') || request('per_page') == '20') ? 'selected' : '' }}>20 per page</option>
                                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 per page</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.users', ['view' => $viewType ?? 'active']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(($viewType ?? 'active') == 'active')
    <!-- Bulk Archive Section -->
    <div class="card mb-3" style="display: block !important;">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Select by Role to Archive:</label>
                    <select id="archiveRoleFilter" class="form-select" onchange="toggleUsersByRole()">
                        <option value="">Select Role</option>
                        <option value="faculty">Faculty Only</option>
                        <option value="maintenance">Maintenance Only</option>
                        <option value="student">Student Only</option>
                    </select>
                </div>
                <div class="col-md-9 text-end">
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#archiveAllModal">
                        <i class="fas fa-archive"></i> Archive All
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAllModal">
                        <i class="fas fa-trash"></i> Delete All
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#archiveSelectedModal" onclick="prepareArchiveSelected()">
                        <i class="fas fa-check-circle"></i> Archive Selected
                    </button>
                    <span id="selectedCount" class="text-muted ms-3">0 users selected</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Users Table -->
    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">

            {{-- Role Tabs --}}
            <ul class="nav nav-tabs mb-3" id="roleTabNav">
                <li class="nav-item">
                    <a class="nav-link {{ !request('role_filter') ? 'active' : '' }}" href="{{ route('admin.users', array_merge(request()->except('role_filter'), ['view' => $viewType ?? 'active'])) }}">
                        All
                        <span class="badge bg-secondary ms-1">{{ $totalAll ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('role_filter') == 'student' ? 'active' : '' }}" href="{{ route('admin.users', array_merge(request()->except('role_filter'), ['view' => $viewType ?? 'active', 'role_filter' => 'student'])) }}">
                        Student
                        <span class="badge bg-primary ms-1">{{ $totalStudent ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('role_filter') == 'faculty' ? 'active' : '' }}" href="{{ route('admin.users', array_merge(request()->except('role_filter'), ['view' => $viewType ?? 'active', 'role_filter' => 'faculty'])) }}">
                        Faculty
                        <span class="badge bg-info ms-1">{{ $totalFaculty ?? 0 }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request('role_filter') == 'staff' ? 'active' : '' }}" href="{{ route('admin.users', array_merge(request()->except('role_filter'), ['view' => $viewType ?? 'active', 'role_filter' => 'staff'])) }}">
                        Staff
                        <span class="badge bg-warning ms-1">{{ $totalStaff ?? 0 }}</span>
                    </a>
                </li>
            </ul>

            <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                <table class="table table-hover" style="display: table !important;">
                    <thead>
                        <tr>
                            <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr data-id="{{ $user->id }}" data-role="{{ $user->role }}" data-archived="{{ $user->is_archived ? '1' : '0' }}">
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="user-checkbox" value="{{ $user->id }}" onchange="updateSelectedCount()"></td>
                                <td>{{ $user->student_id ?? 'N/A' }}</td>
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
                                    @if($user->locked_until)
                                        <span class="badge bg-danger ms-1" title="Account locked — requires MIS to unlock">
                                            <i class="fas fa-lock"></i> Locked
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewUser({{ $user->id }})" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editUser({{ $user->id }})" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($user->locked_until)
                                        <form action="{{ route('admin.users.unlock', $user->uuid) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Unlock Account" onclick="return confirm('Unlock account for {{ $user->name }}?')">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if(!$user->is_archived)
                                        <button type="button" class="btn btn-sm btn-secondary" title="Archive" onclick="showUserActionModal('archive', '{{ $user->uuid }}', '{{ $user->name }}')">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                        @else
                                        <button type="button" class="btn btn-sm btn-success" title="Restore" onclick="showUserActionModal('restore', '{{ $user->uuid }}', '{{ $user->name }}')">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
                                        @endif
                                        @if($user->id !== auth()->id())
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="showUserActionModal('delete', '{{ $user->uuid }}', '{{ $user->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit User Modal -->
                            <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit User: {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.users.update', $user->uuid) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Role</label>
                                                    <select name="role" class="form-select" required>
                                                        <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>Student</option>
                                                        <option value="faculty" {{ $user->role == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                                        <option value="maintenance" {{ $user->role == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                                        <option value="mis" {{ $user->role == 'mis' ? 'selected' : '' }}>MIS</option>
                                                        <option value="school_admin" {{ $user->role == 'school_admin' ? 'selected' : '' }}>School Administrator</option>
                                                        <option value="building_admin" {{ $user->role == 'building_admin' ? 'selected' : '' }}>Building Administrator</option>
                                                        <option value="academic_head" {{ $user->role == 'academic_head' ? 'selected' : '' }}>Academic Head</option>
                                                        <option value="program_head" {{ $user->role == 'program_head' ? 'selected' : '' }}>Program Head</option>
                                                        <option value="principal_assistant" {{ $user->role == 'principal_assistant' ? 'selected' : '' }}>Principal Assistant</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3" id="departmentField">
                                                    <label class="form-label">Department</label>
                                                    <select name="department" class="form-select">
                                                        <option value="">Select Department</option>
                                                        <option value="GE" {{ $user->department == 'GE' ? 'selected' : '' }}>GE</option>
                                                        <option value="ICT" {{ $user->department == 'ICT' ? 'selected' : '' }}>ICT</option>
                                                        <option value="Business Management" {{ $user->department == 'Business Management' ? 'selected' : '' }}>Business Management</option>
                                                        <option value="THM" {{ $user->department == 'THM' ? 'selected' : '' }}>THM</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" name="phone" class="form-control" value="{{ $user->phone }}" maxlength="11" pattern="09[0-9]{9}" placeholder="09XXXXXXXXX">
                                                    <small class="text-muted">11-digit PH number (e.g., 09123456789)</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Student ID</label>
                                                    <input type="text" name="student_id" class="form-control" value="{{ $user->student_id }}">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">New Password (leave blank to keep current)</label>
                                                    <div class="input-group">
                                                        <input type="password" name="password" class="form-control edit-user-password" id="editPassword{{ $user->id }}" minlength="8" maxlength="20" autocomplete="new-password">
                                                        <button type="button" class="btn btn-outline-secondary toggle-edit-pw" data-target="editPassword{{ $user->id }}">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    <!-- Strength bar -->
                                                    <div class="edit-pw-bar-wrap mt-1" style="display:none">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <div class="progress flex-grow-1" style="height:6px">
                                                                <div class="edit-pw-bar progress-bar" style="width:0%;transition:width .3s,background .3s"></div>
                                                            </div>
                                                            <small class="edit-pw-label fw-semibold" style="min-width:52px;font-size:12px"></small>
                                                        </div>
                                                    </div>
                                                    <!-- Requirements -->
                                                    <div class="edit-pw-reqs mt-2 p-3 rounded shadow-sm" style="display:none;background:#f8f9fa;font-size:13px;border:1px solid #dee2e6">
                                                        <div class="fw-semibold mb-2">Password must include:</div>
                                                        <div class="edit-req-length  req-item"><i class="fas fa-times-circle text-danger me-2"></i>8-20 <strong>Characters</strong></div>
                                                        <div class="edit-req-upper   req-item mt-1"><i class="fas fa-times-circle text-danger me-2"></i>At least one <strong>capital letter</strong></div>
                                                        <div class="edit-req-number  req-item mt-1"><i class="fas fa-times-circle text-danger me-2"></i>At least one <strong>number</strong></div>
                                                        <div class="edit-req-nospace req-item mt-1"><i class="fas fa-times-circle text-danger me-2"></i><strong>No spaces</strong></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="submit" class="btn btn-primary">Update User</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- View User Modal -->
                            <div class="modal fade" id="viewUserModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">User Details: {{ $user->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Name</label>
                                                    <p>{{ $user->name }}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Email</label>
                                                    <p>{{ $user->email }}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Role</label>
                                                    <p>
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
                                                    </p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Department</label>
                                                    <p>{{ $user->department ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Phone</label>
                                                    <p>{{ $user->phone ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">STI ID</label>
                                                    <p>{{ $user->sti_id ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Created At</label>
                                                    <p>{{ $user->created_at->format('M d, Y h:i A') }}</p>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-bold">Last Updated</label>
                                                    <p>{{ $user->updated_at->format('M d, Y h:i A') }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}" data-bs-dismiss="modal">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- User Count -->
            <div class="text-muted mt-3 small text-center">
                Total active users: {{ $users->total() }}
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $users->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
    @endif

    <!-- Archive Folders View -->
    @if(($viewType ?? '') == 'archives')
    <div class="card mb-3">
        <div class="card-body">
            <div class="text-end">
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAllArchivedModal">
                    <i class="fas fa-trash"></i> Delete All Archived
                </button>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-folder"></i> Folder Name</th>
                            <th>Description</th>
                            <th>Users Count</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($archiveFolders as $folder)
                            <tr>
                                <td>
                                    <i class="fas fa-folder text-warning"></i> 
                                    <strong>{{ $folder->name }}</strong>
                                </td>
                                <td>{{ $folder->description ?? 'No description' }}</td>
                                <td><span class="badge bg-primary">{{ $folder->user_count }} users</span></td>
                                <td>{{ $folder->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.archiveFolderUsers', $folder->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-folder-open"></i> View Users
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete Folder" data-bs-toggle="modal" data-bs-target="#deleteFolderModal{{ $folder->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Delete Folder Modal -->
                            <div class="modal fade" id="deleteFolderModal{{ $folder->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Delete Archive Folder</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete the folder <strong>{{ $folder->name }}</strong>?</p>
                                            <p class="text-danger">{{ $folder->user_count }} users in this folder will be permanently deleted!</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('admin.archiveFolder.delete', $folder->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete Folder</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-folder-open"></i> No archive folders yet. Use "Archive All" to create one.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if(($viewType ?? '') == 'deleted')
    <!-- Deleted Users Section -->
    <div id="deletedUsersContextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" onclick="deletedUsersContextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" onclick="deletedUsersContextPermanentDelete()"><i class="fas fa-times-circle"></i> Permanently Delete</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-trash-alt"></i> Deleted Users</h2>
            <p class="text-muted">Users in this folder can be restored or permanently deleted.</p>
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
                    <span class="badge bg-warning fs-5">{{ $deletedUsers->count() ?? 0 }} users</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-delete Settings -->
    <div class="card mb-4 border-info">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1"><i class="fas fa-clock"></i> Auto-delete Settings</h5>
                    <p class="mb-0 text-muted">Automatically delete users that have been in the deleted folder for the selected period.</p>
                </div>
                <div class="col-md-4">
                    <select id="autoDeleteDays" class="form-select" onchange="
                        const days = this.value;
                        fetch('{{ route('saveAutoDeletePreference') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                            },
                            body: JSON.stringify({ days: parseInt(days), module: 'users' })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Optional: Show success message
                            } else {
                                alert('Error saving preference: ' + (data.error || 'Unknown error'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error saving preference');
                        });
                    ">
                        <option value="3" {{ (isset($days) && $days == 3) ? 'selected' : '' }}>3 days</option>
                        <option value="7" {{ (isset($days) && $days == 7) ? 'selected' : '' }}>7 days</option>
                        <option value="15" {{ (!isset($days) || $days == 15) ? 'selected' : '' }}>15 days</option>
                        <option value="30" {{ (isset($days) && $days == 30) ? 'selected' : '' }}>30 days</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if(($deletedUsers->count() ?? 0) > 0)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form method="POST" action="{{ route('admin.deletedUsers.restoreAll') }}">
                        @csrf
                        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to restore all deleted users?')">
                            <i class="fas fa-trash-restore"></i> Restore All
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletedUsersPermanentDeleteAllModal">
                        <i class="fas fa-times-circle"></i> Permanently Delete All
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Deleted Users Table -->
    <div class="card">
        <div class="card-body">
            @if(isset($deletedUsers) && $deletedUsers->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;" id="deletedUsersTable">
                        <thead>
                            <tr>
                                <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="deletedUsersSelectAll" onchange="deletedUsersToggleSelectAll()"></th>
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
                            @forelse($deletedUsers as $user)
                                <tr data-id="{{ $user->id }}">
                                    <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-user-checkbox" value="{{ $user->id }}" onchange="deletedUsersUpdateSelectedCount()"></td>
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
                                        <div class="btn-group" role="group">
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
                                <div class="modal fade" id="deletedUsersPermanentDeleteModal{{ $user->id }}" tabindex="-1">
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
                
                {{-- Pagination --}}
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $deletedUsers->firstItem() ?? 0 }} to {{ $deletedUsers->lastItem() ?? 0 }} of {{ $deletedUsers->total() }} deleted users
                    </div>
                    <div>
                        {{ $deletedUsers->appends(request()->except('page'))->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-2x d-block mb-3 text-success"></i>
                        <h5>No Deleted Users</h5>
                        <p class="mb-0 text-muted">Deleted users will appear here. You can delete users from the User Management page.</p>
                        <a href="{{ route('admin.users') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-users"></i> Go to User Management
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Locked Users View --}}
    @if(($viewType ?? '') == 'locked')
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="fas fa-lock text-warning"></i> Locked Users</h2>
            <p class="text-muted">Accounts locked due to too many failed login attempts. Unlock them to restore access.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if(isset($lockedUsersList) && $lockedUsersList->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:13px">
                        <thead class="table-warning">
                            <tr>
                                <th class="ps-3">Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Failed Attempts</th>
                                <th>Locked Since</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lockedUsersList as $lu)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $lu->name }}</td>
                                    <td class="text-muted">{{ $lu->email }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $lu->role)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">{{ $lu->failed_login_attempts }}</span>
                                    </td>
                                    <td class="text-muted">{{ $lu->updated_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <form action="{{ route('admin.users.unlock', $lu->uuid) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Unlock account for {{ $lu->name }}?')">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                    <small class="text-muted">{{ $lockedUsersList->total() }} locked account(s)</small>
                    {{ $lockedUsersList->links('pagination::bootstrap-4') }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-2x text-success d-block mb-3"></i>
                    <h5>No Locked Accounts</h5>
                    <p class="mb-0">All accounts are currently active.</p>
                </div>
            @endif
        </div>
    </div>
    @endif

</div>

<!-- Permanent Delete All Confirmation Modal for Deleted Users -->
@if(($viewType ?? '') == 'deleted' && isset($deletedUsers))
<div class="modal fade" id="deletedUsersPermanentDeleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Permanently Delete All Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete all <strong>{{ $deletedUsers->count() }}</strong> users?</p>
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
@endif

<!-- User Action Confirmation Modal -->
<div class="modal fade" id="userActionModal" tabindex="-1" aria-labelledby="userActionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="userActionModalHeader">
                <h5 class="modal-title" id="userActionModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="userActionMessage"></p>
                <div id="userActionAlert" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="userActionConfirmBtn"><i class="fas fa-check"></i> Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel"><i class="fas fa-check-circle"></i> Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="successMessage"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.store') }}" method="POST" id="addUserForm" novalidate>
                @csrf
                <div class="modal-body">

                    @if($errors->hasAny(['name','email','password','phone','role']))
                        <div class="alert alert-danger py-2 mb-3">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->only(['name','email','password','phone','role']) as $field => $msgs)
                                    @foreach($msgs as $msg)
                                        <li style="font-size:13px">{{ $msg }}</li>
                                    @endforeach
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" value="{{ old('name') }}" required>
                        <div class="invalid-feedback">{{ $errors->first('name') ?: 'Name is required.' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" required>
                        <div class="invalid-feedback">{{ $errors->first('email') ?: 'A valid email is required.' }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}" required>
                            <option value="student"              {{ old('role') == 'student'              ? 'selected' : '' }}>Student</option>
                            <option value="faculty"              {{ old('role') == 'faculty'              ? 'selected' : '' }}>Faculty</option>
                            <option value="maintenance"          {{ old('role') == 'maintenance'          ? 'selected' : '' }}>Maintenance</option>
                            <option value="mis"                  {{ old('role') == 'mis'                  ? 'selected' : '' }}>MIS</option>
                            <option value="school_admin"         {{ old('role') == 'school_admin'         ? 'selected' : '' }}>School Administrator</option>
                            <option value="building_admin"       {{ old('role') == 'building_admin'       ? 'selected' : '' }}>Building Administrator</option>
                            <option value="academic_head"        {{ old('role') == 'academic_head'        ? 'selected' : '' }}>Academic Head</option>
                            <option value="program_head"         {{ old('role') == 'program_head'         ? 'selected' : '' }}>Program Head</option>
                            <option value="principal_assistant"  {{ old('role') == 'principal_assistant'  ? 'selected' : '' }}>Principal Assistant</option>
                        </select>
                    </div>
                    <div class="mb-3" id="departmentField" style="display: {{ old('role') == 'program_head' ? 'block' : 'none' }};">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select">
                            <option value="">Select Department</option>
                            <option value="GE"                  {{ old('department') == 'GE'                  ? 'selected' : '' }}>GE</option>
                            <option value="ICT"                 {{ old('department') == 'ICT'                 ? 'selected' : '' }}>ICT</option>
                            <option value="Business Management" {{ old('department') == 'Business Management' ? 'selected' : '' }}>Business Management</option>
                            <option value="THM"                 {{ old('department') == 'THM'                 ? 'selected' : '' }}>THM</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone <small class="text-muted">(optional)</small></label>
                        <input type="text" name="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" maxlength="11" placeholder="09XXXXXXXXX" value="{{ old('phone') }}">
                        <div class="invalid-feedback">{{ $errors->first('phone') ?: 'Enter a valid 11-digit PH number (e.g., 09123456789).' }}</div>
                        <small class="text-muted">11-digit PH number (e.g., 09123456789)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="addUserPassword" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" required>
                        <div class="invalid-feedback">{{ $errors->first('password') ?: 'Password must be at least 8 characters.' }}</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddUserForm()">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Import Users from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Step 1: Role selection --}}
                    <div id="importStep1">
                        <p class="text-muted mb-3">Select the type of users you are importing:</p>
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="import-role-card border rounded p-3 text-center" onclick="selectImportRole('student')" style="cursor:pointer;transition:all .2s">
                                    <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                                    <div class="fw-semibold">Student</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="import-role-card border rounded p-3 text-center" onclick="selectImportRole('faculty')" style="cursor:pointer;transition:all .2s">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                                    <div class="fw-semibold">Faculty</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: File upload (hidden until role selected) --}}
                    <div id="importStep2" style="display:none">
                        <div class="d-flex align-items-center mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="backToRoleSelect()">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <span>Importing as: <span id="importRoleLabel" class="badge bg-primary fs-6"></span></span>
                        </div>
                        <input type="hidden" name="default_role" id="importRoleInput">
                        <input type="hidden" name="file_format" value="masterlist">

                        <div class="mb-3">
                            <label class="form-label">Archive Folder Name</label>
                            <input type="text" name="archive_folder_name" class="form-control" value="2025-2026" placeholder="e.g., 2025-2026">
                            <small class="text-muted">The folder will be created automatically if it doesn't exist.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">CSV File</label>
                            <input type="file" name="file" class="form-control" accept=".csv,.txt,.xlsx" required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="importSubmitBtn" style="display:none">
                        <i class="fas fa-upload me-1"></i>Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.import-role-card:hover { background: #f0f4ff; border-color: #0d6efd !important; }
.import-role-card.selected { background: #e7f1ff; border-color: #0d6efd !important; border-width: 2px !important; }
</style>

<script>
function selectImportRole(role) {
    document.getElementById('importRoleInput').value = role;
    document.getElementById('importRoleLabel').textContent = role.charAt(0).toUpperCase() + role.slice(1);
    document.getElementById('importRoleLabel').className = 'badge fs-6 ' + (role === 'student' ? 'bg-primary' : 'bg-success');
    document.getElementById('importStep1').style.display = 'none';
    document.getElementById('importStep2').style.display = 'block';
    document.getElementById('importSubmitBtn').style.display = 'inline-block';
}

function backToRoleSelect() {
    document.getElementById('importStep1').style.display = 'block';
    document.getElementById('importStep2').style.display = 'none';
    document.getElementById('importSubmitBtn').style.display = 'none';
}

// Reset modal to step 1 when closed
document.getElementById('importModal').addEventListener('hidden.bs.modal', function () {
    backToRoleSelect();
    document.querySelector('#importModal input[type=file]').value = '';
});
</script>

<!-- Archive All Modal -->
<div class="modal fade" id="archiveAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive All Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.archiveAll') }}" method="POST" id="archiveAllForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Archive Folder Name</label>
                        <input type="text" name="folder_name" class="form-control" value="2025-2026" placeholder="Enter folder name for this archive" required>
                        <small class="text-muted">All users will be archived into this folder. The folder will be created automatically if it doesn't exist.</small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This will archive all non-archived users. Users already archived will not be affected.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-archive"></i> Archive All</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete All Modal -->
<div class="modal fade" id="deleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Delete All Users</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.deleteAll') }}" method="POST" id="deleteAllForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <strong>Warning:</strong> This action will move all non-archived users to the "Deleted Users" folder. You can restore them later from the Deleted Users view.
                    </div>
                    <p class="mb-3">Type <strong>DELETE ALL</strong> to confirm:</p>
                    <input type="text" id="deleteAllConfirm" class="form-control" placeholder="Type DELETE ALL" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" id="deleteAllBtn" disabled>
                        <i class="fas fa-trash"></i> Delete All Users
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Delete All confirmation
document.getElementById('deleteAllConfirm')?.addEventListener('input', function() {
    const btn = document.getElementById('deleteAllBtn');
    btn.disabled = this.value.trim() !== 'DELETE ALL';
});

// Reset on modal close
document.getElementById('deleteAllModal')?.addEventListener('hidden.bs.modal', function() {
    document.getElementById('deleteAllConfirm').value = '';
    document.getElementById('deleteAllBtn').disabled = true;
});
</script>

<!-- Archive Selected Modal -->
<div class="modal fade" id="archiveSelectedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Archive Selected Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.archiveSelected') }}" method="POST" id="archiveSelectedForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Archive Folder Name</label>
                        <input type="text" name="folder_name" class="form-control" value="2025-2026" placeholder="Enter folder name for this archive" required>
                        <small class="text-muted">Selected users will be archived into this folder. The folder will be created automatically if it doesn't exist.</small>
                    </div>
                    <input type="hidden" name="user_ids" id="selectedUserIds" value="">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> <span id="selectedUsersCount">0</span> users will be archived.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-archive"></i> Archive Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete All Archived Modal -->
<div class="modal fade" id="deleteAllArchivedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete All Archived Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.deleteAllArchived') }}" method="POST" id="deleteAllArchivedForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Warning:</strong> This action cannot be undone! All archived users will be permanently deleted.
                    </div>
                    <p>Are you sure you want to delete all archived users? This will remove all users who have been archived.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete All</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Dropdown fix for table */
.table .dropdown {
    position: static;
}
.table .dropdown-menu {
    position: absolute;
}

/* Context Menu (Right-Click) */
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
    cursor: pointer;
}
.context-menu ul li a:hover {
    background: #f5f5f5;
}
.context-menu ul li a i {
    margin-right: 8px;
    width: 20px;
}

/* Pagination arrow size fix */
.pagination .page-link {
    font-size: 14px;
    padding: 6px 10px;
}
.pagination .page-link svg {
    width: 12px;
    height: 12px;
}
</style>

<script>
// Global variable for selected user ID
window.selectedUserId = null;

function submitAddUserForm() {
    const form = document.getElementById('addUserForm');
    let valid = true;

    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const name     = form.querySelector('[name="name"]');
    const email    = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]');
    const phone    = form.querySelector('[name="phone"]');

    if (!name.value.trim()) { name.classList.add('is-invalid'); valid = false; }

    if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        email.classList.add('is-invalid'); valid = false;
    }

    if (!password.value || password.value.length < 8) {
        password.classList.add('is-invalid'); valid = false;
    }

    if (phone.value && !/^09[0-9]{9}$/.test(phone.value)) {
        phone.classList.add('is-invalid'); valid = false;
    }

    if (valid) form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    // Show/hide department field based on role selection (Add User modal)
    const addUserModal = document.getElementById('addUserModal');
    if (addUserModal) {
        const roleSelect = addUserModal.querySelector('select[name="role"]');
        const departmentField = addUserModal.querySelector('#departmentField');
        
        if (roleSelect && departmentField) {
            roleSelect.addEventListener('change', function() {
                if (this.value === 'program_head') {
                    departmentField.style.display = 'block';
                } else {
                    departmentField.style.display = 'none';
                    departmentField.querySelector('select').value = '';
                }
            });
        }
    }

    // Auto-reopen Add User modal if server returned validation errors for it
    @if($errors->hasAny(['name','email','password','phone','role']))
    (function() {
        var modal = new bootstrap.Modal(document.getElementById('addUserModal'));
        modal.show();
    })();
    @endif

    // Show/hide department field for Edit User modal (using event delegation)
    document.addEventListener('shown.bs.modal', function(event) {
        const modal = event.target;
        if (modal.id.startsWith('editUserModal')) {
            const roleSelectEdit = modal.querySelector('select[name="role"]');
            const departmentFieldEdit = modal.querySelector('#departmentField');
            
            if (roleSelectEdit && departmentFieldEdit) {
                // Initial check when modal opens
                const initialRole = roleSelectEdit.value;
                if (initialRole === 'program_head') {
                    departmentFieldEdit.style.display = 'block';
                } else {
                    departmentFieldEdit.style.display = 'none';
                }
                
                // Listen for changes
                roleSelectEdit.addEventListener('change', function() {
                    if (this.value === 'program_head') {
                        departmentFieldEdit.style.display = 'block';
                    } else {
                        departmentFieldEdit.style.display = 'none';
                        departmentFieldEdit.querySelector('select').value = '';
                    }
                });
            }
        }
    });

    // Auto-open edit modal if ?edit=ID is present
    const urlParams = new URLSearchParams(window.location.search);
    const editId = urlParams.get('edit');
    if (editId) {
        setTimeout(function() {
            editUser(editId);
        }, 500); // Small delay to ensure modal is rendered
    }

    // Right-click handler
    document.addEventListener('contextmenu', function(e) {
        const row = e.target.closest('tr[data-id]');
        if (row) {
            e.preventDefault();
            window.selectedUserId = row.getAttribute('data-id');
            showContextMenu(e.pageX, e.pageY);
        }
    });

    // Long-press handler for mobile
    let longPressTimer;
    document.addEventListener('touchstart', function(e) {
        const row = e.target.closest('tr[data-id]');
        if (row) {
            longPressTimer = setTimeout(function() {
                window.selectedUserId = row.getAttribute('data-id');
                const touch = e.touches[0];
                showContextMenu(touch.pageX, touch.pageY);
            }, 500);
        }
    });

    document.addEventListener('touchend', function() {
        clearTimeout(longPressTimer);
    });

    // Make filterTable globally accessible
    window.filterTable = function() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput) return;
        
        const filter = searchInput.value.toLowerCase();
        const table = document.querySelector('.card .table');
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            // Get all text content from the row
            const rowText = row.textContent.toLowerCase();
            
            if (rowText.includes(filter)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Run filter on page load if there's a search value
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        if (searchInput && searchInput.value) {
            window.filterTable();
        }
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
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('contextMenu');
        if (!e.target.closest('.context-menu')) {
            menu.style.display = 'none';
        }
    });

});

// View user function
function viewUser(userId) {
    const modal = new bootstrap.Modal(document.getElementById('viewUserModal' + userId));
    modal.show();
}

// Edit user function
function editUser(userId) {
    const modal = new bootstrap.Modal(document.getElementById('editUserModal' + userId));
    modal.show();
}

// Context menu actions
function contextView() {
    if (window.selectedUserId) {
        viewUser(window.selectedUserId);
        document.getElementById('contextMenu').style.display = 'none';
    }
}

function contextEdit() {
    if (window.selectedUserId) {
        editUser(window.selectedUserId);
        document.getElementById('contextMenu').style.display = 'none';
    }
}

function contextArchive() {
    if (window.selectedUserId) {
        const row = document.querySelector('tr[data-id="' + window.selectedUserId + '"]');
        const isArchived = row && row.getAttribute('data-archived') === '1';
        
        if (isArchived) {
            // Restore user
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/users/' + window.selectedUserId + '/restore';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        } else {
            // Archive user
            if (confirm('Are you sure you want to archive this user?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/users/' + window.selectedUserId + '/archive';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                document.body.appendChild(form);
                form.submit();
            }
        }
        document.getElementById('contextMenu').style.display = 'none';
    }
}

function contextDelete() {
    if (window.selectedUserId) {
        if (confirm('Are you sure you want to delete this user?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/users/' + window.selectedUserId;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
        document.getElementById('contextMenu').style.display = 'none';
    }
}

// Bulk archive functions
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
}

function updateSelectedCount() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const count = checkboxes.length;
    const selectedCountEl = document.getElementById('selectedCount');
    
    if (selectedCountEl) {
        selectedCountEl.textContent = count + ' user' + (count !== 1 ? 's' : '') + ' selected';
    }
    
    return count;
}

function toggleUsersByRole() {
    const roleFilter = document.getElementById('archiveRoleFilter');
    const selectedRole = roleFilter ? roleFilter.value : '';
    const rows = document.querySelectorAll('tr[data-role]');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    
    // First uncheck everything
    const selectAll = document.getElementById('selectAll');
    if (selectAll) selectAll.checked = false;
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        checkbox.disabled = false;
    });
    
    if (selectedRole) {
        // Enable and check only checkboxes for selected role
        rows.forEach(row => {
            const rowRole = row.getAttribute('data-role');
            const checkbox = row.querySelector('.user-checkbox');
            
            if (rowRole === selectedRole) {
                if (checkbox) {
                    checkbox.disabled = false;
                    checkbox.checked = true;
                }
            } else {
                if (checkbox) checkbox.disabled = true;
            }
        });
    }
    
    updateSelectedCount();
}

function prepareArchiveSelected() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked:not(:disabled)');
    const userIds = [];
    
    checkboxes.forEach(checkbox => {
        userIds.push(checkbox.value);
    });
    
    // Set user_ids as comma-separated string (Laravel will convert to array)
    document.getElementById('selectedUserIds').value = userIds.join(',');
    document.getElementById('selectedUsersCount').textContent = userIds.length;
}

// Deleted Users JavaScript Functions
let currentDeletedUserId = null;

function deletedUsersToggleSelectAll() {
    const selectAll = document.getElementById('deletedUsersSelectAll');
    const checkboxes = document.querySelectorAll('.deleted-user-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    deletedUsersUpdateSelectedCount();
}

function deletedUsersUpdateSelectedCount() {
    const countEl = document.getElementById('deletedUsersSelectedCount');
    const userIdsEl = document.getElementById('selectedDeletedUserIds');
    
    if (!countEl || !userIdsEl) {
        return;
    }
    
    const checkboxes = document.querySelectorAll('.deleted-user-checkbox:checked');
    const count = checkboxes.length;
    countEl.textContent = count + ' user' + (count !== 1 ? 's' : '') + ' selected';
    
    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    userIdsEl.value = JSON.stringify(selectedIds);
}

function deletedUsersBulkRestore() {
    const checkboxes = document.querySelectorAll('.deleted-user-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one user to restore.');
        return;
    }
    
    if (confirm('Are you sure you want to restore ' + checkboxes.length + ' user(s)?')) {
        document.getElementById('deletedUsersBulkRestoreForm').submit();
    }
}

function showDeletedUsersContextMenu(e, userId) {
    e.preventDefault();
    currentDeletedUserId = userId;
    
    const menu = document.getElementById('deletedUsersContextMenu');
    menu.style.display = 'block';
    menu.style.left = e.pageX + 'px';
    menu.style.top = e.pageY + 'px';
}

function hideDeletedUsersContextMenu() {
    const menu = document.getElementById('deletedUsersContextMenu');
    if (menu) {
        menu.style.display = 'none';
    }
}

function deletedUsersContextRestore() {
    hideDeletedUsersContextMenu();
    if (confirm('Are you sure you want to restore this user?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/deleted-users/' + currentDeletedUserId + '/restore';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deletedUsersContextPermanentDelete() {
    hideDeletedUsersContextMenu();
    const modalId = 'deletedUsersPermanentDeleteModal' + currentDeletedUserId;
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

// Add right-click listeners to deleted users table rows
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('deletedUsersTable');
    if (table) {
        table.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('contextmenu', (e) => {
                const userId = row.getAttribute('data-id');
                if (userId) {
                    showDeletedUsersContextMenu(e, userId);
                }
            });
        });
    }
    
    // Hide context menu on click outside
    document.addEventListener('click', (e) => {
        const menu = document.getElementById('deletedUsersContextMenu');
        if (menu && !menu.contains(e.target)) {
            hideDeletedUsersContextMenu();
        }
    });
    
    deletedUsersUpdateSelectedCount();
});

// User Action Modal Functions
let userActionType = null;
let userActionId = null;

function showUserActionModal(type, id, name) {
    userActionType = type;
    userActionId = id;
    
    const modal = new bootstrap.Modal(document.getElementById('userActionModal'));
    const modalHeader = document.getElementById('userActionModalHeader');
    const modalMessage = document.getElementById('userActionMessage');
    const modalAlert = document.getElementById('userActionAlert');
    const confirmBtn = document.getElementById('userActionConfirmBtn');
    
    modalAlert.classList.remove('alert-info', 'alert-warning', 'alert-danger', 'd-none');
    
    if (type === 'archive') {
        modalHeader.className = 'modal-header bg-secondary text-white';
        document.getElementById('userActionModalLabel').innerHTML = '<i class="fas fa-archive"></i> Archive User';
        modalMessage.innerHTML = 'Are you sure you want to archive <strong>' + name + '</strong>?';
        modalAlert.classList.add('alert-info');
        modalAlert.innerHTML = '<i class="fas fa-info-circle"></i> You can restore this user later from the Archive tab.';
        confirmBtn.className = 'btn btn-secondary';
        confirmBtn.innerHTML = '<i class="fas fa-archive"></i> Archive';
    } else if (type === 'restore') {
        modalHeader.className = 'modal-header bg-success text-white';
        document.getElementById('userActionModalLabel').innerHTML = '<i class="fas fa-trash-restore"></i> Restore User';
        modalMessage.innerHTML = 'Are you sure you want to restore <strong>' + name + '</strong>?';
        modalAlert.classList.add('alert-info');
        modalAlert.innerHTML = '<i class="fas fa-info-circle"></i> This user will be restored to their original status.';
        confirmBtn.className = 'btn btn-success';
        confirmBtn.innerHTML = '<i class="fas fa-trash-restore"></i> Restore';
    } else if (type === 'delete') {
        modalHeader.className = 'modal-header bg-danger text-white';
        document.getElementById('userActionModalLabel').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Delete User';
        modalMessage.innerHTML = 'Are you sure you want to delete <strong>' + name + '</strong>?';
        modalAlert.classList.add('alert-warning');
        modalAlert.innerHTML = '<i class="fas fa-warning"></i> This action will move the user to deleted. You can restore them later from the Deleted tab.';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
    
    // Set up confirm button click
    confirmBtn.onclick = function() {
        executeUserAction(type, id);
    };
    
    modal.show();
}

function executeUserAction(type, id) {
    let url = '';
    let method = 'POST';
    
    if (type === 'archive') {
        url = '/admin/users/' + id + '/archive';
    } else if (type === 'restore') {
        url = '/admin/users/' + id + '/restore';
    } else if (type === 'delete') {
        url = '/admin/users/' + id;
        method = 'DELETE';
    }
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('successMessage').innerHTML = data.success;
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
            // Reload page when modal is hidden
            document.getElementById('successModal').addEventListener('hidden.bs.modal', function() {
                location.reload();
            });
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error performing action');
    });
    
    // Close modal
    const modalEl = document.getElementById('userActionModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }

    // Auto-delete period update function
    function updateAutoDeletePeriod(days) {
        // Update the URL with the selected days parameter and reload the page
        const url = new URL(window.location);
        url.searchParams.set('days', days);
        window.location.href = url.toString();
    }
}

// Edit user password strength checker
document.addEventListener('input', function(e) {
    if (!e.target.classList.contains('edit-user-password')) return;
    const input = e.target;
    const wrap  = input.closest('.mb-3');
    const barWrap = wrap.querySelector('.edit-pw-bar-wrap');
    const bar     = wrap.querySelector('.edit-pw-bar');
    const lbl     = wrap.querySelector('.edit-pw-label');
    const reqs    = wrap.querySelector('.edit-pw-reqs');

    barWrap.style.display = 'block';
    reqs.style.display    = 'block';

    const val = input.value;
    const okLength  = val.length >= 8 && val.length <= 20;
    const okUpper   = /[A-Z]/.test(val);
    const okNumber  = /[0-9]/.test(val);
    const okNoSpace = val.length > 0 && !/\s/.test(val);

    function setReq(cls, pass) {
        const el   = wrap.querySelector('.' + cls);
        const icon = el.querySelector('i');
        icon.className = pass ? 'fas fa-check-circle text-success me-2' : 'fas fa-times-circle text-danger me-2';
        el.style.color = pass ? '#198754' : '#dc3545';
    }
    setReq('edit-req-length',  okLength);
    setReq('edit-req-upper',   okUpper);
    setReq('edit-req-number',  okNumber);
    setReq('edit-req-nospace', okNoSpace);

    const score  = [okLength, okUpper, okNumber, okNoSpace].filter(Boolean).length;
    const levels = [
        { pct: 25,  color: '#dc3545', text: 'Weak'   },
        { pct: 50,  color: '#fd7e14', text: 'Fair'   },
        { pct: 75,  color: '#ffc107', text: 'Medium' },
        { pct: 100, color: '#198754', text: 'Strong' },
    ];
    const lvl = levels[score - 1] || { pct: 0, color: '#dee2e6', text: '' };
    bar.style.width      = lvl.pct + '%';
    bar.style.background = lvl.color;
    lbl.textContent      = lvl.text;
    lbl.style.color      = lvl.color;

    if (val === '') {
        barWrap.style.display = 'none';
        reqs.style.display    = 'none';
    }
});

document.addEventListener('focus', function(e) {
    if (!e.target.classList.contains('edit-user-password')) return;
    const wrap = e.target.closest('.mb-3');
    if (e.target.value !== '') {
        wrap.querySelector('.edit-pw-bar-wrap').style.display = 'block';
        wrap.querySelector('.edit-pw-reqs').style.display    = 'block';
    } else {
        wrap.querySelector('.edit-pw-reqs').style.display = 'block';
    }
}, true);

document.addEventListener('click', function(e) {
    if (!e.target.closest('.toggle-edit-pw')) return;
    const btn    = e.target.closest('.toggle-edit-pw');
    const target = document.getElementById(btn.dataset.target);
    if (!target) return;
    const isText = target.type === 'text';
    target.type  = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'fas fa-eye' : 'fas fa-eye-slash';
});
</script>
@endsection

