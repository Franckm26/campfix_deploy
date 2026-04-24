@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    .stat-card { border-left: 4px solid; border-radius: 8px; }
    .stat-card.blue   { border-color: #0d6efd; }
    .stat-card.green  { border-color: #198754; }
    .stat-card.orange { border-color: #fd7e14; }
    .stat-card.red    { border-color: #dc3545; }
    .stat-card.purple { border-color: #6f42c1; }
    .role-badge { font-size: 12px; padding: 4px 10px; border-radius: 20px; }
    .trend-bar-wrap { height: 6px; background: #e9ecef; border-radius: 3px; overflow: hidden; }
    .trend-bar { height: 100%; border-radius: 3px; background: #0d6efd; transition: width .4s; }
    .avatar-sm { width: 34px; height: 34px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px; }
</style>
@endsection

@section('page_title')
<div style="display:flex;align-items:center;gap:12px">
    <img src="{{ asset('Campfix/Images/images.png') }}" alt="STI Logo" style="height:40px">
    <h2 style="margin:0">Home</h2>
</div>
@endsection

@section('content')
<div class="container-fluid px-2 px-md-3">

    {{-- Header --}}
    <div class="row mb-3 mb-md-4 align-items-center">
        <div class="col-auto">
            <a href="{{ route('admin.users') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-users me-1"></i> Manage Users
            </a>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card blue h-100">
                <div class="card-body py-3 px-3">
                    <div class="text-muted small mb-1">Total Users</div>
                    <h3 class="mb-0 fw-bold">{{ $totalUsers }}</h3>
                    <small class="text-muted" style="font-size:10px">All registered accounts</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card green h-100">
                <div class="card-body py-3 px-3">
                    <div class="text-muted small mb-1">Active</div>
                    <h3 class="mb-0 fw-bold">{{ $activeUsers }}</h3>
                    <small class="text-muted" style="font-size:10px">Currently active accounts</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card orange h-100">
                <div class="card-body py-3 px-3">
                    <div class="text-muted small mb-1">Archived Accounts</div>
                    <h3 class="mb-0 fw-bold">{{ $archivedUsers }}</h3>
                    <small class="text-muted" style="font-size:10px">Archived accounts</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card red h-100" onclick="document.getElementById('lockedModal').style.display='flex'" style="cursor:pointer" title="Click to view locked accounts">
                <div class="card-body py-3 px-3">
                    <div class="text-muted small mb-1">Locked Accounts</div>
                    <h3 class="mb-0 fw-bold text-danger">{{ $lockedUsers }}</h3>
                    @if($lockedUsers > 0)
                        <small class="text-danger" style="font-size:10px"><i class="fas fa-exclamation-circle"></i> Needs attention</small>
                    @else
                        <small class="text-muted" style="font-size:10px">No locked accounts</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card purple h-100">
                <div class="card-body py-3 px-3">
                    <div class="text-muted small mb-1">Force Reset</div>
                    <h3 class="mb-0 fw-bold">{{ $forceChangeUsers }}</h3>
                    <small class="text-muted" style="font-size:10px">Password reset required</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card stat-card blue h-100">
                <div class="card-body py-3 px-3">
                    <div class="text-muted small mb-1">New This Month</div>
                    <h3 class="mb-0 fw-bold">{{ $newUsersThisMonth }}</h3>
                    @if($newUsersLastMonth > 0)
                        @php $diff = $newUsersThisMonth - $newUsersLastMonth; @endphp
                        <small class="{{ $diff >= 0 ? 'text-success' : 'text-danger' }}" style="font-size:11px">
                            {{ $diff >= 0 ? '▲' : '▼' }} {{ abs($diff) }} vs last month
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">

        {{-- Users by Role --}}
        <div class="col-12 col-md-5">
            <div class="card h-100">
                <div class="card-header py-2 px-3">
                    <h6 class="mb-0"><i class="fas fa-id-badge me-2 text-primary"></i>Users by Role</h6>
                </div>
                <div class="card-body px-3 py-2">
                    @php
                        $roleColors = [
                            'mis'                 => ['bg' => 'bg-primary',   'label' => 'MIS'],
                            'school_admin'        => ['bg' => 'bg-danger',    'label' => 'School Admin'],
                            'academic_head'       => ['bg' => 'bg-warning',   'label' => 'Academic Head'],
                            'program_head'        => ['bg' => 'bg-info',      'label' => 'Program Head'],
                            'faculty'             => ['bg' => 'bg-success',   'label' => 'Faculty'],
                            'maintenance'         => ['bg' => 'bg-secondary', 'label' => 'Maintenance'],
                            'student'             => ['bg' => 'bg-dark',      'label' => 'Student'],
                            'building_admin'      => ['bg' => 'bg-primary',   'label' => 'Building Admin'],
                            'principal_assistant' => ['bg' => 'bg-warning',   'label' => 'Principal Asst.'],
                        ];
                        $maxCount = $usersByRole->max() ?: 1;
                    @endphp
                    @forelse($usersByRole as $role => $count)
                        @php $meta = $roleColors[$role] ?? ['bg' => 'bg-secondary', 'label' => ucfirst($role)]; @endphp
                        <div class="mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge role-badge {{ $meta['bg'] }}">{{ $meta['label'] }}</span>
                                <span class="fw-semibold small">{{ $count }}</span>
                            </div>
                            <div class="trend-bar-wrap">
                                <div class="trend-bar {{ $meta['bg'] }}" style="width:{{ round(($count / $maxCount) * 100) }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">No users found.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Registration Trend --}}
        <div class="col-12 col-md-7">
            <div class="card h-100">
                <div class="card-header py-2 px-3">
                    <h6 class="mb-0"><i class="fas fa-chart-line me-2 text-success"></i>New Registrations — Last 6 Months</h6>
                </div>
                <div class="card-body px-3 py-3">
                    @php $trendMax = $registrationTrend->max('count') ?: 1; @endphp
                    <div class="d-flex align-items-end gap-2" style="height:90px">
                        @foreach($registrationTrend as $t)
                            @php $pct = round(($t['count'] / $trendMax) * 100); @endphp
                            <div class="flex-fill text-center d-flex flex-column align-items-center justify-content-end" style="height:100%">
                                <span class="small fw-semibold mb-1" style="font-size:11px">{{ $t['count'] }}</span>
                                <div style="width:100%;height:{{ max($pct, 4) }}%;background:#0d6efd;border-radius:4px 4px 0 0;min-height:4px"></div>
                                <span class="text-muted mt-1" style="font-size:10px">{{ $t['month'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Registrations --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center py-2 px-3">
            <h6 class="mb-0"><i class="fas fa-user-plus me-2 text-info"></i>Recent Registrations</h6>
            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:12px">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:13px">
                    <thead class="table-light">
                        <tr>
                            <th class="checkbox-col"><input type="checkbox" id="selectAllUsers" onclick="toggleAllUsers(this)"></th>
                            <th>User</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $u)
                            @php
                                $meta = $roleColors[$u->role] ?? ['bg' => 'bg-secondary', 'label' => ucfirst($u->role)];
                                $initials = collect(explode(' ', $u->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
                                $bgList = ['#0d6efd','#198754','#fd7e14','#6f42c1','#0dcaf0','#dc3545'];
                                $bg = $bgList[crc32($u->email) % count($bgList)];
                            @endphp
                            <tr>
                                <td class="checkbox-col"><input type="checkbox" class="user-checkbox" value="{{ $u->id }}"></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($u->profile_picture)
                                            <img src="{{ asset('storage/'.$u->profile_picture) }}" class="avatar-sm" style="object-fit:cover">
                                        @else
                                            <span class="avatar-sm text-white" style="background:{{ $bg }}">{{ $initials }}</span>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $u->name }}</div>
                                            <div class="text-muted" style="font-size:11px">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge role-badge {{ $meta['bg'] }}">{{ $meta['label'] }}</span></td>
                                <td>
                                    @if($u->is_archived)
                                        <span class="badge bg-warning text-dark">Archived</span>
                                    @elseif($u->locked_until && now()->lessThan($u->locked_until))
                                        <span class="badge bg-danger">Locked</span>
                                    @elseif($u->force_password_change)
                                        <span class="badge bg-secondary">Pending Reset</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $u->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row g-2 mb-3">
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.users') }}" class="card text-decoration-none text-center h-100 p-3">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <div class="small fw-semibold">Manage Users</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('admin.logs') }}" class="card text-decoration-none text-center h-100 p-3">
                <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                <div class="small fw-semibold">Activity Logs</div>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('settings.index') }}" class="card text-decoration-none text-center h-100 p-3">
                <i class="fas fa-cog fa-2x text-secondary mb-2"></i>
                <div class="small fw-semibold">Settings</div>
            </a>
        </div>
    </div>

</div>

{{-- Locked Accounts Modal --}}
<div id="lockedModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1055;align-items:center;justify-content:center;">
    <div class="card shadow-lg" style="width:95%;max-width:900px;max-height:90vh;display:flex;flex-direction:column;border-radius:12px;overflow:hidden">
        <div class="d-flex align-items-center justify-content-between px-4 py-3" style="background:#dc3545;color:#fff">
            <div>
                <h5 class="mb-0 fw-bold"><i class="fas fa-lock me-2"></i>Locked Accounts</h5>
                <small style="opacity:.85">{{ $lockedUsers }} account(s) require attention</small>
            </div>
            <button onclick="document.getElementById('lockedModal').style.display='none'" class="btn btn-sm btn-light"><i class="fas fa-times"></i></button>
        </div>
        <div style="overflow-y:auto;flex:1">
            @if($lockedUsersList->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                    No locked accounts.
                </div>
            @else
                <table class="table table-hover mb-0" style="font-size:15px">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="ps-3">User</th>
                            <th>Role</th>
                            <th>Failed Attempts</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lockedUsersList as $lu)
                            @php
                                $initials = collect(explode(' ', $lu->name))->map(fn($w) => strtoupper($w[0] ?? ''))->take(2)->implode('');
                                $bgList = ['#dc3545','#fd7e14','#6f42c1','#0d6efd'];
                                $bg = $bgList[crc32($lu->email) % count($bgList)];
                            @endphp
                            <tr id="locked-row-{{ $lu->id }}">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        @if($lu->profile_picture)
                                            <img src="{{ asset('storage/'.$lu->profile_picture) }}" class="avatar-sm" style="object-fit:cover">
                                        @else
                                            <span class="avatar-sm text-white" style="background:{{ $bg }}">{{ $initials }}</span>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $lu->name }}</div>
                                            <div class="text-muted" style="font-size:11px">{{ $lu->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $lu->role)) }}</span></td>
                                <td class="text-danger fw-semibold">{{ $lu->failed_login_attempts }}</td>
                                <td>
                                    <button class="btn btn-sm btn-success" onclick="unlockUser('{{ $lu->uuid }}', {{ $lu->id }}, '{{ addslashes($lu->name) }}')">
                                        <i class="fas fa-unlock"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function unlockUser(uuid, userId, name) {
    if (!confirm('Unlock account for ' + name + '?')) return;

    fetch('/admin/users/unlock/' + uuid, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Remove the row from the modal
            const row = document.getElementById('locked-row-' + userId);
            if (row) row.remove();

            // Update the counter
            const remaining = document.querySelectorAll('#lockedModal tbody tr').length;
            document.querySelector('#lockedModal small').textContent = remaining + ' account(s) require attention';

            // Update the stat card
            const card = document.querySelector('.stat-card.red h3');
            if (card) card.textContent = remaining;

            if (remaining === 0) {
                document.querySelector('#lockedModal tbody').innerHTML =
                    '<tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-check-circle text-success me-1"></i>No locked accounts.</td></tr>';
                const badge = document.querySelector('.stat-card.red small');
                if (badge) badge.remove();
            }
        }
    })
    .catch(() => alert('Failed to unlock. Please try again.'));
}


// Checkbox functions for bulk actions
function toggleAllUsers(checkbox) {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function getSelectedUsers() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}
</script>
@endsection
