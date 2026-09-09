<?php

namespace App\Console\Commands;

use App\Notifications\WelcomeDeliveryTestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SendWelcomeTestEmail extends Command
{
    protected $signature = 'users:test-welcome-email';

    protected $description = 'Send one diagnostic email to the requested test account without processing the welcome queue';

    public function handle(): int
    {
        $recipient = 'mercurio.372282@novaliches.sti.edu.ph';
        $reference = (string) Str::uuid();
        Notification::route('mail', $recipient)->notify(new WelcomeDeliveryTestNotification($reference));
        $this->info("Test email submitted to {$recipient}. Reference: {$reference}. Check Brevo logs/inbox for delivery. Welcome queue unchanged.");

        return self::SUCCESS;
    }
}
