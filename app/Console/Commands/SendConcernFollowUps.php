<?php

namespace App\Console\Commands;

use App\Models\Concern;
use App\Notifications\ConcernFollowUpNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendConcernFollowUps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concerns:send-follow-ups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send follow-up notifications for concerns that have been pending without assignment for 1 day';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $followUpCount = 0;
        $oneDayAgo = Carbon::now()->subDays(1);

        // Find concerns that:
        // 1. Are still in Pending status
        // 2. Have not been assigned (assigned_to is null)
        // 3. Were created at least 1 day ago
        // 4. Have not received a follow-up notification yet
        $concerns = Concern::where('status', Concern::STATUS_PENDING)
            ->whereNull('assigned_to')
            ->where('created_at', '<=', $oneDayAgo)
            ->where('follow_up_sent', false)
            ->with('user', 'categoryRelation')
            ->get();

        foreach ($concerns as $concern) {
            // Skip if the concern has no user (anonymous or deleted user)
            if (!$concern->user) {
                $this->warn("Skipping concern ID {$concern->id}: No user associated");
                continue;
            }

            // Calculate days waiting
            $daysWaiting = $concern->created_at->diffInDays(Carbon::now());

            try {
                // Send notification to the user who submitted the concern
                $concern->user->notify(new ConcernFollowUpNotification($concern, $daysWaiting));

                // Mark follow-up as sent
                $concern->update([
                    'follow_up_sent' => true,
                    'follow_up_sent_at' => Carbon::now(),
                ]);

                $followUpCount++;
                $this->info("Sent follow-up for concern ID: {$concern->id} (waiting {$daysWaiting} days)");
            } catch (\Exception $e) {
                $this->error("Failed to send follow-up for concern ID {$concern->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$followUpCount} follow-up notification(s).");

        return self::SUCCESS;
    }
}
