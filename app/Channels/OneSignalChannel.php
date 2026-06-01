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
        Log::info('OneSignalChannel: Starting send', [
            'user_id' => $notifiable->id,
            'push_enabled' => $notifiable->push_notifications,
        ]);

        // Check if user has push notifications enabled
        if (!$notifiable->push_notifications) {
            Log::info('OneSignalChannel: Push notifications disabled for user', ['user_id' => $notifiable->id]);
            return;
        }

        // Get OneSignal message data
        $message = $notification->toOneSignal($notifiable);

        if (!$message) {
            Log::warning('OneSignalChannel: No message data returned', ['user_id' => $notifiable->id]);
            return;
        }

        Log::info('OneSignalChannel: Message data', ['message' => $message]);

        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');

        Log::info('OneSignalChannel: Config check', [
            'app_id_set' => !empty($appId),
            'api_key_set' => !empty($apiKey),
            'app_id' => $appId ? substr($appId, 0, 10) . '...' : 'NOT SET',
        ]);

        if (!$appId || !$apiKey) {
            Log::error('OneSignal not configured', [
                'app_id' => $appId,
                'api_key_set' => !empty($apiKey),
            ]);
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

            Log::info('OneSignalChannel: Sending to OneSignal API', [
                'payload' => $payload,
                'user_id' => $notifiable->id,
            ]);

            // Send notification via OneSignal API
            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://onesignal.com/api/v1/notifications', $payload);

            Log::info('OneSignalChannel: API Response', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            if ($response->failed()) {
                Log::error('OneSignal notification failed', [
                    'user_id' => $notifiable->id,
                    'status' => $response->status(),
                    'response' => $response->json(),
                ]);
            } else {
                Log::info('OneSignal notification sent successfully', [
                    'user_id' => $notifiable->id,
                    'notification_id' => $response->json()['id'] ?? null,
                    'recipients' => $response->json()['recipients'] ?? 0,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('OneSignal notification exception', [
                'user_id' => $notifiable->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
