@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    /* Pending Approvals Task List Styles */
    .approvals-list {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .approval-item {
        padding: 16px;
        border-bottom: 1px solid #e9ecef;
        transition: background 0.2s;
    }
    
    .approval-item:hover {
        background: #f8f9fa;
    }
    
    .approval-item:last-child {
        border-bottom: none;
    }
    
    .approval-title {
        font-size: 15px;
        font-weight: 600;
        color: #212529;
        margin-bottom: 6px;
    }
    
    .approval-meta {
        font-size: 13px;
        color: #6c757d;
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
    }
    
    .approval-actions {
        display: flex;
        gap: 8px;
    }
    
    #pending-chevron {
        transition: transform 0.3s;
    }
    
    #pending-chevron.rotated {
        transform: rotate(180deg);
    }

    /* Calendar Widget Styles */
    .calendar-widget-nav {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #e9ecef;
        background: #f8f9fa;
    }
    
    [data-theme="dark"] .calendar-widget-nav {
        background: #1e1e38 !important;
        border-bottom-color: #2a2a45 !important;
    }
    
    .calendar-widget-nav .nav-btn {
        background: transparent;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.2s;
    }
    
    [data-theme="dark"] .calendar-widget-nav .nav-btn {
        color: #888 !important;
    }
    
    .calendar-widget-nav .nav-btn:hover {
        background: #e9ecef;
        color: #212529;
    }
    
    [data-theme="dark"] .calendar-widget-nav .nav-btn:hover {
        background: #2a2a45 !important;
        color: #e0e0e0 !important;
    }
    
    .calendar-days-row {
        display: flex;
        gap: 8px;
        flex: 1;
        margin: 0 16px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    
    .calendar-days-row::-webkit-scrollbar {
        display: none;
    }
    
    .calendar-day-item {
        flex: 0 0 auto;
        text-align: center;
        padding: 12px 16px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        min-width: 70px;
        background: white;
        border: 2px solid transparent;
    }
    
    [data-theme="dark"] .calendar-day-item {
        background: #1a1a2e !important;
        color: #e0e0e0 !important;
    }
    
    .calendar-day-item:hover {
        background: #f8f9fa;
    }
    
    [data-theme="dark"] .calendar-day-item:hover {
        background: #22223a !important;
    }
    
    .calendar-day-item.active {
        background: #6f42c1;
        color: white;
        border-color: #6f42c1;
    }
    
    .calendar-day-item.has-event {
        border-color: #28a745;
    }
    
    .calendar-day-item.active.has-event {
        background: #6f42c1;
        border-color: #6f42c1;
    }
    
    .day-name {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: #6c757d;
        margin-bottom: 4px;
    }
    
    [data-theme="dark"] .day-name {
        color: #888 !important;
    }
    
    .calendar-day-item.active .day-name {
        color: rgba(255,255,255,0.8);
    }
    
    .day-number {
        font-size: 20px;
        font-weight: 700;
        color: #212529;
    }
    
    [data-theme="dark"] .day-number {
        color: #e0e0e0 !important;
    }
    
    .calendar-day-item.active .day-number {
        color: white;
    }
    
    .day-event-dot {
        width: 6px;
        height: 6px;
        background: #28a745;
        border-radius: 50%;
        margin: 4px auto 0;
    }
    
    .calendar-day-item.active .day-event-dot {
        background: white;
    }
    
    /* Events List */
    .events-list-widget {
        padding: 20px;
    }
    
    .event-card {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 12px;
        transition: all 0.2s;
        border-left: 4px solid #28a745;
    }
    
    .event-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .event-card:last-child {
        margin-bottom: 0;
    }
    
    .event-header {
        display: flex;
        justify-content: between;
        align-items: start;
        margin-bottom: 8px;
    }
    
    .event-title {
        font-size: 16px;
        font-weight: 600;
        color: #212529;
        margin-bottom: 4px;
    }
    
    .event-time {
        font-size: 13px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
    }
    
    .event-location {
        font-size: 13px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    
    .event-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: #e3f2fd;
        color: #1976d2;
        margin-top: 8px;
    }
    
    .no-events-message {
        text-align: center;
        padding: 40px 20px;
        color: #6c757d;
    }
</style>
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

    <!-- Pending Approvals -->
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div style="cursor: pointer;" onclick="togglePendingApprovals()">
                        <i class="fas fa-tasks text-warning me-2"></i>
                        <span class="fw-bold">Pending Event Approvals</span>
                        <span class="badge bg-warning text-dark ms-2">{{ $pendingEventsList->count() }} requests</span>
                        <i class="fas fa-chevron-down ms-2" id="pending-chevron"></i>
                    </div>
                    <a href="{{ route('events.pending') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-check-circle"></i> Review Now
                    </a>
                </div>
                <div class="card-body" id="pending-approvals-body" style="display: none;">
                    @if($pendingEventsList->count() > 0)
                        <div class="approvals-list">
                            @foreach($pendingEventsList as $event)
                            <div class="approval-item">
                                <div class="d-flex align-items-start">
                                    <input type="checkbox" class="form-check-input me-3 mt-1" disabled>
                                    <div class="flex-grow-1">
                                        <div class="approval-title">{{ $event->title }}</div>
                                        <div class="approval-meta">
                                            <span class="me-3"><i class="fas fa-user text-muted me-1"></i>{{ $event->user->name ?? 'Unknown' }}</span>
                                            <span class="me-3"><i class="fas fa-map-marker-alt text-muted me-1"></i>{{ $event->location }}</span>
                                            <span><i class="fas fa-calendar text-muted me-1"></i>{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="text-end ms-3">
                                        @php
                                            $daysUntil = \Carbon\Carbon::parse($event->event_date)->diffInDays(now(), false);
                                            $isUrgent = $daysUntil <= 3 && $daysUntil >= 0;
                                        @endphp
                                        @if($daysUntil < 0)
                                            <span class="badge bg-danger">{{ abs($daysUntil) }} days overdue</span>
                                        @elseif($isUrgent)
                                            <span class="badge bg-warning text-dark">{{ $daysUntil }} days left</span>
                                        @else
                                            <span class="text-muted small">{{ \Carbon\Carbon::parse($event->event_date)->format('M d') }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="approval-actions mt-2">
                                    <a href="{{ route('events.pending') }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> Review
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-center mt-3 pt-3 border-top">
                            <a href="{{ route('events.pending') }}" class="btn btn-warning">
                                <i class="fas fa-check-circle"></i> Review All Requests
                            </a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h6 class="text-muted">All caught up!</h6>
                            <p class="text-muted small mb-0">No pending approvals at the moment.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Widget -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center py-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar text-primary me-2"></i>
                        <h5 class="mb-0">Calendar</h5>
                        <span class="ms-2 text-muted" id="current-month-label"></span>
                    </div>
                    <div>
                        @if(in_array($user->role, ['school_admin', 'academic_head', 'program_head', 'principal_assistant']))
                        <button type="button" class="btn btn-sm btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                        @endif
                        <a href="{{ route('events.calendar') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-expand"></i> Full Calendar
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($upcomingEventsList->count() > 0)
                        <!-- Calendar Navigation -->
                        <div class="calendar-widget-nav">
                            <button class="nav-btn" onclick="changeMonth(-1)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <div class="calendar-days-row" id="calendar-days-row"></div>
                            <button class="nav-btn" onclick="changeMonth(1)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <!-- Events List -->
                        <div class="events-list-widget" id="events-list-widget"></div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Upcoming Events</h5>
                            <p class="text-muted mb-3">There are no approved events scheduled.</p>
                            @if(in_array($user->role, ['school_admin', 'academic_head', 'program_head', 'principal_assistant']))
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                                    <i class="fas fa-plus"></i> Submit Event Request
                                </button>
                            @else
                                <a href="{{ route('events.calendar') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-calendar"></i> View Events Calendar
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
@if(in_array($user->role, ['school_admin', 'academic_head', 'program_head', 'principal_assistant']))
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
@endif
@endsection

@section('scripts')
<script>
    // Toggle pending approvals section
    function togglePendingApprovals() {
        const body = document.getElementById('pending-approvals-body');
        const chevron = document.getElementById('pending-chevron');
        
        if (body.style.display === 'none') {
            body.style.display = 'block';
            chevron.classList.add('rotated');
        } else {
            body.style.display = 'none';
            chevron.classList.remove('rotated');
        }
    }

    // Events data from backend
    const eventsData = @json($upcomingEventsList ?? []);
    
    let currentDate = new Date();
    let selectedDate = new Date();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    const dayNamesShort = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        // Update month label
        document.getElementById('current-month-label').textContent = monthNames[month];
        
        // Get first and last day of the month
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        
        // Render days row
        let daysHtml = '';
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dateStr = formatDateStr(date);
            const dayName = dayNamesShort[date.getDay()];
            const isToday = isSameDay(date, new Date());
            const isSelected = isSameDay(date, selectedDate);
            const hasEvent = eventsData.some(e => e.event_date === dateStr);
            
            daysHtml += `
                <div class="calendar-day-item ${isSelected ? 'active' : ''} ${hasEvent ? 'has-event' : ''}" 
                     onclick="selectDate('${dateStr}')">
                    <div class="day-name">${dayName}</div>
                    <div class="day-number">${day}</div>
                    ${hasEvent ? '<div class="day-event-dot"></div>' : ''}
                </div>
            `;
        }
        
        document.getElementById('calendar-days-row').innerHTML = daysHtml;
        
        // Render events for selected date
        renderEvents();
    }

    function renderEvents() {
        const dateStr = formatDateStr(selectedDate);
        const dayEvents = eventsData.filter(e => e.event_date === dateStr);
        
        const container = document.getElementById('events-list-widget');
        
        if (dayEvents.length === 0) {
            const dateDisplay = selectedDate && !isNaN(selectedDate.getTime()) 
                ? formatDateDisplay(selectedDate) 
                : 'this date';
            container.innerHTML = `
                <div class="no-events-message">
                    <i class="fas fa-calendar-day fa-2x mb-3"></i>
                    <p>No events scheduled for ${dateDisplay}</p>
                </div>
            `;
            return;
        }
        
        let eventsHtml = '';
        dayEvents.forEach(event => {
            const startTime = formatTime(event.start_time);
            const endTime = formatTime(event.end_time);
            
            eventsHtml += `
                <div class="event-card">
                    <div class="event-header">
                        <div>
                            <div class="event-title">${event.title}</div>
                            <div class="event-time">
                                <i class="fas fa-clock"></i>
                                <span>${startTime} - ${endTime}</span>
                            </div>
                            <div class="event-location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>${event.location || 'TBA'}</span>
                            </div>
                            ${event.department ? `<span class="event-badge">${event.department}</span>` : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = eventsHtml;
    }

    function changeMonth(delta) {
        currentDate.setMonth(currentDate.getMonth() + delta);
        // Also move selected date to first day of new month
        selectedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
        renderCalendar();
    }

    function selectDate(dateStr) {
        const [year, month, day] = dateStr.split('-').map(Number);
        selectedDate = new Date(year, month - 1, day);
        renderCalendar();
    }

    function formatDateStr(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatDateDisplay(date) {
        return date.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    }

    function formatTime(timeStr) {
        if (!timeStr) return '';
        const [hours, minutes] = timeStr.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const displayHour = hour % 12 || 12;
        return `${displayHour}:${minutes} ${ampm}`;
    }

    function isSameDay(date1, date2) {
        return date1.getDate() === date2.getDate() &&
               date1.getMonth() === date2.getMonth() &&
               date1.getFullYear() === date2.getFullYear();
    }

    // Initialize calendar on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('calendar-days-row')) {
            // Always set selected date to today
            const today = new Date();
            selectedDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
            currentDate = new Date(today.getFullYear(), today.getMonth(), 1);
            
            renderCalendar();
        }
    });
</script>
@endsection
