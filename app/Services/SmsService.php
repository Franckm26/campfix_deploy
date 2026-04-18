<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;

    protected $apiUrl;

    protected $senderName;

    public function __construct()
    {
        $this->apiKey = config('services.sms.api_key');
        $this->apiUrl = config('services.sms.api_url');
        $this->senderName = config('services.sms.sender_name', 'CampFix');
    }

    /**
     * Send OTP via SMS using UnisSMS API
     *
     * @param  string  $phoneNumber  Recipient's phone number
     * @param  string  $otp  The OTP code to send
     * @return bool True if SMS was sent successfully, false otherwise
     */
    public function sendOtp(string $phoneNumber, string $otp): bool
    {
        try {
            $cleanPhone = $this->cleanPhoneNumber($phoneNumber);
            $message = "Your CampFix verification code is: {$otp}. This code expires in 5 minutes.";

            // UnisSMS uses HTTP Basic Auth: API key as username, empty password
            $response = Http::timeout(10)
                ->withBasicAuth($this->apiKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'recipient' => $cleanPhone,
                    'content'   => $message,
                ]);

            if ($response->failed()) {
                Log::error('UnisSMS OTP request failed:', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'phone'  => $cleanPhone,
                ]);
                return false;
            }

            $responseData = $response->json();

            Log::info('UnisSMS OTP Response:', [
                'status'   => $response->status(),
                'phone'    => $cleanPhone,
                'response' => $responseData,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('SMS OTP sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a custom SMS notification via UnisSMS API
     *
     * @param  string  $phoneNumber  Recipient's phone number
     * @param  string  $message  The message to send
     * @return bool True if SMS was sent successfully, false otherwise
     */
    public function sendSmsNotification(string $phoneNumber, string $message): bool
    {
        try {
            $cleanPhone = $this->cleanPhoneNumber($phoneNumber);

            // UnisSMS uses HTTP Basic Auth: API key as username, empty password
            $response = Http::timeout(10)
                ->withBasicAuth($this->apiKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'recipient' => $cleanPhone,
                    'content'   => $message,
                ]);

            Log::info('UnisSMS Notification Response:', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'phone'  => $cleanPhone,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('SMS notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Clean and format phone number to international format (+63XXXXXXXXXX)
     */
    private function cleanPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // 09XXXXXXXXX → 639XXXXXXXXX
        if (strlen($cleaned) == 11 && substr($cleaned, 0, 1) == '0') {
            $cleaned = '63' . substr($cleaned, 1);
        }

        // 9XXXXXXXXX → 639XXXXXXXXX
        if (strlen($cleaned) == 10 && substr($cleaned, 0, 1) == '9') {
            $cleaned = '63' . $cleaned;
        }

        return '+' . $cleaned;
    }
}
