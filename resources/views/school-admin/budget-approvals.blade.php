@extends('layouts.app')

@section('page_title')
<h2><i class="fas fa-coins me-2"></i>Budget Approvals</h2>
@endsection

@section('styles')
<link href="{{ asset('css/admin.css') }}" rel="stylesheet">
<style>
    .budget-table { margin: 0; }
    .budget-table th { white-space: nowrap; }
    .budget-table td { vertical-align: middle; }
    .budget-amount { font-size: 17px; font-weight: 700; color: #0f172a; white-space: nowrap; }
    .budget-empty { padding: 70px 20px; text-align: center; color: #64748b; }
    .budget-empty i { font-size: 48px; margin-bottom: 14px; color: #94a3b8; }
</style>
@endsection

@section('content')
<div class="container-fluid px-3">
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                <ul class="nav nav-pills mb-0 flex-wrap">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals') }}" class="nav-link {{ $viewType === 'active' && $status === '' ? 'active' : '' }}">
                            <i class="fas fa-list"></i> All
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['status' => 'pending']) }}" class="nav-link {{ $viewType === 'active' && $status === 'pending' ? 'active' : '' }}">
                            <i class="fas fa-clock"></i> Pending
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['status' => 'approved']) }}" class="nav-link {{ $viewType === 'active' && $status === 'approved' ? 'active' : '' }}">
                            <i class="fas fa-check-circle"></i> Approved
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['status' => 'rejected']) }}" class="nav-link {{ $viewType === 'active' && $status === 'rejected' ? 'active' : '' }}" style="color: {{ $viewType === 'active' && $status === 'rejected' ? '#fff' : '#dc3545' }};">
                            <i class="fas fa-times-circle"></i> Rejected
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['view' => 'archives']) }}" class="nav-link {{ $viewType === 'archives' ? 'active' : '' }}"><i class="fas fa-archive"></i> Archives</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['view' => 'deleted']) }}" class="nav-link {{ $viewType === 'deleted' ? 'active' : '' }}"><i class="fas fa-trash"></i> Deleted</a>
                    </li>
                </ul>
            </div>
            <form method="GET" action="{{ route('school-admin.budget-approvals') }}">
                <input type="hidden" name="view" value="{{ $viewType }}">
                @if($status !== '')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="row g-2">
                    <div class="col-12 col-md">
                        <input type="search" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Search issue, location, requester..." enterkeyhint="search">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
                        <a href="{{ route('school-admin.budget-approvals', array_filter(['view' => $viewType, 'status' => $status])) }}" class="btn btn-secondary btn-sm ms-1" aria-label="Clear search">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card" style="display: block !important;">
        <div class="card-body" style="display: block !important;">
        @if($budgets->count())
            <div class="table-responsive" style="display: block !important; visibility: visible !important; opacity: 1 !important;">
                <table class="table table-hover budget-table" style="display: table !important;">
                    <thead>
                        <tr>
                            <th style="width: 80px; min-width: 80px; text-align: center;">Ticket</th>
                            <th>Issue</th>
                            <th>Location</th>
                            <th>Requested By</th>
                            <th>Amount</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th class="text-center">Decision</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgets as $budget)
                            <tr>
                                <td style="width: 80px; min-width: 80px; text-align: center; white-space: nowrap;">#{{ str_pad($budget->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $budget->title ?: 'Untitled concern' }}</div>
                                    <small class="text-muted">{{ optional($budget->category)->name ?: 'Uncategorized' }}</small>
                                </td>
                                <td>{{ $budget->location ?: 'N/A' }}</td>
                                <td>{{ optional($budget->budgetRequester)->name ?: 'Unknown user' }}</td>
                                <td class="budget-amount">PHP {{ number_format((float) $budget->budget_amount, 2) }}</td>
                                <td>{{ optional($budget->budget_requested_at)->timezone('Asia/Manila')->format('M d, Y g:i A') ?: '-' }}</td>
                                <td>
                                    <span class="badge bg-{{ $budget->budget_status === 'approved' ? 'success' : ($budget->budget_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($budget->budget_status) }}
                                    </span>
                                    @if($budget->budget_status === 'rejected' && $budget->budget_rejection_reason)
                                        <div class="small text-danger mt-1">{{ $budget->budget_rejection_reason }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($budget->budget_status === 'pending' && $viewType === 'active')
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn btn-success btn-sm" type="button" onclick="reviewBudget({{ $budget->id }}, 'approve')">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" type="button" onclick="reviewBudget({{ $budget->id }}, 'reject')">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted small">
                                            {{ $budget->budget_status === 'pending' ? 'Pending review' : (optional($budget->budgetReviewer)->name ?: 'Reviewed') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center" style="white-space:nowrap">
                                    <div class="btn-group" role="group" aria-label="Budget report actions">
                                        <button type="button" class="btn btn-sm btn-info" onclick="viewBudget({{ $budget->id }})" title="View" aria-label="View budget report"><i class="fas fa-eye"></i></button>
                                        @if($viewType === 'deleted')
                                            <button type="button" class="btn btn-sm btn-success" onclick="budgetAction('{{ route('school-admin.budget-approvals.restore-deleted', $budget->id) }}', 'restore')" title="Restore" aria-label="Restore deleted budget report"><i class="fas fa-trash-restore"></i></button>
                                        @else
                                            @if($viewType === 'archives')
                                                <button type="button" class="btn btn-sm btn-success" onclick="budgetAction('{{ route('reports.restore', $budget) }}', 'restore')" title="Restore" aria-label="Restore archived budget report"><i class="fas fa-trash-restore"></i></button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="budgetAction('{{ route('reports.archive', $budget) }}', 'archive')" title="Archive" aria-label="Archive budget report"><i class="fas fa-archive"></i></button>
                                            @endif
                                            @if(!$budget->assigned_to || $budget->status === 'Resolved')
                                                <button type="button" class="btn btn-sm btn-danger" onclick="budgetAction('{{ route('reports.destroy', $budget) }}', 'delete')" title="Delete" aria-label="Delete budget report"><i class="fas fa-trash"></i></button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete assigned reports until resolved" aria-label="Cannot delete assigned reports until resolved"><i class="fas fa-trash"></i></button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3">{{ $budgets->links() }}</div>
        @else
            <div class="budget-empty">
                <i class="fas fa-clipboard-check"></i>
                <h4>No budget requests found</h4>
                <p class="mb-0">Submitted concern budgets will appear here for review.</p>
            </div>
        @endif
        </div>
    </div>
</div>
<div class="modal fade" id="budgetDetailModal" tabindex="-1" aria-label="Budget report details" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" id="budgetDetailContent"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
window.viewBudget = async function(reportId) {
    const content = document.getElementById('budgetDetailContent');
    content.innerHTML = '<div class="modal-body text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('budgetDetailModal')).show();
    try {
        const response = await fetch(`/school-admin/budget-approvals/${reportId}/details`, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
        if (!response.ok) throw new Error('Unable to load budget report details.');
        content.innerHTML = await response.text();
    } catch (error) {
        content.innerHTML = '<div class="modal-header"><h5 class="modal-title">Budget report</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body text-danger"></div>';
        content.querySelector('.modal-body').textContent = error.message;
    }
};

window.budgetAction = async function(url, action) {
    const labels = {archive: 'Archive Report?', delete: 'Delete Report?', restore: 'Restore Report?'};
    const text = action === 'delete' ? 'This report will be moved to Deleted. You can restore it later.'
        : action === 'archive' ? 'This report will be archived and hidden from your active list.' : 'This report will return to your active list.';
    const confirmation = await getSwal().fire({title: labels[action], text, icon: 'question', showCancelButton: true,
        confirmButtonText: action.charAt(0).toUpperCase() + action.slice(1), confirmButtonColor: action === 'delete' ? '#dc3545' : '#0d6efd'});
    if (!confirmation.isConfirmed) return;
    try {
        const response = await fetch(url, {method: action === 'delete' ? 'DELETE' : 'POST', headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'
        }});
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.error || data.message || 'Unable to update this report.');
        await getSwal().fire({icon: 'success', title: 'Report updated', text: data.message});
        window.location.reload();
    } catch (error) {
        await getSwal().fire({icon: 'error', title: 'Unable to update report', text: error.message});
    }
};

window.reviewBudget = async function(reportId, action) {
    let payload = {};
    if (action === 'reject') {
        const rejection = await getSwal().fire({
            title: 'Reject Budget Request',
            input: 'textarea',
            inputLabel: 'Reason for rejection',
            inputPlaceholder: 'Explain what needs to be revised...',
            inputValidator: value => !value.trim() ? 'A rejection reason is required.' : null,
            showCancelButton: true,
            confirmButtonText: 'Reject Budget',
            confirmButtonColor: '#dc3545'
        });
        if (!rejection.isConfirmed) return;
        payload = {reason: rejection.value.trim()};
    } else {
        const approval = await getSwal().fire({
            title: 'Approve this budget?',
            text: 'The assigned staff will be allowed to complete the concern.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Approve Budget',
            confirmButtonColor: '#198754'
        });
        if (!approval.isConfirmed) return;
    }

    const response = await fetch(`/reports/${reportId}/budget/${action}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });
    const data = await response.json();
    await getSwal().fire({
        icon: response.ok ? 'success' : 'error',
        title: response.ok ? 'Budget Updated' : 'Unable to Review',
        text: data.message || data.error || 'Please try again.'
    });
    if (response.ok) window.location.reload();
};
</script>
@endsection
