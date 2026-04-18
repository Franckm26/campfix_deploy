<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ArchiveFolder;
use App\Models\Category;
use App\Models\Concern;
use App\Models\Event;
use App\Models\EventDiscussion;
use App\Models\EventRequest;
use App\Models\FacilityRequest;
use App\Models\OtpVerification;
use App\Models\Report;
use App\Models\ReportStatusLog;
use App\Models\UserArchiveFolder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearAllDataExceptUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-except-users {--force : Force the operation without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all database data except users table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL data except users. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting database cleanup...');

        DB::beginTransaction();

        try {
            // Disable foreign key checks temporarily
            Schema::disableForeignKeyConstraints();

            // Clear all tables except users
            $this->clearTable('event_discussions', EventDiscussion::class);
            $this->clearTable('event_requests', EventRequest::class);
            $this->clearTable('events', Event::class);
            $this->clearTable('facility_requests', FacilityRequest::class);
            $this->clearTable('report_status_logs', ReportStatusLog::class);
            $this->clearTable('reports', Report::class);
            $this->clearTable('concerns', Concern::class);
            $this->clearTable('activity_logs', ActivityLog::class);
            $this->clearTable('categories', Category::class);
            $this->clearTable('otp_verifications', OtpVerification::class);
            $this->clearTable('user_archive_folders', UserArchiveFolder::class);
            $this->clearTable('archive_folders', ArchiveFolder::class);

            // Clear notifications table if it exists
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->delete();
                $this->info('✓ Cleared notifications table');
            }

            // Clear failed_jobs table if it exists
            if (Schema::hasTable('failed_jobs')) {
                DB::table('failed_jobs')->delete();
                $this->info('✓ Cleared failed_jobs table');
            }

            // Clear jobs table if it exists
            if (Schema::hasTable('jobs')) {
                DB::table('jobs')->delete();
                $this->info('✓ Cleared jobs table');
            }

            // Clear cache table if it exists
            if (Schema::hasTable('cache')) {
                DB::table('cache')->delete();
                $this->info('✓ Cleared cache table');
            }

            // Clear sessions table if it exists
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->delete();
                $this->info('✓ Cleared sessions table');
            }

            // Reset user-related fields but keep user accounts
            DB::table('users')->update([
                'otp' => null,
                'otp_expires_at' => null,
                'otp_attempts' => 0,
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'login_lockout_level' => 0,
            ]);
            $this->info('✓ Reset user security fields (OTP, lockouts)');

            // Re-enable foreign key checks
            Schema::enableForeignKeyConstraints();

            DB::commit();

            $this->newLine();
            $this->info('✅ Database cleanup completed successfully!');
            $this->info('All data has been cleared except user accounts.');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            Schema::enableForeignKeyConstraints();

            $this->error('❌ Error during cleanup: ' . $e->getMessage());
            $this->error($e->getTraceAsString());

            return 1;
        }
    }

    /**
     * Clear a specific table
     */
    private function clearTable(string $tableName, string $modelClass = null)
    {
        if (Schema::hasTable($tableName)) {
            if ($modelClass && class_exists($modelClass)) {
                $modelClass::query()->forceDelete();
            } else {
                DB::table($tableName)->delete();
            }
            $this->info("✓ Cleared {$tableName} table");
        }
    }
}
