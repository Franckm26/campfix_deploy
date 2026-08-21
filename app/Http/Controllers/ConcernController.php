<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Category;
use App\Models\Concern;
use App\Models\ConcernReporter;
use App\Models\EventRequest;
use App\Models\Report;
use App\Models\User;
use App\Notifications\ConcernAssignedNotification;
use App\Services\DefaultCategoryService;
use App\Services\NotificationService;
use App\Services\SecureFileUpload;
use App\Services\SupabaseStorage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ConcernController extends Controller
{
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
            Log::error('Concern update notification failed: '.$e->getMessage());
        }
    }

    private function linkDuplicateReporter(Concern $concern, Request $request): int
    {
        $this->ensureDuplicateTrackingSchema();

        if (! Concern::supportsLinkedReporters()) {
            return $concern->report_count ?? 1;
        }

        return DB::transaction(function () use ($concern, $request) {
            ConcernReporter::firstOrCreate(
                [
                    'concern_id' => $concern->id,
                    'user_id' => $concern->user_id,
                ],
                [
                    'is_original' => true,
                    'is_anonymous' => (bool) $concern->is_anonymous,
                    'reported_at' => $concern->created_at ?? now(),
                ]
            );

            ConcernReporter::firstOrCreate(
                [
                    'concern_id' => $concern->id,
                    'user_id' => auth()->id(),
                ],
                [
                    'is_original' => auth()->id() === $concern->user_id,
                    'is_anonymous' => (bool) $request->boolean('is_anonymous'),
                    'reported_at' => now(),
                ]
            );

            $reportCount = ConcernReporter::where('concern_id', $concern->id)->count();
            if (Concern::supportsReportCount()) {
                $concern->forceFill(['report_count' => max(1, $reportCount)])->save();
            }

            if (Report::supportsReportCount()) {
                Report::where('concern_id', $concern->id)
                    ->update(['report_count' => max(1, $reportCount)]);
            }

            ActivityLog::log(
                'duplicate_concern_linked',
                'Duplicate concern reporter linked to ticket #'.$concern->id.': '.auth()->user()->email,
                $concern->id
            );

            return max(1, $reportCount);
        });
    }

    private function ensureDuplicateTrackingSchema(): void
    {
        try {
            if (! Schema::hasTable('concern_reporters')) {
                Schema::create('concern_reporters', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('concern_id')->constrained()->onDelete('cascade');
                    $table->foreignId('user_id')->constrained()->onDelete('cascade');
                    $table->boolean('is_original')->default(false);
                    $table->boolean('is_anonymous')->default(false);
                    $table->timestamp('reported_at')->useCurrent();
                    $table->timestamps();
                    $table->unique(['concern_id', 'user_id']);
                });
            }

            if (! Schema::hasColumn('concerns', 'report_count')) {
                Schema::table('concerns', function (Blueprint $table) {
                    $table->unsignedInteger('report_count')->default(1)->after('is_anonymous');
                });
            }

            if (! Schema::hasColumn('reports', 'report_count')) {
                Schema::table('reports', function (Blueprint $table) {
                    $table->unsignedInteger('report_count')->default(1)->after('status');
                });
            }
        } catch (\Throwable $e) {
            Log::warning('Duplicate tracking schema could not be verified: '.$e->getMessage());
        }
    }

    // Show the form to submit a new concern
    public function create()
    {
        // Only maintenance cannot create concerns
        // Building Admin, School Admin, Academic Head, Program Head, Faculty, and Students can create concerns
        $role = auth()->user()->role;
        if ($role === 'maintenance') {
            return redirect()->route('concerns.my')->with('error', 'Your role cannot submit concerns.');
        }

        DefaultCategoryService::ensureDefaults();
        $categories = Category::all();

        return view('concerns.create', compact('categories'));
    }

    // Store a new concern in the database
    public function store(Request $request)
    {
        try {
            // Only maintenance cannot create concerns
            $role = auth()->user()->role;
            if ($role === 'maintenance') {
                return redirect('/dashboard')->with('error', 'Your role cannot submit concerns.');
            }

            // Check daily submission limit (5 per day)
            $userId = auth()->id();
            if (\App\Models\UserRateLimit::hasExceededLimit($userId, 'submission', 5)) {
                $remaining = \App\Models\UserRateLimit::getRemainingAttempts($userId, 'submission', 5);
                
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have reached your daily submission limit of 5 concerns. Please try again tomorrow.',
                        'remaining_attempts' => $remaining
                    ], 429);
                }
                
                return redirect()->back()->with('error', 'You have reached your daily submission limit of 5 concerns. Please try again tomorrow.');
            }

            // Validate user input
            $request->validate([
                'location' => 'nullable|string|max:255',
                'location_type' => 'nullable|in:Room,AVR,Computer Laboratory',
                'room_number' => 'nullable|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'description' => 'nullable|string',
                'details' => 'nullable|string|max:2000',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_anonymous' => 'nullable|boolean',
            ]);

        // Additional validation based on category
        $category = Category::find($request->category_id);
        $categoryName = strtolower(trim($category?->name ?? ''));
        $isRoomsCategory = $categoryName === 'rooms';
        $isTechnologyCategory = $categoryName === 'technology/internet';
        if ($isRoomsCategory) {
            $request->validate([
                'location_type' => 'required|in:Room,AVR,Computer Laboratory',
                'room_number' => 'required|string|max:255',
            ]);
        } else {
            $request->validate([
                'location' => 'required|string|max:255',
            ]);
        }

        // Normalize room number: strip any leading location-type prefix (e.g. "Room 211" → "211")
        $normalizeRoomNumber = function (string $value): string {
            $prefixes = ['computer laboratory', 'computer lab', 'room', 'avr'];
            $lower = strtolower(trim($value));
            foreach ($prefixes as $prefix) {
                if (str_starts_with($lower, $prefix)) {
                    $value = trim(substr($value, strlen($prefix)));
                    break;
                }
            }
            return trim($value);
        };

        $problemType = trim((string) $request->input('description', ''));
        $problemTypeForStorage = $problemType !== '' ? $problemType : null;

        // Check if the same issue/problem type in the same room/location already has an active concern.
        // This allows multiple different problem types under the same issue to be reported for the same room.
        $activeDuplicateStatuses = ['Pending', 'Assigned', 'In Progress'];

        $baseQuery = Concern::whereIn('status', $activeDuplicateStatuses)
            ->where('is_deleted', false)
            ->where('title', $request->title);

        if ($problemTypeForStorage === null) {
            $baseQuery->where(function ($query) {
                $query->whereNull('description')->orWhere('description', '');
            });
        } else {
            $baseQuery->where('description', $problemTypeForStorage);
        }

        if ($isRoomsCategory) {
            $normalizedInput = $normalizeRoomNumber($request->room_number);
            // Fetch candidates matching location_type and title, then normalize room_number for comparison
            $existingConcern = Concern::whereIn('status', $activeDuplicateStatuses)
                ->where('is_deleted', false)
                ->where('title', $request->title) // Same issue
                ->where('location_type', $request->location_type)
                ->when(
                    $problemTypeForStorage === null,
                    fn ($query) => $query->where(fn ($q) => $q->whereNull('description')->orWhere('description', '')),
                    fn ($query) => $query->where('description', $problemTypeForStorage)
                )
                ->get()
                ->first(function ($concern) use ($normalizedInput, $normalizeRoomNumber) {
                    return strcasecmp(
                        $normalizeRoomNumber($concern->room_number ?? ''),
                        $normalizedInput
                    ) === 0;
                });
        } else {
            $normalizedLocation = trim($request->location);
            $existingConcern = $baseQuery->get()->first(function ($concern) use ($normalizedLocation) {
                return strcasecmp(trim($concern->location ?? ''), $normalizedLocation) === 0;
            });
        }

        if ($existingConcern) {
            $reportCount = $this->linkDuplicateReporter($existingConcern, $request);
            \App\Models\UserRateLimit::incrementCounter(auth()->id(), 'submission');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'linked_duplicate' => true,
                    'message' => 'Concern submitted successfully.',
                    'concern_id' => $existingConcern->id,
                    'issue' => $existingConcern->title,
                    'problem_type' => $existingConcern->description,
                    'location' => $isRoomsCategory
                        ? $request->location_type . ' ' . $request->room_number
                        : $request->location,
                    'status' => $existingConcern->status,
                    'report_count' => $reportCount,
                ]);
            }

            return redirect()->route('concerns.my')
                ->with('success', 'Concern submitted successfully!');
        }

        // Handle image upload with security validation
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Use Supabase Storage for production/Vercel, local storage for development
            if (config('app.env') === 'production' || config('app.env') === 'vercel') {
                $supabaseStorage = new \App\Services\SupabaseStorage();
                $imagePath = $supabaseStorage->upload($request->file('image'), 'concerns');
                
                if ($imagePath === null) {
                    return redirect()->back()->withInput()->with('error', 'Failed to upload image to cloud storage.');
                }
            } else {
                // Local storage for development
                $secureUpload = new SecureFileUpload();
                $imagePath = $secureUpload->validateAndStore(
                    $request->file('image'),
                    'concerns',
                    'concerns'
                );

                if ($imagePath === null) {
                    return redirect()->back()->withInput()->with('error', 'Invalid file upload. Please ensure the file is a valid image under 2MB.');
                }
            }
        }

        // Priority is NOT set on submission — Building Admin sets it after assigning via the priority popup
        $priority = null;

        // Construct location for concern
        $concernLocation = $request->location;
        if ($isRoomsCategory) {
            $concernLocation = $request->location_type . ' ' . $request->room_number;
        }

        // Save concern - always set is_anonymous to false since anonymous submission is disabled
        // user_id is automatically set to authenticated user's ID
        $concernData = [
            'title' => $request->title,
            'description' => $problemTypeForStorage,
            'location' => $concernLocation,
            'location_type' => $request->location_type,
            'room_number' => $request->room_number,
            'category_id' => $request->category_id,
            'user_id' => auth()->id(), // Automatically set to authenticated user
            'status' => 'Pending',
            'priority' => $priority,
            'image_path' => $imagePath,
            'is_anonymous' => false,
        ];

        // Keep optional details compatible with deployed databases that have not
        // yet run the details-column migration.
        if (Schema::hasColumn('concerns', 'details')) {
            $concernData['details'] = $request->input('details');
        }

        if (Concern::supportsReportCount()) {
            $concernData['report_count'] = 1;
        }

        $concern = Concern::create($concernData);

        if (Concern::supportsLinkedReporters()) {
            ConcernReporter::firstOrCreate(
                [
                    'concern_id' => $concern->id,
                    'user_id' => auth()->id(),
                ],
                [
                    'is_original' => true,
                    'is_anonymous' => false,
                    'reported_at' => now(),
                ]
            );
        }

        // Severity for report — null until building admin sets priority after assignment
        $severity = null;

        // Construct location for report
        $reportLocation = $request->location;
        if ($isRoomsCategory) {
            $reportLocation = $request->location_type . ' ' . $request->room_number;
        }

        // Also create a report entry
        $reportData = [
            'user_id' => auth()->id(),
            'title' => $request->title,
            'concern_id' => $concern->id,
            'category_id' => $request->category_id,
            'description' => $problemTypeForStorage,
            'location' => $reportLocation,
            'location_type' => $request->location_type,
            'room_number' => $request->room_number,
            'severity' => $severity,
            'status' => 'Pending',
            'photo_path' => $imagePath,
        ];

        if (Schema::hasColumn('reports', 'details')) {
            $reportData['details'] = $request->input('details');
        }

        if (Report::supportsReportCount()) {
            $reportData['report_count'] = 1;
        }

        $report = Report::create($reportData);

        // Auto-assignment removed - Building Admin should manually assign reports
        // Reports will remain in "Pending" status until manually assigned

        // Increment submission counter
        \App\Models\UserRateLimit::incrementCounter(auth()->id(), 'submission');

        // Log concern and report creation
        \Log::info('Concern and Report created: ', ['concern_id' => $concern->id, 'report_id' => $report->id, 'user_id' => auth()->id()]);

        // Log activity
        ActivityLog::log(
            'concern_created',
            'New concern submitted: '.($request->title ?? 'Untitled'),
            $concern->id
        );

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern Submitted',
            'Your concern has been submitted successfully and is now pending review.',
            auth()->user()
        );

        try {
            (new NotificationService)->notifyBuildingAdminsOfNewConcern($concern);
        } catch (\Exception $e) {
            Log::error('Building admin new concern notification failed: '.$e->getMessage());
        }

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Concern submitted successfully!',
                'concern_id' => $concern->id
            ], 201);
        }

        return redirect()->route('concerns.my')->with('success', 'Concern submitted successfully!');
        
        } catch (\Exception $e) {
            \Log::error('Error submitting concern: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error submitting concern: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->withInput()->with('error', 'Error submitting concern: ' . $e->getMessage());
        }
    }

    // Show concerns list with inline archive and deleted views
    // IMPORTANT: Each user (including admin) can only see their OWN concerns
    public function myConcerns(Request $request)
    {
        DefaultCategoryService::ensureDefaults();

        // Only maintenance cannot access this page to view their own concerns
        // Building Admin, School Admin, Academic Head, Program Head, Faculty, and Students can access
        $role = auth()->user()->role;
        if ($role === 'maintenance') {
            return redirect('/dashboard')->with('error', 'Your role cannot view this page.');
        }

        $viewType = $request->get('view', 'active'); // 'active', 'resolved', 'archives', or 'deleted'

        // ========== RESOLVED VIEW ==========
        if ($viewType === 'resolved') {
            // All users see only their OWN resolved concerns
            $query = Concern::query()
                ->forUser(auth()->id())
                ->where('status', 'Resolved')
                ->notArchivedByUser(auth()->id())
                ->where('is_deleted', false)
                ->with('categoryRelation', 'user');

            // Apply filters
            if ($request->filled('search')) {
                $query->where('description', 'like', '%'.$request->search.'%');
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $resolvedConcerns = $query->orderBy('updated_at', 'desc')->get();

            $activeConcerns = Concern::query()
                ->forUser(Auth::id())
                ->with('categoryRelation', 'user')
                ->notArchivedByUser(Auth::id())
                ->where('is_deleted', false)
                ->latest()
                ->paginate(20);

            $archivedConcerns = Concern::query()
                ->forUser(Auth::id())
                ->archivedByUser(Auth::id())
                ->where('is_deleted', false)
                ->with('categoryRelation', 'user', 'archivedByUsers')
                ->orderBy('updated_at', 'desc')
                ->get();

            $deletedConcerns = Concern::query()
                ->forUser(Auth::id())
                ->where('is_deleted', true)
                ->where('deleted_at', '>=', now()->subDays(auth()->user()->concerns_auto_delete_days ?? 15))
                ->with('categoryRelation', 'user', 'deletedBy')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('concerns.my', [
                'resolvedConcerns' => $resolvedConcerns,
                'viewType' => $viewType,
                'concerns' => $activeConcerns,
                'archivedConcerns' => $archivedConcerns,
                'deletedConcerns' => $deletedConcerns,
                'categories' => Category::all(),
                'facilities' => \App\Models\Facility::orderBy('type')->orderBy('name')->get(),
                'days' => auth()->user()->concerns_auto_delete_days ?? 15,
            ]);
        }

        // ========== ARCHIVES VIEW ==========
        if ($viewType === 'archives') {
            // IMPORTANT: All users (including admin) can ONLY see their OWN archived concerns
            // This ensures complete data separation between users
            // Uses the pivot table to get concerns archived by the current user
            $query = Concern::query()
                ->forUser(auth()->id())
                ->archivedByUser(auth()->id())
                ->where('is_deleted', false)
                ->with('categoryRelation', 'user', 'archivedByUsers');

            // Apply filters
            if ($request->filled('search')) {
                $query->where('description', 'like', '%'.$request->search.'%');
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $archivedConcerns = $query->orderBy('updated_at', 'desc')->get();

            $activeConcerns = Concern::query()
                ->forUser(Auth::id())
                ->with('categoryRelation', 'user')
                ->notArchivedByUser(Auth::id())
                ->where('is_deleted', false)
                ->where('status', '!=', 'Resolved')
                ->latest()
                ->paginate(20);

            $resolvedConcerns = Concern::query()
                ->forUser(Auth::id())
                ->where('status', 'Resolved')
                ->notArchivedByUser(Auth::id())
                ->where('is_deleted', false)
                ->with('categoryRelation', 'user')
                ->orderBy('updated_at', 'desc')
                ->get();

            $deletedConcerns = Concern::query()
                ->forUser(Auth::id())
                ->where('is_deleted', true)
                ->where('deleted_at', '>=', now()->subDays(auth()->user()->concerns_auto_delete_days ?? 15))
                ->with('categoryRelation', 'user', 'deletedBy')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('concerns.my', [
                'archivedConcerns' => $archivedConcerns,
                'viewType' => $viewType,
                'concerns' => $activeConcerns,
                'resolvedConcerns' => $resolvedConcerns,
                'deletedConcerns' => $deletedConcerns,
                'categories' => Category::all(),
                'facilities' => \App\Models\Facility::orderBy('type')->orderBy('name')->get(),
                'days' => auth()->user()->concerns_auto_delete_days ?? 15,
            ]);
        }

        // ========== DELETED VIEW ==========
        if ($viewType === 'deleted') {
            // IMPORTANT: All users (including admin) can ONLY see their OWN deleted concerns
            // This ensures complete data separation between users
            $user = auth()->user();
            $days = $request->get('days', $user->concerns_auto_delete_days ?? 15);

            $query = Concern::query()
                ->forUser(auth()->id()) // User can only see their own
                ->where('is_deleted', true)
                ->when($days > 0, fn ($query) => $query->where('deleted_at', '>=', now()->subDays($days)))
                ->with('categoryRelation', 'user', 'deletedBy');

            // Apply filters
            if ($request->filled('search')) {
                $query->where('description', 'like', '%'.$request->search.'%');
            }
            if ($request->filled('category')) {
                $query->where('category_id', $request->category);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('priority')) {
                $query->where('priority', $request->priority);
            }

            $deletedConcerns = $query->orderBy('updated_at', 'desc')->get();

            if ($request->expectsJson()) {
                return response()->json([
                    'concerns' => $deletedConcerns->map(function ($concern) {
                        return [
                            'id' => $concern->id,
                            'title' => 'Concern #'.$concern->id,
                            'description' => $concern->description,
                            'location' => $concern->location,
                            'category' => $concern->categoryRelation ? $concern->categoryRelation->name : 'N/A',
                            'priority' => $concern->priority,
                            'status' => $concern->status,
                            'created_at' => $concern->created_at->format('M d, Y'),
                            'resolved_at' => $concern->resolved_at ? $concern->resolved_at->format('M d, Y') : null,
                            'user' => $concern->user ? $concern->user->name : 'Anonymous',
                        ];
                    }),
                    'days' => $days,
                ]);
            }

            $activeConcerns = Concern::query()
                ->forUser(Auth::id())
                ->with('categoryRelation', 'user')
                ->notArchivedByUser(Auth::id())
                ->where('is_deleted', false)
                ->where('status', '!=', 'Resolved')
                ->latest()
                ->paginate(20);

            $resolvedConcerns = Concern::query()
                ->forUser(Auth::id())
                ->where('status', 'Resolved')
                ->notArchivedByUser(Auth::id())
                ->where('is_deleted', false)
                ->with('categoryRelation', 'user')
                ->orderBy('updated_at', 'desc')
                ->get();

            $archivedConcerns = Concern::query()
                ->forUser(Auth::id())
                ->archivedByUser(Auth::id())
                ->where('is_deleted', false)
                ->with('categoryRelation', 'user', 'archivedByUsers')
                ->orderBy('updated_at', 'desc')
                ->get();

            return view('concerns.my', [
                'deletedConcerns' => $deletedConcerns,
                'viewType' => $viewType,
                'concerns' => $activeConcerns,
                'resolvedConcerns' => $resolvedConcerns,
                'archivedConcerns' => $archivedConcerns,
                'categories' => Category::all(),
                'facilities' => \App\Models\Facility::orderBy('type')->orderBy('name')->get(),
                'days' => $days,
            ]);
        }

        // ========== ACTIVE VIEW (DEFAULT) ==========
        // All users see only their OWN active concerns that are NOT archived by them and NOT resolved
        $query = Concern::query()
            ->forUser(Auth::id()) // User can only see their own
            ->with('categoryRelation', 'user')
            ->notArchivedByUser(Auth::id()) // Exclude concerns archived by this user
            ->where('is_deleted', false)
            ->where('status', '!=', 'Resolved'); // Exclude resolved concerns

        // Apply filters
        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Get per_page parameter with default
        $perPage = $request->get('per_page', 20);
        $perPage = in_array($perPage, [20, 50, 100]) ? $perPage : 20;
        $concerns = $query->latest()->paginate($perPage);
        $categories = Category::all();

        // Always fetch resolved, archived and deleted concerns for tab display
        $resolvedConcerns = Concern::query()
            ->forUser(Auth::id())
            ->where('status', 'Resolved')
            ->notArchivedByUser(Auth::id())
            ->where('is_deleted', false)
            ->with('categoryRelation', 'user')
            ->orderBy('updated_at', 'desc')
            ->get();

        $archivedConcerns = Concern::query()
            ->forUser(Auth::id())
            ->archivedByUser(Auth::id())
            ->where('is_deleted', false)
            ->with('categoryRelation', 'user', 'archivedByUsers')
            ->orderBy('updated_at', 'desc')
            ->get();

        $user = auth()->user();
        $days = $request->get('days', $user->concerns_auto_delete_days ?? 15);

        $deletedConcerns = Concern::query()
            ->forUser(Auth::id())
            ->where('is_deleted', true)
            ->when($days > 0, fn ($query) => $query->where('deleted_at', '>=', now()->subDays($days)))
            ->with('categoryRelation', 'user', 'deletedBy')
            ->orderBy('updated_at', 'desc')
            ->get();

        $facilities = \App\Models\Facility::orderBy('type')->orderBy('name')->get();

        return view('concerns.my', compact('concerns', 'categories', 'viewType', 'resolvedConcerns', 'archivedConcerns', 'deletedConcerns', 'days', 'facilities'));
    }

    public function show($id)
    {
        $concern = Concern::with('categoryRelation', 'user', 'assignedTo')->findOrFail($id);

        // "My Concerns" must be owner-only for non-maintenance users, including building admins.
        // Only maintenance may view concerns assigned to them.
        $user = auth()->user();
        $isOwner = $concern->user_id === $user->id;
        $isLinkedReporter = $concern->hasLinkedReporter($user->id);
        $isAssignedMaintenance = $user->role === 'maintenance' && $concern->assigned_to === $user->id;

        if (! $isOwner && ! $isLinkedReporter && ! $isAssignedMaintenance) {
            // Return JSON error for AJAX requests
            if (request()->ajax()) {
                return response()->json([
                    'error' => 'You cannot view this concern.'
                ], 403);
            }
            return redirect('/dashboard')->with('error', 'You cannot view this concern.');
        }

        // Check if request is AJAX for modal
        if (request()->ajax()) {
            return view('concerns.show_modal', compact('concern'));
        }

        return view('concerns.show', compact('concern'));
    }

    public function edit($id)
    {
        $user = auth()->user();

        // Maintenance cannot edit concerns
        if ($user->role === 'maintenance') {
            return redirect('/dashboard')->with('error', 'Maintenance staff cannot edit concerns.');
        }

        $concern = Concern::findOrFail($id);

        $isOwner = $concern->user_id === $user->id;
        $isLinkedReporter = $concern->hasLinkedReporter($user->id);
        $isAssignedMis = $user->role === 'mis' && $concern->assigned_to === $user->id;
        $isMisUser = $user->role === 'mis';
        $editableStatusesForOwner = ['Pending', 'Assigned'];

        if (! $isOwner && ! $isLinkedReporter && ! $isAssignedMis && ! $isMisUser) {
            return redirect('/dashboard')->with('error', 'You cannot edit this concern.');
        }

        if (($isOwner || $isLinkedReporter || $isAssignedMis) && ! $isMisUser && ! in_array($concern->status, $editableStatusesForOwner, true)) {
            return redirect('/dashboard')->with('error', 'You cannot edit this concern once work has started or it has been completed.');
        }

        DefaultCategoryService::ensureDefaults();
        $categories = Category::all();

        return view('concerns.edit', compact('concern', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        DefaultCategoryService::ensureDefaults();
        $concern = Concern::findOrFail($id);

        // Allow the submitter to update their own concern.
        // For MIS users, also allow editing concerns assigned to them because
        // some self-submitted concerns are auto-assigned to MIS immediately.
        $isOwner = $concern->user_id === $user->id;
        $isLinkedReporter = $concern->hasLinkedReporter($user->id);
        $isAssignedMis = $user->role === 'mis' && $concern->assigned_to === $user->id;
        $isAdmin = in_array($user->role, ['mis', 'school_admin', 'building_admin']);

        // Maintenance cannot update concerns
        if ($user->role === 'maintenance') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Maintenance staff cannot edit concerns.'], 403);
            }

            return redirect('/dashboard')->with('error', 'Maintenance staff cannot edit concerns.');
        }

        if (! $isOwner && ! $isLinkedReporter && ! $isAssignedMis && ! $isAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot update this concern.'], 403);
            }

            return redirect('/dashboard')->with('error', 'You cannot update this concern.');
        }

        if ($isLinkedReporter && ! $isOwner && ! $isAdmin && $concern->status !== 'Pending') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot edit this concern once it has been assigned or work has started.'], 403);
            }

            return redirect('/dashboard')->with('error', 'You cannot edit this concern once it has been assigned or work has started.');
        }

        // Validate
        $request->validate([
            'title' => 'nullable|string|max:255',
            'location' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'status' => 'nullable|in:Pending,Assigned,In Progress,Resolved,Closed',
            'assigned_to' => 'nullable|exists:maintenance_staff,id',
            'cost' => 'nullable|numeric|min:0',
        ]);

        // Handle image upload
        $imagePath = $concern->image_path;
        if ($request->hasFile('image')) {
            // Delete old image
            if ($concern->image_path) {
                Storage::disk('public')->delete($concern->image_path);
            }
            $imagePath = $request->file('image')->store('concerns', 'public');
        }

        $updateData = [
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'category_id' => $request->category_id,
            'image_path' => $imagePath,
            'cost' => $request->cost ?? $concern->cost,
        ];

        // Only Building Admin and MIS can change priority
        if (in_array($user->role, ['building_admin', 'mis']) && $request->filled('priority')) {
            $updateData['priority'] = $request->priority;
        }

        // Admin roles can update status and assign_to
        if ($isAdmin) {
            if ($request->filled('status')) {
                $updateData['status'] = $request->status;
                // Set assigned_at when status changes to Assigned
                if ($request->status === 'Assigned' && ! $concern->assigned_at) {
                    $updateData['assigned_at'] = now();
                }
                // Set resolved_at when status changes to Resolved
                if ($request->status === 'Resolved' && ! $concern->resolved_at) {
                    $updateData['resolved_at'] = now();
                }
            }
            if ($request->filled('assigned_to')) {
                // Check if this is a new assignment (not unassigning)
                $oldAssignedTo = $concern->assigned_to;
                $newAssignedTo = $request->assigned_to;

                $updateData['assigned_to'] = $newAssignedTo;

                // Auto-set status to Assigned if assigning to maintenance
                if ($concern->status === 'Pending') {
                    $updateData['status'] = 'Assigned';
                    $updateData['assigned_at'] = now();
                }

                // Send notification to maintenance user when assigned
                if ($newAssignedTo && $newAssignedTo != $oldAssignedTo) {
                    $maintenanceUser = User::find($newAssignedTo);
                    if ($maintenanceUser && $maintenanceUser->role === 'maintenance') {
                        $maintenanceUser->notify(new ConcernAssignedNotification(
                            $concern,
                            $user->name,
                            now()
                        ));
                    }
                }
            }
        }

        $oldStatus = $concern->status;
        $oldPriority = $concern->priority;
        $oldAssignedTo = $concern->assigned_to;

        $concern->update($updateData);

        // Also update the corresponding report to keep them in sync
        $report = Report::where('concern_id', $concern->id)->first();
        if ($report) {
            $reportUpdateData = [
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'category_id' => $request->category_id,
                'photo_path' => $imagePath,
            ];

            // Sync status
            if (isset($updateData['status'])) {
                $reportUpdateData['status'] = $updateData['status'];
            }

            // Sync assigned_to
            if (isset($updateData['assigned_to'])) {
                $reportUpdateData['assigned_to'] = $updateData['assigned_to'];
            }

            // Sync assigned_at
            if (isset($updateData['assigned_at'])) {
                $reportUpdateData['assigned_at'] = $updateData['assigned_at'];
            }

            // Sync resolved_at
            if (isset($updateData['resolved_at'])) {
                $reportUpdateData['resolved_at'] = $updateData['resolved_at'];
            }

            // Sync severity (maps to priority)
            if (isset($updateData['priority'])) {
                $reportUpdateData['severity'] = $updateData['priority'];
            }

            $report->update($reportUpdateData);

            ActivityLog::log(
                'report_synced',
                'Report synced with concern update: '.$report->title,
                $report->id
            );
        }

        ActivityLog::log(
            'concern_updated',
            'Concern updated: '.$concern->title,
            $concern->id
        );

        $changes = [];
        if ($oldStatus !== $concern->status) {
            $changes[] = "status changed from {$oldStatus} to {$concern->status}";
        }
        if ($oldPriority !== $concern->priority) {
            $changes[] = 'priority updated';
        }
        if ($oldAssignedTo !== $concern->assigned_to) {
            $changes[] = 'assignment updated';
        }

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern Updated',
            'Your concern has been updated'.($changes ? ': '.implode(', ', $changes).'.' : '.'),
            $user
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Concern updated successfully']);
        }

        return redirect()->route('concerns.my')
            ->with('success', 'Concern updated successfully');
    }

    // API: Get concern data for edit modal (JSON)
    public function apiEdit($id)
    {
        $user = auth()->user();
        DefaultCategoryService::ensureDefaults();
        $concern = Concern::findOrFail($id);

        $isOwner = $concern->user_id === $user->id;
        $isAssignedMis = $user->role === 'mis' && $concern->assigned_to === $user->id;
        $isMisUser = $user->role === 'mis';
        $isAdmin = in_array($user->role, ['mis', 'school_admin', 'building_admin']);
        $editableStatusesForOwner = ['Pending', 'Assigned'];

        if (! $isOwner && ! $isAssignedMis && ! $isMisUser && ! $isAdmin) {
            return response()->json(['error' => 'You cannot edit this concern.'], 403);
        }

        if (($isOwner || $isAssignedMis) && ! $isMisUser && ! $isAdmin && ! in_array($concern->status, $editableStatusesForOwner, true)) {
            return response()->json(['error' => 'You cannot edit this concern once work has started or it has been completed.'], 403);
        }

        $categories = Category::all();

        // Get maintenance users for assignment (only for admin roles)
        $maintenanceUsers = [];
        if ($isAdmin) {
            $maintenanceUsers = User::where('role', 'maintenance')
                ->where('is_archived', false)
                ->where('is_deleted', false)
                ->select('id', 'name', 'email')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                    ];
                })
                ->toArray();
        }

        return response()->json([
            'concern' => [
                'id' => $concern->id,
                'title' => $concern->title,
                'description' => $concern->description,
                'location' => $concern->location,
                'category_id' => $concern->category_id,
                'priority' => $concern->priority,
                'status' => $concern->status,
                'assigned_to' => $concern->assigned_to,
                'cost' => $concern->cost,
                'damaged_part' => $concern->damaged_part,
                'replaced_part' => $concern->replaced_part,
                'resolution_notes' => $concern->resolution_notes,
                'image_path' => $concern->image_path ? asset('storage/'.$concern->image_path) : null,
            ],
            'categories' => $categories->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                ];
            }),
            'maintenance_users' => $maintenanceUsers,
            'can_assign' => $isAdmin,
        ]);
    }

    public function destroy($id)
    {
        // Default delete action should perform a soft delete, not a permanent delete
        return $this->softDelete(request(), $id);
    }

    // Archive a concern - users can only archive their OWN concerns, MIS can archive any
    public function archive(Request $request, $id)
    {
        $concern = Concern::findOrFail($id);
        $user = auth()->user();

        // Owner can archive their own concerns
        // MIS and Building Admin can archive any concern
        $isOwner = $concern->user_id === $user->id;
        $isLinkedReporter = $concern->hasLinkedReporter($user->id);
        $isMIS = $user->role === 'mis';
        $isBuildingAdmin = $user->role === 'building_admin';

        if (! $isOwner && ! $isLinkedReporter && ! $isMIS && ! $isBuildingAdmin) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'You cannot archive this concern.']);
            }

            return back()->with('error', 'You cannot archive this concern.');
        }

        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $concern->getFillable())) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Invalid role for archiving.']);
            }

            return back()->with('error', 'Invalid role for archiving.');
        }

        // Check if already archived by this role
        if ($concern->$archiveColumn) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'This concern is already archived by your role.']);
            }

            return back()->with('error', 'This concern is already archived by your role.');
        }

        if ($isOwner || $isMIS || $isBuildingAdmin) {
            // Set role-specific archive column to true (role-based archiving)
            $concern->update([$archiveColumn => true]);
        }

        // Also add to user's archive using pivot table (user-based archiving)
        $folderName = $request->archive_folder_name ?? 'My Archive';
        $concern->archivedByUsers()->syncWithoutDetaching([
            $user->id => [
            'archived_at' => now(),
            'archive_folder_name' => $folderName,
            ],
        ]);

        ActivityLog::log(
            'concern_archived',
            'Concern archived: '.$concern->title,
            $concern->id
        );

        // Check if request is AJAX
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Concern archived successfully']);
        }

        return back()->with('success', 'Concern archived successfully');
    }

    // Send follow-up notification for a concern
    public function sendFollowUp($id)
    {
        $concern = Concern::findOrFail($id);
        $user = auth()->user();

        // Only the owner can send follow-up for their own concerns
        if ($concern->user_id != $user->id) {
            return response()->json(['success' => false, 'error' => 'You can only send follow-ups for your own concerns.'], 403);
        }

        // Check if concern is still pending and unassigned
        if ($concern->status !== Concern::STATUS_PENDING) {
            return response()->json(['success' => false, 'error' => 'Follow-up can only be sent for pending concerns.'], 400);
        }

        if ($concern->assigned_to !== null) {
            return response()->json(['success' => false, 'error' => 'This concern has already been assigned.'], 400);
        }

        // Check if concern is at least 1 day old
        if ($concern->created_at->diffInDays(now()) < 1) {
            return response()->json(['success' => false, 'error' => 'Follow-up can only be sent for concerns older than 1 day.'], 400);
        }

        // Check if follow-up was already sent
        if ($concern->follow_up_sent) {
            return response()->json(['success' => false, 'error' => 'A follow-up notification has already been sent for this concern.'], 400);
        }

        try {
            // Calculate days waiting
            $daysWaiting = $concern->created_at->diffInDays(now());

            // Send notification to the user
            $user->notify(new \App\Notifications\ConcernFollowUpNotification($concern, $daysWaiting));

            // Mark follow-up as sent
            $concern->update([
                'follow_up_sent' => true,
                'follow_up_sent_at' => now(),
            ]);

            // Log activity
            ActivityLog::log(
                'concern_follow_up_sent',
                'Follow-up notification sent for concern: ' . ($concern->title ?? 'Untitled'),
                $concern->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Follow-up notification sent successfully!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send follow-up notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to send follow-up notification. Please try again later.'
            ], 500);
        }
    }

    // Restore an archived concern - users can only restore their OWN concerns, MIS can restore any
    public function restore($id)
    {
        $concern = Concern::findOrFail($id);
        $user = auth()->user();

        // Owner can restore their own concerns
        // MIS and Building Admin can restore any concern
        $isOwner = $concern->user_id === $user->id;
        $isLinkedReporter = $concern->hasLinkedReporter($user->id);
        $isMIS = $user->role === 'mis';
        $isBuildingAdmin = $user->role === 'building_admin';

        if (! $isOwner && ! $isLinkedReporter && ! $isMIS && ! $isBuildingAdmin) {
            return back()->with('error', 'You cannot restore this concern.');
        }

        $role = $user->role;
        $archiveColumn = $role.'_archived';

        // Check if the column exists in the fillable array
        if (! in_array($archiveColumn, $concern->getFillable())) {
            return back()->with('error', 'Invalid role for restoring.');
        }

        if ($isOwner || $isMIS || $isBuildingAdmin) {
            // Set role-specific archive column to false (role-based restoring)
            $concern->update([$archiveColumn => false]);
        }

        // Also remove from user's archive using pivot table (user-based restoring)
        $concern->archivedByUsers()->detach($user->id);

        ActivityLog::log(
            'concern_restored',
            'Concern restored from archive: '.$concern->title,
            $concern->id
        );

        return back()->with('success', 'Concern restored successfully');
    }

    // Soft delete a concern - users can only delete their OWN concerns, MIS can delete any
    public function softDelete(Request $request, $id)
    {
        $concern = Concern::findOrFail($id);
        $user = auth()->user();

        // Check if concern is assigned but not resolved - assigned concerns cannot be deleted unless resolved
        if ($concern->assigned_to && $concern->status !== 'Resolved') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Cannot delete assigned concerns that are not resolved. Please wait for resolution or unassign first.'], 403);
            }

            return back()->with('error', 'Cannot delete assigned concerns that are not resolved. Please wait for resolution or unassign first.');
        }

        // Only owner can soft delete their own concerns
        // MIS and Building Admin can soft delete any concern
        $isOwner = $concern->user_id === $user->id;
        $isLinkedReporter = $concern->hasLinkedReporter($user->id);
        $isMIS = $user->role === 'mis';
        $isBuildingAdmin = $user->role === 'building_admin';

        if ($isLinkedReporter && ! $isOwner && ! $isMIS && ! $isBuildingAdmin) {
            if (Concern::supportsLinkedReporters()) {
                ConcernReporter::where('concern_id', $concern->id)
                    ->where('user_id', $user->id)
                    ->delete();

                $reportCount = ConcernReporter::where('concern_id', $concern->id)->count();
                $reportCount = max(1, $reportCount);

                if (Concern::supportsReportCount()) {
                    $concern->forceFill(['report_count' => $reportCount])->save();
                }

                if (Report::supportsReportCount()) {
                    Report::where('concern_id', $concern->id)
                        ->update(['report_count' => $reportCount]);
                }
            }

            ActivityLog::log(
                'duplicate_concern_unlinked',
                'Linked concern reporter removed from ticket #'.$concern->id.': '.$user->email,
                $concern->id
            );

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Concern removed from your list successfully']);
            }

            return redirect()->route('concerns.my')
                ->with('success', 'Concern removed from your list successfully');
        }

        if (! $isOwner && ! $isMIS && ! $isBuildingAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot delete this concern.'], 403);
            }

            return back()->with('error', 'You cannot delete this concern.');
        }

        // Get or create Deleted Concerns folder
        $deletedFolder = ArchiveFolder::firstOrCreate(
            ['name' => 'Deleted Concerns', 'type' => 'concerns'],
            ['description' => 'Soft deleted concerns', 'item_count' => 0]
        );

        $concern->is_deleted = true;
        $concern->deleted_at = now();
        $concern->deleted_by = auth()->id();
        $concern->archive_folder_id = $deletedFolder->id;
        $concern->save();

        // Update folder item count
        $deletedFolder->item_count = Concern::where('archive_folder_id', $deletedFolder->id)->count();
        $deletedFolder->save();

        ActivityLog::log(
            'concern_soft_deleted',
            'Concern moved to deleted: '.$concern->title,
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Concern moved to deleted successfully']);
        }

        return redirect()->route('concerns.my', ['view' => 'deleted'])
            ->with('success', 'Concern moved to deleted successfully');
    }

    // Restore a deleted concern - users can only restore their OWN concerns, MIS can restore any
    public function restoreDeleted(Request $request, $id)
    {
        $concern = Concern::findOrFail($id);
        $user = auth()->user();

        // Owner can restore their own deleted concerns
        // MIS and Building Admin can restore any concern
        $isOwner = $concern->user_id === $user->id;
        $isMIS = $user->role === 'mis';
        $isBuildingAdmin = $user->role === 'building_admin';

        if (! $isOwner && ! $isMIS && ! $isBuildingAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot restore this concern.'], 403);
            }

            return back()->with('error', 'You cannot restore this concern.');
        }

        // Get the folder before clearing
        $folderId = $concern->archive_folder_id;

        $concern->is_deleted = false;
        $concern->deleted_at = null;
        $concern->deleted_by = null;
        $concern->archive_folder_id = null;
        $concern->save();

        // Update folder item count if folder exists
        if ($folderId) {
            $folder = ArchiveFolder::find($folderId);
            if ($folder) {
                $folder->item_count = Concern::where('archive_folder_id', $folderId)->count();
                $folder->save();
            }
        }

        ActivityLog::log(
            'concern_restored_deleted',
            'Concern restored from deleted: '.$concern->title,
            $concern->id
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Concern restored successfully']);
        }

        return back()->with('success', 'Concern restored successfully');
    }

    // Permanently delete a concern - users can only permanently delete their OWN concerns
    public function permanentDelete(Request $request, $id)
    {
        try {
            $concern = Concern::findOrFail($id);

            // Only owner can permanently delete their own concerns
            if ($concern->user_id != auth()->id()) {
                Log::warning('Unauthorized permanent delete attempt', [
                    'user_id' => auth()->id(),
                    'concern_id' => $id,
                    'concern_owner' => $concern->user_id
                ]);
                
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'You cannot permanently delete this concern.'], 403);
                }

                return back()->with('error', 'You cannot permanently delete this concern.');
            }

            $concernTitle = $concern->title;

            // Delete image if exists
            if ($concern->image_path) {
                // Check if it's a Supabase URL
                if (str_contains($concern->image_path, 'supabase')) {
                    $supabaseStorage = new SupabaseStorage();
                    $deleted = $supabaseStorage->delete($concern->image_path);
                    Log::info('Deleted concern image from Supabase', [
                        'concern_id' => $id,
                        'url' => $concern->image_path,
                        'success' => $deleted
                    ]);
                } else {
                    // Legacy local storage
                    if (Storage::disk('public')->exists($concern->image_path)) {
                        Storage::disk('public')->delete($concern->image_path);
                    }
                }
            }

            $concern->delete();

            ActivityLog::log(
                'concern_permanent_deleted',
                'Concern permanently deleted: '.$concernTitle,
                $id
            );

            Log::info('Concern permanently deleted', [
                'concern_id' => $id,
                'title' => $concernTitle,
                'user_id' => auth()->id()
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Concern permanently deleted']);
            }

            return back()->with('success', 'Concern permanently deleted');
            
        } catch (\Exception $e) {
            Log::error('Error permanently deleting concern', [
                'concern_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to delete concern: ' . $e->getMessage()], 500);
            }
            
            return back()->with('error', 'Failed to delete concern: ' . $e->getMessage());
        }
    }

    // Batch archive concerns - users can only archive their OWN concerns
    public function batchArchive(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:concerns,id',
        ]);

        $user = auth()->user();
        $count = 0;
        foreach ($request->ids as $id) {
            $concern = Concern::find($id);
            if ($concern) {
                // Only owner can archive their own concerns, or MIS can archive any
                $isOwner = $concern->user_id === $user->id;
                $isMIS = $user->role === 'mis';

                if ($isOwner || $isMIS) {
                    // Check if already archived by this user
                    if (! $concern->isArchivedByUser($user->id)) {
                        $folderName = $request->archive_folder_name ?? 'My Archive';
                        $concern->archivedByUsers()->attach($user->id, [
                            'archived_at' => now(),
                            'archive_folder_name' => $folderName,
                        ]);
                        $count++;

                        ActivityLog::log(
                            'concern_archived',
                            'Concern archived: '.$concern->title,
                            $concern->id
                        );
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} concern(s) archived successfully",
        ]);
    }

    // Batch soft delete concerns - users can only delete their OWN concerns
    public function batchSoftDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:concerns,id',
        ]);

        // Get or create Deleted Concerns folder
        $deletedFolder = ArchiveFolder::firstOrCreate(
            ['name' => 'Deleted Concerns', 'type' => 'concerns'],
            ['description' => 'Soft deleted concerns', 'item_count' => 0]
        );

        $count = 0;
        $skipped = 0;
        $user = auth()->user();
        foreach ($request->ids as $id) {
            $concern = Concern::find($id);
            if ($concern) {
                // Skip assigned concerns that are not resolved
                if ($concern->assigned_to && $concern->status !== 'Resolved') {
                    $skipped++;
                    continue;
                }

                // Only owner can delete their own concerns, or MIS can delete any
                $isOwner = $concern->user_id === $user->id;
                $isMIS = $user->role === 'mis';

                if ($isOwner || $isMIS) {
                    if (! $concern->is_deleted) {
                        $concern->is_deleted = true;
                        $concern->deleted_at = now();
                        $concern->deleted_by = $user->id;
                        $concern->archive_folder_id = $deletedFolder->id;
                        $concern->save();
                        $count++;

                        ActivityLog::log(
                            'concern_soft_deleted',
                            'Concern moved to deleted: '.$concern->title,
                            $concern->id
                        );
                    }
                }
            }
        }

        // Update folder item count
        $deletedFolder->item_count = Concern::where('archive_folder_id', $deletedFolder->id)->count();
        $deletedFolder->save();

        return response()->json([
            'success' => true,
            'message' => "{$count} concern(s) moved to deleted" . ($skipped > 0 ? ", {$skipped} assigned concern(s) skipped" : ""),
        ]);
    }

    // Batch restore archived concerns - users can only restore their OWN concerns
    public function batchRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:concerns,id',
        ]);

        $count = 0;
        $user = auth()->user();
        foreach ($request->ids as $id) {
            $concern = Concern::find($id);
            if ($concern) {
                // Only owner can restore their own concerns, or MIS can restore any
                $isOwner = $concern->user_id === $user->id;
                $isMIS = $user->role === 'mis';

                if ($isOwner || $isMIS) {
                    // Check if this user has archived this concern
                    if ($concern->isArchivedByUser($user->id)) {
                        // Remove from user's archive using pivot table
                        $concern->archivedByUsers()->detach($user->id);
                        $count++;

                        ActivityLog::log(
                            'concern_restored',
                            'Concern restored from archive: '.$concern->title,
                            $concern->id
                        );
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} concern(s) restored successfully",
        ]);
    }

    // Batch restore deleted concerns - users can only restore their OWN concerns
    public function batchRestoreDeleted(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:concerns,id',
        ]);

        $count = 0;
        $user = auth()->user();
        foreach ($request->ids as $id) {
            $concern = Concern::find($id);
            if ($concern) {
                // Only owner can restore their own deleted concerns, or MIS can restore any
                $isOwner = $concern->user_id === $user->id;
                $isMIS = $user->role === 'mis';

                if ($isOwner || $isMIS) {
                    if ($concern->is_deleted) {
                        $folderId = $concern->archive_folder_id;

                        $concern->is_deleted = false;
                        $concern->deleted_at = null;
                        $concern->deleted_by = null;
                        $concern->archive_folder_id = null;
                        $concern->save();
                        $count++;

                        // Update folder item count
                        if ($folderId) {
                            $folder = ArchiveFolder::find($folderId);
                            if ($folder) {
                                $folder->item_count = Concern::where('archive_folder_id', $folderId)->count();
                                $folder->save();
                            }
                        }

                        ActivityLog::log(
                            'concern_restored_deleted',
                            'Concern restored from deleted: '.$concern->title,
                            $concern->id
                        );
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} concern(s) restored successfully",
        ]);
    }

    // Batch permanent delete concerns - users can only permanently delete their OWN concerns
    public function batchPermanentDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:concerns,id',
            ]);

            $count = 0;
            $errors = [];
            
            foreach ($request->ids as $id) {
                try {
                    $concern = Concern::find($id);
                    if ($concern) {
                        // Only owner can permanently delete their own concerns
                        if ($concern->user_id == auth()->id()) {
                            $concernTitle = $concern->title;

                            // Delete image if exists
                            if ($concern->image_path) {
                                // Check if it's a Supabase URL
                                if (str_contains($concern->image_path, 'supabase')) {
                                    $supabaseStorage = new SupabaseStorage();
                                    $deleted = $supabaseStorage->delete($concern->image_path);
                                    Log::info('Deleted concern image from Supabase', [
                                        'concern_id' => $id,
                                        'url' => $concern->image_path,
                                        'success' => $deleted
                                    ]);
                                } else {
                                    // Legacy local storage
                                    if (Storage::disk('public')->exists($concern->image_path)) {
                                        Storage::disk('public')->delete($concern->image_path);
                                    }
                                }
                            }

                            $concern->delete();
                            $count++;

                            ActivityLog::log(
                                'concern_permanent_deleted',
                                'Concern permanently deleted: '.$concernTitle,
                                $id
                            );
                        } else {
                            $errors[] = "Concern #{$id}: Not authorized";
                            Log::warning('Unauthorized batch permanent delete attempt', [
                                'user_id' => auth()->id(),
                                'concern_id' => $id,
                                'concern_owner' => $concern->user_id
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Concern #{$id}: " . $e->getMessage();
                    Log::error('Error in batch permanent delete', [
                        'concern_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = "{$count} concern(s) permanently deleted";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', $errors);
            }

            Log::info('Batch permanent delete completed', [
                'count' => $count,
                'errors' => count($errors),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $count,
                'errors' => $errors
            ]);
            
        } catch (\Exception $e) {
            Log::error('Batch permanent delete error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error permanently deleting concerns: ' . $e->getMessage()
            ], 500);
        }
    }

    // User archive page - shows user's OWN archived concerns only
    public function userArchive(Request $request)
    {
        $status = $request->get('status', null);

        // Get archived concerns - USER CAN ONLY SEE THEIR OWN
        $concernsQuery = Concern::where('is_archived', true)
            ->where('is_deleted', false)
            ->where('user_id', auth()->id()) // User can only see their own
            ->with('categoryRelation', 'user');

        // Filter by status if provided
        if ($status) {
            $concernsQuery->where('status', $status);
        }

        $archivedConcerns = $concernsQuery->orderBy('updated_at', 'desc')->get();

        // Get archived event requests (only for faculty)
        $archivedEvents = collect([]);
        if (auth()->user()->role === 'faculty') {
            $eventsQuery = EventRequest::where('is_archived', true)
                ->where('user_id', auth()->id());

            // Filter by status if provided
            if ($status) {
                $eventsQuery->where('status', $status);
            }

            $archivedEvents = $eventsQuery->orderBy('updated_at', 'desc')->get();
        }

        return view('concerns.archive', compact('archivedConcerns', 'archivedEvents', 'status'));
    }

    // ============ API METHODS ============

    /**
     * API: List concerns - users can only see their OWN concerns
     */
    public function apiIndex(Request $request)
    {
        $userId = $request->user()->id;
        $query = Concern::with('categoryRelation', 'user')->forUser($userId);
        if ($request->archived === '1') {
            $query->where('is_archived', true);
        } elseif ($request->archived !== 'all') {
            $query->where('is_archived', false);
        }
        $perPage = auth()->user()->items_per_page ?? 10;

        return response()->json($query->latest()->paginate($perPage));
    }

    /**
     * API: Create concern
     */
    public function apiStore(Request $request)
    {
        if ($request->user()->role === 'maintenance') {
            return response()->json(['error' => 'Cannot submit concerns'], 403);
        }

        $request->validate([
            'location' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $concern = Concern::create([
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id,
            'status' => 'Pending',
            'priority' => $request->priority ?? 'medium',
        ]);

        return response()->json(['concern' => $concern], 201);
    }

    /**
     * API: Show concern - users can view their OWN concerns, admins can view ALL concerns
     */
    public function apiShow(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            // For admins and MIS, include archived and deleted concerns
            $isMIS = $user->role === 'mis';
            $isAdmin = in_array($user->role, ['admin', 'building_admin', 'school_admin']);
            
            if ($isMIS || $isAdmin) {
                // Admins can view all concerns including archived/deleted
                $concern = Concern::with('categoryRelation', 'user', 'assignedTo')
                    ->withoutGlobalScopes()
                    ->find($id);
            } else {
                // Regular users can only view non-archived, non-deleted concerns
                $concern = Concern::with('categoryRelation', 'user', 'assignedTo')
                    ->where('is_archived', false)
                    ->where('is_deleted', false)
                    ->find($id);
            }

            if (! $concern) {
                return response()->json(['error' => 'Concern not found'], 404);
            }

            // Users can view their own concerns, concerns assigned to them, or MIS/Admin can view all
            $isOwner = $concern->user_id == $user->id;
            $isLinkedReporter = $concern->hasLinkedReporter($user->id);
            $isAssigned = $concern->assigned_to == $user->id;

            if (! $isOwner && ! $isLinkedReporter && ! $isAssigned && ! $isMIS && ! $isAdmin) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Determine if user can see sensitive fields (OWASP API3: Object Property Level Authorization)
            $canSeeSensitiveFields = in_array($user->role, ['building_admin', 'mis', 'school_admin', 'admin']) || $concern->assigned_to === $user->id;
            $displayUser = ($isLinkedReporter && ! $isOwner) ? $user : $concern->user;

            // Format the concern data for the view modal (matching viewConcern expectations)
            $formattedConcern = [
                'id' => $concern->id,
                'title' => $concern->title,
                'description' => $concern->description,
                'location' => $concern->location,
                'issue' => $concern->issue ?: $concern->title,
                'categoryRelation' => $concern->categoryRelation,
                'user' => $displayUser ? [
                    'id' => $displayUser->id,
                    'name' => $displayUser->name,
                    'role' => $displayUser->role,
                ] : null,
                'assignedTo' => $concern->assignedTo,
                'assigned_to' => $concern->assigned_to,
                'report_id' => Report::where('concern_id', $concern->id)->latest('id')->value('id'),
                'status' => $concern->status,
                'priority' => $concern->priority,
                'report_count' => $concern->report_count ?? 1,
                'created_at' => $concern->created_at ? $concern->created_at->format('M d, Y h:i A') : null,
                'assigned_at' => $concern->assigned_at ? $concern->assigned_at->format('M d, Y h:i A') : null,
                'in_progress_at' => $concern->in_progress_at ? $concern->in_progress_at->format('M d, Y h:i A') : null,
                'resolved_at' => $concern->resolved_at ? $concern->resolved_at->format('M d, Y h:i A') : null,
                'image_path' => $concern->image_path ? (str_starts_with($concern->image_path, 'http') ? $concern->image_path : asset('storage/'.$concern->image_path)) : null,
            ];

            // Add sensitive fields only for authorized users
            if ($canSeeSensitiveFields) {
                $formattedConcern['resolution_notes'] = $concern->resolution_notes;
                $formattedConcern['cost'] = $concern->cost;
                $formattedConcern['damaged_part'] = $concern->damaged_part;
                $formattedConcern['replaced_part'] = $concern->replaced_part;
            }

            return response()->json(['concern' => $formattedConcern]);
        } catch (\Exception $e) {
            \Log::error('Error in apiShow: ' . $e->getMessage(), [
                'concern_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'An error occurred while retrieving the concern.',
                'message' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * API: Get data for editing concern - users can only edit their OWN concerns
     */
    public function apiEditData(Request $request, $id)
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return response()->json(['error' => 'Not authenticated'], 401);
            }

            $concern = Concern::find($id);

            if (! $concern) {
                return response()->json(['error' => 'Concern not found'], 404);
            }

            // Owners can edit their own concerns even if auto-assignment changed the status
            // to Assigned. For MIS users, also allow concerns assigned to them, since
            // self-submitted MIS concerns may be auto-assigned back to the same user.
            $isOwner = $concern->user_id == $user->id;
            $isLinkedReporter = $concern->hasLinkedReporter($user->id);
            $isAssignedMis = $user->role === 'mis' && $concern->assigned_to == $user->id;
            $isMisUser = $user->role === 'mis';
            $isAdmin = in_array($user->role, ['admin', 'school_admin', 'building_admin', 'mis']);
            $editableStatusesForOwner = ['Pending', 'Assigned'];

            if (! $isOwner && ! $isLinkedReporter && ! $isAssignedMis && ! $isMisUser && ! $isAdmin) {
                return response()->json(['error' => 'You cannot edit this concern.'], 403);
            }

            if (($isOwner || $isLinkedReporter || $isAssignedMis) && ! $isMisUser && ! $isAdmin && ! in_array($concern->status, $editableStatusesForOwner, true)) {
                return response()->json(['error' => 'You cannot edit this concern once work has started or it has been completed.'], 403);
            }

            // Get categories with their issues
            DefaultCategoryService::ensureDefaults();
            $categories = Category::select('id', 'name', 'issues')->get();

            // Check if user can assign (owner or admin)
            $isOwner = $concern->user_id === $user->id;
            $isAdmin = in_array($user->role, ['admin', 'school_admin', 'building_admin']);
            $canAssign = $isOwner || $isAdmin;

            // Get maintenance users if can assign
            $maintenanceUsers = [];
            if ($canAssign) {
                $maintenanceUsers = User::where('role', 'maintenance')
                    ->where('is_archived', false)
                    ->where('is_deleted', false)
                    ->select('id', 'name')
                    ->orderBy('name')
                    ->get();
            }

            // Format concern data for edit
            $formattedConcern = [
                'id' => $concern->id,
                'title' => $concern->title,
                'description' => $concern->description,
                'location' => $concern->location,
                'location_type' => $concern->location_type,
                'room_number' => $concern->room_number,
                'category_id' => $concern->category_id,
                'priority' => $concern->priority,
                'status' => $concern->status,
                'image_path' => $concern->image_path ? (str_starts_with($concern->image_path, 'http') ? $concern->image_path : asset('storage/'.$concern->image_path)) : null,
            ];

            return response()->json([
                'concern' => $formattedConcern,
                'categories' => $categories,
                'maintenance_users' => $maintenanceUsers,
                'can_assign' => $canAssign,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while retrieving concern data.'], 500);
        }
    }

    /**
     * API: Update concern - users can only update their OWN concerns
     */
    public function apiUpdate(Request $request, $id)
    {
        $concern = Concern::findOrFail($id);

        $user = $request->user();
        $isOwner = $concern->user_id === $user->id;
        $isLinkedReporter = $concern->hasLinkedReporter($user->id);
        $isAssignedMis = $user->role === 'mis' && $concern->assigned_to === $user->id;
        $isAdmin = in_array($user->role, ['mis', 'admin', 'school_admin', 'building_admin']);
        $editableStatusesForOwner = ['Pending', 'Assigned'];

        if (! $isOwner && ! $isLinkedReporter && ! $isAssignedMis && ! $isAdmin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (($isOwner || $isLinkedReporter || $isAssignedMis) && ! $isAdmin && ! in_array($concern->status, $editableStatusesForOwner, true)) {
            return response()->json(['error' => 'You cannot edit this concern once work has started or it has been completed.'], 403);
        }

        // Only Building Admin and MIS can update priority
        $updateFields = ['title', 'description', 'location'];
        if (in_array($user->role, ['building_admin', 'mis', 'admin'])) {
            $updateFields[] = 'priority';
        }

        $concern->update($request->only($updateFields));

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern Updated',
            'Your concern details have been updated.',
            $user
        );

        return response()->json(['concern' => $concern]);
    }

    /**
     * API: Delete concern - users can only delete their OWN concerns
     */
    public function apiDestroy(Request $request, $id)
    {
        // API delete should also soft delete by default
        return $this->softDelete($request, $id);
    }

    /**
     * Show assigned concerns for maintenance users
     * Maintenance can view concerns assigned to them
     */
    /**
     * Acknowledge a concern - MIS acknowledges they will work on it
     */
    public function misAcknowledge(Request $request, $id)
    {
        // Only MIS can acknowledge
        if (auth()->user()->role !== 'mis') {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Access denied.'], 403);
            }
            return back()->with('error', 'Access denied.');
        }

        $concern = Concern::findOrFail($id);

        // Verify the concern is assigned to any MIS user (department-level access)
        $misUserIds = \App\Models\User::where('role', 'mis')->pluck('id');
        if (! $misUserIds->contains($concern->assigned_to)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'This concern is not assigned to the MIS department.'], 403);
            }
            return back()->with('error', 'This concern is not assigned to the MIS department.');
        }

        // Update status to In Progress
        $concern->status = 'In Progress';
        $concern->save();

        // Log activity
        ActivityLog::log(
            'concern_acknowledged',
            'Concern acknowledged by MIS: '.auth()->user()->name,
            $concern->id
        );

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern In Progress',
            'Your concern has been acknowledged and is now in progress.',
            auth()->user()
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Concern acknowledged! You can now work on it.']);
        }

        return back()->with('success', 'Concern acknowledged! You can now work on it.');
    }

    /**
     * Acknowledge a concern - maintenance acknowledges they will work on it
     */
    public function acknowledge(Request $request, $id)
    {
        // Only maintenance can acknowledge
        if (auth()->user()->role !== 'maintenance') {
            return back()->with('error', 'Access denied.');
        }

        $concern = Concern::findOrFail($id);

        // Verify the concern is assigned to this maintenance user
        if ($concern->assigned_to !== auth()->id()) {
            return back()->with('error', 'This concern is not assigned to you.');
        }

        $oldStatus = $concern->status;

        // Update status to In Progress
        $concern->status = 'In Progress';
        $concern->save();

        // Log activity
        ActivityLog::log(
            'concern_acknowledged',
            'Concern acknowledged by maintenance: '.auth()->user()->name,
            $concern->id
        );

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern In Progress',
            'Your concern has been acknowledged and is now in progress.',
            auth()->user()
        );

        return back()->with('success', 'Concern acknowledged! You can now work on it.');
    }

    /**
     * API: Get assigned concerns for maintenance (JSON)
     */
    public function apiAssignedConcerns(Request $request)
    {
        // Only maintenance can access this API
        if (auth()->user()->role !== 'maintenance') {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $concerns = Concern::with('categoryRelation', 'user')
            ->where('assigned_to', auth()->id())
            ->where('is_deleted', false)
            ->where('is_archived', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['concerns' => $concerns]);
    }

    /**
     * API: Acknowledge a concern (JSON)
     */
    public function apiAcknowledge(Request $request, $id)
    {
        // Only maintenance can acknowledge
        if (auth()->user()->role !== 'maintenance') {
            return response()->json(['error' => 'Access denied.'], 403);
        }

        $concern = Concern::findOrFail($id);

        // Verify the concern is assigned to this maintenance user
        if ($concern->assigned_to !== auth()->id()) {
            return response()->json(['error' => 'This concern is not assigned to you.'], 403);
        }

        // Update status to In Progress
        $concern->status = 'In Progress';
        $concern->save();

        // Log activity
        ActivityLog::log(
            'concern_acknowledged',
            'Concern acknowledged by maintenance: '.auth()->user()->name,
            $concern->id
        );

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern In Progress',
            'Your concern has been acknowledged and is now in progress.',
            auth()->user()
        );

        return response()->json(['success' => true, 'message' => 'Concern acknowledged successfully!']);
    }

    /**
     * Get maintenance users for assignment dropdown (JSON)
     */
    public function getMaintenanceUsers()
    {
        $maintenanceUsers = User::where('role', 'maintenance')
            ->where('is_archived', false)
            ->where('is_deleted', false)
            ->select('id', 'name', 'email', 'department')
            ->orderBy('name')
            ->get();

        return response()->json(['users' => $maintenanceUsers]);
    }

    /**
     * Assign a concern to a maintenance user
     */
    public function assign(Request $request, $id)
    {
        $request->validate([
            'assigned_to' => 'required|exists:maintenance_staff,id',
        ]);

        $user = auth()->user();
        $concern = Concern::findOrFail($id);

        // Check if user can assign: owner OR admin/school_admin/building_admin
        $isOwner = $concern->user_id === $user->id;
        $isAdmin = in_array($user->role, ['admin', 'school_admin', 'building_admin']);

        // Only owner or admin can assign
        if (! $isOwner && ! $isAdmin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You cannot assign this concern.'], 403);
            }

            return redirect('/dashboard')->with('error', 'You cannot assign this concern.');
        }

        // Get the maintenance staff member
        $maintenanceStaff = \App\Models\MaintenanceStaff::findOrFail($request->assigned_to);

        // Update the concern
        $concern->assigned_to = $request->assigned_to;
        $concern->assigned_at = now();
        $concern->status = 'Assigned';
        $concern->save();

        // Log activity
        ActivityLog::log(
            'concern_assigned',
            "Concern assigned to {$maintenanceStaff->name}",
            $concern->id
        );

        $this->sendConcernUpdateNotification(
            $concern,
            'Concern Assigned',
            "Your concern has been assigned to {$maintenanceStaff->name}.",
            $user
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "Concern assigned to {$maintenanceStaff->name} successfully!"]);
        }

        return back()->with('success', "Concern assigned to {$maintenanceStaff->name} successfully!");
    }
}
