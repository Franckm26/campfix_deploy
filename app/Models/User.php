<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    protected static function boot()
    {
        parent::boot();

        // Auto-generate UUID on creation
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });

        // Global scope to exclude deleted users
        static::addGlobalScope('not_deleted', function (Builder $builder) {
            $builder->where('is_deleted', false);
        });
    }

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'role',
        'is_admin',
        'otp',
        'otp_expires_at',
        'phone',
        'department',
        'student_id',
        'level',
        'force_password_change',
        'is_archived',
        'archive_folder_id',
        'is_deleted',
        'deleted_by',
        'profile_picture',
        'active_session_id',
        // OWASP A2: Account Lockout fields
        'failed_login_attempts',
        'locked_until',
        // OTP
        'otp_attempts',
        // Theme
        'theme',
        // Notification settings
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        // Display preferences
        'language',
        'timezone',
        'date_format',
        'items_per_page',
        // Privacy settings
        'show_online_status',
        'show_activity',
        'allow_messages',
        // Security settings
        'two_factor_enabled',
        // Auto-delete preferences
        'auto_delete_days',
        'reports_auto_delete_days',
        'concerns_auto_delete_days',
        'event_requests_auto_delete_days',
        'facility_requests_auto_delete_days',
        'users_auto_delete_days',
        // Security misconfiguration settings
        'session_timeout_minutes',
        'security_notifications_enabled',
        'password_change_frequency_days',
        'file_security_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    public function concerns()
    {
        return $this->hasMany(Concern::class);
    }

    public function assignedConcerns()
    {
        return $this->hasMany(Concern::class, 'assigned_to');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function eventRequests()
    {
        return $this->hasMany(EventRequest::class);
    }

    public function isAdmin()
    {
        return $this->role === 'mis';
    }

    public function isSchoolAdmin()
    {
        return $this->role === 'school_admin';
    }

    public function isAcademicHead()
    {
        return $this->role === 'academic_head';
    }

    public function isProgramHead()
    {
        return $this->role === 'program_head';
    }

    public function isBuildingAdmin()
    {
        return $this->role === self::ROLE_BUILDING_ADMIN;
    }

    public function isPrincipalAssistant()
    {
        return $this->role === self::ROLE_PRINCIPAL_ASSISTANT;
    }

    public function isPrincipal()
    {
        return $this->role === 'school_admin';
    }

    /**
     * Format a date using the user's preferred date format
     */
    public function formatDate($date, $includeTime = false)
    {
        if (! $date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }

        // Set timezone
        $date->setTimezone($this->timezone ?? 'Asia/Shanghai');

        $format = $this->date_format ?? 'Y-m-d';
        if ($includeTime) {
            $format .= ' H:i:s';
        }

        return $date->format($format);
    }

    public function isMaintenance()
    {
        return $this->role === 'maintenance';
    }

    public function isFaculty()
    {
        return $this->role === 'faculty';
    }

    public function canManageConcerns()
    {
        return in_array($this->role, ['mis', 'maintenance', 'school_admin', 'academic_head', 'program_head']);
    }

    public function canApproveRequests()
    {
        return in_array($this->role, ['mis', 'school_admin', 'academic_head', 'program_head', 'building_admin', 'principal_assistant']);
    }

    // Role constants
    const ROLE_STUDENT = 'student';

    const ROLE_FACULTY = 'faculty';

    const ROLE_MAINTENANCE = 'maintenance';

    const ROLE_ADMIN = 'mis';

    const ROLE_SCHOOL_ADMIN = 'school_admin';

    const ROLE_BUILDING_ADMIN = 'building_admin';

    const ROLE_ACADEMIC_HEAD = 'academic_head';

    const ROLE_PROGRAM_HEAD = 'program_head';

    const ROLE_PRINCIPAL_ASSISTANT = 'principal_assistant';

    // Priority constants
    const PRIORITY_LOW = 'low';

    const PRIORITY_MEDIUM = 'medium';

    const PRIORITY_HIGH = 'high';

    const PRIORITY_URGENT = 'urgent';

    public static function getRoles()
    {
        return [
            self::ROLE_STUDENT,
            self::ROLE_FACULTY,
            self::ROLE_MAINTENANCE,
            self::ROLE_ADMIN,
            self::ROLE_SCHOOL_ADMIN,
            self::ROLE_BUILDING_ADMIN,
            self::ROLE_ACADEMIC_HEAD,
            self::ROLE_PROGRAM_HEAD,
        ];
    }

    public static function getMaintenanceStaff()
    {
        return self::where('role', self::ROLE_MAINTENANCE)->get();
    }

    public function archiveFolder()
    {
        return $this->belongsTo(UserArchiveFolder::class, 'archive_folder_id');
    }

    /**
     * Get the user who deleted this user
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public static function getSchoolAdmins()
    {
        return self::where('role', self::ROLE_SCHOOL_ADMIN)->get();
    }

    public static function getBuildingAdmins()
    {
        return self::where('role', self::ROLE_BUILDING_ADMIN)->get();
    }

    public static function getProgramHeads()
    {
        return self::where('role', self::ROLE_PROGRAM_HEAD)->get();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public static function getAcademicHeads()
    {
        return self::where('role', self::ROLE_ACADEMIC_HEAD)->get();
    }
}
