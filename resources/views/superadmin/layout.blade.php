<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SuperAdmin — CampFix</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        /* ── Dark Theme (Default) ── */
        :root {
            --sa-bg:       #0f1117;
            --sa-sidebar:  #161b27;
            --sa-accent:   #7c3aed;
            --sa-accent2:  #a855f7;
            --sa-border:   #2a2f3e;
            --sa-text:     #e2e8f0;
            --sa-muted:    #8892a4;
            --sa-card:     #1e2435;
            --sa-hover:    #252d40;
            --sa-danger:   #ef4444;
            --sa-success:  #22c55e;
            --sa-warning:  #f59e0b;
            --sa-info:     #3b82f6;
        }

        /* ── Light Theme ── */
        [data-theme="light"] {
            --sa-bg:       #f8fafc;
            --sa-sidebar:  #ffffff;
            --sa-accent:   #7c3aed;
            --sa-accent2:  #a855f7;
            --sa-border:   #e2e8f0;
            --sa-text:     #1e293b;
            --sa-muted:    #64748b;
            --sa-card:     #ffffff;
            --sa-hover:    #f1f5f9;
            --sa-danger:   #ef4444;
            --sa-success:  #22c55e;
            --sa-warning:  #f59e0b;
            --sa-info:     #3b82f6;
        }

        * { box-sizing: border-box; }

        body {
            background: var(--sa-bg);
            color: var(--sa-text);
            font-family: 'Segoe UI', system-ui, sans-serif;
            margin: 0;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sa-sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 240px;
            height: 100vh;
            background: var(--sa-sidebar);
            border-right: 1px solid var(--sa-border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            overflow-y: auto;
        }

        .sa-brand {
            padding: 20px 20px 16px;
            border-bottom: 1px solid var(--sa-border);
        }

        .sa-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--sa-accent), var(--sa-accent2));
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 6px;
        }

        .sa-brand-title {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .sa-brand-sub {
            font-size: 11px;
            color: var(--sa-muted);
            margin: 0;
        }

        .sa-nav {
            flex: 1;
            padding: 12px 0;
        }

        .sa-nav-section {
            padding: 8px 20px 4px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--sa-muted);
        }

        .sa-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 20px;
            color: var(--sa-muted);
            text-decoration: none;
            font-size: 13.5px;
            transition: all .15s;
            border-left: 3px solid transparent;
        }

        .sa-nav-link:hover {
            background: var(--sa-hover);
            color: var(--sa-text);
        }

        .sa-nav-link.active {
            background: rgba(124, 58, 237, .15);
            color: var(--sa-accent2);
            border-left-color: var(--sa-accent);
        }

        .sa-nav-link i { width: 16px; text-align: center; font-size: 13px; }

        .sa-sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--sa-border);
        }

        .sa-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .sa-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--sa-accent), var(--sa-accent2));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 13px; color: #fff;
            flex-shrink: 0;
        }

        .sa-user-name { font-size: 13px; font-weight: 600; color: var(--sa-text); }
        .sa-user-role { font-size: 11px; color: var(--sa-accent2); }

        /* ── Main ── */
        .sa-main {
            margin-left: 240px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .sa-topbar {
            background: var(--sa-sidebar);
            border-bottom: 1px solid var(--sa-border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .sa-page-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--sa-text);
            margin: 0;
        }

        .sa-content {
            padding: 24px;
            flex: 1;
        }

        /* ── Cards ── */
        .sa-card {
            background: var(--sa-card);
            border: 1px solid var(--sa-border);
            border-radius: 10px;
            padding: 20px;
        }

        .sa-stat-card {
            background: var(--sa-card);
            border: 1px solid var(--sa-border);
            border-radius: 10px;
            padding: 18px 20px;
            border-left: 3px solid;
        }

        .sa-stat-card.purple { border-left-color: var(--sa-accent); }
        .sa-stat-card.blue   { border-left-color: var(--sa-info); }
        .sa-stat-card.green  { border-left-color: var(--sa-success); }
        .sa-stat-card.red    { border-left-color: var(--sa-danger); }
        .sa-stat-card.yellow { border-left-color: var(--sa-warning); }
        .sa-stat-card.teal   { border-left-color: #14b8a6; }

        .sa-stat-label { font-size: 11px; color: var(--sa-muted); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 4px; }
        .sa-stat-value { font-size: 28px; font-weight: 700; color: var(--sa-text); line-height: 1; }
        .sa-stat-sub   { font-size: 11px; color: var(--sa-muted); margin-top: 4px; }

        /* ── Tables ── */
        .sa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .sa-table th {
            background: rgba(255,255,255,.04);
            color: var(--sa-muted);
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .8px;
            text-transform: uppercase;
            padding: 10px 14px;
            border-bottom: 1px solid var(--sa-border);
            white-space: nowrap;
        }
        .sa-table td {
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255,255,255,.04);
            color: var(--sa-text);
            vertical-align: middle;
        }
        .sa-table tr:hover td { background: var(--sa-hover); }

        /* ── Badges ── */
        .sa-badge {
            display: inline-flex; align-items: center;
            padding: 3px 9px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }
        .sa-badge-purple { background: rgba(124,58,237,.2); color: var(--sa-accent2); }
        .sa-badge-blue   { background: rgba(59,130,246,.2); color: #60a5fa; }
        .sa-badge-green  { background: rgba(34,197,94,.2);  color: #4ade80; }
        .sa-badge-red    { background: rgba(239,68,68,.2);  color: #f87171; }
        .sa-badge-yellow { background: rgba(245,158,11,.2); color: #fbbf24; }
        .sa-badge-gray   { background: rgba(255,255,255,.08); color: var(--sa-muted); }

        /* ── Buttons ── */
        .sa-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 7px;
            font-size: 13px; font-weight: 500;
            border: none; cursor: pointer; text-decoration: none;
            transition: all .15s;
        }
        .sa-btn-primary { background: var(--sa-accent); color: #fff; }
        .sa-btn-primary:hover { background: var(--sa-accent2); color: #fff; }
        .sa-btn-danger  { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.3); }
        .sa-btn-danger:hover { background: rgba(239,68,68,.3); }
        .sa-btn-ghost   { background: transparent; color: var(--sa-muted); border: 1px solid var(--sa-border); }
        .sa-btn-ghost:hover { background: var(--sa-hover); color: var(--sa-text); }
        .sa-btn-sm { padding: 4px 10px; font-size: 12px; }

        /* ── Alerts ── */
        .sa-alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; }
        .sa-alert-success { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
        .sa-alert-error   { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
        .sa-alert-info    { background: rgba(59,130,246,.1); border: 1px solid rgba(59,130,246,.3); color: #60a5fa; }

        /* ── Forms ── */
        .sa-input {
            background: var(--sa-bg);
            border: 1px solid var(--sa-border);
            color: var(--sa-text);
            border-radius: 7px;
            padding: 8px 12px;
            font-size: 13px;
            width: 100%;
        }
        .sa-input:focus { outline: none; border-color: var(--sa-accent); box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
        .sa-label { font-size: 12px; color: var(--sa-muted); margin-bottom: 5px; display: block; font-weight: 500; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--sa-border); border-radius: 3px; }

        /* ── Light mode overrides for hardcoded rgba ── */
        [data-theme="light"] .sa-table th {
            background: rgba(0,0,0,.03);
        }
        [data-theme="light"] .sa-table td {
            border-bottom-color: rgba(0,0,0,.05);
        }
        [data-theme="light"] .sa-table tr:hover td {
            background: var(--sa-hover);
        }
        [data-theme="light"] .sa-badge-gray {
            background: rgba(0,0,0,.07);
            color: var(--sa-muted);
        }
        [data-theme="light"] .sa-btn-danger {
            background: rgba(239,68,68,.08);
        }
        [data-theme="light"] .sa-brand-title {
            color: var(--sa-text);
        }
        [data-theme="light"] .sa-sidebar {
            box-shadow: 1px 0 12px rgba(0,0,0,.06);
        }
        [data-theme="light"] .sa-topbar {
            box-shadow: 0 1px 8px rgba(0,0,0,.06);
        }
        [data-theme="light"] .sa-card {
            box-shadow: 0 1px 4px rgba(0,0,0,.06);
        }

        /* ── Theme toggle button ── */
        .sa-theme-toggle {
            width: 36px; height: 36px;
            border-radius: 8px;
            border: 1px solid var(--sa-border);
            background: transparent;
            color: var(--sa-muted);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            transition: all .15s;
        }
        .sa-theme-toggle:hover {
            background: var(--sa-hover);
            color: var(--sa-text);
        }

        /* ── Responsive ── */
        @media (max-width: 768px) {
            .sa-sidebar { transform: translateX(-100%); transition: transform .25s; }
            .sa-sidebar.open { transform: translateX(0); }
            .sa-main { margin-left: 0; }
            .sa-content { padding: 16px; }
        }
    </style>

    @yield('extra_styles')
</head>
<body>
<script>
    // Apply theme before paint to prevent flash
    (function() {
        const t = localStorage.getItem('sa_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', t);
    })();
</script>

{{-- Sidebar --}}
<aside class="sa-sidebar" id="saSidebar">
    <div class="sa-brand">
        <div class="sa-brand-badge"><i class="fas fa-shield-halved"></i> Superadmin</div>
        <p class="sa-brand-title">CampFix</p>
        <p class="sa-brand-sub">System Control Panel</p>
    </div>

    <nav class="sa-nav">
        <div class="sa-nav-section">Overview</div>
        <a href="{{ route('superadmin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-gauge-high"></i> Dashboard
        </a>
        <a href="{{ route('superadmin.analytics') }}" class="sa-nav-link {{ request()->routeIs('superadmin.analytics') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i> Analytics
        </a>

        <div class="sa-nav-section">Management</div>
        <a href="{{ route('superadmin.users') }}" class="sa-nav-link {{ request()->routeIs('superadmin.users*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> All Users
        </a>
        <a href="{{ route('superadmin.concerns') }}" class="sa-nav-link {{ request()->routeIs('superadmin.concerns') ? 'active' : '' }}">
            <i class="fas fa-triangle-exclamation"></i> Concerns
        </a>
        <a href="{{ route('superadmin.reports') }}" class="sa-nav-link {{ request()->routeIs('superadmin.reports') ? 'active' : '' }}">
            <i class="fas fa-file-lines"></i> Reports
        </a>
        <a href="{{ route('superadmin.events') }}" class="sa-nav-link {{ request()->routeIs('superadmin.events') ? 'active' : '' }}">
            <i class="fas fa-calendar-days"></i> Events
        </a>
        <a href="{{ route('superadmin.categories') }}" class="sa-nav-link {{ request()->routeIs('superadmin.categories') ? 'active' : '' }}">
            <i class="fas fa-tags"></i> Categories
        </a>

        <div class="sa-nav-section">Audit</div>
        <a href="{{ route('superadmin.activity-logs') }}" class="sa-nav-link {{ request()->routeIs('superadmin.activity-logs') ? 'active' : '' }}">
            <i class="fas fa-list-check"></i> Activity Logs
        </a>
        <a href="{{ route('superadmin.superadmin-logs') }}" class="sa-nav-link {{ request()->routeIs('superadmin.superadmin-logs') ? 'active' : '' }}">
            <i class="fas fa-eye-slash"></i> Superadmin Logs
        </a>

        <div class="sa-nav-section">System</div>
        <a href="{{ route('superadmin.settings') }}" class="sa-nav-link {{ request()->routeIs('superadmin.settings') ? 'active' : '' }}">
            <i class="fas fa-gear"></i> Settings
        </a>
        <a href="{{ route('dashboard') }}" class="sa-nav-link">
            <i class="fas fa-arrow-left"></i> Back to App
        </a>
    </nav>

    <div class="sa-sidebar-footer">
        <div class="sa-user-info">
            <div class="sa-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
            <div>
                <div class="sa-user-name">{{ auth()->user()->name }}</div>
                <div class="sa-user-role">Superadmin</div>
            </div>
        </div>
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="sa-btn sa-btn-ghost w-100" style="justify-content:center">
                <i class="fas fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>
</aside>

{{-- Main --}}
<div class="sa-main">
    <div class="sa-topbar">
        <div style="display:flex;align-items:center;gap:12px">
            <button class="sa-btn sa-btn-ghost sa-btn-sm d-md-none" onclick="document.getElementById('saSidebar').classList.toggle('open')">
                <i class="fas fa-bars"></i>
            </button>
            <h1 class="sa-page-title">@yield('page_title', 'Dashboard')</h1>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
            <span style="font-size:12px;color:var(--sa-muted)">{{ now()->format('M d, Y') }}</span>
            <button class="sa-theme-toggle" id="themeToggle" title="Toggle light/dark mode" onclick="toggleTheme()">
                <i class="fas fa-sun" id="themeIcon"></i>
            </button>
            <span class="sa-badge sa-badge-purple"><i class="fas fa-shield-halved me-1"></i>Superadmin</span>
        </div>
    </div>

    <div class="sa-content">
        @if(session('success'))
            <div class="sa-alert sa-alert-success"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="sa-alert sa-alert-error"><i class="fas fa-circle-xmark me-2"></i>{{ session('error') }}</div>
        @endif
        @if(session('info'))
            <div class="sa-alert sa-alert-info">{!! session('info') !!}</div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // ── Theme Management ──────────────────────────────────────────────────────
    const THEME_KEY = 'sa_theme';

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        const icon = document.getElementById('themeIcon');
        if (icon) {
            icon.className = theme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
        }
        // Update chart colors if charts exist on the page
        if (window.saCharts) {
            const gridColor = theme === 'light' ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.05)';
            const tickColor = theme === 'light' ? '#64748b' : '#8892a4';
            window.saCharts.forEach(chart => {
                if (chart.options.scales) {
                    ['x','y'].forEach(axis => {
                        if (chart.options.scales[axis]) {
                            chart.options.scales[axis].grid.color = gridColor;
                            chart.options.scales[axis].ticks.color = tickColor;
                        }
                    });
                }
                chart.update('none');
            });
        }
    }

    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        const next = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem(THEME_KEY, next);
        applyTheme(next);
    }

    // Apply saved theme immediately (before paint to avoid flash)
    (function() {
        const saved = localStorage.getItem(THEME_KEY) || 'dark';
        applyTheme(saved);
    })();
</script>

@yield('scripts')
</body>
</html>
