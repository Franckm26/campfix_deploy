<?php

namespace App\Helpers;

use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

/**
 * Helper class to send both database and push notifications
 */
class NotificationHelper
{
    /**
     * Send notification to a user (database + push).
     *
     * @param User $user The user to notify
     * @param string $title Notification title
     * @param string $message Notification message  
     * @param array $data Additional data (url, etc.)
     * @param string|null $notificationClass Custom notification class to use
     * @return bool Success status
     */
    public static function notify(
        User $user,
        string $title,
        string $message,
        array $data = [],
        ?string $notificationClass = null
    ): bool {
        try {
            // Send database notification (existing system)
            $notificationData = array_merge([
                'title' => $title,
                'message' => $message,
            ], $data);

            // Create database notification
            $user->notifications()->create([
                'id' => \Illuminate\Support\Str::uuid(),
                'type' => $notificationClass ?? 'App\Notifications\SystemNotification',
                'data' => $notificationData,
                'read_at' => null,
            ]);

            // Send push notification
            $pushService = new PushNotificationService();
            $pushService->sendToUser($user, $title, $message, $data);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple users.
     *
     * @param array $userIds Array of user IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return int Number of users notified
     */
    public static function notifyMultiple(
        array $userIds,
        string $title,
        string $message,
        array $data = []
    ): int {
        $count = 0;
        
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && self::notify($user, $title, $message, $data)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Broadcast notification to all users.
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return int Number of users notified
     */
    public static function broadcast(
        string $title,
        string $message,
        array $data = []
    ): int {
        $users = User::all();
        $count = 0;

        foreach ($users as $user) {
            if (self::notify($user, $title, $message, $data)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Send notification to users with specific role(s).
     *
     * @param string|array $roles Single role or array of roles
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return int Number of users notified
     */
    public static function notifyByRole(
        $roles,
        string $title,
        string $message,
        array $data = []
    ): int {
        $roles = is_array($roles) ? $roles : [$roles];
        $users = User::whereIn('role', $roles)->get();
        $count = 0;

        foreach ($users as $user) {
            if (self::notify($user, $title, $message, $data)) {
                $count++;
            }
        }

        return $count;
    }
}
