

<?php $__env->startSection('page_title', 'Edit User'); ?>

<?php $__env->startSection('content'); ?>
<div style="max-width:600px">
    <a href="<?php echo e(route('superadmin.users')); ?>" class="sa-btn sa-btn-ghost sa-btn-sm mb-4">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>

    <div class="sa-card">
        <h2 style="font-size:16px;font-weight:600;color:var(--sa-text);margin:0 0 4px">Edit User</h2>
        <p style="font-size:12px;color:var(--sa-muted);margin:0 0 20px">UUID: <?php echo e($user->uuid); ?></p>

        <?php if($errors->any()): ?>
            <div class="sa-alert sa-alert-error">
                <ul style="margin:0;padding-left:16px">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('superadmin.users.update', $user->uuid)); ?>">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="sa-label">Full Name *</label>
                    <input type="text" name="name" value="<?php echo e(old('name', $user->name)); ?>" class="sa-input" required>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Email *</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $user->email)); ?>" class="sa-input" required>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">New Password <span style="color:var(--sa-muted)">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="sa-input" minlength="8">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="sa-input">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Role *</label>
                    <select name="role" class="sa-input" required>
                        <?php $__currentLoopData = ['student','faculty','maintenance','mis','school_admin','building_admin','academic_head','program_head','principal_assistant','superadmin']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($r); ?>" <?php echo e(old('role', $user->role) === $r ? 'selected' : ''); ?>><?php echo e(str_replace('_',' ',ucfirst($r))); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Department</label>
                    <input type="text" name="department" value="<?php echo e(old('department', $user->department)); ?>" class="sa-input">
                </div>
                <div class="col-md-6">
                    <label class="sa-label">Phone</label>
                    <input type="text" name="phone" value="<?php echo e(old('phone', $user->phone)); ?>" class="sa-input">
                </div>

                
                <div class="col-12">
                    <div style="background:rgba(255,255,255,.03);border:1px solid var(--sa-border);border-radius:8px;padding:14px">
                        <div style="font-size:12px;color:var(--sa-muted);margin-bottom:8px;font-weight:600;text-transform:uppercase;letter-spacing:.8px">Account Status</div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <?php if($user->is_deleted): ?>
                                <span class="sa-badge sa-badge-red">Deleted</span>
                            <?php elseif($user->is_archived): ?>
                                <span class="sa-badge sa-badge-yellow">Archived</span>
                            <?php else: ?>
                                <span class="sa-badge sa-badge-green">Active</span>
                            <?php endif; ?>
                            <?php if($user->locked_until && $user->locked_until > now()): ?>
                                <span class="sa-badge sa-badge-red"><i class="fas fa-lock me-1"></i>Locked until <?php echo e($user->locked_until->format('M d g:i A')); ?></span>
                            <?php endif; ?>
                            <?php if($user->force_password_change): ?>
                                <span class="sa-badge sa-badge-yellow">Force PW Change</span>
                            <?php endif; ?>
                            <?php if($user->is_superadmin): ?>
                                <span class="sa-badge sa-badge-purple"><i class="fas fa-shield-halved me-1"></i>Superadmin</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:11px;color:var(--sa-muted);margin-top:8px">
                            Joined: <?php echo e($user->created_at->format('M d, Y g:i A')); ?>

                            · Last updated: <?php echo e($user->updated_at->format('M d, Y g:i A')); ?>

                        </div>
                    </div>
                </div>

                
                <div class="col-12">
                    <div style="border:1px solid var(--sa-border);border-radius:8px;padding:16px;background:rgba(255,255,255,.02)">
                        <div style="font-size:13px;font-weight:600;color:var(--sa-text);margin-bottom:12px">
                            <i class="fas fa-shield-halved me-1" style="color:var(--sa-accent2)"></i> Module Access
                            <span style="font-size:11px;color:var(--sa-muted);font-weight:400;margin-left:6px">Select which modules this user can access</span>
                        </div>
                        <input type="hidden" name="use_custom_permissions" value="1">
                        <?php
                            $saEditPerms = is_array($user->permissions) && count($user->permissions)
                                            ? $user->permissions
                                            : \App\Models\User::defaultPermissions($user->role);
                            $saEditMods  = \App\Models\User::allModules();
                            $saEditSubs  = \App\Models\User::subPermissions();
                        ?>
                        <div class="row g-2">
                            <?php $__currentLoopData = $saEditMods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $mod): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="permissions[]" value="<?php echo e($key); ?>"
                                           id="saedit_perm_<?php echo e($key); ?>"
                                           <?php echo e(in_array($key, $saEditPerms) ? 'checked' : ''); ?>

                                           <?php if(isset($saEditSubs[$key])): ?> onchange="saToggleEditSub('<?php echo e($key); ?>',this.checked)" <?php endif; ?>>
                                    <label class="form-check-label" for="saedit_perm_<?php echo e($key); ?>" style="font-size:13px;color:var(--sa-text)">
                                        <?php echo e($mod['label']); ?>

                                    </label>
                                </div>
                                <?php if(isset($saEditSubs[$key])): ?>
                                <div id="saedit_sub_<?php echo e($key); ?>" class="ms-4 mt-1<?php echo e(in_array($key,$saEditPerms) ? '' : ' d-none'); ?>">
                                    <?php $__currentLoopData = $saEditSubs[$key]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subKey => $subLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="permissions[]" value="<?php echo e($subKey); ?>"
                                               id="saedit_perm_<?php echo e($subKey); ?>"
                                               <?php echo e(in_array($subKey, $saEditPerms) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="saedit_perm_<?php echo e($subKey); ?>" style="font-size:12px;color:var(--sa-muted)">
                                            <?php echo e($subLabel); ?>

                                        </label>
                                    </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:12px">
                            <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick="saEditSelectAll(true)">
                                <i class="fas fa-check-double"></i> Select All
                            </button>
                            <button type="button" class="sa-btn sa-btn-ghost sa-btn-sm" onclick="saEditSelectAll(false)">
                                <i class="fas fa-xmark"></i> Clear All
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12" style="display:flex;gap:10px;margin-top:8px">
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="<?php echo e(route('superadmin.users')); ?>" class="sa-btn sa-btn-ghost">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
function saToggleEditSub(parent, checked) {
    const sub = document.getElementById('saedit_sub_' + parent);
    if (!sub) return;
    sub.classList.toggle('d-none', !checked);
    if (!checked) sub.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = false);
}

function saEditSelectAll(checked) {
    document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = checked);
    const sub = document.getElementById('saedit_sub_users');
    if (sub) sub.classList.toggle('d-none', !checked);
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('superadmin.layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/superadmin/users-edit.blade.php ENDPATH**/ ?>