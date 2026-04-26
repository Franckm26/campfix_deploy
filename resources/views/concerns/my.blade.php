@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2>My Concerns</h2>
@endsection

@section('content')
<div class="container-fluid px-3">
    
    <!-- Context Menu -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li><a href="#" id="ctxView" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" id="ctxEdit" onclick="contextEdit()"><i class="fas fa-edit"></i> Edit</a></li>
            <li><a href="#" id="ctxArchive" onclick="contextArchive()"><i class="fas fa-archive"></i> Archive</a></li>
            <li><a href="#" id="ctxDelete" onclick="contextDelete()"><i class="fas fa-trash"></i> Delete</a></li>
        </ul>
    </div>

    <!-- Context Menu for Archives -->
    <div id="contextMenuArchive" class="context-menu">
        <ul>
            <li><a href="#" id="ctxViewArchive" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" id="ctxRestore" onclick="contextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" id="ctxDeleteArchive" onclick="contextDeleteFromArchive()"><i class="fas fa-trash"></i> Delete</a></li>
        </ul>
    </div>

    <!-- Context Menu for Deleted -->
    <div id="contextMenuDeleted" class="context-menu">
        <ul>
            <li><a href="#" id="ctxViewDeleted" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" id="ctxRestoreDeleted" onclick="contextRestoreDeleted()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" id="ctxPermanentDelete" onclick="contextPermanentDelete()"><i class="fas fa-ban"></i> Permanent Delete</a></li>
        </ul>
    </div>


    <!-- Tabs Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <ul class="nav nav-tabs border-0 mb-0" id="concernTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? 'active') === 'active' ? 'active' : '' }}" 
                    id="active-tab" data-bs-toggle="tab" data-bs-target="#active-concerns" 
                    type="button" role="tab" aria-controls="active-concerns" 
                    aria-selected="{{ ($viewType ?? 'active') === 'active' ? 'true' : 'false' }}">
                <i class="fas fa-list"></i> Active
                @if(isset($concerns) && $concerns->count() > 0)
                    <span class="badge bg-primary ms-1">{{ $concerns->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'resolved' ? 'active' : '' }}" 
                    id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved-concerns" 
                    type="button" role="tab" aria-controls="resolved-concerns" 
                    aria-selected="{{ ($viewType ?? '') === 'resolved' ? 'true' : 'false' }}">
                <i class="fas fa-check-circle"></i> Resolved
                @if(isset($resolvedConcerns) && $resolvedConcerns->count() > 0)
                    <span class="badge bg-success ms-1">{{ $resolvedConcerns->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'archives' ? 'active' : '' }}" 
                    id="archives-tab" data-bs-toggle="tab" data-bs-target="#archived-concerns" 
                    type="button" role="tab" aria-controls="archived-concerns" 
                    aria-selected="{{ ($viewType ?? '') === 'archives' ? 'true' : 'false' }}">
                <i class="fas fa-archive"></i> Archives
                @if(isset($archivedConcerns) && $archivedConcerns->count() > 0)
                    <span class="badge bg-warning ms-1">{{ $archivedConcerns->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'deleted' ? 'active' : '' }}" 
                    id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted-concerns" 
                    type="button" role="tab" aria-controls="deleted-concerns" 
                    aria-selected="{{ ($viewType ?? '') === 'deleted' ? 'true' : 'false' }}">
                <i class="fas fa-trash"></i> Deleted
                @if(isset($deletedConcerns) && $deletedConcerns->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $deletedConcerns->count() }}</span>
                @endif
            </button>
        </li>
        </ul>
        @if(auth()->user()->role !== 'maintenance')
            <button type="button" class="btn btn-primary btn-sm" onclick="openNewConcernModal()">
                <i class="fas fa-plus"></i> New Concern
            </button>
        @endif
    </div>

    <!-- Tab Content -->
    <div class="tab-content show" id="concernTabContent">
        
        <!-- Active Concerns Tab -->
        <div class="tab-pane fade {{ ($viewType ?? 'active') === 'active' ? 'show active' : '' }}" 
             id="active-concerns" role="tabpanel" aria-labelledby="active-tab">
            
            <!-- Filters for Active -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('concerns.my') }}" class="row g-2 align-items-center" id="filterForm">
                        <input type="hidden" name="view" value="active">
                        <div class="col-6 col-md">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." 
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-md">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">All Priority</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('concerns.my', ['view' => 'active']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Active Concerns List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Active -->
                    <div class="bulk-actions mb-3" id="activeBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm" onclick="batchArchiveSelected()">
                                <i class="fas fa-archive"></i> Archive Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchSoftDeleteSelected()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="activeSelectedCount">0 selected</span>
                    </div>

                    @if($concerns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAllActive" onchange="toggleSelectAll('active')"></th>
                                        <th style="width:100px;white-space:nowrap;">Issue</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        @if(auth()->user()->role === 'mis')
                                        <th>Reported By</th>
                                        @endif
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($concerns as $concern)
                                        <tr data-id="{{ $concern->id }}" data-view="active">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="active-checkbox" value="{{ $concern->id }}" onchange="updateActiveBulkActions()"></td>
                                            <td style="width:100px;max-width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                <a href="#" onclick="event.preventDefault(); viewConcern({{ $concern->id }});">
                                                    {{ $concern->title ?? 'No Title' }}
                                                </a>
                                                @if($concern->image_path)
                                                    <i class="fas fa-image text-muted" title="Has photo"></i>
                                                @endif
                                            </td>
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
                                            @if(auth()->user()->role === 'mis')
                                            <td>
                                                @if($concern->is_anonymous)
                                                    Anonymous
                                                @else
                                                    {{ $concern->user->name ?? 'Unknown' }}
                                                    @if($concern->user)
                                                        <small class="text-muted">({{ $concern->user->role }})</small>
                                                    @endif
                                                @endif
                                            </td>
                                            @endif
                                            <td>{{ $concern->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info bg-transparent border-0" onclick="viewConcern({{ $concern->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($concern->user_id == auth()->id() && $concern->status == 'Pending')
                                                    <button type="button" class="btn btn-sm btn-warning bg-transparent border-0" onclick="editConcern({{ $concern->id }})" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    @endif
                                                    @if($concern->status == 'Pending' && !$concern->assigned_to && $concern->created_at->diffInDays(now()) >= 1)
                                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0" onclick="sendFollowUp({{ $concern->id }})" title="Send Follow-up">
                                                        <i class="fas fa-bell"></i>
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
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h4 class="text-muted">No active concerns found</h4>
                        </div>
                    @endif

                    <!-- Pagination -->
                    @if(method_exists($concerns, 'hasPages') && $concerns->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $concerns->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Resolved Concerns Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'resolved' ? 'show active' : '' }}" 
             id="resolved-concerns" role="tabpanel" aria-labelledby="resolved-tab">
            
            <!-- Filters for Resolved -->
            @if(($viewType ?? '') === 'resolved' || isset($resolvedConcerns))
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('concerns.my') }}" class="row g-2 align-items-center" id="resolvedFilterForm">
                        <input type="hidden" name="view" value="resolved">
                        <div class="col-6 col-md">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." 
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-md">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">All Priority</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('concerns.my', ['view' => 'resolved']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resolved Concerns List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Resolved -->
                    <div class="bulk-actions mb-3" id="resolvedBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm" onclick="batchArchiveResolved()">
                                <i class="fas fa-archive"></i> Archive Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchSoftDeleteResolved()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="resolvedSelectedCount">0 selected</span>
                    </div>

                    @if(isset($resolvedConcerns) && $resolvedConcerns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAllResolved" onchange="toggleSelectAll('resolved')"></th>
                                        <th style="width:100px;white-space:nowrap;">Issue</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Priority</th>
                                        @if(auth()->user()->role === 'mis')
                                        <th>Reported By</th>
                                        @endif
                                        <th>Resolved Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resolvedConcerns as $concern)
                                        <tr data-id="{{ $concern->id }}" data-view="resolved">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="resolved-checkbox" value="{{ $concern->id }}" onchange="updateResolvedBulkActions()"></td>
                                            <td style="width:100px;max-width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                <a href="#" onclick="event.preventDefault(); viewConcern({{ $concern->id }});">
                                                    {{ $concern->title ?? 'No Title' }}
                                                </a>
                                                @if($concern->image_path)
                                                    <i class="fas fa-image text-muted" title="Has photo"></i>
                                                @endif
                                            </td>
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
                                            @if(auth()->user()->role === 'mis')
                                            <td>
                                                @if($concern->is_anonymous)
                                                    Anonymous
                                                @else
                                                    {{ $concern->user->name ?? 'Unknown' }}
                                                    @if($concern->user)
                                                        <small class="text-muted">({{ $concern->user->role }})</small>
                                                    @endif
                                                @endif
                                            </td>
                                            @endif
                                            <td>{{ $concern->updated_at->format('M d, Y') }}</td>
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
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h4 class="text-muted">No resolved concerns found</h4>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Archived Concerns Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'archives' ? 'show active' : '' }}" 
             id="archived-concerns" role="tabpanel" aria-labelledby="archives-tab">
            
            <!-- Filters for Archives -->
            @if(($viewType ?? '') === 'archives' || isset($archivedConcerns))
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('concerns.my') }}" class="row g-2 align-items-center" id="archiveFilterForm">
                        <input type="hidden" name="view" value="archives">
                        <div class="col-6 col-md">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." 
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-md">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('concerns.my', ['view' => 'archives']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Archived Concerns List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Archives -->
                    <div class="bulk-actions mb-3" id="archiveBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreArchived()">
                                <i class="fas fa-trash-restore"></i> Restore Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchSoftDeleteArchived()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="archiveSelectedCount">0 selected</span>
                    </div>

                    @if(isset($archivedConcerns) && $archivedConcerns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAllArchive" onchange="toggleSelectAll('archive')"></th>
                                        <th style="width:100px;white-space:nowrap;">Issue</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Archived By</th>
                                        <th>Archived At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($archivedConcerns as $concern)
                                        <tr data-id="{{ $concern->id }}" data-view="archive">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="archive-checkbox" value="{{ $concern->id }}" onchange="updateArchiveBulkActions()"></td>
                                            <td style="width:100px;max-width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                <a href="#" onclick="event.preventDefault(); viewConcern({{ $concern->id }});">
                                                    {{ $concern->title ?? 'No Title' }}
                                                </a>
                                            </td>
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
                                            <td>{{ $concern->archivedByUsers->first()?->name ?? 'Self' }}</td>
                                            <td>{{ $concern->archivedByUsers->first()?->pivot->archived_at ? \Carbon\Carbon::parse($concern->archivedByUsers->first()->pivot->archived_at)->format('M d, Y g:i A') : $concern->updated_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info bg-transparent border-0" onclick="viewConcern({{ $concern->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('concerns.restore', $concern->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success bg-transparent border-0" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" onclick="softDeleteArchivedConcern({{ $concern->id }})" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h4 class="text-muted">No archived concerns found</h4>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Deleted Concerns Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'deleted' ? 'show active' : '' }}"
             id="deleted-concerns" role="tabpanel" aria-labelledby="deleted-tab">

            <!-- Auto-delete Settings -->
            <div class="card mb-4 border-info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-1"><i class="fas fa-clock"></i> Auto-delete Settings</h5>
                            <p class="mb-0 text-muted">Automatically delete concerns that have been in the deleted folder for the selected period.</p>
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
                                    body: JSON.stringify({ days: parseInt(days), module: 'concerns' })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        // Reload page to show updated filter
                                        location.reload();
                                    } else {
                                        alert('Error saving preference: ' + (data.error || 'Unknown error'));
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    alert('Error saving preference');
                                });
                            ">
                                <option value="3" {{ ($days ?? 15) == 3 ? 'selected' : '' }}>3 days</option>
                                <option value="7" {{ ($days ?? 15) == 7 ? 'selected' : '' }}>7 days</option>
                                <option value="15" {{ ($days ?? 15) == 15 ? 'selected' : '' }}>15 days</option>
                                <option value="30" {{ ($days ?? 15) == 30 ? 'selected' : '' }}>30 days</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters for Deleted -->
            @if(($viewType ?? '') === 'deleted' || isset($deletedConcerns))
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('concerns.my') }}" class="row g-2 align-items-center" id="deletedFilterForm">
                        <input type="hidden" name="view" value="deleted">
                        <div class="col-6 col-md">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." 
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-md">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Assigned" {{ request('status') == 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('concerns.my', ['view' => 'deleted']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Deleted Concerns List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Deleted -->
                    <div class="bulk-actions mb-3" id="deletedBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreDeleted()">
                                <i class="fas fa-trash-restore"></i> Restore Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchPermanentDeleteSelected()">
                                <i class="fas fa-ban"></i> Permanent Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="deletedSelectedCount">0 selected</span>
                    </div>

                    @if(isset($deletedConcerns) && $deletedConcerns->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAllDeleted" onchange="toggleSelectAll('deleted')"></th>
                                        <th style="width:100px;white-space:nowrap;">Issue</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Deleted By</th>
                                        <th>Deleted At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deletedConcerns as $concern)
                                        <tr data-id="{{ $concern->id }}" data-view="deleted">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-checkbox" value="{{ $concern->id }}" onchange="updateDeletedBulkActions()"></td>
                                            <td style="width:100px;max-width:100px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                <a href="#" onclick="event.preventDefault(); viewConcern({{ $concern->id }});">
                                                    {{ $concern->title ?? 'No Title' }}
                                                </a>
                                            </td>
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
                                            <td>{{ $concern->deletedBy ? $concern->deletedBy->name : 'System' }}</td>
                                            <td>{{ $concern->deleted_at ? $concern->deleted_at->format('M d, Y g:i A') : 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info bg-transparent border-0" onclick="viewConcern({{ $concern->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('concerns.restore-deleted', $concern->id) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success bg-transparent border-0" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" onclick="permanentDeleteConcern({{ $concern->id }})" title="Permanent Delete">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <h4 class="text-muted">No deleted concerns found</h4>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewConcernModal" tabindex="-1" aria-labelledby="viewConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewConcernModalLabel">Concern Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewConcernContent">
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

<!-- Edit Modal -->
<div class="modal fade" id="editConcernModal" tabindex="-1" aria-labelledby="editConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editConcernModalLabel">Edit Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editConcernForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editConcernContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Concern</button>
                </div>
            </form>
        </div>
</div>
</div>

<!-- Assign Modal -->
<div class="modal fade" id="assignConcernModal" tabindex="-1" aria-labelledby="assignConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignConcernModalLabel">Assign Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignConcernForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="assignConcernId" name="concern_id" value="">
                    <div class="mb-3">
                        <label for="assigned_to" class="form-label">Assign to Maintenance Staff</label>
                        <select class="form-select" id="assigned_to" name="assigned_to" required>
                            <option value="">Select maintenance staff</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="archiveModal" tabindex="-1" aria-labelledby="archiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="archiveModalLabel">Archive Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="archiveConcernForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="archiveConcernId" name="concern_id" value="">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This concern will be archived and hidden from your active list.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-archive"></i> Archive</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Soft Delete Modal -->
<div class="modal fade" id="softDeleteModal" tabindex="-1" aria-labelledby="softDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="softDeleteModalLabel">Delete Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="softDeleteConcernForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="softDeleteConcernId" name="concern_id" value="">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> This concern will be moved to deleted. You can restore it later from the Deleted tab.
                    </div>
                    <p class="mb-0">Are you sure you want to delete this concern?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Permanent Delete Modal -->
<div class="modal fade" id="permanentDeleteModal" tabindex="-1" aria-labelledby="permanentDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="permanentDeleteModalLabel"><i class="fas fa-exclamation-triangle"></i> Permanently Delete Concern</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="permanentDeleteConcernId" value="">
                <div class="alert alert-danger">
                    <i class="fas fa-ban"></i> <strong>Warning:</strong> This action cannot be undone! The concern will be permanently removed.
                </div>
                <p class="mb-0">Are you sure you want to permanently delete this concern?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmPermanentDeleteBtn"><i class="fas fa-ban"></i> Permanent Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Concern Warning Modal -->
@if(session('warning'))
<div class="modal fade" id="duplicateConcernWarningModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Concern Already Reported</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-tools fa-3x text-warning"></i>
                </div>
                <p class="mb-0">{{ session('warning') }}</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal">Got it</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var warningModal = new bootstrap.Modal(document.getElementById('duplicateConcernWarningModal'));
        warningModal.show();
    });
</script>
@endif

<!-- New Concern Modal -->
<div class="modal fade" id="newConcernModal" tabindex="-1" aria-labelledby="newConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newConcernModalLabel">Submit New Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('concerns.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                {{-- hidden title populated from Issue dropdown --}}
                <input type="hidden" id="new_title" name="title">
                <div class="modal-body">

                    <div class="mb-3">
                        <label for="new_category_id" class="form-label">Category *</label>
                        <select class="form-select" id="new_category_id" name="category_id" required>
                            <option value="" disabled selected>Select a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" data-name="{{ strtolower(trim($category->name)) }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Issue dropdown -->
                    <div class="mb-3" id="new_issue_container">
                        <label for="new_issue" class="form-label">Issue *</label>
                        <select class="form-select" id="new_issue" name="_issue" required>
                            <option value="" disabled selected>Select an issue</option>
                        </select>
                    </div>

                    <!-- Location (single flat dropdown for all categories) -->
                    <div class="mb-3" id="new_location_container" style="display: none;">
                        <label for="new_location" class="form-label">Location *</label>
                        <select class="form-select" id="new_location" name="location">
                            <option value="" disabled selected>Select a location</option>
                            <optgroup label="Rooms">
                                <option value="Room M01">Room M01</option>
                                <option value="Room M02">Room M02</option>
                                <option value="Room 202">Room 202</option>
                                <option value="Room 203">Room 203</option>
                                <option value="Room 204">Room 204</option>
                                <option value="Room 205">Room 205</option>
                                <option value="Room 206">Room 206</option>
                                <option value="Room 207">Room 207</option>
                                <option value="Room 301">Room 301</option>
                                <option value="Room 302">Room 302</option>
                                <option value="Room 303">Room 303</option>
                                <option value="Room 304">Room 304</option>
                                <option value="Room 305">Room 305</option>
                                <option value="Room 306">Room 306</option>
                                <option value="Room 307">Room 307</option>
                                <option value="Room 308">Room 308</option>
                                <option value="Room 309">Room 309</option>
                                <option value="Room 310">Room 310</option>
                                <option value="Room 311">Room 311</option>
                                <option value="Room 401">Room 401</option>
                                <option value="Room 402">Room 402</option>
                                <option value="Room 403">Room 403</option>
                                <option value="Room 404">Room 404</option>
                                <option value="Room 405">Room 405</option>
                                <option value="Room 406">Room 406</option>
                                <option value="Room 407">Room 407</option>
                                <option value="Room 408">Room 408</option>
                                <option value="Room 409">Room 409</option>
                                <option value="Room 410">Room 410</option>
                                <option value="Room 411">Room 411</option>
                                <option value="Room 501">Room 501</option>
                                <option value="Room 502">Room 502</option>
                                <option value="Room 503">Room 503</option>
                                <option value="Room 504">Room 504</option>
                                <option value="Room 505">Room 505</option>
                                <option value="Room 506">Room 506</option>
                                <option value="Room 507">Room 507</option>
                                <option value="Room 508">Room 508</option>
                                <option value="Room 509">Room 509</option>
                                <option value="Room 510">Room 510</option>
                                <option value="Room 511">Room 511</option>
                            </optgroup>
                            <optgroup label="Computer Laboratory">
                                <option value="Computer Laboratory 101">Computer Laboratory 101</option>
                                <option value="Computer Laboratory 208">Computer Laboratory 208</option>
                                <option value="Computer Laboratory 209">Computer Laboratory 209</option>
                                <option value="Computer Laboratory 210">Computer Laboratory 210</option>
                                <option value="Computer Laboratory 211">Computer Laboratory 211</option>
                                <option value="Computer Laboratory 212">Computer Laboratory 212</option>
                            </optgroup>
                            <optgroup label="AVR">
                                <option value="AVR 1">AVR 1</option>
                                <option value="AVR 2">AVR 2</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="new_description" class="form-label">Description *</label>
                        <textarea class="form-control" id="new_description" name="description" 
                            rows="3" placeholder="Describe the problem in detail..." required maxlength="500"></textarea>
                        <small class="text-muted d-block text-end" id="new_description_count">0 / 500</small>
                    </div>

                    <div class="mb-3">
                        <label for="new_image" class="form-label">Upload Photo (Optional)</label>
                        <input type="file" class="form-control" id="new_image" name="image" 
                            accept="image/*">
                        <small class="text-muted d-block" style="font-size: 12px;">Supported formats: JPEG, PNG, JPG (Max 2MB)</small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Concern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateAutoDeletePeriod(days) {
    if (confirm('Set auto-delete period to ' + days + ' days? Concerns deleted longer than this will be automatically removed.')) {
        fetch('{{ route('saveAutoDeletePreference') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ days: parseInt(days), module: 'concerns' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Auto-delete period updated to ' + days + ' days');
                location.reload(); // Reload to show updated list
            } else {
                alert('Error saving preference: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error saving preference');
        });
    } else {
        // Reset the dropdown to current value
        document.getElementById('autoDeleteDays').value = '{{ $days ?? 15 }}';
    }
}
</script>
@endsection
<style>
.context-menu {
    position: fixed;
    z-index: 1000;
    display: none;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
    min-width: 180px;
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
}
.context-menu ul li a:hover {
    background: #f5f5f5;
}
.context-menu ul li a i {
    margin-right: 8px;
    width: 20px;
}
/* Dropdown fix for table */
.table .dropdown {
    position: static;
}
.table .dropdown-menu {
    position: absolute;
    z-index: 1000;
}
/* Bulk actions styling */
.bulk-actions {
    padding: 10px;
    background-color: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}
.bulk-actions .btn-group {
    gap: 5px;
}
</style>

<script>
// Global variable for selected concern ID
window.selectedConcernId = null;
window.selectedConcernView = 'active';
// User role for conditional display
window.userRole = '{{ auth()->user()->role }}';

// Open New Concern Modal
function openNewConcernModal() {
    // Close any open modals to prevent conflicts
    const modalsToClose = ['viewConcernModal', 'editConcernModal', 'assignConcernModal', 'archiveModal', 'softDeleteModal', 'permanentDeleteModal', 'eventRequestModal', 'eventPreviewModal'];
    modalsToClose.forEach(id => {
        const modalElement = document.getElementById(id);
        if (modalElement) {
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    });

    // Reset form fields
    const catSel = document.getElementById('new_category_id');
    if (catSel) catSel.value = '';
    const issueCont = document.getElementById('new_issue_container');
    const issueEl = document.getElementById('new_issue');
    if (issueEl) { issueEl.value = ''; }
    const titleEl = document.getElementById('new_title');
    if (titleEl) titleEl.value = '';
    const locCont = document.getElementById('new_location_container');
    const locEl = document.getElementById('new_location');
    if (locCont) locCont.style.display = 'none';
    if (locEl) { locEl.value = ''; locEl.removeAttribute('required'); }
    const locTypeCont = document.getElementById('new_location_type_container');
    const locTypeEl = document.getElementById('new_location_type');
    if (locTypeCont) locTypeCont.style.display = 'none';
    if (locTypeEl) { locTypeEl.value = ''; locTypeEl.removeAttribute('required'); }
    const roomCont = document.getElementById('new_room_number_container');
    const roomEl = document.getElementById('new_room_number');
    if (roomCont) roomCont.style.display = 'none';
    if (roomEl) { roomEl.value = ''; roomEl.removeAttribute('required'); }
    const descEl = document.getElementById('new_description');
    if (descEl) descEl.value = '';
    const descCount = document.getElementById('new_description_count');
    if (descCount) descCount.textContent = '0 / 500';

    const modal = new bootstrap.Modal(document.getElementById('newConcernModal'));
    modal.show();
}

// Handle category change for concerns modal
document.addEventListener('DOMContentLoaded', function() {

    // Description character counter
    const descTextarea = document.getElementById('new_description');
    const descCount = document.getElementById('new_description_count');
    if (descTextarea && descCount) {
        descTextarea.addEventListener('input', function() {
            descCount.textContent = this.value.length + ' / 500';
        });
    }

    const categorySelect = document.getElementById('new_category_id');
    const issueSelect = document.getElementById('new_issue');
    const titleHidden = document.getElementById('new_title');
    const locationContainer = document.getElementById('new_location_container');
    const locationSelect = document.getElementById('new_location');

    // Per-category issue options
    const categoryIssues = {
        'maintenance': [
            'Aircon',
            'Door',
            'Window',
            'Chair',
            'Table',
            'Electrical outlet',
            'Light',
        ],
        'technology/internet': [
            'No Internet',
            'Printer',
            'Monitor',
            'PC Monitor',
            'Mouse',
            'Keyboard',
        ],
    };

    function populateIssues(categoryName) {
        const key = categoryName.toLowerCase().trim();
        const issues = categoryIssues[key] || [];
        issueSelect.innerHTML = '<option value="" disabled selected>Select an issue</option>';
        issues.forEach(function(issue) {
            const opt = document.createElement('option');
            opt.value = issue;
            opt.textContent = issue;
            issueSelect.appendChild(opt);
        });
        issueSelect.value = '';
        if (titleHidden) titleHidden.value = '';
    }

    if (issueSelect) {
        issueSelect.addEventListener('change', function() {
            if (titleHidden) titleHidden.value = this.value;
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            const categoryName = selectedOption ? selectedOption.getAttribute('data-name') : '';

            populateIssues(categoryName);

            // Show location dropdown for all categories
            locationContainer.style.display = 'block';
            if (locationSelect) locationSelect.setAttribute('required', 'required');
        });
    }

});

// Auto-submit filter form on change
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('select, input');
        
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                filterForm.submit();
            });
            if (input.type === 'text') {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        filterForm.submit();
                    }
                });
            }
        });
    }
    
    // Same for archive and deleted filter forms
    ['archiveFilterForm', 'deletedFilterForm'].forEach(formId => {
        const form = document.getElementById(formId);
        if (form) {
            const inputs = form.querySelectorAll('select, input');
            inputs.forEach(input => {
                input.addEventListener('change', function() {
                    form.submit();
                });
                if (input.type === 'text') {
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            form.submit();
                        }
                    });
                }
            });
        }
    });
});

// Right-click handler
document.addEventListener('contextmenu', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        e.preventDefault();
        window.selectedConcernId = row.getAttribute('data-id');
        window.selectedConcernView = row.getAttribute('data-view') || 'active';
        
        // Hide all context menus first
        document.getElementById('contextMenu').style.display = 'none';
        document.getElementById('contextMenuArchive').style.display = 'none';
        document.getElementById('contextMenuDeleted').style.display = 'none';
        
        // Show appropriate context menu
        if (window.selectedConcernView === 'archive') {
            showContextMenu(e.pageX, e.pageY, 'contextMenuArchive');
        } else if (window.selectedConcernView === 'deleted') {
            showContextMenu(e.pageX, e.pageY, 'contextMenuDeleted');
        } else {
            showContextMenu(e.pageX, e.pageY, 'contextMenu');
        }
    }
});

// Long-press handler for mobile
let longPressTimer;
document.addEventListener('touchstart', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        longPressTimer = setTimeout(function() {
            window.selectedConcernId = row.getAttribute('data-id');
            window.selectedConcernView = row.getAttribute('data-view') || 'active';
            const touch = e.touches[0];
            
            // Hide all context menus first
            document.getElementById('contextMenu').style.display = 'none';
            document.getElementById('contextMenuArchive').style.display = 'none';
            document.getElementById('contextMenuDeleted').style.display = 'none';
            
            if (window.selectedConcernView === 'archive') {
                showContextMenu(touch.pageX, touch.pageY, 'contextMenuArchive');
            } else if (window.selectedConcernView === 'deleted') {
                showContextMenu(touch.pageX, touch.pageY, 'contextMenuDeleted');
            } else {
                showContextMenu(touch.pageX, touch.pageY, 'contextMenu');
            }
        }, 500);
    }
});

document.addEventListener('touchend', function() {
    clearTimeout(longPressTimer);
});

document.addEventListener('touchmove', function() {
    clearTimeout(longPressTimer);
});

function showContextMenu(x, y, menuId) {
    const menu = document.getElementById(menuId);
    menu.style.display = 'block';
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
    
    const menuRect = menu.getBoundingClientRect();
    if (x + menuRect.width > window.innerWidth) {
        menu.style.left = (x - menuRect.width) + 'px';
    }
    if (y + menuRect.height > window.innerHeight) {
        menu.style.top = (y - menuRect.height) + 'px';
    }
}

// Hide context menu when clicking elsewhere
document.addEventListener('click', function() {
    document.getElementById('contextMenu').style.display = 'none';
    document.getElementById('contextMenuArchive').style.display = 'none';
    document.getElementById('contextMenuDeleted').style.display = 'none';
});

// Context menu actions
function contextView() {
    if (window.selectedConcernId) {
        viewConcern(window.selectedConcernId);
    }
}

function contextEdit() {
    if (window.selectedConcernId && window.selectedConcernView === 'active') {
        editConcern(window.selectedConcernId);
    }
}

function contextArchive() {
    if (window.selectedConcernId && window.selectedConcernView === 'active') {
        showArchiveModal(window.selectedConcernId);
    }
}

function contextDelete() {
    if (window.selectedConcernId && window.selectedConcernView === 'active') {
        softDeleteConcern(window.selectedConcernId);
    }
}

function contextRestore() {
    if (window.selectedConcernId && window.selectedConcernView === 'archive') {
        fetch('/concerns/' + window.selectedConcernId + '/restore', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Concern restored successfully!');
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error restoring concern');
        });
    }
}

function contextDeleteFromArchive() {
    if (window.selectedConcernId && window.selectedConcernView === 'archive') {
        softDeleteArchivedConcern(window.selectedConcernId);
    }
}

function contextRestoreDeleted() {
    if (window.selectedConcernId && window.selectedConcernView === 'deleted') {
        fetch('/concerns/' + window.selectedConcernId + '/restore-deleted', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Concern restored successfully!');
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error restoring concern');
        });
    }
}

function contextPermanentDelete() {
    if (window.selectedConcernId && window.selectedConcernView === 'deleted') {
        permanentDeleteConcern(window.selectedConcernId);
    }
}

// Soft Delete Functions
function softDeleteConcern(id) {
    document.getElementById('softDeleteConcernForm').action = '/concerns/' + id + '/soft-delete';
    document.getElementById('softDeleteConcernId').value = id;
    
    var modal = new bootstrap.Modal(document.getElementById('softDeleteModal'));
    modal.show();
}

function softDeleteArchivedConcern(id) {
    if (confirm('Are you sure you want to move this archived concern to deleted?')) {
        fetch('/concerns/' + id + '/soft-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Concern moved to deleted!');
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting concern');
        });
    }
}

// Permanent Delete Function
function permanentDeleteConcern(id) {
    document.getElementById('permanentDeleteConcernId').value = id;
    var modal = new bootstrap.Modal(document.getElementById('permanentDeleteModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('confirmPermanentDeleteBtn').addEventListener('click', function () {
        var id = document.getElementById('permanentDeleteConcernId').value;
        var btn = this;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        fetch('/concerns/' + id + '/permanent-delete', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                var modalEl = bootstrap.Modal.getInstance(document.getElementById('permanentDeleteModal'));
                if (modalEl) modalEl.hide();
                location.reload();
            } else {
                alert(data.error || 'Failed to delete concern.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-ban"></i> Permanent Delete';
            }
        })
        .catch(function () {
            alert('An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-ban"></i> Permanent Delete';
        });
    });
});

// Send Follow-up Function
function sendFollowUp(id) {
    if (confirm('Send a follow-up notification for this concern?')) {
        // Show loading state
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        fetch('/concerns/' + id + '/send-follow-up', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Follow-up notification sent successfully!');
                location.reload();
            } else if (data.error) {
                alert(data.error);
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending follow-up notification');
            button.disabled = false;
            button.innerHTML = originalHtml;
        });
    }
}

// Batch Operations for Active
function toggleSelectAll(type) {
    const checkboxId = type === 'active' ? 'selectAllActive' : 
                      (type === 'resolved' ? 'selectAllResolved' : 
                      (type === 'archive' ? 'selectAllArchive' : 'selectAllDeleted'));
    const className = type === 'active' ? 'active-checkbox' : 
                     (type === 'resolved' ? 'resolved-checkbox' : 
                     (type === 'archive' ? 'archive-checkbox' : 'deleted-checkbox'));
    const checkboxes = document.querySelectorAll('.' + className);
    const selectAll = document.getElementById(checkboxId);
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    if (type === 'active') {
        updateActiveBulkActions();
    } else if (type === 'resolved') {
        updateResolvedBulkActions();
    } else if (type === 'archive') {
        updateArchiveBulkActions();
    } else {
        updateDeletedBulkActions();
    }
}

function updateActiveBulkActions() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const bulkActions = document.getElementById('activeBulkActions');
    const countSpan = document.getElementById('activeSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

function updateResolvedBulkActions() {
    const selected = document.querySelectorAll('.resolved-checkbox:checked');
    const bulkActions = document.getElementById('resolvedBulkActions');
    const countSpan = document.getElementById('resolvedSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

function updateArchiveBulkActions() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const bulkActions = document.getElementById('archiveBulkActions');
    const countSpan = document.getElementById('archiveSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

function updateDeletedBulkActions() {
    const selected = document.querySelectorAll('.deleted-checkbox:checked');
    const bulkActions = document.getElementById('deletedBulkActions');
    const countSpan = document.getElementById('deletedSelectedCount');
    
    if (selected.length > 0) {
        bulkActions.style.display = 'block';
        countSpan.textContent = selected.length + ' selected';
    } else {
        bulkActions.style.display = 'none';
    }
}

function batchArchiveSelected() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to archive ' + ids.length + ' concern(s)?')) {
        fetch('/concerns/batch-archive', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error archiving concerns');
        });
    }
}

function batchSoftDeleteSelected() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to delete ' + ids.length + ' concern(s)?')) {
        fetch('/concerns/batch-soft-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting concerns');
        });
    }
}

function batchArchiveResolved() {
    const selected = document.querySelectorAll('.resolved-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to archive ' + ids.length + ' resolved concern(s)?')) {
        fetch('/concerns/batch-archive', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while archiving concerns.');
        });
    }
}

function batchSoftDeleteResolved() {
    const selected = document.querySelectorAll('.resolved-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to delete ' + ids.length + ' resolved concern(s)? They will be moved to the deleted folder.')) {
        fetch('/concerns/batch-soft-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting concerns.');
        });
    }
}

function batchRestoreArchived() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    fetch('/concerns/batch-restore', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
    }).then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error restoring concerns');
    });
}

function batchSoftDeleteArchived() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to move ' + ids.length + ' archived concern(s) to deleted?')) {
        fetch('/concerns/batch-soft-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting concerns');
        });
    }
}

function batchRestoreDeleted() {
    const selected = document.querySelectorAll('.deleted-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    fetch('/concerns/batch-restore-deleted', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ ids: ids })
    }).then(response => response.json())
    .then(data => {
        alert(data.message);
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error restoring concerns');
    });
}

function batchPermanentDeleteSelected() {
    const selected = document.querySelectorAll('.deleted-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    if (confirm('Are you sure you want to PERMANENTLY DELETE ' + ids.length + ' concern(s)? This action cannot be undone!')) {
        fetch('/concerns/batch-permanent-delete', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        }).then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error permanently deleting concerns');
        });
    }
}

// View Concern Modal
function viewConcern(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewConcernModal'));
    const contentDiv = document.getElementById('viewConcernContent');
    
    // Store current concern ID for assign functionality
    window.currentConcernId = id;
    
    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    modal.show();
    
    fetch('/api/concerns/' + id, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || 'Request failed with status ' + response.status);
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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Concern #${concern.id}</h4>
                    <div>
                        <span class="badge bg-${priorityClass} me-2">${concern.priority.charAt(0).toUpperCase() + concern.priority.slice(1)} Priority</span>
                        <span class="badge bg-${statusClass}">${concern.status}</span>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title">${concern.title || 'No Title'}</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Category:</strong> ${categoryName}</p>
                            <p><strong>Location:</strong> ${concern.location}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Reported:</strong> ${concern.created_at}</p>
                            ${window.userRole === 'admin' ? '<p><strong>Reported by:</strong> ' + userName + (userRole ? ' (' + userRole + ')' : '') + '</p>' : ''}
                        </div>
                    </div>
                    
                    ${imageHtml}
                    
                    <div class="mb-3">
                        <p><strong>Description:</strong></p>
                        <p class="card-text">${concern.description}</p>
                    </div>
                    
                    ${resolutionHtml}
                </div>
            </div>
        `;

        // Add action buttons to modal footer based on user role and concern status
        const modalFooter = document.querySelector('#viewConcernModal .modal-footer');
        let actionButtons = '';

        if (window.userRole === 'maintenance') {
            if (concern.status === 'Assigned') {
                actionButtons = `
                    <form action="/concerns/${concern.id}/acknowledge" method="POST" class="d-inline">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Acknowledge & Start Work
                        </button>
                    </form>
                `;
            } else if (concern.status === 'In Progress') {
                actionButtons = `
                    <button type="button" class="btn btn-success" onclick="window.location.href='/concerns/assigned'">
                        <i class="fas fa-tasks"></i> View My Tasks
                    </button>
                `;
            }
        }

        // Update modal footer
        modalFooter.innerHTML = actionButtons + '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading concern details.</div>';
    });
}

// Edit Concern Modal
function editConcern(id) {
    const modal = new bootstrap.Modal(document.getElementById('editConcernModal'));
    const contentDiv = document.getElementById('editConcernContent');
    const form = document.getElementById('editConcernForm');

    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    form.action = '/concerns/' + id;

    modal.show();

    fetch('/api/concerns/' + id + '/edit-data', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(async response => {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Non-JSON edit response:', text);
            throw new Error('The edit endpoint returned HTML instead of JSON.');
        }

        const data = await response.json();
        console.log('Edit data response:', data);

        if (!response.ok) {
            throw new Error(data.error || 'Failed to load concern data.');
        }

        return data;
    })
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }
        
        const concern = data.concern;
        const categories = data.categories;
        const maintenanceUsers = data.maintenance_users || [];
        const canAssign = data.can_assign || false;
        
        let categoryOptions = '';
        categories.forEach(cat => {
            const selected = cat.id === concern.category_id ? 'selected' : '';
            categoryOptions += `<option value="${cat.id}" ${selected}>${cat.name}</option>`;
        });
        
        const priorities = ['low', 'medium', 'high', 'urgent'];
        let priorityOptions = '';
        priorities.forEach(pri => {
            const selected = pri === concern.priority ? 'selected' : '';
            priorityOptions += `<option value="${pri}" ${selected}>${pri.charAt(0).toUpperCase() + pri.slice(1)}</option>`;
        });
        
        const statuses = ['Pending', 'Assigned', 'In Progress', 'Resolved', 'Closed'];
        let statusOptions = '';
        statuses.forEach(sta => {
            const selected = sta === concern.status ? 'selected' : '';
            statusOptions += `<option value="${sta}" ${selected}>${sta}</option>`;
        });
        
        let maintenanceOptions = '<option value="">-- Select Maintenance --</option>';
        maintenanceUsers.forEach(user => {
            const selected = user.id === concern.assigned_to ? 'selected' : '';
            maintenanceOptions += `<option value="${user.id}" ${selected}>${user.name}</option>`;
        });
        
        let imageHtml = '';
        if (concern.image_path) {
            imageHtml = `
                <div class="mb-3">
                    <label class="form-label">Current Photo</label>
                    <div>
                        <img src="${concern.image_path}" alt="Current photo" class="img-fluid rounded" style="max-width: 200px;">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Replace Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            `;
        } else {
            imageHtml = `
                <div class="mb-3">
                    <label class="form-label">Photo</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            `;
        }
        
        // Build form fields based on role
        let adminFields = '';
        if (canAssign) {
            adminFields = `
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        ${statusOptions}
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Assign To</label>
                    <select name="assigned_to" class="form-select">
                        ${maintenanceOptions}
                    </select>
                </div>
            `;
        }
        
        contentDiv.innerHTML = `
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="${concern.title || ''}">
            </div>
            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" value="${concern.location}" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select" required>
                    ${categoryOptions}
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    ${priorityOptions}
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" required>${concern.description}</textarea>
            </div>
            ${imageHtml}
            ${adminFields}
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="alert alert-danger">' + error.message + '</div>';
    });
}

// Handle edit form submission
const editConcernForm = document.getElementById('editConcernForm');
if (editConcernForm) {
    editConcernForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);
        
        formData.append('_method', 'PUT');
        
        const url = form.action;
        
        fetch(url, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('editConcernModal')).hide();
                location.reload();
            } else if (data.errors) {
                alert('Validation errors: ' + Object.values(data.errors).flat().join('\n'));
            } else if (data.message) {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating concern.');
        });
    });
}

// Archive Modal Function
function showArchiveModal(concernId) {
    document.getElementById('archiveConcernForm').action = '/concerns/' + concernId + '/archive';
    document.getElementById('archiveConcernId').value = concernId;
    
    var modal = new bootstrap.Modal(document.getElementById('archiveModal'));
    modal.show();
}

function archiveConcern(id) {
    fetch('/concerns/' + id + '/archive', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Concern archived successfully!');
            location.reload();
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error archiving concern');
    });
}

// Store current concern ID for assignment
let currentConcernId = null;

// Show Assign Modal
function showAssignModal() {
    const concernId = window.currentConcernId;
    if (!concernId) {
        alert('No concern selected');
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById('assignConcernModal'));
    const select = document.getElementById('assigned_to');
    const form = document.getElementById('assignConcernForm');
    
    // Set the concern ID
    document.getElementById('assignConcernId').value = concernId;
    
    // Load maintenance staff list
    select.innerHTML = '<option value="">Loading...</option>';
    
    fetch('/api/maintenance-users', {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            select.innerHTML = '<option value="">Error loading users</option>';
            return;
        }
        
        select.innerHTML = '<option value="">Select maintenance staff</option>';
        data.users.forEach(user => {
            select.innerHTML += `<option value="${user.id}">${user.name}</option>`;
        });
    })
    .catch(error => {
        console.error('Error:', error);
        select.innerHTML = '<option value="">Error loading users</option>';
    });
    
    modal.show();
}

// Handle assign form submission
const assignConcernForm = document.getElementById('assignConcernForm');
if (assignConcernForm) {
    assignConcernForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const concernId = document.getElementById('assignConcernId').value;
        const assignedTo = document.getElementById('assigned_to').value;
    
    if (!assignedTo) {
        alert('Please select a maintenance staff');
        return;
    }
    
    fetch('/concerns/' + concernId + '/assign', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ assigned_to: assignedTo })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('assignConcernModal')).hide();
            alert('Concern assigned successfully!');
            location.reload();
        } else {
            alert(data.error || 'Failed to assign concern');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error assigning concern');
    });
    });
}
</script>

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
                <!-- Action buttons will be added dynamically -->
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Concern Modal -->
<div class="modal fade" id="assignConcernModal2" tabindex="-1" aria-labelledby="assignConcernModalLabel2" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignConcernModalLabel2">Assign Concern to Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="assignConcernForm2">
                @csrf
                <input type="hidden" id="assignConcernId2" name="concern_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_to2" class="form-label">Select Maintenance Staff</label>
                        <select class="form-select" id="assigned_to2" name="assigned_to" required>
                            <option value="">Loading...</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Assign Concern</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


