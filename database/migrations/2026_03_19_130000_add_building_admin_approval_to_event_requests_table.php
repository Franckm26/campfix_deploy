<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('event_requests', 'approved_by_level_3')) {
                $table->unsignedBigInteger('approved_by_level_3')->nullable()->after('approved_by_level_2');
            }
            if (!Schema::hasColumn('event_requests', 'approved_at_level_3')) {
                $table->timestamp('approved_at_level_3')->nullable()->after('approved_at_level_2');
            }
        });
    }

    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $columnsToCheck = [
                'approved_by_level_3',
                'approved_at_level_3',
            ];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('event_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
