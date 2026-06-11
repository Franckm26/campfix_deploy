<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserRateLimit extends Model
{
    protected $fillable = [
        'user_id',
        'action_type',
        'count',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Check if user has exceeded daily limit
     */
    public static function hasExceededLimit(int $userId, string $actionType, int $maxAttempts = 5): bool
    {
        $today = Carbon::today();
        
        $rateLimit = self::firstOrCreate(
            [
                'user_id' => $userId,
                'action_type' => $actionType,
                'date' => $today,
            ],
            ['count' => 0]
        );

        return $rateLimit->count >= $maxAttempts;
    }

    /**
     * Increment the counter for a user action
     */
    public static function incrementCounter(int $userId, string $actionType): void
    {
        $today = Carbon::today();
        
        $rateLimit = self::firstOrCreate(
            [
                'user_id' => $userId,
                'action_type' => $actionType,
                'date' => $today,
            ],
            ['count' => 0]
        );

        $rateLimit->increment('count');
    }

    /**
     * Get remaining attempts for today
     */
    public static function getRemainingAttempts(int $userId, string $actionType, int $maxAttempts = 5): int
    {
        $today = Carbon::today();
        
        $rateLimit = self::where('user_id', $userId)
            ->where('action_type', $actionType)
            ->where('date', $today)
            ->first();

        if (!$rateLimit) {
            return $maxAttempts;
        }

        return max(0, $maxAttempts - $rateLimit->count);
    }

    /**
     * Reset counter for a user (admin function)
     */
    public static function resetCounter(int $userId, string $actionType): void
    {
        $today = Carbon::today();
        
        self::where('user_id', $userId)
            ->where('action_type', $actionType)
            ->where('date', $today)
            ->delete();
    }

    /**
     * Clean up old rate limit records (older than 7 days)
     */
    public static function cleanup(): int
    {
        $sevenDaysAgo = Carbon::now()->subDays(7);
        
        return self::where('date', '<', $sevenDaysAgo)->delete();
    }
}
