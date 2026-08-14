@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
/* Desktop: Hide mobile cards */
.mobile-event-cards {
    display: none;
}

/* Mobile Card Layout for Event Requests */
@media screen and (max-width: 768px) {
    /* Hide table on mobile */
    .card-body .table-responsive {
        display: none !important;
    }
    
    /* Show mobile card layout */
    .mobile-event-cards {
        display: block !important;
    }
    
    /* Event card styling */
    .event-card {
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .dark-mode .event-card {
        background: #1a1d20;
        border-color: #404347;
    }
    
    .event-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .dark-mode .event-card-header {
        border-bottom-color: #404347;
    }
    
    .event-card-id {
        font-weight: 600;
        font-size: 14px;
        color: #212529;
    }
    
    .dark-mode .event-card-id {
        color: #e9ecef;
    }
    
    .event-card-body {
        margin-bottom: 12px;
    }
    
    .event-card-field {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        font-size: 14px;
    }
    
    .event-card-label {
        font-weight: 500;
        color: #6c757d;
        margin-right: 12px;
    }
    
    .dark-mode .event-card-label {
        color: #adb5bd;
    }
    
    .event-card-value {
        text-align: right;
        color: #212529;
    }
    
    .dark-mode .event-card-value {
        color: #e9ecef;
    }
    
    .event-card-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding-top: 12px;
        border-top: 1px solid #dee2e6;
    }
    
    .dark-mode .event-card-actions {
        border-top-color: #404347;
    }
    
    .event-card-actions .btn {
        flex: 1;
        min-width: fit-content;
    }
    
    /* Checkbox for mobile cards */
    .event-card-checkbox {
        margin-right: 8px;
    }
}
</style>
@endsection

@section('page_title')
<h2>My Event Requests</h2>
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


    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: @json(session('success')),
                    confirmButtonColor: '#28a745',
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    <!-- Tabs Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <ul class="nav nav-tabs border-0 mb-0 flex-wrap" id="eventTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? 'active') === 'active' ? 'active' : '' }}"
                    id="active-tab" data-bs-toggle="tab" data-bs-target="#active-events"
                    type="button" role="tab" aria-controls="active-events"
                    aria-selected="{{ ($viewType ?? 'active') === 'active' ? 'true' : 'false' }}">
                <i class="fas fa-list"></i> Active
                @if(isset($requests) && $requests->count() > 0)
                    <span class="badge bg-primary ms-1">{{ $requests->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'approved' ? 'active' : '' }}"
                    id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved-events"
                    type="button" role="tab" aria-controls="approved-events"
                    aria-selected="{{ ($viewType ?? '') === 'approved' ? 'true' : 'false' }}">
                <i class="fas fa-check-circle"></i> Approved
                @if(isset($approvedRequests) && $approvedRequests->count() > 0)
                    <span class="badge bg-success ms-1">{{ $approvedRequests->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'finished' ? 'active' : '' }}"
                    id="finished-tab" data-bs-toggle="tab" data-bs-target="#finished-events"
                    type="button" role="tab" aria-controls="finished-events"
                    aria-selected="{{ ($viewType ?? '') === 'finished' ? 'true' : 'false' }}">
                <i class="fas fa-flag-checkered"></i> Finished
                @if(isset($finishedRequests) && $finishedRequests->count() > 0)
                    <span class="badge bg-secondary ms-1">{{ $finishedRequests->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'rejected' ? 'active' : '' }}"
                    id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected-events"
                    type="button" role="tab" aria-controls="rejected-events"
                    aria-selected="{{ ($viewType ?? '') === 'rejected' ? 'true' : 'false' }}">
                <i class="fas fa-times-circle"></i> Rejected
                @if(isset($rejectedRequests) && $rejectedRequests->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $rejectedRequests->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'archives' ? 'active' : '' }}"
                    id="archives-tab" data-bs-toggle="tab" data-bs-target="#archived-events"
                    type="button" role="tab" aria-controls="archived-events"
                    aria-selected="{{ ($viewType ?? '') === 'archives' ? 'true' : 'false' }}">
                <i class="fas fa-archive"></i> Archives
                @if(isset($archivedRequests) && $archivedRequests->count() > 0)
                    <span class="badge bg-warning ms-1">{{ $archivedRequests->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ ($viewType ?? '') === 'deleted' ? 'active' : '' }}"
                    id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted-events"
                    type="button" role="tab" aria-controls="deleted-events"
                    aria-selected="{{ ($viewType ?? '') === 'deleted' ? 'true' : 'false' }}">
                <i class="fas fa-trash"></i> Deleted
                @if(isset($deletedRequests) && $deletedRequests->count() > 0)
                    <span class="badge bg-danger ms-1">{{ $deletedRequests->count() }}</span>
                @endif
            </button>
        </li>
    </ul>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#eventRequestModal">
            <i class="fas fa-plus"></i> New Request
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content show" id="eventTabContent">

        <!-- Active Events Tab -->
        <div class="tab-pane fade {{ ($viewType ?? 'active') === 'active' ? 'show active' : '' }}"
             id="active-events" role="tabpanel" aria-labelledby="active-tab">

            <!-- Filters for Active -->
            @if(($viewType ?? 'active') === 'active')
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('events.my') }}" class="row g-2 align-items-center" id="filterForm">
                        <input type="hidden" name="view" value="active">
                        <div class="col-6 col-md">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..."
                                value="{{ request('search') }}" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
                        </div>
                        <div class="col-6 col-md">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date"
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date"
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('events.my', ['view' => 'active']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Active Events List -->
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

                    @if($requests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 50px;"><input type="checkbox" id="selectAllActive" onchange="toggleSelectAll('active')"></th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Ticket</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr data-id="{{ $request->id }}" data-view="active">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="active-checkbox" value="{{ $request->id }}" onchange="updateActiveBulkActions()"></td>
                                            <td>EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($request->category) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                                            <td>{{ $request->location }}</td>
                                            <td>
                                                <span class="badge bg-{{
                                                    $request->status == 'Approved' ? 'success' :
                                                    ($request->status == 'Rejected' ? 'danger' :
                                                    ($request->status == 'Cancelled' ? 'secondary' : 'warning'))
                                                }}">
                                                    {{ $request->status }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    @if($request->status == 'Cancelled')
                                                        {{-- Show Archive and Delete for cancelled events --}}
                                                        <a href="#" class="btn btn-sm btn-secondary"
                                                            onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});"
                                                            title="Archive">
                                                            <i class="fas fa-archive"></i>
                                                        </a>
                                                        <form action="{{ route('events.delete', $request->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="button" class="btn btn-sm btn-danger" onclick="cancelEventRequest({{ $request->id }})"
                                                                data-confirm="Delete this event? It will be moved to deleted events."
                                                                data-confirm-title="Delete Event"
                                                                data-confirm-ok="Yes, Delete"
                                                                data-confirm-color="#dc3545"
                                                                title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @elseif(in_array($request->status, ['Pending', 'Approved']))
                                                        {{-- Requesters may cancel pending or already approved events. --}}
                                                        <form action="{{ route('events.cancel', $request->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                data-confirm="Cancel this request?"
                                                                data-confirm-title="Cancel Request"
                                                                data-confirm-ok="Yes, Cancel"
                                                                data-confirm-color="#dc3545"
                                                                title="Cancel">
                                                                <i class="fas fa-times"></i>
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

                        <!-- Mobile Card Layout -->
                        <div class="mobile-event-cards" style="display: none;">
                            @foreach($requests as $request)
                                <div class="event-card" data-id="{{ $request->id }}" data-view="active">
                                    <div class="event-card-header">
                                        <div>
                                            <input type="checkbox" class="event-card-checkbox active-checkbox" value="{{ $request->id }}" onchange="updateActiveBulkActions()">
                                            <span class="event-card-id">EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <span class="badge bg-{{
                                            $request->status == 'Approved' ? 'success' :
                                            ($request->status == 'Rejected' ? 'danger' :
                                            ($request->status == 'Cancelled' ? 'secondary' : 'warning'))
                                        }}">
                                            {{ $request->status }}
                                        </span>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-card-field">
                                            <span class="event-card-label">Category:</span>
                                            <span class="event-card-value">
                                                <span class="badge bg-info">{{ ucfirst($request->category) }}</span>
                                            </span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Date:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Time:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Location:</span>
                                            <span class="event-card-value">{{ $request->location }}</span>
                                        </div>
                                    </div>
                                    <div class="event-card-actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        @if($request->status == 'Cancelled')
                                            <a href="#" class="btn btn-sm btn-secondary"
                                                onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});">
                                                <i class="fas fa-archive"></i> Archive
                                            </a>
                                            <form action="{{ route('events.delete', $request->id) }}" method="POST" style="flex: 1;">
                                                @csrf
                                                <button type="button" class="btn btn-sm btn-danger w-100" onclick="cancelEventRequest({{ $request->id }})"
                                                    data-confirm="Delete this event? It will be moved to deleted events."
                                                    data-confirm-title="Delete Event"
                                                    data-confirm-ok="Yes, Delete"
                                                    data-confirm-color="#dc3545">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        @elseif(in_array($request->status, ['Pending', 'Approved']))
                                            <form action="{{ route('events.cancel', $request->id) }}" method="POST" style="flex: 1;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger w-100"
                                                    data-confirm="Cancel this request?"
                                                    data-confirm-title="Cancel Request"
                                                    data-confirm-ok="Yes, Cancel"
                                                    data-confirm-color="#dc3545">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else
                        <div class="text-center py-5">
                            <h4 class="text-muted">No active event requests</h4>
                            <p>Submit your first event request</p>
                            <a href="{{ route('events.create') }}" class="btn btn-primary">Create Event Request</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Approved Events Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'approved' ? 'show active' : '' }}"
             id="approved-events" role="tabpanel" aria-labelledby="approved-tab">
            <div class="card">
                <div class="card-body">
                    @if(isset($approvedRequests) && $approvedRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Ticket</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($approvedRequests as $request)
                                        <tr data-id="{{ $request->id }}">
                                            <td>EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($request->category) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                                            <td>{{ $request->location }}</td>
                                            <td>
                                                <span class="badge bg-success">{{ $request->status }}</span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="{{ route('events.pdf', $request->id) }}" class="btn btn-sm btn-primary" title="Download PDF" target="_blank">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-secondary"
                                                        onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});"
                                                        title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card Layout -->
                        <div class="mobile-event-cards" style="display: none;">
                            @foreach($approvedRequests as $request)
                                <div class="event-card" data-id="{{ $request->id }}">
                                    <div class="event-card-header">
                                        <span class="event-card-id">EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="badge bg-success">{{ $request->status }}</span>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-card-field">
                                            <span class="event-card-label">Category:</span>
                                            <span class="event-card-value">
                                                <span class="badge bg-info">{{ ucfirst($request->category) }}</span>
                                            </span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Date:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Time:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Location:</span>
                                            <span class="event-card-value">{{ $request->location }}</span>
                                        </div>
                                    </div>
                                    <div class="event-card-actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <a href="{{ route('events.pdf', $request->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="#" class="btn btn-sm btn-secondary"
                                            onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});">
                                            <i class="fas fa-archive"></i> Archive
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No approved events</h4>
                            <p>Your approved events will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Finished Events Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'finished' ? 'show active' : '' }}"
             id="finished-events" role="tabpanel" aria-labelledby="finished-tab">
            <div class="card">
                <div class="card-body">
                    @if(isset($finishedRequests) && $finishedRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Ticket</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($finishedRequests as $request)
                                        <tr data-id="{{ $request->id }}">
                                            <td>EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($request->category) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                                            <td>{{ $request->location }}</td>
                                            <td>
                                                <span class="badge bg-secondary">Finished</span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="{{ route('events.pdf', $request->id) }}" class="btn btn-sm btn-primary" title="Download PDF" target="_blank">
                                                        <i class="fas fa-file-pdf"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-secondary"
                                                        onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});"
                                                        title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card Layout -->
                        <div class="mobile-event-cards" style="display: none;">
                            @foreach($finishedRequests as $request)
                                <div class="event-card" data-id="{{ $request->id }}">
                                    <div class="event-card-header">
                                        <span class="event-card-id">EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="badge bg-secondary">Finished</span>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-card-field">
                                            <span class="event-card-label">Category:</span>
                                            <span class="event-card-value">
                                                <span class="badge bg-info">{{ ucfirst($request->category) }}</span>
                                            </span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Date:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Time:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Location:</span>
                                            <span class="event-card-value">{{ $request->location }}</span>
                                        </div>
                                    </div>
                                    <div class="event-card-actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <a href="{{ route('events.pdf', $request->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                        <a href="#" class="btn btn-sm btn-secondary"
                                            onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});">
                                            <i class="fas fa-archive"></i> Archive
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-flag-checkered fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No finished events</h4>
                            <p>Completed events will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Rejected Events Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'rejected' ? 'show active' : '' }}"
             id="rejected-events" role="tabpanel" aria-labelledby="rejected-tab">
            <div class="card">
                <div class="card-body">
                    @if(isset($rejectedRequests) && $rejectedRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Ticket</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($rejectedRequests as $request)
                                        <tr data-id="{{ $request->id }}">
                                            <td>EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($request->category) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                                            <td>{{ $request->location }}</td>
                                            <td>
                                                <span class="badge bg-danger">{{ $request->status }}</span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="#" class="btn btn-sm btn-secondary"
                                                        onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});"
                                                        title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </a>
                                                    <form action="{{ route('events.delete', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            data-confirm="Delete this event? It will be moved to deleted events."
                                                            data-confirm-title="Delete Event"
                                                            data-confirm-ok="Yes, Delete"
                                                            data-confirm-color="#dc3545"
                                                            title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card Layout -->
                        <div class="mobile-event-cards" style="display: none;">
                            @foreach($rejectedRequests as $request)
                                <div class="event-card" data-id="{{ $request->id }}">
                                    <div class="event-card-header">
                                        <span class="event-card-id">EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        <span class="badge bg-danger">{{ $request->status }}</span>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-card-field">
                                            <span class="event-card-label">Category:</span>
                                            <span class="event-card-value">
                                                <span class="badge bg-info">{{ ucfirst($request->category) }}</span>
                                            </span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Date:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Time:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Location:</span>
                                            <span class="event-card-value">{{ $request->location }}</span>
                                        </div>
                                    </div>
                                    <div class="event-card-actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <a href="#" class="btn btn-sm btn-secondary"
                                            onclick="event.preventDefault(); showEventArchiveModal({{ $request->id }});">
                                            <i class="fas fa-archive"></i> Archive
                                        </a>
                                        <form action="{{ route('events.delete', $request->id) }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger w-100"
                                                data-confirm="Delete this event? It will be moved to deleted events."
                                                data-confirm-title="Delete Event"
                                                data-confirm-ok="Yes, Delete"
                                                data-confirm-color="#dc3545">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-times-circle fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No rejected events</h4>
                            <p>Rejected events will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Archived Events Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'archives' ? 'show active' : '' }}"
             id="archived-events" role="tabpanel" aria-labelledby="archives-tab">

            <!-- Filters for Archives -->
            @if(($viewType ?? '') === 'archives')
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('events.my') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="archives">
                        <div class="col-6 col-md">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..."
                                value="{{ request('search') }}" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
                        </div>
                        <div class="col-6 col-md">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <option value="Area Use" {{ request('category') == 'Area Use' ? 'selected' : '' }}>Area Use</option>
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date"
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date"
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('events.my', ['view' => 'archives']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Archived Events List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Archived -->
                    <div class="bulk-actions mb-3" id="archiveBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreSelected()">
                                <i class="fas fa-trash-restore"></i> Restore Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchDeleteFromArchiveSelected()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="archiveSelectedCount">0 selected</span>
                    </div>

                    @if(isset($archivedRequests) && $archivedRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 50px;"><input type="checkbox" id="selectAllArchive" onchange="toggleSelectAll('archive')"></th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Ticket</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($archivedRequests as $request)
                                        <tr data-id="{{ $request->id }}" data-view="archive">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="archive-checkbox" value="{{ $request->id }}" onchange="updateArchiveBulkActions()"></td>
                                            <td>EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($request->category) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                                            <td>{{ $request->location }}</td>
                                            <td>
                                                <span class="badge bg-{{ $request->status == 'Approved' ? 'success' : ($request->status == 'Rejected' ? 'danger' : ($request->status == 'Cancelled' ? 'secondary' : 'warning')) }}">
                                                    {{ $request->status }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form action="{{ route('events.restore', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            data-confirm="Restore this request?"
                                                            data-confirm-title="Restore Event"
                                                            data-confirm-ok="Yes, Restore"
                                                            data-confirm-color="#198754"
                                                            title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('events.delete', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            data-confirm="Permanently delete this event?"
                                                            data-confirm-title="Permanent Delete"
                                                            data-confirm-ok="Yes, Delete"
                                                            data-confirm-color="#dc3545"
                                                            title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card Layout -->
                        <div class="mobile-event-cards" style="display: none;">
                            @foreach($archivedRequests as $request)
                                <div class="event-card" data-id="{{ $request->id }}" data-view="archive">
                                    <div class="event-card-header">
                                        <div>
                                            <input type="checkbox" class="event-card-checkbox archive-checkbox" value="{{ $request->id }}" onchange="updateArchiveBulkActions()">
                                            <span class="event-card-id">EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <span class="badge bg-{{ $request->status == 'Approved' ? 'success' : ($request->status == 'Rejected' ? 'danger' : ($request->status == 'Cancelled' ? 'secondary' : 'warning')) }}">
                                            {{ $request->status }}
                                        </span>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-card-field">
                                            <span class="event-card-label">Category:</span>
                                            <span class="event-card-value">
                                                <span class="badge bg-info">{{ ucfirst($request->category) }}</span>
                                            </span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Date:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Time:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Location:</span>
                                            <span class="event-card-value">{{ $request->location }}</span>
                                        </div>
                                    </div>
                                    <div class="event-card-actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewEvent({{ $request->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <form action="{{ route('events.restore', $request->id) }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success w-100"
                                                data-confirm="Restore this request?"
                                                data-confirm-title="Restore Event"
                                                data-confirm-ok="Yes, Restore"
                                                data-confirm-color="#198754">
                                                <i class="fas fa-trash-restore"></i> Restore
                                            </button>
                                        </form>
                                        <form action="{{ route('events.delete', $request->id) }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger w-100"
                                                data-confirm="Permanently delete this event?"
                                                data-confirm-title="Permanent Delete"
                                                data-confirm-ok="Yes, Delete"
                                                data-confirm-color="#dc3545">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else
                        <div class="text-center py-5">
                            <h4 class="text-muted">No archived event requests</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Deleted Events Tab -->
        <div class="tab-pane fade {{ ($viewType ?? '') === 'deleted' ? 'show active' : '' }}"
             id="deleted-events" role="tabpanel" aria-labelledby="deleted-tab">

            <!-- Filters for Deleted -->
            @if(($viewType ?? '') === 'deleted')
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('events.my') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="deleted">
                        <div class="col-6 col-md">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..."
                                value="{{ request('search') }}" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
                        </div>
                        <div class="col-6 col-md">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <option value="Area Use" {{ request('category') == 'Area Use' ? 'selected' : '' }}>Area Use</option>
                            
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date"
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date"
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('events.my', ['view' => 'deleted']) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Deleted Events List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Deleted -->
                    <div class="bulk-actions mb-3" id="deletedBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreDeletedSelected()">
                                <i class="fas fa-trash-restore"></i> Restore Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchPermanentDeleteSelected()">
                                <i class="fas fa-ban"></i> Permanent Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="deletedSelectedCount">0 selected</span>
                    </div>

                    @if(isset($deletedRequests) && $deletedRequests->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 50px;"><input type="checkbox" id="selectAllDeleted" onchange="toggleSelectAll('deleted')"></th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Ticket</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Event Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deletedRequests as $request)
                                        <tr data-id="{{ $request->id }}" data-view="deleted">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-checkbox" value="{{ $request->id }}" onchange="updateDeletedBulkActions()"></td>
                                            <td>EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ ucfirst($request->category) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                                            <td>{{ $request->location }}</td>
                                            <td>
                                                <span class="badge bg-{{ $request->status == 'Approved' ? 'success' : ($request->status == 'Rejected' ? 'danger' : ($request->status == 'Cancelled' ? 'secondary' : 'warning')) }}">
                                                    {{ $request->status }}
                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewDeletedEvent({{ $request->id }})" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form action="{{ route('events.restore', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            data-confirm="Restore this event?"
                                                            data-confirm-title="Restore Event"
                                                            data-confirm-ok="Yes, Restore"
                                                            data-confirm-color="#198754"
                                                            title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('events.delete', $request->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="permanent" value="1">
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            data-confirm="Permanently delete this event? This action cannot be undone."
                                                            data-confirm-title="Permanent Delete"
                                                            data-confirm-ok="Yes, Delete Forever"
                                                            data-confirm-color="#dc3545"
                                                            title="Permanent Delete">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Card Layout -->
                        <div class="mobile-event-cards" style="display: none;">
                            @foreach($deletedRequests as $request)
                                <div class="event-card" data-id="{{ $request->id }}" data-view="deleted">
                                    <div class="event-card-header">
                                        <div>
                                            <input type="checkbox" class="event-card-checkbox deleted-checkbox" value="{{ $request->id }}" onchange="updateDeletedBulkActions()">
                                            <span class="event-card-id">EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <span class="badge bg-{{ $request->status == 'Approved' ? 'success' : ($request->status == 'Rejected' ? 'danger' : ($request->status == 'Cancelled' ? 'secondary' : 'warning')) }}">
                                            {{ $request->status }}
                                        </span>
                                    </div>
                                    <div class="event-card-body">
                                        <div class="event-card-field">
                                            <span class="event-card-label">Category:</span>
                                            <span class="event-card-value">
                                                <span class="badge bg-info">{{ ucfirst($request->category) }}</span>
                                            </span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Date:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Time:</span>
                                            <span class="event-card-value">{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</span>
                                        </div>
                                        <div class="event-card-field">
                                            <span class="event-card-label">Location:</span>
                                            <span class="event-card-value">{{ $request->location }}</span>
                                        </div>
                                    </div>
                                    <div class="event-card-actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewDeletedEvent({{ $request->id }})">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                        <form action="{{ route('events.restore', $request->id) }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success w-100"
                                                data-confirm="Restore this event?"
                                                data-confirm-title="Restore Event"
                                                data-confirm-ok="Yes, Restore"
                                                data-confirm-color="#198754">
                                                <i class="fas fa-trash-restore"></i> Restore
                                            </button>
                                        </form>
                                        <form action="{{ route('events.delete', $request->id) }}" method="POST" style="flex: 1;">
                                            @csrf
                                            <input type="hidden" name="permanent" value="1">
                                            <button type="submit" class="btn btn-sm btn-danger w-100"
                                                data-confirm="Permanently delete this event? This action cannot be undone."
                                                data-confirm-title="Permanent Delete"
                                                data-confirm-ok="Yes, Delete Forever"
                                                data-confirm-color="#dc3545">
                                                <i class="fas fa-ban"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else
                        <div class="text-center py-5">
                            <h4 class="text-muted">No deleted event requests</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewEventModalLabel">Event Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewEventContent">
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

<!-- Archive Modal -->
<div class="modal fade" id="eventArchiveModal" tabindex="-1" aria-labelledby="eventArchiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventArchiveModalLabel">Archive Event Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventArchiveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="archiveEventId" name="event_id" value="">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This event request will be archived and hidden from your active list.
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

/* Approval Steps Styling */
.approval-step {
    padding: 15px 10px;
    border-radius: 10px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 2px solid #dee2e6;
}

.approval-step.approved {
    background-color: #d4edda;
    border-color: #28a745;
}

.approval-step.pending {
    background-color: #fff3cd;
    border-color: #ffc107;
}

.approval-step.rejected {
    background-color: #f8d7da;
    border-color: #dc3545;
}

/* Dark Mode Support for Approval Steps */
[data-theme="dark"] .approval-step {
    background-color: #2a2a45;
    border-color: #3a3a55;
}

[data-theme="dark"] .approval-step h6 {
    color: #e0e0e0 !important;
}

[data-theme="dark"] .approval-step small {
    color: #a0a0a0 !important;
}

[data-theme="dark"] .approval-step.approved {
    background-color: #1b3a2f;
    border-color: #28a745;
}

[data-theme="dark"] .approval-step.approved h6,
[data-theme="dark"] .approval-step.approved small {
    color: #80cbc4 !important;
}

[data-theme="dark"] .approval-step.pending {
    background-color: #3a3020;
    border-color: #ffc107;
}

[data-theme="dark"] .approval-step.pending h6,
[data-theme="dark"] .approval-step.pending small {
    color: #ffd54f !important;
}

[data-theme="dark"] .approval-step.rejected {
    background-color: #3a2020;
    border-color: #dc3545;
}

[data-theme="dark"] .approval-step.rejected h6,
[data-theme="dark"] .approval-step.rejected small {
    color: #ef9a9a !important;
}

.step-icon {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.approval-step h6 {
    font-size: 12px;
    margin-bottom: 5px;
    font-weight: 600;
}

.approval-step small {
    font-size: 10px;
}
</style>

<script>
// View Event Modal
function formatTime12(t) {
    if (!t) return '';
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    return (hour % 12 || 12) + ':' + m + ' ' + ampm;
}
function viewEvent(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewEventModal'));
    const contentDiv = document.getElementById('viewEventContent');

    // Store current event ID
    window.currentEventId = id;

    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/events/' + id, {
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

        const event = data.event;
        const userName = event.user ? event.user.name : 'Unknown';
        const userRole = event.user ? event.user.role : '';

        const statusClass = event.status === 'Approved' ? 'success' :
            (event.status === 'Rejected' ? 'danger' :
            (event.status === 'Cancelled' ? 'secondary' : 'warning'));

        const categoryBadge = `<span class="badge bg-info">${event.category.charAt(0).toUpperCase() + event.category.slice(1)}</span>`;

        let materialsHtml = '';
        if (event.materials_needed && event.materials_needed.length > 0) {
            materialsHtml = `
                <div class="mt-3">
                    <p><strong>Materials Needed:</strong></p>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Qty</th>
                                <th>Item</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${event.materials_needed.map(m => `
                                <tr>
                                    <td>${m.qty ?? 1}</td>
                                    <td>${m.item ?? 'N/A'}</td>
                                    <td>${m.purpose ?? 'N/A'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Calculate approval progress from the actual approval flow.
        const approvalLevel = event.approval_level || 0;
        const isShsEvent = event.education_level === 'shs';
        const isNonAcademicEvent = event.request_type === 'Non-Academic';
        let approvalSteps;

        if (isShsEvent) {
            approvalSteps = [
                {
                    label: 'Principal Assistant',
                    level: 1,
                    approved: !!event.approved_by_level_1,
                    current: event.status === 'Pending' && approvalLevel === 1
                },
                {
                    label: 'Academic Head',
                    level: 2,
                    approved: !!event.approved_by_level_2,
                    current: event.status === 'Pending' && approvalLevel === 2
                },
                {
                    label: 'School Admin',
                    level: 4,
                    approved: event.status === 'Approved',
                    current: event.status === 'Pending' && approvalLevel === 4
                }
            ];
        } else if (isNonAcademicEvent) {
            approvalSteps = [
                {
                    label: 'Building Admin',
                    level: 3,
                    approved: !!event.approved_by_level_3,
                    current: event.status === 'Pending' && approvalLevel === 3
                },
                {
                    label: 'School Admin',
                    level: 4,
                    approved: event.status === 'Approved',
                    current: event.status === 'Pending' && approvalLevel === 4
                }
            ];
        } else {
            approvalSteps = [
                {
                    label: 'Program Head',
                    level: 1,
                    approved: !!event.approved_by_level_1,
                    current: event.status === 'Pending' && approvalLevel === 1
                },
                {
                    label: 'Academic Head',
                    level: 2,
                    approved: !!event.approved_by_level_2,
                    current: event.status === 'Pending' && approvalLevel === 2
                },
                {
                    label: 'Building Admin',
                    level: 3,
                    approved: !!event.approved_by_level_3,
                    current: event.status === 'Pending' && approvalLevel === 3
                },
                {
                    label: 'School Admin',
                    level: 4,
                    approved: event.status === 'Approved',
                    current: event.status === 'Pending' && approvalLevel === 4
                }
            ];
        }
        const totalApprovalSteps = approvalSteps.length;
        const doneApprovalSteps = event.status === 'Approved' ? totalApprovalSteps :
            event.status === 'Rejected' || event.status === 'Cancelled' ? 0 :
            approvalSteps.filter(step => step.approved).length;
        const progressPercentage = totalApprovalSteps > 0
            ? Math.round((doneApprovalSteps / totalApprovalSteps) * 100)
            : 0;
        const approvalStepColClass = totalApprovalSteps === 2 ? 'col-6' : (totalApprovalSteps === 3 ? 'col-4' : 'col-3');
        const approvalStepsHtml = approvalSteps.map(step => {
            const isApproved = event.status === 'Approved' || step.approved;
            const isRejected = event.status === 'Rejected' && approvalLevel >= step.level;
            const isCurrent = !isApproved && !isRejected && step.current;
            const stateClass = isApproved ? 'approved' : (isRejected ? 'rejected' : (isCurrent ? 'pending' : ''));
            const iconHtml = isApproved
                ? '<i class="fas fa-check-circle fa-2x text-success"></i>'
                : isRejected
                    ? '<i class="fas fa-times-circle fa-2x text-danger"></i>'
                    : `<i class="fas fa-clock fa-2x ${isCurrent ? 'text-warning' : 'text-muted'}"></i>`;
            const statusText = isApproved ? 'Approved' : (isRejected ? 'Rejected' : (isCurrent ? 'Waiting' : 'Pending'));

            return `
                <div class="${approvalStepColClass}">
                    <div class="approval-step ${stateClass}">
                        <div class="step-icon mb-2">
                            ${iconHtml}
                        </div>
                        <h6 class="small">${step.label}</h6>
                        <small class="text-muted">${statusText}</small>
                    </div>
                </div>
            `;
        }).join('');

        contentDiv.innerHTML = `
            <!-- Approval Progress -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Approval Progress</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Progress</span>
                            <span class="fw-bold">
                                ${event.status === 'Approved' ? '<span class="text-success"><i class="fas fa-check-circle"></i> Fully Approved</span>' :
                                  event.status === 'Rejected' ? '<span class="text-danger"><i class="fas fa-times-circle"></i> Rejected</span>' :
                                  `<span class="text-warning">${doneApprovalSteps} / ${totalApprovalSteps}</span>`}
                            </span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-${statusClass}" role="progressbar" style="width: ${progressPercentage}%">
                                ${progressPercentage}%
                            </div>
                        </div>
                    </div>

                    <!-- Approval Steps -->
                    <div class="row text-center g-2">
                        ${approvalStepsHtml}
                    </div>

                    <!-- Current Status -->
                    <div class="mt-3 text-center">
                        <span class="badge bg-${statusClass} fs-6">
                            <i class="fas fa-${event.status === 'Approved' ? 'check' : (event.status === 'Rejected' ? 'times' : 'clock')} me-1"></i>
                            ${event.status}
                        </span>
                        ${event.status === 'Approved' ? `
                        <div class="mt-3">
                            <a href="/events/${event.id}/pdf" class="btn btn-primary" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Download PDF
                            </a>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Category:</strong> ${categoryBadge}</p>
                    <p><strong>Date:</strong> ${new Date(event.event_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    <p><strong>Time:</strong> ${formatTime12(event.start_time)} - ${formatTime12(event.end_time)}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Location:</strong> ${event.location}</p>
                    <p><strong>Department:</strong> ${event.department || 'N/A'}</p>
                    <p><strong>Submitted:</strong> ${new Date(event.created_at).toLocaleString()}</p>
                </div>
            </div>
            <div class="mt-3">
                <p><strong>Description:</strong></p>
                <p class="text-muted">${event.description}</p>
            </div>
            ${event.notes ? `
            <div class="mt-3">
                <p><strong>Notes:</strong></p>
                <p class="text-muted">${event.notes}</p>
            </div>
            ` : ''}
            ${materialsHtml}

            <!-- Event Discussion -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Event Discussion</h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="chatContainer-${event.id}">
                    <div id="chatMessages-${event.id}" class="mb-3">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-spinner fa-spin"></i> Loading discussions...
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <form id="chatForm-${event.id}" class="d-flex gap-2">
                        <input type="text" id="chatMessage-${event.id}" class="form-control" placeholder="Type your message..." maxlength="1000">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        `;

        // Initialize discussion chat
        initializeDiscussionChat(event.id);
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading event details: ' + error.message + '</div>';
    });
}

function initializeDiscussionChat(eventId) {
    const chatMessages = document.getElementById(`chatMessages-${eventId}`);
    const chatForm = document.getElementById(`chatForm-${eventId}`);
    const chatMessage = document.getElementById(`chatMessage-${eventId}`);
    const chatContainer = document.getElementById(`chatContainer-${eventId}`);
    const currentUserId = {{ auth()->id() }};
    
    // Load discussions
    loadDiscussions();
    
    function loadDiscussions() {
        fetch(`/events/${eventId}/discussions`)
            .then(response => response.json())
            .then(data => {
                displayDiscussions(data);
            })
            .catch(error => {
                console.error('Error loading discussions:', error);
                chatMessages.innerHTML = '<div class="text-center text-muted py-3">Failed to load discussions</div>';
            });
    }
    
    function displayDiscussions(discussions) {
        if (discussions.length === 0) {
            chatMessages.innerHTML = '<div class="text-center text-muted py-3">No discussions yet. Start the conversation!</div>';
            return;
        }
        
        chatMessages.innerHTML = discussions.map(discussion => {
            const isOwn = discussion.user_id === currentUserId;
            const time = new Date(discussion.created_at).toLocaleString();
            
            return `
                <div class="mb-3 d-flex ${isOwn ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="d-flex flex-column ${isOwn ? 'align-items-end' : 'align-items-start'}" style="max-width: 75%;">
                        <div class="text-muted small mb-1">
                            <strong>${discussion.user ? discussion.user.name : 'Unknown'}</strong>
                            <span class="ms-1">${time}</span>
                        </div>
                        <div class="p-2 rounded ${isOwn ? 'bg-primary text-white' : 'bg-light text-dark'}" style="word-wrap: break-word;">
                            ${escapeHtml(discussion.message)}
                        </div>
                        ${isOwn ? `<button class="btn btn-link btn-sm text-danger p-0 mt-1" onclick="deleteDiscussion(${discussion.id}, ${eventId})">Delete</button>` : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatMessage.value.trim();
        if (!message) return;
        
        fetch(`/events/${eventId}/discussions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            chatMessage.value = '';
            loadDiscussions();
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        });
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Delete discussion function
function deleteDiscussion(discussionId, eventId) {
    if (!confirm('Are you sure you want to delete this message?')) return;
    
    fetch(`/discussions/${discussionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Reload discussions for this event
        initializeDiscussionChat(eventId);
    })
    .catch(error => {
        console.error('Error deleting message:', error);
        alert('Failed to delete message.');
    });
}

// View deleted event (includes soft-deleted events)
function viewDeletedEvent(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewEventModal'));
    const contentDiv = document.getElementById('viewEventContent');

    // Store current event ID
    window.currentEventId = id;

    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/events/' + id + '/deleted', {
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
            }).catch(() => {
                throw new Error('Failed to load deleted event');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }

        const event = data.event;
        const userName = event.user ? event.user.name : 'Unknown';
        const userRole = event.user ? event.user.role : '';

        const statusClass = event.status === 'Approved' ? 'success' :
            (event.status === 'Rejected' ? 'danger' :
            (event.status === 'Cancelled' ? 'secondary' : 'warning'));

        const categoryBadge = `<span class="badge bg-info">${event.category.charAt(0).toUpperCase() + event.category.slice(1)}</span>`;

        let materialsHtml = '';
        if (event.materials_needed && event.materials_needed.length > 0) {
            materialsHtml = `
                <div class="mt-3">
                    <p><strong>Materials Needed:</strong></p>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Qty</th>
                                <th>Item</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${event.materials_needed.map(m => `
                                <tr>
                                    <td>${m.qty ?? 1}</td>
                                    <td>${m.item ?? 'N/A'}</td>
                                    <td>${m.purpose ?? 'N/A'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        contentDiv.innerHTML = `
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Submitted by:</strong> ${userName} ${userRole ? `(${userRole.replace('_', ' ').toUpperCase()})` : ''}
            </div>
            <div class="alert alert-${statusClass}">
                <strong>Status:</strong> ${event.status}
                ${event.status === 'Rejected' && event.rejection_reason ? `<br><strong>Reason:</strong> ${event.rejection_reason}` : ''}
                ${event.status === 'Cancelled' && event.cancellation_reason ? `<br><strong>Reason:</strong> ${event.cancellation_reason}` : ''}
            </div>
            <h5 class="mb-3">${event.title}</h5>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Category:</strong> ${categoryBadge}</p>
                    <p><strong>Date:</strong> ${new Date(event.event_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    <p><strong>Time:</strong> ${formatTime12(event.start_time)} - ${formatTime12(event.end_time)}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Location:</strong> ${event.location}</p>
                    <p><strong>Department:</strong> ${event.department || 'N/A'}</p>
                    <p><strong>Submitted:</strong> ${new Date(event.created_at).toLocaleString()}</p>
                </div>
            </div>
            <div class="mt-3">
                <p><strong>Description:</strong></p>
                <p class="text-muted">${event.description}</p>
            </div>
            ${event.notes ? `
            <div class="mt-3">
                <p><strong>Notes:</strong></p>
                <p class="text-muted">${event.notes}</p>
            </div>
            ` : ''}
            ${materialsHtml}
            <div class="alert alert-warning mt-4">
                <i class="fas fa-trash me-2"></i><strong>This event has been deleted.</strong>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading event details: ' + error.message + '</div>';
    });
}

function showEventArchiveModal(eventId) {
    confirmArchive({
        title: 'Archive Event?',
        text: 'This event will be archived and hidden from your active list.',
        confirmButtonText: '<i class="fas fa-archive me-1"></i> Archive'
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Archiving...',
                html: '<div class="spinner-border text-primary"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/events/' + eventId + '/archive';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Bulk actions functions
function toggleSelectAll(type) {
    let checkboxes, selectAllCheckbox, updateFunction;
    
    if (type === 'active') {
        selectAllCheckbox = document.getElementById('selectAllActive');
        checkboxes = document.querySelectorAll('.active-checkbox');
        updateFunction = updateActiveBulkActions;
    } else if (type === 'archive') {
        selectAllCheckbox = document.getElementById('selectAllArchive');
        checkboxes = document.querySelectorAll('.archive-checkbox');
        updateFunction = updateArchiveBulkActions;
    } else if (type === 'deleted') {
        selectAllCheckbox = document.getElementById('selectAllDeleted');
        checkboxes = document.querySelectorAll('.deleted-checkbox');
        updateFunction = updateDeletedBulkActions;
    }
    
    if (checkboxes && selectAllCheckbox) {
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
        });
        if (updateFunction) updateFunction();
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

function cancelEventRequest(eventId) {
    Swal.fire({
        title: 'Cancel event request?',
        input: 'textarea',
        inputLabel: 'Reason (optional)',
        inputPlaceholder: 'Tell the approvers why this request is being cancelled…',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Cancel request',
        confirmButtonColor: '#dc3545',
        preConfirm: (reason) => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/events/${eventId}/cancel`;
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="reason">`;
            form.querySelector('[name="reason"]').value = reason || '';
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function batchArchiveSelected() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    Swal.fire({
        title: 'Archive Events?',
        text: `Are you sure you want to archive ${ids.length} event(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, archive them',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/events/batch-archive', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            }).then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error archiving events'
                });
            });
        }
    });
}

function batchSoftDeleteSelected() {
    const selected = document.querySelectorAll('.active-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    Swal.fire({
        title: 'Delete Events?',
        text: `Are you sure you want to delete ${ids.length} event(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete them',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/events/batch-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            }).then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error deleting events'
                });
            });
        }
    });
}

function batchRestoreSelected() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    Swal.fire({
        title: 'Restoring...',
        text: `Restoring ${ids.length} event(s)...`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            fetch('/events/batch-restore', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            }).then(response => response.json())
            .then(data => {
                Swal.fire({icon: 'success', title: 'Restored!', text: data.message, timer: 2000, showConfirmButton: false}).then(() => location.reload());
            })
            .catch(error => {
                Swal.fire({icon: 'error', title: 'Error', text: 'Error restoring events'});
            });
        }
    });
}

function batchDeleteFromArchiveSelected() {
    const selected = document.querySelectorAll('.archive-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    Swal.fire({
        title: 'Delete Archived Events?',
        text: `Are you sure you want to delete ${ids.length} archived event(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete them'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/events/batch-delete', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            }).then(response => response.json())
            .then(data => {
                Swal.fire({icon: 'success', title: 'Deleted!', text: data.message, timer: 2000, showConfirmButton: false}).then(() => location.reload());
            })
            .catch(error => {
                Swal.fire({icon: 'error', title: 'Error', text: 'Error deleting events'});
            });
        }
    });
}

function batchRestoreDeletedSelected() {
    const selected = document.querySelectorAll('.deleted-checkbox:checked');
    const ids = Array.from(selected).map(cb => cb.value);
    
    if (ids.length === 0) return;
    
    Swal.fire({
        title: 'Restoring...',
        text: `Restoring ${ids.length} event(s)...`,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
            fetch('/events/batch-restore-deleted', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: ids })
            }).then(response => response.json())
            .then(data => {
                Swal.fire({icon: 'success', title: 'Restored!', text: data.message, timer: 2000, showConfirmButton: false}).then(() => location.reload());
            })
            .catch(error => {
                Swal.fire({icon: 'error', title: 'Error', text: 'Error restoring events'});
            });
        }
    });
}

function batchPermanentDeleteSelected() {
    // Implementation for bulk permanent delete
}

// Context menu functions
function contextView() {
    // Implementation for context view
}

function contextEdit() {
    // Implementation for context edit
}

function contextArchive() {
    if (window.selectedEventId && window.selectedEventView === 'active') {
        showEventArchiveModal(window.selectedEventId);
    }
}

function contextDelete() {
    if (window.selectedEventId && window.selectedEventView === 'active') {
        confirmDelete({
            title: 'Delete Event?',
            text: 'This event will be moved to deleted. You can restore it later.',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Delete'
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/events/' + window.selectedEventId + '/soft-delete';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
}

function contextRestore() {
    if (window.selectedEventId && (window.selectedEventView === 'archived' || window.selectedEventView === 'deleted')) {
        confirmRestore({
            title: 'Restore Event?',
            text: 'This will move the event back to active events.',
            confirmButtonText: '<i class="fas fa-trash-restore me-1"></i> Restore'
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/events/' + window.selectedEventId + '/restore';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
}

function contextDeleteFromArchive() {
    if (window.selectedEventId && window.selectedEventView === 'archived') {
        confirmDelete({
            title: 'Delete Archived Event?',
            text: 'This archived event will be moved to deleted.',
            confirmButtonText: '<i class="fas fa-trash me-1"></i> Delete'
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/events/' + window.selectedEventId + '/soft-delete';
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);
                
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
}

function contextRestoreDeleted() {
    // Implementation for context restore deleted
}

function contextPermanentDelete() {
    // Implementation for context permanent delete
}

// Auto-open modal if URL contains open_modal=true
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    console.log('[Open Modal] Checking for open_modal parameter:', urlParams.get('open_modal'));
    console.log('[Open Modal] Full URL before:', window.location.href);
    
    if (urlParams.get('open_modal') === 'true') {
        const modal = new bootstrap.Modal(document.getElementById('eventRequestModal'));
        modal.show();

        // Clean up URL by removing ONLY the open_modal parameter
        urlParams.delete('open_modal');
        const newSearch = urlParams.toString();
        const newUrl = window.location.pathname + (newSearch ? '?' + newSearch : '') + window.location.hash;
        window.history.replaceState({}, document.title, newUrl);
        console.log('[Open Modal] URL cleaned to:', newUrl);
    }

    // Reopen modal if there are validation errors
    @if($errors->any())
        const modal = new bootstrap.Modal(document.getElementById('eventRequestModal'));
        modal.show();
    @endif

    // Auto-refresh after delete/restore operations
    // Intercept delete/restore/archive form submissions and reload the page after success
    document.querySelectorAll('form[action*="/delete"], form[action*="/restore"], form[action*="/archive"]').forEach(function(form) {
        const originalSubmit = form.onsubmit;
        form.addEventListener('submit', function(e) {
            // Check if this is from data-confirm button
            const submitBtn = form.querySelector('[data-confirm]');
            if (submitBtn) {
                e.preventDefault();
                
                swalConfirm({
                    title: submitBtn.dataset.confirmTitle || 'Are you sure?',
                    text: submitBtn.dataset.confirm,
                    icon: submitBtn.dataset.confirmIcon || 'warning',
                    confirmButtonText: submitBtn.dataset.confirmOk || 'Yes',
                    confirmButtonColor: submitBtn.dataset.confirmColor || '#0d6efd',
                }).then(result => {
                    if (result.isConfirmed) {
                        // Submit form via AJAX to handle success
                        const formData = new FormData(form);
                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message || 'Operation completed successfully.',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Get current tab from URL or default to active
                                    const urlParams = new URLSearchParams(window.location.search);
                                    let currentView = urlParams.get('view') || 'active';
                                    
                                    // Redirect logic:
                                    // - Restore always goes to Active tab
                                    // - Delete from Active tab goes to Active tab
                                    // - Archive from Active tab goes to Active tab
                                    // - Delete from other tabs stays on current tab
                                    if (form.action.includes('/restore')) {
                                        // Restore always goes to active
                                        window.location.href = '{{ route("events.my") }}?view=active';
                                    } else if (form.action.includes('/archive') || form.action.includes('/delete')) {
                                        // Archive or delete from any tab goes to active
                                        window.location.href = '{{ route("events.my") }}?view=active';
                                    } else {
                                        // For other operations, preserve current tab
                                        window.location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message || 'Operation failed.',
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                            });
                        });
                    }
                });
            }
        }, true); // Use capture phase to intercept before other handlers
    });
});
</script>

<script>
// Auto-open event modal if event_id is in URL
console.log('[Auto-open] Script loaded - events page');
document.addEventListener('DOMContentLoaded', function() {
    console.log('[Auto-open] DOMContentLoaded fired');
    const urlParams = new URLSearchParams(window.location.search);
    const eventId = urlParams.get('event_id');
    
    console.log('[Auto-open] URL params:', window.location.search);
    console.log('[Auto-open] Event ID:', eventId);
    
    if (eventId) {
        console.log('[Auto-open] Found event_id:', eventId);
        console.log('[Auto-open] viewEvent function exists?', typeof viewEvent === 'function');
        
        // Function to try opening the modal
        function tryOpenModal(retryCount = 0) {
            if (typeof viewEvent === 'function') {
                console.log('[Auto-open] Opening event modal for ID:', eventId);
                setTimeout(function() {
                    viewEvent(parseInt(eventId));
                    // Clean URL without reloading page
                    const cleanUrl = window.location.pathname + window.location.hash;
                    window.history.replaceState({}, document.title, cleanUrl);
                    console.log('[Auto-open] Modal opened and URL cleaned');
                }, 300);
            } else {
                console.error('[Auto-open] viewEvent function not found, retry count:', retryCount);
                if (retryCount < 5) {
                    // Retry after a delay
                    setTimeout(function() {
                        tryOpenModal(retryCount + 1);
                    }, 500);
                } else {
                    console.error('[Auto-open] Failed to find viewEvent function after 5 retries');
                }
            }
        }
        
        tryOpenModal();
    } else {
        console.log('[Auto-open] No event_id in URL');
    }
});
</script>

@endsection
