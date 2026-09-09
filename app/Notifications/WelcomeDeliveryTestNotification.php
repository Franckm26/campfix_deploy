<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeDeliveryTestNotification extends Notification
{
    public function __construct(public string $reference) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('CampFix delivery test - '.$this->reference)
            ->greeting('Hello!')
            ->line('This is a one-recipient email delivery test requested through the CampFix cron endpoint.')
            ->line('Test reference: '.$this->reference)
            ->line('Your password and welcome-email delivery records have not been changed.')
            ->line('No other users were included in this test.');
    }
}
