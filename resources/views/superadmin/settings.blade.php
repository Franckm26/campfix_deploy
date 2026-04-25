@extends('superadmin.layout')

@section('page_title', 'System Settings')

@section('content')

<div class="row g-4">
    {{-- Superadmin Accounts --}}
    <div class="col-md-6">
        <div class="sa-card">
            <h2 style="font-size:15px;font-weight:600;color:var(--sa-text);margin:0 0 16px">
                <i class="fas fa-shield-halved me-2" style="color:var(--sa-accent2)"></i>Superadmin Accounts
            </h2>
            @forelse($superadmins as $sa)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--sa-border)">
                <div class="sa-avatar">{{ strtoupper(substr($sa->name,0,1)) }}</div>
                <div style="flex:1">
                    <div style="font-weight:500;color:var(--sa-text)">{{ $sa->name }}</div>
                    <div style="font-size:12px;color:var(--sa-muted)">{{ $sa->email }}</div>
                </div>
                @if($sa->id === auth()->id())
                    <span class="sa-badge sa-badge-green" style="font-size:10px">You</span>
                @endif
                <a href="{{ route('superadmin.users.edit', $sa->uuid) }}" class="sa-btn sa-btn-ghost sa-btn-sm">
                    <i class="fas fa-pen"></i>
                </a>
            </div>
            @empty
            <p style="color:var(--sa-muted);font-size:13px">No superadmin accounts found.</p>
            @endforelse
            <a href="{{ route('superadmin.users.create') }}" class="sa-btn sa-btn-primary mt-3" style="width:100%;justify-content:center">
                <i class="fas fa-plus"></i> Add Superadmin
            </a>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="col-md-6">
        <div class="sa-card">
            <h2 style="font-size:15px;font-weight:600;color:var(--sa-text);margin:0 0 16px">
                <i class="fas fa-bolt me-2" style="color:var(--sa-warning)"></i>Quick Actions
            </h2>
            <div style="display:flex;flex-direction:column;gap:10px">
                <a href="{{ route('superadmin.users', ['status' => 'locked']) }}" class="sa-btn sa-btn-ghost" style="justify-content:flex-start">
                    <i class="fas fa-lock" style="color:var(--sa-danger)"></i> View Locked Accounts
                </a>
                <a href="{{ route('superadmin.users', ['status' => 'deleted']) }}" class="sa-btn sa-btn-ghost" style="justify-content:flex-start">
                    <i class="fas fa-trash" style="color:var(--sa-danger)"></i> View Deleted Users
                </a>
                <a href="{{ route('superadmin.activity-logs') }}" class="sa-btn sa-btn-ghost" style="justify-content:flex-start">
                    <i class="fas fa-list-check" style="color:var(--sa-info)"></i> View All Activity Logs
                </a>
                <a href="{{ route('superadmin.superadmin-logs') }}" class="sa-btn sa-btn-ghost" style="justify-content:flex-start">
                    <i class="fas fa-eye-slash" style="color:var(--sa-accent2)"></i> View Superadmin Logs
                </a>
                <a href="{{ route('superadmin.analytics') }}" class="sa-btn sa-btn-ghost" style="justify-content:flex-start">
                    <i class="fas fa-chart-line" style="color:var(--sa-success)"></i> System Analytics
                </a>
            </div>
        </div>
    </div>

    {{-- System Info --}}
    <div class="col-12">
        <div class="sa-card">
            <h2 style="font-size:15px;font-weight:600;color:var(--sa-text);margin:0 0 16px">
                <i class="fas fa-server me-2" style="color:var(--sa-muted)"></i>System Information
            </h2>
            <div class="row g-3">
                @php
                    $info = [
                        'Laravel Version'  => app()->version(),
                        'PHP Version'      => PHP_VERSION,
                        'Environment'      => app()->environment(),
                        'Debug Mode'       => config('app.debug') ? 'Enabled' : 'Disabled',
                        'App Timezone'     => config('app.timezone'),
                        'App URL'          => config('app.url'),
                        'Database Driver'  => config('database.default'),
                        'Cache Driver'     => config('cache.default'),
                        'Queue Driver'     => config('queue.default'),
                        'Server Time'      => now()->format('M d, Y g:i:s A T'),
                    ];
                @endphp
                @foreach($info as $label => $value)
                <div class="col-md-4 col-lg-3">
                    <div style="background:rgba(255,255,255,.03);border:1px solid var(--sa-border);border-radius:8px;padding:12px">
                        <div style="font-size:11px;color:var(--sa-muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px">{{ $label }}</div>
                        <div style="font-size:13px;font-weight:500;color:var(--sa-text)">
                            @if($label === 'Debug Mode' && config('app.debug'))
                                <span style="color:var(--sa-danger)">{{ $value }}</span>
                            @elseif($label === 'Environment' && app()->environment('production'))
                                <span style="color:var(--sa-success)">{{ $value }}</span>
                            @else
                                {{ $value }}
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@endsection
