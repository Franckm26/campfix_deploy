<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user if it doesn't exist
        if (!User::where('email', 'admin@novaliches.sti.edu.ph')->exists()) {
            User::create([
                'name' => 'System Administrator',
                'email' => 'admin@novaliches.sti.edu.ph',
                'password' => Hash::make('Admin@123456'),
                'role' => 'mis',
                'is_admin' => true,
                'force_password_change' => false,
                'is_deleted' => false,
                'is_archived' => false,
                'failed_login_attempts' => 0,
                'otp_attempts' => 0,
                'theme' => 'light',
                'email_notifications' => true,
                'sms_notifications' => true,
                'push_notifications' => true,
                'language' => 'en',
                'timezone' => 'Asia/Shanghai',
                'date_format' => 'Y-m-d',
                'items_per_page' => 10,
                'show_online_status' => true,
                'show_activity' => true,
                'allow_messages' => true,
                'two_factor_enabled' => false,
                'session_timeout_minutes' => 60,
                'security_notifications_enabled' => true,
                'password_change_frequency_days' => 90,
                'file_security_enabled' => true,
                'auto_delete_days' => 15,
                'reports_auto_delete_days' => 15,
                'concerns_auto_delete_days' => 15,
                'event_requests_auto_delete_days' => 15,
                'facility_requests_auto_delete_days' => 15,
                'users_auto_delete_days' => 15,
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@novaliches.sti.edu.ph');
            $this->command->info('Password: Admin@123456');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
