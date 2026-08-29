<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAccountUpdatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected array $changes,
        protected string $updatedBy
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your CampFix Account Was Updated')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your CampFix account information was updated by '.$this->updatedBy.'.')
            ->line('The following changes were made:');

        foreach ($this->changes as $label => $change) {
            $mail->line('**'.$label.':** '.$change['old'].' -> '.$change['new']);
        }

        return $mail
            ->line('Your primary email address remains unchanged.')
            ->line('If you do not recognize these changes, contact your system administrator immediately.')
            ->action('Open CampFix', config('app.url').'/login')
            ->salutation('CampFix Team');
    }
}
