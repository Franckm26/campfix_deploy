

<?php $__env->startSection('styles'); ?>
<link href="<?php echo e(asset('css/admin.css')); ?>" rel="stylesheet">
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page_title'); ?>
<h2><i class="fas fa-tools"></i> Management</h2>
<p>Maintenance staff & facility management</p>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-3">

    <!-- Tabs -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <ul class="nav nav-pills mb-0 flex-wrap">
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'staff' ? 'active' : ''); ?>"
                       href="<?php echo e(route('admin.management', ['tab' => 'staff'])); ?>">
                        <i class="fas fa-hard-hat"></i> Maintenance Staff
                        <span class="badge bg-secondary ms-1"><?php echo e($staff->count()); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'facilities' ? 'active' : ''); ?>"
                       href="<?php echo e(route('admin.management', ['tab' => 'facilities'])); ?>">
                        <i class="fas fa-building"></i> Facilities
                        <span class="badge bg-secondary ms-1"><?php echo e($facilities->count()); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($tab === 'categories' ? 'active' : ''); ?>"
                       href="<?php echo e(route('admin.management', ['tab' => 'categories'])); ?>">
                        <i class="fas fa-tags"></i> Categories
                        <span class="badge bg-secondary ms-1"><?php echo e($categories->count()); ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    
    <?php if($tab === 'staff'): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-hard-hat text-warning"></i> Maintenance Staff</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="fas fa-plus"></i> Add Staff
            </button>
        </div>
        <div class="card-body">
            <!-- Search -->
            <form method="GET" action="<?php echo e(route('admin.management')); ?>" class="row g-2 mb-3">
                <input type="hidden" name="tab" value="staff">
                <div class="col-md-4">
                    <input type="text" name="staff_search" class="form-control form-control-sm"
                           placeholder="Search name or email..." value="<?php echo e(request('staff_search')); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    <a href="<?php echo e(route('admin.management', ['tab' => 'staff'])); ?>" class="btn btn-secondary btn-sm ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>

            <?php if($staff->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $staff; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center"
                                         style="width:34px;height:34px;font-weight:700;font-size:14px;flex-shrink:0;">
                                        <?php echo e(strtoupper(substr($member->name, 0, 1))); ?>

                                    </div>
                                    <?php echo e($member->name); ?>

                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                            title="Edit" onclick="openEditStaffModal(<?php echo e($member->id); ?>, '<?php echo e(addslashes($member->name)); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?php echo e(route('admin.management.staff.destroy', $member->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0"
                                                title="Remove"
                                                data-confirm="Remove <?php echo e($member->name); ?> from maintenance staff?"
                                                data-confirm-title="Remove Staff"
                                                data-confirm-ok="Yes, Remove"
                                                data-confirm-color="#dc3545">
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
                <i class="fas fa-hard-hat fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No maintenance staff found</h5>
                <p class="text-muted">Add your first maintenance staff member.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="fas fa-plus"></i> Add Staff
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'facilities'): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-building text-info"></i> Facilities</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFacilityModal">
                <i class="fas fa-plus"></i> Add Facility
            </button>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="<?php echo e(route('admin.management')); ?>" class="row g-2 mb-3">
                <input type="hidden" name="tab" value="facilities">
                <div class="col-md-3">
                    <input type="text" name="facility_search" class="form-control form-control-sm"
                           placeholder="Search name or location..." value="<?php echo e(request('facility_search')); ?>">
                </div>
                <div class="col-md-2">
                    <select name="facility_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <?php $__currentLoopData = \App\Models\Facility::types(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($key); ?>" <?php echo e(request('facility_type') == $key ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="facility_status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="available" <?php echo e(request('facility_status') == 'available' ? 'selected' : ''); ?>>Available</option>
                        <option value="unavailable" <?php echo e(request('facility_status') == 'unavailable' ? 'selected' : ''); ?>>Unavailable</option>
                        <option value="under_maintenance" <?php echo e(request('facility_status') == 'under_maintenance' ? 'selected' : ''); ?>>Under Maintenance</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?php echo e(route('admin.management', ['tab' => 'facilities'])); ?>" class="btn btn-secondary btn-sm ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>

            <?php if($facilities->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $facilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $facility): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><strong><?php echo e($facility->name); ?></strong></td>
                            <td><span class="badge bg-info"><?php echo e($facility->type_label); ?></span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                            title="Edit"
                                            onclick="openEditFacilityModal(<?php echo e($facility->id); ?>, '<?php echo e(addslashes($facility->name)); ?>', '<?php echo e($facility->type); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?php echo e(route('admin.management.facilities.destroy', $facility->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0"
                                                title="Delete"
                                                data-confirm="Delete facility '<?php echo e($facility->name); ?>'?"
                                                data-confirm-title="Delete Facility"
                                                data-confirm-ok="Yes, Delete"
                                                data-confirm-color="#dc3545">
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
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No facilities found</h5>
                <p class="text-muted">Add rooms, courts, and other facilities.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacilityModal">
                    <i class="fas fa-plus"></i> Add Facility
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'categories'): ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-tags text-primary"></i> Categories</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            <?php if($categories->count() > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                         style="width:34px;height:34px;font-weight:700;font-size:14px;flex-shrink:0;">
                                        <?php echo e(strtoupper(substr($category->name, 0, 1))); ?>

                                    </div>
                                    <?php echo e($category->name); ?>

                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                            title="Edit" onclick="openEditCategoryModal(<?php echo e($category->id); ?>, '<?php echo e(addslashes($category->name)); ?>', '<?php echo e(addslashes(json_encode($category->issues ?? []))); ?>')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="<?php echo e(route('admin.management.categories.destroy', $category->id)); ?>" method="POST" class="d-inline">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0"
                                                title="Delete"
                                                onclick="return confirm('Delete category \'<?php echo e($category->name); ?>\'?')">
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
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No categories found</h5>
                <p class="text-muted">Add your first category.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

</div>




<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-hard-hat"></i> Add Maintenance Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.management.staff.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Staff</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Maintenance Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStaffForm" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" id="editStaffFirstName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" id="editStaffLastName" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="addFacilityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-building"></i> Add Facility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.management.facilities.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Facility Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Room 301, Main Court">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                <?php $__currentLoopData = \App\Models\Facility::types(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <input type="hidden" name="status" value="available">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Facility</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editFacilityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Facility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFacilityForm" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Facility Name *</label>
                            <input type="text" name="name" id="editFacilityName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Type *</label>
                            <select name="type" id="editFacilityType" class="form-select" required>
                                <?php $__currentLoopData = \App\Models\Facility::types(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        
                        <input type="hidden" name="status" id="editFacilityStatus" value="available">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tags"></i> Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('admin.management.categories.store')); ?>" method="POST" id="addCategoryForm">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Maintenance, Rooms, Technology/Internet" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issues</label>
                        <small class="text-muted d-block mb-2">Each issue will appear in the dropdown when this category is selected.</small>
                        <div id="addIssuesList">
                            
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addIssueRow('addIssuesList')">
                            <i class="fas fa-plus"></i> Add Issue
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" id="editCategoryName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issues</label>
                        <small class="text-muted d-block mb-2">Each issue will appear in the dropdown when this category is selected.</small>
                        <div id="editIssuesList">
                            
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addIssueRow('editIssuesList')">
                            <i class="fas fa-plus"></i> Add Issue
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script>
// ── Staff modal helpers ───────────────────────────────────────────────────────
function openEditStaffModal(id, fullName) {
    const nameParts = fullName.trim().split(' ');
    const lastName  = nameParts.pop();
    const firstName = nameParts.join(' ');

    document.getElementById('editStaffFirstName').value = firstName;
    document.getElementById('editStaffLastName').value  = lastName;
    document.getElementById('editStaffForm').action     = '/admin/management/staff/' + id;

    new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}

// ── Category modal helpers ────────────────────────────────────────────────────
function addIssueRow(listId, value) {
    const list = document.getElementById(listId);
    const row = document.createElement('div');
    row.className = 'd-flex align-items-center gap-2 mb-2 issue-row';
    row.innerHTML = `
        <input type="text" name="issues[]" class="form-control form-control-sm" value="${value || ''}" placeholder="e.g., Aircon">
        <button type="button" class="btn btn-sm btn-danger flex-shrink-0" onclick="this.closest('.issue-row').remove()" title="Remove">
            <i class="fas fa-trash"></i>
        </button>
    `;
    list.appendChild(row);
    row.querySelector('input').focus();
}

function openEditCategoryModal(id, name, issuesJson) {
    document.getElementById('editCategoryName').value  = name;
    document.getElementById('editCategoryForm').action = '/admin/management/categories/' + id;

    // Clear and repopulate issue rows
    const list = document.getElementById('editIssuesList');
    list.innerHTML = '';
    try {
        const issues = issuesJson ? JSON.parse(issuesJson) : [];
        issues.forEach(issue => addIssueRow('editIssuesList', issue));
    } catch(e) {}

    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

// Clear add modal issues when it opens
document.getElementById('addCategoryModal').addEventListener('show.bs.modal', function() {
    document.getElementById('addIssuesList').innerHTML = '';
});

// ── Facility modal helpers ────────────────────────────────────────────────────
function openEditFacilityModal(id, name, type) {
    document.getElementById('editFacilityName').value  = name;
    document.getElementById('editFacilityType').value  = type;
    document.getElementById('editFacilityForm').action = '/admin/management/facilities/' + id;

    new bootstrap.Modal(document.getElementById('editFacilityModal')).show();
}

// ── Quick status update ───────────────────────────────────────────────────────
function updateFacilityStatus(select) {
    const id     = select.dataset.id;
    const status = select.value;

    fetch('/admin/management/facilities/' + id + '/status', {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            swalToast('Status updated successfully', 'success');
        } else {
            swalAlert('Failed to update status', 'error');
        }
    })
    .catch(() => swalAlert('Error updating status', 'error'));
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Campfix\resources\views/admin/management.blade.php ENDPATH**/ ?>