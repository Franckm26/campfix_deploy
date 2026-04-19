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
            // Security misconfiguration prevention settings
            if (!Schema::hasColumn('users', 'session_timeout_minutes')) {
                $table->integer('session_timeout_minutes')->default(60)->after('users_auto_delete_days');
            }
            if (!Schema::hasColumn('users', 'security_notifications_enabled')) {
                $table->boolean('security_notifications_enabled')->default(true)->after('session_timeout_minutes');
            }
            if (!Schema::hasColumn('users', 'password_change_frequency_days')) {
                $table->integer('password_change_frequency_days')->default(90)->after('security_notifications_enabled');
            }
            if (!Schema::hasColumn('users', 'file_security_enabled')) {
                $table->boolean('file_security_enabled')->default(true)->after('password_change_frequency_days');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToCheck = [
                'session_timeout_minutes',
                'security_notifications_enabled',
                'password_change_frequency_days',
                'file_security_enabled'
            ];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
