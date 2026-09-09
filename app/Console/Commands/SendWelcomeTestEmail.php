<?php

namespace App\Console\Commands;

use App\Notifications\WelcomeDeliveryTestNotification;
use App\Services\MailFailureDiagnostic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWelcomeTestEmail extends Command
{
    protected $signature = 'users:test-welcome-email';

    protected $description = 'Send one diagnostic email to the requested test account without processing the welcome queue';

    public function handle(): int
    {
        $recipient = 'mercurio.372282@novaliches.sti.edu.ph';
        $reference = (string) Str::uuid();
        try {
            Notification::route('mail', $recipient)->notify(new WelcomeDeliveryTestNotification($reference));
        } catch (Throwable $exception) {
            $diagnostic = MailFailureDiagnostic::describe($exception);
            // A compact log avoids losing the error to a truncated stack trace.
            Log::error('Welcome test failed: '.$diagnostic, ['reference' => $reference]);
            $this->error($diagnostic.' Reference: '.$reference.'. Welcome queue unchanged.');

            return self::FAILURE;
        }
        $this->info("Test email submitted to {$recipient}. Reference: {$reference}. Check Brevo logs/inbox for delivery. Welcome queue unchanged.");

        return self::SUCCESS;
    }
}
