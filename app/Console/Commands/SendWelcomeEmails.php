<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WelcomeEmailDelivery;
use App\Notifications\NewUserCreatedNotification;
use App\Notifications\ExistingUserWelcomeNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendWelcomeEmails extends Command
{
    protected $signature = 'users:send-welcome-emails
        {--limit= : Maximum number of Brevo delivery attempts for this UTC day}
        {--batch= : Maximum number to process in this command invocation}';

    protected $description = 'Send the next deduplicated daily batch of imported-user welcome emails';

    public function handle(): int
    {
        $configuredLimit = (int) ($this->option('limit') ?: config('welcome-emails.daily_limit', 300));
        $dailyLimit = min(300, max(1, $configuredLimit));
        $dayStart = now()->startOfDay();
        $dayEnd = now()->endOfDay();

        $attemptedToday = WelcomeEmailDelivery::query()
            ->whereBetween('last_attempted_at', [$dayStart, $dayEnd])
            ->count();
        $available = max(0, $dailyLimit - $attemptedToday);
        if ($this->option('batch') !== null) {
            $available = min($available, max(1, (int) $this->option('batch')));
        }

        if ($available === 0) {
            $this->info("The daily welcome-email allowance of {$dailyLimit} has already been used.");

            return self::SUCCESS;
        }

        WelcomeEmailDelivery::query()
            ->where('status', 'processing')
            ->where('claimed_at', '<', now()->subHours(6))
            ->update([
                'status' => 'uncertain',
                'claimed_at' => null,
                'next_attempt_at' => null,
                'last_error' => 'Interrupted delivery: check provider logs before any manual retry.',
                'updated_at' => now(),
            ]);

        $sent = 0;
        $failed = 0;
        $attempted = 0;

        while ($attempted < $available) {
            $delivery = $this->claimNext($dayStart, $dailyLimit);
            if (! $delivery) {
                break;
            }

            $attempted++;

            try {
                $user = User::withoutGlobalScopes()->find($delivery->user_id);
                if (! $user || $user->is_deleted || $user->is_archived) {
                    $delivery->update([
                        'status' => 'cancelled',
                        'encrypted_password' => null,
                        'claimed_at' => null,
                        'last_error' => 'The user account is missing, deleted, or archived.',
                    ]);
                    $failed++;

                    continue;
                }

                $notification = $delivery->encrypted_password === null
                    ? new ExistingUserWelcomeNotification
                    : new NewUserCreatedNotification(Crypt::decryptString($delivery->encrypted_password));
                $user->notify($notification);

                $delivery->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'encrypted_password' => null,
                    'claimed_at' => null,
                    'next_attempt_at' => null,
                    'last_error' => null,
                ]);
                $sent++;
            } catch (Throwable $exception) {
                report($exception);
                $delivery->update([
                    // SMTP may have accepted the message before a timeout or a
                    // database error. Never automatically resend an ambiguous send.
                    'status' => 'uncertain',
                    'claimed_at' => null,
                    'next_attempt_at' => null,
                    'last_error' => mb_substr($exception->getMessage(), 0, 2000),
                ]);
                $failed++;
            }
        }

        $pending = WelcomeEmailDelivery::query()
            ->whereIn('status', ['pending', 'failed', 'processing'])
            ->count();

        $this->info("Welcome-email batch complete: {$sent} sent, {$failed} failed or cancelled, {$pending} remaining.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function claimNext($dayStart, int $dailyLimit): ?WelcomeEmailDelivery
    {
        return DB::transaction(function () use ($dayStart, $dailyLimit): ?WelcomeEmailDelivery {
            // Vercel invocations share PostgreSQL, not an in-memory cache.
            // Serialize quota reservations across all scheduler invocations.
            if (DB::getDriverName() === 'pgsql') {
                DB::select('SELECT pg_advisory_xact_lock(20260908, 1)');
            }
            if (WelcomeEmailDelivery::whereBetween('last_attempted_at', [$dayStart, $dayStart->copy()->endOfDay()])->count() >= $dailyLimit) {
                return null;
            }

            $delivery = WelcomeEmailDelivery::query()
                ->whereNull('sent_at')
                ->whereIn('status', ['pending', 'failed'])
                ->where('attempts', '<', (int) config('welcome-emails.max_attempts', 5))
                ->where(function ($query): void {
                    $query->whereNull('next_attempt_at')->orWhere('next_attempt_at', '<=', now());
                })
                ->where(function ($query) use ($dayStart): void {
                    $query->whereNull('last_attempted_at')->orWhere('last_attempted_at', '<', $dayStart);
                })
                ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (! $delivery) {
                return null;
            }

            $delivery->update([
                'status' => 'processing',
                'attempts' => $delivery->attempts + 1,
                'last_attempted_at' => now(),
                'claimed_at' => now(),
            ]);

            return $delivery->fresh();
        }, 3);
    }
}
