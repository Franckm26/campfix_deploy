<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Concern;
use App\Models\EventRequest;
use App\Models\EventApprovalChain;
use App\Models\EventEducationLevel;
use App\Models\EventRequestType;
use App\Models\EventIntendedUser;
use App\Models\EventDepartment;
use App\Models\FacilityRequest;
use App\Models\Report;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class EventRequestController extends Controller
{
    public static function rejectExpiredPendingRequests(): int
    {
        $expiredRequests = EventRequest::with('user')
            ->where('status', EventRequest::STATUS_PENDING)
            ->whereDate('event_date', '<', now()->toDateString())
            ->get();

        if ($expiredRequests->isEmpty()) {
            return 0;
        }

        $notificationService = new NotificationService;
        $rejectedCount = 0;

        foreach ($expiredRequests as $eventRequest) {
            try {
                $level = (int) ($eventRequest->approval_level ?: EventRequest::LEVEL_NONE);
                $eventName = $eventRequest->location.' - '.\Carbon\Carbon::parse($eventRequest->event_date)->format('M d, Y');
                $notes = 'Automatically rejected because the event date has already passed.';
                $history = $eventRequest->approval_history ?? [];
                $history[] = [
                    'level' => $level,
                    'role' => 'System',
                    'approver' => 'System',
                    'approver_id' => null,
                    'at' => now()->toDateTimeString(),
                    'notes' => $notes,
                    'action' => 'rejected',
                    'status' => 'expired',
                ];

                $eventRequest->status = EventRequest::STATUS_REJECTED;
                $eventRequest->approved_at = now();
                $eventRequest->approval_level = $level;
                $eventRequest->notes = $notes;
                $eventRequest->approval_history = $history;
                $eventRequest->save();

                ActivityLog::log(
                    'event_auto_rejected_expired',
                    "Event request '{$eventName}' (ID: {$eventRequest->id}) automatically rejected because the event date has already passed.",
                    $eventRequest->id,
                    'event_request'
                );

                if ($eventRequest->user) {
                    $notificationService->notifyEventRequestStatus(
                        $eventRequest->user,
                        $eventName,
                        max(1, (int) ($level ?: 1)),
                        'Expired',
                        $eventRequest
                    );
                }

                $rejectedCount++;
            } catch (\Throwable $e) {
                Log::error('Failed to auto-reject expired event request', [
                    'event_request_id' => $eventRequest->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $rejectedCount;
    }

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
        try {
            // The modal's visible location inputs are area_of_use / avr_selection.
            // Normalize them before validation so a missing client-side sync cannot reject a valid request.
            if (! filled($request->input('location'))) {
                $request->merge([
                    'location' => $request->input('area_of_use') ?: $request->input('avr_selection'),
                ]);
            }

            $allowedRoles = ['faculty', 'school_admin', 'academic_head', 'program_head', 'building_admin'];
            if (!in_array(auth()->user()->role, $allowedRoles)) {
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => 'You do not have permission to create event requests.'], 403);
                }
                return redirect('/dashboard')->with('error', 'You do not have permission to create event requests.');
            }

            // Check daily submission limit (5 per day)
            $userId = auth()->id();
            if (\App\Models\UserRateLimit::hasExceededLimit($userId, 'submission', 5)) {
                $remaining = \App\Models\UserRateLimit::getRemainingAttempts($userId, 'submission', 5);
                
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have reached your daily submission limit of 5 event requests. Please try again tomorrow.',
                        'remaining_attempts' => $remaining
                    ], 429);
                }
                
                return redirect()->back()->with('error', 'You have reached your daily submission limit of 5 event requests. Please try again tomorrow.');
            }
            
            $validatedData = $request->validate([
                'description' => 'required|string',
                'event_date' => 'required|date|after_or_equal:today',
                'location' => 'required|string|min:3|max:255',
                'start_time' => 'required',
                'end_time' => 'required|after:start_time',
                'category' => 'required|in:Area Use',
                'request_type' => 'required|string|max:100',
                'area_of_use' => 'required_if:category,Area Use|string',
                'room_number' => 'nullable|string',
                'department' => 'nullable|string|max:100',
                'education_level' => 'required|string|max:100',
                'intended_user' => 'nullable|string|max:100',
                'picture' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            ], [
                'description.required' => 'The description is required.',
                'event_date.required' => 'The event date is required.',
                'event_date.after_or_equal' => 'The event date cannot be in the past.',
                'location.required' => 'The location is required.',
                'location.min' => 'The location must be at least 3 characters.',
                'start_time.required' => 'The start time is required.',
                'end_time.required' => 'The end time is required.',
                'end_time.after' => 'The end time must be after the start time.',
                'category.required' => 'Please select a category.',
                'category.in' => 'Please select a valid category.',
                'request_type.required' => 'Please select a request type.',
                'request_type.in' => 'Please select a valid request type (Academic or Non-Academic).',
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

            $requestType = EventRequestType::where('name', $request->request_type)->where('is_active', true)->first();
            $intendedUserCode = $request->intended_user ?: $request->education_level;
            $intendedUser = EventIntendedUser::where('code', $intendedUserCode)->where('is_active', true)->first();
            $educationLevelRecord = Schema::hasTable('event_education_levels')
                ? EventEducationLevel::where('code', $request->education_level)->where('is_active', true)->first()
                : null;
            if (! $requestType || ! $intendedUser || (Schema::hasTable('event_education_levels') && ! $educationLevelRecord)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['request_type' => 'Please select an active education level, request type, and intended user.']);
            }
            $educationLevel = $educationLevelRecord?->code ?? $request->education_level;
            $isFacultyIntended = false;
            $isShsIntended = $educationLevel === 'shs';
            $configuredChain = $educationLevelRecord && Schema::hasTable('event_approval_chains')
                ? EventApprovalChain::where('event_education_level_id', $educationLevelRecord->id)
                    ->where('event_request_type_id', $requestType->id)->first()
                : null;
            // Specific intended-user overrides take precedence, followed by the
            // education-level/request-type combination, then the request-type default.
            $approvalRoute = array_values($intendedUser->approval_roles ?: ($configuredChain?->approval_roles ?: ($requestType->approval_roles ?? [])));
            $requiresDepartment = in_array('program_head', $approvalRoute, true);
            if ($requiresDepartment && ! EventDepartment::where('name', $request->department)->where('is_active', true)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages(['department' => 'Please select an active department for this approval chain.']);
            }

            // Determine initial approval level based on request type
            // Non-Academic: starts at Building Admin (level 3)
            // Academic: starts at Program Head (level 1), except SHS where level 1 is Principal Assistant
            $initialApprovalLevel = EventRequest::LEVEL_NONE;
            if (!$isFacultyIntended && $approvalRoute) $initialApprovalLevel = 1;

            $eventRequest = EventRequest::create([
                'user_id' => auth()->id(),
                'description' => $request->description,
                'event_date' => $request->event_date,
                'location' => $request->location,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'category' => $request->category,
                'request_type' => $request->request_type,
                'other_category' => $request->other_category,
                'department' => $requiresDepartment ? $request->department : null,
                'education_level' => $educationLevel,
                ...(Schema::hasColumn('event_requests', 'intended_user') ? ['intended_user' => $intendedUser->code] : []),
                // Kept internally for the current non-null database column; it is no longer shown or chosen by users.
                'priority' => 'medium',
                // Faculty-intended requests are auto-approved; others go through the approval chain
                'status' => $isFacultyIntended ? 'Approved' : 'Pending',
                'approval_level' => $isFacultyIntended ? EventRequest::LEVEL_APPROVED : $initialApprovalLevel,
                'approval_route' => $approvalRoute,
                'approved_at' => $isFacultyIntended ? now() : null,
                'materials_needed' => $materialsNeeded,
                'image_path' => $imagePath,
            ]);

            $user = auth()->user();

            ActivityLog::log(
                'event_request_created',
                "New event request submitted: '{$eventRequest->title}' (ID: {$eventRequest->id}) by {$user->name} ({$user->role}) - Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A') . ", Attendees: {$eventRequest->expected_attendees}, Audience: {$eventRequest->intended_for}",
                null
            );

            // Increment submission counter
            \App\Models\UserRateLimit::incrementCounter(auth()->id(), 'submission');

            $notificationService = new NotificationService;

            if ($isFacultyIntended) {
                // Faculty-intended: no approval needed — notify building admin and school admin only
                try {
                    $notificationService->notifyAdminsOfFacultyRequest($eventRequest);
                } catch (\Exception $notifEx) {
                    \Log::warning('Failed to send notifications: ' . $notifEx->getMessage());
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Facility request submitted successfully! Building Admin and School Admin have been notified.',
                        'redirect' => route('events.my', ['view' => 'approved'])
                    ]);
                }
                return redirect()->route('events.my', ['view' => 'approved'])->with('success', 'Facility request submitted successfully! Building Admin and School Admin have been notified.');
            }

            // Auto-approve for the requester if they are also an approver
            $autoApproved = false;
            $approvalHistory = [];

            if ($eventRequest->hasConfiguredApprovalRoute()) {
                $requiredRole = $eventRequest->requiredApprovalRole();
                $requesterMatchesCurrentRole = $user->role === $requiredRole
                    || ($requiredRole === 'school_admin' && $user->role === 'admin');
                $requesterMatchesDepartment = $requiredRole !== 'program_head'
                    || ! $user->department
                    || $eventRequest->department === $user->department;

                if ($requesterMatchesCurrentRole && $requesterMatchesDepartment) {
                    $approvalHistory[] = [
                        'level' => 1,
                        'role' => ucwords(str_replace('_', ' ', $requiredRole)),
                        'approver' => $user->name,
                        'approver_id' => $user->id,
                        'at' => now()->toDateTimeString(),
                        'notes' => 'Auto-approved (requester is the first configured approver)',
                    ];
                    $autoApproved = true;
                }
            } else {
                // Legacy requests retain the original fixed-level auto-approval behavior.
                if (! $isShsIntended && $user->isProgramHead()) {
                    $eventRequest->approved_by_level_1 = $user->id;
                    $eventRequest->approved_at_level_1 = now();
                    $approvalHistory[] = [
                        'level' => 1,
                        'role' => 'Program Head',
                        'approver' => $user->name,
                        'approver_id' => $user->id,
                        'at' => now()->toDateTimeString(),
                        'notes' => 'Auto-approved (requester is also approver)',
                    ];
                    $autoApproved = true;
                }

                if ($isShsIntended && $user->isPrincipalAssistant()) {
                    $eventRequest->approved_by_level_1 = $user->id;
                    $eventRequest->approved_at_level_1 = now();
                    $approvalHistory[] = [
                        'level' => 1,
                        'role' => 'Principal Assistant',
                        'approver' => $user->name,
                        'approver_id' => $user->id,
                        'at' => now()->toDateTimeString(),
                        'notes' => 'Auto-approved (requester is also approver)',
                    ];
                    $autoApproved = true;
                }

                // Check if requester is an Academic Head
                if ($user->isAcademicHead()) {
                    $eventRequest->approved_by_level_2 = $user->id;
                    $eventRequest->approved_at_level_2 = now();
                    $approvalHistory[] = [
                        'level' => 2,
                        'role' => 'Academic Head',
                        'approver' => $user->name,
                        'approver_id' => $user->id,
                        'at' => now()->toDateTimeString(),
                        'notes' => 'Auto-approved (requester is also approver)',
                    ];
                    $autoApproved = true;
                }

                // Check if requester is a Building Admin
                if ($user->isBuildingAdmin()) {
                    $eventRequest->approved_by_level_3 = $user->id;
                    $eventRequest->approved_at_level_3 = now();
                    $approvalHistory[] = [
                        'level' => 3,
                        'role' => 'Building Admin',
                        'approver' => $user->name,
                        'approver_id' => $user->id,
                        'at' => now()->toDateTimeString(),
                        'notes' => 'Auto-approved (requester is also approver)',
                    ];
                    $autoApproved = true;
                }

                // Check if requester is a School Admin
                if ($user->isSchoolAdmin() || $user->isAdmin()) {
                    $approvalHistory[] = [
                        'level' => 4,
                        'role' => 'School Admin',
                        'approver' => $user->name,
                        'approver_id' => $user->id,
                        'at' => now()->toDateTimeString(),
                        'notes' => 'Auto-approved (requester is also approver)',
                    ];
                    $autoApproved = true;
                }
            }

            // Save approval history if auto-approved
            if ($autoApproved) {
                $eventRequest->approval_history = $approvalHistory;
                
                // Determine the approval level based on what was auto-approved
                if ($eventRequest->isFullyApproved()) {
                    $eventRequest->status = 'Approved';
                    $eventRequest->approved_by = $user->id;
                    $eventRequest->approved_at = now();
                    $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;
                } else {
                    // Partially approved, determine next level
                    $nextLevel = $eventRequest->getNextApprovalLevel();
                    if ($nextLevel !== null) {
                        $eventRequest->approval_level = $nextLevel;
                    }
                }
                
                $eventRequest->save();

                ActivityLog::log(
                    'event_auto_approved',
                    'Event request auto-approved for requester at their approval level',
                    null
                );
            }

            // Non-faculty: go through the normal multi-level approval chain
            try {
                $notificationService->notifyApproversOfNewEvent($eventRequest);
            } catch (\Exception $notifEx) {
                \Log::warning('Failed to send notifications: ' . $notifEx->getMessage());
            }

            $message = $autoApproved 
                ? 'Event request submitted successfully! Your approval has been automatically recorded. Waiting for other approvers.'
                : 'Event request submitted successfully! Waiting for approval.';

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'redirect' => route('events.my', ['view' => 'active'])
                ]);
            }
            return redirect()->route('events.my', ['view' => 'active'])->with('success', $message);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Event request store error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'An error occurred while creating the event request: ' . $e->getMessage());
        }
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
            $this->recordRequesterApprovalIfNeeded($event);

            return response()->json(['event' => $event]);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Show deleted event (includes soft-deleted events)
    public function showDeleted($id)
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }

        $event = EventRequest::with('user:id,name,role')
            ->where('is_deleted', true)
            ->findOrFail($id);

        // Check if user can view this deleted event
        // Allow if: user owns the event OR user can approve requests
        if ((int)$event->user_id === (int)$user->id || $user->canApproveRequests()) {
            return response()->json(['event' => $event]);
        }

        return response()->json(['error' => 'Unauthorized'], 403);
    }

    private function recordRequesterApprovalIfNeeded(EventRequest $event): void
    {
        if ($event->status !== EventRequest::STATUS_PENDING || ! $event->user) {
            return;
        }

        $requester = $event->user;
        $history = $event->approval_history ?? [];
        $changed = false;

        $recordApproval = function (int $level, string $role, string $field, string $dateField) use ($event, $requester, &$history, &$changed) {
            if ($event->{$field}) {
                return;
            }

            $event->{$field} = $requester->id;
            $event->{$dateField} = now();
            $history[] = [
                'level' => $level,
                'role' => $role,
                'approver' => $requester->name,
                'approver_id' => $requester->id,
                'at' => now()->toDateTimeString(),
                'notes' => 'Auto-approved (requester is also approver)',
            ];
            $changed = true;
        };

        if (($event->education_level ?? 'tertiary') !== 'shs' && $requester->isProgramHead()) {
            $recordApproval(EventRequest::LEVEL_1_PROGRAM_HEAD, 'Program Head', 'approved_by_level_1', 'approved_at_level_1');
        }

        if ($requester->isAcademicHead()) {
            $recordApproval(EventRequest::LEVEL_2_ACADEMIC_HEAD, 'Academic Head', 'approved_by_level_2', 'approved_at_level_2');
        }

        if ($requester->isBuildingAdmin()) {
            $recordApproval(EventRequest::LEVEL_3_BUILDING_ADMIN, 'Building Admin', 'approved_by_level_3', 'approved_at_level_3');
        }

        if (! $changed) {
            return;
        }

        $event->approval_history = $history;

        if ($event->isFullyApproved()) {
            $event->status = EventRequest::STATUS_APPROVED;
            $event->approved_by = $requester->id;
            $event->approved_at = now();
            $event->approval_level = EventRequest::LEVEL_APPROVED;
        } else {
            $event->approval_level = $event->getNextApprovalLevel() ?? $event->approval_level;
        }

        $event->save();
    }

    // Show user's event requests - Only for faculty
    public function myRequests(Request $request)
    {
        self::rejectExpiredPendingRequests();

        $allowedRoles = ['faculty', 'building_admin', 'school_admin', 'academic_head', 'program_head', 'principal_assistant'];

        if (! auth()->user()->canAccess('events')) {
            return redirect('/dashboard')->with('error', 'You do not have permission to view event requests.');
        }

        if (!in_array(auth()->user()->role, $allowedRoles)) {
            return redirect('/dashboard')->with('error', 'You do not have permission to view event requests.');
        }

        $viewType = $request->get('view', 'active'); // 'active', 'approved', 'finished', 'rejected', 'archives', or 'deleted'

        $user = auth()->user();
        $archiveColumn = $user->role.'_archived';
        
        // Get facilities for the modal dropdown
        $facilities = \App\Models\Facility::orderBy('type')->orderBy('name')->get();

        // ========== APPROVED VIEW ==========
        if ($viewType === 'approved') {
            $approvedRequests = EventRequest::where('user_id', Auth::id())
                ->where('is_deleted', false)
                ->where('status', 'Approved')
                ->whereRaw("(event_date + end_time::time) > NOW()")
                ->orderBy('event_date', 'asc')
                ->get();

            return view('events.my', [
                'approvedRequests' => $approvedRequests,
                'viewType' => $viewType,
                'requests' => collect(),
                'finishedRequests' => collect(),
                'rejectedRequests' => collect(),
                'archivedRequests' => collect(),
                'deletedRequests' => collect(),
                'facilities' => $facilities,
            ]);
        }

        // ========== FINISHED VIEW ==========
        if ($viewType === 'finished') {
            $query = EventRequest::where('user_id', Auth::id())
                ->where('is_deleted', false)
                ->where('status', 'Approved')
                ->whereRaw("(event_date + end_time::time) <= NOW()");

            $finishedRequests = $query->orderBy('event_date', 'desc')->get();

            return view('events.my', [
                'finishedRequests' => $finishedRequests,
                'viewType' => $viewType,
                'requests' => collect(),
                'approvedRequests' => collect(),
                'rejectedRequests' => collect(),
                'archivedRequests' => collect(),
                'deletedRequests' => collect(),
                'facilities' => $facilities,
            ]);
        }

        // ========== REJECTED VIEW ==========
        if ($viewType === 'rejected') {
            $query = EventRequest::where('user_id', Auth::id())
                ->where('is_deleted', false)
                ->where('status', 'Rejected');

            $rejectedRequests = $query->orderBy('updated_at', 'desc')->get();

            return view('events.my', [
                'rejectedRequests' => $rejectedRequests,
                'viewType' => $viewType,
                'requests' => collect(),
                'approvedRequests' => collect(),
                'finishedRequests' => collect(),
                'archivedRequests' => collect(),
                'deletedRequests' => collect(),
                'facilities' => $facilities,
            ]);
        }

        // ========== ARCHIVES VIEW ==========
        if ($viewType === 'archives') {
            $query = EventRequest::where('user_id', Auth::id())
                ->where('is_deleted', false)
                ->where($archiveColumn, true);

            // Apply filters
            if ($request->filled('search')) {
                $query->where('description', 'like', '%'.$request->search.'%');
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
                'approvedRequests' => collect(),
                'finishedRequests' => collect(),
                'rejectedRequests' => collect(),
                'deletedRequests' => collect(),
                'facilities' => $facilities,
            ]);
        }

        // ========== DELETED VIEW ==========
        if ($viewType === 'deleted') {
            $query = EventRequest::where('user_id', Auth::id())
                ->where('is_deleted', true);

            // Apply filters
            if ($request->filled('search')) {
                $query->where('description', 'like', '%'.$request->search.'%');
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
                'approvedRequests' => collect(),
                'finishedRequests' => collect(),
                'rejectedRequests' => collect(),
                'archivedRequests' => collect(),
                'facilities' => $facilities,
            ]);
        }

        // ========== ACTIVE VIEW (DEFAULT) ==========
        $query = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', false)
            ->where($archiveColumn, false)
            ->whereNotIn('status', ['Approved', 'Rejected']); // Exclude approved and rejected from active

        // Apply filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
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

        $requests = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Always fetch counts for tab badges
        $approvedRequests = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', false)
            ->where('status', 'Approved')
            ->whereRaw("(event_date + end_time::time) > NOW()")
            ->orderBy('event_date', 'asc')
            ->get();

        $finishedRequests = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', false)
            ->where('status', 'Approved')
            ->whereRaw("(event_date + end_time::time) <= NOW()")
            ->orderBy('event_date', 'desc')
            ->get();

        $rejectedRequests = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', false)
            ->where('status', 'Rejected')
            ->orderBy('updated_at', 'desc')
            ->get();

        $archivedRequests = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', false)
            ->where($archiveColumn, true)
            ->orderBy('updated_at', 'desc')
            ->get();

        $deletedRequests = EventRequest::where('user_id', Auth::id())
            ->where('is_deleted', true)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('events.my', compact('requests', 'viewType', 'approvedRequests', 'finishedRequests', 'rejectedRequests', 'archivedRequests', 'deletedRequests', 'facilities'));
    }

    // Approve request - handles multi-level approval (ALL approvers must approve at each level)
    public function approve(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);
        $user = auth()->user();
        if ($eventRequest->hasConfiguredApprovalRoute()) {
            return $this->approveConfiguredRoute($request, $eventRequest, $user);
        }
        $isShs = ($eventRequest->education_level ?? 'tertiary') === 'shs';

        if ($isShs && $user->isProgramHead()) {
            return back()->with('error', 'Senior High School event requests do not require Program Head approval.');
        }

        // SHS chain: Principal Assistant (level 1) → Academic Head (level 2) → School Admin (final)
        if ($isShs && $user->isPrincipalAssistant()) {
            if (! $eventRequest->canBeApprovedAtLevel(EventRequest::LEVEL_1_PROGRAM_HEAD)) {
                return back()->with('error', 'This event request is not waiting for Principal Assistant approval.');
            }

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
            $eventRequest->approval_level = EventRequest::LEVEL_2_ACADEMIC_HEAD;
            $eventRequest->status = 'Pending';
            $eventRequest->save();

            ActivityLog::log('event_approved_level_1', "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) approved by Principal Assistant: {$user->name} - Requestor: {$eventRequest->user->name}, Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A'), null);
            $this->sendApprovalNotification($eventRequest, 1, 'Approved');

            $notificationService = new NotificationService;
            $notificationService->notifyApproversOfNewEvent($eventRequest);

            return back()->with('success', 'Event request approved! Forwarded to Academic Head.');
        }

        if ($isShs && $user->isAcademicHead()) {
            if (! $eventRequest->canBeApprovedAtLevel(EventRequest::LEVEL_2_ACADEMIC_HEAD)) {
                return back()->with('error', 'This event request is still waiting for the previous approval level.');
            }

            if ($eventRequest->hasUserApprovedAtLevel($user->id, 2)) {
                return back()->with('error', 'You have already approved this event request.');
            }

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

            if (\App\Models\User::whereIn('role', ['school_admin', 'mis'])->exists()) {
                $eventRequest->status = 'Pending';
                $eventRequest->approval_level = EventRequest::LEVEL_4_SCHOOL_ADMIN;
            } else {
                $eventRequest->status = 'Approved';
                $eventRequest->approved_by = $user->id;
                $eventRequest->approved_at = now();
                $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;
            }

            $eventRequest->notes = $request->notes;
            $eventRequest->save();

            ActivityLog::log('event_approved_level_2', "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) approved by Academic Head: {$user->name} - Requestor: {$eventRequest->user->name}, Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A'), null);
            $this->sendApprovalNotification($eventRequest, 2, 'Approved');

            if ($eventRequest->status !== 'Approved') {
                $notificationService = new NotificationService;
                $notificationService->notifyApproversOfNewEvent($eventRequest);
            }

            return back()->with('success', $eventRequest->status === 'Approved' ? 'Event request fully approved!' : 'Event request approved! Forwarded to School Admin.');
        }

        // Determine approval level based on user role
        if ($user->isProgramHead()) {
            if (! $eventRequest->canBeApprovedAtLevel(EventRequest::LEVEL_1_PROGRAM_HEAD)) {
                return back()->with('error', 'This event request is not waiting for Program Head approval.');
            }

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

            if ($eventRequest->status === EventRequest::STATUS_PENDING) {
                $eventRequest->approval_level = $eventRequest->getNextApprovalLevel() ?? $eventRequest->approval_level;
            }

            ActivityLog::log(
                'event_approved_level_1',
                "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) approved by Program Head: {$user->name} - Requestor: {$eventRequest->user->name}, Department: {$eventRequest->department}, Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A'),
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
            if (! $eventRequest->canBeApprovedAtLevel(EventRequest::LEVEL_2_ACADEMIC_HEAD)) {
                return back()->with('error', 'This event request is still waiting for the previous approval level.');
            }

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

            if ($eventRequest->status === EventRequest::STATUS_PENDING) {
                $eventRequest->approval_level = $eventRequest->getNextApprovalLevel() ?? $eventRequest->approval_level;
            }

            ActivityLog::log(
                'event_approved_level_2',
                "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) approved by Academic Head: {$user->name} - Requestor: {$eventRequest->user->name}, Department: {$eventRequest->department}, Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A'),
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
            if (! $eventRequest->canBeApprovedAtLevel(EventRequest::LEVEL_3_BUILDING_ADMIN)) {
                return back()->with('error', 'This event request is still waiting for the previous approval level.');
            }

            // Building Admin approval is role-level; one Building Admin approval satisfies this step.
            if ($eventRequest->isApprovedByAllBuildingAdmins()) {
                return back()->with('error', 'This event request has already been approved by a Building Admin.');
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

            // Check if this is a Non-Academic event
            $isNonAcademic = $eventRequest->request_type === 'Non-Academic';

            // For Non-Academic events: Building Admin → School Admin (skip levels 1 & 2)
            // For Academic events: Check all previous levels
            if ($isNonAcademic) {
                // Non-Academic: Building Admin approved, now go to School Admin (level 4)
                if ($eventRequest->isApprovedByAllBuildingAdmins()) {
                    $eventRequest->approval_level = EventRequest::LEVEL_4_SCHOOL_ADMIN;

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
                    $eventRequest->approval_level = EventRequest::LEVEL_3_BUILDING_ADMIN;
                }
            } else {
                // Academic: Check if ALL Building Admins have approved AND all previous levels
                if ($eventRequest->isApprovedByAllBuildingAdmins() && $eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                    $eventRequest->approval_level = EventRequest::LEVEL_4_SCHOOL_ADMIN;

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
            }

            if ($eventRequest->status === EventRequest::STATUS_PENDING) {
                $eventRequest->approval_level = $eventRequest->getNextApprovalLevel() ?? $eventRequest->approval_level;
            }

            ActivityLog::log(
                'event_approved_level_3',
                "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) approved by Building Admin: {$user->name} - Requestor: {$eventRequest->user->name}, Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A'),
                null
            );

            // Notify the requester
            $this->sendApprovalNotification($eventRequest, 3, 'Approved');

            // Notify the next approver (School Admin)
            if ($isNonAcademic) {
                // Non-Academic: notify School Admin after Building Admin approves
                if ($eventRequest->isApprovedByAllBuildingAdmins()) {
                    $notificationService = new NotificationService;
                    $notificationService->notifyApproversOfNewEvent($eventRequest);
                }
            } else {
                // Academic: notify School Admin only if all previous levels have approved
                if ($eventRequest->isApprovedByAllBuildingAdmins() && $eventRequest->isApprovedByAllAcademicHeads() && $eventRequest->isApprovedByAllProgramHeads()) {
                    $notificationService = new NotificationService;
                    $notificationService->notifyApproversOfNewEvent($eventRequest);
                }
            }

        } elseif ($user->isSchoolAdmin() || $user->isAdmin()) {
            if (! $eventRequest->canBeApprovedAtLevel(EventRequest::LEVEL_4_SCHOOL_ADMIN)) {
                $nextLevel = $eventRequest->getNextApprovalLevel();
                if ($nextLevel !== null && $eventRequest->approval_level !== $nextLevel) {
                    $eventRequest->approval_level = $nextLevel;
                    $eventRequest->save();
                }

                return back()->with('error', 'This event request is still waiting for the previous approval level.');
            }

            // Check if this user has already approved at this level
            if ($eventRequest->hasUserApprovedAtLevel($user->id, 4)) {
                // Already approved - just ensure it's marked as approved
                if ($eventRequest->status !== 'Approved') {
                    $eventRequest->status = 'Approved';
                    $eventRequest->approved_by = $user->id;
                    $eventRequest->approved_at = now();
                    $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;
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

            // School Admin approval is final after all previous required levels are complete.
            $eventRequest->status = 'Approved';
            $eventRequest->approved_by = $user->id;
            $eventRequest->approved_at = now();
            $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;

            ActivityLog::log(
                'event_approved',
                "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) FULLY APPROVED by School Admin: {$user->name} - Requestor: {$eventRequest->user->name}, Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A') . ", Attendees: {$eventRequest->expected_attendees}",
                null
            );

            // Notify the requester
            $this->sendApprovalNotification($eventRequest, 4, 'Fully Approved');
        } else {
            // Fallback for any other role with approval permission
            $eventRequest->status = 'Approved';
            $eventRequest->approved_by = $user->id;
            $eventRequest->approved_at = now();
            $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;

            ActivityLog::log(
                'event_approved',
                "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) approved by {$user->name} ({$user->role}) - Requestor: {$eventRequest->user->name}, Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A'),
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

    private function approveConfiguredRoute(Request $request, EventRequest $eventRequest, User $user)
    {
        $level = $eventRequest->getNextApprovalLevel();
        $requiredRole = $eventRequest->requiredApprovalRole();
        if (! $level || ! $requiredRole) return back()->with('error', 'This request has already completed its approval route.');
        $allowed = $user->role === $requiredRole || ($requiredRole === 'school_admin' && $user->role === 'admin');
        if (! $allowed) return back()->with('error', 'This request is waiting for '.str_replace('_', ' ', $requiredRole).' approval.');
        if ($eventRequest->hasUserApprovedAtLevel($user->id, $level)) return back()->with('error', 'You have already approved this request.');

        $history = $eventRequest->approval_history ?? [];
        $history[] = ['level' => $level, 'role' => ucwords(str_replace('_', ' ', $requiredRole)), 'approver' => $user->name, 'approver_id' => $user->id, 'at' => now()->toDateTimeString(), 'notes' => $request->notes];
        $eventRequest->approval_history = $history;
        $nextLevel = $eventRequest->getNextApprovalLevel();
        if ($nextLevel === null) {
            $eventRequest->status = EventRequest::STATUS_APPROVED;
            $eventRequest->approved_by = $user->id;
            $eventRequest->approved_at = now();
            $eventRequest->approval_level = EventRequest::LEVEL_APPROVED;
        } else {
            $eventRequest->status = EventRequest::STATUS_PENDING;
            $eventRequest->approval_level = $nextLevel;
        }
        $eventRequest->save();
        ActivityLog::log('event_approved_configured_route', "Event request ID {$eventRequest->id} approved by {$user->name} as {$requiredRole}", null);
        $this->sendApprovalNotification($eventRequest, $level, 'Approved');
        if ($eventRequest->status === EventRequest::STATUS_PENDING) (new NotificationService)->notifyApproversOfNewEvent($eventRequest);
        return back()->with('success', $eventRequest->status === EventRequest::STATUS_APPROVED ? 'Event request fully approved.' : 'Approved and forwarded to the next approver.');
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
                    $eventRequest->location.' - '.\Carbon\Carbon::parse($eventRequest->event_date)->format('M d, Y'),
                    $level,
                    $status,
                    $eventRequest
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

        if ($eventRequest->hasConfiguredApprovalRoute()) {
            $rejectLevel = $eventRequest->getNextApprovalLevel();
            $requiredRole = $eventRequest->requiredApprovalRole();
            $isAllowed = $requiredRole
                && ($user->role === $requiredRole
                    || ($requiredRole === 'school_admin' && $user->role === 'admin'));

            if (! $rejectLevel || ! $isAllowed) {
                return back()->with('error', 'Only the approver currently assigned to this step can reject the request.');
            }

            $rejectRole = ucwords(str_replace('_', ' ', $requiredRole));
        } else {
            // Determine which level is rejecting for legacy requests.
            $rejectLevel = 0;
            $rejectRole = 'Unknown';

            if ($user->isProgramHead()) {
                $rejectLevel = 1;
                $rejectRole = 'Program Head';
            } elseif ($user->isPrincipalAssistant()) {
                $rejectLevel = 1;
                $rejectRole = 'SHS Principal';
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

            if ($rejectLevel === 0 || (int) $eventRequest->approval_level !== $rejectLevel) {
                return back()->with('error', 'This request is not waiting for your approval.');
            }
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
            'Event rejected by '.$rejectRole.': ',
            null
        );

        // Notify the requester about rejection
        $this->sendApprovalNotification($eventRequest, $rejectLevel, 'Rejected');

        return back()->with('success', 'Event request rejected!');
    }

    // Show all approved events (calendar view)
    public function calendar(Request $request)
    {
        if (! auth()->user()->canAccess('events')) {
            return redirect('/dashboard')->with('error', 'You do not have permission to view events.');
        }

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
        if (! auth()->user()->canAccess('events')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $calendarEvents = collect();

        // Event Requests - Only Approved
        $eventQuery = EventRequest::where('status', 'Approved');
        $events = $eventQuery->with('user')->orderBy('event_date', 'asc')->get();

        $eventCalendarEvents = $events->map(function ($event) {
            // All events are Approved, use green color
            $backgroundColor = '#26a69a';  // Green for Approved

            return [
                'id' => 'event_'.$event->id,
                'title' => $event->title,
                'start' => $event->event_date->format('Y-m-d').'T'.$event->start_time,
                'end' => $event->event_date->format('Y-m-d').'T'.$event->end_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'extendedProps' => [
                    'type' => 'approved',
                    'category' => $event->category,
                    'location' => $event->location,  // Fixed: use location instead of event_location
                    'description' => $event->description,
                    'requestedBy' => $event->user->name ?? 'Unknown',
                    'status' => $event->status,
                ],
            ];
        });

        // Facility Requests - Only Approved
        $facilityQuery = FacilityRequest::where('status', 'Approved');
        $facilities = $facilityQuery->with('user')->orderBy('event_date', 'asc')->get();

        $facilityCalendarEvents = $facilities->map(function ($facility) {
            // All facilities are Approved, use green color
            $backgroundColor = '#26a69a';  // Green for Approved

            return [
                'id' => 'facility_'.$facility->id,
                'title' => $facility->event_title.' ('.$facility->facility.')',
                'start' => $facility->event_date->format('Y-m-d').'T'.$facility->start_time,
                'end' => $facility->event_date->format('Y-m-d').'T'.$facility->end_time,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'extendedProps' => [
                    'type' => 'approved',
                    'facility' => $facility->facility,
                    'location' => $facility->facility,
                    'description' => $facility->description,
                    'requestedBy' => $facility->user->name ?? 'Unknown',
                    'status' => $facility->status,
                ],
            ];
        });

        // Concerns - Show Pending, Assigned, and In-Progress (NOT Approved)
        $concernQuery = Concern::whereIn('status', ['Pending', 'Assigned', 'In-Progress'])->where('is_deleted', false);
        $concerns = $concernQuery->with('categoryRelation', 'user')->orderBy('created_at', 'asc')->get();

        $concernCalendarEvents = $concerns->map(function ($concern) {
            $date = $concern->created_at->format('Y-m-d');
            $title = $concern->title ?? ($concern->categoryRelation->name ?? 'Concern');

            // Set color based on status
            $backgroundColor = match($concern->status) {
                'Pending' => '#ffa726',        // Orange
                'Assigned' => '#4f6ef7',       // Blue
                'In-Progress' => '#7c4dff',    // Purple
                default => '#dc3545'
            };

            // Construct location from room_number or location field
            $location = $concern->location;
            if (empty($location) && $concern->room_number) {
                $location = 'Room ' . $concern->room_number;
            }

            return [
                'id' => 'concern_'.$concern->id,
                'title' => $title,
                'start' => $date.'T09:00',
                'end' => $date.'T10:00',
                'backgroundColor' => $backgroundColor,
                'borderColor' => $backgroundColor,
                'extendedProps' => [
                    'type' => strtolower(str_replace('-', '_', $concern->status)),
                    'location' => $location,
                    'description' => $concern->description,
                    'requestedBy' => $concern->user->name ?? 'Anonymous',
                    'status' => $concern->status,
                ],
            ];
        });

        $calendarEvents = $eventCalendarEvents->concat($facilityCalendarEvents)->concat($concernCalendarEvents);

        return response()->json($calendarEvents);
    }

    // Cancel request
    public function cancel(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);

        // Only owner can cancel
        if ((int)$eventRequest->user_id !== (int)auth()->id() && ! auth()->user()->canApproveRequests()) {
            return redirect()->route('events.my')->with('error', 'You cannot cancel this request.');
        }

        $request->validate(['reason' => 'nullable|string|max:1000']);

        $cancellationUpdate = ['status' => 'Cancelled'];
        // Allow cancellation to work while older deployments are still waiting for the migration.
        if (\Illuminate\Support\Facades\Schema::hasColumn('event_requests', 'cancellation_reason')) {
            $cancellationUpdate['cancellation_reason'] = $request->input('reason');
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('event_requests', 'cancelled_at')) {
            $cancellationUpdate['cancelled_at'] = now();
        }
        $eventRequest->update($cancellationUpdate);

        ActivityLog::log(
            'event_cancelled',
            "Event request cancelled: {$eventRequest->location}. Reason: ".($request->input('reason') ?: 'Not provided'),
            null
        );

        return redirect()->route('events.my')->with('success', 'Event request cancelled.');
    }

    public function reschedule(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);
        if ((int) $eventRequest->user_id !== (int) auth()->id()) {
            return redirect()->route('events.my')->with('error', 'You cannot reschedule this request.');
        }

        $validated = $request->validate([
            'event_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'reason' => 'nullable|string|max:1000',
        ]);

        $eventRequest->update([
            'event_date' => $validated['event_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'status' => 'Pending',
            'approval_level' => 0,
            'approved_by' => null,
            'approved_at' => null,
            'notes' => trim(($eventRequest->notes ? $eventRequest->notes."\n" : '').'Reschedule requested: '.($validated['reason'] ?: 'No reason provided')),
        ]);

        return redirect()->route('events.my')->with('success', 'Event request rescheduled and returned for approval.');
    }

    // Delete event request (soft delete - moves to deleted events)
    public function delete(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);

        // Allow owner or admin to delete
        if ((int)$eventRequest->user_id !== (int)auth()->id() && ! auth()->user()->canApproveRequests()) {
            return redirect()->route('events.my')->with('error', 'You cannot delete this event request.');
        }

        // Permanent delete — physically remove from database
        if ($request->input('permanent') == '1') {
            ActivityLog::log(
                'event_permanent_deleted',
                'Event permanently deleted: ',
                null
            );

            $eventRequest->delete();

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Event permanently deleted.']);
            }

            return redirect()->route('events.my')->with('success', 'Event permanently deleted.');
        }

        // Soft delete — move to deleted folder
        $deletedFolder = ArchiveFolder::where('name', 'Deleted Events')
            ->where('type', 'mixed')
            ->first();

        if (! $deletedFolder) {
            $deletedFolder = ArchiveFolder::create([
                'name' => 'Deleted Events',
                'type' => 'mixed',
                'description' => 'Deleted event requests',
                'is_system' => true,
            ]);
        }

        $eventRequest->archive_folder_id = $deletedFolder->id;
        $eventRequest->is_deleted = true;
        $eventRequest->deleted_by = auth()->id();
        $eventRequest->save();

        ActivityLog::log(
            'event_deleted',
            "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) moved to deleted folder by " . auth()->user()->name . " (" . auth()->user()->role . ") - Location: {$eventRequest->event_location}, Date: " . ($eventRequest->start_date ? $eventRequest->start_date->format('M d, Y') : 'N/A'),
            null
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event request deleted.']);
        }

        return redirect()->route('events.my')->with('success', 'Event request deleted.');
    }

    // Archive event request
    public function archive(Request $request, $id)
    {
        $eventRequest = EventRequest::findOrFail($id);
        $user = auth()->user();

        // Owner or admin can archive
        if ((int)$eventRequest->user_id !== (int)$user->id && ! $user->canApproveRequests()) {
            return redirect()->route('events.my')->with('error', 'You cannot archive this event request.');
        }

        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $eventRequest->getFillable())) {
            return redirect()->route('events.my')->with('error', 'Invalid role for archiving.');
        }

        // Check if already archived by this role
        if ($eventRequest->$archiveColumn) {
            return redirect()->route('events.my')->with('error', 'This event is already archived by your role.');
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
            'Event archived: ',
            null
        );

        // Return JSON for AJAX requests
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event request archived successfully.']);
        }

        return redirect()->route('events.my')->with('success', 'Event request archived successfully.');
    }

    // Restore event request - handles both archive restore and deleted restore
    public function restore($id)
    {
        // Use findOrFail without is_deleted filter since deleted records have is_deleted=true
        $eventRequest = EventRequest::where('id', $id)->firstOrFail();
        $user = auth()->user();

        // Only owner or admin can restore
        if ((int)$eventRequest->user_id !== (int)$user->id && ! $user->canApproveRequests()) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'You cannot restore this event request.'], 403);
            }
            return redirect()->route('events.my')->with('error', 'You cannot restore this event request.');
        }

        // If restoring from deleted state
        if ($eventRequest->is_deleted) {
            $eventRequest->is_deleted = false;
            $eventRequest->deleted_by = null;
            $eventRequest->archive_folder_id = null;
            $eventRequest->save();

            ActivityLog::log(
                'event_restored_from_deleted',
                "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) restored from deleted folder by " . $user->name . " ({$user->role})",
                null
            );

            if (request()->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Event request restored successfully.']);
            }

            return redirect()->route('events.my')->with('success', 'Event request restored successfully.');
        }

        // Restoring from archive
        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $eventRequest->getFillable())) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid role for restoring.'], 400);
            }
            return redirect()->route('events.my')->with('error', 'Invalid role for restoring.');
        }

        // Set role-specific archive column to false (role-based restoring)
        $eventRequest->update([$archiveColumn => false]);

        // Also remove from user's archive using pivot table (user-based restoring)
        $eventRequest->archivedByUsers()->detach($user->id);

        ActivityLog::log(
            'event_restored',
            "Event '{$eventRequest->title}' (ID: {$eventRequest->id}) restored from archive by " . $user->name . " ({$user->role})",
            null
        );

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Event request restored successfully.']);
        }

        return redirect()->route('events.my')->with('success', 'Event request restored successfully.');
    }

    // Batch archive events
    public function batchArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:event_requests,id'
        ]);

        $user = auth()->user();
        $role = $user->role;
        $archiveColumn = $role . '_archived';
        $ids = $request->ids;
        $archivedCount = 0;

        foreach ($ids as $id) {
            $event = EventRequest::find($id);
            if ($event && ((int)$event->user_id === (int)$user->id || $user->canApproveRequests())) {
                $event->$archiveColumn = true;
                $event->save();
                $archivedCount++;
            }
        }

        ActivityLog::log('events_batch_archived', "Batch archived {$archivedCount} events");

        return response()->json([
            'success' => true,
            'message' => "Successfully archived {$archivedCount} event(s)"
        ]);
    }

    // Batch delete events (soft delete)
    public function batchDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:event_requests,id'
        ]);

        $user = auth()->user();
        $ids = $request->ids;
        $deletedCount = 0;

        foreach ($ids as $id) {
            $event = EventRequest::find($id);
            if ($event && ((int)$event->user_id === (int)$user->id || $user->canApproveRequests())) {
                $event->is_deleted = true;
                $event->deleted_by = $user->id;
                $event->save();
                $deletedCount++;
            }
        }

        ActivityLog::log('events_batch_deleted', "Batch deleted {$deletedCount} events");

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} event(s)"
        ]);
    }

    // Batch restore events from archive
    public function batchRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:event_requests,id'
        ]);

        $user = auth()->user();
        $role = $user->role;
        $archiveColumn = $role . '_archived';
        $ids = $request->ids;
        $restoredCount = 0;

        foreach ($ids as $id) {
            $event = EventRequest::find($id);
            if ($event && ((int)$event->user_id === (int)$user->id || $user->canApproveRequests())) {
                $event->$archiveColumn = false;
                $event->save();
                $restoredCount++;
            }
        }

        ActivityLog::log('events_batch_restored', "Batch restored {$restoredCount} events from archive");

        return response()->json([
            'success' => true,
            'message' => "Successfully restored {$restoredCount} event(s)"
        ]);
    }

    // Batch restore events from deleted
    public function batchRestoreDeleted(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $user = auth()->user();
        $ids = $request->ids;
        $restoredCount = 0;

        foreach ($ids as $id) {
            $event = EventRequest::where('id', $id)->first();
            if ($event && ((int)$event->user_id === (int)$user->id || $user->canApproveRequests())) {
                $event->is_deleted = false;
                $event->deleted_by = null;
                $event->save();
                $restoredCount++;
            }
        }

        ActivityLog::log('events_batch_restored_from_deleted', "Batch restored {$restoredCount} events from deleted");

        return response()->json([
            'success' => true,
            'message' => "Successfully restored {$restoredCount} event(s) from deleted"
        ]);
    }

    // Show all event requests (for admin)
    public function adminIndex(Request $request)
    {
        self::rejectExpiredPendingRequests();

        $user = auth()->user();
        if (! $user || ! $user->canApproveRequests()) {
            return redirect('/dashboard')->with('error', 'You do not have permission to view pending approvals.');
        }

        $viewType = $request->view ?? 'pending';
        if ($viewType === 'active') {
            $viewType = 'pending';
        }

        if ($viewType === 'rejected') {
            $rejectedEvents = EventRequest::with('user')
                ->where('is_deleted', false)
                ->where('status', 'Rejected')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('admin.events', [
                'viewType' => $viewType,
                'rejectedEvents' => $rejectedEvents,
                'requests' => collect(),
            ]);
        }

        if ($viewType === 'approved') {
            $approvedEvents = $this->approvedEventsForApprover($user);

            return view('admin.events', [
                'viewType' => $viewType,
                'approvedEvents' => $approvedEvents,
                'requests' => collect(),
            ]);
        }

        if ($viewType === 'finished') {
            // Finished events where the end datetime (event_date + end_time) has already passed
            $finishedEvents = EventRequest::with('user')
                ->where('is_deleted', false)
                ->where('status', 'Approved')
                ->whereRaw("(event_date + end_time::time) <= NOW()")
                ->orderBy('event_date', 'desc')
                ->get();

            return view('admin.events', [
                'viewType' => $viewType,
                'finishedEvents' => $finishedEvents,
                'requests' => collect(),
            ]);
        }

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
            $days = $request->get('days', $user->event_requests_auto_delete_days ?? 15);

            if ($deletedFolder) {
                $deletedEvents = EventRequest::where('archive_folder_id', $deletedFolder->id)
                    ->where('is_deleted', true)
                    ->when($days > 0, fn ($query) => $query->where('updated_at', '<=', now()->subDays($days)))
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

        // For pending approvals: show events not archived by any role
        $query = EventRequest::with('user')
            ->where('is_deleted', false)
            ->where('student_archived', false)
            ->where('faculty_archived', false)
            ->where('building_admin_archived', false)
            ->where('school_admin_archived', false)
            ->where('academic_head_archived', false)
            ->where('program_head_archived', false)
            ->where('mis_archived', false)
            ->where('maintenance_archived', false)
            ->whereNotIn('status', ['Approved', 'Rejected']); // Approved/Rejected events live in their own tabs

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
            $query->where('description', 'like', '%'.$request->search.'%');
        }

        $allRequests = $query->orderBy('created_at', 'desc')->get();
        $allRequests->each(function ($eventRequest) {
            $this->recordRequesterApprovalIfNeeded($eventRequest);
            $this->repairPendingApprovalLevel($eventRequest);
        });

        // Filter events to show only those at the current user's approval level
        $user = auth()->user();
        $requests = $allRequests->filter(function ($eventRequest) use ($user) {
            if ($eventRequest->hasConfiguredApprovalRoute()) {
                $requiredRole = $eventRequest->requiredApprovalRole();
                $matchesRole = $user->role === $requiredRole
                    || ($requiredRole === 'school_admin' && $user->role === 'admin');

                if (! $matchesRole) {
                    return false;
                }

                if ($requiredRole === 'program_head'
                    && $user->department
                    && $eventRequest->department !== $user->department) {
                    return false;
                }

                $level = $eventRequest->getNextApprovalLevel();

                return $level !== null
                    && ! $eventRequest->hasUserApprovedAtLevel($user->id, $level);
            }

            $isShs = ($eventRequest->education_level ?? 'tertiary') === 'shs';

            // Determine the user's approval level
            $userLevel = null;
            if ($user->isProgramHead()) {
                if ($isShs) {
                    return false;
                }
                $userLevel = 1;
            } elseif ($user->isPrincipalAssistant()) {
                if (! $isShs) {
                    return false;
                }
                $userLevel = 1;
            } elseif ($user->isAcademicHead()) {
                $userLevel = 2;
            } elseif ($user->isBuildingAdmin()) {
                if ($isShs) {
                    return false;
                }
                $userLevel = 3;
            } elseif ($user->isSchoolAdmin() || $user->isAdmin()) {
                $userLevel = 4;
            }

            // If user is not an approver, show all events
            if ($userLevel === null) {
                return true;
            }

            // Only show events that are at this user's approval level
            // approval_level indicates which level needs to approve next
            // 1 = Program Head, 2 = Academic Head, 3 = Building Admin, 4 = School Admin
            if ($eventRequest->approval_level != $userLevel) {
                return false;
            }

            if (! $eventRequest->arePreviousApprovalLevelsComplete($userLevel)) {
                return false;
            }

            // Also check if user has already approved at their level
            if ($userLevel === 3) {
                return ! $eventRequest->isApprovedByAllBuildingAdmins();
            }

            return !$eventRequest->hasUserApprovedAtLevel($user->id, $userLevel);
        });

        return view('admin.events', compact('requests', 'viewType'));
    }

    private function repairPendingApprovalLevel(EventRequest $eventRequest): void
    {
        if ($eventRequest->status !== EventRequest::STATUS_PENDING) {
            return;
        }

        $nextLevel = $eventRequest->getNextApprovalLevel();
        if ($nextLevel === null || (int) $eventRequest->approval_level === $nextLevel) {
            return;
        }

        $eventRequest->approval_level = $nextLevel;
        $eventRequest->save();
    }

    private function approvedEventsForApprover(User $user)
    {
        return EventRequest::with('user')
            ->where('is_deleted', false)
            ->whereRaw("(event_date + end_time::time) > NOW()")
            ->whereNotIn('status', ['Rejected', 'Cancelled'])
            ->orderBy('event_date', 'asc')
            ->get()
            ->filter(function (EventRequest $eventRequest) use ($user) {
                $approvedInHistory = collect($eventRequest->approval_history ?? [])
                    ->contains(fn ($entry) => (int) ($entry['approver_id'] ?? 0) === (int) $user->id
                        && strtolower((string) ($entry['action'] ?? 'approved')) !== 'rejected');

                if ($approvedInHistory) {
                    return true;
                }

                return (($user->isProgramHead() || $user->isPrincipalAssistant()) && (int) $eventRequest->approved_by_level_1 === (int) $user->id)
                    || ($user->isAcademicHead() && (int) $eventRequest->approved_by_level_2 === (int) $user->id)
                    || ($user->isBuildingAdmin() && (int) $eventRequest->approved_by_level_3 === (int) $user->id)
                    || (($user->isSchoolAdmin() || $user->isAdmin()) && (int) $eventRequest->approved_by === (int) $user->id);
            })
            ->values();
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
                    'date' => isset($h['at']) ? \Carbon\Carbon::parse($h['at'])->format('m/d/Y h:i A') : 'N/A',
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
                        'date' => $eventRequest->approved_at_level_1 ? \Carbon\Carbon::parse($eventRequest->approved_at_level_1)->format('m/d/Y h:i A') : 'N/A',
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
                        'date' => $eventRequest->approved_at_level_2 ? \Carbon\Carbon::parse($eventRequest->approved_at_level_2)->format('m/d/Y h:i A') : 'N/A',
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
                        'date' => $eventRequest->approved_at_level_3 ? \Carbon\Carbon::parse($eventRequest->approved_at_level_3)->format('m/d/Y h:i A') : 'N/A',
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
            
            'event_date' => optional($event->event_date)->toDateString(),
            'location' => $event->location,
            'start_time' => $event->start_time,
            'end_time' => $event->end_time,
            'category' => $event->category,
            'department' => $event->department,
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
        $data['materials_needed'] = $event->materials_needed ?? [];

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
            
            'description' => 'required|string|max:5000',
            'event_date' => 'required|date|after_or_equal:today',
            'location' => 'required|string|min:3|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'category' => 'required|in:event,meeting,activity,training,other',
            'department' => 'nullable|in:GE,ICT,Business Management,THM',
        ]);

        $event = EventRequest::create([
            'user_id' => $request->user()->id,
            
            'description' => $validated['description'],
            'event_date' => $validated['event_date'],
            'location' => $validated['location'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'category' => $validated['category'],
            'department' => $validated['department'] ?? null,
            // Kept internally for the current non-null database column; it is no longer shown or chosen by users.
            'priority' => 'medium',
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
                    
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'user' => $event->user->name ?? 'Unknown',
                ];
            }),
        ]);
    }
}
