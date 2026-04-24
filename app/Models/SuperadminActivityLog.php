<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuperadminActivityLog extends Model
{
    protected $table = 'superadmin_activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata'   => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log a superadmin action (stored in separate table, invisible to regular admins)
     */
    public static function log(string $action, string $description, array $oldValues = null, array $newValues = null, array $metadata = []): self
    {
        $request = request();

        return self::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'old_values'  => $oldValues,
            'new_values'  => $newValues,
            'metadata'    => array_merge($metadata, [
                'url'    => $request->fullUrl(),
                'method' => $request->method(),
            ]),
        ]);
    }
}
