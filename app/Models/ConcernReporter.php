<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcernReporter extends Model
{
    protected $fillable = [
        'concern_id',
        'user_id',
        'is_original',
        'is_anonymous',
        'reported_at',
    ];

    protected $casts = [
        'is_original' => 'boolean',
        'is_anonymous' => 'boolean',
        'reported_at' => 'datetime',
    ];

    public function concern()
    {
        return $this->belongsTo(Concern::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
