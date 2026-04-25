@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-calendar-alt"></i> Event Requests</h2>
<p>Manage all event requests</p>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-4">
        <div class="col-md-6">
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('events.calendar') }}" class="btn btn-info">
                <i class="fas fa-calendar"></i> Calendar View
            </a>
            <a href="{{ route('events.pending') }}" class="btn btn-secondary">
                <i class="fas fa-hourglass-half"></i> Pending Only
            </a>
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

    <!-- Event Action Confirmation Modal -->
    <div class="modal fade" id="eventActionModal" tabindex="-1" aria-labelledby="eventActionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="eventActionModalHeader">
                    <h5 class="modal-title" id="eventActionModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="eventActionMessage"></p>
                    <div id="eventActionAlert" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="eventActionConfirmBtn"><i class="fas fa-check"></i> Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Event Modal -->
    <div class="modal fade" id="archiveEventModal" tabindex="-1" aria-labelledby="archiveEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveEventModalLabel">Archive Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="archiveEventForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="archiveEventId" name="event_id" value="">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> This event will be archived and hidden from your active list.
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

    <!-- Delete Event Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">Delete Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteEventForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" id="deleteEventId" name="event_id" value="">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> This event will be moved to deleted. You can restore it later from the Deleted tab.
                        </div>
                        <p class="mb-0">Are you sure you want to delete this event?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <ul class="nav nav-pills mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ ($viewType ?? 'active') == 'active' ? 'active' : '' }}" href="{{ route('admin.events', ['view' => 'active']) }}">
                                <i class="fas fa-calendar-check"></i> Active Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($viewType ?? '') == 'archives' ? 'active' : '' }}" href="{{ route('admin.events', ['view' => 'archives']) }}">
                                <i class="fas fa-archive"></i> Archived Events
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ ($viewType ?? '') == 'deleted' ? 'active' : '' }}" href="{{ route('admin.events', ['view' => 'deleted']) }}" style="color: #dc3545;">
                                <i class="fas fa-trash-alt"></i> Deleted Events
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-7">
                    <form method="GET" action="{{ route('admin.events') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="{{ $viewType ?? 'active' }}">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..." 
                                value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                                <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <option value="Area Use" {{ request('category') == 'Area Use' ? 'selected' : '' }}>Area Use</option>

                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date" 
                                value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date" 
                                value="{{ request('date_to') }}">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.events') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(($viewType ?? 'active') == 'active')
    <!-- Summary Cards -->
    <div class="row mb-4" style="display: flex !important;">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Requests</h5>
                    <h3>{{ $requests->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h3>{{ $requests->where('status', 'Pending')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h3>{{ $requests->where('status', 'Approved')->count() }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Rejected</h5>
                    <h3>{{ $requests->where('status', 'Rejected')->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    @if($requests->count() > 0)
        <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
            <table class="table table-hover" style="display: table !important;">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Requestor</th>
                        <th>Category</th>
                        <th>Event Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                        <tr>
                            <td>{{ $request->title }}</td>
                            <td>{{ $request->user->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info">{{ ucfirst($request->category) }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                            <td>{{ $request->location }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $request->status == 'Approved' ? 'success' : 
                                    ($request->status == 'Pending' ? 'warning' : 
                                    ($request->status == 'Rejected' ? 'danger' : 'secondary'))
                                }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0" data-bs-toggle="modal"
                                        data-bs-target="#viewModal{{ $request->id }}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($request->status == 'Pending' && auth()->user()->role !== 'mis')
                                        <form method="POST" action="{{ route('events.approve', $request->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success bg-transparent border-0" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('events.reject', $request->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @php
                                        $userRole = auth()->user()->role;
                                        $archiveColumn = $userRole . '_archived';
                                        $isArchivedByRole = $request->$archiveColumn ?? false;
                                    @endphp
                                    @if(!$isArchivedByRole)
                                        <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" title="Archive" onclick="showArchiveEventModal({{ $request->id }})">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" title="Delete" onclick="showDeleteEventModal({{ $request->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal{{ $request->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Event Request Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Reference Number:</strong> EVT-{{ str_pad($request->id, 5, '0', STR_PAD_LEFT) }}</p>
                                                <p><strong>Title:</strong> {{ $request->title }}</p>
                                                <p><strong>Category:</strong> {{ ucfirst($request->category) }}</p>
                                                <p><strong>Status:</strong> 
                                                    <span class="badge bg-{{ 
                                                        $request->status == 'Approved' ? 'success' : 
                                                        ($request->status == 'Pending' ? 'warning' : 
                                                        ($request->status == 'Rejected' ? 'danger' : 'secondary'))
                                                    }}">
                                                        {{ $request->status }}
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Requestor:</strong> {{ $request->user->name ?? 'N/A' }}</p>
                                                <p><strong>Event Date:</strong> {{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</p>
                                                <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</p>
                                                <p><strong>Location:</strong> {{ $request->location }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <p><strong>Description:</strong></p>
                                        <p>{{ $request->description }}</p>
                                        @if($request->notes)
                                            <hr>
                                            <p><strong>Notes:</strong></p>
                                            <p>{{ $request->notes }}</p>
                                        @endif
                                        
                                        <!-- Progress Tracker -->
                                        <hr>
                                        <x-request-progress-tracker :request="$request" title="Approval Progress" />
                                        
                                        <!-- PDF Download Button for Approved Events -->
                                        @if($request->status == 'Approved')
                                        <div class="text-center my-3">
                                            <a href="{{ route('events.pdf', $request->id) }}" class="btn btn-primary btn-lg" target="_blank">
                                                <i class="fas fa-file-pdf me-2"></i> Download PDF
                                            </a>
                                        </div>
                                        @endif
                                        
                                        @if($request->approved_by)
                                            <hr>
                                            <p><strong>Processed By:</strong> {{ $request->approver->name ?? 'N/A' }}</p>
                                            <p><strong>Processed At:</strong> {{ $request->approved_at ? \Carbon\Carbon::parse($request->approved_at)->format('M d, Y h:i A') : 'N/A' }}</p>
                                        @endif
                                    </div>
                                    <div class="modal-footer">
                                        @if($request->status == 'Pending' && auth()->user()->role !== 'mis')
                                            <form method="POST" action="{{ route('events.approve', $request->id) }}" class="d-inline">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </div>
                                            </form>
                                            <form method="POST" action="{{ route('events.reject', $request->id) }}" class="d-inline">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="text" name="notes" class="form-control" placeholder="Reason for rejection">
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        @endif
                                        @if($request->status == 'Approved')
                                            <a href="{{ route('events.pdf', $request->id) }}" class="btn btn-primary" target="_blank">
                                                <i class="fas fa-file-pdf me-1"></i> Download PDF
                                            </a>
                                        @endif
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No event requests found</h4>
                <p>There are no event requests matching your filters.</p>
                <a href="{{ route('admin.events') }}" class="btn btn-primary">View All Requests</a>
            </div>
        </div>
    @endif
    @endif

    @if(($viewType ?? '') == 'archives')
    <!-- Archived Events Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-archive"></i> Archived Events</h5>
                </div>
                <div class="card-body">
                    @if(isset($archivedEvents) && $archivedEvents->count() > 0)
                        <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                            <table class="table table-hover" style="display: table !important;">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Requestor</th>
                                        <th>Category</th>
                                        <th>Event Date</th>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($archivedEvents as $event)
                                        <tr>
                                            <td>{{ $event->title }}</td>
                                            <td>{{ $event->user->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ ucfirst($event->category) }}</span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}</td>
                                            <td>{{ $event->location }}</td>
                                            <td>
                                                <span class="badge bg-{{
                                                    $event->status == 'Approved' ? 'success' :
                                                    ($event->status == 'Pending' ? 'warning' :
                                                    ($event->status == 'Rejected' ? 'danger' : 'secondary'))
                                                }}">
                                                    {{ $event->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-primary bg-transparent" data-bs-toggle="modal"
                                                        data-bs-target="#viewModal{{ $event->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form method="POST" action="{{ route('admin.archive.restore') }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="type" value="event">
                                                        <input type="hidden" name="id" value="{{ $event->id }}">
                                                        <button type="submit" class="btn btn-sm btn-success bg-transparent" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal{{ $event->id }}" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Event Request Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Reference Number:</strong> EVT-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</p>
                                                                <p><strong>Title:</strong> {{ $event->title }}</p>
                                                                <p><strong>Category:</strong> {{ ucfirst($event->category) }}</p>
                                                                <p><strong>Status:</strong>
                                                                    <span class="badge bg-{{
                                                                        $event->status == 'Approved' ? 'success' :
                                                                        ($event->status == 'Pending' ? 'warning' :
                                                                        ($event->status == 'Rejected' ? 'danger' : 'secondary'))
                                                                    }}">
                                                                        {{ $event->status }}
                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Requestor:</strong> {{ $event->user->name ?? 'N/A' }}</p>
                                                                <p><strong>Event Date:</strong> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</p>
                                                                <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}</p>
                                                                <p><strong>Location:</strong> {{ $event->location }}</p>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <p><strong>Description:</strong></p>
                                                        <p>{{ $event->description }}</p>
                                                        @if($event->notes)
                                                            <hr>
                                                            <p><strong>Notes:</strong></p>
                                                            <p>{{ $event->notes }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-archive"></i> No archived events found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Deleted Events View -->
    @if(($viewType ?? '') == 'deleted')
    <!-- Context Menu (Right-Click) -->
    <div id="deletedEventsContextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" onclick="deletedEventsContextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" onclick="deletedEventsContextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" onclick="deletedEventsContextPermanentDelete()"><i class="fas fa-times-circle"></i> Permanently Delete</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-trash-alt"></i> Deleted Events</h2>
            <p class="text-muted">Events in this folder can be restored or permanently deleted.</p>
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
                    <h5 class="mb-1"><i class="fas fa-info-circle"></i> About Deleted Events</h5>
                    <p class="mb-0 text-muted">Events that have been deleted are moved here. You can restore them to their original state or permanently delete them. Once permanently deleted, events cannot be recovered.</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-warning fs-5">{{ $deletedEvents->count() }} events</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-filter Settings -->
    <div class="card mb-4 border-info">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1"><i class="fas fa-clock"></i> Auto-filter Settings</h5>
                    <p class="mb-0 text-muted">Show events that have been deleted for the selected period or less.</p>
                </div>
                <div class="col-md-4">
                    <select id="retentionDays" class="form-select">
                        <option value="3" {{ ($days ?? 15) == 3 ? 'selected' : '' }}>3 days</option>
                        <option value="7" {{ ($days ?? 15) == 7 ? 'selected' : '' }}>7 days</option>
                        <option value="15" {{ ($days ?? 15) == 15 ? 'selected' : '' }}>15 days</option>
                        <option value="30" {{ ($days ?? 15) == 30 ? 'selected' : '' }}>30 days</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($deletedEvents->count() > 0)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form id="deletedEventsBulkRestoreForm" method="POST" action="{{ route('admin.deletedEvents.restoreSelected') }}">
                        @csrf
                        <input type="hidden" name="event_ids" id="selectedDeletedEventIds">
                        <button type="button" class="btn btn-success" onclick="deletedEventsBulkRestore()">
                            <i class="fas fa-trash-restore"></i> Restore Selected
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletedEventsPermanentDeleteAllModal">
                        <i class="fas fa-times-circle"></i> Permanently Delete All
                    </button>
                    <span id="deletedEventsSelectedCount" class="text-muted ms-3">0 events selected</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Deleted Events Table -->
    <div class="card">
        <div class="card-body">
            @if(isset($deletedEvents) && $deletedEvents->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;" id="deletedEventsTable">
                        <thead>
                            <tr>
                                <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="deletedEventsSelectAll" onchange="deletedEventsToggleSelectAll()"></th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Event Date</th>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th>Deleted Date</th>
                                <th>Deleted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deletedEvents as $event)
                                <tr data-id="{{ $event->id }}">
                                    <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-event-checkbox" value="{{ $event->id }}" onchange="deletedEventsUpdateSelectedCount()"></td>
                                    <td>{{ $event->title }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $event->getCategoryLabel() }}
                                        </span>
                                    </td>
                                    <td>{{ $event->event_date->format('M d, Y') }}</td>
                                    <td>{{ $event->location }}</td>
                                    <td>{{ $event->department }}</td>
                                    <td>
                                        @php
                                            $priorityColors = [
                                                'low' => 'success',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'urgent' => 'dark'
                                            ];
                                            $priorityClass = $priorityColors[$event->priority] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $priorityClass }}">
                                            {{ ucfirst($event->priority) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($event->status) {
                                                'Pending' => 'warning',
                                                'Approved' => 'success',
                                                'Rejected' => 'danger',
                                                'Cancelled' => 'secondary',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">
                                            {{ $event->status }}
                                        </span>
                                    </td>
                                    <td>{{ $event->user ? $event->user->name : 'Unknown' }}</td>
                                    <td>{{ $event->updated_at->format('M d, Y h:i A') }}</td>
                                    <td>{{ $event->deletedBy ? $event->deletedBy->name : 'System' }}</td>
                                    <td>
                                        <div class="action-icons">
                                            <button type="button" class="btn btn-sm btn-info bg-transparent" data-bs-toggle="modal" data-bs-target="#deletedEventsViewModal{{ $event->id }}" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form action="{{ route('admin.deletedEvents.restore', $event->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-sm btn-success bg-transparent" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.deletedEvents.permanentDelete', $event->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger bg-transparent" title="Permanently Delete" onclick="return confirm('Are you sure you want to permanently delete this event?')">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Permanent Delete Confirmation Modal -->
                                <div class="modal fade" id="deletedEventsPermanentDeleteModal{{ $event->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Permanently Delete Event</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to permanently delete <strong>{{ $event->title }}</strong>?</p>
                                                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. The event will be permanently removed from the system.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('admin.deletedEvents.permanentDelete', $event->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Permanently Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- View Event Modal -->
                                <div class="modal fade" id="deletedEventsViewModal{{ $event->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Event Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Reference Number:</strong> EVT-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</p>
                                                        <p><strong>Title:</strong> {{ $event->title }}</p>
                                                        <p><strong>Category:</strong> {{ ucfirst($event->category) }}</p>
                                                        <p><strong>Status:</strong>
                                                            <span class="badge bg-{{
                                                                $event->status == 'Approved' ? 'success' :
                                                                ($event->status == 'Pending' ? 'warning' :
                                                                ($event->status == 'Rejected' ? 'danger' : 'secondary'))
                                                            }}">
                                                                {{ $event->status }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Requestor:</strong> {{ $event->user->name ?? 'N/A' }}</p>
                                                        <p><strong>Event Date:</strong> {{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</p>
                                                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}</p>
                                                        <p><strong>Location:</strong> {{ $event->location }}</p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p><strong>Description:</strong></p>
                                                <p>{{ $event->description }}</p>
                                                @if($event->notes)
                                                    <hr>
                                                    <p><strong>Notes:</strong></p>
                                                    <p>{{ $event->notes }}</p>
                                                @endif
                                                @if($event->deletedBy)
                                                    <hr>
                                                    <p><strong>Deleted By:</strong> {{ $event->deletedBy->name }}</p>
                                                    <p><strong>Deleted Date:</strong> {{ $event->updated_at->format('M d, Y h:i A') }}</p>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center p-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-check-circle fa-2x d-block mb-3"></i>
                                            <h5>No Deleted Events</h5>
                                            <p class="mb-0">Deleted events will appear here. You can delete events from the Events page.</p>
                                            <a href="{{ route('admin.events') }}" class="btn btn-primary mt-3">
                                                <i class="fas fa-calendar-alt"></i> Go to Events
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-2x d-block mb-3 text-success"></i>
                        <h5>No Deleted Events</h5>
                        <p class="mb-0 text-muted">Deleted events will appear here. You can delete events from the Events page.</p>
                        <a href="{{ route('admin.events') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-calendar-alt"></i> Go to Events
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

<script>
// Archive Event Function
function showArchiveEventModal(eventId) {
    document.getElementById('archiveEventForm').action = '/events/' + eventId + '/archive';
    document.getElementById('archiveEventId').value = eventId;

    var modal = new bootstrap.Modal(document.getElementById('archiveEventModal'));
    modal.show();
}

// Delete Event Function
function showDeleteEventModal(eventId) {
    document.getElementById('deleteEventForm').action = '/events/' + eventId + '/delete';
    document.getElementById('deleteEventId').value = eventId;

    var modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
    modal.show();
}
// Event Action Modal Functions
let eventActionType = null;
let eventActionId = null;

function showEventActionModal(type, id, name) {
    eventActionType = type;
    eventActionId = id;

    const modal = new bootstrap.Modal(document.getElementById('eventActionModal'));
    const modalHeader = document.getElementById('eventActionModalHeader');
    const modalMessage = document.getElementById('eventActionMessage');
    const modalAlert = document.getElementById('eventActionAlert');
    const confirmBtn = document.getElementById('eventActionConfirmBtn');

    modalAlert.classList.remove('alert-info', 'alert-warning', 'alert-danger', 'd-none');

    if (type === 'archive') {
        modalHeader.className = 'modal-header bg-secondary text-white';
        document.getElementById('eventActionModalLabel').innerHTML = '<i class="fas fa-archive"></i> Archive Event';
        modalMessage.innerHTML = 'Are you sure you want to archive <strong>' + name + '</strong>?';
        modalAlert.classList.add('alert-info');
        modalAlert.innerHTML = '<i class="fas fa-info-circle"></i> You can restore this event later from the Archive tab.';
        confirmBtn.className = 'btn btn-secondary';
        confirmBtn.innerHTML = '<i class="fas fa-archive"></i> Archive';
    } else if (type === 'delete') {
        modalHeader.className = 'modal-header bg-danger text-white';
        document.getElementById('eventActionModalLabel').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Delete Event';
        modalMessage.innerHTML = 'Are you sure you want to delete <strong>' + name + '</strong>?';
        modalAlert.classList.add('alert-warning');
        modalAlert.innerHTML = '<i class="fas fa-warning"></i> This action will move the event to deleted. You can restore it later from the Deleted tab.';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }

    // Set up confirm button click
    confirmBtn.onclick = function() {
        executeEventAction(type, id);
    };

    modal.show();
}

function executeEventAction(type, id) {
    let url = '';
    let method = 'POST';

    if (type === 'archive') {
        url = '/events/' + id + '/archive';
    } else if (type === 'delete') {
        url = '/events/' + id + '/delete';
    }

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(type.charAt(0).toUpperCase() + type.slice(1) + ' successful!');
            location.reload();
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error performing action');
    });

    // Close modal
    const modalEl = document.getElementById('eventActionModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}
</script>

@if(($viewType ?? '') == 'deleted')
<!-- Permanent Delete All Confirmation Modal for Deleted Events -->
<div class="modal fade" id="deletedEventsPermanentDeleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Permanently Delete All Events</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete all <strong>{{ $deletedEvents->count() }}</strong> events?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. All events will be permanently removed from the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.deletedEvents.permanentDeleteAll') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Permanently Delete All</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Context menu variables for deleted events
    let deletedEventsContextMenu = document.getElementById('deletedEventsContextMenu');
    let currentDeletedEventId = null;

    // Toggle select all checkboxes for deleted events
    function deletedEventsToggleSelectAll() {
        const selectAll = document.getElementById('deletedEventsSelectAll');
        const checkboxes = document.querySelectorAll('.deleted-event-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        deletedEventsUpdateSelectedCount();
    }

    // Update selected count for deleted events
    function deletedEventsUpdateSelectedCount() {
        const countEl = document.getElementById('deletedEventsSelectedCount');
        const eventIdsEl = document.getElementById('selectedDeletedEventIds');
        
        // Return early if elements don't exist (no events)
        if (!countEl || !eventIdsEl) {
            return;
        }
        
        const checkboxes = document.querySelectorAll('.deleted-event-checkbox:checked');
        const count = checkboxes.length;
        countEl.textContent = count + ' event' + (count !== 1 ? 's' : '') + ' selected';
        
        // Update hidden input for bulk form
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        eventIdsEl.value = JSON.stringify(selectedIds);
    }

    // Bulk restore function for deleted events
    function deletedEventsBulkRestore() {
        const checkboxes = document.querySelectorAll('.deleted-event-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one event to restore.');
            return;
        }
        
        if (confirm('Are you sure you want to restore ' + checkboxes.length + ' event(s)?')) {
            document.getElementById('deletedEventsBulkRestoreForm').submit();
        }
    }

    // Context menu functions for deleted events
    function showDeletedEventsContextMenu(e, eventId) {
        e.preventDefault();
        currentDeletedEventId = eventId;
        
        deletedEventsContextMenu.style.display = 'block';
        deletedEventsContextMenu.style.left = e.pageX + 'px';
        deletedEventsContextMenu.style.top = e.pageY + 'px';
    }

    function hideDeletedEventsContextMenu() {
        deletedEventsContextMenu.style.display = 'none';
    }

    function deletedEventsContextView() {
        hideDeletedEventsContextMenu();
        const modalId = 'deletedEventsViewModal' + currentDeletedEventId;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    function deletedEventsContextRestore() {
        hideDeletedEventsContextMenu();
        if (confirm('Are you sure you want to restore this event?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/deleted-events/' + currentDeletedEventId + '/restore';
            
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

    function deletedEventsContextPermanentDelete() {
        hideDeletedEventsContextMenu();
        const modalId = 'deletedEventsPermanentDeleteModal' + currentDeletedEventId;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    // Add right-click listeners to deleted events table rows
    document.querySelectorAll('#deletedEventsTable tbody tr').forEach(row => {
        row.addEventListener('contextmenu', (e) => {
            const eventId = row.getAttribute('data-id');
            if (eventId) {
                showDeletedEventsContextMenu(e, eventId);
            }
        });
    });

    // Hide context menu on click outside
    document.addEventListener('click', (e) => {
        if (deletedEventsContextMenu && !deletedEventsContextMenu.contains(e.target)) {
            hideDeletedEventsContextMenu();
        }
    });

    // Handle retention days dropdown change (only if element exists) and page load
    document.addEventListener('DOMContentLoaded', function() {
        // Update selected count on page load
        deletedEventsUpdateSelectedCount();

        // Handle retention days dropdown change
        const retentionDaysElement = document.getElementById('retentionDays');
        if (retentionDaysElement) {
            retentionDaysElement.addEventListener('change', function() {
                const days = this.value;
                if (confirm(`Set auto-filter to show events deleted more than ${days} days ago?`)) {
                    // Show loading indicator
                    this.disabled = true;

                    // Make AJAX request to save preference
                    fetch('{{ route("saveAutoDeletePreference") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ days: parseInt(days), module: 'event_requests' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show filtered results
                            window.location.href = '{{ route("admin.events", ["view" => "deleted"]) }}&days=' + days;
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
                    this.value = '{{ $days ?? 15 }}';
                }
            });
        }
    });
</script>
@endif

@endsection
