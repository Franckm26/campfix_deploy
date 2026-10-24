<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Concern;
use App\Models\MaintenanceStaff;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssetReplacementService
{
    public function scopeKey(Concern|Report $ticket): string
    {
        return hash('sha256', json_encode([$ticket->category_id, strtolower(trim((string) $ticket->title)), strtolower(trim((string) $ticket->location))]));
    }

    public function canRecord(Concern|Report $ticket, User $user): bool
    {
        if ($ticket->is_deleted || $ticket->status !== 'Resolved' || !filled($ticket->title) || !filled($ticket->location)) return false;
        if ($user->isSystemAdministrator() || $user->role === 'school_admin') return true;
        $category = $ticket instanceof Concern ? $ticket->categoryRelation : $ticket->category;
        $technology = strtolower(trim($category?->name ?? '')) === 'technology/internet';
        if ($user->role === 'mis') return $technology;
        if ($user->role === 'building_admin') return !$technology;
        if ($user->role !== 'maintenance' || !$ticket->assigned_to || $technology) return false;
        $staff = MaintenanceStaff::withTrashed()->find($ticket->assigned_to);
        return $staff ? filled($staff->email) && strcasecmp($staff->email, $user->email) === 0 : (int) $ticket->assigned_to === (int) $user->id;
    }

    public function canRecordOnResolution(Concern|Report $ticket, User $user): bool
    {
        $resolved = clone $ticket;
        $resolved->status = 'Resolved';
        return $this->canRecord($resolved, $user);
    }

    public function latestFor(Collection $tickets): Collection
    {
        if ($tickets->isEmpty() || !Schema::hasTable('activity_logs')) return collect();
        return ActivityLog::with('user')->where('action', 'asset_replaced')
            ->whereIn('metadata->asset_scope_key', $tickets->map(fn ($ticket) => $this->scopeKey($ticket))->unique()->values())
            ->orderByDesc('id')->get()->unique(fn ($log) => $log->metadata['asset_scope_key'])->keyBy(fn ($log) => $log->metadata['asset_scope_key']);
    }

    public function historyFor(Concern|Report $ticket): Collection
    {
        return ActivityLog::with('user')->where('action', 'asset_replaced')
            ->where('metadata->asset_scope_key', $this->scopeKey($ticket))->orderByDesc('id')->get();
    }

    public function record(Concern|Report $ticket, User $user, string $details, string $notes, string $operationId): ActivityLog
    {
        return DB::transaction(function () use ($ticket, $user, $details, $notes, $operationId) {
            // Match the lock order used by reopening a linked concern/report.
            $concernId = $ticket instanceof Concern ? $ticket->id : $ticket->concern_id;
            if ($concernId) Concern::whereKey($concernId)->lockForUpdate()->firstOrFail();
            $model = $ticket instanceof Concern ? Concern::class : Report::class;
            $ticket = $model::whereKey($ticket->id)->lockForUpdate()->firstOrFail();
            abort_unless($this->canRecord($ticket, $user), 403, 'Only authorized staff can record a replacement on a resolved ticket.');
            $scope = $this->scopeKey($ticket);
            $existing = ActivityLog::where('action', 'asset_replaced')->where('metadata->operation_id', $operationId)->first();
            if ($existing) {
                abort_unless(($existing->metadata['asset_scope_key'] ?? '') === $scope && (int) $existing->user_id === (int) $user->id, 403);
                return $existing;
            }
            $match = function ($query) use ($ticket) {
                $query->where('category_id', $ticket->category_id)
                    ->whereRaw('LOWER(TRIM(title)) = ?', [strtolower(trim($ticket->title))])
                    ->whereRaw('LOWER(TRIM(location)) = ?', [strtolower(trim($ticket->location))]);
            };
            $concerns = Concern::where($match)->pluck('id')->all();
            $reports = Report::withTrashed()->where($match)->pluck('id')->all();
            $type = $ticket instanceof Concern ? 'concern' : 'report';
            return ActivityLog::log('asset_replaced', 'Asset replaced: '.$details, $ticket->id, $type,
                app(TicketHistoryService::class)->snapshot($ticket), ['replacement_details' => $details, 'replacement_notes' => $notes], [
                    'asset_scope_key' => $scope, 'operation_id' => $operationId,
                    'baseline_concern_ids' => $concerns, 'baseline_report_ids' => $reports,
                    'replacement_details' => $details, 'replacement_notes' => $notes,
                    'issue' => $ticket->title, 'location' => $ticket->location, 'category_id' => $ticket->category_id,
                ]);
        });
    }
}
