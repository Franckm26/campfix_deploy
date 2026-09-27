<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['reports', 'concerns'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'budget_amount')) {
                    $table->decimal('budget_amount', 12, 2)->nullable();
                }
                if (! Schema::hasColumn($tableName, 'budget_status')) {
                    $table->string('budget_status', 20)->nullable()->index();
                }
                if (! Schema::hasColumn($tableName, 'budget_requested_by')) {
                    $table->foreignId('budget_requested_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'budget_requested_at')) {
                    $table->timestamp('budget_requested_at')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'budget_reviewed_by')) {
                    $table->foreignId('budget_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'budget_reviewed_at')) {
                    $table->timestamp('budget_reviewed_at')->nullable();
                }
                if (! Schema::hasColumn($tableName, 'budget_rejection_reason')) {
                    $table->text('budget_rejection_reason')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['reports', 'concerns'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['budget_requested_by', 'budget_reviewed_by'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }

                $columns = [
                    'budget_amount',
                    'budget_status',
                    'budget_requested_at',
                    'budget_reviewed_at',
                    'budget_rejection_reason',
                ];

                $table->dropColumn(array_values(array_filter(
                    $columns,
                    fn ($column) => Schema::hasColumn($tableName, $column)
                )));
            });
        }
    }
};
