<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditConcern extends Model
{
    /**
     * Immutable audit table for concerns
     * NO DELETES ALLOWED - For record keeping only
     */
    
    protected $table = 'audit_concerns';
    
    protected $fillable = [
        'original_concern_id',
        'user_id',
        'reporter_name',
        'reporter_email',
        'reporter_role',
        'reporter_department',
        'reporter_phone',
        'reporter_student_id',
        'is_anonymous',
        'title',
        'description',
        'details',
        'location',
        'location_type',
        'room_number',
        'priority',
        'status',
        'image_path',
        'category_id',
        'category_name',
        'assigned_to',
        'assigned_to_name',
        'assigned_at',
        'resolution_notes',
        'resolved_at',
        'cost',
        'damaged_part',
        'replaced_part',
        'action',
        'action_by',
        'action_by_name',
        'action_at',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'assigned_at' => 'datetime',
        'resolved_at' => 'datetime',
        'action_at' => 'datetime',
        'cost' => 'decimal:2',
    ];

    /**
     * Prevent deletion of audit records
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            throw new \Exception('Audit records cannot be deleted. This is an immutable record for compliance.');
        });
    }

    /**
     * Get the original concern
     */
    public function originalConcern()
    {
        return $this->belongsTo(Concern::class, 'original_concern_id');
    }
}
