

<?php $__env->startSection('page_title', 'System Dashboard'); ?>

<?php $__env->startSection('extra_styles'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .module-card {
        background: var(--sa-card);
        border: 1px solid var(--sa-border);
        border-radius: 12px;
        padding: 20px;
        text-decoration: none;
        display: block;
        transition: all .2s;
        position: relative;
        overflow: hidden;
    }
    .module-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
    }
    .module-card:hover {
        transform: translateY(-2px);
        border-color: var(--sa-accent);
        box-shadow: 0 8px 24px rgba(124,58,237,.15);
    }
    .module-card.purple::before { background: linear-gradient(90deg, var(--sa-accent), var(--sa-accent2)); }
    .module-card.blue::before   { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .module-card.green::before  { background: linear-gradient(90deg, #22c55e, #4ade80); }
    .module-card.red::before    { background: linear-gradient(90deg, #ef4444, #f87171); }
    .module-card.yellow::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .module-card.teal::before   { background: linear-gradient(90deg, #14b8a6, #2dd4bf); }
    .module-card.pink::before   { background: linear-gradient(90deg, #ec4899, #f472b6); }
    .module-card.indigo::before { background: linear-gradient(90deg, #6366f1, #818cf8); }

    .module-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        margin-bottom: 12px;
    }
    .module-title { font-size: 14px; font-weight: 600; color: var(--sa-text); margin-bottom: 4px; }
    .module-count { font-size: 28px; font-weight: 700; color: var(--sa-text); line-height: 1; margin-bottom: 4px; }
    .module-sub   { font-size: 11px; color: var(--sa-muted); }

    .section-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.2px;
        text-transform: uppercase;
        color: var(--sa-muted);
        margin: 28px 0 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .section-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--sa-border);
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 50%,#4c1d95 100%);border-radius:14px;padding:28px 32px;margin-bottom:28px;position:relative;overflow:hidden">
    <div style="position:absolute;top:-20px;right:-20px;width:180px;height:180px;background:rgba(255,255,255,.04);border-radius:50%"></div>
    <div style="position:absolute;bottom:-40px;right:60px;width:120px;height:120px;background:rgba(255,255,255,.03);border-radius:50%"></div>
    <div style="position:relative">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
            <span style="background:rgba(255,255,255,.15);padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#c4b5fd">
                <i class="fas fa-shield-halved me-1"></i>Superadmin Access
            </span>
        </div>
        <h1 style="font-size:24px;font-weight:700;color:#fff;margin:0 0 6px">Welcome back, <?php echo e(auth()->user()->name); ?></h1>
        <p style="color:#c4b5fd;margin:0;font-size:13px">Full system control · All modules · Hidden audit trail · <?php echo e(now()->format('l, F j, Y')); ?></p>
    </div>
</div>


<div class="section-title"><i class="fas fa-users"></i> Users</div>
<div class="row g-3 mb-2">
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.users')); ?>" class="module-card blue">
            <div class="module-icon" style="background:rgba(59,130,246,.15);color:#60a5fa"><i class="fas fa-users"></i></div>
            <div class="module-title">Total Users</div>
            <div class="module-count"><?php echo e(number_format($stats['total_users'])); ?></div>
            <div class="module-sub">All accounts</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.users', ['status'=>'active'])); ?>" class="module-card green">
            <div class="module-icon" style="background:rgba(34,197,94,.15);color:#4ade80"><i class="fas fa-user-check"></i></div>
            <div class="module-title">Active</div>
            <div class="module-count"><?php echo e(number_format($stats['active_users'])); ?></div>
            <div class="module-sub">Not archived/deleted</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.users', ['status'=>'archived'])); ?>" class="module-card yellow">
            <div class="module-icon" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class="fas fa-box-archive"></i></div>
            <div class="module-title">Archived</div>
            <div class="module-count"><?php echo e(number_format($stats['archived_users'])); ?></div>
            <div class="module-sub">Soft-archived</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.users', ['status'=>'deleted'])); ?>" class="module-card red">
            <div class="module-icon" style="background:rgba(239,68,68,.15);color:#f87171"><i class="fas fa-user-slash"></i></div>
            <div class="module-title">Deleted</div>
            <div class="module-count"><?php echo e(number_format($stats['deleted_users'])); ?></div>
            <div class="module-sub">Soft-deleted</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.users', ['status'=>'locked'])); ?>" class="module-card red">
            <div class="module-icon" style="background:rgba(239,68,68,.15);color:#f87171"><i class="fas fa-lock"></i></div>
            <div class="module-title">Locked</div>
            <div class="module-count" style="<?php echo e($stats['locked_users'] > 0 ? 'color:#f87171' : ''); ?>"><?php echo e(number_format($stats['locked_users'])); ?></div>
            <div class="module-sub"><?php echo e($stats['locked_users'] > 0 ? 'Needs attention' : 'All clear'); ?></div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.users.create')); ?>" class="module-card purple">
            <div class="module-icon" style="background:rgba(124,58,237,.15);color:#a855f7"><i class="fas fa-user-plus"></i></div>
            <div class="module-title">Create User</div>
            <div class="module-count" style="font-size:20px;padding-top:4px"><i class="fas fa-plus"></i></div>
            <div class="module-sub">Add new account</div>
        </a>
    </div>
</div>


<div class="section-title"><i class="fas fa-triangle-exclamation"></i> Concerns</div>
<div class="row g-3 mb-2">
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.concerns')); ?>" class="module-card purple">
            <div class="module-icon" style="background:rgba(124,58,237,.15);color:#a855f7"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="module-title">All Concerns</div>
            <div class="module-count"><?php echo e(number_format($stats['total_concerns'])); ?></div>
            <div class="module-sub">System-wide total</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.concerns', ['status'=>'Pending'])); ?>" class="module-card yellow">
            <div class="module-icon" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class="fas fa-clock"></i></div>
            <div class="module-title">Open</div>
            <div class="module-count"><?php echo e(number_format($stats['open_concerns'])); ?></div>
            <div class="module-sub">Unresolved</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.concerns', ['status'=>'Resolved'])); ?>" class="module-card green">
            <div class="module-icon" style="background:rgba(34,197,94,.15);color:#4ade80"><i class="fas fa-circle-check"></i></div>
            <div class="module-title">Resolved</div>
            <div class="module-count"><?php echo e(number_format($stats['resolved_concerns'])); ?></div>
            <div class="module-sub">All time</div>
        </a>
    </div>
</div>


<div class="section-title"><i class="fas fa-file-lines"></i> Reports</div>
<div class="row g-3 mb-2">
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.reports')); ?>" class="module-card blue">
            <div class="module-icon" style="background:rgba(59,130,246,.15);color:#60a5fa"><i class="fas fa-file-lines"></i></div>
            <div class="module-title">All Reports</div>
            <div class="module-count"><?php echo e(number_format($stats['total_reports'])); ?></div>
            <div class="module-sub">System-wide total</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.reports', ['status'=>'Pending'])); ?>" class="module-card yellow">
            <div class="module-icon" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class="fas fa-hourglass-half"></i></div>
            <div class="module-title">Open</div>
            <div class="module-count"><?php echo e(number_format($stats['open_reports'])); ?></div>
            <div class="module-sub">Unresolved</div>
        </a>
    </div>
</div>


<div class="section-title"><i class="fas fa-calendar-days"></i> Event Requests</div>
<div class="row g-3 mb-2">
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.events')); ?>" class="module-card teal">
            <div class="module-icon" style="background:rgba(20,184,166,.15);color:#2dd4bf"><i class="fas fa-calendar-days"></i></div>
            <div class="module-title">All Events</div>
            <div class="module-count"><?php echo e(number_format($stats['total_events'])); ?></div>
            <div class="module-sub">System-wide total</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.events', ['status'=>'Pending'])); ?>" class="module-card yellow">
            <div class="module-icon" style="background:rgba(245,158,11,.15);color:#fbbf24"><i class="fas fa-calendar-clock"></i></div>
            <div class="module-title">Pending</div>
            <div class="module-count"><?php echo e(number_format($stats['pending_events'])); ?></div>
            <div class="module-sub">Awaiting approval</div>
        </a>
    </div>
</div>


<div class="section-title"><i class="fas fa-gear"></i> System</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.categories')); ?>" class="module-card indigo">
            <div class="module-icon" style="background:rgba(99,102,241,.15);color:#818cf8"><i class="fas fa-tags"></i></div>
            <div class="module-title">Categories</div>
            <div class="module-count"><?php echo e(number_format($stats['total_categories'])); ?></div>
            <div class="module-sub">Active categories</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.activity-logs')); ?>" class="module-card pink">
            <div class="module-icon" style="background:rgba(236,72,153,.15);color:#f472b6"><i class="fas fa-list-check"></i></div>
            <div class="module-title">Activity Logs</div>
            <div class="module-count"><?php echo e(number_format($stats['total_activity_logs'])); ?></div>
            <div class="module-sub">All system events</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.superadmin-logs')); ?>" class="module-card purple">
            <div class="module-icon" style="background:rgba(124,58,237,.15);color:#a855f7"><i class="fas fa-eye-slash"></i></div>
            <div class="module-title">SA Logs</div>
            <div class="module-count" style="font-size:18px;padding-top:6px">Private</div>
            <div class="module-sub">Hidden from admins</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.analytics')); ?>" class="module-card green">
            <div class="module-icon" style="background:rgba(34,197,94,.15);color:#4ade80"><i class="fas fa-chart-line"></i></div>
            <div class="module-title">Analytics</div>
            <div class="module-count" style="font-size:18px;padding-top:6px">Charts</div>
            <div class="module-sub">12-month trends</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('superadmin.settings')); ?>" class="module-card teal">
            <div class="module-icon" style="background:rgba(20,184,166,.15);color:#2dd4bf"><i class="fas fa-gear"></i></div>
            <div class="module-title">Settings</div>
            <div class="module-count" style="font-size:18px;padding-top:6px">System</div>
            <div class="module-sub">Config & info</div>
        </a>
    </div>
    <div class="col-6 col-md-3 col-xl-2">
        <a href="<?php echo e(route('dashboard')); ?>" class="module-card" style="border-color:var(--sa-border)">
            <div class="module-icon" style="background:rgba(255,255,255,.06);color:var(--sa-muted)"><i class="fas fa-arrow-left"></i></div>
            <div class="module-title" style="color:var(--sa-muted)">Back to App</div>
            <div class="module-count" style="font-size:18px;padding-top:6px;color:var(--sa-muted)">App</div>
            <div class="module-sub">Regular dashboard</div>
        </a>
    </div>
</div>


<div class="row g-3 mb-4">
    
    <div class="col-md-6">
        <div class="sa-card">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-users me-2" style="color:#60a5fa"></i>User Registrations — Last 6 Months
            </div>
            <canvas id="regChart" height="160"></canvas>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="sa-card">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-triangle-exclamation me-2" style="color:#a855f7"></i>Concerns — Last 6 Months
            </div>
            <canvas id="concernChart" height="160"></canvas>
        </div>
    </div>
</div>


<div class="row g-3">
    
    <div class="col-md-4">
        <div class="sa-card h-100">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-chart-pie me-2" style="color:var(--sa-accent2)"></i>Users by Role
            </div>
            <?php
                $roleColors = [
                    'student'             => 'sa-badge-blue',
                    'faculty'             => 'sa-badge-green',
                    'maintenance'         => 'sa-badge-yellow',
                    'mis'                 => 'sa-badge-purple',
                    'school_admin'        => 'sa-badge-red',
                    'building_admin'      => 'sa-badge-gray',
                    'academic_head'       => 'sa-badge-blue',
                    'program_head'        => 'sa-badge-green',
                    'principal_assistant' => 'sa-badge-yellow',
                    'superadmin'          => 'sa-badge-purple',
                ];
            ?>
            <?php $__empty_1 = true; $__currentLoopData = $usersByRole; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--sa-border)">
                <span class="sa-badge <?php echo e($roleColors[$role] ?? 'sa-badge-gray'); ?>"><?php echo e(str_replace('_', ' ', ucfirst($role))); ?></span>
                <span style="font-weight:700;color:var(--sa-text)"><?php echo e($count); ?></span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p style="color:var(--sa-muted);font-size:13px">No users found.</p>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="col-md-4">
        <div class="sa-card h-100">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-user-shield me-2" style="color:var(--sa-warning)"></i>Admin Accounts
            </div>
            <div style="overflow-y:auto;max-height:300px">
                <?php $__empty_1 = true; $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--sa-border)">
                    <div class="sa-avatar" style="width:30px;height:30px;font-size:11px;flex-shrink:0"><?php echo e(strtoupper(substr($admin->name,0,1))); ?></div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:13px;font-weight:500;color:var(--sa-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?php echo e($admin->name); ?></div>
                        <div style="font-size:11px;color:var(--sa-muted)"><?php echo e(str_replace('_',' ',ucfirst($admin->role))); ?></div>
                    </div>
                    <?php if($admin->is_superadmin || $admin->role === 'superadmin'): ?>
                        <span class="sa-badge sa-badge-purple" style="font-size:10px">SA</span>
                    <?php endif; ?>
                    <a href="<?php echo e(route('superadmin.users.edit', $admin->uuid)); ?>" style="color:var(--sa-muted);font-size:12px;text-decoration:none" title="Edit">
                        <i class="fas fa-pen"></i>
                    </a>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="color:var(--sa-muted);font-size:13px">No admin accounts.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="col-md-4">
        <div class="sa-card h-100">
            <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:14px">
                <i class="fas fa-eye-slash me-2" style="color:var(--sa-danger)"></i>Recent SA Activity
                <span class="sa-badge sa-badge-red" style="font-size:10px;margin-left:6px">Private</span>
            </div>
            <div style="overflow-y:auto;max-height:280px">
                <?php $__empty_1 = true; $__currentLoopData = $recentActivity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div style="padding:8px 0;border-bottom:1px solid var(--sa-border)">
                    <div style="font-size:12px;color:var(--sa-text)"><?php echo e(Str::limit($log->description, 65)); ?></div>
                    <div style="font-size:11px;color:var(--sa-muted);margin-top:2px">
                        <?php echo e($log->created_at->diffForHumans()); ?>

                        <?php if($log->ip_address): ?> · <?php echo e($log->ip_address); ?> <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p style="color:var(--sa-muted);font-size:13px">No activity yet.</p>
                <?php endif; ?>
            </div>
            <a href="<?php echo e(route('superadmin.superadmin-logs')); ?>" class="sa-btn sa-btn-ghost sa-btn-sm mt-3" style="width:100%;justify-content:center">
                View All SA Logs
            </a>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const chartDefaults = {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
        x: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8892a4', font: { size: 11 } } },
        y: { grid: { color: 'rgba(255,255,255,.05)' }, ticks: { color: '#8892a4', font: { size: 11 } }, beginAtZero: true }
    }
};

function getChartColors() {
    const isLight = document.documentElement.getAttribute('data-theme') === 'light';
    return {
        grid: isLight ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.05)',
        tick: isLight ? '#64748b' : '#8892a4',
    };
}

function makeOpts() {
    const c = getChartColors();
    return {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 11 } } },
            y: { grid: { color: c.grid }, ticks: { color: c.tick, font: { size: 11 } }, beginAtZero: true }
        }
    };
}

const regData = <?php echo json_encode($registrationTrend, 15, 512) ?>;
const regChart = new Chart(document.getElementById('regChart'), {
    type: 'bar',
    data: {
        labels: regData.map(d => d.month),
        datasets: [{
            data: regData.map(d => d.count),
            backgroundColor: 'rgba(59,130,246,.5)',
            borderColor: '#3b82f6',
            borderWidth: 1,
            borderRadius: 5,
        }]
    },
    options: makeOpts()
});

const concernData = <?php echo json_encode($concernTrend, 15, 512) ?>;
const concernChart = new Chart(document.getElementById('concernChart'), {
    type: 'line',
    data: {
        labels: concernData.map(d => d.month),
        datasets: [{
            data: concernData.map(d => d.count),
            borderColor: '#a855f7',
            backgroundColor: 'rgba(168,85,247,.1)',
            borderWidth: 2,
            fill: true,
            tension: .4,
            pointBackgroundColor: '#a855f7',
            pointRadius: 4,
        }]
    },
    options: makeOpts()
});

window.saCharts = [regChart, concernChart];
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/superadmin/dashboard.blade.php ENDPATH**/ ?>