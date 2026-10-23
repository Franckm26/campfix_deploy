<?php

namespace App\Services;

use App\Models\EventRequest;
use App\Models\User;

class FacilityApprovalProgress
{
    public function forRequest(EventRequest $request): array
    {
        $labels = ['program_head' => 'Program Head', 'principal_assistant' => 'Principal Assistant',
            'academic_head' => 'Academic Head', 'building_admin' => 'Building Admin', 'school_admin' => 'School Administrator'];
        $configured = $request->hasConfiguredApprovalRoute();
        if ($configured) {
            $roles = array_combine(range(1, count($request->approval_route)), $request->approval_route);
        } elseif (is_array($request->approval_route) && $request->approval_route === [] && $request->status === 'Approved') {
            $roles = [];
        } elseif ($request->education_level === 'shs') {
            $roles = [1 => 'principal_assistant', 2 => 'academic_head', 4 => 'school_admin'];
        } elseif ($request->request_type === 'Non-Academic') {
            $roles = [3 => 'building_admin', 4 => 'school_admin'];
        } else {
            $roles = [1 => 'program_head', 2 => 'academic_head', 3 => 'building_admin', 4 => 'school_admin'];
        }
        $history = collect($request->approval_history ?? []);
        $steps = [];
        foreach ($roles as $level => $role) {
            $entry = $history->filter(fn ($item) => (int) ($item['level'] ?? 0) === $level)->last() ?? [];
            $action = strtolower($entry['status'] ?? $entry['action'] ?? 'approved');
            $field = $level === 4 ? 'approved_by' : 'approved_by_level_'.$level;
            $dateField = $level === 4 ? 'approved_at' : 'approved_at_level_'.$level;
            $approver = $entry['approver'] ?? null;
            $approverId = $entry['approver_id'] ?? (!$configured ? $request->{$field} : null);
            $date = $entry['at'] ?? (!$configured ? $request->{$dateField} : null);
            $rejected = $request->status === 'Rejected' && $action === 'rejected' && $entry !== [];
            $done = !$rejected && ($request->status === 'Approved' || ($action === 'approved' && filled($approverId)));
            $state = $done ? 'approved' : ($rejected ? 'rejected' : ($request->status === 'Pending' && (int) $request->approval_level === $level ? 'pending' : 'waiting'));
            $steps[] = ['level' => $level, 'role' => $role, 'label' => $labels[$role] ?? ucwords(str_replace('_', ' ', $role)),
                'state' => $state, 'approver' => $approver, 'approver_id' => $approverId, 'at' => $date];
        }
        $ids = collect($steps)->whereNull('approver')->pluck('approver_id')->filter()->unique();
        $names = $ids->isNotEmpty() ? User::whereIn('id', $ids)->pluck('name', 'id') : collect();
        foreach ($steps as &$step) $step['approver'] ??= $names->get($step['approver_id']);
        unset($step);
        $total = count($steps);
        $done = collect($steps)->where('state', 'approved')->count();
        return ['steps' => $steps, 'total' => $total, 'completed' => $done,
            'percentage' => $request->status === 'Approved' ? 100 : ($total ? (int) round($done / $total * 100) : 0)];
    }
}
