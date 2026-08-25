<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailAddressNotification extends Notification
{
    use Queueable;

    protected string $loginUrl;

    public function __construct()
    {
        $this->loginUrl = config('app.url') . '/login';
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📧 Your CampFix Account Email Address')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your CampFix account has been created! Here is your login email address:')
            ->line('**Email Address:** ' . $notifiable->email)
            ->line('You will receive your password in a separate email for security purposes.')
            ->line('Use this email address to login to the system.')
            ->action('Go to Login Page', $this->loginUrl)
            ->line('If you have any questions, please contact your system administrator.')
            ->salutation('Welcome to CampFix!');
    }
    
    /**
     * Determine if notification should be queued
     */
    public function shouldQueue(): bool
    {
        return false; // Send immediately, don't queue
    }
}