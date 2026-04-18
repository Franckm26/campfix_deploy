<?php

namespace App\Console\Commands;

use App\Models\Concern;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoResolveRoomsConcerns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'concerns:auto-resolve-rooms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically resolve concerns in the "rooms" category after 15 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $resolvedCount = 0;
        $resolutionTime = Carbon::now();

        // Find Rooms concerns that have been open for at least 15 minutes
        $concerns = Concern::whereHas('categoryRelation', function ($query) {
            $query->whereRaw('LOWER(name) = ?', ['rooms']);
        })
            ->whereNotIn('status', [Concern::STATUS_RESOLVED, Concern::STATUS_CLOSED])
            ->where('created_at', '<=', $resolutionTime->copy()->subMinutes(10))
            ->get();

        foreach ($concerns as $concern) {
            $concern->update([
                'status' => Concern::STATUS_RESOLVED,
                'resolved_at' => $resolutionTime,
            ]);

            Report::where('concern_id', $concern->id)
                ->whereNotIn('status', ['Resolved', 'Closed'])
                ->update([
                    'status' => 'Resolved',
                    'resolved_at' => $resolutionTime,
                ]);

            $resolvedCount++;

            $this->info("Auto-resolved Rooms concern ID: {$concern->id}");
        }

        $this->info("Auto-resolved {$resolvedCount} Rooms concern(s).");

        return self::SUCCESS;
    }
}
