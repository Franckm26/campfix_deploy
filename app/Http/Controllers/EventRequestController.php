<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\FacilityRequest;
use App\Models\Report;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventRequestController extends Controller
{
    // Show form to create event request - Only for faculty
    public function create()
    {
        if (auth()->user()->role !== 'faculty') {
            return redirect('/dashboard')->with('error', 'You do not have permission to create event requests.');
        }

        // Redirect to my events page with modal trigger
        return redirect('/my-events?open_modal=true');
    }

    // Store new event request - Only for faculty and admin roles
    public function store(Request $request)
    {
        $allowedRoles = ['faculty', 'school_admin', 'academic_head', 'program_head', 'building_admin'];
        if (!in_array(auth()->user()->role, $allowedRoles)) {
            return redirect('/dashboard')->with('error', 'You do not have permission to create event requests.');
        }
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10',
            'event_date' => 'required|date|after_or_equal:today',
            'location' => 'required|string|min:3|max:255',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'category' => 'required|in:Area Use',
            'area_of_use' => 'required_if:category,Area Use|in:Room,Court,AVR,Library,Open Lobby,Computer Laboratory,Kitchen',
            'room_number' => 'nullable|string',
            'department' => 'nullable|in:GE,ICT,Business Management,THM',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'education_level' => 'required|in:tertiary,shs,faculty,staff,maintenance',
            'picture' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'title.required' => 'The event title is required.',
            'title.min' => 'The event title must be at least 3 characters.',
            'title.max' => 'The event title cannot exceed 255 characters.',
            'description.required' => 'The description is required.',
            'description.min' => 'The description must be at least 10 characters.',
            'event_date.required' => 'The event date is required.',
            'event_date.after_or_equal' => 'The event date cannot be in the past.',
            'location.required' => 'The location is required.',
            'location.min' => 'The location must be at least 3 characters.',
            'start_time.required' => 'The start time is required.',
            'end_time.required' => 'The end time is required.',
            'end_time.after' => 'The end time must be after the start time.',
            'category.required' => 'Please select a category.',
            'category.in' => 'Please select a valid category.',
            'other_category.required_if' => 'Please specify the category when selecting "Other".',
        ]);

        // Process materials_needed - convert to array if provided
        $materialsNeeded = null;
        if ($request->has('materials') && is_array($request->materials)) {
            $materials = array_filter($request->materials, function ($item) {
                return ! empty($item['item']);
            });
            if (! empty($materials)) {
                $materialsNeeded = array_values($materials);
            }
        }

        // Handle picture upload
        $imagePath = null;
        if ($request->hasFile('picture')) {
            $imagePath = $request->file('picture')->store('event-images', 'public');
        }

        $educationLevel = $request->education_level ?? 'tertiary';
        $isFacultyIntended = $educationLevel === 'faculty';

        $eventRequest = EventRequest::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'event_date' => $request->event_date,
            'location' => $request->location,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'category' => $request->category,
            'other_category' => $request->other_category,
            'department' => $request->department,
            'level' => $educationLevel,
            'education_level' => $educationLevel,
            'priority' => $request->priority ?? 'medium',
            // Faculty-intended requests are auto-approved; others go through the approval chain
            'status' => $isFacultyIntended ? 'Approved' : 'Pending',
            'approval_level' => $isFacultyIntended ? EventRequest::LEVEL_APPROVED : EventRequest::LEVEL_NONE,
            'approved_at' => $isFacultyIntended ? now() : null,
            'materials_needed' => $materialsNeeded,
            'image_path' => $imagePath,
        ]);

        ActivityLog::log(
            'event_request_created',
            'Event request submitted: '.$request->title,
            null
        );

        $notificationService = new NotificationService;

        if ($isFacultyIntended) {
            // Faculty-intended: no approval needed — notify building admin and school admin only
            $notificationService->notifyAdminsOfFacultyRequest($eventRequest);

            return redirect('/dashboard')->with('success', 'Facility request submitted successfully! Building Admin and School Admin have been notified.');
        }

        // Non-faculty: go through the normal multi-level approval chain
        $notificationService->notifyApproversOfNewEvent($eventRequest);

        return redirect('/dashboard')->with('success', 'Event request submitted successfully! Waiting for approval.');
    }

    // Show single event request (for web/AJAX calls)
    public function show($id)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $event = EventRequest::with('user:id,name,role')
            ->where('is_deleted', false)
            ->findOrFail($id);

        // Check if user can view this event
        // Allow if: user owns the event OR user can approve requests
        if ((int)$event->user_id === (int)$user->id || $user->canApproveRequests()) {
            return response()->json(['event' => $event]);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Show user's event requests - Only for faculty
    public function myRequests(Request $request)
    {
        $allowedRoles = ['faculty', 'building_admin', 'school_admin', 'academic_head', 'program_head', 'principal_assistant'];
        
        if (!in_array(auth()->user()->role, $allowedRoles)) {
            return redirect('/dashboard')->with('error', 'You do not have permission to view event requests.');
        }

        $viewType = $request->get('view', 'active'); // 'active', 'archives', or 'deleted'

        $user = auth()->user();
        $archiveColumn = $user->role.'_archived';

        // ========== ARCHIVES VIEW ==========
        if ($viewType === 'archives') {
            $query = EventRequest::where('user_id', Auth::id())
                ->where('is_deleted', false)
                ->where($archiveColumn, true);

            // Apply filters
            if ($request->filled('search')) {
                $query->where('title', 'like', '%'.$request->search.'%');
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('event_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('event_date', '<=', $request->date_to);
            }

            $archivedRequests = $query->orderBy('updated_at', 'desc')->get();

            return view('events.my', [
                'archivedRequests' => $archivedRequests,
                'viewType' => $viewType,
                'requests' => collect(),
                'deletedRequests' => collect(),
            ]);
        }

        // ========== DELETED VIEW ==========
        if ($viewType === 'deleted') {
            $query = EventRequest::where('user_id', Auth::id())
                ->where('is_deleted', true);

            // Apply filters
            if ($request->filled('search')) {
                $query->where('title', 'like', '%'.$request->search.'%');
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('event_date', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('event_date', '<=', $request->date_to);
            }

            $deletedRequests = $query->orderBy('updated_at', 'desc')->get();

            return view('events.my', [
                'deletedRequests' => $deletedRequests,
                'viewType' => $viewType,
                'requests' => collect(),
                'archivedRequests' => collect(),
            ]);
        }

        // ========== ACTIVE VIEW (DEFAULT) ==========
        $query = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', false)
            ->where($archiveColumn, false);

        // Apply filters
        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('event_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        // Always fetch archived and deleted for tab counts
        $archivedRequests = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', false)
            ->where($archiveColumn, true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $deletedRequests = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('events.my', compact('requests', 'viewType', 'archivedRequests', 'deletedRequests'));
    }

    // Show all pending requests (for principal/admin)
    public function pendingRequests(Request $request)
    {
        $query = EventRequest::with('user');
        $user = auth()->user();

        if (! $user->canApproveRequests()) {
            $query->where('user_id', $user->id)->where('status', 'Pending');
        } else {
            // Each approver only sees requests that are at THEIR level in the chain
            $query->where('status', 'Pending');

            if ($user->isProgramHead()) {
                // Level 0 → waiting for Program Head
                $query->where('approval_level', EventRequest::LEVEL_NONE)
                      ->where(function ($q) {
                          $q->where('education_level', 'tertiary')
                            ->orWhereNull('education_level');
                      });
            } elseif ($user->isPrincipalAssistant()) {
                // SHS level 0 → waiting for Principal Assistant
                $query->where('approval_level', EventRequest::LEVEL_NONE)
                      ->where('education_level', 'shs');
            } elseif ($user->isAcademicHead()) {
                // Level 1 → Program Head (or Principal Assistant) already approved
                $query->where('approval_level', EventRequest::LEVEL_1_PROGRAM_HEAD);
            } elseif ($user->isBuildingAdmin()) {
                // Building Admin sees requests that have passed all levels before them.
                // If Program Head and Academic Head don't exist, requests stay at LEVEL_NONE.
                $hasProgramHead  = \App\Models\User::where('role', 'program_head')->exists();
                $hasAcademicHead = \App\Models\User::where('role', 'academic_head')->exists();

                $allowedLevels = [];
                if (!$hasProgramHead && !$hasAcademicHead) {
                    // No PH or AH → building admin is first approver
                    $allowedLevels = [EventRequest::LEVEL_NONE];
                } elseif ($hasProgramHead && !$hasAcademicHead) {
                    // PH exists but no AH → building admin sees after PH
                    $allowedLevels = [EventRequest::LEVEL_1_PROGRAM_HEAD];
                } elseif (!$hasProgramHead && $hasAcademicHead) {
                    // AH exists but no PH → building admin sees after AH
                    $allowedLevels = [EventRequest::LEVEL_2_ACADEMIC_HEAD];
                } else {
                    // Both exist → normal flow
                    $allowedLevels = [EventRequest::LEVEL_2_ACADEMIC_HEAD];
                }

                $query->whereIn('approval_level', $allowedLevels)
                      ->where(function ($q) {
                          $q->where('education_level', 'tertiary')
                            ->orWhereNull('education_level');
                      });
            } elseif ($user->isSchoolAdmin() || $user->isAdmin()) {
                $hasProgramHead   = \App\Models\User::where('role', 'program_head')->exists();
                $hasAcademicHead  = \App\Models\User::where('role', 'academic_head')->exists();
                $hasBuildingAdmin = \App\Models\User::where('role', 'building_admin')->exists();

                // Determine the highest level tertiary requests can reach before school admin
                $tertiaryLevels = [];
                if (!$hasProgramHead && !$hasAcademicHead && !$hasBuildingAdmin) {
                    $tertiaryLevels = [EventRequest::LEVEL_NONE];
                } elseif ($hasProgramHead && !$hasAcademicHead && !$hasBuildingAdmin) {
                    $tertiaryLevels = [EventRequest::LEVEL_1_PROGRAM_HEAD];
                } elseif (!$hasProgramHead && $hasAcademicHead && !$hasBuildingAdmin) {
                    $tertiaryLevels = [EventRequest::LEVEL_2_ACADEMIC_HEAD];
                } elseif (!$hasProgramHead && !$hasAcademicHead && $hasBuildingAdmin) {
                    $tertiaryLevels = [EventRequest::LEVEL_3_BUILDING_ADMIN];
                } elseif ($hasProgramHead && $hasAcademicHead && !$hasBuildingAdmin) {
                    $tertiaryLevels = [EventRequest::LEVEL_2_ACADEMIC_HEAD];
                } elseif ($hasProgramHead && !$hasAcademicHead && $hasBuildingAdmin) {
                    $tertiaryLevels = [EventRequest::LEVEL_3_BUILDING_ADMIN];
                } elseif (!$hasProgramHead && $hasAcademicHead && $hasBuildingAdmin) {
                    $tertiaryLevels = [EventRequest::LEVEL_3_BUILDING_ADMIN];
                } else {
                    // All exist → normal full chain
                    $tertiaryLevels = [EventRequest::LEVEL_3_BUILDING_ADMIN];
                }

                $query->where(function ($q) use ($tertiaryLevels) {
                    $q->where(function ($inner) use ($tertiaryLevels) {
                        // Tertiary: passed all levels before school admin
                        $inner->whereIn('approval_level', $tertiaryLevels)
                              ->where(function ($e) {
                                  $e->where('education_level', 'tertiary')
                                    ->orWhereNull('education_level');
                              });
                    })->orWhere(function ($inner) {
                        // SHS: Academic Head approved
                        $inner->where('approval_level', EventRequest::LEVEL_2_ACADEMIC_HEAD)
                              ->where('education_level', 'shs');
                    });
                });
            }
        }

        // Additional filters
        if ($request->status && $user->canApproveRequests()) {
            // Allow overriding status filter for admins (e.g. to view Approved/Rejected history)
            $query->where('status', $request->status);
        }
        if ($request->category) {
            $query->where('category', $request->category);
        }
        if ($request->date_from) {
            $query->whereDate('event_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }
        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $requests = $query->orderBy('event_date', 'asc')->get();

        return view('events.pending', compact('requests'));
    }

    // Approve request - handles multi-level approval (ALL approvers must approve at each level)
    public function approve(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);
        $user = auth()->user();
        $isShs = ($eventRequest->education_level ?? 'tertiary') === 'shs';

        // SHS chain: Principal Assistant (level 1) → Academic Head (level 2) → School Admin (final)
        if ($isShs && $user->isPrincipalAssistant()) {
            if ($eventRequest->hasUserApprovedAtLevel($user->id, 1)) {
                return back()->with('error', 'You have already approved this event request.');
            }

            $eventRequest->approved_by_level_1 = $user->id;
            $eventRequest->approved_at_level_1 = now();

            $history = $eventRequest->approval_history ?? [];
            $history[] = [
                'level' => 1,
                'role' => 'Principal Assistant',
                'approver' => $user->name,
                'approver_id' => $user->id,
                'at' => now()->toDateTimeString(),
                'notes' => $request->notes,
            ];
            $eventRequest->approval_history = $history;
            $eventRequest->approval_level = EventRequest::LEVEL_1_PROGRAM_HEAD;
            $eventRequest->status = 'Pending';
            $eventRequest->save();

            ActivityLog::log('event_approved_level_1', 'SHS Event approved by Principal Assistant: '.$eventRequest->title, null);
            $this->sendApprovalNotification($eventRequest, 1, 'Approved');

            $notificationService = new NotificationService;
            $notificationService->notifyApproversOfNewEvent($eventRequest);

            return back()->with('success', 'Event request approved! Forwarded to Academic Head.');
        }

        // Determine approval level based on user role
        if ($user->isProgramHead()) {
            // Check if this user has already approved at this level
            if ($eventRequest->hasUserApprovedAtLevel($user->id, 1)) {
                return back()->with('error', 'You have already approved this event request.');
            }

            // Level 1: Program Head approval
            $eventRequest->approved_by_level_1 = $user->id;
            $eventRequest->approved_at_level_1 = now();

            $history = $eventRequest->approval_history ?? [];
            $history[] = [
                'level' => 1,
                'role' => 'Program Head',
                'approver' => $user->name,
                'approver_id' => $user->id,
                'at' => now()->toDateTimeString(),
                'notes' => $request->notes,
            ];
            $eventRequest->approval_history = $history;

            // Check if ALL Program Heads have approved
            if ($eventRequest->isApprovedByAllProgramHeads()) {
                $eventRequest->approval_level = EventRequest::LEVEL_1_PROGRAM_HEAD;

                // Check if Academic Head exists, if not skip to next level
                if (\App\Models\User::where('role', 'academic_head')->exists()) {
                    $eventRequest->status = 'Pending'; // Still pending for Academic Head
                } elseif (\App\Models\User::where('role', 'building_admin')->exists()) {
                    $eventRequest->status = 'Pending'; // Still pending for Building Admin
                } elseif (\App\Models\User::whereIn('role', ['school_admin', 'mis'])->exists()) {
                    $eventRequest->status = 'Pending'; // Still pending for School Admin / MIS
                } else {
                    // No more approvers, approve directly
                    $eventRequest->status = 'Approved';
                    $eventRequest->approved_by = $user->id;
                    $eventRequest->approved_at = now();
                    $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;
                }
            } else {
                // Still waiting for other Program Heads to approve
                $eventRequest->status = 'Pending';
                $eventRequest->approval_level = EventRequest::LEVEL_NONE;
            }

            ActivityLog::log(
                'event_approved_level_1',
                'Event approved by Program Head: '.$eventRequest->title,
                null
            );

            // Notify the requester
            $this->sendApprovalNotification($eventRequest, 1, 'Approved');

            // Notify the next approver (Academic Head) only if all Program Heads have approved
            if ($eventRequest->isApprovedByAllProgramHeads()) {
                $notificationService = new NotificationService;
                $notificationService->notifyApproversOfNewEvent($eventRequest);
            }

        } elseif ($user->isAcademicHead()) {
            // Check if this user has already approved at this level
            if ($eventRequest->hasUserApprovedAtLevel($user->id, 2)) {
                return back()->with('error', 'You have already approved this event request.');
            }

            // Level 2: Academic Head approval
            $eventRequest->approved_by_level_2 = $user->id;
            $eventRequest->approved_at_level_2 = now();

            $history = $eventRequest->approval_history ?? [];
            $history[] = [
                'level' => 2,
                'role' => 'Academic Head',
                'approver' => $user->name,
                'approver_id' => $user->id,
                'at' => now()->toDateTimeString(),
                'notes' => $request->notes,
            ];
            $eventRequest->approval_history = $history;

            // Check if ALL Academic Heads have approved AND all Program Heads have approved
            if ($eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                $eventRequest->approval_level = EventRequest::LEVEL_2_ACADEMIC_HEAD;

                // Check if Building Admin exists
                if (\App\Models\User::where('role', 'building_admin')->exists()) {
                    $eventRequest->status = 'Pending'; // Still pending for Building Admin
                } elseif (\App\Models\User::whereIn('role', ['school_admin', 'mis'])->exists()) {
                    $eventRequest->status = 'Pending'; // Still pending for School Admin / MIS
                } else {
                    // No more approvers, approve directly
                    $eventRequest->status = 'Approved';
                    $eventRequest->approved_by = $user->id;
                    $eventRequest->approved_at = now();
                    $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;
                }
            } else {
                // Still waiting for other Academic Heads to approve
                $eventRequest->status = 'Pending';
                $eventRequest->approval_level = EventRequest::LEVEL_1_PROGRAM_HEAD;
            }

            ActivityLog::log(
                'event_approved_level_2',
                'Event approved by Academic Head: '.$eventRequest->title,
                null
            );

            // Notify the requester
            $this->sendApprovalNotification($eventRequest, 2, 'Approved');

            // Notify the next approver (Building Admin) only if all Academic Heads have approved
            if ($eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                $notificationService = new NotificationService;
                $notificationService->notifyApproversOfNewEvent($eventRequest);
            }

        } elseif ($user->isBuildingAdmin()) {
            // Check if this user has already approved at this level
            if ($eventRequest->hasUserApprovedAtLevel($user->id, 3)) {
                return back()->with('error', 'You have already approved this event request.');
            }

            // Level 3: Building Admin approval
            $eventRequest->approved_by_level_3 = $user->id;
            $eventRequest->approved_at_level_3 = now();

            $history = $eventRequest->approval_history ?? [];
            $history[] = [
                'level' => 3,
                'role' => 'Building Admin',
                'approver' => $user->name,
                'approver_id' => $user->id,
                'at' => now()->toDateTimeString(),
                'notes' => $request->notes,
            ];
            $eventRequest->approval_history = $history;

            // Check if ALL Building Admins have approved AND all previous levels
            if ($eventRequest->isApprovedByAllBuildingAdmins() && $eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                $eventRequest->approval_level = EventRequest::LEVEL_3_BUILDING_ADMIN;

                // Check if School Admin exists
                if (\App\Models\User::whereIn('role', ['school_admin', 'mis'])->exists()) {
                    $eventRequest->status = 'Pending'; // Still pending for final approval
                } else {
                    // No School Admin, approve directly
                    $eventRequest->status = 'Approved';
                    $eventRequest->approved_by = $user->id;
                    $eventRequest->approved_at = now();
                    $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;
                }
            } else {
                // Still waiting for other Building Admins to approve
                $eventRequest->status = 'Pending';
                $eventRequest->approval_level = EventRequest::LEVEL_2_ACADEMIC_HEAD;
            }

            ActivityLog::log(
                'event_approved_level_3',
                'Event approved by Building Admin: '.$eventRequest->title,
                null
            );

            // Notify the requester
            $this->sendApprovalNotification($eventRequest, 3, 'Approved');

            // Notify the next approver (School Admin) only if all Building Admins have approved
            if ($eventRequest->isApprovedByAllBuildingAdmins() && $eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                $notificationService = new NotificationService;
                $notificationService->notifyApproversOfNewEvent($eventRequest);
            }

        } elseif ($user->isSchoolAdmin() || $user->isAdmin()) {
            // Check if this user has already approved at this level
            if ($eventRequest->hasUserApprovedAtLevel($user->id, 4)) {
                // Already approved - re-evaluate in case previous logic left it stuck
                if ($eventRequest->isApprovedByAllSchoolAdmins() && $eventRequest->isApprovedByAllBuildingAdmins() && $eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                    $eventRequest->status = 'Approved';
                    $eventRequest->approved_by = $user->id;
                    $eventRequest->approved_at = now();
                    $eventRequest->approval_level = EventRequest::LEVEL_4_SCHOOL_ADMIN;
                    $eventRequest->save();
                    return back()->with('success', 'Event request fully approved!');
                }
                return back()->with('error', 'You have already approved this event request.');
            }

            // Level 4: School Admin / Principal final approval
            $history = $eventRequest->approval_history ?? [];
            $history[] = [
                'level' => 4,
                'role' => 'School Admin',
                'approver' => $user->name,
                'approver_id' => $user->id,
                'at' => now()->toDateTimeString(),
                'notes' => $request->notes,
            ];
            $eventRequest->approval_history = $history;

            // Check if ALL School Admins have approved AND all previous levels
            if ($eventRequest->isApprovedByAllSchoolAdmins() && $eventRequest->isApprovedByAllBuildingAdmins() && $eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                $eventRequest->status = 'Approved';
                $eventRequest->approved_by = $user->id;
                $eventRequest->approved_at = now();
                $eventRequest->approval_level = EventRequest::LEVEL_4_SCHOOL_ADMIN;

                ActivityLog::log(
                    'event_approved',
                    'Event fully approved by all School Admins: '.$eventRequest->title,
                    null
                );
            } else {
                // Still waiting for other School Admins to approve
                $eventRequest->status = 'Pending';
                $eventRequest->approval_level = EventRequest::LEVEL_3_BUILDING_ADMIN;

                ActivityLog::log(
                    'event_approved_level_4_partial',
                    'Event approved by School Admin (waiting for others): '.$eventRequest->title,
                    null
                );
            }

            // Notify the requester
            $this->sendApprovalNotification($eventRequest, 4, $eventRequest->status === 'Approved' ? 'Fully Approved' : 'Partially Approved');
        } else {
            // Fallback for any other role with approval permission
            $eventRequest->status = 'Approved';
            $eventRequest->approved_by = $user->id;
            $eventRequest->approved_at = now();
            $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;

            ActivityLog::log(
                'event_approved',
                'Event approved: '.$eventRequest->title,
                null
            );

            // Notify the requester
            $this->sendApprovalNotification($eventRequest, 4, 'Approved');
        }

        $eventRequest->notes = $request->notes;
        $eventRequest->save();

        $message = $eventRequest->status === 'Approved'
            ? 'Event request fully approved!'
            : 'Your approval has been recorded. Waiting for other approvers.';

        return back()->with('success', $message);
    }

    /**
     * Send notification to requester about approval progress
     */
    private function sendApprovalNotification(EventRequest $eventRequest, int $level, string $status): void
    {
        try {
            $requester = $eventRequest->user;
            if ($requester) {
                $notificationService = new NotificationService;
                $notificationService->notifyEventRequestStatus(
                    $requester,
                    $eventRequest->title,
                    $level,
                    $status
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the approval process
            \Log::error('Notification failed: '.$e->getMessage());
        }
    }

    // Reject request
    public function reject(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);
        $user = auth()->user();

        // Determine which level is rejecting
        $rejectLevel = 0;
        $rejectRole = 'Unknown';

        if ($user->isProgramHead()) {
            $rejectLevel = 1;
            $rejectRole = 'Program Head';
        } elseif ($user->isAcademicHead()) {
            $rejectLevel = 2;
            $rejectRole = 'Academic Head';
        } elseif ($user->isBuildingAdmin()) {
            $rejectLevel = 3;
            $rejectRole = 'Building Admin';
        } elseif ($user->isSchoolAdmin() || $user->isAdmin()) {
            $rejectLevel = 4;
            $rejectRole = 'School Admin';
        }

        $eventRequest->status = 'Rejected';
        $eventRequest->approved_by = $user->id;
        $eventRequest->approved_at = now();
        $eventRequest->approval_level = $rejectLevel;
        $eventRequest->notes = $request->notes;

        $history = $eventRequest->approval_history ?? [];
        $history[] = [
            'level' => $rejectLevel,
            'role' => $rejectRole,
            'approver' => $user->name,
            'at' => now()->toDateTimeString(),
            'notes' => $request->notes,
            'action' => 'rejected',
        ];
        $eventRequest->approval_history = $history;
        $eventRequest->save();

        ActivityLog::log(
            'event_rejected',
            'Event rejected by '.$rejectRole.': '.$eventRequest->title,
            null
        );

        // Notify the requester about rejection
        $this->sendApprovalNotification($eventRequest, $rejectLevel, 'Rejected');

        return back()->with('success', 'Event request rejected!');
    }

    // Show all approved events (calendar view)
    public function calendar(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;
        $view = $request->input('view', 'calendar');

        // Get filters
        $filterTypes = $request->input('filter_types', []);
        $filterLocation = $request->input('filter_location', '');

        // Get month and year for prev/next navigation
        $prevDate = now()->create($year.'-'.$month.'-01')->subMonth();
        $nextDate = now()->create($year.'-'.$month.'-01')->addMonth();

        $prevMonth = $prevDate->month;
        $prevYear = $prevDate->year;
        $nextMonth = $nextDate->month;
        $nextYear = $nextDate->year;

        $monthName = now()->create($year.'-'.$month.'-01')->format('F');

        // Get all approved events for the month
        $eventRequests = EventRequest::with('user')
            ->where('status', 'Approved')
            ->whereMonth('event_date', $month)
            ->whereYear('event_date', $year)
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($event) {
                $event->type = 'event';
                $event->category_color = '#3788d8'; // Blue for events
                $event->event_date_string = $event->event_date->format('Y-m-d');
                $event->requested_by = optional($event->user)->name ?? 'Unknown';

                return $event;
            });

        // Get approved facility requests
        $facilityRequests = FacilityRequest::with('user')
            ->where('status', 'Approved')
            ->whereMonth('event_date', $month)
            ->whereYear('event_date', $year)
            ->orderBy('event_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($facility) {
                $facility->type = 'facility';
                $facility->title = $facility->event_title.' ('.$facility->facility.')';
                $facility->category_color = '#28a745'; // Green for facilities
                $facility->event_date_string = $facility->event_date->format('Y-m-d');
                $facility->requested_by = optional($facility->user)->name ?? 'Unknown';

                return $facility;
            });

        // Get resolved reports
        $reports = Report::with('user')->whereNotNull('resolved_at')
            ->whereMonth('resolved_at', $month)
            ->whereYear('resolved_at', $year)
            ->orderBy('resolved_at', 'asc')
            ->get()
            ->map(function ($report) {
                $report->type = 'maintenance';
                $report->title = 'Maintenance: '.($report->damaged_part ?? 'Repair');
                $report->event_date = $report->resolved_at->toDateString();
                $report->start_time = null;
                $report->end_time = null;
                $report->category_color = '#dc3545'; // Red for maintenance
                $report->event_date_string = $report->resolved_at->format('Y-m-d');
                $report->requested_by = optional($report->user)->name ?? 'Maintenance';

                return $report;
            });

        // Get resolved concerns
        $concerns = Concern::with('user')->whereNotNull('resolved_at')
            ->whereMonth('resolved_at', $month)
            ->whereYear('resolved_at', $year)
            ->orderBy('resolved_at', 'asc')
            ->get()
            ->map(function ($concern) {
                $concern->type = 'maintenance';
                $concern->title = 'Maintenance: '.($concern->damaged_part ?? 'Concern');
                $concern->event_date = $concern->resolved_at->toDateString();
                $concern->start_time = null;
                $concern->end_time = null;
                $concern->category_color = '#dc3545'; // Red for maintenance
                $concern->event_date_string = $concern->resolved_at->format('Y-m-d');
                $concern->requested_by = optional($concern->user)->name ?? 'Maintenance';

                return $concern;
            });

        // Combine all events
        // Ensure all event_date are strings
        $eventRequests->transform(function ($event) {
            $event->event_date = $event->event_date->toDateString();

            return $event;
        });
        $facilityRequests->transform(function ($facility) {
            $facility->event_date = $facility->event_date->toDateString();

            return $facility;
        });

        $eventsWithColors = $eventRequests->concat($facilityRequests)->concat($reports)->concat($concerns);

        // Get all locations for filter
        $allLocations = $eventsWithColors->pluck('location')->unique()->filter()->sort();

        // Apply filters
        $events = $eventsWithColors;
        if (! empty($filterTypes)) {
            $events = $events->filter(function ($event) use ($filterTypes) {
                return in_array($event->type, $filterTypes);
            });
        }

        if (! empty($filterLocation)) {
            $events = $events->filter(function ($event) use ($filterLocation) {
                return $event->location == $filterLocation;
            });
        }

        // Build calendar grid
        $calendarDays = [];
        $firstDayOfMonth = now()->create($year.'-'.$month.'-01');
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        $startDayOfWeek = $firstDayOfMonth->dayOfWeek; // 0 = Sunday

        $today = now()->toDateString();

        // Create empty weeks
        $currentWeek = [];

        // Add empty days for the start of the month
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $currentWeek[] = ['day' => 0, 'isToday' => false, 'hasEvent' => false, 'events' => collect([])];
        }

        // Add days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
            $dayEvents = $events->filter(function ($event) use ($dateString) {
                return $event->event_date === $dateString;
            });

            $currentWeek[] = [
                'day' => $day,
                'isToday' => $dateString === $today,
                'hasEvent' => $dayEvents->count() > 0,
                'events' => $dayEvents,
            ];

            // If we've reached the end of a week (Saturday), start a new week
            if (count($currentWeek) === 7) {
                $calendarDays[] = $currentWeek;
                $currentWeek = [];
            }
        }

        // Add empty days for the end of the month and pad the last week
        if (count($currentWeek) > 0) {
            while (count($currentWeek) < 7) {
                $currentWeek[] = ['day' => 0, 'isToday' => false, 'hasEvent' => false, 'events' => collect([])];
            }
            $calendarDays[] = $currentWeek;
        }

        // Sorted events for list view
        $sortedEvents = $events->sortBy(['event_date', 'start_time']);

        return view('events.calendar', compact('events', 'month', 'year', 'monthName', 'prevMonth', 'prevYear', 'nextMonth', 'nextYear', 'calendarDays', 'filterTypes', 'filterLocation', 'allLocations', 'view', 'sortedEvents'));
    }

    // Import events from CSV
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');
        $header = fgetcsv($handle);

        $importedCount = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            try {
                $data = array_combine($header, $row);

                EventRequest::create([
                    'user_id' => auth()->id(),
                    'title' => $data['title'] ?? 'Untitled Event',
                    'description' => $data['description'] ?? '',
                    'event_date' => $data['event_date'] ?? now()->toDateString(),
                    'start_time' => $data['start_time'] ?? '09:00',
                    'end_time' => $data['end_time'] ?? '10:00',
                    'location' => $data['location'] ?? '',
                    'category' => $data['category'] ?? 'event',
                    'status' => 'Pending', // Default to pending for imported events
                ]);

                $importedCount++;
            } catch (\Exception $e) {
                $errors[] = 'Error importing row: failed to process record.';
            }
        }

        fclose($handle);

        if ($importedCount > 0) {
            return redirect()->route('events.calendar')->with('success', "Successfully imported $importedCount events!");
        } else {
            return redirect()->route('events.calendar')->with('error', 'No events were imported. Please check your CSV file format.');
        }
    }

    // API: Get events for calendar (JSON)
    public function calendarEvents(Request $request)
    {
        $calendarEvents = collect();

        // Event Requests
        $eventQuery = EventRequest::where('status', 'Approved');
        if ($request->start) {
            $eventQuery->whereDate('event_date', '>=', $request->start);
        }
        if ($request->end) {
            $eventQuery->whereDate('event_date', '<=', $request->end);
        }
        $events = $eventQuery->with('user')->orderBy('event_date', 'asc')->get();

        $eventCalendarEvents = $events->map(function ($event) {
            return [
                'id' => 'event_'.$event->id,
                'title' => $event->title,
                'start' => $event->event_date->format('Y-m-d').'T'.$event->start_time,
                'end' => $event->event_date->format('Y-m-d').'T'.$event->end_time,
                'backgroundColor' => '#3788d8', // Blue for events
                'borderColor' => '#3788d8',
                'extendedProps' => [
                    'type' => 'event',
                    'category' => $event->category,
                    'location' => $event->location,
                    'description' => $event->description,
                    'requestedBy' => $event->user->name ?? 'Unknown',
                ],
            ];
        });

        // Facility Requests
        $facilityQuery = FacilityRequest::where('status', 'Approved');
        if ($request->start) {
            $facilityQuery->whereDate('event_date', '>=', $request->start);
        }
        if ($request->end) {
            $facilityQuery->whereDate('event_date', '<=', $request->end);
        }
        $facilities = $facilityQuery->with('user')->orderBy('event_date', 'asc')->get();

        $facilityCalendarEvents = $facilities->map(function ($facility) {
            return [
                'id' => 'facility_'.$facility->id,
                'title' => $facility->event_title.' ('.$facility->facility.')',
                'start' => $facility->event_date->format('Y-m-d').'T'.$facility->start_time,
                'end' => $facility->event_date->format('Y-m-d').'T'.$facility->end_time,
                'backgroundColor' => '#28a745', // Green for facilities
                'borderColor' => '#28a745',
                'extendedProps' => [
                    'type' => 'facility',
                    'facility' => $facility->facility,
                    'location' => $facility->facility,
                    'description' => $facility->description,
                    'requestedBy' => $facility->user->name ?? 'Unknown',
                ],
            ];
        });

        // Maintenance Reports
        $reportQuery = Report::whereNotNull('resolved_at');
        if ($request->start) {
            $reportQuery->whereDate('resolved_at', '>=', $request->start);
        }
        if ($request->end) {
            $reportQuery->whereDate('resolved_at', '<=', $request->end);
        }
        $reports = $reportQuery->orderBy('resolved_at', 'asc')->get();

        $reportCalendarEvents = $reports->map(function ($report) {
            $date = $report->resolved_at->format('Y-m-d');

            return [
                'id' => 'report_'.$report->id,
                'title' => 'Maintenance: '.($report->damaged_part ?? 'Repair'),
                'start' => $date.'T09:00', // Default time
                'end' => $date.'T10:00',
                'backgroundColor' => '#dc3545', // Red for maintenance
                'borderColor' => '#dc3545',
                'extendedProps' => [
                    'type' => 'maintenance',
                    'location' => $report->location,
                    'description' => 'Report resolved',
                    'requestedBy' => 'Maintenance',
                ],
            ];
        });

        // Maintenance Concerns
        $concernQuery = Concern::whereNotNull('resolved_at');
        if ($request->start) {
            $concernQuery->whereDate('resolved_at', '>=', $request->start);
        }
        if ($request->end) {
            $concernQuery->whereDate('resolved_at', '<=', $request->end);
        }
        $concerns = $concernQuery->orderBy('resolved_at', 'asc')->get();

        $concernCalendarEvents = $concerns->map(function ($concern) {
            $date = $concern->resolved_at->format('Y-m-d');

            return [
                'id' => 'concern_'.$concern->id,
                'title' => 'Maintenance: '.($concern->damaged_part ?? 'Concern'),
                'start' => $date.'T09:00',
                'end' => $date.'T10:00',
                'backgroundColor' => '#dc3545', // Red for maintenance
                'borderColor' => '#dc3545',
                'extendedProps' => [
                    'type' => 'maintenance',
                    'location' => $concern->location,
                    'description' => 'Concern resolved',
                    'requestedBy' => 'Maintenance',
                ],
            ];
        });

        $calendarEvents = $eventCalendarEvents->concat($facilityCalendarEvents)->concat($reportCalendarEvents)->concat($concernCalendarEvents);

        return response()->json($calendarEvents);
    }

    // Cancel request
    public function cancel($id)
    {
        $eventRequest = EventRequest::findOrFail($id);

        // Only owner can cancel
        if ($eventRequest->user_id !== auth()->id() && ! auth()->user()->canApproveRequests()) {
            return back()->with('error', 'You cannot cancel this request.');
        }

        $eventRequest->status = 'Cancelled';
        $eventRequest->save();

        ActivityLog::log(
            'event_cancelled',
            'Event cancelled: '.$eventRequest->title,
            null
        );

        return back()->with('success', 'Event request cancelled.');
    }

    // Delete event request (soft delete - moves to deleted events)
    public function delete(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);

        // Allow owner or admin to delete
        if ($eventRequest->user_id !== auth()->id() && ! auth()->user()->canApproveRequests()) {
            return back()->with('error', 'You cannot delete this event request.');
        }

        // Find or create the deleted events folder
        $deletedFolder = ArchiveFolder::where('name', 'Deleted Events')
            ->whereIn('type', ['events', 'mixed'])
            ->first();

        if (! $deletedFolder) {
            $deletedFolder = ArchiveFolder::create([
                'name' => 'Deleted Events',
                'type' => 'events',
                'description' => 'Deleted event requests',
                'is_system' => true,
            ]);
        }

        // Move to deleted folder
        $eventRequest->archive_folder_id = $deletedFolder->id;
        $eventRequest->is_deleted = true;
        $eventRequest->deleted_by = auth()->id();
        $eventRequest->save();

        ActivityLog::log(
            'event_deleted',
            'Event deleted: '.$eventRequest->title,
            null
        );

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event request deleted.']);
        }

        return back()->with('success', 'Event request deleted.');
    }

    // Archive event request
    public function archive(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);
        $user = auth()->user();

        // Owner or admin can archive
        if ($eventRequest->user_id !== $user->id && ! $user->canApproveRequests()) {
            return back()->with('error', 'You cannot archive this event request.');
        }

        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $eventRequest->getFillable())) {
            return back()->with('error', 'Invalid role for archiving.');
        }

        // Check if already archived by this role
        if ($eventRequest->$archiveColumn) {
            return back()->with('error', 'This event is already archived by your role.');
        }

        // Set role-specific archive column to true (role-based archiving)
        $eventRequest->update([$archiveColumn => true]);

        // Also add to user's archive using pivot table (user-based archiving)
        $folderName = $request->archive_folder_name ?? 'My Archive';
        $eventRequest->archivedByUsers()->attach($user->id, [
            'archived_at' => now(),
            'archive_folder_name' => $folderName,
        ]);

        ActivityLog::log(
            'event_archived',
            'Event archived: '.$eventRequest->title,
            null
        );

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event request archived successfully.']);
        }

        return back()->with('success', 'Event request archived successfully.');
    }

    // Restore event request - users can only restore their OWN archived event requests
    public function restore($id)
    {
        $eventRequest = EventRequest::findOrFail($id);
        $user = auth()->user();

        // Only owner or admin can restore
        if ($eventRequest->user_id !== $user->id && ! $user->canApproveRequests()) {
            return back()->with('error', 'You cannot restore this event request.');
        }

        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $eventRequest->getFillable())) {
            return back()->with('error', 'Invalid role for restoring.');
        }

        // Set role-specific archive column to false (role-based restoring)
        $eventRequest->update([$archiveColumn => false]);

        // Also remove from user's archive using pivot table (user-based restoring)
        $eventRequest->archivedByUsers()->detach($user->id);

        ActivityLog::log(
            'event_restored',
            'Event restored: '.$eventRequest->title,
            null
        );

        return back()->with('success', 'Event request restored successfully.');
    }

    // Show all event requests (for admin)
    public function adminIndex(Request $request)
    {
        $viewType = $request->view ?? 'active';

        if ($viewType === 'archives') {
            // Show events archived by any role
            $archivedEvents = EventRequest::where('is_deleted', false)
                ->where(function ($q) {
                    $q->where('student_archived', true)
                        ->orWhere('faculty_archived', true)
                        ->orWhere('building_admin_archived', true)
                        ->orWhere('school_admin_archived', true)
                        ->orWhere('academic_head_archived', true)
                        ->orWhere('program_head_archived', true)
                        ->orWhere('mis_archived', true)
                        ->orWhere('maintenance_archived', true);
                })
                ->with('user')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.events', [
                'viewType' => $viewType,
                'archivedEvents' => $archivedEvents,
                'requests' => collect(),
            ]);
        }

        if ($viewType === 'deleted') {
            // Show deleted events
            $deletedFolder = ArchiveFolder::where('name', 'Deleted Events')->first();
            $user = auth()->user();
            $days = $request->get('days', $user->event_requests_auto_delete_days ?? 15);

            if ($deletedFolder) {
                $deletedEvents = EventRequest::where('archive_folder_id', $deletedFolder->id)
                    ->where('is_deleted', true)
                    ->where('updated_at', '<=', now()->subDays($days))
                    ->with(['user', 'deletedBy'])
                    ->orderBy('updated_at', 'desc')
                    ->get();
            } else {
                $deletedEvents = collect();
            }

            return view('admin.events', [
                'viewType' => $viewType,
                'deletedEvents' => $deletedEvents,
                'requests' => collect(),
                'days' => $days,
            ]);
        }

        // For active events: show events not archived by any role
        $query = EventRequest::with('user')
            ->where('is_deleted', false)
            ->where('student_archived', false)
            ->where('faculty_archived', false)
            ->where('building_admin_archived', false)
            ->where('school_admin_archived', false)
            ->where('academic_head_archived', false)
            ->where('program_head_archived', false)
            ->where('mis_archived', false)
            ->where('maintenance_archived', false);

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->category) {
            $query->where('category', $request->category);
        }

        // Filter by date range
        if ($request->date_from) {
            $query->whereDate('event_date', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }

        // Search by title
        if ($request->search) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return view('admin.events', compact('requests', 'viewType'));
    }

    // Generate PDF for approved event request
    public function generatePdf($id)
    {
        $eventRequest = EventRequest::with(['user', 'approver'])->findOrFail($id);

        // Only allow PDF generation for approved requests
        if ($eventRequest->status !== 'Approved') {
            return back()->with('error', 'PDF can only be generated for approved requests.');
        }

        // Get approver names from approval history (remove duplicates)
        $approvers = [];
        $seenNames = [];
        $history = $eventRequest->approval_history ?? [];
        foreach ($history as $h) {
            $name = $h['approver'] ?? 'Unknown';
            $role = $h['role'] ?? 'Unknown';

            // Skip if we've already seen this name and role combination
            if (! isset($seenNames[$name.'-'.$role])) {
                $approvers[] = [
                    'level' => $h['level'],
                    'role' => $role,
                    'name' => $name,
                    'date' => isset($h['at']) ? \Carbon\Carbon::parse($h['at'])->format('M d, Y h:i A') : 'N/A',
                ];
                $seenNames[$name.'-'.$role] = true;
            }
        }

        // Also check level-based approval fields for Building Admin
        if ($eventRequest->approved_by_level_1) {
            $buildingAdmin = \App\Models\User::find($eventRequest->approved_by_level_1);
            if ($buildingAdmin) {
                $name = $buildingAdmin->name;
                // Only add if not already in list
                $found = false;
                foreach ($approvers as $a) {
                    if ($a['name'] === $name) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    $approvers[] = [
                        'level' => '1',
                        'role' => 'Building Admin',
                        'name' => $name,
                        'date' => $eventRequest->approved_at_level_1 ? \Carbon\Carbon::parse($eventRequest->approved_at_level_1)->format('M d, Y h:i A') : 'N/A',
                    ];
                }
            }
        }

        // Check level 2 for Academic Head
        if ($eventRequest->approved_by_level_2) {
            $academicHead = \App\Models\User::find($eventRequest->approved_by_level_2);
            if ($academicHead) {
                $name = $academicHead->name;
                // Only add if not already in list
                $found = false;
                foreach ($approvers as $a) {
                    if ($a['name'] === $name) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    $approvers[] = [
                        'level' => '2',
                        'role' => 'Academic Head',
                        'name' => $name,
                        'date' => $eventRequest->approved_at_level_2 ? \Carbon\Carbon::parse($eventRequest->approved_at_level_2)->format('M d, Y h:i A') : 'N/A',
                    ];
                }
            }
        }

        // Check level 3 for additional approvers
        if ($eventRequest->approved_by_level_3) {
            $level3Approver = \App\Models\User::find($eventRequest->approved_by_level_3);
            if ($level3Approver) {
                $name = $level3Approver->name;
                // Only add if not already in list
                $found = false;
                foreach ($approvers as $a) {
                    if ($a['name'] === $name) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    $approvers[] = [
                        'level' => '3',
                        'role' => 'Additional Approver',
                        'name' => $name,
                        'date' => $eventRequest->approved_at_level_3 ? \Carbon\Carbon::parse($eventRequest->approved_at_level_3)->format('M d, Y h:i A') : 'N/A',
                    ];
                }
            }
        }

        // Get final approver (School Admin)
        $finalApprover = $eventRequest->approver;

        $pdf = \PDF::loadView('events.pdf', [
            'eventRequest' => $eventRequest,
            'requester' => $eventRequest->user,
            'finalApprover' => $finalApprover,
            'approvers' => $approvers,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('event-request-'.$eventRequest->id.'.pdf');
    }

    // ============ API METHODS ============

    private function eventUserCanApprove($user): bool
    {
        return $user && (method_exists($user, 'canApproveRequests') ? $user->canApproveRequests() : false);
    }

    private function eventCanView(EventRequest $event, $user): bool
    {
        return $event->user_id === $user->id || $this->eventUserCanApprove($user);
    }

    private function eventApiSummary(EventRequest $event): array
    {
        return [
            'id' => $event->id,
            'title' => $event->title,
            'event_date' => optional($event->event_date)->toDateString(),
            'location' => $event->location,
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'category' => $event->category,
            'department' => $event->department,
            'priority' => $event->priority,
            'status' => $event->status,
            'approval_level' => $event->approval_level,
            'created_at' => optional($event->created_at)->toIso8601String(),
            'updated_at' => optional($event->updated_at)->toIso8601String(),
        ];
    }

    private function eventApiDetail(EventRequest $event, $user): array
    {
        $data = $this->eventApiSummary($event);
        $data['description'] = $event->description;
        $data['room_number'] = $event->room_number;
        $data['area_of_use'] = $event->area_of_use;
        $data['other_category'] = $event->other_category;

        if ($event->user_id === $user->id || $this->eventUserCanApprove($user)) {
            $data['notes'] = $event->notes;
        }

        if ($this->eventUserCanApprove($user)) {
            $data['requester'] = [
                'id' => $event->user?->id,
                'name' => $event->user?->name,
                'role' => $event->user?->role,
            ];
        }

        return $data;
    }

    /**
     * API: List events
     */
    public function apiIndex(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'faculty' && ! $this->eventUserCanApprove($user)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:Pending,Approved,Rejected,Cancelled',
            'category' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $perPage = min((int) ($validated['per_page'] ?? ($user->items_per_page ?? 10)), 50);
        if ($perPage < 1) {
            $perPage = 10;
        }

        $query = EventRequest::query()
            ->with('user:id,name,role')
            ->where('is_deleted', false);

        if ($this->eventUserCanApprove($user)) {
            if (! empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (! empty($validated['category'])) {
                $query->where('category', $validated['category']);
            }

            if (! empty($validated['date_from'])) {
                $query->whereDate('event_date', '>=', $validated['date_from']);
            }

            if (! empty($validated['date_to'])) {
                $query->whereDate('event_date', '<=', $validated['date_to']);
            }
        } else {
            $query->where('user_id', $user->id);

            if (! empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (! empty($validated['category'])) {
                $query->where('category', $validated['category']);
            }

            if (! empty($validated['date_from'])) {
                $query->whereDate('event_date', '>=', $validated['date_from']);
            }

            if (! empty($validated['date_to'])) {
                $query->whereDate('event_date', '<=', $validated['date_to']);
            }
        }

        $events = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $events->getCollection()->map(function ($event) {
                return $this->eventApiSummary($event);
            }),
            'meta' => [
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
                'per_page' => $events->perPage(),
                'total' => $events->total(),
            ],
        ]);
    }

    /**
     * API: Create event
     */
    public function apiStore(Request $request)
    {
        if ($request->user()->role !== 'faculty') {
            return response()->json(['error' => 'Faculty only'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|min:3|max:255',
            'description' => 'required|string|min:10|max:5000',
            'event_date' => 'required|date|after_or_equal:today',
            'location' => 'required|string|min:3|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'category' => 'required|in:event,meeting,activity,training,other',
            'department' => 'nullable|in:GE,ICT,Business Management,THM',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $event = EventRequest::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'event_date' => $validated['event_date'],
            'location' => $validated['location'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'category' => $validated['category'],
            'department' => $validated['department'] ?? null,
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'Pending',
            'approval_level' => EventRequest::LEVEL_NONE,
        ]);

        return response()->json(['event' => $this->eventApiDetail($event->load('user:id,name,role'), $request->user())], 201);
    }

    /**
     * API: Show event
     */
    public function apiShow(Request $request, $id)
    {
        $user = $request->user();
        $event = EventRequest::with('user:id,name,role')
            ->where('is_deleted', false)
            ->findOrFail($id);

        if (! $this->eventCanView($event, $user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['event' => $this->eventApiDetail($event, $user)]);
    }

    public function checkRoomAvailability(Request $request)
    {
        $request->validate([
            'room_number' => 'required|string',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $roomNumber = $request->room_number;
        $eventDate = $request->event_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        // Check for conflicting events
        $conflictingEvents = EventRequest::where('room_number', $roomNumber)
            ->where('event_date', $eventDate)
            ->where('status', 'Approved')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime) {
                    // New event starts during existing event
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($endTime) {
                    // New event ends during existing event
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New event completely encompasses existing event
                    $q->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // Existing event completely encompasses new event
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            })
            ->get();

        $available = $conflictingEvents->isEmpty();

        return response()->json([
            'available' => $available,
            'conflicting_events' => $conflictingEvents->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'user' => $event->user->name ?? 'Unknown',
                ];
            }),
        ]);
    }

    public function checkCourtAvailability(Request $request)
    {
        $request->validate([
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $eventDate = $request->event_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        // Check for conflicting court events
        $conflictingEvents = EventRequest::with('user')
            ->where('location', 'LIKE', 'Court%')
            ->where('event_date', $eventDate)
            ->where('status', 'Approved')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime) {
                    // New event starts during existing event
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($endTime) {
                    // New event ends during existing event
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New event completely encompasses existing event
                    $q->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // Existing event completely encompasses new event
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            })
            ->get();

        $available = $conflictingEvents->isEmpty();

        return response()->json([
            'available' => $available,
            'conflicting_events' => $conflictingEvents->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'user' => $event->user->name ?? 'Unknown',
                ];
            }),
        ]);
    }

    public function checkAvrAvailability(Request $request)
    {
        $request->validate([
            'avr_selection' => 'required|in:AVR 1,AVR 2',
            'event_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $avrSelection = $request->avr_selection;
        $eventDate = $request->event_date;
        $startTime = $request->start_time;
        $endTime = $request->end_time;

        // Check for conflicting AVR events
        $conflictingEvents = EventRequest::with('user')
            ->where('location', 'LIKE', 'AVR%')
            ->where('location', 'LIKE', "%{$avrSelection}%")
            ->where('event_date', $eventDate)
            ->where('status', 'Approved')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime) {
                    // New event starts during existing event
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($endTime) {
                    // New event ends during existing event
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // New event completely encompasses existing event
                    $q->where('start_time', '>=', $startTime)
                        ->where('end_time', '<=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    // Existing event completely encompasses new event
                    $q->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            })
            ->get();

        $available = $conflictingEvents->isEmpty();

        return response()->json([
            'available' => $available,
            'conflicting_events' => $conflictingEvents->map(function ($event) {
                return [
                    'title' => $event->title,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'user' => $event->user->name ?? 'Unknown',
                ];
            }),
        ]);
    }
}