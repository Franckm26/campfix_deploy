<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BudgetApprovalSchema
{
    private const COLUMNS = [
        'budget_amount',
        'budget_status',
        'budget_requested_by',
        'budget_requested_at',
        'budget_reviewed_by',
        'budget_reviewed_at',
        'budget_rejection_reason',
    ];

    public static function ensure(): void
    {
        foreach (['reports', 'concerns'] as $tableName) {
            $missing = array_values(array_filter(
                self::COLUMNS,
                fn (string $column): bool => ! Schema::hasColumn($tableName, $column)
            ));

            if ($missing === []) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($missing) {
                foreach ($missing as $column) {
                    match ($column) {
                        'budget_amount' => $table->decimal($column, 12, 2)->nullable(),
                        'budget_status' => $table->string($column, 20)->nullable(),
                        'budget_requested_by', 'budget_reviewed_by' => $table->unsignedBigInteger($column)->nullable(),
                        'budget_requested_at', 'budget_reviewed_at' => $table->timestamp($column)->nullable(),
                        'budget_rejection_reason' => $table->text($column)->nullable(),
                    };
                }
            });
        }
    }
}
