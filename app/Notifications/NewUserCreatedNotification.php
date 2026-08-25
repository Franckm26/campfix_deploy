<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserCreatedNotification extends Notification
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
            ->subject('🎉 Welcome to CampFix - Your Account Has Been Created')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your CampFix account has been successfully created! You now have access to the system.')
            ->line('**Your Login Credentials:**')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Password:** ' . htmlspecialchars_decode($this->password, ENT_QUOTES))
            ->line('**Role:** ' . ucwords(str_replace('_', ' ', $notifiable->role)))
            ->line('Please keep this information secure and do not share your password with anyone.')
            ->line('For security reasons, we recommend changing your password after your first login.')
            ->action('Login to CampFix', $this->loginUrl)
            ->line('If you have any questions or need assistance, please contact your system administrator.')
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
