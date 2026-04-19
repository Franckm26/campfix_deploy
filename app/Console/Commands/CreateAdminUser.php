<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin {email} {password} {--name=System Administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->option('name');

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->error("User with email {$email} already exists!");
            return 1;
        }

        // Create the admin user
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
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

        $this->info('Admin user created successfully!');
        $this->info("Email: {$email}");
        $this->info("Name: {$name}");
        $this->info("Role: MIS Administrator");

        return 0;
    }
}
