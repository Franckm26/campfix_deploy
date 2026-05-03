<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title'); ?>
<h2><i class="fas fa-calendar-alt"></i> Event Requests</h2>
<p>Manage all event requests</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3">


    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Event Action Confirmation Modal -->
    <div class="modal fade" id="eventActionModal" tabindex="-1" aria-labelledby="eventActionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" id="eventActionModalHeader">
                    <h5 class="modal-title" id="eventActionModalLabel"><i class="fas fa-exclamation-circle"></i> Confirm Action</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="eventActionMessage"></p>
                    <div id="eventActionAlert" class="alert d-none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="eventActionConfirmBtn"><i class="fas fa-check"></i> Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Event Modal -->
    <div class="modal fade" id="archiveEventModal" tabindex="-1" aria-labelledby="archiveEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="archiveEventModalLabel">Archive Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="archiveEventForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" id="archiveEventId" name="event_id" value="">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> This event will be archived and hidden from your active list.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-archive"></i> Archive</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Event Modal -->
    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEventModalLabel">Delete Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="deleteEventForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" id="deleteEventId" name="event_id" value="">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> This event will be moved to deleted. You can restore it later from the Deleted tab.
                        </div>
                        <p class="mb-0">Are you sure you want to delete this event?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <ul class="nav nav-pills mb-0 flex-wrap">
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($viewType ?? 'active') == 'active' ? 'active' : ''); ?>" href="<?php echo e(route('admin.events', ['view' => 'active'])); ?>">
                            <i class="fas fa-calendar-check"></i> Active
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($viewType ?? '') == 'approved' ? 'active' : ''); ?>" href="<?php echo e(route('admin.events', ['view' => 'approved'])); ?>" style="<?php echo e(($viewType ?? '') == 'approved' ? '' : 'color: #28a745;'); ?>">
                            <i class="fas fa-check-circle"></i> Approved
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($viewType ?? '') == 'finished' ? 'active' : ''); ?>" href="<?php echo e(route('admin.events', ['view' => 'finished'])); ?>" style="<?php echo e(($viewType ?? '') == 'finished' ? '' : 'color: #6f42c1;'); ?>">
                            <i class="fas fa-flag-checkered"></i> Finished
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($viewType ?? '') == 'rejected' ? 'active' : ''); ?>" href="<?php echo e(route('admin.events', ['view' => 'rejected'])); ?>" style="<?php echo e(($viewType ?? '') == 'rejected' ? '' : 'color: #dc3545;'); ?>">
                            <i class="fas fa-times-circle"></i> Rejected
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($viewType ?? '') == 'archives' ? 'active' : ''); ?>" href="<?php echo e(route('admin.events', ['view' => 'archives'])); ?>">
                            <i class="fas fa-archive"></i> Archived
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(($viewType ?? '') == 'deleted' ? 'active' : ''); ?>" href="<?php echo e(route('admin.events', ['view' => 'deleted'])); ?>" style="color: #dc3545;">
                            <i class="fas fa-trash-alt"></i> Deleted
                        </a>
                    </li>
                </ul>
                <div>
                    <a href="<?php echo e(route('events.calendar')); ?>" class="btn btn-info btn-sm">
                        <i class="fas fa-calendar"></i> Calendar View
                    </a>
                </div>
            </div>
            <form method="GET" action="<?php echo e(route('admin.events')); ?>">
                <input type="hidden" name="view" value="<?php echo e($viewType ?? 'active'); ?>">
                <div class="row g-2">
                    <div class="col-12 col-md">
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Search by title..."
                            value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-6 col-md">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo e(request('status') == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                            <option value="Approved" <?php echo e(request('status') == 'Approved' ? 'selected' : ''); ?>>Approved</option>
                            <option value="Rejected" <?php echo e(request('status') == 'Rejected' ? 'selected' : ''); ?>>Rejected</option>
                            <option value="Cancelled" <?php echo e(request('status') == 'Cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-6 col-md">
                        <select name="category" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <option value="Area Use" <?php echo e(request('category') == 'Area Use' ? 'selected' : ''); ?>>Area Use</option>
                        </select>
                    </div>
                    <div class="col-6 col-md">
                        <input type="date" name="date_from" class="form-control form-control-sm"
                            value="<?php echo e(request('date_from')); ?>">
                    </div>
                    <div class="col-6 col-md">
                        <input type="date" name="date_to" class="form-control form-control-sm"
                            value="<?php echo e(request('date_to')); ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        <a href="<?php echo e(route('admin.events')); ?>" class="btn btn-secondary btn-sm ms-1"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if(($viewType ?? 'active') == 'active'): ?>
    <!-- Summary Cards -->
    <?php if(auth()->user()->role !== 'building_admin'): ?>
    <div class="row mb-4" style="display: flex !important;">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Requests</h5>
                    <h3><?php echo e($requests->count()); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h3><?php echo e($requests->where('status', 'Pending')->count()); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Approved</h5>
                    <h3><?php echo e($requests->where('status', 'Approved')->count()); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Rejected</h5>
                    <h3><?php echo e($requests->where('status', 'Rejected')->count()); ?></h3>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($requests->count() > 0): ?>
        <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
            <table class="table table-hover" style="display: table !important;">
                <thead>
                    <tr>
                        <th>Event Ticket</th>
                        <th>Requestor</th>                        <th>Event Date</th>
                        <th>Time</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>EVT-<?php echo e(str_pad($request->id, 5, '0', STR_PAD_LEFT)); ?></td>
                            <td><?php echo e($request->user->name ?? 'N/A'); ?></td>                            <td><?php echo e(\Carbon\Carbon::parse($request->event_date)->format('M d, Y')); ?></td>
                            <td><?php echo e(\Carbon\Carbon::parse($request->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($request->end_time)->format('g:i A')); ?></td>
                            <td><?php echo e($request->location); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($request->status == 'Approved' ? 'success' : 
                                    ($request->status == 'Pending' ? 'warning' : 
                                    ($request->status == 'Rejected' ? 'danger' : 'secondary'))); ?>">
                                    <?php echo e($request->status); ?>

                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0" data-bs-toggle="modal"
                                        data-bs-target="#viewModal<?php echo e($request->id); ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if($request->status == 'Pending' && auth()->user()->role !== 'mis'): ?>
                                        <button type="button" class="btn btn-sm btn-success bg-transparent border-0" title="Approve" onclick="approveEvent(<?php echo e($request->id); ?>)">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" title="Reject" onclick="rejectEvent(<?php echo e($request->id); ?>)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    <?php
                                        $userRole = auth()->user()->role;
                                        $archiveColumn = $userRole . '_archived';
                                        $isArchivedByRole = $request->$archiveColumn ?? false;
                                    ?>
                                    <?php if(!$isArchivedByRole): ?>
                                        <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" title="Archive" onclick="showArchiveEventModal(<?php echo e($request->id); ?>)">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" title="Delete" onclick="showDeleteEventModal(<?php echo e($request->id); ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- View Modal -->
                        <div class="modal fade" id="viewModal<?php echo e($request->id); ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Event Request Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Ticket Number:</strong> EVT-<?php echo e(str_pad($request->id, 5, '0', STR_PAD_LEFT)); ?></p>                                                <p><strong>Status:</strong> 
                                                    <span class="badge bg-<?php echo e($request->status == 'Approved' ? 'success' : 
                                                        ($request->status == 'Pending' ? 'warning' : 
                                                        ($request->status == 'Rejected' ? 'danger' : 'secondary'))); ?>">
                                                        <?php echo e($request->status); ?>

                                                    </span>
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Requestor:</strong> <?php echo e($request->user->name ?? 'N/A'); ?></p>
                                                <p><strong>Event Date:</strong> <?php echo e(\Carbon\Carbon::parse($request->event_date)->format('M d, Y')); ?></p>
                                                <p><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($request->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($request->end_time)->format('g:i A')); ?></p>
                                                <p><strong>Location:</strong> <?php echo e($request->location); ?></p>
                                            </div>
                                        </div>
                                        <hr>
                                        <p><strong>Description:</strong></p>
                                        <p><?php echo e($request->description); ?></p>
                                        <?php if($request->notes): ?>
                                            <hr>
                                            <p><strong>Notes:</strong></p>
                                            <p><?php echo e($request->notes); ?></p>
                                        <?php endif; ?>
                                        
                                        <!-- Progress Tracker -->
                                        <hr>
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
                                        
                                        <!-- PDF Download Button for Approved Events -->
                                        <?php if($request->status == 'Approved'): ?>
                                        <div class="text-center my-3">
                                            <a href="<?php echo e(route('events.pdf', $request->id)); ?>" class="btn btn-primary btn-lg" target="_blank">
                                                <i class="fas fa-file-pdf me-2"></i> Download PDF
                                            </a>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <?php if($request->approved_by): ?>
                                            <hr>
                                            <p><strong>Processed By:</strong> <?php echo e($request->approver->name ?? 'N/A'); ?></p>
                                            <p><strong>Processed At:</strong> <?php echo e($request->approved_at ? \Carbon\Carbon::parse($request->approved_at)->format('M d, Y h:i A') : 'N/A'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <?php if($request->status == 'Pending' && auth()->user()->role !== 'mis'): ?>
                                            <form method="POST" action="<?php echo e(route('events.approve', $request->id)); ?>" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <div class="input-group">
                                                    <input type="text" name="notes" class="form-control" placeholder="Notes (optional)">
                                                    <button type="submit" class="btn btn-success">Approve</button>
                                                </div>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('events.reject', $request->id)); ?>" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <div class="input-group">
                                                    <input type="text" name="notes" class="form-control" placeholder="Reason for rejection">
                                                    <button type="submit" class="btn btn-danger">Reject</button>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                        <?php if($request->status == 'Approved'): ?>
                                            <a href="<?php echo e(route('events.pdf', $request->id)); ?>" class="btn btn-primary" target="_blank">
                                                <i class="fas fa-file-pdf me-1"></i> Download PDF
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">No event requests found</h4>
                <p>There are no event requests matching your filters.</p>
                <a href="<?php echo e(route('admin.events')); ?>" class="btn btn-primary">View All Requests</a>
            </div>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'approved'): ?>
    <!-- Approved Events Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-check-circle text-success"></i> Approved Events
                <?php if(isset($approvedEvents)): ?>
                    <span class="badge bg-success ms-2"><?php echo e($approvedEvents->count()); ?></span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <?php if(isset($approvedEvents) && $approvedEvents->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Event Ticket</th>
                                <th>Requestor</th>                                <th>Event Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $approvedEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></td>
                                    <td><?php echo e($event->user->name ?? 'N/A'); ?></td>                                    <td><?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></td>
                                    <td><?php echo e($event->location); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                                data-bs-toggle="modal" data-bs-target="#approvedViewModal<?php echo e($event->id); ?>" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" title="Archive" onclick="showArchiveEventModal(<?php echo e($event->id); ?>)">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" title="Delete" onclick="showDeleteEventModal(<?php echo e($event->id); ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- View Modal -->
                                <div class="modal fade" id="approvedViewModal<?php echo e($event->id); ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Event Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Ticket Number:</strong> EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></p>                                                        <p><strong>Status:</strong> <span class="badge bg-success">Approved</span></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Requestor:</strong> <?php echo e($event->user->name ?? 'N/A'); ?></p>
                                                        <p><strong>Event Date:</strong> <?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></p>
                                                        <p><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></p>
                                                        <p><strong>Location:</strong> <?php echo e($event->location); ?></p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p><strong>Description:</strong></p>
                                                <p><?php echo e($event->description); ?></p>
                                                <?php if($event->notes): ?>
                                                    <hr>
                                                    <p><strong>Notes:</strong></p>
                                                    <p><?php echo e($event->notes); ?></p>
                                                <?php endif; ?>
                                                <hr>
                                                <?php if (isset($component)) { $__componentOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.request-progress-tracker','data' => ['request' => $event,'title' => 'Approval Progress']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('request-progress-tracker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['request' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event),'title' => 'Approval Progress']); ?>
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
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No approved upcoming events</h5>
                    <p class="text-muted">Approved events with a future date will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'finished'): ?>
    <!-- Finished Events Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-flag-checkered" style="color:#6f42c1;"></i> Finished Events
                <?php if(isset($finishedEvents)): ?>
                    <span class="badge ms-2" style="background:#6f42c1;"><?php echo e($finishedEvents->count()); ?></span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <?php if(isset($finishedEvents) && $finishedEvents->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Event Ticket</th>
                                <th>Requestor</th>                                <th>Event Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $finishedEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></td>
                                    <td><?php echo e($event->user->name ?? 'N/A'); ?></td>                                    <td><?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></td>
                                    <td><?php echo e($event->location); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                                data-bs-toggle="modal" data-bs-target="#finishedViewModal<?php echo e($event->id); ?>" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" title="Archive" onclick="showArchiveEventModal(<?php echo e($event->id); ?>)">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" title="Delete" onclick="showDeleteEventModal(<?php echo e($event->id); ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- View Modal -->
                                <div class="modal fade" id="finishedViewModal<?php echo e($event->id); ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Event Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Ticket Number:</strong> EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></p>                                                        <p><strong>Status:</strong> <span class="badge" style="background:#6f42c1;">Finished</span></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Requestor:</strong> <?php echo e($event->user->name ?? 'N/A'); ?></p>
                                                        <p><strong>Event Date:</strong> <?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></p>
                                                        <p><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></p>
                                                        <p><strong>Location:</strong> <?php echo e($event->location); ?></p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p><strong>Description:</strong></p>
                                                <p><?php echo e($event->description); ?></p>
                                                <?php if($event->notes): ?>
                                                    <hr>
                                                    <p><strong>Notes:</strong></p>
                                                    <p><?php echo e($event->notes); ?></p>
                                                <?php endif; ?>
                                                <hr>
                                                <?php if (isset($component)) { $__componentOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.request-progress-tracker','data' => ['request' => $event,'title' => 'Approval Progress']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('request-progress-tracker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['request' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event),'title' => 'Approval Progress']); ?>
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
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-flag-checkered fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No finished events yet</h5>
                    <p class="text-muted">Approved events whose date has passed will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'rejected'): ?>
    <!-- Rejected Events Section -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-times-circle text-danger"></i> Rejected Events
                <?php if(isset($rejectedEvents)): ?>
                    <span class="badge bg-danger ms-2"><?php echo e($rejectedEvents->count()); ?></span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <?php if(isset($rejectedEvents) && $rejectedEvents->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Event Ticket</th>
                                <th>Requestor</th>                                <th>Event Date</th>
                                <th>Time</th>
                                <th>Location</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $rejectedEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></td>
                                    <td><?php echo e($event->user->name ?? 'N/A'); ?></td>                                    <td><?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></td>
                                    <td><?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></td>
                                    <td><?php echo e($event->location); ?></td>
                                    <td><?php echo e($event->notes ? \Illuminate\Support\Str::limit($event->notes, 40) : '-'); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                                data-bs-toggle="modal" data-bs-target="#rejectedViewModal<?php echo e($event->id); ?>" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary bg-transparent border-0" title="Archive" onclick="showArchiveEventModal(<?php echo e($event->id); ?>)">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger bg-transparent border-0" title="Delete" onclick="showDeleteEventModal(<?php echo e($event->id); ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- View Modal -->
                                <div class="modal fade" id="rejectedViewModal<?php echo e($event->id); ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Event Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Ticket Number:</strong> EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></p>                                                        <p><strong>Status:</strong> <span class="badge bg-danger">Rejected</span></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Requestor:</strong> <?php echo e($event->user->name ?? 'N/A'); ?></p>
                                                        <p><strong>Event Date:</strong> <?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></p>
                                                        <p><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></p>
                                                        <p><strong>Location:</strong> <?php echo e($event->location); ?></p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p><strong>Description:</strong></p>
                                                <p><?php echo e($event->description); ?></p>
                                                <?php if($event->notes): ?>
                                                    <hr>
                                                    <p><strong>Reason for Rejection:</strong></p>
                                                    <p class="text-danger"><?php echo e($event->notes); ?></p>
                                                <?php endif; ?>
                                                <hr>
                                                <?php if (isset($component)) { $__componentOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf6ed9c701b8ff063a88b8473bcdabf00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.request-progress-tracker','data' => ['request' => $event,'title' => 'Approval Progress']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('request-progress-tracker'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['request' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($event),'title' => 'Approval Progress']); ?>
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
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-times-circle fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No rejected events</h5>
                    <p class="text-muted">Rejected event requests will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'archives'): ?>
    <!-- Archived Events Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-archive"></i> Archived Events</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($archivedEvents) && $archivedEvents->count() > 0): ?>
                        <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                            <table class="table table-hover" style="display: table !important;">
                                <thead>
                                    <tr>
                                        <th>Event Ticket</th>
                                        <th>Requestor</th>                                        <th>Event Date</th>
                                        <th>Time</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $archivedEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></td>
                                            <td><?php echo e($event->user->name ?? 'N/A'); ?></td>                                            <td><?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></td>
                                            <td><?php echo e($event->location); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($event->status == 'Approved' ? 'success' :
                                                    ($event->status == 'Pending' ? 'warning' :
                                                    ($event->status == 'Rejected' ? 'danger' : 'secondary'))); ?>">
                                                    <?php echo e($event->status); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-primary bg-transparent" data-bs-toggle="modal"
                                                        data-bs-target="#viewModal<?php echo e($event->id); ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form method="POST" action="<?php echo e(route('admin.archive.restore')); ?>" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="type" value="event">
                                                        <input type="hidden" name="id" value="<?php echo e($event->id); ?>">
                                                        <button type="submit" class="btn btn-sm btn-success bg-transparent" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewModal<?php echo e($event->id); ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Event Request Details</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p><strong>Ticket Number:</strong> EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></p>                                                                <p><strong>Status:</strong>
                                                                    <span class="badge bg-<?php echo e($event->status == 'Approved' ? 'success' :
                                                                        ($event->status == 'Pending' ? 'warning' :
                                                                        ($event->status == 'Rejected' ? 'danger' : 'secondary'))); ?>">
                                                                        <?php echo e($event->status); ?>

                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <p><strong>Requestor:</strong> <?php echo e($event->user->name ?? 'N/A'); ?></p>
                                                                <p><strong>Event Date:</strong> <?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></p>
                                                                <p><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></p>
                                                                <p><strong>Location:</strong> <?php echo e($event->location); ?></p>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <p><strong>Description:</strong></p>
                                                        <p><?php echo e($event->description); ?></p>
                                                        <?php if($event->notes): ?>
                                                            <hr>
                                                            <p><strong>Notes:</strong></p>
                                                            <p><?php echo e($event->notes); ?></p>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-archive"></i> No archived events found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Deleted Events View -->
    <?php if(($viewType ?? '') == 'deleted'): ?>
    <!-- Context Menu (Right-Click) -->
    <div id="deletedEventsContextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" onclick="deletedEventsContextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" onclick="deletedEventsContextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" onclick="deletedEventsContextPermanentDelete()"><i class="fas fa-times-circle"></i> Permanently Delete</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-trash-alt"></i> Deleted Events</h2>
            <p class="text-muted">Events in this folder can be restored or permanently deleted.</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <!-- Info + Auto-filter Card -->
    <div class="card mb-4 border-warning">
        <div class="card-body bg-warning bg-opacity-10 py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong><i class="fas fa-info-circle"></i> About Deleted Events</strong>
                    <p class="mb-0 text-muted small">Events deleted are moved here. Restore or permanently delete them.</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-warning fs-6"><?php echo e($deletedEvents->count()); ?> events</span>
                    <select id="retentionDays" class="form-select form-select-sm" style="width:120px;">
                        <option value="3" <?php echo e(($days ?? 15) == 3 ? 'selected' : ''); ?>>3 days</option>
                        <option value="7" <?php echo e(($days ?? 15) == 7 ? 'selected' : ''); ?>>7 days</option>
                        <option value="15" <?php echo e(($days ?? 15) == 15 ? 'selected' : ''); ?>>15 days</option>
                        <option value="30" <?php echo e(($days ?? 15) == 30 ? 'selected' : ''); ?>>30 days</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <?php if($deletedEvents->count() > 0): ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form id="deletedEventsBulkRestoreForm" method="POST" action="<?php echo e(route('admin.deletedEvents.restoreSelected')); ?>">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="event_ids" id="selectedDeletedEventIds">
                        <button type="button" class="btn btn-success" onclick="deletedEventsBulkRestore()">
                            <i class="fas fa-trash-restore"></i> Restore Selected
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletedEventsPermanentDeleteAllModal">
                        <i class="fas fa-times-circle"></i> Permanently Delete All
                    </button>
                    <span id="deletedEventsSelectedCount" class="text-muted ms-3">0 events selected</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Deleted Events Table -->
    <div class="card">
        <div class="card-body">
            <?php if(isset($deletedEvents) && $deletedEvents->count() > 0): ?>
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;" id="deletedEventsTable">
                        <thead>
                            <tr>
                                <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="deletedEventsSelectAll" onchange="deletedEventsToggleSelectAll()"></th>
                                <th>Event Ticket</th>                                <th>Event Date</th>
                                <th>Location</th>
                                <th>Department</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Requested By</th>
                                <th>Deleted Date</th>
                                <th>Deleted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $deletedEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr data-id="<?php echo e($event->id); ?>">
                                    <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-event-checkbox" value="<?php echo e($event->id); ?>" onchange="deletedEventsUpdateSelectedCount()"></td>
                                    <td>EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo e($event->getCategoryLabel()); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($event->event_date->format('M d, Y')); ?></td>
                                    <td><?php echo e($event->location); ?></td>
                                    <td><?php echo e($event->department); ?></td>
                                    <td>
                                        <?php
                                            $priorityColors = [
                                                'low' => 'success',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'urgent' => 'dark'
                                            ];
                                            $priorityClass = $priorityColors[$event->priority] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo e($priorityClass); ?>">
                                            <?php echo e(ucfirst($event->priority)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = match($event->status) {
                                                'Pending' => 'warning',
                                                'Approved' => 'success',
                                                'Rejected' => 'danger',
                                                'Cancelled' => 'secondary',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?php echo e($statusClass); ?>">
                                            <?php echo e($event->status); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($event->user ? $event->user->name : 'Unknown'); ?></td>
                                    <td><?php echo e($event->updated_at->format('M d, Y h:i A')); ?></td>
                                    <td><?php echo e($event->deletedBy ? $event->deletedBy->name : 'System'); ?></td>
                                    <td>
                                        <div class="action-icons">
                                            <button type="button" class="btn btn-sm btn-info bg-transparent" data-bs-toggle="modal" data-bs-target="#deletedEventsViewModal<?php echo e($event->id); ?>" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form action="<?php echo e(route('admin.deletedEvents.restore', $event->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('POST'); ?>
                                                <button type="submit" class="btn btn-sm btn-success bg-transparent" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('admin.deletedEvents.permanentDelete', $event->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-danger bg-transparent" title="Permanently Delete"
                                                    data-confirm="Are you sure you want to permanently delete this event?"
                                                    data-confirm-title="Permanent Delete"
                                                    data-confirm-ok="Yes, Delete Forever"
                                                    data-confirm-color="#dc3545">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Permanent Delete Confirmation Modal -->
                                <div class="modal fade" id="deletedEventsPermanentDeleteModal<?php echo e($event->id); ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Permanently Delete Event</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to permanently delete <strong>EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></strong>?</p>
                                                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. The event will be permanently removed from the system.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="<?php echo e(route('admin.deletedEvents.permanentDelete', $event->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger">Permanently Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- View Event Modal -->
                                <div class="modal fade" id="deletedEventsViewModal<?php echo e($event->id); ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Event Request Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p><strong>Ticket Number:</strong> EVT-<?php echo e(str_pad($event->id, 5, '0', STR_PAD_LEFT)); ?></p>                                                        <p><strong>Status:</strong>
                                                            <span class="badge bg-<?php echo e($event->status == 'Approved' ? 'success' :
                                                                ($event->status == 'Pending' ? 'warning' :
                                                                ($event->status == 'Rejected' ? 'danger' : 'secondary'))); ?>">
                                                                <?php echo e($event->status); ?>

                                                            </span>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong>Requestor:</strong> <?php echo e($event->user->name ?? 'N/A'); ?></p>
                                                        <p><strong>Event Date:</strong> <?php echo e(\Carbon\Carbon::parse($event->event_date)->format('M d, Y')); ?></p>
                                                        <p><strong>Time:</strong> <?php echo e(\Carbon\Carbon::parse($event->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($event->end_time)->format('g:i A')); ?></p>
                                                        <p><strong>Location:</strong> <?php echo e($event->location); ?></p>
                                                    </div>
                                                </div>
                                                <hr>
                                                <p><strong>Description:</strong></p>
                                                <p><?php echo e($event->description); ?></p>
                                                <?php if($event->notes): ?>
                                                    <hr>
                                                    <p><strong>Notes:</strong></p>
                                                    <p><?php echo e($event->notes); ?></p>
                                                <?php endif; ?>
                                                <?php if($event->deletedBy): ?>
                                                    <hr>
                                                    <p><strong>Deleted By:</strong> <?php echo e($event->deletedBy->name); ?></p>
                                                    <p><strong>Deleted Date:</strong> <?php echo e($event->updated_at->format('M d, Y h:i A')); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="13" class="text-center p-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-check-circle fa-2x d-block mb-3"></i>
                                            <h5>No Deleted Events</h5>
                                            <p class="mb-0">Deleted events will appear here. You can delete events from the Events page.</p>
                                            <a href="<?php echo e(route('admin.events')); ?>" class="btn btn-primary mt-3">
                                                <i class="fas fa-calendar-alt"></i> Go to Events
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-2x d-block mb-3 text-success"></i>
                        <h5>No Deleted Events</h5>
                        <p class="mb-0 text-muted">Deleted events will appear here. You can delete events from the Events page.</p>
                        <a href="<?php echo e(route('admin.events')); ?>" class="btn btn-primary mt-3">
                            <i class="fas fa-calendar-alt"></i> Go to Events
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Archive Event Function
function showArchiveEventModal(eventId) {
    confirmArchive({
        title: 'Archive Event?',
        text: 'This event will be archived and hidden from your active list.',
        confirmButtonText: '<i class="fas fa-archive me-1"></i> Archive'
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Archiving...',
                html: '<div class="spinner-border text-primary"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/events/' + eventId + '/archive';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '<?php echo e(csrf_token()); ?>';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Delete Event Function
function showDeleteEventModal(eventId) {
    confirmDelete({
        title: 'Delete Event?',
        text: 'This event will be moved to deleted. You can restore it later from the Deleted tab.',
        confirmButtonText: '<i class="fas fa-trash me-1"></i> Delete'
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Deleting...',
                html: '<div class="spinner-border text-danger"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/events/' + eventId + '/delete';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '<?php echo e(csrf_token()); ?>';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Approve Event Function
function approveEvent(eventId) {
    confirmApprove({
        title: 'Approve Event?',
        text: 'This event request will be approved and the requestor will be notified.',
        confirmButtonText: '<i class="fas fa-check me-1"></i> Approve'
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Approving...',
                html: '<div class="spinner-border text-success"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/events/' + eventId + '/approve';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '<?php echo e(csrf_token()); ?>';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Reject Event Function
function rejectEvent(eventId) {
    confirmReject({
        title: 'Reject Event?',
        text: 'This event request will be rejected and the requestor will be notified.',
        confirmButtonText: '<i class="fas fa-times me-1"></i> Reject'
    }).then(result => {
        if (result.isConfirmed) {
            // Show loading
            getSwal().fire({
                title: 'Rejecting...',
                html: '<div class="spinner-border text-danger"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
            
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/events/' + eventId + '/reject';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '<?php echo e(csrf_token()); ?>';
            form.appendChild(csrfToken);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Event Action Modal Functions
let eventActionType = null;
let eventActionId = null;

function showEventActionModal(type, id, name) {
    eventActionType = type;
    eventActionId = id;

    const modal = new bootstrap.Modal(document.getElementById('eventActionModal'));
    const modalHeader = document.getElementById('eventActionModalHeader');
    const modalMessage = document.getElementById('eventActionMessage');
    const modalAlert = document.getElementById('eventActionAlert');
    const confirmBtn = document.getElementById('eventActionConfirmBtn');

    modalAlert.classList.remove('alert-info', 'alert-warning', 'alert-danger', 'd-none');

    if (type === 'archive') {
        modalHeader.className = 'modal-header bg-secondary text-white';
        document.getElementById('eventActionModalLabel').innerHTML = '<i class="fas fa-archive"></i> Archive Event';
        modalMessage.innerHTML = 'Are you sure you want to archive <strong>' + name + '</strong>?';
        modalAlert.classList.add('alert-info');
        modalAlert.innerHTML = '<i class="fas fa-info-circle"></i> You can restore this event later from the Archive tab.';
        confirmBtn.className = 'btn btn-secondary';
        confirmBtn.innerHTML = '<i class="fas fa-archive"></i> Archive';
    } else if (type === 'delete') {
        modalHeader.className = 'modal-header bg-danger text-white';
        document.getElementById('eventActionModalLabel').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Delete Event';
        modalMessage.innerHTML = 'Are you sure you want to delete <strong>' + name + '</strong>?';
        modalAlert.classList.add('alert-warning');
        modalAlert.innerHTML = '<i class="fas fa-warning"></i> This action will move the event to deleted. You can restore it later from the Deleted tab.';
        confirmBtn.className = 'btn btn-danger';
        confirmBtn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }

    // Set up confirm button click
    confirmBtn.onclick = function() {
        executeEventAction(type, id);
    };

    modal.show();
}

function executeEventAction(type, id) {
    let url = '';
    let method = 'POST';

    if (type === 'archive') {
        url = '/events/' + id + '/archive';
    } else if (type === 'delete') {
        url = '/events/' + id + '/delete';
    }

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            swalToast(type.charAt(0).toUpperCase() + type.slice(1) + ' successful!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else if (data.error) {
            swalAlert(data.error, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        swalAlert('Error performing action', 'error');
    });

    // Close modal
    const modalEl = document.getElementById('eventActionModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}
</script>

<?php if(($viewType ?? '') == 'deleted'): ?>
<!-- Permanent Delete All Confirmation Modal for Deleted Events -->
<div class="modal fade" id="deletedEventsPermanentDeleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Permanently Delete All Events</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete all <strong><?php echo e($deletedEvents->count()); ?></strong> events?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. All events will be permanently removed from the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo e(route('admin.deletedEvents.permanentDeleteAll')); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger">Permanently Delete All</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Context menu variables for deleted events
    let deletedEventsContextMenu = document.getElementById('deletedEventsContextMenu');
    let currentDeletedEventId = null;

    // Toggle select all checkboxes for deleted events
    function deletedEventsToggleSelectAll() {
        const selectAll = document.getElementById('deletedEventsSelectAll');
        const checkboxes = document.querySelectorAll('.deleted-event-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
        
        deletedEventsUpdateSelectedCount();
    }

    // Update selected count for deleted events
    function deletedEventsUpdateSelectedCount() {
        const countEl = document.getElementById('deletedEventsSelectedCount');
        const eventIdsEl = document.getElementById('selectedDeletedEventIds');
        
        // Return early if elements don't exist (no events)
        if (!countEl || !eventIdsEl) {
            return;
        }
        
        const checkboxes = document.querySelectorAll('.deleted-event-checkbox:checked');
        const count = checkboxes.length;
        countEl.textContent = count + ' event' + (count !== 1 ? 's' : '') + ' selected';
        
        // Update hidden input for bulk form
        const selectedIds = Array.from(checkboxes).map(cb => cb.value);
        eventIdsEl.value = JSON.stringify(selectedIds);
    }

    // Bulk restore function for deleted events
    function deletedEventsBulkRestore() {
        const checkboxes = document.querySelectorAll('.deleted-event-checkbox:checked');
        if (checkboxes.length === 0) {
            swalAlert('Please select at least one event to restore.', 'warning');
            return;
        }
        
        if (confirm('Are you sure you want to restore ' + checkboxes.length + ' event(s)?')) {
            document.getElementById('deletedEventsBulkRestoreForm').submit();
        }
    }

    // Context menu functions for deleted events
    function showDeletedEventsContextMenu(e, eventId) {
        e.preventDefault();
        currentDeletedEventId = eventId;
        
        deletedEventsContextMenu.style.display = 'block';
        deletedEventsContextMenu.style.left = e.pageX + 'px';
        deletedEventsContextMenu.style.top = e.pageY + 'px';
    }

    function hideDeletedEventsContextMenu() {
        deletedEventsContextMenu.style.display = 'none';
    }

    function deletedEventsContextView() {
        hideDeletedEventsContextMenu();
        const modalId = 'deletedEventsViewModal' + currentDeletedEventId;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    function deletedEventsContextRestore() {
        hideDeletedEventsContextMenu();
        if (confirm('Are you sure you want to restore this event?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/deleted-events/' + currentDeletedEventId + '/restore';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    }

    function deletedEventsContextPermanentDelete() {
        hideDeletedEventsContextMenu();
        const modalId = 'deletedEventsPermanentDeleteModal' + currentDeletedEventId;
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
    }

    // Add right-click listeners to deleted events table rows
    document.querySelectorAll('#deletedEventsTable tbody tr').forEach(row => {
        row.addEventListener('contextmenu', (e) => {
            const eventId = row.getAttribute('data-id');
            if (eventId) {
                showDeletedEventsContextMenu(e, eventId);
            }
        });
    });

    // Hide context menu on click outside
    document.addEventListener('click', (e) => {
        if (deletedEventsContextMenu && !deletedEventsContextMenu.contains(e.target)) {
            hideDeletedEventsContextMenu();
        }
    });

    // Handle retention days dropdown change (only if element exists) and page load
    document.addEventListener('DOMContentLoaded', function() {
        // Update selected count on page load
        deletedEventsUpdateSelectedCount();

        // Handle retention days dropdown change
        const retentionDaysElement = document.getElementById('retentionDays');
        if (retentionDaysElement) {
            retentionDaysElement.addEventListener('change', function() {
                const days = this.value;
                if (confirm(`Set auto-filter to show events deleted more than ${days} days ago?`)) {
                    // Show loading indicator
                    this.disabled = true;

                    // Make AJAX request to save preference
                    fetch('<?php echo e(route("saveAutoDeletePreference")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ days: parseInt(days), module: 'event_requests' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show filtered results
                            window.location.href = '<?php echo e(route("admin.events", ["view" => "deleted"])); ?>&days=' + days;
                        } else {
                            swalAlert('Error saving preference.', 'error');
                        }
                    })
                    .catch(error => {
                        swalAlert('An error occurred while saving your preference.', 'error');
                        console.error(error);
                    })
                    .finally(() => {
                        // Restore dropdown
                        this.disabled = false;
                    });
                } else {
                    // Reset to previous value
                    this.value = '<?php echo e($days ?? 15); ?>';
                }
            });
        }
    });
</script>
<?php endif; ?>

<?php $__env->stopSection(); ?>










<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/events.blade.php ENDPATH**/ ?>