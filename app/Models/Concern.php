<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Concern extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'location_type',
        'room_number',
        'category_id',
        'user_id',
        'status',
        'priority',
        'assigned_to',
        'resolution_notes',
        'image_path',
        'is_anonymous',
        'resolved_at',
        'assigned_at',
        'follow_up_sent',
        'follow_up_sent_at',
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
        'archived_at',
        'archived_by',
        'is_deleted',
        'deleted_at',
        'deleted_by',
        'student_deleted',
        'faculty_deleted',
        'building_admin_deleted',
        'school_admin_deleted',
        'academic_head_deleted',
        'program_head_deleted',
        'mis_deleted',
        'maintenance_deleted',
        'cost',
        'damaged_part',
        'replaced_part',
        'archive_folder_id',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
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
        'is_deleted' => 'boolean',
        'student_deleted' => 'boolean',
        'faculty_deleted' => 'boolean',
        'building_admin_deleted' => 'boolean',
        'school_admin_deleted' => 'boolean',
        'academic_head_deleted' => 'boolean',
        'program_head_deleted' => 'boolean',
        'mis_deleted' => 'boolean',
        'maintenance_deleted' => 'boolean',
        'follow_up_sent' => 'boolean',
        'resolved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'follow_up_sent_at' => 'datetime',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categoryRelation()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Status constants
    const STATUS_PENDING = 'Pending';

    const STATUS_ASSIGNED = 'Assigned';

    const STATUS_IN_PROGRESS = 'In Progress';

    const STATUS_RESOLVED = 'Resolved';

    const STATUS_CLOSED = 'Closed';

    // Priority constants
    const PRIORITY_LOW = 'low';

    const PRIORITY_MEDIUM = 'medium';

    const PRIORITY_HIGH = 'high';

    const PRIORITY_URGENT = 'urgent';

    public static function getStatuses()
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
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

    // Check if concern is urgent
    public function isUrgent()
    {
        return $this->priority === self::PRIORITY_URGENT;
    }

    /**
     * Scope to get concerns NOT deleted by a specific role
     */
    public function scopeNotDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, false);
    }

    /**
     * Scope to get concerns deleted by a specific role
     */
    public function scopeDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, true);
    }

    // Check if concern is resolved
    public function isResolved()
    {
        return in_array($this->status, [self::STATUS_RESOLVED, self::STATUS_CLOSED]);
    }

    // Get priority color for UI
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

    // Get status color for UI
    public function getStatusColor()
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_ASSIGNED => 'blue',
            self::STATUS_IN_PROGRESS => 'orange',
            self::STATUS_RESOLVED => 'green',
            self::STATUS_CLOSED => 'gray',
            default => 'gray'
        };
    }

    // Scope for urgent concerns
    public function scopeUrgent($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
    }

    // Scope for pending concerns
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    // Scope for assigned concerns
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('assigned_to');
    }

    // Scope for user's concerns
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope for assigned user
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // Scope for archived concerns
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    // Scope for deleted concerns
    public function scopeDeleted($query)
    {
        return $query->where('is_deleted', true);
    }

    // Scope for active concerns (not archived and not deleted)
    public function scopeActive($query)
    {
        return $query->where('is_archived', false)->where('is_deleted', false);
    }

    /**
     * Get users who have archived this concern
     */
    public function archivedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_archived_concerns', 'concern_id', 'user_id')
            ->withPivot('archived_at', 'archive_folder_name')
            ->withTimestamps();
    }

    /**
     * Check if a specific user has archived this concern
     */
    public function isArchivedByUser($userId)
    {
        return $this->archivedByUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Scope for concerns not archived by a specific user
     */
    public function scopeNotArchivedByUser($query, $userId)
    {
        return $query->whereDoesntHave('archivedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope for concerns archived by a specific user
     */
    public function scopeArchivedByUser($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->whereHas('archivedByUsers', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
    }
}
