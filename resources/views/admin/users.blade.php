@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        /* Hide tables on mobile - stronger selectors */
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
        .mobile-user-cards {
            display: block !important;
        }
        
        /* User card styling */
        .user-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .user-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .user-card-header div {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .user-card-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .user-card-id {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        
        .user-card-body {
            margin-bottom: 12px;
        }
        
        .user-card-field {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .user-card-label {
            font-weight: 600;
            min-width: 100px;
            color: #666;
        }
        
        .user-card-value {
            color: #333;
            flex: 1;
            word-break: break-word;
        }
        
        .user-card-value a {
            color: #007bff;
            text-decoration: none;
        }
        
        .user-card-value a:hover {
            text-decoration: underline;
        }
        
        .user-card-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .user-card-actions .btn {
            flex: 1;
            font-size: 12px;
        }
        
        .user-card-actions form {
            flex: 1;
            display: flex;
        }
        
        .user-card-actions form .btn {
            width: 100%;
        }
        
        /* Dark mode support */
        [data-bs-theme="dark"] .user-card {
            background: #2d3238;
            border-color: #495057;
        }
        
        [data-bs-theme="dark"] .user-card-header {
            border-bottom-color: #495057;
        }
        
        [data-bs-theme="dark"] .user-card-id,
        [data-bs-theme="dark"] .user-card-value {
            color: #e9ecef;
        }
        
        [data-bs-theme="dark"] .user-card-label {
            color: #adb5bd;
        }
        
        [data-bs-theme="dark"] .user-card-actions {
            border-top-color: #495057;
        }
    }
    
    /* Hide mobile cards on desktop */
    .mobile-user-cards {
        display: none;
    }
</style>
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
            @if(auth()->user()->canAccess('users_edit'))
            <li><a href="#" onclick="contextEdit()"><i class="fas fa-edit"></i> Edit</a></li>
            @endif
            @if(auth()->user()->canAccess('users_archive'))
            <li><a href="#" onclick="contextArchive()"><i class="fas fa-archive"></i> Archive</a></li>
            @endif
            @if(auth()->user()->canAccess('users_delete'))
            <li><a href="#" onclick="contextDelete()"><i class="fas fa-trash"></i> Delete</a></li>
            @endif
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-12 text-end">
            @if(auth()->user()->canAccess('users_create'))
            <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
                <i class="fas fa-plus"></i> Add User
            </button>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-upload"></i> Import CSV
            </button>
            @endif
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
                        <div class="col-auto position-relative">
                            <input type="text" name="search" id="searchInput" class="form-control form-control-sm" placeholder="Search Name, Email, ID, Mobile, Dept..." 
                                value="{{ request('search') }}" autocomplete="off">
                            <div id="searchSpinner" class="spinner-border spinner-border-sm text-primary position-absolute" 
                                style="right: 10px; top: 50%; transform: translateY(-50%); display: none;" role="status">
                                <span class="visually-hidden">Searching...</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">All Roles</option>
                                <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="faculty" {{ request('role') == 'faculty' ? 'selected' : '' }}>Faculty</option>
                                <option value="mis" {{ request('role') == 'mis' ? 'selected' : '' }}>MIS</option>
                                <option value="school_admin" {{ request('role') == 'school_admin' ? 'selected' : '' }}>School Administrator</option>
                                <option value="building_admin" {{ request('role') == 'building_admin' ? 'selected' : '' }}>Building Administrator</option>
                                <option value="academic_head" {{ request('role') == 'academic_head' ? 'selected' : '' }}>Academic Head</option>
                                <option value="program_head" {{ request('role') == 'program_head' ? 'selected' : '' }}>Program Head</option>
                                <option value="principal_assistant" {{ request('role') == 'principal_assistant' ? 'selected' : '' }}>Principal Assistant</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width:110px;padding-right:2rem">
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
    <!-- Users Table -->
    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">

            <!-- Bulk Actions for Active Users -->
            <div class="bulk-actions mb-3" id="activeBulkActions" style="display: none;">
                <div class="btn-group">
                    @if(auth()->user()->canAccess('users_archive'))
                    <button type="button" class="btn btn-warning btn-sm" onclick="batchArchiveSelected()">
                        <i class="fas fa-archive"></i> Archive Selected
                    </button>
                    @endif
                    @if(auth()->user()->canAccess('users_delete'))
                    <button type="button" class="btn btn-danger btn-sm" onclick="batchDeleteSelected()">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                    @endif
                </div>
                <span class="ms-2 text-muted" id="activeSelectedCount">0 selected</span>
            </div>

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
                            <th>Department</th>
                            <th>Mobile Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr data-id="{{ $user->uuid ?? $user->id }}" data-role="{{ $user->role }}" data-archived="{{ $user->is_archived ? '1' : '0' }}">
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="user-checkbox active-user-checkbox" value="{{ $user->id }}" onchange="updateSelectedCount(); updateActiveBulkActions()"></td>
                                <td>{{ $user->student_id ?? 'N/A' }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php $staffRoles = ['mis','school_admin','building_admin','academic_head','program_head','principal_assistant']; @endphp
                                    @if($user->department)
                                        {{ $user->department }}{{ $user->level ? ' - ' . $user->level : '' }}
                                    @elseif(in_array($user->role, $staffRoles))
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewUser('{{ $user->uuid ?? $user->id }}')" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if(auth()->user()->canAccess('users_edit') && !$user->isProtectedFrom(auth()->user()))
                                        <button type="button" class="btn btn-sm btn-warning" onclick="editUser('{{ $user->uuid ?? $user->id }}')" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->canAccess('users_unlock') && $user->locked_until)
                                        <form action="{{ route('admin.users.unlock', $user->uuid ?? $user->id) }}" method="POST" class="d-inline unlock-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success" title="Unlock Account" onclick="unlockUserAccount(this, '{{ $user->name }}')">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                        </form>
                                        @endif
                                        @if(auth()->user()->canAccess('users_archive') && !$user->isProtectedFrom(auth()->user()))
                                            @if(!$user->is_archived)
                                            <button type="button" class="btn btn-sm btn-secondary" title="Archive" onclick="showUserActionModal('archive', '{{ $user->uuid ?? $user->id }}', '{{ $user->name }}')">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-success" title="Restore" onclick="showUserActionModal('restore', '{{ $user->uuid ?? $user->id }}', '{{ $user->name }}')">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                            @endif
                                        @endif
                                        @if(auth()->user()->canAccess('users_delete') && $user->id !== auth()->id() && !$user->isProtectedFrom(auth()->user()))
                                        <button type="button" class="btn btn-sm btn-danger" title="Delete" onclick="showUserActionModal('delete', '{{ $user->uuid ?? $user->id }}', '{{ $user->name }}')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit User Modal -->
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout for Active Users -->
            <div class="mobile-user-cards">
                @forelse($users as $user)
                    <div class="user-card" data-id="{{ $user->id }}" data-role="{{ $user->role }}">
                        <div class="user-card-header">
                            <div>
                                <input type="checkbox" class="user-card-checkbox user-checkbox active-user-checkbox" value="{{ $user->id }}" onchange="updateSelectedCount(); updateActiveBulkActions()">
                                <span class="user-card-id">{{ $user->name }}</span>
                            </div>
                            <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                        </div>
                        <div class="user-card-body">
                            <div class="user-card-field">
                                <span class="user-card-label">ID:</span>
                                <span class="user-card-value">{{ $user->student_id ?? 'N/A' }}</span>
                            </div>
                            <div class="user-card-field">
                                <span class="user-card-label">Email:</span>
                                <span class="user-card-value">{{ $user->email }}</span>
                            </div>
                            <div class="user-card-field">
                                <span class="user-card-label">Department:</span>
                                <span class="user-card-value">
                                    @php $staffRoles = ['mis','school_admin','building_admin','academic_head','program_head','principal_assistant']; @endphp
                                    @if($user->department)
                                        {{ $user->department }}{{ $user->level ? ' - ' . $user->level : '' }}
                                    @elseif(in_array($user->role, $staffRoles))
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="user-card-field">
                                <span class="user-card-label">Mobile Number:</span>
                                <span class="user-card-value">{{ $user->phone ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="user-card-actions">
                            <button type="button" class="btn btn-sm btn-info" onclick="viewUser('{{ $user->uuid ?? $user->id }}')">
                                <i class="fas fa-eye"></i> View
                            </button>
                            @if(auth()->user()->canAccess('users_edit') && !$user->isProtectedFrom(auth()->user()))
                            <button type="button" class="btn btn-sm btn-warning" onclick="editUser('{{ $user->uuid ?? $user->id }}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            @endif
                            @if(auth()->user()->canAccess('users_archive') && !$user->isProtectedFrom(auth()->user()))
                                @if(!$user->is_archived)
                                <button type="button" class="btn btn-sm btn-secondary" onclick="showUserActionModal('archive', '{{ $user->uuid ?? $user->id }}', '{{ $user->name }}')">
                                    <i class="fas fa-archive"></i> Archive
                                </button>
                                @endif
                            @endif
                            @if(auth()->user()->canAccess('users_delete') && $user->id !== auth()->id() && !$user->isProtectedFrom(auth()->user()))
                            <button type="button" class="btn btn-sm btn-danger" onclick="showUserActionModal('delete', '{{ $user->uuid ?? $user->id }}', '{{ $user->name }}')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <h4 class="text-muted">No users found</h4>
                    </div>
                @endforelse
            </div>
            
            <!-- User Count -->
            <div class="text-muted mt-3 small text-center">
                @if(request('search'))
                    Found {{ $users->total() }} user(s) matching "{{ request('search') }}"
                    @if($users->total() > 0)
                        <span class="text-muted">| Showing {{ $users->firstItem() }}-{{ $users->lastItem() }}</span>
                    @endif
                @else
                    Total active users: {{ $users->total() }}
                @endif
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
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <form method="GET" action="{{ route('admin.users', ['view' => 'archives']) }}" class="d-inline">
                        <input type="hidden" name="view" value="archives">
                        <label class="form-label me-2 mb-0">Show:</label>
                        <select name="per_page" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                            <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10 per page</option>
                            <option value="20" {{ (!request('per_page') || request('per_page') == '20') ? 'selected' : '' }}>20 per page</option>
                            <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50 per page</option>
                            <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 per page</option>
                        </select>
                    </form>
                </div>
                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    <button type="button" class="btn btn-success" onclick="confirmRestoreAllArchived()">
                        <i class="fas fa-trash-restore"></i> Restore All Archived
                    </button>
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAllArchivedModal">
                        <i class="fas fa-trash"></i> Delete All Archived
                    </button>
                </div>
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
                                <td>{{ $folder->created_at->format('m/d/Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.archiveFolderUsers', $folder->id) }}" class="btn btn-sm btn-outline-primary" title="View Users">
                                            <i class="fas fa-folder-open"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success" title="Restore All Users"
                                                data-restore-url="{{ route('admin.users.restoreAllFolder', $folder->id) }}"
                                                data-folder-name="{{ $folder->name }}"
                                                data-user-count="{{ (int) $folder->user_count }}"
                                                onclick="confirmRestoreArchivedFolder(this)">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
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

            <!-- Mobile Card Layout for Archive Folders -->
            <div class="mobile-user-cards">
                @forelse($archiveFolders as $folder)
                    <div class="user-card">
                        <div class="user-card-header">
                            <span class="user-card-id">
                                <i class="fas fa-folder text-warning"></i> {{ $folder->name }}
                            </span>
                            <span class="badge bg-primary">{{ $folder->user_count }} users</span>
                        </div>
                        <div class="user-card-body">
                            <div class="user-card-field">
                                <span class="user-card-label">Description:</span>
                                <span class="user-card-value">{{ $folder->description ?? 'No description' }}</span>
                            </div>
                            <div class="user-card-field">
                                <span class="user-card-label">Created:</span>
                                <span class="user-card-value">{{ $folder->created_at->format('m/d/Y') }}</span>
                            </div>
                        </div>
                        <div class="user-card-actions">
                            <a href="{{ route('admin.archiveFolderUsers', $folder->id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-folder-open"></i> View Users
                            </a>
                            <button type="button" class="btn btn-sm btn-success"
                                    data-restore-url="{{ route('admin.users.restoreAllFolder', $folder->id) }}"
                                    data-folder-name="{{ $folder->name }}"
                                    data-user-count="{{ (int) $folder->user_count }}"
                                    onclick="confirmRestoreArchivedFolder(this)">
                                <i class="fas fa-trash-restore"></i> Restore All
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteFolderModal{{ $folder->id }}">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-folder-open"></i> No archive folders yet. Use "Archive All" to create one.
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($archiveFolders->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $archiveFolders->firstItem() ?? 0 }} to {{ $archiveFolders->lastItem() ?? 0 }} of {{ $archiveFolders->total() }} folders
                </div>
                <div>
                    {{ $archiveFolders->appends(['view' => 'archives', 'per_page' => request('per_page')])->links() }}
                </div>
            </div>
            @endif
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
                        <option value="0" {{ (isset($days) && $days == 0) ? 'selected' : '' }}>Off</option>`r`n                        <option value="3" {{ (isset($days) && $days == 3) ? 'selected' : '' }}>3 days</option>
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

                <!-- Mobile Card Layout for Deleted Users -->
                <div class="mobile-user-cards">
                    @forelse($deletedUsers as $user)
                        <div class="user-card" data-id="{{ $user->id }}">
                            <div class="user-card-header">
                                <div>
                                    <input type="checkbox" class="user-card-checkbox deleted-user-checkbox" value="{{ $user->id }}" onchange="deletedUsersUpdateSelectedCount()">
                                    <span class="user-card-id">{{ $user->name }}</span>
                                </div>
                                <span class="badge bg-{{ 
                                    $user->role == 'admin' ? 'danger' : 
                                    ($user->role == 'school_admin' ? 'dark' : 
                                    ($user->role == 'building_admin' ? 'secondary' : 
                                    ($user->role == 'academic_head' ? 'warning' : 
                                    ($user->role == 'program_head' ? 'info' : 
                                    ($user->role == 'maintenance' ? 'warning' : 
                                    ($user->role == 'faculty' ? 'info' : 'primary'))))))
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                </span>
                            </div>
                            <div class="user-card-body">
                                <div class="user-card-field">
                                    <span class="user-card-label">Email:</span>
                                    <span class="user-card-value">{{ $user->email }}</span>
                                </div>
                                <div class="user-card-field">
                                    <span class="user-card-label">Department:</span>
                                    <span class="user-card-value">{{ $user->department ?? 'N/A' }}</span>
                                </div>
                                <div class="user-card-field">
                                    <span class="user-card-label">Deleted Date:</span>
                                    <span class="user-card-value">{{ $user->updated_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="user-card-field">
                                    <span class="user-card-label">Deleted By:</span>
                                    <span class="user-card-value">{{ $user->deletedBy ? $user->deletedBy->name : 'System' }}</span>
                                </div>
                            </div>
                            <div class="user-card-actions">
                                <form action="{{ route('admin.deletedUsers.restore', $user->id) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                        <i class="fas fa-trash-restore"></i> Restore
                                    </button>
                                </form>
                                <form action="{{ route('admin.deletedUsers.permanentDelete', $user->id) }}" method="POST" style="flex: 1;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Are you sure you want to permanently delete this user?')">
                                        <i class="fas fa-times-circle"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-check-circle fa-2x d-block mb-3"></i>
                                <h5>No Deleted Users</h5>
                                <p class="mb-0">Deleted users will appear here. You can delete users from the User Management page.</p>
                                <a href="{{ route('admin.users') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-users"></i> Go to User Management
                                </a>
                            </div>
                        </div>
                    @endforelse
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
                                        <form action="{{ route('admin.users.unlock', $lu->uuid ?? $lu->id) }}" method="POST" class="d-inline unlock-form">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-success" onclick="unlockUserAccount(this, '{{ $lu->name }}')">
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
@if(false)
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Add New User</h5>
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

                    {{-- â”€â”€ Basic Info â”€â”€ --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}" value="{{ old('first_name') }}" required>
                            <div class="invalid-feedback">{{ $errors->first('first_name') ?: 'First name is required.' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}" value="{{ old('last_name') }}" required>
                            <div class="invalid-feedback">{{ $errors->first('last_name') ?: 'Last name is required.' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email') }}" required>
                            <div class="invalid-feedback">{{ $errors->first('email') ?: 'A valid email is required.' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select name="role" id="addUserRole" class="form-select {{ $errors->has('role') ? 'is-invalid' : '' }}" required onchange="onRoleChange(this.value)">
                                <option value="student"             {{ old('role') == 'student'             ? 'selected' : '' }}>Student</option>
                                <option value="faculty"             {{ old('role') == 'faculty'             ? 'selected' : '' }}>Faculty</option>
                                <option value="mis"                 {{ old('role') == 'mis'                 ? 'selected' : '' }}>MIS</option>
                                <option value="school_admin"        {{ old('role') == 'school_admin'        ? 'selected' : '' }}>School Administrator</option>
                                <option value="building_admin"      {{ old('role') == 'building_admin'      ? 'selected' : '' }}>Building Administrator</option>
                                <option value="academic_head"       {{ old('role') == 'academic_head'       ? 'selected' : '' }}>Academic Head</option>
                                <option value="program_head"        {{ old('role') == 'program_head'        ? 'selected' : '' }}>Program Head</option>
                                <option value="principal_assistant" {{ old('role') == 'principal_assistant' ? 'selected' : '' }}>Principal Assistant</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="departmentField" style="display:{{ old('role') == 'program_head' ? 'block' : 'none' }}">
                            <label class="form-label fw-semibold">Department</label>
                            <select name="department" class="form-select">
                                <option value="">Select Department</option>
                                <option value="GE"                  {{ old('department') == 'GE'                  ? 'selected' : '' }}>GE</option>
                                <option value="ICT"                 {{ old('department') == 'ICT'                 ? 'selected' : '' }}>ICT</option>
                                <option value="Business Management" {{ old('department') == 'Business Management' ? 'selected' : '' }}>Business Management</option>
                                <option value="THM"                 {{ old('department') == 'THM'                 ? 'selected' : '' }}>THM</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mobile Number <small class="text-muted fw-normal">(optional)</small></label>
                            <input type="text" name="phone" class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}" maxlength="11" placeholder="09XXXXXXXXX" value="{{ old('phone') }}">
                            <div class="invalid-feedback">{{ $errors->first('phone') ?: 'Enter a valid 11-digit PH number.' }}</div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" style="margin-bottom: 1rem;">
                                <i class="fas fa-info-circle me-2"></i>
                                <div>
                                    <strong>Auto-Generated Password:</strong> A secure password will be automatically generated and sent to the user's email address.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- â”€â”€ Access Management â”€â”€ --}}
                    <div class="border rounded p-3" style="background:#f8fafc">
                        <div class="mb-3">
                            <span class="fw-semibold"><i class="fas fa-shield-halved me-1 text-primary"></i>Module Access</span>
                            <small class="text-muted ms-2">Select which modules this user can access</small>
                        </div>
                        <input type="hidden" name="use_custom_permissions" value="1">
                        @php
                            $modules    = \App\Models\User::allModules();
                            $subPerms   = \App\Models\User::subPermissions();
                            $oldPerms   = old('permissions', \App\Models\User::defaultPermissions(old('role', 'student')));
                            $hiddenMods = \App\Models\User::roleSpecificHiddenModules(old('role', 'student'));
                            // Auto-add hidden modules to permissions
                            foreach($hiddenMods as $hidden) {
                                if(!in_array($hidden, $oldPerms)) {
                                    $oldPerms[] = $hidden;
                                }
                            }
                        @endphp
                        {{-- Hidden inputs for auto-granted modules --}}
                        @foreach($hiddenMods as $hidden)
                            <input type="hidden" name="permissions[]" value="{{ $hidden }}">
                        @endforeach
                        <div class="row g-2">
                            @foreach($modules as $key => $mod)
                                @if(!in_array($key, $hiddenMods))
                            <div class="col-6 col-md-4 permission-module" data-module="{{ $key }}">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="permissions[]" value="{{ $key }}"
                                           id="perm_{{ $key }}"
                                           {{ in_array($key, $oldPerms) ? 'checked' : '' }}
                                           @if(isset($subPerms[$key])) onchange="toggleSubPerms('add','{{ $key }}',this.checked)" @endif>
                                    <label class="form-check-label" for="perm_{{ $key }}" style="font-size:14px">
                                        {{ $mod['label'] }}
                                    </label>
                                </div>
                                {{-- Sub-permissions --}}
                                @if(isset($subPerms[$key]))
                                <div id="add_sub_{{ $key }}" class="ms-4 mt-1{{ in_array($key, $oldPerms) ? '' : ' d-none' }}">
                                    @foreach($subPerms[$key] as $subKey => $subLabel)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[]" value="{{ $subKey }}"
                                               id="perm_{{ $subKey }}"
                                               {{ in_array($subKey, $oldPerms) ? 'checked' : '' }}>
                                        <label class="form-check-label text-muted" for="perm_{{ $subKey }}" style="font-size:13px">
                                            {{ $subLabel }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="d-flex gap-2 mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllPerms(true)">
                                <i class="fas fa-check-double me-1"></i>Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllPerms(false)">
                                <i class="fas fa-xmark me-1"></i>Clear All
                            </button>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitAddUserForm()">
                        <i class="fas fa-user-plus me-1"></i>Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Import Users from CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">

                    {{-- Step indicator --}}
                    <div class="d-flex align-items-center gap-2 mb-4" id="importStepIndicator">
                        <span class="import-step-dot active" id="dot1">1</span>
                        <div class="flex-grow-1" style="height:2px;background:#dee2e6"></div>
                        <span class="import-step-dot" id="dot2">2</span>
                    </div>

                    {{-- Step 1: Role selection --}}
                    <div id="importStep1">
                        <p class="text-muted mb-3">Select the type of users you are importing:</p>
                        <div class="row g-3">
                            <div class="col-4">
                                <div class="import-role-card border rounded p-3 text-center" onclick="selectImportRole('student')" style="cursor:pointer;transition:all .2s">
                                    <i class="fas fa-user-graduate fa-2x text-primary mb-2"></i>
                                    <div class="fw-semibold">Student</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="import-role-card border rounded p-3 text-center" onclick="selectImportRole('faculty')" style="cursor:pointer;transition:all .2s">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-success mb-2"></i>
                                    <div class="fw-semibold">Faculty</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="import-role-card border rounded p-3 text-center" onclick="selectImportRole('staff')" style="cursor:pointer;transition:all .2s">
                                    <i class="fas fa-user-tie fa-2x text-info mb-2"></i>
                                    <div class="fw-semibold">Staff</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: File upload --}}
                    <div id="importStep2" style="display:none">
                        <div class="d-flex align-items-center mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="importGoTo(1)">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <span>Importing as: <span id="importRoleLabel" class="badge bg-primary fs-6"></span></span>
                        </div>
                        <input type="hidden" name="default_role" id="importRoleInput">
                        <input type="hidden" name="file_format" value="masterlist">

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Archive Folder Name</label>
                            <input type="text" name="archive_folder_name" class="form-control" value="2025-2026" placeholder="e.g., 2025-2026">
                            <small class="text-muted">The folder will be created automatically if it doesn't exist.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">CSV / XLSX File</label>
                            <input type="file" name="file" id="importFileInput" class="form-control" accept=".csv,.txt,.xlsx" required>
                            <small class="text-muted" id="staffImportNote" style="display:none">
                                <i class="fas fa-info-circle text-info"></i> <strong>Staff Import:</strong> 
                                If your CSV has a "STAFF" column (column 7), roles will be automatically assigned based on position 
                                (MIS, School Administrator, Building Administrator, etc.).
                            </small>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Import Users
                            </button>
                        </div>
                    </div>

                    {{-- Step 3: Access control --}}
                    <div id="importStep3" style="display:none">
                        <div class="d-flex align-items-center mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary me-2" onclick="importGoTo(2)">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <span class="fw-semibold">Module Access for imported users</span>
                        </div>

                        <input type="hidden" name="import_use_custom_permissions" value="1">

                        @php
                            $importModules  = \App\Models\User::allModules();
                            $importSubPerms = \App\Models\User::subPermissions();
                        @endphp

                        <div class="border rounded p-3 mb-3" style="background:#f8fafc">
                            <p class="text-muted mb-3" style="font-size:13px">
                                <i class="fas fa-info-circle me-1"></i>
                                These permissions will apply to <strong>all users</strong> in this import batch.
                                Defaults are pre-selected based on the chosen role.
                            </p>
                            <div class="row g-2">
                                @foreach($importModules as $key => $mod)
                                <div class="col-6 col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="import_permissions[]" value="{{ $key }}"
                                               id="import_perm_{{ $key }}"
                                               @if(isset($importSubPerms[$key])) onchange="toggleSubPerms('import','{{ $key }}',this.checked)" @endif>
                                        <label class="form-check-label" for="import_perm_{{ $key }}" style="font-size:14px">
                                            {{ $mod['label'] }}
                                        </label>
                                    </div>
                                    {{-- Sub-permissions --}}
                                    @if(isset($importSubPerms[$key]))
                                    <div id="import_sub_{{ $key }}" class="ms-4 mt-1 d-none">
                                        @foreach($importSubPerms[$key] as $subKey => $subLabel)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="import_permissions[]" value="{{ $subKey }}"
                                                   id="import_perm_{{ $subKey }}">
                                            <label class="form-check-label text-muted" for="import_perm_{{ $subKey }}" style="font-size:13px">
                                                {{ $subLabel }}
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllImportPerms(true)">
                                    <i class="fas fa-check-double me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllImportPerms(false)">
                                    <i class="fas fa-xmark me-1"></i>Clear All
                                </button>
                            </div>
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
.import-step-dot {
    width: 28px; height: 28px; border-radius: 50%;
    background: #dee2e6; color: #6c757d;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.import-step-dot.active { background: #0d6efd; color: #fff; }
.import-step-dot.done   { background: #198754; color: #fff; }
</style>

<script>
// Role defaults for import (mirrors PHP)
const importRoleDefaults = {
    student: ['concerns','settings'],
    faculty: ['events','concerns','settings'],
    staff: ['events','concerns','settings'], // Default for staff, will be overridden by specific role
    mis: ['concerns','events','users','users_create','users_archive','users_lock','users_unlock','users_edit','users_delete','module_access','categories','logs','mis_tasks','settings'],
    school_admin: ['concerns','reports','events','analytics','settings'],
    building_admin: ['concerns','reports','events','analytics','settings'],
    academic_head: ['events','settings'],
    program_head: ['events','settings'],
    principal_assistant: ['events','settings'],
    maintenance: ['reports','concerns','settings'],
};

function selectImportRole(role) {
    document.getElementById('importRoleInput').value = role;
    document.getElementById('importRoleLabel').textContent = role.charAt(0).toUpperCase() + role.slice(1);
    
    // Set badge color based on role
    let badgeClass = 'badge fs-6 ';
    if (role === 'student') badgeClass += 'bg-primary';
    else if (role === 'faculty') badgeClass += 'bg-success';
    else if (role === 'staff') badgeClass += 'bg-info';
    document.getElementById('importRoleLabel').className = badgeClass;
    
    // Show/hide staff import note
    const staffImportNote = document.getElementById('staffImportNote');
    if (staffImportNote) {
        if (role === 'staff') {
            staffImportNote.style.display = 'block';
        } else {
            staffImportNote.style.display = 'none';
        }
    }
    
    // Go to step 2
    importGoTo(2);
}

function importGoTo(step) {
    document.getElementById('importStep1').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('importStep2').style.display = step === 2 ? 'block' : 'none';

    // Update step dots
    ['dot1','dot2'].forEach((id, i) => {
        const dot = document.getElementById(id);
        if (dot) {
            dot.classList.remove('active','done');
            if (i + 1 < step) dot.classList.add('done');
            else if (i + 1 === step) dot.classList.add('active');
        }
    });
}

function backToRoleSelect() { importGoTo(1); }

function selectAllImportPerms(checked) {
    document.querySelectorAll('#importModal input[name="import_permissions[]"]').forEach(cb => {
        cb.checked = checked;
    });
    const sub = document.getElementById('import_sub_users');
    if (sub) sub.classList.toggle('d-none', !checked);
}

// Reset modal when closed
document.getElementById('importModal')?.addEventListener('hidden.bs.modal', function () {
    importGoTo(1);
    const fi = document.querySelector('#importModal input[type=file]');
    if (fi) { fi.value = ''; fi.classList.remove('is-invalid'); }
    document.querySelectorAll('.import-role-card').forEach(c => c.classList.remove('selected'));
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

<script>
function submitArchivedUsersRestore(url) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = @json(csrf_token());
    form.appendChild(csrf);

    document.body.appendChild(form);
    form.submit();
}

function escapeArchivedRestoreText(value) {
    const element = document.createElement('div');
    element.textContent = String(value ?? '');
    return element.innerHTML;
}

function confirmRestoreAllArchived() {
    Swal.fire({
        icon: 'question',
        title: 'Restore all archived users?',
        text: 'Every archived user in every archive folder will be returned to Active Users. Deleted users will not be affected.',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-restore me-1"></i> Restore All',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754'
    }).then(function (result) {
        if (!result.isConfirmed) return;
        Swal.fire({
            title: 'Restoring archived users...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); }
        });
        submitArchivedUsersRestore(@json(route('admin.users.restoreAllArchived')));
    });
}

function confirmRestoreArchivedFolder(button) {
    const folderName = button.dataset.folderName || 'this folder';
    const userCount = Number(button.dataset.userCount || 0);

    Swal.fire({
        icon: 'question',
        title: 'Restore all users in this folder?',
        html: '<strong>' + escapeArchivedRestoreText(folderName) + '</strong><br>' + userCount + ' archived user(s) will be returned to Active Users.',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash-restore me-1"></i> Restore All',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754'
    }).then(function (result) {
        if (!result.isConfirmed) return;
        Swal.fire({
            title: 'Restoring users...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); }
        });
        submitArchivedUsersRestore(button.dataset.restoreUrl);
    });
}
</script>

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

/* Keyboard key styling for search hint */
kbd {
    background-color: #f7f7f7;
    border: 1px solid #ccc;
    border-radius: 3px;
    box-shadow: 0 1px 0 rgba(0,0,0,0.2), 0 0 0 2px #fff inset;
    color: #333;
    display: inline-block;
    font-family: 'Courier New', Courier, monospace;
    font-size: 11px;
    line-height: 1.4;
    margin: 0 2px;
    padding: 2px 6px;
    white-space: nowrap;
}

/* Mobile Pagination Styles */
@media screen and (max-width: 768px) {
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
    
    /* Hide some page numbers on very small screens to save space */
    .pagination .page-item:not(.active):not(.disabled):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
        display: none;
    }
    
    /* Show ellipsis if needed */
    .pagination .page-item.disabled {
        display: inline-block;
    }
    
    /* Ensure pagination container is responsive */
    .d-flex.justify-content-center {
        padding: 0 10px;
    }
    
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 10px;
        align-items: center !important;
    }
    
    .d-flex.justify-content-between > div {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
// Global variable for selected user ID
window.selectedUserId = null;

// Live AJAX search with debouncing (no page refresh)
let searchTimeout = null;
const searchInput = document.getElementById('searchInput');
const searchSpinner = document.getElementById('searchSpinner');
const userFilterForm = document.getElementById('userFilterForm');

if (searchInput) {
    searchInput.addEventListener('input', function() {
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        const searchValue = searchInput.value.trim();
        
        // Show loading spinner
        if (searchSpinner) searchSpinner.style.display = 'inline-block';
        
        // Wait 500ms after user stops typing before searching
        searchTimeout = setTimeout(function() {
            performAjaxSearch(searchValue);
        }, 500);
    });
}

function performAjaxSearch(searchTerm) {
    // Get all form parameters
    const formData = new FormData(userFilterForm);
    const params = new URLSearchParams(formData);
    
    // Make AJAX request
    fetch('{{ route("admin.users") }}?' + params.toString(), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(html => {
        // Parse the response HTML
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Update the table body
        const newTableBody = doc.querySelector('.table tbody');
        const currentTableBody = document.querySelector('.table tbody');
        if (newTableBody && currentTableBody) {
            currentTableBody.innerHTML = newTableBody.innerHTML;
        }
        
        // Update mobile cards if exists
        const newMobileCards = doc.querySelector('.mobile-user-cards');
        const currentMobileCards = document.querySelector('.mobile-user-cards');
        if (newMobileCards && currentMobileCards) {
            currentMobileCards.innerHTML = newMobileCards.innerHTML;
        }
        
        // Update pagination
        const newPagination = doc.querySelector('.pagination');
        const currentPagination = document.querySelector('.pagination');
        if (newPagination && currentPagination) {
            currentPagination.parentElement.innerHTML = newPagination.parentElement.innerHTML;
        }
        
        // Update user count
        const newUserCount = doc.querySelector('.text-muted.mt-3.small.text-center');
        const currentUserCount = document.querySelector('.text-muted.mt-3.small.text-center');
        if (newUserCount && currentUserCount) {
            currentUserCount.innerHTML = newUserCount.innerHTML;
        }
        
        // Update URL without page reload
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({}, '', newUrl);
        
        // Hide loading spinner
        if (searchSpinner) searchSpinner.style.display = 'none';
        
        // Reinitialize event listeners for new elements
        initializeUserTableEvents();
    })
    .catch(error => {
        console.error('Search error:', error);
        if (searchSpinner) searchSpinner.style.display = 'none';
    });
}

// Initialize event listeners for table elements
function initializeUserTableEvents() {
    // Reinitialize checkboxes
    document.querySelectorAll('.user-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            updateActiveBulkActions();
        });
    });
    
    // Reinitialize context menu events
    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            window.selectedUserId = this.getAttribute('data-id');
            showContextMenu(e.pageX, e.pageY);
        });
    });
}

// â”€â”€ Role default permissions map (mirrors User::defaultPermissions) â”€â”€
const roleDefaults = {
    mis:                  ['concerns','events','users','users_create','users_archive','users_lock','users_unlock','users_edit','users_delete','module_access','categories','logs','mis_tasks','settings'],
    school_admin:         ['concerns','reports','events','analytics','settings'],
    building_admin:       ['concerns','reports','events','analytics','settings'],
    academic_head:        ['events','settings'],
    program_head:         ['events','settings'],
    principal_assistant:  ['events','settings'],
    maintenance:          ['reports','concerns','settings'],
    faculty:              ['events','concerns','settings'],
    student:              ['concerns','settings'],
};

const addUserModules = @json(\App\Models\User::allModules());
const addUserSubPermissions = @json(\App\Models\User::subPermissions());
const hiddenModulesBase = @json(\App\Models\User::hiddenModules());

console.log('Initial hiddenModulesBase:', hiddenModulesBase);
console.log('All modules:', addUserModules);
console.log('openAddUserModal function defined:', typeof openAddUserModal);

// Test: What items are generated for student role?
console.log('=== TEST: Generating items for student role ===');
const testItems = [];
const testHidden = [...hiddenModulesBase];
console.log('Test hidden modules:', testHidden);
Object.entries(addUserModules).forEach(([key, module]) => {
    const isHidden = testHidden.includes(key);
    console.log(`Module ${key}: hidden=${isHidden}`);
    if (!isHidden) {
        testItems.push(key);
    }
});
console.log('Test result - visible modules:', testItems);
console.log('=== END TEST ===');

// Get hidden modules for a specific role
function getHiddenModulesForRole(role) {
    // Start with base hidden modules (settings, categories)
    const hidden = [...hiddenModulesBase];
    
    // MIS role automatically gets mis_tasks and module_access, so hide them from UI
    if (role === 'mis') {
        hidden.push('mis_tasks');
        hidden.push('module_access');
    }
    
    console.log('Hidden modules for role', role, ':', hidden);
    return hidden;
}

function escapeAddUserHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function addUserPermissionItems(role = 'student') {
    const items = [];
    const hidden = getHiddenModulesForRole(role);
    const allowed = roleDefaults[role] || ['settings'];
    
    console.log('Building permission items for role:', role, 'Hidden modules:', hidden, 'Allowed modules:', allowed);
    
    Object.entries(addUserModules).forEach(([key, module]) => {
        const isHidden = hidden.includes(key);
        
        // Extract parent module for sub-permissions
        const parentModule = key.split('_')[0];
        const isAllowed = allowed.includes(key) || allowed.includes(parentModule);
        
        console.log('Module:', key, 'Hidden:', isHidden, 'Allowed:', isAllowed);
        
        // Only show if not hidden AND allowed for this role
        if (!isHidden && isAllowed) {
            items.push({ key, label: module.label });
            Object.entries(addUserSubPermissions[key] || {}).forEach(([subKey, subLabel]) => {
                // Check if sub-permission is allowed
                const isSubAllowed = allowed.includes(subKey);
                if (!hidden.includes(subKey) && isSubAllowed) {
                    items.push({ key: subKey, label: `${module.label}: ${subLabel}` });
                }
            });
        }
    });
    
    console.log('Final items count:', items.length);
    return items;
}

function applyAddUserRoleDefaults(role) {
    console.log('=== Applying role defaults for:', role, '===');
    const defaults = roleDefaults[role] || ['settings'];
    const departmentWrap = document.getElementById('swal-add-department-wrap');
    const department = document.getElementById('swal-add-department');
    if (departmentWrap) departmentWrap.hidden = role !== 'program_head';
    if (department && role !== 'program_head') department.value = '';
    
    // Rebuild permission HTML with role-specific visibility
    const permissionsContainer = document.querySelector('.swal-add-permissions');
    console.log('Permissions container found:', !!permissionsContainer);
    if (permissionsContainer) {
        const permissionHtml = addUserPermissionItems(role).map(({ key, label }) => `
            <label class="swal-add-permission-item">
                <input type="checkbox" id="swal-add-permission-${key}" value="${key}" ${defaults.includes(key) ? 'checked' : ''}>
                <span>${escapeAddUserHtml(label)}</span>
            </label>
        `).join('');
        console.log('Setting innerHTML with', permissionHtml.length, 'characters');
        permissionsContainer.innerHTML = permissionHtml;
    }
}

async function openAddUserModal() {
    console.log('=== Opening Add User Modal ===');
    const permissionHtml = addUserPermissionItems('student').map(({ key, label }) => `
        <label class="swal-add-permission-item">
            <input type="checkbox" id="swal-add-permission-${key}" value="${key}">
            <span>${escapeAddUserHtml(label)}</span>
        </label>
    `).join('');

    const result = await Swal.fire({
        title: '<i class="fas fa-user-plus me-2"></i>Add New User',
        html: `
            <style>
                .swal-add-user-form{text-align:left;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px 16px}
                .swal-add-user-field label{display:block;font-weight:600;margin-bottom:6px;color:#1f2937}
                .swal-add-user-field input,.swal-add-user-field select{width:100%;height:46px;border:1px solid #ced4da;border-radius:7px;padding:9px 12px;background:#fff;color:#212529}
                .swal-add-user-wide{grid-column:1/-1}
                .swal-add-permissions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-top:8px;max-height:220px;overflow:auto;padding:2px}
                .swal-add-permission-item{display:flex!important;align-items:flex-start;gap:8px;padding:9px;border:1px solid #dbe3ee;border-radius:7px;font-weight:400!important;background:#fff;cursor:pointer}
                .swal-add-permission-item input{width:17px!important;height:17px!important;margin-top:2px}
                .swal-add-user-note{padding:11px 13px;border-radius:7px;background:#dff6fc;color:#075985}
                @media(max-width:700px){.swal-add-user-form{grid-template-columns:1fr}.swal-add-user-wide{grid-column:1}.swal-add-permissions{grid-template-columns:1fr 1fr}}
            </style>
            <div class="swal-add-user-form">
                <div class="swal-add-user-field"><label for="swal-add-first-name">First Name <span class="text-danger">*</span></label><input id="swal-add-first-name" autocomplete="given-name"></div>
                <div class="swal-add-user-field"><label for="swal-add-last-name">Last Name <span class="text-danger">*</span></label><input id="swal-add-last-name" autocomplete="family-name"></div>
                <div class="swal-add-user-field"><label for="swal-add-email">Primary Email <span class="text-danger">*</span></label><input id="swal-add-email" type="email" autocomplete="email"></div>
                <div class="swal-add-user-field"><label for="swal-add-backup-email">Backup Email <span class="text-muted fw-normal">(optional)</span></label><input id="swal-add-backup-email" type="email" autocomplete="off" placeholder="backup@example.com"></div>
                <div class="swal-add-user-field">
                    <label for="swal-add-role">Role <span class="text-danger">*</span></label>
                    <select id="swal-add-role">
                        <option value="student">Student</option><option value="faculty">Faculty</option><option value="maintenance">Maintenance</option><option value="mis">MIS</option>
                        <option value="school_admin">School Administrator</option><option value="building_admin">Building Administrator</option><option value="academic_head">Academic Head</option>
                        <option value="program_head">Program Head</option><option value="principal_assistant">Principal Assistant</option>
                    </select>
                </div>
                <div class="swal-add-user-field" id="swal-add-department-wrap" hidden>
                    <label for="swal-add-department">Department</label>
                    <select id="swal-add-department"><option value="">Select Department</option><option value="GE">GE</option><option value="ICT">ICT</option><option value="Business Management">Business Management</option><option value="THM">THM</option></select>
                </div>
                <div class="swal-add-user-field"><label for="swal-add-phone">Mobile Number <span class="text-muted fw-normal">(optional)</span></label><input id="swal-add-phone" inputmode="numeric" maxlength="11" placeholder="09XXXXXXXXX"></div>
                <div class="swal-add-user-field"><label for="swal-add-student-id">Student ID <span class="text-muted fw-normal">(optional)</span></label><input id="swal-add-student-id" autocomplete="off"></div>
                <div class="swal-add-user-wide swal-add-user-note"><i class="fas fa-info-circle me-1"></i><strong>Auto-generated password:</strong> Login credentials will be sent to the primary email.</div>
                <div class="swal-add-user-field swal-add-user-wide"><label>Module Access</label><small class="text-muted">Defaults update automatically when the role changes.</small><div class="swal-add-permissions">${permissionHtml}</div></div>
            </div>
        `,
        width: '900px',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-user-plus me-1"></i>Create User',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !Swal.isLoading(),
        didOpen: () => {
            const roleSelect = document.getElementById('swal-add-role');
            roleSelect.addEventListener('change', () => applyAddUserRoleDefaults(roleSelect.value));
            // Apply defaults for initial role (student) - this will filter hidden modules
            applyAddUserRoleDefaults('student');
            document.getElementById('swal-add-first-name').focus();
        },
        preConfirm: async () => {
            const firstName = document.getElementById('swal-add-first-name').value.trim();
            const lastName = document.getElementById('swal-add-last-name').value.trim();
            const email = document.getElementById('swal-add-email').value.trim();
            const backupEmail = document.getElementById('swal-add-backup-email').value.trim();
            const role = document.getElementById('swal-add-role').value;
            const phone = document.getElementById('swal-add-phone').value.trim();
            const studentId = document.getElementById('swal-add-student-id').value.trim();
            const department = document.getElementById('swal-add-department').value;
            const permissions = addUserPermissionItems(role).filter(({ key }) => document.getElementById(`swal-add-permission-${key}`)?.checked).map(({ key }) => key);
            
            // Auto-add hidden modules based on role
            const hiddenMods = getHiddenModulesForRole(role);
            hiddenMods.forEach(mod => {
                if (!permissions.includes(mod)) {
                    permissions.push(mod);
                }
            });

            if (!firstName || !lastName || !email || !role) { Swal.showValidationMessage('First name, last name, primary email, and role are required.'); return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { Swal.showValidationMessage('Enter a valid primary email address.'); return false; }
            if (backupEmail && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(backupEmail)) { Swal.showValidationMessage('Enter a valid backup email address.'); return false; }
            if (backupEmail && backupEmail.toLowerCase() === email.toLowerCase()) { Swal.showValidationMessage('Backup email must be different from the primary email.'); return false; }
            if (phone && !/^09[0-9]{9}$/.test(phone)) { Swal.showValidationMessage('Mobile number must use the 09XXXXXXXXX format.'); return false; }

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            Object.entries({ first_name: firstName, last_name: lastName, email, backup_email: backupEmail, role, phone, student_id: studentId, department, use_custom_permissions: '1' }).forEach(([key, value]) => formData.append(key, value));
            permissions.forEach(permission => formData.append('permissions[]', permission));

            try {
                const response = await fetch('{{ route('admin.users.store') }}', { method: 'POST', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, body: formData });
                const payload = await response.json().catch(() => ({}));
                if (!response.ok) {
                    const messages = Object.values(payload.errors || {}).flat();
                    throw new Error(messages[0] || payload.message || payload.error || 'Unable to create the user.');
                }
                return payload;
            } catch (error) {
                Swal.showValidationMessage(error.message);
                return false;
            }
        },
    });

    if (result.isConfirmed) {
        await Swal.fire({ icon: 'success', title: 'User created', text: result.value?.message || 'The account was created and its credentials were sent by email.', confirmButtonColor: '#0d6efd' });
        window.location.reload();
    }
}

function onRoleChange(role) {
    // Show/hide department field
    document.getElementById('departmentField').style.display =
        role === 'program_head' ? 'block' : 'none';

    // Get role-specific allowed modules
    const allowedModules = roleDefaults[role] || ['settings'];
    
    // Get hidden modules for this role
    const hiddenModules = ['settings', 'categories'];  // Always hidden for all roles
    const isMis = role === 'mis';
    if (isMis) {
        hiddenModules.push('module_access', 'mis_tasks');  // Also hidden for MIS
    }
    
    // Show/hide all modules based on role
    document.querySelectorAll('#addUserModal .permission-module').forEach(moduleDiv => {
        const moduleKey = moduleDiv.dataset.module;
        const checkbox = moduleDiv.querySelector('input[type="checkbox"]');
        
        if (!moduleKey) return;
        
        // Extract parent module for sub-permissions (e.g., 'users_create' -> 'users')
        const parentModule = moduleKey.split('_')[0];
        const isAllowed = allowedModules.includes(moduleKey) || allowedModules.includes(parentModule);
        const isHidden = hiddenModules.includes(moduleKey);
        
        if (isHidden) {
            // Hide auto-granted modules
            moduleDiv.style.display = 'none';
            if (checkbox) checkbox.checked = true;
        } else if (isAllowed) {
            // Show allowed modules
            moduleDiv.style.display = '';
            // Check based on role defaults
            if (checkbox) checkbox.checked = allowedModules.includes(moduleKey);
        } else {
            // Hide modules not allowed for this role
            moduleDiv.style.display = 'none';
            if (checkbox) checkbox.checked = false;
        }
    });

    // Auto-apply role defaults to the checkboxes
    applyRoleDefaults(role);
}

function applyRoleDefaults(role) {
    const defaults = roleDefaults[role] || ['settings'];
    
    // Determine which modules should be hidden for this role
    const hiddenModules = ['settings', 'categories'];  // Always hidden for all roles
    const isMis = role === 'mis';
    
    if (isMis) {
        hiddenModules.push('module_access', 'mis_tasks');  // Also hidden for MIS role
    }
    
    document.querySelectorAll('#addUserModal input[name="permissions[]"]').forEach(cb => {
        const module = cb.value;
        const moduleDiv = cb.closest('.permission-module');
        
        if (!moduleDiv) return;
        
        // Extract parent module for sub-permissions
        const parentModule = module.split('_')[0];
        const isAllowed = defaults.includes(module) || defaults.includes(parentModule);
        const isHidden = hiddenModules.includes(module);
        
        if (isHidden) {
            // Hide auto-granted modules
            moduleDiv.style.display = 'none';
            cb.checked = true;
        } else if (isAllowed) {
            // Show and check allowed modules
            moduleDiv.style.display = '';
            cb.checked = defaults.includes(module);
        } else {
            // Hide modules not allowed for this role
            moduleDiv.style.display = 'none';
            cb.checked = false;
        }
    });
    
    // Show/hide sub-permission sections based on defaults
    const subParents = ['users'];
    subParents.forEach(parent => {
        const checked = defaults.includes(parent);
        const sub = document.getElementById('add_sub_' + parent);
        if (sub) sub.classList.toggle('d-none', !checked);
    });
}

function toggleSubPerms(prefix, parent, checked) {
    const sub = document.getElementById(prefix + '_sub_' + parent);
    if (!sub) return;
    sub.classList.toggle('d-none', !checked);
    // When unchecking parent, also uncheck all sub-permissions
    if (!checked) {
        sub.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
    }
}

function selectAllPerms(checked) {
    document.querySelectorAll('#addUserModal input[name="permissions[]"]').forEach(cb => {
        cb.checked = checked;
    });
    // Show/hide sub-permission sections
    const sub = document.getElementById('add_sub_users');
    if (sub) sub.classList.toggle('d-none', !checked);
}

function selectAllEditPerms(uid, checked) {
    document.querySelectorAll('#editUserModal' + uid + ' input[name="permissions[]"]').forEach(cb => {
        cb.checked = checked;
    });
    // Show/hide sub-permission sections
    const sub = document.getElementById('edit' + uid + '_sub_users');
    if (sub) sub.classList.toggle('d-none', !checked);
}

// Wire up edit-modal role selects for department field and auto-granted modules
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Add User modal on first open
    const addUserModal = document.getElementById('addUserModal');
    if (addUserModal) {
        addUserModal.addEventListener('shown.bs.modal', function () {
            const roleSelect = document.getElementById('addUserRole');
            if (roleSelect) {
                // Trigger role change to filter modules on initial open
                onRoleChange(roleSelect.value);
            }
        });
    }
    
    document.querySelectorAll('.edit-role-select').forEach(function (sel) {
        sel.addEventListener('change', function () {
            const uid = this.dataset.userid;
            const role = this.value;
            const isMis = role === 'mis';
            
            // Show/hide department field
            const deptField = document.getElementById('editDeptField' + uid);
            if (deptField) deptField.style.display = role === 'program_head' ? 'block' : 'none';
            
            // Hide modules that should be auto-granted
            const modal = document.getElementById('editUserModal' + uid);
            if (modal) {
                // Always hide settings and categories (auto-granted for all roles)
                const alwaysHidden = ['settings', 'categories'];
                alwaysHidden.forEach(moduleKey => {
                    const moduleDiv = modal.querySelector(`.permission-module[data-module="${moduleKey}"]`);
                    if (moduleDiv) {
                        moduleDiv.style.display = 'none';
                        const checkbox = moduleDiv.querySelector('input[type="checkbox"]');
                        if (checkbox) checkbox.checked = true;
                    }
                });
                
                // For MIS role, also hide mis_tasks and module_access (auto-granted)
                const misModules = ['module_access', 'mis_tasks'];
                misModules.forEach(moduleKey => {
                    const moduleDiv = modal.querySelector(`.permission-module[data-module="${moduleKey}"]`);
                    if (moduleDiv) {
                        if (isMis) {
                            // Hide and auto-grant for MIS role
                            moduleDiv.style.display = 'none';
                            const checkbox = moduleDiv.querySelector('input[type="checkbox"]');
                            if (checkbox) checkbox.checked = true;
                        } else {
                            // Show for non-MIS roles
                            moduleDiv.style.display = '';
                            const checkbox = moduleDiv.querySelector('input[type="checkbox"]');
                            if (checkbox) checkbox.checked = false;
                        }
                    }
                });
            }
        });
    });
});

function submitAddUserForm() {
    const form = document.getElementById('addUserForm');
    let valid = true;

    // Clear previous errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    const firstName = form.querySelector('[name="first_name"]');
    const lastName  = form.querySelector('[name="last_name"]');
    const email    = form.querySelector('[name="email"]');
    const phone    = form.querySelector('[name="phone"]');

    if (!firstName.value.trim()) { firstName.classList.add('is-invalid'); valid = false; }
    if (!lastName.value.trim())  { lastName.classList.add('is-invalid');  valid = false; }

    if (!email.value.trim() || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
        email.classList.add('is-invalid'); valid = false;
    }

    // Password validation removed - passwords are now auto-generated

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
        
        const filter = searchInput.value.toLowerCase().trim();
        const table = document.querySelector('.card .table');
        if (!table) return;
        
        const rows = table.querySelectorAll('tbody tr');
        let visibleCount = 0;
        
        rows.forEach(row => {
            // Skip if it's a "no results" row
            if (row.classList.contains('no-results-row')) {
                row.remove();
                return;
            }
            
            // Get all text content from the row (name, email, role, department, phone)
            const rowText = row.textContent.toLowerCase();
            
            if (filter === '' || rowText.includes(filter)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show "no results" message if no rows are visible
        const tbody = table.querySelector('tbody');
        let noResultsRow = tbody.querySelector('.no-results-row');
        
        if (visibleCount === 0 && filter !== '') {
            if (!noResultsRow) {
                noResultsRow = document.createElement('tr');
                noResultsRow.className = 'no-results-row';
                noResultsRow.innerHTML = `
                    <td colspan="6" class="text-center py-4 text-muted">
                        <i class="fas fa-search mb-2" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p class="mb-0">No users found matching "<strong>${searchInput.value}</strong>"</p>
                        <small class="text-muted">Try a different search term</small>
                    </td>
                `;
                tbody.appendChild(noResultsRow);
            }
        } else if (noResultsRow) {
            noResultsRow.remove();
        }
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

// View user function - now uses SweetAlert instead of Bootstrap modal
async function viewUser(userUuid) {
    // Show loading
    Swal.fire({
        title: 'Loading...',
        html: '<div class="spinner-border text-primary"></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    try {
        // Fetch user data from server
        const response = await fetch(`/admin/users/${userUuid}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch user data');
        }
        
        const userData = await response.json();
        const allModules = @json(\App\Models\User::allModules());
        const rolePermissionsMap = {
            'mis': @json(\App\Models\User::defaultPermissions('mis')),
            'school_admin': @json(\App\Models\User::defaultPermissions('school_admin')),
            'building_admin': @json(\App\Models\User::defaultPermissions('building_admin')),
            'academic_head': @json(\App\Models\User::defaultPermissions('academic_head')),
            'program_head': @json(\App\Models\User::defaultPermissions('program_head')),
            'principal_assistant': @json(\App\Models\User::defaultPermissions('principal_assistant')),
            'maintenance': @json(\App\Models\User::defaultPermissions('maintenance')),
            'faculty': @json(\App\Models\User::defaultPermissions('faculty')),
            'student': @json(\App\Models\User::defaultPermissions('student')),
        };
        
        // Build permissions list
        const allowedModules = rolePermissionsMap[userData.role] || ['settings'];
        let permissionsHtml = '<div style="max-height:300px;overflow-y:auto">';
        if (userData.permissions && userData.permissions.length > 0) {
            userData.permissions.forEach(perm => {
                if (allModules[perm]) {
                    permissionsHtml += `<span class="badge bg-primary me-1 mb-1">${allModules[perm].label}</span>`;
                }
            });
        } else {
            permissionsHtml += '<span class="text-muted">No permissions set</span>';
        }
        permissionsHtml += '</div>';
        
        // Format dates
        const createdAt = new Date(userData.created_at).toLocaleString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true});
        const updatedAt = new Date(userData.updated_at).toLocaleString('en-US', {month: 'short', day: 'numeric', year: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true});
        
        // Role badge color
        const roleBadgeMap = {
            'mis': 'danger',
            'school_admin': 'dark',
            'building_admin': 'secondary',
            'academic_head': 'warning',
            'program_head': 'info',
            'principal_assistant': 'warning',
            'faculty': 'info',
            'student': 'primary'
        };
        const badgeColor = roleBadgeMap[userData.role] || 'primary';
        
        Swal.fire({
            title: `<i class="fas fa-user me-2"></i>User Details: ${userData.name}`,
            html: `
                <div style="text-align:left">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Name</label>
                            <p>${userData.name}</p>
                        </div>
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Email</label>
                            <p>${userData.email}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Role</label>
                            <p><span class="badge bg-${badgeColor}">${userData.role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</span></p>
                        </div>
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Department</label>
                            <p>${userData.department || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Mobile Number</label>
                            <p>${userData.phone || 'N/A'}</p>
                        </div>
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Student ID</label>
                            <p>${userData.student_id || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Created At</label>
                            <p>${createdAt}</p>
                        </div>
                        <div class="col-6">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Last Updated</label>
                            <p>${updatedAt}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label style="font-weight:bold;display:block;margin-bottom:5px">Module Access</label>
                            ${permissionsHtml}
                        </div>
                    </div>
                </div>
            `,
            width: '700px',
            showCancelButton: true,
            showConfirmButton: {{ auth()->user()->canAccess('users_edit') ? 'true' : 'false' }},
            confirmButtonText: '<i class="fas fa-edit me-1"></i>Edit User',
            cancelButtonText: 'Close',
            confirmButtonColor: '#0d6efd'
        }).then((result) => {
            if (result.isConfirmed) {
                editUser(userUuid);
            }
        });
        
    } catch (error) {
        console.error('Error fetching user:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load user details'
        });
    }
}

// Edit user function with SweetAlert2 - fetch data via AJAX
async function editUser(userUuid) {
    // Show loading while fetching user data
    Swal.fire({
        title: 'Loading...',
        html: '<div class="spinner-border text-primary"></div>',
        showConfirmButton: false,
        allowOutsideClick: false
    });
    
    try {
        // Fetch user data from server
        const response = await fetch(`/admin/users/${userUuid}/edit`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to fetch user data');
        }
        
        const userData = await response.json();
        
        // Get all modules and sub-permissions
        const allModules = @json(\App\Models\User::allModules());
        const subPerms = @json(\App\Models\User::subPermissions());
        const currentRole = userData.role;
        
        // Get default permissions for each role
        const rolePermissionsMap = {
            'mis': @json(\App\Models\User::defaultPermissions('mis')),
            'school_admin': @json(\App\Models\User::defaultPermissions('school_admin')),
            'building_admin': @json(\App\Models\User::defaultPermissions('building_admin')),
            'academic_head': @json(\App\Models\User::defaultPermissions('academic_head')),
            'program_head': @json(\App\Models\User::defaultPermissions('program_head')),
            'principal_assistant': @json(\App\Models\User::defaultPermissions('principal_assistant')),
            'maintenance': @json(\App\Models\User::defaultPermissions('maintenance')),
            'faculty': @json(\App\Models\User::defaultPermissions('faculty')),
            'student': @json(\App\Models\User::defaultPermissions('student')),
        };
        
        // Build module access HTML with role-based visibility
        function buildModuleHtml(role) {
            const allowedModules = rolePermissionsMap[role] || ['settings'];
            
            // Get hidden modules for this role
            const hiddenModules = ['settings', 'categories'];  // Base hidden: settings, categories
            const isMis = role === 'mis';
            const hidden = isMis ? [...hiddenModules, 'mis_tasks', 'module_access'] : hiddenModules;
            
            let moduleHtml = '<div style="max-height:400px;overflow-y:auto">';
            moduleHtml += '<div style="margin-bottom:12px"><strong style="color:#0d6efd"><i class="fas fa-shield-halved me-2"></i>Module Access</strong><br><small class="text-muted">Defaults update automatically when the role changes.</small></div>';
            
            // Use same grid layout as Add User modal - flat grid, no nesting
            moduleHtml += '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:15px">';
            
            Object.keys(allModules).forEach(key => {
                const mod = allModules[key];
                
                // Skip hidden modules (automatically granted)
                if (hidden.includes(key)) return;
                
                const isChecked = userData.permissions && userData.permissions.includes(key);
                
                // Extract parent module from sub-permissions (e.g., 'users_create' -> 'users')
                const parentModule = key.split('_')[0];
                const shouldShow = allowedModules.includes(key) || allowedModules.includes(parentModule);
                
                // Only show modules that are allowed for this role
                if (!shouldShow) return;
                
                // Use same styling as Add User modal - each permission is its own card
                moduleHtml += `<div class="perm-module-item" data-module="${key}" style="background:white;padding:10px;border:1px solid #dee2e6;border-radius:6px">`;
                moduleHtml += `<label style="display:flex;align-items:center;cursor:pointer;margin:0;font-size:14px">`;
                moduleHtml += `<input type="checkbox" class="perm-checkbox" value="${key}" ${isChecked ? 'checked' : ''} style="margin-right:8px;width:18px;height:18px">`;
                moduleHtml += `<span>${mod.label}</span>`;
                moduleHtml += `</label>`;
                moduleHtml += `</div>`;
            });
            
            // Now add sub-permissions as separate cards (not nested)
            Object.keys(allModules).forEach(key => {
                if (subPerms[key]) {
                    Object.keys(subPerms[key]).forEach(subKey => {
                        const subLabel = subPerms[key][subKey];
                        
                        // Skip hidden sub-permissions
                        if (hidden.includes(subKey)) return;
                        
                        const isSubChecked = userData.permissions && userData.permissions.includes(subKey);
                        const shouldShowSub = allowedModules.includes(subKey);
                        
                        // Only show sub-permissions that are allowed for this role
                        if (!shouldShowSub) return;
                        
                        moduleHtml += `<div class="perm-module-item" data-module="${subKey}" style="background:white;padding:10px;border:1px solid #dee2e6;border-radius:6px">`;
                        moduleHtml += `<label style="display:flex;align-items:center;cursor:pointer;margin:0;font-size:14px">`;
                        moduleHtml += `<input type="checkbox" class="perm-checkbox" value="${subKey}" ${isSubChecked ? 'checked' : ''} style="margin-right:8px;width:18px;height:18px">`;
                        moduleHtml += `<span>${allModules[key].label}: ${subLabel}</span>`;
                        moduleHtml += `</label>`;
                        moduleHtml += `</div>`;
                    });
                }
            });
            
            moduleHtml += '</div>';
            
            // Select All / Clear All buttons - match Add User modal style
            moduleHtml += '<div style="display:flex;gap:10px">';
            moduleHtml += '<button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllSwalPerms(true)"><i class="fas fa-check-double me-1"></i>Select All</button>';
            moduleHtml += '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAllSwalPerms(false)"><i class="fas fa-xmark me-1"></i>Clear All</button>';
            moduleHtml += '</div>';
            
            moduleHtml += '</div>';
            
            return moduleHtml;
        }
        
        let moduleHtml = buildModuleHtml(currentRole);
        
        const { value: formValues } = await Swal.fire({
            title: '<i class="fas fa-user-pen me-2"></i>Edit User',
            html: `
                <div style="text-align:left">
                    <div style="margin-bottom:15px">
                        <label style="display:block;font-weight:600;margin-bottom:5px">Name *</label>
                        <input id="swal-name" class="swal2-input" value="${userData.name || ''}" style="width:100%;margin:0" required>
                    </div>
                    <div style="margin-bottom:15px">
                        <label style="display:block;font-weight:600;margin-bottom:5px">Primary Email <i class="fas fa-lock text-muted ms-1" title="Primary email cannot be changed"></i></label>
                        <input id="swal-email" type="email" class="swal2-input" value="${userData.email || ''}" style="width:100%;margin:0;background:#e9ecef;cursor:not-allowed" readonly aria-readonly="true">
                        <small class="text-muted"><i class="fas fa-circle-info me-1"></i>The primary email is locked and cannot be edited.</small>
                    </div>
                    <div style="margin-bottom:15px">
                        <label style="display:block;font-weight:600;margin-bottom:5px">Backup Email</label>
                        <input id="swal-backup-email" type="email" class="swal2-input" value="${userData.backup_email || ''}" placeholder="backup@example.com" style="width:100%;margin:0">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:5px">Role *</label>
                            <select id="swal-role" class="swal2-select" style="width:100%;margin:0" onchange="onSwalRoleChange(this.value)">
                                <option value="student" ${userData.role === 'student' ? 'selected' : ''}>Student</option>
                                <option value="faculty" ${userData.role === 'faculty' ? 'selected' : ''}>Faculty</option>
                                <option value="mis" ${userData.role === 'mis' ? 'selected' : ''}>MIS</option>
                                <option value="school_admin" ${userData.role === 'school_admin' ? 'selected' : ''}>School Administrator</option>
                                <option value="building_admin" ${userData.role === 'building_admin' ? 'selected' : ''}>Building Administrator</option>
                                <option value="academic_head" ${userData.role === 'academic_head' ? 'selected' : ''}>Academic Head</option>
                                <option value="program_head" ${userData.role === 'program_head' ? 'selected' : ''}>Program Head</option>
                                <option value="principal_assistant" ${userData.role === 'principal_assistant' ? 'selected' : ''}>Principal Assistant</option>
                            </select>
                        </div>
                        <div id="swal-dept-field" style="${userData.role === 'program_head' ? '' : 'display:none'}">
                            <label style="display:block;font-weight:600;margin-bottom:5px">Department</label>
                            <select id="swal-dept" class="swal2-select" style="width:100%;margin:0">
                                <option value="">Select Department</option>
                                <option value="GE" ${userData.department === 'GE' ? 'selected' : ''}>GE</option>
                                <option value="ICT" ${userData.department === 'ICT' ? 'selected' : ''}>ICT</option>
                                <option value="Business Management" ${userData.department === 'Business Management' ? 'selected' : ''}>Business Management</option>
                                <option value="THM" ${userData.department === 'THM' ? 'selected' : ''}>THM</option>
                            </select>
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px">
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:5px">Mobile Number</label>
                            <input id="swal-phone" class="swal2-input" value="${userData.phone || ''}" placeholder="09XXXXXXXXX" maxlength="11" style="width:100%;margin:0">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;margin-bottom:5px">Student ID</label>
                            <input id="swal-studentid" class="swal2-input" value="${userData.student_id || ''}" style="width:100%;margin:0">
                        </div>
                    </div>
                    <div style="margin-bottom:15px;padding:15px;background:#f8f9fa;border-radius:8px;border:1px solid #e9ecef">
                        <label style="display:flex;align-items:center;cursor:pointer;margin:0">
                            <input type="checkbox" id="swal-reset-password" style="width:18px;height:18px;margin-right:10px">
                            <div>
                                <span style="font-weight:600;color:#333"><i class="fas fa-key text-primary me-1"></i>Reset Password</span>
                                <div style="font-size:13px;color:#666;margin-top:4px">
                                    <i class="fas fa-info-circle text-info me-1"></i>Check this box to generate a new password and send it to the user's email address
                                </div>
                            </div>
                        </label>
                    </div>
                    ${moduleHtml}
                </div>
            `,
            width: '900px',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-save me-1"></i>Update User',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#0d6efd',
            preConfirm: () => {
                const name = document.getElementById('swal-name').value;
                const email = document.getElementById('swal-email').value;
                const backupEmail = document.getElementById('swal-backup-email').value.trim();
                const role = document.getElementById('swal-role').value;
                const dept = document.getElementById('swal-dept')?.value || '';
                const phone = document.getElementById('swal-phone').value;
                const studentId = document.getElementById('swal-studentid').value;
                const resetPassword = document.getElementById('swal-reset-password').checked;
                const permissions = Array.from(document.querySelectorAll('.perm-checkbox:checked')).map(cb => cb.value);
                
                if (!name || !email || !role) {
                    Swal.showValidationMessage('Please fill in all required fields');
                    return false;
                }

                if (backupEmail && backupEmail.toLowerCase() === email.toLowerCase()) {
                    Swal.showValidationMessage('Backup email must be different from the primary email');
                    return false;
                }
                
                return { name, email, backupEmail, role, dept, phone, studentId, resetPassword, permissions };
            }
        });
        
        if (formValues) {
            // Submit the form
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'PUT');
            formData.append('name', formValues.name);
            formData.append('email', formValues.email);
            formData.append('backup_email', formValues.backupEmail);
            formData.append('role', formValues.role);
            formData.append('department', formValues.dept);
            formData.append('phone', formValues.phone);
            formData.append('student_id', formValues.studentId);
            if (formValues.resetPassword) {
                formData.append('reset_password', '1');
            }
            formData.append('use_custom_permissions', '1');
            formValues.permissions.forEach(perm => {
                formData.append('permissions[]', perm);
            });
            
            // Show loading
            Swal.fire({
                title: 'Updating...',
                html: '<div class="spinner-border text-primary"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            try {
                const updateResponse = await fetch(`/admin/users/${userUuid}`, {
                    method: 'POST',
                    body: formData
                });
                
                if (updateResponse.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'User updated successfully',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    const data = await updateResponse.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update user'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating the user'
                });
            }
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load user data. Please try again.'
        });
    }
}

// Helper functions for SweetAlert2 module access
function toggleSwalSubPerms(key, checked) {
    const subDiv = document.getElementById('swal_sub_' + key);
    if (subDiv) {
        subDiv.style.display = checked ? 'block' : 'none';
    }
}

function selectAllSwalPerms(checked) {
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
        cb.checked = checked;
        // Trigger change event for parent checkboxes
        if (cb.hasAttribute('onchange')) {
            cb.dispatchEvent(new Event('change'));
        }
    });
}

function toggleSwalDept() {
    const role = document.getElementById('swal-role').value;
    const deptField = document.getElementById('swal-dept-field');
    if (deptField) {
        deptField.style.display = role === 'program_head' ? 'block' : 'none';
    }
}

// Handle role change in SweetAlert modal - rebuild modules to show role-appropriate permissions
function onSwalRoleChange(role) {
    // Toggle department field
    toggleSwalDept();
    
    // Get the module container
    const moduleContainer = document.querySelector('.swal2-html-container > div > div:last-child');
    if (!moduleContainer) return;
    
    // Get role permissions map (needs to be defined in the same scope as editUser)
    const rolePermissionsMap = {
        'mis': @json(\App\Models\User::defaultPermissions('mis')),
        'school_admin': @json(\App\Models\User::defaultPermissions('school_admin')),
        'building_admin': @json(\App\Models\User::defaultPermissions('building_admin')),
        'academic_head': @json(\App\Models\User::defaultPermissions('academic_head')),
        'program_head': @json(\App\Models\User::defaultPermissions('program_head')),
        'principal_assistant': @json(\App\Models\User::defaultPermissions('principal_assistant')),
        'maintenance': @json(\App\Models\User::defaultPermissions('maintenance')),
        'faculty': @json(\App\Models\User::defaultPermissions('faculty')),
        'student': @json(\App\Models\User::defaultPermissions('student')),
    };
    
    const allModules = @json(\App\Models\User::allModules());
    const subPerms = @json(\App\Models\User::subPermissions());
    const allowedModules = rolePermissionsMap[role] || ['settings'];
    
    // Get hidden modules for this role
    const hiddenModules = ['settings', 'categories'];
    const isMis = role === 'mis';
    const hidden = isMis ? [...hiddenModules, 'mis_tasks', 'module_access'] : hiddenModules;
    
    // Get currently checked permissions before rebuild
    const currentChecked = Array.from(document.querySelectorAll('.perm-checkbox:checked')).map(cb => cb.value);
    
    // Rebuild the module access HTML
    const modulesGrid = moduleContainer.querySelector('div[style*="grid-template-columns"]');
    if (!modulesGrid) return;
    
    modulesGrid.innerHTML = '';
    
    // First add main modules
    Object.keys(allModules).forEach(key => {
        const mod = allModules[key];
        
        // Skip hidden modules (automatically granted)
        if (hidden.includes(key)) return;
        
        // Only show modules allowed for this role
        const parentModule = key.split('_')[0];
        const shouldShow = allowedModules.includes(key) || allowedModules.includes(parentModule);
        
        if (!shouldShow) return; // Skip modules not relevant to this role
        
        // Check if this was previously checked
        const isChecked = currentChecked.includes(key) && shouldShow;
        
        // Match Add User modal styling - flat grid, no nesting
        let moduleHtml = `<div class="perm-module-item" data-module="${key}" style="background:white;padding:10px;border:1px solid #dee2e6;border-radius:6px">`;
        moduleHtml += `<label style="display:flex;align-items:center;cursor:pointer;margin:0;font-size:14px">`;
        moduleHtml += `<input type="checkbox" class="perm-checkbox" value="${key}" ${isChecked ? 'checked' : ''} style="margin-right:8px;width:18px;height:18px">`;
        moduleHtml += `<span>${mod.label}</span>`;
        moduleHtml += `</label>`;
        moduleHtml += `</div>`;
        
        modulesGrid.insertAdjacentHTML('beforeend', moduleHtml);
    });
    
    // Then add sub-permissions as separate cards (not nested)
    Object.keys(allModules).forEach(key => {
        if (subPerms[key]) {
            Object.keys(subPerms[key]).forEach(subKey => {
                const subLabel = subPerms[key][subKey];
                
                // Skip hidden sub-permissions
                if (hidden.includes(subKey)) return;
                
                const shouldShowSub = allowedModules.includes(subKey);
                
                if (!shouldShowSub) return; // Skip sub-permissions not allowed for this role
                
                const isSubChecked = currentChecked.includes(subKey) && shouldShowSub;
                
                let moduleHtml = `<div class="perm-module-item" data-module="${subKey}" style="background:white;padding:10px;border:1px solid #dee2e6;border-radius:6px">`;
                moduleHtml += `<label style="display:flex;align-items:center;cursor:pointer;margin:0;font-size:14px">`;
                moduleHtml += `<input type="checkbox" class="perm-checkbox" value="${subKey}" ${isSubChecked ? 'checked' : ''} style="margin-right:8px;width:18px;height:18px">`;
                moduleHtml += `<span>${allModules[key].label}: ${subLabel}</span>`;
                moduleHtml += `</label>`;
                moduleHtml += `</div>`;
                
                modulesGrid.insertAdjacentHTML('beforeend', moduleHtml);
            });
        }
    });
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

// Bulk archive functions - Updated
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox:not(:disabled)');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedCount();
    updateActiveBulkActions();
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

// Update bulk actions visibility for Active Users
function updateActiveBulkActions() {
    const selected = document.querySelectorAll('.active-user-checkbox:checked');
    const bulkActions = document.getElementById('activeBulkActions');
    const countSpan = document.getElementById('activeSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

// Batch archive selected users
function batchArchiveSelected() {
    const selected = document.querySelectorAll('.active-user-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Users Selected',
            text: 'Please select users to archive'
        });
        return;
    }
    
    Swal.fire({
        title: 'Archive Users?',
        text: `Are you sure you want to archive ${ids.length} user(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, archive them',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/admin/users/batch-archive', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_ids: ids })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archived!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error archiving users'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error archiving users'
                });
            });
        }
    });
}

// Batch delete selected users
function batchDeleteSelected() {
    const selected = document.querySelectorAll('.active-user-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'No Users Selected',
            text: 'Please select users to delete'
        });
        return;
    }
    
    Swal.fire({
        title: 'Delete Users?',
        html: `Are you sure you want to delete ${ids.length} user(s)?<br><small>They will be moved to deleted users.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete them',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/admin/users/batch-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ user_ids: ids })
            }).then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error deleting users'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error deleting users'
                });
            });
        }
    });
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
    const okSpecial = /[@$!%*?&]/.test(val);
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
    setReq('edit-req-special', okSpecial);
    setReq('edit-req-nospace', okNoSpace);

    const score  = [okLength, okUpper, okNumber, okSpecial, okNoSpace].filter(Boolean).length;
    const levels = [
        { pct: 20,  color: '#dc3545', text: 'Weak'   },
        { pct: 40,  color: '#fd7e14', text: 'Weak'   },
        { pct: 60,  color: '#ffc107', text: 'Fair'   },
        { pct: 80,  color: '#0dcaf0', text: 'Medium' },
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

// Unlock user account with SweetAlert
function unlockUserAccount(button, userName) {
    const form = button.closest('form');
    
    swalConfirm({
        title: 'Unlock Account?',
        text: 'Unlock account for ' + userName + '?',
        icon: 'question',
        confirmButtonText: 'Yes, Unlock',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Unlocking...',
                html: '<div class="spinner-border text-success"></div>',
                allowOutsideClick: false,
                showConfirmButton: false
            });
            
            // Submit the form
            form.submit();
        }
    });
}
</script>
@endsection
