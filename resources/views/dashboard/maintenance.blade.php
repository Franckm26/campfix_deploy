@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
.dashboard-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    padding: 20px;
}

.dashboard-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 20px;
}

/* Dark mode styles */
[data-theme="dark"] .dashboard-card {
    background: #1a1a2e !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    color: #e0e0e0 !important;
}

.card-header-custom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.card-title {
    font-size: 18px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

[data-theme="dark"] .card-title {
    color: #e2e8f0 !important;
}

.task-section {
    margin-bottom: 20px;
}

.task-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    color: #666;
    user-select: none;
}

[data-theme="dark"] .task-section-header {
    color: #94a3b8 !important;
}

.task-section-header i {
    transition: transform 0.2s;
}

#inProgress-content,
#toDo-content,
#completed-content {
    transition: all 0.3s ease;
}

.task-badge {
    background: #e0e0e0;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    color: #333;
}

[data-theme="dark"] .task-badge {
    background: #334155 !important;
    color: #cbd5e1 !important;
}

.task-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s;
}

.task-item:hover {
    background: #f5f5f5;
}

[data-theme="dark"] .task-item:hover {
    background: #334155 !important;
}

.task-checkbox {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    flex-shrink: 0;
    margin-top: 2px;
}

[data-theme="dark"] .task-checkbox {
    border-color: #475569 !important;
}

.task-content {
    flex: 1;
}

.task-title {
    font-size: 14px;
    margin-bottom: 4px;
}

[data-theme="dark"] .task-title {
    color: #e2e8f0 !important;
}

.task-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #666;
}

[data-theme="dark"] .task-meta {
    color: #94a3b8 !important;
}

.priority-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
}

.priority-critical {
    background: #fee;
    color: #c00;
}

[data-theme="dark"] .priority-critical {
    background: #7f1d1d !important;
    color: #fca5a5 !important;
}

.priority-high {
    background: #fff3cd;
    color: #856404;
}

[data-theme="dark"] .priority-high {
    background: #78350f !important;
    color: #fcd34d !important;
}

.priority-medium {
    background: #e7f3ff;
    color: #004085;
}

[data-theme="dark"] .priority-medium {
    background: #1e3a8a !important;
    color: #93c5fd !important;
}

.priority-low {
    background: #e7f3ff;
    color: #004085;
}

[data-theme="dark"] .priority-low {
    background: #1e3a8a !important;
    color: #93c5fd !important;
}

.calendar-mini {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 8px;
    margin-top: 16px;
}

.calendar-day-header {
    text-align: center;
    font-size: 12px;
    font-weight: 600;
    color: #666;
    padding: 8px 0;
}

[data-theme="dark"] .calendar-day-header {
    color: #94a3b8 !important;
}

.calendar-day {
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: background 0.2s;
    color: inherit;
}

.calendar-day:hover {
    background: #f0f0f0;
}

[data-theme="dark"] .calendar-day {
    color: #cbd5e1 !important;
}

[data-theme="dark"] .calendar-day:hover {
    background: #334155 !important;
}

.calendar-day.today {
    background: #6366f1;
    color: white !important;
    font-weight: 600;
}

.calendar-day.has-event {
    background: #e0e7ff;
    color: #4f46e5;
    font-weight: 600;
}

[data-theme="dark"] .calendar-day.has-event {
    background: #312e81 !important;
    color: #a5b4fc !important;
}

.calendar-day.empty {
    cursor: default;
}

.calendar-day.empty:hover {
    background: transparent !important;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.calendar-nav {
    display: flex;
    gap: 8px;
    align-items: center;
}

.calendar-nav-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background 0.2s;
    color: inherit;
}

.calendar-nav-btn:hover {
    background: #f0f0f0;
}

[data-theme="dark"] .calendar-nav-btn {
    color: #cbd5e1 !important;
}

[data-theme="dark"] .calendar-nav-btn:hover {
    background: #334155 !important;
}

[data-theme="dark"] #current-month {
    color: #e2e8f0 !important;
}

.reminder-item {
    padding: 12px;
    border-left: 3px solid #6366f1;
    background: #f9fafb;
    border-radius: 4px;
    margin-bottom: 12px;
}

[data-theme="dark"] .reminder-item {
    background: #1e293b !important;
    border-left-color: #818cf8 !important;
}

.reminder-title {
    font-size: 14px;
    margin-bottom: 4px;
}

[data-theme="dark"] .reminder-title {
    color: #e2e8f0 !important;
}

.reminder-time {
    font-size: 12px;
    color: #666;
}

[data-theme="dark"] .reminder-time {
    color: #94a3b8 !important;
}

.add-task-btn {
    color: #666;
    font-size: 14px;
    padding: 8px;
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.2s;
}

.add-task-btn:hover {
    background: #f5f5f5;
}

[data-theme="dark"] .add-task-btn {
    color: #94a3b8 !important;
}

[data-theme="dark"] .add-task-btn:hover {
    background: #334155 !important;
}

.event-card {
    background: white;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
    border-left: 4px solid #6366f1;
}

[data-theme="dark"] .event-card {
    background: #1e293b !important;
    border-left-color: #818cf8 !important;
}

.event-title {
    font-weight: 600;
    margin-bottom: 8px;
}

[data-theme="dark"] .event-title {
    color: #e2e8f0 !important;
}

.event-time {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

[data-theme="dark"] .event-time {
    color: #94a3b8 !important;
}

.event-platform {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    background: #e0f2fe;
    border-radius: 4px;
    font-size: 12px;
    color: #0369a1;
}

[data-theme="dark"] .event-platform {
    background: #164e63 !important;
    color: #67e8f9 !important;
}

[data-theme="dark"] .text-muted {
    color: #888 !important;
}

[data-theme="dark"] .btn-link {
    color: #94a3b8 !important;
}

@media (max-width: 1200px) {
    .dashboard-container {
        grid-template-columns: 1fr;
    }
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
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
        </div>
    </div>

    <div class="dashboard-container">
        <!-- Left Column: My Tasks -->
        <div class="dashboard-card">
            <div class="card-header-custom">
                <div class="card-title">
                    <i class="fas fa-tasks"></i>
                    My Tasks
                </div>
                <div>
                    <button class="btn btn-sm btn-link"><i class="fas fa-ellipsis-h"></i></button>
                </div>
            </div>

            <!-- In Progress Section -->
            <div class="task-section">
                <div class="task-section-header" onclick="toggleSection('inProgress')" style="cursor: pointer;">
                    <i class="fas fa-chevron-down" id="inProgress-icon"></i>
                    <span>IN PROGRESS</span>
                    <span class="task-badge">{{ $assignedReports->where('status', 'In Progress')->count() }}</span>
                </div>

                <div id="inProgress-content">
                    @forelse($assignedReports->where('status', 'In Progress') as $report)
                    <div class="task-item" onclick="viewReport({{ $report->id }})">
                        <div class="task-checkbox"></div>
                        <div class="task-content">
                            <div class="task-title">{{ $report->title ?? 'Report #' . $report->id }}</div>
                            <div class="task-meta">
                                <span class="priority-badge priority-{{ strtolower($report->severity) }}">
                                    {{ ucfirst($report->severity) }}
                                </span>
                                <span>{{ $report->location }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small">No tasks in progress</p>
                    @endforelse
                </div>
            </div>

            <!-- To Do Section -->
            <div class="task-section">
                <div class="task-section-header" onclick="toggleSection('toDo')" style="cursor: pointer;">
                    <i class="fas fa-chevron-down" id="toDo-icon"></i>
                    <span>TO DO</span>
                    <span class="task-badge">{{ $assignedReports->whereIn('status', ['Pending', 'Assigned'])->count() }}</span>
                </div>

                <div id="toDo-content">
                    @forelse($assignedReports->whereIn('status', ['Pending', 'Assigned'])->take(10) as $report)
                    <div class="task-item" onclick="viewReport({{ $report->id }})">
                        <div class="task-checkbox"></div>
                        <div class="task-content">
                            <div class="task-title">{{ $report->title ?? 'Report #' . $report->id }}</div>
                            <div class="task-meta">
                                <span class="priority-badge priority-{{ strtolower($report->severity) }}">
                                    {{ ucfirst($report->severity) }}
                                </span>
                                <span>{{ $report->location }}</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted small">No pending tasks</p>
                    @endforelse
                </div>
            </div>

            <!-- Completed Section -->
            <div class="task-section">
                <div class="task-section-header" onclick="toggleSection('completed')" style="cursor: pointer;">
                    <i class="fas fa-chevron-right" id="completed-icon"></i>
                    <span>COMPLETED</span>
                    <span class="task-badge">{{ $resolvedCount }}</span>
                </div>

                <div id="completed-content" style="display: none;">
                    <p class="text-muted small">{{ $resolvedCount }} task(s) completed</p>
                </div>
            </div>
        </div>

        <!-- Right Column: Calendar and Reminders -->
        <div>
            <!-- Calendar Card -->
            <div class="dashboard-card mb-3">
                <div class="card-header-custom">
                    <div class="card-title">
                        <i class="fas fa-calendar"></i>
                        Calendar
                    </div>
                    <div class="calendar-nav">
                        <button class="calendar-nav-btn" onclick="changeMonth(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span id="current-month" style="font-weight: 600; min-width: 100px; text-align: center;"></span>
                        <button class="calendar-nav-btn" onclick="changeMonth(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="calendar-mini" id="calendar-grid">
                    <!-- Calendar will be rendered here -->
                </div>

                <!-- Today's Events -->
                <div id="today-events" class="mt-4">
                    <!-- Events will be shown here -->
                </div>
            </div>

            <!-- Reminders Card -->
            <div class="dashboard-card">
                <div class="card-header-custom">
                    <div class="card-title">
                        <i class="fas fa-bell"></i>
                        Reminders
                    </div>
                </div>

                <div class="task-section-header">
                    <i class="fas fa-chevron-down"></i>
                    <span>TODAY</span>
                    <span class="task-badge">{{ $assignedReports->where('status', '!=', 'Resolved')->count() }}</span>
                </div>

                @forelse($assignedReports->where('status', '!=', 'Resolved')->take(3) as $report)
                <div class="reminder-item">
                    <div class="reminder-title">{{ $report->title ?? 'Report #' . $report->id }}</div>
                    <div class="reminder-time">
                        <i class="fas fa-clock"></i> 
                        {{ $report->created_at->format('g:i A') }} - {{ $report->location }}
                    </div>
                </div>
                @empty
                <p class="text-muted small">No reminders for today</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="reportModalContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Event Details Modal -->
<div class="modal fade" id="eventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Events on <span id="modal-date"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modal-events"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentDate = new Date();
let events = [];
let reportModal = null;
let eventModal = null;

document.addEventListener('DOMContentLoaded', function() {
    reportModal = new bootstrap.Modal(document.getElementById('reportModal'));
    eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
    
    // Fetch events
    fetch('/events')
        .then(r => r.json())
        .then(data => {
            events = data;
            renderCalendar();
            showTodayEvents();
        });
    
    renderCalendar();
});

// Toggle section collapse/expand
function toggleSection(sectionId) {
    const content = document.getElementById(sectionId + '-content');
    const icon = document.getElementById(sectionId + '-icon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-right');
        icon.classList.add('fa-chevron-down');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-right');
    }
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    document.getElementById('current-month').textContent = monthNames[month] + ' ' + year;
    
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const today = new Date();
    
    let html = dayNames.map(d => '<div class="calendar-day-header">' + d + '</div>').join('');
    
    // Empty cells before first day
    for (let i = 0; i < firstDay; i++) {
        html += '<div class="calendar-day empty"></div>';
    }
    
    // Days of month
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
        const dayEvents = events.filter(e => e.start && e.start.startsWith(dateStr));
        const hasEvent = dayEvents.length > 0;
        const isToday = today.getDate() === day && today.getMonth() === month && today.getFullYear() === year;
        
        let classes = 'calendar-day';
        if (isToday) classes += ' today';
        else if (hasEvent) classes += ' has-event';
        
        html += '<div class="' + classes + '" ' + (hasEvent ? 'onclick="showDayEvents(\'' + dateStr + '\')"' : '') + '>' + day + '</div>';
    }
    
    document.getElementById('calendar-grid').innerHTML = html;
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
}

function showTodayEvents() {
    const today = new Date();
    const dateStr = today.getFullYear() + '-' + 
                   String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                   String(today.getDate()).padStart(2, '0');
    
    const todayEvents = events.filter(e => e.start && e.start.startsWith(dateStr));
    
    if (todayEvents.length > 0) {
        let html = '<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e7eb;">';
        html += '<div style="font-weight: 600; margin-bottom: 12px;">Today\'s Events</div>';
        
        todayEvents.forEach(event => {
            html += '<div class="event-card">';
            html += '<div class="event-title">' + event.title + '</div>';
            html += '<div class="event-time">Today • 10:00 - 11:00 am</div>';
            html += '<div class="event-platform"><i class="fas fa-video"></i> Google Meet</div>';
            html += '</div>';
        });
        
        html += '</div>';
        document.getElementById('today-events').innerHTML = html;
    }
}

function showDayEvents(dateStr) {
    const dayEvents = events.filter(e => e.start && e.start.startsWith(dateStr));
    if (dayEvents.length > 0) {
        document.getElementById('modal-date').textContent = dateStr;
        document.getElementById('modal-events').innerHTML = dayEvents.map(e => 
            '<div class="mb-2"><i class="fas fa-calendar-check text-success me-1"></i>' + e.title + '</div>'
        ).join('');
        eventModal.show();
    }
}

function viewReport(id) {
    fetch('{{ route("reports.show", ":id") }}'.replace(':id', id), {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => response.text())
    .then(data => {
        document.getElementById('reportModalContent').innerHTML = data;
        reportModal.show();

        // Attach form handler
        setTimeout(() => {
            const form = document.getElementById('updateStatusForm');
            if (form) {
                form.addEventListener('submit', handleStatusUpdate);
            }
        }, 100);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error loading report details');
    });
}

function handleStatusUpdate(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const reportId = formData.get('id');

    fetch('/update-report-status/' + reportId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            reportModal.hide();
            location.reload();
        } else {
            alert(data.error || 'Failed to update status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating status');
    });
}
</script>
@endsection
