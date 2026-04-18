@props(['request' => null, 'title' => 'Request Progress'])

@if($request)
<div class="card mb-4" style="width: 100%;">
    <div class="card-header bg-primary text-white text-center">
        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>{{ $title }}</h5>
    </div>
    <div class="card-body" style="padding: 20px;">
        @php
            $progress  = $request->getApprovalProgress();
            $history   = $request->approval_history ?? [];

            // Determine which approval steps actually exist in this system
            $hasProgramHead   = \App\Models\User::where('role', 'program_head')->exists();
            $hasAcademicHead  = \App\Models\User::where('role', 'academic_head')->exists();
            $hasBuildingAdmin = \App\Models\User::where('role', 'building_admin')->exists();
            $hasSchoolAdmin   = \App\Models\User::whereIn('role', ['school_admin', 'mis'])->exists();

            // Build the ordered list of active steps only
            $steps = [];
            if ($hasProgramHead)   $steps[] = ['level' => 1, 'label' => $request->department ? ucfirst($request->department).' Dept. Head' : 'Program Head',  'field_approved' => 'approved_by_level_1', 'field_at' => 'approved_at_level_1'];
            if ($hasAcademicHead)  $steps[] = ['level' => 2, 'label' => 'Academic Head',  'field_approved' => 'approved_by_level_2', 'field_at' => 'approved_at_level_2'];
            if ($hasBuildingAdmin) $steps[] = ['level' => 3, 'label' => 'Building Admin', 'field_approved' => 'approved_by_level_3', 'field_at' => 'approved_at_level_3'];
            if ($hasSchoolAdmin)   $steps[] = ['level' => 4, 'label' => 'School Admin',   'field_approved' => 'approved_by',         'field_at' => 'approved_at'];

            $totalSteps = count($steps);

            // Recalculate progress percentage based on actual steps
            if (isset($progress['approved'])) {
                $doneSteps = $totalSteps;
            } elseif (isset($progress['rejected']) || isset($progress['cancelled'])) {
                $doneSteps = 0;
            } else {
                // Count how many active steps are done based on approval_level
                $doneSteps = 0;
                foreach ($steps as $s) {
                    if ($s['level'] === 4) {
                        // School admin: done only when fully approved
                        break;
                    }
                    if ($request->approval_level >= $s['level']) {
                        $doneSteps++;
                    }
                }
            }
            $pct = $totalSteps > 0 ? round(($doneSteps / $totalSteps) * 100) : 0;
            if (isset($progress['approved'])) $pct = 100;

            // Bootstrap col class based on step count
            $colClass = match($totalSteps) {
                1 => 'col-12',
                2 => 'col-6',
                3 => 'col-4',
                default => 'col-3',
            };
        @endphp

        <!-- Progress Bar -->
        <div class="mb-4" style="width: 100%;">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Approval Progress</span>
                <span class="fw-bold">
                    @if(isset($progress['approved']))
                        <span class="text-success"><i class="fas fa-check-circle"></i> Fully Approved</span>
                    @elseif(isset($progress['rejected']))
                        <span class="text-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                    @elseif(isset($progress['cancelled']))
                        <span class="text-secondary"><i class="fas fa-ban"></i> Cancelled</span>
                    @else
                        <span class="text-warning">{{ $doneSteps }} / {{ $totalSteps }}</span>
                    @endif
                </span>
            </div>
            <div class="progress" style="height: 25px; border-radius: 10px;">
                <div class="progress-bar
                    @if(isset($progress['approved'])) bg-success
                    @elseif(isset($progress['rejected'])) bg-danger
                    @elseif(isset($progress['cancelled'])) bg-secondary
                    @else bg-warning
                    @endif"
                    role="progressbar"
                    style="width: {{ $pct }}%; border-radius: 10px;"
                    aria-valuenow="{{ $pct }}"
                    aria-valuemin="0"
                    aria-valuemax="100">
                    {{ $pct }}%
                </div>
            </div>
        </div>

        <!-- Approval Steps (only active roles) -->
        <div class="approval-steps">
            <div class="row text-center g-2 justify-content-center">
                @foreach($steps as $idx => $step)
                    @php
                        $lvl         = $step['level'];
                        $approvedBy  = $lvl === 4 ? $request->approved_by        : $request->{$step['field_approved']};
                        $approvedAt  = $lvl === 4 ? $request->approved_at        : $request->{$step['field_at']};
                        $approverName = null;

                        if ($approvedBy) {
                            $approverUser = \App\Models\User::find($approvedBy);
                            $approverName = $approverUser?->name;
                            // Fallback: check history
                            if (!$approverName) {
                                foreach ($history as $h) {
                                    if ($h['level'] == $lvl) {
                                        $approverName = $h['approver'] ?? null;
                                        break;
                                    }
                                }
                            }
                        }

                        // Determine step state
                        $isDone     = false;
                        $isPending  = false;
                        $isRejected = false;

                        if ($lvl === 4) {
                            $isDone     = isset($progress['approved']);
                            $isRejected = isset($progress['rejected']) && $request->approval_level >= 4;
                            $isPending  = !$isDone && !$isRejected && $request->approval_level >= 3;
                        } else {
                            $isDone     = $request->approval_level >= $lvl || isset($progress['approved']);
                            $isRejected = isset($progress['rejected']) && $request->approval_level >= $lvl;
                            // Pending = previous step done but this one not yet
                            $prevLevel  = $idx > 0 ? $steps[$idx - 1]['level'] : 0;
                            $isPending  = !$isDone && !$isRejected && ($prevLevel === 0 || $request->approval_level >= $prevLevel);
                        }

                        // Rejection note from history
                        $rejectedBy = null;
                        $rejectedAt = null;
                        if ($isRejected) {
                            foreach ($history as $h) {
                                if (($h['level'] ?? null) == $lvl && isset($h['status']) && strtolower($h['status']) === 'rejected') {
                                    $rejectedBy = $h['approver'] ?? null;
                                    $rejectedAt = $h['at'] ?? null;
                                    break;
                                }
                            }
                            // Also check top-level rejection history
                            if (!$rejectedBy) {
                                foreach ($history as $h) {
                                    if (($h['level'] ?? null) == $lvl) {
                                        $rejectedBy = $h['approver'] ?? null;
                                        $rejectedAt = $h['at'] ?? null;
                                    }
                                }
                            }
                        }
                    @endphp

                    <div class="{{ $colClass }}">
                        <div class="approval-step {{ $isDone ? 'approved' : ($isRejected ? 'rejected' : ($isPending ? 'pending' : '')) }}">
                            <div class="step-icon mb-2">
                                @if($isDone)
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                @elseif($isRejected)
                                    <i class="fas fa-times-circle fa-2x text-danger"></i>
                                @elseif($isPending)
                                    <i class="fas fa-clock fa-2x text-warning"></i>
                                @else
                                    <i class="fas fa-clock fa-2x text-muted"></i>
                                @endif
                            </div>
                            <h6>{{ $step['label'] }}</h6>
                            <small class="text-muted">
                                @if($approvedBy && $approvedAt)
                                    Approved by {{ $approverName ?? 'N/A' }}<br>
                                    {{ \Carbon\Carbon::parse($approvedAt)->format('M d, Y h:i A') }}
                                @elseif($isRejected && $rejectedBy)
                                    Rejected by {{ $rejectedBy }}<br>
                                    @if($rejectedAt){{ \Carbon\Carbon::parse($rejectedAt)->format('M d, Y h:i A') }}@endif
                                @elseif($isPending)
                                    Waiting for Approval
                                @else
                                    Pending Previous Level
                                @endif
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Current Status Badge -->
        <div class="mt-4 text-center">
            <span class="badge bg-{{
                $request->status == 'Approved'  ? 'success'   :
                ($request->status == 'Rejected'  ? 'danger'    :
                ($request->status == 'Cancelled' ? 'secondary' : 'warning'))
            }} fs-6">
                <i class="fas fa-{{
                    $request->status == 'Approved'  ? 'check' :
                    ($request->status == 'Rejected'  ? 'times' :
                    ($request->status == 'Cancelled' ? 'ban'   : 'clock'))
                }} me-1"></i>
                {{ $request->status }}
            </span>

            @if($request->status == 'Approved')
            <div class="mt-3">
                <a href="{{ route('events.pdf', $request->id) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Event Discussion Chat -->
<x-event-discussion-chat :eventId="$request->id" />

<style>
.approval-steps { width: 100%; }

.approval-steps .row {
    display: flex !important;
    justify-content: center !important;
    flex-wrap: nowrap;
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
