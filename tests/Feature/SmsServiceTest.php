<?php

namespace Tests\Feature;

use App\Services\SmsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['services.sms' => [
            'api_key' => 'test-secret', 'api_url' => 'https://unismsapi.com/api/sms',
            'sender_name' => 'ApprovedSender', 'sender_id' => null,
        ]]);
        Http::preventStrayRequests();
    }

    public function test_otp_includes_required_sender_id_and_basic_auth(): void
    {
        Http::fake(['*' => Http::response(['message' => ['status' => 'pending']], 201)]);
        $this->assertTrue((new SmsService)->sendOtp('0917 123 4567', '123456'));
        Http::assertSent(fn (Request $request) => $request->url() === 'https://unismsapi.com/api/sms'
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('test-secret:'))
            && $request['recipient'] === '+639171234567'
            && $request['sender_id'] === 'ApprovedSender'
            && str_contains($request['content'], '123456'));
    }

    public function test_sender_id_override_is_used_for_notifications_too(): void
    {
        Http::fake(['*' => Http::response(['message' => ['status' => 'pending']], 201)]);
        config(['services.sms.sender_id' => 'RegisteredID']);
        $this->assertTrue((new SmsService)->sendSmsNotification('+63 917 123 4567', 'Your concern was updated.'));
        Http::assertSent(fn (Request $request) => $request['sender_id'] === 'RegisteredID' && $request['content'] === 'Your concern was updated.');
    }

    public function test_supported_phone_formats_are_normalized(): void
    {
        Http::fake(['*' => Http::response(['message' => ['status' => 'pending']], 201)]);
        foreach (['09171234567', '9171234567', '639171234567', '+639171234567', '00639171234567'] as $phone) {
            $this->assertTrue((new SmsService)->sendOtp($phone, '123456'));
        }
        Http::assertSentCount(5);
        Http::assertNotSent(fn (Request $request) => $request['recipient'] !== '+639171234567');
    }

    public function test_invalid_numbers_do_not_trigger_paid_sms_requests(): void
    {
        foreach (['', '123', 'user@example.com', '0212345678', '+12025550123'] as $phone) {
            $this->assertFalse((new SmsService)->sendOtp($phone, '123456'));
        }
        Http::assertNothingSent();
    }

    public function test_missing_credentials_or_sender_does_not_send(): void
    {
        config(['services.sms.api_key' => '']);
        $this->assertFalse((new SmsService)->sendOtp('09171234567', '123456'));
        config(['services.sms.api_key' => 'test-secret', 'services.sms.sender_name' => '', 'services.sms.sender_id' => '']);
        $this->assertFalse((new SmsService)->sendOtp('09171234567', '123456'));
        Http::assertNothingSent();
    }

    public function test_provider_validation_errors_fail_without_logging_otp_or_phone(): void
    {
        Log::spy();
        Http::fake(['*' => Http::response(['errors' => ['sender_id' => ['Unregistered sender']], 'content' => 'OTP 123456'], 422)]);
        $this->assertFalse((new SmsService)->sendOtp('09171234567', '123456'));
        Log::shouldHaveReceived('warning')->with('UniSMS rejected the message', [
            'http_status' => 422, 'message_failed' => false, 'validation_fields' => ['sender_id'],
        ])->once();
    }

    public function test_rejected_messages_do_not_count_as_success_even_with_http_200(): void
    {
        Http::fake(['*' => Http::response(['message' => ['status' => 'failed']], 200)]);
        $this->assertFalse((new SmsService)->sendOtp('09171234567', '123456'));
    }

    public function test_connection_failures_return_false_without_exposing_request_content(): void
    {
        Log::spy();
        Http::fake(fn () => throw new ConnectionException('Secret content: 123456'));
        $this->assertFalse((new SmsService)->sendOtp('09171234567', '123456'));
        Log::shouldHaveReceived('warning')->with('UniSMS connection failed', ['exception_type' => ConnectionException::class])->once();
    }
}
