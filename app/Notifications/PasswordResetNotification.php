<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    protected string $password;
    protected string $loginUrl;

    public function __construct(string $password)
    {
        $this->password = $password;
        $this->loginUrl = config('app.url') . '/login';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔑 Your CampFix Password Has Been Reset')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your CampFix account password has been reset by an administrator.')
            ->line('**New Password:** ' . $this->password)
            ->line('Please keep this password secure and do not share it with anyone.')
            ->line('You will be required to change this password after your next login.')
            ->action('Login to CampFix', $this->loginUrl)
            ->line('If you did not request this password reset, please contact your system administrator immediately.')
            ->salutation('CampFix Team');
    }
    
    /**
     * Determine if notification should be queued
     */
    public function shouldQueue(): bool
    {
        return false; // Send immediately, don't queue
    }
}
