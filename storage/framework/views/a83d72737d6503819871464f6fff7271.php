<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
<title>CampFix</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="<?php echo e(asset('css/app.css')); ?>" rel="stylesheet">

<?php echo $__env->yieldContent('styles'); ?>

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

/* Reduce main content padding */
.content > *:not(.top-header) {
    padding: 20px !important;
}

/* Narrow sidebar to fit nav text */
.sidebar {
    width: 200px !important;
}
.content {
    margin-left: 200px !important;
    width: calc(100% - 200px) !important;
}
.sidebar a.active {
    border-radius: 20px !important;
    margin-left: 10px !important;
    margin-right: 10px !important;
}
.sidebar a.active::after,
.sidebar a.active::before {
    display: none !important;
}

/* Nav dropdown */
.nav-dropdown-toggle {
    display: flex !important;
    align-items: center;
    cursor: pointer;
}
.nav-dropdown-arrow {
    font-size: 11px;
    transition: transform 0.2s;
    margin-left: auto;
    margin-right: 10px;
}
.nav-dropdown.open .nav-dropdown-arrow {
    transform: rotate(180deg);
}
.nav-dropdown-menu {
    display: none;
    flex-direction: column;
    background: rgba(0,0,0,0.15);
    border-radius: 8px;
    margin: 2px 8px 4px 8px;
    overflow: hidden;
}
.nav-dropdown.open .nav-dropdown-menu {
    display: flex;
}
.nav-dropdown-menu a {
    color: rgba(255,255,255,0.85) !important;
    border-radius: 6px !important;
    margin: 1px 4px !important;
}
.nav-dropdown-menu a:hover,
.nav-dropdown-menu a.active {
    background: rgba(255,255,255,0.15) !important;
    color: #fff !important;
}

/* Fix modal scrolling - override app.css max-height */
#modalMaterialsTable {
    min-width: 0 !important;
}
.modal-dialog-scrollable .modal-content {
    max-height: 90vh !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
}
.modal-dialog-scrollable form {
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    flex: 1 !important;
    min-height: 0 !important;
}
.modal-dialog-scrollable .modal-body {
    overflow-y: auto !important;
    flex: 1 !important;
    min-height: 0 !important;
}

@media (max-width: 991px) {
    .sidebar {
        transform: translateX(-100%) !important;
        width: 200px !important;
    }
    .sidebar.show {
        transform: translateX(0) !important;
    }
    .content {
        margin-left: 0 !important;
        width: 100% !important;
    }
}

/* Make asterisks red for required fields */
.required-asterisk {
    color: #dc3545 !important;
    font-weight: bold;
}

/* Fallback styling */
.text-danger {
    color: #dc3545 !important;
}
</style>

<style>
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
[data-theme="dark"] .sidebar { background: #0a1628 !important; }
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
[data-theme="dark"] .user-dropdown-top span { color: #e0e0e0 !important; }
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

<script>
// Make all asterisks in labels red
document.addEventListener('DOMContentLoaded', function() {
    function makeAsterisksRed() {
        // Find all labels and form-labels
        const labels = document.querySelectorAll('label, .form-label');
        
        labels.forEach(function(label) {
            // Check if the label contains an asterisk
            if (label.textContent.includes('*')) {
                // Replace asterisk with red-colored span
                label.innerHTML = label.innerHTML.replace(/\*/g, '<span class="required-asterisk">*</span>');
            }
        });
    }
    
    // Run initially
    makeAsterisksRed();
    
    // Run again after any dynamic content is loaded (for modals, AJAX, etc.)
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                // Check if any new labels were added
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const newLabels = node.querySelectorAll ? node.querySelectorAll('label, .form-label') : [];
                        newLabels.forEach(function(label) {
                            if (label.textContent.includes('*')) {
                                label.innerHTML = label.innerHTML.replace(/\*/g, '<span class="required-asterisk">*</span>');
                            }
                        });
                    }
                });
            }
        });
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
</script>
</head>
<body data-user-timezone="<?php echo e(auth()->check() ? auth()->user()->timezone : config('app.timezone')); ?>" data-user-locale="<?php echo e(app()->getLocale()); ?>" data-user-date-format="<?php echo e($userDateFormat ?? 'Y-m-d'); ?>">

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" onclick="toggleSidebar()"></div>

<!-- Sidebar/Nav -->
<div class="sidebar">
    <div class="sidebar-header">
        <img src="<?php echo e(asset('Campfix/Images/logo.png')); ?>" alt="CampFix Logo">
    </div>

    <div class="sidebar-content">
        <?php if(auth()->guard()->check()): ?>

        <a href="/dashboard" class="<?php echo e(Request::is('dashboard') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
            <i class="fas fa-home"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Home' : 'Home'); ?>

        </a>

        
        <?php if(auth()->user()->role !== 'mis' && auth()->user()->role !== 'maintenance'): ?>
            <a href="<?php echo e(route('concerns.my')); ?>" class="<?php echo e(Request::is('my-concerns') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-clipboard-list"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Aking Mga Concern' : 'My Concerns'); ?>

            </a>

            
            <?php if(auth()->user()->role === 'faculty'): ?>
                
                <div class="nav-dropdown <?php echo e(Request::is('my-events') || Request::is('events-calendar') ? 'open' : ''); ?>">
                    <a href="#" class="nav-dropdown-toggle <?php echo e(Request::is('my-events') || Request::is('events-calendar') ? 'active' : ''); ?>"
                       data-nav-toggle style="padding-top:8px;padding-bottom:8px;">
                        <i class="fas fa-calendar-alt"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mga Event' : 'Events'); ?>

                        <i class="fas fa-chevron-down nav-dropdown-arrow ms-auto"></i>
                    </a>
                    <div class="nav-dropdown-menu">
                        <a href="<?php echo e(route('events.my')); ?>" class="<?php echo e(Request::is('my-events') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                            <i class="fas fa-calendar me-1"></i> My Events
                        </a>
                        <a href="<?php echo e(route('events.calendar')); ?>" class="<?php echo e(Request::is('events-calendar') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                            <i class="fas fa-calendar-check me-1"></i> Upcoming Events
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        <?php elseif(auth()->user()->role === 'mis'): ?>
            <a href="<?php echo e(route('concerns.my')); ?>" class="<?php echo e(Request::is('my-concerns') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-clipboard-list"></i> My Concerns
            </a>
        <?php endif; ?>

        

        
        <?php if(auth()->user()->role === 'mis'): ?>

            <a href="/admin/mis-tasks" class="<?php echo e(Request::is('admin/mis-tasks') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-tasks"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Gawain' : 'Task'); ?>

            </a>

            <a href="/admin/users" class="<?php echo e(Request::is('admin/users') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-users"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mga Gumagamit' : 'Users'); ?>

            </a>

            <a href="/admin/logs" class="<?php echo e(Request::is('admin/logs') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-history"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Audit Logs' : 'Audit Logs'); ?>

            </a>

            <a href="/settings" class="<?php echo e(Request::is('settings') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-cog"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mga Setting' : 'Settings'); ?>

            </a>

        <?php endif; ?>

        
        <?php if(auth()->user()->role === 'building_admin'): ?>
            
            <div class="nav-dropdown <?php echo e(Request::is('admin/reports*') || Request::is('admin/analytics*') ? 'open' : ''); ?>">
                <a href="#" class="nav-dropdown-toggle <?php echo e(Request::is('admin/reports*') || Request::is('admin/analytics*') ? 'active' : ''); ?>"
                   data-nav-toggle style="padding-top:8px;padding-bottom:8px;">
                    <i class="fas fa-chart-bar"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mga Ulat' : 'Reports'); ?>

                    <i class="fas fa-chevron-down nav-dropdown-arrow ms-auto"></i>
                </a>
                <div class="nav-dropdown-menu">
                    <a href="/admin/reports" class="<?php echo e(Request::is('admin/reports') && !Request::is('admin/reports*view=analytics*') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                        <i class="fas fa-file-alt me-1"></i> Reports
                    </a>
                    <a href="<?php echo e(route('admin.analytics')); ?>" class="<?php echo e(Request::is('admin/analytics*') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                        <i class="fas fa-chart-line me-1"></i> Analytics
                    </a>
                </div>
            </div>

            
            <div class="nav-dropdown <?php echo e(Request::is('my-events') || Request::is('events-calendar') || Request::is('admin/events') ? 'open' : ''); ?>">
                <a href="#" class="nav-dropdown-toggle <?php echo e(Request::is('my-events') || Request::is('events-calendar') || Request::is('admin/events') ? 'active' : ''); ?>"
                   data-nav-toggle style="padding-top:8px;padding-bottom:8px;">
                    <i class="fas fa-calendar-alt"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mga Event' : 'Events'); ?>

                    <i class="fas fa-chevron-down nav-dropdown-arrow ms-auto"></i>
                </a>
                <div class="nav-dropdown-menu">
                    <a href="/admin/events" class="<?php echo e(Request::is('admin/events') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                        <i class="fas fa-calendar-alt me-1"></i> Pending Approval
                    </a>
                    <a href="<?php echo e(route('events.my')); ?>" class="<?php echo e(Request::is('my-events') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                        <i class="fas fa-calendar me-1"></i> My Events
                    </a>
                    <a href="<?php echo e(route('events.calendar')); ?>" class="<?php echo e(Request::is('events-calendar') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                        <i class="fas fa-calendar-check me-1"></i> Upcoming Events
                    </a>
                </div>
            </div>

            <a href="<?php echo e(route('admin.management')); ?>" class="<?php echo e(Request::is('admin/management*') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-tools"></i> Management
            </a>

            <a href="/admin/logs" class="<?php echo e(Request::is('admin/logs') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-history"></i> Audit Logs
            </a>
        <?php endif; ?>

        
        <?php if(in_array(auth()->user()->role, ['school_admin', 'academic_head', 'program_head', 'principal_assistant'])): ?>
            <div class="nav-dropdown <?php echo e(Request::is('my-events') || Request::is('events-calendar') ? 'open' : ''); ?>">
                <a href="#" class="nav-dropdown-toggle <?php echo e(Request::is('my-events') || Request::is('events-calendar') ? 'active' : ''); ?>"
                   data-nav-toggle style="padding-top:8px;padding-bottom:8px;">
                    <i class="fas fa-calendar-alt"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mga Event' : 'Events'); ?>

                    <i class="fas fa-chevron-down nav-dropdown-arrow ms-auto"></i>
                </a>
                <div class="nav-dropdown-menu">
                    <a href="<?php echo e(route('events.my')); ?>" class="<?php echo e(Request::is('my-events') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                        <i class="fas fa-calendar me-1"></i> My Events
                    </a>
                    <a href="<?php echo e(route('events.calendar')); ?>" class="<?php echo e(Request::is('events-calendar') ? 'active' : ''); ?>" style="padding-left:36px;padding-top:6px;padding-bottom:6px;font-size:13px;">
                        <i class="fas fa-calendar-check me-1"></i> Upcoming Events
                    </a>
                </div>
            </div>
        <?php endif; ?>

        
        <?php if(auth()->user()->role !== 'mis'): ?>
            <a href="/settings" class="<?php echo e(Request::is('settings') ? 'active' : ''); ?>" style="padding-top:8px;padding-bottom:8px;">
                <i class="fas fa-cog"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mga Setting' : 'Settings'); ?>

            </a>
        <?php endif; ?>

        <?php endif; ?>
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
            <?php if (! empty(trim($__env->yieldContent('page_title')))): ?>
                <div class="header-page-title"><?php echo $__env->yieldContent('page_title'); ?></div>
            <?php endif; ?>
        </div>
        <div class="header-right">
            <!-- Notification Bell -->
            <div class="notification-bell" onclick="toggleNotification(event)">
                <i class="fas fa-bell"></i>
            <?php if(isset($unread_count) && $unread_count > 0): ?>
                <span class="notification-badge"><?php echo e($unread_count); ?></span>
            <?php endif; ?>
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <span><?php echo e(app()->getLocale() === 'tl' ? 'Mga Abiso' : 'Notifications'); ?></span>
                    </div>
                    <div class="notification-list">
                        <?php $__empty_1 = true; $__currentLoopData = $notifications ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="notification-item">
                                <a href="<?php echo e(route('notifications.read', $notification->id)); ?>" class="notification-link">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="notification-content">
                                        <p><?php echo e($notification->data['message'] ?? 'Notification'); ?></p>
                                        <small><?php echo e($notification->created_at->diffForHumans()); ?></small>
                                    </div>
                                </a>
                                <form action="<?php echo e(route('notifications.destroy', $notification->id)); ?>" method="POST" class="notification-delete-form" onsubmit="return confirm('Are you sure you want to delete this notification?')">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="notification-delete-btn" title="Delete notification">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="notification-empty">
                                <i class="fas fa-bell-slash"></i>
                                <p><?php echo e(app()->getLocale() === 'tl' ? 'Walang abiso' : 'No notifications'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- User Dropdown -->
            <?php if(auth()->guard()->check()): ?>
            <div class="user-dropdown-top" onclick="toggleDropdown(event)" style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <span style="font-weight:700;font-size:14px;color:var(--header-text,#1e293b);white-space:nowrap;max-width:160px;overflow:hidden;text-overflow:ellipsis;"><?php echo e(auth()->user()->name); ?></span>
                <div class="user-icon" style="pointer-events:none;">
                    <?php if(auth()->user()->profile_picture): ?>
                        <img src="<?php echo e(asset('storage/' . auth()->user()->profile_picture)); ?>?t=<?php echo e(time()); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                    <?php else: ?>
                        <?php echo e(substr(auth()->user()->name, 0, 1)); ?>

                    <?php endif; ?>
                </div>
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <div class="dropdown-user-name"><?php echo e(auth()->user()->name); ?></div>
                        <div class="dropdown-user-email"><?php echo e(auth()->user()->email); ?></div>
                    </div>
                    <hr class="dropdown-divider">

                    
                    <a href="<?php echo e(route('profile.index')); ?>" class="dropdown-item" style="display:flex;align-items:center;gap:8px;padding:8px 16px;text-decoration:none;color:inherit;font-size:14px;">
                        <i class="fas fa-user-circle" style="width:16px;text-align:center;"></i> Profile Management
                    </a>

                    
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
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="dropdown-item logout" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
                            <i class="fas fa-sign-out-alt"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Mag-sign Out' : 'Sign Out'); ?>

                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php echo $__env->yieldContent('content'); ?>

    <?php if(auth()->guard()->check()): ?>

    <?php endif; ?>

</div>

<?php if(auth()->guard()->check()): ?>
<!-- Event Request Modal for Faculty -->
<div class="modal fade" id="eventRequestModal" tabindex="-1" aria-labelledby="eventRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventRequestModalLabel"><i class="fas fa-calendar-plus"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Magsumite ng Event Request' : 'Submit Event Request'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventRequestForm" action="<?php echo e(route('events.store')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="modal_location" name="location" value="">
                <div class="modal-body">
                    <!-- Date and Time Selection First -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="modal_event_date" class="form-label">Date *</label>
                            <input type="date" class="form-control <?php $__errorArgs = ['event_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_event_date" name="event_date"
                                min="<?php echo e(date('Y-m-d')); ?>" required value="<?php echo e(old('event_date')); ?>">
                            <?php $__errorArgs = ['event_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-3">
                            <label for="modal_start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_start_time" name="start_time" required value="<?php echo e(old('start_time')); ?>">
                            <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-3">
                            <label for="modal_end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_end_time" name="end_time" required value="<?php echo e(old('end_time')); ?>">
                            <?php $__errorArgs = ['end_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="modal_request_type" class="form-label">Request Type *</label>
                        <select class="form-select <?php $__errorArgs = ['request_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_request_type" name="request_type" required>
                                <option value="">Select type</option>
                                <option value="Academic">Academic</option>
                                <option value="Non-Academic">Non-Academic</option>
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
                        <!-- Hidden field for category - automatically set to Area Use -->
                        <input type="hidden" name="category" value="Area Use">
                        </div>

                        <div class="row">
                        <div class="col-md-6 mb-3" id="modal_area_of_use_container" style="display: none;">
                            <label for="modal_area_of_use" class="form-label">Location *</label>
                            <select class="form-select <?php $__errorArgs = ['area_of_use'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_area_of_use" name="area_of_use">
                                <option value="">Select area</option>
                                <option value="Room">Room</option>
                                <option value="Court">Court</option>
                                <option value="AVR">AVR</option>
                                <option value="Library">Library</option>
                                <option value="Open Lobby">Open Lobby</option>
                                <option value="Computer Laboratory">Computer Laboratory</option>
                                <option value="Kitchen">Kitchen</option>
                            </select>
                            <?php $__errorArgs = ['area_of_use'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6 mb-3" id="modal_room_number_container" style="display: none;">
                            <label for="modal_room_number" class="form-label">Room Number *</label>
                            <select class="form-select <?php $__errorArgs = ['room_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_room_number" name="room_number">
                                <option value="">Select room</option>
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php for($j = 1; $j <= 11; $j++): ?>
                                        <?php $room = $i . str_pad($j, 2, '0', STR_PAD_LEFT); ?>
                                        <option value="<?php echo e($room); ?>"><?php echo e($room); ?></option>
                                    <?php endfor; ?>
                                <?php endfor; ?>
                                <option value="Suite Room">Suite Room</option>
                                <option value="Kitchen 1">Kitchen 1</option>
                                <option value="Kitchen 2">Kitchen 2</option>
                                <option value="Bar">Bar</option>
                                <option value="M01">M01</option>
                            </select>
                            <?php $__errorArgs = ['room_number'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6 mb-3" id="modal_court_purpose_container" style="display: none;">
                            <label for="modal_court_purpose" class="form-label">Purpose *</label>
                            <input type="text" class="form-control <?php $__errorArgs = ['court_purpose'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_court_purpose" name="court_purpose"
                                placeholder="Describe the purpose for court use" value="<?php echo e(old('court_purpose')); ?>">
                            <?php $__errorArgs = ['court_purpose'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <div class="col-md-6 mb-3" id="modal_avr_selection_container" style="display: none;">
                            <label for="modal_avr_selection" class="form-label">AVR Selection *</label>
                            <select class="form-select <?php $__errorArgs = ['avr_selection'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_avr_selection" name="avr_selection">
                                <option value="">Select AVR</option>
                                <option value="AVR 1">AVR 1</option>
                                <option value="AVR 2">AVR 2</option>
                            </select>
                            <?php $__errorArgs = ['avr_selection'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                        <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" id="modal_description" name="description" 
                            rows="4" placeholder="Describe the event purpose and details..." required maxlength="500" oninput="updateDescCount()"><?php echo e(old('description')); ?></textarea>
                        <div class="d-flex justify-content-end">
                            <small id="desc_char_count" class="text-muted">0 / 500</small>
                        </div>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <div class="invalid-feedback"><?php echo e($message); ?></div>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
                        <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="modalMaterialsTable" style="min-width:0;">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">Qty</th>
                                    <th>Item</th>
                                    <th>Purpose</th>
                                    <th style="width: 40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" class="form-control form-control-sm" name="materials[0][qty]" min="1" placeholder="1"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="materials[0][item]" placeholder="e.g., Projector"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="materials[0][purpose]" placeholder="e.g., For presentation"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeModalMaterialRow(this)"><i class="fas fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="addModalMaterialRow()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo e(app()->getLocale() === 'tl' ? 'Kanselahin' : 'Cancel'); ?></button>
                    <button type="button" class="btn btn-primary" id="previewBtn"><?php echo e(app()->getLocale() === 'tl' ? 'I-preview' : 'Preview'); ?></button>
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
                <h5 class="modal-title" id="eventPreviewModalLabel"><i class="fas fa-eye"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Preview ng Event Request' : 'Event Request Preview'); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 class="border-bottom pb-2 mb-3">Event Details</h6>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Category:</div>
                    <div class="col-md-8" id="preview_category"></div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-4 fw-bold">Request Type:</div>
                    <div class="col-md-8" id="preview_request_type"></div>
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
                <div class="row mb-2" id="preview_court_purpose_row" style="display: none;">
                    <div class="col-md-4 fw-bold">Court Purpose:</div>
                    <div class="col-md-8" id="preview_court_purpose"></div>
                </div>
                <div class="row mb-2" id="preview_avr_selection_row" style="display: none;">
                    <div class="col-md-4 fw-bold">AVR Selection:</div>
                    <div class="col-md-8" id="preview_avr_selection"></div>
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
                    <i class="fas fa-edit"></i> <?php echo e(app()->getLocale() === 'tl' ? 'I-edit' : 'Edit'); ?>

                </button>
                <button type="submit" form="eventRequestForm" class="btn btn-primary" id="submitEventBtn">
                    <i class="fas fa-check"></i> <?php echo e(app()->getLocale() === 'tl' ? 'Isumite para sa Pag-apruba' : 'Submit for Approval'); ?>

                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modalRequestType = document.getElementById('modal_request_type');
    if (modalRequestType) {
        modalRequestType.addEventListener('change', function() {
            var areaOfUseContainer = document.getElementById('modal_area_of_use_container');
            var areaOfUseSelect = document.getElementById('modal_area_of_use');

            // Always show area of use when request type is selected (category is always "Area Use")
            if (this.value) {
                areaOfUseContainer.style.display = 'block';
                areaOfUseSelect.required = true;
            } else {
                areaOfUseContainer.style.display = 'none';
                areaOfUseSelect.required = false;
                areaOfUseSelect.value = '';
                // Reset all dependent fields
                document.getElementById('modal_room_number_container').style.display = 'none';
                document.getElementById('modal_department_container').style.display = 'none';
                document.getElementById('modal_court_type_container').style.display = 'none';
                document.getElementById('modal_court_purpose_container').style.display = 'none';
                document.getElementById('modal_avr_selection_container').style.display = 'none';
                document.getElementById('modal_avr_request_category_container').style.display = 'none';
            }
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
            var courtPurposeInput = document.getElementById('modal_court_purpose');
            var avrSelectionSelect = document.getElementById('modal_avr_selection');

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

            // Get request type to determine if Academic (for department requirement)
            var requestType = document.getElementById('modal_request_type').value;

            if (this.value === 'Room') {
                var isShs = document.getElementById('modal_education_level').value === 'shs';
                roomNumberContainer.style.display = 'block';
                // Show department for Academic requests and non-SHS
                departmentContainer.style.display = (requestType === 'Academic' && !isShs) ? 'block' : 'none';
                courtPurposeContainer.style.display = 'none';
                avrSelectionContainer.style.display = 'none';
                roomNumberSelect.setAttribute('required', 'required');
                if (requestType === 'Academic' && !isShs) { 
                    departmentSelect.setAttribute('required', 'required'); 
                } else { 
                    departmentSelect.removeAttribute('required'); 
                    departmentSelect.value = ''; 
                }
                courtPurposeInput.removeAttribute('required');
                avrSelectionSelect.removeAttribute('required');
                courtPurposeInput.value = '';
                avrSelectionSelect.value = '';
            } else if (this.value === 'Court') {
                roomNumberContainer.style.display = 'none';
                courtPurposeContainer.style.display = 'block';
                avrSelectionContainer.style.display = 'none';
                // Show department for Academic requests
                departmentContainer.style.display = (requestType === 'Academic') ? 'block' : 'none';
                roomNumberSelect.removeAttribute('required');
                courtPurposeInput.setAttribute('required', 'required');
                avrSelectionSelect.removeAttribute('required');
                if (requestType === 'Academic') { 
                    departmentSelect.setAttribute('required', 'required'); 
                } else { 
                    departmentSelect.removeAttribute('required'); 
                    departmentSelect.value = ''; 
                }
                roomNumberSelect.value = '';
                avrSelectionSelect.value = '';
            } else if (this.value === 'AVR') {
                roomNumberContainer.style.display = 'none';
                courtPurposeContainer.style.display = 'none';
                avrSelectionContainer.style.display = 'block';
                // Show department for Academic requests
                departmentContainer.style.display = (requestType === 'Academic') ? 'block' : 'none';
                roomNumberSelect.removeAttribute('required');
                courtPurposeInput.removeAttribute('required');
                avrSelectionSelect.setAttribute('required', 'required');
                if (requestType === 'Academic') { 
                    departmentSelect.setAttribute('required', 'required'); 
                } else { 
                    departmentSelect.removeAttribute('required'); 
                    departmentSelect.value = ''; 
                }
                roomNumberSelect.value = '';
                courtPurposeInput.value = '';
            } else {
                roomNumberContainer.style.display = 'none';
                departmentContainer.style.display = 'none';
                courtPurposeContainer.style.display = 'none';
                avrSelectionContainer.style.display = 'none';
                roomNumberSelect.removeAttribute('required');
                departmentSelect.removeAttribute('required');
                courtPurposeInput.removeAttribute('required');
                avrSelectionSelect.removeAttribute('required');
                roomNumberSelect.value = '';
                departmentSelect.value = '';
                courtPurposeInput.value = '';
                avrSelectionSelect.value = '';
            }
            updatePreviewButtonState();
        });
    }

    // Court Type change handler for modal
    var modalCourtType = document.getElementById('modal_court_type');

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
        var requestType = document.getElementById('modal_request_type');
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
        var requestTypeValue = requestType ? requestType.value : '';
        var areaValue = areaOfUse ? areaOfUse.value : '';
        var courtTypeValue = courtType ? courtType.value : '';
        var avrSelectionValue = avrSelection ? avrSelection.value : '';
        var educationLevelValue = educationLevel ? educationLevel.value : 'faculty';

        // Request type is required
        if (!requestTypeValue) isValid = false;

        // Area of use is always required (category is always "Area Use")
        if (!areaValue) isValid = false;

        if (areaValue === 'Room') {
            if (!roomNumber || !roomNumber.value) isValid = false;
            // Only require department for tertiary level
            if (educationLevelValue !== 'shs' && (!department || !department.value)) isValid = false;
        }

        if (areaValue === 'Court') {
            if (!courtPurpose || !courtPurpose.value.trim()) isValid = false;
            if (requestTypeValue === 'Academic' && (!department || !department.value)) isValid = false;
        }

        if (areaValue === 'AVR') {
            if (!avrSelectionValue) isValid = false;
            // Require department for Academic AVR
            if (requestTypeValue === 'Academic' && (!department || !department.value)) isValid = false;
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
    var requiredFieldIds = ['modal_request_type', 'modal_area_of_use', 'modal_room_number', 'modal_court_type', 'modal_court_purpose', 'modal_department', 'modal_education_level', 'modal_avr_selection', 'modal_avr_request_category', 'modal_event_date', 'modal_start_time', 'modal_end_time', 'modal_description'];
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
            var requestType = document.getElementById('modal_request_type').value;
            var areaOfUse = document.getElementById('modal_area_of_use').value;
            var roomNumber = document.getElementById('modal_room_number').value;
            var department = document.getElementById('modal_department').value;
            var courtPurpose = document.getElementById('modal_court_purpose').value;
            var avrSelection = document.getElementById('modal_avr_selection').value;
            var eventDate = document.getElementById('modal_event_date').value;
            var startTime = document.getElementById('modal_start_time').value;
            var endTime = document.getElementById('modal_end_time').value;
            var description = document.getElementById('modal_description').value;

            // Build location from selected options
            var location = '';
            if (areaOfUse) {
                location = areaOfUse;
                if (areaOfUse === 'Room' && roomNumber) {
                    location += ' ' + roomNumber;
                } else if (areaOfUse === 'Court') {
                    location += ' (' + requestType + ')';
                } else if (areaOfUse === 'AVR' && avrSelection) {
                    location += ' ' + avrSelection;
                }
            }

            // Populate preview fields
            document.getElementById('preview_category').textContent = 'Area Use';
            document.getElementById('preview_request_type').textContent = requestType || 'Not specified';
            document.getElementById('preview_area_of_use').textContent = areaOfUse || 'Not specified';
            document.getElementById('preview_room_number').textContent = roomNumber || 'Not specified';
            document.getElementById('preview_department').textContent = department || 'Not specified';
            document.getElementById('preview_court_purpose').textContent = courtPurpose || 'Not specified';
            document.getElementById('preview_avr_selection').textContent = avrSelection || 'Not specified';
            document.getElementById('preview_event_date').textContent = eventDate ? new Date(eventDate).toLocaleDateString() : 'Not specified';
            document.getElementById('preview_start_time').textContent = startTime || 'Not specified';
            document.getElementById('preview_end_time').textContent = endTime || 'Not specified';
            document.getElementById('preview_location').textContent = location || 'Not specified';
            document.getElementById('preview_description').textContent = description || 'Not specified';

            // Show/hide area of use row
            var areaOfUseRow = document.getElementById('preview_area_of_use_row');
            if (areaOfUse) {
                areaOfUseRow.style.display = 'block';
            } else {
                areaOfUseRow.style.display = 'none';
            }

            // Show/hide room number row
            var roomNumberRow = document.getElementById('preview_room_number_row');
            if (areaOfUse === 'Room' && roomNumber) {
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

            // Show/hide court purpose row
            var courtPurposeRow = document.getElementById('preview_court_purpose_row');
            if (areaOfUse === 'Court' && courtPurpose) {
                courtPurposeRow.style.display = 'block';
            } else {
                courtPurposeRow.style.display = 'none';
            }

            // Show/hide AVR selection row
            var avrSelectionRow = document.getElementById('preview_avr_selection_row');
            if (areaOfUse === 'AVR' && avrSelection) {
                avrSelectionRow.style.display = 'block';
            } else {
                avrSelectionRow.style.display = 'none';
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
        var requestType = document.getElementById('modal_request_type');
        var eventDate = document.getElementById('modal_event_date');
        var startTime = document.getElementById('modal_start_time');
        var endTime = document.getElementById('modal_end_time');
        var description = document.getElementById('modal_description');
        
        // Validate Request Type
        if (!requestType.value) {
            showFieldError(requestType, 'Please select a request type');
            errors.push('Request type is required');
            isValid = false;
        }

        var areaOfUse = document.getElementById('modal_area_of_use');

        // Validate Area of Use (always required since category is always "Area Use")
        if (!areaOfUse || !areaOfUse.value) {
            showFieldError(areaOfUse, 'Please select an area of use');
            errors.push('Area of use is required');
            isValid = false;
        }

        // Validate Room Number (if 'Room' is selected in Area of Use)
        var roomNumber = document.getElementById('modal_room_number');
        if (areaOfUse.value === 'Room' && !roomNumber.value) {
            showFieldError(roomNumber, 'Please select a room number');
            errors.push('Room number is required');
            isValid = false;
        }

        // Validate Department (if 'Room' is selected in Area of Use and education level is tertiary)
        var department = document.getElementById('modal_department');
        var educationLevel = document.getElementById('modal_education_level');
        var educationLevelValue = educationLevel ? educationLevel.value : 'faculty';
        if (areaOfUse.value === 'Room' && educationLevelValue !== 'shs' && !department.value) {
            showFieldError(department, 'Please select a department');
            errors.push('Department is required');
            isValid = false;
        }

        // Validate Court Purpose (if Court is selected)
        var courtPurpose = document.getElementById('modal_court_purpose');
        if (areaOfUse.value === 'Court' && !courtPurpose.value.trim()) {
            showFieldError(courtPurpose, 'Please enter the purpose for court use');
            errors.push('Court purpose is required');
            isValid = false;
        }

        // Validate Department (if Academic request type and Court/AVR selected)
        if (requestType.value === 'Academic' && (areaOfUse.value === 'Court' || areaOfUse.value === 'AVR') && !department.value) {
            showFieldError(department, 'Please select a department for academic requests');
            errors.push('Department is required for academic requests');
            isValid = false;
        }

        // Validate AVR Selection (if AVR is selected in Area of Use)
        var avrSelection = document.getElementById('modal_avr_selection');
        if (areaOfUse.value === 'AVR' && !avrSelection.value) {
            showFieldError(avrSelection, 'Please select an AVR');
            errors.push('AVR selection is required');
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
            var requestType = document.getElementById('modal_request_type').value;
            var areaOfUse = document.getElementById('modal_area_of_use').value;
            var roomNumber = document.getElementById('modal_room_number').value;
            var courtPurpose = document.getElementById('modal_court_purpose').value;
            var department = document.getElementById('modal_department').value;
            var educationLevel = document.getElementById('modal_education_level').value;
            var avrSelectionValue = document.getElementById('modal_avr_selection').value;
            var eventDate = document.getElementById('modal_event_date').value;
            var startTime = document.getElementById('modal_start_time').value;
            var endTime = document.getElementById('modal_end_time').value;
            var description = document.getElementById('modal_description').value;
            
            var location = '';
            if (areaOfUse) {
                location = areaOfUse;
                if (areaOfUse === 'Room' && roomNumber) {
                    location += ' ' + roomNumber;
                } else if (areaOfUse === 'Court') {
                    location += ' (' + requestType + ')';
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

            document.getElementById('preview_category').textContent = 'Area Use';
            document.getElementById('preview_request_type').textContent = requestType || 'Not specified';
            document.getElementById('preview_education_level').textContent = educationLevel === 'shs' ? 'Senior High School' : educationLevel === 'faculty' ? 'Faculty' : educationLevel === 'staff' ? 'Staff' : educationLevel === 'maintenance' ? 'Maintenance' : 'Tertiary';
            document.getElementById('preview_area_of_use').textContent = areaOfUse || 'Not specified';
            document.getElementById('preview_room_number').textContent = roomNumber || 'Not specified';
            document.getElementById('preview_department').textContent = department || 'Not specified';
            document.getElementById('preview_court_purpose').textContent = courtPurpose || 'Not specified';
            document.getElementById('preview_avr_selection').textContent = avrSelectionValue || 'Not specified';
            document.getElementById('preview_event_date').textContent = dateDisplay || '-';
            document.getElementById('preview_start_time').textContent = formatTime(startTime);
            document.getElementById('preview_end_time').textContent = formatTime(endTime);
            document.getElementById('preview_location').textContent = location || 'Not specified';
            document.getElementById('preview_description').textContent = description || '-';

            document.getElementById('preview_area_of_use_row').style.display = areaOfUse ? 'flex' : 'none';
            document.getElementById('preview_room_number_row').style.display = (areaOfUse === 'Room' && roomNumber) ? 'flex' : 'none';
            document.getElementById('preview_department_row').style.display = department ? 'flex' : 'none';
            document.getElementById('preview_court_purpose_row').style.display = (areaOfUse === 'Court' && courtPurpose) ? 'flex' : 'none';
            document.getElementById('preview_avr_selection_row').style.display = (areaOfUse === 'AVR' && avrSelectionValue) ? 'flex' : 'none';

            // Set approval recipients based on education level, department, and request type
            var approvalRecipients = 'Chosen Department on the selection, Academic Head, Building Admin, and School Administrator';

            // SHS approval flow: Principal Assistant → Academic Head → School Administrator
            if (educationLevel === 'shs') {
                approvalRecipients = 'Principal Assistant, Academic Head, and School Administrator';
            } else if (requestType === 'Non-Academic') {
                // Non-academic requests (Court or AVR)
                approvalRecipients = 'Building Admin and School Administrator';
            } else if (department && requestType === 'Academic') {
                // Academic requests with department (Room, Court, or AVR)
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
            // AVR selection doesn't need to show additional fields anymore
            // Department visibility is controlled by request_type in area_of_use handler
            updatePreviewButtonState();
        });
    }

    // Update approval recipients on department or request type change
    var departmentSelect = document.getElementById('modal_department');
    var requestTypeSelect = document.getElementById('modal_request_type');

    function updateApprovalRecipients() {
        var areaOfUse = document.getElementById('modal_area_of_use').value;
        var requestType = requestTypeSelect ? requestTypeSelect.value : '';
        var department = departmentSelect ? departmentSelect.value : '';
        var educationLevel = document.getElementById('modal_education_level').value;
        var approvalRecipients = 'Chosen Department on the selection, Academic Head, Building Admin, and School Administrator';

        // SHS approval flow: Principal Assistant → Academic Head → School Administrator
        if (educationLevel === 'shs') {
            approvalRecipients = 'Principal Assistant, Academic Head, and School Administrator';
        } else if (requestType === 'Non-Academic') {
            // Non-academic requests (Court or AVR)
            approvalRecipients = 'Building Admin and School Administrator';
        } else if (department && requestType === 'Academic') {
            // Academic requests with department (Room, Court, or AVR)
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
    if (requestTypeSelect) {
        requestTypeSelect.addEventListener('change', updateApprovalRecipients);
    }

    // Add listener for education level changes
    var educationLevelSelect = document.getElementById('modal_education_level');
    if (educationLevelSelect) {
        educationLevelSelect.addEventListener('change', updateApprovalRecipients);
    }

    // Materials/Equipment dynamic rows for modal
    let modalMaterialRowCount = 1;

    window.updateDescCount = function() {
        var ta = document.getElementById('modal_description');
        var counter = document.getElementById('desc_char_count');
        if (ta && counter) {
            var len = ta.value.length;
            counter.textContent = len + ' / 500';
            counter.style.color = len >= 480 ? '#dc3545' : '';
        }
    };

    window.addModalMaterialRow = function() {
        var table = document.getElementById('modalMaterialsTable');
        if (table) {
            var tbody = table.getElementsByTagName('tbody')[0];
            var newRow = tbody.insertRow();
            newRow.innerHTML = `
                <td><input type="number" class="form-control form-control-sm" name="materials[${modalMaterialRowCount}][qty]" min="1" placeholder="1"></td>
                <td><input type="text" class="form-control form-control-sm" name="materials[${modalMaterialRowCount}][item]" placeholder="e.g., Projector"></td>
                <td><input type="text" class="form-control form-control-sm" name="materials[${modalMaterialRowCount}][purpose]" placeholder="e.g., For presentation"></td>
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
    
    // Auto-open modal if there are validation errors (only on event-related pages)
    <?php if($errors->any() && !Request::is('admin/users*', 'admin/logs*', 'admin/reports*', 'admin/dashboard*', 'settings*', 'profile*', 'my-concerns*', 'reports*')): ?>
    document.addEventListener('DOMContentLoaded', function() {
        var modalEl = document.getElementById('eventRequestModal');
        if (modalEl) {
            var modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    });
    <?php endif; ?>
    
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo e(asset('js/app.js')); ?>"></script>

<script>
// ── SweetAlert2 Global Helpers ────────────────────────────────────────────────

// Themed Swal instance that respects dark mode
function getSwal() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    return Swal.mixin({
        background: isDark ? '#1a1a2e' : '#fff',
        color: isDark ? '#e0e0e0' : '#333',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
    });
}

// Drop-in replacement for confirm() — returns a Promise
window.swalConfirm = function(options) {
    const defaults = {
        title: 'Are you sure?',
        text: '',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
    };
    return getSwal().fire(Object.assign(defaults, options));
};

// Drop-in replacement for alert()
window.swalAlert = function(message, icon = 'info', title = '') {
    return getSwal().fire({ title: title || (icon === 'error' ? 'Error' : icon === 'success' ? 'Success' : 'Notice'), text: message, icon });
};

// Toast notification (top-right, auto-dismiss)
window.swalToast = function(message, icon = 'success') {
    getSwal().mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    }).fire({ icon, title: message });
};

// ── Global Action Confirmations ──────────────────────────────────────────────

// Delete confirmation
window.confirmDelete = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Delete this item?',
        text: options.text || 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-trash me-1"></i> Delete',
        cancelButtonText: 'Cancel',
        ...options
    });
};

// Archive confirmation
window.confirmArchive = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Archive this item?',
        text: options.text || 'You can restore it later from the archive.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-archive me-1"></i> Archive',
        cancelButtonText: 'Cancel',
        ...options
    });
};

// Restore confirmation
window.confirmRestore = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Restore this item?',
        text: options.text || 'This will move the item back to active items.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-trash-restore me-1"></i> Restore',
        cancelButtonText: 'Cancel',
        ...options
    });
};

// Permanent delete confirmation
window.confirmPermanentDelete = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Permanently Delete?',
        html: options.html || '<p class="mb-2">This action <strong>cannot be undone</strong>!</p><p class="text-muted">The item will be permanently removed from the database.</p>',
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-exclamation-triangle me-1"></i> Yes, Delete Forever',
        cancelButtonText: 'Cancel',
        ...options
    });
};

// Assign confirmation
window.confirmAssign = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Assign this item?',
        text: options.text || 'This will assign the item to the selected user.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-user-check me-1"></i> Assign',
        cancelButtonText: 'Cancel',
        ...options
    });
};

// Approve confirmation
window.confirmApprove = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Approve this request?',
        text: options.text || 'This will mark the request as approved.',
        icon: 'success',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-check me-1"></i> Approve',
        cancelButtonText: 'Cancel',
        ...options
    });
};

// Reject confirmation
window.confirmReject = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Reject this request?',
        text: options.text || 'This will mark the request as rejected.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-times me-1"></i> Reject',
        cancelButtonText: 'Cancel',
        ...options
    });
};

// Cancel confirmation
window.confirmCancel = function(options = {}) {
    return getSwal().fire({
        title: options.title || 'Cancel this item?',
        text: options.text || 'This action will cancel the item.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: options.confirmText || '<i class="fas fa-ban me-1"></i> Cancel Item',
        cancelButtonText: 'Go Back',
        ...options
    });
};

// Generic action confirmation
window.confirmAction = function(action, options = {}) {
    const actionConfigs = {
        delete: { fn: confirmDelete, color: '#dc3545', icon: 'trash' },
        archive: { fn: confirmArchive, color: '#6c757d', icon: 'archive' },
        restore: { fn: confirmRestore, color: '#28a745', icon: 'trash-restore' },
        permanent_delete: { fn: confirmPermanentDelete, color: '#dc3545', icon: 'exclamation-triangle' },
        assign: { fn: confirmAssign, color: '#0d6efd', icon: 'user-check' },
        approve: { fn: confirmApprove, color: '#28a745', icon: 'check' },
        reject: { fn: confirmReject, color: '#dc3545', icon: 'times' },
        cancel: { fn: confirmCancel, color: '#ffc107', icon: 'ban' },
    };
    
    const config = actionConfigs[action];
    if (config && config.fn) {
        return config.fn(options);
    }
    return swalConfirm(options);
};

// Handle form submit with SweetAlert2 confirmation
// Usage: <form data-confirm="Are you sure?"> or <button data-confirm="...">
document.addEventListener('DOMContentLoaded', function () {

    // ── Flash messages from Laravel session ──────────────────────
    <?php if(session('success')): ?>
        swalToast(<?php echo json_encode(session('success'), 15, 512) ?>, 'success');
    <?php endif; ?>
    <?php if(session('error')): ?>
        swalToast(<?php echo json_encode(session('error'), 15, 512) ?>, 'error');
    <?php endif; ?>
    <?php if(session('warning')): ?>
        swalToast(<?php echo json_encode(session('warning'), 15, 512) ?>, 'warning');
    <?php endif; ?>
    <?php if(session('info')): ?>
        swalToast(<?php echo json_encode(session('info'), 15, 512) ?>, 'info');
    <?php endif; ?>

    // ── Intercept forms/buttons with data-confirm attribute ──────
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-confirm]');
        if (!btn) return;

        // If it's a submit button inside a form
        const form = btn.closest('form');
        if (form && (btn.type === 'submit' || btn.tagName === 'BUTTON')) {
            e.preventDefault();
            swalConfirm({
                title: btn.dataset.confirmTitle || 'Are you sure?',
                text: btn.dataset.confirm,
                icon: btn.dataset.confirmIcon || 'warning',
                confirmButtonText: btn.dataset.confirmOk || 'Yes',
                confirmButtonColor: btn.dataset.confirmColor || '#0d6efd',
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        }
    });

    // ── Replace native confirm() on onclick="return confirm(...)" ─
    // Intercept all forms that have buttons with onclick confirm
    document.querySelectorAll('form button[onclick*="confirm("], form input[onclick*="confirm("]').forEach(function(btn) {
        const onclickAttr = btn.getAttribute('onclick') || '';
        const match = onclickAttr.match(/confirm\(['"](.+?)['"]\)/);
        if (!match) return;
        const message = match[1];
        btn.removeAttribute('onclick');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = btn.closest('form');
            swalConfirm({ text: message }).then(result => {
                if (result.isConfirmed && form) form.submit();
            });
        });
    });

    // ── Notification delete confirm ───────────────────────────────
    document.querySelectorAll('.notification-delete-form').forEach(function(form) {
        form.removeAttribute('onsubmit');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            swalConfirm({
                title: 'Delete notification?',
                text: 'This notification will be removed.',
                icon: 'question',
                confirmButtonText: 'Delete',
                confirmButtonColor: '#dc3545',
            }).then(result => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

});
</script>
<script>
    // ── Theme System ──────────────────────────────────────────────
    const THEME_KEY = 'campfix_theme';
    const userDbTheme = '<?php echo e(auth()->check() ? auth()->user()->theme : "light"); ?>';

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
        fetch('<?php echo e(route("settings.theme")); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '<?php echo e(csrf_token()); ?>',
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

<?php echo $__env->yieldContent('scripts'); ?>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\Campfix\resources\views/layouts/app.blade.php ENDPATH**/ ?>