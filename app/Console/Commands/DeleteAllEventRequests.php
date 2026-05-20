<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\EventRequest;
use Illuminate\Console\Command;

class DeleteAllEventRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:delete-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete all event requests from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = EventRequest::count();

        if ($count === 0) {
            $this->info('No event requests found in the database.');
            return 0;
        }

        $this->warn("⚠️  WARNING: This will permanently delete ALL {$count} event request(s) from the database!");
        $this->warn('This action CANNOT be undone!');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you absolutely sure you want to proceed?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }

            // Double confirmation
            if (!$this->confirm('This is your last chance. Delete ALL event requests permanently?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Deleting all event requests...');
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $deleted = 0;
        EventRequest::chunk(100, function ($events) use (&$deleted, $bar) {
            foreach ($events as $event) {
                $event->forceDelete();
                $deleted++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        // Log the action
        ActivityLog::log(
            'events_bulk_delete',
            "Permanently deleted all {$deleted} event request(s) from database",
            null
        );

        $this->info("✓ Successfully deleted {$deleted} event request(s) from the database.");
        
        return 0;
    }
}
