<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'report_id')) {
                $table->unsignedBigInteger('report_id')->nullable()->after('concern_id');
            }
            if (!Schema::hasColumn('activity_logs', 'event_request_id')) {
                $table->unsignedBigInteger('event_request_id')->nullable()->after('report_id');
            }
            if (!Schema::hasColumn('activity_logs', 'facility_request_id')) {
                $table->unsignedBigInteger('facility_request_id')->nullable()->after('event_request_id');
            }
            if (!Schema::hasColumn('activity_logs', 'item_user_id')) {
                $table->unsignedBigInteger('item_user_id')->nullable()->after('facility_request_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $columnsToCheck = ['report_id', 'event_request_id', 'facility_request_id', 'item_user_id'];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('activity_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
