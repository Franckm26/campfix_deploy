<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EmergencyAccountRecovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emergency:unlock-reset 
                            {email : The email address of the account to recover}
                            {password : The new password to set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emergency unlock and password reset for locked accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $newPassword = $this->argument('password');

        $this->info("🔓 Emergency Account Recovery Tool");
        $this->newLine();

        try {
            // Find user by email (including locked accounts)
            $user = User::withoutGlobalScopes()->where('email', $email)->first();

            if (!$user) {
                $this->error("❌ No user found with email: {$email}");
                return Command::FAILURE;
            }

            $this->info("Found user: {$user->name} ({$user->email})");
            $this->info("Role: {$user->role}");
            
            // Show current lock status
            if ($user->locked_until && $user->locked_until > now()) {
                $this->warn("⚠️  Account is locked until: {$user->locked_until}");
            } else {
                $this->info("✅ Account is not locked");
            }

            $this->newLine();

            // Confirm action
            if (!$this->confirm('Do you want to unlock this account and reset the password?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }

            // Unlock and reset password
            $user->update([
                'password' => Hash::make($newPassword),
                'locked_until' => null,
                'failed_login_attempts' => 0,
                'login_lockout_level' => 0,
                'force_password_change' => false,
            ]);

            $this->newLine();
            $this->info("✅ Account recovery completed successfully!");
            $this->info("📧 Email: {$user->email}");
            $this->info("🔑 New Password: {$newPassword}");
            $this->info("🔓 Account Status: Unlocked");
            $this->newLine();
            $this->info("You can now login with these credentials.");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}