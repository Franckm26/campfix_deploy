<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityRequest extends Model
{
    protected $fillable = [
        'user_id',
        'event_title',
        'facility',
        'event_date',
        'start_time',
        'end_time',
        'attendees',
        'equipment',
        'description',
        'status',
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
        'is_deleted',
        'delete_after_days',
        'student_deleted',
        'faculty_deleted',
        'building_admin_deleted',
        'school_admin_deleted',
        'academic_head_deleted',
        'program_head_deleted',
        'mis_deleted',
        'maintenance_deleted',
        'archive_folder_id',
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
        'event_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function archiveFolder()
    {
        return $this->belongsTo(ArchiveFolder::class, 'archive_folder_id');
    }

    /**
     * Get users who have archived this facility request
     */
    public function archivedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_archived_facility_requests', 'facility_request_id', 'user_id')
            ->withPivot('archived_at', 'archive_folder_name')
            ->withTimestamps();
    }

    /**
     * Check if a specific user has archived this facility request
     */
    public function isArchivedByUser($userId)
    {
        return $this->archivedByUsers()->where('user_id', $userId)->exists();
    }

    /**
     * Scope for facility requests not archived by a specific user
     */
    public function scopeNotArchivedByUser($query, $userId)
    {
        return $query->whereDoesntHave('archivedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope for facility requests archived by a specific user
     */
    public function scopeArchivedByUser($query, $userId)
    {
        return $query->whereHas('archivedByUsers', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope to get facility requests NOT deleted by a specific role
     */
    public function scopeNotDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, false);
    }

    /**
     * Scope to get facility requests deleted by a specific role
     */
    public function scopeDeletedByRole($query, $role)
    {
        $column = $role.'_deleted';

        return $query->where($column, true);
    }
}
