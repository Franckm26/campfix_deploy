<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ArchiveFolder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'item_count',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'item_count' => 'integer',
    ];

    /**
     * Get archived concerns in this folder
     */
    public function concerns(): HasMany
    {
        return $this->hasMany(Concern::class, 'archive_folder_id');
    }

    /**
     * Get archived reports in this folder
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'archive_folder_id');
    }

    /**
     * Get archived facility requests in this folder
     */
    public function facilityRequests(): HasMany
    {
        return $this->hasMany(FacilityRequest::class, 'archive_folder_id');
    }

    /**
     * Get archived event requests in this folder
     */
    public function eventRequests(): HasMany
    {
        return $this->hasMany(EventRequest::class, 'archive_folder_id');
    }

    /**
     * Update the item count
     */
    public function updateItemCount(): void
    {
        $this->item_count = $this->concerns()->count()
            + $this->reports()->count()
            + $this->facilityRequests()->count()
            + $this->eventRequests()->count();
        $this->save();
    }
}
