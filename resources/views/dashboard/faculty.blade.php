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
        --cal-mini-has-event:  #7c4dff;
        --cal-mini-day-hover:  #e9ecef;
        --cal-list-border:     #dee2e6;
        --cal-list-text:       #495057;
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
        --cal-mini-has-event:  #7c4dff;
        --cal-mini-day-hover:  #2a2a45;
        --cal-list-border:     #2a2a45;
        --cal-list-text:       #bbbbbb;
    }

    /* ── Layout ── */
    .faculty-home-wrap {
        display: flex;
        gap: 20px;
        align-items: flex-start;
    }

    .faculty-main-col {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Welcome Card ── */
    .welcome-card {
        background: var(--cal-bg);
        border: 1px solid var(--cal-border);
        border-radius: 14px;
        overflow: hidden;
    }

    .welcome-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px 10px;
        font-size: 15px;
        font-weight: 700;
        color: var(--cal-text);
        border-bottom: 1px solid var(--cal-border);
    }

    .welcome-card-header .header-controls {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .welcome-card-header .header-controls button {
        background: none;
        border: none;
        color: var(--cal-text-muted);
        font-size: 15px;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 6px;
        transition: background 0.15s;
        line-height: 1;
    }
    .welcome-card-header .header-controls button:hover {
        background: var(--cal-btn-bg);
    }

    /* Carousel slides */
    #welcomeCarousel .carousel-item {
        min-height: 220px;
    }

    /* Event Request Slide */
    .slide-event {
        display: flex !important;
        min-height: 220px;
    }

    .slide-event .ev-left {
        flex: 1;
        padding: 28px 28px 28px 32px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
    }

    .slide-event .ev-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 14px;
        width: fit-content;
    }
    .ev-badge.status-pending    { background: rgba(255,193,7,0.25);  color: #ffd600; border: 1px solid rgba(255,193,7,0.4); }
    .ev-badge.status-approved   { background: rgba(76,175,80,0.25);  color: #a5d6a7; border: 1px solid rgba(76,175,80,0.4); }
    .ev-badge.status-rejected   { background: rgba(244,67,54,0.25);  color: #ef9a9a; border: 1px solid rgba(244,67,54,0.4); }
    .ev-badge.status-cancelled  { background: rgba(158,158,158,0.25);color: #e0e0e0; border: 1px solid rgba(158,158,158,0.4); }

    .slide-event .ev-title {
        font-size: 22px;
        font-weight: 800;
        color: #fff;
        line-height: 1.3;
        margin-bottom: 10px;
    }

    .slide-event .ev-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        font-size: 12px;
        color: rgba(255,255,255,0.75);
    }

    .slide-event .ev-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .slide-event .ev-right {
        width: 240px;
        flex-shrink: 0;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(4px);
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 14px;
        border-left: 1px solid rgba(255,255,255,0.15);
    }

    .slide-event .ev-right .ev-detail-row {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .slide-event .ev-right .ev-detail-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.55);
    }

    .slide-event .ev-right .ev-detail-val {
        font-size: 13px;
        font-weight: 600;
        color: #fff;
    }

    .slide-event .ev-right .ev-view-btn {
        display: block;
        margin-top: 4px;
        padding: 8px 14px;
        background: rgba(255,255,255,0.2);
        color: #fff;
        border-radius: 8px;
        font-weight: 700;
        font-size: 12px;
        text-decoration: none;
        text-align: center;
        border: 1px solid rgba(255,255,255,0.35);
        transition: background 0.15s;
    }
    .slide-event .ev-right .ev-view-btn:hover {
        background: rgba(255,255,255,0.3);
        color: #fff;
    }

    /* Slide counter dots */
    #welcomeCarousel .carousel-indicators {
        bottom: 6px;
        margin: 0;
        justify-content: flex-end;
        padding-right: 14px;
    }
    #welcomeCarousel .carousel-indicators button {
        width: 7px; height: 7px;
        border-radius: 50%;
        border: none;
        background: rgba(255,255,255,0.4);
        opacity: 1;
        transition: background 0.2s;
        margin: 0 3px;
    }
    #welcomeCarousel .carousel-indicators button.active {
        background: #fff;
    }

    /* Empty state slide */
    .slide-empty {
        min-height: 220px;
        background: linear-gradient(135deg, #1565c0 0%, #1976d2 45%, #42a5f5 100%);
        position: relative;
        overflow: hidden;
        display: flex !important;
        align-items: center;
        justify-content: center;
        padding: 30px 24px;
        text-align: center;
    }
    .slide-empty .se-bg {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 28px 28px;
    }
    .slide-empty .se-content { position: relative; z-index: 1; color: #fff; }
    .slide-empty .se-icon { font-size: 40px; opacity: 0.6; margin-bottom: 12px; }
    .slide-empty .se-title { font-size: 20px; font-weight: 800; margin-bottom: 8px; }
    .slide-empty .se-sub { font-size: 13px; color: rgba(255,255,255,0.8); margin-bottom: 16px; }
    .slide-empty .se-btn {
        display: inline-block;
        padding: 9px 22px;
        background: #fff;
        color: #1565c0;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
        text-decoration: none;
        cursor: pointer;
    }

    /* ── Quick Stats ── */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    .stat-card {
        background: var(--cal-bg);
        border: 1px solid var(--cal-border);
        border-radius: 12px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .stat-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .stat-label {
        font-size: 12px;
        color: var(--cal-text-muted);
        font-weight: 600;
        margin-bottom: 2px;
    }

    .stat-value {
        font-size: 22px;
        font-weight: 800;
        color: var(--cal-text);
        line-height: 1;
    }

    /* ── Quick Actions ── */
    .quick-actions-card {
        background: var(--cal-bg);
        border: 1px solid var(--cal-border);
        border-radius: 14px;
        padding: 18px;
    }

    .quick-actions-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--cal-text);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .quick-action-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid var(--cal-border);
        background: var(--cal-bg-secondary);
        color: var(--cal-text);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
    }
    .quick-action-btn:hover {
        background: var(--cal-btn-hover);
        color: var(--cal-text);
        text-decoration: none;
    }

    .quick-action-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }

    /* ── Right Panel (mini cal only) ── */
    .cal-right-panel {
        width: 240px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .mini-cal-card {
        background: var(--cal-bg);
        border: 1px solid var(--cal-border);
        border-radius: 14px;
        padding: 16px;
        color: var(--cal-text);
    }

    .mini-cal-card h6 {
        font-size: 14px; font-weight: 700;
        margin-bottom: 12px;
        display: flex; align-items: center; gap: 6px;
        color: var(--cal-text);
    }

    .mini-cal-nav {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 10px;
    }
    .mini-cal-nav span { font-size: 13px; font-weight: 700; color: var(--cal-text); }
    .mini-cal-nav button {
        background: none; border: none;
        color: var(--cal-text-muted); cursor: pointer;
        font-size: 13px; padding: 2px 6px;
    }

    .mini-cal-grid {
        display: grid; grid-template-columns: repeat(7, 1fr);
        gap: 2px; text-align: center;
    }
    .mini-cal-grid .day-label {
        font-size: 10px; color: var(--cal-text-muted);
        font-weight: 700; padding: 2px 0;
    }
    .mini-cal-grid .mini-day {
        font-size: 11px; padding: 4px 2px;
        border-radius: 50%; cursor: pointer;
        color: var(--cal-text-muted); transition: background 0.15s;
    }
    .mini-cal-grid .mini-day:hover { background: var(--cal-mini-day-hover); }
    .mini-cal-grid .mini-day.today {
        background: var(--cal-today-bg);
        color: var(--cal-today-color); font-weight: 700;
    }
    .mini-cal-grid .mini-day.has-event {
        color: var(--cal-mini-has-event); font-weight: 700;
    }
    .mini-cal-grid .mini-day.other { color: var(--cal-other-month); }

    /* ── Upcoming Events Card ── */
    .upcoming-card {
        background: var(--cal-bg);
        border: 1px solid var(--cal-border);
        border-radius: 14px;
        padding: 16px;
        color: var(--cal-text);
    }

    .upcoming-card h6 {
        font-size: 14px; font-weight: 700;
        margin-bottom: 12px;
        display: flex; align-items: center; gap: 6px;
        color: var(--cal-text);
    }

    .upcoming-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 8px 0;
        border-bottom: 1px solid var(--cal-list-border);
        font-size: 12px;
    }
    .upcoming-item:last-child { border-bottom: none; }

    .upcoming-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 3px;
    }

    .upcoming-title {
        font-weight: 600;
        color: var(--cal-text);
        line-height: 1.3;
    }

    .upcoming-date {
        font-size: 11px;
        color: var(--cal-text-muted);
        margin-top: 2px;
    }

    @media (max-width: 900px) {
        .faculty-home-wrap { flex-direction: column; }
        .cal-right-panel { width: 100%; }
        .quick-stats { grid-template-columns: repeat(2, 1fr); }
    }

    @media (max-width: 576px) {
        .quick-stats { grid-template-columns: 1fr; }
        .quick-actions-grid { grid-template-columns: 1fr; }
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
<div class="container-fluid px-3" style="min-height: 100vh;">
    <div class="row mb-3">
        <div class="col-12">
        </div>
    </div>

    <div class="faculty-home-wrap">
        <!-- Left / Main Column -->
        <div class="faculty-main-col">

            <!-- My Event Requests Carousel -->
            <div class="welcome-card">
                <div class="welcome-card-header">
                    <div style="display:flex; align-items:center; gap:0;">
                        <button id="ann-tab-announcement" onclick="switchAnnTab('announcement')"
                            style="padding:3px 14px; border-radius:20px; border:none; font-weight:700; font-size:13px; cursor:pointer; background:#1a8fc1; color:#fff; transition:all .2s; line-height:1.6;">
                            Announcement
                        </button>
                        <button id="ann-tab-news" onclick="switchAnnTab('news')"
                            style="padding:3px 14px; border-radius:20px; border:none; font-weight:700; font-size:13px; cursor:pointer; background:transparent; color:var(--cal-text-muted,#6b7280); transition:all .2s; line-height:1.6;">
                            News
                        </button>
                    </div>
                    <div class="header-controls">
                        <button id="carouselPauseBtn" onclick="toggleWelcomeCarousel()" title="Pause/Play">
                            <i class="fas fa-pause" id="carouselPauseIcon"></i>
                        </button>
                        <button title="More"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>

                {{-- Announcement carousel panel --}}
                <div id="ann-panel-announcement">

                @php
                    $slideGradients = [
                        '#1b5e20,#2e7d32,#43a047',
                        '#1a237e,#283593,#3949ab',
                        '#004d40,#00695c,#00897b',
                        '#1565c0,#1976d2,#42a5f5',
                        '#4a148c,#6a1b9a,#8e24aa',
                        '#e65100,#ef6c00,#fb8c00',
                        '#880e4f,#ad1457,#d81b60',
                        '#006064,#00838f,#00acc1',
                        '#33691e,#558b2f,#7cb342',
                        '#bf360c,#d84315,#f4511e',
                    ];
                @endphp

                <div id="welcomeCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">

                    @if(isset($myEventRequests) && $myEventRequests->count())

                        {{-- Indicators --}}
                        <div class="carousel-indicators">
                            @foreach($myEventRequests as $i => $req)
                                <button type="button" data-bs-target="#welcomeCarousel" data-bs-slide-to="{{ $i }}" {{ $i === 0 ? 'class=active aria-current=true' : '' }}></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach($myEventRequests as $i => $req)
                                @php
                                    $grad = $slideGradients[$i % count($slideGradients)];
                                    $bgStyle = $req->image_path
                                        ? 'background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url(' . asset('storage/' . $req->image_path) . ') center/cover no-repeat;'
                                        : 'background: linear-gradient(135deg, ' . $grad . ');';
                                @endphp
                                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                    <div class="slide-event" style="{{ $bgStyle }}">
                                        <div class="ev-left">
                                            <div class="ev-badge status-approved">
                                                <i class="fas fa-check-circle"></i>
                                                Approved
                                            </div>
                                            <div class="ev-title">{{ $req->title }}</div>
                                            <div class="ev-meta">
                                                <span><i class="fas fa-calendar-day"></i> {{ \Carbon\Carbon::parse($req->event_date)->format('M d, Y') }}</span>
                                                @if($req->location)
                                                <span><i class="fas fa-map-marker-alt"></i> {{ $req->location }}</span>
                                                @endif
                                                @if($req->category)
                                                <span><i class="fas fa-tag"></i> {{ $req->getCategoryLabel() !== 'Unknown' ? $req->getCategoryLabel() : ucfirst($req->category) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ev-right">
                                            @if($req->start_time && $req->end_time)
                                            <div class="ev-detail-row">
                                                <div class="ev-detail-label">Time</div>
                                                <div class="ev-detail-val">
                                                    {{ \Carbon\Carbon::parse($req->start_time)->format('g:i A') }} –
                                                    {{ \Carbon\Carbon::parse($req->end_time)->format('g:i A') }}
                                                </div>
                                            </div>
                                            @endif
                                            @if($req->department)
                                            <div class="ev-detail-row">
                                                <div class="ev-detail-label">Department</div>
                                                <div class="ev-detail-val">{{ $req->department }}</div>
                                            </div>
                                            @endif
                                            <a href="{{ route('events.my') }}" class="ev-view-btn">
                                                <i class="fas fa-external-link-alt me-1"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @else
                        {{-- Empty state --}}
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="slide-empty">
                                    <div class="se-bg"></div>
                                    <div class="se-content">
                                        <div class="se-icon"><i class="fas fa-calendar-plus"></i></div>
                                        <div class="se-title">No Event Requests Yet</div>
                                        <div class="se-sub">Submit your first event request to get started.</div>
                                        <a href="javascript:void(0)" class="se-btn" onclick="openEventModal('{{ now()->toDateString() }}')">
                                            <i class="fas fa-plus me-1"></i> New Request
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
                </div>{{-- end ann-panel-announcement --}}

                {{-- News inline list panel --}}
                <div id="ann-panel-news" style="display:none;">
                    @php
                        $newsItems = \App\Models\EventRequest::where('status', 'Approved')
                            ->where('event_date', '>=', now()->toDateString())
                            ->orderBy('event_date', 'asc')
                            ->limit(20)
                            ->get();
                    @endphp
                    @if($newsItems->isEmpty())
                        <div style="text-align:center; padding:40px 20px; color:#6b7280;">
                            <i class="fas fa-bell-slash" style="font-size:32px; margin-bottom:10px; opacity:.4; display:block;"></i>
                            <p style="font-size:14px; margin:0;">No announcements at this time.</p>
                        </div>
                    @else
                        <div style="display:flex; flex-direction:column; gap:0;">
                            @foreach($newsItems as $item)
                            @php
                                $nColors = ['#1a237e','#1b5e20','#4a148c','#1565c0','#004d40','#e65100','#880e4f','#006064','#33691e','#bf360c'];
                                $nColor = $nColors[$loop->index % count($nColors)];
                            @endphp
                            <div style="display:flex; gap:12px; align-items:center; padding:11px 16px; border-bottom:1px solid var(--cal-border,#e2e8f0); {{ $loop->last ? 'border-bottom:none;' : '' }}">
                                <div style="width:38px; height:38px; border-radius:8px; background:{{ $nColor }}; flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                                    <i class="fas fa-calendar-check" style="color:#fff; font-size:15px;"></i>
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div style="font-weight:700; font-size:13px; color:var(--cal-text,#1e293b); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $item->title }}</div>
                                    <div style="display:flex; flex-wrap:wrap; gap:8px; font-size:11px; color:#6b7280; margin-top:2px;">
                                        <span><i class="fas fa-calendar-day me-1"></i>{{ \Carbon\Carbon::parse($item->event_date)->format('M d, Y') }}</span>
                                        @if($item->location)<span><i class="fas fa-map-marker-alt me-1"></i>{{ $item->location }}</span>@endif
                                        @if($item->start_time && $item->end_time)<span><i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($item->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($item->end_time)->format('g:i A') }}</span>@endif
                                    </div>
                                </div>
                                <a href="{{ route('events.calendar') }}" style="flex-shrink:0; font-size:11px; color:#1a8fc1; font-weight:600; text-decoration:none; white-space:nowrap;">
                                    View <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>{{-- end ann-panel-news --}}

            </div>{{-- end welcome-card --}}

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e3f2fd;">
                        <i class="fas fa-calendar-check" style="color:#1565c0;"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Requests</div>
                        <div class="stat-value">{{ isset($myEventRequests) ? $myEventRequests->count() : 0 }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#e8f5e9;">
                        <i class="fas fa-check-circle" style="color:#2e7d32;"></i>
                    </div>
                    <div>
                        <div class="stat-label">Approved</div>
                        <div class="stat-value">{{ $approvedCount ?? 0 }}</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fff3e0;">
                        <i class="fas fa-clock" style="color:#e65100;"></i>
                    </div>
                    <div>
                        <div class="stat-label">Pending</div>
                        <div class="stat-value">{{ $pendingCount ?? 0 }}</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions-card">
                <div class="quick-actions-title">
                    <i class="fas fa-bolt text-warning"></i> Quick Actions
                </div>
                <div class="quick-actions-grid">
                    <a href="javascript:void(0)" class="quick-action-btn" onclick="openEventModal('{{ now()->toDateString() }}')">
                        <div class="quick-action-icon" style="background:#e3f2fd;">
                            <i class="fas fa-calendar-plus" style="color:#1565c0;"></i>
                        </div>
                        New Event Request
                    </a>
                    <a href="{{ route('events.my') }}" class="quick-action-btn">
                        <div class="quick-action-icon" style="background:#ede7f6;">
                            <i class="fas fa-list-alt" style="color:#5e35b1;"></i>
                        </div>
                        My Requests
                    </a>
                    <a href="{{ route('events.calendar') }}" class="quick-action-btn">
                        <div class="quick-action-icon" style="background:#e0f2f1;">
                            <i class="fas fa-calendar-alt" style="color:#00695c;"></i>
                        </div>
                        View Calendar
                    </a>
                    <a href="{{ route('concerns.my') }}" class="quick-action-btn">
                        <div class="quick-action-icon" style="background:#fce4ec;">
                            <i class="fas fa-exclamation-circle" style="color:#c62828;"></i>
                        </div>
                        My Concerns
                    </a>
                </div>
            </div>

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
                <div style="margin-top:10px; text-align:right;">
                    <a href="{{ route('events.calendar') }}" style="font-size:12px; color:#4f6ef7; text-decoration:none; font-weight:600;">full calendar</a>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="upcoming-card">
                <h6><i class="fas fa-calendar-day text-primary"></i> Upcoming Events</h6>
                @php
                    $upcoming = collect($calendarEvents ?? [])
                        ->filter(fn($e) => ($e['start'] ?? '') >= now()->toDateString())
                        ->sortBy('start')
                        ->take(5)
                        ->values();
                @endphp
                @if($upcoming->isEmpty())
                    <p style="font-size:12px; color:var(--cal-text-muted); text-align:center; margin:8px 0;">No upcoming events.</p>
                @else
                    @foreach($upcoming as $ev)
                    <div class="upcoming-item">
                        <div class="upcoming-dot" style="background:#26a69a;"></div>
                        <div>
                            <div class="upcoming-title">{{ $ev['title'] }}</div>
                            <div class="upcoming-date">{{ \Carbon\Carbon::parse($ev['start'])->format('M d, Y') }}</div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function switchAnnTab(tab) {
    const isAnn = tab === 'announcement';
    document.getElementById('ann-panel-announcement').style.display = isAnn ? '' : 'none';
    document.getElementById('ann-panel-news').style.display = isAnn ? 'none' : '';
    document.getElementById('carouselPauseBtn').style.display = isAnn ? '' : 'none';
    const btnAnn = document.getElementById('ann-tab-announcement');
    const btnNews = document.getElementById('ann-tab-news');
    btnAnn.style.background = isAnn ? '#1a8fc1' : 'transparent';
    btnAnn.style.color = isAnn ? '#fff' : 'var(--cal-text-muted, #6b7280)';
    btnNews.style.background = isAnn ? 'transparent' : '#1a8fc1';
    btnNews.style.color = isAnn ? 'var(--cal-text-muted, #6b7280)' : '#fff';
}

    const calEvents = @json($calendarEvents ?? []);

    let miniDate = new Date();

    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const dayLabels  = ['S','M','T','W','T','F','S'];

    function changeMiniMonth(delta) {
        miniDate.setMonth(miniDate.getMonth() + delta);
        renderMiniCalendar();
    }

    function renderMiniCalendar() {
        const year  = miniDate.getFullYear();
        const month = miniDate.getMonth();
        const today = new Date();
        const firstDay    = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();

        document.getElementById('mini-cal-label').textContent = monthNames[month].substring(0,3) + ' ' + year;

        let html = dayLabels.map(d => `<div class="day-label">${d}</div>`).join('');
        for (let i = 0; i < firstDay; i++) html += '<div></div>';

        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr  = year + '-' + String(month+1).padStart(2,'0') + '-' + String(day).padStart(2,'0');
            const isToday  = today.getDate() === day && today.getMonth() === month && today.getFullYear() === year;
            const hasEvent = calEvents.some(e => e.start && e.start.startsWith(dateStr));
            html += `<div class="mini-day ${isToday ? 'today' : ''} ${hasEvent ? 'has-event' : ''}" onclick="openEventModal('${dateStr}')">${day}</div>`;
        }

        document.getElementById('mini-cal-grid').innerHTML = html;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const theme = '{{ auth()->user()->theme ?? "light" }}';
        document.documentElement.setAttribute('data-theme', theme);
        renderMiniCalendar();
    });

    let carouselPaused = false;
    function toggleWelcomeCarousel() {
        const el = document.getElementById('welcomeCarousel');
        const icon = document.getElementById('carouselPauseIcon');
        const bsCar = bootstrap.Carousel.getOrCreateInstance(el);
        if (carouselPaused) {
            bsCar.cycle();
            icon.classList.replace('fa-play', 'fa-pause');
            carouselPaused = false;
        } else {
            bsCar.pause();
            icon.classList.replace('fa-pause', 'fa-play');
            carouselPaused = true;
        }
    }

    function openEventModal(dateStr) {
        const dateInput = document.getElementById('modal_event_date');
        if (dateInput) {
            dateInput.value = dateStr;
            dateInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        const modal = document.getElementById('eventRequestModal');
        if (modal) {
            bootstrap.Modal.getOrCreateInstance(modal).show();
        }
    }
</script>
@endsection
