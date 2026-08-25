<?php

namespace App\Console\Commands;

use App\Helpers\PasswordGenerator;
use App\Models\User;
use App\Notifications\NewUserCreatedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GenerateUserPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-passwords 
                            {--all : Generate passwords for all users}
                            {--role= : Generate passwords for users with specific role}
                            {--email= : Generate password for specific user by email}
                            {--exclude-superadmin : Exclude superadmin users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new passwords for existing users and send them via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔐 Starting password generation for users...');
        $this->newLine();

        // Build query based on options
        $query = User::query();

        if ($this->option('exclude-superadmin')) {
            $query->where('is_superadmin', false)->where('role', '!=', 'superadmin');
        }

        if ($this->option('email')) {
            $query->where('email', $this->option('email'));
        } elseif ($this->option('role')) {
            $query->where('role', $this->option('role'));
        } elseif (!$this->option('all')) {
            $this->error('❌ Please specify --all, --role=ROLE, or --email=EMAIL');
            return Command::FAILURE;
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            $this->warn('⚠️  No users found matching the criteria.');
            return Command::SUCCESS;
        }

        $this->info("Found {$users->count()} user(s) to process.");
        $this->newLine();

        if (!$this->confirm('Do you want to continue and generate new passwords for these users?', true)) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $successCount = 0;
        $failedCount = 0;
        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            try {
                // Generate new password
                $newPassword = PasswordGenerator::generate(12);

                // Update user password
                $user->password = Hash::make($newPassword);
                $user->force_password_change = false;
                $user->save();

                // Send email notification
                try {
                    $user->notify(new NewUserCreatedNotification($newPassword));
                    $successCount++;
                } catch (\Exception $emailException) {
                    $this->newLine();
                    $this->error("Failed to send email to {$user->email}: " . $emailException->getMessage());
                    $failedCount++;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to process user {$user->email}: " . $e->getMessage());
                $failedCount++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Password generation completed!');
        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Total Users', $users->count()],
                ['Successful', $successCount],
                ['Failed', $failedCount],
            ]
        );

        if ($successCount > 0) {
            $this->info("📧 {$successCount} email(s) sent successfully with new credentials.");
        }

        if ($failedCount > 0) {
            $this->warn("⚠️  {$failedCount} user(s) failed to process. Check the logs for details.");
        }

        return Command::SUCCESS;
    }
}
