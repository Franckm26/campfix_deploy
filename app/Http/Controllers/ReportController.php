<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Category;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Services\SecureFileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $role = $user->role;
        $archiveColumn = $role.'_archived';

        if (in_array($role, ['building_admin', 'mis', 'school_admin', 'admin'])) {
            // Only these admin roles can access all reports not archived by their role
            $reports = Report::with('user', 'category')->where($archiveColumn, false)->orderBy('created_at', 'desc')->get();
        } else {
            abort(403, 'Unauthorized');
        }

        return view('reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();

        return view('reports.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high,critical',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $secureUpload = new SecureFileUpload();
            $photoPath = $secureUpload->validateAndStore(
                $request->file('photo'),
                'reports',
                'reports'
            );

            if ($photoPath === null) {
                return redirect()->back()->withInput()->with('error', 'Invalid file upload. Please ensure the file is a valid image under 2MB.');
            }
        }

        Report::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'location' => $request->location,
            'severity' => $request->severity,
            'photo_path' => $photoPath,
            'status' => 'Pending',
        ]);

        return redirect()->route('reports.index')->with('success', 'Report submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        $user = Auth::user();

        // Allow building_admin, mis, school_admin, or maintenance access to shared Rooms reports
        $isAdmin = in_array($user->role, ['building_admin', 'mis', 'school_admin']);
        $isAssigned = $report->assigned_to === $user->id;
        $isSharedRoomsReport = $user->role === 'maintenance'
            && strtolower(trim($report->category->name ?? '')) === 'rooms';

        if (! $isAdmin && ! $isAssigned && ! $isSharedRoomsReport) {
            abort(403, 'Unauthorized');
        }

        // Check if request is AJAX for modal
        if (request()->ajax()) {
            return view('reports.show_modal', compact('report'));
        }

        return view('reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        $user = Auth::user();

        // Only allow admin roles to edit reports, and it's not resolved
        if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin']) || $report->status === 'Resolved') {
            abort(403, 'Unauthorized');
        }

        $categories = Category::all();

        return view('reports.edit', compact('report', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $user = Auth::user();

        // Only allow admin roles to update reports, and it's not resolved
        if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin']) || $report->status === 'Resolved') {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'location' => 'required|string|max:255',
            'severity' => 'required|in:low,medium,high,critical',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $photoPath = $report->photo_path;
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($photoPath) {
                $secureUpload = new SecureFileUpload();
                $secureUpload->deleteFile($photoPath);
            }

            $secureUpload = new SecureFileUpload();
            $photoPath = $secureUpload->validateAndStore(
                $request->file('photo'),
                'reports',
                'reports'
            );

            if ($photoPath === null) {
                return redirect()->back()->withInput()->with('error', 'Invalid file upload. Please ensure the file is a valid image under 2MB.');
            }
        }

        $report->update([
            'title' => $request->title,
            'category_id' => $request->category_id,
            'description' => $request->description,
            'location' => $request->location,
            'severity' => $request->severity,
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('reports.show', $report)->with('success', 'Report updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        try {
            $user = Auth::user();

            // Check if report is assigned but not resolved - assigned reports cannot be deleted unless resolved
            if ($report->assigned_to && $report->status !== 'Resolved') {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Cannot delete assigned reports that are not resolved. Please wait for resolution or unassign first.']);
                }
                return redirect()->back()->with('error', 'Cannot delete assigned reports that are not resolved. Please wait for resolution or unassign first.');
            }

            // Only allow admin roles to delete reports
            if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin'])) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Unauthorized.']);
                }
                abort(403, 'Unauthorized');
            }

            // Check if already deleted
            if ($report->is_deleted) {
                if (request()->expectsJson()) {
                    return response()->json(['success' => false, 'error' => 'Report is already deleted.']);
                }

                return redirect()->back()->with('error', 'Report is already deleted.');
            }

            // Get or create the Deleted Reports folder
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

            // Move report to deleted folder
            $roleDeletedColumn = $user->role.'_deleted';
            $report->update([
                'is_deleted' => true,
                'archive_folder_id' => $deletedFolder->id,
                'deleted_by' => $user->id,
                $roleDeletedColumn => true,
            ]);

            // Update folder item count
            // $deletedFolder->updateItemCount();

            // Log activity
            // $userName = $user->name ?: 'Unknown User';
            // ActivityLog::log('report_deleted', "Report '{$report->title}' moved to Deleted Reports by {$userName}");

            // Check if request is AJAX
            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Report deleted successfully.']);
            }

            return redirect()->route('reports.index')->with('success', 'Report deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'An error occurred while deleting the report.']);
            }

            return redirect()->back()->with('error', 'An error occurred while deleting the report.');
        }
    }

    /**
     * Archive the specified resource.
     */
    public function archive(Report $report)
    {
        $user = Auth::user();

        // Allow admin roles and maintenance to archive reports
        if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin', 'maintenance'])) {
            abort(403, 'Unauthorized');
        }

        // For maintenance, only allow archiving their assigned reports
        if ($user->role === 'maintenance' && $report->assigned_to !== $user->id) {
            abort(403, 'You can only archive reports assigned to you.');
        }

        // Set the appropriate archive flag based on role
        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $report->getFillable())) {
            return redirect()->route('reports.index')->with('error', 'Invalid role for archiving.');
        }

        // Check if already archived by this role
        if ($report->$archiveColumn) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'This report is already archived by your role.']);
            }

            return redirect()->route('reports.index')->with('error', 'This report is already archived by your role.');
        }

        // Set role-specific archive column to true (role-based archiving)
        $report->update([$archiveColumn => true]);

        // Also add to user's archive using pivot table (user-based archiving)
        $folderName = 'My Archive'; // Default folder name for reports
        $report->archivedByUsers()->attach($user->id, [
            'archived_at' => now(),
            'archive_folder_name' => $folderName,
        ]);

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Report archived successfully.']);
        }

        // Redirect maintenance users to their assigned reports page
        if ($user->role === 'maintenance') {
            return redirect()->route('reports.assigned')->with('success', 'Report archived successfully.');
        }

        return redirect()->route('reports.index')->with('success', 'Report archived successfully.');
    }

    /**
     * Restore the specified archived resource.
     */
    public function restore(Report $report)
    {
        $user = Auth::user();

        // Allow admin roles and maintenance to restore reports
        if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'maintenance'])) {
            abort(403, 'Unauthorized');
        }

        // For maintenance, only allow restoring their assigned reports
        if ($user->role === 'maintenance' && $report->assigned_to !== $user->id) {
            abort(403, 'You can only restore reports assigned to you.');
        }

        // Set the appropriate archive flag based on role
        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $report->getFillable())) {
            return redirect()->route('reports.index')->with('error', 'Invalid role for restoring.');
        }

        // Set role-specific archive column to false (role-based restoring)
        $report->update([$archiveColumn => false]);

        // Also remove from user's archive using pivot table (user-based restoring)
        $report->archivedByUsers()->detach($user->id);

        // Redirect maintenance users to their assigned reports page
        if ($user->role === 'maintenance') {
            return redirect()->route('reports.assigned')->with('success', 'Report restored successfully.');
        }

        return redirect()->route('reports.index')->with('success', 'Report restored successfully.');
    }

    /**
     * API: Get report data for view modal (JSON)
     */
    public function apiShow($id)
    {
        $report = Report::with('category', 'user', 'assignedTo')->findOrFail($id);

        $user = Auth::user();

        // Allow admin roles and maintenance users to view their assigned reports,
        // plus shared Rooms reports visible to all maintenance users
        if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin'])) {
            $isSharedRoomsReport = $user->role === 'maintenance'
                && strtolower(trim($report->category->name ?? '')) === 'rooms';

            if ($user->role === 'maintenance' && $report->assigned_to !== $user->id && ! $isSharedRoomsReport) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } elseif ($user->role !== 'maintenance') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        // OWASP API3: Object Property Level Authorization - filter sensitive fields
        $canSeeSensitiveFields = in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin']) || $report->assigned_to === $user->id;

        $reportData = $report->toArray();
        $reportData['assigned_user_name'] = $report->assignedTo ? $report->assignedTo->name : null;

        // Remove sensitive fields for unauthorized users
        if (! $canSeeSensitiveFields) {
            unset($reportData['resolution_notes']);
            unset($reportData['cost']);
            unset($reportData['damaged_part']);
            unset($reportData['replaced_part']);
        }

        return response()->json([
            'report' => $reportData,
        ]);

        // In the view modal JavaScript, add display for maintenance details
        // Similar to concerns view
    }

    /**
     * API: Get report data for edit modal (JSON)
     */
    public function apiEdit($id)
    {
        $report = Report::with('category')->findOrFail($id);

        $user = Auth::user();

        // Only allow admin roles to edit reports
        if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $categories = Category::all();

        return response()->json([
            'report' => $report,
            'categories' => $categories,
        ]);
    }

    /**
    /**
     * Show deleted reports for admin users
     */
    public function deleted(Request $request)
    {
        $user = Auth::user();

        // Only admin roles can access deleted reports
        if (! in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin'])) {
            abort(403, 'Unauthorized');
        }

        $days = $request->get('days', $user->reports_auto_delete_days ?? 15); // Use user's preference, default to 15 days

        // Get deleted reports that are older than the specified days
        $query = Report::with('user', 'category', 'deletedBy')
            ->where('is_deleted', true)
            ->where('deleted_at', '<=', now()->subDays($days));

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        // Filter by category
        if ($request->category) {
            $query->where('category_id', $request->category);
        }

        // Filter by search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%')
                    ->orWhere('location', 'like', '%'.$request->search.'%');
            });
        }

        $reports = $query->orderBy('deleted_at', 'desc')->paginate(10);
        $categories = Category::all();

        return view('reports.deleted', compact('reports', 'categories', 'days'));
    }

    /**
     * Maintenance can view reports assigned to them
     */
    /**
     * Acknowledge a report - maintenance acknowledges they will work on it
     */
    public function acknowledge(Request $request, $id)
    {
        // Only maintenance can acknowledge
        if (auth()->user()->role !== 'maintenance') {
            return back()->with('error', 'Access denied.');
        }

        $report = Report::findOrFail($id);

        // Allow maintenance to acknowledge their assigned reports and shared Rooms reports
        $isSharedRoomsReport = strtolower(trim($report->category->name ?? '')) === 'rooms';
        if ($report->assigned_to !== auth()->id() && ! $isSharedRoomsReport) {
            return back()->with('error', 'This report is not assigned to you.');
        }

        $oldStatus = $report->status;

        // Update status to In Progress
        $report->status = 'In Progress';
        $report->save();

        // Log activity
        ActivityLog::log(
            'report_acknowledged',
            'Report acknowledged by maintenance user',
            $report->id,
            'report'
        );

        // Log status change
        ReportStatusLog::create([
            'report_id' => $report->id,
            'changed_by' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => 'In Progress',
            'changed_at' => now(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Report acknowledged successfully!']);
        }

        return back()->with('success', 'Report acknowledged successfully!');
    }

    /**
     * Update report status (for progress tracking)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Assigned,In Progress,Resolved',
            'cost' => 'nullable|numeric|min:0',
            'damaged_part' => 'nullable|string|max:255',
            'replaced_part' => 'nullable|string|max:255',
            'resolution_notes' => 'nullable|string|max:1000'
        ]);

        $report = \App\Models\Report::findOrFail($id);

        // Check permissions
        $user = auth()->user();
        $canUpdate = false;

        // MIS, School Admin, Building Admin can update any report
        if (in_array($user->role, ['mis', 'school_admin', 'building_admin'])) {
            $canUpdate = true;
        }

        // Maintenance can update their assigned reports
        if ($user->role === 'maintenance' && $report->assigned_to === $user->id) {
            $canUpdate = true;
        }

        // Maintenance can update shared Rooms reports
        $isSharedRoomsReport = strtolower(trim($report->category->name ?? '')) === 'rooms';
        if ($user->role === 'maintenance' && $isSharedRoomsReport) {
            $canUpdate = true;
        }

        if (!$canUpdate) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to update this report.'
            ], 403);
        }

        $oldStatus = $report->status;
        $newStatus = $request->status;

        // Update status
        $report->status = $newStatus;

        // Set resolved_at timestamp if marking as resolved
        if ($newStatus === 'Resolved' && $oldStatus !== 'Resolved') {
            $report->resolved_at = now();
            
            // Update resolution details if provided
            if ($request->has('cost')) {
                $report->cost = $request->cost;
            }
            if ($request->has('damaged_part')) {
                $report->damaged_part = $request->damaged_part;
            }
            if ($request->has('replaced_part')) {
                $report->replaced_part = $request->replaced_part;
            }
            if ($request->has('resolution_notes')) {
                $report->resolution_notes = $request->resolution_notes;
            }
        }

        $report->save();

        // Sync the corresponding concern status
        $concern = $report->concern;
        if ($concern && $concern->status !== 'Resolved') {
            $concern->status = $newStatus;
            if ($newStatus === 'Resolved') {
                $concern->resolved_at = now();
                $concern->resolution_notes = $request->resolution_notes ?? null;
                $concern->cost = $request->cost ?? null;
                $concern->damaged_part = $request->damaged_part ?? null;
                $concern->replaced_part = $request->replaced_part ?? null;
            }
            $concern->save();
        }

        // Log the activity
        $logMessage = "Report #{$report->id} status changed from {$oldStatus} to {$newStatus} by " . $user->name;
        if ($newStatus === 'Resolved' && $request->has('cost')) {
            $logMessage .= " (Cost: ₱" . number_format($request->cost, 2) . ")";
        }
        
        \App\Models\ActivityLog::log(
            'report_status_updated',
            $logMessage,
            $report->id,
            'report'
        );

        return response()->json([
            'success' => true,
            'message' => "Report status updated to {$newStatus}.",
            'report' => $report
        ]);
    }
}
