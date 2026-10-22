<div class="border rounded p-3 mb-3">
    <h6>Budget request</h6>
    <p class="mb-1"><strong>Requested amount:</strong> PHP {{ number_format((float) $report->budget_amount, 2) }}</p>
    <p class="mb-1"><strong>Budget status:</strong> {{ ucfirst($report->budget_status) }}</p>
    <p class="mb-1"><strong>Requested by:</strong> {{ $report->budgetRequester?->name ?? 'Unknown user' }}</p>
    <p class="mb-1"><strong>Requested:</strong> {{ $report->budget_requested_at?->copy()->timezone('Asia/Manila')->format('M d, Y g:i A') ?? 'Not recorded' }}</p>
    <p class="mb-1"><strong>Reviewed by:</strong> {{ $report->budgetReviewer?->name ?? 'Not reviewed' }}</p>
    <p class="mb-1"><strong>Reviewed:</strong> {{ $report->budget_reviewed_at?->copy()->timezone('Asia/Manila')->format('M d, Y g:i A') ?? 'Not reviewed' }}</p>
    @if($report->budget_rejection_reason)
        <p class="mb-0" style="white-space:pre-wrap"><strong>Rejection reason:</strong> {{ $report->budget_rejection_reason }}</p>
    @endif
</div>
