<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set default values for users with NULL in important columns
        DB::table('users')->whereNull('role')->update(['role' => 'student']);
        DB::table('users')->whereNull('is_deleted')->update(['is_deleted' => false]);
        DB::table('users')->whereNull('is_archived')->update(['is_archived' => false]);
        DB::table('users')->whereNull('failed_login_attempts')->update(['failed_login_attempts' => 0]);
        DB::table('users')->whereNull('otp_attempts')->update(['otp_attempts' => 0]);
        DB::table('users')->whereNull('force_password_change')->update(['force_password_change' => false]);
        DB::table('users')->whereNull('is_admin')->update(['is_admin' => false]);
        
        // Set default theme
        DB::table('users')->whereNull('theme')->update(['theme' => 'light']);
        
        // Set default notification settings
        DB::table('users')->whereNull('email_notifications')->update(['email_notifications' => true]);
        DB::table('users')->whereNull('sms_notifications')->update(['sms_notifications' => true]);
        DB::table('users')->whereNull('push_notifications')->update(['push_notifications' => true]);
        
        // Set default display preferences
        DB::table('users')->whereNull('language')->update(['language' => 'en']);
        DB::table('users')->whereNull('timezone')->update(['timezone' => 'Asia/Shanghai']);
        DB::table('users')->whereNull('date_format')->update(['date_format' => 'Y-m-d']);
        DB::table('users')->whereNull('items_per_page')->update(['items_per_page' => 10]);
        
        // Set default privacy settings
        DB::table('users')->whereNull('show_online_status')->update(['show_online_status' => true]);
        DB::table('users')->whereNull('show_activity')->update(['show_activity' => true]);
        DB::table('users')->whereNull('allow_messages')->update(['allow_messages' => true]);
        
        // Set default security settings
        DB::table('users')->whereNull('two_factor_enabled')->update(['two_factor_enabled' => false]);
        DB::table('users')->whereNull('session_timeout_minutes')->update(['session_timeout_minutes' => 60]);
        DB::table('users')->whereNull('security_notifications_enabled')->update(['security_notifications_enabled' => true]);
        DB::table('users')->whereNull('password_change_frequency_days')->update(['password_change_frequency_days' => 90]);
        DB::table('users')->whereNull('file_security_enabled')->update(['file_security_enabled' => true]);
        
        // Set default auto-delete preferences
        DB::table('users')->whereNull('auto_delete_days')->update(['auto_delete_days' => 15]);
        DB::table('users')->whereNull('reports_auto_delete_days')->update(['reports_auto_delete_days' => 15]);
        DB::table('users')->whereNull('concerns_auto_delete_days')->update(['concerns_auto_delete_days' => 15]);
        DB::table('users')->whereNull('event_requests_auto_delete_days')->update(['event_requests_auto_delete_days' => 15]);
        DB::table('users')->whereNull('facility_requests_auto_delete_days')->update(['facility_requests_auto_delete_days' => 15]);
        DB::table('users')->whereNull('users_auto_delete_days')->update(['users_auto_delete_days' => 15]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
