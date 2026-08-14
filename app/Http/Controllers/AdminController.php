<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Category;
use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\FacilityRequest;
use App\Models\LogArchiveFolder;
use App\Models\MaintenanceStaff;
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

        // Use withoutGlobalScopes to allow unlocking superadmin accounts
        $user = User::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        $user->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
            'login_lockout_level' => 0,
        ]);

        ActivityLog::log('account_unlocked', "Unlocked account: {$user->name} ({$user->email})", $user->id, 'user');

        if (request()->expectsJson() || request()->ajax()) {
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

        if ($newStatus !== 'Resolved') {
            $this->sendConcernUpdateNotification(
                $concern,
                'Concern Status Updated',
                "Your concern status changed from {$oldStatus} to {$newStatus}.",
                $user
            );
        }

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

            if ($newStatus !== 'Resolved' && $oldStatus !== $newStatus) {
                $correspondingConcern = $report->concern;
                if ($correspondingConcern) {
                    $correspondingConcern->status = $newStatus;
                    if ($newStatus === 'In Progress') {
                        $correspondingConcern->damaged_part = $request->input('damaged_part');
                    }
                    $correspondingConcern->save();

                    $this->sendConcernUpdateNotification(
                        $correspondingConcern,
                        'Concern Status Updated',
                        "Your concern status changed from {$oldStatus} to {$newStatus}.",
                        $user
                    );
                }
            }

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
            'assigned_to' => 'required|exists:maintenance_staff,id',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $concern = Concern::findOrFail($id);

        // Check if user is building_admin, school_admin, academic_head, or mis
        $user = auth()->user();
        if (!in_array($user->role, ['building_admin', 'school_admin', 'academic_head', 'mis'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You do not have permission to assign concerns.'], 403);
            }

            return back()->with('error', 'You do not have permission to assign concerns.');
        }

        // Get the maintenance staff member
        $maintenanceStaff = \App\Models\MaintenanceStaff::findOrFail($request->input('assigned_to'));

        // Update the concern
        $concern->assigned_to = $request->input('assigned_to');
        $concern->assigned_at = now();
        $concern->status      = 'Assigned';
        if ($request->filled('notes')) {
            $concern->notes = $request->input('notes');
        }
        $concern->save();

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern Assigned',
            "Your concern has been assigned to {$maintenanceStaff->name}.",
            $user
        );

        // Log activity
        ActivityLog::log(
            'concern_assigned',
            "Concern assigned to {$maintenanceStaff->name}",
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Concern assigned to {$maintenanceStaff->name} successfully!"]);
        }

        return back()->with('success', "Concern assigned to {$maintenanceStaff->name} successfully!");
    }

    /**
     * Assign a report to a maintenance staff member
     * Building Admin can assign reports to maintenance staff
     */
    public function assignReport(Request $request, $id)
    {
        try {
            $report = Report::findOrFail($id);

            // Check if user is building_admin, school_admin, academic_head, or mis
            $user = auth()->user();
            if (!in_array($user->role, ['building_admin', 'school_admin', 'academic_head', 'mis'])) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'You do not have permission to assign reports.'], 403);
                }

                return back()->with('error', 'You do not have permission to assign reports.');
            }

            // Determine if assigning to MIS or Maintenance based on category
            $isTechnologyCategory = $report->category && strtolower(trim($report->category->name)) === 'technology/internet';
            
            if ($isTechnologyCategory) {
                // Validate for MIS user (from users table)
                $request->validate([
                    'assigned_to' => 'required|exists:users,id',
                    'notes'       => 'nullable|string|max:1000',
                ]);
                
                // Get the MIS user
                $assignedUser = User::findOrFail($request->input('assigned_to'));
                
                // Verify the user is actually MIS
                if ($assignedUser->role !== 'mis') {
                    if ($request->expectsJson()) {
                        return response()->json(['error' => 'Selected user is not a MIS staff member.'], 422);
                    }
                    return back()->with('error', 'Selected user is not a MIS staff member.');
                }
                
                $assignedName = $assignedUser->name;
            } else {
                // Validate for Maintenance staff (from maintenance_staff table)
                $request->validate([
                    'assigned_to' => 'required|exists:maintenance_staff,id',
                    'notes'       => 'nullable|string|max:1000',
                ]);
                
                // Get the maintenance staff member
                $assignedUser = \App\Models\MaintenanceStaff::findOrFail($request->input('assigned_to'));
                $assignedName = $assignedUser->name;
            }

            $oldAssignedTo = $report->assigned_to;

            // Update the report - store id in assigned_to
            $report->assigned_to = $request->input('assigned_to');
            $report->assigned_at = now();
            $report->status      = 'Assigned';
            if ($request->filled('notes')) {
                $report->notes = $request->input('notes');
            }
            $report->save();

            // Sync the corresponding concern
            $concern = $report->concern;
            if ($concern) {
                $oldConcernStatus = $concern->status;
                $oldConcernAssignee = $concern->assigned_to;
                $concern->assigned_to = $request->input('assigned_to');
                $concern->assigned_at = now();
                $concern->status      = 'Assigned';
                $concern->save();

                if ($oldConcernStatus !== 'Assigned' || (int) $oldConcernAssignee !== (int) $concern->assigned_to) {
                    $this->sendConcernUpdateNotification(
                        $concern,
                        'Concern Assigned',
                        "Your concern has been assigned to {$assignedName}.",
                        $user
                    );
                }
            }

            // Log activity for report (wrapped in try-catch to prevent failure if ActivityLog has issues)
            try {
                ActivityLog::log(
                    'report_assigned',
                    "Report assigned to {$assignedName}",
                    $report->id,
                    'report'
                );
            } catch (\Exception $logException) {
                // Log the error but don't fail the assignment
                \Log::error('Failed to log activity: ' . $logException->getMessage());
            }

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => "Report assigned to {$assignedName} successfully!"]);
            }

            return back()->with('success', "Report assigned to {$assignedName} successfully!");
            
        } catch (\Exception $e) {
            // Log the full error for debugging
            \Log::error('Error in assignReport: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'report_id' => $id,
                'user_id' => auth()->id()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to assign report: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Failed to assign report. Please try again.');
        }
    }

    /**
     * Set priority on a report after assignment
     */
    public function setReportPriority(Request $request, $id)
    {
        $request->validate(['priority' => 'required|in:low,medium,high,urgent,safety_hazard']);

        $report = Report::findOrFail($id);

        if (auth()->user()->role !== 'building_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Handle safety_hazard: set severity to urgent and flag as safety hazard
        if ($request->priority === 'safety_hazard') {
            $report->severity = 'urgent'; // Set as urgent
            $report->is_safety_hazard = true; // Flag as safety hazard
        } else {
            $report->severity = $request->priority; // low, medium, high, urgent
            $report->is_safety_hazard = false; // Not a safety hazard
        }
        
        $report->save();

        // Sync to linked concern
        if ($report->concern) {
            if ($request->priority === 'safety_hazard') {
                $report->concern->priority = 'urgent';
                $report->concern->is_safety_hazard = true;
            } else {
                $report->concern->priority = $request->priority;
                $report->concern->is_safety_hazard = false;
            }
            $report->concern->save();

            $this->sendConcernUpdateNotification(
                $report->concern,
                'Concern Priority Updated',
                'Your concern priority has been updated to '.$request->priority.'.',
                auth()->user()
            );
        }

        return response()->json(['success' => true, 'priority' => $request->priority]);
    }

    public function setConcernPriority(Request $request, $id)
    {
        $request->validate(['priority' => 'required|in:low,medium,high,urgent,safety_hazard']);

        $concern = Concern::findOrFail($id);

        if (auth()->user()->role !== 'building_admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Handle safety_hazard: set priority to urgent and flag as safety hazard
        if ($request->priority === 'safety_hazard') {
            $concern->priority = 'urgent';
            $concern->is_safety_hazard = true;
        } else {
            $concern->priority = $request->priority;
            $concern->is_safety_hazard = false;
        }
        
        $concern->save();

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern Priority Updated',
            'Your concern priority has been updated to '.$request->priority.'.',
            auth()->user()
        );

        return response()->json(['success' => true, 'priority' => $request->priority]);
    }

    /**
     * Get list of maintenance staff for assignment dropdown
     */
    public function getMaintenanceUsers()
    {
        $maintenanceStaff = \App\Models\MaintenanceStaff::where('is_active', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['users' => $maintenanceStaff]);
    }

    /**
     * Get list of MIS staff for assignment dropdown
     */
    public function getMisUsers()
    {
        $misUsers = User::where('role', 'mis')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json(['users' => $misUsers]);
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

    private function sendConcernUpdateNotification(Concern $concern, string $title, string $message, ?User $updatedBy = null): void
    {
        try {
            (new NotificationService)->notifyConcernUpdated(
                $concern,
                $title,
                $message,
                $updatedBy?->name
            );
        } catch (\Exception $e) {
            \Log::error('Concern update notification failed: '.$e->getMessage());
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

        if ($concern->status !== 'Resolved') {
            $this->sendConcernUpdateNotification(
                $concern,
                'Concern Updated',
                'Resolution notes were added to your concern.',
                $user
            );
        }

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
            $query = Report::with('user', 'category')
                ->where('status', 'Resolved')
                ->where('is_deleted', false)
                ->where(function ($q) {
                    $q->where('building_admin_archived', false)
                        ->where('mis_archived', false)
                        ->where('school_admin_archived', false)
                        ->where('admin_archived', false);
                });

            // Apply filters
            if ($request->filled('priority')) {
                $query->where('severity', $request->input('priority'));
            }

            if ($request->filled('category')) {
                $query->where('category_id', $request->input('category'));
            }

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'ILIKE', "%{$search}%")
                      ->orWhere('description', 'ILIKE', "%{$search}%")
                      ->orWhere('location', 'ILIKE', "%{$search}%");
                });
            }

            $resolvedReports = $query->orderBy('updated_at', 'desc')->get();

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
                ->where('updated_at', '>=', now()->subDays($days))
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
            $reports = $analyticsQuery->select('location', 'damaged_part', 'resolved_at', 'cost', 'status')
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

            // Prepare data for charts
            $chartLocations = $locationStats->pluck('location')->toArray();
            $chartCounts = $locationStats->pluck('count')->toArray();
            $chartCosts = $locationStats->pluck('total_cost')->toArray();

            // Status distribution from concerns (has proper status values)
            $statusStats = Concern::whereNotNull('location')
                ->where('location', '!=', '')
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->select('status')
                ->selectRaw('COUNT(*) as cnt')
                ->groupBy('status')
                ->orderByDesc('cnt')
                ->get()
                ->map(fn($r) => ['status' => $r->status, 'count' => $r->cnt]);

            $chartStatuses = $statusStats->pluck('status')->toArray();
            $chartStatusCounts = $statusStats->pluck('count')->toArray();

            // Per-issue monthly trend - last 6 months (with status breakdown)
            $monthlyStats = Concern::where('created_at', '>=', now()->subMonths(6))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month")
                ->selectRaw("COALESCE(NULLIF(title, ''), LEFT(description, 50)) as title")
                ->selectRaw('status')
                ->selectRaw('COUNT(*) as count')
                ->groupByRaw("month, COALESCE(NULLIF(title, ''), LEFT(description, 50)), status")
                ->orderBy('month')
                ->get();

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

            // Use only concerns for combined stats to avoid double-counting
            // (resolving a report copies cost to the linked concern, so summing both would double-count)
            $combinedLocationStats = $concernsCombined->groupBy('location')->map(function ($group) {
                $totalCount = $group->count();
                $totalCost = $group->sum('cost') ?? 0;
                // Count only fixed concerns (with cost > 0) for average calculation
                $fixedCount = $group->where('cost', '>', 0)->count();
                
                return [
                    'location' => $group->first()->location,
                    'total_count' => $totalCount,
                    'total_cost' => $totalCost,
                    'fixed_count' => $fixedCount,
                ];
            })->sortByDesc('total_cost')->values();

            $totalTickets = $concernsCombined->count();
            $totalCombinedCost = $concernsCombined->sum('cost');
            $uniqueLocationsCombined = $combinedLocationStats->count();

            // ── PERIOD COMPARISON (this month vs last month) ──────────────────
            $thisMonthStart = now()->startOfMonth();
            $lastMonthStart = now()->subMonth()->startOfMonth();
            $lastMonthEnd   = now()->subMonth()->endOfMonth();

            $thisMonthCount = Concern::where('status', 'Resolved')
                ->where('resolved_at', '>=', $thisMonthStart)->count();

            $lastMonthCount = Concern::where('status', 'Resolved')
                ->whereBetween('resolved_at', [$lastMonthStart, $lastMonthEnd])->count();

            $thisMonthCost = Concern::where('status', 'Resolved')
                ->where('resolved_at', '>=', $thisMonthStart)->sum('cost') ?? 0;

            $lastMonthCost = Concern::where('status', 'Resolved')
                ->whereBetween('resolved_at', [$lastMonthStart, $lastMonthEnd])->sum('cost') ?? 0;

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
                $fixedCount = $stat['fixed_count'];
                $totalCost   = $stat['total_cost'];
                $avgCost     = $fixedCount > 0 ? $totalCost / $fixedCount : 0;

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
            // Original asset prices per issue type
            $assetOriginalPrices = [
                'aircon' => 33498,
                'door'  => 500,
                'window'  => 8000,
                'chair'  => 2500,
                'table'  => 4000,
                'electrical outlet' => 1500,
                'light' => 1200,
                'no internet' => 200,
                'printer' => 12000,
                'Smart tv' => 22000,
                'monitor' => 9000,
                'mouse' => 100,
                'keyboard' => 150,
                'projector'=> 25000,
                'whiteboard' => 1700,
            ];
            $getReplacementThreshold = function (?string $issue) use ($assetOriginalPrices): float {
                if (!$issue) return 10000;
                $key = strtolower(trim($issue));
                return $assetOriginalPrices[$key] ?? 10000;
            };

            $trendAlerts = collect();

            // Trend alerts — flag locations with recurring issues
            $locationIssues = Concern::whereNotNull('location')
                ->where('location', '!=', '')
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->select('location', 'title')
                ->distinct()
                ->get();

            foreach ($locationIssues as $li) {
                $loc   = $li->location;
                $issue = $li->title;
                $recent = Concern::where('location', $loc)->where('title', $issue)
                    ->where('created_at', '>=', now()->subMonths(3))->count();
                if ($recent < 1) continue;
                $allTimeCost = Concern::where('location', $loc)->where('title', $issue)->sum('cost') ?? 0;
                $recentCost  = Concern::where('location', $loc)->where('title', $issue)
                    ->where('created_at', '>=', now()->subMonths(3))->sum('cost') ?? 0;
                $prior = Concern::where('location', $loc)->where('title', $issue)
                    ->whereBetween('created_at', [now()->subMonths(6), now()->subMonths(3)])->count();
                $severity   = $recent >= 3 ? 'critical' : ($recent >= 2 ? 'warning' : 'info');
                $alertTitle = $severity === 'critical' ? 'High Frequency Issue' : ($severity === 'warning' ? 'Recurring Issue' : 'Issue Detected');
                
                // Get monthly breakdown for the last 12 months
                $monthlyCosts = Concern::where('location', $loc)
                    ->where('title', $issue)
                    ->where('status', 'Resolved')
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as count, SUM(cost) as cost")
                    ->groupBy('month')
                    ->orderBy('month', 'desc')
                    ->get()
                    ->map(function($row) {
                        return [
                            'month' => \Carbon\Carbon::parse($row->month . '-01')->format('M Y'),
                            'count' => $row->count,
                            'cost' => $row->cost ?? 0
                        ];
                    });
                
                // Get replacement threshold based on issue type
                $replacementThreshold = $getReplacementThreshold($issue);
                
                $trendAlerts->push([
                    'location'    => $loc, 'top_issue' => $issue,
                    'recent'      => $recent, 'prior' => $prior,
                    'recent_cost' => $recentCost, 'all_time_cost' => $allTimeCost,
                    'severity'    => $severity, 'alert_title' => $alertTitle,
                    'updated_at'  => Concern::where('location', $loc)->where('title', $issue)->latest()->value('updated_at'),
                    'monthly_costs' => $monthlyCosts, 'replacement_threshold' => $replacementThreshold,
                ]);
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
                'monthlyStats' => $monthlyStats,
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
        $query = Report::with('user', 'category')
            ->where('is_deleted', false)
            ->where('status', '!=', 'Resolved')
            ->where(function ($q) {
                $q->where('building_admin_archived', false)
                    ->where('mis_archived', false)
                    ->where('school_admin_archived', false)
                    ->where('admin_archived', false);
            });

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('severity', $request->input('priority'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('location', 'ILIKE', "%{$search}%");
            });
        }

        $reportStats = [
            'total' => (clone $query)->count(),
            'pending' => (clone $query)->where('status', 'Pending')->count(),
            'resolved' => (clone $query)->where('status', 'Resolved')->count(),
            'critical' => (clone $query)->where('severity', 'critical')->count(),
        ];

        $reports = $query
            ->orderByRaw("CASE status WHEN 'Pending' THEN 1 WHEN 'Assigned' THEN 2 WHEN 'In Progress' THEN 3 WHEN 'Resolved' THEN 4 ELSE 5 END")
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->appends($request->except(['page', 'open_report']));

        return view('admin.reports', [
            'viewType' => $viewType,
            'reports' => $reports,
            'categories' => Category::all(),
            'totalReports' => $reportStats['total'],
            'reportStats' => $reportStats,
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

            // Per-issue monthly trend - last 6 months (with status breakdown)
            $monthlyStats = Concern::where('created_at', '>=', now()->subMonths(6))
                ->selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month")
                ->selectRaw("COALESCE(NULLIF(title, ''), LEFT(description, 50)) as title")
                ->selectRaw('status')
                ->selectRaw('COUNT(*) as count')
                ->groupByRaw("month, COALESCE(NULLIF(title, ''), LEFT(description, 50)), status")
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

    // Export Reports to PDF
    public function exportPdf(Request $request)
    {
        $viewType = $request->input('view', 'active');
        
        $query = Report::with('category', 'user')
            ->where('is_deleted', false);

        // Apply view type filter
        if ($viewType === 'resolved') {
            $query->where('status', 'Resolved');
        } elseif ($viewType === 'archives') {
            // For archives, get reports archived by current user
            $query->whereHas('archivedByUsers', function ($q) {
                $q->where('user_id', auth()->id());
            });
        } elseif ($viewType === 'deleted') {
            $query->where('is_deleted', true);
        } else {
            // Active reports - not deleted and not archived by current user
            $query->whereDoesntHave('archivedByUsers', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        // Apply archived filter
        if ($request->filled('archived')) {
            if ($request->input('archived') === '1') {
                $query->whereHas('archivedByUsers', function ($q) {
                    $q->where('user_id', auth()->id());
                });
            } elseif ($request->input('archived') === 'all') {
                // No filter - show all
            } else {
                // Active concerns (not archived)
                $query->whereDoesntHave('archivedByUsers', function ($q) {
                    $q->where('user_id', auth()->id());
                });
            }
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Apply priority filter (severity in reports table)
        if ($request->filled('priority')) {
            $query->where('severity', $request->input('priority'));
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('location', 'ILIKE', "%{$search}%");
            });
        }

        // Legacy date filters (if provided)
        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $reports = $query->orderBy('created_at', 'desc')->get();

        // Organize reports by status
        $resolvedReports = $reports->where('status', 'Resolved')->values();
        $inProgressReports = $reports->where('status', 'In Progress')->values();
        $pendingReports = $reports->where('status', 'Pending')->values();
        $assignedReports = $reports->where('status', 'Assigned')->values();

        // Cost by Issue + Room — group resolved reports by title + location
        $costByRoom = $resolvedReports
            ->whereNotNull('location')
            ->groupBy(fn($r) => ($r->title ?? \Illuminate\Support\Str::limit($r->description, 30)) . '||' . $r->location)
            ->map(fn($group) => [
                'issue'      => $group->first()->title ?? \Illuminate\Support\Str::limit($group->first()->description, 30),
                'location'   => $group->first()->location,
                'count'      => $group->count(),
                'total_cost' => $group->sum('cost'),
                'avg_cost'   => $group->count() > 0 ? $group->sum('cost') / $group->count() : 0,
            ])
            ->sortByDesc('total_cost')
            ->values();

        // Build filter description for PDF
        $filterDescription = $this->buildFilterDescription($request, $viewType);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.reports-pdf', compact('reports', 'resolvedReports', 'inProgressReports', 'pendingReports', 'assignedReports', 'costByRoom', 'filterDescription', 'viewType'))
            ->setPaper('a4', 'portrait')
            ->setOptions(['defaultFont' => 'DejaVu Sans', 'isHtml5ParserEnabled' => true]);

        ActivityLog::log('export_created', 'Exported reports to PDF with filters: ' . $filterDescription);

        return $pdf->stream('campfix-reports-' . now()->format('Y-m-d') . '.pdf');
    }

    // Helper method to build filter description
    private function buildFilterDescription(Request $request, $viewType)
    {
        $filters = [];
        
        // View type
        $viewLabels = [
            'active' => 'Reported Issues',
            'resolved' => 'Resolved Reports',
            'archives' => 'Archived Reports',
            'deleted' => 'Deleted Reports'
        ];
        $filters[] = $viewLabels[$viewType] ?? 'All Reports';

        // Archived filter
        if ($request->filled('archived')) {
            if ($request->input('archived') === '1') {
                $filters[] = 'Archived';
            } elseif ($request->input('archived') === 'all') {
                $filters[] = 'All Concerns';
            } else {
                $filters[] = 'Active Concerns';
            }
        }

        // Status filter
        if ($request->filled('status')) {
            $filters[] = 'Status: ' . $request->input('status');
        }

        // Priority filter
        if ($request->filled('priority')) {
            $filters[] = 'Priority: ' . ucfirst($request->input('priority'));
        }

        // Category filter
        if ($request->filled('category')) {
            $category = \App\Models\Category::find($request->input('category'));
            if ($category) {
                $filters[] = 'Category: ' . $category->name;
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $filters[] = 'Search: "' . $request->input('search') . '"';
        }

        // Date filters
        if ($request->filled('date_from')) {
            $filters[] = 'From: ' . \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y');
        }
        if ($request->filled('date_to')) {
            $filters[] = 'To: ' . \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
        }

        return !empty($filters) ? implode(' | ', $filters) : 'No filters applied';
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
            // Get archive folders (exclude Deleted Users system folder) with pagination
            $perPage = $request->get('per_page', 20);
            $archiveFolders = UserArchiveFolder::where('name', '!=', 'Deleted Users')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

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

        // Search by name, email, student_id, phone, or department (case-insensitive)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) like ?', ['%'.strtolower($search).'%'])
                    ->orWhereRaw('LOWER(email) like ?', ['%'.strtolower($search).'%'])
                    ->orWhere('student_id', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhereRaw('LOWER(department) like ?', ['%'.strtolower($search).'%']);
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
        
        // If it's an AJAX request, return JSON
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'id' => $user->id,
                'uuid' => $user->uuid,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'phone' => $user->phone,
                'student_id' => $user->student_id,
                'permissions' => $user->permissions ?? []
            ]);
        }
        
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
            'days' => 'required|integer|in:0,3,7,15,30',
        ]);

        $days = $request->input('days');
        if ($days === 0) {
            return response()->json(['success' => true, 'message' => 'Automatic deletion is off.', 'deleted_count' => 0]);
        }
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
            ->when($days > 0, fn ($query) => $query->where('deleted_at', '<=', now()->subDays($days)))
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

            ActivityLog::log('event_restored', "Event '{$eventTitle}' (ID: {$event->id}) restored from deleted by " . auth()->user()->name . " (" . auth()->user()->role . ") - Location: {$event->event_location}, Date: {$event->start_date->format('M d, Y')}");

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

                ActivityLog::log('event_restored', "Event '{$event->title}' (ID: {$event->id}) restored from deleted (batch) by " . auth()->user()->name . " (" . auth()->user()->role . ")");
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

        // Restore in one database statement. Loading and saving thousands of
        // users one-by-one can exceed serverless request limits on Vercel.
        $count = User::withoutGlobalScopes()
            ->where('archive_folder_id', $folder_id)
            ->update([
                'is_archived' => false,
                'archive_folder_id' => null,
                'updated_at' => now(),
            ]);

        // Delete the folder if all users were restored
        if ($count > 0) {
            $folder->delete();
            ActivityLog::log('archive_folder_deleted', "Deleted empty archive folder: {$folderName}");
            ActivityLog::log('users_restored', "Restored {$count} users from archive folder: {$folderName}");
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

    // Batch archive users (JSON API)
    public function batchArchiveUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        $userIds = $request->user_ids;
        $currentUserId = auth()->id();

        // Remove current user from the list
        $userIds = array_filter($userIds, function($id) use ($currentUserId) {
            return $id != $currentUserId;
        });

        if (empty($userIds)) {
            return response()->json(['success' => false, 'message' => 'No valid users to archive'], 400);
        }

        // Create archive folder with timestamp
        $folderName = 'Bulk_Archive_' . now()->format('Y_m_d_His');
        $archiveFolder = UserArchiveFolder::create([
            'name' => $folderName,
            'description' => 'Bulk archived on ' . now()->format('M d, Y H:i:s'),
            'user_count' => 0,
        ]);

        $archivePath = 'archive/' . $folderName;
        if (!Storage::disk('public')->exists($archivePath)) {
            Storage::disk('public')->makeDirectory($archivePath);
        }

        $users = User::whereIn('id', $userIds)->get();
        $archivedCount = 0;

        foreach ($users as $user) {
            // Check if user is protected
            if ($user->isProtectedFrom(auth()->user())) {
                continue;
            }

            // Export user data to JSON
            $userData = $user->toArray();
            $fileName = $user->student_id ?? 'user_' . $user->id;
            $filePath = $archivePath . '/' . $fileName . '.json';
            Storage::disk('public')->put($filePath, json_encode($userData, JSON_PRETTY_PRINT));

            // Mark as archived
            $user->is_archived = true;
            $user->archived_at = now();
            $user->archive_folder_id = $archiveFolder->id;
            $user->save();

            $archivedCount++;
        }

        // Update folder user count
        $archiveFolder->user_count = $archivedCount;
        $archiveFolder->save();

        ActivityLog::log('users_batch_archived', "Batch archived {$archivedCount} users to folder: {$folderName}");

        return response()->json([
            'success' => true,
            'message' => "Successfully archived {$archivedCount} user(s) to {$folderName}"
        ]);
    }

    // Batch delete users (JSON API)
    public function batchDeleteUsers(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        $userIds = $request->user_ids;
        $currentUserId = auth()->id();

        // Remove current user from the list
        $userIds = array_filter($userIds, function($id) use ($currentUserId) {
            return $id != $currentUserId;
        });

        if (empty($userIds)) {
            return response()->json(['success' => false, 'message' => 'No valid users to delete'], 400);
        }

        $users = User::whereIn('id', $userIds)->get();
        $deletedCount = 0;

        foreach ($users as $user) {
            // Check if user is protected
            if ($user->isProtectedFrom(auth()->user())) {
                continue;
            }

            // Soft delete
            $user->deleted_at = now();
            $user->deleted_by = $currentUserId;
            $user->save();

            $deletedCount++;
        }

        ActivityLog::log('users_batch_deleted', "Batch deleted {$deletedCount} users");

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} user(s)"
        ]);
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
    public function archiveFolderUsers(Request $request, $id)
    {
        $folder = UserArchiveFolder::findOrFail($id);
        $perPage = $request->get('per_page', 20);
        $users = User::where('archive_folder_id', $id)
            ->orderBy('name', 'asc')
            ->paginate($perPage);

        return view('admin.archive-folder-users', compact('folder', 'users'));
    }

    // Delete all users in archive folder
    public function deleteAllFolderUsers($id)
    {
        if (!auth()->user()->canAccess('users_delete')) {
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        $folder = UserArchiveFolder::findOrFail($id);
        $folderName = $folder->name;
        
        // Get all users in this folder
        $users = User::where('archive_folder_id', $id)->get();
        $count = $users->count();

        // Get or create the "Deleted Users" system folder
        $deletedFolder = UserArchiveFolder::firstOrCreate(
            ['name' => 'Deleted Users'],
            [
                'description' => 'Users that have been deleted and can be restored',
                'is_system' => true,
            ]
        );

        // Move all users to Deleted Users folder
        foreach ($users as $user) {
            $user->is_deleted = true;
            $user->deleted_at = now();
            $user->deleted_by = auth()->id();
            $user->archive_folder_id = $deletedFolder->id;
            $user->save();

            // Log activity
            ActivityLog::log('user_deleted', "User '{$user->name}' deleted from folder '{$folderName}'", $user->id, 'user');
        }

        // Update folder counts
        $folder->user_count = 0;
        $folder->save();

        $deletedFolder->user_count = $deletedFolder->archivedUsers()->count();
        $deletedFolder->save();

        // Delete the folder if it's now empty
        if ($folder->user_count == 0 && !$folder->is_system) {
            $folder->delete();
            return redirect()->route('admin.users', ['view' => 'archives'])
                ->with('success', "Successfully deleted all {$count} users from folder '{$folderName}'. The empty folder has been removed.");
        }

        return redirect()->route('admin.archiveFolderUsers', $id)
            ->with('success', "Successfully deleted all {$count} users from folder '{$folderName}'.");
    }

    // Delete archive folder
    public function deleteArchiveFolder($id)
    {
        // Try to find in UserArchiveFolder first
        $userFolder = UserArchiveFolder::find($id);

        if ($userFolder) {
            if (!auth()->user()->canAccess('users_delete')) {
                return redirect()->back()->with('error', 'You do not have permission to perform this action.');
            }

            if ($userFolder->is_system) {
                return back()->with('error', 'Cannot delete system folder!');
            }

            if (User::withoutGlobalScopes()->where('archive_folder_id', $id)->where('id', auth()->id())->exists()) {
                return back()->with('error', 'Cannot delete a folder that contains your own account.');
            }

            // Delete in one database statement. Loading and deleting thousands of users
            // one-by-one can exceed serverless request limits on Vercel.
            $count = User::withoutGlobalScopes()
                ->where('archive_folder_id', $id)
                ->delete();

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
            'default_role' => 'required|in:student,faculty,staff',
            'file_format'  => 'required|in:masterlist,standard',
        ]);

        $isMasterlist = $request->input('file_format') === 'masterlist';
        $defaultRole  = $request->input('default_role', 'student');
        $folderName   = $request->input('archive_folder_name', '2025-2026');
        $extension    = strtolower($request->file('file')->getClientOriginalExtension());
        $filePath     = $request->file('file')->getRealPath();

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
                if ($defaultRole === 'faculty' || $defaultRole === 'staff') {
                    if (count($row) < 6) {
                        $skippedRows[] = "Row {$rowIndex}: too few columns (".count($row).")";
                        continue;
                    }

                    // For staff, employee number is optional (can be empty)
                    $empNumber = isset($row[5]) ? preg_replace('/\s+/', '', $row[5]) : '';
                    
                    // Only validate employee number if it's not empty
                    if (!empty($empNumber) && ! preg_match('/^NVS\d+/i', $empNumber)) {
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

                    // Student ID can be null for staff
                    $studentId  = !empty($empNumber) ? $empNumber : null;
                    
                    // For staff imports, check if there's a STAFF column (column 6) to determine role
                    if ($defaultRole === 'staff' && isset($row[6]) && !empty(trim($row[6]))) {
                        $staffPosition = strtolower(trim($row[6]));
                        
                        // Map staff positions to system roles
                        $roleMapping = [
                            'mis' => 'mis',
                            'school administrator' => 'school_admin',
                            'building administrator' => 'building_admin',
                            'academic head' => 'academic_head',
                            'program head' => 'program_head',
                            'principal assistant' => 'principal_assistant',
                            'maintenance' => 'maintenance',
                            // Default other positions to faculty-like access
                            'registrar' => 'faculty',
                            'library assistant' => 'faculty',
                            'records assistant' => 'faculty',
                            'cashier' => 'faculty',
                            'admissions officer' => 'faculty',
                            'hr' => 'faculty',
                            'laboratory custodian' => 'maintenance',
                            'communications officer' => 'faculty',
                            'nurse' => 'faculty',
                            'd.o' => 'faculty',
                            'purchasing and asset mgt. officer' => 'faculty',
                            'apo' => 'faculty',
                            'accounting assistant' => 'faculty',
                            'sao' => 'faculty',
                            'guidance associate' => 'faculty',
                        ];
                        
                        $role = $roleMapping[$staffPosition] ?? 'faculty';
                    } else {
                        // Default to faculty if no STAFF column or empty
                        $role = 'faculty';
                    }
                    
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
                // Validate role - for staff imports, will be overridden by CSV column
                $validRoles = ['student', 'faculty', 'maintenance', 'mis', 'school_admin', 'building_admin', 'academic_head', 'program_head', 'principal_assistant'];
                if (! in_array($role, $validRoles)) {
                    $role = $defaultRole === 'staff' ? 'faculty' : $defaultRole;
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
                'is_archived'           => false, // Changed to false so users appear in Active Users
                'is_deleted'            => false,
                'failed_login_attempts' => 0,
                'otp_attempts'          => 0,
                'is_admin'              => false,
                // Always use role-specific default permissions
                'permissions'           => json_encode(\App\Models\User::defaultPermissions($role)),
                'created_at'            => now(),
                'updated_at'            => now(),
            ];

            $existingEmails[]     = $emailLower;
            $existingStudentIds[] = $studentIdLower;
            $rowCount++;
        }

        if (! empty($usersToCreate)) {
            foreach (array_chunk($usersToCreate, 500) as $chunk) {
                User::insertOrIgnore($chunk);
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
            ->where(function ($query) {
                $query->whereNotNull('item_user_id')
                    ->orWhere('action', 'like', 'user_%')
                    ->orWhere('action', 'like', 'users_%')
                    ->orWhere('action', 'like', 'account_%')
                    ->orWhere('action', 'like', 'login%')
                    ->orWhere('action', 'like', 'logout%')
                    ->orWhere('action', 'like', 'microsoft_login%')
                    ->orWhere('action', 'like', 'password_%')
                    ->orWhere('action', 'like', 'permission_%')
                    ->orWhere('action', 'like', 'role_%')
                    ->orWhere('action', 'like', 'security_%')
                    ->orWhere('action', 'like', 'session_%');
            })
            // Never expose superadmin actions to regular admins
            ->whereDoesntHave('user', fn($q) => $q->withoutGlobalScopes()
                ->where(function ($q) {
                    $q->where('is_superadmin', true)->orWhere('role', 'superadmin');
                })
            );

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
        $filters = $request->validate([
            'location' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $reportsQuery = Report::with(['category', 'assignedTo']);

        if (\Illuminate\Support\Facades\Schema::hasColumn('reports', 'is_deleted')) {
            $reportsQuery->where('is_deleted', false);
        }

        if (! empty($filters['date_from'])) {
            $reportsQuery->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $reportsQuery->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['location'])) {
            $reportsQuery->where('location', $filters['location']);
        }

        $reports = $reportsQuery->orderBy('created_at')->get();
        $supportsReportCount = Report::supportsReportCount();
        $reportWeight = fn ($report) => $supportsReportCount ? max(1, (int) ($report->report_count ?? 1)) : 1;
        $totalReports = $reports->sum($reportWeight);
        $resolvedReports = $reports->filter(fn ($report) => strtolower((string) $report->status) === 'resolved')->sum($reportWeight);
        $openReports = max(0, $totalReports - $resolvedReports);
        $hazardReports = $reports->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight);
        $totalCost = (float) $reports->sum(fn ($report) => (float) ($report->cost ?? 0));
        $resolutionRate = $totalReports > 0 ? round(($resolvedReports / $totalReports) * 100, 1) : 0;

        $resolvedWithDates = $reports->filter(fn ($report) => $report->assigned_at && $report->resolved_at);
        $avgResolutionHours = $resolvedWithDates->count() > 0
            ? round($resolvedWithDates->avg(fn ($report) => $report->assigned_at->diffInHours($report->resolved_at)), 1)
            : null;
        $now = now();
        $openReportItems = $reports->filter(fn ($report) => strtolower((string) $report->status) !== 'resolved');
        $pendingReports = $reports->filter(fn ($report) => strtolower((string) $report->status) === 'pending')->sum($reportWeight);
        $assignedReports = $reports->filter(fn ($report) => strtolower((string) $report->status) === 'assigned')->sum($reportWeight);
        $avgReportAgeDays = $openReportItems->count() > 0
            ? round($openReportItems->avg(fn ($report) => $report->created_at ? $report->created_at->diffInHours($now) / 24 : 0), 1)
            : 0;
        $oldestOpenDays = $openReportItems->max(fn ($report) => $report->created_at ? $report->created_at->diffInDays($now) : 0) ?? 0;
        $avgCost = $totalReports > 0 ? round($totalCost / $totalReports, 2) : 0;
        $slaTargetHours = 72;
        $slaCompliant = $resolvedWithDates->filter(fn ($report) => $report->assigned_at->diffInHours($report->resolved_at) <= $slaTargetHours)->count();
        $slaCompliance = $resolvedWithDates->count() > 0 ? round(($slaCompliant / $resolvedWithDates->count()) * 100, 1) : null;
        $completedCost = (float) $reports->filter(fn ($report) => strtolower((string) $report->status) === 'resolved')->sum(fn ($report) => (float) ($report->cost ?? 0));
        $remainingRecordedCost = max(0, $totalCost - $completedCost);

        $locations = Report::whereNotNull('location')
            ->where('location', '!=', '')
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('reports', 'is_deleted'), fn ($query) => $query->where('is_deleted', false))
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $issueCostStats = $reports
            ->groupBy(function ($report) {
                $issue = trim((string) ($report->title ?: optional($report->category)->name ?: 'Unspecified issue'));
                $location = trim((string) ($report->location ?: 'Unspecified location'));

                return $issue.'|'.$location;
            })
            ->map(function ($items) use ($reportWeight) {
                $firstReport = $items->first();
                $issue = trim((string) ($firstReport->title ?: optional($firstReport->category)->name ?: 'Unspecified issue'));
                $location = trim((string) ($firstReport->location ?: 'Unspecified location'));
                $total = $items->sum($reportWeight);
                $open = $items->filter(fn ($report) => strtolower((string) $report->status) !== 'resolved')->sum($reportWeight);
                $hazards = $items->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight);
                $cost = (float) $items->sum(fn ($report) => (float) ($report->cost ?? 0));

                return [
                    'issue' => $issue,
                    'location' => $location,
                    'total' => $total,
                    'open' => $open,
                    'hazards' => $hazards,
                    'cost' => $cost,
                    'average_cost' => $total > 0 ? round($cost / $total, 2) : 0,
                    'interpretation' => $cost >= 1000
                        ? 'High recorded cost: review repair history and compare replacement options.'
                        : ($open > 0 ? 'Open work remains: schedule repair and monitor further cost.' : 'No open work: continue monitoring recorded repair cost.'),
                ];
            })
            ->sortByDesc('cost')
            ->values();

        $locationStats = $reports
            ->filter(fn ($report) => filled($report->location))
            ->groupBy('location')
            ->map(function ($items, $location) use ($reportWeight) {
                $total = $items->sum($reportWeight);
                $open = $items->filter(fn ($report) => strtolower((string) $report->status) !== 'resolved')->sum($reportWeight);
                $hazards = $items->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight);
                $cost = (float) $items->sum(fn ($report) => (float) ($report->cost ?? 0));
                $riskScore = ($open * 3) + ($hazards * 4) + min(20, (int) floor($cost / 1000));
                $resolved = max(0, $total - $open);

                return [
                    'location' => $location,
                    'total' => $total,
                    'open' => $open,
                    'resolved' => $resolved,
                    'hazards' => $hazards,
                    'cost' => $cost,
                    'risk_score' => $riskScore,
                    'resolution_rate' => $total > 0 ? round(($resolved / $total) * 100, 1) : 0,
                    'status_breakdown' => $items
                        ->groupBy(fn ($report) => ucfirst(strtolower((string) ($report->status ?: 'Unknown'))))
                        ->map(fn ($statusItems) => $statusItems->sum($reportWeight))
                        ->sortDesc()
                        ->all(),
                    'category_breakdown' => $items
                        ->groupBy(fn ($report) => optional($report->category)->name ?: 'Uncategorized')
                        ->map(fn ($categoryItems) => $categoryItems->sum($reportWeight))
                        ->sortDesc()
                        ->take(5)
                        ->all(),
                    'recent_reports' => $items
                        ->sortByDesc('created_at')
                        ->take(8)
                        ->map(fn ($report) => [
                            'id' => $report->id,
                            'title' => $report->title ?: 'Untitled report',
                            'status' => $report->status ?: 'Unknown',
                            'priority' => $report->severity ?: 'Not set',
                            'category' => optional($report->category)->name ?: 'Uncategorized',
                            'is_hazard' => (bool) $report->is_safety_hazard,
                            'cost' => (float) ($report->cost ?? 0),
                            'date' => optional($report->created_at)->format('M d, Y'),
                        ])
                        ->values()
                        ->all(),
                    'interpretation' => $riskScore >= 12
                        ? 'High priority: inspect this area and allocate repair resources first.'
                        : ($open > 0 ? 'Monitor closely: unresolved reports may affect users.' : 'Stable: reports are currently resolved.'),
                ];
            })
            ->sortByDesc('risk_score')
            ->values();

        $statusStats = $reports
            ->groupBy(fn ($report) => ucfirst(strtolower((string) ($report->status ?: 'Unknown'))))
            ->map(function ($items, $status) use ($reportWeight, $now, $slaTargetHours) {
                $resolvedItems = $items->filter(fn ($report) => $report->assigned_at && $report->resolved_at);
                $ages = $items->map(fn ($report) => $report->created_at ? $report->created_at->diffInHours($now) / 24 : 0);
                $avgResolution = $resolvedItems->count() > 0
                    ? round($resolvedItems->avg(fn ($report) => $report->assigned_at->diffInHours($report->resolved_at)), 1)
                    : null;
                $avgAge = round($ages->avg() ?? 0, 1);

                return [
                    'status' => $status,
                    'count' => $items->sum($reportWeight),
                    'avg_age_days' => $avgAge,
                    'avg_resolution_hours' => $avgResolution,
                    'oldest_days' => (int) round($ages->max() ?? 0),
                    'priority' => strtolower($status) !== 'resolved' && ($avgAge >= 7 || $items->contains(fn ($report) => (bool) $report->is_safety_hazard))
                        ? 'High'
                        : ($avgResolution !== null && $avgResolution > $slaTargetHours ? 'Monitor' : 'Normal'),
                ];
            })
            ->sortByDesc('count')
            ->values();

        $currentMonthKey = now()->format('Y-m');
        $previousMonthKey = now()->subMonth()->format('Y-m');
        $categoryStats = $reports
            ->groupBy(fn ($report) => optional($report->category)->name ?: 'Uncategorized')
            ->map(function ($items, $category) use ($reportWeight, $currentMonthKey, $previousMonthKey) {
                $resolvedItems = $items->filter(fn ($report) => $report->assigned_at && $report->resolved_at);
                $currentMonth = $items->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $currentMonthKey)->sum($reportWeight);
                $previousMonth = $items->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $previousMonthKey)->sum($reportWeight);
                $trendPercent = $previousMonth > 0 ? round((($currentMonth - $previousMonth) / $previousMonth) * 100, 1) : ($currentMonth > 0 ? 100 : 0);

                return [
                    'category' => $category,
                    'count' => $items->sum($reportWeight),
                    'avg_cost' => round((float) $items->avg(fn ($report) => (float) ($report->cost ?? 0)), 2),
                    'avg_resolution_hours' => $resolvedItems->count() > 0
                        ? round($resolvedItems->avg(fn ($report) => $report->assigned_at->diffInHours($report->resolved_at)), 1)
                        : null,
                    'hazards' => $items->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight),
                    'trend_percent' => $trendPercent,
                    'trend_direction' => $trendPercent > 0 ? 'Increasing' : ($trendPercent < 0 ? 'Decreasing' : 'Stable'),
                ];
            })
            ->sortByDesc('count')
            ->take(6)
            ->values();

        $monthStart = now()->startOfMonth()->subMonths(5);
        $trendStats = collect(range(0, 5))->map(function ($offset) use ($reports, $monthStart, $reportWeight) {
            $month = $monthStart->copy()->addMonths($offset);
            $items = $reports->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $month->format('Y-m'));

            return [
                'label' => $month->format('M Y'),
                'reports' => $items->sum($reportWeight),
                'resolved' => $items->filter(fn ($report) => strtolower((string) $report->status) === 'resolved')->sum($reportWeight),
            ];
        });

        $assignedIds = $reports->pluck('assigned_to')->filter()->unique()->values();
        $maintenanceNames = MaintenanceStaff::whereIn('id', $assignedIds)->pluck('name', 'id');
        $misNames = User::whereIn('id', $assignedIds)->where('role', 'mis')->pluck('name', 'id');
        $currentRole = (string) optional(auth()->user())->role;
        $canAssignCategoryTickets = in_array($currentRole, ['building_admin', 'school_admin', 'academic_head', 'mis'], true);
        $canProgressCategoryTickets = in_array($currentRole, ['building_admin', 'school_admin', 'mis', 'maintenance'], true);
        $categoryWorkspace = $reports
            ->groupBy(fn ($report) => optional($report->category)->name ?: 'Uncategorized')
            ->map(function ($items, $category) use ($reportWeight, $monthStart, $maintenanceNames, $misNames, $canAssignCategoryTickets, $canProgressCategoryTickets) {
                $isTechnology = strtolower(trim($category)) === 'technology/internet';
                $monthly = collect(range(0, 5))->map(function ($offset) use ($items, $monthStart, $reportWeight) {
                    $month = $monthStart->copy()->addMonths($offset);
                    $submitted = $items->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $month->format('Y-m'))->sum($reportWeight);
                    $resolved = $items->filter(fn ($report) => $report->resolved_at && $report->resolved_at->format('Y-m') === $month->format('Y-m'))->sum($reportWeight);
                    $hazards = $items->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $month->format('Y-m') && (bool) $report->is_safety_hazard)->sum($reportWeight);

                    return ['label' => $month->format('M Y'), 'submitted' => $submitted, 'resolved' => $resolved, 'hazards' => $hazards];
                })->values();
                $statusOrder = ['pending' => 1, 'assigned' => 2, 'in progress' => 3, 'resolved' => 4];
                $orderedItems = $items
                    ->sortByDesc('created_at')
                    ->sortBy(fn ($report) => $statusOrder[strtolower(trim((string) $report->status))] ?? 5);

                return [
                    'name' => $category,
                    'is_technology' => $isTechnology,
                    'staff_label' => $isTechnology ? 'MIS Staff' : 'Maintenance Staff',
                    'can_assign' => $canAssignCategoryTickets,
                    'can_progress' => $canProgressCategoryTickets,
                    'stats' => [
                        'total' => $items->sum($reportWeight),
                        'open' => $items->filter(fn ($report) => strtolower((string) $report->status) !== 'resolved')->sum($reportWeight),
                        'resolved' => $items->filter(fn ($report) => strtolower((string) $report->status) === 'resolved')->sum($reportWeight),
                        'hazards' => $items->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight),
                    ],
                    'monthly' => $monthly,
                    'tickets' => $orderedItems->map(function ($report) use ($category, $isTechnology, $maintenanceNames, $misNames, $canAssignCategoryTickets, $canProgressCategoryTickets, $reportWeight) {
                        $status = $report->status ?: 'Pending';
                        $assignee = $report->assigned_to
                            ? ($isTechnology ? $misNames->get($report->assigned_to) : $maintenanceNames->get($report->assigned_to))
                            : null;

                        return [
                            'id' => $report->id,
                            'ticket' => 'RPT-'.str_pad((string) $report->id, 5, '0', STR_PAD_LEFT),
                            'title' => $report->title ?: 'Untitled report',
                            'description' => $report->description ?: 'No description provided.',
                            'category' => $category,
                            'location' => $report->location ?: 'Not specified',
                            'status' => $status,
                            'priority' => $report->severity ?: 'Not set',
                            'is_hazard' => (bool) $report->is_safety_hazard,
                            'report_count' => $reportWeight($report),
                            'cost' => (float) ($report->cost ?? 0),
                            'assignee' => $assignee ?: ($report->assigned_to ? 'Assigned staff record' : 'Unassigned'),
                            'assigned_to' => $report->assigned_to,
                            'created_at' => optional($report->created_at)->format('M d, Y h:i A'),
                            'assigned_at' => optional($report->assigned_at)->format('M d, Y h:i A'),
                            'resolved_at' => optional($report->resolved_at)->format('M d, Y h:i A'),
                            'resolution_notes' => $report->resolution_notes,
                            'damaged_part' => $report->damaged_part,
                            'replaced_part' => $report->replaced_part,
                            'can_assign' => $canAssignCategoryTickets && ! $report->assigned_to && strtolower($status) !== 'resolved',
                            'can_progress' => $canProgressCategoryTickets && (bool) $report->assigned_to && in_array($status, ['Assigned', 'In Progress'], true),
                        ];
                    })->values(),
                ];
            })
            ->sortByDesc(fn ($category) => $category['stats']['total'])
            ->values();

        $recentAverage = round($trendStats->take(-3)->avg('reports') ?? 0, 1);
        $previousAverage = round($trendStats->take(3)->avg('reports') ?? 0, 1);
        $trendDelta = round($recentAverage - $previousAverage, 1);
        $topLocation = $locationStats->first();
        $topCategory = $categoryStats->first();
        $targetResolutionRate = 85;
        $currentMonthReports = $reports->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $currentMonthKey)->sum($reportWeight);
        $previousMonthReports = $reports->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $previousMonthKey)->sum($reportWeight);
        $monthlyChangePercent = $previousMonthReports > 0
            ? round((($currentMonthReports - $previousMonthReports) / $previousMonthReports) * 100, 1)
            : ($currentMonthReports > 0 ? 100 : 0);
        $trendDirection = $monthlyChangePercent > 0 ? 'Increasing' : ($monthlyChangePercent < 0 ? 'Decreasing' : 'Stable');
        $resolvedLast30Days = $reports
            ->filter(fn ($report) => $report->resolved_at && $report->resolved_at->gte(now()->subDays(30)))
            ->sum($reportWeight);
        $dailyResolutionCapacity = $resolvedLast30Days > 0 ? $resolvedLast30Days / 22 : 0;
        $estimatedBacklogDays = $dailyResolutionCapacity > 0 ? (int) ceil($openReports / $dailyResolutionCapacity) : null;
        $riskIndex = $topLocation ? min(100, (int) $topLocation['risk_score']) : 0;
        $priorityLevel = $hazardReports > 0 || $riskIndex >= 60 || $resolutionRate < 50
            ? 'Critical'
            : ($riskIndex >= 30 || $resolutionRate < $targetResolutionRate ? 'High' : 'Normal');
        $recurringIssueStats = $reports
            ->filter(fn ($report) => filled($report->title) && filled($report->location))
            ->groupBy(fn ($report) => strtolower(trim((string) $report->title)).'|'.strtolower(trim((string) $report->location)))
            ->map(function ($items) use ($reportWeight, $monthStart) {
                $open = $items->filter(fn ($report) => strtolower((string) $report->status) !== 'resolved')->sum($reportWeight);
                $resolvedItems = $items->filter(fn ($report) => strtolower((string) $report->status) === 'resolved');
                $hazards = $items->filter(fn ($report) => (bool) $report->is_safety_hazard)->sum($reportWeight);
                $cost = (float) $items->sum(fn ($report) => (float) ($report->cost ?? 0));
                $repairCycles = $resolvedItems->count();
                $reportCount = $items->sum($reportWeight);
                $trend = collect(range(0, 5))->map(function ($offset) use ($items, $monthStart, $reportWeight) {
                    $month = $monthStart->copy()->addMonths($offset);
                    $monthKey = $month->format('Y-m');

                    return [
                        'label' => $month->format('M Y'),
                        'reports' => $items->filter(fn ($report) => $report->created_at && $report->created_at->format('Y-m') === $monthKey)->sum($reportWeight),
                        'repairs' => $items->filter(fn ($report) => $report->resolved_at && $report->resolved_at->format('Y-m') === $monthKey)->count(),
                    ];
                })->values()->all();

                return [
                    'issue' => trim((string) $items->first()->title),
                    'location' => trim((string) $items->first()->location),
                    'count' => $reportCount,
                    'open' => $open,
                    'repair_cycles' => $repairCycles,
                    'hazards' => $hazards,
                    'cost' => $cost,
                    'recurrence_score' => ($repairCycles * 5) + $reportCount + ($hazards * 4) + min(20, (int) floor($cost / 1000)),
                    'trend' => $trend,
                ];
            })
            ->filter(fn ($issue) => $issue['repair_cycles'] >= 2)
            ->sortBy([['repair_cycles', 'desc'], ['recurrence_score', 'desc'], ['count', 'desc']])
            ->values();
        $topRecurringIssue = $recurringIssueStats->first();

        $staffStats = $reports
            ->filter(fn ($report) => $report->assigned_to)
            ->groupBy(function ($report) {
                $isTechnology = strtolower(trim((string) optional($report->category)->name)) === 'technology/internet';
                return ($isTechnology ? 'mis:' : 'maintenance:').$report->assigned_to;
            })
            ->map(function ($items) use ($reportWeight, $slaTargetHours, $maintenanceNames, $misNames) {
                $resolved = $items->filter(fn ($report) => strtolower((string) $report->status) === 'resolved');
                $timed = $resolved->filter(fn ($report) => $report->assigned_at && $report->resolved_at);
                $slaCount = $timed->filter(fn ($report) => $report->assigned_at->diffInHours($report->resolved_at) <= $slaTargetHours)->count();
                $assigned = $items->sum($reportWeight);
                $resolvedCount = $resolved->sum($reportWeight);
                $firstReport = $items->first();
                $isTechnology = strtolower(trim((string) optional($firstReport->category)->name)) === 'technology/internet';
                $staffName = $isTechnology ? $misNames->get($firstReport->assigned_to) : $maintenanceNames->get($firstReport->assigned_to);

                return [
                    'staff' => $staffName ?: 'Unassigned staff record',
                    'assigned' => $assigned,
                    'resolved' => $resolvedCount,
                    'avg_hours' => $timed->count() > 0 ? round($timed->avg(fn ($report) => $report->assigned_at->diffInHours($report->resolved_at)), 1) : null,
                    'sla' => $timed->count() > 0 ? round(($slaCount / $timed->count()) * 100, 1) : null,
                    'efficiency' => $assigned > 0 ? round(($resolvedCount / $assigned) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('resolved')
            ->values();

        $priorityStats = $reports
            ->groupBy(fn ($report) => ucfirst(strtolower((string) ($report->severity ?: 'Not set'))))
            ->map(fn ($items, $priority) => ['priority' => $priority, 'count' => $items->sum($reportWeight)])
            ->sortByDesc('count')
            ->values();

        $agingStats = collect([
            ['bucket' => '0-2 days', 'count' => $openReportItems->filter(fn ($report) => $report->created_at && $report->created_at->diffInDays($now) <= 2)->sum($reportWeight)],
            ['bucket' => '3-7 days', 'count' => $openReportItems->filter(fn ($report) => $report->created_at && $report->created_at->diffInDays($now) >= 3 && $report->created_at->diffInDays($now) <= 7)->sum($reportWeight)],
            ['bucket' => '8-14 days', 'count' => $openReportItems->filter(fn ($report) => $report->created_at && $report->created_at->diffInDays($now) >= 8 && $report->created_at->diffInDays($now) <= 14)->sum($reportWeight)],
            ['bucket' => '15+ days', 'count' => $openReportItems->filter(fn ($report) => $report->created_at && $report->created_at->diffInDays($now) >= 15)->sum($reportWeight)],
        ]);

        $costStats = $reports
            ->groupBy(fn ($report) => optional($report->category)->name ?: 'Uncategorized')
            ->map(function ($items, $category) {
                $total = round((float) $items->sum(fn ($report) => (float) ($report->cost ?? 0)), 2);
                $completed = round((float) $items->filter(fn ($report) => strtolower((string) $report->status) === 'resolved')->sum(fn ($report) => (float) ($report->cost ?? 0)), 2);

                return ['category' => $category, 'cost' => $total, 'completed_cost' => $completed, 'remaining_cost' => max(0, $total - $completed)];
            })
            ->sortByDesc('cost')
            ->take(6)
            ->values();

        $reportEvidence = fn ($items) => $items->take(10)->map(fn ($report) => [
            'id' => $report->id,
            'title' => $report->title ?: 'Untitled report',
            'location' => $report->location ?: 'Not specified',
            'status' => $report->status ?: 'Unknown',
            'priority' => $report->severity ?: 'Not set',
            'age_days' => $report->created_at ? $report->created_at->diffInDays($now) : 0,
            'assigned_to' => optional($report->assignedTo)->name ?: 'Unassigned',
        ])->values()->all();

        $aiInsights = collect();
        if ($totalReports > 0) {
            $aiInsights->push($monthlyChangePercent > 0
                ? "Report volume increased by {$monthlyChangePercent}% compared with the previous month."
                : ($monthlyChangePercent < 0 ? 'Report volume decreased by '.abs($monthlyChangePercent).'% compared with the previous month.' : 'Report volume is unchanged from the previous month.'));
            if ($topCategory) {
                $aiInsights->push("{$topCategory['category']} is the leading category with {$topCategory['count']} reports and {$topCategory['hazards']} hazard cases.");
            }
            if ($topLocation) {
                $aiInsights->push("{$topLocation['location']} has the highest operational risk score ({$topLocation['risk_score']}) and {$topLocation['open']} unresolved reports.");
            }
            $aiInsights->push($estimatedBacklogDays !== null
                ? "At the current 30-day completion rate, the open backlog may require approximately {$estimatedBacklogDays} working days to clear."
                : 'Backlog clearance cannot be estimated because no reports were resolved during the last 30 days.');
        }
        $decisionAlerts = collect();

        foreach ($recurringIssueStats->take(3) as $recurringIssue) {
            $issue = $recurringIssue['issue'];
            $location = $recurringIssue['location'];
            $recordedCost = 'PHP '.number_format($recurringIssue['cost'], 2);
            $isCritical = $recurringIssue['repair_cycles'] >= 3 || $recurringIssue['hazards'] > 0;
            $decisionAlerts->push([
                'key' => 'recurring-repair-'.substr(md5(strtolower($issue.'|'.$location)), 0, 10),
                'level' => $isCritical ? 'critical' : 'warning',
                'title' => "Evaluate replacing {$issue} in {$location}",
                'body' => "{$recurringIssue['count']} report(s), {$recurringIssue['repair_cycles']} completed repair cycle(s), {$recordedCost} recorded repair cost.",
                'why' => "The same {$issue} issue in {$location} returned after multiple completed repairs, indicating a possible ageing asset, recurring component failure, or unresolved root cause.",
                'priority' => $isCritical ? 'Critical' : 'High',
                'impact' => 'Continuing to repair the same issue in the same location may cost more over time than replacing the failing asset or component.',
                'stats' => ['Issue' => $issue, 'Location' => $location, 'Issue reports' => $recurringIssue['count'], 'Completed repairs' => $recurringIssue['repair_cycles'], 'Recorded repair cost' => $recordedCost, 'Open reports' => $recurringIssue['open']],
                'actions' => ['Obtain a replacement quotation for this location', 'Compare the quotation with cumulative and projected repair cost', 'Inspect asset age and recurring failed parts', 'Replace the asset or component when replacement offers better lifecycle value'],
                'trend' => $recurringIssue['trend'],
                'related_reports' => $reportEvidence($reports->filter(fn ($report) => strcasecmp(trim((string) $report->title), $issue) === 0 && strcasecmp(trim((string) $report->location), $location) === 0)->sortByDesc('created_at')),
            ]);
        }

        $executiveSummary = [
            'period' => (! empty($filters['date_from']) || ! empty($filters['date_to']))
                ? (($filters['date_from'] ?? 'Beginning').' to '.($filters['date_to'] ?? now()->toDateString()))
                : 'All available report data',
            'total_reports' => $totalReports,
            'resolved_reports' => $resolvedReports,
            'open_reports' => $openReports,
            'resolution_rate' => $resolutionRate,
            'total_cost' => $totalCost,
            'avg_resolution_hours' => $avgResolutionHours,
            'pending_reports' => $pendingReports,
            'assigned_reports' => $assignedReports,
            'avg_report_age_days' => $avgReportAgeDays,
            'oldest_open_days' => $oldestOpenDays,
            'avg_cost' => $avgCost,
            'completed_cost' => $completedCost,
            'remaining_recorded_cost' => $remainingRecordedCost,
            'sla_target_hours' => $slaTargetHours,
            'sla_compliance' => $slaCompliance,
            'estimated_backlog_days' => $estimatedBacklogDays,
            'monthly_change_percent' => $monthlyChangePercent,
            'trend_direction' => $trendDirection,
            'risk_index' => $riskIndex,
            'priority_level' => $priorityLevel,
            'top_recurring_issue' => $topRecurringIssue,
            'top_location' => $topLocation,
            'top_category' => $topCategory,
            'trend_delta' => $trendDelta,
        ];

        return view('admin.analytics', compact(
            'reports',
            'locations',
            'totalReports',
            'resolvedReports',
            'openReports',
            'hazardReports',
            'totalCost',
            'resolutionRate',
            'avgResolutionHours',
            'issueCostStats',
            'locationStats',
            'statusStats',
            'categoryStats',
            'categoryWorkspace',
            'trendStats',
            'decisionAlerts',
            'executiveSummary',
            'targetResolutionRate',
            'pendingReports',
            'assignedReports',
            'avgReportAgeDays',
            'oldestOpenDays',
            'avgCost',
            'completedCost',
            'remainingRecordedCost',
            'slaTargetHours',
            'slaCompliance',
            'estimatedBacklogDays',
            'monthlyChangePercent',
            'trendDirection',
            'riskIndex',
            'priorityLevel',
            'staffStats',
            'priorityStats',
            'agingStats',
            'costStats',
            'aiInsights'
        ));

        $reportCountExpression = Report::supportsReportCount()
            ? 'SUM(COALESCE(report_count, 1))'
            : 'COUNT(*)';
        $qualifiedReportCountExpression = Report::supportsReportCount()
            ? 'SUM(COALESCE(reports.report_count, 1))'
            : 'COUNT(*)';

        // Handle AJAX request for location tickets FIRST - before any other processing
        if ($request->has('location_filter') && $request->input('ajax') == '1') {
            $location = $request->input('location_filter');
            
            if ($location) {
                $query = Report::where('location', $location);
                
                // Apply date filters if present
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->input('date_from'));
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->input('date_to'));
                }
                
                $tickets = $query->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($ticket) {
                        return [
                            'id' => $ticket->id,
                            'damaged_part' => $ticket->damaged_part,
                            'title' => $ticket->title,
                            'status' => $ticket->status,
                            'report_count' => Report::supportsReportCount() ? ($ticket->report_count ?? 1) : 1,
                            'cost' => $ticket->cost,
                            'resolved_at' => $ticket->resolved_at,
                            'created_at' => $ticket->created_at,
                        ];
                    });

                // Get distinct damage parts from the tickets
                $damageParts = collect($tickets)->pluck('damaged_part')
                    ->filter(function($part) {
                        return $part !== null && $part !== '';
                    })
                    ->unique()
                    ->sort()
                    ->values();

                return response()->json([
                    'success' => true,
                    'tickets' => $tickets,
                    'count' => $tickets->sum(fn ($ticket) => $ticket['report_count'] ?? 1),
                    'damage_parts' => $damageParts
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No location specified',
                'tickets' => []
            ]);
        }
        
        // Base query: resolved reports with location
        $baseQuery = Report::whereNotNull('location')
            ->where('location', '!=', '')
            ->where('status', 'Resolved');

        // Apply room filter if provided
        if ($request->filled('room_filter')) {
            $baseQuery->where('location', $request->input('room_filter'));
        }

        // Apply filters based on period selection
        $period = $request->input('period');
        
        // Set default to last 6 months if no filters are specified
        $hasDateFilters = $request->filled('date_from') || $request->filled('date_to') || 
                         $request->filled('month') || $request->filled('year') || 
                         $period;
        
        if (!$hasDateFilters) {
            // Default: Last 6 months
            $baseQuery->where('created_at', '>=', now()->subMonths(6));
        }
        
        if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
            // Monthly: specific month and year
            $baseQuery->whereMonth('created_at', $request->input('month'))
                     ->whereYear('created_at', $request->input('year'));
        } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
            // Quarterly: month range (e.g., January to March)
            $monthFrom = (int) $request->input('month_from');
            $monthTo = (int) $request->input('month_to');
            
            if ($request->filled('year')) {
                $year = (int) $request->input('year');
                
                if ($monthFrom <= $monthTo) {
                    // Normal range within same year (e.g., Jan to Mar)
                    $baseQuery->whereYear('created_at', $year)
                             ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                             ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                } else {
                    // Range wraps around year (e.g., Nov to Feb)
                    $baseQuery->where(function($q) use ($year, $monthFrom, $monthTo) {
                        $q->where(function($q1) use ($year, $monthFrom) {
                            $q1->whereYear('created_at', $year)
                               ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                        })->orWhere(function($q2) use ($year, $monthTo) {
                            $q2->whereYear('created_at', $year + 1)
                               ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    });
                }
            } else {
                // No year specified - filter by month range across all years
                if ($monthFrom <= $monthTo) {
                    $baseQuery->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                             ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                } else {
                    // Range wraps around year
                    $baseQuery->where(function($q) use ($monthFrom, $monthTo) {
                        $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                          ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    });
                }
            }
        } elseif ($period === 'yearly' && $request->filled('year')) {
            // Yearly: entire year
            $baseQuery->whereYear('created_at', $request->input('year'));
        } else {
            // Custom filters
            if ($request->filled('month')) {
                $baseQuery->whereMonth('created_at', $request->input('month'));
            }
            if ($request->filled('year')) {
                $baseQuery->whereYear('created_at', $request->input('year'));
            }
            if ($request->filled('date_from')) {
                $baseQuery->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $baseQuery->whereDate('created_at', '<=', $request->input('date_to'));
            }
        }

        // Summary stats
        $totalConcerns = Report::supportsReportCount()
            ? (clone $baseQuery)->sum('report_count')
            : (clone $baseQuery)->count();
        $totalCost     = (clone $baseQuery)->sum('cost') ?? 0;

        // Location stats with individual items - for detailed modal
        $locationStatsDetailed = (clone $baseQuery)
            ->with('category')
            ->select('id', 'location', 'title', 'damaged_part', 'category_id', 'cost', 'resolved_at')
            ->when(Report::supportsReportCount(), fn ($query) => $query->addSelect('report_count'))
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderBy('location')
            ->orderByDesc('cost')
            ->get()
            ->map(function ($stat) {
                return [
                    'id' => $stat->id,
                    'location' => $stat->location,
                    'title' => $stat->title,
                    'damaged_part' => $stat->damaged_part ?: 'N/A',
                    'category' => $stat->category ? $stat->category->name : 'Uncategorized',
                    'cost' => $stat->cost ?? 0,
                    'report_count' => Report::supportsReportCount() ? ($stat->report_count ?? 1) : 1,
                    'resolved_at' => $stat->resolved_at ? $stat->resolved_at->format('M d, Y') : 'N/A',
                ];
            });
        
        // Location stats grouped - for existing modals and displays
        $locationStats = (clone $baseQuery)
            ->select('location', 'title')
            ->selectRaw($reportCountExpression.' as count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->groupBy('location', 'title')
            ->orderByDesc('count')
            ->get()
            ->map(function ($stat) {
                return [
                    'location' => $stat->location,
                    'title' => $stat->title,
                    'count' => $stat->count,
                    'total_cost' => $stat->total_cost ?? 0,
                ];
            });
        
        // For chart data, we still need aggregated by location only
        $locationChartStats = (clone $baseQuery)
            ->select('location')
            ->selectRaw($reportCountExpression.' as count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->groupBy('location')
            ->orderByDesc('count')
            ->get();

        // Combined location stats (all tickets)
        $combinedLocationStats = Report::whereNotNull('location')
            ->where('location', '!=', '')
            ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
            ->select('location')
            ->selectRaw($reportCountExpression.' as total_count')
            ->selectRaw('SUM(COALESCE(cost, 0)) as total_cost')
            ->groupBy('location')
            ->orderByDesc('total_count')
            ->get()
            ->map(function ($stat) {
                return [
                    'location' => $stat->location,
                    'total_count' => $stat->total_count,
                    'total_cost' => $stat->total_cost ?? 0,
                ];
            });

        // Store full list for reference
        $combinedLocationStatsAll = $combinedLocationStats;

        // Paginate combined location stats (10 per page)
        $perPageLocation = 10;
        $currentPageLocation = (int) request()->input('location_page', 1);
        $totalLocations = $combinedLocationStatsAll->count();
        $offsetLocation = ($currentPageLocation - 1) * $perPageLocation;
        $paginatedLocationStats = $combinedLocationStatsAll->slice($offsetLocation, $perPageLocation);
        $combinedLocationStats = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLocationStats,
            $totalLocations,
            $perPageLocation,
            $currentPageLocation,
            ['path' => request()->url(), 'pageName' => 'location_page']
        );

        // Handle AJAX request for location pagination
        if ($request->input('ajax') === 'locations') {
            $locationsHtml = '';
            foreach ($paginatedLocationStats as $stat) {
                $fixedCount = $stat['fixed_count'] ?? $stat['total_count']; // Fallback for backward compatibility
                $avgCost = $fixedCount > 0 ? $stat['total_cost'] / $fixedCount : 0;
                $location = addslashes($stat['location']);
                $locationsHtml .= '<tr style="cursor: pointer; transition: all 0.2s;" 
                    data-location="' . htmlspecialchars($stat['location']) . '"
                    onclick="showLocationTicketsModal(\'' . $location . '\', ' . $stat['total_count'] . ', ' . $stat['total_cost'] . ')"
                    onmouseover="this.style.backgroundColor=\'#f0f4ff\'"
                    onmouseout="this.style.backgroundColor=\'\'">
                    <td><strong>' . htmlspecialchars($stat['location']) . '</strong></td>
                    <td><span class="count-badge">' . $stat['total_count'] . '</span></td>
                    <td><span class="cost-badge">₱' . number_format($stat['total_cost'], 2) . '</span></td>
                    <td>₱' . number_format($avgCost, 2) . '</td>
                </tr>';
            }
            
            if (empty($locationsHtml)) {
                $locationsHtml = '<tr><td colspan="4" class="text-center">No data found</td></tr>';
            }
            
            $paginationHtml = $combinedLocationStats->appends(request()->except('location_page'))->links('pagination::bootstrap-4')->render();
            $showingText = 'Showing ' . ($combinedLocationStats->firstItem() ?? 0) . ' – ' . ($combinedLocationStats->lastItem() ?? 0) . ' of ' . $combinedLocationStats->total() . ' locations';
            
            return response()->json([
                'success' => true,
                'locations_html' => $locationsHtml,
                'pagination_html' => $paginationHtml,
                'showing_text' => $showingText,
                'has_pages' => $combinedLocationStats->hasPages()
            ]);
        }

        // Reports with details
        $reports = (clone $baseQuery)
            ->whereNotNull('resolved_at')
            ->orderBy('resolved_at', 'desc')
            ->get();

        // Chart data (aggregated by location for the pie chart)
        $chartLocations = $locationChartStats->pluck('location')->toArray();
        $chartCounts = $locationChartStats->pluck('count')->toArray();
        $chartCosts = $locationChartStats->pluck('total_cost')->toArray();

        // Status distribution - use same filters as baseQuery
        $statusQuery = Report::select('status');
        
        // Apply room filter if provided
        if ($request->filled('room_filter')) {
            $statusQuery->where('location', $request->input('room_filter'));
        }
        
        // Apply same period filters
        if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
            $statusQuery->whereMonth('created_at', $request->input('month'))
                       ->whereYear('created_at', $request->input('year'));
        } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
            $monthFrom = (int) $request->input('month_from');
            $monthTo = (int) $request->input('month_to');
            
            if ($request->filled('year')) {
                $year = (int) $request->input('year');
                
                if ($monthFrom <= $monthTo) {
                    $statusQuery->whereYear('created_at', $year)
                               ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                               ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                } else {
                    $statusQuery->where(function($q) use ($year, $monthFrom, $monthTo) {
                        $q->where(function($q1) use ($year, $monthFrom) {
                            $q1->whereYear('created_at', $year)
                               ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                        })->orWhere(function($q2) use ($year, $monthTo) {
                            $q2->whereYear('created_at', $year + 1)
                               ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    });
                }
            } else {
                if ($monthFrom <= $monthTo) {
                    $statusQuery->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                               ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                } else {
                    $statusQuery->where(function($q) use ($monthFrom, $monthTo) {
                        $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                          ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    });
                }
            }
        } elseif ($period === 'yearly' && $request->filled('year')) {
            $statusQuery->whereYear('created_at', $request->input('year'));
        } else {
            if ($request->filled('month')) {
                $statusQuery->whereMonth('created_at', $request->input('month'));
            }
            if ($request->filled('year')) {
                $statusQuery->whereYear('created_at', $request->input('year'));
            }
            if ($request->filled('date_from')) {
                $statusQuery->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $statusQuery->whereDate('created_at', '<=', $request->input('date_to'));
            }
        }
        
        $statusStats = $statusQuery->selectRaw($reportCountExpression . ' as count')
            ->groupBy('status')
            ->get();

        $chartStatuses = $statusStats->pluck('status')->toArray();
        $chartStatusCounts = $statusStats->pluck('count')->toArray();
        
        // Get report IDs with titles grouped by status for the modal
        $statusReportIds = [];
        foreach ($chartStatuses as $status) {
            $query = Report::where('status', $status);
            
            // Apply room filter if provided
            if ($request->filled('room_filter')) {
                $query->where('location', $request->input('room_filter'));
            }
            
            // Apply same filters as statusQuery
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $query->whereMonth('created_at', $request->input('month'))
                      ->whereYear('created_at', $request->input('year'));
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFrom = (int) $request->input('month_from');
                $monthTo = (int) $request->input('month_to');
                
                if ($request->filled('year')) {
                    $year = (int) $request->input('year');
                    
                    if ($monthFrom <= $monthTo) {
                        $query->whereYear('created_at', $year)
                              ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                              ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $query->where(function($q) use ($year, $monthFrom, $monthTo) {
                            $q->where(function($q1) use ($year, $monthFrom) {
                                $q1->whereYear('created_at', $year)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                            })->orWhere(function($q2) use ($year, $monthTo) {
                                $q2->whereYear('created_at', $year + 1)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                            });
                        });
                    }
                } else {
                    if ($monthFrom <= $monthTo) {
                        $query->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                              ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $query->where(function($q) use ($monthFrom, $monthTo) {
                            $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                              ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    }
                }
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $query->whereYear('created_at', $request->input('year'));
            } else {
                if ($request->filled('month')) {
                    $query->whereMonth('created_at', $request->input('month'));
                }
                if ($request->filled('year')) {
                    $query->whereYear('created_at', $request->input('year'));
                }
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->input('date_from'));
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->input('date_to'));
                }
            }
            
            $reports = $query->select('id', 'title')->get();
            
            $formattedReports = [];
            foreach ($reports as $report) {
                $ticketNum = '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT);
                $title = $report->title ? substr($report->title, 0, 30) : 'N/A';
                if (strlen($report->title ?? '') > 30) {
                    $title .= '...';
                }
                $formattedReports[] = $ticketNum . ' - ' . $title;
            }
            
            $statusReportIds[$status] = implode(', ', $formattedReports);
        }
        
        // ========== RESPONSE TIME ANALYSIS (must be before AJAX response) ==========
        
        try {
            // 1. Response Time Analysis
            $responseTimeStats = Report::whereNotNull('assigned_at')
                ->whereNotNull('resolved_at')
                ->where('is_deleted', false)
                ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->get()
                ->map(function($report) {
                    // Calculate time differences in seconds
                    $submittedToAssignedSeconds = $report->created_at->diffInSeconds($report->assigned_at, false);
                    $assignedToResolvedSeconds = $report->assigned_at->diffInSeconds($report->resolved_at, false);
                    $totalTimeSeconds = $report->created_at->diffInSeconds($report->resolved_at, false);
                    
                    // Convert to user-friendly format (e.g., "30m", "1h 30m", "1d 5h")
                    $formatTime = function($seconds) {
                        if ($seconds < 0) return '0m';
                        
                        $days = floor($seconds / 86400); // 86400 seconds in a day
                        $hours = floor(($seconds % 86400) / 3600);
                        $minutes = floor(($seconds % 3600) / 60);
                        
                        if ($days > 0) {
                            return $days . 'd ' . $hours . 'h';
                        } elseif ($hours > 0) {
                            return $hours . 'h ' . $minutes . 'm';
                        } elseif ($minutes > 0) {
                            return $minutes . 'm';
                        } else {
                            return '< 1m';
                        }
                    };
                    
                    return [
                        'id' => $report->id,
                        'title' => $report->title ?? 'N/A',
                        'location' => $report->location,
                        'submitted_to_assigned' => $submittedToAssignedSeconds,
                        'assigned_to_resolved' => $assignedToResolvedSeconds,
                        'total_time' => $totalTimeSeconds,
                        'submitted_to_assigned_formatted' => $formatTime($submittedToAssignedSeconds),
                        'assigned_to_resolved_formatted' => $formatTime($assignedToResolvedSeconds),
                        'total_time_formatted' => $formatTime($totalTimeSeconds),
                        'assigned_to_name' => optional($report->assignedTo)->name ?? 'N/A',
                        'created_at' => $report->created_at->format('Y-m-d h:i:s A'),
                        'assigned_at' => $report->assigned_at->format('Y-m-d h:i:s A'),
                        'resolved_at' => $report->resolved_at->format('Y-m-d h:i:s A'),
                    ];
                })
                ->filter(function($item) {
                    // Filter out records with invalid date sequences (negative values)
                    return $item['submitted_to_assigned'] >= 0 
                        && $item['assigned_to_resolved'] >= 0 
                        && $item['total_time'] >= 0;
                })
                ->values();

            // Calculate averages in hours for display
            $avgSubmittedToAssigned = $responseTimeStats->avg('submitted_to_assigned') / 3600 ?? 0;
            $avgAssignedToResolved = $responseTimeStats->avg('assigned_to_resolved') / 3600 ?? 0;
            $avgTotalTime = $responseTimeStats->avg('total_time') / 3600 ?? 0;
        } catch (\Exception $e) {
            \Log::error('Response Time Analysis Error: ' . $e->getMessage());
            $responseTimeStats = collect();
            $avgSubmittedToAssigned = 0;
            $avgAssignedToResolved = 0;
            $avgTotalTime = 0;
        }

        // Monthly stats - apply same filters
        $monthlyQuery = Report::query();
        
        // Apply room filter if provided
        if ($request->filled('room_filter')) {
            $monthlyQuery->where('location', $request->input('room_filter'));
        }
        
        // Apply same period filters
        if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
            $monthlyQuery->whereMonth('created_at', $request->input('month'))
                        ->whereYear('created_at', $request->input('year'));
        } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
            $monthFrom = (int) $request->input('month_from');
            $monthTo = (int) $request->input('month_to');
            
            if ($request->filled('year')) {
                $year = (int) $request->input('year');
                
                if ($monthFrom <= $monthTo) {
                    $monthlyQuery->whereYear('created_at', $year)
                                ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                } else {
                    $monthlyQuery->where(function($q) use ($year, $monthFrom, $monthTo) {
                        $q->where(function($q1) use ($year, $monthFrom) {
                            $q1->whereYear('created_at', $year)
                               ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                        })->orWhere(function($q2) use ($year, $monthTo) {
                            $q2->whereYear('created_at', $year + 1)
                               ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    });
                }
            } else {
                if ($monthFrom <= $monthTo) {
                    $monthlyQuery->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                } else {
                    $monthlyQuery->where(function($q) use ($monthFrom, $monthTo) {
                        $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                          ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    });
                }
            }
        } elseif ($period === 'yearly' && $request->filled('year')) {
            $monthlyQuery->whereYear('created_at', $request->input('year'));
        } else {
            // Default to last 6 months if no specific filters
            if (!$request->filled('month') && !$request->filled('year') && !$request->filled('date_from') && !$request->filled('date_to')) {
                $monthlyQuery->where('created_at', '>=', now()->subMonths(6));
            }
            if ($request->filled('month')) {
                $monthlyQuery->whereMonth('created_at', $request->input('month'));
            }
            if ($request->filled('year')) {
                $monthlyQuery->whereYear('created_at', $request->input('year'));
            }
            if ($request->filled('date_from')) {
                $monthlyQuery->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $monthlyQuery->whereDate('created_at', '<=', $request->input('date_to'));
            }
        }
        
        $monthlyStats = $monthlyQuery->selectRaw("TO_CHAR(reports.created_at, 'YYYY-MM') as month")
            ->selectRaw("COALESCE(NULLIF(reports.title, ''), LEFT(reports.description, 50)) as title")
            ->selectRaw('reports.status')
            ->selectRaw($qualifiedReportCountExpression.' as count')
            ->selectRaw("STRING_AGG(CAST(reports.id AS TEXT), ',' ORDER BY reports.id) as ticket_ids")
            ->selectRaw("STRING_AGG(COALESCE(NULLIF(reports.damaged_part, ''), 'N/A'), '|' ORDER BY reports.id) as damaged_parts")
            ->groupByRaw("month, COALESCE(NULLIF(reports.title, ''), LEFT(reports.description, 50)), reports.status")
            ->orderBy('month')
            ->get();

        // Monthly cost data for period comparison chart
        $monthlyCostQuery = clone $monthlyQuery;
        $monthlyCostData = $monthlyCostQuery->selectRaw("TO_CHAR(reports.created_at, 'YYYY-MM') as month")
            ->selectRaw($qualifiedReportCountExpression.' as count')
            ->selectRaw('SUM(COALESCE(reports.cost, 0)) as total_cost')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Trend alerts
        $trendAlerts = collect();
        $locationIssues = Report::whereNotNull('location')
            ->where('location', '!=', '')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->select('location', 'title')
            ->distinct()
            ->get();

        foreach ($locationIssues as $li) {
            $loc   = $li->location;
            $issue = $li->title;
            $recentQuery = Report::where('location', $loc)->where('title', $issue)
                ->where('created_at', '>=', now()->subMonths(3));
            $recent = Report::supportsReportCount() ? $recentQuery->sum('report_count') : $recentQuery->count();
            if ($recent < 1) continue;
            $allTimeCost = Report::where('location', $loc)->where('title', $issue)->sum('cost') ?? 0;
            $recentCost  = Report::where('location', $loc)->where('title', $issue)
                ->where('created_at', '>=', now()->subMonths(3))->sum('cost') ?? 0;
            $priorQuery = Report::where('location', $loc)->where('title', $issue)
                ->whereBetween('created_at', [now()->subMonths(6), now()->subMonths(3)]);
            $prior = Report::supportsReportCount() ? $priorQuery->sum('report_count') : $priorQuery->count();
            $severity   = $recent >= 3 ? 'critical' : ($recent >= 2 ? 'warning' : 'info');
            $alertTitle = $severity === 'critical' ? 'High Frequency Issue' : ($severity === 'warning' ? 'Recurring Issue' : 'Issue Detected');
            
            // Get monthly breakdown for the last 12 months
            $monthlyCosts = Report::where('location', $loc)
                ->where('title', $issue)
                ->where('status', 'Resolved')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw("TO_CHAR(reports.created_at, 'YYYY-MM') as month, {$qualifiedReportCountExpression} as count, SUM(reports.cost) as cost")
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function($row) {
                    return [
                        'month' => \Carbon\Carbon::parse($row->month . '-01')->format('M Y'),
                        'count' => $row->count,
                        'cost' => $row->cost ?? 0
                    ];
                });
            
            // Get replacement threshold based on issue type
            $assetOriginalPrices = [
                'aircon' => 35000, 'air conditioner' => 35000, 'ac' => 35000,
                'chair' => 800, 'table' => 1500, 'desk' => 1500,
                'door' => 3000, 'window' => 2000,
                'light' => 200, 'bulb' => 200, 'lighting' => 200,
                'fan' => 1200, 'ceiling fan' => 1200,
                'computer' => 20000, 'pc' => 20000,
                'monitor' => 9000,
                'mouse' => 100, 'keyboard' => 150,
                'projector'=> 25000, 'whiteboard' => 1700,
            ];
            $key = strtolower(trim($issue));
            $replacementThreshold = $assetOriginalPrices[$key] ?? 10000;
            
            $trendAlerts->push([
                'location'    => $loc, 'top_issue' => $issue,
                'recent'      => $recent, 'prior' => $prior,
                'recent_cost' => $recentCost, 'all_time_cost' => $allTimeCost,
                'severity'    => $severity, 'alert_title' => $alertTitle,
                'updated_at'  => Report::where('location', $loc)->where('title', $issue)->latest()->value('updated_at'),
                'monthly_costs' => $monthlyCosts, 'replacement_threshold' => $replacementThreshold,
            ]);
        }
        $trendAlerts = $trendAlerts->sortByDesc('recent')->values();

        // Paginate trend alerts (10 per page)
        $perPage = 10;
        $currentPage = request('alerts_page', 1);
        $total = $trendAlerts->count();
        $offset = ($currentPage - 1) * $perPage;
        $paginatedAlerts = $trendAlerts->slice($offset, $perPage);
        $trendAlerts = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedAlerts,
            $total,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'pageName' => 'alerts_page']
        );

        $employeePerformanceStats = $this->buildEmployeePerformanceStats($request);

        // Handle AJAX request for alerts pagination
        if ($request->input('ajax') === 'alerts') {
            $alertsHtml = '';
            foreach ($paginatedAlerts as $alert) {
                $borderColor = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                $bgColor = $alert['severity'] === 'critical' ? '#fef2f2' : ($alert['severity'] === 'warning' ? '#fff7ed' : '#fffbeb');
                $iconColor = $alert['severity'] === 'critical' ? '#ef4444' : ($alert['severity'] === 'warning' ? '#f97316' : '#f59e0b');
                $timeAgo = isset($alert['updated_at']) && $alert['updated_at'] ? \Carbon\Carbon::parse($alert['updated_at'])->diffForHumans(null, true, true) : 'recently';
                $alertJson = htmlspecialchars(json_encode($alert), ENT_QUOTES, 'UTF-8');
                
                $alertsHtml .= '<div style="display:flex;align-items:center;gap:14px;padding:14px 16px;border-left:4px solid ' . $borderColor . ';background:' . $bgColor . ';border-radius:8px;margin-bottom:10px;cursor:pointer;" onclick="showCostTrendModal(' . $alertJson . ')">';
                $alertsHtml .= '<div style="width:36px;height:36px;border-radius:50%;background:' . $iconColor . ';display:flex;align-items:center;justify-content:center;flex-shrink:0;">';
                $alertsHtml .= '<i class="fas fa-triangle-exclamation" style="color:#fff;font-size:15px;"></i>';
                $alertsHtml .= '</div>';
                $alertsHtml .= '<div style="flex:1;">';
                $alertsHtml .= '<div style="font-weight:700;font-size:.95rem;color:#1e293b;">' . htmlspecialchars($alert['alert_title'] ?? 'Trend Detected') . '</div>';
                $alertsHtml .= '<div style="font-size:.82rem;color:#64748b;">';
                if (!empty($alert['top_issue'])) {
                    $alertsHtml .= htmlspecialchars($alert['top_issue']) . ' on ' . htmlspecialchars($alert['location']);
                } else {
                    $alertsHtml .= htmlspecialchars($alert['location']);
                }
                $alertsHtml .= '</div>';
                $alertsHtml .= '</div>';
                $alertsHtml .= '<div style="font-size:.78rem;color:#94a3b8;white-space:nowrap;">' . $timeAgo . '</div>';
                $alertsHtml .= '</div>';
            }
            
            if (empty($alertsHtml)) {
                $alertsHtml = '<div class="text-center py-4 text-muted">No alerts found</div>';
            }
            
            $paginationHtml = $trendAlerts->appends(request()->except('alerts_page'))->links('pagination::bootstrap-4')->render();
            $showingText = 'Showing ' . ($trendAlerts->firstItem() ?? 0) . ' – ' . ($trendAlerts->lastItem() ?? 0) . ' of ' . $trendAlerts->total() . ' alerts';
            
            return response()->json([
                'success' => true,
                'alerts_html' => $alertsHtml,
                'pagination_html' => $paginationHtml,
                'showing_text' => $showingText,
                'total_count' => $trendAlerts->total(),
                'has_pages' => $trendAlerts->hasPages()
            ]);
        }

        // Handle AJAX request for detailed alert data (damaged parts breakdown)
        if ($request->input('ajax') === 'alert_detail') {
            $location = $request->input('location');
            $issue = $request->input('issue');
            
            if (!$location || !$issue) {
                return response()->json(['success' => false, 'message' => 'Missing location or issue parameter']);
            }
            
            // Get all resolved reports for this location and issue
            $reports = Report::where('location', $location)
                ->where('title', $issue)
                ->where('status', 'Resolved')
                ->whereNotNull('resolved_at')
                ->orderBy('resolved_at', 'desc')
                ->get();
            
            // Group by damaged_part
            $partBreakdown = [];
            foreach ($reports as $report) {
                $part = $report->damaged_part ?: 'Not Specified';
                
                if (!isset($partBreakdown[$part])) {
                    $partBreakdown[$part] = [
                        'part_name' => $part,
                        'count' => 0,
                        'total_cost' => 0,
                        'tickets' => []
                    ];
                }
                
                $partBreakdown[$part]['count']++;
                $partBreakdown[$part]['total_cost'] += $report->cost ?? 0;
                $partBreakdown[$part]['tickets'][] = [
                    'ticket_number' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                    'cost' => $report->cost ?? 0,
                    'date_fixed' => $report->resolved_at ? $report->resolved_at->format('M d, Y h:i A') : 'N/A',
                    'description' => $report->description ? substr($report->description, 0, 100) : 'N/A'
                ];
            }
            
            // Sort by count descending
            usort($partBreakdown, function($a, $b) {
                return $b['count'] - $a['count'];
            });
            
            // Get monthly breakdown
            $monthlyCosts = Report::where('location', $location)
                ->where('title', $issue)
                ->where('status', 'Resolved')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw("TO_CHAR(reports.created_at, 'YYYY-MM') as month")
                ->selectRaw((Report::supportsReportCount() ? 'SUM(COALESCE(reports.report_count, 1))' : 'COUNT(*)') . ' as count')
                ->selectRaw('SUM(reports.cost) as cost')
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function($row) {
                    return [
                        'month' => \Carbon\Carbon::parse($row->month . '-01')->format('M Y'),
                        'count' => $row->count,
                        'cost' => $row->cost ?? 0
                    ];
                });
            
            return response()->json([
                'success' => true,
                'location' => $location,
                'issue' => $issue,
                'part_breakdown' => array_values($partBreakdown),
                'monthly_costs' => $monthlyCosts,
                'total_repairs' => Report::supportsReportCount() ? $reports->sum('report_count') : $reports->count(),
                'total_cost' => $reports->sum('cost')
            ]);
        }

        // ========== ADVANCED ANALYTICS (Calculate before AJAX response) ==========
        
        try {
            // 2. Cost by Category Analysis
            $costByCategory = Report::with('category')
                ->whereNotNull('category_id')
                ->where('is_deleted', false)
                ->whereNotNull('resolved_at')
                ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->get()
                ->groupBy('category.name')
                ->map(function($group, $categoryName) {
                    return [
                        'category' => $categoryName ?: 'Uncategorized',
                        'count' => $group->count(),
                        'total_cost' => $group->sum('cost') ?? 0,
                        'avg_cost' => $group->avg('cost') ?? 0,
                        'percentage' => 0, // Will calculate after
                    ];
                })
                ->sortByDesc('total_cost')
                ->values();

            $totalCategoryCost = $costByCategory->sum('total_cost');
            $costByCategory = $costByCategory->map(function($item) use ($totalCategoryCost) {
                $item['percentage'] = $totalCategoryCost > 0 ? ($item['total_cost'] / $totalCategoryCost) * 100 : 0;
                return $item;
            });
        } catch (\Exception $e) {
            \Log::error('Cost by Category Analysis Error: ' . $e->getMessage());
            $costByCategory = collect();
        }

        // Handle AJAX requests - return JSON data
        if ($request->ajax() || $request->input('ajax')) {
            return response()->json([
                'chartLocations' => $chartLocations,
                'chartCounts' => $chartCounts,
                'chartCosts' => $chartCosts,
                'chartStatuses' => $chartStatuses,
                'chartStatusCounts' => $chartStatusCounts,
                'statusReportIds' => $statusReportIds,
                'monthlyStats' => $monthlyStats->map(fn($s) => [
                    'month' => $s->month,
                    'title' => $s->title,
                    'status' => $s->status,
                    'count' => $s->count
                ])->values(),
                'monthlyCostData' => $monthlyCostData->map(fn($s) => [
                    'month' => $s->month,
                    'count' => $s->count,
                    'total_cost' => $s->total_cost
                ])->values(),
                'locationStats' => $locationStats,
                'locationStatsDetailed' => $locationStatsDetailed,
                'responseTimeStats' => $responseTimeStats ?? collect(),
                'avgSubmittedToAssigned' => $avgSubmittedToAssigned ?? 0,
                'avgAssignedToResolved' => $avgAssignedToResolved ?? 0,
                'avgTotalTime' => $avgTotalTime ?? 0,
                'costByCategory' => $costByCategory,
                'employeePerformanceStats' => $employeePerformanceStats,
            ]);
        }

        return view('admin.analytics', compact(
            'totalConcerns',
            'totalCost',
            'locationStats',
            'locationStatsDetailed',
            'combinedLocationStats',
            'reports',
            'chartLocations',
            'chartCounts',
            'chartCosts',
            'chartStatuses',
            'chartStatusCounts',
            'statusReportIds',
            'monthlyStats',
            'monthlyCostData',
            'trendAlerts',
            'responseTimeStats',
            'avgSubmittedToAssigned',
            'avgAssignedToResolved',
            'avgTotalTime',
            'costByCategory',
            'employeePerformanceStats'
        ));
    }

    // Get Period Breakdown - Individual repairs for a specific period
    public function getPeriodBreakdown(Request $request)
    {
        try {
            $period = $request->input('period'); // YYYY-MM or YYYY format
            $location = $request->input('location');
            $category = $request->input('category');
            
            // Base query for all reports (not just resolved with cost)
            $query = Report::query();
            
            // Apply period filter
            if (strlen($period) === 4) {
                // Year format (YYYY)
                $query->whereYear('created_at', $period);
            } else {
                // Month format (YYYY-MM)
                $query->whereRaw("TO_CHAR(created_at, 'YYYY-MM') = ?", [$period]);
            }
            
            // Apply location filter if provided
            if ($location && $location !== 'all') {
                $query->where('location', $location);
            }
            
            // Apply category filter if provided
            if ($category && $category !== 'all') {
                $query->where('title', $category);
            }
            
            // Get all repairs with relevant details
            $repairs = $query->select([
                    'id',
                    'title',
                    'location',
                    'damaged_part',
                    'cost',
                    'status',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'repairs' => $repairs
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Period breakdown error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch period breakdown'
            ], 500);
        }
    }

    // Get All Periods Breakdown - Individual repairs across all periods
    public function getAllPeriodsBreakdown(Request $request)
    {
        try {
            $location = $request->input('location');
            $category = $request->input('category');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            
            // Base query for all reports
            $query = Report::query();
            
            // Apply date range filters
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
            
            // If no date filters, default to last 6 months
            if (!$dateFrom && !$dateTo) {
                $query->where('created_at', '>=', now()->subMonths(6));
            }
            
            // Apply location filter if provided
            if ($location && $location !== 'all') {
                $query->where('location', $location);
            }
            
            // Apply category filter if provided
            if ($category && $category !== 'all') {
                $query->where('title', $category);
            }
            
            // Get all repairs with relevant details
            $repairs = $query->select([
                    'id',
                    'title',
                    'location',
                    'damaged_part',
                    'cost',
                    'status',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'repairs' => $repairs
            ]);
            
        } catch (\Exception $e) {
            \Log::error('All periods breakdown error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch all periods breakdown'
            ], 500);
        }
    }

    // Export Period Breakdown to PDF
    public function exportPeriodBreakdownPDF(Request $request)
    {
        try {
            $period = $request->input('period');
            $periodLabel = $request->input('period_label');
            $location = $request->input('location');
            $category = $request->input('category');
            $preview = $request->input('preview', false);
            
            // Base query for all reports
            $query = Report::query();
            
            // Apply period filter
            if (strlen($period) === 4) {
                $query->whereYear('created_at', $period);
            } else {
                $query->whereRaw("TO_CHAR(created_at, 'YYYY-MM') = ?", [$period]);
            }
            
            // Apply location filter if provided
            if ($location && $location !== 'all') {
                $query->where('location', $location);
            }
            
            // Apply category filter if provided
            if ($category && $category !== 'all') {
                $query->where('title', $category);
            }
            
            // Get repairs
            $repairs = $query->select([
                    'id',
                    'title',
                    'location',
                    'damaged_part',
                    'cost',
                    'status',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calculate statistics
            $totalRepairs = $repairs->count();
            $totalCost = $repairs->sum('cost');
            $repairsWithCost = $repairs->where('cost', '>', 0)->count();
            $avgCost = $repairsWithCost > 0 ? $totalCost / $repairsWithCost : 0;
            
            $pdf = \PDF::loadView('admin.period-breakdown-pdf', compact(
                'repairs',
                'periodLabel',
                'totalRepairs',
                'totalCost',
                'avgCost'
            ));
            
            // Stream the PDF to open in browser (like main analytics)
            return $pdf->stream($periodLabel . '_Repair_Breakdown.pdf');
            
        } catch (\Exception $e) {
            \Log::error('Period breakdown PDF error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF');
        }
    }

    // Export All Periods Breakdown to PDF
    public function exportAllPeriodsBreakdownPDF(Request $request)
    {
        try {
            $location = $request->input('location');
            $category = $request->input('category');
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $preview = $request->input('preview', false);
            
            // Base query for all reports
            $query = Report::query();
            
            // Apply date range filters
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
            
            // If no date filters, default to last 6 months
            if (!$dateFrom && !$dateTo) {
                $query->where('created_at', '>=', now()->subMonths(6));
            }
            
            // Apply location filter if provided
            if ($location && $location !== 'all') {
                $query->where('location', $location);
            }
            
            // Apply category filter if provided
            if ($category && $category !== 'all') {
                $query->where('title', $category);
            }
            
            // Get repairs
            $repairs = $query->select([
                    'id',
                    'title',
                    'location',
                    'damaged_part',
                    'cost',
                    'status',
                    'created_at'
                ])
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Calculate statistics
            $totalRepairs = $repairs->count();
            $totalCost = $repairs->sum('cost');
            $repairsWithCost = $repairs->where('cost', '>', 0)->count();
            $avgCost = $repairsWithCost > 0 ? $totalCost / $repairsWithCost : 0;
            
            // Determine period label
            $periodLabel = 'All Periods';
            if ($dateFrom && $dateTo) {
                $periodLabel = \Carbon\Carbon::parse($dateFrom)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($dateTo)->format('M d, Y');
            } elseif (!$dateFrom && !$dateTo) {
                $periodLabel = 'Last 6 Months';
            }
            
            $pdf = \PDF::loadView('admin.period-breakdown-pdf', compact(
                'repairs',
                'periodLabel',
                'totalRepairs',
                'totalCost',
                'avgCost'
            ));
            
            // Stream the PDF to open in browser (like main analytics)
            return $pdf->stream('All_Periods_Repair_Breakdown.pdf');
            
        } catch (\Exception $e) {
            \Log::error('All periods breakdown PDF error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF');
        }
    }

    // Export Location Report to PDF
    public function locationReportPDF(Request $request)
    {
        try {
            // Base query: resolved reports with location
            $baseQuery = Report::whereNotNull('location')
                ->where('location', '!=', '')
                ->where('status', 'Resolved');

            // Apply room filter if provided
            if ($request->filled('room_filter')) {
                $baseQuery->where('location', $request->input('room_filter'));
            }

            // Apply filters based on period selection
            $period = $request->input('period');
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $baseQuery->whereMonth('created_at', $request->input('month'))
                         ->whereYear('created_at', $request->input('year'));
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFrom = (int) $request->input('month_from');
                $monthTo = (int) $request->input('month_to');
                
                if ($request->filled('year')) {
                    $year = (int) $request->input('year');
                    
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereYear('created_at', $year)
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($year, $monthFrom, $monthTo) {
                            $q->where(function($q1) use ($year, $monthFrom) {
                                $q1->whereYear('created_at', $year)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                            })->orWhere(function($q2) use ($year, $monthTo) {
                                $q2->whereYear('created_at', $year + 1)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                            });
                        });
                    }
                } else {
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($monthFrom, $monthTo) {
                            $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                              ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    }
                }
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $baseQuery->whereYear('created_at', $request->input('year'));
            } else {
                if ($request->filled('month')) {
                    $baseQuery->whereMonth('created_at', $request->input('month'));
                }
                if ($request->filled('year')) {
                    $baseQuery->whereYear('created_at', $request->input('year'));
                }
                if ($request->filled('date_from')) {
                    $baseQuery->whereDate('created_at', '>=', $request->input('date_from'));
                }
                if ($request->filled('date_to')) {
                    $baseQuery->whereDate('created_at', '<=', $request->input('date_to'));
                }
            }

            // Get detailed location stats with individual ticket items
            $locationStatsDetailed = (clone $baseQuery)
                ->with('category')
                ->select('id', 'location', 'title', 'damaged_part', 'category_id', 'cost', 'resolved_at')
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->orderBy('location')
                ->orderByDesc('cost')
                ->get()
                ->map(function ($stat) {
                    return [
                        'id' => $stat->id,
                        'location' => $stat->location,
                        'title' => $stat->title,
                        'damaged_part' => $stat->damaged_part ?: 'N/A',
                        'category' => $stat->category ? $stat->category->name : 'Uncategorized',
                        'cost' => $stat->cost ?? 0,
                        'resolved_at' => $stat->resolved_at ? $stat->resolved_at->format('M d, Y') : 'N/A',
                    ];
                });

            $totalRepairs = $locationStatsDetailed->count();
            $totalCost = $locationStatsDetailed->sum('cost');
            
            // Get unique locations and categories count
            $uniqueLocations = $locationStatsDetailed->pluck('location')->unique()->count();
            $uniqueCategories = $locationStatsDetailed->pluck('category')->unique()->count();
            
            // Build date range string
            $dateRange = '';
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $monthName = \Carbon\Carbon::createFromDate($request->input('year'), $request->input('month'), 1)->format('F');
                $dateRange = $monthName . ' ' . $request->input('year');
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFromName = \Carbon\Carbon::createFromDate(null, $request->input('month_from'), 1)->format('F');
                $monthToName = \Carbon\Carbon::createFromDate(null, $request->input('month_to'), 1)->format('F');
                $year = $request->filled('year') ? ' ' . $request->input('year') : '';
                $dateRange = $monthFromName . ' - ' . $monthToName . $year;
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $dateRange = 'Year ' . $request->input('year');
            } else {
                $parts = [];
                if ($request->filled('month')) {
                    $monthName = \Carbon\Carbon::createFromDate(null, $request->input('month'), 1)->format('F');
                    $parts[] = $monthName;
                }
                if ($request->filled('year')) {
                    $parts[] = $request->input('year');
                }
                if ($request->filled('date_from')) {
                    $parts[] = 'From ' . \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y');
                }
                if ($request->filled('date_to')) {
                    $parts[] = 'To ' . \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
                }
                $dateRange = !empty($parts) ? implode(' ', $parts) : 'All Time';
            }
            
            // Add room filter to date range if present
            if ($request->filled('room_filter')) {
                $dateRange .= ' - Room: ' . $request->input('room_filter');
            }

            $pdf = \PDF::loadView('admin.analytics-location-pdf', compact(
                'locationStatsDetailed',
                'totalRepairs',
                'totalCost',
                'uniqueLocations',
                'uniqueCategories',
                'dateRange'
            ));

            return $pdf->stream('location-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            \Log::error('Request params: ' . json_encode($request->all()));
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Cost Report to PDF
    public function costReportPDF(Request $request)
    {
        try {
            $baseQuery = Report::whereNotNull('location')
                ->where('location', '!=', '')
                ->where('status', 'Resolved');
            $reportCountExpression = Report::supportsReportCount()
                ? 'SUM(COALESCE(report_count, 1))'
                : 'COUNT(*)';

            // Apply filters based on period selection
            $period = $request->input('period');
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $baseQuery->whereMonth('created_at', $request->input('month'))
                         ->whereYear('created_at', $request->input('year'));
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFrom = (int) $request->input('month_from');
                $monthTo = (int) $request->input('month_to');
                
                if ($request->filled('year')) {
                    $year = (int) $request->input('year');
                    
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereYear('created_at', $year)
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($year, $monthFrom, $monthTo) {
                            $q->where(function($q1) use ($year, $monthFrom) {
                                $q1->whereYear('created_at', $year)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                            })->orWhere(function($q2) use ($year, $monthTo) {
                                $q2->whereYear('created_at', $year + 1)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                            });
                        });
                    }
                } else {
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($monthFrom, $monthTo) {
                            $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                              ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    }
                }
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $baseQuery->whereYear('created_at', $request->input('year'));
            } else {
                if ($request->filled('month')) {
                    $baseQuery->whereMonth('created_at', $request->input('month'));
                }
                if ($request->filled('year')) {
                    $baseQuery->whereYear('created_at', $request->input('year'));
                }
            }

            // Get cost data by location
            $locationCosts = (clone $baseQuery)
                ->select('location')
                ->selectRaw($reportCountExpression . ' as count')
                ->selectRaw('SUM(COALESCE(cost, 0)) as cost')
                ->groupBy('location')
                ->orderByDesc('cost')
                ->get();

            $costData = [];
            $avgCost = $locationCosts->avg('cost') ?: 0;
            
            foreach ($locationCosts as $item) {
                $avgPerRepair = $item->count > 0 ? $item->cost / $item->count : 0;
                
                if ($item->cost > $avgCost * 1.5) {
                    $costLevel = 'Very High';
                    $badgeClass = 'danger';
                } elseif ($item->cost > $avgCost) {
                    $costLevel = 'High';
                    $badgeClass = 'warning';
                } elseif ($item->cost > $avgCost * 0.5) {
                    $costLevel = 'Medium';
                    $badgeClass = 'info';
                } else {
                    $costLevel = 'Low';
                    $badgeClass = 'success';
                }
                
                $costData[] = [
                    'location' => $item->location,
                    'count' => $item->count,
                    'cost' => $item->cost,
                    'avg_per_repair' => $avgPerRepair,
                    'cost_level' => $costLevel,
                    'badge_class' => $badgeClass
                ];
            }

            $highestCost = $costData[0] ?? ['location' => 'N/A', 'cost' => 0];
            $lowestCost = end($costData) ?: ['location' => 'N/A', 'cost' => 0];
            
            // Build date range string
            $dateRange = '';
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $monthName = \Carbon\Carbon::createFromDate($request->input('year'), $request->input('month'), 1)->format('F');
                $dateRange = $monthName . ' ' . $request->input('year');
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFromName = \Carbon\Carbon::createFromDate(null, $request->input('month_from'), 1)->format('F');
                $monthToName = \Carbon\Carbon::createFromDate(null, $request->input('month_to'), 1)->format('F');
                $year = $request->filled('year') ? ' ' . $request->input('year') : '';
                $dateRange = $monthFromName . ' - ' . $monthToName . $year;
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $dateRange = 'Year ' . $request->input('year');
            } else {
                $parts = [];
                if ($request->filled('month')) {
                    $monthName = \Carbon\Carbon::createFromDate(null, $request->input('month'), 1)->format('F');
                    $parts[] = $monthName;
                }
                if ($request->filled('year')) {
                    $parts[] = $request->input('year');
                }
                $dateRange = !empty($parts) ? implode(' ', $parts) : 'All Time';
            }

            $pdf = \PDF::loadView('admin.analytics-cost-pdf', compact(
                'costData',
                'highestCost',
                'lowestCost',
                'avgCost',
                'dateRange'
            ));

            return $pdf->stream('cost-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Cost PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Status Report to PDF
    public function statusReportPDF(Request $request)
    {
        try {
            $baseQuery = Report::query();
            $reportCountExpression = Report::supportsReportCount()
                ? 'SUM(COALESCE(report_count, 1))'
                : 'COUNT(*)';

            // Apply filters based on period selection
            $period = $request->input('period');
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $baseQuery->whereMonth('created_at', $request->input('month'))
                         ->whereYear('created_at', $request->input('year'));
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFrom = (int) $request->input('month_from');
                $monthTo = (int) $request->input('month_to');
                
                if ($request->filled('year')) {
                    $year = (int) $request->input('year');
                    
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereYear('created_at', $year)
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($year, $monthFrom, $monthTo) {
                            $q->where(function($q1) use ($year, $monthFrom) {
                                $q1->whereYear('created_at', $year)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                            })->orWhere(function($q2) use ($year, $monthTo) {
                                $q2->whereYear('created_at', $year + 1)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                            });
                        });
                    }
                } else {
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($monthFrom, $monthTo) {
                            $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                              ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    }
                }
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $baseQuery->whereYear('created_at', $request->input('year'));
            } else {
                if ($request->filled('month')) {
                    $baseQuery->whereMonth('created_at', $request->input('month'));
                }
                if ($request->filled('year')) {
                    $baseQuery->whereYear('created_at', $request->input('year'));
                }
            }

            // Get status distribution
            $statusCounts = (clone $baseQuery)
                ->select('status')
                ->selectRaw($reportCountExpression . ' as count')
                ->groupBy('status')
                ->orderByDesc('count')
                ->get();

            $totalCount = $statusCounts->sum('count');
            $statusData = [];
            
            foreach ($statusCounts as $item) {
                $percentage = $totalCount > 0 ? number_format(($item->count / $totalCount) * 100, 1) : 0;
                
                $badgeClass = 'secondary';
                if (in_array($item->status, ['Resolved', 'Completed'])) {
                    $badgeClass = 'success';
                } elseif (in_array($item->status, ['Pending', 'In Progress'])) {
                    $badgeClass = 'warning';
                } elseif (in_array($item->status, ['Rejected', 'Cancelled'])) {
                    $badgeClass = 'danger';
                }
                
                $statusData[] = [
                    'status' => $item->status,
                    'count' => $item->count,
                    'percentage' => $percentage,
                    'badge_class' => $badgeClass
                ];
            }
            
            // Build date range string
            $dateRange = '';
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $monthName = \Carbon\Carbon::createFromDate($request->input('year'), $request->input('month'), 1)->format('F');
                $dateRange = $monthName . ' ' . $request->input('year');
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFromName = \Carbon\Carbon::createFromDate(null, $request->input('month_from'), 1)->format('F');
                $monthToName = \Carbon\Carbon::createFromDate(null, $request->input('month_to'), 1)->format('F');
                $year = $request->filled('year') ? ' ' . $request->input('year') : '';
                $dateRange = $monthFromName . ' - ' . $monthToName . $year;
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $dateRange = 'Year ' . $request->input('year');
            } else {
                $parts = [];
                if ($request->filled('month')) {
                    $monthName = \Carbon\Carbon::createFromDate(null, $request->input('month'), 1)->format('F');
                    $parts[] = $monthName;
                }
                if ($request->filled('year')) {
                    $parts[] = $request->input('year');
                }
                $dateRange = !empty($parts) ? implode(' ', $parts) : 'All Time';
            }

            $pdf = \PDF::loadView('admin.analytics-status-pdf', compact(
                'statusData',
                'totalCount',
                'dateRange'
            ));

            return $pdf->stream('status-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Status PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Status Distribution & Response Time to PDF
    public function statusDistributionPDF(Request $request)
    {
        try {
            // Apply date filters
            $query = Report::query();
            
            // Apply room filter if provided
            if ($request->filled('room_filter')) {
                $query->where('location', $request->input('room_filter'));
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }
            
            // Get status distribution with ticket details
            $statusData = [];
            $statuses = ['Pending', 'Assigned', 'In Progress', 'Resolved'];
            $totalTickets = 0;
            
            foreach ($statuses as $status) {
                $reports = (clone $query)->where('status', $status)
                    ->select('id', 'title')
                    ->when(Report::supportsReportCount(), fn ($q) => $q->addSelect('report_count'))
                    ->get();
                
                if ($reports->count() > 0) {
                    $reportTotal = Report::supportsReportCount() ? $reports->sum('report_count') : $reports->count();
                    $tickets = [];
                    foreach ($reports as $report) {
                        $ticketNum = '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT);
                        $title = $report->title ? substr($report->title, 0, 50) : 'N/A';
                        if (strlen($report->title ?? '') > 50) {
                            $title .= '...';
                        }
                        $tickets[] = $ticketNum . ' - ' . $title;
                    }
                    
                    $statusData[] = [
                        'status' => $status,
                        'count' => $reportTotal,
                        'tickets' => $tickets
                    ];
                    
                    $totalTickets += $reportTotal;
                }
            }
            
            // Get response time data
            $responseTimeStats = (clone $query)
                ->whereNotNull('assigned_at')
                ->whereNotNull('resolved_at')
                ->get()
                ->map(function($report) {
                    $submittedToAssigned = $report->created_at->diffInSeconds($report->assigned_at);
                    $assignedToResolved = $report->assigned_at->diffInSeconds($report->resolved_at);
                    $totalTime = $report->created_at->diffInSeconds($report->resolved_at);
                    
                    $formatTime = function($seconds) {
                        if ($seconds < 0) return '00:00:00';
                        $hours = floor($seconds / 3600);
                        $minutes = floor(($seconds % 3600) / 60);
                        $secs = $seconds % 60;
                        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
                    };
                    
                    return [
                        'id' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                        'title' => substr($report->title ?? 'N/A', 0, 30),
                        'location' => $report->location,
                        'submitted_to_assigned' => $formatTime($submittedToAssigned),
                        'assigned_to_resolved' => $formatTime($assignedToResolved),
                        'total_time' => $formatTime($totalTime),
                        'staff' => optional($report->assignedTo)->name ?? 'N/A',
                    ];
                })
                ->filter(function($item) {
                    // Filter out invalid times
                    return $item['submitted_to_assigned'] !== '00:00:00' && 
                           $item['assigned_to_resolved'] !== '00:00:00';
                })
                ->values();
            
            // Calculate average response times
            $avgSubmittedToAssigned = '00:00:00';
            $avgAssignedToResolved = '00:00:00';
            $avgTotalTime = '00:00:00';
            
            if ($responseTimeStats->count() > 0) {
                $totalSubmittedToAssigned = 0;
                $totalAssignedToResolved = 0;
                $totalTimeSum = 0;
                
                foreach ($responseTimeStats as $stat) {
                    list($h, $m, $s) = explode(':', $stat['submitted_to_assigned']);
                    $totalSubmittedToAssigned += ($h * 3600) + ($m * 60) + $s;
                    
                    list($h, $m, $s) = explode(':', $stat['assigned_to_resolved']);
                    $totalAssignedToResolved += ($h * 3600) + ($m * 60) + $s;
                    
                    list($h, $m, $s) = explode(':', $stat['total_time']);
                    $totalTimeSum += ($h * 3600) + ($m * 60) + $s;
                }
                
                $count = $responseTimeStats->count();
                $avgSubmittedToAssigned = sprintf('%02d:%02d:%02d', 
                    floor($totalSubmittedToAssigned / $count / 3600),
                    floor(($totalSubmittedToAssigned / $count % 3600) / 60),
                    $totalSubmittedToAssigned / $count % 60
                );
                $avgAssignedToResolved = sprintf('%02d:%02d:%02d',
                    floor($totalAssignedToResolved / $count / 3600),
                    floor(($totalAssignedToResolved / $count % 3600) / 60),
                    $totalAssignedToResolved / $count % 60
                );
                $avgTotalTime = sprintf('%02d:%02d:%02d',
                    floor($totalTimeSum / $count / 3600),
                    floor(($totalTimeSum / $count % 3600) / 60),
                    $totalTimeSum / $count % 60
                );
            }
            
            $dateRange = '';
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateRange = \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y') . ' - ' . 
                            \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
            } else {
                $dateRange = 'All Time';
            }
            
            // Add room filter to date range if present
            if ($request->filled('room_filter')) {
                $dateRange .= ' - Room: ' . $request->input('room_filter');
            }
            
            $pdf = \PDF::loadView('admin.analytics-status-distribution-pdf', compact(
                'statusData',
                'responseTimeStats',
                'totalTickets',
                'avgSubmittedToAssigned',
                'avgAssignedToResolved',
                'avgTotalTime',
                'dateRange'
            ));
            
            return $pdf->stream('status-distribution-report-' . now()->format('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            \Log::error('Status Distribution PDF Error: ' . $e->getMessage());
            \Log::error('Request params: ' . json_encode($request->all()));
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Trend Report to PDF
    public function trendReportPDF(Request $request)
    {
        try {
            $baseQuery = Report::query();
            $reportCountExpression = Report::supportsReportCount()
                ? 'SUM(COALESCE(reports.report_count, 1))'
                : 'COUNT(*)';

            // Apply filters based on period selection
            $period = $request->input('period');
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $baseQuery->whereMonth('created_at', $request->input('month'))
                         ->whereYear('created_at', $request->input('year'));
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFrom = (int) $request->input('month_from');
                $monthTo = (int) $request->input('month_to');
                
                if ($request->filled('year')) {
                    $year = (int) $request->input('year');
                    
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereYear('created_at', $year)
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($year, $monthFrom, $monthTo) {
                            $q->where(function($q1) use ($year, $monthFrom) {
                                $q1->whereYear('created_at', $year)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom]);
                            })->orWhere(function($q2) use ($year, $monthTo) {
                                $q2->whereYear('created_at', $year + 1)
                                   ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                            });
                        });
                    }
                } else {
                    if ($monthFrom <= $monthTo) {
                        $baseQuery->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                                 ->whereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                    } else {
                        $baseQuery->where(function($q) use ($monthFrom, $monthTo) {
                            $q->whereRaw('EXTRACT(MONTH FROM created_at) >= ?', [$monthFrom])
                              ->orWhereRaw('EXTRACT(MONTH FROM created_at) <= ?', [$monthTo]);
                        });
                    }
                }
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $baseQuery->whereYear('created_at', $request->input('year'));
            } else {
                // Default to last 6 months if no specific filters
                if (!$request->filled('month') && !$request->filled('year') && !$request->filled('date_from') && !$request->filled('date_to')) {
                    $baseQuery->where('created_at', '>=', now()->subMonths(6));
                }
                if ($request->filled('month')) {
                    $baseQuery->whereMonth('created_at', $request->input('month'));
                }
                if ($request->filled('year')) {
                    $baseQuery->whereYear('created_at', $request->input('year'));
                }
                if ($request->filled('date_from')) {
                    $baseQuery->whereDate('created_at', '>=', $request->input('date_from'));
                }
                if ($request->filled('date_to')) {
                    $baseQuery->whereDate('created_at', '<=', $request->input('date_to'));
                }
            }

            // Get monthly trend data with status breakdown
            $monthlyData = (clone $baseQuery)
                ->selectRaw("TO_CHAR(reports.created_at, 'YYYY-MM') as month")
                ->selectRaw("COALESCE(NULLIF(reports.title, ''), LEFT(reports.description, 50)) as title")
                ->selectRaw('reports.status')
                ->selectRaw($reportCountExpression . ' as count')
                ->selectRaw("STRING_AGG(CAST(reports.id AS TEXT), ',' ORDER BY reports.id) as ticket_ids")
                ->selectRaw("STRING_AGG(COALESCE(NULLIF(reports.damaged_part, ''), 'N/A'), '|' ORDER BY reports.id) as damaged_parts")
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->groupByRaw("month, COALESCE(NULLIF(reports.title, ''), LEFT(reports.description, 50)), reports.status")
                ->orderBy('month')
                ->get();

            // Build 6-month labels
            $monthLabels = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key = $date->format('Y-m');
                $label = $date->format('M Y');
                $monthLabels[$key] = ['label' => $label, 'issues' => []];
            }

            // Group data by month with status breakdown
            foreach ($monthlyData as $item) {
                if (isset($monthLabels[$item->month])) {
                    if (!isset($monthLabels[$item->month]['issues'][$item->title])) {
                        $monthLabels[$item->month]['issues'][$item->title] = [
                            'total' => 0,
                            'Pending' => ['count' => 0, 'tickets' => []],
                            'Assigned' => ['count' => 0, 'tickets' => []],
                            'In Progress' => ['count' => 0, 'tickets' => []],
                            'Resolved' => ['count' => 0, 'tickets' => []]
                        ];
                    }
                    
                    $count = (int) $item->count;
                    $ticketIds = $item->ticket_ids ? explode(',', $item->ticket_ids) : [];
                    $damagedParts = $item->damaged_parts ? explode('|', $item->damaged_parts) : [];
                    
                    $monthLabels[$item->month]['issues'][$item->title]['total'] += $count;
                    $monthLabels[$item->month]['issues'][$item->title][$item->status]['count'] += $count;
                    
                    // Store ticket info
                    foreach ($ticketIds as $idx => $ticketId) {
                        $monthLabels[$item->month]['issues'][$item->title][$item->status]['tickets'][] = [
                            'id' => $ticketId,
                            'damaged_part' => $damagedParts[$idx] ?? 'N/A'
                        ];
                    }
                }
            }

            // Calculate statistics
            $peakMonth = null;
            $peakCount = 0;
            $lowestMonth = null;
            $lowestCount = PHP_INT_MAX;
            $totalCount = 0;

            foreach ($monthLabels as $key => $data) {
                $monthTotal = 0;
                foreach ($data['issues'] as $issue => $statusData) {
                    $monthTotal += $statusData['total'];
                }
                $totalCount += $monthTotal;
                
                if ($monthTotal > $peakCount) {
                    $peakCount = $monthTotal;
                    $peakMonth = $data['label'];
                }
                if ($monthTotal < $lowestCount) {
                    $lowestCount = $monthTotal;
                    $lowestMonth = $data['label'];
                }
            }

            $avgPerMonth = $totalCount / 6;
            if ($lowestCount === PHP_INT_MAX) $lowestCount = 0;

            // Build table data
            $trendData = [];
            foreach ($monthLabels as $key => $data) {
                if (empty($data['issues'])) {
                    $trendData[] = [
                        'is_first_row' => true,
                        'rowspan' => 1,
                        'month_label' => $data['label'],
                        'issue_type' => null,
                        'total' => 0,
                        'resolved' => [],
                        'in_progress' => [],
                        'pending' => []
                    ];
                } else {
                    $isFirst = true;
                    $rowspan = count($data['issues']);
                    foreach ($data['issues'] as $issue => $statusData) {
                        $trendData[] = [
                            'is_first_row' => $isFirst,
                            'rowspan' => $rowspan,
                            'month_label' => $data['label'],
                            'issue_type' => $issue,
                            'total' => $statusData['total'],
                            'resolved' => $statusData['Resolved']['tickets'] ?? [],
                            'in_progress' => $statusData['In Progress']['tickets'] ?? [],
                            'pending' => $statusData['Pending']['tickets'] ?? []
                        ];
                        $isFirst = false;
                    }
                }
            }
            
            // Build date range string
            $dateRange = '';
            
            if ($period === 'monthly' && $request->filled('month') && $request->filled('year')) {
                $monthName = \Carbon\Carbon::createFromDate($request->input('year'), $request->input('month'), 1)->format('F');
                $dateRange = $monthName . ' ' . $request->input('year');
            } elseif ($period === 'quarterly' && $request->filled('month_from') && $request->filled('month_to')) {
                $monthFromName = \Carbon\Carbon::createFromDate(null, $request->input('month_from'), 1)->format('F');
                $monthToName = \Carbon\Carbon::createFromDate(null, $request->input('month_to'), 1)->format('F');
                $year = $request->filled('year') ? ' ' . $request->input('year') : '';
                $dateRange = $monthFromName . ' - ' . $monthToName . $year;
            } elseif ($period === 'yearly' && $request->filled('year')) {
                $dateRange = 'Year ' . $request->input('year');
            } else {
                $parts = [];
                if ($request->filled('month')) {
                    $monthName = \Carbon\Carbon::createFromDate(null, $request->input('month'), 1)->format('F');
                    $parts[] = $monthName;
                }
                if ($request->filled('year')) {
                    $parts[] = $request->input('year');
                }
                $dateRange = !empty($parts) ? implode(' ', $parts) : 'Last 6 Months';
            }

            $pdf = \PDF::loadView('admin.analytics-trend-pdf', compact(
                'trendData',
                'peakMonth',
                'peakCount',
                'lowestMonth',
                'lowestCount',
                'avgPerMonth',
                'dateRange'
            ));

            return $pdf->stream('trend-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Trend PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Period Comparison Report to PDF
    public function periodComparisonPDF(Request $request)
    {
        try {
            $baseQuery = Report::where('status', 'Resolved');
            $reportCountExpression = Report::supportsReportCount()
                ? 'SUM(COALESCE(reports.report_count, 1))'
                : 'COUNT(*)';

            // Apply date_from and date_to filters if provided
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $baseQuery->whereBetween('created_at', [
                    $request->input('date_from'),
                    $request->input('date_to')
                ]);
            }

            // Get monthly cost data
            $monthlyCostData = (clone $baseQuery)
                ->selectRaw("TO_CHAR(reports.created_at, 'YYYY-MM') as month")
                ->selectRaw($reportCountExpression . ' as count')
                ->selectRaw('COUNT(CASE WHEN reports.cost > 0 THEN 1 END) as fixed_count')
                ->selectRaw('SUM(COALESCE(reports.cost, 0)) as total_cost')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Build 6-month labels (or use filtered range)
            $monthLabels = [];
            $monthKeys = [];
            
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $startDate = \Carbon\Carbon::parse($request->input('date_from'));
                $endDate = \Carbon\Carbon::parse($request->input('date_to'));
                $monthsDiff = $startDate->diffInMonths($endDate);
                
                for ($i = 0; $i <= min($monthsDiff, 11); $i++) {
                    $date = $startDate->copy()->addMonths($i);
                    $key = $date->format('Y-m');
                    $label = $date->format('M Y');
                    $monthKeys[] = $key;
                    $monthLabels[] = $label;
                }
            } else {
                // Default to last 6 months
                for ($i = 5; $i >= 0; $i--) {
                    $date = now()->subMonths($i);
                    $key = $date->format('Y-m');
                    $label = $date->format('M Y');
                    $monthKeys[] = $key;
                    $monthLabels[] = $label;
                }
            }

            // Initialize data arrays
            $monthCosts = array_fill(0, count($monthKeys), 0);
            $monthCounts = array_fill(0, count($monthKeys), 0);
            $monthFixedCounts = array_fill(0, count($monthKeys), 0);

            // Populate data from query results
            foreach ($monthlyCostData as $item) {
                $monthIndex = array_search($item->month, $monthKeys);
                if ($monthIndex !== false) {
                    $monthCosts[$monthIndex] = (float) $item->total_cost;
                    $monthCounts[$monthIndex] = (int) $item->count;
                    $monthFixedCounts[$monthIndex] = (int) $item->fixed_count;
                }
            }

            // Calculate statistics
            $totalCost = array_sum($monthCosts);
            $totalCount = array_sum($monthCounts);
            $totalFixedCount = array_sum($monthFixedCounts);
            $avgCostPerMonth = count($monthKeys) > 0 ? $totalCost / count($monthKeys) : 0;
            $avgCostPerRepair = $totalFixedCount > 0 ? $totalCost / $totalFixedCount : 0;

            // Find highest and lowest months
            $highestIdx = 0;
            $lowestIdx = 0;
            for ($i = 1; $i < count($monthCosts); $i++) {
                if ($monthCosts[$i] > $monthCosts[$highestIdx]) $highestIdx = $i;
                if ($monthCosts[$i] < $monthCosts[$lowestIdx]) $lowestIdx = $i;
            }

            // Build table data with repair breakdown
            $periodData = [];
            foreach ($monthLabels as $idx => $label) {
                $cost = $monthCosts[$idx];
                $count = $monthCounts[$idx];
                $avgPerRepair = $count > 0 ? $cost / $count : 0;
                $percentOfTotal = $totalCost > 0 ? ($cost / $totalCost) * 100 : 0;

                $trend = '';
                if ($idx > 0) {
                    $prevCost = $monthCosts[$idx - 1];
                    if ($cost > $prevCost) {
                        $trend = 'up';
                    } elseif ($cost < $prevCost) {
                        $trend = 'down';
                    } else {
                        $trend = 'neutral';
                    }
                }

                // Get repair breakdown for this period
                $periodKey = $monthKeys[$idx];
                $repairs = Report::whereRaw("TO_CHAR(created_at, 'YYYY-MM') = ?", [$periodKey])
                    ->when($request->filled('date_from') && $request->filled('date_to'), function($q) use ($request) {
                        $q->whereBetween('created_at', [
                            $request->input('date_from'),
                            $request->input('date_to')
                        ]);
                    })
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($repair) {
                        return [
                            'ticket_number' => '#' . str_pad($repair->id, 4, '0', STR_PAD_LEFT),
                            'date' => $repair->created_at->format('M d, Y'),
                            'title' => $repair->title ?: 'N/A',
                            'location' => $repair->location ?: 'N/A',
                            'status' => $repair->status,
                            'damaged_part' => $repair->damaged_part ?: 'N/A',
                            'cost' => $repair->cost ?? 0
                        ];
                    });

                $periodData[] = [
                    'label' => $label,
                    'count' => $count,
                    'cost' => $cost,
                    'avg_per_repair' => $avgPerRepair,
                    'percent' => $percentOfTotal,
                    'trend' => $trend,
                    'repairs' => $repairs
                ];
            }

            // Determine date range label
            $dateRange = '';
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateRange = \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y') . 
                           ' - ' . 
                           \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
            } else {
                $dateRange = 'Last 6 Months';
            }

            $pdf = \PDF::loadView('admin.analytics-period-comparison-pdf', compact(
                'periodData',
                'totalCost',
                'totalCount',
                'avgCostPerMonth',
                'avgCostPerRepair',
                'highestIdx',
                'lowestIdx',
                'monthLabels',
                'monthCosts',
                'dateRange'
            ));

            return $pdf->stream('period-comparison-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Period Comparison PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function employeePerformancePDF(Request $request)
    {
        try {
            $employeePerformanceStats = $this->buildEmployeePerformanceStats($request);
            $dateRange = 'All Time';

            if ($request->filled('date_from') || $request->filled('date_to')) {
                $from = $request->filled('date_from')
                    ? \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y')
                    : 'Start';
                $to = $request->filled('date_to')
                    ? \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y')
                    : 'Today';
                $dateRange = $from . ' - ' . $to;
            }

            $pdf = \PDF::loadView('admin.analytics-employee-performance-pdf', compact(
                'employeePerformanceStats',
                'dateRange'
            ));

            return $pdf->stream('employee-performance-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Employee Performance PDF Generation Error: ' . $e->getMessage());

            return back()->with('error', 'Failed to generate employee performance PDF: ' . $e->getMessage());
        }
    }

    // Export Combined Location Cost Report to PDF
    public function combinedLocationPDF(Request $request)
    {
        try {
            // Get all reports grouped by location with ticket details
            $query = Report::whereNotNull('location')
                ->where('location', '!=', '')
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'),   fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->orderBy('location')
                ->get();

            // Group by location and issue type (damaged_part)
            $combinedLocationStats = collect();
            
            foreach ($query->groupBy('location') as $location => $locationReports) {
                // Further group by issue type within this location
                $issueGroups = $locationReports->groupBy(function($report) {
                    return $report->title ?: ($report->damaged_part ?: 'N/A');
                });
                
                foreach ($issueGroups as $issueType => $issueReports) {
                    $tickets = $issueReports->map(function ($report) {
                        return [
                            'ticket_number' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                            'damaged_part' => $report->damaged_part ?: 'N/A',
                            'cost' => $report->cost ?? 0,
                            'date_fixed' => $report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->format('M d, Y') : 'Not Fixed',
                        ];
                    });

                    $totalCost = $issueReports->sum('cost');
                    $totalCount = $issueReports->count();
                    // Count only fixed tickets (with cost > 0) for average calculation
                    $fixedCount = $issueReports->where('cost', '>', 0)->count();

                    $combinedLocationStats->push([
                        'location' => $location,
                        'issue_type' => $issueType,
                        'total_count' => $totalCount,
                        'total_cost' => $totalCost,
                        'avg_cost' => $fixedCount > 0 ? ($totalCost / $fixedCount) : 0,
                        'tickets' => $tickets,
                    ]);
                }
            }
            
            // Sort by location and total cost
            $combinedLocationStats = $combinedLocationStats->sortBy([
                ['location', 'asc'],
                ['total_cost', 'desc']
            ])->values();

            // Calculate totals
            $totalTickets = $combinedLocationStats->sum('total_count');
            $totalCost = $combinedLocationStats->sum('total_cost');
            // Calculate total fixed tickets across all locations for overall average
            $totalFixedTickets = $query->where('cost', '>', 0)->count();
            $avgCostPerTicket = $totalFixedTickets > 0 ? ($totalCost / $totalFixedTickets) : 0;

            // Date range for display
            $dateRange = 'All Time';
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateRange = \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y') . ' - ' . 
                            \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
            }

            $pdf = \PDF::loadView('admin.combined-location-pdf', compact(
                'combinedLocationStats',
                'totalTickets',
                'totalCost',
                'avgCostPerTicket',
                'dateRange'
            ));

            return $pdf->stream('combined-location-report-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Combined Location PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Single Location Detail Report to PDF
    public function locationDetailPDF(Request $request)
    {
        try {
            $location = $request->input('location');
            
            if (!$location) {
                return back()->with('error', 'Location parameter is required');
            }

            // Get tickets for this specific location
            $query = Report::where('location', $location)
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->orderBy('created_at', 'desc')
                ->get();

            $tickets = $query->map(function ($report) {
                return [
                    'ticket_number' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                    'damaged_part' => $report->damaged_part ?: 'N/A',
                    'issue' => $report->title ?: 'N/A',
                    'status' => $report->status,
                    'cost' => $report->cost ?? 0,
                    'date_fixed' => $report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->format('M d, Y h:i A') : 'Not Fixed',
                ];
            });

            // Calculate statistics
            $totalTickets = $tickets->count();
            $totalCost = $tickets->sum('cost');
            // Count only fixed tickets (with cost > 0) for average calculation
            $fixedTickets = $tickets->where('cost', '>', 0)->count();
            $avgCostPerTicket = $fixedTickets > 0 ? ($totalCost / $fixedTickets) : 0;

            // Date range for display
            $dateRange = 'All Time';
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateRange = \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y') . ' - ' . 
                            \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
            }

            $pdf = \PDF::loadView('admin.location-detail-pdf', compact(
                'location',
                'tickets',
                'totalTickets',
                'totalCost',
                'avgCostPerTicket',
                'dateRange'
            ));

            $filename = 'location-' . str_replace(' ', '-', strtolower($location)) . '-' . now()->format('Y-m-d') . '.pdf';
            return $pdf->stream($filename);
        } catch (\Exception $e) {
            \Log::error('Location Detail PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Complete Analytics to PDF
    public function exportAnalyticsPDF(Request $request)
    {
        try {
            // Base query for reports
            $baseQuery = Report::whereNotNull('location')
                ->where('location', '!=', '');

            // Apply room filter if provided
            if ($request->filled('room_filter')) {
                $baseQuery->where('location', $request->input('room_filter'));
            }

            // Apply date filters
            if ($request->filled('date_from')) {
                $baseQuery->whereDate('created_at', '>=', $request->input('date_from'));
            }
            if ($request->filled('date_to')) {
                $baseQuery->whereDate('created_at', '<=', $request->input('date_to'));
            }

            // Summary stats
            $totalConcerns = (clone $baseQuery)->count();
            $totalCost = (clone $baseQuery)->sum('cost') ?? 0;
            // Count only fixed concerns (with cost > 0) for average calculation
            $fixedConcerns = (clone $baseQuery)->where('cost', '>', 0)->count();
            $avgCost = $fixedConcerns > 0 ? $totalCost / $fixedConcerns : 0;
            $uniqueLocations = (clone $baseQuery)->distinct('location')->count('location');

            // 1. Combined Cost by Location and Issue Type
            $allReports = (clone $baseQuery)->orderBy('location')->get();
            $combinedLocationStats = collect();
            
            foreach ($allReports->groupBy('location') as $location => $locationReports) {
                // Further group by issue type within this location
                $issueGroups = $locationReports->groupBy(function($report) {
                    return $report->title ?: ($report->damaged_part ?: 'N/A');
                });
                
                foreach ($issueGroups as $issueType => $issueReports) {
                    $tickets = $issueReports->map(function ($report) {
                        return [
                            'ticket_number' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                            'damaged_part' => $report->damaged_part ?: 'N/A',
                            'cost' => $report->cost ?? 0,
                            'date_fixed' => $report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->format('M d, Y') : 'Not Fixed',
                        ];
                    });

                    $totalCost = $issueReports->sum('cost');
                    $totalCount = $issueReports->count();
                    // Count only fixed tickets (with cost > 0) for average calculation
                    $fixedCount = $issueReports->where('cost', '>', 0)->count();

                    $combinedLocationStats->push([
                        'location' => $location,
                        'issue_type' => $issueType,
                        'total_count' => $totalCount,
                        'total_cost' => $totalCost,
                        'avg_cost' => $fixedCount > 0 ? ($totalCost / $fixedCount) : 0,
                        'tickets' => $tickets,
                    ]);
                }
            }
            
            // Sort by location and total cost
            $combinedLocationStats = $combinedLocationStats->sortBy([
                ['location', 'asc'],
                ['total_cost', 'desc']
            ])->values();

            // 2. Cost by Category Analysis
            $costByCategory = Report::with('category')
                ->whereNotNull('category_id')
                ->where('is_deleted', false)
                ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->get()
                ->groupBy('category.name')
                ->map(function($group, $categoryName) {
                    return [
                        'category' => $categoryName ?: 'Uncategorized',
                        'count' => $group->count(),
                        'total_cost' => $group->sum('cost') ?? 0,
                        'avg_cost' => $group->avg('cost') ?? 0,
                        'percentage' => 0,
                    ];
                })
                ->sortByDesc('total_cost')
                ->values();

            $totalCategoryCost = $costByCategory->sum('total_cost');
            $costByCategory = $costByCategory->map(function($item) use ($totalCategoryCost) {
                $item['percentage'] = $totalCategoryCost > 0 ? ($item['total_cost'] / $totalCategoryCost) * 100 : 0;
                return $item;
            });

            // 3. Status distribution
            $statusStats = (clone $baseQuery)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get();

            // 4. Response Time Analysis
            $responseTimeQuery = Report::whereNotNull('assigned_at')
                ->whereNotNull('resolved_at')
                ->where('is_deleted', false)
                ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->get();

            $responseTimeDetails = $responseTimeQuery->map(function($report) {
                $submittedToAssignedSeconds = $report->created_at->diffInSeconds($report->assigned_at, false);
                $assignedToResolvedSeconds = $report->assigned_at->diffInSeconds($report->resolved_at, false);
                $totalTimeSeconds = $report->created_at->diffInSeconds($report->resolved_at, false);
                
                $formatTime = function($seconds) {
                    if ($seconds < 0) return '0m';
                    
                    $days = floor($seconds / 86400); // 86400 seconds in a day
                    $hours = floor(($seconds % 86400) / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    
                    if ($days > 0) {
                        return $days . 'd ' . $hours . 'h';
                    } elseif ($hours > 0) {
                        return $hours . 'h ' . $minutes . 'm';
                    } elseif ($minutes > 0) {
                        return $minutes . 'm';
                    } else {
                        return '< 1m';
                    }
                };
                
                return [
                    'ticket_number' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                    'location' => $report->location,
                    'created_at' => $report->created_at->format('Y-m-d h:i A'),
                    'assigned_at' => $report->assigned_at->format('Y-m-d h:i A'),
                    'resolved_at' => $report->resolved_at->format('Y-m-d h:i A'),
                    'submitted_to_assigned' => $submittedToAssignedSeconds,
                    'assigned_to_resolved' => $assignedToResolvedSeconds,
                    'total_time' => $totalTimeSeconds,
                    'submitted_to_assigned_formatted' => $formatTime($submittedToAssignedSeconds),
                    'assigned_to_resolved_formatted' => $formatTime($assignedToResolvedSeconds),
                    'total_time_formatted' => $formatTime($totalTimeSeconds),
                    'assigned_to_name' => optional($report->assignedTo)->name ?? 'N/A',
                ];
            })
            ->filter(function($item) {
                return $item['submitted_to_assigned'] >= 0 
                    && $item['assigned_to_resolved'] >= 0 
                    && $item['total_time'] >= 0;
            })
            ->values();

            $avgSubmittedToAssigned = $responseTimeDetails->avg('submitted_to_assigned') / 3600 ?? 0;
            $avgAssignedToResolved = $responseTimeDetails->avg('assigned_to_resolved') / 3600 ?? 0;
            $avgTotalTime = $responseTimeDetails->avg('total_time') / 3600 ?? 0;

            // 5. Period Comparison (Yearly Breakdown)
            $yearlyStats = Report::selectRaw('
                    EXTRACT(YEAR FROM created_at)::integer as year,
                    COUNT(*) as count,
                    COUNT(CASE WHEN cost > 0 THEN 1 END) as fixed_count,
                    SUM(cost) as total_cost
                ')
                ->whereNotNull('location')
                ->where('location', '!=', '')
                ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->groupBy('year')
                ->orderBy('year')
                ->get();

            $periodData = $yearlyStats->map(function($stat, $index) use ($yearlyStats, $request) {
                $avgPerRepair = $stat->fixed_count > 0 ? $stat->total_cost / $stat->fixed_count : 0;
                
                // Determine trend
                $trend = 'neutral';
                if ($index > 0) {
                    $prevCost = $yearlyStats[$index - 1]->total_cost;
                    if ($stat->total_cost > $prevCost) {
                        $trend = 'up';
                    } elseif ($stat->total_cost < $prevCost) {
                        $trend = 'down';
                    }
                }
                
                // Get repair breakdown for this year
                $repairs = Report::whereRaw('EXTRACT(YEAR FROM created_at)::integer = ?', [$stat->year])
                    ->whereNotNull('location')
                    ->where('location', '!=', '')
                    ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                    ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                    ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function($repair) {
                        return [
                            'ticket_number' => '#' . str_pad($repair->id, 4, '0', STR_PAD_LEFT),
                            'date' => $repair->created_at->format('M d, Y'),
                            'title' => $repair->title ?: 'N/A',
                            'location' => $repair->location ?: 'N/A',
                            'status' => $repair->status,
                            'damaged_part' => $repair->damaged_part ?: 'N/A',
                            'cost' => $repair->cost ?? 0
                        ];
                    });
                
                return [
                    'label' => 'Year ' . $stat->year,
                    'count' => $stat->count,
                    'cost' => $stat->total_cost ?? 0,
                    'avg_per_repair' => $avgPerRepair,
                    'trend' => $trend,
                    'repairs' => $repairs
                ];
            });

            $highestIdx = $periodData->isEmpty() ? 0 : $periodData->search(fn($item) => $item['cost'] == $periodData->max('cost'));
            $lowestIdx = $periodData->isEmpty() ? 0 : $periodData->search(fn($item) => $item['cost'] == $periodData->min('cost'));
            $avgCostPerYear = $periodData->isEmpty() ? 0 : $periodData->avg('cost');
            $totalFixedYearly = $yearlyStats->sum('fixed_count');
            $avgCostPerRepair = $totalFixedYearly > 0 ? $periodData->sum('cost') / $totalFixedYearly : 0;

            // Build date range string
            $dateRange = 'All Time';
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateRange = \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y') . ' - ' . 
                            \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
            }
            
            // Add room filter to date range if present
            if ($request->filled('room_filter')) {
                $dateRange .= ' | Room: ' . $request->input('room_filter');
            }

            // 6. Trend Alerts with Damaged Parts Breakdown
            $trendAlertsData = collect();
            $locationIssues = Report::whereNotNull('location')
                ->where('location', '!=', '')
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->when($request->filled('room_filter'), fn($q) => $q->where('location', $request->input('room_filter')))
                ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                ->select('location', 'title')
                ->distinct()
                ->get();

            foreach ($locationIssues as $li) {
                $loc = $li->location;
                $issue = $li->title;
                
                // Get recent count for severity
                $recent = Report::where('location', $loc)
                    ->where('title', $issue)
                    ->where('created_at', '>=', now()->subMonths(3))
                    ->count();
                
                if ($recent < 1) continue;
                
                // Determine severity
                $severity = $recent >= 3 ? 'critical' : ($recent >= 2 ? 'warning' : 'info');
                $alertTitle = $severity === 'critical' ? 'High Frequency Issue' : ($severity === 'warning' ? 'Recurring Issue' : 'Issue Detected');
                
                // Get all resolved reports for this location and issue
                $reports = Report::where('location', $loc)
                    ->where('title', $issue)
                    ->where('status', 'Resolved')
                    ->whereNotNull('resolved_at')
                    ->when($request->filled('date_from'), fn($q) => $q->whereDate('created_at', '>=', $request->input('date_from')))
                    ->when($request->filled('date_to'), fn($q) => $q->whereDate('created_at', '<=', $request->input('date_to')))
                    ->orderBy('resolved_at', 'desc')
                    ->get();
                
                // Group by damaged_part
                $partBreakdown = [];
                foreach ($reports as $report) {
                    $part = $report->damaged_part ?: 'Not Specified';
                    
                    if (!isset($partBreakdown[$part])) {
                        $partBreakdown[$part] = [
                            'part_name' => $part,
                            'count' => 0,
                            'total_cost' => 0,
                            'tickets' => []
                        ];
                    }
                    
                    $partBreakdown[$part]['count']++;
                    $partBreakdown[$part]['total_cost'] += $report->cost ?? 0;
                    $partBreakdown[$part]['tickets'][] = [
                        'ticket_number' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                        'cost' => $report->cost ?? 0,
                        'date_fixed' => $report->resolved_at ? $report->resolved_at->format('M d, Y h:i A') : 'N/A'
                    ];
                }
                
                // Sort by count descending
                usort($partBreakdown, function($a, $b) {
                    return $b['count'] - $a['count'];
                });
                
                $totalRepairs = $reports->count();
                $totalCost = $reports->sum('cost');
                $fixedRepairs = $reports->where('cost', '>', 0)->count();
                
                $trendAlertsData->push([
                    'location' => $loc,
                    'issue' => $issue,
                    'severity' => $severity,
                    'alert_title' => $alertTitle,
                    'total_repairs' => $totalRepairs,
                    'total_cost' => $totalCost,
                    'avg_cost_per_repair' => $fixedRepairs > 0 ? $totalCost / $fixedRepairs : 0,
                    'part_breakdown' => $partBreakdown,
                    'recent_count' => $recent
                ]);
            }
            
            // Sort by recent count descending and take top 10
            $trendAlertsData = $trendAlertsData->sortByDesc('recent_count')->take(10)->values();
            $employeePerformanceStats = $this->buildEmployeePerformanceStats($request);

            $pdf = \PDF::loadView('admin.analytics-comprehensive-pdf', compact(
                'totalConcerns',
                'totalCost',
                'avgCost',
                'uniqueLocations',
                'combinedLocationStats',
                'costByCategory',
                'periodData',
                'highestIdx',
                'lowestIdx',
                'avgCostPerYear',
                'avgCostPerRepair',
                'statusStats',
                'avgSubmittedToAssigned',
                'avgAssignedToResolved',
                'avgTotalTime',
                'responseTimeDetails',
                'dateRange',
                'trendAlertsData',
                'employeePerformanceStats'
            ));

            return $pdf->stream('comprehensive-analytics-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Comprehensive Analytics PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    // Export Alert Detail to PDF
    public function alertDetailPDF(Request $request)
    {
        try {
            $location = $request->input('location');
            $issue = $request->input('issue');
            
            if (!$location || !$issue) {
                return back()->with('error', 'Missing location or issue parameter');
            }
            
            // Get all resolved reports for this location and issue
            $reports = Report::where('location', $location)
                ->where('title', $issue)
                ->where('status', 'Resolved')
                ->whereNotNull('resolved_at')
                ->orderBy('resolved_at', 'desc')
                ->get();
            
            // Group by damaged_part
            $partBreakdown = [];
            foreach ($reports as $report) {
                $part = $report->damaged_part ?: 'Not Specified';
                
                if (!isset($partBreakdown[$part])) {
                    $partBreakdown[$part] = [
                        'part_name' => $part,
                        'count' => 0,
                        'total_cost' => 0,
                        'tickets' => []
                    ];
                }
                
                $partBreakdown[$part]['count']++;
                $partBreakdown[$part]['total_cost'] += $report->cost ?? 0;
                $partBreakdown[$part]['tickets'][] = [
                    'ticket_number' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                    'cost' => $report->cost ?? 0,
                    'date_fixed' => $report->resolved_at ? $report->resolved_at->format('M d, Y h:i A') : 'N/A',
                    'description' => $report->description ? substr($report->description, 0, 100) : 'N/A'
                ];
            }
            
            // Sort by count descending
            usort($partBreakdown, function($a, $b) {
                return $b['count'] - $a['count'];
            });
            
            // Get monthly breakdown
            $monthlyCosts = Report::where('location', $location)
                ->where('title', $issue)
                ->where('status', 'Resolved')
                ->where('created_at', '>=', now()->subMonths(12))
                ->selectRaw("TO_CHAR(reports.created_at, 'YYYY-MM') as month, COUNT(*) as count, SUM(reports.cost) as cost")
                ->groupBy('month')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function($row) {
                    return [
                        'month' => \Carbon\Carbon::parse($row->month . '-01')->format('M Y'),
                        'count' => $row->count,
                        'cost' => $row->cost ?? 0
                    ];
                });
            
            // Calculate summary stats
            $totalRepairs = $reports->count();
            $totalCost = $reports->sum('cost');
            $fixedRepairs = $reports->where('cost', '>', 0)->count();
            $avgCostPerRepair = $fixedRepairs > 0 ? $totalCost / $fixedRepairs : 0;
            
            // Determine severity
            $recentCount = Report::where('location', $location)
                ->where('title', $issue)
                ->where('created_at', '>=', now()->subMonths(3))
                ->count();
            $severity = $recentCount >= 3 ? 'critical' : ($recentCount >= 2 ? 'warning' : 'info');
            $alertTitle = $severity === 'critical' ? 'High Frequency Issue' : ($severity === 'warning' ? 'Recurring Issue' : 'Issue Detected');
            
            // Build date range string
            $dateRange = 'All Time';
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $dateRange = \Carbon\Carbon::parse($request->input('date_from'))->format('M d, Y') . ' - ' . 
                            \Carbon\Carbon::parse($request->input('date_to'))->format('M d, Y');
            }
            
            $pdf = \PDF::loadView('admin.alert-detail-pdf', compact(
                'location',
                'issue',
                'partBreakdown',
                'monthlyCosts',
                'totalRepairs',
                'totalCost',
                'avgCostPerRepair',
                'severity',
                'alertTitle',
                'dateRange'
            ));

            return $pdf->stream('alert-detail-' . str_replace(' ', '-', $location) . '-' . str_replace(' ', '-', $issue) . '-' . now()->format('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            \Log::error('Alert Detail PDF Generation Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
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
                'days' => 'required|integer|in:0,3,7,15,30',
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

    private function buildEmployeePerformanceStats(Request $request)
    {
        $employeeReportQuery = Report::with('category')
            ->whereNotNull('assigned_to')
            ->where('is_deleted', false);

        if ($request->filled('room_filter')) {
            $employeeReportQuery->where('location', $request->input('room_filter'));
        }

        if ($request->filled('date_from')) {
            $employeeReportQuery->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $employeeReportQuery->whereDate('created_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('month')) {
            $employeeReportQuery->whereMonth('created_at', $request->input('month'));
        }

        if ($request->filled('year')) {
            $employeeReportQuery->whereYear('created_at', $request->input('year'));
        }

        $employeeReports = $employeeReportQuery->get()->groupBy('assigned_to');
        $maxAssignedReports = max(1, $employeeReports->map->count()->max() ?? 1);

        $staffQuery = MaintenanceStaff::where('is_active', true)
            ->orderBy('name');

        if ($request->filled('employee_id')) {
            $staffQuery->where('id', $request->input('employee_id'));
        }

        return $staffQuery->get()
            ->map(function ($employee) use ($employeeReports, $maxAssignedReports) {
                $reports = $employeeReports->get($employee->id, collect());
                $assignedCount = $reports->count();
                $resolvedReports = $reports->where('status', 'Resolved');
                $resolvedCount = $resolvedReports->count();
                $activeCount = $reports->whereIn('status', ['Assigned', 'In Progress', 'Pending'])->count();
                $completionRate = $assignedCount > 0 ? round(($resolvedCount / $assignedCount) * 100) : 0;

                $resolutionHours = $resolvedReports
                    ->filter(fn ($report) => $report->assigned_at && $report->resolved_at)
                    ->map(fn ($report) => $report->assigned_at->diffInMinutes($report->resolved_at) / 60);
                $avgResolutionHours = $resolutionHours->count() > 0 ? round($resolutionHours->avg(), 1) : null;

                $volumeScore = min(100, round(($assignedCount / $maxAssignedReports) * 100));
                $speedScore = $avgResolutionHours === null
                    ? ($resolvedCount > 0 ? 80 : 0)
                    : max(40, min(100, round(100 - min($avgResolutionHours, 72))));
                $performanceScore = $assignedCount > 0
                    ? round(($completionRate * 0.55) + ($volumeScore * 0.25) + ($speedScore * 0.20))
                    : 0;

                $status = match (true) {
                    $performanceScore >= 90 => 'Excellent',
                    $performanceScore >= 80 => 'Very Good',
                    $performanceScore >= 70 => 'Good',
                    $performanceScore >= 60 => 'Needs Monitoring',
                    default => 'No Data',
                };

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'phone' => $employee->contact_number,
                    'position' => $employee->specialization ?: 'Maintenance Staff',
                    'department' => str_contains(strtolower($employee->specialization ?? ''), 'internet') || str_contains(strtolower($employee->specialization ?? ''), 'computer')
                        ? 'MIS'
                        : 'Maintenance',
                    'status' => $employee->is_active ? 'Active' : 'Inactive',
                    'performance_status' => $status,
                    'performance_score' => $performanceScore,
                    'completion_rate' => $completionRate,
                    'assigned_count' => $assignedCount,
                    'resolved_count' => $resolvedCount,
                    'active_count' => $activeCount,
                    'avg_resolution_hours' => $avgResolutionHours,
                    'total_cost_handled' => (float) $resolvedReports->sum('cost'),
                    'recent_tickets' => $reports
                        ->sortByDesc('created_at')
                        ->take(6)
                        ->map(fn ($report) => [
                            'ticket' => '#' . str_pad($report->id, 4, '0', STR_PAD_LEFT),
                            'issue' => $report->title ?? 'Report',
                            'location' => $report->location ?? 'N/A',
                            'status' => $report->status,
                            'cost' => (float) ($report->cost ?? 0),
                            'created_at' => optional($report->created_at)->format('M d, Y'),
                        ])
                        ->values(),
                    'notes' => [
                        $assignedCount > 0 ? "Handled {$assignedCount} assigned report(s) in this period." : 'No assigned reports in this period.',
                        $resolvedCount > 0 ? "Resolved {$resolvedCount} report(s)." : 'No resolved reports recorded yet.',
                        $avgResolutionHours !== null ? 'Average resolution time is ' . number_format($avgResolutionHours, 1) . ' hours.' : 'Resolution time is not yet available.',
                    ],
                ];
            })
            ->sortByDesc('performance_score')
            ->values();
    }
}
