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
                        <a href="{{ route('school-admin.budget-approvals') }}" class="nav-link {{ $status === '' ? 'active' : '' }}">
                            <i class="fas fa-list"></i> All
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['status' => 'pending']) }}" class="nav-link {{ $status === 'pending' ? 'active' : '' }}">
                            <i class="fas fa-clock"></i> Pending
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['status' => 'approved']) }}" class="nav-link {{ $status === 'approved' ? 'active' : '' }}">
                            <i class="fas fa-check-circle"></i> Approved
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.budget-approvals', ['status' => 'rejected']) }}" class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" style="color: {{ $status === 'rejected' ? '#fff' : '#dc3545' }};">
                            <i class="fas fa-times-circle"></i> Rejected
                        </a>
                    </li>
                </ul>
            </div>
            <form method="GET" action="{{ route('school-admin.budget-approvals') }}">
                @if($status !== '')
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif
                <div class="row g-2">
                    <div class="col-12 col-md">
                        <input type="search" name="search" class="form-control form-control-sm" value="{{ $search }}" placeholder="Search issue, location, requester..." enterkeyhint="search">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
                        <a href="{{ route('school-admin.budget-approvals', array_filter(['status' => $status])) }}" class="btn btn-secondary btn-sm ms-1" aria-label="Clear search">
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
