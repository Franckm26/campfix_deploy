@extends('superadmin.layout')

@section('page_title', 'All Event Requests')

@section('content')

<div class="sa-card mb-4">
    <form method="GET" action="{{ \App\Support\ProtectedRoute::url('superadmin.events') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end">
        <div style="flex:1;min-width:200px">
            <label class="sa-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" class="sa-input" placeholder="Title, description…" enterkeyhint="search" inputmode="search" onkeypress="if(event.key==='Enter'){this.form.submit();}">
        </div>
        <div style="min-width:150px">
            <label class="sa-label">Status</label>
            <select name="status" class="sa-input">
                <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>All</option>
                <option value="Pending"  {{ $status === 'Pending'  ? 'selected' : '' }}>Pending</option>
                <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                <option value="Cancelled"{{ $status === 'Cancelled'? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div style="display:flex;gap:8px">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="fas fa-search"></i> Filter</button>
            <a href="{{ \App\Support\ProtectedRoute::url('superadmin.events') }}" class="sa-btn sa-btn-ghost">Reset</a>
        </div>
        <button type="button" class="sa-btn sa-btn-primary" style="margin-left:auto" onclick="openNewRequestModal()">
            <i class="fas fa-plus"></i> New Request
        </button>
    </form>
</div>

<div class="sa-card">
    <div style="font-size:13px;color:var(--sa-muted);margin-bottom:12px">
        Showing {{ $events->firstItem() }}–{{ $events->lastItem() }} of {{ $events->total() }} event requests
    </div>
    <div style="overflow-x:auto">
        <table class="sa-table">
            <thead>
                <tr>
                    <th>Event Ticket</th>
                    <th>Requested By</th>
                    <th>Department</th>
                    <th>Event Date</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                @php
                    $statusColors = [
                        'Pending'   => 'sa-badge-yellow',
                        'Approved'  => 'sa-badge-green',
                        'Rejected'  => 'sa-badge-red',
                        'Cancelled' => 'sa-badge-gray',
                    ];
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:500;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            {{ $event->title ?? 'Untitled' }}
                        </div>
                        @if($event->is_deleted)
                            <span class="sa-badge sa-badge-red" style="font-size:10px">Deleted</span>
                        @endif
                    </td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $event->user->name ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $event->department ?? '—' }}</td>
                    <td style="color:var(--sa-muted);font-size:12px">
                        {{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('m/d/Y') : '—' }}
                    </td>
                    <td><span class="sa-badge {{ $statusColors[$event->status] ?? 'sa-badge-gray' }}">{{ $event->status }}</span></td>
                    <td style="color:var(--sa-muted);font-size:12px">{{ $event->created_at->format('m/d/Y') }}</td>
                    <td>
                        <form method="POST" action="{{ \App\Support\ProtectedRoute::url('superadmin.events.force-delete', $event->id) }}"
                              onsubmit="return confirm('Permanently delete this event request? Cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="sa-btn sa-btn-danger sa-btn-sm" title="Force Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:var(--sa-muted);padding:32px">No event requests found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($events->hasPages())
    <div style="margin-top:16px;display:flex;justify-content:flex-end">
        {{ $events->links('vendor.pagination.superadmin') }}
    </div>
    @endif
</div>
@endsection

@php
    $erEventSetupAvailable = \Illuminate\Support\Facades\Schema::hasTable('event_request_types');
    $erEventTypes = $erEventSetupAvailable ? \App\Models\EventRequestType::where('is_active', true)->orderBy('name')->get() : collect([['name' => 'Academic'], ['name' => 'Non-Academic']]);
    $erEventUsers = $erEventSetupAvailable ? \App\Models\EventIntendedUser::where('is_active', true)->orderBy('name')->get() : collect([['name' => 'Faculty', 'code' => 'faculty'], ['name' => 'Tertiary', 'code' => 'tertiary'], ['name' => 'Senior High School', 'code' => 'shs'], ['name' => 'Staff', 'code' => 'staff'], ['name' => 'Maintenance', 'code' => 'maintenance']]);
    $erEventDepartments = $erEventSetupAvailable ? \App\Models\EventDepartment::where('is_active', true)->orderBy('name')->get() : collect([['name' => 'GE'], ['name' => 'ICT'], ['name' => 'Business Management'], ['name' => 'THM']]);
    $erApprovalSetupAvailable = $erEventSetupAvailable && \Illuminate\Support\Facades\Schema::hasColumns('event_approval_chains', ['event_intended_user_id', 'event_request_type_id']);
    $erEventApprovalChains = $erApprovalSetupAvailable ? \App\Models\EventApprovalChain::with(['intendedUser', 'requestType'])->get() : collect();
    $erFacilities = \App\Models\Facility::orderBy('type')->orderBy('name')->get();
@endphp

<!-- New Request Modal -->
<div class="modal fade" id="newRequestModal" tabindex="-1" aria-labelledby="newRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newRequestModalLabel"><i class="fas fa-calendar-plus"></i> Submit Event Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newRequestForm" action="{{ route('events.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="nr_location" name="location" value="">
                <div class="modal-body">
                    <!-- Date and Time -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nr_event_date" class="form-label">Date *</label>
                            <input type="date" class="form-control" id="nr_event_date" name="event_date"
                                min="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label for="nr_start_time" class="form-label">Start Time *</label>
                            <input type="time" class="form-control" id="nr_start_time" name="start_time" required>
                        </div>
                        <div class="col-md-3">
                            <label for="nr_end_time" class="form-label">End Time *</label>
                            <input type="time" class="form-control" id="nr_end_time" name="end_time" required>
                        </div>
                    </div>

                    <!-- Request Type + Intended User -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="nr_request_type" class="form-label">Request Type *</label>
                            <select class="form-select" id="nr_request_type" name="request_type" required>
                                <option value="">Select type</option>
                                @foreach($erEventTypes as $type)
                                    <option value="{{ is_array($type) ? $type['name'] : $type->name }}"
                                        data-requires-department="{{ (is_array($type) ? $type['name'] === 'Academic' : $type->requires_department) ? '1' : '0' }}">
                                        {{ is_array($type) ? $type['name'] : $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label for="nr_intended_user" class="form-label">Intended User *</label>
                            <select class="form-select" id="nr_intended_user" name="intended_user" required>
                                @foreach($erEventUsers as $userOption)
                                    <option value="{{ is_array($userOption) ? $userOption['code'] : $userOption->code }}"
                                        {{ (is_array($userOption) ? $userOption['code'] : $userOption->code) === 'faculty' ? 'selected' : '' }}>
                                        {{ is_array($userOption) ? $userOption['name'] : $userOption->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="category" value="Area Use">
                    </div>

                    <!-- Location -->
                    <div class="row mb-3">
                        <div class="col-md-6" id="nr_area_of_use_container">
                            <label for="nr_area_of_use" class="form-label">Location *</label>
                            <select class="form-select" id="nr_area_of_use" name="area_of_use" required>
                                <option value="">Select a location</option>
                                @foreach($erFacilities->groupBy('type') as $fType => $fGroup)
                                    <optgroup label="{{ ucfirst($fType) }}">
                                        @foreach($fGroup as $facility)
                                            <option value="{{ $facility->name }}">{{ $facility->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6" id="nr_avr_selection_container" style="display:none;">
                            <label for="nr_avr_selection" class="form-label">AVR Selection *</label>
                            <select class="form-select" id="nr_avr_selection" name="avr_selection">
                                <option value="">Select AVR</option>
                                <option value="AVR 1">AVR 1</option>
                                <option value="AVR 2">AVR 2</option>
                            </select>
                        </div>
                    </div>

                    <!-- Department (conditional) -->
                    <div class="mb-3" id="nr_department_container" style="display:none;">
                        <label for="nr_department" class="form-label">Department *</label>
                        <select class="form-select" id="nr_department" name="department">
                            <option value="">Select department</option>
                            @foreach($erEventDepartments as $dept)
                                <option value="{{ is_array($dept) ? $dept['name'] : $dept->name }}">
                                    {{ is_array($dept) ? $dept['name'] : $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="nr_description" class="form-label">Description *</label>
                        <textarea class="form-control" id="nr_description" name="description"
                            rows="4" placeholder="Describe the event purpose and details..."
                            required maxlength="500" oninput="nrUpdateDescCount()"></textarea>
                        <div class="d-flex justify-content-end">
                            <small id="nr_desc_char_count" class="text-muted">0 / 500</small>
                        </div>
                    </div>

                    <!-- Materials (optional) -->
                    <div class="mb-3">
                        <label class="form-label">Materials/Equipment Needed <span class="text-muted">(Optional)</span></label>
                        <table class="table table-bordered table-sm" id="nrMaterialsTable">
                            <thead>
                                <tr>
                                    <th style="width:70px">Qty</th>
                                    <th>Item</th>
                                    <th style="width:40px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><input type="number" class="form-control form-control-sm" name="materials[0][qty]" min="1" placeholder="1"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="materials[0][item]" placeholder="e.g., Projector"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="nrRemoveMaterialRow(this)"><i class="fas fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="nrAddMaterialRow()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="nrShowPreview()">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- New Request Preview Modal -->
<div class="modal fade" id="newRequestPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-eye"></i> Event Request Preview</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-2"><div class="col-4 fw-bold">Request Type:</div><div class="col-8" id="nrp_request_type"></div></div>
                <div class="row mb-2"><div class="col-4 fw-bold">Intended User:</div><div class="col-8" id="nrp_intended_user"></div></div>
                <div class="row mb-2"><div class="col-4 fw-bold">Location:</div><div class="col-8" id="nrp_location"></div></div>
                <div class="row mb-2" id="nrp_department_row" style="display:none;"><div class="col-4 fw-bold">Department:</div><div class="col-8" id="nrp_department"></div></div>
                <div class="row mb-2"><div class="col-4 fw-bold">Date:</div><div class="col-8" id="nrp_date"></div></div>
                <div class="row mb-2"><div class="col-4 fw-bold">Time:</div><div class="col-8"><span id="nrp_start_time"></span> – <span id="nrp_end_time"></span></div></div>
                <div class="row mb-2"><div class="col-4 fw-bold">Description:</div><div class="col-8" id="nrp_description"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="nrBackToEdit()"><i class="fas fa-edit"></i> Edit</button>
                <button type="button" class="btn btn-primary" id="nrSubmitBtn" onclick="nrConfirmSubmit()">
                    <i class="fas fa-check"></i> Submit for Approval
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openNewRequestModal() {
    document.getElementById('newRequestForm').reset();
    document.getElementById('nr_desc_char_count').textContent = '0 / 500';
    document.getElementById('nr_department_container').style.display = 'none';
    document.getElementById('nr_avr_selection_container').style.display = 'none';
    document.getElementById('nr_area_of_use_container').style.display = 'block';
    // Reset materials table to one row
    const tbody = document.querySelector('#nrMaterialsTable tbody');
    tbody.innerHTML = '<tr><td><input type="number" class="form-control form-control-sm" name="materials[0][qty]" min="1" placeholder="1"></td><td><input type="text" class="form-control form-control-sm" name="materials[0][item]" placeholder="e.g., Projector"></td><td><button type="button" class="btn btn-danger btn-sm" onclick="nrRemoveMaterialRow(this)"><i class="fas fa-times"></i></button></td></tr>';
    new bootstrap.Modal(document.getElementById('newRequestModal')).show();
}

function nrUpdateDescCount() {
    const ta = document.getElementById('nr_description');
    const counter = document.getElementById('nr_desc_char_count');
    if (ta && counter) {
        const len = ta.value.length;
        counter.textContent = len + ' / 500';
        counter.style.color = len >= 480 ? '#dc3545' : '';
    }
}

// Sync department requirement when request type or area changes
document.addEventListener('DOMContentLoaded', function() {
    const nrRequestType = document.getElementById('nr_request_type');
    const nrIntendedUser = document.getElementById('nr_intended_user');
    const nrAreaOfUse = document.getElementById('nr_area_of_use');

    function nrSyncDepartment() {
        const container = document.getElementById('nr_department_container');
        const dept = document.getElementById('nr_department');
        const selected = nrRequestType ? nrRequestType.options[nrRequestType.selectedIndex] : null;
        const requiresDept = selected && selected.dataset.requiresDepartment === '1' && nrAreaOfUse && nrAreaOfUse.value;
        if (container) container.style.display = requiresDept ? 'block' : 'none';
        if (dept) {
            dept.required = !!requiresDept;
        }
    }

    function nrSyncLocation() {
        // Sync hidden location from area_of_use
        const locationHidden = document.getElementById('nr_location');
        if (locationHidden && nrAreaOfUse) {
            locationHidden.value = nrAreaOfUse.value;
        }
    }

    if (nrRequestType) nrRequestType.addEventListener('change', nrSyncDepartment);
    if (nrAreaOfUse) {
        nrAreaOfUse.addEventListener('change', function() {
            nrSyncDepartment();
            nrSyncLocation();
        });
    }
});

function nrAddMaterialRow() {
    const tbody = document.querySelector('#nrMaterialsTable tbody');
    const idx = tbody.rows.length;
    const row = tbody.insertRow();
    row.innerHTML = `<td><input type="number" class="form-control form-control-sm" name="materials[${idx}][qty]" min="1" placeholder="1"></td><td><input type="text" class="form-control form-control-sm" name="materials[${idx}][item]" placeholder="e.g., Chair"></td><td><button type="button" class="btn btn-danger btn-sm" onclick="nrRemoveMaterialRow(this)"><i class="fas fa-times"></i></button></td>`;
}

function nrRemoveMaterialRow(btn) {
    const row = btn.closest('tr');
    const tbody = row.closest('tbody');
    if (tbody.rows.length > 1) row.remove();
}

function nrShowPreview() {
    const form = document.getElementById('newRequestForm');
    if (!form.checkValidity()) { form.reportValidity(); return; }

    // Sync location hidden before preview
    const areaEl = document.getElementById('nr_area_of_use');
    const locationHidden = document.getElementById('nr_location');
    if (areaEl && locationHidden) locationHidden.value = areaEl.value;

    const rtSel = document.getElementById('nr_request_type');
    const iuSel = document.getElementById('nr_intended_user');
    const dateEl = document.getElementById('nr_event_date');
    const deptEl = document.getElementById('nr_department');
    const deptContainer = document.getElementById('nr_department_container');

    document.getElementById('nrp_request_type').textContent = rtSel ? rtSel.options[rtSel.selectedIndex].text : '';
    document.getElementById('nrp_intended_user').textContent = iuSel ? iuSel.options[iuSel.selectedIndex].text : '';
    document.getElementById('nrp_location').textContent = areaEl ? areaEl.options[areaEl.selectedIndex].text : '';
    document.getElementById('nrp_date').textContent = dateEl ? dateEl.value : '';
    document.getElementById('nrp_start_time').textContent = document.getElementById('nr_start_time').value;
    document.getElementById('nrp_end_time').textContent = document.getElementById('nr_end_time').value;
    document.getElementById('nrp_description').textContent = document.getElementById('nr_description').value;

    const deptRow = document.getElementById('nrp_department_row');
    if (deptContainer && deptContainer.style.display !== 'none' && deptEl && deptEl.value) {
        document.getElementById('nrp_department').textContent = deptEl.value;
        if (deptRow) deptRow.style.display = 'flex';
    } else {
        if (deptRow) deptRow.style.display = 'none';
    }

    // Hide form modal, show preview
    bootstrap.Modal.getInstance(document.getElementById('newRequestModal')).hide();
    new bootstrap.Modal(document.getElementById('newRequestPreviewModal')).show();
}

function nrBackToEdit() {
    bootstrap.Modal.getInstance(document.getElementById('newRequestPreviewModal')).hide();
    new bootstrap.Modal(document.getElementById('newRequestModal')).show();
}

function nrConfirmSubmit() {
    const submitBtn = document.getElementById('nrSubmitBtn');
    const form = document.getElementById('newRequestForm');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        // Close preview modal
        const previewModal = bootstrap.Modal.getInstance(document.getElementById('newRequestPreviewModal'));
        if (previewModal) previewModal.hide();

        if (data.success) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'success', title: 'Submitted!', text: data.message || 'Event request submitted successfully.', timer: 3000, timerProgressBar: true });
            }
            form.reset();
            setTimeout(() => location.reload(), 2500);
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to submit request.' });
            } else {
                alert(data.message || 'Failed to submit request.');
            }
        }
    })
    .catch(err => {
        console.error('Submit error:', err);
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred. Please try again.' });
        }
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-check"></i> Submit for Approval';
    });
}
</script>
