<?php

namespace App\Support;

use App\Models\User;

class ConcernDecision
{
    public static function canResolve(User $user, ?string $priority, bool $hazard): bool
    {
        if (! self::canDecide($user, $priority, $hazard)) return false;
        return ! $user->isSystemAdministrator() || ($priority === 'low' && ! $hazard);
    }

    public static function canDecide(User $user, ?string $priority, bool $hazard): bool
    {
        return $user->isSystemAdministrator() || $user->role !== 'school_admin' || $priority === 'urgent' || $hazard;
    }

    public static function authorizeSchoolDecision(User $user, ?string $priority, bool $hazard): void
    {
        abort_unless(self::canDecide($user, $priority, $hazard), 403, 'School Administrators can decide only urgent and safety-hazard concerns.');
    }
}
