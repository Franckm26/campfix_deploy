@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    /* ── Theme Variables ── */
    :root {
        --cal-bg:           #ffffff;
        --cal-bg-secondary: #f8f9fa;
        --cal-border:       #dee2e6;
        --cal-text:         #212529;
        --cal-text-muted:   #6c757d;
        --cal-text-header:  #495057;
        --cal-cell-hover:   #f1f3f5;
        --cal-btn-bg:       #e9ecef;
        --cal-btn-hover:    #dee2e6;
        --cal-btn-color:    #495057;
        --cal-today-bg:     #4f6ef7;
        --cal-today-color:  #ffffff;
        --cal-other-month:  #adb5bd;
        --cal-event-purple-bg:    #ede7f6; --cal-event-purple-text: #5e35b1; --cal-event-purple-border: #7c4dff;
        --cal-event-blue-bg:      #e3f2fd; --cal-event-blue-text:   #1565c0; --cal-event-blue-border:   #2196f3;
        --cal-event-green-bg:     #e0f2f1; --cal-event-green-text:  #00695c; --cal-event-green-border:  #26a69a;
        --cal-mini-has-event:     #7c4dff;
        --cal-mini-day-hover:     #e9ecef;
        --cal-list-border:        #dee2e6;
        --cal-list-text:          #495057;
        --cal-agenda-border:      #dee2e6;
        --cal-agenda-text:        #212529;
        --cal-agenda-date:        #6c757d;
    }

    [data-theme="dark"] {
        --cal-bg:           #1a1a2e;
        --cal-bg-secondary: #2a2a45;
        --cal-border:       #2a2a45;
        --cal-text:         #e0e0e0;
        --cal-text-muted:   #888888;
        --cal-text-header:  #888888;
        --cal-cell-hover:   #22223a;
        --cal-btn-bg:       #2a2a45;
        --cal-btn-hover:    #3a3a60;
        --cal-btn-color:    #cccccc;
        --cal-today-bg:     #4f6ef7;
        --cal-today-color:  #ffffff;
        --cal-other-month:  #444444;
        --cal-event-purple-bg:    #3b2f6e; --cal-event-purple-text: #b39ddb; --cal-event-purple-border: #7c4dff;
        --cal-event-blue-bg:      #1a3a5c; --cal-event-blue-text:   #90caf9; --cal-event-blue-border:   #2196f3;
        --cal-event-green-bg:     #1b3a2f; --cal-event-green-text:  #80cbc4; --cal-event-green-border:  #26a69a;
        --cal-mini-has-event:     #7c4dff;
        --cal-mini-day-hover:     #2a2a45;
        --cal-list-border:        #2a2a45;
        --cal-list-text:          #bbbbbb;
        --cal-agenda-border:      #2a2a45;
        --cal-agenda-text:        #dddddd;
        --cal-agenda-date:        #888888;
    }

    .home-calendar-wrap {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    /* ── Main Calendar ── */
    .main-calendar {
        flex: 1;
        background: var(--cal-bg);
        border-radius: 14px;
        overflow: hidden;
        color: var(--cal-text);
        border: 1px solid var(--cal-border);
    }

    .cal-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        border-bottom: 1px solid var(--cal-border);
        flex-wrap: wrap;
        gap: 10px;
    }

    .cal-nav { display: flex; align-items: center; gap: 8px; }

    .cal-nav-btn {
        background: var(--cal-btn-bg);
        border: none;
        color: var(--cal-btn-color);
        width: 32px; height: 32px;
        border-radius: 8px;
        cursor: pointer; font-size: 13px;
        display: flex; align-items: center; justify-content: center;
        transition: background 0.2s;
    }
    .cal-nav-btn:hover { background: var(--cal-btn-hover); }

    .cal-today-btn {
        background: var(--cal-btn-bg);
        border: none;
        color: var(--cal-btn-color);
        padding: 6px 14px; border-radius: 8px;
        cursor: pointer; font-size: 13px; font-weight: 600;
        transition: background 0.2s;
    }
    .cal-today-btn:hover { background: var(--cal-btn-hover); }

    .cal-view-tabs {
        display: flex; gap: 4px;
        background: var(--cal-btn-bg);
        border-radius: 10px; padding: 4px;
    }

    .cal-view-tab {
        padding: 6px 14px; border-radius: 7px;
        border: none; background: transparent;
        color: var(--cal-text-muted);
        font-size: 13px; font-weight: 600;
        cursor: pointer; transition: all 0.2s;
        display: flex; align-items: center; gap: 5px;
    }
    .cal-view-tab.active { background: var(--cal-bg-secondary); color: var(--cal-text); }

    #main-cal-label { color: var(--cal-text); }

    /* Grid */
    .cal-grid-header {
        display: grid; grid-template-columns: repeat(7, 1fr);
        border-bottom: 1px solid var(--cal-border);
    }
    .cal-grid-header div {
        text-align: center; padding: 10px 0;
        font-size: 12px; font-weight: 700;
        color: var(--cal-text-header);
        text-transform: uppercase; letter-spacing: 0.5px;
    }

    .cal-grid-body { display: grid; grid-template-columns: repeat(7, 1fr); }

    .cal-cell {
        min-height: 90px;
        border-right: 1px solid var(--cal-border);
        border-bottom: 1px solid var(--cal-border);
        padding: 6px; position: relative;
        cursor: pointer; transition: background 0.15s;
    }
    .cal-cell:hover { background: var(--cal-cell-hover); }
    .cal-cell:nth-child(7n) { border-right: none; }

    .cal-day-num {
        font-size: 13px; font-weight: 600;
        color: var(--cal-text-muted);
        margin-bottom: 4px;
        width: 26px; height: 26px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%;
    }
    .cal-cell.today .cal-day-num { background: var(--cal-today-bg); color: var(--cal-today-color); }
    .cal-cell.other-month .cal-day-num { color: var(--cal-other-month); }

    .cal-event {
        font-size: 11px; padding: 3px 6px; border-radius: 5px;
        margin-bottom: 3px; white-space: nowrap;
        overflow: hidden; text-overflow: ellipsis;
        font-weight: 600; cursor: pointer;
    }
    .cal-event.purple { background: var(--cal-event-purple-bg); color: var(--cal-event-purple-text); border-left: 3px solid var(--cal-event-purple-border); }
    .cal-event.blue   { background: var(--cal-event-blue-bg);   color: var(--cal-event-blue-text);   border-left: 3px solid var(--cal-event-blue-border); }
    .cal-event.green  { background: var(--cal-event-green-bg);  color: var(--cal-event-green-text);  border-left: 3px solid var(--cal-event-green-border); }

    /* ── Right Panel ── */
    .cal-right-panel { width: 240px; flex-shrink: 0; display: flex; flex-direction: column; gap: 16px; }

    .mini-cal-card, .cal-list-card {
        background: var(--cal-bg);
        border: 1px solid var(--cal-border);
        border-radius: 14px; padding: 16px;
        color: var(--cal-text);
    }
    .mini-cal-card h6, .cal-list-card h6 {
        font-size: 14px; font-weight: 700;
        margin-bottom: 12px;
        display: flex; align-items: center; gap: 6px;
        color: var(--cal-text);
    }

    .mini-cal-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
    .mini-cal-nav span { font-size: 13px; font-weight: 700; color: var(--cal-text); }
    .mini-cal-nav button { background: none; border: none; color: var(--cal-text-muted); cursor: pointer; font-size: 13px; padding: 2px 6px; }

    .mini-cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; text-align: center; }
    .mini-cal-grid .day-label { font-size: 10px; color: var(--cal-text-muted); font-weight: 700; padding: 2px 0; }
    .mini-cal-grid .mini-day { font-size: 11px; padding: 4px 2px; border-radius: 50%; cursor: pointer; color: var(--cal-text-muted); transition: background 0.15s; }
    .mini-cal-grid .mini-day:hover { background: var(--cal-mini-day-hover); }
    .mini-cal-grid .mini-day.today { background: var(--cal-today-bg); color: var(--cal-today-color); font-weight: 700; }
    .mini-cal-grid .mini-day.has-event { color: var(--cal-mini-has-event); font-weight: 700; }
    .mini-cal-grid .mini-day.other { color: var(--cal-other-month); }

    .cal-list-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--cal-list-text); padding: 5px 0; border-bottom: 1px solid var(--cal-list-border); }
    .cal-list-item:last-child { border-bottom: none; }
    .cal-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

    /* ── Week View ── */
    .week-header-grid { display: grid; grid-template-columns: 56px repeat(7, 1fr); border-bottom: 1px solid var(--cal-border); }
    .week-header-spacer { border-right: 1px solid var(--cal-border); }
    .week-header-day { text-align: center; padding: 10px 4px 8px; border-right: 1px solid var(--cal-border); }
    .week-header-day .wh-name { font-size: 11px; font-weight: 700; color: var(--cal-text-header); text-transform: uppercase; letter-spacing: 0.5px; }
    .week-header-day .wh-num { font-size: 20px; font-weight: 700; color: var(--cal-text); width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 4px auto 0; }
    .week-header-day.today .wh-num { background: var(--cal-today-bg); color: var(--cal-today-color); }

    .week-allday-row { display: flex; border-bottom: 2px solid var(--cal-border); min-height: 36px; }
    .week-time-label { width: 56px; flex-shrink: 0; text-align: right; padding-right: 10px; font-size: 11px; color: var(--cal-text-muted); font-weight: 600; padding-top: 6px; }
    .week-allday-cells { flex: 1; display: grid; grid-template-columns: repeat(7, 1fr); }
    .week-allday-cell { border-right: 1px solid var(--cal-border); min-height: 36px; cursor: pointer; transition: background 0.15s; }
    .week-allday-cell:hover { background: var(--cal-cell-hover); }
    .week-allday-cell:last-child { border-right: none; }

    .week-time-grid { overflow-y: auto; max-height: 480px; }
    .week-time-row { display: flex; border-bottom: 1px solid var(--cal-border); min-height: 52px; }
    .week-time-row:last-child { border-bottom: none; }
    .week-row-cells { flex: 1; display: grid; grid-template-columns: repeat(7, 1fr); }
    .week-cell { border-right: 1px solid var(--cal-border); min-height: 52px; cursor: pointer; transition: background 0.15s; position: relative; padding: 2px 4px; }
    .week-cell:hover { background: var(--cal-cell-hover); }
    .week-cell:last-child { border-right: none; }
    .week-cell-event { font-size: 11px; padding: 2px 5px; border-radius: 4px; margin-bottom: 2px; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Agenda */
    .agenda-row { display: flex; gap: 14px; padding: 10px 0; border-bottom: 1px solid var(--cal-agenda-border); color: var(--cal-agenda-text); }
    .agenda-date { min-width: 70px; font-size: 12px; color: var(--cal-agenda-date); }

    @media (max-width: 768px) {
        .home-calendar-wrap { flex-direction: column; }
        .cal-right-panel { width: 100%; }
    }
    
    /* Dark Mode Modal Styles */
    [data-theme="dark"] .modal-content {
        background: #1a1a2e !important;
        color: #e0e0e0 !important;
        border-color: #2a2a45 !important;
    }
    
    [data-theme="dark"] .modal-header {
        border-bottom-color: #2a2a45 !important;
    }
    
    [data-theme="dark"] .modal-footer {
        border-top-color: #2a2a45 !important;
        background: #1e1e38 !important;
    }
    
    [data-theme="dark"] .modal-footer.bg-light {
        background: #1e1e38 !important;
    }
    
    [data-theme="dark"] .modal-body {
        background: #1a1a2e !important;
        color: #e0e0e0 !important;
    }
    
    [data-theme="dark"] .modal-body .bg-light {
        background: #1e1e38 !important;
        color: #e0e0e0 !important;
    }
    
    [data-theme="dark"] .modal-body .p-3.bg-light {
        background: #1e1e38 !important;
        border: 1px solid #2a2a45 !important;
    }
    
    [data-theme="dark"] .modal-body .form-label {
        color: #e0e0e0 !important;
    }
    
    [data-theme="dark"] .modal-body .text-muted {
        color: #888 !important;
    }
    
    [data-theme="dark"] .modal-body .badge {
        opacity: 0.9;
    }
    
    [data-theme="dark"] .modal-body .alert-info {
        background: #1e3a5c !important;
        border-color: #2a4a6c !important;
        color: #90caf9 !important;
    }
    
    [data-theme="dark"] .modal-body pre {
        background: #0f0f1a !important;
        color: #e0e0e0 !important;
        border: 1px solid #2a2a45 !important;
        padding: 8px;
        border-radius: 4px;
    }
    
    [data-theme="dark"] #eventTitle {
        color: #5e5ce6 !important;
    }
    
    [data-theme="dark"] #eventType,
    [data-theme="dark"] #eventDate,
    [data-theme="dark"] #eventTime,
    [data-theme="dark"] #eventLocation,
    [data-theme="dark"] #eventRequestedBy,
    [data-theme="dark"] #eventDescription {
        color: #e0e0e0 !important;
    }
</style>
@endsection

@section('page_title')
<h2><i class="fas fa-calendar-check"></i> Facilities Calendar</h2>
<p>Comprehensive view of events, facility bookings, and maintenance activities</p>
@endsection

@section('content')
<div class="container-fluid px-3" style="background: var(--cal-bg); min-height: 100vh;">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                <div>
                </div>
                <div class="d-flex gap-2">
                    @if(auth()->user()->role === 'building_admin')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#importEventsModal">
                        <i class="fas fa-file-import"></i> Import Events
                    </button>
                    @endif
                    @if(auth()->user()->canApproveRequests())
                        <a href="{{ route('events.pending') }}" class="btn btn-warning">
                            <i class="fas fa-tasks"></i> Pending Approvals
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="home-calendar-wrap">
        <!-- Main Calendar -->
        <div class="main-calendar">
            <div class="cal-toolbar">
                <div class="cal-nav">
                    <button class="cal-today-btn" onclick="goToday()">Today</button>
                    <button class="cal-nav-btn" onclick="changeMainMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                    <button class="cal-nav-btn" onclick="changeMainMonth(1)"><i class="fas fa-chevron-right"></i></button>
                    <span id="main-cal-label" style="font-size:16px;font-weight:700;margin-left:6px;"></span>
                </div>
                <div class="cal-view-tabs">
                    <button class="cal-view-tab" onclick="setView('week', this)"><i class="fas fa-calendar-week"></i> Week</button>
                    <button class="cal-view-tab active" onclick="setView('month', this)"><i class="fas fa-calendar-alt"></i> Month</button>
                    <button class="cal-view-tab" onclick="setView('agenda', this)"><i class="fas fa-list"></i> Agenda</button>
                </div>
            </div>

            <!-- Month View -->
            <div id="month-view">
                <div class="cal-grid-header">
                    <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
                </div>
                <div class="cal-grid-body" id="main-cal-body"></div>
            </div>

            <!-- Week View -->
            <div id="week-view" style="display:none;">
                <!-- Day headers -->
                <div class="week-header-grid" id="week-header-grid"></div>
                <!-- All day row -->
                <div class="week-allday-row">
                    <div class="week-time-label" style="font-size:11px;line-height:1.2;">All<br>day</div>
                    <div class="week-allday-cells" id="week-allday-cells"></div>
                </div>
                <!-- Time grid -->
                <div class="week-time-grid" id="week-time-grid"></div>
            </div>

            <!-- Agenda View -->
            <div id="agenda-view" style="display:none; padding: 16px; color: #aaa; min-height: 300px;" id="agenda-list"></div>
        </div>

        <!-- Right Panel -->
        <div class="cal-right-panel">
            <!-- Mini Calendar -->
            <div class="mini-cal-card">
                <h6><i class="fas fa-calendar-alt text-primary"></i> Calendar</h6>
                <div class="mini-cal-nav">
                    <button onclick="changeMiniMonth(-1)"><i class="fas fa-chevron-left"></i></button>
                    <span id="mini-cal-label"></span>
                    <button onclick="changeMiniMonth(1)"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div class="mini-cal-grid" id="mini-cal-grid"></div>
            </div>

            <!-- Event Types -->
            <div class="cal-list-card">
                <h6><i class="fas fa-th-large text-primary"></i> Event Types</h6>
                <div class="cal-list-item"><div class="cal-dot" style="background:#4f6ef7"></div> Events</div>
                <div class="cal-list-item"><div class="cal-dot" style="background:#7c4dff"></div> Facility Bookings</div>
                <div class="cal-list-item"><div class="cal-dot" style="background:#26a69a"></div> Approved Events</div>
                <div class="cal-list-item"><div class="cal-dot" style="background:#ef5350"></div> Maintenance</div>
            </div>

            <!-- Filters -->
            <div class="cal-list-card">
                <h6><i class="fas fa-filter text-primary"></i> Filters</h6>
                <form method="GET" action="{{ route('events.calendar') }}" id="filterForm">
                    <input type="hidden" name="view" value="calendar">
                    <div class="mb-2">
                        <label class="form-label small">Event Types</label>
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="filter_types[]" value="event" id="type_event" {{ in_array('event', $filterTypes ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="type_event">Events</label>
                        </div>
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="filter_types[]" value="facility" id="type_facility" {{ in_array('facility', $filterTypes ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="type_facility">Facility Bookings</label>
                        </div>
                        <div class="form-check form-check-sm">
                            <input class="form-check-input" type="checkbox" name="filter_types[]" value="maintenance" id="type_maintenance" {{ in_array('maintenance', $filterTypes ?? []) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="type_maintenance">Maintenance</label>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="filter_location" class="form-label small">Location</label>
                        <select class="form-select form-select-sm" name="filter_location" id="filter_location">
                            <option value="">All Locations</option>
                            @foreach($allLocations ?? [] as $location)
                                <option value="{{ $location }}" {{ ($filterLocation ?? '') == $location ? 'selected' : '' }}>{{ $location }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-search"></i> Apply Filters
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

    <!-- Import Events Modal -->
    <div class="modal fade" id="importEventsModal" tabindex="-1" aria-labelledby="importEventsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="importEventsModalLabel">
                        <i class="fas fa-file-import me-2"></i>Import Events from CSV
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('events.import') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="csvFile" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" id="csvFile" name="csv_file" accept=".csv" required>
                            <div class="form-text">Upload a CSV file with columns: title, description, event_date, start_time, end_time, location, category</div>
                        </div>
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>CSV Format</h6>
                            <pre class="mb-0" style="font-size: 0.8rem;">title,description,event_date,start_time,end_time,location,category
Meeting Title,Description,2026-03-20,09:00,10:00,Room 101,meeting</pre>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="eventModalLabel">
                        <i class="fas fa-calendar-alt me-2"></i>Event Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h3 id="eventTitle" class="text-primary fw-bold"></h3>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block"><i class="fas fa-tag me-1"></i>Type</small>
                                <span id="eventType" class="badge fs-6"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block"><i class="fas fa-calendar me-1"></i>Date</small>
                                <span id="eventDate" class="fw-bold"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block"><i class="fas fa-clock me-1"></i>Time</small>
                                <span id="eventTime" class="fw-bold"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block"><i class="fas fa-map-marker-alt me-1"></i>Location</small>
                                <span id="eventLocation" class="fw-bold"></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block"><i class="fas fa-user me-1"></i>Requested By</small>
                                <span id="eventRequestedBy" class="fw-bold"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block mb-2"><i class="fas fa-align-left me-1"></i>Description</small>
                            <p id="eventDescription" class="mb-0"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Get calendar events from the backend
    let calEvents = [];
    
    // Fetch events from API
    async function fetchCalendarEvents() {
        try {
            const response = await fetch('{{ route("events.calendar.events") }}');
            const events = await response.json();
            calEvents = events.map(event => ({
                id: event.id,
                title: event.title,
                start: event.start.split('T')[0], // Get date part only
                startTime: event.start.split('T')[1] || '09:00',
                endTime: event.end ? event.end.split('T')[1] : '10:00',
                type: event.extendedProps.type,
                location: event.extendedProps.location,
                description: event.extendedProps.description,
                requestedBy: event.extendedProps.requestedBy,
                backgroundColor: event.backgroundColor
            }));
            
            // Re-render calendar after loading events
            renderMainCalendar();
            renderMiniCalendar();
            if (activeView === 'agenda') renderAgenda();
            if (activeView === 'week') renderWeekView();
        } catch (error) {
            console.error('Error fetching calendar events:', error);
        }
    }

    let mainDate = new Date();
    let miniDate = new Date();
    let activeView = 'month';

    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const dayLabels = ['S','M','T','W','T','F','S'];

    function setView(view, btn) {
        activeView = view;
        document.querySelectorAll('.cal-view-tab').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('month-view').style.display = view === 'month' ? '' : 'none';
        document.getElementById('week-view').style.display = view === 'week' ? '' : 'none';
        document.getElementById('agenda-view').style.display = view === 'agenda' ? '' : 'none';
        if (view === 'agenda') renderAgenda();
        if (view === 'week') renderWeekView();
    }

    function goToday() {
        mainDate = new Date();
        miniDate = new Date();
        renderMainCalendar();
        renderMiniCalendar();
        if (activeView === 'week') renderWeekView();
    }

    function changeMainMonth(delta) {
        if (activeView === 'week') {
            mainDate.setDate(mainDate.getDate() + delta * 7);
            renderWeekView();
        } else {
            mainDate.setMonth(mainDate.getMonth() + delta);
            renderMainCalendar();
        }
    }

    function changeMiniMonth(delta) {
        miniDate.setMonth(miniDate.getMonth() + delta);
        renderMiniCalendar();
    }

    function renderMainCalendar() {
        const year = mainDate.getFullYear();
        const month = mainDate.getMonth();
        const today = new Date();

        document.getElementById('main-cal-label').textContent = monthNames[month] + ' ' + year;
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const prevDays = new Date(year, month, 0).getDate();

        let html = '';
        let totalCells = Math.ceil((firstDay + daysInMonth) / 7) * 7;

        for (let i = 0; i < totalCells; i++) {
            let day, isOther = false, cellYear = year, cellMonth = month;

            if (i < firstDay) {
                day = prevDays - firstDay + i + 1;
                isOther = true;
                cellMonth = month - 1;
                if (cellMonth < 0) { cellMonth = 11; cellYear--; }
            } else if (i >= firstDay + daysInMonth) {
                day = i - firstDay - daysInMonth + 1;
                isOther = true;
                cellMonth = month + 1;
                if (cellMonth > 11) { cellMonth = 0; cellYear++; }
            } else {
                day = i - firstDay + 1;
            }

            const dateStr = cellYear + '-' + String(cellMonth + 1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
            const isToday = !isOther && today.getDate() === day && today.getMonth() === month && today.getFullYear() === year;
            const dayEvents = calEvents.filter(e => e.start === dateStr);

            // Event type colors
            const getEventColor = (type) => {
                switch(type) {
                    case 'event': return 'blue';
                    case 'facility': return 'green';
                    case 'maintenance': return 'purple';
                    default: return 'blue';
                }
            };

            const evHtml = dayEvents.slice(0,3).map(e => {
                const color = getEventColor(e.type);
                const escapedTitle = e.title.replace(/'/g, "\\'");
                const escapedDesc = (e.description || '').replace(/'/g, "\\'");
                return `<div class="cal-event ${color}" title="${escapedTitle}" onclick="showEventDetails('${e.id}', '${escapedTitle}', '${e.type}', '${e.start}', '${e.startTime}', '${e.endTime}', '${e.location || ''}', '${e.requestedBy}', '${escapedDesc}')">${e.title}</div>`;
            }).join('');

            html += `<div class="cal-cell ${isToday ? 'today' : ''} ${isOther ? 'other-month' : ''}">
                        <div class="cal-day-num">${day}</div>
                        ${evHtml}
                     </div>`;
        }

        document.getElementById('main-cal-body').innerHTML = html;
    }

    function renderMiniCalendar() {
        const year = miniDate.getFullYear();
        const month = miniDate.getMonth();
        const today = new Date();
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        document.getElementById('mini-cal-label').textContent = monthNames[month].substring(0,3) + ' ' + year;

        let html = dayLabels.map(d => `<div class="day-label">${d}</div>`).join('');
        for (let i = 0; i < firstDay; i++) html += '<div></div>';

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = year + '-' + String(month+1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
            const isToday = today.getDate() === day && today.getMonth() === month && today.getFullYear() === year;
            const hasEvent = calEvents.some(e => e.start === dateStr);
            html += `<div class="mini-day ${isToday ? 'today' : ''} ${hasEvent ? 'has-event' : ''}" onclick="goToDate('${dateStr}')">${day}</div>`;
        }

        document.getElementById('mini-cal-grid').innerHTML = html;
    }

    function renderWeekView() {
        const today = new Date();
        const dayOfWeek = mainDate.getDay();
        const weekStart = new Date(mainDate);
        weekStart.setDate(mainDate.getDate() - dayOfWeek);

        const dayShortNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

        // Update label
        const weekEnd = new Date(weekStart);
        weekEnd.setDate(weekStart.getDate() + 6);
        document.getElementById('main-cal-label').textContent =
            monthNames[weekStart.getMonth()] + ' ' + weekStart.getDate() + ' – ' +
            (weekStart.getMonth() !== weekEnd.getMonth() ? monthNames[weekEnd.getMonth()] + ' ' : '') +
            weekEnd.getDate() + ', ' + weekEnd.getFullYear();

        // Build header
        let headerHtml = '<div class="week-header-spacer"></div>';
        for (let i = 0; i < 7; i++) {
            const d = new Date(weekStart);
            d.setDate(weekStart.getDate() + i);
            const isToday = d.toDateString() === today.toDateString();
            headerHtml += `<div class="week-header-day ${isToday ? 'today' : ''}">
                <div class="wh-name">${dayShortNames[i]}</div>
                <div class="wh-num">${d.getDate()}</div>
            </div>`;
        }
        document.getElementById('week-header-grid').innerHTML = headerHtml;

        // All-day cells
        let alldayHtml = '';
        for (let i = 0; i < 7; i++) {
            const d = new Date(weekStart);
            d.setDate(weekStart.getDate() + i);
            const dateStr = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
            const dayEvents = calEvents.filter(e => e.start === dateStr);
            const evHtml = dayEvents.slice(0,2).map(e => {
                const color = e.type === 'event' ? 'blue' : e.type === 'facility' ? 'green' : 'purple';
                const escapedTitle = e.title.replace(/'/g, "\\'");
                const escapedDesc = (e.description || '').replace(/'/g, "\\'");
                return `<div class="week-cell-event ${color}" onclick="showEventDetails('${e.id}', '${escapedTitle}', '${e.type}', '${e.start}', '${e.startTime}', '${e.endTime}', '${e.location || ''}', '${e.requestedBy}', '${escapedDesc}')">${e.title}</div>`;
            }).join('');
            alldayHtml += `<div class="week-allday-cell">${evHtml}</div>`;
        }
        document.getElementById('week-allday-cells').innerHTML = alldayHtml;

        // Time rows
        const hours = ['12am','1am','2am','3am','4am','5am','6am','7am','8am','9am','10am','11am',
                       '12pm','1pm','2pm','3pm','4pm','5pm','6pm','7pm','8pm','9pm','10pm','11pm'];
        let timeHtml = '';
        hours.forEach((label, h) => {
            let cellsHtml = '';
            for (let i = 0; i < 7; i++) {
                const d = new Date(weekStart);
                d.setDate(weekStart.getDate() + i);
                const dateStr = d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
                cellsHtml += `<div class="week-cell"></div>`;
            }
            timeHtml += `<div class="week-time-row">
                <div class="week-time-label">${label}</div>
                <div class="week-row-cells">${cellsHtml}</div>
            </div>`;
        });
        document.getElementById('week-time-grid').innerHTML = timeHtml;
    }

    function renderAgenda() {
        const upcoming = calEvents
            .filter(e => e.start >= new Date().toISOString().slice(0,10))
            .sort((a,b) => a.start.localeCompare(b.start))
            .slice(0, 20);

        const el = document.getElementById('agenda-view');
        if (!upcoming.length) {
            el.innerHTML = '<p class="text-center mt-4" style="color:var(--cal-text-muted)">No upcoming events.</p>';
            return;
        }
        el.innerHTML = upcoming.map(e => {
            const escapedTitle = e.title.replace(/'/g, "\\'");
            const escapedDesc = (e.description || '').replace(/'/g, "\\'");
            return `<div class="agenda-row" style="cursor:pointer;" onclick="showEventDetails('${e.id}', '${escapedTitle}', '${e.type}', '${e.start}', '${e.startTime}', '${e.endTime}', '${e.location || ''}', '${e.requestedBy}', '${escapedDesc}')">
                <div class="agenda-date">${e.start}</div>
                <div style="font-size:13px;font-weight:600;">${e.title}</div>
            </div>`;
        }).join('');
    }

    function goToDate(dateStr) {
        const date = new Date(dateStr);
        mainDate = date;
        miniDate = date;
        renderMainCalendar();
        renderMiniCalendar();
    }

    function showEventDetails(id, title, type, date, startTime, endTime, location, requestedBy, description) {
        document.getElementById('eventTitle').textContent = title;

        var typeBadge = document.getElementById('eventType');
        typeBadge.textContent = type.charAt(0).toUpperCase() + type.slice(1);

        // Set type badge color
        var colors = {
            'event': 'bg-primary',
            'facility': 'bg-success',
            'maintenance': 'bg-danger'
        };
        typeBadge.className = 'badge fs-6 ' + (colors[type] || 'bg-primary');

        // Format date - parse the date string properly
        var dateParts = date.split('-');
        var dateObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        document.getElementById('eventDate').textContent = dateObj.toLocaleDateString('en-US', options);

        document.getElementById('eventTime').textContent = startTime + ' - ' + endTime;
        document.getElementById('eventLocation').textContent = location || 'N/A';
        document.getElementById('eventRequestedBy').textContent = requestedBy;
        document.getElementById('eventDescription').textContent = description || 'No description provided.';

        var modal = new bootstrap.Modal(document.getElementById('eventModal'));
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Apply theme from user preference
        const theme = '{{ auth()->user()->theme ?? "light" }}';
        document.documentElement.setAttribute('data-theme', theme);

        // Load calendar events and render
        fetchCalendarEvents();
    });
</script>
@endsection
