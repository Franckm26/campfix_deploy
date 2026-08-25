<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordNotification extends Notification
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
            ->subject('🔑 Your CampFix Password')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Here is your CampFix login password:')
            ->line('**Password:** ' . htmlspecialchars_decode($this->password, ENT_QUOTES))
            ->line('Please keep this password secure and do not share it with anyone.')
            ->line('You will be required to change this password after your first login.')
            ->action('Login to CampFix', $this->loginUrl)
            ->line('If you have any questions, please contact your system administrator.')
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