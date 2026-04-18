<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserArchiveFolder extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_count',
        'is_system',
    ];

    /**
     * Get the archived users in this folder
     */
    public function archivedUsers(): HasMany
    {
        return $this->hasMany(User::class, 'archive_folder_id');
    }
}
