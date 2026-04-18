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
            $table->unsignedBigInteger('report_id')->nullable()->after('concern_id');
            $table->unsignedBigInteger('event_request_id')->nullable()->after('report_id');
            $table->unsignedBigInteger('facility_request_id')->nullable()->after('event_request_id');
            $table->unsignedBigInteger('item_user_id')->nullable()->after('facility_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn(['report_id', 'event_request_id', 'facility_request_id', 'item_user_id']);
        });
    }
};
