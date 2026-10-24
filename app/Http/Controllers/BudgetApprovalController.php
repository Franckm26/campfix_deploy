<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Report;
use App\Models\User;
use App\Notifications\BudgetApprovalNotification;
use App\Support\BudgetApprovalSchema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class BudgetApprovalController extends Controller
{
    public function index(Request $request): View
    {
        BudgetApprovalSchema::ensure();
        $this->authorizeSchoolAdministrator();

        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());
        $viewType = $request->validate(['view' => ['nullable', 'in:active,archives,deleted']])['view'] ?? 'active';

        $budgets = Report::withTrashed()->with(['category', 'user', 'budgetRequester', 'budgetReviewer'])
            ->whereNotNull('budget_status')
            ->when($viewType === 'deleted', fn ($query) => $query->where(fn ($deleted) => $deleted->where('is_deleted', true)->orWhereNotNull('deleted_at')),
                fn ($query) => $query->where('is_deleted', false)->whereNull('deleted_at'))
            ->when($viewType === 'archives', fn ($query) => $query->whereHas('archivedByUsers', fn ($users) => $users->where('users.id', auth()->id())))
            ->when($viewType === 'active', fn ($query) => $query->whereDoesntHave('archivedByUsers', fn ($users) => $users->where('users.id', auth()->id())))
            ->when(in_array($status, [Report::BUDGET_PENDING, Report::BUDGET_APPROVED, Report::BUDGET_REJECTED], true), fn ($query) => $query->where('budget_status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $term = '%'.mb_strtolower($search).'%';
                $query->where(function ($query) use ($term) {
                    $query->whereRaw("LOWER(COALESCE(title, '')) LIKE ?", [$term])
                        ->orWhereRaw("LOWER(COALESCE(location, '')) LIKE ?", [$term])
                        ->orWhereHas('budgetRequester', fn ($requester) => $requester->whereRaw('LOWER(name) LIKE ?', [$term]));
                });
            })
            ->orderByRaw("CASE budget_status WHEN 'pending' THEN 1 WHEN 'rejected' THEN 2 ELSE 3 END")
            ->orderByDesc('budget_requested_at')
            ->paginate(20)
            ->withQueryString();

        return view('school-admin.budget-approvals', compact('budgets', 'status', 'search', 'viewType'));
    }

    public function show(int $id): View
    {
        $this->authorizeSchoolAdministrator();
        $report = Report::withTrashed()->with(['category', 'user', 'budgetRequester', 'budgetReviewer'])
            ->whereNotNull('budget_status')->findOrFail($id);

        return view('reports.show_modal', ['report' => $report, 'showBudgetDetails' => true]);
    }

    public function restoreDeleted(int $id): JsonResponse
    {
        $this->authorizeSchoolAdministrator();
        return DB::transaction(function () use ($id) {
            $report = Report::withTrashed()->whereNotNull('budget_status')->lockForUpdate()->findOrFail($id);
            abort_unless($report->is_deleted || $report->trashed(), 422, 'This report is not deleted.');
            $report->forceFill([
                'is_deleted' => false, 'deleted_at' => null, 'deleted_by' => null,
                'school_admin_deleted' => false, 'school_admin_archived' => false,
                'is_archived' => false, 'archive_folder_id' => null,
            ])->save();
            $report->archivedByUsers()->detach(auth()->id());
            ActivityLog::log('report_restored', 'Restored budget report: '.$report->title, $report->id, 'report');

            return response()->json(['success' => true, 'message' => 'Report restored successfully.']);
        });
    }

    public function request(Request $request, Report $report): JsonResponse
    {
        BudgetApprovalSchema::ensure();

        $validated = $request->validate([
            'budget_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ]);

        abort_unless($this->canRequest(auth()->user(), $report), 403, 'You cannot request a budget for this concern.');

        if ($report->status === 'Resolved') {
            return response()->json(['success' => false, 'error' => 'A budget cannot be requested after work has been resolved.'], 422);
        }

        DB::transaction(function () use (&$report, $validated) {
            if ($report->concern_id) \App\Models\Concern::whereKey($report->concern_id)->lockForUpdate()->firstOrFail();
            $report = Report::whereKey($report->id)->lockForUpdate()->firstOrFail();
            abort_unless($this->canRequest(auth()->user(), $report), 403, 'You cannot request a budget for this concern.');
            abort_if($report->status === 'Resolved' || $report->is_deleted, 422, 'A budget cannot be requested for resolved or deleted work.');
            abort_if(in_array($report->budget_status, [Report::BUDGET_PENDING, Report::BUDGET_APPROVED], true), 422,
                'This report already has a pending or approved budget. A revised request is allowed only after rejection.');
            $values = [
                'budget_amount' => $validated['budget_amount'],
                'budget_status' => Report::BUDGET_PENDING,
                'budget_requested_by' => auth()->id(),
                'budget_requested_at' => now(),
                'budget_reviewed_by' => null,
                'budget_reviewed_at' => null,
                'budget_rejection_reason' => null,
            ];

            $report->update($values);
            $report->concern?->update($values);
            // A revised budget must reappear for review even if the earlier request was archived.
            if (Schema::hasColumn('reports', 'school_admin_archived')) {
                $report->update(['school_admin_archived' => false]);
            }
            if (Schema::hasTable('user_archived_reports')) {
                $report->archivedByUsers()->detach(User::where('role', User::ROLE_SCHOOL_ADMIN)->pluck('id'));
            }
        });

        $report->refresh();
        User::where('role', User::ROLE_SCHOOL_ADMIN)->get()
            ->each(fn (User $administrator) => $administrator->notify(
                new BudgetApprovalNotification($report, 'requested')
            ));

        ActivityLog::log(
            'budget_approval_requested',
            'Budget approval requested: PHP '.number_format((float) $report->budget_amount, 2),
            $report->id,
            'report'
        );

        return response()->json([
            'success' => true,
            'message' => 'Budget submitted to the School Administrator for approval.',
            'budget_status' => $report->budget_status,
        ]);
    }

    public function approve(Report $report): JsonResponse
    {
        BudgetApprovalSchema::ensure();
        $this->authorizeSchoolAdministrator();

        if ($report->budget_status !== Report::BUDGET_PENDING) {
            return response()->json(['success' => false, 'error' => 'This budget is no longer pending approval.'], 422);
        }

        $this->review($report, Report::BUDGET_APPROVED);

        return response()->json([
            'success' => true,
            'message' => 'Budget approved. The assigned staff may now start work.',
        ]);
    }

    public function reject(Request $request, Report $report): JsonResponse
    {
        BudgetApprovalSchema::ensure();
        $this->authorizeSchoolAdministrator();
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        if ($report->budget_status !== Report::BUDGET_PENDING) {
            return response()->json(['success' => false, 'error' => 'This budget is no longer pending approval.'], 422);
        }

        $this->review($report, Report::BUDGET_REJECTED, $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Budget rejected. The assigned staff can submit a revised budget.',
        ]);
    }

    private function review(Report $report, string $status, ?string $reason = null): void
    {
        $values = [
            'budget_status' => $status,
            'budget_reviewed_by' => auth()->id(),
            'budget_reviewed_at' => now(),
            'budget_rejection_reason' => $reason,
        ];

        DB::transaction(function () use ($report, $values) {
            $report->update($values);
            $report->concern?->update($values);
        });

        $report->refresh();
        $requester = $report->budgetRequester;
        $requester?->notify(new BudgetApprovalNotification($report, $status, $reason));

        ActivityLog::log(
            'budget_'.$status,
            'Budget '.strtolower($status).': PHP '.number_format((float) $report->budget_amount, 2).
                ($reason ? '. Reason: '.$reason : ''),
            $report->id,
            'report'
        );
    }

    private function authorizeSchoolAdministrator(): void
    {
        abort_unless(auth()->user()?->role === User::ROLE_SCHOOL_ADMIN, 403, 'Only the School Administrator can review concern budgets.');
    }

    public function canRequest(User $user, Report $report): bool
    {
        if ($user->isSystemAdministrator() || in_array($user->role, ['admin', 'building_admin', 'academic_head', 'school_admin'], true)) {
            return true;
        }

        if ($user->role === 'mis') {
            return (int) $report->assigned_to === (int) $user->id;
        }

        if ($user->role === 'maintenance') {
            return (int) $report->assigned_to === (int) $user->id
                || strtolower(trim($report->category->name ?? '')) === 'rooms';
        }

        return false;
    }
}
