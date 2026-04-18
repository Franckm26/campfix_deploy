

<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<style>
    .diff-old { color: #dc3545; text-decoration: line-through; }
    .diff-new { color: #198754; font-weight: 600; }
    .diff-field { font-weight: 600; color: #495057; }
    [data-theme="dark"] .diff-field { color: #aaa; }
    .log-folder-card { border-radius: 10px; padding: 18px 20px; cursor: pointer; transition: box-shadow .2s; border: 1px solid #dee2e6; }
    .log-folder-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }
    [data-theme="dark"] .log-folder-card { border-color: #2a2a45; background: #1a1a2e; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title'); ?>
<h2>Audit Logs</h2>
<p>Full change history with field-level tracking</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3">

    <div class="row mb-3 align-items-center">
        <div class="col">
        </div>
        <?php if(!$isArchived): ?>
        <div class="col-auto">
            <button class="btn btn-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#archiveAllModal">
                <i class="fas fa-archive me-1"></i> Archive All Logs
            </button>
        </div>
        <?php endif; ?>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show py-2"><?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link <?php echo e(!$isArchived ? 'active' : ''); ?>" href="<?php echo e(route('admin.logs')); ?>">
                <i class="fas fa-list me-1"></i> Active Logs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo e($isArchived ? 'active' : ''); ?>" href="<?php echo e(route('admin.logs', ['view' => 'archived'])); ?>">
                <i class="fas fa-archive me-1"></i> Archived Folders
            </a>
        </li>
    </ul>

    
    <?php if($isArchived): ?>
        <?php if($folders->isEmpty()): ?>
            <div class="card">
                <div class="card-body text-center py-5 text-muted">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>No archived log folders yet.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php $__currentLoopData = $folders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $folder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="col-md-3 col-sm-6">
                    <div class="log-folder-card bg-white">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <i class="fas fa-folder fa-2x text-warning"></i>
                            <div>
                                <div class="fw-bold"><?php echo e($folder->name); ?></div>
                                <small class="text-muted"><?php echo e($folder->log_count); ?> logs</small>
                            </div>
                        </div>
                        <div class="text-muted" style="font-size:12px"><?php echo e($folder->description); ?></div>
                        <div class="text-muted" style="font-size:11px"><?php echo e($folder->created_at->format('M d, Y')); ?></div>
                        <div class="d-flex gap-2 mt-3">
                            <a href="<?php echo e(route('admin.logs.folder', $folder->id)); ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-folder-open"></i> View
                            </a>
                            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#restoreFolderModal<?php echo e($folder->id); ?>" title="Restore">
                                <i class="fas fa-undo"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteFolderModal<?php echo e($folder->id); ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                
                <div class="modal fade" id="deleteFolderModal<?php echo e($folder->id); ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-1"></i> Delete Folder</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Delete folder <strong><?php echo e($folder->name); ?></strong>?</p>
                                <div class="alert alert-warning py-2">
                                    <i class="fas fa-info-circle me-1"></i> All <strong><?php echo e($folder->log_count); ?></strong> logs inside will be permanently deleted.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form method="POST" action="<?php echo e(route('admin.logs.folder.delete', $folder->id)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="modal fade" id="restoreFolderModal<?php echo e($folder->id); ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title"><i class="fas fa-undo me-1"></i> Restore Folder</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <p>Restore folder <strong><?php echo e($folder->name); ?></strong> to active logs?</p>
                                <div class="alert alert-info py-2">
                                    <i class="fas fa-info-circle me-1"></i> All <strong><?php echo e($folder->log_count); ?></strong> logs inside will be moved back to active logs.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <form method="POST" action="<?php echo e(route('admin.logs.folder.restore', $folder->id)); ?>" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-undo me-1"></i> Restore</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

    
    <?php else: ?>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" action="<?php echo e(route('admin.logs')); ?>" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="action" class="form-select form-select-sm">
                        <option value="">All Actions</option>
                        <optgroup label="Users">
                            <option value="user_created"           <?php echo e(request('action') == 'user_created'           ? 'selected' : ''); ?>>User Created</option>
                            <option value="user_updated"           <?php echo e(request('action') == 'user_updated'           ? 'selected' : ''); ?>>User Updated</option>
                            <option value="user_deleted"           <?php echo e(request('action') == 'user_deleted'           ? 'selected' : ''); ?>>User Deleted</option>
                            <option value="user_restored"          <?php echo e(request('action') == 'user_restored'          ? 'selected' : ''); ?>>User Restored</option>
                            <option value="user_archived"          <?php echo e(request('action') == 'user_archived'          ? 'selected' : ''); ?>>User Archived</option>
                            <option value="users_imported"         <?php echo e(request('action') == 'users_imported'         ? 'selected' : ''); ?>>Users Imported</option>
                        </optgroup>
                        <optgroup label="Concerns">
                            <option value="concern_created"  <?php echo e(request('action') == 'concern_created'  ? 'selected' : ''); ?>>Concern Created</option>
                            <option value="concern_updated"  <?php echo e(request('action') == 'concern_updated'  ? 'selected' : ''); ?>>Concern Updated</option>
                            <option value="concern_deleted"  <?php echo e(request('action') == 'concern_deleted'  ? 'selected' : ''); ?>>Concern Deleted</option>
                            <option value="concern_assigned" <?php echo e(request('action') == 'concern_assigned' ? 'selected' : ''); ?>>Concern Assigned</option>
                            <option value="concern_resolved" <?php echo e(request('action') == 'concern_resolved' ? 'selected' : ''); ?>>Concern Resolved</option>
                            <option value="status_updated"   <?php echo e(request('action') == 'status_updated'   ? 'selected' : ''); ?>>Status Updated</option>
                        </optgroup>
                        <optgroup label="Reports">
                            <option value="report_status_updated"   <?php echo e(request('action') == 'report_status_updated'   ? 'selected' : ''); ?>>Report Status Updated</option>
                            <option value="report_assigned"         <?php echo e(request('action') == 'report_assigned'         ? 'selected' : ''); ?>>Report Assigned</option>
                        </optgroup>
                        <optgroup label="System">
                            <option value="export_created"         <?php echo e(request('action') == 'export_created'         ? 'selected' : ''); ?>>Export Created</option>
                            <option value="archive_folder_deleted" <?php echo e(request('action') == 'archive_folder_deleted' ? 'selected' : ''); ?>>Archive Folder Deleted</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search user / description" value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="col-auto">
                    <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="20"  <?php echo e((!request('per_page') || request('per_page') == '20') ? 'selected' : ''); ?>>20 / page</option>
                        <option value="50"  <?php echo e(request('per_page') == '50' ? 'selected' : ''); ?>>50 / page</option>
                        <option value="100" <?php echo e(request('per_page') == '100' ? 'selected' : ''); ?>>100 / page</option>
                    </select>
                </div>
                <div class="col-auto d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?php echo e(route('admin.logs')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:13px">
                    <thead class="table-dark">
                        <tr>
                            <th style="width:14%">Action</th>
                            <th>Description</th>
                            <th style="width:16%">Performed By</th>
                            <th style="width:12%">IP Address</th>
                            <th style="width:14%">Date / Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $changes = $log->changes ?? [];
                                $badgeColor = str_contains($log->action, 'created') || str_contains($log->action, 'imported') ? 'success' :
                                             (str_contains($log->action, 'deleted') || str_contains($log->action, 'permanent') ? 'danger' :
                                             (str_contains($log->action, 'updated') || str_contains($log->action, 'restored') ? 'warning' :
                                             (str_contains($log->action, 'archived') ? 'secondary' : 'info')));
                            ?>
                            <tr>
                                <td>
                                    <span class="badge bg-<?php echo e($badgeColor); ?>" style="font-size:11px">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $log->action))); ?>

                                    </span>
                                </td>
                                <td>
                                    <div><?php echo e($log->description); ?></div>
                                    <?php if(!empty($changes)): ?>
                                        <div class="mt-1" style="font-size:11px">
                                            <?php $__currentLoopData = $changes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $diff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($field === 'is_admin'): ?> <?php continue; ?> <?php endif; ?>
                                                <span class="diff-field"><?php echo e(ucfirst(str_replace('_', ' ', $field))); ?>:</span>
                                                <span class="diff-old"><?php echo e($diff['old'] ?? '—'); ?></span>
                                                <i class="fas fa-arrow-right mx-1 text-muted" style="font-size:9px"></i>
                                                <span class="diff-new"><?php echo e($diff['new'] ?? '—'); ?></span>
                                                <?php if(!$loop->last): ?> &nbsp;&bull;&nbsp; <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php elseif($log->new_values && !$log->old_values): ?>
                                        <div class="mt-1 text-muted" style="font-size:11px">
                                            <?php $__currentLoopData = $log->new_values; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="diff-field"><?php echo e(ucfirst(str_replace('_', ' ', $field))); ?>:</span>
                                                <span><?php echo e($value ?? '—'); ?></span>
                                                <?php if(!$loop->last): ?> &nbsp;&bull;&nbsp; <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?php echo e($log->user->name ?? 'System'); ?></div>
                                    <?php if($log->user): ?>
                                        <small class="text-muted"><?php echo e(ucfirst(str_replace('_', ' ', $log->user->role ?? ''))); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted font-monospace" style="font-size:11px"><?php echo e($log->ip_address ?? '—'); ?></td>
                                <td class="text-muted">
                                    <?php echo e($log->created_at->format('M d, Y')); ?><br>
                                    <small><?php echo e($log->created_at->format('h:i:s A')); ?></small>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No activity logs found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between align-items-center px-3 py-2">
                <small class="text-muted">Showing <?php echo e($logs->firstItem() ?? 0); ?> – <?php echo e($logs->lastItem() ?? 0); ?> of <?php echo e($logs->total()); ?> entries</small>
                <?php echo e($logs->appends(request()->except('page'))->links('pagination::bootstrap-4')); ?>

            </div>
        </div>
    </div>

    <?php endif; ?>
</div>


<div class="modal fade" id="archiveAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-archive me-1"></i> Archive All Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo e(route('admin.logs.archive.bulk')); ?>">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <p>All active logs will be archived into a folder. Give it a name:</p>
                    <input type="text" name="folder_name" class="form-control"
                        placeholder="e.g. 2025-2026, Q1-2026"
                        value="<?php echo e(now()->format('Y-m-d')); ?>" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-secondary"><i class="fas fa-archive me-1"></i> Archive All</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/logs.blade.php ENDPATH**/ ?>