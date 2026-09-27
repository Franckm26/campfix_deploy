@extends('layouts.app')

@section('page_title', 'Budget Approvals')

@section('styles')
<style>
    .budget-page { padding: 28px 30px; }
    .budget-card { background: #fff; border: 1px solid #dfe3e8; border-radius: 12px; overflow: hidden; }
    .budget-toolbar { padding: 20px 24px; border-bottom: 1px solid #e5e7eb; display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
    .budget-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
    .budget-tabs a { padding: 9px 15px; border-radius: 8px; color: #334155; text-decoration: none; border: 1px solid #dbe2ea; font-weight: 600; }
    .budget-tabs a.active { background: #0d6efd; border-color: #0d6efd; color: #fff; }
    .budget-search { margin-left: auto; display: flex; gap: 8px; }
    .budget-search input { min-width: 260px; }
    .budget-table { margin: 0; }
    .budget-table th { background: #f8fafc; color: #334155; font-weight: 700; white-space: nowrap; }
    .budget-table td { vertical-align: middle; }
    .budget-amount { font-size: 17px; font-weight: 700; color: #0f172a; white-space: nowrap; }
    .budget-empty { padding: 70px 20px; text-align: center; color: #64748b; }
    .budget-empty i { font-size: 48px; margin-bottom: 14px; color: #94a3b8; }
    @media (max-width: 768px) {
        .budget-page { padding: 16px; }
        .budget-search { margin-left: 0; width: 100%; }
        .budget-search input { min-width: 0; flex: 1; }
    }
</style>
@endsection

@section('content')
<div class="budget-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1"><i class="fas fa-coins text-primary me-2"></i>Budget Approvals</h2>
            <p class="text-muted mb-0">Review budget requests submitted for concerns already in progress.</p>
        </div>
    </div>

    <div class="budget-card">
        <div class="budget-toolbar">
            <div class="budget-tabs">
                <a href="{{ route('school-admin.budget-approvals') }}" class="{{ $status === '' ? 'active' : '' }}">All</a>
                <a href="{{ route('school-admin.budget-approvals', ['status' => 'pending']) }}" class="{{ $status === 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('school-admin.budget-approvals', ['status' => 'approved']) }}" class="{{ $status === 'approved' ? 'active' : '' }}">Approved</a>
                <a href="{{ route('school-admin.budget-approvals', ['status' => 'rejected']) }}" class="{{ $status === 'rejected' ? 'active' : '' }}">Rejected</a>
            </div>
            <form method="GET" action="{{ route('school-admin.budget-approvals') }}" class="budget-search">
                @if($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
                <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Search issue, location, requester...">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        @if($budgets->count())
            <div class="table-responsive">
                <table class="table table-hover budget-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Issue</th>
                            <th>Location</th>
                            <th>Requested By</th>
                            <th>Amount</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th class="text-center">Decision</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($budgets as $budget)
                            <tr>
                                <td>#{{ str_pad($budget->id, 4, '0', STR_PAD_LEFT) }}</td>
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
                                    @if($budget->budget_status === 'pending')
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
                                            {{ optional($budget->budgetReviewer)->name ?: 'Reviewed' }}
                                        </span>
                                    @endif
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
@endsection

@section('scripts')
<script>
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
