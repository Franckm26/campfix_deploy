<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TestPushNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['onesignal'];
    }

    public function toOneSignal(object $notifiable): array
    {
        return [
            'title' => 'Test Notification',
            'body' => 'This is a test push notification from CampFix!',
            'icon' => '/favicon.ico',
            'badge' => '/favicon.ico',
            'url' => url('/dashboard'),
            'data' => [
                'type' => 'test',
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }
}
