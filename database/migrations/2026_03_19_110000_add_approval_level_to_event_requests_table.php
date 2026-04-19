<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('event_requests', 'approval_level')) {
                $table->tinyInteger('approval_level')->default(0)->after('status');
            }
            if (!Schema::hasColumn('event_requests', 'approval_history')) {
                $table->json('approval_history')->nullable()->after('approval_level');
            }
            if (!Schema::hasColumn('event_requests', 'approved_by_level_1')) {
                $table->unsignedBigInteger('approved_by_level_1')->nullable()->after('approval_history');
            }
            if (!Schema::hasColumn('event_requests', 'approved_at_level_1')) {
                $table->timestamp('approved_at_level_1')->nullable()->after('approved_by_level_1');
            }
            if (!Schema::hasColumn('event_requests', 'approved_by_level_2')) {
                $table->unsignedBigInteger('approved_by_level_2')->nullable()->after('approved_at_level_1');
            }
            if (!Schema::hasColumn('event_requests', 'approved_at_level_2')) {
                $table->timestamp('approved_at_level_2')->nullable()->after('approved_by_level_2');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $columnsToCheck = [
                'approval_level',
                'approval_history',
                'approved_by_level_1',
                'approved_at_level_1',
                'approved_by_level_2',
                'approved_at_level_2',
            ];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('event_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
