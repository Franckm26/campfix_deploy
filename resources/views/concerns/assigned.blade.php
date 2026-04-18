@extends('layouts.app')

@section('title', 'Assigned Concerns')

@section('page_title')
<h2><i class="fas fa-tasks"></i> Assigned Concerns</h2>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div></div>
                <div>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-home"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a class="nav-link {{ $viewType === 'active' ? 'active' : '' }}" href="{{ route('concerns.assigned', ['view' => 'active']) }}">
                        <i class="fas fa-list"></i> Active
                        <span class="badge bg-primary">{{ $activeCount }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $viewType === 'archives' ? 'active' : '' }}" href="{{ route('concerns.assigned', ['view' => 'archives']) }}">
                        <i class="fas fa-archive"></i> Archives
                        <span class="badge bg-secondary">{{ $archiveCount }}</span>
                    </a>
                </li>
            </ul>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('concerns.assigned') }}" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="{{ $viewType }}">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Assigned" {{ request('status') === 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                <option value="In Progress" {{ request('status') === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Resolved" {{ request('status') === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">All Priority</option>
                                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                                <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('concerns.assigned', ['view' => $viewType]) }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Concerns Table -->
            <div class="card">
                <div class="card-body">
                    @if($concerns->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle text-success" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">No Assigned Concerns</h4>
                            <p class="text-muted">You don't have any concerns assigned to you yet.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>ID</th>
                                        <th>Location</th>
                                        <th>Category</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Assigned Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($concerns as $concern)
                                        <tr>
                                            <td>
                                                <strong>{{ $concern->title ?? 'No Title' }}</strong>
                                                @if($concern->description)
                                                    <br><small class="text-muted">{{ strlen($concern->description) > 50 ? substr($concern->description, 0, 50) . '...' : $concern->description }}</small>
                                                @endif
                                            </td>
                                            <td>#{{ $concern->id }}</td>
                                            <td>{{ $concern->location }}</td>
                                            <td>{{ optional($concern->categoryRelation)->name ?? 'N/A' }}</td>
                                            <td>
                                                @switch($concern->priority)
                                                    @case('low')
                                                        <span class="badge bg-success">Low</span>
                                                        @break
                                                    @case('medium')
                                                        <span class="badge bg-warning text-dark">Medium</span>
                                                        @break
                                                    @case('high')
                                                        <span class="badge bg-danger">High</span>
                                                        @break
                                                    @case('urgent')
                                                        <span class="badge bg-danger">Urgent</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $concern->priority }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @switch($concern->status)
                                                    @case('Pending')
                                                        <span class="badge bg-warning text-dark">{{ $concern->status }}</span>
                                                        @break
                                                    @case('Assigned')
                                                        <span class="badge bg-info">{{ $concern->status }}</span>
                                                        @break
                                                    @case('In Progress')
                                                        <span class="badge bg-primary">{{ $concern->status }}</span>
                                                        @break
                                                    @case('Resolved')
                                                        <span class="badge bg-success">{{ $concern->status }}</span>
                                                        @break
                                                    @case('Closed')
                                                        <span class="badge bg-secondary">{{ $concern->status }}</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $concern->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $concern->assigned_at ? \Carbon\Carbon::parse($concern->assigned_at)->format('M d, Y h:i A') : 'N/A' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $concern->id }}">
                                                    <i class="fas fa-eye"></i> View
                                                </button>

                                                @if($concern->status === 'Assigned')
                                                    <form action="{{ route('concerns.acknowledge', $concern->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-check"></i> Acknowledge
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal{{ $concern->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Concern #{{ $concern->id }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Title:</strong> {{ $concern->title ?? 'No Title' }}</p>
                                                                <p><strong>Location:</strong> {{ $concern->location }}</p>
                                                                <p><strong>Category:</strong> {{ optional($concern->categoryRelation)->name ?? 'N/A' }}</p>
                                                                <p><strong>Priority:</strong> 
                                                                    @switch($concern->priority)
                                                                        @case('low') <span class="badge bg-success">Low</span> @break
                                                                        @case('medium') <span class="badge bg-warning text-dark">Medium</span> @break
                                                                        @case('high') <span class="badge bg-danger">High</span> @break
                                                                        @case('urgent') <span class="badge bg-danger">Urgent</span> @break
                                                                    @endswitch
                                                                </p>
                                                                <p><strong>Status:</strong> {{ $concern->status }}</p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Reported By:</strong> {{ optional($concern->user)->name ?? 'Unknown' }}</p>
                                                                <p><strong>Assigned Date:</strong> {{ $concern->assigned_at ? \Carbon\Carbon::parse($concern->assigned_at)->format('M d, Y h:i A') : 'N/A' }}</p>
                                                                <p><strong>Created Date:</strong> {{ $concern->created_at ? \Carbon\Carbon::parse($concern->created_at)->format('M d, Y h:i A') : 'N/A' }}</p>
                                                                @if($concern->resolved_at)
                                                                    <p><strong>Resolved Date:</strong> {{ \Carbon\Carbon::parse($concern->resolved_at)->format('M d, Y h:i A') }}</p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                        <div class="mt-3">
                                                            <strong>Description:</strong>
                                                            <p>{{ $concern->description }}</p>
                                                        </div>
                                                        @if($concern->image_path)
                                                            <div class="mt-3">
                                                                <strong>Image:</strong><br>
                                                                <img src="{{ asset('storage/' . $concern->image_path) }}" alt="Concern Image" class="img-fluid" style="max-width: 300px;">
                                                            </div>
                                                        @endif
                                                        @if($concern->resolution_notes)
                                                            <div class="mt-3">
                                                                <strong>Resolution Notes:</strong>
                                                                <p>{{ $concern->resolution_notes }}</p>
                                                            </div>
                                                        @endif
                                                        @if($concern->cost || $concern->damaged_part || $concern->replaced_part)
                                                            <div class="mt-3">
                                                                <strong>Maintenance Details:</strong>
                                                                <div class="row">
                                                                    @if($concern->cost)
                                                                        <div class="col-md-4">
                                                                            <strong>Cost:</strong> ₱{{ number_format($concern->cost, 2) }}
                                                                        </div>
                                                                    @endif
                                                                    @if($concern->damaged_part)
                                                                        <div class="col-md-4">
                                                                            <strong>Damaged Part:</strong> {{ $concern->damaged_part }}
                                                                        </div>
                                                                    @endif
                                                                    @if($concern->replaced_part)
                                                                        <div class="col-md-4">
                                                                            <strong>Replaced With:</strong> {{ $concern->replaced_part }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        @if($concern->status === 'Assigned')
                                                            <form action="{{ route('concerns.acknowledge', $concern->id) }}" method="POST">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success">
                                                                    <i class="fas fa-check"></i> Acknowledge & Start Work
                                                                </button>
                                                            </form>
                                                        @elseif(in_array($concern->status, ['In Progress', 'Assigned']))
                                                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#resolveModal{{ $concern->id }}">
                                                                <i class="fas fa-check-circle"></i> Mark as Completed
                                                            </button>
                                                        @endif
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Resolve Modal -->
                                        @if(in_array($concern->status, ['In Progress', 'Assigned']))
                                        <div class="modal fade" id="resolveModal{{ $concern->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Mark Concern as Completed</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.updateStatus', $concern->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="Resolved">
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="resolution_notes{{ $concern->id }}" class="form-label">Resolution Notes</label>
                                                                        <textarea class="form-control" id="resolution_notes{{ $concern->id }}" name="resolution_notes" rows="3" placeholder="Describe what was done to fix the issue..."></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="cost{{ $concern->id }}" class="form-label">Cost (PHP)</label>
                                                                        <input type="number" class="form-control" id="cost{{ $concern->id }}" name="cost" step="0.01" min="0" placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="damaged_part{{ $concern->id }}" class="form-label">Damaged Part</label>
                                                                        <input type="text" class="form-control" id="damaged_part{{ $concern->id }}" name="damaged_part" placeholder="What part was damaged?">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label for="replaced_part{{ $concern->id }}" class="form-label">Replaced With</label>
                                                                        <input type="text" class="form-control" id="replaced_part{{ $concern->id }}" name="replaced_part" placeholder="What was it replaced with?">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-check-circle"></i> Mark as Completed
                                                            </button>
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection