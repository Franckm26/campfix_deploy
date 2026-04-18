<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearUsersExcept extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-users-except {email : The email address of the user to keep} {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all users except the specified email address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keepEmail = $this->argument('email');

        // Check if the user exists
        $keepUser = User::where('email', $keepEmail)->first();

        if (!$keepUser) {
            $this->error("❌ User with email '{$keepEmail}' not found!");
            return 1;
        }

        // Count users to be deleted
        $totalUsers = User::count();
        $usersToDelete = User::where('email', '!=', $keepEmail)->count();

        $this->info("Found {$totalUsers} total users.");
        $this->info("Will keep: {$keepUser->name} ({$keepUser->email})");
        $this->warn("Will delete: {$usersToDelete} users");

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all other users?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        DB::beginTransaction();

        try {
            // Delete all users except the specified one
            $deleted = User::where('email', '!=', $keepEmail)->delete();

            DB::commit();

            $this->newLine();
            $this->info("✅ Successfully deleted {$deleted} users");
            $this->info("✅ Kept user: {$keepUser->name} ({$keepUser->email})");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('❌ Error during deletion: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }
}
