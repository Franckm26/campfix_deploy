<style>
    .event-config-card { border: 1px solid #dbe4f0; border-radius: 12px; overflow: hidden; }
    .event-config-card .card-header { background: #fff; padding: 1rem 1.25rem; }
    .event-config-card .table { margin-bottom: 0; }
    .event-config-card th { background: #f8fafc; color: #5f6f86; font-weight: 700; }
    .event-config-card th, .event-config-card td { padding: .9rem 1rem; vertical-align: middle; }
    .event-config-actions { display: flex; align-items: center; justify-content: center; gap: 1rem; }
    .event-config-action { border: 0; background: transparent; padding: .25rem; font-size: 1.05rem; }
    .approval-role-option { border: 1px solid #dbe4f0; border-radius: 8px; padding: .7rem .85rem; cursor: pointer; }
    .approval-role-option:hover { background: #f5f9ff; border-color: #0d6efd; }
    .approval-role-option:has(input:checked) { background: #e8f1ff; border-color: #0d6efd; }
    .approval-step { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 50%; background: #e8f1ff; color: #0d6efd; font-weight: 700; font-size: .8rem; margin-right: .4rem; }
    .approval-flow-preview { min-height: 46px; background: #f5f9ff; border: 1px dashed #aac8f5; border-radius: 8px; padding: .7rem .85rem; }
    .approval-chain-step { display: inline-flex; align-items: center; gap: .5rem; padding: .5rem .7rem; border-radius: 999px; background: #e8f1ff; color: #0b5ed7; font-weight: 600; }
    .approval-chain-arrow { color: #789; }
    .event-swal-input { width: 100% !important; margin: 0 !important; box-sizing: border-box !important; }
    .event-swal-role-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem; }
    .event-swal-role { display: flex; align-items: center; min-height: 52px; padding: .7rem .85rem; border: 1px solid #dbe4f0; border-radius: 8px; cursor: pointer; }
    .event-swal-role:hover, .event-swal-role:has(input:checked) { background: #e8f1ff; border-color: #0d6efd; }
    .event-swal-flow { display: flex; flex-wrap: wrap; align-items: center; gap: .55rem; min-height: 52px; padding: .7rem; background: #f5f9ff; border: 1px dashed #aac8f5; border-radius: 8px; }
    .event-swal-flow-step { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .65rem; border-radius: 999px; background: #e8f1ff; color: #0b5ed7; font-weight: 600; }
    .event-swal-flow-step b { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 50%; background: #0d6efd; color: #fff; font-size: .75rem; }
    @media (max-width: 575.98px) { .event-swal-role-grid { grid-template-columns: 1fr; } }
</style>

<div class="event-config-card card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center gap-3">
        <div><h5 class="mb-1"><i class="fas fa-users text-primary me-2"></i>Intended Users</h5><small class="text-muted">Audience groups available in the event request form.</small></div>
        <button class="btn btn-primary add-intended-user" type="button"><i class="fas fa-plus"></i> Add User Group</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead><tr><th>Intended User</th><th>Usage</th><th class="text-center" style="width:170px">Actions</th></tr></thead>
            <tbody>
                @forelse($eventIntendedUsers as $item)
                    <tr>
                        <td class="fw-bold">{{ $item->name }}</td>
                        <td class="text-muted">Available for approval-chain combinations</td>
                        <td><div class="event-config-actions">
                            <button type="button" class="event-config-action text-primary edit-intended-user" data-id="{{ $item->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                            <form class="event-config-delete-form" method="POST" action="{{ route('admin.management.event-setup.toggle', ['type' => 'intended-user', 'id' => $item->id]) }}" data-item-name="{{ $item->name }}" data-item-type="intended user">@csrf @method('PATCH')<button class="event-config-action text-danger" title="Delete"><i class="fas fa-trash"></i></button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No intended users configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="event-config-card card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center gap-3">
        <div><h5 class="mb-1"><i class="fas fa-file-signature text-primary me-2"></i>Request Types</h5><small class="text-muted">Types of event requests and their default approval route.</small></div>
        <button class="btn btn-primary add-request-type" type="button"><i class="fas fa-plus"></i> Add Request Type</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead><tr><th>Request Type</th><th>Default Approval Flow</th><th class="text-center" style="width:170px">Actions</th></tr></thead>
            <tbody>
                @forelse($eventRequestTypes as $item)
                    <tr>
                        <td class="fw-bold">{{ $item->name }}</td>
                        <td>{{ collect($item->approval_roles)->map(fn($role) => $approvalRoles[$role] ?? $role)->implode(' → ') }}</td>
                        <td><div class="event-config-actions">
                            <button type="button" class="event-config-action text-primary edit-request-type" data-id="{{ $item->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                            <form class="event-config-delete-form" method="POST" action="{{ route('admin.management.event-setup.toggle', ['type' => 'request-type', 'id' => $item->id]) }}" data-item-name="{{ $item->name }}" data-item-type="request type">@csrf @method('PATCH')<button class="event-config-action text-danger" title="Delete"><i class="fas fa-trash"></i></button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">No request types configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(! $approvalConfigurationReady)
    <div class="alert alert-warning mb-4"><i class="fas fa-database me-2"></i>Apply the latest intended-user approval migration to configure approval chains.</div>
@else
    <div class="event-config-card card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center gap-3">
            <div><h5 class="mb-1"><i class="fas fa-sitemap text-primary me-2"></i>Approval Chains</h5><small class="text-muted">Who approves each intended-user and request-type combination.</small></div>
            <button class="btn btn-primary configure-approval-chain" type="button"><i class="fas fa-sliders"></i> Configure Chain</button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead><tr><th>Intended User</th><th>Request Type</th><th>Approval Flow</th><th class="text-center" style="width:170px">Actions</th></tr></thead>
                <tbody>
                    @forelse($eventApprovalChains as $chain)
                        <tr>
                            <td class="fw-bold">{{ $chain->intendedUser?->name ?? 'Unavailable' }}</td>
                            <td>{{ $chain->requestType?->name ?? 'Unavailable' }}</td>
                            <td>{{ collect($chain->approval_roles)->map(fn($role) => $approvalRoles[$role] ?? $role)->implode(' → ') }}</td>
                            <td><div class="event-config-actions">
                                <button type="button" class="event-config-action text-primary configure-approval-chain" data-intended-user-id="{{ $chain->event_intended_user_id }}" data-request-type-id="{{ $chain->event_request_type_id }}" title="Edit approval chain" aria-label="Edit approval chain"><i class="fas fa-edit"></i></button>
                                <form class="event-config-delete-form" method="POST" action="{{ route('admin.management.event-approval-chains.destroy', $chain) }}" data-item-name="{{ ($chain->intendedUser?->name ?? 'Unavailable').' / '.($chain->requestType?->name ?? 'Unavailable') }}" data-item-type="approval chain">@csrf @method('DELETE')<button class="event-config-action text-danger" type="submit" title="Delete approval chain" aria-label="Delete approval chain"><i class="fas fa-trash"></i></button></form>
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No approval chains saved yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

<div class="event-config-card card mb-0">
    <div class="card-header d-flex justify-content-between align-items-center gap-3">
        <div><h5 class="mb-1"><i class="fas fa-building text-primary me-2"></i>Departments</h5><small class="text-muted">Departments available when Program Head approval is required.</small></div>
        <button class="btn btn-primary add-event-department" type="button"><i class="fas fa-plus"></i> Add Department</button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead><tr><th>Department</th><th class="text-center" style="width:170px">Actions</th></tr></thead>
            <tbody>
                @forelse($eventDepartments as $item)
                    <tr>
                        <td class="fw-bold">{{ $item->name }}</td>
                        <td><div class="event-config-actions">
                            <button type="button" class="event-config-action text-primary edit-event-department" data-id="{{ $item->id }}" data-name="{{ $item->name }}" title="Edit"><i class="fas fa-edit"></i></button>
                            <form class="event-config-delete-form" method="POST" action="{{ route('admin.management.event-setup.toggle', ['type' => 'department', 'id' => $item->id]) }}" data-item-name="{{ $item->name }}" data-item-type="department">@csrf @method('PATCH')<button class="event-config-action text-danger" title="Delete"><i class="fas fa-trash"></i></button></form>
                        </div></td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="text-center text-muted py-4">No departments configured.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
