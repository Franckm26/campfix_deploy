<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Category;
use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\FacilityRequest;
use App\Models\LogArchiveFolder;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\User;
use App\Models\UserArchiveFolder;
use App\Notifications\ConcernResolvedNotification;
use App\Notifications\ReportAssignedNotification;
use App\Notifications\ReportResolvedNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // Unlock a locked user account
    public function unlockUser($uuid)
    {
        if (! auth()->user()->canAccess('users_unlock')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You do not have permission to perform this action.');
        }

        $user = User::hideSuperadmin()->where('uuid', $uuid)->firstOrFail();

        $user->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
            'login_lockout_level' => 0,
        ]);

        ActivityLog::log('account_unlocked', "Unlocked account: {$user->name} ({$user->email})", $user->id, 'user');

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('admin.users')->with('success', "Account '{$user->name}' has been unlocked.");
    }

    // Re-authentication check for sensitive actions (e.g. Manage Users button)
    public function reauth(Request $request)
    {
        $request->validate(['password' => 'required|string']);

        if (! \Illuminate\Support\Facades\Hash::check($request->password, auth()->user()->password)) {
            return response()->json(['success' => false, 'message' => 'Incorrect password.'], 401);
        }

        // Store reauth timestamp in session (valid for 5 minutes)
        session(['reauth_at' => now()->timestamp]);

        return response()->json(['success' => true]);
    }

    // Main dashboard - MIS only

    // Sample code changes
    public function index()
    {
        // Only MIS can access admin dashboard
        if (auth()->user()->role !== 'mis') {
            return redirect('/dashboard');
        }

        // User stats
        $totalUsers        = User::hideSuperadmin()->where('is_deleted', false)->count();
        $activeUsers       = User::hideSuperadmin()->where('is_deleted', false)->where('is_archived', false)->whereNull('locked_until')->count();
        $archivedUsers     = User::hideSuperadmin()->where('is_archived', true)->where('is_deleted', false)->count();
        $lockedUsers       = User::hideSuperadmin()->where('is_deleted', false)->whereNotNull('locked_until')->where('locked_until', '>', now())->count();
        $forceChangeUsers  = User::hideSuperadmin()->where('is_deleted', false)->where('force_password_change', true)->count();

        // Locked users list for dashboard modal
        $lockedUsersList = User::hideSuperadmin()->where('is_deleted', false)
            ->whereNotNull('locked_until')
            ->where('locked_until', '>', now())
            ->orderBy('updated_at', 'desc')
            ->get();

        // Users by role
        $usersByRole = User::hideSuperadmin()->where('is_deleted', false)
            ->where('is_archived', false)
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        // New users this month
        $newUsersThisMonth = User::hideSuperadmin()->where('is_deleted', false)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // New users last month
        $newUsersLastMonth = User::hideSuperadmin()->where('is_deleted', false)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        // Recent registrations
        $recentUsers = User::hideSuperadmin()->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // Users registered per month (last 6 months)
        $registrationTrend = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'month' => $date->format('M'),
                'count' => User::hideSuperadmin()->whereMonth('created_at', $date->month)
                               ->whereYear('created_at', $date->year)
                               ->count(),
            ];
        });

        // MIS staff count
        $misCount = User::hideSuperadmin()->where('role', 'mis')->where('is_deleted', false)->count();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'archivedUsers',
            'lockedUsers',
            'lockedUsersList',
            'forceChangeUsers',
            'usersByRole',
            'newUsersThisMonth',
            'newUsersLastMonth',
            'recentUsers',
            'registrationTrend',
            'misCount'
        ));
    }

    // MIS Task Module
    public function misTasks(Request $request)
    {
        // Only MIS can access
        if (auth()->user()->role !== 'mis') {
            return redirect('/dashboard');
        }

        $viewType = $request->get('view', 'active');

        // Get concerns assigned to MIS users
        $misUsers = User::where('role', 'mis')->pluck('id');

        if ($viewType === 'resolved') {
            // Get resolved concerns assigned to MIS users
            $resolvedConcerns = Concern::with('categoryRelation', 'assignedTo', 'user')
                ->whereIn('assigned_to', $misUsers)
                ->where('status', 'Resolved')
                ->where('is_deleted', false)
                ->whereDoesntHave('archivedByUsers', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->orderBy('updated_at', 'desc')
                ->paginate(20);

            // Get active concerns for tab count
            $concerns = Concern::with('categoryRelation', 'assignedTo', 'user')
                ->whereIn('assigned_to', $misUsers)
                ->where('status', '!=', 'Resolved')
                ->where('is_deleted', false)
                ->whereDoesntHave('archivedByUsers', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('admin.mis-tasks', compact('resolvedConcerns', 'concerns', 'viewType', 'misUsers'));
        } elseif ($viewType === 'archives') {
            // Get archived concerns assigned to MIS users
            $concerns = Concern::with('categoryRelation', 'assignedTo', 'user', 'archivedByUsers')
                ->whereIn('assigned_to', $misUsers)
                ->whereHas('archivedByUsers', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('admin.mis-tasks', compact('concerns', 'viewType', 'misUsers'));
        } elseif ($viewType === 'deleted') {
            // Get deleted concerns assigned to MIS users (only those deleted by MIS users)
            $concerns = Concern::with('categoryRelation', 'assignedTo', 'user', 'deletedBy')
                ->whereIn('assigned_to', $misUsers)
                ->where('is_deleted', true)
                ->where('deleted_by', auth()->id()) // Only show concerns deleted by current MIS user
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return view('admin.mis-tasks', compact('concerns', 'viewType', 'misUsers'));
        }

        // Active concerns assigned to MIS users (excluding resolved and deleted)
        $concerns = Concern::with('categoryRelation', 'assignedTo', 'user')
            ->whereIn('assigned_to', $misUsers)
            ->where('status', '!=', 'Resolved')
            ->where('is_deleted', false)
            ->whereDoesntHave('archivedByUsers', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.mis-tasks', compact('concerns', 'viewType', 'misUsers'));
    }

    // Update concern status - Admin or maintenance can update any concern
    public function updateStatus(Request $request, $id)
    {
        $concern = Concern::findOrFail($id);

        // Check if user is MIS or maintenance
        $user = auth()->user();
        if (! in_array($user->role, ['mis', 'maintenance'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to update this concern.'], 403);
            }
            return back()->with('error', 'You do not have permission to update this concern.');
        }

        // Maintenance can only update their own assigned concerns.
        // MIS users can update any concern assigned to any MIS user (department-level access).
        if ($user->role === 'maintenance' && $concern->assigned_to !== $user->id) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You can only update concerns assigned to you.'], 403);
            }
            return back()->with('error', 'You can only update concerns assigned to you.');
        }

        if ($user->role === 'mis') {
            $misUserIds = \App\Models\User::where('role', 'mis')->pluck('id');
            if (! $misUserIds->contains($concern->assigned_to)) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'This concern is not assigned to the MIS department.'], 403);
                }
                return back()->with('error', 'This concern is not assigned to the MIS department.');
            }
        }

        // Validate request
        $request->validate([
            'status' => 'required|in:Pending,Assigned,In Progress,Resolved,Closed',
            'resolution_notes' => 'nullable|string|max:1000',
            'cost' => 'nullable|numeric|min:0',
            'damaged_part' => 'nullable|string|max:255',
            'replaced_part' => 'nullable|string|max:255',
        ]);

        $oldStatus = $concern->status;
        $newStatus = $request->input('status');

        // OWASP API6: Validate business logic - status transitions
        if (! $this->isValidStatusTransition($oldStatus, $newStatus)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Invalid status transition from ' . $oldStatus . ' to ' . $newStatus], 422);
            }
            return back()->with('error', 'Invalid status transition from ' . $oldStatus . ' to ' . $newStatus);
        }

        $concern->status = $newStatus;

        // Update additional fields when resolving (for maintenance)
        if ($newStatus === 'Resolved') {
            $concern->resolution_notes = $request->input('resolution_notes');
            $concern->cost = $request->input('cost');
            $concern->damaged_part = $request->input('damaged_part');
            $concern->replaced_part = $request->input('replaced_part');

            // Set resolved_at when status is Resolved and notify the requester
            if ($oldStatus !== 'Resolved') {
                $concern->resolved_at = now();

                // Notify the requester about the resolution
                $this->sendConcernResolvedNotification($concern, $user);

                // Archive for maintenance user when resolved
                if ($user->role === 'maintenance') {
                    $concern->maintenance_archived = true;
                    $concern->archived_at = now();
                    $concern->archived_by = $user->id;
                }
            }
        }

        $concern->save();

        // Log activity
        $activityMessage = "Status changed from {$oldStatus} to {$newStatus}";
        if ($newStatus === 'Resolved' && $user->role === 'maintenance') {
            $activityMessage .= '. Cost: '.($request->input('cost') ?? 0).', Damaged: '.($request->input('damaged_part') ?? 'N/A').', Replaced: '.($request->input('replaced_part') ?? 'N/A');
        }

        ActivityLog::log(
            'status_updated',
            $activityMessage,
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
        }

        return back()->with('success', 'Status updated successfully!');
    }

    // Update report status - Admin or maintenance can update any report
    public function updateReportStatus(Request $request, $id)
    {
        try {
            $report = Report::findOrFail($id);

            // Check if user is MIS or maintenance
            $user = auth()->user();
            if (! in_array($user->role, ['mis', 'maintenance'])) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission to update this report.'], 403);
                }

                return back()->with('error', 'You do not have permission to update this report.');
            }

            // Maintenance can only update their assigned reports
            if ($user->role === 'maintenance' && $report->assigned_to !== $user->id) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'You can only update reports assigned to you.'], 403);
                }

                return back()->with('error', 'You can only update reports assigned to you.');
            }

            // Validate request
            $request->validate([
                'status' => 'required|in:Pending,Assigned,In Progress,Resolved',
                'resolution_notes' => 'nullable|string|max:1000',
                'cost' => 'nullable|numeric|min:0',
                'damaged_part' => 'nullable|string|max:255',
                'replaced_part' => 'nullable|string|max:255',
            ]);

            $oldStatus = $report->status;
            $newStatus = $request->input('status');
            $report->status = $newStatus;

            // Update maintenance fields based on status
            if ($newStatus === 'In Progress') {
                // Save damaged part when starting work
                $report->damaged_part = $request->input('damaged_part');
            } elseif ($newStatus === 'Resolved') {
                // Save all maintenance details when completing work
                $report->resolution_notes = $request->input('resolution_notes');
                $report->cost = $request->input('cost');
                $report->damaged_part = $request->input('damaged_part');
                $report->replaced_part = $request->input('replaced_part');

                // Set resolved_at when status is Resolved and notify the requester
                if ($oldStatus !== 'Resolved') {
                    $report->resolved_at = now();

                    // Notify the requester about the resolution
                    // Assuming there's a ReportResolvedNotification, similar to ConcernResolvedNotification
                    try {
                        $report->user->notify(new ReportResolvedNotification($report, $user->name));
                    } catch (\Exception $e) {
                        \Log::error('Failed to send resolution notification: '.$e->getMessage());
                    }

                    // Find and update corresponding concern
                    $correspondingConcern = $report->concern;

                    if ($correspondingConcern && $correspondingConcern->status !== 'Resolved') {
                        $correspondingConcern->status = 'Resolved';
                        $correspondingConcern->resolution_notes = $request->input('resolution_notes');
                        $correspondingConcern->cost = $request->input('cost');
                        $correspondingConcern->damaged_part = $request->input('damaged_part');
                        $correspondingConcern->replaced_part = $request->input('replaced_part');
                        $correspondingConcern->resolved_at = now();

                        // Archive for maintenance user when resolved
                        if ($user->role === 'maintenance') {
                            $correspondingConcern->maintenance_archived = true;
                            $correspondingConcern->archived_at = now();
                            $correspondingConcern->archived_by = $user->id;
                        }

                        $correspondingConcern->save();

                        // Log concern resolution
                        ActivityLog::log(
                            'concern_resolved',
                            'Concern resolved via report resolution',
                            $correspondingConcern->id
                        );

                        // Notify the concern requester about the resolution
                        try {
                            $this->sendConcernResolvedNotification($correspondingConcern, $user);
                        } catch (\Exception $e) {
                            \Log::error('Failed to send concern resolution notification: '.$e->getMessage());
                        }
                    }
                }
            }

            $report->save();

            // Log activity
            $activityMessage = "Status changed from {$oldStatus} to {$newStatus}";
            if ($newStatus === 'Resolved' && $user->role === 'maintenance') {
                $activityMessage .= '. Resolution notes: '.($request->input('resolution_notes') ?? 'N/A');
            }

            ActivityLog::log(
                'report_status_updated',
                $activityMessage,
                $report->id,
                'report'
            );

            // Log status change
            ReportStatusLog::create([
                'report_id' => $report->id,
                'changed_by' => auth()->id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_at' => now(),
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Status updated successfully!']);
            }

            return back()->with('success', 'Status updated successfully!');
        } catch (\Exception $e) {
            \Log::error('Error updating report status: '.$e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to update status'], 500);
            }

            return back()->with('error', 'Failed to update status');
        }
    }

    /**
     * Assign a concern to a maintenance user
     * Building Admin can assign concerns to maintenance staff
     */
    public function assignConcern(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $concern = Concern::findOrFail($id);

        // Check if user is building_admin only
        $user = auth()->user();
        if ($user->role !== 'building_admin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to assign concerns.'], 403);
            }

            return back()->with('error', 'You do not have permission to assign concerns.');
        }

        // Verify the assigned user is a maintenance staff
        $maintenanceUser = User::findOrFail($request->input('assigned_to'));
        if ($maintenanceUser->role !== 'maintenance') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You can only assign concerns to maintenance staff.'], 422);
            }

            return back()->with('error', 'You can only assign concerns to maintenance staff.');
        }

        $oldAssignedTo = $concern->assigned_to;

        // Update the concern
        $concern->assigned_to = $request->input('assigned_to');
        $concern->assigned_at = now();
        $concern->status = 'Assigned';
        $concern->save();

        // Log activity
        ActivityLog::log(
            'concern_assigned',
            "Concern assigned to {$maintenanceUser->name}",
            $concern->id
        );

        // Send notification to the maintenance staff
        try {
            $notificationService = new NotificationService;
            $notificationService->notifyConcernAssigned($concern, $maintenanceUser, $user->name);
        } catch (\Exception $e) {
            \Log::error('Failed to send assignment notification: '.$e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Concern assigned to {$maintenanceUser->name} successfully!"]);
        }

        return back()->with('success', "Concern assigned to {$maintenanceUser->name} successfully!");
    }

    /**
     * Assign a report to a maintenance user
     * Building Admin can assign reports to maintenance staff
     */
    public function assignReport(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $report = Report::findOrFail($id);

        // Check if user is building_admin only
        $user = auth()->user();
        if ($user->role !== 'building_admin') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to assign reports.'], 403);
            }

            return back()->with('error', 'You do not have permission to assign reports.');
        }

        // Verify the assigned user is a maintenance staff
        $maintenanceUser = User::findOrFail($request->input('assigned_to'));
        if ($maintenanceUser->role !== 'maintenance') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You can only assign reports to maintenance staff.'], 422);
            }

            return back()->with('error', 'You can only assign reports to maintenance staff.');
        }

        $oldAssignedTo = $report->assigned_to;

        // Update the report
        $report->assigned_to = $request->input('assigned_to');
        $report->assigned_at = now();
        $report->status = 'Assigned';
        $report->save();

        // Find and update corresponding concern
        $correspondingConcern = Concern::where('user_id', $report->user_id)
            ->whereRaw('TRIM(description) = ?', [trim($report->description)])
            ->whereRaw('TRIM(location) = ?', [trim($report->location)])
            ->where('status', '!=', 'Resolved')
            ->first();

        if ($correspondingConcern && $correspondingConcern->status === 'Pending') {
            $correspondingConcern->assigned_to = $request->input('assigned_to');
            $correspondingConcern->assigned_at = now();
            $correspondingConcern->status = 'Assigned';
            $correspondingConcern->save();

            // Log concern assignment
            ActivityLog::log(
                'concern_assigned',
                "Concern assigned to {$maintenanceUser->name} via report assignment",
                $correspondingConcern->id
            );

            // Send notification to the maintenance staff for the concern
            try {
                $notificationService = new NotificationService;
                $notificationService->notifyConcernAssigned($correspondingConcern, $maintenanceUser, $user->name);
            } catch (\Exception $e) {
                \Log::error('Failed to send concern assignment notification: '.$e->getMessage());
            }
        }

        // Log activity for report
        ActivityLog::log(
            'report_assigned',
            "Report assigned to {$maintenanceUser->name}",
            $report->id,
            'report'
        );

        // Send notification to the maintenance staff for the report
        try {
            $maintenanceUser->notify(new ReportAssignedNotification($report, $user->name, $report->assigned_at));
        } catch (\Exception $e) {
            \Log::error('Failed to send assignment notification: '.$e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Report assigned to {$maintenanceUser->name} successfully!"]);
        }

        return back()->with('success', "Report assigned to {$maintenanceUser->name} successfully!");
    }

    /**
     * Get list of maintenance users for assignment dropdown
     */
    public function getMaintenanceUsers()
    {
        $maintenanceUsers = User::where('role', 'maintenance')
            ->where('is_archived', false)
            ->select('id', 'name', 'email', 'department')
            ->orderBy('name')
            ->get();

        return response()->json(['users' => $maintenanceUsers]);
    }

    /**
     * Send notification to concern requester when it's resolved
     */
    private function sendConcernResolvedNotification(Concern $concern, User $resolvedBy): void
    {
        try {
            $resolvedByName = $resolvedBy->name ?? 'Admin';
            $notificationService = new NotificationService;
            $notificationService->notifyConcernResolved($concern, $resolvedByName);
        } catch (\Exception $e) {
            // Log error but don't fail the status update
            \Log::error('Concern resolution notification failed: '.$e->getMessage());
        }
    }

    private function sendReportResolvedNotification(Report $report, User $resolvedBy): void
    {
        try {
            $resolvedByName = $resolvedBy->name ?? 'Admin';
            $notificationService = new NotificationService;
            $notificationService->notifyReportResolved($report, $resolvedByName);
        } catch (\Exception $e) {
            // Log error but don't fail the status update
            \Log::error('Report resolution notification failed: '.$e->getMessage());
        }
    }

    // Add resolution notes - Admin or maintenance can add
    public function addResolutionNotes(Request $request, $id)
    {
        $request->validate([
            'resolution_notes' => 'required|string',
        ]);

        $concern = Concern::findOrFail($id);

        // Check if user is MIS or maintenance
        $user = auth()->user();
        if (! in_array($user->role, ['mis', 'maintenance'])) {
            return back()->with('error', 'You do not have permission to add notes to this concern.');
        }

        $oldStatus = $concern->status;
        $concern->resolution_notes = $request->input('resolution_notes');

        // Auto-resolve if notes added
        if ($request->input('status') === 'Resolved' || $concern->status === 'In Progress') {
            $concern->status = 'Resolved';
            $concern->resolved_at = now();

            // Notify the requester about the resolution
            $this->sendConcernResolvedNotification($concern, $user);
        }

        $concern->save();

        // Log activity
        ActivityLog::log(
            'resolution_added',
            'Resolution notes added'.($concern->status === 'Resolved' ? ' and concern resolved' : ''),
            $concern->id
        );

        return back()->with('success', 'Resolution notes added successfully!');
    }

    // Reports view
    public function reports(Request $request)
    {
        $viewType = $request->input('view', 'active');

        if ($viewType === 'resolved') {
            // Show resolved reports
            $resolvedReports = Report::with('user', 'category')
                ->where('status', 'Resolved')
                ->where('is_deleted', false)
                ->where(function ($query) {
                    $query->where('building_admin_archived', false)
                        ->where('mis_archived', false)
                        ->where('school_admin_archived', false)
                        ->where('admin_archived', false);
                })
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.reports', [
                'viewType' => $viewType,
                'resolvedReports' => $resolvedReports,
                'reports' => collect(),
                'categories' => Category::all(),
                'totalReports' => $resolvedReports->count(),
                'totalCost' => 0,
                'groupedReports' => collect(),
                'locationStats' => collect(),
                'uniqueLocations' => 0,
                'concerns' => collect(),
            ]);
        }

        if ($viewType === 'archives') {
            // Show reports archived by the current admin user
            $archivedReports = Report::archivedByUser(auth()->id())
                ->with('category', 'user', 'archivedByUsers')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.reports', [
                'viewType' => $viewType,
                'archivedConcerns' => $archivedReports,
                'concerns' => collect(),
                'categories' => Category::all(),
                'totalConcerns' => $archivedReports->count(),
                'totalCost' => 0,
                'groupedReports' => collect(),
                'locationStats' => collect(),
                'uniqueLocations' => 0,
                'reports' => collect(),
            ]);
        }

        if ($viewType === 'deleted') {
            // Show deleted reports
            $user = auth()->user();
            $days = $request->get('days', $user->reports_auto_delete_days ?? 15);

            $deletedReports = Report::where('is_deleted', true)
                ->where('deleted_at', '<=', now()->subDays($days))
                ->with(['user', 'category', 'deletedBy'])
                ->orderBy('updated_at', 'desc')
                ->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'reports' => $deletedReports->map(function ($report) {
                        return [
                            'id' => $report->id,
                            'title' => 'Report #'.$report->id,
                            'category' => $report->categoryRelation ? $report->categoryRelation->name : 'N/A',
                            'location' => $report->location,
                            'priority' => $report->priority,
                            'status' => $report->status,
                            'user' => $report->user ? $report->user->name : 'Unknown',
                            'updated_at' => $report->updated_at->format('M d, Y h:i A'),
                            'deleted_by' => $report->deletedBy ? $report->deletedBy->name : 'System',
                        ];
                    }),
                    'days' => $days,
                ]);
            }

            return view('admin.reports', [
                'viewType' => $viewType,
                'deletedReports' => $deletedReports,
                'concerns' => collect(),
                'categories' => Category::all(),
                'totalConcerns' => $deletedReports->count(),
                'totalCost' => 0,
                'groupedReports' => collect(),
                'locationStats' => collect(),
                'uniqueLocations' => 0,
                'reports' => collect(),
                'days' => $days,
            ]);
        }

        if ($viewType === 'analytics') {
            // Analytics for reports - individual repairs
            $analyticsQuery = Report::whereNotNull('location')
                ->where('location', '!=', '')
                ->where('is_deleted', false)
                ->whereNotNull('resolved_at');

            // Filter by date range
            if ($request->filled('date_from')) {
                $analyticsQuery->whereNotNull('resolved_at')->whereDate('resolved_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $analyticsQuery->whereNotNull('resolved_at')->whereDate('resolved_at', '<=', $request->input('date_to'));
            }

            // Get the reports list
            $reports = $analyticsQuery->select('location', 'damaged_part', 'resolved_at', 'cost')
                ->orderBy('resolved_at', 'desc')
                ->get();

            // Summary
            $totalConcerns = $reports->count();
            $totalCost = $reports->sum('cost') ?? 0;
            $uniqueLocations = $reports->unique('location')->count();

            // Location stats
            $locationStats = $reports->groupBy('location')->map(function ($group) {
                return [
                    'location' => $group->first()->location,
                    'count' => $group->count(),
                    'total_cost' => $group->sum('cost') ?? 0,
                ];
            })->sortByDesc('count')->values();

            $groupedReports = $reports->groupBy('location');

            // Debug: log any report descriptions that might contain problematic characters
            \Log::info('groupedReports locations: ' . implode(', ', $groupedReports->keys()->toArray()));
            foreach ($groupedReports as $location => $group) {
                foreach ($group as $report) {
                    $encoded = json_encode($report->toArray(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
                    if ($encoded === false) {
                        \Log::error('json_encode failed for report ID: ' . $report->id . ' - ' . json_last_error_msg());
                    }
                }
            }

            // Prepare data for charts
            $chartLocations = $locationStats->pluck('location')->toArray();
            $chartCounts = $locationStats->pluck('count')->toArray();
            $chartCosts = $locationStats->pluck('total_cost')->toArray();

            // Status distribution for additional chart
            $statusStats = $reports->groupBy('status')->map(function ($group) {
                return [
                    'status' => $group->first()->status,
                    'count' => $group->count(),
                ];
            })->sortByDesc('count')->values();

            $chartStatuses = $statusStats->pluck('status')->toArray();
            $chartStatusCounts = $statusStats->pluck('count')->toArray();

            // Combined cost tracking across all tickets
            $concernsQueryCombined = Concern::whereNotNull('location')
                ->where('location', '!=', '')
                ->where('status', 'Resolved');

            $reportsQueryCombined = Report::whereNotNull('location')
                ->where('location', '!=', '')
                ->whereNotNull('resolved_at');

            if ($request->filled('date_from')) {
                $concernsQueryCombined->whereDate('created_at', '>=', $request->input('date_from'));
                $reportsQueryCombined->whereDate('resolved_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $concernsQueryCombined->whereDate('created_at', '<=', $request->input('date_to'));
                $reportsQueryCombined->whereDate('resolved_at', '<=', $request->input('date_to'));
            }

            $concernsCombined = $concernsQueryCombined->get();
            $reportsCombined = $reportsQueryCombined->get();

            $combinedLocationStats = $concernsCombined->concat($reportsCombined)->groupBy('location')->map(function ($group) {
                return [
                    'location' => $group->first()->location,
                    'total_count' => $group->count(),
                    'total_cost' => $group->sum('cost') ?? 0,
                ];
            })->sortByDesc('total_cost')->values();

            $totalTickets = $concernsCombined->count() + $reportsCombined->count();
            $totalCombinedCost = $concernsCombined->sum('cost') + $reportsCombined->sum('cost');
            $uniqueLocationsCombined = $combinedLocationStats->count();

            // ── PERIOD COMPARISON (this month vs last month) ──────────────────
            $thisMonthStart = now()->startOfMonth();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd   = now()->subMonth()->endOfMonth();

            $thisMonthCount = Report::where('is_deleted', false)->whereNotNull('resolved_at')
                ->where('resolved_at', '>=', $thisMonthStart)->count()
                + Concern::where('status', 'Resolved')->where('resolved_at', '>=', $thisMonthStart)->count();

            $lastMonthCount = Report::where('is_deleted', false)->whereNotNull('resolved_at')
                ->whereBetween('resolved_at', [$lastMonthStart, $lastMonthEnd])->count()
                + Concern::where('status', 'Resolved')->whereBetween('resolved_at', [$lastMonthStart, $lastMonthEnd])->count();

            $thisMonthCost = (Report::where('is_deleted', false)->whereNotNull('resolved_at')
                ->where('resolved_at', '>=', $thisMonthStart)->sum('cost') ?? 0)
                + (Concern::where('status', 'Resolved')->where('resolved_at', '>=', $thisMonthStart)->sum('cost') ?? 0);

            $lastMonthCost = (Report::where('is_deleted', false)->whereNotNull('resolved_at')
                ->whereBetween('resolved_at', [$lastMonthStart, $lastMonthEnd])->sum('cost') ?? 0)
                + (Concern::where('status', 'Resolved')->whereBetween('resolved_at', [$lastMonthStart, $lastMonthEnd])->sum('cost') ?? 0);

            $countChange = $lastMonthCount > 0
                ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1)
                : ($thisMonthCount > 0 ? 100 : 0);
            $costChange = $lastMonthCost > 0
                ? round((($thisMonthCost - $lastMonthCost) / $lastMonthCost) * 100, 1)
                : ($thisMonthCost > 0 ? 100 : 0);

            $periodComparison = [
                'this_month_count' => $thisMonthCount,
                'last_month_count' => $lastMonthCount,
                'this_month_cost'  => $thisMonthCost,
                'last_month_cost'  => $lastMonthCost,
                'count_change'     => $countChange,
                'cost_change'      => $costChange,
                'this_month_label' => now()->format('F Y'),
                'last_month_label' => now()->subMonth()->format('F Y'),
            ];

            // ── REPLACEMENT SUGGESTIONS ───────────────────────────────────────
            // Flag locations where cumulative repair cost suggests buying new is more cost-effective
            $replacementThreshold = (float) env('REPLACEMENT_COST_THRESHOLD', 10000);
            $replacementSuggestions = collect();

            foreach ($combinedLocationStats as $stat) {
                if ($stat['total_cost'] <= 0) continue;

                $repairCount = $stat['total_count'];
                $totalCost   = $stat['total_cost'];
                $avgCost     = $repairCount > 0 ? $totalCost / $repairCount : 0;

                // Suggest replacement if total repair cost exceeds threshold
                if ($totalCost >= $replacementThreshold) {
                    $urgency = $totalCost >= $replacementThreshold * 3
                        ? 'critical'
                        : ($totalCost >= $replacementThreshold * 1.5 ? 'warning' : 'info');

                    $replacementSuggestions->push([
                        'location'   => $stat['location'],
                        'repairs'    => $repairCount,
                        'total_cost' => $totalCost,
                        'avg_cost'   => $avgCost,
                        'urgency'    => $urgency,
                    ]);
                }
            }
            $replacementSuggestions = $replacementSuggestions->sortByDesc('total_cost')->values();

            // ── PREDICTIVE TREND ALERTS ───────────────────────────────────────
            $trendAlerts  = collect();
            $allLocations = Report::where('is_deleted', false)->whereNotNull('location')
                ->where('location', '!=', '')->distinct()->pluck('location')
                ->merge(Concern::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location'))
                ->unique();

            foreach ($allLocations as $loc) {
                $recent = Report::where('location', $loc)->where('is_deleted', false)
                    ->where('created_at', '>=', now()->subMonths(3))->count()
                    + Concern::where('location', $loc)->where('created_at', '>=', now()->subMonths(3))->count();

                $prior = Report::where('location', $loc)->where('is_deleted', false)
                    ->whereBetween('created_at', [now()->subMonths(6), now()->subMonths(3)])->count()
                    + Concern::where('location', $loc)->whereBetween('created_at', [now()->subMonths(6), now()->subMonths(3)])->count();

                if ($recent >= 2 && $recent > $prior) {
                    $recentCost = (Report::where('location', $loc)->where('is_deleted', false)
                        ->where('created_at', '>=', now()->subMonths(3))->sum('cost') ?? 0)
                        + (Concern::where('location', $loc)->where('created_at', '>=', now()->subMonths(3))->sum('cost') ?? 0);

                    $trendAlerts->push([
                        'location'    => $loc,
                        'recent'      => $recent,
                        'prior'       => $prior,
                        'recent_cost' => $recentCost,
                        'severity'    => $recent >= 4 ? 'critical' : ($recent >= 3 ? 'warning' : 'info'),
                    ]);
                }
            }
            $trendAlerts = $trendAlerts->sortByDesc('recent')->values();

            return view('admin.reports', [
                'viewType' => $viewType,
                'concerns' => collect(),
                'categories' => Category::all(),
                'totalConcerns' => $totalConcerns,
                'totalCost' => $totalCost,
                'uniqueLocations' => $uniqueLocations,
                'locationStats' => $locationStats,
                'groupedReports' => $groupedReports,
                'reports' => $reports,
                'chartLocations' => $chartLocations,
                'chartCounts' => $chartCounts,
                'chartCosts' => $chartCosts,
                'chartStatuses' => $chartStatuses,
                'chartStatusCounts' => $chartStatusCounts,
                'combinedLocationStats' => $combinedLocationStats,
                'totalTickets' => $totalTickets,
                'totalCombinedCost' => $totalCombinedCost,
                'uniqueLocationsCombined' => $uniqueLocationsCombined,
                'periodComparison' => $periodComparison,
                'replacementSuggestions' => $replacementSuggestions,
                'replacementThreshold' => $replacementThreshold,
                'trendAlerts' => $trendAlerts,
            ]);
        }

        // Default: Show all active reports for admin management (excluding resolved)
        $reports = Report::with('user', 'category')
            ->where('is_deleted', false)
            ->where('status', '!=', 'Resolved')
            ->where(function ($query) {
                $query->where('building_admin_archived', false)
                    ->where('mis_archived', false)
                    ->where('school_admin_archived', false)
                    ->where('admin_archived', false);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.reports', [
            'viewType' => $viewType,
            'reports' => $reports,
            'categories' => Category::all(),
            'totalReports' => $reports->count(),
            'totalCost' => 0,
            'groupedReports' => collect(),
            'locationStats' => collect(),
            'uniqueLocations' => 0,
            'concerns' => collect(),
        ]);
    }

    // Old concerns reports - rename or remove if not needed
    public function concernsReports(Request $request)
    {
        $viewType = $request->input('view', 'active');

        if ($viewType === 'archives') {
            // Show concerns archived by the current admin user
            // Uses the pivot table to get concerns archived by admin
            $archivedConcerns = Concern::archivedByUser(auth()->id())
                ->with('categoryRelation', 'user', 'archivedByUsers')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.reports', [
                'viewType' => $viewType,
                'archivedConcerns' => $archivedConcerns,
                'concerns' => collect(),
                'categories' => Category::all(),
            ]);
        }

        if ($viewType === 'deleted') {
            // Show deleted concerns
            // Concerns use is_deleted flag for soft deletion
            $deletedReports = Concern::where('is_deleted', true)
                ->with(['user', 'categoryRelation', 'deletedBy'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.reports', [
                'viewType' => $viewType,
                'deletedReports' => $deletedReports,
                'concerns' => collect(),
                'categories' => Category::all(),
            ]);
        }

        if ($viewType === 'analytics') {
            // Show analytics inline
            $analyticsQuery = Concern::whereNotNull('location')
                ->where('location', '!=', '')
                ->where('status', 'Resolved');

            // Filter by date range
            if ($request->filled('date_from')) {
                $analyticsQuery->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $analyticsQuery->whereDate('created_at', '<=', $request->input('date_to'));
            }

            // Get concerns grouped by location
            $locationStats = $analyticsQuery->select('location')
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
                ->groupBy('location')
                ->orderBy('total_count', 'desc')
                ->get();

            // Get category-based stats
            $categoryStats = Concern::with('categoryRelation')
                ->whereNotNull('category_id')
                ->where('status', 'Resolved')
                ->select('category_id')
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
                ->groupBy('category_id')
                ->orderBy('total_count', 'desc')
                ->get();

            // Get monthly trend for the last 12 months
            $monthlyStats = Concern::where('status', 'Resolved')
                ->whereNotNull('location')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get total summary
            $totalConcerns = Concern::where('status', 'Resolved')
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->count();
            $totalCost = Concern::where('status', 'Resolved')
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->sum('cost') ?? 0;
            $uniqueLocations = Concern::where('status', 'Resolved')
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->distinct()
                ->count('location');

            // Get repeated damage stats - locations with multiple repairs
            $repeatedDamageStats = Concern::whereNotNull('location')
                ->where('location', '!=', '')
                ->select('location')
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
                ->groupBy('location')
                ->having('total_count', '>', 1)
                ->orderBy('total_count', 'desc')
                ->get();

            // Get damaged parts stats
            $damagedPartsStats = Concern::whereNotNull('damaged_part')
                ->where('damaged_part', '!=', '')
                ->where('status', 'Resolved')
                ->select('damaged_part')
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
                ->groupBy('damaged_part')
                ->orderBy('total_count', 'desc')
                ->limit(10)
                ->get();

            return view('admin.reports', [
                'viewType' => $viewType,
                'concerns' => collect(),
                'categories' => Category::all(),
                'locationStats' => $locationStats,
                'categoryStats' => $categoryStats,
                'monthlyStats' => $monthlyStats,
                'totalConcerns' => $totalConcerns,
                'totalCost' => $totalCost,
                'uniqueLocations' => $uniqueLocations,
                'repeatedDamageStats' => $repeatedDamageStats,
                'damagedPartsStats' => $damagedPartsStats,
            ]);
        }

        $adminId = auth()->id();
        $query = Concern::with('categoryRelation', 'user');

        // Filter by archived status - uses pivot table
        if ($request->input('archived') === '1') {
            // Show concerns archived by this admin
            $query->archivedByUser($adminId);
        } elseif ($request->input('archived') === 'all') {
            // Show all concerns regardless of archive status
            // No additional filtering needed
        } else {
            // Default: show concerns NOT archived by this admin
            $query->notArchivedByUser($adminId);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%');
            });
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $concerns = $query->orderBy('created_at', 'desc')->get();
        $categories = Category::all();

        return view('admin.reports', compact('concerns', 'categories', 'viewType'));
    }

    // Export to CSV
    public function exportCsv(Request $request)
    {
        $query = Concern::with('categoryRelation', 'user', 'assignedTo');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $concerns = $query->get();

        $filename = 'concerns_export_'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($concerns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Title', 'Description', 'Location', 'Category', 'Priority', 'Status', 'Reported By', 'Assigned To', 'Created At', 'Resolved At']);

            foreach ($concerns as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->title,
                    $c->description,
                    $c->location,
                    $c->category->name ?? 'N/A',
                    $c->priority,
                    $c->status,
                    $c->user->name ?? 'Anonymous',
                    $c->assignedTo->name ?? 'Unassigned',
                    $c->created_at,
                    $c->resolved_at,
                ]);
            }
            fclose($file);
        };

        ActivityLog::log('export_created', 'Exported concerns to CSV');

        return response()->stream($callback, 200, $headers);
    }

    // Archive a concern (MIS only)
    public function archiveConcern(Request $request, $id)
    {
        // Only MIS can archive concerns
        if (auth()->user()->role !== 'mis') {
            return back()->with('error', 'You do not have permission to archive concerns.');
        }

        $concern = Concern::findOrFail($id);

        // Set admin_archived to true
        $concern->update(['admin_archived' => true]);

        ActivityLog::log(
            'concern_archived',
            'Concern archived by admin: '.$concern->title,
            $concern->id
        );

        return back()->with('success', 'Concern archived successfully!');
    }

    // Soft delete a concern (MIS only)
    public function softDeleteConcern(Request $request, $id)
    {
        // Only MIS can soft delete concerns
        if (auth()->user()->role !== 'mis') {
            return back()->with('error', 'You do not have permission to delete concerns.');
        }

        $concern = Concern::findOrFail($id);

        // Check if concern is assigned but not resolved - assigned concerns cannot be deleted unless resolved
        if ($concern->assigned_to && $concern->status !== 'Resolved') {
            return back()->with('error', 'Cannot delete assigned concerns that are not resolved. Please wait for resolution or unassign first.');
        }

        // Get or create the deleted folder
        $deletedFolder = ArchiveFolder::where('name', 'Deleted Reports')->first();
        if (! $deletedFolder) {
            $deletedFolder = ArchiveFolder::create([
                'name' => 'Deleted Reports',
                'description' => 'Reports that have been deleted and can be restored',
                'type' => 'reports',
                'is_system' => true,
                'item_count' => 0,
            ]);
        }

        $concern->archive_folder_id = $deletedFolder->id;
        $concern->is_deleted = true;
        $concern->deleted_by = auth()->id();
        $concern->save();

        // Update folder item count
        $deletedFolder->updateItemCount();

        ActivityLog::log(
            'concern_soft_deleted',
            'Concern soft deleted: '.$concern->title,
            $concern->id
        );

        return back()->with('success', 'Concern moved to deleted successfully!');
    }

    // Archive MIS Task Concern - separate from personal concerns
    public function archiveMisTaskConcern(Request $request, $id)
    {
        // Only MIS can archive MIS task concerns
        if (auth()->user()->role !== 'mis') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to archive MIS task concerns.'], 403);
            }
            return back()->with('error', 'You do not have permission to archive MIS task concerns.');
        }

        $concern = Concern::findOrFail($id);

        // Verify this concern is assigned to a MIS user
        $misUsers = User::where('role', 'mis')->pluck('id');
        if (!$misUsers->contains($concern->assigned_to)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This concern is not assigned to the MIS department.'], 403);
            }
            return back()->with('error', 'This concern is not assigned to the MIS department.');
        }

        // Archive for the current MIS user only (not affecting personal concerns)
        $concern->archivedByUsers()->syncWithoutDetaching([auth()->id() => ['archived_at' => now()]]);

        ActivityLog::log(
            'mis_task_archived',
            'MIS task concern archived: '.$concern->title,
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'MIS task concern archived successfully!']);
        }

        return back()->with('success', 'MIS task concern archived successfully!');
    }

    // Delete MIS Task Concern - separate from personal concerns
    public function deleteMisTaskConcern(Request $request, $id)
    {
        // Only MIS can delete MIS task concerns
        if (auth()->user()->role !== 'mis') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to delete MIS task concerns.'], 403);
            }
            return back()->with('error', 'You do not have permission to delete MIS task concerns.');
        }

        $concern = Concern::findOrFail($id);

        // Verify this concern is assigned to a MIS user
        $misUsers = User::where('role', 'mis')->pluck('id');
        if (!$misUsers->contains($concern->assigned_to)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This concern is not assigned to the MIS department.'], 403);
            }
            return back()->with('error', 'This concern is not assigned to the MIS department.');
        }

        // Check if concern is assigned but not resolved - assigned concerns cannot be deleted unless resolved
        if ($concern->assigned_to && $concern->status !== 'Resolved') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Cannot delete assigned concerns that are not resolved. Please wait for resolution or unassign first.'], 422);
            }
            return back()->with('error', 'Cannot delete assigned concerns that are not resolved. Please wait for resolution or unassign first.');
        }

        // For MIS tasks, we mark as deleted but don't affect the original concern for the user
        // Instead, we remove it from MIS view by marking it as deleted for MIS users only
        $concern->is_deleted = true;
        $concern->deleted_by = auth()->id();
        $concern->save();

        ActivityLog::log(
            'mis_task_deleted',
            'MIS task concern deleted: '.$concern->title,
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'MIS task concern deleted successfully!']);
        }

        return back()->with('success', 'MIS task concern deleted successfully!');
    }

    // Restore MIS Task Concern from archive
    public function restoreMisTaskConcern(Request $request, $id)
    {
        // Only MIS can restore MIS task concerns
        if (auth()->user()->role !== 'mis') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to restore MIS task concerns.'], 403);
            }
            return back()->with('error', 'You do not have permission to restore MIS task concerns.');
        }

        $concern = Concern::findOrFail($id);

        // Verify this concern is assigned to a MIS user
        $misUsers = User::where('role', 'mis')->pluck('id');
        if (!$misUsers->contains($concern->assigned_to)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This concern is not assigned to the MIS department.'], 403);
            }
            return back()->with('error', 'This concern is not assigned to the MIS department.');
        }

        // Remove from archive for the current MIS user
        $concern->archivedByUsers()->detach(auth()->id());

        ActivityLog::log(
            'mis_task_restored',
            'MIS task concern restored from archive: '.$concern->title,
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'MIS task concern restored successfully!']);
        }

        return back()->with('success', 'MIS task concern restored successfully!');
    }

    // Restore MIS Task Concern from deleted
    public function restoreDeletedMisTaskConcern(Request $request, $id)
    {
        // Only MIS can restore deleted MIS task concerns
        if (auth()->user()->role !== 'mis') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to restore deleted MIS task concerns.'], 403);
            }
            return back()->with('error', 'You do not have permission to restore deleted MIS task concerns.');
        }

        $concern = Concern::findOrFail($id);

        // Verify this concern was deleted by current MIS user
        if ($concern->deleted_by !== auth()->id()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You can only restore MIS task concerns that you deleted.'], 403);
            }
            return back()->with('error', 'You can only restore MIS task concerns that you deleted.');
        }

        // Restore from deleted state
        $concern->is_deleted = false;
        $concern->deleted_by = null;
        $concern->save();

        ActivityLog::log(
            'mis_task_restored_deleted',
            'MIS task concern restored from deleted: '.$concern->title,
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'MIS task concern restored from deleted successfully!']);
        }

        return back()->with('success', 'MIS task concern restored from deleted successfully!');
    }

    // User management
    public function users(Request $request)
    {
        $viewType = $request->get('view', 'active'); // 'active', 'archives', or 'deleted'

        // Handle locked users view
        if ($viewType === 'locked') {
            $perPage = $request->get('per_page', 20);
            $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 20;

            $lockedUsersList = User::hideSuperadmin()->where('is_deleted', false)
                ->whereNotNull('locked_until')
                ->orderBy('updated_at', 'desc')
                ->paginate($perPage);

            $lockedCount = $lockedUsersList->total();

            return view('admin.users', [
                'viewType'        => $viewType,
                'lockedUsersList' => $lockedUsersList,
                'lockedCount'     => $lockedCount,
                'users'           => collect(),
                'editUser'        => null,
                'archiveFolders'  => collect(),
                'deletedUsers'    => collect(),
            ]);
        }

        // Handle deleted users view
        if ($viewType === 'deleted') {
            $user = auth()->user();
            $days = $request->get('days', $user->users_auto_delete_days ?? 15);

            $deletedFolder = UserArchiveFolder::where('name', 'Deleted Users')->first();

            if ($deletedFolder) {
                $perPage = $request->get('per_page', 20);
                $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 20;
                
                $deletedUsers = User::hideSuperadmin()->withoutGlobalScope('not_deleted')
                    ->where('archive_folder_id', $deletedFolder->id)
                    ->where('is_deleted', true)
                    ->with('deletedBy')
                    ->orderBy('updated_at', 'desc')
                    ->paginate($perPage);
            } else {
                $deletedUsers = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            }

            return view('admin.users', [
                'viewType' => $viewType,
                'deletedUsers' => $deletedUsers,
                'users' => collect(),
                'editUser' => null,
                'archiveFolders' => collect(),
                'days' => $days,
            ]);
        }

        // Handle archive folders view
        if ($viewType === 'archives') {
            // Get archive folders (exclude Deleted Users system folder)
            $archiveFolders = UserArchiveFolder::where('name', '!=', 'Deleted Users')->orderBy('created_at', 'desc')->get();

            return view('admin.users', [
                'viewType' => $viewType,
                'users' => collect(),
                'editUser' => null,
                'archiveFolders' => $archiveFolders,
                'deletedUsers' => collect(),
            ]);
        }

        // Only show active (non-archived) users - deleted users are automatically excluded by global scope
        $query = User::hideSuperadmin()->where(function ($q) {
            $q->where('is_archived', false)->orWhereNull('is_archived');
        });

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Filter by role tab
        if ($request->filled('role_filter')) {
            $roleFilter = $request->input('role_filter');
            if ($roleFilter === 'staff') {
                $staffRoles = ['maintenance', 'mis', 'school_admin', 'building_admin', 'academic_head', 'program_head', 'principal_assistant'];
                $query->whereIn('role', $staffRoles);
            } else {
                $query->where('role', $roleFilter);
            }
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->where('department', 'like', '%'.$request->input('department').'%');
        }

        // Show active users with pagination
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 20;
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Get edit user from query parameter
        $editUserId = $request->query('edit');
        $editUser = $editUserId ? User::hideSuperadmin()->find($editUserId) : null;

        // Get archive folders (exclude Deleted Users system folder)
        $archiveFolders = UserArchiveFolder::where('name', '!=', 'Deleted Users')->orderBy('created_at', 'desc')->get();

        // Calculate total counts for role tabs (ignoring pagination)
        $totalAll = User::hideSuperadmin()->where(function ($q) {
            $q->where('is_archived', false)->orWhereNull('is_archived');
        })->count();

        $totalStudent = User::hideSuperadmin()->where(function ($q) {
            $q->where('is_archived', false)->orWhereNull('is_archived');
        })->where('role', 'student')->count();

        $totalFaculty = User::hideSuperadmin()->where(function ($q) {
            $q->where('is_archived', false)->orWhereNull('is_archived');
        })->where('role', 'faculty')->count();

        $staffRoles = ['maintenance', 'mis', 'school_admin', 'building_admin', 'academic_head', 'program_head', 'principal_assistant'];
        $totalStaff = User::hideSuperadmin()->where(function ($q) {
            $q->where('is_archived', false)->orWhereNull('is_archived');
        })->whereIn('role', $staffRoles)->count();

        $lockedCount = User::hideSuperadmin()->where('is_deleted', false)->whereNotNull('locked_until')->count();

        return view('admin.users', compact('users', 'editUser', 'viewType', 'archiveFolders', 'totalAll', 'totalStudent', 'totalFaculty', 'totalStaff', 'lockedCount'));
    }

    // Store new user
    public function storeUser(Request $request)
    {
        if (! auth()->user()->canAccess('users_create')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You do not have permission to perform this action.');
        }

        \Log::info('[storeUser] Request received', [
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
            'role'  => $request->input('role'),
            'phone' => $request->input('phone'),
            'has_password' => $request->filled('password'),
        ]);

        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'phone'    => 'nullable|regex:/^09[0-9]{9}$/',
                'role'     => 'required|in:student,faculty,maintenance,mis,school_admin,building_admin,academic_head,program_head,principal_assistant',
            ]);

            \Log::info('[storeUser] Validation passed');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[storeUser] Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        try {
            $user = User::create([
                'name'                  => trim($request->input('first_name') . ' ' . $request->input('last_name')),
                'email'                 => $request->input('email'),
                'password'              => Hash::make($request->input('password')),
                'role'                  => $request->input('role'),
                'phone'                 => $request->input('phone'),
                'department'            => $request->input('department'),
                'student_id'            => $request->input('student_id'),
                'force_password_change' => $request->input('role') === 'student',
                'permissions'           => $request->input('permissions', []),
                'created_by'            => auth()->id(),
            ]);

            \Log::info('[storeUser] User created successfully', ['user_id' => $user->id, 'email' => $user->email]);
        } catch (\Exception $e) {
            \Log::error('[storeUser] User::create failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        ActivityLog::log('user_created', "Created user: {$user->name}", $user->id, 'user', null, [
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'phone'      => $user->phone,
            'department' => $user->department,
            'student_id' => $user->student_id,
        ], ['target_user_id' => $user->id, 'target_user_name' => $user->name]);

        return redirect()->route('admin.users')->with('success', 'User created successfully!');
    }

    // Show edit user form
    public function editUser($uuid)
    {
        $user = User::hideSuperadmin()->where('uuid', $uuid)->firstOrFail();
        $users = User::hideSuperadmin()->get();

        return view('admin.users', compact('user', 'users'));
    }

    // Update user
    public function updateUser(Request $request, $uuid)
    {
        if (! auth()->user()->canAccess('users_edit')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You do not have permission to perform this action.');
        }

        $user = User::hideSuperadmin()->where('uuid', $uuid)->firstOrFail();

        // Prevent editing a user that was created by someone else
        if ($user->isProtectedFrom(auth()->user())) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You cannot edit a user that was created by another administrator.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You cannot edit a user that was created by another administrator.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone' => 'nullable|regex:/^09[0-9]{9}$/',
            'role' => 'required|in:student,faculty,maintenance,mis,school_admin,building_admin,academic_head,program_head,principal_assistant',
        ]);

        // Capture old values before changes
        $oldValues = [
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => ucfirst(str_replace('_', ' ', $user->role)),
            'phone'       => $user->phone,
            'department'  => $user->department,
            'student_id'  => $user->student_id,
            'permissions' => implode(', ', (array) ($user->permissions ?? [])) ?: '(none)',
        ];

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->role = $request->input('role');
        $user->phone = $request->input('phone');
        $user->department = $request->input('department');
        $user->student_id = $request->input('student_id');
        $user->is_admin = $request->has('is_admin');
        $user->permissions = $request->input('permissions', []);

        $passwordChanged = false;
        if ($request->filled('password')) {
            $request->validate(['password' => ['string', 'min:8', 'max:20', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/^\S+$/']]);
            $user->password = Hash::make($request->input('password'));
            $passwordChanged = true;
        }

        $user->save();

        $newValues = [
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => ucfirst(str_replace('_', ' ', $user->role)),
            'phone'       => $user->phone,
            'department'  => $user->department,
            'student_id'  => $user->student_id,
            'permissions' => implode(', ', (array) ($user->permissions ?? [])) ?: '(none)',
        ];

        if ($passwordChanged) {
            $oldValues['password'] = '(hidden)';
            $newValues['password'] = '(changed)';
        }

        ActivityLog::log('user_updated', "Updated user: {$user->name}", $user->id, 'user', $oldValues, $newValues, [
            'target_user_id' => $user->id,
            'target_user_name' => $user->name,
        ]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully!');
    }

    // Delete user - moves to Deleted Users folder for potential restore
    public function deleteUser($uuid)
    {
        if (! auth()->user()->canAccess('users_delete')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You do not have permission to perform this action.');
        }

        $user = User::hideSuperadmin()->where('uuid', $uuid)->firstOrFail();

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You cannot delete your own account!'], 403);
            }

            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account!');
        }

        // Prevent deleting a user created by another administrator
        if ($user->isProtectedFrom(auth()->user())) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You cannot delete a user that was created by another administrator.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You cannot delete a user that was created by another administrator.');
        }

        $userName = $user->name;

        // Get the existing Deleted Users folder (do not create new one)
        $deletedFolder = UserArchiveFolder::where('name', 'Deleted Users')->first();

        // If Deleted Users folder doesn't exist, create it
        if (! $deletedFolder) {
            $deletedFolder = UserArchiveFolder::create([
                'name' => 'Deleted Users',
                'description' => 'Users that have been deleted and can be restored',
                'user_count' => 0,
                'is_system' => true,
            ]);
        }

        // Move user to Deleted Users folder instead of hard delete
        $user->is_deleted = true;
        $user->is_archived = true;
        $user->archive_folder_id = $deletedFolder->id;
        $user->deleted_by = auth()->id();
        $user->save();

        // Update folder user count
        $deletedFolder->user_count = $deletedFolder->archivedUsers()->count();
        $deletedFolder->save();

        ActivityLog::log('user_deleted', "Deleted user: {$userName} (moved to Deleted Users folder)");

        if (request()->ajax()) {
            return response()->json(['success' => 'User deleted successfully!']);
        }

        return redirect()->route('admin.users')->with('success', 'User deleted successfully!');
    }

    // View deleted users
    public function deletedUsers(Request $request)
    {
        // Get the Deleted Users folder
        $deletedFolder = UserArchiveFolder::where('name', 'Deleted Users')->first();

        if (! $deletedFolder) {
            return redirect()->route('admin.users')->with('error', 'Deleted Users folder not found.');
        }

        $user = auth()->user();
        $days = $request->get('days', $user->users_auto_delete_days ?? 15);

        // Get users in the Deleted Users folder
        $users = User::hideSuperadmin()->withoutGlobalScope('not_deleted')
            ->where('archive_folder_id', $deletedFolder->id)
            ->where('is_deleted', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.deleted-users', compact('users', 'deletedFolder', 'days'));
    }

    // Restore a deleted user
    public function restoreDeletedUser($id)
    {
        $user = User::hideSuperadmin()->withoutGlobalScope('not_deleted')->findOrFail($id);

        if (! $user->is_deleted) {
            return redirect()->route('admin.users', ['view' => 'deleted'])->with('error', 'User is not in the Deleted Users folder.');
        }

        $userName = $user->name;
        $oldFolderId = $user->archive_folder_id;

        // Restore user to active state
        $user->is_deleted = false;
        $user->is_archived = false;
        $user->archive_folder_id = null;
        $user->deleted_by = null;
        $user->save();

        // Update the old folder's user count
        $oldFolder = UserArchiveFolder::find($oldFolderId);
        if ($oldFolder) {
            $oldFolder->user_count = $oldFolder->archivedUsers()->count();
            $oldFolder->save();
        }

        ActivityLog::log('user_restored', "Restored deleted user: {$userName}");

        return redirect()->route('admin.users', ['view' => 'deleted'])->with('success', "User '{$userName}' has been restored successfully!");
    }

    // Restore all deleted users
    public function restoreAllDeletedUsers(Request $request)
    {
        $deletedUsers = User::hideSuperadmin()->withoutGlobalScope('not_deleted')
            ->where('is_deleted', true)
            ->get();

        if ($deletedUsers->isEmpty()) {
            return redirect()->route('admin.users', ['view' => 'deleted'])->with('error', 'No deleted users to restore.');
        }

        $count = 0;
        foreach ($deletedUsers as $user) {
            $oldFolderId = $user->archive_folder_id;

            $user->is_deleted = false;
            $user->is_archived = false;
            $user->archive_folder_id = null;
            $user->deleted_by = null;
            $user->save();

            // Update the old folder's user count
            if ($oldFolderId) {
                $oldFolder = UserArchiveFolder::find($oldFolderId);
                if ($oldFolder) {
                    $oldFolder->user_count = $oldFolder->archivedUsers()->count();
                    $oldFolder->save();
                }
            }

            ActivityLog::log('user_restored', "Restored deleted user: {$user->name}");
            $count++;
        }

        return redirect()->route('admin.users', ['view' => 'deleted'])->with('success', "All {$count} deleted user(s) have been restored successfully!");
    }

    // Restore selected deleted users
    public function restoreSelectedDeletedUsers(Request $request)
    {
        $userIds = $request->input('user_ids', []);

        if (empty($userIds)) {
            return redirect()->route('admin.users', ['view' => 'deleted'])->with('error', 'No users selected.');
        }

        $count = 0;
        foreach ($userIds as $id) {
            $user = User::hideSuperadmin()->withoutGlobalScope('not_deleted')->find($id);
            if ($user && $user->is_deleted) {
                $oldFolderId = $user->archive_folder_id;

                $user->is_deleted = false;
                $user->is_archived = false;
                $user->archive_folder_id = null;
                $user->deleted_by = null;
                $user->save();

                // Update the old folder's user count
                $oldFolder = UserArchiveFolder::find($oldFolderId);
                if ($oldFolder) {
                    $oldFolder->user_count = $oldFolder->archivedUsers()->count();
                    $oldFolder->save();
                }

                ActivityLog::log('user_restored', "Restored deleted user: {$user->name}");
                $count++;
            }
        }

        return redirect()->route('admin.users', ['view' => 'deleted'])->with('success', "{$count} user(s) have been restored successfully!");
    }

    // Permanently delete a user from Deleted Users folder
    public function permanentDeleteUser($id)
    {
        $user = User::hideSuperadmin()->withoutGlobalScope('not_deleted')->findOrFail($id);

        if (! $user->is_deleted) {
            return redirect()->route('admin.users', ['view' => 'deleted'])->with('error', 'User is not in the Deleted Users folder.');
        }

        $userName = $user->name;
        $folderId = $user->archive_folder_id;

        // Permanently delete the user
        ActivityLog::log('user_permanent_delete', "Permanently deleted user: {$userName}");
        $user->forceDelete();

        // Update folder user count
        $folder = UserArchiveFolder::find($folderId);
        if ($folder) {
            $folder->user_count = $folder->archivedUsers()->count();
            $folder->save();
        }

        return redirect()->route('admin.users', ['view' => 'deleted'])->with('success', "User '{$userName}' has been permanently deleted!");
    }

    // Permanently delete all users in Deleted Users folder
    public function permanentDeleteAllDeleted()
    {
        $deletedFolder = UserArchiveFolder::where('name', 'Deleted Users')->first();

        if (! $deletedFolder) {
            return redirect()->route('admin.users', ['view' => 'deleted'])->with('error', 'Deleted Users folder not found.');
        }

        $users = User::hideSuperadmin()->withoutGlobalScope('not_deleted')
            ->where('archive_folder_id', $deletedFolder->id)
            ->where('is_deleted', true)
            ->get();

        $count = $users->count();

        foreach ($users as $user) {
            ActivityLog::log('user_permanent_delete', "Permanently deleted user: {$user->name}");
            $user->forceDelete();
        }

        // Reset folder count
        $deletedFolder->user_count = 0;
        $deletedFolder->save();

        return redirect()->route('admin.users', ['view' => 'deleted'])->with('success', "{$count} user(s) have been permanently deleted!");
    }

    // =====================================================
    // DELETED REPORTS MANAGEMENT
    // =====================================================

    // Auto-delete reports older than specified days
    public function autoDeleteOldReports(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|in:3,7,15,30',
        ]);

        $days = $request->input('days');
        $cutoffDate = now()->subDays($days);

        $deletedFolder = ArchiveFolder::where('name', 'Deleted Reports')->first();
        if (! $deletedFolder) {
            return response()->json(['success' => false, 'error' => 'Deleted Reports folder not found.']);
        }

        // Find reports older than the cutoff date
        $oldReports = Report::withTrashed()
            ->where('archive_folder_id', $deletedFolder->id)
            ->where('is_deleted', true)
            ->where('updated_at', '<', $cutoffDate)
            ->get();

        $count = $oldReports->count();

        if ($count > 0) {
            // Permanently delete the old reports
            foreach ($oldReports as $report) {
                // Delete associated files if any
                if ($report->photo_path) {
                    Storage::disk('public')->delete($report->photo_path);
                }
                $report->forceDelete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Automatically deleted {$count} report(s) older than {$days} days.",
            'deleted_count' => $count,
        ]);
    }

    // View deleted reports
    public function deletedReports(Request $request)
    {
        $deletedFolder = ArchiveFolder::where('name', 'Deleted Reports')->first();

        if (! $deletedFolder) {
            return redirect()->route('admin.reports')->with('error', 'Deleted Reports folder not found.');
        }

        $user = auth()->user();
        $days = $request->get('days', $user->reports_auto_delete_days ?? 15);

        $reports = Report::withTrashed()->where('archive_folder_id', $deletedFolder->id)
            ->where('is_deleted', true)
            ->where('deleted_at', '<=', now()->subDays($days))
            ->with(['user', 'category', 'deletedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.deleted-reports', compact('reports', 'deletedFolder', 'days'));
    }

    // Restore a deleted report
    public function restoreDeletedReport($id)
    {
        $report = Report::withTrashed()->findOrFail($id);

        if (! $report->is_deleted) {
            return redirect()->route('admin.deletedReports')->with('error', 'Report is not in the Deleted Reports folder.');
        }

        $reportTitle = $report->title;
        $oldFolderId = $report->archive_folder_id;

        // Restore report to active state
        $report->is_deleted = false;
        $report->is_archived = false;
        $report->archive_folder_id = null;
        $report->deleted_by = null;
        $report->restore();

        // Update the old folder's item count
        $oldFolder = ArchiveFolder::find($oldFolderId);
        if ($oldFolder) {
            $oldFolder->updateItemCount();
        }

        ActivityLog::log('report_restored', "Restored deleted report: {$reportTitle}");

        return redirect()->route('admin.reports', ['view' => 'active'])->with('success', "Report '{$reportTitle}' has been restored successfully!");
    }

    // Restore selected deleted reports
    public function restoreSelectedDeletedReports(Request $request)
    {
        $reportIds = $request->input('report_ids', []);

        if (empty($reportIds)) {
            return redirect()->route('admin.deletedReports')->with('error', 'No reports selected.');
        }

        $count = 0;
        foreach ($reportIds as $id) {
            $report = Report::find($id);
            if ($report && $report->is_deleted) {
                $oldFolderId = $report->archive_folder_id;

                $report->is_deleted = false;
                $report->is_archived = false;
                $report->archive_folder_id = null;
                $report->deleted_by = null;
                $report->save();

                // Update the old folder's item count
                $oldFolder = ArchiveFolder::find($oldFolderId);
                if ($oldFolder) {
                    $oldFolder->item_count = $oldFolder->reports()->count();
                    $oldFolder->save();
                }

                ActivityLog::log('report_restored', "Restored deleted report: {$report->title}");
                $count++;
            }
        }

        return redirect()->route('admin.reports', ['view' => 'active'])->with('success', "{$count} report(s) have been restored successfully!");
    }

    // Permanently delete a report from Deleted Reports folder
    public function permanentDeleteReport($id)
    {
        $report = Report::findOrFail($id);

        if (! $report->is_deleted) {
            return redirect()->route('admin.deletedReports')->with('error', 'Report is not in the Deleted Reports folder.');
        }

        $reportTitle = $report->title;
        $folderId = $report->archive_folder_id;

        // Permanently delete the report
        ActivityLog::log('report_permanent_delete', "Permanently deleted report: {$reportTitle}");
        $report->forceDelete();

        // Update folder item count
        $folder = ArchiveFolder::find($folderId);
        if ($folder) {
            $folder->item_count = $folder->reports()->count();
            $folder->save();
        }

        return redirect()->route('admin.deletedReports')->with('success', "Report '{$reportTitle}' has been permanently deleted!");
    }

    // Permanently delete all reports in Deleted Reports folder
    public function permanentDeleteAllReports()
    {
        $deletedFolder = ArchiveFolder::where('name', 'Deleted Reports')->first();

        if (! $deletedFolder) {
            return redirect()->route('admin.deletedReports')->with('error', 'Deleted Reports folder not found.');
        }

        $reports = Report::where('archive_folder_id', $deletedFolder->id)
            ->where('is_deleted', true)
            ->get();

        $count = $reports->count();

        foreach ($reports as $report) {
            ActivityLog::log('report_permanent_delete', "Permanently deleted report: {$report->title}");
            $report->forceDelete();
        }

        $deletedFolder->item_count = 0;
        $deletedFolder->save();

        return redirect()->route('admin.deletedReports')->with('success', "{$count} report(s) have been permanently deleted!");
    }

    // =====================================================
    // DELETED EVENTS MANAGEMENT
    // =====================================================

    // View deleted events
    public function deletedEvents(Request $request)
    {
        $deletedFolder = ArchiveFolder::where('name', 'Deleted Events')->first();

        if (! $deletedFolder) {
            return redirect()->route('admin.events')->with('error', 'Deleted Events folder not found.');
        }

        $user = auth()->user();
        $days = $request->get('days', $user->event_requests_auto_delete_days ?? 15);

        $events = EventRequest::where('archive_folder_id', $deletedFolder->id)
            ->where('is_deleted', true)
            ->where('deleted_at', '<=', now()->subDays($days))
            ->with(['user', 'deletedBy'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.deleted-events', compact('events', 'deletedFolder', 'days'));
    }

    // View a deleted event details
    public function viewDeletedEvent($id)
    {
        $event = EventRequest::with(['user', 'deletedBy'])->findOrFail($id);

        if (! $event->is_deleted) {
            return redirect()->route('admin.deletedEvents')->with('error', 'Event is not in the Deleted Events folder.');
        }

        // Use existing view modal instead of separate page
        return redirect()->route('admin.deletedEvents')->with('selected_event', $event->id);
    }

    // Restore a deleted event
    public function restoreDeletedEvent($id)
    {
        try {
            $event = EventRequest::findOrFail($id);

            if (! $event->is_deleted) {
                return redirect()->route('admin.deletedEvents')->with('error', 'Event is not in the Deleted Events folder.');
            }

            $eventTitle = $event->title;
            $oldFolderId = $event->archive_folder_id;

            // Restore event to active state
            $event->is_deleted = false;
            $event->is_archived = false;
            $event->archive_folder_id = null;
            $event->deleted_by = null;
            $event->save();

            // Update the old folder's item count
            $oldFolder = ArchiveFolder::find($oldFolderId);
            if ($oldFolder) {
                $oldFolder->item_count = $oldFolder->eventRequests()->count();
                $oldFolder->save();
            }

            ActivityLog::log('event_restored', "Restored deleted event: {$eventTitle}");

            return redirect()->route('admin.deletedEvents')->with('success', "Event '{$eventTitle}' has been restored successfully!");
        } catch (\Exception $e) {
            \Log::error('Restore deleted event failed: '.$e->getMessage());

            return redirect()->route('admin.deletedEvents')->with('error', 'Failed to restore event. Please try again.');
        }
    }

    // Restore selected deleted events
    public function restoreSelectedDeletedEvents(Request $request)
    {
        $eventIds = $request->input('event_ids', []);

        if (empty($eventIds)) {
            return redirect()->route('admin.deletedEvents')->with('error', 'No events selected.');
        }

        $count = 0;
        foreach ($eventIds as $id) {
            $event = EventRequest::find($id);
            if ($event && $event->is_deleted) {
                $oldFolderId = $event->archive_folder_id;

                $event->is_deleted = false;
                $event->is_archived = false;
                $event->archive_folder_id = null;
                $event->deleted_by = null;
                $event->save();

                // Update the old folder's item count
                $oldFolder = ArchiveFolder::find($oldFolderId);
                if ($oldFolder) {
                    $oldFolder->item_count = $oldFolder->eventRequests()->count();
                    $oldFolder->save();
                }

                ActivityLog::log('event_restored', "Restored deleted event: {$event->title}");
                $count++;
            }
        }

        return redirect()->route('admin.deletedEvents')->with('success', "{$count} event(s) have been restored successfully!");
    }

    // Permanently delete an event from Deleted Events folder
    public function permanentDeleteEvent($id)
    {
        $event = EventRequest::findOrFail($id);

        if (! $event->is_deleted) {
            return redirect()->route('admin.deletedEvents')->with('error', 'Event is not in the Deleted Events folder.');
        }

        $eventTitle = $event->title;
        $folderId = $event->archive_folder_id;

        // Permanently delete the event
        ActivityLog::log('event_permanent_delete', "Permanently deleted event: {$eventTitle}");
        $event->forceDelete();

        // Update folder item count
        $folder = ArchiveFolder::find($folderId);
        if ($folder) {
            $folder->item_count = $folder->eventRequests()->count();
            $folder->save();
        }

        return redirect()->route('admin.deletedEvents')->with('success', "Event '{$eventTitle}' has been permanently deleted!");
    }

    // Permanently delete all events in Deleted Events folder
    public function permanentDeleteAllEvents()
    {
        $deletedFolder = ArchiveFolder::where('name', 'Deleted Events')->first();

        if (! $deletedFolder) {
            return redirect()->route('admin.deletedEvents')->with('error', 'Deleted Events folder not found.');
        }

        $events = EventRequest::where('archive_folder_id', $deletedFolder->id)
            ->where('is_deleted', true)
            ->get();

        $count = $events->count();

        foreach ($events as $event) {
            ActivityLog::log('event_permanent_delete', "Permanently deleted event: {$event->title}");
            $event->forceDelete();
        }

        $deletedFolder->item_count = 0;
        $deletedFolder->save();

        return redirect()->route('admin.deletedEvents')->with('success', "{$count} event(s) have been permanently deleted!");
    }

    // Archive user
    public function archiveUser($uuid)
    {
        if (! auth()->user()->canAccess('users_archive')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You do not have permission to perform this action.');
        }

        $user = User::hideSuperadmin()->where('uuid', $uuid)->firstOrFail();

        // Prevent archiving yourself
        if ($user->id === auth()->id()) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You cannot archive your own account!'], 403);
            }

            return redirect()->route('admin.users')->with('error', 'You cannot archive your own account!');
        }

        // Prevent archiving a user created by another administrator
        if ($user->isProtectedFrom(auth()->user())) {
            if (request()->ajax()) {
                return response()->json(['error' => 'You cannot archive a user that was created by another administrator.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You cannot archive a user that was created by another administrator.');
        }

        // Get or create the 2025-2026 archive folder
        $folderName = '2025-2026';
        $archiveFolder = UserArchiveFolder::where('name', $folderName)->first();
        if (! $archiveFolder) {
            $archiveFolder = UserArchiveFolder::create([
                'name' => $folderName,
                'description' => 'Archived users for school year '.$folderName,
                'user_count' => 0,
                'is_system' => false,
            ]);
        }

        $user->is_archived = true;
        $user->archive_folder_id = $archiveFolder->id;
        $user->save();

        // Update folder user count
        $archiveFolder->user_count = User::where('archive_folder_id', $archiveFolder->id)->count();
        $archiveFolder->save();

        ActivityLog::log('user_archived', "Archived user: {$user->name} to folder '{$folderName}'");

        if (request()->ajax()) {
            return response()->json(['success' => "User archived successfully to folder '{$folderName}'!"]);
        }

        return redirect()->route('admin.users')->with('success', "User archived successfully to folder '{$folderName}'!");
    }

    // Restore user
    public function restoreUser($uuid)
    {
        // Support both UUID and numeric ID
        $user = is_numeric($uuid)
            ? User::hideSuperadmin()->where('id', $uuid)->firstOrFail()
            : User::hideSuperadmin()->where('uuid', $uuid)->firstOrFail();

        // Get the folder before clearing
        $folderId = $user->archive_folder_id;

        $user->is_archived = false;
        $user->archive_folder_id = null;
        $user->save();

        ActivityLog::log('user_restored', "Restored user from archive: {$user->name}");

        if (request()->ajax()) {
            return response()->json(['success' => 'User restored successfully!']);
        }

        // Check remaining users in the folder
        if ($folderId) {
            $folder = UserArchiveFolder::find($folderId);
            if ($folder) {
                $remainingUsers = $folder->archivedUsers()->count();
                if ($remainingUsers === 0) {
                    // Folder is now empty — delete it and go to archives list
                    $folderName = $folder->name;
                    $folder->delete();
                    ActivityLog::log('archive_folder_deleted', "Deleted empty archive folder: {$folderName}");
                    return redirect()->route('admin.users', ['view' => 'archives'])
                        ->with('success', 'User restored successfully! The folder is now empty and has been removed.');
                } else {
                    // Still users in folder — stay in the folder view
                    $folder->user_count = $remainingUsers;
                    $folder->save();
                    return redirect()->route('admin.archiveFolderUsers', $folderId)
                        ->with('success', 'User restored successfully!');
                }
            }
        }

        return redirect()->route('admin.users', ['view' => 'archives'])
            ->with('success', 'User restored successfully!');
    }

    // Restore selected users
    public function restoreSelectedUsers(Request $request)
    {
        // Handle both array and JSON string input
        $userIdsInput = $request->input('user_ids');

        // If user_ids is a JSON string, decode it
        if (is_string($userIdsInput)) {
            $userIds = json_decode($userIdsInput, true);
            if (! is_array($userIds)) {
                return redirect()->back()->with('error', 'Invalid user IDs format.');
            }
        } else {
            $userIds = $userIdsInput;
        }

        // Validate the decoded array
        if (empty($userIds)) {
            return redirect()->back()->with('error', 'No users selected.');
        }

        $users = User::hideSuperadmin()->whereIn('id', $userIds)->get();

        $count = 0;
        $folderIds = [];

        foreach ($users as $user) {
            if ($user->is_archived) {
                $folderIds[] = $user->archive_folder_id;

                $user->is_archived = false;
                $user->archive_folder_id = null;
                $user->save();

                $count++;
                ActivityLog::log('user_restored', "Restored user from archive: {$user->name}");
            }
        }

        // Update folder user counts and delete empty folders
        $uniqueFolderIds = array_unique(array_filter($folderIds));
        foreach ($uniqueFolderIds as $folderId) {
            $folder = UserArchiveFolder::find($folderId);
            if ($folder) {
                $remainingUsers = $folder->archivedUsers()->count();
                if ($remainingUsers == 0) {
                    // Delete the folder if it's now empty
                    $folderName = $folder->name;
                    $folder->delete();
                    ActivityLog::log('archive_folder_deleted', "Deleted empty archive folder: {$folderName}");
                } else {
                    $folder->user_count = $remainingUsers;
                    $folder->save();
                }
            }
        }

        return redirect()->back()->with('success', "Successfully restored {$count} users!");
    }

    // Restore all users in a folder
    public function restoreAllFolderUsers($folder_id)
    {
        $folder = UserArchiveFolder::findOrFail($folder_id);
        $folderName = $folder->name;

        $users = User::where('archive_folder_id', $folder_id)->get();
        $count = 0;

        foreach ($users as $user) {
            $user->is_archived = false;
            $user->archive_folder_id = null;
            $user->save();

            $count++;
            ActivityLog::log('user_restored', "Restored user from archive: {$user->name}");
        }

        // Delete the folder if all users were restored
        if ($count > 0) {
            $folder->delete();
            ActivityLog::log('archive_folder_deleted', "Deleted empty archive folder: {$folderName}");
        }

        return redirect()->route('admin.users', ['view' => 'archives'])->with('success', "Successfully restored {$count} users from folder '{$folderName}'! The empty folder has been deleted.");
    }

    // Archive all users
    public function archiveAllUsers(Request $request)
    {
        $request->validate([
            'folder_name' => 'required|string|max:255',
        ]);

        $folderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->input('folder_name'));
        $folderName = trim($folderName, '_');

        // Sanitize folder name to prevent path traversal
        $folderName = str_replace(['..', '/', '\\'], '', $folderName);

        // Check if folder already exists, if not create it
        $archiveFolder = UserArchiveFolder::where('name', $folderName)->first();
        if (! $archiveFolder) {
            $archiveFolder = UserArchiveFolder::create([
                'name' => $folderName,
                'description' => 'Archived on '.now()->format('M d, Y'),
                'user_count' => 0,
            ]);
        }

        // Use the public disk to store archive files
        $archivePath = 'archive/'.$folderName;

        // Create the directory if it doesn't exist
        if (! Storage::disk('public')->exists($archivePath)) {
            Storage::disk('public')->makeDirectory($archivePath);
        }

        // Get all non-archived users (except the current logged in user)
        $usersToArchive = User::where('is_archived', false)
            ->where('id', '!=', auth()->id())
            ->get();

        $count = 0;
        $userData = [];

        foreach ($usersToArchive as $user) {
            $user->is_archived = true;
            $user->archive_folder_id = $archiveFolder->id;
            $user->save();

            // Collect user data for the archive file
            $userData[] = [
                'id' => $user->id,
                'student_id' => $user->student_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'phone' => $user->phone,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'archived_at' => now()->format('Y-m-d H:i:s'),
            ];

            $count++;
        }

        // Update folder user count
        $archiveFolder->user_count = $archiveFolder->archivedUsers()->count();
        $archiveFolder->save();

        // Save user data to a JSON file in the archive folder
        if (! empty($userData)) {
            $jsonFile = $archivePath.'/users.json';
            Storage::disk('public')->put($jsonFile, json_encode($userData, JSON_PRETTY_PRINT));

            // Also create a CSV file
            $csvFile = $archivePath.'/users.csv';
            $csvContent = "ID,Student ID,Name,Email,Role,Department,Phone,Created At,Archived At\n";
            foreach ($userData as $row) {
                $csvContent .= implode(',', $row)."\n";
            }
            Storage::disk('public')->put($csvFile, $csvContent);
        }

        ActivityLog::log('users_archived_all', "Archived {$count} users to folder: {$folderName}");

        return redirect()->route('admin.users')->with('success', "Successfully archived {$count} users to folder '{$folderName}'!");
    }

    // Delete all users (soft delete - move to Deleted Users folder)
    public function deleteAllUsers(Request $request)
    {
        // Get or create the "Deleted Users" system folder
        $deletedFolder = UserArchiveFolder::firstOrCreate(
            ['name' => 'Deleted Users'],
            [
                'description' => 'System folder for deleted users',
                'user_count' => 0,
                'is_system' => true,
            ]
        );

        // Get all non-archived users (except the current logged in user)
        $usersToDelete = User::where('is_archived', false)
            ->where('id', '!=', auth()->id())
            ->get();

        $count = 0;

        foreach ($usersToDelete as $user) {
            $user->is_deleted = true;
            $user->deleted_by = auth()->id();
            $user->archive_folder_id = $deletedFolder->id;
            $user->save();
            $count++;
        }

        // Update folder user count
        $deletedFolder->user_count = $deletedFolder->archivedUsers()->count();
        $deletedFolder->save();

        ActivityLog::log('users_deleted_all', "Soft deleted {$count} users to Deleted Users folder");

        return redirect()->route('admin.users')->with('success', "Successfully deleted {$count} users!");
    }

    // Archive selected users
    public function archiveSelectedUsers(Request $request)
    {
        // Handle both array and comma-separated string input
        $userIdsInput = $request->input('user_ids');

        if (is_string($userIdsInput)) {
            // Convert comma-separated string to array
            $userIds = array_filter(array_map('trim', explode(',', $userIdsInput)));
            $request->merge(['user_ids' => $userIds]);
        }

        $request->validate([
            'folder_name' => 'required|string|max:255',
            'user_ids' => 'required',
        ]);

        // Accept either an array or comma-separated string
        $userIdsInput = $request->input('user_ids');
        if (is_array($userIdsInput)) {
            $userIds = $userIdsInput;
        } else {
            // Handle comma-separated string
            $userIds = array_filter(array_map('trim', explode(',', (string) $userIdsInput)));
        }

        // Validate that user_ids contains valid IDs
        if (empty($userIds)) {
            return redirect()->route('admin.users')->with('error', 'No users selected for archiving!');
        }

        $folderName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $request->folder_name);
        $folderName = trim($folderName, '_');

        // Sanitize folder name to prevent path traversal
        $folderName = str_replace(['..', '/', '\\'], '', $folderName);

        // Check if folder already exists, if not create it
        $archiveFolder = UserArchiveFolder::where('name', $folderName)->first();
        if (! $archiveFolder) {
            $archiveFolder = UserArchiveFolder::create([
                'name' => $folderName,
                'description' => 'Archived on '.now()->format('M d, Y'),
                'user_count' => 0,
            ]);
        }

        // Use the public disk to store archive files
        $archivePath = 'archive/'.$folderName;

        // Create the directory if it doesn't exist
        if (! Storage::disk('public')->exists($archivePath)) {
            Storage::disk('public')->makeDirectory($archivePath);
        }

        // Get selected users (except the current logged in user)
        // After validation, user_ids should be a proper array (from merge or original input)
        $userIdsValue = $request->input('user_ids');
        $userIds = is_array($userIdsValue) ? $userIdsValue :
                   array_filter(array_map('trim', explode(',', (string) $userIdsValue)));
        $usersToArchive = User::whereIn('id', $userIds)
            ->where('is_archived', false)
            ->where('id', '!=', auth()->id())
            ->get();

        $count = 0;
        $userData = [];

        foreach ($usersToArchive as $user) {
            $user->is_archived = true;
            $user->archive_folder_id = $archiveFolder->id;
            $user->save();

            // Collect user data for the archive file
            $userData[] = [
                'id' => $user->id,
                'student_id' => $user->student_id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'phone' => $user->phone,
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                'archived_at' => now()->format('Y-m-d H:i:s'),
            ];

            $count++;
        }

        // Update folder user count
        $archiveFolder->user_count = $archiveFolder->archivedUsers()->count();
        $archiveFolder->save();

        // Save user data to a JSON file in the archive folder
        if (! empty($userData)) {
            $jsonFile = $archivePath.'/users.json';
            Storage::disk('public')->put($jsonFile, json_encode($userData, JSON_PRETTY_PRINT));

            // Also create a CSV file
            $csvFile = $archivePath.'/users.csv';
            $csvContent = "ID,Student ID,Name,Email,Role,Department,Phone,Created At,Archived At\n";
            foreach ($userData as $row) {
                $csvContent .= implode(',', $row)."\n";
            }
            Storage::disk('public')->put($csvFile, $csvContent);
        }

        ActivityLog::log('users_archived_selected', "Archived {$count} selected users to folder: {$folderName}");

        return redirect()->route('admin.users')->with('success', "Successfully archived {$count} selected users to folder '{$folderName}'!");
    }

    // View archive folders (combined for users and items)
    public function archiveFolders(Request $request)
    {
        $type = $request->get('type', '');

        // Get user archive folders
        $userFolders = UserArchiveFolder::orderBy('created_at', 'desc')->get();

        // Get item archive folders
        $query = ArchiveFolder::query();
        if ($type) {
            $query->where('type', $type);
        }
        $itemFolders = $query->orderBy('created_at', 'desc')->get();

        // Get unorganized archived items (not in any folder)
        $unorganizedConcerns = Concern::where('is_archived', true)
            ->whereNull('archive_folder_id')
            ->with('categoryRelation', 'user')
            ->orderBy('updated_at', 'desc')
            ->get();

        $unorganizedReports = Report::where('is_archived', true)
            ->whereNull('archive_folder_id')
            ->with('category', 'user')
            ->orderBy('updated_at', 'desc')
            ->get();

        $unorganizedFacilities = FacilityRequest::where('is_archived', true)
            ->whereNull('archive_folder_id')
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Merge both types of folders for the view
        $folders = $itemFolders;

        return view('admin.archive-folders', compact(
            'folders',
            'userFolders',
            'itemFolders',
            'unorganizedConcerns',
            'unorganizedReports',
            'unorganizedFacilities',
            'type'
        ));
    }

    // View users in a specific archive folder
    public function archiveFolderUsers($id)
    {
        $folder = UserArchiveFolder::findOrFail($id);
        $users = User::where('archive_folder_id', $id)->orderBy('name', 'asc')->get();

        return view('admin.archive-folder-users', compact('folder', 'users'));
    }

    // Delete archive folder
    public function deleteArchiveFolder($id)
    {
        // Try to find in UserArchiveFolder first
        $userFolder = UserArchiveFolder::find($id);

        if ($userFolder) {
            if ($userFolder->is_system) {
                return back()->with('error', 'Cannot delete system folder!');
            }

            // Delete all users in this folder (permanently remove archived users)
            $users = User::where('archive_folder_id', $id)->get();
            $count = $users->count();

            foreach ($users as $user) {
                ActivityLog::log('user_deleted', "Permanently deleted archived user: {$user->name}");
                $user->forceDelete();
            }

            $folderName = $userFolder->name;
            $userFolder->delete();

            ActivityLog::log('archive_folder_deleted', "Deleted archive folder: {$folderName} and {$count} users");

            return back()->with('success', "Archive folder '{$folderName}' deleted with {$count} users!");
        }

        // Try to find in ArchiveFolder
        $folder = ArchiveFolder::find($id);

        if ($folder) {
            if ($folder->is_system) {
                return back()->with('error', 'Cannot delete system folder.');
            }

            // Set all items in this folder to null (unorganized)
            Concern::where('archive_folder_id', $id)->update(['archive_folder_id' => null]);
            Report::where('archive_folder_id', $id)->update(['archive_folder_id' => null]);
            FacilityRequest::where('archive_folder_id', $id)->update(['archive_folder_id' => null]);

            $folderName = $folder->name;
            $folder->delete();

            ActivityLog::log('archive_folder_deleted', "Deleted archive folder: {$folderName}");

            return back()->with('success', 'Archive folder deleted successfully!');
        }

        return back()->with('error', 'Archive folder not found.');
    }

    // Delete all archived users
    public function deleteAllArchived()
    {
        // Get count before deletion
        $archivedCount = User::where('is_archived', true)->count();

        // Prevent deleting the current logged in user if they are archived
        $currentUserId = auth()->id();

        // Delete all archived users except the current logged in user
        User::where('is_archived', true)
            ->where('id', '!=', $currentUserId)
            ->delete();

        // Update all folder counts
        $folders = UserArchiveFolder::all();
        foreach ($folders as $folder) {
            $folder->user_count = $folder->archivedUsers()->count();
            $folder->save();
        }

        ActivityLog::log('users_archived_deleted', "Deleted {$archivedCount} archived users");

        return redirect()->route('admin.users')->with('success', "Successfully deleted {$archivedCount} archived users!");
    }

    // Import users from CSV / XLSX
    public function importUsers(Request $request)
    {
        if (! auth()->user()->canAccess('users_create')) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to perform this action.'], 403);
            }
            return redirect()->route('admin.users')->with('error', 'You do not have permission to perform this action.');
        }

        set_time_limit(300);

        $request->validate([
            'file'         => 'required',
            'default_role' => 'required|in:student,faculty',
            'file_format'  => 'required|in:masterlist,standard',
        ]);

        $isMasterlist = $request->input('file_format') === 'masterlist';
        $defaultRole  = $request->input('default_role', 'student');
        $folderName   = $request->input('archive_folder_name', '2025-2026');
        $extension    = strtolower($request->file('file')->getClientOriginalExtension());
        $filePath     = $request->file('file')->getRealPath();

        // Resolve permissions for imported users
        // If custom permissions were submitted, use them; otherwise fall back to role defaults
        $importPermissions = $request->has('import_use_custom_permissions')
            ? $request->input('import_permissions', [])
            : \App\Models\User::defaultPermissions($defaultRole);

        // Build flat array of rows
        $allRows = [];
        if (in_array($extension, ['xlsx', 'xls'])) {
            $xlsx = \Shuchkin\SimpleXLSX::parse($filePath);
            if ($xlsx) {
                $allRows = $xlsx->rows();
            }
        } else {
            $delimiter = $isMasterlist ? "\t" : ',';
            $handle = fopen($filePath, 'r');
            while (($row = fgetcsv($handle, 2000, $delimiter)) !== false) {
                $allRows[] = $row;
            }
            fclose($handle);
        }

        $rowCount = 0;
        $skippedRows = [];

        $existingEmails     = User::selectRaw('LOWER(email) as email')->pluck('email')->toArray();
        $existingStudentIds = User::selectRaw('LOWER(student_id) as student_id')->whereNotNull('student_id')->pluck('student_id')->toArray();

        $archiveFolder = UserArchiveFolder::where('name', $folderName)->first();
        if (! $archiveFolder) {
            $archiveFolder = UserArchiveFolder::create([
                'name'        => $folderName,
                'description' => 'Users imported for school year '.$folderName,
                'user_count'  => 0,
                'is_system'   => false,
            ]);
        }

        $usersToCreate = [];

        foreach ($allRows as $rowIndex => $row) {
            $row = array_map(fn($v) => trim((string) $v), $row);

            // Skip header row (first row) - it contains column names not data
            if ($rowIndex === 0) {
                $skippedRows[] = "Row 0: skipped (header row)";
                continue;
            }

            // Skip completely empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            if ($isMasterlist) {
                if ($defaultRole === 'faculty') {
                    if (count($row) < 6) {
                        $skippedRows[] = "Row {$rowIndex}: too few columns (".count($row).")";
                        continue;
                    }

                    $empNumber = preg_replace('/\s+/', '', $row[5]);
                    if (! preg_match('/^NVS\d+/i', $empNumber)) {
                        $skippedRows[] = "Row {$rowIndex}: emp number '{$empNumber}' doesn't match NVS pattern";
                        continue;
                    }

                    $lastName   = $row[1];
                    $firstName  = $row[2];
                    $middleName = $row[3];

                    $name = trim($firstName . ($middleName ? ' '.$middleName : '') . ' ' . $lastName);

                    $lastNameSlug  = strtolower(preg_replace('/[^a-zA-Z]/', '', $lastName));
                    $firstNameSlug = strtolower(preg_replace('/[^a-zA-Z]/', '', $firstName));
                    $email         = $firstNameSlug . '.' . $lastNameSlug . '@novaliches.sti.edu.ph';

                    $lastNameClean  = ucfirst(strtolower(preg_replace('/[^a-zA-Z]/', '', $lastName)));
                    $firstNameClean = ucfirst(strtolower(preg_replace('/[^a-zA-Z]/', '', $firstName)));
                    $password       = $lastNameClean . '_' . $firstNameClean . '_' . now()->year;

                    $studentId  = $empNumber;
                    $role       = 'faculty';
                    $department = null;
                    $level      = null;

                } else {
                    if (count($row) < 4) {
                        $skippedRows[] = "Row {$rowIndex}: too few columns (".count($row).")";
                        continue;
                    }
                    $studentId = $row[0];
                    if (! preg_match('/^\d{11}$/', $studentId)) {
                        $skippedRows[] = "Row {$rowIndex}: student ID '{$studentId}' doesn't match 11-digit pattern";
                        continue;
                    }

                    $lastName   = $row[2];
                    $firstName  = $row[3];
                    $middleName = $row[4] ?? '';
                    $program    = $row[5] ?? '';
                    $level      = ($row[6] ?? '') ?: null;

                    $name = trim($firstName . ($middleName ? ' '.$middleName : '') . ' ' . $lastName);

                    $lastNameSlug  = strtolower(preg_replace('/[^a-zA-Z]/', '', $lastName));
                    $last6         = substr($studentId, -6);
                    $email         = $lastNameSlug . '.' . $last6 . '@novaliches.sti.edu.ph';

                    $firstInitial  = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $firstName), 0, 1));
                    $lastInitial   = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $lastName), 0, 1));
                    $last6         = substr($studentId, -6);
                    $password      = '@' . $firstInitial . $lastInitial . '_' . $last6;

                    $role       = 'student';
                    $department = $program ?: null;
                }

            } else {
                if (count($row) < 3) {
                    $skippedRows[] = "Row {$rowIndex}: too few columns (".count($row).")";
                    continue;
                }
                $studentId  = $row[0];
                $email      = $row[1];
                $password   = $row[2];
                $role       = isset($row[3]) ? strtolower($row[3]) : $defaultRole;
                $name       = $studentId;
                $department = null;
                $level      = null;

                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $skippedRows[] = "Row {$rowIndex}: invalid email '{$email}'";
                    continue;
                }
                if (! in_array($role, ['student', 'faculty', 'maintenance', 'mis'])) {
                    $role = $defaultRole;
                }
            }

            $emailLower     = strtolower($email);
            $studentIdLower = strtolower($studentId);

            if (in_array($emailLower, $existingEmails)) {
                $skippedRows[] = "Row {$rowIndex}: email '{$email}' already exists";
                continue;
            }
            if (! empty($studentId) && in_array($studentIdLower, $existingStudentIds)) {
                $skippedRows[] = "Row {$rowIndex}: student ID '{$studentId}' already exists";
                continue;
            }

            $usersToCreate[] = [
                'uuid'                  => (string) \Illuminate\Support\Str::uuid(),
                'student_id'            => $studentId,
                'name'                  => $name,
                'email'                 => $email,
                'password'              => password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]),
                'role'                  => $role,
                'department'            => $department,
                'level'                 => $level,
                'force_password_change' => true,
                'archive_folder_id'     => $archiveFolder->id,
                'is_archived'           => true,
                'is_deleted'            => false,
                'failed_login_attempts' => 0,
                'otp_attempts'          => 0,
                'is_admin'              => false,
                'permissions'           => json_encode($importPermissions),
                'created_at'            => now(),
                'updated_at'            => now(),
            ];

            $existingEmails[]     = $emailLower;
            $existingStudentIds[] = $studentIdLower;
            $rowCount++;
        }

        if (! empty($usersToCreate)) {
            foreach (array_chunk($usersToCreate, 500) as $chunk) {
                User::insert($chunk);
            }
            $archiveFolder->user_count = User::where('archive_folder_id', $archiveFolder->id)->count();
            $archiveFolder->save();
        }

        ActivityLog::log('users_imported', "Imported {$rowCount} users to folder '{$folderName}'");

        \Log::info('Import debug', [
            'total_rows' => count($allRows),
            'imported' => $rowCount,
            'skipped_count' => count($skippedRows),
            'first_10_skipped' => array_slice($skippedRows, 0, 10),
            'format' => $isMasterlist ? 'masterlist' : 'standard',
            'role' => $defaultRole,
            'extension' => $extension,
        ]);

        $debugMsg = count($skippedRows) > 0
            ? ' (Skipped '.count($skippedRows).' rows. First reason: '.($skippedRows[0] ?? 'none').')'
            : '';

        return redirect()->route('admin.users')->with('success', "Successfully imported {$rowCount} users!{$debugMsg}");
    }

    // Activity logs
    public function logs(Request $request)
    {
        $currentUser = auth()->user();
        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 20;
        $isArchived = $request->input('view') === 'archived';

        $query = ActivityLog::with('user', 'concern')
            ->where('is_archived', $isArchived)
            // Never expose superadmin actions to regular admins
            ->whereDoesntHave('user', fn($q) => $q->withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('is_superadmin', true)->orWhere('role', 'superadmin');
                })
            );

        if ($currentUser && $currentUser->role === 'mis') {
            $query->where(function ($q) {
                $q->whereNotNull('item_user_id')
                    ->orWhere('action', 'like', 'user_%')
                    ->orWhere('action', 'like', 'users_%')
                    ->orWhere('action', 'like', 'log_%')
                    ->orWhere('action', 'like', 'logs_%');
            });
        } elseif ($currentUser && $currentUser->role === 'building_admin') {
            $query->where(function ($q) {
                $q->whereNotNull('report_id')
                    ->orWhereNotNull('event_request_id')
                    ->orWhereNotNull('concern_id')
                    ->orWhere('action', 'like', 'report_%')
                    ->orWhere('action', 'like', 'event_%')
                    ->orWhere('action', 'like', 'concern_%')
                    ->orWhereIn('action', ['status_updated', 'resolution_added', 'cost_updated']);
            });
        }

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', '%'.$search.'%'));
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $folders = $isArchived ? LogArchiveFolder::orderBy('created_at', 'desc')->get() : collect();

        return view('admin.logs', compact('logs', 'folders', 'isArchived'));
    }

    public function restoreLog(Request $request, ActivityLog $log)
    {
        $folderId = $log->log_archive_folder_id;

        $log->update([
            'is_archived'           => false,
            'archived_at'           => null,
            'archived_by'           => null,
            'log_archive_folder_id' => null,
        ]);

        // Update folder log count
        if ($folderId) {
            $folder = LogArchiveFolder::find($folderId);
            if ($folder) {
                $remaining = ActivityLog::where('log_archive_folder_id', $folderId)->count();
                if ($remaining === 0) {
                    $folder->delete();
                    return redirect()->route('admin.logs', ['view' => 'archived'])
                        ->with('success', 'Log restored successfully. Folder was empty and has been removed.');
                }
                $folder->log_count = $remaining;
                $folder->save();
            }
        }

        return redirect()->route('admin.logs.folder', $folderId)
            ->with('success', 'Log restored successfully.');
    }

    public function archiveLogsBulk(Request $request)
    {
        $request->validate(['folder_name' => 'required|string|max:100']);

        $folderName = trim($request->input('folder_name'));

        $folder = LogArchiveFolder::firstOrCreate(
            ['name' => $folderName],
            ['description' => 'Archived on ' . now()->format('M d, Y'), 'log_count' => 0]
        );

        $count = ActivityLog::where('is_archived', false)->update([
            'is_archived'           => true,
            'archived_at'           => now(),
            'archived_by'           => auth()->id(),
            'log_archive_folder_id' => $folder->id,
        ]);

        $folder->log_count = ActivityLog::where('log_archive_folder_id', $folder->id)->count();
        $folder->save();

        ActivityLog::log('logs_archived_all', "{$count} audit log(s) archived to folder '{$folderName}' by " . auth()->user()->name);

        return back()->with('success', "{$count} log(s) archived to folder '{$folderName}'.");
    }

    public function logArchiveFolder($id)
    {
        $folder = LogArchiveFolder::findOrFail($id);
        $logs   = ActivityLog::where('log_archive_folder_id', $id)
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

        return view('admin.log-archive-folder', compact('folder', 'logs'));
    }

    public function restoreLogArchiveFolder(Request $request, $id)
    {
        $folder = LogArchiveFolder::findOrFail($id);
        $count  = ActivityLog::where('log_archive_folder_id', $id)->count();

        // Move all logs back to active (clear archive fields)
        ActivityLog::where('log_archive_folder_id', $id)->update([
            'log_archive_folder_id' => null,
            'is_archived'           => false,
            'archived_at'           => null,
            'archived_by'           => null,
        ]);

        // Delete the folder
        $folder->delete();

        return redirect()->route('admin.logs', ['view' => 'archived'])
            ->with('success', "Folder '{$folder->name}' restored. {$count} log(s) moved back to active logs.");
    }

    public function deleteLogArchiveFolder(Request $request, $id)
    {
        $folder = LogArchiveFolder::findOrFail($id);
        $count  = ActivityLog::where('log_archive_folder_id', $id)->count();

        ActivityLog::where('log_archive_folder_id', $id)->delete();
        $folder->delete();

        ActivityLog::log('log_folder_deleted', "Deleted log archive folder '{$folder->name}' with {$count} logs.");

        return redirect()->route('admin.logs', ['view' => 'archived'])
            ->with('success', "Folder '{$folder->name}' and {$count} log(s) permanently deleted.");
    }

    public function deleteLog(Request $request, ActivityLog $log)
    {
        $log->delete();
        return back()->with('success', 'Log permanently deleted.');
    }

    // Archive view - show archived items for current user (per-user archive)
    public function archive(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;

        // Get items archived by the current user (per-user archive system)
        $archivedConcerns = Concern::archivedByUser($userId)
            ->with('categoryRelation', 'user', 'archivedByUsers')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Reports use per-user archive - get reports archived by current user
        $archivedReports = Report::archivedByUser($userId)
            ->with('category', 'user', 'archivedByUsers')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Events - using per-user archive system
        // For MIS role, show all archived events; for others, show only events archived by them
        if ($user->role === 'mis') {
            // MIS sees all events that have been archived by anyone
            $archivedEvents = EventRequest::whereHas('archivedByUsers')
                ->with('user', 'archivedByUsers')
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            // Other users see only events they have archived
            $archivedEvents = EventRequest::archivedByUser($userId)
                ->with('user', 'archivedByUsers')
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        // Facility requests - still using global archive
        // For MIS role, show all archived facility requests; for others, show only their own
        $facilityQuery = FacilityRequest::where('is_archived', true);
        if ($user->role !== 'mis') {
            $facilityQuery->where('user_id', $userId);
        }
        $archivedFacilities = $facilityQuery
            ->with('user')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.archive', compact(
            'archivedConcerns',
            'archivedEvents',
            'archivedReports',
            'archivedFacilities'
        ));
    }

    // Restore archived item
    public function restoreArchivedItem(Request $request)
    {
        try {
            $type = $request->input('type');
            $id = $request->input('id');
            $userId = auth()->id();

            switch ($type) {
                case 'concern':
                    $item = Concern::findOrFail($id);
                    $itemName = $item->title ?? 'Concern';

                    // Use hybrid system: per-user + role-based
                    $user = auth()->user();
                    $role = $user->role;
                    $archiveColumn = $role.'_archived';

                    // Set role-specific archive column to false
                    if (in_array($archiveColumn, $item->getFillable())) {
                        $item->update([$archiveColumn => false]);
                    }

                    // Also remove from per-user archive system
                    $item->archivedByUsers()->detach($userId);
                    break;
                case 'event':
                    $item = EventRequest::findOrFail($id);
                    $itemName = $item->title ?? 'Event';

                    // Use hybrid system: per-user + role-based
                    $user = auth()->user();
                    $role = $user->role;
                    $archiveColumn = $role.'_archived';

                    // Set role-specific archive column to false
                    if (in_array($archiveColumn, $item->getFillable())) {
                        $item->update([$archiveColumn => false]);
                    }

                    // Also remove from per-user archive system
                    $item->archivedByUsers()->detach($userId);
                    break;
                case 'report':
                    $item = Report::findOrFail($id);
                    $itemName = $item->title ?? 'Report';

                    // Use hybrid system: per-user + role-based
                    $user = auth()->user();
                    $role = $user->role;
                    $archiveColumn = $role.'_archived';

                    // Set role-specific archive column to false
                    if (in_array($archiveColumn, $item->getFillable())) {
                        $item->update([$archiveColumn => false]);
                    }

                    // Also remove from per-user archive system
                    $item->archivedByUsers()->detach($userId);
                    break;
                case 'facility':
                    $item = FacilityRequest::findOrFail($id);
                    $itemName = $item->title ?? 'Facility Request';
                    // Still using global archive for facilities
                    $item->is_archived = false;
                    $item->save();
                    break;
                default:
                    return back()->with('error', 'Invalid item type.');
            }

            ActivityLog::log(
                'item_restored',
                ucfirst($type).' restored from archive: '.$itemName,
                $type === 'concern' ? $id : null
            );

            return back()->with('success', ucfirst($type).' restored successfully.');
        } catch (\Exception $e) {
            \Log::error('Restore archived item failed: '.$e->getMessage());

            return back()->with('error', 'Failed to restore item. Please try again.');
        }
    }

    // View items in archive folder
    public function archiveFolderItems(Request $request, $id)
    {
        // Try to find in UserArchiveFolder first (for user folders)
        $folder = ArchiveFolder::findOrFail($id);
        $type = $request->get('type', '');

        $concerns = collect();
        $reports = collect();
        $facilities = collect();

        if ($type == '' || $type == 'concerns') {
            $concerns = $folder->concerns()
                ->with('categoryRelation', 'user')
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        if ($type == '' || $type == 'reports') {
            $reports = $folder->reports()
                ->with('category', 'user')
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        if ($type == '' || $type == 'facilities') {
            $facilities = $folder->facilityRequests()
                ->with('user')
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return view('admin.archive-folder-items', compact(
            'folder',
            'concerns',
            'reports',
            'facilities',
            'type'
        ));
    }

    // Move item to archive folder
    public function moveToArchiveFolder(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');
        $folderId = $request->input('folder_id');

        switch ($type) {
            case 'concern':
                $item = Concern::findOrFail($id);
                $item->archive_folder_id = $folderId;
                $item->save();
                break;
            case 'report':
                $item = Report::findOrFail($id);
                $item->archive_folder_id = $folderId;
                $item->save();
                break;
            case 'facility':
                $item = FacilityRequest::findOrFail($id);
                $item->archive_folder_id = $folderId;
                $item->save();
                break;
            default:
                return back()->with('error', 'Invalid type.');
        }

        // Update folder item counts
        if ($folderId) {
            $folder = ArchiveFolder::find($folderId);
            if ($folder) {
                $folder->updateItemCount();
            }
        }

        return back()->with('success', 'Item moved to folder successfully!');
    }

    // Restore all items from archive folder
    public function restoreAllFromFolder($id)
    {
        $folder = ArchiveFolder::findOrFail($id);

        // Restore all concerns
        foreach ($folder->concerns as $concern) {
            $concern->is_archived = false;
            $concern->archive_folder_id = null;
            $concern->save();
            ActivityLog::log('concern_restored', "Restored concern from archive: {$concern->title}");
        }

        // Restore all reports
        foreach ($folder->reports as $report) {
            $report->is_archived = false;
            $report->archive_folder_id = null;
            $report->save();
            ActivityLog::log('report_restored', "Restored report from archive: {$report->title}");
        }

        // Restore all facilities
        foreach ($folder->facilityRequests as $facility) {
            $facility->is_archived = false;
            $facility->archive_folder_id = null;
            $facility->save();
            ActivityLog::log('facility_restored', "Restored facility request from archive: {$facility->id}");
        }

        $folder->item_count = 0;
        $folder->save();

        return back()->with('success', 'All items restored successfully!');
    }

    // Restore selected items from archive folder
    public function restoreSelectedFromFolder(Request $request, $id)
    {
        $itemIds = explode(',', (string) $request->input('item_ids', ''));
        $itemTypes = explode(',', (string) $request->input('item_types', ''));

        $folder = ArchiveFolder::findOrFail($id);

        foreach ($itemIds as $index => $itemId) {
            $type = $itemTypes[$index] ?? null;

            if (! $type) {
                continue;
            }

            switch ($type) {
                case 'concern':
                    $item = Concern::findOrFail($itemId);
                    $item->is_archived = false;
                    $item->archive_folder_id = null;
                    $item->save();
                    ActivityLog::log('concern_restored', "Restored concern from archive: {$item->title}");
                    break;
                case 'report':
                    $item = Report::findOrFail($itemId);
                    $item->is_archived = false;
                    $item->archive_folder_id = null;
                    $item->save();
                    ActivityLog::log('report_restored', "Restored report from archive: {$item->title}");
                    break;
                case 'facility':
                    $item = FacilityRequest::findOrFail($itemId);
                    $item->is_archived = false;
                    $item->archive_folder_id = null;
                    $item->save();
                    ActivityLog::log('facility_restored', "Restored facility request from archive: {$item->id}");
                    break;
            }
        }

        $folder->updateItemCount();

        return back()->with('success', 'Selected items restored successfully!');
    }

    // Restore archived item
    public function restoreArchive(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        switch ($type) {
            case 'concern':
                $item = Concern::findOrFail($id);
                $item->is_archived = false;
                $item->archive_folder_id = null;
                $item->save();
                ActivityLog::log('concern_restored', "Restored concern from archive: {$item->title}");
                break;
            case 'report':
                $item = Report::findOrFail($id);
                $item->is_archived = false;
                $item->archive_folder_id = null;
                $item->save();
                ActivityLog::log('report_restored', "Restored report from archive: {$item->title}");
                break;
            case 'facility':
                $item = FacilityRequest::findOrFail($id);
                $item->is_archived = false;
                $item->archive_folder_id = null;
                $item->save();
                ActivityLog::log('facility_restored', "Restored facility request from archive: {$item->id}");
                break;
            case 'user':
                $item = User::findOrFail($id);
                $item->is_archived = false;
                $item->archive_folder_id = null;
                $item->save();
                ActivityLog::log('user_restored', "Restored user from archive: {$item->name}");
                break;
            default:
                return back()->with('error', 'Invalid archive type.');
        }

        return back()->with('success', 'Item restored successfully!');
    }

    // Permanently delete archived item
    public function deleteArchive(Request $request)
    {
        $type = $request->input('type');
        $id = $request->input('id');

        switch ($type) {
            case 'concern':
                $item = Concern::findOrFail($id);
                ActivityLog::log('concern_deleted', "Permanently deleted archived concern: {$item->title}");
                $item->forceDelete();
                break;
            case 'report':
                $item = Report::findOrFail($id);
                ActivityLog::log('report_deleted', "Permanently deleted archived report: {$item->title}");
                $item->forceDelete();
                break;
            case 'facility':
                $item = FacilityRequest::findOrFail($id);
                ActivityLog::log('facility_deleted', "Permanently deleted archived facility request: {$item->id}");
                $item->forceDelete();
                break;
            case 'user':
                $item = User::findOrFail($id);
                ActivityLog::log('user_deleted', "Permanently deleted archived user: {$item->name}");
                $item->forceDelete();
                break;
            default:
                return back()->with('error', 'Invalid archive type.');
        }

        return back()->with('success', 'Item permanently deleted!');
    }

    // Analytics - Location-based repair/damage analytics
    public function analytics(Request $request)
    {
        // Base query: resolved concerns with location
        $baseQuery = Concern::whereNotNull('location')
            ->where('location', '!=', '')
            ->where('status', 'Resolved');

        if ($request->filled('date_from')) {
            $baseQuery->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Summary stats
        $totalConcerns = (clone $baseQuery)->count();
        $totalCost     = (clone $baseQuery)->sum('cost') ?? 0;
        $uniqueLocations = (clone $baseQuery)->distinct()->count('location');

        // Location stats
        $locationStats = (clone $baseQuery)
            ->select('location')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->groupBy('location')
            ->orderByDesc('total_count')
            ->get();

        // Category stats
        $categoryStats = Concern::with('categoryRelation')
            ->whereNotNull('category_id')
            ->where('status', 'Resolved')
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->select('category_id')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->groupBy('category_id')
            ->orderByDesc('total_count')
            ->get();

        // Monthly trend – last 12 months
        $monthlyStats = Concern::where('status', 'Resolved')
            ->whereNotNull('location')
            ->where('created_at', '>=', now()->subMonths(12))
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Repeated damage: locations repaired more than once
        $repeatedDamageStats = Concern::whereNotNull('location')
            ->where('location', '!=', '')
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->select('location')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->groupBy('location')
            ->having('total_count', '>', 1)
            ->orderByDesc('total_count')
            ->get();

        // Damaged parts top 10
        $damagedPartsStats = Concern::whereNotNull('damaged_part')
            ->where('damaged_part', '!=', '')
            ->where('status', 'Resolved')
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->select('damaged_part')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->groupBy('damaged_part')
            ->orderByDesc('total_count')
            ->limit(10)
            ->get();

        // ── PERIOD COMPARISON (this month vs last month) ──────────────────────
        $thisMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd   = now()->subMonth()->endOfMonth();

        $thisMonthCount = Concern::where('status', 'Resolved')
            ->where('created_at', '>=', $thisMonthStart)->count();
        $lastMonthCount = Concern::where('status', 'Resolved')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $thisMonthCost = Concern::where('status', 'Resolved')
            ->where('created_at', '>=', $thisMonthStart)->sum('cost') ?? 0;
        $lastMonthCost = Concern::where('status', 'Resolved')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('cost') ?? 0;

        $countChange = $lastMonthCount > 0
            ? round((($thisMonthCount - $lastMonthCount) / $lastMonthCount) * 100, 1)
            : ($thisMonthCount > 0 ? 100 : 0);
        $costChange = $lastMonthCost > 0
            ? round((($thisMonthCost - $lastMonthCost) / $lastMonthCost) * 100, 1)
            : ($thisMonthCost > 0 ? 100 : 0);

        $periodComparison = [
            'this_month_count' => $thisMonthCount,
            'last_month_count' => $lastMonthCount,
            'this_month_cost'  => $thisMonthCost,
            'last_month_cost'  => $lastMonthCost,
            'count_change'     => $countChange,
            'cost_change'      => $costChange,
            'this_month_label' => now()->format('F Y'),
            'last_month_label' => now()->subMonth()->format('F Y'),
        ];

        // ── PREDICTIVE / TREND ALERTS ─────────────────────────────────────────
        // Flag locations where repairs are accelerating (more repairs in last 3 months vs prior 3)
        $trendAlerts = collect();
        $allLocations = Concern::whereNotNull('location')->where('location', '!=', '')->distinct()->pluck('location');

        foreach ($allLocations as $loc) {
            $recent = Concern::where('location', $loc)
                ->where('created_at', '>=', now()->subMonths(3))->count();
            $prior  = Concern::where('location', $loc)
                ->whereBetween('created_at', [now()->subMonths(6), now()->subMonths(3)])->count();

            if ($recent >= 2 && $recent > $prior) {
                $recentCost = Concern::where('location', $loc)
                    ->where('created_at', '>=', now()->subMonths(3))->sum('cost') ?? 0;
                $trendAlerts->push([
                    'location'    => $loc,
                    'recent'      => $recent,
                    'prior'       => $prior,
                    'recent_cost' => $recentCost,
                    'severity'    => $recent >= 4 ? 'critical' : ($recent >= 3 ? 'warning' : 'info'),
                ]);
            }
        }
        $trendAlerts = $trendAlerts->sortByDesc('recent')->values();

        return view('admin.analytics', compact(
            'totalConcerns',
            'totalCost',
            'uniqueLocations',
            'locationStats',
            'categoryStats',
            'monthlyStats',
            'repeatedDamageStats',
            'damagedPartsStats',
            'periodComparison',
            'trendAlerts'
        ));
    }

    // Update concern cost - Admin or maintenance can update
    public function updateCost(Request $request, $id)
    {
        $request->validate([
            'cost' => 'required|numeric|min:0',
        ]);

        $concern = Concern::findOrFail($id);

        // Check if user is MIS or maintenance
        $user = auth()->user();
        if (! in_array($user->role, ['mis', 'maintenance'])) {
            return back()->with('error', 'You do not have permission to update cost.');
        }

        $concern->cost = $request->input('cost');
        $concern->save();

        ActivityLog::log('cost_updated', 'Updated cost to '.$request->input('cost').' for concern: '.$concern->title);

        return back()->with('success', 'Cost updated successfully!');
    }

    // Export analytics data to CSV
    public function exportAnalytics(Request $request)
    {
        // Get all resolved concerns with location data
        $query = Concern::whereNotNull('location')
            ->where('location', '!=', '')
            ->where('status', 'Resolved');

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $concerns = $query->select('location', 'damaged_part', 'date_fixed', 'cost', 'created_at', 'resolution_notes')
            ->orderBy('created_at', 'desc')
            ->get();

        // Prepare CSV data
        $filename = 'analytics_export_'.now()->format('Y-m-d_H-i-s').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($concerns) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, ['Date Created', 'Location', 'Damaged Part', 'Date Fixed', 'Cost', 'Resolution Notes']);

            // Data rows
            foreach ($concerns as $concern) {
                fputcsv($file, [
                    $concern->created_at->format('Y-m-d H:i:s'),
                    $concern->location,
                    $concern->damaged_part,
                    $concern->date_fixed ? $concern->date_fixed->format('Y-m-d') : '',
                    $concern->cost,
                    $concern->resolution_notes,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function saveAutoDeletePreference(Request $request)
    {
        try {
            $request->validate([
                'days' => 'required|integer|in:3,7,15,30',
                'module' => 'required|string|in:reports,concerns,event_requests,facility_requests,users',
            ]);

            $user = auth()->user();
            $column = $request->input('module').'_auto_delete_days';
            $user->update([$column => $request->input('days')]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while saving your preference.'], 500);
        }
    }

    /**
     * Validate status transition for concerns (OWASP API6: Business Flow Protection)
     */
    private function isValidStatusTransition(string $oldStatus, string $newStatus): bool
    {
        // Define valid status transitions
        $validTransitions = [
            'Pending' => ['Assigned', 'In Progress', 'Resolved', 'Closed'],
            'Assigned' => ['In Progress', 'Resolved', 'Closed'],
            'In Progress' => ['Resolved', 'Closed'],
            'Resolved' => ['Closed'], // Allow reopening if needed
            'Closed' => [], // Final state, no transitions allowed
        ];

        // Check if the transition is valid
        return isset($validTransitions[$oldStatus]) && in_array($newStatus, $validTransitions[$oldStatus]);
    }

    // Soft delete an archived report (admin)
    public function softDeleteArchivedReport(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        if ($report->is_deleted) {
            return back()->with('error', 'Report is already deleted.');
        }

        $deletedFolder = ArchiveFolder::where('name', 'Deleted Reports')->where('is_system', true)->first();
        if (! $deletedFolder) {
            $deletedFolder = ArchiveFolder::create([
                'name' => 'Deleted Reports',
                'description' => 'Reports that have been deleted and can be restored',
                'type' => 'reports',
                'is_system' => true,
                'item_count' => 0,
            ]);
        }

        $report->update([
            'is_deleted' => true,
            'archive_folder_id' => $deletedFolder->id,
            'deleted_by' => auth()->id(),
        ]);

        ActivityLog::log('report_soft_deleted', 'Report moved to deleted: ' . $report->title, $report->id, 'report');

        return back()->with('success', 'Report moved to deleted successfully!');
    }

    // Soft delete an archived event (admin)
    public function softDeleteArchivedEvent(Request $request, $id)
    {
        $event = EventRequest::findOrFail($id);

        if ($event->is_deleted) {
            return back()->with('error', 'Event is already deleted.');
        }

        $deletedFolder = ArchiveFolder::where('name', 'Deleted Events')->where('type', 'mixed')->first();
        if (! $deletedFolder) {
            $deletedFolder = ArchiveFolder::create([
                'name' => 'Deleted Events',
                'type' => 'mixed',
                'description' => 'Deleted event requests',
                'is_system' => true,
            ]);
        }

        $event->archive_folder_id = $deletedFolder->id;
        $event->is_deleted = true;
        $event->deleted_by = auth()->id();
        $event->save();

        ActivityLog::log('event_soft_deleted', 'Event moved to deleted: ' . $event->title, null);

        return back()->with('success', 'Event moved to deleted successfully!');
    }

    // Soft delete an archived facility request (admin)
    public function softDeleteArchivedFacility(Request $request, $id)
    {
        $facility = FacilityRequest::findOrFail($id);

        if ($facility->is_deleted) {
            return back()->with('error', 'Facility request is already deleted.');
        }

        $deletedFolder = ArchiveFolder::where('name', 'Deleted Facility Requests')->where('is_system', true)->first();
        if (! $deletedFolder) {
            $deletedFolder = ArchiveFolder::create([
                'name' => 'Deleted Facility Requests',
                'description' => 'Facility requests that have been deleted',
                'type' => 'mixed',
                'is_system' => true,
                'item_count' => 0,
            ]);
        }

        $facility->update([
            'is_deleted' => true,
            'archive_folder_id' => $deletedFolder->id,
        ]);

        ActivityLog::log('facility_soft_deleted', 'Facility request moved to deleted: ' . ($facility->event_title ?? 'N/A'), null);

        return back()->with('success', 'Facility request moved to deleted successfully!');
    }
}


