<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>CampFix</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="{{ asset('css/app.css') }}" rel="stylesheet">

@yield('styles')

<style>
/* Fix table layout and prevent content wrapping issues */
.table {
    table-layout: auto !important;
    width: 100%;
}

/* Prevent headers from wrapping */
.table th {
    white-space: nowrap;
}

/* Prevent ugly word breaking in cells */
.table td {
    word-break: normal !important;
    overflow-wrap: break-word;
}

/* Set min-widths for common columns */
/* Title column (first or second) */
.table th:nth-child(1),
.table td:nth-child(1),
.table th:nth-child(2),
.table td:nth-child(2) {
    min-width: 200px;
}

/* Category (second or third) */
.table th:nth-child(2),
.table td:nth-child(2),
.table th:nth-child(3),
.table td:nth-child(3) {
    min-width: 180px;
}

/* Location (third or fourth) */
.table th:nth-child(3),
.table td:nth-child(3),
.table th:nth-child(4),
.table td:nth-child(4) {
    min-width: 100px;
}

/* Actions (last) */
.table th:last-child,
.table td:last-child {
    min-width: 120px;
    text-align: center;
}

/* ── Global Dark Theme ── */
[data-theme="dark"],
[data-theme="dark"] html,
[data-theme="dark"] body {
    background-color: #0f0f1a !important;
    color: #e0e0e0 !important;
}
html[data-theme="dark"],
html[data-theme="dark"] body {
    background-color: #0f0f1a !important;
    color: #e0e0e0 !important;
}
[data-theme="dark"] .sidebar { background: #12122a !important; }
[data-theme="dark"] .main-content { background: #0f0f1a !important; }
[data-theme="dark"] .card { background: #1a1a2e !important; border-color: #2a2a45 !important; color: #e0e0e0 !important; }
[data-theme="dark"] .card-header { background: #1e1e38 !important; border-color: #2a2a45 !important; color: #e0e0e0 !important; }
[data-theme="dark"] .table { color: #e0e0e0 !important; }
[data-theme="dark"] .table thead th { background: #1e1e38 !important; color: #aaa !important; border-color: #2a2a45 !important; }
[data-theme="dark"] .table td, [data-theme="dark"] .table th { border-color: #2a2a45 !important; }
[data-theme="dark"] .table-hover tbody tr:hover { background: #22223a !important; }
[data-theme="dark"] .table-light { background: #1e1e38 !important; color: #aaa !important; }
[data-theme="dark"] .form-control, [data-theme="dark"] .form-select { background: #1e1e38 !important; border-color: #2a2a45 !important; color: #e0e0e0 !important; }
[data-theme="dark"] .modal-content { background: #1a1a2e !important; color: #e0e0e0 !important; border-color: #2a2a45 !important; }
[data-theme="dark"] .modal-header { border-color: #2a2a45 !important; }
[data-theme="dark"] .modal-footer { border-color: #2a2a45 !important; }
[data-theme="dark"] .nav-tabs .nav-link { color: #aaa !important; }
[data-theme="dark"] .nav-tabs .nav-link.active { background: #1e1e38 !important; color: #fff !important; border-color: #2a2a45 #2a2a45 #1e1e38 !important; }
[data-theme="dark"] .alert { border-color: #2a2a45 !important; }
[data-theme="dark"] .dropdown-menu { background: #1a1a2e !important; border-color: #2a2a45 !important; }
[data-theme="dark"] .user-dropdown-menu { background: #1a1a2e !important; border-color: #2a2a45 !important; color: #e0e0e0 !important; }
[data-theme="dark"] .dropdown-header { color: #e0e0e0 !important; }
[data-theme="dark"] .dropdown-user-email { color: #aaa !important; }
[data-theme="dark"] .dropdown-item { color: #e0e0e0 !important; }
[data-theme="dark"] .dropdown-item:hover { background: #22223a !important; }
[data-theme="dark"] .text-muted { color: #888 !important; }
[data-theme="dark"] hr, [data-theme="dark"] .dropdown-divider { border-color: #2a2a45 !important; }
[data-theme="dark"] .nav-pills .nav-link { color: #aaa !important; }
[data-theme="dark"] .nav-pills .nav-link.active { background: #4f6ef7 !important; color: #fff !important; }
[data-theme="dark"] .badge.bg-light { background: #2a2a45 !important; color: #e0e0e0 !important; }
[data-theme="dark"] .top-bar { background: #12122a !important; border-color: #2a2a45 !important; }
[data-theme="dark"] input::placeholder, [data-theme="dark"] textarea::placeholder { color: #666 !important; }
[data-theme="dark"] .content { background: #0f0f1a !important; }
[data-theme="dark"] .top-header { background: #12122a !important; border-color: #2a2a45 !important; }
[data-theme="dark"] .header-page-title h2,
[data-theme="dark"] .header-page-title h1 { color: #e0e0e0 !important; }
.header-page-title { display: flex; flex-direction: column; justify-content: center; }
.header-page-title h1,
.header-page-title h2 { margin: 0; font-size: 20px; font-weight: 700; color: #1e293b; line-height: 1.2; }
.header-page-title p { margin: 0; font-size: 12px; color: #6b7280; line-height: 1.2; }
[data-theme="dark"] .notification-bell { color: #e0e0e0 !important; }
[data-theme="dark"] .notification-bell:hover { background: #2a2a45 !important; }
[data-theme="dark"] .notification-bell i { color: #e0e0e0 !important; }
[data-theme="dark"] .notification-dropdown { background: #1a1a2e !important; border-color: #2a2a45 !important; }
[data-theme="dark"] .notification-header { background: #1e1e38 !important; color: #e0e0e0 !important; border-color: #2a2a45 !important; }
[data-theme="dark"] .notification-item { border-color: #2a2a45 !important; }
[data-theme="dark"] .notification-item:hover { background: #22223a !important; }
[data-theme="dark"] .notification-content p { color: #e0e0e0 !important; }
[data-theme="dark"] .notification-content small { color: #888 !important; }
[data-theme="dark"] .notification-empty { color: #888 !important; }
[data-theme="dark"] .mobile-menu-btn { color: #e0e0e0 !important; }
[data-theme="dark"] .bg-white { background: #1a1a2e !important; color: #e0e0e0 !important; }
[data-theme="dark"] .table-card { background: #1a1a2e !important; color: #e0e0e0 !important; }
[data-theme="dark"] .card-header-custom { background: #1e1e38 !important; color: #e0e0e0 !important; }
[data-theme="dark"] h1, [data-theme="dark"] h2, [data-theme="dark"] h3, [data-theme="dark"] h4, [data-theme="dark"] h5, [data-theme="dark"] h6 { color: #e0e0e0 !important; }
[data-theme="dark"] .table tbody tr { background: #1a1a2e !important; }
[data-theme="dark"] .table tbody tr:nth-child(even) { background: #1e1e38 !important; }
[data-theme="dark"] .table-striped tbody tr:nth-of-type(odd) { background: #1a1a2e !important; }
[data-theme="dark"] .table-striped tbody tr:nth-of-type(even) { background: #1e1e38 !important; }
[data-theme="dark"] .badge { border-color: #2a2a45 !important; }
[data-theme="dark"] .badge.bg-success { background: #10b981 !important; color: #fff !important; }
[data-theme="dark"] .badge.bg-primary { background: #3b82f6 !important; color: #fff !important; }
[data-theme="dark"] .badge.bg-warning { background: #f59e0b !important; color: #000 !important; }
[data-theme="dark"] .badge.bg-danger { background: #ef4444 !important; color: #fff !important; }
[data-theme="dark"] .badge.bg-secondary { background: #6b7280 !important; color: #fff !important; }
[data-theme="dark"] .badge.bg-info { background: #06b6d4 !important; color: #fff !important; }
[data-theme="dark"] .table tbody tr td { background: transparent !important; }
[data-theme="dark"] .table-hover tbody tr:hover td { background: #22223a !important; }
[data-theme="dark"] .card { background: #1a1a2e !important; }
[data-theme="dark"] .card-body { background: #1a1a2e !important; }
[data-theme="dark"] .table-responsive { background: #1a1a2e !important; }
[data-theme="dark"] .avatar-sm { border-color: #2a2a45 !important; }
[data-theme="dark"] .role-badge { opacity: 0.9; }
</style>
</head>
<body data-user-timezone="{{ auth()->check() ? auth()->user()->timezone : config('app.timezone') }}" data-user-locale="{{ app()->getLocale() }}" data-user-date-format="{{ $userDateFormat ?? 'Y-m-d' }}">

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar/Nav -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="{{ asset('Campfix/Images/logo.png') }}" alt="CampFix Logo" height="35" style="margin-right: 8px;"><span class="sidebar-logo-text"><span class="camp-text">Camp</span><span class="fix-text">fix</span></span>
    </div>

    <div class="sidebar-content">
        @auth

        <a href="/dashboard" class="{{ Request::is('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i> {{ app()->getLocale() === 'tl' ? 'Home' : 'Home' }}
        </a>

        {{-- Only show My Concerns for non-admin and non-maintenance users --}}
        @if(auth()->user()->role !== 'mis' && auth()->user()->role !== 'maintenance')
            <a href="{{ route('concerns.my') }}" class="{{ Request::is('my-concerns') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i> {{ app()->getLocale() === 'tl' ? 'Aking Mga Concern' : 'My Concerns' }}
            </a>

            {{-- Only show My Events for faculty users --}}
            @if(auth()->user()->role === 'faculty')
                <a href="{{ route('events.my') }}" class="{{ Request::is('my-events') ? 'active' : '' }}">
                    <i class="fas fa-calendar"></i> {{ app()->getLocale() === 'tl' ? 'Aking Mga Event' : 'My Events' }}
                </a>
            @endif
        @elseif(auth()->user()->role === 'mis')
            <a href="{{ route('concerns.my') }}" class="{{ Request::is('my-concerns') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list"></i> My Concerns
            </a>
        @endif

        {{-- Only show Assigned Tasks for maintenance role --}}
        @if(auth()->user()->role === 'maintenance')
            <a href="{{ route('reports.assigned') }}" class="{{ Request::is('reports/assigned*') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> {{ app()->getLocale() === 'tl' ? 'Mga Nakatalagang Gawain' : 'Assigned Tasks' }}
            </a>
        @endif

        {{-- MIS navigation --}}
        @if(auth()->user()->role === 'mis')

            <a href="/admin/mis-tasks" class="{{ Request::is('admin/mis-tasks') ? 'active' : '' }}">
                <i class="fas fa-tasks"></i> {{ app()->getLocale() === 'tl' ? 'Gawain' : 'Task' }}
            </a>

            <hr>

            <a href="#" onclick="requirePasswordToAccess('/admin/users', event)" class="{{ Request::is('admin/users') ? 'active' : '' }}">
                <i class="fas fa-users"></i> {{ app()->getLocale() === 'tl' ? 'Mga Gumagamit' : 'Users' }}
            </a>

            <a href="/admin/logs" class="{{ Request::is('admin/logs') ? 'active' : '' }}">
                <i class="fas fa-history"></i> {{ app()->getLocale() === 'tl' ? 'Audit Logs' : 'Audit Logs' }}
            </a>

            <a href="/settings" class="{{ Request::is('settings') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> {{ app()->getLocale() === 'tl' ? 'Mga Setting' : 'Settings' }}
            </a>

        @endif

        {{-- Building Admin navigation --}}
        @if(auth()->user()->role === 'building_admin')
            <a href="/admin/reports" class="{{ Request::is('admin/reports') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i> {{ app()->getLocale() === 'tl' ? 'Mga Ulat' : 'Reports' }}
            </a>

            <a href="/admin/events" class="{{ Request::is('admin/events') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt"></i> {{ app()->getLocale() === 'tl' ? 'Mga Event' : 'Events' }}
            </a>

            <a href="{{ route('events.my') }}" class="{{ Request::is('my-events') ? 'active' : '' }}">
                <i class="fas fa-calendar"></i> {{ app()->getLocale() === 'tl' ? 'Aking Mga Event' : 'My Events' }}
            </a>

            <a href="/admin/logs" class="{{ Request::is('admin/logs') ? 'active' : '' }}">
                <i class="fas fa-history"></i> Audit Logs
            </a>
        @endif

        {{-- School Admin, Academic Head, Program Head, Principal Assistant navigation --}}
        @if(in_array(auth()->user()->role, ['school_admin', 'academic_head', 'program_head', 'principal_assistant']))
            <a href="{{ route('events.my') }}" class="{{ Request::is('my-events') ? 'active' : '' }}">
                <i class="fas fa-calendar"></i> {{ app()->getLocale() === 'tl' ? 'Aking Mga Event' : 'My Events' }}
            </a>
        @endif

        @endauth
    </div>
</div>

<!-- Main content -->
<div class="content">
    
    <!-- Top Header Bar -->
    <div class="top-header">
        <div class="header-left" style="display: flex; align-items: center;">
            <button class="mobile-menu-btn" onclick="toggleSidebar()" style="margin-right: 10px;">
                <i class="fas fa-bars"></i>
            </button>
            @hasSection('page_title')
                <div class="header-page-title">@yield('page_title')</div>
            @endif
        </div>
        <div class="header-right">
            <!-- Notification Bell -->
            <div class="notification-bell" onclick="toggleNotification(event)">
                <i class="fas fa-bell"></i>
            @if(isset($unread_count) && $unread_count > 0)
                <span class="notification-badge">{{ $unread_count }}</span>
            @endif
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <span>{{ app()->getLocale() === 'tl' ? 'Mga Abiso' : 'Notifications' }}</span>
                    </div>
                    <div class="notification-list">
                        @forelse($notifications ?? [] as $notification)
                            <div class="notification-item">
                                <a href="{{ route('notifications.read', $notification->id) }}" class="notification-link">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="notification-content">
                                        <p>{{ $notification->data['message'] ?? 'Notification' }}</p>
                                        <small>{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                </a>
                                <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="notification-delete-form" onsubmit="return confirm('Are you sure you want to delete this notification?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="notification-delete-btn" title="Delete notification">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p>{{ app()->getLocale() === 'tl' ? 'Walang abiso' : 'No notifications' }}</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- User Dropdown -->
            @auth
            <div class="user-dropdown-top">
                <div class="user-icon" onclick="toggleDropdown(event)">
                    @if(auth()->user()->profile_picture)
                        <img src="{{ asset('storage/' . auth()->user()->profile_picture) }}?t={{ time() }}" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ substr(auth()->user()->name, 0, 1) }}
                    @endif
                </div>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div class="dropdown-user-name">{{ auth()->user()->name }}</div>
                        <div class="dropdown-user-email">{{ auth()->user()->email }}</div>
                    </div>
                    <hr class="dropdown-divider">

                    {{-- Profile Management --}}
                    <a href="{{ route('profile.index') }}" class="dropdown-item" style="display:flex;align-items:center;gap:8px;padding:8px 16px;text-decoration:none;color:inherit;font-size:14px;">
                        <i class="fas fa-user-circle" style="width:16px;text-align:center;"></i> Profile Management
                    </a>

                    {{-- Theme Toggle --}}
                    <div class="dropdown-item" style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px;font-size:14px;">
                        <span><i class="fas fa-palette" style="width:16px;text-align:center;margin-right:8px;"></i> Theme</span>
                        <div style="display:flex;gap:4px;">
                            <button id="theme-light-btn" onclick="setTheme('light')" title="Light"
                                style="border:2px solid #dee2e6;background:#fff;color:#333;border-radius:6px;padding:3px 8px;font-size:11px;cursor:pointer;font-weight:600;transition:all .2s;">
                                <i class="fas fa-sun"></i>
                            </button>
                            <button id="theme-dark-btn" onclick="setTheme('dark')" title="Dark"
                                style="border:2px solid #dee2e6;background:#1a1a2e;color:#e0e0e0;border-radius:6px;padding:3px 8px;font-size:11px;cursor:pointer;font-weight:600;transition:all .2s;">
                                <i class="fas fa-moon"></i>
                            </button>
                        </div>
                    </div>

                    <hr class="dropdown-divider">
                    <form method="POST" action="/logout" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item logout" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
                            <i class="fas fa-sign-out-alt"></i> {{ app()->getLocale() === 'tl' ? 'Mag-sign Out' : 'Sign Out' }}
                        </button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </div>

    @yield('content')

    @auth

    @endauth

</div>

@auth
<!-- Event Request Modal for Faculty -->
<div class="modal fade" id="eventRequestModal" tabindex="-1" aria-labelledby="eventRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventRequestModalLabel"><i class="fas fa-calendar-plus"></i> {{ app()->getLocale() === 'tl' ? 'Magsumite ng Facility Request' : 'Submit Facility Request' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventRequestForm" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="modal_location" name="location" value="">
                <div class="modal-body">
                    <!-- Date and Time Selection First -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="modal_event_date" class="form-label">Date *</label>
                            <input type="date" class="form-control @error('event_date') is-invalid @enderror" id="modal_event_date" name="event_date"
                                min="{{ date('Y-m-d') }}" required value="{{ old('event_date') }}">
                            @error('event_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="modal_start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control @error('start_time') is-invalid @enderror" id="modal_start_time" name="start_time" required value="{{ old('start_time') }}">
                            @error('start_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="modal_end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control @error('end_time') is-invalid @enderror" id="modal_end_time" name="end_time" required value="{{ old('end_time') }}">
                            @error('end_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_title" class="form-label">Event Title *</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="modal_title" name="title"
                            placeholder="e.g., Science Fair 2026, Faculty Meeting" required value="{{ old('title') }}">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="modal_category" class="form-label">Request Type *</label>
                        <select class="form-select @error('category') is-invalid @enderror" id="modal_category" name="category" required>
                                <option value="">Select category</option>
                                <option value="Area Use">Area Use</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="modal_education_level" class="form-label">Intended User *</label>
                            <select class="form-select" id="modal_education_level" name="education_level" required>
                                <option value="faculty" selected>Faculty</option>
                                <option value="tertiary">Tertiary</option>
                                <option value="shs">Senior High School</option>
                                <option value="staff">Staff</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                        </div>

                        <div class="row">
                        <div class="col-md-6 mb-3" id="modal_area_of_use_container" style="display: none;">
                            <label for="modal_area_of_use" class="form-label">Location *</label>
                            <select class="form-select @error('area_of_use') is-invalid @enderror" id="modal_area_of_use" name="area_of_use">
                                <option value="">Select area</option>
                                <option value="Room">Room</option>
                                <option value="Court">Court</option>
                                <option value="AVR">AVR</option>
                                <option value="Library">Library</option>
                                <option value="Open Lobby">Open Lobby</option>
                                <option value="Computer Laboratory">Computer Laboratory</option>
                                <option value="Kitchen">Kitchen</option>
                            </select>
                            @error('area_of_use')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3" id="modal_room_number_container" style="display: none;">
                            <label for="modal_room_number" class="form-label">Room Number *</label>
                            <select class="form-select @error('room_number') is-invalid @enderror" id="modal_room_number" name="room_number">
                                <option value="">Select room</option>
                                @for($i = 1; $i <= 5; $i++)
                                    @for($j = 1; $j <= 11; $j++)
                                        @php $room = $i . str_pad($j, 2, '0', STR_PAD_LEFT); @endphp
                                        <option value="{{ $room }}">{{ $room }}</option>
                                    @endfor
                                @endfor
                                <option value="Suite Room">Suite Room</option>
                                <option value="Kitchen 1">Kitchen 1</option>
                                <option value="Kitchen 2">Kitchen 2</option>
                                <option value="Bar">Bar</option>
                                <option value="M01">M01</option>
                            </select>
                            @error('room_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3" id="modal_court_type_container" style="display: none;">
                            <label for="modal_court_type" class="form-label">Request Category *</label>
                            <select class="form-select @error('court_type') is-invalid @enderror" id="modal_court_type" name="court_type">
                                <option value="">Select type</option>
                                <option value="Non-academic">Non-academic</option>
                                <option value="Academic">Academic</option>
                            </select>
                            @error('court_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3" id="modal_court_purpose_container" style="display: none;">
                            <label for="modal_court_purpose" class="form-label">Purpose *</label>
                            <input type="text" class="form-control @error('court_purpose') is-invalid @enderror" id="modal_court_purpose" name="court_purpose"
                                placeholder="Describe the purpose for court use" value="{{ old('court_purpose') }}">
                            @error('court_purpose')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3" id="modal_avr_selection_container" style="display: none;">
                            <label for="modal_avr_selection" class="form-label">AVR Selection *</label>
                            <select class="form-select @error('avr_selection') is-invalid @enderror" id="modal_avr_selection" name="avr_selection">
                                <option value="">Select AVR</option>
                                <option value="AVR 1">AVR 1</option>
                                <option value="AVR 2">AVR 2</option>
                            </select>
                            @error('avr_selection')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3" id="modal_avr_request_category_container" style="display: none;">
                            <label for="modal_avr_request_category" class="form-label">Request Category *</label>
                            <select class="form-select @error('avr_request_category') is-invalid @enderror" id="modal_avr_request_category" name="avr_request_category">
                                <option value="">Select category</option>
                                <option value="Non-academic">Non-academic</option>
                                <option value="Academic">Academic</option>
                            </select>
                            @error('avr_request_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Department (only shows when Room is selected in Area of Use) -->
                    <div class="mb-3" id="modal_department_container" style="display: none;">
                        <label for="modal_department" class="form-label">Department *</label>
                        <select class="form-select" id="modal_department" name="department">
                            <option value="">Select department</option>
                            <option value="GE">GE</option>
                            <option value="ICT">ICT</option>
                            <option value="Business Management">Business Management</option>
                            <option value="THM">THM</option>
                        </select>
                    </div>


                    <div class="mb-3">
                        <label for="modal_description" class="form-label">Description *</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="modal_description" name="description" 
                            rows="4" placeholder="Describe the event purpose and details..." required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Upload Picture (Optional) -->
                    <div class="mb-3">
                        <label for="modal_picture" class="form-label">Upload Picture <span class="text-muted">(Optional)</span></label>
                        <input type="file" class="form-control" id="modal_picture" name="picture" accept="image/*">
                        <div class="form-text">Accepted formats: JPG, PNG. Max size: 5MB.</div>
                        <div id="modal_picture_preview" class="mt-2" style="display: none;">
                            <img id="modal_picture_img" src="" alt="Preview" style="max-height: 150px; border-radius: 6px; border: 1px solid #dee2e6;">
                        </div>
                    </div>

                    <!-- Materials/Equipment Needed (Optional) -->
                    <div class="mb-3">
                        <label class="form-label">Materials/Equipment Needed (Optional)</label>
                        <table class="table table-bordered" id="modalMaterialsTable">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">Qty</th>
                                    <th>Item</th>
                                    <th>Purpose</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" class="form-control" name="materials[0][qty]" min="1" placeholder="1"></td>
                                    <td><input type="text" class="form-control" name="materials[0][item]" placeholder="e.g., Projector, Chair, etc."></td>
                                    <td><input type="text" class="form-control" name="materials[0][purpose]" placeholder="e.g., For presentation"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeModalMaterialRow(this)"><i class="fas fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addModalMaterialRow()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale() === 'tl' ? 'Kanselahin' : 'Cancel' }}</button>
                    <button type="button" class="btn btn-primary" id="previewBtn">{{ app()->getLocale() === 'tl' ? 'I-preview' : 'Preview' }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="eventPreviewModal" tabindex="-1" aria-labelledby="eventPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="eventPreviewModalLabel"><i class="fas fa-eye"></i> {{ app()->getLocale() === 'tl' ? 'Preview ng Event Request' : 'Event Request Preview' }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="border-bottom pb-2 mb-3">Event Details</h6>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Event Title:</div>
                    <div class="col-md-8" id="preview_title"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Category:</div>
                    <div class="col-md-8" id="preview_category"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Intended User:</div>
                    <div class="col-md-8" id="preview_education_level"></div>
                </div>
                <div class="row mb-2" id="preview_area_of_use_row" style="display: none;">
                    <div class="col-md-4 fw-bold">Location:</div>
                    <div class="col-md-8" id="preview_area_of_use"></div>
                </div>
                <div class="row mb-2" id="preview_room_number_row" style="display: none;">
                    <div class="col-md-4 fw-bold">Room Number:</div>
                    <div class="col-md-8" id="preview_room_number"></div>
                </div>
                <div class="row mb-2" id="preview_department_row" style="display: none;">
                    <div class="col-md-4 fw-bold">Department:</div>
                    <div class="col-md-8" id="preview_department"></div>
                </div>
                <div class="row mb-2" id="preview_court_type_row" style="display: none;">
                    <div class="col-md-4 fw-bold">Court Type:</div>
                    <div class="col-md-8" id="preview_court_type"></div>
                </div>
                <div class="row mb-2" id="preview_court_purpose_row" style="display: none;">
                    <div class="col-md-4 fw-bold">Court Purpose:</div>
                    <div class="col-md-8" id="preview_court_purpose"></div>
                </div>
                <div class="row mb-2" id="preview_avr_selection_row" style="display: none;">
                    <div class="col-md-4 fw-bold">AVR Selection:</div>
                    <div class="col-md-8" id="preview_avr_selection"></div>
                </div>
                <div class="row mb-2" id="preview_avr_request_category_row" style="display: none;">
                    <div class="col-md-4 fw-bold">AVR Request Category:</div>
                    <div class="col-md-8" id="preview_avr_request_category"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Date:</div>
                    <div class="col-md-8" id="preview_event_date"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Time:</div>
                    <div class="col-md-8"><span id="preview_start_time"></span> - <span id="preview_end_time"></span></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Location:</div>
                    <div class="col-md-8" id="preview_location"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Description:</div>
                    <div class="col-md-8" id="preview_description"></div>
                </div>
                <div class="row mt-3 pt-3 border-top">
                    <div class="col-12">
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> <strong>Approval will be sent to:</strong> <span id="approval_recipients">Chosen Department on the selection, Academic Head, Building Admin, and School Administrator</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="editEventBtn">
                    <i class="fas fa-edit"></i> {{ app()->getLocale() === 'tl' ? 'I-edit' : 'Edit' }}
                </button>
                <button type="submit" form="eventRequestForm" class="btn btn-primary" id="submitEventBtn">
                    <i class="fas fa-check"></i> {{ app()->getLocale() === 'tl' ? 'Isumite para sa Pag-apruba' : 'Submit for Approval' }}
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalCategory = document.getElementById('modal_category');
    if (modalCategory) {
        modalCategory.addEventListener('change', function() {
            var areaOfUseContainer = document.getElementById('modal_area_of_use_container');
            var roomNumberContainer = document.getElementById('modal_room_number_container');
            var departmentContainer = document.getElementById('modal_department_container');
            var areaOfUseSelect = document.getElementById('modal_area_of_use');
            var roomNumberSelect = document.getElementById('modal_room_number');

            if (this.value === 'Area Use') {
                areaOfUseContainer.style.display = 'block';
                areaOfUseSelect.setAttribute('required', 'required');
            } else {
                areaOfUseContainer.style.display = 'none';
                roomNumberContainer.style.display = 'none';
                departmentContainer.style.display = 'none';
                areaOfUseSelect.removeAttribute('required');
                areaOfUseSelect.value = '';
                roomNumberSelect.removeAttribute('required');
                roomNumberSelect.value = '';
            }
            updatePreviewButtonState();
        });
    }

    var modalAreaOfUse = document.getElementById('modal_area_of_use');
    if (modalAreaOfUse) {
        modalAreaOfUse.addEventListener('change', function() {
            var roomNumberContainer = document.getElementById('modal_room_number_container');
            var departmentContainer = document.getElementById('modal_department_container');
            var courtTypeContainer = document.getElementById('modal_court_type_container');
            var courtPurposeContainer = document.getElementById('modal_court_purpose_container');
            var avrSelectionContainer = document.getElementById('modal_avr_selection_container');
            var avrRequestCategoryContainer = document.getElementById('modal_avr_request_category_container');
            var roomNumberSelect = document.getElementById('modal_room_number');
            var departmentSelect = document.getElementById('modal_department');
            var courtTypeSelect = document.getElementById('modal_court_type');
            var courtPurposeInput = document.getElementById('modal_court_purpose');
            var avrSelectionSelect = document.getElementById('modal_avr_selection');
            var avrRequestCategorySelect = document.getElementById('modal_avr_request_category');

            // Clear court availability message
            var existingCourtMsg = document.getElementById('modal_court_availability_message');
            if (existingCourtMsg) {
                existingCourtMsg.remove();
            }

            // Clear AVR availability message
            var existingAvrMsg = document.getElementById('modal_avr_availability_message');
            if (existingAvrMsg) {
                existingAvrMsg.remove();
            }

            if (this.value === 'Room') {
                var isShs = document.getElementById('modal_education_level').value === 'shs';
                roomNumberContainer.style.display = 'block';
                departmentContainer.style.display = isShs ? 'none' : 'block';
                courtTypeContainer.style.display = 'none';
                courtPurposeContainer.style.display = 'none';
                avrSelectionContainer.style.display = 'none';
                avrRequestCategoryContainer.style.display = 'none';
                roomNumberSelect.setAttribute('required', 'required');
                if (isShs) { departmentSelect.removeAttribute('required'); departmentSelect.value = ''; }
                else { departmentSelect.setAttribute('required', 'required'); }
                courtTypeSelect.removeAttribute('required');
                courtPurposeInput.removeAttribute('required');
                avrSelectionSelect.removeAttribute('required');
                avrRequestCategorySelect.removeAttribute('required');
                courtTypeSelect.value = '';
                courtPurposeInput.value = '';
                avrSelectionSelect.value = '';
                avrRequestCategorySelect.value = '';
            } else if (this.value === 'Court') {
                roomNumberContainer.style.display = 'none';
                departmentContainer.style.display = 'none';
                courtTypeContainer.style.display = 'block';
                courtPurposeContainer.style.display = 'none';
                avrSelectionContainer.style.display = 'none';
                avrRequestCategoryContainer.style.display = 'none';
                roomNumberSelect.removeAttribute('required');
                departmentSelect.removeAttribute('required');
                courtTypeSelect.setAttribute('required', 'required');
                courtPurposeInput.removeAttribute('required');
                avrSelectionSelect.removeAttribute('required');
                avrRequestCategorySelect.removeAttribute('required');
                roomNumberSelect.value = '';
                departmentSelect.value = '';
                courtPurposeInput.value = '';
                avrSelectionSelect.value = '';
                avrRequestCategorySelect.value = '';
            } else if (this.value === 'AVR') {
                roomNumberContainer.style.display = 'none';
                departmentContainer.style.display = 'none';
                courtTypeContainer.style.display = 'none';
                courtPurposeContainer.style.display = 'none';
                avrSelectionContainer.style.display = 'block';
                avrRequestCategoryContainer.style.display = 'none';
                roomNumberSelect.removeAttribute('required');
                departmentSelect.removeAttribute('required');
                courtTypeSelect.removeAttribute('required');
                courtPurposeInput.removeAttribute('required');
                avrSelectionSelect.setAttribute('required', 'required');
                avrRequestCategorySelect.removeAttribute('required');
                roomNumberSelect.value = '';
                departmentSelect.value = '';
                courtTypeSelect.value = '';
                courtPurposeInput.value = '';
                avrRequestCategorySelect.value = '';
            } else {
                roomNumberContainer.style.display = 'none';
                departmentContainer.style.display = 'none';
                courtTypeContainer.style.display = 'none';
                courtPurposeContainer.style.display = 'none';
                avrSelectionContainer.style.display = 'none';
                avrRequestCategoryContainer.style.display = 'none';
                roomNumberSelect.removeAttribute('required');
                departmentSelect.removeAttribute('required');
                courtTypeSelect.removeAttribute('required');
                courtPurposeInput.removeAttribute('required');
                avrSelectionSelect.removeAttribute('required');
                avrRequestCategorySelect.removeAttribute('required');
                roomNumberSelect.value = '';
                departmentSelect.value = '';
                courtTypeSelect.value = '';
                courtPurposeInput.value = '';
                avrSelectionSelect.value = '';
                avrRequestCategorySelect.value = '';
            }
            updatePreviewButtonState();
        });
    }

    // Court Type change handler for modal
    var modalCourtType = document.getElementById('modal_court_type');
    if (modalCourtType) {
        modalCourtType.addEventListener('change', function() {
            var courtPurposeContainer = document.getElementById('modal_court_purpose_container');
            var courtPurposeInput = document.getElementById('modal_court_purpose');
            var departmentContainer = document.getElementById('modal_department_container');
            var departmentSelect = document.getElementById('modal_department');

            if (this.value) {
                courtPurposeContainer.style.display = 'block';
                courtPurposeInput.setAttribute('required', 'required');

                // Show department for academic court requests
                if (this.value === 'Academic') {
                    departmentContainer.style.display = 'block';
                    departmentSelect.setAttribute('required', 'required');
                } else {
                    departmentContainer.style.display = 'none';
                    departmentSelect.removeAttribute('required');
                    departmentSelect.value = '';
                }
            } else {
                courtPurposeContainer.style.display = 'none';
                courtPurposeInput.removeAttribute('required');
                courtPurposeInput.value = '';
                departmentContainer.style.display = 'none';
                departmentSelect.removeAttribute('required');
                departmentSelect.value = '';
            }
            updatePreviewButtonState();
        });
    }

    // Court availability check
    function checkCourtAvailabilityModal() {
        var eventDate = document.getElementById('modal_event_date') ? document.getElementById('modal_event_date').value : '';
        var startTime = document.getElementById('modal_start_time') ? document.getElementById('modal_start_time').value : '';
        var endTime = document.getElementById('modal_end_time') ? document.getElementById('modal_end_time').value : '';
        var areaOfUse = document.getElementById('modal_area_of_use') ? document.getElementById('modal_area_of_use').value : '';

        if (!eventDate || !startTime || !endTime || areaOfUse !== 'Court') {
            return; // Don't check if fields are empty or not checking court
        }

        // Remove previous availability message
        var existingMsg = document.getElementById('modal_court_availability_message');
        if (existingMsg) {
            existingMsg.remove();
        }

        // Show loading message
        var messageDiv = document.createElement('div');
        messageDiv.id = 'modal_court_availability_message';
        messageDiv.className = 'alert alert-info mt-2';
        messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking court availability...';

        var courtContainer = document.getElementById('modal_court_type_container');
        if (courtContainer) {
            courtContainer.parentNode.insertBefore(messageDiv, courtContainer.nextSibling);
        }

        fetch('/api/check-court-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                event_date: eventDate,
                start_time: startTime,
                end_time: endTime
            })
        })
        .then(response => response.json())
        .then(data => {
            var messageDiv = document.getElementById('modal_court_availability_message');

            if (data.available) {
                messageDiv.className = 'alert alert-success mt-2';
                messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> Court is available for the selected time.';
            } else {
                messageDiv.className = 'alert alert-danger mt-2';
                var conflicts = data.conflicting_events.map(event =>
                    `${event.title} (${event.start_time} - ${event.end_time}) by ${event.user}`
                ).join('<br>');
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Court is not available. Conflicts:<br>' + conflicts;
            }
        })
        .catch(error => {
            console.error('Error checking court availability:', error);
            var messageDiv = document.getElementById('modal_court_availability_message');
            if (messageDiv) {
                messageDiv.className = 'alert alert-warning mt-2';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error checking court availability. Please try again.';
            }
        });
    }

    // AVR availability check
    function checkAvrAvailabilityModal() {
        var avrSelection = document.getElementById('modal_avr_selection') ? document.getElementById('modal_avr_selection').value : '';
        var eventDate = document.getElementById('modal_event_date') ? document.getElementById('modal_event_date').value : '';
        var startTime = document.getElementById('modal_start_time') ? document.getElementById('modal_start_time').value : '';
        var endTime = document.getElementById('modal_end_time') ? document.getElementById('modal_end_time').value : '';
        var areaOfUse = document.getElementById('modal_area_of_use') ? document.getElementById('modal_area_of_use').value : '';

        if (!avrSelection || !eventDate || !startTime || !endTime || areaOfUse !== 'AVR') {
            return; // Don't check if fields are empty or not checking AVR
        }

        // Remove previous availability message
        var existingMsg = document.getElementById('modal_avr_availability_message');
        if (existingMsg) {
            existingMsg.remove();
        }

        // Show loading message
        var messageDiv = document.createElement('div');
        messageDiv.id = 'modal_avr_availability_message';
        messageDiv.className = 'alert alert-info mt-2';
        messageDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking AVR availability...';

        var avrContainer = document.getElementById('modal_avr_request_category_container');
        if (avrContainer) {
            avrContainer.parentNode.insertBefore(messageDiv, avrContainer.nextSibling);
        }

        fetch('/api/check-avr-availability', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                avr_selection: avrSelection,
                event_date: eventDate,
                start_time: startTime,
                end_time: endTime
            })
        })
        .then(response => response.json())
        .then(data => {
            var messageDiv = document.getElementById('modal_avr_availability_message');

            if (data.available) {
                messageDiv.className = 'alert alert-success mt-2';
                messageDiv.innerHTML = '<i class="fas fa-check-circle"></i> ' + avrSelection + ' is available for the selected time.';
            } else {
                messageDiv.className = 'alert alert-danger mt-2';
                var conflicts = data.conflicting_events.map(event =>
                    `${event.title} (${event.start_time} - ${event.end_time}) by ${event.user}`
                ).join('<br>');
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + avrSelection + ' is not available. Conflicts:<br>' + conflicts;
            }
        })
        .catch(error => {
            console.error('Error checking AVR availability:', error);
            var messageDiv = document.getElementById('modal_avr_availability_message');
            if (messageDiv) {
                messageDiv.className = 'alert alert-warning mt-2';
                messageDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error checking AVR availability. Please try again.';
            }
        });
    }

    // Room availability check
    var modalRoomNumber = document.getElementById('modal_room_number');
    if (modalRoomNumber) {
        modalRoomNumber.addEventListener('change', function() {
            checkRoomAvailability();
        });
    }

    var modalEventDate = document.getElementById('modal_event_date');
    var modalStartTime = document.getElementById('modal_start_time');
    var modalEndTime = document.getElementById('modal_end_time');
    var modalAreaOfUse = document.getElementById('modal_area_of_use');

    [modalEventDate, modalStartTime, modalEndTime].forEach(function(element) {
        if (element) {
            element.addEventListener('change', function() {
                if (modalRoomNumber && modalRoomNumber.value) {
                    checkRoomAvailability();
                }
                if (modalAreaOfUse && modalAreaOfUse.value === 'Court') {
                    checkCourtAvailabilityModal();
                }
            });
        }
    });

    // Court availability listeners
    if (modalAreaOfUse) {
        modalAreaOfUse.addEventListener('change', function() {
            if (this.value === 'Court') {
                checkCourtAvailabilityModal();
            }
        });
    }

    // AVR availability listeners
    if (modalAreaOfUse) {
        modalAreaOfUse.addEventListener('change', function() {
            if (this.value === 'AVR') {
                checkAvrAvailabilityModal();
            }
        });
    }

    var modalAvrSelection = document.getElementById('modal_avr_selection');
    if (modalAvrSelection) {
        modalAvrSelection.addEventListener('change', function() {
            checkAvrAvailabilityModal();
        });
    }

    function checkRoomAvailability() {
        var roomNumber = modalRoomNumber ? modalRoomNumber.value : '';
        var eventDate = modalEventDate ? modalEventDate.value : '';
        var startTime = modalStartTime ? modalStartTime.value : '';
        var endTime = modalEndTime ? modalEndTime.value : '';

        if (roomNumber && eventDate && startTime && endTime) {
            // Here you would typically make an AJAX call to check availability
            // For now, we'll show a placeholder message
            var availabilityMessage = document.getElementById('room_availability_message');
            if (!availabilityMessage) {
                availabilityMessage = document.createElement('div');
                availabilityMessage.id = 'room_availability_message';
                availabilityMessage.className = 'alert alert-warning mt-2';
                modalRoomNumber.parentNode.appendChild(availabilityMessage);
            }

            // Placeholder: In a real implementation, this would check against existing bookings
            availabilityMessage.innerHTML = '<i class="fas fa-info-circle"></i> Checking room availability for ' + roomNumber + ' on ' + eventDate + ' from ' + startTime + ' to ' + endTime + '...';
            availabilityMessage.style.display = 'block';

            // Simulate API call delay
            setTimeout(function() {
                // This is where you'd handle the response
                // For demo purposes, we'll assume the room is available
                availabilityMessage.innerHTML = '<i class="fas fa-check-circle text-success"></i> Room ' + roomNumber + ' appears to be available for the selected time.';
                availabilityMessage.className = 'alert alert-success mt-2';
            }, 1000);
        }
    }

    // Function to check if required fields are filled and enable/disable Preview button
    function updatePreviewButtonState() {
        var title = document.getElementById('modal_title');
        var category = document.getElementById('modal_category');
        var areaOfUse = document.getElementById('modal_area_of_use');
        var roomNumber = document.getElementById('modal_room_number');
        var courtType = document.getElementById('modal_court_type');
        var courtPurpose = document.getElementById('modal_court_purpose');
        var department = document.getElementById('modal_department');
        var educationLevel = document.getElementById('modal_education_level');
        var avrSelection = document.getElementById('modal_avr_selection');
        var avrRequestCategory = document.getElementById('modal_avr_request_category');
        var eventDate = document.getElementById('modal_event_date');
        var startTime = document.getElementById('modal_start_time');
        var endTime = document.getElementById('modal_end_time');
        var description = document.getElementById('modal_description');
        var previewBtn = document.getElementById('previewBtn');

        var isValid = true;
        var categoryValue = category ? category.value : '';
        var areaValue = areaOfUse ? areaOfUse.value : '';
        var courtTypeValue = courtType ? courtType.value : '';
        var avrSelectionValue = avrSelection ? avrSelection.value : '';
        var educationLevelValue = educationLevel ? educationLevel.value : 'faculty';

        if (!title || !title.value.trim() || title.value.trim().length < 3) isValid = false;
        if (!categoryValue) isValid = false;

        if (categoryValue === 'Area Use') {
            if (!areaValue) isValid = false;

            if (areaValue === 'Room') {
                if (!roomNumber || !roomNumber.value) isValid = false;
                // Only require department for tertiary level
                if (educationLevelValue !== 'shs' && (!department || !department.value)) isValid = false;
            }

            if (areaValue === 'Court') {
                if (!courtTypeValue) isValid = false;
                if (courtTypeValue && (!courtPurpose || !courtPurpose.value.trim())) isValid = false;
                if (courtTypeValue === 'Academic' && (!department || !department.value)) isValid = false;
            }

            if (areaValue === 'AVR') {
                if (!avrSelectionValue) isValid = false;
                if (avrSelectionValue && (!avrRequestCategory || !avrRequestCategory.value)) isValid = false;
                // Require department for Academic AVR (same as Academic Court)
                var avrRequestCategoryValue = avrRequestCategory ? avrRequestCategory.value : '';
                if (avrRequestCategoryValue === 'Academic' && (!department || !department.value)) isValid = false;
            }
        }

        if (!eventDate || !eventDate.value) isValid = false;
        if (!startTime || !startTime.value) isValid = false;
        if (!endTime || !endTime.value) isValid = false;
        if (startTime && endTime && startTime.value && endTime.value && endTime.value <= startTime.value) isValid = false;
        if (!description || !description.value.trim() || description.value.trim().length < 10) isValid = false;

        if (previewBtn) {
            previewBtn.disabled = false;
            previewBtn.classList.toggle('btn-secondary', !isValid);
            previewBtn.classList.toggle('btn-primary', isValid);
        }
    }
    
    // Picture preview
    var pictureInput = document.getElementById('modal_picture');
    if (pictureInput) {
        pictureInput.addEventListener('change', function() {
            var preview = document.getElementById('modal_picture_preview');
            var img = document.getElementById('modal_picture_img');
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(this.files[0]);
            } else {
                preview.style.display = 'none';
                img.src = '';
            }
        });
    }

    // Add event listeners to all required fields to update Preview button state
    var requiredFieldIds = ['modal_title', 'modal_category', 'modal_area_of_use', 'modal_room_number', 'modal_court_type', 'modal_court_purpose', 'modal_department', 'modal_education_level', 'modal_avr_selection', 'modal_avr_request_category', 'modal_event_date', 'modal_start_time', 'modal_end_time', 'modal_description'];
    requiredFieldIds.forEach(function(fieldId) {
        var field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updatePreviewButtonState);
            field.addEventListener('change', updatePreviewButtonState);
        }
    });
    
    // Initial check on page load
    updatePreviewButtonState();

    // Hide department when SHS is selected
    var educationLevelSelect = document.getElementById('modal_education_level');
    if (educationLevelSelect) {
        educationLevelSelect.addEventListener('change', function() {
            var isShs = this.value === 'shs';
            var areaOfUse = document.getElementById('modal_area_of_use');
            var departmentContainer = document.getElementById('modal_department_container');
            var departmentSelect = document.getElementById('modal_department');
            if (isShs) {
                departmentContainer.style.display = 'none';
                departmentSelect.removeAttribute('required');
                departmentSelect.value = '';
            } else if (areaOfUse && areaOfUse.value === 'Room') {
                departmentContainer.style.display = 'block';
                departmentSelect.setAttribute('required', 'required');
            }
            // Update preview button state after changing requirements
            updatePreviewButtonState();
        });
    }

    // Populate preview modal when shown
    var previewModal = document.getElementById('eventPreviewModal');
    if (previewModal) {
        previewModal.addEventListener('show.bs.modal', function() {
            // Get form values
            var title = document.getElementById('modal_title').value;
            var category = document.getElementById('modal_category').value;
            var areaOfUse = document.getElementById('modal_area_of_use').value;
            var roomNumber = document.getElementById('modal_room_number').value;
            var department = document.getElementById('modal_department').value;
            var courtType = document.getElementById('modal_court_type').value;
            var courtPurpose = document.getElementById('modal_court_purpose').value;
            var eventDate = document.getElementById('modal_event_date').value;
            var startTime = document.getElementById('modal_start_time').value;
            var endTime = document.getElementById('modal_end_time').value;
            var description = document.getElementById('modal_description').value;

            // Build location from selected options
            var location = '';
            if (category === 'Area Use' && areaOfUse) {
                location = areaOfUse;
                if (areaOfUse === 'Room' && roomNumber) {
                    location += ' ' + roomNumber;
                } else if (areaOfUse === 'Court' && courtType) {
                    location += ' (' + courtType + ')';
                }
            }

            // Populate preview fields
            document.getElementById('preview_title').textContent = title || 'Not specified';
            document.getElementById('preview_category').textContent = category || 'Not specified';
            document.getElementById('preview_area_of_use').textContent = areaOfUse || 'Not specified';
            document.getElementById('preview_room_number').textContent = roomNumber || 'Not specified';
            document.getElementById('preview_department').textContent = department || 'Not specified';
            document.getElementById('preview_court_type').textContent = courtType || 'Not specified';
            document.getElementById('preview_court_purpose').textContent = courtPurpose || 'Not specified';
            document.getElementById('preview_event_date').textContent = eventDate ? new Date(eventDate).toLocaleDateString() : 'Not specified';
            document.getElementById('preview_start_time').textContent = startTime || 'Not specified';
            document.getElementById('preview_end_time').textContent = endTime || 'Not specified';
            document.getElementById('preview_location').textContent = location || 'Not specified';
            document.getElementById('preview_description').textContent = description || 'Not specified';

            // Show/hide area of use row
            var areaOfUseRow = document.getElementById('preview_area_of_use_row');
            if (category === 'Area Use' && areaOfUse) {
                areaOfUseRow.style.display = 'block';
            } else {
                areaOfUseRow.style.display = 'none';
            }

            // Show/hide room number row
            var roomNumberRow = document.getElementById('preview_room_number_row');
            if (category === 'Area Use' && areaOfUse === 'Room' && roomNumber) {
                roomNumberRow.style.display = 'block';
            } else {
                roomNumberRow.style.display = 'none';
            }

            // Show/hide department row
            var departmentRow = document.getElementById('preview_department_row');
            if (department) {
                departmentRow.style.display = 'block';
            } else {
                departmentRow.style.display = 'none';
            }

            // Show/hide court type row
            var courtTypeRow = document.getElementById('preview_court_type_row');
            if (category === 'Area Use' && areaOfUse === 'Court' && courtType) {
                courtTypeRow.style.display = 'block';
            } else {
                courtTypeRow.style.display = 'none';
            }

            // Show/hide court purpose row
            var courtPurposeRow = document.getElementById('preview_court_purpose_row');
            if (category === 'Area Use' && areaOfUse === 'Court' && courtType && courtPurpose) {
                courtPurposeRow.style.display = 'block';
            } else {
                courtPurposeRow.style.display = 'none';
            }
        });
    }
    
    // Validation function for event request form
    function validateEventRequestForm() {
        // Clear previous errors
        clearValidationErrors();
        
        var errors = [];
        var isValid = true;
        var firstErrorField = null;
        
        // Get form elements
        var title = document.getElementById('modal_title');
        var category = document.getElementById('modal_category');
        var eventDate = document.getElementById('modal_event_date');
        var startTime = document.getElementById('modal_start_time');
        var endTime = document.getElementById('modal_end_time');
        var description = document.getElementById('modal_description');
        
        // Validate Title
        if (!title.value.trim()) {
            showFieldError(title, 'Event title is required');
            errors.push('Title is required');
            isValid = false;
        } else if (title.value.trim().length < 3) {
            showFieldError(title, 'Event title must be at least 3 characters');
            errors.push('Title too short');
            isValid = false;
        } else if (title.value.trim().length > 255) {
            showFieldError(title, 'Event title cannot exceed 255 characters');
            errors.push('Title too long');
            isValid = false;
        }
        
        // Validate Category
        if (!category.value) {
            showFieldError(category, 'Please select a category');
            errors.push('Category is required');
            isValid = false;
        }

        var areaOfUse = document.getElementById('modal_area_of_use');

        // Validate Area of Use (if 'Area Use' is selected)
        if (category.value === 'Area Use' && (!areaOfUse || !areaOfUse.value)) {
            showFieldError(areaOfUse, 'Please select an area of use');
            errors.push('Area of use is required');
            isValid = false;
        }

        // Validate Room Number (if 'Room' is selected in Area of Use)
        var roomNumber = document.getElementById('modal_room_number');
        if (category.value === 'Area Use' && areaOfUse.value === 'Room' && !roomNumber.value) {
            showFieldError(roomNumber, 'Please select a room number');
            errors.push('Room number is required');
            isValid = false;
        }

        // Validate Department (if 'Room' is selected in Area of Use and education level is tertiary)
        var department = document.getElementById('modal_department');
        var educationLevel = document.getElementById('modal_education_level');
        var educationLevelValue = educationLevel ? educationLevel.value : 'faculty';
        if (category.value === 'Area Use' && areaOfUse.value === 'Room' && educationLevelValue !== 'shs' && !department.value) {
            showFieldError(department, 'Please select a department');
            errors.push('Department is required');
            isValid = false;
        }

        // Validate Court Type (if 'Court' is selected in Area of Use)
        var courtType = document.getElementById('modal_court_type');
        if (category.value === 'Area Use' && areaOfUse.value === 'Court' && !courtType.value) {
            showFieldError(courtType, 'Please select a court type');
            errors.push('Court type is required');
            isValid = false;
        }

        // Validate Court Purpose (if Court Type is selected)
        var courtPurpose = document.getElementById('modal_court_purpose');
        if (category.value === 'Area Use' && areaOfUse.value === 'Court' && courtType.value && !courtPurpose.value.trim()) {
            showFieldError(courtPurpose, 'Please enter the purpose for court use');
            errors.push('Court purpose is required');
            isValid = false;
        }

        // Validate Department (if Academic Court is selected)
        var department = document.getElementById('modal_department');
        if (category.value === 'Area Use' && areaOfUse.value === 'Court' && courtType.value === 'Academic' && !department.value) {
            showFieldError(department, 'Please select a department for academic court use');
            errors.push('Department is required for academic court requests');
            isValid = false;
        }

        // Validate AVR Selection (if AVR is selected in Area of Use)
        var avrSelection = document.getElementById('modal_avr_selection');
        if (category.value === 'Area Use' && areaOfUse.value === 'AVR' && !avrSelection.value) {
            showFieldError(avrSelection, 'Please select an AVR');
            errors.push('AVR selection is required');
            isValid = false;
        }

        // Validate AVR Request Category (if AVR Selection is selected)
        var avrRequestCategory = document.getElementById('modal_avr_request_category');
        if (category.value === 'Area Use' && areaOfUse.value === 'AVR' && avrSelection.value && !avrRequestCategory.value) {
            showFieldError(avrRequestCategory, 'Please select a request category');
            errors.push('AVR request category is required');
            isValid = false;
        }

        // Validate Department (if Academic AVR is selected)
        if (category.value === 'Area Use' && areaOfUse.value === 'AVR' && avrRequestCategory.value === 'Academic' && !department.value) {
            showFieldError(department, 'Please select a department for academic AVR use');
            errors.push('Department is required for academic AVR requests');
            isValid = false;
        }
        
        // Validate Event Date
        if (!eventDate.value) {
            showFieldError(eventDate, 'Event date is required');
            errors.push('Date is required');
            isValid = false;
        } else {
            var selectedDate = new Date(eventDate.value);
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                showFieldError(eventDate, 'Event date cannot be in the past');
                errors.push('Date cannot be in the past');
                isValid = false;
            }
        }
        
        // Validate Start Time
        if (!startTime.value) {
            showFieldError(startTime, 'Start time is required');
            errors.push('Start time is required');
            isValid = false;
        }
        
        // Validate End Time
        if (!endTime.value) {
            showFieldError(endTime, 'End time is required');
            errors.push('End time is required');
            isValid = false;
        }
        
        // Validate End Time is after Start Time
        if (startTime.value && endTime.value) {
            if (endTime.value <= startTime.value) {
                showFieldError(endTime, 'End time must be after start time');
                errors.push('End time must be after start time');
                isValid = false;
            }
        }

        // Validate Description
        if (!description.value.trim()) {
            showFieldError(description, 'Description is required');
            errors.push('Description is required');
            isValid = false;
        } else if (description.value.trim().length < 10) {
            showFieldError(description, 'Description must be at least 10 characters');
            errors.push('Description too short');
            isValid = false;
        }

        // Check court availability before submission if court is selected
        var areaOfUse = document.getElementById('modal_area_of_use');
        if (areaOfUse && areaOfUse.value === 'Court') {
            var courtAvailabilityMsg = document.getElementById('modal_court_availability_message');
            if (courtAvailabilityMsg && courtAvailabilityMsg.classList.contains('alert-danger')) {
                showFieldError(areaOfUse, 'The court is not available for the chosen time. Please select a different time.');
                errors.push('Court not available');
                isValid = false;
            }
        }

        // Check AVR availability before submission if AVR is selected
        if (areaOfUse && areaOfUse.value === 'AVR') {
            var avrAvailabilityMsg = document.getElementById('modal_avr_availability_message');
            if (avrAvailabilityMsg && avrAvailabilityMsg.classList.contains('alert-danger')) {
                showFieldError(areaOfUse, 'The AVR is not available for the chosen time. Please select a different time.');
                errors.push('AVR not available');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    // Show field error
    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        var feedbackDiv = field.nextElementSibling;
        if (feedbackDiv && feedbackDiv.classList.contains('invalid-feedback')) {
            feedbackDiv.textContent = message;
        } else {
            var div = document.createElement('div');
            div.className = 'invalid-feedback';
            div.textContent = message;
            field.parentNode.insertBefore(div, field.nextSibling);
        }
    }
    
    // Clear all validation errors
    function clearValidationErrors() {
        var form = document.getElementById('eventRequestForm');
        if (form) {
            var invalidFields = form.querySelectorAll('.is-invalid');
            invalidFields.forEach(function(field) {
                field.classList.remove('is-invalid');
            });
        }
        // Remove error alert if exists
        var errorAlert = document.getElementById('eventValidationError');
        if (errorAlert) {
            errorAlert.remove();
        }
    }
    
    // Add form submit handler to prevent submission if validation fails
    var eventRequestForm = document.getElementById('eventRequestForm');
    if (eventRequestForm) {
        eventRequestForm.addEventListener('submit', function(e) {
            if (!validateEventRequestForm()) {
                e.preventDefault();
                return false;
            }
        });
    }
    
    // Show error alert
    function showErrorAlert(message) {
        var modalBody = document.querySelector('#eventRequestModal .modal-body');
        if (modalBody) {
            var existingAlert = document.getElementById('eventValidationError');
            if (existingAlert) {
                existingAlert.remove();
            }
            var alertDiv = document.createElement('div');
            alertDiv.id = 'eventValidationError';
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> <strong>Validation Error:</strong> ' + message + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            modalBody.insertBefore(alertDiv, modalBody.firstChild);
        }
    }
    
    // Preview button click handler
    var previewBtn = document.getElementById('previewBtn');
    if (previewBtn) {
        previewBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Run validation first
            if (!validateEventRequestForm()) {
                return;
            }

            // Get form values
            var title = document.getElementById('modal_title').value;
            var category = document.getElementById('modal_category').value;
            var areaOfUse = document.getElementById('modal_area_of_use').value;
            var roomNumber = document.getElementById('modal_room_number').value;
            var courtType = document.getElementById('modal_court_type').value;
            var courtPurpose = document.getElementById('modal_court_purpose').value;
            var department = document.getElementById('modal_department').value;
            var educationLevel = document.getElementById('modal_education_level').value;
            var avrSelectionValue = document.getElementById('modal_avr_selection').value;
            var avrRequestCategoryValue = document.getElementById('modal_avr_request_category').value;
            var eventDate = document.getElementById('modal_event_date').value;
            var startTime = document.getElementById('modal_start_time').value;
            var endTime = document.getElementById('modal_end_time').value;
            var description = document.getElementById('modal_description').value;
            var location = '';

            if (category === 'Area Use' && areaOfUse) {
                location = areaOfUse;
                if (areaOfUse === 'Room' && roomNumber) {
                    location += ' ' + roomNumber;
                } else if (areaOfUse === 'Court' && courtType) {
                    location += ' (' + courtType + ')';
                } else if (areaOfUse === 'AVR' && avrSelectionValue) {
                    location += ' (' + avrSelectionValue + ')';
                }
            }

            var dateDisplay = eventDate ? new Date(eventDate).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : '';

            var formatTime = function(time) {
                if (!time) return '';
                var parts = time.split(':');
                var hours = parseInt(parts[0], 10);
                var minutes = parts[1];
                var period = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;
                return hours + ':' + minutes + ' ' + period;
            };

            // Set the hidden location field
            document.getElementById('modal_location').value = location;

            document.getElementById('preview_title').textContent = title || '-';
            document.getElementById('preview_category').textContent = category || '-';
            document.getElementById('preview_education_level').textContent = educationLevel === 'shs' ? 'Senior High School' : educationLevel === 'faculty' ? 'Faculty' : educationLevel === 'staff' ? 'Staff' : educationLevel === 'maintenance' ? 'Maintenance' : 'Tertiary';
            document.getElementById('preview_area_of_use').textContent = areaOfUse || 'Not specified';
            document.getElementById('preview_room_number').textContent = roomNumber || 'Not specified';
            document.getElementById('preview_department').textContent = department || 'Not specified';
            document.getElementById('preview_court_type').textContent = courtType || 'Not specified';
            document.getElementById('preview_court_purpose').textContent = courtPurpose || 'Not specified';
            document.getElementById('preview_avr_selection').textContent = avrSelectionValue || 'Not specified';
            document.getElementById('preview_avr_request_category').textContent = avrRequestCategoryValue || 'Not specified';
            document.getElementById('preview_event_date').textContent = dateDisplay || '-';
            document.getElementById('preview_start_time').textContent = formatTime(startTime);
            document.getElementById('preview_end_time').textContent = formatTime(endTime);
            document.getElementById('preview_location').textContent = location || 'Not specified';
            document.getElementById('preview_description').textContent = description || '-';

            document.getElementById('preview_area_of_use_row').style.display = (category === 'Area Use' && areaOfUse) ? 'flex' : 'none';
            document.getElementById('preview_room_number_row').style.display = (category === 'Area Use' && areaOfUse === 'Room' && roomNumber) ? 'flex' : 'none';
            document.getElementById('preview_department_row').style.display = department ? 'flex' : 'none';
            document.getElementById('preview_court_type_row').style.display = (category === 'Area Use' && areaOfUse === 'Court' && courtType) ? 'flex' : 'none';
            document.getElementById('preview_court_purpose_row').style.display = (category === 'Area Use' && areaOfUse === 'Court' && courtPurpose) ? 'flex' : 'none';
            document.getElementById('preview_avr_selection_row').style.display = (category === 'Area Use' && areaOfUse === 'AVR' && avrSelectionValue) ? 'flex' : 'none';
            document.getElementById('preview_avr_request_category_row').style.display = (category === 'Area Use' && areaOfUse === 'AVR' && avrRequestCategoryValue) ? 'flex' : 'none';

            // Set approval recipients based on education level, department, court type, and AVR category
            var approvalRecipients = 'Chosen Department on the selection, Academic Head, Building Admin, and School Administrator';

            // SHS approval flow: Principal Assistant → Academic Head → School Administrator
            if (educationLevel === 'shs') {
                approvalRecipients = 'Principal Assistant, Academic Head, and School Administrator';
            } else if (category === 'Area Use' && areaOfUse === 'Court' && courtType === 'Non-academic') {
                // Tertiary non-academic court requests
                approvalRecipients = 'Building Admin and School Administrator';
            } else if (category === 'Area Use' && areaOfUse === 'AVR' && avrRequestCategoryValue === 'Non-academic') {
                // Tertiary non-academic AVR requests (same as non-academic court)
                approvalRecipients = 'Building Admin and School Administrator';
            } else if (department) {
                // Tertiary with department (Room, Academic Court, or Academic AVR)
                var deptHead = '';
                switch(department) {
                    case 'GE':
                        deptHead = 'GE Department Head';
                        break;
                    case 'ICT':
                        deptHead = 'ICT Department Head';
                        break;
                    case 'Business Management':
                        deptHead = 'Business Management Department Head';
                        break;
                    case 'THM':
                        deptHead = 'THM Department Head';
                        break;
                }
                if (deptHead) {
                    approvalRecipients = deptHead + ', Academic Head, Building Admin, and School Administrator';
                }
            }
            document.getElementById('approval_recipients').textContent = approvalRecipients;

            var requestModalEl = document.getElementById('eventRequestModal');
            var previewModalEl = document.getElementById('eventPreviewModal');

            if (requestModalEl && previewModalEl) {
                bootstrap.Modal.getOrCreateInstance(requestModalEl).hide();
                previewModalEl.addEventListener('hidden.bs.modal', function reopenRequestModalOnce() {
                    previewModalEl.removeEventListener('hidden.bs.modal', reopenRequestModalOnce);
                });
                bootstrap.Modal.getOrCreateInstance(previewModalEl).show();
            }
        });
    }

    // Handle Edit button in preview modal
    var editEventBtn = document.getElementById('editEventBtn');
    if (editEventBtn) {
        editEventBtn.addEventListener('click', function() {
            var previewModalEl = document.getElementById('eventPreviewModal');
            var requestModalEl = document.getElementById('eventRequestModal');
            
            if (previewModalEl && requestModalEl) {
                bootstrap.Modal.getOrCreateInstance(previewModalEl).hide();
                setTimeout(function() {
                    bootstrap.Modal.getOrCreateInstance(requestModalEl).show();
                }, 300);
            }
        });
    }

    // AVR Selection change handler for modal
    var modalAvrSelection = document.getElementById('modal_avr_selection');
    if (modalAvrSelection) {
        modalAvrSelection.addEventListener('change', function() {
            var avrRequestCategoryContainer = document.getElementById('modal_avr_request_category_container');
            var avrRequestCategorySelect = document.getElementById('modal_avr_request_category');

            if (this.value) {
                avrRequestCategoryContainer.style.display = 'block';
                avrRequestCategorySelect.setAttribute('required', 'required');
            } else {
                avrRequestCategoryContainer.style.display = 'none';
                avrRequestCategorySelect.removeAttribute('required');
                avrRequestCategorySelect.value = '';
            }
            updatePreviewButtonState();
        });
    }

    // AVR Request Category change handler - show department for Academic
    var modalAvrRequestCategory = document.getElementById('modal_avr_request_category');
    if (modalAvrRequestCategory) {
        modalAvrRequestCategory.addEventListener('change', function() {
            var departmentContainer = document.getElementById('modal_department_container');
            var departmentSelect = document.getElementById('modal_department');

            if (this.value === 'Academic') {
                departmentContainer.style.display = 'block';
                departmentSelect.setAttribute('required', 'required');
            } else {
                departmentContainer.style.display = 'none';
                departmentSelect.removeAttribute('required');
                departmentSelect.value = '';
            }
            updatePreviewButtonState();
            updateApprovalRecipients();
        });
    }

    // Update approval recipients on department or court type change
    var departmentSelect = document.getElementById('modal_department');
    var courtTypeSelect = document.getElementById('modal_court_type');

    function updateApprovalRecipients() {
        var category = document.getElementById('modal_category').value;
        var areaOfUse = document.getElementById('modal_area_of_use').value;
        var courtType = courtTypeSelect ? courtTypeSelect.value : '';
        var avrRequestCategory = document.getElementById('modal_avr_request_category') ? document.getElementById('modal_avr_request_category').value : '';
        var department = departmentSelect ? departmentSelect.value : '';
        var educationLevel = document.getElementById('modal_education_level').value;
        var approvalRecipients = 'Chosen Department on the selection, Academic Head, Building Admin, and School Administrator';

        // SHS approval flow: Principal Assistant → Academic Head → School Administrator
        if (educationLevel === 'shs') {
            approvalRecipients = 'Principal Assistant, Academic Head, and School Administrator';
        } else if (category === 'Area Use' && areaOfUse === 'Court' && courtType === 'Non-academic') {
            // Tertiary non-academic court requests
            approvalRecipients = 'Building Admin and School Administrator';
        } else if (category === 'Area Use' && areaOfUse === 'AVR' && avrRequestCategory === 'Non-academic') {
            // Tertiary non-academic AVR requests (same as non-academic court)
            approvalRecipients = 'Building Admin and School Administrator';
        } else if (department) {
            // Tertiary with department (Room, Academic Court, or Academic AVR)
            var deptHead = '';
            switch(department) {
                case 'GE':
                    deptHead = 'GE Department Head';
                    break;
                case 'ICT':
                    deptHead = 'ICT Department Head';
                    break;
                case 'Business Management':
                    deptHead = 'Business Management Department Head';
                    break;
                case 'THM':
                    deptHead = 'THM Department Head';
                    break;
            }
            if (deptHead) {
                approvalRecipients = deptHead + ', Academic Head, Building Admin, and School Administrator';
            }
        }
        document.getElementById('approval_recipients').textContent = approvalRecipients;
    }

    if (departmentSelect) {
        departmentSelect.addEventListener('change', updateApprovalRecipients);
    }
    if (courtTypeSelect) {
        courtTypeSelect.addEventListener('change', updateApprovalRecipients);
    }
    
    // Add listener for AVR request category changes
    var avrRequestCategorySelect = document.getElementById('modal_avr_request_category');
    if (avrRequestCategorySelect) {
        avrRequestCategorySelect.addEventListener('change', updateApprovalRecipients);
    }
    
    // Add listener for education level changes
    var educationLevelSelect = document.getElementById('modal_education_level');
    if (educationLevelSelect) {
        educationLevelSelect.addEventListener('change', updateApprovalRecipients);
    }

    // Materials/Equipment dynamic rows for modal
    let modalMaterialRowCount = 1;

    window.addModalMaterialRow = function() {
        var table = document.getElementById('modalMaterialsTable');
        if (table) {
            var tbody = table.getElementsByTagName('tbody')[0];
            var newRow = tbody.insertRow();
            newRow.innerHTML = `
                <td><input type="number" class="form-control" name="materials[${modalMaterialRowCount}][qty]" min="1" placeholder="1"></td>
                <td><input type="text" class="form-control" name="materials[${modalMaterialRowCount}][item]" placeholder="e.g., Projector, Chair, etc."></td>
                <td><input type="text" class="form-control" name="materials[${modalMaterialRowCount}][purpose]" placeholder="e.g., For presentation"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeModalMaterialRow(this)"><i class="fas fa-times"></i></button></td>
            `;
            modalMaterialRowCount++;
        }
    };

    window.removeModalMaterialRow = function(button) {
        var table = document.getElementById('modalMaterialsTable');
        if (table) {
            var tbody = table.getElementsByTagName('tbody')[0];
            if (tbody.rows.length > 1) {
                button.closest('tr').remove();
            }
        }
    };
});
    
    // Auto-open modal if there are validation errors
    @if($errors->any())
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('eventRequestModal'));
        modal.show();
    });
    @endif
    
</script>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
    // ── Theme System ──────────────────────────────────────────────
    const THEME_KEY = 'campfix_theme';
    const userDbTheme = '{{ auth()->check() ? auth()->user()->theme : "light" }}';

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        // Update button active states
        const lightBtn = document.getElementById('theme-light-btn');
        const darkBtn  = document.getElementById('theme-dark-btn');
        if (lightBtn && darkBtn) {
            lightBtn.style.borderColor = theme === 'light' ? '#4f6ef7' : '#dee2e6';
            darkBtn.style.borderColor  = theme === 'dark'  ? '#4f6ef7' : '#dee2e6';
        }
    }

    function setTheme(theme) {
        applyTheme(theme);
        localStorage.setItem(THEME_KEY, theme);
        // Persist to server
        fetch('{{ route("settings.theme") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ theme }),
        });
    }

    // Apply on load — DB value takes priority, fallback to localStorage
    (function() {
        const saved = userDbTheme || localStorage.getItem(THEME_KEY) || 'light';
        applyTheme(saved);
    })();

    document.addEventListener('DOMContentLoaded', function() {
        // Re-apply to update button states after DOM is ready
        const saved = userDbTheme || localStorage.getItem(THEME_KEY) || 'light';
        applyTheme(saved);
    });
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const userTimezone = body.dataset.userTimezone || 'Asia/Shanghai';
        const userLocale = body.dataset.userLocale === 'tl' ? 'fil-PH' : 'en-US';
        const userDateFormat = body.dataset.userDateFormat || 'Y-m-d';

        const buildIntlOptions = (hasTime) => {
            switch (userDateFormat) {
                case 'd/m/Y':
                    return hasTime
                        ? { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }
                        : { day: '2-digit', month: '2-digit', year: 'numeric' };
                case 'm/d/Y':
                    return hasTime
                        ? { month: '2-digit', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }
                        : { month: '2-digit', day: '2-digit', year: 'numeric' };
                case 'Y-m-d':
                default:
                    return hasTime
                        ? { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' }
                        : { year: 'numeric', month: '2-digit', day: '2-digit' };
            }
        };

        document.querySelectorAll('[data-user-date]').forEach(function (element) {
            const rawValue = element.getAttribute('data-user-date');
            if (!rawValue) {
                return;
            }

            const parsedDate = new Date(rawValue);
            if (Number.isNaN(parsedDate.getTime())) {
                return;
            }

            const hasTime = element.getAttribute('data-has-time') === '1';
            const formatter = new Intl.DateTimeFormat(userLocale, {
                ...buildIntlOptions(hasTime),
                timeZone: userTimezone,
                hour12: true
            });

            element.textContent = formatter.format(parsedDate);
        });
    });
</script>
@yield('scripts')

<!-- Security Password Modal for User Management -->
<div class="modal fade" id="securityPasswordModal" tabindex="-1" aria-labelledby="securityPasswordModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="securityPasswordModalLabel">
                    <i class="fas fa-lock text-warning me-2"></i>Security Check
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">Enter your password to access User Management.</p>
                <div id="securityPasswordError" class="alert alert-danger py-2 small d-none"></div>
                <div class="input-group">
                    <input type="password" id="securityPasswordInput" class="form-control" placeholder="Your password" autocomplete="current-password">
                    <button class="btn btn-outline-secondary" type="button" id="toggleSecurityPassword">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" id="securityPasswordSubmit">
                    <i class="fas fa-unlock me-1"></i>Verify
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _securityRedirectUrl = '';

function requirePasswordToAccess(url, event) {
    event.preventDefault();
    _securityRedirectUrl = url;
    const input = document.getElementById('securityPasswordInput');
    const error = document.getElementById('securityPasswordError');
    input.value = '';
    error.classList.add('d-none');
    error.textContent = '';
    const modal = new bootstrap.Modal(document.getElementById('securityPasswordModal'));
    modal.show();
    setTimeout(() => input.focus(), 400);
}

document.addEventListener('DOMContentLoaded', function () {
    // Submit on Enter key
    document.getElementById('securityPasswordInput').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') document.getElementById('securityPasswordSubmit').click();
    });

    // Toggle password visibility
    document.getElementById('toggleSecurityPassword').addEventListener('click', function () {
        const input = document.getElementById('securityPasswordInput');
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });

    // Verify button
    document.getElementById('securityPasswordSubmit').addEventListener('click', function () {
        const password = document.getElementById('securityPasswordInput').value;
        const error = document.getElementById('securityPasswordError');
        const btn = this;

        if (!password) {
            error.textContent = 'Please enter your password.';
            error.classList.remove('d-none');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verifying...';

        fetch('/verify-access-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ password })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('securityPasswordModal')).hide();
                window.location.href = _securityRedirectUrl;
            } else {
                error.textContent = data.message || 'Incorrect password.';
                error.classList.remove('d-none');
                document.getElementById('securityPasswordInput').value = '';
                document.getElementById('securityPasswordInput').focus();
            }
        })
        .catch(() => {
            error.textContent = 'An error occurred. Please try again.';
            error.classList.remove('d-none');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-unlock me-1"></i>Verify';
        });
    });
});
</script>
</body>
</html>
