<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    private const FORENSIC_ACTION_PATTERN = '/^(user_|users_|account_|login|logout|microsoft_login|password_|permission_|role_|security_|session_)/';

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'concern_id',
        'report_id',
        'event_request_id',
        'facility_request_id',
        'item_user_id',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'metadata',
        'is_archived',
        'archived_at',
        'archived_by',
        'log_archive_folder_id',
    ];

    protected $casts = [
        'old_values'  => 'array',
        'new_values'  => 'array',
        'metadata'    => 'array',
        'is_archived' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function concern()
    {
        return $this->belongsTo(Concern::class);
    }

    public function archiveFolder()
    {
        return $this->belongsTo(LogArchiveFolder::class, 'log_archive_folder_id');
    }

    /**
     * Restrict a query to the user/account/security records shown in MIS Audit Logs.
     */
    public function scopeForensic(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNotNull('item_user_id')
                ->orWhere('action', 'like', 'user_%')
                ->orWhere('action', 'like', 'users_%')
                ->orWhere('action', 'like', 'account_%')
                ->orWhere('action', 'like', 'login%')
                ->orWhere('action', 'like', 'logout%')
                ->orWhere('action', 'like', 'microsoft_login%')
                ->orWhere('action', 'like', 'password_%')
                ->orWhere('action', 'like', 'permission_%')
                ->orWhere('action', 'like', 'role_%')
                ->orWhere('action', 'like', 'security_%')
                ->orWhere('action', 'like', 'session_%');
        });
    }

    // Enhanced log activity helper with detailed change tracking
    public static function log($action, $description, $itemId = null, $itemType = 'concern', $oldValues = null, $newValues = null, $metadata = [])
    {
        // Notification delivery is system noise rather than an action the user performed.
        if (str_starts_with((string) $action, 'notification_')) {
            return null;
        }

        $forensicAction = (bool) preg_match(self::FORENSIC_ACTION_PATTERN, (string) $action);

        // Superadmin actions are logged exclusively in superadmin_activity_logs — skip here
        if (auth()->check()) {
            $actor = auth()->user();
            if ($actor->is_superadmin || $actor->role === 'superadmin') {
                return null;
            }
        }

        $request = request();
        
        $data = [
            'user_id' => auth()->id() ?? null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => array_merge($metadata, [
                'record_scope' => $forensicAction ? 'forensic' : 'personal_history',
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]),
        ];

        switch ($itemType) {
            case 'concern':
                $data['concern_id'] = $itemId;
                break;
            case 'report':
                $data['report_id'] = $itemId;
                break;
            case 'event_request':
                $data['event_request_id'] = $itemId;
                break;
            case 'facility_request':
                $data['facility_request_id'] = $itemId;
                break;
            case 'user':
                $data['item_user_id'] = $itemId;
                break;
        }

        return self::create($data);
    }

    // Helper to get formatted changes
    public function getChangesAttribute()
    {
        if (!$this->old_values || !$this->new_values) {
            return null;
        }

        $changes = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }
}
