<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<?php if(($viewType ?? '') == 'analytics'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title'); ?>
<h2>Reports</h2>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3">
    
    <!-- Context Menu -->
    <div id="contextMenu" class="context-menu">
        <ul>
            <li><a href="#" id="ctxView" onclick="contextView()"><i class="fas fa-eye"></i> View</a></li>
        </ul>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewConcernModal" tabindex="-1" aria-labelledby="viewConcernModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewConcernModalLabel">Concern Details</h5>
                    <?php if(in_array(auth()->user()->role, ['building_admin', 'school_admin', 'academic_head'])): ?>
                        <button type="button" class="btn btn-primary btn-sm ms-2" onclick="showAssignModal()">
                            <i class="fas fa-user-plus"></i> Assign
                        </button>
                    <?php endif; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewConcernContent">
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

    <!-- Assign Modal -->
    <div class="modal fade" id="assignConcernModal" tabindex="-1" aria-labelledby="assignConcernModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="assignConcernModalLabel">Assign Concern</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="assignConcernForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <input type="hidden" id="assignConcernId" name="concern_id" value="">
                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">Assign to Maintenance Staff</label>
                            <select class="form-select" id="assigned_to" name="assigned_to" required>
                                <option value="">Select maintenance staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-user-plus"></i> Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editConcernModal" tabindex="-1" aria-labelledby="editConcernModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editConcernModalLabel">Edit Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editConcernForm" method="POST" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-body" id="editConcernContent">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div class="modal fade" id="reportArchiveModal" tabindex="-1" aria-labelledby="reportArchiveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportArchiveModalLabel">Archive Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to archive this report?</p>
                    <p class="text-muted">You can restore it later from the archive.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="confirmReportArchive()">Archive</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="reportDeleteModal" tabindex="-1" aria-labelledby="reportDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="reportDeleteModalLabel"><i class="fas fa-exclamation-triangle"></i> Delete Report</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this report?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> This action will move the report to deleted. You can restore it later from the Deleted tab.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="confirmReportDeleteButton" class="btn btn-danger" onclick="confirmReportDelete()"><i class="fas fa-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6">
        </div>
        <div class="col-md-6 text-end">
            <a href="<?php echo e(route('admin.export')); ?>" class="btn btn-success">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center g-2">
                <div class="col-md-5">
                    <ul class="nav nav-pills mb-0">
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(($viewType ?? 'active') == 'active' ? 'active' : ''); ?>" href="<?php echo e(route('admin.reports', ['view' => 'active'])); ?>">
                                <i class="fas fa-clipboard-list"></i> Active Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(($viewType ?? '') == 'resolved' ? 'active' : ''); ?>" href="<?php echo e(route('admin.reports', ['view' => 'resolved'])); ?>" style="color: #28a745;">
                                <i class="fas fa-check-circle"></i> Resolved Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(($viewType ?? '') == 'archives' ? 'active' : ''); ?>" href="<?php echo e(route('admin.reports', ['view' => 'archives'])); ?>">
                                <i class="fas fa-archive"></i> Archived Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(($viewType ?? '') == 'deleted' ? 'active' : ''); ?>" href="<?php echo e(route('admin.reports', ['view' => 'deleted'])); ?>" style="color: #dc3545;">
                                <i class="fas fa-trash-alt"></i> Deleted Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo e(($viewType ?? '') == 'analytics' ? 'active' : ''); ?>" href="<?php echo e(route('admin.reports', ['view' => 'analytics'])); ?>" style="color: #17a2b8;">
                                <i class="fas fa-chart-line"></i> Analytics
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="col-md-7">
                    <form method="GET" action="<?php echo e(route('admin.reports')); ?>" class="row g-2 align-items-center">
                        <input type="hidden" name="view" value="<?php echo e($viewType ?? 'active'); ?>">
                        <div class="col-md-2">
                            <select name="archived" class="form-select form-select-sm">
                                <option value="">Active Concerns</option>
                                <option value="1" <?php echo e(request('archived') == '1' ? 'selected' : ''); ?>>Archived</option>
                                <option value="all" <?php echo e(request('archived') == 'all' ? 'selected' : ''); ?>>All Concerns</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="Pending" <?php echo e(request('status') == 'Pending' ? 'selected' : ''); ?>>Pending</option>
                                <option value="Assigned" <?php echo e(request('status') == 'Assigned' ? 'selected' : ''); ?>>Assigned</option>
                                <option value="In Progress" <?php echo e(request('status') == 'In Progress' ? 'selected' : ''); ?>>In Progress</option>
                                <option value="Resolved" <?php echo e(request('status') == 'Resolved' ? 'selected' : ''); ?>>Resolved</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="priority" class="form-select form-select-sm">
                                <option value="">All Priority</option>
                                <option value="low" <?php echo e(request('priority') == 'low' ? 'selected' : ''); ?>>Low</option>
                                <option value="medium" <?php echo e(request('priority') == 'medium' ? 'selected' : ''); ?>>Medium</option>
                                <option value="high" <?php echo e(request('priority') == 'high' ? 'selected' : ''); ?>>High</option>
                                <option value="urgent" <?php echo e(request('priority') == 'urgent' ? 'selected' : ''); ?>>Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($category->id); ?>" <?php echo e(request('category') == $category->id ? 'selected' : ''); ?>>
                                        <?php echo e($category->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search concerns..." 
                                value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-auto">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                        <div class="col-auto">
                            <a href="<?php echo e(route('admin.reports')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if(($viewType ?? 'active') == 'active'): ?>
    <!-- Summary Cards -->
    <div class="row mb-4" style="display: flex !important;">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total</h5>
                    <h2><?php echo e($reports->count()); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning">
                <div class="card-body">
                    <h5>Pending</h5>
                    <h2><?php echo e($reports->where('status', 'Pending')->count()); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Resolved</h5>
                    <h2><?php echo e($reports->where('status', 'Resolved')->count()); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Critical</h5>
                    <h2><?php echo e($reports->where('severity', 'critical')->count()); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">
            <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                <table class="table table-hover" style="display: table !important;">
                    <thead>
                        <tr>
                            <th class="checkbox-col"><input type="checkbox" id="selectAllReports" onclick="toggleAllReports(this)"></th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Reported By</th>
                            <th>Created</th>
                            <th>Resolved</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr data-id="<?php echo e($report->id); ?>">
                                <td class="checkbox-col"><input type="checkbox" class="report-checkbox" value="<?php echo e($report->id); ?>"></td>
                                <td><?php echo e($report->title ?? 'No Title'); ?></td>
                                        <td><?php echo e($report->category->name ?? 'N/A'); ?></td>
                                        <td><?php echo e($report->location); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo e($report->severity == 'critical' ? 'danger' :
                                                ($report->severity == 'high' ? 'warning' :
                                                ($report->severity == 'medium' ? 'info' : 'secondary'))); ?>">
                                                <?php echo e(ucfirst($report->severity)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo e($report->status == 'Resolved' ? 'success' :
                                                ($report->status == 'In Progress' ? 'warning' :
                                                ($report->status == 'Assigned' ? 'primary' : 'secondary'))); ?>">
                                                <?php echo e($report->status); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <?php echo e($report->user->name ?? 'Unknown'); ?>

                                        </td>
                                        <td><?php echo e($report->created_at->format('M d, Y')); ?></td>
                                        <td><?php echo e($report->resolved_at ? $report->resolved_at->format('M d, Y H:i') : '-'); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" onclick="viewReport(<?php echo e($report->id); ?>)" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="editReport(<?php echo e($report->id); ?>)" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if(!$report->isArchivedByUser(auth()->id())): ?>
                                                    <button type="button" class="btn btn-sm btn-secondary" onclick="showReportArchiveModal(<?php echo e($report->id); ?>)" title="Archive">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <form method="POST" action="<?php echo e(route('reports.restore', $report)); ?>" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if(!$report->assigned_to || $report->status === 'Resolved'): ?>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal(<?php echo e($report->id); ?>)" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete assigned reports until resolved">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="10" class="text-center">No reports found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'resolved'): ?>
    <!-- Resolved Reports Section -->
    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">
            <h5 class="card-title mb-3">
                <i class="fas fa-check-circle text-success"></i> Resolved Reports
                <?php if(isset($resolvedReports)): ?>
                    <span class="badge bg-success ms-2"><?php echo e($resolvedReports->count()); ?></span>
                <?php endif; ?>
            </h5>
            
            <?php if(isset($resolvedReports) && $resolvedReports->count() > 0): ?>
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Severity</th>
                                <th>Reported By</th>
                                <th>Created</th>
                                <th>Resolved</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $resolvedReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr data-id="<?php echo e($report->id); ?>">
                                    <td><?php echo e($report->title ?? 'No Title'); ?></td>
                                    <td><?php echo e($report->category->name ?? 'N/A'); ?></td>
                                    <td><?php echo e($report->location); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo e($report->severity == 'critical' ? 'danger' :
                                            ($report->severity == 'high' ? 'warning' :
                                            ($report->severity == 'medium' ? 'info' : 'secondary'))); ?>">
                                            <?php echo e(ucfirst($report->severity)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($report->user->name ?? 'Unknown'); ?></td>
                                    <td><?php echo e($report->created_at->format('M d, Y')); ?></td>
                                    <td><?php echo e($report->resolved_at ? $report->resolved_at->format('M d, Y H:i') : '-'); ?></td>
                                    <td>₱<?php echo e(number_format($report->cost ?? 0, 2)); ?></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-info" onclick="viewReport(<?php echo e($report->id); ?>)" title="View">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="showReportArchiveModal(<?php echo e($report->id); ?>)" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                            <?php if(!$report->assigned_to || $report->status === 'Resolved'): ?>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal(<?php echo e($report->id); ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete assigned reports until resolved">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No resolved reports found</h4>
                    <p class="text-muted">Resolved reports will appear here once maintenance staff completes their work.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'archives'): ?>
    <!-- Archived Concerns Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-archive"></i> Archived Reports</h5>
                </div>
                <div class="card-body">
                    <?php if(isset($archivedConcerns) && $archivedConcerns->count() > 0): ?>
                        <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                            <table class="table table-hover" style="display: table !important;">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Reported By</th>
                                        <th>Created</th>
                                        <th>Resolved</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__currentLoopData = $archivedConcerns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $concern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr data-id="<?php echo e($concern->id); ?>">
                                            <td><?php echo e($concern->title ?? 'No Title'); ?></td>
                                            <td><?php echo e($concern->categoryRelation->name ?? 'N/A'); ?></td>
                                            <td><?php echo e($concern->location); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo e($concern->priority == 'urgent' ? 'danger' :
                                                    ($concern->priority == 'high' ? 'warning' :
                                                    ($concern->priority == 'medium' ? 'info' : 'secondary'))); ?>">
                                                    <?php echo e(ucfirst($concern->priority)); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo e($concern->status == 'Resolved' ? 'success' :
                                                    ($concern->status == 'In Progress' ? 'warning' :
                                                    ($concern->status == 'Assigned' ? 'primary' : 'secondary'))); ?>">
                                                    <?php echo e($concern->status); ?>

                                                </span>
                                            </td>
                                            <td>
                                                <?php echo e($concern->is_anonymous ? 'Anonymous' : ($concern->user->name ?? 'Unknown')); ?>

                                            </td>
                                            <td><?php echo e($concern->created_at->format('M d, Y')); ?></td>
                                            <td><?php echo e($concern->resolved_at ? $concern->resolved_at->format('M d, Y') : '-'); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-info" onclick="viewConcern(<?php echo e($concern->id); ?>)" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <?php if(in_array(auth()->user()->role, ['mis', 'school_admin', 'building_admin'])): ?>
                                                    <button type="button" class="btn btn-sm btn-warning" onclick="editConcern(<?php echo e($concern->id); ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <form method="POST" action="<?php echo e(route('admin.archive.restore')); ?>" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="type" value="report">
                                                        <input type="hidden" name="id" value="<?php echo e($concern->id); ?>">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                            <i class="fas fa-trash-restore"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-danger" onclick="showReportDeleteModal(<?php echo e($concern->id); ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-archive"></i> No archived reports found.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'deleted'): ?>
    <!-- Deleted Concerns Section -->
    <div id="deletedReportsContextMenu" class="context-menu" style="display: none;">
        <ul>
            <li><a href="#" onclick="deletedReportsContextView()"><i class="fas fa-eye"></i> View</a></li>
            <li><a href="#" onclick="deletedReportsContextRestore()"><i class="fas fa-trash-restore"></i> Restore</a></li>
            <li><a href="#" onclick="deletedReportsContextPermanentDelete()"><i class="fas fa-times-circle"></i> Permanently Delete</a></li>
        </ul>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-trash-alt"></i> Deleted Reports</h2>
            <p class="text-muted">Reports in this folder can be restored or permanently deleted.</p>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <!-- Auto-delete Settings -->
    <div class="card mb-4 border-info">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="mb-1"><i class="fas fa-clock"></i> Auto-filter Settings</h5>
                    <p class="mb-0 text-muted">Show reports that have been deleted for the selected period or less.</p>
                </div>
                <div class="col-md-4">
                    <select id="retentionDays" class="form-select">
                        <option value="3" <?php echo e(($days ?? 15) == 3 ? 'selected' : ''); ?>>3 days</option>
                        <option value="7" <?php echo e(($days ?? 15) == 7 ? 'selected' : ''); ?>>7 days</option>
                        <option value="15" <?php echo e(($days ?? 15) == 15 ? 'selected' : ''); ?>>15 days</option>
                        <option value="30" <?php echo e(($days ?? 15) == 30 ? 'selected' : ''); ?>>30 days</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card mb-4 border-warning">
        <div class="card-body bg-warning bg-opacity-10">
            <div class="row align-items-center">
                <div class="col-12">
                    <h5 class="mb-1"><i class="fas fa-info-circle"></i> About Deleted Reports</h5>
                    <p class="mb-0 text-muted">Reports that have been deleted are moved here. You can restore them to their original state or permanently delete them. Once permanently deleted, reports cannot be recovered.</p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge bg-warning fs-5"><?php echo e($deletedReports->count() ?? 0); ?> reports</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <?php if(($deletedReports->count() ?? 0) > 0): ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <form id="deletedReportsBulkRestoreForm" method="POST" action="<?php echo e(route('concerns.batchRestoreDeleted')); ?>">
                        <?php echo csrf_field(); ?>
                        <div id="selectedDeletedReportIdsContainer"></div>
                        <button type="button" class="btn btn-success" onclick="deletedReportsBulkRestore()">
                            <i class="fas fa-trash-restore"></i> Restore Selected
                        </button>
                    </form>
                </div>
                <div class="col-md-6 text-end">
                    
                    <span id="deletedReportsSelectedCount" class="text-muted ms-3">0 concerns selected</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Deleted Reports Table -->
    <div class="card">
        <div class="card-body">
            <?php if(isset($deletedReports) && $deletedReports->count() > 0): ?>
                <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <table class="table table-hover" style="display: table !important;" id="deletedReportsTable">
                        <thead>
                            <tr>
                                <th style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" id="deletedReportsSelectAll" onchange="deletedReportsToggleSelectAll()"></th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Reported By</th>
                                <th>Deleted Date</th>
                                <th>Deleted By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $deletedReports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr data-id="<?php echo e($report->id); ?>">
                                    <td style="width:1%;white-space:nowrap;text-align:center"><input type="checkbox" class="deleted-report-checkbox" value="<?php echo e($report->id); ?>" onchange="deletedReportsUpdateSelectedCount()"></td>
                                    <td>Report #<?php echo e($report->id); ?></td>
                                    <td><?php echo e($report->categoryRelation ? $report->categoryRelation->name : 'N/A'); ?></td>
                                    <td><?php echo e($report->location); ?></td>
                                    <td>
                                        <?php
                                            $severityClass = match($report->priority) {
                                                'low' => 'success',
                                                'medium' => 'warning',
                                                'high' => 'danger',
                                                'urgent' => 'dark',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?php echo e($severityClass); ?>">
                                            <?php echo e(ucfirst($report->priority ?? 'N/A')); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = match($report->status) {
                                                'pending' => 'warning',
                                                'in_progress' => 'info',
                                                'resolved' => 'success',
                                                'rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?php echo e($statusClass); ?>">
                                            <?php echo e(ucfirst($report->status)); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($report->user ? $report->user->name : 'Unknown'); ?></td>
                                    <td><?php echo e($report->updated_at->format('M d, Y h:i A')); ?></td>
                                    <td><?php echo e($report->user ? $report->user->name : 'System'); ?></td>
                                    <td>
                                        <div class="action-icons">
                                            <form action="<?php echo e(route('admin.deletedReports.restore', $report->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn btn-sm btn-success" title="Restore">
                                                    <i class="fas fa-trash-restore"></i>
                                                </button>
                                            </form>
                                            <form action="<?php echo e(route('admin.deletedReports.permanentDelete', $report->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="btn btn-sm btn-danger" title="Permanently Delete" onclick="return confirm('Are you sure you want to permanently delete this report?')">
                                                    <i class="fas fa-times-circle"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Permanent Delete Confirmation Modal -->
                                <div class="modal fade" id="deletedReportsPermanentDeleteModal<?php echo e($report->id); ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">Permanently Delete Concern</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Are you sure you want to permanently delete <strong>Report #<?php echo e($report->id); ?></strong>?</p>
                                                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. The concern will be permanently removed from the system.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="<?php echo e(route('admin.deletedReports.permanentDelete', $report->id)); ?>" method="POST" class="d-inline">
                                                    <?php echo csrf_field(); ?>
                                                    <?php echo method_field('DELETE'); ?>
                                                    <button type="submit" class="btn btn-danger">Permanently Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="11" class="text-center p-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-check-circle fa-2x d-block mb-3"></i>
                                            <h5>No Deleted Concerns</h5>
                                            <p class="mb-0">Deleted concerns will appear here. You can delete concerns from the Reports page.</p>
                                            <a href="<?php echo e(route('admin.reports')); ?>" class="btn btn-primary mt-3">
                                                <i class="fas fa-file-alt"></i> Go to Reports
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
                        <h5>No Deleted Concerns</h5>
                        <p class="mb-0 text-muted">Deleted concerns will appear here. You can delete concerns from the Reports page.</p>
                        <a href="<?php echo e(route('admin.reports')); ?>" class="btn btn-primary mt-3">
                            <i class="fas fa-file-alt"></i> Go to Reports
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if(($viewType ?? '') == 'analytics'): ?>
    <!-- Analytics Section -->
    <div class="container-fluid">
        <div class="analytics-header">
            <div class="analytics-title">
                <i class="fas fa-chart-line"></i> Analytics - Cost Tracking & Repair/Damage Analysis
            </div>
        </div>


        <!-- Summary Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo e($totalConcerns); ?></div>
                <div class="stat-label">Total Repairs/Damages</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value">₱<?php echo e(number_format($totalCost, 2)); ?></div>
                <div class="stat-label">
                    Total Cost
                    <a href="#" data-bs-toggle="modal" data-bs-target="#costModal" style="color: #fff; text-decoration: underline;">View Details</a>
                </div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value"><?php echo e($locationStats->count()); ?></div>
                <div class="stat-label">
                    Frequently Fixed Room
                    <a href="#" data-bs-toggle="modal" data-bs-target="#roomsModal" style="color: #fff; text-decoration: underline;">See Room</a>
                </div>
            </div>
            <div class="stat-card yellow">
                <div class="stat-value"><?php echo e($totalConcerns > 0 ? number_format($totalCost / $totalConcerns, 2) : 0); ?></div>
                <div class="stat-label">Average Cost per Repair</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="<?php echo e(route('admin.reports', ['view' => 'analytics'])); ?>" class="filter-form">
                <div class="filter-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="<?php echo e(request('date_from')); ?>">
                </div>
                <div class="filter-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="<?php echo e(request('date_to')); ?>">
                </div>
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="<?php echo e(route('admin.reports', ['view' => 'analytics'])); ?>" class="btn-reset">
                        <i class="fas fa-times"></i> Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- ── TREND ALERTS ─────────────────────────────────────────────── -->
        <?php if(isset($trendAlerts) && $trendAlerts->count() > 0): ?>
        <div class="analytics-card">
            <div class="analytics-header">
                <div class="analytics-title">
                    <i class="fas fa-bell text-danger"></i> Predictive Trend Alerts
                    <span class="badge bg-danger ms-2"><?php echo e($trendAlerts->count()); ?> Location(s) Flagged</span>
                </div>
            </div>
            <p class="text-muted mb-3" style="font-size:.88rem;">
                Locations where repair frequency <strong>increased</strong> in the last 3 months vs the prior 3 months.
            </p>
            <?php $__currentLoopData = $trendAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="trend-alert-item <?php echo e($alert['severity']); ?>">
                <div class="trend-alert-icon">
                    <?php if($alert['severity'] === 'critical'): ?> 🔴
                    <?php elseif($alert['severity'] === 'warning'): ?> 🟡
                    <?php else: ?> 🔵
                    <?php endif; ?>
                </div>
                <div class="trend-alert-text">
                    <strong><?php echo e($alert['location']); ?></strong>
                    <span>
                        <?php echo e($alert['recent']); ?> repair(s) in last 3 months
                        (vs <?php echo e($alert['prior']); ?> prior) &mdash;
                        Cost: ₱<?php echo e(number_format($alert['recent_cost'], 2)); ?>

                        <?php if($alert['severity'] === 'critical'): ?>
                            &mdash; <strong class="text-danger">Consider replacement</strong>
                        <?php elseif($alert['severity'] === 'warning'): ?>
                            &mdash; <strong class="text-warning">Monitor closely</strong>
                        <?php else: ?>
                            &mdash; Increasing trend detected
                        <?php endif; ?>
                    </span>
                </div>
                <span class="badge <?php echo e($alert['severity'] === 'critical' ? 'bg-danger' : ($alert['severity'] === 'warning' ? 'bg-warning text-dark' : 'bg-info')); ?>">
                    +<?php echo e($alert['recent'] - $alert['prior']); ?> more
                </span>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>

        <!-- ── PERIOD COMPARISON ─────────────────────────────────────────── -->
        <?php if(isset($periodComparison)): ?>
        <div class="analytics-card">
            <div class="analytics-header">
                <div class="analytics-title">
                    <i class="fas fa-exchange-alt"></i> Period Comparison
                </div>
            </div>
            <p class="text-muted mb-3" style="font-size:.88rem;">
                <?php echo e($periodComparison['this_month_label']); ?> vs <?php echo e($periodComparison['last_month_label']); ?>

            </p>
            <div class="period-grid">
                <div class="period-card <?php echo e($periodComparison['count_change'] > 0 ? 'up' : ($periodComparison['count_change'] < 0 ? 'down' : 'neutral')); ?>">
                    <div class="period-label">Repairs — <?php echo e($periodComparison['this_month_label']); ?></div>
                    <div class="period-value"><?php echo e($periodComparison['this_month_count']); ?></div>
                    <div class="period-sub">
                        Last month: <?php echo e($periodComparison['last_month_count']); ?>

                        <span class="chg-badge <?php echo e($periodComparison['count_change'] > 0 ? 'up' : ($periodComparison['count_change'] < 0 ? 'down' : 'neutral')); ?>">
                            <?php echo e($periodComparison['count_change'] > 0 ? '▲' : ($periodComparison['count_change'] < 0 ? '▼' : '—')); ?>

                            <?php echo e(abs($periodComparison['count_change'])); ?>%
                        </span>
                    </div>
                </div>
                <div class="period-card <?php echo e($periodComparison['cost_change'] > 0 ? 'up' : ($periodComparison['cost_change'] < 0 ? 'down' : 'neutral')); ?>">
                    <div class="period-label">Cost — <?php echo e($periodComparison['this_month_label']); ?></div>
                    <div class="period-value">₱<?php echo e(number_format($periodComparison['this_month_cost'], 2)); ?></div>
                    <div class="period-sub">
                        Last month: ₱<?php echo e(number_format($periodComparison['last_month_cost'], 2)); ?>

                        <span class="chg-badge <?php echo e($periodComparison['cost_change'] > 0 ? 'up' : ($periodComparison['cost_change'] < 0 ? 'down' : 'neutral')); ?>">
                            <?php echo e($periodComparison['cost_change'] > 0 ? '▲' : ($periodComparison['cost_change'] < 0 ? '▼' : '—')); ?>

                            <?php echo e(abs($periodComparison['cost_change'])); ?>%
                        </span>
                    </div>
                </div>
                <?php
                    $avgThis   = $periodComparison['this_month_count'] > 0 ? $periodComparison['this_month_cost'] / $periodComparison['this_month_count'] : 0;
                    $avgLast   = $periodComparison['last_month_count'] > 0 ? $periodComparison['last_month_cost'] / $periodComparison['last_month_count'] : 0;
                    $avgChange = $avgLast > 0 ? round((($avgThis - $avgLast) / $avgLast) * 100, 1) : ($avgThis > 0 ? 100 : 0);
                ?>
                <div class="period-card <?php echo e($avgChange > 0 ? 'up' : ($avgChange < 0 ? 'down' : 'neutral')); ?>">
                    <div class="period-label">Avg Cost / Repair</div>
                    <div class="period-value">₱<?php echo e(number_format($avgThis, 2)); ?></div>
                    <div class="period-sub">
                        Last month: ₱<?php echo e(number_format($avgLast, 2)); ?>

                        <span class="chg-badge <?php echo e($avgChange > 0 ? 'up' : ($avgChange < 0 ? 'down' : 'neutral')); ?>">
                            <?php echo e($avgChange > 0 ? '▲' : ($avgChange < 0 ? '▼' : '—')); ?>

                            <?php echo e(abs($avgChange)); ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Combined Cost by Location -->
        <div class="analytics-card">
            <div class="analytics-header">
                <div class="analytics-title">
                    <i class="fas fa-map-marker-alt"></i> Combined Cost by Location (All Tickets)
                </div>
            </div>
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Total Tickets</th>
                            <th>Total Cost</th>
                            <th>Avg Cost per Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $combinedLocationStats ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($stat['location']); ?></td>
                            <td><span class="count-badge"><?php echo e($stat['total_count']); ?></span></td>
                            <td><span class="cost-badge">₱<?php echo e(number_format($stat['total_cost'], 2)); ?></span></td>
                            <td>₱<?php echo e(number_format($stat['total_count'] > 0 ? $stat['total_cost'] / $stat['total_count'] : 0, 2)); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center">No data found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Repair/Damage Details -->
        <div class="analytics-card">
            <div class="analytics-header">
                <div class="analytics-title">
                    <i class="fas fa-list"></i> Reports Details
                </div>
            </div>
            
            <?php if($reports->count() > 0): ?>
            <div class="table-responsive">
                <table class="analytics-table">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Damage</th>
                            <th>Date and Time Fixed</th>
                            <th>Repair Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($report->location); ?></td>
                            <td><?php echo e($report->damaged_part ?? 'N/A'); ?></td>
                            <td><?php echo e($report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->format('M d, Y H:i') : 'Not Fixed'); ?></td>
                            <td><span class="cost-badge">₱<?php echo e(number_format($report->cost ?? 0, 2)); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert-info">
                <i class="fas fa-info-circle"></i> No reports with location and date fixed data found for the selected period.
            </div>
            <?php endif; ?>
        </div>

        <!-- Charts Section -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-pie"></i> Repairs by Location
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="locationPieChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-bar"></i> Cost by Location
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="locationBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-line"></i> Status Distribution
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="statusDoughnutChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="analytics-card">
                    <div class="analytics-header">
                        <div class="analytics-title">
                            <i class="fas fa-chart-area"></i> Monthly Trend
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Rooms Modal -->
<div class="modal fade" id="roomsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Frequently Fixed Rooms</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php $__currentLoopData = $locationStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="room-item" onclick="showRoomDetails('<?php echo e($stat['location']); ?>')" style="cursor: pointer; padding: 10px; border-bottom: 1px solid #eee;">
                    <strong><?php echo e($stat['location']); ?></strong> - <?php echo e($stat['count']); ?> repairs, Total Cost: ₱<?php echo e(number_format($stat['total_cost'], 2)); ?>

                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>

<!-- Room Details Modal -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Repairs for <span id="roomName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="roomDetailsBody">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Cost Modal -->
<div class="modal fade" id="costModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cost Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Total Repairs/Damages</h6>
                        <p class="h4 text-primary"><?php echo e($totalConcerns); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Total Cost</h6>
                        <p class="h4 text-success">₱<?php echo e(number_format($totalCost, 2)); ?></p>
                    </div>
                </div>
                <hr>
                <h6>Cost by Location</h6>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Repairs</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $locationStats->sortByDesc('total_cost'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($stat['location']); ?></td>
                                <td><?php echo e($stat['count']); ?></td>
                                <td>₱<?php echo e(number_format($stat['total_cost'], 2)); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
    <?php endif; ?>
</div>

<!-- Permanent Delete All Confirmation Modal for Deleted Concerns -->
<?php if(($viewType ?? '') == 'deleted' && isset($deletedReports)): ?>
<div class="modal fade" id="deletedReportsPermanentDeleteAllModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Permanently Delete All Concerns</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to permanently delete all <strong><?php echo e($deletedReports->count()); ?></strong> concerns?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone. All concerns will be permanently removed from the system.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="<?php echo e(route('admin.deletedReports.permanentDeleteAll')); ?>" method="POST" class="d-inline">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn btn-danger">Permanently Delete All</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<style>
.analytics-card {
    background: var(--card-bg, #fff);
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.analytics-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.analytics-title {
    font-size: 1.5rem;
    font-weight: bold;
    color: var(--text-color, #333);
}

/* Trend Alerts */
.trend-alert-item { display:flex; align-items:center; gap:12px; padding:12px 16px; border-radius:8px; margin-bottom:10px; }
.trend-alert-item.critical { background:#fde8ea; border-left:4px solid #dc3545; }
.trend-alert-item.warning  { background:#fff8e1; border-left:4px solid #ffc107; }
.trend-alert-item.info     { background:#e8f4fd; border-left:4px solid #17a2b8; }
.trend-alert-icon { font-size:1.3rem; }
.trend-alert-text { flex:1; }
.trend-alert-text strong { display:block; font-size:.95rem; }
.trend-alert-text span   { font-size:.82rem; color:#666; }

/* Period Comparison */
.period-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px; margin-bottom:16px; }
.period-card { background:#fff; border-radius:10px; padding:16px 18px; box-shadow:0 2px 8px rgba(0,0,0,.07); border-left:4px solid #667eea; }
.period-card.up      { border-left-color:#dc3545; }
.period-card.down    { border-left-color:#28a745; }
.period-card.neutral { border-left-color:#6c757d; }
.period-label { font-size:.78rem; color:#888; margin-bottom:4px; }
.period-value { font-size:1.5rem; font-weight:700; }
.period-sub   { font-size:.8rem; color:#555; margin-top:4px; }
.chg-badge { display:inline-block; padding:1px 7px; border-radius:10px; font-size:.76rem; font-weight:600; }
.chg-badge.up      { background:#fde8ea; color:#dc3545; }
.chg-badge.down    { background:#e6f9f0; color:#28a745; }
.chg-badge.neutral { background:#f0f0f0; color:#6c757d; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
}

.stat-card.green {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.stat-card.orange {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card.yellow {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.filter-section {
    background: var(--card-bg, #fff);
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.filter-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-color, #333);
}

.filter-group input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 0.9rem;
}

.btn-reset {
    padding: 8px 15px;
    background: #6c757d;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-size: 0.9rem;
}

.btn-reset:hover {
    background: #5a6268;
    color: white;
}

.analytics-table {
    width: 100%;
    border-collapse: collapse;
}

.analytics-table th,
.analytics-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.analytics-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
    text-align: center;
    white-space: nowrap;
}

.analytics-table tbody td {
    text-align: center;
}

.analytics-table tr:hover {
    background: #f8f9fa;
}

.cost-badge {
    background: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 15px;
    font-size: 0.85rem;
}
</style>

<style>
.dropdown-menu {
    z-index: 1050;
}
.dropdown-item {
    cursor: pointer;
}
.dropdown-item:hover {
    background-color: #f8f9fa;
}
.btn-group {
    position: relative;
}
</style>

<style>
.table .dropdown {
    position: static;
}
.table .dropdown-menu {
    position: absolute;
    z-index: 1000;
}
</style>

<style>
.context-menu {
    position: fixed;
    z-index: 1000;
    display: none;
    background: white;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-shadow: 2px 2px 10px rgba(0,0,0,0.2);
    min-width: 150px;
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
</style>

<script>
<?php if(isset($groupedReports)): ?>
window.groupedReports = <?php echo json_encode($groupedReports, 15, 512) ?>;
<?php else: ?>
window.groupedReports = {};
<?php endif; ?>
function showRoomDetails(location) {
    document.getElementById('roomName').textContent = location;
    var details = '';
    if (window.groupedReports[location]) {
        window.groupedReports[location].forEach(function(report) {
            var dateFixed = report.resolved_at ? new Date(report.resolved_at).toLocaleDateString('en-US', {month: 'short', day: '2-digit', year: 'numeric'}) + ' ' + new Date(report.resolved_at).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit'}) : 'Not Fixed';
            details += '<tr><td>' + report.location + '</td><td>' + (report.damaged_part || 'N/A') + '</td><td>' + dateFixed + '</td><td>₱' + (report.cost ? parseFloat(report.cost).toFixed(2) : '0.00') + '</td></tr>';
        });
    }
    document.getElementById('roomDetailsBody').innerHTML = '<table class="table table-striped"><thead><tr><th>Location</th><th>Damage</th><th>Date Fixed</th><th>Cost</th></tr></thead><tbody>' + details + '</tbody></table>';
    const modal = new bootstrap.Modal(document.getElementById('roomDetailsModal'));
    modal.show();
}
// Global variable for selected concern ID - defined outside DOMContentLoaded
window.selectedConcernId = null;

// Right-click handler
document.addEventListener('contextmenu', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        e.preventDefault();
        window.selectedConcernId = row.getAttribute('data-id');
        showContextMenu(e.pageX, e.pageY);
    }
});

// Long-press handler for mobile
let longPressTimer;
document.addEventListener('touchstart', function(e) {
    const row = e.target.closest('tr[data-id]');
    if (row) {
        longPressTimer = setTimeout(function() {
            window.selectedConcernId = row.getAttribute('data-id');
            const touch = e.touches[0];
            showContextMenu(touch.pageX, touch.pageY);
        }, 500);
    }
});

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    <?php if(($viewType ?? '') == 'analytics'): ?>
    window.chartLocations = <?php echo json_encode($chartLocations ?? []); ?>;
    window.chartCounts    = <?php echo json_encode($chartCounts ?? []); ?>;
    window.chartCosts     = <?php echo json_encode($chartCosts ?? []); ?>;
    window.chartStatuses  = <?php echo json_encode($chartStatuses ?? []); ?>;
    window.chartStatusCounts = <?php echo json_encode($chartStatusCounts ?? []); ?>;
    window.monthlyData    = <?php echo json_encode(
        isset($monthlyStats) ? $monthlyStats->map(fn($s) => ['month' => \Carbon\Carbon::parse($s->month)->format('M Y'), 'count' => $s->total_count, 'cost' => $s->total_cost])->values() : []
    ); ?>;
    initializeCharts();
    <?php endif; ?>
    // Pie Chart for Repairs by Location
    const locationPieCtx = document.getElementById('locationPieChart');
    if (locationPieCtx && window.chartLocations.length > 0) {
        new Chart(locationPieCtx, {
            type: 'pie',
            data: {
                labels: window.chartLocations,
                datasets: [{
                    data: window.chartCounts,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF',
                        '#4BC0C0', '#FF6384'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' repairs';
                            }
                        }
                    }
                }
            }
        });
    }

    // Bar Chart for Cost by Location
    const locationBarCtx = document.getElementById('locationBarChart');
    if (locationBarCtx && window.chartLocations.length > 0) {
        new Chart(locationBarCtx, {
            type: 'bar',
            data: {
                labels: window.chartLocations,
                datasets: [{
                    label: 'Total Cost (₱)',
                    data: window.chartCosts,
                    backgroundColor: '#36A2EB',
                    borderColor: '#36A2EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '₱' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Doughnut Chart for Status Distribution
    const statusDoughnutCtx = document.getElementById('statusDoughnutChart');
    if (statusDoughnutCtx && window.chartStatuses.length > 0) {
        new Chart(statusDoughnutCtx, {
            type: 'doughnut',
            data: {
                labels: window.chartStatuses,
                datasets: [{
                    data: window.chartStatusCounts,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' reports';
                            }
                        }
                    }
                }
            }
        });
    }

    // Line Chart for Monthly Trend
    const monthlyTrendCtx = document.getElementById('monthlyTrendChart');
    if (monthlyTrendCtx && window.monthlyData.length > 0) {
        const months = window.monthlyData.map(item => item.month);
        const counts = window.monthlyData.map(item => item.count);
        const costs = window.monthlyData.map(item => item.cost);

        new Chart(monthlyTrendCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Number of Repairs',
                    data: counts,
                    borderColor: '#36A2EB',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Total Cost (₱)',
                    data: costs,
                    borderColor: '#FF6384',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Repairs'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Total Cost (₱)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.datasetIndex === 0) {
                                    return 'Repairs: ' + context.parsed.y;
                                } else {
                                    return 'Cost: ₱' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    }
                }
            }
        });
    }

}

document.addEventListener('touchend', function() {
    clearTimeout(longPressTimer);
});

document.addEventListener('touchmove', function() {
    clearTimeout(longPressTimer);
});

function showContextMenu(x, y) {
    const menu = document.getElementById('contextMenu');
    menu.style.display = 'block';
    menu.style.left = x + 'px';
    menu.style.top = y + 'px';
    
    // Adjust if menu goes off screen
    const menuRect = menu.getBoundingClientRect();
    if (x + menuRect.width > window.innerWidth) {
        menu.style.left = (x - menuRect.width) + 'px';
    }
    if (y + menuRect.height > window.innerHeight) {
        menu.style.top = (y - menuRect.height) + 'px';
    }
}

// Hide context menu when clicking elsewhere
document.addEventListener('click', function() {
    const menu = document.getElementById('contextMenu');
    if (menu) {
        menu.style.display = 'none';
    }
});

// Context menu actions
function contextView() {
    if (window.selectedConcernId) {
        viewConcern(window.selectedConcernId);
    }
}

function contextEdit() {
    if (window.selectedConcernId) {
        editConcern(window.selectedConcernId);
    }
}

function contextArchive() {
    if (window.selectedConcernId) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/concerns/' + window.selectedConcernId + '/archive';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}


// Edit Report Modal
function editReport(id) {
    const modal = new bootstrap.Modal(document.getElementById('editConcernModal'));
    const contentDiv = document.getElementById('editConcernContent');
    const form = document.getElementById('editConcernForm');

    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/api/reports/' + id + '/edit-data', {
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }

        form.action = '/reports/' + id;

        contentDiv.innerHTML = '<div class="mb-3">' +
            '<label class="form-label">Title</label>' +
            '<input type="text" name="title" class="form-control" value="' + (data.report.title || '') + '" required>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Category</label>' +
            '<select name="category_id" class="form-control" required>' +
            '<?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>' +
            '<option value="<?php echo e($category->id); ?>" ' + (data.report.category_id == <?php echo e($category->id); ?> ? 'selected' : '') + '><?php echo e($category->name); ?></option>' +
            '<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>' +
            '</select>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Location</label>' +
            '<input type="text" name="location" class="form-control" value="' + (data.report.location || '') + '" required>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Severity</label>' +
            '<select name="severity" class="form-control" required>' +
            '<option value="low" ' + (data.report.severity == 'low' ? 'selected' : '') + '>Low</option>' +
            '<option value="medium" ' + (data.report.severity == 'medium' ? 'selected' : '') + '>Medium</option>' +
            '<option value="high" ' + (data.report.severity == 'high' ? 'selected' : '') + '>High</option>' +
            '<option value="critical" ' + (data.report.severity == 'critical' ? 'selected' : '') + '>Critical</option>' +
            '</select>' +
            '</div><div class="mb-3">' +
            '<label class="form-label">Description</label>' +
            '<textarea name="description" class="form-control" rows="4" required>' + (data.report.description || '') + '</textarea>' +
            '</div>';
    })
    .catch(error => {
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading report details</div>';
    });
}

// Edit Concern Modal
function editConcern(id) {
    const modal = new bootstrap.Modal(document.getElementById('editConcernModal'));
    const contentDiv = document.getElementById('editConcernContent');
    const form = document.getElementById('editConcernForm');
    
    contentDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    
    modal.show();
    
    fetch('/api/concerns/' + id + '/edit-data', {
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            contentDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }
        
        form.action = '/concerns/' + id;
        
        const concern = data.concern;
        const categories = data.categories || [];

        contentDiv.innerHTML = '<div class="mb-3">' +
            '<label class="form-label">Title</label>' +
            '<input type="text" name="title" class="form-control" value="' + (concern.title || '') + '">' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label">Category</label>' +
            '<select name="category_id" class="form-select" required>' +
                categories.map(cat => '<option value="' + cat.id + '" ' + (concern.category_id == cat.id ? 'selected' : '') + '>' + cat.name + '</option>').join('') +
            '</select>' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label">Location</label>' +
            '<input type="text" name="location" class="form-control" value="' + (concern.location || '') + '" required>' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label">Description</label>' +
            '<textarea name="description" class="form-control" rows="4" required>' + (concern.description || '') + '</textarea>' +
        '</div>' +
        '<div class="mb-3">' +
            '<label class="form-label fw-bold">Priority</label>' +
            '<select name="priority" class="form-select">' +
                '<option value="low" ' + (concern.priority === 'low' ? 'selected' : '') + '>Low - Minor issue, can wait</option>' +
                '<option value="medium" ' + (concern.priority === 'medium' ? 'selected' : '') + '>Medium - Needs attention</option>' +
                '<option value="high" ' + (concern.priority === 'high' ? 'selected' : '') + '>High - Affecting activities</option>' +
                '<option value="urgent" ' + (concern.priority === 'urgent' ? 'selected' : '') + '>Urgent - Emergency</option>' +
            '</select>' +
            '<small class="text-muted">Set the priority level for this concern.</small>' +
        '</div>';
    })
    .catch(error => {
        contentDiv.innerHTML = '<div class="alert alert-danger">Error loading concern details</div>';
    });
}

// Handle edit form submission
const editConcernForm = document.getElementById('editConcernForm');
if (editConcernForm) {
    editConcernForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const id = this.action.split('/').pop();
        
        const data = {};
        formData.forEach((value, key) => data[key] = value);
        
        fetch('/concerns/' + id, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Concern updated successfully!');
                location.reload();
            } else if (data.error) {
                alert(data.error);
            }
        })
        .catch(error => {
            alert('Error updating concern: ' + error.message);
        });
    });
}

// Function to archive directly from table button
function archiveConcern(id) {
    fetch('/concerns/' + id + '/archive', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Concern archived successfully!');
            location.reload();
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error archiving concern');
    });
}

// Global variables for modal actions
let currentActionType = null;
let currentActionId = null;

// Show modal based on action type
function showDeleteModal(type, id, name) {
    currentActionType = type;
    currentActionId = id;

    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));

    // Update modal content based on action type
    const modalTitle = document.getElementById('deleteModalLabel');
    const modalBody = document.querySelector('#deleteModal .modal-body');
    const modalHeader = document.querySelector('#deleteModal .modal-header');
    const closeButton = document.querySelector('#deleteModal .btn-close');

    const confirmButton = document.getElementById('confirmActionButton');

    if (type === 'archive') {
        modalTitle.innerHTML = '<i class="fas fa-archive"></i> Archive Concern';
        modalBody.innerHTML = `
            <p>Are you sure you want to archive <strong>${name}</strong>?</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You can restore it later from the Archives tab.
            </div>
        `;
        confirmButton.innerHTML = '<i class="fas fa-archive"></i> Archive';
        confirmButton.className = 'btn btn-secondary';
        modalHeader.classList.remove('bg-danger', 'text-white');
        modalHeader.classList.add('bg-secondary', 'text-white');
        closeButton.classList.remove('btn-close-white');
    } else {
        modalTitle.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Delete Concern';
        modalBody.innerHTML = `
            <p>Are you sure you want to delete <strong>${name}</strong>?</p>
            <div class="alert alert-warning">
                <i class="fas fa-info-circle"></i> This action will move the item to deleted. You can restore it later from the Deleted tab.
            </div>
        `;
        confirmButton.innerHTML = '<i class="fas fa-trash"></i> Delete';
        confirmButton.className = 'btn btn-danger';
        modalHeader.classList.add('bg-danger', 'text-white');
        closeButton.classList.add('btn-close-white');
    }
    
    modal.show();
}

// Confirm and execute the action
function confirmDelete() {
    if (currentActionType === 'archive') {
        // Archive action - submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/concerns/' + currentActionId + '/archive';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    } else if (currentActionType === 'delete') {
        // Delete action - submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/concerns/' + currentActionId + '/soft-delete';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }

    // Close modal
    const modalEl = document.getElementById('deleteModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) {
        modal.hide();
    }
}

// Deleted Reports JavaScript Functions
let currentDeletedReportId = null;

function deletedReportsToggleSelectAll() {
    const selectAll = document.getElementById('deletedReportsSelectAll');
    const checkboxes = document.querySelectorAll('.deleted-report-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    deletedReportsUpdateSelectedCount();
}

function deletedReportsUpdateSelectedCount() {
    const countEl = document.getElementById('deletedReportsSelectedCount');
    const reportIdsEl = document.getElementById('selectedDeletedReportIds');

    if (!countEl || !reportIdsEl) {
        return;
    }

    const checkboxes = document.querySelectorAll('.deleted-report-checkbox:checked');
    const count = checkboxes.length;
    countEl.textContent = count + ' concern' + (count !== 1 ? 's' : '') + ' selected';

    const selectedIds = Array.from(checkboxes).map(cb => cb.value);
    reportIdsEl.value = selectedIds.join(',');
}

function deletedReportsBulkRestore() {
    const checkboxes = document.querySelectorAll('.deleted-report-checkbox:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one concern to restore.');
        return;
    }

    if (confirm('Are you sure you want to restore ' + checkboxes.length + ' concern(s)?')) {
        // Clear existing inputs
        const container = document.getElementById('selectedDeletedReportIdsContainer');
        container.innerHTML = '';

        // Add hidden inputs for each selected ID
        checkboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = checkbox.value;
            container.appendChild(input);
        });

        document.getElementById('deletedReportsBulkRestoreForm').submit();
    }
}

function showDeletedReportsContextMenu(e, reportId) {
    e.preventDefault();
    currentDeletedReportId = reportId;
    
    const menu = document.getElementById('deletedReportsContextMenu');
    menu.style.display = 'block';
    menu.style.left = e.pageX + 'px';
    menu.style.top = e.pageY + 'px';
}

function hideDeletedReportsContextMenu() {
    const menu = document.getElementById('deletedReportsContextMenu');
    if (menu) {
        menu.style.display = 'none';
    }
}

function deletedReportsContextView() {
    hideDeletedReportsContextMenu();
    alert('View functionality for report ' + currentDeletedReportId);
}

function deletedReportsContextRestore() {
    hideDeletedReportsContextMenu();
    if (confirm('Are you sure you want to restore this concern?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/deleted-reports/' + currentDeletedReportId + '/restore';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '<?php echo e(csrf_token()); ?>';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function deletedReportsContextPermanentDelete() {
    hideDeletedReportsContextMenu();
    const modalId = 'deletedReportsPermanentDeleteModal' + currentDeletedReportId;
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();
}

// Add right-click listeners to deleted reports table rows
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('deletedReportsTable');
    if (table) {
        table.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('contextmenu', (e) => {
                const reportId = row.getAttribute('data-id');
                if (reportId) {
                    showDeletedReportsContextMenu(e, reportId);
                }
            });
        });
    }
    
    // Hide context menu on click outside
    document.addEventListener('click', (e) => {
        const menu = document.getElementById('deletedReportsContextMenu');
        if (menu && !menu.contains(e.target)) {
            hideDeletedReportsContextMenu();
        }
    });
    
    deletedReportsUpdateSelectedCount();
});

// Store current report IDs for modals
let currentReportId = null;

// Show Archive Modal
function showReportArchiveModal(reportId) {
    currentReportId = reportId;
    const modal = new bootstrap.Modal(document.getElementById('reportArchiveModal'));
    modal.show();
}

// Confirm Archive
function confirmReportArchive() {
    if (!currentReportId) return;

    fetch('/reports/' + currentReportId + '/archive', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportArchiveModal'));
            modal.hide();
            // Reload page to update the table
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to archive report'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while archiving the report');
    });
}

// Show Delete Modal
function showReportDeleteModal(reportId) {
    currentReportId = reportId;
    const modal = new bootstrap.Modal(document.getElementById('reportDeleteModal'));
    modal.show();
}

// Confirm Delete
function confirmReportDelete() {
    if (!currentReportId || isNaN(currentReportId)) {
        alert('Invalid report ID');
        return;
    }

    fetch('/reports/' + currentReportId, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP error! status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('reportDeleteModal'));
            modal.hide();
            // Reload page to update the table
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to delete report'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the report');
    });
}

// Store current concern ID for assignment
let currentConcernId = null;

// Show Assign Modal
function showAssignModal() {
    const concernId = window.currentConcernId;
    const reportId = window.currentReportId;

    if (!concernId && !reportId) {
        alert('No item selected');
        return;
    }

    const modal = new bootstrap.Modal(document.getElementById('assignConcernModal'));
    const select = document.getElementById('assigned_to');
    const form = document.getElementById('assignConcernForm');

    // Determine if it's a concern or report
    const isReport = !!reportId;
    const itemId = isReport ? reportId : concernId;
    const itemType = isReport ? 'report' : 'concern';

    // Set the ID
    document.getElementById('assignConcernId').value = itemId;

    // Set data attribute for type
    form.setAttribute('data-type', itemType);

    // Set the form action for non-JS fallback
    form.action = '/admin/' + itemType + '/' + itemId + '/assign';
    
    // Load maintenance staff list
    select.innerHTML = '<option value="">Loading...</option>';
    
    fetch('/admin/maintenance-users', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            select.innerHTML = '<option value="">Error loading users</option>';
            return;
        }
        
        select.innerHTML = '<option value="">Select maintenance staff</option>';
        data.users.forEach(user => {
            select.innerHTML += `<option value="${user.id}">${user.name}</option>`;
        });
    })
    .catch(error => {
        console.error('Error:', error);
        select.innerHTML = '<option value="">Error loading users</option>';
    });
    
    modal.show();
}

// Handle assign form submission
const assignForm = document.getElementById('assignConcernForm');
if (assignForm) {
    assignForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const itemId = document.getElementById('assignConcernId').value;
        const assignedTo = document.getElementById('assigned_to').value;
        const itemType = this.getAttribute('data-type') || 'concern';

        if (!assignedTo) {
            alert('Please select a maintenance staff');
            return;
        }

        const formData = new FormData();
        formData.append('assigned_to', assignedTo);
        formData.append('_token', '<?php echo e(csrf_token()); ?>');

        fetch('/admin/' + itemType + '/' + itemId + '/assign', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('assignConcernModal')).hide();
                alert(itemType.charAt(0).toUpperCase() + itemType.slice(1) + ' assigned successfully!');
                location.reload();
            } else {
                alert(data.error || 'Failed to assign ' + itemType);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error assigning ' + itemType);
        });
    });
}

// Date formatting function
function formatDate(dateString) {
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = months[date.getMonth()];
    const day = date.getDate();
    const year = date.getFullYear();
    let hours = date.getHours();
    const minutes = date.getMinutes().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    return `${month} ${day}, ${year} ${hours}:${minutes} ${ampm}`;
}

// View report function for Building Admin
function viewReport(id) {
    window.currentReportId = id; // Store the current report ID
    const modal = new bootstrap.Modal(document.getElementById('viewConcernModal'));
    const contentDiv = document.getElementById('viewConcernModalLabel');
    const bodyDiv = document.getElementById('viewConcernContent');

    contentDiv.textContent = 'Report Details';
    bodyDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>';

    modal.show();

    fetch('/api/reports/' + id, {
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            bodyDiv.innerHTML = '<div class="alert alert-danger">' + data.error + '</div>';
            return;
        }

        const report = data.report;
        const severityClass = report.severity === 'critical' ? 'danger' :
            (report.severity === 'high' ? 'warning' :
            (report.severity === 'medium' ? 'info' : 'secondary'));

        const statusClass = report.status === 'Resolved' ? 'success' :
            (report.status === 'In Progress' ? 'warning' :
            (report.status === 'Assigned' ? 'primary' : 'secondary'));

        let imageHtml = '';
        if (report.photo_path) {
            imageHtml = '<div class="mb-3"><p><strong>Photo:</strong></p><img src="' + report.photo_path + '" alt="Report photo" class="img-fluid rounded" style="max-width: 400px;"></div>';
        }

        bodyDiv.innerHTML = '<div class="card">' +
            '<div class="card-header d-flex justify-content-between align-items-center">' +
                '<h4>Report #' + report.id + '</h4>' +
                '<div><span class="badge bg-' + severityClass + ' me-2">' + report.severity.charAt(0).toUpperCase() + report.severity.slice(1) + ' Severity</span><span class="badge bg-' + statusClass + '">' + report.status + '</span></div>' +
            '</div>' +
            '<div class="card-body">' +
                '<h5 class="card-title">' + (report.title || 'No Title') + '</h5>' +
                '<div class="row mb-3">' +
                    '<div class="col-md-6"><p><strong>Category:</strong> ' + (report.category ? report.category.name : 'N/A') + '</p><p><strong>Location:</strong> ' + report.location + '</p></div>' +
                    '<div class="col-md-6"><p><strong>Reported by:</strong> ' + (report.user ? report.user.name : 'Unknown') + '</p><p><strong>Date:</strong> ' + report.created_at + '</p></div>' +
                '</div>' +
                (report.assigned_to ? '<div class="mb-3"><p><strong>Assigned to:</strong> ' + (report.assigned_user_name || 'Unknown') + '</p></div>' : '') +
                (report.damaged_part ? '<div class="mb-3"><p><strong>Damaged Part:</strong> ' + report.damaged_part + '</p></div>' : '') +
                '<div class="mb-3"><p><strong>Description:</strong></p><p>' + report.description + '</p></div>' +
                imageHtml +
                (report.resolution_notes ? '<div class="mb-3"><p><strong>Resolution Notes:</strong></p><p>' + report.resolution_notes + '</p></div>' : '') +
                ((report.cost || report.replaced_part) ? '<div class="mb-3"><p><strong>Maintenance Details:</strong></p><div class="row"><div class="col-md-6">' + (report.cost ? '<p><strong>Cost:</strong> ₱' + parseFloat(report.cost).toFixed(2) + '</p>' : '') + '</div><div class="col-md-6">' + (report.replaced_part ? '<p><strong>Replaced With:</strong> ' + report.replaced_part + '</p>' : '') + '</div></div></div>' : '') +
            '</div>' +
        '</div>';
    })
    .catch(error => {
        bodyDiv.innerHTML = '<div class="alert alert-danger">Error loading report details</div>';
    });
}

// Acknowledge concern function for maintenance
function acknowledgeConcern(concernId) {
    if (!concernId) {
        alert('No concern ID provided');
        return;
    }

    fetch('/concerns/' + concernId + '/acknowledge', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Concern acknowledged! You can now work on it.');
            bootstrap.Modal.getInstance(document.getElementById('viewConcernModal')).hide();
            location.reload();
        } else {
            alert(data.error || 'Failed to acknowledge concern');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error acknowledging concern');
    });
}

    // Handle retention days dropdown change (only if element exists)
    document.addEventListener('DOMContentLoaded', function() {
        const retentionDaysElement = document.getElementById('retentionDays');
        if (retentionDaysElement) {
            retentionDaysElement.addEventListener('change', function() {
                const days = this.value;
                if (confirm(`Set auto-filter to show reports deleted more than ${days} days ago?`)) {
                    // Show loading indicator
                    this.disabled = true;

                    // Make AJAX request to save preference
                    fetch('<?php echo e(route("saveAutoDeletePreference")); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ days: parseInt(days), module: 'reports' })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload the page to show filtered results
                            window.location.href = '<?php echo e(route("admin.reports", ["view" => "deleted"])); ?>&days=' + days;
                        } else {
                            alert('Error saving preference.');
                        }
                    })
                    .catch(error => {
                        alert('An error occurred while saving your preference.');
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

<script>
// Checkbox functions for bulk actions
function toggleAllReports(checkbox) {
    const checkboxes = document.querySelectorAll('.report-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function getSelectedReports() {
    const checkboxes = document.querySelectorAll('.report-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}
</script>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/reports.blade.php ENDPATH**/ ?>