<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Concern;
use App\Models\Report;
use App\Models\User;
use App\Notifications\TicketReopenedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ReopenTicketService
{
    public function reopen(Concern|Report $ticket, User $actor, string $reason): Concern|Report
    {
        if (trim($reason) === '') {
            throw ValidationException::withMessages(['reason' => 'Please explain why this ticket needs to be reopened.']);
        }

        return DB::transaction(function () use ($ticket, $actor, $reason) {
            // Always lock the concern first, including requests through a linked report.
            $concernId = $ticket instanceof Concern ? $ticket->id : $ticket->concern_id;
            $concern = $concernId ? Concern::whereKey($concernId)->lockForUpdate()->firstOrFail() : null;
            $reports = $concern
                ? Report::where('concern_id', $concern->id)->orderBy('id')->lockForUpdate()->get()
                : collect([Report::whereKey($ticket->id)->lockForUpdate()->firstOrFail()]);
            $primary = $concern ?? $reports->first();

            abort_unless((int) $primary->user_id === (int) $actor->id, 403, 'Only the original reporter can reopen this ticket.');

            $records = $concern ? collect([$concern])->merge($reports) : $reports;
            foreach ($records as $record) {
                if ($record->status !== 'Resolved' || $record->is_deleted || $record->deleted_at) {
                    throw ValidationException::withMessages(['reason' => 'Only resolved tickets that have not been deleted can be reopened.']);
                }
            }

            $assignee = $primary->assigned_to ?: $reports->first()?->assigned_to;
            $status = $assignee ? 'Assigned' : 'Pending';
            foreach ($records as $record) {
                $old = $record->only(['status', 'resolved_at', 'resolution_notes', 'cost', 'damaged_part', 'replaced_part', 'assigned_to', 'assigned_at', 'in_progress_at']);
                $record->status = $status;
                $record->assigned_to = $assignee;
                $record->assigned_at = $assignee ? now() : null;
                $record->resolved_at = null;
                $record->resolution_notes = null;
                // Keep recorded spending and parts; the full previous resolution is also in history.
                foreach ($record->getAttributes() as $key => $value) {
                    if ($key === 'is_archived' || str_ends_with($key, '_archived')) {
                        $record->setAttribute($key, false);
                    }
                    if (in_array($key, ['archived_at', 'archived_by', 'archive_folder_id', 'auto_delete_at', 'follow_up_sent_at', 'in_progress_at'], true)) {
                        $record->setAttribute($key, null);
                    }
                    if ($key === 'follow_up_sent') {
                        $record->setAttribute($key, false);
                    }
                }
                $record->save();
                if (Schema::hasTable('user_archived_'.$record->getTable())) {
                    $record->archivedByUsers()->detach();
                }

                $type = $record instanceof Concern ? 'concern' : 'report';
                ActivityLog::log($type.'_reopened', 'Ticket reopened: '.$reason, $record->id, $type,
                    $old, $record->only(array_keys($old)), ['reason' => $reason]);
            }

            DB::afterCommit(function () use ($primary, $records, $actor, $reason) {
                try {
                    $this->notify($primary, $records, $actor, $reason);
                } catch (\Throwable $exception) {
                    Log::warning('Ticket reopening notifications failed', ['ticket_id' => $primary->id, 'error' => $exception->getMessage()]);
                }
            });

            return $ticket instanceof Concern ? $concern : $reports->firstWhere('id', $ticket->id);
        });
    }

    private function notify(Concern|Report $ticket, $records, User $actor, string $reason): void
    {
        $category = $ticket instanceof Concern ? $ticket->categoryRelation : $ticket->category;
        $role = strtolower(trim($category?->name ?? '')) === 'technology/internet' ? 'mis' : 'building_admin';
        $recipients = User::where('role', $role)->where('is_archived', false)->get()->push($actor);
        foreach ($records as $record) {
            if ($record->assigned_to) {
                // Technology assignments reference users; maintenance assignments reference staff.
                $assignedUser = $role === 'mis'
                    ? User::find($record->assigned_to)
                    : ($record->assignedTo?->email ? User::where('email', $record->assignedTo->email)->first() : null);
                $recipients->push($assignedUser);
            }
        }
        if ($ticket instanceof Concern && Concern::supportsLinkedReporters()) {
            $recipients = $recipients->merge($ticket->linkedUsers);
        }
        foreach ($recipients->filter()->unique(fn ($recipient) => get_class($recipient).':'.$recipient->id) as $recipient) {
            try {
                $recipient->notify(new TicketReopenedNotification($ticket, $actor->name, $reason));
            } catch (\Throwable $exception) {
                Log::warning('Ticket reopening notification failed', ['ticket_id' => $ticket->id, 'error' => $exception->getMessage()]);
            }
        }
    }
}
