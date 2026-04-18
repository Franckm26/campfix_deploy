<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginLockedNotification extends Notification
{
    use Queueable;

    protected int $attempts;
    protected string $ip;
    protected string $lockoutDuration;
    protected string $lockoutUntil;

    public function __construct(int $attempts, string $ip, string $lockoutDuration, string $lockoutUntil)
    {
        $this->attempts = $attempts;
        $this->ip = $ip;
        $this->lockoutDuration = $lockoutDuration;
        $this->lockoutUntil = $lockoutUntil;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🔒 Your CampFix Account Has Been Locked')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your CampFix account has been temporarily locked due to multiple failed login attempts.')
            ->line('**Failed Attempts:** ' . $this->attempts)
            ->line('**IP Address:** ' . $this->ip)
            ->line('**Locked Until:** ' . $this->lockoutUntil)
            ->line('**Lock Duration:** ' . $this->lockoutDuration)
            ->line('If this was not you, your account may be under a brute-force attack. Please contact your system administrator immediately.')
            ->line('You may try logging in again after the lockout period expires.')
            ->salutation('CampFix Security System');
    }
}
