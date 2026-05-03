<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceStaff extends Model
{
    use SoftDeletes;

    protected $table = 'maintenance_staff';

    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'specialization',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this staff member
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
