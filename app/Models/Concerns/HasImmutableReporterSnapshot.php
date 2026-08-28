<?php

namespace App\Models\Concerns;

use App\Models\User;

trait HasImmutableReporterSnapshot
{
    protected static function bootHasImmutableReporterSnapshot(): void
    {
        static::updating(function ($model): void {
            foreach ($model->reporterSnapshotColumns() as $column) {
                if ($model->isDirty($column) && $model->getOriginal($column) !== null) {
                    $model->setAttribute($column, $model->getOriginal($column));
                }
            }
        });
    }

    public static function reporterSnapshotFor(User $user, bool $reportUsesReportedByName = false): array
    {
        return [
            $reportUsesReportedByName ? 'reported_by_name' : 'reporter_name' => $user->name,
            'reporter_email' => $user->email,
            'reporter_role' => $user->role,
            'reporter_department' => $user->department,
            'reporter_phone' => $user->phone,
            'reporter_student_id' => $user->student_id,
        ];
    }

    public function getReporterDisplayNameAttribute(): string
    {
        return (string) ($this->getAttribute('reporter_name')
            ?? $this->getAttribute('reported_by_name')
            ?? $this->user?->name
            ?? 'Unknown');
    }

    private function reporterSnapshotColumns(): array
    {
        return [
            'reporter_name',
            'reported_by_name',
            'reporter_email',
            'reporter_role',
            'reporter_department',
            'reporter_phone',
            'reporter_student_id',
        ];
    }
}
