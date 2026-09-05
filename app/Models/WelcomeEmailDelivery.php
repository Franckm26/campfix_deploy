<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomeEmailDelivery extends Model
{
    protected $fillable = [
        'user_id',
        'encrypted_password',
        'status',
        'attempts',
        'next_attempt_at',
        'last_attempted_at',
        'claimed_at',
        'sent_at',
        'last_error',
    ];

    protected $hidden = ['encrypted_password'];

    protected $casts = [
        'next_attempt_at' => 'datetime',
        'last_attempted_at' => 'datetime',
        'claimed_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
