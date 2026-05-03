@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-archive"></i> My Archive</h2>
@if(request('status'))
<p>Archived {{ request('status') }} Items</p>
@else
<p>View and manage your archived concerns</p>
@endif
@endsection

@section('content')
<div class="container-fluid px-3">
    
    <!-- Context Menu -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li><a href="#" id="ctxView" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" id="ctxEdit" onclick="contextEdit()"><i class="fas fa-edit"></i> Edit</a></li>
            <li><a href="#" id="ctxArchive" onclick="contextArchive()"><i class="fas fa-archive"></i> Archive</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
        </div>
    </div>

    <!-- Status Filter Dropdown -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('user.archive') }}" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ request('status') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="Resolved" {{ request('status') == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Messages -->
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

    <!-- Archived Concerns -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> My Archived Concerns</h5>
            <span class="text-muted">Total: {{ $archivedConcerns->count() }} items</span>
        </div>
        <div class="card-body">
            @if($archivedConcerns->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;">
                        <thead>
                            <tr>
                                <th>Event Ticket</th>                                <th>Priority</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Archived Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivedConcerns as $concern)
                                <tr data-id="{{ $concern->id }}">
                                    <td>CFR-{{ date('Y') }}-{{ str_pad($concern->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ $concern->title }}</td>
                                    <td>{{ $concern->categoryRelation->name ?? 'N/A' }}</td>
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
                                    <td>{{ $concern->location }}</td>
                                    <td>{{ $concern->updated_at->format('M d, Y') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('concerns.restore', $concern->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                <i class="fas fa-trash-restore"></i> Restore
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('concerns.softDelete', $concern->id) }}" class="d-inline"
                                              onsubmit="return confirm('Move this concern to deleted? It can be restored from the deleted folder.')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No archived concerns found.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Archived Event Requests (Only for Faculty) -->
    @if(auth()->user()->role === 'faculty')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-calendar"></i> My Archived Event Requests</h5>
            <span class="text-muted">Total: {{ $archivedEvents->count() }} items</span>
        </div>
        <div class="card-body">
            @if($archivedEvents->count() > 0)
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;">
                        <thead>
                            <tr>
                                <th>Event Ticket</th>                                <th>Event Date</th>
                                <th>Status</th>
                                <th>Location</th>
                                <th>Archived Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivedEvents as $event)
                                <tr data-id="{{ $event->id }}">
                                    <td>EVT-{{ date('Y') }}-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>EVT-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td>{{ ucfirst($event->category) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $event->status == 'Approved' ? 'success' : 
                                            ($event->status == 'Rejected' ? 'danger' : 
                                            ($event->status == 'Cancelled' ? 'secondary' : 'warning'))
                                        }}>
                                            {{ $event->status }}
                                        </span>
                                    </td>
                                    <td>{{ $event->location }}</td>
                                    <td>{{ $event->updated_at->format('M d, Y') }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('events.restore', $event->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                <i class="fas fa-trash-restore"></i> Restore
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('events.delete', $event->id) }}" class="d-inline"
                                              onsubmit="return confirm('Move this event to deleted? It can be restored from the deleted folder.')">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No archived event requests found.</p>
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
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
}
.context-menu ul li a:hover {
    background: #f5f5f5;
}
.context-menu ul li a i {
    margin-right: 8px;
    width: 20px;
}
</style>

<script>
// Global variable for selected concern ID - defined outside DOMContentLoaded
window.selectedConcernId = null;

document.addEventListener('DOMContentLoaded', function() {
// Right-click handler
document.addEventListener('contextmenu', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        e.preventDefault();
        window.selectedConcernId = row.getAttribute('data-id');
        showContextMenu(e.pageX, e.pageY);
    }
});

// Long-press handler for mobile
let longPressTimer;
document.addEventListener('touchstart', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        longPressTimer = setTimeout(function() {
            window.selectedConcernId = row.getAttribute('data-id');
            const touch = e.touches[0];
            showContextMenu(touch.pageX, touch.pageY);
        }, 500);
    }
});

document.addEventListener('touchend', function() {
    clearTimeout(longPressTimer);
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
document.addEventListener('click', function() {
    document.getElementById('contextMenu').style.display = 'none';
});

});

// Context menu actions - defined outside DOMContentLoaded
function contextView() {
    if (window.selectedConcernId) {
        window.location.href = '/concerns/' + window.selectedConcernId;
    }
}

function contextEdit() {
    if (window.selectedConcernId) {
        window.location.href = '/concerns/' + window.selectedConcernId + '/edit';
    }
}

function contextArchive() {
    if (window.selectedConcernId) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/concerns/' + window.selectedConcernId + '/archive';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Function to archive directly from table button
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
</script>





