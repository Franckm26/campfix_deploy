<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
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

        $source = $ticket;
        $type = $source instanceof Concern ? 'concern' : 'report';
        $current = $this->snapshot($source);
        $logs = ActivityLog::with('user')
            ->where($type.'_id', $source->id)
            ->where('action', $type.'_reopened')
            ->orderByDesc('id')->get();
        // Reports created after an earlier reopening may only have concern history.
        if ($logs->isEmpty() && $ticket instanceof Report && $ticket->concern_id) {
            $logs = ActivityLog::with('user')->where('concern_id', $ticket->concern_id)
                ->where('action', 'concern_reopened')->orderByDesc('id')->get();
        }
        $history = $logs->map(function (ActivityLog $log) use ($current) {
            $saved = $log->old_values ?? [];
            if (! array_key_exists('assignee_name', $saved)) {
                $technology = strtolower(trim($saved['category_name'] ?? $current['category_name'] ?? '')) === 'technology/internet';
                $saved['assignee_name'] = $this->assigneeName($saved['assigned_to'] ?? null, $technology);
            }

            return array_merge($this->details(array_replace($current, $saved)), [
                'label' => 'Previous repair',
                'legacy' => ! isset($saved['snapshot_version']),
                'reopened_at' => $this->date($log->created_at),
                'reopened_by' => ! empty($saved['is_anonymous']) && (int) $log->user_id === (int) ($saved['user_id'] ?? 0)
                    ? 'Anonymous' : ($log->user?->name ?? 'Unavailable user'),
                'reason' => $log->metadata['reason'] ?? $log->description,
            ]);
        });

        if ($source->status === 'Resolved' && $source->resolved_at) {
            $history->prepend(array_merge($this->details($current), [
                'label' => 'Latest resolution', 'reopened_at' => null, 'legacy' => false,
            ]));
        }

        return view('partials.ticket-history', ['history' => $history])->render();
    }

    public function snapshot(Concern|Report $ticket): array
    {
        $other = $ticket instanceof Report
            ? $ticket->concern
            : Report::where('concern_id', $ticket->id)->latest('id')->first();
        $fields = ['id', 'title', 'description', 'details', 'location', 'location_type', 'room_number',
            'category_id', 'user_id', 'reporter_name', 'reported_by_name', 'reporter_email', 'reporter_role',
            'reporter_department', 'reporter_phone', 'reporter_student_id', 'is_anonymous', 'status',
            'priority', 'severity', 'is_safety_hazard', 'report_count', 'image_path', 'photo_path',
            'created_at', 'assigned_to', 'assigned_at', 'in_progress_at', 'resolved_at', 'resolution_notes',
            'cost', 'damaged_part', 'replaced_part', 'budget_amount', 'budget_status', 'budget_requested_by',
            'budget_requested_at', 'budget_reviewed_by', 'budget_reviewed_at', 'budget_rejection_reason'];
        $values = $ticket->only($fields);
        foreach ($fields as $field) {
            if ($field !== 'id' && $values[$field] === null && $other?->getAttribute($field) !== null) {
                $values[$field] = $other->getAttribute($field);
            }
        }
        $values['snapshot_version'] = 1;
        $values['reference'] = ($ticket instanceof Concern ? 'Concern #' : 'Report #').str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT);
        $values['category_name'] = Category::find($values['category_id'])?->name;
        $technology = strtolower(trim($values['category_name'] ?? '')) === 'technology/internet';
        $values['assignee_name'] = $this->assigneeName($values['assigned_to'], $technology);
        $reporter = $ticket->user;
        $values['reporter_name'] ??= $values['reported_by_name'] ?? $reporter?->name;
        foreach (['email', 'role', 'department', 'phone', 'student_id'] as $field) {
            $values['reporter_'.$field] ??= $reporter?->getAttribute($field);
        }
        $values['budget_requested_by_name'] = $values['budget_requested_by'] ? User::withoutGlobalScopes()->find($values['budget_requested_by'])?->name : null;
        $values['budget_reviewed_by_name'] = $values['budget_reviewed_by'] ? User::withoutGlobalScopes()->find($values['budget_reviewed_by'])?->name : null;

        return $values;
    }

    private function assigneeName($id, bool $technology): ?string
    {
        if (! $id) {
            return null;
        }
        // Legacy assignments can reference either accounts or maintenance staff.
        $user = User::withoutGlobalScopes()->find($id);
        $staff = MaintenanceStaff::withTrashed()->find($id);

        return $technology ? ($user?->name ?? $staff?->name) : ($staff?->name ?? $user?->name);
    }

    private function details(array $values): array
    {
        $anonymous = (bool) ($values['is_anonymous'] ?? false);
        $photo = $values['photo_path'] ?? $values['image_path'] ?? null;
        $photoUrl = null;
        if ($photo) {
            if (preg_match('~^https?://~i', $photo)) {
                $photoUrl = $photo;
            } elseif (! preg_match('~^[a-z][a-z0-9+.-]*:|^//~i', $photo)) {
                $photoUrl = asset('storage/'.ltrim($photo, '/'));
            }
        }

        return array_merge($values, [
            'reported_by' => $anonymous ? 'Anonymous' : ($values['reporter_name'] ?? 'Unavailable user'),
            'reporter_contact' => $anonymous ? [] : array_filter([
                'Email' => $values['reporter_email'], 'Role' => $values['reporter_role'],
                'Department' => $values['reporter_department'], 'Phone' => $values['reporter_phone'],
                'Student ID' => $values['reporter_student_id'],
            ], fn ($value) => $value !== null && $value !== ''),
            'assigned_to' => $values['assignee_name'] ?? ($values['assigned_to'] ? 'Unavailable assignee (#'.$values['assigned_to'].')' : 'Unassigned'),
            'priority' => ucfirst($values['priority'] ?? $values['severity'] ?? 'Not set'),
            'report_count' => $values['report_count'] ?? 1,
            'photo_url' => $photoUrl,
            'created_at' => $this->date($values['created_at']),
            'assigned_at' => $this->date($values['assigned_at'] ?? null),
            'in_progress_at' => $this->date($values['in_progress_at'] ?? null),
            'resolved_at' => $this->date($values['resolved_at'] ?? null),
            'resolution_notes' => $values['resolution_notes'] ?? null,
            'cost' => $values['cost'] ?? null,
            'damaged_part' => $values['damaged_part'] ?? null,
            'replaced_part' => $values['replaced_part'] ?? null,
            'budget_requested_at' => $this->date($values['budget_requested_at']),
            'budget_reviewed_at' => $this->date($values['budget_reviewed_at']),
        ]);
    }

    private function date($value): ?string
    {
        return $value ? Carbon::parse($value)->timezone('Asia/Manila')->format('M d, Y h:i A') : null;
    }
}
