<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogArchiveFolder extends Model
{
    protected $fillable = ['name', 'description', 'log_count'];

    public function logs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'log_archive_folder_id');
    }
}
