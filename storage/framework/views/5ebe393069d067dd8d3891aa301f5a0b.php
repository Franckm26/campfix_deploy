

<?php $__env->startSection('page_title', 'All Users'); ?>

<?php $__env->startSection('content'); ?>


<div class="sa-card mb-4">
    <form method="GET" action="<?php echo e(route('superadmin.users')); ?>" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:180px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="<?php echo e($search); ?>" class="sa-input" placeholder="Name, email, department…">
        </div>
        <div style="min-width:150px">
            <label class="sa-label">Role</label>
            <select name="role" class="sa-input">
                <option value="">All Roles</option>
                <?php $__currentLoopData = ['student','faculty','maintenance','mis','school_admin','building_admin','academic_head','program_head','principal_assistant','superadmin']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($r); ?>" <?php echo e($role === $r ? 'selected' : ''); ?>><?php echo e(str_replace('_',' ',ucfirst($r))); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div style="min-width:140px">
            <label class="sa-label">Status</label>
            <select name="status" class="sa-input">
                <option value="active"   <?php echo e($status === 'active'   ? 'selected' : ''); ?>>Active</option>
                <option value="archived" <?php echo e($status === 'archived' ? 'selected' : ''); ?>>Archived</option>
                <option value="deleted"  <?php echo e($status === 'deleted'  ? 'selected' : ''); ?>>Deleted</option>
                <option value="locked"   <?php echo e($status === 'locked'   ? 'selected' : ''); ?>>Locked</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="<?php echo e(route('superadmin.users')); ?>" class="sa-btn sa-btn-ghost">Reset</a>
        </div>
        <a href="<?php echo e(route('superadmin.users.create')); ?>" class="sa-btn sa-btn-primary" style="margin-left:auto">
            <i class="fas fa-plus"></i> New User
        </a>
    </form>
</div>


<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        Showing <?php echo e($users->firstItem()); ?>–<?php echo e($users->lastItem()); ?> of <?php echo e($users->total()); ?> users
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <div class="sa-avatar" style="width:28px;height:28px;font-size:11px;flex-shrink:0"><?php echo e(strtoupper(substr($user->name,0,1))); ?></div>
                            <div>
                                <div style="font-weight:500"><?php echo e($user->name); ?></div>
                                <?php if($user->is_superadmin): ?>
                                    <span class="sa-badge sa-badge-purple" style="font-size:10px">Superadmin</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td style="color:var(--sa-muted)"><?php echo e($user->email); ?></td>
                    <td>
                        <span class="sa-badge sa-badge-gray"><?php echo e(str_replace('_',' ',ucfirst($user->role ?? 'N/A'))); ?></span>
                    </td>
                    <td style="color:var(--sa-muted)"><?php echo e($user->department ?? '—'); ?></td>
                    <td>
                        <?php if($user->is_deleted): ?>
                            <span class="sa-badge sa-badge-red">Deleted</span>
                        <?php elseif($user->is_archived): ?>
                            <span class="sa-badge sa-badge-yellow">Archived</span>
                        <?php elseif($user->locked_until && $user->locked_until > now()): ?>
                            <span class="sa-badge sa-badge-red">Locked</span>
                        <?php else: ?>
                            <span class="sa-badge sa-badge-green">Active</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px"><?php echo e($user->created_at->format('M d, Y')); ?></td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                            <a href="<?php echo e(route('superadmin.users.edit', $user->uuid)); ?>" class="sa-btn sa-btn-ghost sa-btn-sm" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <?php if($user->is_deleted): ?>
                                <form method="POST" action="<?php echo e(route('superadmin.users.restore', $user->uuid)); ?>" style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="sa-btn sa-btn-ghost sa-btn-sm" title="Restore" style="color:#4ade80">
                                        <i class="fas fa-rotate-left"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if($user->locked_until && $user->locked_until > now()): ?>
                                <form method="POST" action="<?php echo e(route('superadmin.users.unlock', $user->uuid)); ?>" style="display:inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="sa-btn sa-btn-ghost sa-btn-sm" title="Unlock" style="color:#fbbf24">
                                        <i class="fas fa-lock-open"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                            <?php if(!$user->is_superadmin && $user->id !== auth()->id()): ?>
                                <form method="POST" action="<?php echo e(route('superadmin.users.delete', $user->uuid)); ?>" style="display:inline"
                                      onsubmit="return confirm('Permanently delete <?php echo e(addslashes($user->name)); ?>? This cannot be undone.')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--sa-muted);padding:32px">No users found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    
    <?php if($users->hasPages()): ?>
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        <?php echo e($users->links('vendor.pagination.superadmin')); ?>

    </div>
    <?php endif; ?>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/superadmin/users.blade.php ENDPATH**/ ?>