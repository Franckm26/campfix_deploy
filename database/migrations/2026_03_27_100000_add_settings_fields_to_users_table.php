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
            // Notification settings
            if (!Schema::hasColumn('users', 'email_notifications')) {
                $table->boolean('email_notifications')->default(true)->after('theme');
            }
            if (!Schema::hasColumn('users', 'sms_notifications')) {
                $table->boolean('sms_notifications')->default(true)->after('email_notifications');
            }
            if (!Schema::hasColumn('users', 'push_notifications')) {
                $table->boolean('push_notifications')->default(true)->after('sms_notifications');
            }

            // Display preferences
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 10)->default('en')->after('push_notifications');
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 50)->default('Asia/Shanghai')->after('language');
            }
            if (!Schema::hasColumn('users', 'date_format')) {
                $table->string('date_format', 20)->default('Y-m-d')->after('timezone');
            }
            if (!Schema::hasColumn('users', 'items_per_page')) {
                $table->integer('items_per_page')->default(10)->after('date_format');
            }

            // Privacy settings
            if (!Schema::hasColumn('users', 'show_online_status')) {
                $table->boolean('show_online_status')->default(true)->after('items_per_page');
            }
            if (!Schema::hasColumn('users', 'show_activity')) {
                $table->boolean('show_activity')->default(true)->after('show_online_status');
            }
            if (!Schema::hasColumn('users', 'allow_messages')) {
                $table->boolean('allow_messages')->default(true)->after('show_activity');
            }

            // Security settings
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('allow_messages');
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
                'email_notifications',
                'sms_notifications',
                'push_notifications',
                'language',
                'timezone',
                'date_format',
                'items_per_page',
                'show_online_status',
                'show_activity',
                'allow_messages',
                'two_factor_enabled',
            ];
            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
