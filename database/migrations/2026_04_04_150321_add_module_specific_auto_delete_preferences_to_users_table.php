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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('reports_auto_delete_days')->default(15)->after('auto_delete_days');
            $table->integer('concerns_auto_delete_days')->default(15)->after('reports_auto_delete_days');
            $table->integer('event_requests_auto_delete_days')->default(15)->after('concerns_auto_delete_days');
            $table->integer('facility_requests_auto_delete_days')->default(15)->after('event_requests_auto_delete_days');
            $table->integer('users_auto_delete_days')->default(15)->after('facility_requests_auto_delete_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'reports_auto_delete_days',
                'concerns_auto_delete_days',
                'event_requests_auto_delete_days',
                'facility_requests_auto_delete_days',
                'users_auto_delete_days',
            ]);
        });
    }
};
