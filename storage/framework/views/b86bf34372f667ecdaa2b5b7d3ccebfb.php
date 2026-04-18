<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-check-circle"></i> Pending Event Requests</h2>
            <p class="text-muted">Review and approve event/agenda requests</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo e(route('events.calendar')); ?>" class="btn btn-info">
                <i class="fas fa-calendar"></i> Approved Events
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('events.pending')); ?>" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..." 
                        value="<?php echo e(request('search')); ?>">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="Pending" <?php echo e(request('status') == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                        <option value="Approved" <?php echo e(request('status') == 'Approved' ? 'selected' : ''); ?>>Approved</option>
                        <option value="Rejected" <?php echo e(request('status') == 'Rejected' ? 'selected' : ''); ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        <option value="event" <?php echo e(request('category') == 'event' ? 'selected' : ''); ?>>Event</option>
                        <option value="meeting" <?php echo e(request('category') == 'meeting' ? 'selected' : ''); ?>>Meeting</option>
                        <option value="activity" <?php echo e(request('category') == 'activity' ? 'selected' : ''); ?>>Activity</option>
                        <option value="training" <?php echo e(request('category') == 'training' ? 'selected' : ''); ?>>Training</option>
                        <option value="other" <?php echo e(request('category') == 'other' ? 'selected' : ''); ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date" 
                        value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date" 
                        value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                </div>
                <div class="col-auto">
                    <a href="<?php echo e(route('events.pending')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <?php if($requests->count() > 0): ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Requestor</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($request->title); ?></td>
                                    <td><?php echo e($request->user->name); ?></td>
                                    <td><?php echo e(ucfirst($request->category)); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($request->event_date)->format('M d, Y')); ?></td>
                                    <td><?php echo e($request->start_time); ?> - <?php echo e($request->end_time); ?></td>
                                    <td><?php echo e($request->location); ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark"><?php echo e($request->status); ?></span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo e($request->id); ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- View Modal -->
                                <div class="modal fade" id="viewModal<?php echo e($request->id); ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning text-dark">
                                                <h5 class="modal-title"><?php echo e($request->title); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <!-- Progress Tracker -->
                                                <?php if (isset($component)) { $__componentOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.request-progress-tracker','data' => ['request' => $request,'title' => 'Approval Progress']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('request-progress-tracker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['request' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($request),'title' => 'Approval Progress']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf6ed9c701b8ff063a88b8473bcdabf00)): ?>
<?php $attributes = $__attributesOriginalf6ed9c701b8ff063a88b8473bcdabf00; ?>
<?php unset($__attributesOriginalf6ed9c701b8ff063a88b8473bcdabf00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf6ed9c701b8ff063a88b8473bcdabf00)): ?>
<?php $component = $__componentOriginalf6ed9c701b8ff063a88b8473bcdabf00; ?>
<?php unset($__componentOriginalf6ed9c701b8ff063a88b8473bcdabf00); ?>
<?php endif; ?>
                                                
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <p><strong>Requestor:</strong> <?php echo e($request->user->name); ?></p>
                                                        <p><strong>Email:</strong> <?php echo e($request->user->email); ?></p>
                                                        <p><strong>Category:</strong> <?php echo e(ucfirst($request->category)); ?></p>
                                                        <?php if($request->department): ?>
                                                            <p><strong>Department:</strong> <?php echo e($request->department); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Date:</strong> <?php echo e(\Carbon\Carbon::parse($request->event_date)->format('M d, Y')); ?></p>
                                                        <p><strong>Time:</strong> <?php echo e($request->start_time); ?> - <?php echo e($request->end_time); ?></p>
                                                        <p><strong>Location:</strong> <?php echo e($request->location); ?></p>
                                                        <p><strong>Priority:</strong> 
                                                            <span class="badge bg-<?php echo e($request->priority == 'urgent' ? 'danger' : ($request->priority == 'high' ? 'warning' : 'info')); ?>">
                                                                <?php echo e(ucfirst($request->priority)); ?>

                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <p><strong>Description:</strong></p>
                                                    <p class="text-muted"><?php echo e($request->description); ?></p>
                                                </div>
                                                <?php if($request->notes): ?>
                                                    <div class="mt-2">
                                                        <p><strong>Notes:</strong></p>
                                                        <p class="text-muted"><?php echo e($request->notes); ?></p>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <form action="<?php echo e(route('events.approve', $request->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="input-group">
                                                        <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                                                        <button type="submit" class="btn btn-success">Approve</button>
                                                    </div>
                                                </form>
                                                <form action="<?php echo e(route('events.reject', $request->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <div class="input-group">
                                                        <input type="text" name="notes" class="form-control" placeholder="Reason for rejection">
                                                        <button type="submit" class="btn btn-danger">Reject</button>
                                                    </div>
                                                </form>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <h4 class="text-muted">No pending requests</h4>
                <p>All event requests have been processed.</p>
                <a href="<?php echo e(route('events.calendar')); ?>" class="btn btn-primary">View Approved Events</a>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/events/pending.blade.php ENDPATH**/ ?>