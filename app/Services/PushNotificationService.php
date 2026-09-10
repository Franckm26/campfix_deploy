<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationService
{
    /**
     * Send push notification to a single user.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();
        
        if ($subscriptions->isEmpty()) {
            Log::info("User {$user->id} has no push subscriptions");
            return false;
        }

        $sentCount = 0;
        
        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $title, $body, $data)) {
                $sentCount++;
            }
        }

        return $sentCount > 0;
    }

    /**
     * Send push notification to multiple users.
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): int
    {
        $subscriptions = PushSubscription::whereIn('user_id', $userIds)->get();
        
        if ($subscriptions->isEmpty()) {
            Log::info("No push subscriptions found for provided user IDs");
            return 0;
        }

        $sentCount = 0;
        
        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $title, $body, $data)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Send push notification to a subscription.
     */
    private function sendToSubscription(PushSubscription $subscription, string $title, string $body, array $data = []): bool
    {
        try {
            // Check if web-push library is available
            if (!class_exists('\Minishlink\WebPush\WebPush')) {
                Log::warning('WebPush library not installed. Run: composer require minishlink/web-push');
                return false;
            }

            $auth = [
                'VAPID' => [
                    'subject' => env('VAPID_SUBJECT', 'mailto:' . config('mail.from.address')),
                    'publicKey' => env('VAPID_PUBLIC_KEY'),
                    'privateKey' => env('VAPID_PRIVATE_KEY'),
                ],
            ];

            $webPush = new WebPush($auth);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'icon' => asset('favicon.ico'),
                'badge' => asset('favicon.ico'),
                'data' => array_merge($data, [
                    'url' => $data['url'] ?? url('/notifications'),
                    'timestamp' => now()->toIso8601String(),
                ]),
            ]);

            $pushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => $subscription->content_encoding,
            ]);

            $report = $webPush->sendOneNotification(
                $pushSubscription,
                $payload
            );

            if ($report->isSuccess()) {
                Log::info("Push notification sent successfully to subscription {$subscription->id}");
                return true;
            } else {
                Log::error("Push notification failed for subscription {$subscription->id}: " . $report->getReason());
                
                // Delete expired or invalid subscriptions
                if ($report->isSubscriptionExpired()) {
                    $subscription->delete();
                    Log::info("Deleted expired subscription {$subscription->id}");
                }
                
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error sending push notification: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send broadcast notification to all subscribed users.
     */
    public function broadcast(string $title, string $body, array $data = []): int
    {
        $subscriptions = PushSubscription::all();
        
        if ($subscriptions->isEmpty()) {
            Log::info("No push subscriptions available for broadcast");
            return 0;
        }

        $sentCount = 0;
        
        foreach ($subscriptions as $subscription) {
            if ($this->sendToSubscription($subscription, $title, $body, $data)) {
                $sentCount++;
            }
        }

        Log::info("Broadcast sent to {$sentCount} subscriptions");
        return $sentCount;
    }
}
