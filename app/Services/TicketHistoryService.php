<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Concern;
use App\Models\MaintenanceStaff;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;

class TicketHistoryService
{
    // Call after the detail endpoint has authorized access to the ticket itself.
    public function renderFor(Concern|Report $ticket, User $viewer): string
    {
        if (! $viewer->isSystemAdministrator()
            && ! in_array($viewer->role, ['mis', 'building_admin', 'school_admin'], true)
            && ! ($viewer->role === 'maintenance' && (int) $ticket->assigned_to === (int) $viewer->id)) {
            return '';
        }

        // Linked concerns/reports log the same reopening twice. Use one canonical history.
        $source = $ticket instanceof Report && $ticket->concern_id ? $ticket->concern : $ticket;
        $source ??= $ticket;
        $type = $source instanceof Concern ? 'concern' : 'report';
        $category = $source instanceof Concern ? $source->categoryRelation : $source->category;
        $technology = strtolower(trim($category?->name ?? '')) === 'technology/internet';
        $history = ActivityLog::with('user')
            ->where($type.'_id', $source->id)
            ->where('action', $type.'_reopened')
            ->orderByDesc('id')->get()
            ->map(function (ActivityLog $log) use ($technology) {
                return array_merge($this->repair($log->old_values ?? [], $technology), [
                    'label' => 'Previous repair',
                    'reopened_at' => $this->date($log->created_at),
                    'reopened_by' => $log->user?->name ?? 'Unavailable user',
                    'reason' => $log->metadata['reason'] ?? $log->description,
                ]);
            });

        if ($source->status === 'Resolved' && $source->resolved_at) {
            $history->prepend(array_merge($this->repair($source->getAttributes(), $technology), [
                'label' => 'Latest resolution', 'reopened_at' => null,
            ]));
        }

        return view('partials.ticket-history', ['history' => $history])->render();
    }

    private function repair(array $values, bool $technology): array
    {
        $assignee = null;
        if (! empty($values['assigned_to'])) {
            $assignee = $technology
                ? User::find($values['assigned_to'])?->name
                : MaintenanceStaff::withTrashed()->find($values['assigned_to'])?->name;
        }

        return [
            'assigned_to' => $assignee ?? 'Unassigned / unavailable',
            'assigned_at' => $this->date($values['assigned_at'] ?? null),
            'resolved_at' => $this->date($values['resolved_at'] ?? null),
            'resolution_notes' => $values['resolution_notes'] ?? null,
            'cost' => $values['cost'] ?? null,
            'damaged_part' => $values['damaged_part'] ?? null,
            'replaced_part' => $values['replaced_part'] ?? null,
        ];
    }

    private function date($value): ?string
    {
        return $value ? Carbon::parse($value)->timezone('Asia/Manila')->format('M d, Y h:i A') : null;
    }
}
