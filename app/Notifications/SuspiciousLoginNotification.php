<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SuspiciousLoginNotification extends Notification
{
    use Queueable;

    protected string $email;
    protected int $attempts;
    protected string $ip;
    protected string $lockoutInfo;

    public function __construct(string $email, int $attempts, string $ip, string $lockoutInfo)
    {
        $this->email = $email;
        $this->attempts = $attempts;
        $this->ip = $ip;
        $this->lockoutInfo = $lockoutInfo;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('⚠️ Suspicious Login Activity Detected - CampFix')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A suspicious login activity has been detected on CampFix.')
            ->line('**Account:** ' . $this->email)
            ->line('**Failed Attempts:** ' . $this->attempts)
            ->line('**IP Address:** ' . $this->ip)
            ->line('**Action Taken:** ' . $this->lockoutInfo)
            ->line('**Time:** ' . now()->format('M d, Y h:i A'))
            ->action('View Audit Logs', url('/admin/logs'))
            ->line('Please review the audit logs for more details.')
            ->salutation('CampFix Security System');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Suspicious Login Activity',
            'message' => "Account '{$this->email}' has {$this->attempts} failed login attempts from IP {$this->ip}. {$this->lockoutInfo}",
            'email' => $this->email,
            'attempts' => $this->attempts,
            'ip' => $this->ip,
            'lockout_info' => $this->lockoutInfo,
            'url' => '/admin/logs',
        ];
    }
}
