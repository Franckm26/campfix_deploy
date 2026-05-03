@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
@endsection

@section('page_title')
<h2><i class="fas fa-trash-alt"></i> Deleted Events</h2>
<p>Events in this folder can be restored or permanently deleted.</p>
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

    <!-- Info Card -->
    <div class="card mb-4 border-warning">
        <div class="card-body bg-warning bg-opacity-10">
            <div class="row align-items-center">
                <div class="col-12">
                    <h5 class="mb-1"><i class="fas fa-info-circle"></i> About Deleted Events</h5>
                    <p class="mb-0 text-muted">Events that have been deleted are moved here. You can restore them to their original state or permanently delete them. Once permanently deleted, events cannot be recovered.</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-warning fs-5">{{ $events->count() }} events</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    @if($events->count() > 0)
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <form id="bulkRestoreForm" method="POST" action="{{ route('admin.deletedEvents.restoreSelected') }}">
                        @csrf
                        <input type="hidden" name="event_ids" id="selectedEventIds">
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
                    <span id="selectedCount" class="text-muted ms-3">0 events selected</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Deleted Events Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="selectAll" onchange="toggleSelectAll()"></th>
                            <th>Event Ticket</th>                            <th>Event Date</th>
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
                        @forelse($events as $event)
                            <tr data-id="{{ $event->id }}">
                                <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="event-checkbox" value="{{ $event->id }}" onchange="updateSelectedCount()"></td>
                                <td>EVT-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</td>
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
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $event->id }}" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form action="{{ route('admin.deletedEvents.restore', $event->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                <i class="fas fa-trash-restore"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.deletedEvents.permanentDelete', $event->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="return confirm('Are you sure you want to permanently delete this event?')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <!-- Permanent Delete Confirmation Modal -->
                            <div class="modal fade" id="permanentDeleteModal{{ $event->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Permanently Delete Event</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to permanently delete <strong>EVT-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</strong>?</p>
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
                                                    <p><strong>Event Ticket:</strong> EVT-{{ str_pad($event->id, 5, '0', STR_PAD_LEFT) }}</p>                                                    <p><strong>Status:</strong>
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
        </div>
    </div>
</div>

<!-- Permanent Delete All Confirmation Modal -->
<div class="modal fade" id="permanentDeleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Permanently Delete All Events</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete all <strong>{{ $events->count() }}</strong> events?</p>
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
    // Context menu variables
    let contextMenu = document.getElementById('contextMenu');
    let currentEventId = null;

    // Toggle select all checkboxes
    function toggleSelectAll() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.event-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        updateSelectedCount();
    }

    // Update selected count
    function updateSelectedCount() {
        const countEl = document.getElementById('selectedCount');
        const eventIdsEl = document.getElementById('selectedEventIds');
        
        // Return early if elements don't exist (no events)
        if (!countEl || !eventIdsEl) {
            return;
        }
        
        const checkboxes = document.querySelectorAll('.event-checkbox:checked');
        const count = checkboxes.length;
        countEl.textContent = count + ' event' + (count !== 1 ? 's' : '') + ' selected';
        
        // Update hidden input for bulk form
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        eventIdsEl.value = JSON.stringify(selectedIds);
    }

    // Bulk restore function
    function bulkRestore() {
        const checkboxes = document.querySelectorAll('.event-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Please select at least one event to restore.');
            return;
        }
        
        if (confirm('Are you sure you want to restore ' + checkboxes.length + ' event(s)?')) {
            document.getElementById('bulkRestoreForm').submit();
        }
    }

    // Context menu functions
    function showContextMenu(e, eventId) {
        e.preventDefault();
        currentEventId = eventId;
        
        contextMenu.style.display = 'block';
        contextMenu.style.left = e.pageX + 'px';
        contextMenu.style.top = e.pageY + 'px';
    }

    function hideContextMenu() {
        contextMenu.style.display = 'none';
    }

    function contextView() {
        hideContextMenu();
        const modalId = 'viewModal' + currentEventId;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    function contextRestore() {
        hideContextMenu();
        if (confirm('Are you sure you want to restore this event?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/deleted-events/' + currentEventId + '/restore';
            
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
        const modalId = 'permanentDeleteModal' + currentEventId;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    // Add right-click listeners to table rows
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('contextmenu', (e) => {
            const eventId = row.getAttribute('data-id');
            if (eventId) {
                showContextMenu(e, eventId);
            }
        });
    });

    // Hide context menu on click outside
    document.addEventListener('click', (e) => {
        if (!contextMenu.contains(e.target)) {
            hideContextMenu();
        }
    });

    // Handle retention days dropdown change (only if element exists) and page load
    document.addEventListener('DOMContentLoaded', function() {
        // Update selected count on page load
        updateSelectedCount();

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
                            window.location.href = '{{ route("admin.deletedEvents") }}?days=' + days;
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
    });
</script>
@endsection








