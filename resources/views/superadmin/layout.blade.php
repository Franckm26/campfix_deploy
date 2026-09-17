@extends('layouts.app')
@section('styles')
<style>
        /* ── Dark Theme (Default) ── */
        [data-theme="dark"] {
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
        :root:not([data-theme="dark"]), [data-theme="light"] {
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
@endsection
