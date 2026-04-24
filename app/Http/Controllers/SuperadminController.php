<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\FacilityRequest;
use App\Models\Report;
use App\Models\SuperadminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuperadminController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────────────────────

    public function dashboard()
    {
        // System-wide stats — bypass both 'not_deleted' and 'hide_superadmin' scopes
        $noScopes = fn() => User::withoutGlobalScopes();

        $stats = [
            'total_users'         => $noScopes()->count(),
            'active_users'        => $noScopes()->where('is_deleted', false)->where('is_archived', false)->count(),
            'archived_users'      => $noScopes()->where('is_archived', true)->count(),
            'deleted_users'       => $noScopes()->where('is_deleted', true)->count(),
            'locked_users'        => $noScopes()->where('is_deleted', false)->whereNotNull('locked_until')->where('locked_until', '>', now())->count(),
            'total_concerns'      => Concern::withoutGlobalScopes()->count(),
            'open_concerns'       => Concern::where('is_deleted', false)->whereNotIn('status', ['Resolved', 'Closed'])->count(),
            'resolved_concerns'   => Concern::where('status', 'Resolved')->count(),
            'total_reports'       => Report::withTrashed()->count(),
            'open_reports'        => Report::where('is_deleted', false)->whereNotIn('status', ['Resolved'])->count(),
            'total_events'        => EventRequest::withoutGlobalScopes()->count(),
            'pending_events'      => EventRequest::where('status', 'Pending')->count(),
            'total_categories'    => Category::count(),
            'total_activity_logs' => ActivityLog::count(),
        ];

        // Users by role (include superadmin)
        $usersByRole = $noScopes()
            ->where('is_deleted', false)
            ->selectRaw('role, count(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        // Recent superadmin activity
        $recentActivity = SuperadminActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(15)
            ->get();

        // Registration trend (last 6 months) — include superadmin
        $registrationTrend = collect(range(5, 0))->map(function ($i) use ($noScopes) {
            $date = now()->subMonths($i);
            return [
                'month' => $date->format('M Y'),
                'count' => $noScopes()
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        });

        // Concern trend (last 6 months)
        $concernTrend = collect(range(5, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'month' => $date->format('M Y'),
                'count' => Concern::withoutGlobalScopes()
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count(),
            ];
        });

        // All admins list (include superadmin)
        $admins = $noScopes()
            ->where('is_deleted', false)
            ->whereIn('role', ['mis', 'school_admin', 'building_admin', 'academic_head', 'program_head', 'principal_assistant', 'superadmin'])
            ->orderBy('role')
            ->get();

        SuperadminActivityLog::log('dashboard_viewed', 'Superadmin dashboard accessed');

        return view('superadmin.dashboard', compact(
            'stats',
            'usersByRole',
            'recentActivity',
            'registrationTrend',
            'concernTrend',
            'admins'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // USER MANAGEMENT (ALL USERS, INCLUDING DELETED)
    // ─────────────────────────────────────────────────────────────────────────

    public function users(Request $request)
    {
        $search    = $request->get('search');
        $role      = $request->get('role');
        $status    = $request->get('status', 'active');
        $perPage   = $request->get('per_page', 20);

        // Bypass BOTH global scopes so superadmin can see all users including other superadmins
        $query = User::withoutGlobalScopes()
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            }))
            ->when($role, fn($q) => $q->where('role', $role))
            ->when($status === 'active',   fn($q) => $q->where('is_deleted', false)->where('is_archived', false))
            ->when($status === 'archived', fn($q) => $q->where('is_archived', true))
            ->when($status === 'deleted',  fn($q) => $q->where('is_deleted', true))
            ->when($status === 'locked',   fn($q) => $q->where('is_deleted', false)->whereNotNull('locked_until')->where('locked_until', '>', now()))
            ->orderBy('created_at', 'desc');

        $users = $query->paginate($perPage)->withQueryString();

        return view('superadmin.users', compact('users', 'search', 'role', 'status'));
    }

    public function createUser()
    {
        return view('superadmin.users-create');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'role'         => 'required|in:student,faculty,maintenance,mis,school_admin,building_admin,academic_head,program_head,principal_assistant,superadmin',
            'department'   => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'is_superadmin'=> 'boolean',
        ]);

        $user = User::create([
            'uuid'          => (string) Str::uuid(),
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'role'          => $request->role,
            'department'    => $request->department,
            'phone'         => $request->phone,
            'is_admin'      => in_array($request->role, ['mis', 'school_admin', 'building_admin', 'superadmin']),
            'is_superadmin' => $request->role === 'superadmin' ? true : false,
            'force_password_change' => $request->boolean('force_password_change', true),
            'permissions'   => $request->input('permissions', []),
        ]);

        SuperadminActivityLog::log(
            'user_created',
            "Created user: {$user->name} ({$user->email}) with role: {$user->role}",
            null,
            ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role]
        );

        return redirect()->route('superadmin.users')->with('success', "User '{$user->name}' created successfully.");
    }

    public function editUser($uuid)
    {
        $user = User::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();
        return view('superadmin.users-edit', compact('user'));
    }

    public function updateUser(Request $request, $uuid)
    {
        $user = User::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => "required|email|unique:users,email,{$user->id}",
            'role'         => 'required|in:student,faculty,maintenance,mis,school_admin,building_admin,academic_head,program_head,principal_assistant,superadmin',
            'department'   => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'password'     => 'nullable|string|min:8|confirmed',
        ]);

        $oldValues = $user->only(['name', 'email', 'role', 'department', 'phone']);

        $updateData = [
            'name'          => $request->name,
            'email'         => $request->email,
            'role'          => $request->role,
            'department'    => $request->department,
            'phone'         => $request->phone,
            'is_admin'      => in_array($request->role, ['mis', 'school_admin', 'building_admin', 'superadmin']),
            'is_superadmin' => $request->role === 'superadmin',
            'permissions'   => $request->input('permissions', []),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
            $updateData['force_password_change'] = false;
        }

        $user->update($updateData);

        SuperadminActivityLog::log(
            'user_updated',
            "Updated user: {$user->name} ({$user->email})",
            $oldValues,
            $user->only(['name', 'email', 'role', 'department', 'phone'])
        );

        return redirect()->route('superadmin.users')->with('success', "User '{$user->name}' updated successfully.");
    }

    public function deleteUser(Request $request, $uuid)
    {
        $user = User::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;

        // Hard delete
        $user->forceDelete();

        SuperadminActivityLog::log(
            'user_permanently_deleted',
            "Permanently deleted user: {$userName} (UUID: {$uuid})"
        );

        return redirect()->route('superadmin.users')->with('success', "User '{$userName}' permanently deleted.");
    }

    public function restoreUser($uuid)
    {
        $user = User::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        $user->update([
            'is_deleted'  => false,
            'is_archived' => false,
            'deleted_by'  => null,
        ]);

        SuperadminActivityLog::log(
            'user_restored',
            "Restored user: {$user->name} ({$user->email})"
        );

        return back()->with('success', "User '{$user->name}' restored successfully.");
    }

    public function unlockUser($uuid)
    {
        $user = User::withoutGlobalScopes()->where('uuid', $uuid)->firstOrFail();

        $user->update([
            'locked_until'          => null,
            'failed_login_attempts' => 0,
            'login_lockout_level'   => 0,
        ]);

        SuperadminActivityLog::log(
            'user_unlocked',
            "Unlocked account: {$user->name} ({$user->email})"
        );

        return back()->with('success', "Account '{$user->name}' unlocked.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SYSTEM OVERVIEW (ALL MODULES)
    // ─────────────────────────────────────────────────────────────────────────

    public function concerns(Request $request)
    {
        $status  = $request->get('status', 'all');
        $search  = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $query = Concern::withoutGlobalScopes()
            ->with(['user', 'assignedTo', 'categoryRelation'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            }))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc');

        $concerns = $query->paginate($perPage)->withQueryString();

        return view('superadmin.concerns', compact('concerns', 'status', 'search'));
    }

    public function reports(Request $request)
    {
        $status  = $request->get('status', 'all');
        $search  = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $query = Report::withTrashed()
            ->with(['user', 'category'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            }))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc');

        $reports = $query->paginate($perPage)->withQueryString();

        return view('superadmin.reports', compact('reports', 'status', 'search'));
    }

    public function events(Request $request)
    {
        $status  = $request->get('status', 'all');
        $search  = $request->get('search');
        $perPage = $request->get('per_page', 20);

        $query = EventRequest::withoutGlobalScopes()
            ->with(['user'])
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            }))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc');

        $events = $query->paginate($perPage)->withQueryString();

        return view('superadmin.events', compact('events', 'status', 'search'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACTIVITY LOGS (ALL LOGS, INCLUDING SUPERADMIN LOGS)
    // ─────────────────────────────────────────────────────────────────────────

    public function activityLogs(Request $request)
    {
        $search  = $request->get('search');
        $perPage = $request->get('per_page', 30);

        $logs = ActivityLog::with('user')
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('superadmin.activity-logs', compact('logs', 'search'));
    }

    public function superadminLogs(Request $request)
    {
        $search  = $request->get('search');
        $perPage = $request->get('per_page', 30);

        $logs = SuperadminActivityLog::with('user')
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            }))
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('superadmin.superadmin-logs', compact('logs', 'search'));
    }

    public function deleteActivityLog($id)
    {
        $log = ActivityLog::findOrFail($id);
        $log->delete();

        SuperadminActivityLog::log('activity_log_deleted', "Deleted activity log ID: {$id}");

        return back()->with('success', 'Activity log deleted.');
    }

    public function clearAllActivityLogs()
    {
        $count = ActivityLog::count();
        ActivityLog::truncate();

        SuperadminActivityLog::log('activity_logs_cleared', "Cleared all {$count} activity logs");

        return back()->with('success', "All {$count} activity logs cleared.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SYSTEM SETTINGS
    // ─────────────────────────────────────────────────────────────────────────

    public function settings()
    {
        $superadmins = User::withoutGlobalScopes()
            ->where('is_superadmin', true)
            ->where('is_deleted', false)
            ->get();

        SuperadminActivityLog::log('settings_viewed', 'Superadmin settings page accessed');

        return view('superadmin.settings', compact('superadmins'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name'        => 'nullable|string|max:100',
            'app_timezone'    => 'nullable|string|max:50',
            'maintenance_mode'=> 'nullable|boolean',
        ]);

        // Update .env or config values as needed
        SuperadminActivityLog::log(
            'settings_updated',
            'System settings updated',
            null,
            $request->only(['app_name', 'app_timezone', 'maintenance_mode'])
        );

        return back()->with('success', 'Settings updated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ANALYTICS
    // ─────────────────────────────────────────────────────────────────────────

    public function analytics()
    {
        // Monthly concerns for the past 12 months
        $monthlyConcerns = collect(range(11, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'month'    => $date->format('M Y'),
                'total'    => Concern::withoutGlobalScopes()->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
                'resolved' => Concern::withoutGlobalScopes()->where('status', 'Resolved')->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
            ];
        });

        // Monthly reports for the past 12 months
        $monthlyReports = collect(range(11, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'month'    => $date->format('M Y'),
                'total'    => Report::withTrashed()->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
                'resolved' => Report::withTrashed()->where('status', 'Resolved')->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
            ];
        });

        // User growth (include superadmin in counts)
        $userGrowth = collect(range(11, 0))->map(function ($i) {
            $date = now()->subMonths($i);
            return [
                'month' => $date->format('M Y'),
                'count' => User::withoutGlobalScopes()->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count(),
            ];
        });

        // Concerns by category
        $concernsByCategory = Concern::withoutGlobalScopes()
            ->with('categoryRelation')
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Top reporters (include all users)
        $topReporters = User::withoutGlobalScopes()
            ->withCount('concerns')
            ->orderByDesc('concerns_count')
            ->limit(10)
            ->get();

        SuperadminActivityLog::log('analytics_viewed', 'Superadmin analytics page accessed');

        return view('superadmin.analytics', compact(
            'monthlyConcerns',
            'monthlyReports',
            'userGrowth',
            'concernsByCategory',
            'topReporters'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORCE ACTIONS (SUPERADMIN ONLY)
    // ─────────────────────────────────────────────────────────────────────────

    public function forceDeleteConcern($id)
    {
        $concern = Concern::withoutGlobalScopes()->findOrFail($id);
        $desc    = $concern->title ?? "ID:{$id}";
        $concern->delete(); // hard delete (no SoftDeletes trait on Concern)

        SuperadminActivityLog::log('concern_force_deleted', "Force deleted concern: {$desc}");

        return back()->with('success', 'Concern permanently deleted.');
    }

    public function forceDeleteReport($id)
    {
        $report = Report::withTrashed()->findOrFail($id);
        $desc   = $report->title ?? "ID:{$id}";
        $report->forceDelete(); // Report uses SoftDeletes

        SuperadminActivityLog::log('report_force_deleted', "Force deleted report: {$desc}");

        return back()->with('success', 'Report permanently deleted.');
    }

    public function forceDeleteEvent($id)
    {
        $event = EventRequest::withoutGlobalScopes()->findOrFail($id);
        $desc  = $event->title ?? "ID:{$id}";
        $event->delete();

        SuperadminActivityLog::log('event_force_deleted', "Force deleted event request: {$desc}");

        return back()->with('success', 'Event request permanently deleted.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CATEGORIES (FULL CONTROL)
    // ─────────────────────────────────────────────────────────────────────────

    public function categories()
    {
        $categories = Category::withCount(['concerns', 'reports'])->orderBy('name')->get();
        return view('superadmin.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:categories,name']);

        $category = Category::create(['name' => $request->name, 'description' => $request->description]);

        SuperadminActivityLog::log('category_created', "Created category: {$category->name}");

        return back()->with('success', "Category '{$category->name}' created.");
    }

    public function updateCategory(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $request->validate(['name' => "required|string|max:100|unique:categories,name,{$id}"]);

        $old = $category->name;
        $category->update(['name' => $request->name, 'description' => $request->description]);

        SuperadminActivityLog::log('category_updated', "Updated category: '{$old}' → '{$category->name}'");

        return back()->with('success', 'Category updated.');
    }

    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $name     = $category->name;
        $category->delete();

        SuperadminActivityLog::log('category_deleted', "Deleted category: {$name}");

        return back()->with('success', "Category '{$name}' deleted.");
    }
}
