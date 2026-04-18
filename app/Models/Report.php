<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'concern_id',
        'category_id',
        'description',
        'location',
        'severity',
        'status',
        'photo_path',
        'assigned_to',
        'assigned_at',
        'resolution_notes',
        'resolved_at',
        'cost',
        'damaged_part',
        'replaced_part',
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
        'auto_delete_at',
        'archive_folder_id',
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
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'auto_delete_at' => 'datetime',
        'cost' => 'decimal:2',
        'assigned_to' => 'integer',
        'user_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function concern()
    {
        return $this->belongsTo(Concern::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function statusLogs()
    {
        return $this->hasMany(ReportStatusLog::class);
    }

    public function archiveFolder()
    {
        return $this->belongsTo(ArchiveFolder::class, 'archive_folder_id');
    }

    /**
     * Get the user who deleted this report
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get users who have archived this report
     */
    public function archivedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_archived_reports')
            ->withPivot('archived_at', 'archive_folder_name')
            ->withTimestamps();
    }

    /**
     * Check if a specific user has archived this report
     */
    public function isArchivedByUser($userId)
    {
        return $this->archivedByUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Scope to get reports NOT archived by a specific user
     */
    public function scopeNotArchivedByUser($query, $userId)
    {
        return $query->whereDoesntHave('archivedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope to get reports archived by a specific user
     */
    public function scopeArchivedByUser($query, $userId)
    {
        return $query->whereHas('archivedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope to get reports NOT deleted by a specific role
     */
    public function scopeNotDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, false);
    }

    /**
     * Scope to get reports deleted by a specific role
     */
    public function scopeDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, true);
    }

    /**
     * Resolve route binding
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }
}
