@props(['request' => null, 'title' => 'Request Progress'])
@if($request)
@php
    $progress = app(\App\Services\FacilityApprovalProgress::class)->forRequest($request);
    $color = match($request->status) { 'Approved' => 'success', 'Rejected' => 'danger', 'Cancelled' => 'secondary', default => 'warning' };
    $columns = match($progress['total']) { 1 => 'col-12', 2 => 'col-6', 3 => 'col-4', default => 'col-6 col-md-3' };
@endphp
<div class="card mb-4" style="width:100%" data-approval-progress>
    <div class="card-header bg-primary text-white text-center"><h5 class="mb-0"><i class="fas fa-tasks me-2"></i>{{ $title }}</h5></div>
    <div class="card-body" style="padding:20px">
        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted">Approval Progress</span>
            <strong class="text-{{ $color }}">{{ $progress['completed'] }} / {{ $progress['total'] }}</strong>
        </div>
        <div class="progress mb-4" style="height:25px;border-radius:10px">
            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width:{{ $progress['percentage'] }}%"
                aria-valuenow="{{ $progress['percentage'] }}" aria-valuemin="0" aria-valuemax="100">{{ $progress['percentage'] }}%</div>
        </div>
        <div class="approval-steps"><div class="row text-center g-2 justify-content-center">
            @foreach($progress['steps'] as $step)
                <div class="{{ $columns }}">
                    <div class="approval-step {{ $step['state'] === 'waiting' ? '' : $step['state'] }}" data-approval-role="{{ $step['role'] }}">
                        <div class="step-icon mb-2">
                            @if($step['state'] === 'approved')<i class="fas fa-check-circle fa-2x text-success"></i>
                            @elseif($step['state'] === 'rejected')<i class="fas fa-times-circle fa-2x text-danger"></i>
                            @else<i class="fas fa-clock fa-2x text-{{ $step['state'] === 'pending' ? 'warning' : 'muted' }}"></i>@endif
                        </div>
                        <h6>{{ $step['label'] }}</h6>
                        <small class="text-muted">
                            @if($step['state'] === 'approved' || $step['state'] === 'rejected')
                                {{ $step['state'] === 'approved' ? 'Approved' : 'Rejected' }}@if($step['approver']) by {{ $step['approver'] }}@endif
                                @if($step['at'])<br>{{ \Carbon\Carbon::parse($step['at'])->timezone('Asia/Manila')->format('M d, Y h:i A') }}@endif
                            @elseif($step['state'] === 'pending')Waiting for Approval
                            @elseif($request->status === 'Cancelled')Cancelled
                            @else Pending Previous Level @endif
                        </small>
                    </div>
                </div>
            @endforeach
        </div></div>
        @if($progress['total'] === 0)<p class="text-muted mt-3">No approval steps required.</p>@endif
        <div class="mt-4 text-center"><span class="badge bg-{{ $color }} fs-6">{{ $request->status === 'Approved' ? 'Fully Approved' : $request->status }}</span></div>
    </div>
</div>
<style>
.approval-steps { width: 100%; }

.approval-steps .row {
    display: flex !important;
    justify-content: center !important;
    flex-wrap: wrap;
}

.approval-step {
    padding: 15px 10px;
    border-radius: 10px;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.approval-step.approved  { background-color: #d4edda; border: 2px solid #28a745; }
.approval-step.pending   { background-color: #fff3cd; border: 2px solid #ffc107; }
.approval-step.rejected  { background-color: #f8d7da; border: 2px solid #dc3545; }

.step-icon {
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
}

.approval-step h6    { font-size: 12px; margin-bottom: 5px; word-wrap: break-word; }
.approval-step small { font-size: 10px; display: block; }

@media (max-width: 768px) {
    .approval-steps .row { flex-wrap: wrap !important; }
    .approval-steps [class*="col-"] { flex: 1 1 50%; max-width: 50%; margin-bottom: 10px; }
}
</style>
@else
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle me-2"></i>No request data available.
</div>
@endif
