<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OtpSmsDeliveryTest extends TestCase
{
    private User $user;

    public function test_timer_keeps_the_saved_expiry_after_refresh_and_a_failed_attempt(): void
    {
        $this->travelTo(now()->startOfSecond());
        $this->user->update([
            'otp' => Hash::make('123456'),
            'otp_expires_at' => now()->utc()->addMinutes(5),
        ]);
        $this->withSession(['otp_user' => $this->user->id, 'reset_timer' => true])
            ->get('/verify-otp')->assertOk()->assertViewHas('otpRemainingMilliseconds', 300000);

        $this->travel(1)->minutes();
        $this->get('/verify-otp')->assertOk()->assertViewHas('otpRemainingMilliseconds', 240000);
        $this->from('/verify-otp')->post('/verify-otp', ['otp' => '000000'])
            ->assertRedirect('/verify-otp')->assertSessionHas('error', 'Invalid OTP code');
        $this->get('/verify-otp')->assertOk()->assertViewHas('otpRemainingMilliseconds', 240000);

        $this->travel(5)->minutes();
        $this->get('/verify-otp')->assertOk()->assertViewHas('otpRemainingMilliseconds', 0);

        $this->user->update(['otp_expires_at' => now()->utc()->addMinutes(5)]);
        $this->get('/verify-otp')->assertOk()->assertViewHas('otpRemainingMilliseconds', 300000);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('role');
            $table->string('phone')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->unsignedInteger('otp_attempts')->default(0);
            $table->string('active_session_id')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });
        $this->user = User::create([
            'name' => 'SMS User', 'email' => 'sms@novaliches.sti.edu.ph',
            'password' => 'password', 'role' => 'student', 'phone' => '09171234567',
        ]);
        config(['services.sms' => [
            'api_key' => 'test-secret', 'api_url' => 'https://unismsapi.com/api/sms', 'sender_name' => 'Unisoft',
        ]]);
        Http::preventStrayRequests();
    }

    public function test_a_timed_out_send_keeps_the_otp_valid_and_still_requires_verification(): void
    {
        $code = null;
        $attempts = 0;
        Http::fake(function ($request) use (&$code, &$attempts) {
            $attempts++;
            preg_match('/code is: (\d{6})/', $request['content'], $matches);
            $code = $matches[1];
            throw new ConnectionException('cURL error 28: response timed out after the request was sent');
        });
        $this->withSession(['otp_user' => $this->user->id])->post('/otp-delivery', ['delivery_method' => 'phone'])
            ->assertRedirect('/verify-otp')->assertSessionHas('warning')
            ->assertSessionMissing('error')->assertSessionHas('otp_delivery_pending', true);
        $this->assertSame(1, $attempts);
        $this->assertGuest();
        $this->assertTrue(Hash::check($code, $this->user->fresh()->otp));
        $this->assertNotSame($code, $this->user->fresh()->otp);
        $this->get('/verify-otp')->assertOk()->assertSee('SMS confirmation is delayed.')
            ->assertSee('Try another way')->assertSee('If your code arrives');
        $this->post('/verify-otp', ['otp' => $code])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
        $this->assertNull($this->user->fresh()->otp);
    }

    public function test_a_provider_rejection_does_not_save_an_undelivered_challenge(): void
    {
        $oldHash = Hash::make('654321');
        $this->user->update(['otp' => $oldHash, 'otp_expires_at' => now()->addMinutes(5)]);
        Http::fake(['*' => Http::response(['errors' => ['sender_id' => ['Invalid sender']]], 422)]);
        $this->withSession(['otp_user' => $this->user->id])->from('/otp-choice')
            ->post('/otp-delivery', ['delivery_method' => 'phone'])->assertRedirect('/otp-choice')
            ->assertSessionHas('error', fn ($value) => str_contains($value, 'SMS-SENDER'))
            ->assertSessionMissing('otp_delivery_pending');
        $this->assertSame($oldHash, $this->user->fresh()->otp);
        $this->assertGuest();
        Http::assertSentCount(1);
    }

    public function test_dns_failure_stays_on_delivery_choice_instead_of_claiming_possible_delivery(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 6: could not resolve host'));
        $this->withSession(['otp_user' => $this->user->id])->from('/otp-choice')
            ->post('/otp-delivery', ['delivery_method' => 'phone'])->assertRedirect('/otp-choice')
            ->assertSessionHas('error', fn ($value) => str_contains($value, 'SMS-DNS'));
        $this->assertNull($this->user->fresh()->otp);
        $this->assertGuest();
    }

    public function test_a_confirmed_send_uses_the_normal_verification_flow(): void
    {
        Http::fake(['*' => Http::response(['message' => ['status' => 'pending']], 201)]);
        $this->withSession(['otp_user' => $this->user->id])->post('/otp-delivery', ['delivery_method' => 'phone'])
            ->assertRedirect('/verify-otp')->assertSessionHas('success')
            ->assertSessionHas('otp_delivery_pending', false)->assertSessionMissing('warning');
        $this->assertNotNull($this->user->fresh()->otp);
        $this->assertGuest();
    }

    public function test_a_pending_delivery_does_not_extend_expiration_or_accept_a_wrong_code(): void
    {
        Http::fake(fn () => throw new ConnectionException('cURL error 28: response timed out'));
        $this->withSession(['otp_user' => $this->user->id])->post('/otp-delivery', ['delivery_method' => 'phone'])
            ->assertRedirect('/verify-otp');
        $this->post('/verify-otp', ['otp' => '000000'])->assertSessionHas('error', 'Invalid OTP code');
        $this->assertGuest();
        $this->travel(6)->minutes();
        $this->post('/verify-otp', ['otp' => '000000'])->assertSessionHas('error', 'OTP has expired. Please request a new code.');
        $this->assertGuest();
    }
}
