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
            $table->boolean('email_notifications')->default(true)->after('theme');
            $table->boolean('sms_notifications')->default(true)->after('email_notifications');
            $table->boolean('push_notifications')->default(true)->after('sms_notifications');

            // Display preferences
            $table->string('language', 10)->default('en')->after('push_notifications');
            $table->string('timezone', 50)->default('Asia/Shanghai')->after('language');
            $table->string('date_format', 20)->default('Y-m-d')->after('timezone');
            $table->integer('items_per_page')->default(10)->after('date_format');

            // Privacy settings
            $table->boolean('show_online_status')->default(true)->after('items_per_page');
            $table->boolean('show_activity')->default(true)->after('show_online_status');
            $table->boolean('allow_messages')->default(true)->after('show_activity');

            // Security settings
            $table->boolean('two_factor_enabled')->default(false)->after('allow_messages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
