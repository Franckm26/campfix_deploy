<?php

namespace App\Http\Controllers;

use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RoleAnalyticsController extends Controller
{
    private const APPROVAL_ROLES = [
        'program_head',
        'academic_head',
        'school_admin',
        'principal_assistant',
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user && ($user->role === 'mis' || in_array($user->role, self::APPROVAL_ROLES, true)), 403);

        $filters = $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        return $user->role === 'mis'
            ? $this->misAnalytics($request, $filters)
            : $this->approvalAnalytics($request, $filters);
    }

    private function misAnalytics(Request $request, array $filters)
    {
        $user = $request->user();
        // Use the same Technology/Internet concern source as the MIS Task module,
        // so dashboard totals always reconcile with the work queue.
        $query = Concern::with('categoryRelation')
            ->whereHas('categoryRelation', function ($category) {
                $category->whereRaw('LOWER(TRIM(name)) = ?', ['technology/internet']);
            });
        $this->applyDates($query, $filters);
        if (Schema::hasColumn('concerns', 'is_deleted')) {
            $query->where('is_deleted', false);
        }

        $reports = $query->latest()->get();
        $misAssignees = User::where('role', 'mis')
            ->whereIn('id', $reports->pluck('assigned_to')->filter()->unique())
            ->pluck('name', 'id');
        $activeUsers = User::hideSuperadmin()->where('is_archived', false)->count();
        $misUsers = User::where('role', 'mis')->where('is_archived', false)->count();
        $lockedUsers = Schema::hasColumn('users', 'locked_until')
            ? User::hideSuperadmin()->whereNotNull('locked_until')->where('locked_until', '>', now())->count()
            : 0;
        $mine = $reports->where('assigned_to', $user->id);

        $metrics = [
            ['label' => 'Active users', 'value' => $activeUsers, 'context' => 'Accounts currently available in CampFix', 'icon' => 'fa-users', 'color' => '#1769e0'],
            ['label' => 'MIS team', 'value' => $misUsers, 'context' => 'Active MIS user accounts', 'icon' => 'fa-user-shield', 'color' => '#6f42c1'],
            ['label' => 'Technology/Internet', 'value' => $reports->count(), 'context' => $reports->where('status', '!=', 'Resolved')->count().' open MIS task(s)', 'icon' => 'fa-laptop-code', 'color' => '#e99a00'],
            ['label' => 'My assigned tasks', 'value' => $mine->count(), 'context' => $mine->where('status', 'Resolved')->count().' resolved by you', 'icon' => 'fa-list-check', 'color' => '#148a58'],
        ];

        $statusStats = $this->statusStats($reports);
        $trendStats = $this->monthlyTrend($reports, 'created_at', fn ($item) => strtolower((string) $item->status) === 'resolved');
        $roleStats = User::hideSuperadmin()->where('is_archived', false)->get()
            ->groupBy('role')->map(fn ($items, $role) => ['label' => $this->roleLabel($role), 'count' => $items->count()])
            ->sortByDesc('count')->values();

        $summary = [
            'title' => 'MIS Executive Summary',
            'scope' => 'User administration and Technology/Internet operations',
            'items' => [
                "{$activeUsers} active user account(s) are currently managed, including {$misUsers} MIS account(s).",
                $lockedUsers > 0 ? "{$lockedUsers} account(s) are currently locked and require a security review." : 'No active account lockouts require attention.',
                $reports->where('status', '!=', 'Resolved')->count().' Technology/Internet task(s) remain open.',
                $mine->where('status', '!=', 'Resolved')->count().' of the open MIS task(s) are assigned to you.',
            ],
        ];

        return view('admin.role-analytics', [
            'mode' => 'mis',
            'pageTitle' => 'MIS Analytics',
            'pageSubtitle' => 'User administration and Technology/Internet task performance',
            'metrics' => $metrics,
            'statusStats' => $statusStats,
            'trendStats' => $trendStats,
            'secondaryStats' => $roleStats,
            'secondaryTitle' => 'Users by Role',
            'recentItems' => $reports->take(12),
            'misAssignees' => $misAssignees,
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    private function approvalAnalytics(Request $request, array $filters)
    {
        $user = $request->user();
        $query = EventRequest::with('user');
        if (Schema::hasColumn('event_requests', 'is_deleted')) {
            $query->where('is_deleted', false);
        }
        $this->applyDates($query, $filters);
        $allEvents = $query->latest()->get();

        $events = $allEvents->filter(function (EventRequest $event) use ($user) {
            $route = $this->approvalRouteFor($event);
            $history = collect($event->approval_history ?? []);
            $participates = $route->contains($user->role)
                || $this->currentApprovalRoleFor($event) === $user->role
                || $history->contains(fn ($approval) => (int) ($approval['approver_id'] ?? 0) === (int) $user->id)
                || in_array((int) $user->id, array_map('intval', array_filter([
                    $event->approved_by_level_1,
                    $event->approved_by_level_2,
                    $event->approved_by_level_3,
                    $event->approved_by,
                ])), true);

            if ($user->role === 'program_head' && filled($user->department)) {
                return $participates && strcasecmp((string) $event->department, (string) $user->department) === 0;
            }

            if ($user->role === 'principal_assistant') {
                return $participates && (($event->intended_user ?? $event->education_level) === 'shs');
            }

            return $participates;
        })->values();

        $awaiting = $events->filter(fn (EventRequest $event) => $event->status === 'Pending' && $this->currentApprovalRoleFor($event) === $user->role);
        $handled = $events->filter(fn (EventRequest $event) => collect($event->approval_history ?? [])->contains(
            fn ($approval) => (int) ($approval['approver_id'] ?? 0) === (int) $user->id
        ));
        $approved = $events->where('status', 'Approved');
        $rejected = $events->where('status', 'Rejected');

        $metrics = [
            ['label' => 'Relevant requests', 'value' => $events->count(), 'context' => 'Requests in your approval responsibility', 'icon' => 'fa-calendar-check', 'color' => '#1769e0'],
            ['label' => 'Awaiting your approval', 'value' => $awaiting->count(), 'context' => 'Requests requiring your decision now', 'icon' => 'fa-hourglass-half', 'color' => '#e99a00'],
            ['label' => 'Decisions recorded', 'value' => $handled->count(), 'context' => 'Requests you have already reviewed', 'icon' => 'fa-clipboard-check', 'color' => '#6f42c1'],
            ['label' => 'Fully approved', 'value' => $approved->count(), 'context' => $events->count() ? round($approved->count() / $events->count() * 100, 1).'% of relevant requests' : 'No requests in this period', 'icon' => 'fa-circle-check', 'color' => '#148a58'],
        ];

        $statusStats = $this->statusStats($events);
        $trendStats = $this->monthlyTrend($events, 'created_at', fn ($item) => $item->status === 'Approved');
        $typeStats = $events->groupBy(fn ($event) => $event->request_type ?: 'Unspecified')
            ->map(fn ($items, $type) => ['label' => $type, 'count' => $items->count()])
            ->sortByDesc('count')->values();
        $roleName = $this->roleLabel($user->role);

        $summary = [
            'title' => $roleName.' Executive Summary',
            'scope' => 'Event request approvals relevant to your role',
            'items' => [
                "{$events->count()} event request(s) fall within the {$roleName} approval scope.",
                $awaiting->count() ? $awaiting->count().' request(s) require your decision now.' : 'No requests are currently waiting for your approval.',
                "{$handled->count()} request(s) contain a decision recorded by you.",
                "{$approved->count()} request(s) are fully approved and {$rejected->count()} are rejected.",
            ],
        ];

        return view('admin.role-analytics', [
            'mode' => 'approval',
            'pageTitle' => $roleName.' Analytics',
            'pageSubtitle' => 'Event request workload, decisions, and approval outcomes',
            'metrics' => $metrics,
            'statusStats' => $statusStats,
            'trendStats' => $trendStats,
            'secondaryStats' => $typeStats,
            'secondaryTitle' => 'Requests by Type',
            'recentItems' => $events->take(12),
            'misAssignees' => collect(),
            'summary' => $summary,
            'filters' => $filters,
        ]);
    }

    private function applyDates($query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }

    private function statusStats(Collection $items): Collection
    {
        return $items->groupBy(fn ($item) => $item->status ?: 'Unknown')
            ->map(fn ($group, $status) => ['label' => $status, 'count' => $group->count()])
            ->sortByDesc('count')->values();
    }

    private function monthlyTrend(Collection $items, string $dateField, callable $completed): Collection
    {
        $start = now()->startOfMonth()->subMonths(5);

        return collect(range(0, 5))->map(function ($offset) use ($items, $dateField, $completed, $start) {
            $month = $start->copy()->addMonths($offset);
            $monthItems = $items->filter(fn ($item) => $item->{$dateField} && $item->{$dateField}->format('Y-m') === $month->format('Y-m'));

            return ['label' => $month->format('M Y'), 'total' => $monthItems->count(), 'completed' => $monthItems->filter($completed)->count()];
        });
    }

    private function approvalRouteFor(EventRequest $event): Collection
    {
        if ($event->hasConfiguredApprovalRoute()) {
            return collect($event->approval_route);
        }

        $audience = $event->intended_user ?: $event->education_level;
        if ($audience === 'shs') {
            return collect(['principal_assistant', 'academic_head', 'school_admin']);
        }

        if ($event->request_type === 'Non-Academic') {
            return collect(['building_admin', 'school_admin']);
        }

        return collect(['program_head', 'academic_head', 'building_admin', 'school_admin']);
    }

    private function currentApprovalRoleFor(EventRequest $event): ?string
    {
        $nextLevel = $event->getNextApprovalLevel();
        if (! $nextLevel) {
            return null;
        }

        if ($event->hasConfiguredApprovalRoute()) {
            return collect($event->approval_route)->get($nextLevel - 1);
        }

        $audience = $event->intended_user ?: $event->education_level;
        if ($audience === 'shs') {
            return [1 => 'principal_assistant', 2 => 'academic_head', 4 => 'school_admin'][$nextLevel] ?? null;
        }

        return [1 => 'program_head', 2 => 'academic_head', 3 => 'building_admin', 4 => 'school_admin'][$nextLevel] ?? null;
    }

    private function roleLabel(string $role): string
    {
        return match ($role) {
            'mis' => 'MIS',
            'program_head' => 'Program Head',
            'academic_head' => 'Academic Head',
            'school_admin' => 'School Administrator',
            'principal_assistant' => 'SHS Principal',
            'building_admin' => 'Building Administrator',
            default => str($role)->replace('_', ' ')->title()->toString(),
        };
    }
}
