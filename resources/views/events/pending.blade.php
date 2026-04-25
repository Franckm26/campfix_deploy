@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-check-circle"></i> Pending Event Requests</h2>
<p>Review and approve event/agenda requests</p>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-4">
        <div class="col-12 text-end">
            <a href="{{ route('events.calendar') }}" class="btn btn-info">
                <i class="fas fa-calendar"></i> Approved Events
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('events.pending') }}" class="row g-2 align-items-center">
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
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <option value="event" {{ request('category') == 'event' ? 'selected' : '' }}>Event</option>
                        <option value="meeting" {{ request('category') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                        <option value="activity" {{ request('category') == 'activity' ? 'selected' : '' }}>Activity</option>
                        <option value="training" {{ request('category') == 'training' ? 'selected' : '' }}>Training</option>
                        <option value="other" {{ request('category') == 'other' ? 'selected' : '' }}>Other</option>
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
                    <a href="{{ route('events.pending') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    @if($requests->count() > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Requestor</th>
                                <th>Category</th>
                                <th>Date</th>
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
                                    <td>{{ $request->user->name }}</td>
                                    <td>{{ ucfirst($request->category) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</td>
                                    <td>{{ $request->location }}</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $request->status }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $request->id }}">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title">{{ $request->title }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Progress Tracker -->
                                                <x-request-progress-tracker :request="$request" title="Approval Progress" />
                                                
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <p><strong>Requestor:</strong> {{ $request->user->name }}</p>
                                                        <p><strong>Email:</strong> {{ $request->user->email }}</p>
                                                        <p><strong>Category:</strong> {{ ucfirst($request->category) }}</p>
                                                        @if($request->department)
                                                            <p><strong>Department:</strong> {{ $request->department }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($request->event_date)->format('M d, Y') }}</p>
                                                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($request->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($request->end_time)->format('g:i A') }}</p>
                                                        <p><strong>Location:</strong> {{ $request->location }}</p>
                                                        <p><strong>Priority:</strong> 
                                                            <span class="badge bg-{{ $request->priority == 'urgent' ? 'danger' : ($request->priority == 'high' ? 'warning' : 'info') }}">
                                                                {{ ucfirst($request->priority) }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <p><strong>Description:</strong></p>
                                                    <p class="text-muted">{{ $request->description }}</p>
                                                </div>
                                                @if($request->notes)
                                                    <div class="mt-2">
                                                        <p><strong>Notes:</strong></p>
                                                        <p class="text-muted">{{ $request->notes }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <form action="{{ route('events.approve', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <div class="input-group">
                                                        <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                                                        <button type="submit" class="btn btn-success">Approve</button>
                                                    </div>
                                                </form>
                                                <form action="{{ route('events.reject', $request->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <div class="input-group">
                                                        <input type="text" name="notes" class="form-control" placeholder="Reason for rejection">
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <h4 class="text-muted">No pending requests</h4>
                <p>All event requests have been processed.</p>
                <a href="{{ route('events.calendar') }}" class="btn btn-primary">View Approved Events</a>
            </div>
        </div>
    @endif
</div>
@endsection
