<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneSignalChannel
{
    /**
     * Send the given notification via OneSignal.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        // Check if user has push notifications enabled
        if (!$notifiable->push_notifications) {
            return;
        }

        // Get OneSignal message data
        $message = $notification->toOneSignal($notifiable);

        if (!$message) {
            return;
        }

        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');

        if (!$appId || !$apiKey) {
            Log::warning('OneSignal not configured');
            return;
        }

        try {
            // Build notification payload
            $payload = [
                'app_id' => $appId,
                'headings' => ['en' => $message['title'] ?? 'CampFix Notification'],
                'contents' => ['en' => $message['body'] ?? ''],
                'url' => $message['url'] ?? url('/dashboard'),
                'data' => $message['data'] ?? [],
            ];

            // Target specific user by external_user_id (user ID)
            if ($notifiable->id) {
                $payload['include_external_user_ids'] = [(string) $notifiable->id];
            }

            // Add custom icon if provided
            if (isset($message['icon'])) {
                $payload['chrome_web_icon'] = url($message['icon']);
                $payload['firefox_icon'] = url($message['icon']);
            }

            // Add badge if provided
            if (isset($message['badge'])) {
                $payload['chrome_web_badge'] = url($message['badge']);
            }

            // Send notification via OneSignal API
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', $payload);

            if ($response->failed()) {
                Log::error('OneSignal notification failed', [
                    'user_id' => $notifiable->id,
                    'response' => $response->json(),
                ]);
            } else {
                Log::info('OneSignal notification sent', [
                    'user_id' => $notifiable->id,
                    'notification_id' => $response->json()['id'] ?? null,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('OneSignal notification exception: ' . $e->getMessage());
        }
    }
}
