<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    private ?string $failureCode = null;

    public function failureMessage(): string
    {
        $code = $this->failureCode ?? 'SMS-UNKNOWN';
        $message = match ($code) {
            'SMS-PHONE' => 'Your saved phone number is not a valid Philippine mobile number.',
            'SMS-RATE-LIMIT' => 'SMS requests are temporarily limited. Please wait before trying again.',
            'SMS-NETWORK' => 'The SMS sender cannot deliver to your mobile network.',
            default => 'SMS verification is temporarily unavailable.',
        };

        return $message.' Please choose email instead. Reference: '.$code.'.';
    }

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
        $this->failureCode = null;
        $apiKey = trim((string) config('services.sms.api_key'));
        $apiUrl = trim((string) config('services.sms.api_url'));
        $senderId = trim((string) (config('services.sms.sender_id') ?: config('services.sms.sender_name', 'CampFix')));
        $recipient = $this->cleanPhoneNumber($phoneNumber);

        if ($apiKey === '' || $apiUrl === '' || $senderId === '') {
            $this->recordFailure($apiKey === '' ? 'SMS-CONFIG-KEY' : ($apiUrl === '' ? 'SMS-CONFIG-URL' : 'SMS-CONFIG-SENDER'));
            Log::warning('UniSMS is not configured', [
                'missing_api_key' => $apiKey === '',
                'missing_api_url' => $apiUrl === '',
                'missing_sender_id' => $senderId === '',
            ]);

            return false;
        }

        if ($recipient === null) {
            $this->recordFailure('SMS-PHONE');
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
                $this->recordFailure($this->providerFailureCode($response->status(), $response->json() ?? []));
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
            $this->recordFailure('SMS-CONNECTION');
            // Connection exceptions can embed request details, so do not log their message.
            Log::warning('UniSMS connection failed', ['exception_type' => get_class($exception)]);

            return false;
        }
    }

    private function recordFailure(string $code): void
    {
        $this->failureCode = $code;
        Log::warning('UniSMS delivery failure', ['code' => $code]);
    }

    private function providerFailureCode(int $httpStatus, mixed $payload): string
    {
        $payload = is_array($payload) ? $payload : [];
        if (in_array($httpStatus, [401, 403], true)) {
            return 'SMS-AUTH';
        }
        if ($httpStatus === 429) {
            return 'SMS-RATE-LIMIT';
        }

        // Classify errors into fixed codes without exposing any provider response content.
        $errors = $payload['errors'] ?? [];
        $reason = strtolower(json_encode([$errors, $payload['message']['fail_reason'] ?? null]) ?: '');
        if ($httpStatus === 402 || str_contains($reason, 'credit') || str_contains($reason, 'balance')) {
            return 'SMS-CREDITS';
        }
        // UniSMS's default sender rejects some networks with a recipient validation error.
        // Its wording includes "Sender ID", so identify this before generic sender errors.
        if (preg_match('/smart|\btnt\b|\bsun\b|network/', $reason)
            && preg_match('/can.t send|cannot send|not supported|unsupported|not allowed|restricted/', $reason)) {
            return 'SMS-NETWORK';
        }
        if ((is_array($errors) && array_key_exists('sender_id', $errors)) || str_contains($reason, 'sender')) {
            return 'SMS-SENDER';
        }
        if (is_array($errors) && array_key_exists('recipient', $errors)) {
            return 'SMS-PHONE';
        }

        return 'SMS-PROVIDER-'.$httpStatus;
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
