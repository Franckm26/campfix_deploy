@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<div style="display:flex;align-items:center;gap:12px">
    <img src="{{ asset('Campfix/Images/images.png') }}" alt="STI Logo" style="height:40px">
    <h2 style="margin:0">Home</h2>
</div>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-4">
        <div class="col-12">
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5>Pending Approvals</h5>
                    <h2>{{ $pendingEvents }}</h2>
                    <a href="{{ route('events.pending') }}" class="text-white">Review Now</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Upcoming Events</h5>
                    <h2>{{ $approvedEvents }}</h2>
                    <a href="{{ route('events.calendar') }}" class="text-white">View Calendar</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Concerns</h5>
                    <h2>{{ $totalConcerns }}</h2>
                    <a href="{{ route('admin.reports') }}" class="text-white">View Reports</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">Pending Event Approvals</h5>
                </div>
                <div class="card-body">
                    @if($pendingEvents > 0)
                        <p>You have {{ $pendingEvents }} event request(s) waiting for your approval.</p>
                        <a href="{{ route('events.pending') }}" class="btn btn-warning">
                            Review Requests
                        </a>
                    @else
                        <p class="text-muted">No pending approvals.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Campus Overview</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Concerns Reported
                            <span class="badge bg-primary rounded-pill">{{ $totalConcerns }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Unresolved Concerns
                            <span class="badge bg-warning rounded-pill">{{ $pendingConcerns }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Upcoming Approved Events
                            <span class="badge bg-success rounded-pill">{{ $approvedEvents }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Approved Events List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="mb-0"><i class="fas fa-calendar-check me-1"></i> Upcoming Approved Events</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                        <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-calendar"></i> Full Calendar
                        </a>
                    </div>
                </div>
                <div class="card-body p-3">
                    @if($upcomingEventsList->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcomingEventsList as $event)
                            <div class="list-group-item d-flex justify-content-between align-items-start border-0 px-0 py-2">
                                <div class="ms-2 me-auto">
                                    <div class="fw-bold text-primary">{{ $event->title }}</div>
                                    <div class="text-muted small">
                                        <i class="fas fa-map-marker-alt me-1"></i>{{ $event->location }}
                                        @if($event->department)
                                            <span class="ms-2"><i class="fas fa-building me-1"></i>{{ $event->department }}</span>
                                        @endif
                                    </div>
                                    @if($event->description)
                                        <div class="text-muted small mt-1">{{ Str::limit($event->description, 80) }}</div>
                                    @endif
                                </div>
                                <div class="text-end">
                                    <div class="badge bg-success mb-1">{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</div>
                                    <div class="text-muted small">
                                        {{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} - 
                                        {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($approvedEvents > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('events.calendar') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-calendar"></i> View All Events ({{ $approvedEvents }} total)
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Upcoming Events</h5>
                            <p class="text-muted mb-3">There are no approved events scheduled for the coming days.</p>
                            <a href="{{ route('events.calendar') }}" class="btn btn-outline-primary">
                                <i class="fas fa-calendar"></i> View Events Calendar
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel"><i class="fas fa-calendar-plus"></i> Submit Event Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('events.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal-title" class="form-label">Event Title *</label>
                        <input type="text" class="form-control" id="modal-title" name="title" placeholder="e.g., Science Fair 2026, Faculty Meeting" required>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="modal-category" class="form-label">Category *</label>
                            <select class="form-select" id="modal-category" name="category" required>
                                <option value="">Select category</option>
                                <option value="Area Use">Area Use</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal-priority" class="form-label">Priority</label>
                            <select class="form-select" id="modal-priority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="modal-department" class="form-label">Department</label>
                            <select class="form-select" id="modal-department" name="department">
                                <option value="">Select department</option>
                                <option value="GE">GE</option>
                                <option value="ICT">ICT</option>
                                <option value="Business Management">Business Management</option>
                                <option value="THM">THM</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal-event_date" class="form-label">Date *</label>
                        <input type="date" class="form-control" id="modal-event_date" name="event_date" min="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="modal-start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control" id="modal-start_time" name="start_time" required>
                        </div>
                        <div class="col-md-6">
                            <label for="modal-end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control" id="modal-end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="modal-location" class="form-label">Location *</label>
                        <input type="text" class="form-control" id="modal-location" name="location" placeholder="e.g., Audio Visual Room, Gymnasium, Room 301" required>
                    </div>
                    <div class="mb-3">
                        <label for="modal-description" class="form-label">Description *</label>
                        <textarea class="form-control" id="modal-description" name="description" rows="3" placeholder="Describe the event purpose and details..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- No scripts needed -->
@endsection
