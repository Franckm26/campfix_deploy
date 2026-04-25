

<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title'); ?>
<h2>My Event Requests</h2>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3">

    <!-- Context Menu -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li><a href="#" id="ctxView" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" id="ctxEdit" onclick="contextEdit()"><i class="fas fa-edit"></i> Edit</a></li>
            <li><a href="#" id="ctxArchive" onclick="contextArchive()"><i class="fas fa-archive"></i> Archive</a></li>
            <li><a href="#" id="ctxDelete" onclick="contextDelete()"><i class="fas fa-trash"></i> Delete</a></li>
        </ul>
    </div>

    <!-- Context Menu for Archives -->
    <div id="contextMenuArchive" class="context-menu">
        <ul>
            <li><a href="#" id="ctxViewArchive" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" id="ctxRestore" onclick="contextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" id="ctxDeleteArchive" onclick="contextDeleteFromArchive()"><i class="fas fa-trash"></i> Delete</a></li>
        </ul>
    </div>

    <!-- Context Menu for Deleted -->
    <div id="contextMenuDeleted" class="context-menu">
        <ul>
            <li><a href="#" id="ctxViewDeleted" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" id="ctxRestoreDeleted" onclick="contextRestoreDeleted()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" id="ctxPermanentDelete" onclick="contextPermanentDelete()"><i class="fas fa-ban"></i> Permanent Delete</a></li>
        </ul>
    </div>


    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <!-- Tabs Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <ul class="nav nav-tabs border-0 mb-0 flex-wrap" id="eventTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e(($viewType ?? 'active') === 'active' ? 'active' : ''); ?>"
                    id="active-tab" data-bs-toggle="tab" data-bs-target="#active-events"
                    type="button" role="tab" aria-controls="active-events"
                    aria-selected="<?php echo e(($viewType ?? 'active') === 'active' ? 'true' : 'false'); ?>">
                <i class="fas fa-list"></i> Active
                <?php if(isset($requests) && $requests->count() > 0): ?>
                    <span class="badge bg-primary ms-1"><?php echo e($requests->count()); ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e(($viewType ?? '') === 'archives' ? 'active' : ''); ?>"
                    id="archives-tab" data-bs-toggle="tab" data-bs-target="#archived-events"
                    type="button" role="tab" aria-controls="archived-events"
                    aria-selected="<?php echo e(($viewType ?? '') === 'archives' ? 'true' : 'false'); ?>">
                <i class="fas fa-archive"></i> Archives
                <?php if(isset($archivedRequests) && $archivedRequests->count() > 0): ?>
                    <span class="badge bg-warning ms-1"><?php echo e($archivedRequests->count()); ?></span>
                <?php endif; ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo e(($viewType ?? '') === 'deleted' ? 'active' : ''); ?>"
                    id="deleted-tab" data-bs-toggle="tab" data-bs-target="#deleted-events"
                    type="button" role="tab" aria-controls="deleted-events"
                    aria-selected="<?php echo e(($viewType ?? '') === 'deleted' ? 'true' : 'false'); ?>">
                <i class="fas fa-trash"></i> Deleted
                <?php if(isset($deletedRequests) && $deletedRequests->count() > 0): ?>
                    <span class="badge bg-danger ms-1"><?php echo e($deletedRequests->count()); ?></span>
                <?php endif; ?>
            </button>
        </li>
    </ul>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#eventRequestModal">
            <i class="fas fa-plus"></i> New Request
        </button>
    </div>

    <!-- Tab Content -->
    <div class="tab-content show" id="eventTabContent">

        <!-- Active Events Tab -->
        <div class="tab-pane fade <?php echo e(($viewType ?? 'active') === 'active' ? 'show active' : ''); ?>"
             id="active-events" role="tabpanel" aria-labelledby="active-tab">

            <!-- Filters for Active -->
            <?php if(($viewType ?? 'active') === 'active'): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('events.my')); ?>" class="row g-2 align-items-center" id="filterForm">
                        <input type="hidden" name="view" value="active">
                        <div class="col-6 col-md">
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
                                <option value="event" <?php echo e(request('category') == 'event' ? 'selected' : ''); ?>>Event</option>
                                <option value="meeting" <?php echo e(request('category') == 'meeting' ? 'selected' : ''); ?>>Meeting</option>
                                <option value="activity" <?php echo e(request('category') == 'activity' ? 'selected' : ''); ?>>Activity</option>
                                <option value="training" <?php echo e(request('category') == 'training' ? 'selected' : ''); ?>>Training</option>
                                <option value="other" <?php echo e(request('category') == 'other' ? 'selected' : ''); ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date"
                                value="<?php echo e(request('date_from')); ?>">
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date"
                                value="<?php echo e(request('date_to')); ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="<?php echo e(route('events.my', ['view' => 'active'])); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Active Events List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Active -->
                    <div class="bulk-actions mb-3" id="activeBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm" onclick="batchArchiveSelected()">
                                <i class="fas fa-archive"></i> Archive Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchSoftDeleteSelected()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="activeSelectedCount">0 selected</span>
                    </div>

                    <?php if($requests->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 50px;"><input type="checkbox" id="selectAllActive" onchange="toggleSelectAll('active')"></th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr data-id="<?php echo e($request->id); ?>" data-view="active">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="active-checkbox" value="<?php echo e($request->id); ?>" onchange="updateActiveBulkActions()"></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo e(ucfirst($request->category)); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e(\Carbon\Carbon::parse($request->event_date)->format('M d, Y')); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($request->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($request->end_time)->format('g:i A')); ?></td>
                                            <td><?php echo e($request->location); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($request->status == 'Approved' ? 'success' :
                                                    ($request->status == 'Rejected' ? 'danger' :
                                                    ($request->status == 'Cancelled' ? 'secondary' : 'warning'))); ?>">
                                                    <?php echo e($request->status); ?>

                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent(<?php echo e($request->id); ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if($request->status == 'Pending'): ?>
                                                        <form action="<?php echo e(route('events.cancel', $request->id)); ?>" method="POST" class="d-inline">
                                                            <?php echo csrf_field(); ?>
                                                            <button type="submit" class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Cancel this request?')" title="Cancel">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <a href="#" class="btn btn-sm btn-secondary"
                                                        onclick="event.preventDefault(); showEventArchiveModal(<?php echo e($request->id); ?>);"
                                                        title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </a>
                                                    <form action="<?php echo e(route('events.delete', $request->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Delete this event? It will be moved to deleted events.')" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <h4 class="text-muted">No active event requests</h4>
                            <p>Submit your first event request</p>
                            <a href="<?php echo e(route('events.create')); ?>" class="btn btn-primary">Create Event Request</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Archived Events Tab -->
        <div class="tab-pane fade <?php echo e(($viewType ?? '') === 'archives' ? 'show active' : ''); ?>"
             id="archived-events" role="tabpanel" aria-labelledby="archives-tab">

            <!-- Filters for Archives -->
            <?php if(($viewType ?? '') === 'archives'): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('events.my')); ?>" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="archives">
                        <div class="col-6 col-md">
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
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date"
                                value="<?php echo e(request('date_from')); ?>">
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date"
                                value="<?php echo e(request('date_to')); ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="<?php echo e(route('events.my', ['view' => 'archives'])); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Archived Events List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Archived -->
                    <div class="bulk-actions mb-3" id="archiveBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreSelected()">
                                <i class="fas fa-trash-restore"></i> Restore Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchDeleteFromArchiveSelected()">
                                <i class="fas fa-trash"></i> Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="archiveSelectedCount">0 selected</span>
                    </div>

                    <?php if(isset($archivedRequests) && $archivedRequests->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 50px;"><input type="checkbox" id="selectAllArchive" onchange="toggleSelectAll('archive')"></th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $archivedRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr data-id="<?php echo e($request->id); ?>" data-view="archive">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="archive-checkbox" value="<?php echo e($request->id); ?>" onchange="updateArchiveBulkActions()"></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo e(ucfirst($request->category)); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e(\Carbon\Carbon::parse($request->event_date)->format('M d, Y')); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($request->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($request->end_time)->format('g:i A')); ?></td>
                                            <td><?php echo e($request->location); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($request->status == 'Approved' ? 'success' : ($request->status == 'Rejected' ? 'danger' : ($request->status == 'Cancelled' ? 'secondary' : 'warning'))); ?>">
                                                    <?php echo e($request->status); ?>

                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent(<?php echo e($request->id); ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form action="<?php echo e(route('events.restore', $request->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Restore this request?')" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <form action="<?php echo e(route('events.delete', $request->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Permanently delete this event?')" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <h4 class="text-muted">No archived event requests</h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Deleted Events Tab -->
        <div class="tab-pane fade <?php echo e(($viewType ?? '') === 'deleted' ? 'show active' : ''); ?>"
             id="deleted-events" role="tabpanel" aria-labelledby="deleted-tab">

            <!-- Filters for Deleted -->
            <?php if(($viewType ?? '') === 'deleted'): ?>
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('events.my')); ?>" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="deleted">
                        <div class="col-6 col-md">
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
                            <input type="date" name="date_from" class="form-control form-control-sm" placeholder="From Date"
                                value="<?php echo e(request('date_from')); ?>">
                        </div>
                        <div class="col-6 col-md">
                            <input type="date" name="date_to" class="form-control form-control-sm" placeholder="To Date"
                                value="<?php echo e(request('date_to')); ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="<?php echo e(route('events.my', ['view' => 'deleted'])); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- Deleted Events List -->
            <div class="card">
                <div class="card-body">
                    <!-- Bulk Actions for Deleted -->
                    <div class="bulk-actions mb-3" id="deletedBulkActions" style="display: none;">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success btn-sm" onclick="batchRestoreDeletedSelected()">
                                <i class="fas fa-trash-restore"></i> Restore Selected
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="batchPermanentDeleteSelected()">
                                <i class="fas fa-ban"></i> Permanent Delete Selected
                            </button>
                        </div>
                        <span class="ms-2 text-muted" id="deletedSelectedCount">0 selected</span>
                    </div>

                    <?php if(isset($deletedRequests) && $deletedRequests->count() > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-nowrap" style="min-width: 50px;"><input type="checkbox" id="selectAllDeleted" onchange="toggleSelectAll('deleted')"></th>
                                        <th class="text-nowrap" style="min-width: 120px;">Category</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Date</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Time</th>
                                        <th class="text-nowrap" style="min-width: 120px;">Location</th>
                                        <th class="text-nowrap" style="min-width: 100px;">Status</th>
                                        <th class="text-nowrap" style="min-width: 200px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $deletedRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr data-id="<?php echo e($request->id); ?>" data-view="deleted">
                                            <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-checkbox" value="<?php echo e($request->id); ?>" onchange="updateDeletedBulkActions()"></td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?php echo e(ucfirst($request->category)); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e(\Carbon\Carbon::parse($request->event_date)->format('M d, Y')); ?></td>
                                            <td><?php echo e(\Carbon\Carbon::parse($request->start_time)->format('g:i A')); ?> - <?php echo e(\Carbon\Carbon::parse($request->end_time)->format('g:i A')); ?></td>
                                            <td><?php echo e($request->location); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($request->status == 'Approved' ? 'success' : ($request->status == 'Rejected' ? 'danger' : ($request->status == 'Cancelled' ? 'secondary' : 'warning'))); ?>">
                                                    <?php echo e($request->status); ?>

                                                </span>
                                            </td>
                                            <td class="text-nowrap">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewEvent(<?php echo e($request->id); ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <form action="<?php echo e(route('events.restore', $request->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            onclick="return confirm('Restore this event?')" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <form action="<?php echo e(route('events.delete', $request->id)); ?>" method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="permanent" value="1">
                                                        <button type="submit" class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Permanently delete this event? This action cannot be undone.')" title="Permanent Delete">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <h4 class="text-muted">No deleted event requests</h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewEventModal" tabindex="-1" aria-labelledby="viewEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewEventModalLabel">Event Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewEventContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Archive Modal -->
<div class="modal fade" id="eventArchiveModal" tabindex="-1" aria-labelledby="eventArchiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventArchiveModalLabel">Archive Event Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventArchiveForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <input type="hidden" id="archiveEventId" name="event_id" value="">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This event request will be archived and hidden from your active list.
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

<style>
.context-menu {
    position: fixed;
    z-index: 1000;
    display: none;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
    min-width: 180px;
}
.context-menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.context-menu ul li {
    border-bottom: 1px solid #eee;
}
.context-menu ul li:last-child {
    border-bottom: none;
}
.context-menu ul li a {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: #333;
}
.context-menu ul li a:hover {
    background: #f5f5f5;
}
.context-menu ul li a i {
    margin-right: 8px;
    width: 20px;
}
/* Dropdown fix for table */
.table .dropdown {
    position: static;
}

/* Approval Steps Styling */
.approval-step {
    padding: 15px 10px;
    border-radius: 10px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 2px solid #dee2e6;
}

.approval-step.approved {
    background-color: #d4edda;
    border-color: #28a745;
}

.approval-step.pending {
    background-color: #fff3cd;
    border-color: #ffc107;
}

.approval-step.rejected {
    background-color: #f8d7da;
    border-color: #dc3545;
}

.step-icon {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.approval-step h6 {
    font-size: 12px;
    margin-bottom: 5px;
    font-weight: 600;
}

.approval-step small {
    font-size: 10px;
}
</style>

<script>
// View Event Modal
function formatTime12(t) {
    if (!t) return '';
    const [h, m] = t.split(':');
    const hour = parseInt(h, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    return (hour % 12 || 12) + ':' + m + ' ' + ampm;
}
function viewEvent(id) {
    const modal = new bootstrap.Modal(document.getElementById('viewEventModal'));
    const contentDiv = document.getElementById('viewEventContent');

    // Store current event ID
    window.currentEventId = id;

    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/events/' + id, {
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.error || 'Request failed with status ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }

        const event = data.event;
        const userName = event.user ? event.user.name : 'Unknown';
        const userRole = event.user ? event.user.role : '';

        const statusClass = event.status === 'Approved' ? 'success' :
            (event.status === 'Rejected' ? 'danger' :
            (event.status === 'Cancelled' ? 'secondary' : 'warning'));

        const categoryBadge = `<span class="badge bg-info">${event.category.charAt(0).toUpperCase() + event.category.slice(1)}</span>`;

        let materialsHtml = '';
        if (event.materials_needed && event.materials_needed.length > 0) {
            materialsHtml = `
                <div class="mt-3">
                    <p><strong>Materials Needed:</strong></p>
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Qty</th>
                                <th>Item</th>
                                <th>Purpose</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${event.materials_needed.map(m => `
                                <tr>
                                    <td>${m.qty ?? 1}</td>
                                    <td>${m.item ?? 'N/A'}</td>
                                    <td>${m.purpose ?? 'N/A'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }

        // Calculate approval progress
        const approvalLevel = event.approval_level || 0;
        const progressPercentage = event.status === 'Approved' ? 100 : 
                                   event.status === 'Rejected' ? (approvalLevel / 4 * 100) :
                                   (approvalLevel / 4 * 100);

        contentDiv.innerHTML = `
            <!-- Approval Progress -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Approval Progress</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Progress</span>
                            <span class="fw-bold">
                                ${event.status === 'Approved' ? '<span class="text-success"><i class="fas fa-check-circle"></i> Fully Approved</span>' :
                                  event.status === 'Rejected' ? '<span class="text-danger"><i class="fas fa-times-circle"></i> Rejected</span>' :
                                  `<span class="text-warning">${approvalLevel} / 4</span>`}
                            </span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-${statusClass}" role="progressbar" style="width: ${progressPercentage}%">
                                ${Math.round(progressPercentage)}%
                            </div>
                        </div>
                    </div>

                    <!-- Approval Steps -->
                    <div class="row text-center g-2">
                        <div class="col-3">
                            <div class="approval-step ${approvalLevel >= 1 ? 'approved' : (approvalLevel === 0 && event.status === 'Pending' ? 'pending' : '')}">
                                <div class="step-icon mb-2">
                                    ${approvalLevel >= 1 ? '<i class="fas fa-check-circle fa-2x text-success"></i>' : '<i class="fas fa-clock fa-2x text-muted"></i>'}
                                </div>
                                <h6 class="small">Program Head</h6>
                                <small class="text-muted">${approvalLevel >= 1 ? 'Approved' : 'Pending'}</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="approval-step ${approvalLevel >= 2 ? 'approved' : (approvalLevel === 1 ? 'pending' : '')}">
                                <div class="step-icon mb-2">
                                    ${approvalLevel >= 2 ? '<i class="fas fa-check-circle fa-2x text-success"></i>' : '<i class="fas fa-clock fa-2x text-muted"></i>'}
                                </div>
                                <h6 class="small">Academic Head</h6>
                                <small class="text-muted">${approvalLevel >= 2 ? 'Approved' : (approvalLevel >= 1 ? 'Waiting' : 'Pending')}</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="approval-step ${approvalLevel >= 3 ? 'approved' : (approvalLevel >= 2 ? 'pending' : '')}">
                                <div class="step-icon mb-2">
                                    ${approvalLevel >= 3 ? '<i class="fas fa-check-circle fa-2x text-success"></i>' : '<i class="fas fa-clock fa-2x text-muted"></i>'}
                                </div>
                                <h6 class="small">Building Admin</h6>
                                <small class="text-muted">${approvalLevel >= 3 ? 'Approved' : (approvalLevel >= 2 ? 'Waiting' : 'Pending')}</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="approval-step ${event.status === 'Approved' ? 'approved' : (approvalLevel >= 3 ? 'pending' : '')}">
                                <div class="step-icon mb-2">
                                    ${event.status === 'Approved' ? '<i class="fas fa-check-circle fa-2x text-success"></i>' : '<i class="fas fa-clock fa-2x text-muted"></i>'}
                                </div>
                                <h6 class="small">School Admin</h6>
                                <small class="text-muted">${event.status === 'Approved' ? 'Approved' : (approvalLevel >= 3 ? 'Waiting' : 'Pending')}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Current Status -->
                    <div class="mt-3 text-center">
                        <span class="badge bg-${statusClass} fs-6">
                            <i class="fas fa-${event.status === 'Approved' ? 'check' : (event.status === 'Rejected' ? 'times' : 'clock')} me-1"></i>
                            ${event.status}
                        </span>
                        ${event.status === 'Approved' ? `
                        <div class="mt-3">
                            <a href="/events/${event.id}/pdf" class="btn btn-primary" target="_blank">
                                <i class="fas fa-file-pdf me-2"></i> Download PDF
                            </a>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Category:</strong> ${categoryBadge}</p>
                    <p><strong>Date:</strong> ${new Date(event.event_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
                    <p><strong>Time:</strong> ${formatTime12(event.start_time)} - ${formatTime12(event.end_time)}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Location:</strong> ${event.location}</p>
                    <p><strong>Department:</strong> ${event.department || 'N/A'}</p>
                    <p><strong>Priority:</strong> <span class="badge bg-${event.priority === 'urgent' ? 'danger' : (event.priority === 'high' ? 'warning' : (event.priority === 'medium' ? 'info' : 'secondary'))}">${event.priority.charAt(0).toUpperCase() + event.priority.slice(1)}</span></p>
                    <p><strong>Submitted:</strong> ${new Date(event.created_at).toLocaleString()}</p>
                </div>
            </div>
            <div class="mt-3">
                <p><strong>Description:</strong></p>
                <p class="text-muted">${event.description}</p>
            </div>
            ${event.notes ? `
            <div class="mt-3">
                <p><strong>Notes:</strong></p>
                <p class="text-muted">${event.notes}</p>
            </div>
            ` : ''}
            ${materialsHtml}

            <!-- Event Discussion -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Event Discussion</h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;" id="chatContainer-${event.id}">
                    <div id="chatMessages-${event.id}" class="mb-3">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-spinner fa-spin"></i> Loading discussions...
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <form id="chatForm-${event.id}" class="d-flex gap-2">
                        <input type="text" id="chatMessage-${event.id}" class="form-control" placeholder="Type your message..." maxlength="1000">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        `;

        // Initialize discussion chat
        initializeDiscussionChat(event.id);
    })
    .catch(error => {
        console.error('Error:', error);
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading event details: ' + error.message + '</div>';
    });
}

function initializeDiscussionChat(eventId) {
    const chatMessages = document.getElementById(`chatMessages-${eventId}`);
    const chatForm = document.getElementById(`chatForm-${eventId}`);
    const chatMessage = document.getElementById(`chatMessage-${eventId}`);
    const chatContainer = document.getElementById(`chatContainer-${eventId}`);
    const currentUserId = <?php echo e(auth()->id()); ?>;
    
    // Load discussions
    loadDiscussions();
    
    function loadDiscussions() {
        fetch(`/events/${eventId}/discussions`)
            .then(response => response.json())
            .then(data => {
                displayDiscussions(data);
            })
            .catch(error => {
                console.error('Error loading discussions:', error);
                chatMessages.innerHTML = '<div class="text-center text-muted py-3">Failed to load discussions</div>';
            });
    }
    
    function displayDiscussions(discussions) {
        if (discussions.length === 0) {
            chatMessages.innerHTML = '<div class="text-center text-muted py-3">No discussions yet. Start the conversation!</div>';
            return;
        }
        
        chatMessages.innerHTML = discussions.map(discussion => {
            const isOwn = discussion.user_id === currentUserId;
            const time = new Date(discussion.created_at).toLocaleString();
            
            return `
                <div class="mb-3 d-flex ${isOwn ? 'justify-content-end' : 'justify-content-start'}">
                    <div class="d-flex flex-column ${isOwn ? 'align-items-end' : 'align-items-start'}" style="max-width: 75%;">
                        <div class="text-muted small mb-1">
                            <strong>${discussion.user ? discussion.user.name : 'Unknown'}</strong>
                            <span class="ms-1">${time}</span>
                        </div>
                        <div class="p-2 rounded ${isOwn ? 'bg-primary text-white' : 'bg-light text-dark'}" style="word-wrap: break-word;">
                            ${escapeHtml(discussion.message)}
                        </div>
                        ${isOwn ? `<button class="btn btn-link btn-sm text-danger p-0 mt-1" onclick="deleteDiscussion(${discussion.id}, ${eventId})">Delete</button>` : ''}
                    </div>
                </div>
            `;
        }).join('');
        
        // Scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    // Handle form submission
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const message = chatMessage.value.trim();
        if (!message) return;
        
        fetch(`/events/${eventId}/discussions`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
            },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            chatMessage.value = '';
            loadDiscussions();
        })
        .catch(error => {
            console.error('Error sending message:', error);
            alert('Failed to send message. Please try again.');
        });
    });
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Delete discussion function
function deleteDiscussion(discussionId, eventId) {
    if (!confirm('Are you sure you want to delete this message?')) return;
    
    fetch(`/discussions/${discussionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Reload discussions for this event
        initializeDiscussionChat(eventId);
    })
    .catch(error => {
        console.error('Error deleting message:', error);
        alert('Failed to delete message.');
    });
}

function showEventArchiveModal(eventId) {
    document.getElementById('eventArchiveForm').action = '/events/' + eventId + '/archive';
    document.getElementById('archiveEventId').value = eventId;

    var modal = new bootstrap.Modal(document.getElementById('eventArchiveModal'));
    modal.show();
}

// Bulk actions functions (placeholders for now)
function toggleSelectAll(type) {
    // Implementation for bulk select
}

function updateActiveBulkActions() {
    // Implementation for bulk actions
}

function updateArchiveBulkActions() {
    // Implementation for bulk actions
}

function updateDeletedBulkActions() {
    // Implementation for bulk actions
}

function batchArchiveSelected() {
    // Implementation for bulk archive
}

function batchSoftDeleteSelected() {
    // Implementation for bulk delete
}

function batchRestoreSelected() {
    // Implementation for bulk restore
}

function batchDeleteFromArchiveSelected() {
    // Implementation for bulk delete from archive
}

function batchRestoreDeletedSelected() {
    // Implementation for bulk restore deleted
}

function batchPermanentDeleteSelected() {
    // Implementation for bulk permanent delete
}

// Context menu functions
function contextView() {
    // Implementation for context view
}

function contextEdit() {
    // Implementation for context edit
}

function contextArchive() {
    // Implementation for context archive
}

function contextDelete() {
    // Implementation for context delete
}

function contextRestore() {
    // Implementation for context restore
}

function contextDeleteFromArchive() {
    // Implementation for context delete from archive
}

function contextRestoreDeleted() {
    // Implementation for context restore deleted
}

function contextPermanentDelete() {
    // Implementation for context permanent delete
}

// Auto-open modal if URL contains open_modal=true
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('open_modal') === 'true') {
        const modal = new bootstrap.Modal(document.getElementById('eventRequestModal'));
        modal.show();

        // Clean up URL by removing the parameter
        const newUrl = window.location.pathname + window.location.search.replace(/[?&]open_modal=true/, '');
        window.history.replaceState({}, document.title, newUrl);
    }

    // Reopen modal if there are validation errors
    <?php if($errors->any()): ?>
        const modal = new bootstrap.Modal(document.getElementById('eventRequestModal'));
        modal.show();
    <?php endif; ?>
});
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/events/my.blade.php ENDPATH**/ ?>