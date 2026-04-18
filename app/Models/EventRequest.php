<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventRequest extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'event_date',
        'location',
        'start_time',
        'end_time',
        'category',
        'other_category',
        'department',
        'level',
        'education_level',
        'priority',
        'status',
        'approved_by',
        'approved_at',
        'notes',
        'is_archived',
        'admin_archived',
        'student_archived',
        'faculty_archived',
        'building_admin_archived',
        'school_admin_archived',
        'academic_head_archived',
        'program_head_archived',
        'mis_archived',
        'maintenance_archived',
        'approval_level',
        'approval_history',
        'approved_by_level_1',
        'approved_at_level_1',
        'approved_by_level_2',
        'approved_at_level_2',
        'approved_by_level_3',
        'approved_at_level_3',
        'materials_needed',
        'archive_folder_id',
        'image_path',
        'is_deleted',
        'deleted_by',
        'student_deleted',
        'faculty_deleted',
        'building_admin_deleted',
        'school_admin_deleted',
        'academic_head_deleted',
        'program_head_deleted',
        'mis_deleted',
        'maintenance_deleted',
    ];

    protected $casts = [
        'event_date' => 'date',
        'approved_at' => 'datetime',
        'approved_at_level_1' => 'datetime',
        'approved_at_level_2' => 'datetime',
        'approved_at_level_3' => 'datetime',
        'approval_history' => 'array',
        'materials_needed' => 'array',
        'is_archived' => 'boolean',
        'admin_archived' => 'boolean',
        'student_archived' => 'boolean',
        'faculty_archived' => 'boolean',
        'building_admin_archived' => 'boolean',
        'school_admin_archived' => 'boolean',
        'academic_head_archived' => 'boolean',
        'program_head_archived' => 'boolean',
        'mis_archived' => 'boolean',
        'maintenance_archived' => 'boolean',
        'student_deleted' => 'boolean',
        'faculty_deleted' => 'boolean',
        'building_admin_deleted' => 'boolean',
        'school_admin_deleted' => 'boolean',
        'academic_head_deleted' => 'boolean',
        'program_head_deleted' => 'boolean',
        'mis_deleted' => 'boolean',
        'maintenance_deleted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function archiveFolder()
    {
        return $this->belongsTo(ArchiveFolder::class, 'archive_folder_id');
    }

    /**
     * Get the user who deleted this event
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get users who have archived this event request
     */
    public function archivedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_archived_event_requests', 'event_request_id', 'user_id')
            ->withPivot('archived_at', 'archive_folder_name')
            ->withTimestamps();
    }

    /**
     * Check if a specific user has archived this event request
     */
    public function isArchivedByUser($userId)
    {
        return $this->archivedByUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Scope for event requests not archived by a specific user
     */
    public function scopeNotArchivedByUser($query, $userId)
    {
        return $query->whereDoesntHave('archivedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope for event requests archived by a specific user
     */
    public function scopeArchivedByUser($query, $userId)
    {
        return $query->whereHas('archivedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope to get event requests NOT deleted by a specific role
     */
    public function scopeNotDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, false);
    }

    /**
     * Scope to get event requests deleted by a specific role
     */
    public function scopeDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, true);
    }

    // Status constants
    const STATUS_PENDING = 'Pending';

    const STATUS_APPROVED = 'Approved';

    const STATUS_REJECTED = 'Rejected';

    const STATUS_CANCELLED = 'Cancelled';

    // Approval level constants
    const LEVEL_NONE = 0;

    const LEVEL_1_PROGRAM_HEAD = 1;

    const LEVEL_2_ACADEMIC_HEAD = 2;

    const LEVEL_3_BUILDING_ADMIN = 3;

    const LEVEL_4_SCHOOL_ADMIN = 4;

    const LEVEL_APPROVED = 5;

    // Category constants
    const CATEGORY_EVENT = 'event';

    const CATEGORY_MEETING = 'meeting';

    const CATEGORY_ACTIVITY = 'activity';

    const CATEGORY_TRAINING = 'training';

    const CATEGORY_OTHER = 'other';

    // Priority constants
    const PRIORITY_LOW = 'low';

    const PRIORITY_MEDIUM = 'medium';

    const PRIORITY_HIGH = 'high';

    const PRIORITY_URGENT = 'urgent';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function getCategories()
    {
        return [
            self::CATEGORY_EVENT,
            self::CATEGORY_MEETING,
            self::CATEGORY_ACTIVITY,
            self::CATEGORY_TRAINING,
            self::CATEGORY_OTHER,
        ];
    }

    public static function getPriorities()
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
        ];
    }

    public function getPriorityLabel()
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
            default => 'Medium'
        };
    }

    public function getPriorityColor()
    {
        return match ($this->priority) {
            self::PRIORITY_LOW => 'green',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
            default => 'gray'
        };
    }

    public static function getDepartments()
    {
        return [
            'GE',
            'ICT',
            'Business Management',
            'THM',
        ];
    }

    public function getStatusColor()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_CANCELLED => 'secondary',
            default => 'gray'
        };
    }

    public function getCategoryLabel()
    {
        return match ($this->category) {
            self::CATEGORY_EVENT => 'Event',
            self::CATEGORY_MEETING => 'Meeting',
            self::CATEGORY_ACTIVITY => 'Activity',
            self::CATEGORY_TRAINING => 'Training',
            self::CATEGORY_OTHER => 'Other',
            default => 'Unknown'
        };
    }

    // Approval level methods
    public static function getApprovalLevels()
    {
        return [
            self::LEVEL_NONE => 'Not Submitted',
            self::LEVEL_1_PROGRAM_HEAD => 'Program Head Review',
            self::LEVEL_2_ACADEMIC_HEAD => 'Academic Head Review',
            self::LEVEL_3_BUILDING_ADMIN => 'Building Admin Review',
            self::LEVEL_4_SCHOOL_ADMIN => 'School Admin Review',
            self::LEVEL_APPROVED => 'Approved',
        ];
    }

    public function getCurrentApprovalLevel()
    {
        if ($this->status === self::STATUS_REJECTED || $this->status === self::STATUS_CANCELLED) {
            return $this->approval_level;
        }

        return $this->approval_level;
    }

    public function getApprovalProgress()
    {
        if ($this->status === self::STATUS_REJECTED) {
            return [
                'current' => $this->approval_level,
                'total' => 4,
                'percentage' => ($this->approval_level / 4) * 100,
                'rejected' => true,
            ];
        }

        if ($this->status === self::STATUS_CANCELLED) {
            return [
                'current' => 0,
                'total' => 4,
                'percentage' => 0,
                'cancelled' => true,
            ];
        }

        if ($this->status === self::STATUS_APPROVED) {
            return [
                'current' => 5,
                'total' => 4,
                'percentage' => 100,
                'approved' => true,
            ];
        }

        return [
            'current' => $this->approval_level,
            'total' => 4,
            'percentage' => ($this->approval_level / 4) * 100,
            'pending' => true,
        ];
    }

    public function getApproverLevel1()
    {
        return $this->belongsTo(User::class, 'approved_by_level_1');
    }

    public function getApproverLevel2()
    {
        return $this->belongsTo(User::class, 'approved_by_level_2');
    }

    public function getApproverLevel3()
    {
        return $this->belongsTo(User::class, 'approved_by_level_3');
    }

    public function getApproverFinal()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scope for pending requests
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Scope for user's requests
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Discussion relationship
    public function discussions()
    {
        return $this->hasMany(EventDiscussion::class)->orderBy('created_at', 'asc');
    }

    /**
     * Check if all Program Heads have approved
     */
    public function isApprovedByAllProgramHeads(): bool
    {
        $programHeads = User::where('role', User::ROLE_PROGRAM_HEAD)->get();
        if ($programHeads->isEmpty()) {
            return true; // No program heads, skip this level
        }

        $approvedIds = collect($this->approval_history ?? [])
            ->where('level', 1)
            ->pluck('approver_id')
            ->filter()
            ->unique()
            ->toArray();

        // Any one Program Head approving is sufficient
        return count($approvedIds) >= 1;
    }

    /**
     * Check if all Academic Heads have approved
     */
    public function isApprovedByAllAcademicHeads(): bool
    {
        $academicHeads = User::where('role', User::ROLE_ACADEMIC_HEAD)->get();
        if ($academicHeads->isEmpty()) {
            return true; // No academic heads, skip this level
        }

        $approvedIds = collect($this->approval_history ?? [])
            ->where('level', 2)
            ->pluck('approver_id')
            ->filter()
            ->unique()
            ->toArray();

        // Any one Academic Head approving is sufficient
        return count($approvedIds) >= 1;
    }

    /**
     * Check if all Building Admins have approved
     */
    public function isApprovedByAllBuildingAdmins(): bool
    {
        $buildingAdmins = User::where('role', User::ROLE_BUILDING_ADMIN)->get();
        if ($buildingAdmins->isEmpty()) {
            return true; // No building admins, skip this level
        }

        $approvedIds = collect($this->approval_history ?? [])
            ->where('level', 3)
            ->pluck('approver_id')
            ->filter()
            ->unique()
            ->toArray();

        // Any one Building Admin approving is sufficient
        return count($approvedIds) >= 1;
    }

    /**
     * Check if all School Admins have approved
     */
    public function isApprovedByAllSchoolAdmins(): bool
    {
        $schoolAdmins = User::whereIn('role', [User::ROLE_SCHOOL_ADMIN, User::ROLE_ADMIN])->get();
        if ($schoolAdmins->isEmpty()) {
            return true; // No school admins or MIS, skip this level
        }

        $approvedIds = collect($this->approval_history ?? [])
            ->where('level', 4)
            ->pluck('approver_id')
            ->filter()
            ->unique()
            ->toArray();

        // Any one School Admin or MIS approving is sufficient for final approval
        return count($approvedIds) >= 1;
    }

    /**
     * Check if event is fully approved (all levels)
     */
    public function isFullyApproved(): bool
    {
        return $this->isApprovedByAllProgramHeads()
            && $this->isApprovedByAllAcademicHeads()
            && $this->isApprovedByAllBuildingAdmins()
            && $this->isApprovedByAllSchoolAdmins();
    }

    /**
     * Get the next approval level needed
     */
    public function getNextApprovalLevel(): ?int
    {
        if (! $this->isApprovedByAllProgramHeads()) {
            return 1;
        }
        if (! $this->isApprovedByAllAcademicHeads()) {
            return 2;
        }
        if (! $this->isApprovedByAllBuildingAdmins()) {
            return 3;
        }
        if (! $this->isApprovedByAllSchoolAdmins()) {
            return 4;
        }

        return null; // Fully approved
    }

    /**
     * Check if user has already approved at their level
     */
    public function hasUserApprovedAtLevel(int $userId, int $level): bool
    {
        $approvedIds = collect($this->approval_history ?? [])
            ->where('level', $level)
            ->pluck('approver_id')
            ->filter()
            ->toArray();

        return in_array($userId, $approvedIds);
    }
}
