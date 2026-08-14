@extends('layouts.app')

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    /* Mobile Responsive Styles */
    @media screen and (max-width: 768px) {
        /* Hide tables on mobile */
        .card-body .table-responsive {
            display: none !important;
        }
        
        /* Show mobile cards */
        .mobile-management-cards {
            display: block !important;
        }
        
        /* Management card styling */
        .management-card {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .management-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .management-card-name {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        
        .management-card-body {
            margin-bottom: 12px;
        }
        
        .management-card-field {
            display: flex;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .management-card-label {
            font-weight: 600;
            min-width: 100px;
            color: #666;
        }
        
        .management-card-value {
            color: #333;
            flex: 1;
            word-break: break-word;
        }
        
        .management-card-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .management-card-actions .btn {
            flex: 1;
            font-size: 12px;
        }
        
        /* Dark mode support */
        [data-bs-theme="dark"] .management-card {
            background: #2d3238;
            border-color: #495057;
        }
        
        [data-bs-theme="dark"] .management-card-header {
            border-bottom-color: #495057;
        }
        
        [data-bs-theme="dark"] .management-card-name,
        [data-bs-theme="dark"] .management-card-value {
            color: #e9ecef;
        }
        
        [data-bs-theme="dark"] .management-card-label {
            color: #adb5bd;
        }
        
        [data-bs-theme="dark"] .management-card-actions {
            border-top-color: #495057;
        }
    }
    
    /* Hide mobile cards on desktop */
    .mobile-management-cards {
        display: none;
    }
</style>
@endsection

@section('page_title')
<h2><i class="fas fa-tools"></i> Management</h2>
<p>Maintenance staff & facility management</p>
@endsection

@section('content')
<div class="container-fluid px-3">

    <!-- Tabs -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <ul class="nav nav-pills mb-0 flex-wrap">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'staff' ? 'active' : '' }}"
                       href="{{ route('admin.management', ['tab' => 'staff']) }}">
                        <i class="fas fa-hard-hat"></i> Maintenance Staff
                        <span class="badge bg-secondary ms-1">{{ $staff->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'facilities' ? 'active' : '' }}"
                       href="{{ route('admin.management', ['tab' => 'facilities']) }}">
                        <i class="fas fa-building"></i> Facilities
                        <span class="badge bg-secondary ms-1">{{ $facilities->total() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'categories' ? 'active' : '' }}"
                       href="{{ route('admin.management', ['tab' => 'categories']) }}">
                        <i class="fas fa-tags"></i> Categories
                        <span class="badge bg-secondary ms-1">{{ $categories->total() }}</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════
         MAINTENANCE STAFF TAB
    ══════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'staff')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-hard-hat text-warning"></i> Maintenance Staff</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                <i class="fas fa-plus"></i> Add Staff
            </button>
        </div>
        <div class="card-body">
            <!-- Search -->
            <form method="GET" action="{{ route('admin.management') }}" class="row g-2 mb-3">
                <input type="hidden" name="tab" value="staff">
                <div class="col-md-4">
                    <input type="text" name="staff_search" class="form-control form-control-sm"
                           placeholder="Search name or email..." value="{{ request('staff_search') }}" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    <a href="{{ route('admin.management', ['tab' => 'staff']) }}" class="btn btn-secondary btn-sm ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>

            @if($staff->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center"
                                         style="width:34px;height:34px;font-weight:700;font-size:14px;flex-shrink:0;">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    {{ $member->name }}
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                            title="Edit" onclick="openEditStaffModal({{ $member->id }}, '{{ addslashes($member->name) }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.management.staff.destroy', $member->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0"
                                                title="Remove"
                                                data-confirm="Remove {{ $member->name }} from maintenance staff?"
                                                data-confirm-title="Remove Staff"
                                                data-confirm-ok="Yes, Remove"
                                                data-confirm-color="#dc3545">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout for Staff -->
            <div class="mobile-management-cards">
                @foreach($staff as $member)
                    <div class="management-card">
                        <div class="management-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center"
                                     style="width:34px;height:34px;font-weight:700;font-size:14px;flex-shrink:0;">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <span class="management-card-name">{{ $member->name }}</span>
                            </div>
                        </div>
                        <div class="management-card-actions">
                            <button type="button" class="btn btn-sm btn-primary" onclick="openEditStaffModal({{ $member->id }}, '{{ addslashes($member->name) }}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('admin.management.staff.destroy', $member->id) }}" method="POST" style="flex: 1;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100"
                                        data-confirm="Remove {{ $member->name }} from maintenance staff?"
                                        data-confirm-title="Remove Staff"
                                        data-confirm-ok="Yes, Remove"
                                        data-confirm-color="#dc3545">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 d-flex justify-content-center">{{ $staff->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-hard-hat fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No maintenance staff found</h5>
                <p class="text-muted">Add your first maintenance staff member.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                    <i class="fas fa-plus"></i> Add Staff
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         FACILITIES TAB
    ══════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'facilities')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-building text-info"></i> Facilities</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addFacilityModal">
                <i class="fas fa-plus"></i> Add Facility
            </button>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" action="{{ route('admin.management') }}" class="row g-2 mb-3">
                <input type="hidden" name="tab" value="facilities">
                <div class="col-md-3">
                    <input type="text" name="facility_search" class="form-control form-control-sm"
                           placeholder="Search name or location..." value="{{ request('facility_search') }}" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
                </div>
                <div class="col-md-2">
                    <select name="facility_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(\App\Models\Facility::types() as $key => $label)
                            <option value="{{ $key }}" {{ request('facility_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.management', ['tab' => 'facilities']) }}" class="btn btn-secondary btn-sm ms-1">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>

            @if($facilities->count() > 0)
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
                        @foreach($facilities as $facility)
                        <tr>
                            <td><strong>{{ $facility->name }}</strong></td>
                            <td><span class="badge bg-info">{{ $facility->type_label }}</span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                            title="Edit"
                                            onclick="openEditFacilityModal({{ $facility->id }}, '{{ addslashes($facility->name) }}', '{{ $facility->type }}')">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.management.facilities.destroy', $facility->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0"
                                                title="Delete"
                                                data-confirm="Delete facility '{{ $facility->name }}'?"
                                                data-confirm-title="Delete Facility"
                                                data-confirm-ok="Yes, Delete"
                                                data-confirm-color="#dc3545">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout for Facilities -->
            <div class="mobile-management-cards">
                @foreach($facilities as $facility)
                    <div class="management-card">
                        <div class="management-card-header">
                            <span class="management-card-name">{{ $facility->name }}</span>
                            <span class="badge bg-info">{{ $facility->type_label }}</span>
                        </div>
                        <div class="management-card-actions">
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="openEditFacilityModal({{ $facility->id }}, '{{ addslashes($facility->name) }}', '{{ $facility->type }}')">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('admin.management.facilities.destroy', $facility->id) }}" method="POST" style="flex: 1;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100"
                                        data-confirm="Delete facility '{{ $facility->name }}'?"
                                        data-confirm-title="Delete Facility"
                                        data-confirm-ok="Yes, Delete"
                                        data-confirm-color="#dc3545">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 d-flex justify-content-center">{{ $facilities->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-building fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No facilities found</h5>
                <p class="text-muted">Add rooms, courts, and other facilities.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacilityModal">
                    <i class="fas fa-plus"></i> Add Facility
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         CATEGORIES TAB
    ══════════════════════════════════════════════════════════════════════ --}}
    @if($tab === 'categories')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-tags text-primary"></i> Categories</h5>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
        <div class="card-body">
            @if($categories->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                         style="width:34px;height:34px;font-weight:700;font-size:14px;flex-shrink:0;">
                                        {{ strtoupper(substr($category->name, 0, 1)) }}
                                    </div>
                                    {{ $category->name }}
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-primary bg-transparent border-0"
                                            title="Edit" onclick="openEditCategoryModal({{ $category->id }}, @js($category->name), @js($category->issues ?? []))">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.management.categories.destroy', $category->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger bg-transparent border-0"
                                                title="Delete"
                                                onclick="return confirm('Delete category \'{{ $category->name }}\'?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card Layout for Categories -->
            <div class="mobile-management-cards">
                @foreach($categories as $category)
                    <div class="management-card">
                        <div class="management-card-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                     style="width:34px;height:34px;font-weight:700;font-size:14px;flex-shrink:0;">
                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                </div>
                                <span class="management-card-name">{{ $category->name }}</span>
                            </div>
                        </div>
                        <div class="management-card-actions">
                            <button type="button" class="btn btn-sm btn-primary" onclick="openEditCategoryModal({{ $category->id }}, @js($category->name), @js($category->issues ?? []))">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="{{ route('admin.management.categories.destroy', $category->id) }}" method="POST" style="flex: 1;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100" onclick="return confirm('Delete category \'{{ $category->name }}\'?')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 d-flex justify-content-center">{{ $categories->links() }}</div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No categories found</h5>
                <p class="text-muted">Add your first category.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="fas fa-plus"></i> Add Category
                </button>
            </div>
            @endif
        </div>
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════════════════ --}}

{{-- Add Staff Modal --}}
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-hard-hat"></i> Add Maintenance Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.management.staff.store') }}" method="POST">
                @csrf
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

{{-- Edit Staff Modal --}}
<div class="modal fade" id="editStaffModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Maintenance Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editStaffForm" method="POST">
                @csrf @method('PUT')
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

{{-- Add Facility Modal --}}
<div class="modal fade" id="addFacilityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-building"></i> Add Facility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.management.facilities.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Facility Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Room 301, Main Court">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-select" required>
                                @foreach(\App\Models\Facility::types() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- hidden defaults --}}
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

{{-- Edit Facility Modal --}}
<div class="modal fade" id="editFacilityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Facility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFacilityForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Facility Name *</label>
                            <input type="text" name="name" id="editFacilityName" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Type *</label>
                            <select name="type" id="editFacilityType" class="form-select" required>
                                @foreach(\App\Models\Facility::types() as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- keep status so validation passes --}}
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

{{-- Add Category Modal --}}
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tags"></i> Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.management.categories.store') }}" method="POST" id="addCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., Maintenance, Rooms, Technology/Internet" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issues</label>
                        <small class="text-muted d-block mb-2">Each issue will appear in the dropdown when this category is selected. Use the list button to add problem types for that issue.</small>
                        <div id="addIssuesList">
                            {{-- rows injected by JS --}}
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

{{-- Edit Category Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" id="editCategoryName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Issues</label>
                        <small class="text-muted d-block mb-2">Each issue will appear in the dropdown when this category is selected. Use the list button to add problem types for that issue.</small>
                        <div id="editIssuesList">
                            {{-- rows injected by JS --}}
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

@endsection
@section('scripts')
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
let issueRowCounter = 0;

function normalizeIssueEntry(issue) {
    if (typeof issue === 'string') {
        return { name: issue, problem_types: [] };
    }

    return {
        name: issue?.name || '',
        problem_types: Array.isArray(issue?.problem_types) ? issue.problem_types : [],
    };
}

function addIssueRow(listId, value) {
    const list = document.getElementById(listId);
    const issue = normalizeIssueEntry(value);
    const index = issueRowCounter++;
    const row = document.createElement('div');
    row.className = 'mb-2 issue-row';
    row.dataset.issueIndex = index;

    const mainRow = document.createElement('div');
    mainRow.className = 'd-flex align-items-center gap-2';

    const input = document.createElement('input');
    input.type = 'text';
    input.name = `issues[${index}][name]`;
    input.className = 'form-control form-control-sm';
    input.value = issue.name;
    input.placeholder = 'e.g., Aircon';

    const problemButton = document.createElement('button');
    problemButton.type = 'button';
    problemButton.className = 'btn btn-sm btn-outline-primary flex-shrink-0';
    problemButton.title = 'Add problem types';
    problemButton.innerHTML = '<i class="fas fa-list-ul"></i>';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-danger flex-shrink-0';
    button.title = 'Remove';
    button.innerHTML = '<i class="fas fa-trash"></i>';
    button.addEventListener('click', function() {
        row.remove();
    });

    const problemPanel = document.createElement('div');
    problemPanel.className = 'mt-2 ms-4 p-2 border rounded bg-light';
    problemPanel.style.display = 'none';
    problemPanel.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <small class="fw-semibold text-muted">Problem Types</small>
            <button type="button" class="btn btn-outline-primary btn-sm py-0 add-problem-type-btn">
                <i class="fas fa-plus"></i> Add
            </button>
        </div>
        <div class="problem-type-list"></div>
    `;

    problemButton.addEventListener('click', function() {
        problemPanel.style.display = problemPanel.style.display === 'none' ? 'block' : 'none';
    });

    problemPanel.querySelector('.add-problem-type-btn').addEventListener('click', function() {
        addProblemTypeRow(problemPanel.querySelector('.problem-type-list'), index);
    });

    mainRow.append(input, problemButton, button);
    row.append(mainRow, problemPanel);
    list.appendChild(row);

    issue.problem_types.forEach(problemType => addProblemTypeRow(problemPanel.querySelector('.problem-type-list'), index, problemType));

    input.focus();
}

function addProblemTypeRow(list, issueIndex, value = '') {
    const row = document.createElement('div');
    row.className = 'd-flex align-items-center gap-2 mb-2';

    const input = document.createElement('input');
    input.type = 'text';
    input.name = `issues[${issueIndex}][problem_types][]`;
    input.className = 'form-control form-control-sm';
    input.value = value;
    input.placeholder = 'e.g., Not working';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-sm btn-danger flex-shrink-0';
    button.title = 'Remove problem type';
    button.innerHTML = '<i class="fas fa-trash"></i>';
    button.addEventListener('click', function() {
        row.remove();
    });

    row.append(input, button);
    list.appendChild(row);
    input.focus();
}

function openEditCategoryModal(id, name, issues) {
    document.getElementById('editCategoryName').value  = name;
    document.getElementById('editCategoryForm').action = '/admin/management/categories/' + id;

    // Clear and repopulate issue rows
    const list = document.getElementById('editIssuesList');
    list.innerHTML = '';
    (Array.isArray(issues) ? issues : []).forEach(issue => addIssueRow('editIssuesList', issue));

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
@endsection
