<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function sendOtp(string $phoneNumber, string $otp): bool
    {
        return $this->sendMessage($phoneNumber, "Your CampFix verification code is: {$otp}. This code expires in 5 minutes.");
    }

    public function sendSmsNotification(string $phoneNumber, string $message): bool
    {
        return $this->sendMessage($phoneNumber, $message);
    }

    private function sendMessage(string $phoneNumber, string $message): bool
    {
        $apiKey = trim((string) config('services.sms.api_key'));
        $apiUrl = trim((string) config('services.sms.api_url'));
        $senderId = trim((string) (config('services.sms.sender_id') ?: config('services.sms.sender_name', 'CampFix')));
        $recipient = $this->cleanPhoneNumber($phoneNumber);

        if ($apiKey === '' || $apiUrl === '' || $senderId === '') {
            Log::warning('UniSMS is not configured', [
                'missing_api_key' => $apiKey === '',
                'missing_api_url' => $apiUrl === '',
                'missing_sender_id' => $senderId === '',
            ]);

            return false;
        }

        if ($recipient === null) {
            Log::warning('UniSMS recipient is not a valid Philippine mobile number');

            return false;
        }

        try {
            // UniSMS requires an account-approved sender ID on every request.
            $response = Http::connectTimeout(5)->timeout(10)
                ->withBasicAuth($apiKey, '')
                ->acceptJson()->asJson()
                ->post($apiUrl, [
                    'recipient' => $recipient,
                    'content' => $message,
                    'sender_id' => $senderId,
                ]);

            $status = $response->json('message.status');
            if (! $response->successful() || $status === 'failed' || $response->json('success') === false) {
                // Provider responses can echo the OTP, recipient and message. Log only safe diagnostics.
                Log::warning('UniSMS rejected the message', [
                    'http_status' => $response->status(),
                    'message_failed' => $status === 'failed',
                    'validation_fields' => array_keys((array) $response->json('errors', [])),
                ]);

                return false;
            }

            Log::info('UniSMS accepted the message', ['http_status' => $response->status()]);

            return true;
        } catch (\Exception $exception) {
            // Connection exceptions can embed request details, so do not log their message.
            Log::warning('UniSMS connection failed', ['exception_type' => get_class($exception)]);

            return false;
        }
    }

    private function cleanPhoneNumber(string $phoneNumber): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $phoneNumber);
        if (str_starts_with($digits, '0063')) {
            $digits = substr($digits, 2);
        } elseif (preg_match('/^09\d{9}$/', $digits)) {
            $digits = '63'.substr($digits, 1);
        } elseif (preg_match('/^9\d{9}$/', $digits)) {
            $digits = '63'.$digits;
        }

        return preg_match('/^639\d{9}$/', $digits) ? '+'.$digits : null;
    }
}
