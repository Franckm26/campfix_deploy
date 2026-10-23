<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Concern;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TicketRecurrenceService
{
    /** Completed repairs preserved when a ticket was reopened, keyed by ticket ID. */
    public function histories(Collection $tickets): Collection
    {
        return $this->forCurrentAssets($tickets)['histories'];
    }

    public function forCurrentAssets(Collection $tickets): array
    {
        $histories = $this->allHistories($tickets);
        $service = app(AssetReplacementService::class);
        $replacements = $service->latestFor($tickets);
        $current = $tickets->filter(function ($ticket) use ($service, $replacements, $histories) {
            $replacement = $replacements->get($service->scopeKey($ticket));
            if (!$replacement) return true;
            $baseline = $replacement->metadata[$ticket instanceof Report ? 'baseline_report_ids' : 'baseline_concern_ids'] ?? [];
            $events = $histories->get($ticket->id, collect())->filter(fn ($event) => $event['log_id'] > $replacement->id)->values();
            if (in_array((int) $ticket->id, array_map('intval', $baseline), true)) {
                if ($events->isEmpty()) {
                    $histories->put($ticket->id, collect());
                    return false;
                }
                // The first later reopening is the new asset's first failure;
                // its saved resolution still belongs to the previous asset.
                $events->shift();
            }
            $histories->put($ticket->id, $events);
            return true;
        });
        return ['tickets' => $current, 'histories' => $histories];
    }

    private function allHistories(Collection $tickets): Collection
    {
        $result = $tickets->mapWithKeys(fn ($ticket) => [$ticket->id => collect()]);
        if ($tickets->isEmpty() || !Schema::hasTable('activity_logs')) return $result;
        $reportIds = $tickets->filter(fn ($ticket) => $ticket instanceof Report)->pluck('id');
        $concernIds = $tickets->map(fn ($ticket) => $ticket instanceof Concern ? $ticket->id : $ticket->concern_id)->filter()->unique();
        $hasReportId = Schema::hasColumn('activity_logs', 'report_id');
        $hasConcernId = Schema::hasColumn('activity_logs', 'concern_id');
        $logs = ActivityLog::where(function ($query) use ($reportIds, $concernIds, $hasReportId, $hasConcernId) {
            $query->whereRaw('1 = 0');
            if ($hasReportId && $reportIds->isNotEmpty()) {
                $query->orWhere(fn ($q) => $q->where('action', 'report_reopened')->whereIn('report_id', $reportIds));
            }
            if ($hasConcernId && $concernIds->isNotEmpty()) {
                $query->orWhere(fn ($q) => $q->where('action', 'concern_reopened')->whereIn('concern_id', $concernIds));
            }
        })->orderBy('id')->get();
        $reportLogs = $logs->where('action', 'report_reopened')->groupBy('report_id');
        $concernLogs = $logs->where('action', 'concern_reopened')->groupBy('concern_id');
        foreach ($tickets as $ticket) {
            $concernId = $ticket instanceof Concern ? $ticket->id : $ticket->concern_id;
            $own = $ticket instanceof Report ? $reportLogs->get($ticket->id, collect()) : collect();
            $events = collect();
            foreach ($concernLogs->get($concernId, collect()) as $log) {
                if ($entry = $this->entry($log, $ticket)) $events->put('concern:'.$log->id, $entry);
            }
            $paired = [];
            foreach ($own as $log) {
                $entry = $this->entry($log, $ticket);
                if (!$entry) continue;
                $match = $events->search(function ($candidate, $key) use ($entry, $paired) {
                    if (!str_starts_with($key, 'concern:') || isset($paired[$key])
                        || $candidate['actor'] !== $entry['actor'] || $candidate['reason'] !== $entry['reason']) return false;
                    if ($candidate['resolved_at'] && $entry['resolved_at']) return $candidate['resolved_at']->equalTo($entry['resolved_at']);
                    // Legacy snapshots can omit the resolution timestamp.
                    return abs($candidate['reopened_at']->getTimestamp() - $entry['reopened_at']->getTimestamp()) <= 30;
                });
                if ($match !== false) {
                    $paired[$match] = true;
                    if (!$events[$match]['resolved_at'] && $entry['resolved_at']) {
                        $events->put($match, array_replace($events[$match], ['resolved_at' => $entry['resolved_at']]));
                    }
                } else {
                    $events->put('report:'.$log->id, $entry);
                }
            }
            $result->put($ticket->id, $events->map(fn ($entry, $key) => [
                'key' => $key, 'log_id' => $entry['log_id'], 'resolved_at' => $entry['resolved_at'], 'reopened_at' => $entry['reopened_at'],
            ])->values());
        }
        return $result;
    }

    private function entry(ActivityLog $log, Concern|Report $ticket): ?array
    {
        $saved = $log->old_values ?? [];
        if (strtolower($saved['status'] ?? 'Resolved') !== 'resolved') return null;
        if ($this->normalize($saved['title'] ?? $ticket->title) !== $this->normalize($ticket->title)
            || $this->normalize($saved['location'] ?? $ticket->location) !== $this->normalize($ticket->location)) return null;
        return ['log_id' => $log->id, 'resolved_at' => $this->date($saved['resolved_at'] ?? null), 'reopened_at' => $log->created_at,
            'actor' => $log->user_id, 'reason' => $log->metadata['reason'] ?? $log->description];
    }

    private function normalize(?string $value): string
    {
        return strtolower(trim((string) $value));
    }

    private function date($value): ?Carbon
    {
        if (!$value) return null;
        try { return Carbon::parse($value)->setTimezone(config('app.timezone', 'UTC')); } catch (\Throwable) { return null; }
    }
}
