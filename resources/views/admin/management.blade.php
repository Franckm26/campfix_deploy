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
                    <a class="nav-link {{ $tab === 'events' ? 'active' : '' }}"
                       href="{{ route('admin.management', ['tab' => 'events']) }}">
                        <i class="fas fa-calendar-check"></i> Event Requests
                        <span class="badge bg-secondary ms-1">{{ $events->total() }}</span>
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

    @if($tab === 'events')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-sliders text-primary"></i> Event Setup</h5>
            <a class="btn btn-primary btn-sm" href="{{ route('admin.events') }}"><i class="fas fa-arrow-up-right-from-square"></i> Review requests</a>
        </div>
        <div class="card-body">
            <p class="text-muted">Configure the event request form and the role-based approval route. Approval roles are followed in the selected order.</p>
            @if(! $eventSetupReady)
                <div class="alert alert-warning mb-4"><i class="fas fa-database"></i> Event Setup will be available after the latest database migration is applied. The existing event-request workflow remains available.</div>
            @else
            <style>
                .event-setup-grid .border { border-color: #dbe4f0 !important; border-radius: 12px !important; }
                .event-setup-grid h6 { font-size: 1.05rem; font-weight: 700; margin-bottom: 1rem; }
                .event-setup-grid .approval-role-option { border: 1px solid #dbe4f0; border-radius: 8px; padding: .7rem .85rem; cursor: pointer; }
                .event-setup-grid .approval-role-option:hover { background: #f5f9ff; border-color: #0d6efd; }
                .event-setup-grid .approval-role-option:has(input:checked) { background: #e8f1ff; border-color: #0d6efd; }
                .event-setup-grid .approval-step { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #e8f1ff; color: #0d6efd; font-weight: 700; font-size: .8rem; margin-right: .4rem; }
                .event-setup-grid .approval-flow-preview { min-height: 42px; background: #f5f9ff; border: 1px dashed #aac8f5; border-radius: 8px; padding: .65rem .8rem; }
                .event-setup-grid .btn-link { text-decoration: none; }
                .event-setup-grid .border-top { padding: .8rem 0; }
                .event-setup-grid > .col-lg-3 { width: 50%; }
                .event-setup-grid input[name="code"] { display: none; }
                form:has(input[name="event_search"]) { display: none !important; }
                @media (max-width: 991.98px) { .event-setup-grid > .col-lg-3 { width: 100%; } }
            </style>
            <div class="row g-4 mb-0 event-setup-grid">
                <div class="col-lg-12"><div class="border rounded p-4 h-100"><div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><div><h6 class="mb-1"><i class="fas fa-code-branch text-primary me-2"></i>Request types and approval roles</h6><small class="text-muted">Configure the approval route used for each event request type.</small></div><button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#eventRequestTypeModal"><i class="fas fa-plus"></i> Add request type</button></div>
                    <div class="alert alert-light border small mb-3"><strong>How it works:</strong> Add a request type, then select the roles that approve it. They approve in the order shown. Selecting <strong>Program Head</strong> automatically requires a department.</div>
                    @foreach($eventRequestTypes as $item)<div class="border-top pt-2 mt-2"><strong>{{ $item->name }}</strong> <span class="badge bg-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span><br><small class="text-muted">{{ $item->requires_department ? 'Department required · ' : '' }}{{ collect($item->approval_roles)->map(fn($role) => $approvalRoles[$role] ?? $role)->implode(' → ') }}</small><form class="d-inline" method="POST" action="{{ route('admin.management.event-setup.toggle', ['type' => 'request-type', 'id' => $item->id]) }}">@csrf @method('PATCH') <button class="btn btn-link btn-sm p-0">{{ $item->is_active ? 'Deactivate' : 'Activate' }}</button></form></div>@endforeach
                </div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h6 class="mb-0"><i class="fas fa-users text-primary"></i> Intended users</h6><button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#eventIntendedUserModal"><i class="fas fa-plus"></i> Add</button></div><p class="small text-muted">Set a custom approval route for a group, or leave it blank to use the request type's default route.</p>@forelse($eventIntendedUsers as $item)<div class="border-top pt-2 mt-2"><strong>{{ $item->name }}</strong><small class="d-block text-muted mt-1">{{ collect($item->approval_roles ?: [])->map(fn($role) => $role === 'principal_assistant' ? 'SHS Principal' : ($approvalRoles[$role] ?? $role))->implode(' → ') ?: 'Uses request type approval' }}</small><form class="d-inline" method="POST" action="{{ route('admin.management.event-setup.toggle', ['type' => 'intended-user', 'id' => $item->id]) }}">@csrf @method('PATCH') <button class="btn btn-link btn-sm p-0">Delete</button></form></div>@empty<div class="text-muted small py-2">No intended users configured.</div>@endforelse</div></div>
                <div class="col-lg-3"><div class="border rounded p-3 h-100"><div class="d-flex justify-content-between align-items-center gap-2 mb-3"><h6 class="mb-0"><i class="fas fa-building text-primary"></i> Departments</h6><button class="btn btn-sm btn-primary" type="button" data-department-add><i class="fas fa-plus"></i> Add</button></div><p class="small text-muted">Manage the departments available on the event request form.</p>@forelse($eventDepartments as $item)<div class="border-top pt-2 mt-2 d-flex justify-content-between align-items-center gap-2"><strong>{{ $item->name }}</strong><span class="text-nowrap"><button type="button" class="btn btn-sm btn-outline-primary" data-department-edit data-id="{{ $item->id }}"><i class="fas fa-edit"></i> Edit</button><button type="button" class="btn btn-sm btn-outline-danger" data-department-delete data-id="{{ $item->id }}"><i class="fas fa-trash"></i> Delete</button></span></div>@empty<div class="text-muted small py-2">No departments configured.</div>@endforelse</div></div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var approvalText = document.querySelector('.approval-flow-text');
                    function updateApprovalFlow() {
                        if (!approvalText) return;
                        var selected = Array.from(document.querySelectorAll('.approval-role-checkbox:checked')).map(function (checkbox) { return checkbox.dataset.label; });
                        approvalText.textContent = selected.length ? selected.join(' → ') : 'Select one or more approvers above.';
                        approvalText.classList.toggle('text-muted', !selected.length);
                        approvalText.classList.toggle('text-primary', selected.length > 0);
                    }
                    document.querySelectorAll('.approval-role-checkbox').forEach(function (checkbox) { checkbox.addEventListener('change', updateApprovalFlow); });
                    updateApprovalFlow();
                    document.querySelectorAll('.event-setup-grid form[action*="/event-setup/"]').forEach(function (form) {
                        var match = form.action.match(/event-setup\/([^/]+)\/(\d+)\/toggle/);
                        if (!match) return;
                        if (match[1] === 'request-type' || match[1] === 'intended-user') return;
                        var deleteButton = form.querySelector('button');
                        if (deleteButton) {
                            deleteButton.textContent = 'Delete';
                            deleteButton.classList.remove('btn-link');
                            deleteButton.classList.add('btn-outline-danger');
                        }
                        form.addEventListener('submit', function (event) {
                            if (!window.confirm('Delete this item? Existing event requests will keep their saved information.')) event.preventDefault();
                        });
                        var editButton = document.createElement('button');
                        editButton.type = 'button';
                        editButton.className = 'btn btn-sm btn-outline-primary me-2';
                        editButton.textContent = 'Edit';
                        editButton.addEventListener('click', function () {
                            var currentName = form.parentElement.firstChild.textContent.trim();
                            var name = window.prompt('Edit name', currentName);
                            if (!name || !name.trim()) return;
                            var payload = new URLSearchParams();
                            payload.append('_token', form.querySelector('input[name="_token"]').value);
                            payload.append('_method', 'PATCH');
                            payload.append('name', name.trim());
                            fetch(form.action.replace('/toggle', ''), { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'text/html' }, body: payload.toString() }).then(function () { window.location.reload(); });
                        });
                        form.insertAdjacentElement('beforebegin', editButton);
                    });
                });
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var modalElement = document.getElementById('eventRequestTypeModal');
                    var form = document.getElementById('eventRequestTypeForm');
                    if (!modalElement || !form) return;

                    var flow = document.getElementById('eventRequestTypeFlow');
                    var departmentNotice = document.getElementById('eventRequestTypeDepartmentNotice');
                    var types = @json($eventRequestTypes->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'roles' => $item->approval_roles])->values());
                    var storeUrl = @json(route('admin.management.event-types.store'));
                    var updateBaseUrl = @json(url('/admin/management/event-setup/request-types'));

                    function updateFlow() {
                        var checked = Array.from(form.querySelectorAll('.event-request-type-role:checked'));
                        flow.textContent = checked.length ? checked.map(function (item) { return item.dataset.label; }).join(' → ') : 'Select one or more approvers.';
                        departmentNotice.classList.toggle('d-none', !checked.some(function (item) { return item.value === 'program_head'; }));
                    }

                    function openTypeModal(item) {
                        form.reset();
                        var editing = Boolean(item);
                        document.getElementById('eventRequestTypeModalLabel').textContent = editing ? 'Edit request type' : 'Add request type';
                        document.getElementById('eventRequestTypeSaveLabel').textContent = editing ? 'Save changes' : 'Add request type';
                        form.action = editing ? updateBaseUrl + '/' + item.id : storeUrl;
                        document.getElementById('eventRequestTypeMethod').value = editing ? 'PUT' : '';
                        if (editing) {
                            form.querySelector('[name="name"]').value = item.name;
                            form.querySelectorAll('.event-request-type-role').forEach(function (checkbox) { checkbox.checked = (item.roles || []).includes(checkbox.value); });
                        }
                        updateFlow();
                        bootstrap.Modal.getOrCreateInstance(modalElement).show();
                    }

                    document.querySelectorAll('.event-request-type-role').forEach(function (checkbox) { checkbox.addEventListener('change', updateFlow); });
                    modalElement.addEventListener('show.bs.modal', function (event) { if (event.relatedTarget) { form.reset(); document.getElementById('eventRequestTypeModalLabel').textContent = 'Add request type'; document.getElementById('eventRequestTypeSaveLabel').textContent = 'Add request type'; form.action = storeUrl; document.getElementById('eventRequestTypeMethod').value = ''; updateFlow(); } });
                    document.querySelectorAll('.event-setup-grid form[action*="event-setup/request-type/"]').forEach(function (deleteForm) {
                        var id = deleteForm.action.match(/request-type\/(\d+)/)[1];
                        var deleteButton = deleteForm.querySelector('button');
                        deleteButton.textContent = 'Delete';
                        deleteButton.classList.remove('btn-link');
                        deleteButton.classList.add('btn-outline-danger');
                        deleteForm.addEventListener('submit', function (event) { if (!window.confirm('Delete this request type? Existing event requests will keep their saved information.')) event.preventDefault(); });
                        var edit = document.createElement('button');
                        edit.type = 'button'; edit.className = 'btn btn-sm btn-outline-primary me-2'; edit.innerHTML = '<i class="fas fa-edit"></i> Edit';
                        edit.addEventListener('click', function () { var item = types.find(function (type) { return String(type.id) === id; }); if (item) openTypeModal(item); });
                        deleteForm.insertAdjacentElement('beforebegin', edit);
                    });

                    var intendedModal = document.getElementById('eventIntendedUserModal');
                    var intendedForm = document.getElementById('eventIntendedUserForm');
                    if (!intendedModal || !intendedForm) return;
                    var intendedFlow = document.getElementById('eventIntendedUserFlow');
                    var intendedUsers = @json($eventIntendedUsers->map(fn ($item) => ['id' => $item->id, 'name' => $item->name, 'code' => $item->code, 'roles' => $item->approval_roles])->values());
                    var intendedStoreUrl = @json(route('admin.management.event-intended-users.store'));
                    var intendedUpdateBaseUrl = @json(url('/admin/management/event-setup/intended-users'));

                    function updateIntendedFlow() {
                        var roles = Array.from(intendedForm.querySelectorAll('.event-intended-user-role:checked')).map(function (item) { return item.dataset.label; });
                        intendedFlow.textContent = roles.length ? roles.join(' → ') : 'Uses the selected request type approval route.';
                    }

                    function openIntendedUserModal(item) {
                        intendedForm.reset();
                        var editing = Boolean(item);
                        var shsPrincipalOption = intendedForm.querySelector('[data-shs-principal-option]');
                        shsPrincipalOption.classList.toggle('d-none', !editing || item.code !== 'shs');
                        document.getElementById('eventIntendedUserModalLabel').textContent = editing ? 'Edit intended user' : 'Add intended user';
                        document.getElementById('eventIntendedUserSaveLabel').textContent = editing ? 'Save changes' : 'Add intended user';
                        intendedForm.action = editing ? intendedUpdateBaseUrl + '/' + item.id : intendedStoreUrl;
                        document.getElementById('eventIntendedUserMethod').value = editing ? 'PUT' : '';
                        if (editing) {
                            intendedForm.querySelector('[name="name"]').value = item.name;
                            intendedForm.querySelectorAll('.event-intended-user-role').forEach(function (checkbox) { checkbox.checked = (item.roles || []).includes(checkbox.value); });
                        }
                        updateIntendedFlow();
                        bootstrap.Modal.getOrCreateInstance(intendedModal).show();
                    }

                    document.querySelectorAll('.event-intended-user-role').forEach(function (checkbox) { checkbox.addEventListener('change', updateIntendedFlow); });
                    intendedModal.addEventListener('show.bs.modal', function (event) { if (event.relatedTarget) { intendedForm.reset(); intendedForm.querySelector('[data-shs-principal-option]').classList.add('d-none'); document.getElementById('eventIntendedUserModalLabel').textContent = 'Add intended user'; document.getElementById('eventIntendedUserSaveLabel').textContent = 'Add intended user'; intendedForm.action = intendedStoreUrl; document.getElementById('eventIntendedUserMethod').value = ''; updateIntendedFlow(); } });
                    document.querySelectorAll('.event-setup-grid form[action*="event-setup/intended-user/"]').forEach(function (deleteForm) {
                        var id = deleteForm.action.match(/intended-user\/(\d+)/)[1];
                        var deleteButton = deleteForm.querySelector('button');
                        deleteButton.textContent = 'Delete'; deleteButton.classList.remove('btn-link'); deleteButton.classList.add('btn-outline-danger');
                        deleteForm.addEventListener('submit', function (event) { if (!window.confirm('Delete this intended user? Existing event requests will keep their saved information.')) event.preventDefault(); });
                        var edit = document.createElement('button');
                        edit.type = 'button'; edit.className = 'btn btn-sm btn-outline-primary me-2'; edit.innerHTML = '<i class="fas fa-edit"></i> Edit';
                        edit.addEventListener('click', function () { var item = intendedUsers.find(function (user) { return String(user.id) === id; }); if (item) openIntendedUserModal(item); });
                        deleteForm.insertAdjacentElement('beforebegin', edit);
                    });

                    var departmentModal = document.getElementById('eventDepartmentModal');
                    var departmentForm = document.getElementById('eventDepartmentForm');
                    var departmentName = document.getElementById('eventDepartmentName');
                    var departments = @json($eventDepartments->map(fn ($item) => ['id' => $item->id, 'name' => $item->name])->values());
                    var departmentStoreUrl = @json(route('admin.management.event-departments.store'));
                    var departmentRenameBaseUrl = @json(url('/admin/management/event-setup/department'));
                    var departmentDeleteBaseUrl = @json(url('/admin/management/event-setup/department'));
                    function openDepartmentModal(item) {
                        var editing = Boolean(item);
                        departmentForm.action = editing ? departmentRenameBaseUrl + '/' + item.id : departmentStoreUrl;
                        document.getElementById('eventDepartmentMethod').value = editing ? 'PATCH' : '';
                        document.getElementById('eventDepartmentModalLabel').textContent = editing ? 'Edit department' : 'Add department';
                        document.getElementById('eventDepartmentSaveLabel').textContent = editing ? 'Save changes' : 'Add department';
                        departmentName.value = editing ? item.name : '';
                        bootstrap.Modal.getOrCreateInstance(departmentModal).show();
                    }
                    document.querySelector('[data-department-add]').addEventListener('click', function () { openDepartmentModal(null); });
                    document.querySelectorAll('[data-department-edit]').forEach(function (button) { button.addEventListener('click', function () { openDepartmentModal(departments.find(function (item) { return String(item.id) === button.dataset.id; })); }); });
                    document.querySelectorAll('[data-department-delete]').forEach(function (button) { button.addEventListener('click', function () { var item = departments.find(function (department) { return String(department.id) === button.dataset.id; }); if (!item) return; document.getElementById('eventDepartmentDeleteName').textContent = item.name; document.getElementById('eventDepartmentDeleteForm').action = departmentDeleteBaseUrl + '/' + item.id; bootstrap.Modal.getOrCreateInstance(document.getElementById('eventDepartmentDeleteModal')).show(); }); });
                });
            </script>
            @endif
            <form method="GET" action="{{ route('admin.management') }}" class="row g-2 mb-3">
                <input type="hidden" name="tab" value="events">
                <div class="col-md-5"><input class="form-control form-control-sm" name="event_search" value="{{ request('event_search') }}" placeholder="Search requester, department, or location"></div>
                <div class="col-auto"><button class="btn btn-primary btn-sm" type="submit">Search</button></div>
            </form>
            @if(false && $events->isNotEmpty())
            <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Requester</th><th>Department</th><th>Location</th><th>Date</th><th>Time</th><th>Status</th><th></th></tr></thead><tbody>
                @foreach($events as $event)
                <tr><td>{{ $event->user->name ?? 'Unknown' }}</td><td>{{ $event->department ?: '—' }}</td><td>{{ $event->location }}</td><td>{{ optional($event->event_date)->format('m/d/Y') }}</td><td>{{ \Carbon\Carbon::parse($event->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}</td><td><span class="badge bg-{{ $event->status === 'Approved' ? 'success' : ($event->status === 'Rejected' ? 'danger' : ($event->status === 'Cancelled' ? 'secondary' : 'warning')) }}">{{ $event->status }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.events') }}"><i class="fas fa-pen-to-square"></i> Manage</a></td></tr>
                @endforeach
            </tbody></table></div>
            <div class="mt-3 d-flex justify-content-center">{{ $events->links() }}</div>
            @else
            <div class="text-center py-5 text-muted"><i class="fas fa-calendar-xmark fa-3x mb-3"></i><p>No event requests found.</p></div>
            @endif
        </div>
    </div>
    @endif

</div>

{{-- Department Add/Edit Modal --}}
<div class="modal fade" id="eventDepartmentModal" tabindex="-1" aria-labelledby="eventDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="eventDepartmentModalLabel">Add department</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><form id="eventDepartmentForm" method="POST" action="{{ route('admin.management.event-departments.store') }}">@csrf<input id="eventDepartmentMethod" type="hidden" name="_method" value=""><div class="modal-body"><label class="form-label" for="eventDepartmentName">Department name</label><input class="form-control" id="eventDepartmentName" name="name" placeholder="e.g. ICT" required></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit"><i class="fas fa-save"></i> <span id="eventDepartmentSaveLabel">Add department</span></button></div></form></div></div>
</div>

{{-- Department Delete Confirmation Modal --}}
<div class="modal fade" id="eventDepartmentDeleteModal" tabindex="-1" aria-labelledby="eventDepartmentDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="eventDepartmentDeleteModalLabel">Delete department</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">Delete <strong id="eventDepartmentDeleteName"></strong>? Existing event requests will keep their saved department.</div><form id="eventDepartmentDeleteForm" method="POST"><div class="modal-footer">@csrf @method('DELETE')<button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-danger" type="submit"><i class="fas fa-trash"></i> Delete department</button></div></form></div></div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════════════════ --}}

{{-- Event Request Type Modal --}}
<div class="modal fade" id="eventRequestTypeModal" tabindex="-1" aria-labelledby="eventRequestTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div><h5 class="modal-title" id="eventRequestTypeModalLabel"><i class="fas fa-code-branch text-primary"></i> Add request type</h5><small class="text-muted">Set the name and approval workflow.</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventRequestTypeForm" method="POST" action="{{ route('admin.management.event-types.store') }}">
                @csrf
                <input id="eventRequestTypeMethod" type="hidden" name="_method" value="">
                <div class="modal-body">
                    <div class="mb-4"><label class="form-label" for="eventRequestTypeName">Request type</label><input class="form-control" id="eventRequestTypeName" name="name" placeholder="e.g. Academic" required></div>
                    <label class="form-label mb-1">Who should approve this request?</label>
                    <p class="small text-muted">Select every approver needed. Approval follows this order.</p>
                    <div class="row g-2 mb-3">
                        @foreach($approvalRoles as $role => $label)
                            <div class="col-md-6"><label class="approval-role-option d-block"><input class="form-check-input me-2 event-request-type-role" type="checkbox" name="approval_roles[]" value="{{ $role }}" data-label="{{ $label }}">{{ $loop->iteration }}. {{ $label }}</label></div>
                        @endforeach
                    </div>
                    <div class="approval-flow-preview"><strong class="me-2">Approval flow:</strong><span id="eventRequestTypeFlow" class="text-muted">Select one or more approvers.</span></div>
                    <div id="eventRequestTypeDepartmentNotice" class="alert alert-info small mt-3 mb-0 d-none"><i class="fas fa-building"></i> Department is required because Program Head is part of this approval flow.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <span id="eventRequestTypeSaveLabel">Add request type</span></button></div>
            </form>
        </div>
    </div>
</div>

{{-- Intended User Modal --}}
<div class="modal fade" id="eventIntendedUserModal" tabindex="-1" aria-labelledby="eventIntendedUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div><h5 class="modal-title" id="eventIntendedUserModalLabel"><i class="fas fa-users text-primary"></i> Add intended user</h5><small class="text-muted">Optionally set a route that overrides the request type's approval flow.</small></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="eventIntendedUserForm" method="POST" action="{{ route('admin.management.event-intended-users.store') }}">
                @csrf
                <input id="eventIntendedUserMethod" type="hidden" name="_method" value="">
                <div class="modal-body">
                    <div class="mb-4"><label class="form-label" for="eventIntendedUserName">Intended user</label><input class="form-control" id="eventIntendedUserName" name="name" placeholder="e.g. Senior High School" required></div>
                    <label class="form-label mb-1">Current approval route</label>
                    <p class="small text-muted">Select a custom route for this group. Leave every role unchecked to use the approval route from the selected request type.</p>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6 d-none" data-shs-principal-option><label class="approval-role-option d-block"><input class="form-check-input me-2 event-intended-user-role" type="checkbox" name="approval_roles[]" value="principal_assistant" data-label="SHS Principal">1. SHS Principal</label></div>
                        @foreach($approvalRoles as $role => $label)
                            <div class="col-md-6"><label class="approval-role-option d-block"><input class="form-check-input me-2 event-intended-user-role" type="checkbox" name="approval_roles[]" value="{{ $role }}" data-label="{{ $label }}">{{ $loop->iteration + 1 }}. {{ $label }}</label></div>
                        @endforeach
                    </div>
                    <div class="approval-flow-preview"><strong class="me-2">Route used:</strong><span id="eventIntendedUserFlow" class="text-muted">Uses the selected request type approval route.</span></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <span id="eventIntendedUserSaveLabel">Add intended user</span></button></div>
            </form>
        </div>
    </div>
</div>

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
