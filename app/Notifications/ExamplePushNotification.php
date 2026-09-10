<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * Example notification that sends both database and push notifications
 * 
 * Usage:
 * $user->notify(new ExamplePushNotification($title, $message, $url));
 */
class ExamplePushNotification extends Notification
{
    protected $title;
    protected $message;
    protected $url;

    public function __construct(string $title, string $message, string $url = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database']; // Add 'push' when ready
    }

    /**
     * Get the array representation for database storage.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Get the push notification data.
     * This will be used by PushNotificationService.
     */
    public function toPush($notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->message,
            'data' => [
                'url' => $this->url ?? url('/notifications'),
            ],
        ];
    }
}
