@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
<style>
    .modern-dashboard {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    [data-theme="light"] .modern-dashboard {
        background: #f8f9fa;
    }
    
    [data-theme="dark"] .modern-dashboard {
        background: #0f0f1a;
    }
    
    .dashboard-grid {
        display: grid;
        grid-template-columns: 480px 1fr;
        gap: 24px;
        align-items: start;
    }
    
    @media (max-width: 1200px) {
        .dashboard-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    @media (max-width: 992px) {
        .dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .card-modern {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        height: fit-content;
    }
    
    [data-theme="dark"] .card-modern {
        background: #1a1a2e !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    
    .card-header-modern {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .card-title-modern {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    [data-theme="dark"] .card-title-modern {
        color: #e0e0e0 !important;
    }
    
    .card-actions {
        display: flex;
        gap: 8px;
    }
    
    .icon-btn {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 4px 8px;
        border-radius: 4px;
        transition: background 0.2s;
    }
    
    .icon-btn:hover {
        background: #f0f0f0;
    }
    
    [data-theme="dark"] .icon-btn {
        color: #888 !important;
    }
    
    [data-theme="dark"] .icon-btn:hover {
        background: #2a2a45 !important;
        color: #e0e0e0 !important;
    }
    
    /* Announcement Carousel Card */
    .ann-carousel-card {
        background: var(--card-bg, #fff);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    [data-theme="dark"] .ann-carousel-card {
        background: #1a1a2e !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .ann-carousel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px 10px;
        font-size: 15px;
        font-weight: 700;
        color: #1a1a1a;
        border-bottom: 1px solid #f0f0f0;
    }
    [data-theme="dark"] .ann-carousel-header {
        color: #e0e0e0 !important;
        border-color: #2a2a45 !important;
    }
    .ann-carousel-header .header-controls {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .ann-carousel-header .header-controls button {
        background: none;
        border: none;
        color: #666;
        font-size: 15px;
        cursor: pointer;
        padding: 4px 6px;
        border-radius: 6px;
        transition: background 0.15s;
        line-height: 1;
    }
    .ann-carousel-header .header-controls button:hover {
        background: #f0f0f0;
    }
    [data-theme="dark"] .ann-carousel-header .header-controls button {
        color: #888 !important;
    }
    [data-theme="dark"] .ann-carousel-header .header-controls button:hover {
        background: #2a2a45 !important;
    }
    /* Slide styles */
    #annCarousel .carousel-item { min-height: 200px; }
    .ann-slide-event {
        display: flex !important;
        min-height: 200px;
    }
    .ann-slide-event .ev-left {
        flex: 1;
        padding: 24px 24px 24px 28px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
    }
    .ann-slide-event .ev-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        padding: 4px 12px;
        border-radius: 20px;
        margin-bottom: 12px;
        width: fit-content;
        background: rgba(76,175,80,0.25);
        color: #a5d6a7;
        border: 1px solid rgba(76,175,80,0.4);
    }
    .ann-slide-event .ev-title {
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        line-height: 1.3;
        margin-bottom: 10px;
    }
    .ann-slide-event .ev-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        font-size: 12px;
        color: rgba(255,255,255,0.75);
    }
    .ann-slide-event .ev-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .ann-slide-event .ev-right {
        width: 220px;
        flex-shrink: 0;
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(4px);
        padding: 20px 18px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        gap: 12px;
        border-left: 1px solid rgba(255,255,255,0.15);
    }
    .ann-slide-event .ev-right .ev-detail-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        color: rgba(255,255,255,0.55);
    }
    .ann-slide-event .ev-right .ev-detail-val {
        font-size: 13px;
        font-weight: 600;
        color: #fff;
    }
    .ann-slide-event .ev-right .ev-view-btn {
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
    .ann-slide-event .ev-right .ev-view-btn:hover {
        background: rgba(255,255,255,0.3);
        color: #fff;
    }
    #annCarousel .carousel-indicators {
        bottom: 6px;
        margin: 0;
        justify-content: flex-end;
        padding-right: 14px;
    }
    #annCarousel .carousel-indicators button {
        width: 7px; height: 7px;
        border-radius: 50%;
        border: none;
        background: rgba(255,255,255,0.4);
        opacity: 1;
        transition: background 0.2s;
        margin: 0 3px;
    }
    #annCarousel .carousel-indicators button.active {
        background: #fff;
    }
    .ann-slide-empty {
        min-height: 200px;
        background: linear-gradient(135deg, #1565c0 0%, #1976d2 45%, #42a5f5 100%);
        position: relative;
        overflow: hidden;
        display: flex !important;
        align-items: center;
        justify-content: center;
        padding: 30px 24px;
        text-align: center;
    }
    .ann-slide-empty .se-bg {
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,0.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 28px 28px;
    }
    .ann-slide-empty .se-content { position: relative; z-index: 1; color: #fff; }
    .ann-slide-empty .se-icon { font-size: 36px; opacity: 0.6; margin-bottom: 10px; }
    .ann-slide-empty .se-title { font-size: 18px; font-weight: 800; margin-bottom: 6px; }
    .ann-slide-empty .se-sub { font-size: 13px; color: rgba(255,255,255,0.8); }
    
    /* Projects Section */
    .projects-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    [data-theme="dark"] .projects-header a {
        color: #5e5ce6 !important;
    }
    
    [data-theme="dark"] .projects-header a:hover {
        color: #7c7aed !important;
    }
    
    .projects-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 0;
    }
    
    .project-card {
        padding: 16px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .project-card:hover {
        border-color: #b0b0b0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    [data-theme="dark"] .project-card {
        background: #1e1e38 !important;
        border-color: #2a2a45 !important;
    }
    
    [data-theme="dark"] .project-card:hover {
        border-color: #3a3a55 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }
    
    [data-theme="dark"] .project-card.new-project {
        border-color: #3a3a55 !important;
        color: #aaa !important;
    }
    
    .project-card.new-project {
        border: 2px dashed #d0d0d0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
    }
    
    .project-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        font-size: 20px;
    }
    
    .project-title {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }
    
    [data-theme="dark"] .project-title {
        color: #e0e0e0 !important;
    }
    
    .project-meta {
        font-size: 12px;
        color: #666;
    }
    
    [data-theme="dark"] .project-meta {
        color: #888 !important;
    }
    
    /* Calendar Section */
    .calendar-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }
    
    [data-theme="dark"] .calendar-header button {
        color: #5e5ce6 !important;
    }
    
    [data-theme="dark"] .calendar-header button:hover {
        color: #7c7aed !important;
    }
    
    .calendar-month {
        font-size: 14px;
        font-weight: 600;
        color: #1a1a1a;
    }
    
    [data-theme="dark"] .calendar-month {
        color: #e0e0e0 !important;
    }
    
    .calendar-days {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 8px;
        margin-bottom: 16px;
    }
    
    .calendar-day-header {
        text-align: center;
        font-size: 11px;
        color: #999;
        font-weight: 500;
        padding: 4px;
    }
    
    .calendar-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.2s;
        color: #1a1a1a;
    }
    
    [data-theme="dark"] .calendar-day {
        color: #e0e0e0 !important;
    }
    
    .calendar-day:hover {
        background: #f0f0f0;
    }
    
    [data-theme="dark"] .calendar-day:hover {
        background: #2a2a45 !important;
    }
    
    [data-theme="dark"] .calendar-day-header {
        color: #888 !important;
    }
    
    .calendar-day.today {
        background: #5e5ce6;
        color: white;
        font-weight: 600;
    }
    
    .calendar-day.has-event {
        background: #e8f5e9;
        color: #2e7d32;
        font-weight: 600;
    }
    
    .calendar-event {
        background: #f5f5f5;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 12px;
    }
    
    [data-theme="dark"] .calendar-event {
        background: #1e1e38 !important;
    }
    
    .event-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }
    
    [data-theme="dark"] .event-title {
        color: #e0e0e0 !important;
    }
    
    .event-time {
        font-size: 12px;
        color: #666;
        margin-bottom: 8px;
    }
    
    [data-theme="dark"] .event-time {
        color: #888 !important;
    }
    
    .event-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: #666;
    }
    
    [data-theme="dark"] .event-badge {
        color: #888 !important;
    }
    
    .event-avatars {
        display: flex;
        margin-top: 8px;
    }
    
    .avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid white;
        margin-left: -8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        color: white;
    }
    
    .avatar:first-child {
        margin-left: 0;
    }
    
    /* Reminders Section */
    .reminder-item {
        padding: 12px;
        border-radius: 8px;
        background: #f8f9fa;
        margin-bottom: 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    [data-theme="dark"] .reminder-item {
        background: #1e1e38 !important;
    }
    
    .reminder-text {
        font-size: 13px;
        color: #1a1a1a;
    }
    
    [data-theme="dark"] .reminder-text {
        color: #e0e0e0 !important;
    }
    
    .reminder-actions {
        display: flex;
        gap: 8px;
    }
    
    .reminder-icon-btn {
        background: none;
        border: none;
        color: #666;
        cursor: pointer;
        padding: 4px;
    }
    
    .reminder-icon-btn:hover {
        color: #1a1a1a;
    }
    
    [data-theme="dark"] .reminder-icon-btn {
        color: #888 !important;
    }
    
    [data-theme="dark"] .reminder-icon-btn:hover {
        color: #e0e0e0 !important;
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
<div class="modern-dashboard">
    <div class="dashboard-grid">
        <!-- Left Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Announcements Carousel Card -->
            @php
                $annSlideGradients = [
                    '#1a237e,#283593,#3949ab',
                    '#1b5e20,#2e7d32,#43a047',
                    '#4a148c,#6a1b9a,#8e24aa',
                    '#1565c0,#1976d2,#42a5f5',
                    '#004d40,#00695c,#00897b',
                    '#e65100,#ef6c00,#fb8c00',
                    '#880e4f,#ad1457,#d81b60',
                    '#33691e,#558b2f,#7cb342',
                    '#006064,#00838f,#00acc1',
                    '#bf360c,#d84315,#f4511e',
                ];
                $announcements = \App\Models\EventRequest::where('status', 'Approved')
                    ->where('event_date', '>=', now()->toDateString())
                    ->orderBy('event_date', 'asc')
                    ->limit(10)
                    ->get();
            @endphp

            <div class="ann-carousel-card">
                <div class="ann-carousel-header">
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
                        <button id="annCarouselPauseBtn" onclick="toggleAnnCarousel()" title="Pause/Play">
                            <i class="fas fa-pause" id="annCarouselPauseIcon"></i>
                        </button>
                        <button title="More"><i class="fas fa-ellipsis-v"></i></button>
                    </div>
                </div>

                {{-- Announcement carousel panel --}}
                <div id="ann-panel-announcement">

                    @if($announcements->count() > 0)
                        <div id="annCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
                        <div class="carousel-indicators">
                            @foreach($announcements as $i => $ann)
                                <button type="button" data-bs-target="#annCarousel" data-bs-slide-to="{{ $i }}" {{ $i === 0 ? 'class=active aria-current=true' : '' }}></button>
                            @endforeach
                        </div>

                        <div class="carousel-inner">
                            @foreach($announcements as $i => $ann)
                                @php
                                    $grad = $annSlideGradients[$i % count($annSlideGradients)];
                                    $bgStyle = $ann->image_path
                                        ? 'background: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url(' . asset('storage/' . $ann->image_path) . ') center/cover no-repeat;'
                                        : 'background: linear-gradient(135deg, ' . $grad . ');';
                                @endphp
                                <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                                    <div class="ann-slide-event" style="{{ $bgStyle }}">
                                        <div class="ev-left">
                                            <div class="ev-badge">
                                                <i class="fas fa-check-circle"></i> Approved
                                            </div>
                                            <div class="ev-title">{{ $ann->title }}</div>
                                            <div class="ev-meta">
                                                <span><i class="fas fa-calendar-day"></i> {{ \Carbon\Carbon::parse($ann->event_date)->format('M d, Y') }}</span>
                                                @if($ann->location)
                                                <span><i class="fas fa-map-marker-alt"></i> {{ $ann->location }}</span>
                                                @endif
                                                @if($ann->category)
                                                <span><i class="fas fa-tag"></i> {{ $ann->getCategoryLabel() !== 'Unknown' ? $ann->getCategoryLabel() : ucfirst($ann->category) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="ev-right">
                                            @if($ann->start_time && $ann->end_time)
                                            <div>
                                                <div class="ev-detail-label">Time</div>
                                                <div class="ev-detail-val">
                                                    {{ \Carbon\Carbon::parse($ann->start_time)->format('g:i A') }} –
                                                    {{ \Carbon\Carbon::parse($ann->end_time)->format('g:i A') }}
                                                </div>
                                            </div>
                                            @endif
                                            <a href="{{ route('events.calendar') }}" class="ev-view-btn">
                                                <i class="fas fa-external-link-alt me-1"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        </div>{{-- end #annCarousel --}}

                    @else
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <div class="ann-slide-empty">
                                    <div class="se-bg"></div>
                                    <div class="se-content">
                                        <div class="se-icon"><i class="fas fa-bullhorn"></i></div>
                                        <div class="se-title">No Announcements</div>
                                        <div class="se-sub">There are no upcoming approved events at this time.</div>
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

            </div>{{-- end ann-carousel-card --}}
            <div class="card-modern">
                <div class="calendar-header">
                    <div class="card-title-modern">
                        <i class="fas fa-calendar"></i>
                        Calendar
                    </div>
                    <button onclick="window.location.href='/events-calendar'" style="border: none; background: none; color: #5e5ce6; font-size: 13px; cursor: pointer; font-weight: 500;">View Calendar</button>
                </div>
                
                <div class="calendar-days">
                    <div class="calendar-day-header">Sun</div>
                    <div class="calendar-day-header">Mon</div>
                    <div class="calendar-day-header">Tue</div>
                    <div class="calendar-day-header">Wed</div>
                    <div class="calendar-day-header">Thu</div>
                    <div class="calendar-day-header">Fri</div>
                    <div class="calendar-day-header">Sat</div>
                    
                    @php
                        $today = now();
                        $startOfWeek = $today->copy()->startOfWeek();
                        $events = \App\Models\EventRequest::where('status', 'Approved')
                            ->whereBetween('event_date', [$startOfWeek, $startOfWeek->copy()->addDays(6)])
                            ->get();
                    @endphp
                    
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $date = $startOfWeek->copy()->addDays($i);
                            $isToday = $date->isToday();
                            $hasEvent = $events->where('event_date', $date->toDateString())->count() > 0;
                        @endphp
                        <div class="calendar-day {{ $isToday ? 'today' : '' }} {{ $hasEvent ? 'has-event' : '' }}">
                            {{ $date->format('d') }}
                        </div>
                    @endfor
                </div>
                
                @php
                    $upcomingEvent = \App\Models\EventRequest::where('status', 'Approved')
                        ->where('event_date', '>=', now()->toDateString())
                        ->orderBy('event_date', 'asc')
                        ->orderBy('start_time', 'asc')
                        ->first();
                @endphp

            </div>
        </div>
        
        <!-- Right Column -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Projects Card -->
            <div class="card-modern">
                <div class="projects-header">
                    <div class="card-title-modern">
                        <i class="fas fa-folder"></i>
                        My Concerns
                    </div>
                    <a href="{{ route('concerns.my') }}" style="text-decoration: none; color: #5e5ce6; font-size: 13px; font-weight: 500;">View All</a>
                </div>
                
                <div class="projects-grid">
                    <div class="project-card new-project" data-bs-toggle="modal" data-bs-target="#newConcernModal" style="cursor: pointer;">
                        <div>
                            <i class="fas fa-plus"></i>
                            <div style="margin-top: 8px; font-size: 13px;">Report new concern</div>
                        </div>
                    </div>
                    
                    <div class="project-card" onclick="window.location.href='{{ route('concerns.my') }}'">
                        <div class="project-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-exclamation-circle" style="color: white;"></i>
                        </div>
                        <div class="project-title">Total Concerns</div>
                        <div class="project-meta">{{ $total }} reports</div>
                    </div>
                    
                    <div class="project-card" onclick="window.location.href='{{ route('concerns.my') }}?status=Pending'">
                        <div class="project-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-clock" style="color: white;"></i>
                        </div>
                        <div class="project-title">Pending</div>
                        <div class="project-meta">{{ $pending }} awaiting review</div>
                    </div>
                    
                    <div class="project-card" onclick="window.location.href='{{ route('concerns.my') }}?status=Resolved'">
                        <div class="project-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-check-circle" style="color: white;"></i>
                        </div>
                        <div class="project-title">Resolved</div>
                        <div class="project-meta">{{ $resolved }} completed</div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity Card -->
            <div class="card-modern">
                <div class="card-header-modern">
                    <div class="card-title-modern">
                        <i class="fas fa-bell"></i>
                        Recent Activity
                    </div>
                </div>
                
                @php
                    $recentConcerns = $concerns->take(3);
                @endphp
                
                @if($recentConcerns->count() > 0)
                    <div style="margin-bottom: 12px; font-size: 13px; font-weight: 600; color: #666;">
                        <span>▼</span> Latest Updates • {{ $recentConcerns->count() }}
                    </div>
                    
                    @foreach($recentConcerns as $concern)
                    <div class="reminder-item" style="cursor: pointer;"
                         onclick="openConcernModal({
                             id: {{ $concern->id }},
                             title: {{ json_encode($concern->title ?? 'No Title') }},
                             description: {{ json_encode($concern->description ?? '') }},
                             location: {{ json_encode($concern->location ?? '') }},
                             location_type: {{ json_encode($concern->location_type ?? '') }},
                             room_number: {{ json_encode($concern->room_number ?? '') }},
                             status: {{ json_encode($concern->status) }},
                             priority: {{ json_encode($concern->priority ?? 'medium') }},
                             category: {{ json_encode(optional($concern->categoryRelation)->name ?? 'Uncategorized') }},
                             image: {{ json_encode($concern->image_path ? asset('storage/' . $concern->image_path) : null) }},
                             resolution_notes: {{ json_encode($concern->resolution_notes ?? '') }},
                             created_at: {{ json_encode($concern->created_at->format('M d, Y h:i A')) }},
                             resolved_at: {{ json_encode($concern->resolved_at ? $concern->resolved_at->format('M d, Y h:i A') : null) }},
                             assigned_to: {{ json_encode(optional($concern->assignedTo)->name ?? null) }}
                         })">
                        <div>
                            <div class="reminder-text">{{ Str::limit($concern->title ?? $concern->location, 60) }}</div>
                            <div style="font-size: 11px; color: #999; margin-top: 4px;">
                                <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $concern->status)) }}">{{ $concern->status }}</span>
                                <span style="margin-left: 8px;">{{ $concern->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="reminder-actions">
                            <button class="reminder-icon-btn" onclick="event.stopPropagation(); openConcernModal({
                                id: {{ $concern->id }},
                                title: {{ json_encode($concern->title ?? 'No Title') }},
                                description: {{ json_encode($concern->description ?? '') }},
                                location: {{ json_encode($concern->location ?? '') }},
                                location_type: {{ json_encode($concern->location_type ?? '') }},
                                room_number: {{ json_encode($concern->room_number ?? '') }},
                                status: {{ json_encode($concern->status) }},
                                priority: {{ json_encode($concern->priority ?? 'medium') }},
                                category: {{ json_encode(optional($concern->categoryRelation)->name ?? 'Uncategorized') }},
                                image: {{ json_encode($concern->image_path ? asset('storage/' . $concern->image_path) : null) }},
                                resolution_notes: {{ json_encode($concern->resolution_notes ?? '') }},
                                created_at: {{ json_encode($concern->created_at->format('M d, Y h:i A')) }},
                                resolved_at: {{ json_encode($concern->resolved_at ? $concern->resolved_at->format('M d, Y h:i A') : null) }},
                                assigned_to: {{ json_encode(optional($concern->assignedTo)->name ?? null) }}
                            })" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div style="padding: 16px; text-align: center; color: #999; font-size: 13px;">
                        No recent activity
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Concern Detail Modal -->
<div class="modal fade" id="concernDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:16px; overflow:hidden;">
            <div class="modal-header" id="concernModalHeader" style="border-bottom: 1px solid #eee;">
                <div class="d-flex align-items-center gap-3">
                    <div id="concernModalIcon" style="width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="fas fa-exclamation-circle" style="color:white;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0" id="concernModalTitle" style="font-size:16px;font-weight:700;"></h5>
                        <small id="concernModalCategory" class="text-muted"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Status bar -->
                <div id="concernModalStatusBar" style="padding:10px 20px;font-size:12px;display:flex;gap:16px;flex-wrap:wrap;border-bottom:1px solid #f0f0f0;">
                    <span id="concernModalStatusBadge"></span>
                    <span id="concernModalPriorityBadge"></span>
                    <span id="concernModalDate" class="text-muted"></span>
                </div>

                <div class="p-4">
                    <!-- Image -->
                    <div id="concernModalImageWrap" style="display:none;margin-bottom:16px;">
                        <img id="concernModalImage" src="" alt="Concern photo"
                             style="width:100%;max-height:260px;object-fit:cover;border-radius:10px;cursor:pointer;"
                             onclick="window.open(this.src,'_blank')">
                    </div>

                    <!-- Location -->
                    <div id="concernModalLocationWrap" class="mb-3" style="display:none;">
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:#999;letter-spacing:.5px;margin-bottom:4px;">Location</div>
                        <div id="concernModalLocation" style="font-size:14px;"></div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:#999;letter-spacing:.5px;margin-bottom:4px;">Description</div>
                        <div id="concernModalDescription" style="font-size:14px;line-height:1.7;white-space:pre-wrap;"></div>
                    </div>

                    <!-- Assigned to -->
                    <div id="concernModalAssignedWrap" class="mb-3" style="display:none;">
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:#999;letter-spacing:.5px;margin-bottom:4px;">Assigned To</div>
                        <div id="concernModalAssigned" style="font-size:14px;"></div>
                    </div>

                    <!-- Resolution notes -->
                    <div id="concernModalResolutionWrap" style="display:none;">
                        <div style="font-size:11px;font-weight:600;text-transform:uppercase;color:#999;letter-spacing:.5px;margin-bottom:4px;">Resolution Notes</div>
                        <div id="concernModalResolution" style="font-size:14px;line-height:1.7;padding:12px;background:#f8f9fa;border-radius:8px;border-left:3px solid #198754;white-space:pre-wrap;"></div>
                        <div id="concernModalResolvedAt" class="text-muted mt-1" style="font-size:11px;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f0f0f0;">
                <a id="concernModalViewFull" href="#" class="btn btn-primary btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> View Full Details
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- New Concern Modal -->
<div class="modal fade" id="newConcernModal" tabindex="-1" aria-labelledby="newConcernModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newConcernModalLabel">Submit New Concern</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('concerns.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_title" class="form-label">Title (Optional)</label>
                        <input type="text" class="form-control" id="new_title" name="title" 
                            placeholder="Brief title for your concern">
                    </div>

                    <div class="mb-3">
                        <label for="new_category_id" class="form-label">Category *</label>
                        <select class="form-select" id="new_category_id" name="category_id" required>
                            <option value="" disabled selected>Select a category</option>
                            @php
                                $categories = \App\Models\Category::all();
                            @endphp
                            @foreach($categories as $category)
                                @if(auth()->user()->role === 'student' && strtolower($category->name) === 'rooms')
                                    @continue
                                @endif
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location field (shown for non-Rooms categories) -->
                    <div class="mb-3" id="new_location_container">
                        <label for="new_location" class="form-label">Location *</label>
                        <input type="text" class="form-control" id="new_location" name="location"
                            placeholder="e.g., Room 201, Building A, Cafeteria" required>
                    </div>

                    <!-- Location Type (shown only for Rooms category) -->
                    <div class="mb-3" id="new_location_type_container" style="display: none;">
                        <label for="new_location_type" class="form-label">Location *</label>
                        <select class="form-select" id="new_location_type" name="location_type">
                            <option value="">Select location type</option>
                            <option value="Room">Room</option>
                            <option value="AVR">AVR</option>
                            <option value="Computer Laboratory">Computer Laboratory</option>
                        </select>
                    </div>

                    <!-- Room Number (shown only when location type is selected) -->
                    <div class="mb-3" id="new_room_number_container" style="display: none;">
                        <label for="new_room_number" class="form-label">Room Number *</label>
                        <select class="form-select" id="new_room_number" name="room_number">
                            <option value="">Select room number</option>
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
                    </div>

                    <div class="mb-3">
                        <label for="new_description" class="form-label">Description *</label>
                        <textarea class="form-control" id="new_description" name="description" 
                            rows="3" placeholder="Describe the problem in detail..." required maxlength="500"></textarea>
                        <small class="text-muted d-block text-end" id="new_description_count">0 / 500</small>
                    </div>

                    <div class="mb-3">
                        <label for="new_image" class="form-label">Upload Photo (Optional)</label>
                        <input type="file" class="form-control" id="new_image" name="image" 
                            accept="image/*">
                        <small class="text-muted d-block" style="font-size: 12px;">Supported formats: JPEG, PNG, JPG (Max 2MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Concern</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let annCarouselPaused = false;
function switchAnnTab(tab) {
    const isAnn = tab === 'announcement';
    document.getElementById('ann-panel-announcement').style.display = isAnn ? '' : 'none';
    document.getElementById('ann-panel-news').style.display = isAnn ? 'none' : '';
    document.getElementById('annCarouselPauseBtn').style.display = isAnn ? '' : 'none';
    const btnAnn = document.getElementById('ann-tab-announcement');
    const btnNews = document.getElementById('ann-tab-news');
    btnAnn.style.background = isAnn ? '#1a8fc1' : 'transparent';
    btnAnn.style.color = isAnn ? '#fff' : 'var(--cal-text-muted, #6b7280)';
    btnNews.style.background = isAnn ? 'transparent' : '#1a8fc1';
    btnNews.style.color = isAnn ? 'var(--cal-text-muted, #6b7280)' : '#fff';
}
function toggleAnnCarousel() {
    const el = document.getElementById('annCarousel');
    const icon = document.getElementById('annCarouselPauseIcon');
    if (!el) return;
    const bsCar = bootstrap.Carousel.getOrCreateInstance(el);
    if (annCarouselPaused) {
        bsCar.cycle();
        icon.classList.replace('fa-play', 'fa-pause');
        annCarouselPaused = false;
    } else {
        bsCar.pause();
        icon.classList.replace('fa-pause', 'fa-play');
        annCarouselPaused = true;
    }
}

function openConcernModal(data) {
    // Title & category
    document.getElementById('concernModalTitle').textContent    = data.title || 'No Title';
    document.getElementById('concernModalCategory').textContent = data.category || '';

    // Status badge
    const statusColors = {
        'pending':     '#f59e0b',
        'assigned':    '#3b82f6',
        'in-progress': '#f97316',
        'in progress': '#f97316',
        'resolved':    '#10b981',
        'closed':      '#6b7280',
    };
    const statusKey = (data.status || '').toLowerCase();
    const statusColor = statusColors[statusKey] || '#6b7280';
    document.getElementById('concernModalStatusBadge').innerHTML =
        `<span style="background:${statusColor};color:#fff;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;">${data.status}</span>`;

    // Priority badge
    const priorityColors = { low: '#10b981', medium: '#f59e0b', high: '#f97316', urgent: '#ef4444' };
    const pColor = priorityColors[(data.priority || '').toLowerCase()] || '#6b7280';
    document.getElementById('concernModalPriorityBadge').innerHTML =
        `<span style="background:${pColor}22;color:${pColor};padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;border:1px solid ${pColor}44;">
            <i class="fas fa-flag" style="font-size:9px;margin-right:3px;"></i>${data.priority ? data.priority.charAt(0).toUpperCase() + data.priority.slice(1) : ''}
        </span>`;

    // Header icon color
    document.getElementById('concernModalIcon').style.background =
        `linear-gradient(135deg, ${pColor} 0%, ${statusColor} 100%)`;

    // Date
    document.getElementById('concernModalDate').innerHTML =
        `<i class="fas fa-clock" style="margin-right:4px;"></i>${data.created_at}`;

    // Image
    const imgWrap = document.getElementById('concernModalImageWrap');
    if (data.image) {
        document.getElementById('concernModalImage').src = data.image;
        imgWrap.style.display = 'block';
    } else {
        imgWrap.style.display = 'none';
    }

    // Location
    const locWrap = document.getElementById('concernModalLocationWrap');
    let locationText = data.location || '';
    if (data.location_type) locationText = data.location_type + (data.room_number ? ' ' + data.room_number : '');
    if (locationText) {
        document.getElementById('concernModalLocation').innerHTML =
            `<i class="fas fa-map-marker-alt text-muted me-1"></i>${locationText}`;
        locWrap.style.display = 'block';
    } else {
        locWrap.style.display = 'none';
    }

    // Description
    document.getElementById('concernModalDescription').textContent = data.description || 'No description provided.';

    // Assigned to
    const assignedWrap = document.getElementById('concernModalAssignedWrap');
    if (data.assigned_to) {
        document.getElementById('concernModalAssigned').innerHTML =
            `<i class="fas fa-user-cog text-muted me-1"></i>${data.assigned_to}`;
        assignedWrap.style.display = 'block';
    } else {
        assignedWrap.style.display = 'none';
    }

    // Resolution notes
    const resWrap = document.getElementById('concernModalResolutionWrap');
    if (data.resolution_notes) {
        document.getElementById('concernModalResolution').textContent = data.resolution_notes;
        document.getElementById('concernModalResolvedAt').innerHTML = data.resolved_at
            ? `<i class="fas fa-check-circle text-success me-1"></i>Resolved on ${data.resolved_at}` : '';
        resWrap.style.display = 'block';
    } else {
        resWrap.style.display = 'none';
    }

    // View full link
    document.getElementById('concernModalViewFull').href = `/concerns/${data.id}`;

    // Open modal
    const modal = new bootstrap.Modal(document.getElementById('concernDetailModal'));
    modal.show();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // close any open modals
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Dashboard is ready
    console.log('Student dashboard loaded');

    // Description character counter
    const descTextarea = document.getElementById('new_description');
    const descCount = document.getElementById('new_description_count');
    if (descTextarea && descCount) {
        descTextarea.addEventListener('input', function() {
            descCount.textContent = this.value.length + ' / 500';
        });
    }
    // Handle category change for new concern modal
    const categorySelect = document.getElementById('new_category_id');
    const locationContainer = document.getElementById('new_location_container');
    const locationTypeContainer = document.getElementById('new_location_type_container');
    const roomNumberContainer = document.getElementById('new_room_number_container');
    const locationInput = document.getElementById('new_location');
    const locationTypeSelect = document.getElementById('new_location_type');
    const roomNumberSelect = document.getElementById('new_room_number');
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const categoryName = selectedOption.text.trim().toLowerCase();
            
            if (categoryName === 'rooms') {
                // Show location type dropdown, hide regular location input
                locationContainer.style.display = 'none';
                locationTypeContainer.style.display = 'block';
                locationInput.removeAttribute('required');
                locationTypeSelect.setAttribute('required', 'required');
            } else {
                // Show regular location input, hide location type dropdown
                locationContainer.style.display = 'block';
                locationTypeContainer.style.display = 'none';
                roomNumberContainer.style.display = 'none';
                locationInput.setAttribute('required', 'required');
                locationTypeSelect.removeAttribute('required');
                roomNumberSelect.removeAttribute('required');
            }
        });
    }
    
    if (locationTypeSelect) {
        locationTypeSelect.addEventListener('change', function() {
            if (this.value) {
                roomNumberContainer.style.display = 'block';
                roomNumberSelect.setAttribute('required', 'required');
            } else {
                roomNumberContainer.style.display = 'none';
                roomNumberSelect.removeAttribute('required');
            }
        });
    }
});
</script>
@endsection
