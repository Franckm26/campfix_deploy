<?php

namespace App\Models;

use App\Models\Concerns\HasImmutableReporterSnapshot;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Report extends Model
{
    use HasImmutableReporterSnapshot, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reported_by_name',
        'reporter_email',
        'reporter_role',
        'reporter_department',
        'reporter_phone',
        'reporter_student_id',
        'title',
        'concern_id',
        'category_id',
        'description',
        'details',
        'location',
        'severity',
        'is_safety_hazard',
        'status',
        'report_count',
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
        'is_safety_hazard' => 'boolean',
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
        'report_count' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function supportsReportCount(): bool
    {
        return Schema::hasColumn('reports', 'report_count');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Keep Technology/Internet work inside MIS and out of Building Admin views.
     * Applying this as a query scope prevents UI-only filtering from leaking data
     * into totals, charts, exports, or pagination counts.
     */
    public function scopeForOperationalRole(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query;
        }

        if ($user->role === 'mis') {
            return $query->whereHas('category', function (Builder $category) {
                $category->whereRaw('LOWER(TRIM(name)) = ?', ['technology/internet']);
            });
        }

        if ($user->role === 'building_admin') {
            return $query->where(function (Builder $reports) {
                $reports->whereNull('category_id')
                    ->orWhereDoesntHave('category')
                    ->orWhereHas('category', function (Builder $category) {
                        $category->whereRaw('LOWER(TRIM(name)) <> ?', ['technology/internet']);
                    });
            });
        }

        return $query;
    }

    public function concern()
    {
        return $this->belongsTo(Concern::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(\App\Models\MaintenanceStaff::class, 'assigned_to');
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
