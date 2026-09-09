<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WelcomeEmailDelivery;
use App\Notifications\ExistingUserWelcomeNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WelcomeEmailBatchTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('welcome_email_deliveries');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('student');
            $table->boolean('is_deleted')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('welcome_email_deliveries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('encrypted_password')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('email_address_sent_at')->nullable();
            $table->timestamp('password_sent_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_daily_batches_never_resend_completed_deliveries(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-09-05 00:10:00');

        foreach (range(1, 3) as $number) {
            $user = User::create([
                'name' => "Student {$number}",
                'email' => "student{$number}@example.com",
                'password' => bcrypt("Password{$number}"),
                'role' => 'student',
            ]);

            WelcomeEmailDelivery::create([
                'user_id' => $user->id,
                'encrypted_password' => Crypt::encryptString("Password{$number}"),
            ]);
        }

        $this->artisan('users:send-welcome-emails', ['--limit' => 2])->assertSuccessful();

        $this->assertSame(2, WelcomeEmailDelivery::where('status', 'sent')->count());
        $this->assertSame(1, WelcomeEmailDelivery::where('status', 'pending')->count());
        $this->assertSame(0, WelcomeEmailDelivery::where('status', 'sent')->whereNotNull('encrypted_password')->count());
        Notification::assertCount(4);

        // A second scheduler invocation on the same day cannot exceed the limit.
        $this->artisan('users:send-welcome-emails', ['--limit' => 2])->assertSuccessful();
        Notification::assertCount(4);

        Carbon::setTestNow('2026-09-06 00:10:00');
        $this->artisan('users:send-welcome-emails', ['--limit' => 2])->assertSuccessful();

        $this->assertSame(3, WelcomeEmailDelivery::where('status', 'sent')->count());
        $this->assertSame(0, WelcomeEmailDelivery::where('status', 'pending')->count());
        Notification::assertCount(6);
    }

    public function test_invocation_batch_size_does_not_change_the_daily_ceiling(): void
    {
        Notification::fake();
        Carbon::setTestNow('2026-09-05 00:10:00');

        foreach (range(1, 3) as $number) {
            $user = User::create([
                'name' => "Batch Student {$number}",
                'email' => "batch{$number}@example.com",
                'password' => bcrypt("Password{$number}"),
                'role' => 'student',
            ]);
            WelcomeEmailDelivery::create([
                'user_id' => $user->id,
                'encrypted_password' => Crypt::encryptString("Password{$number}"),
            ]);
        }

        $this->artisan('users:send-welcome-emails', ['--limit' => 3, '--batch' => 1])->assertSuccessful();
        $this->assertSame(1, WelcomeEmailDelivery::where('status', 'sent')->count());

        $this->artisan('users:send-welcome-emails', ['--limit' => 3, '--batch' => 2])->assertSuccessful();
        $this->assertSame(3, WelcomeEmailDelivery::where('status', 'sent')->count());
        Notification::assertCount(6);
    }

    public function test_existing_account_receives_welcome_without_password_reset_or_repeat(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'Returning Student', 'email' => 'returning@example.com',
            'password' => bcrypt('ExistingPassword'), 'role' => 'student',
        ]);
        $originalPassword = $user->password;
        WelcomeEmailDelivery::create(['user_id' => $user->id]);

        $this->artisan('users:send-welcome-emails')->assertSuccessful();
        $this->artisan('users:send-welcome-emails')->assertSuccessful();

        Notification::assertSentTo($user, ExistingUserWelcomeNotification::class);
        Notification::assertCount(1);
        $this->assertSame($originalPassword, $user->fresh()->password);
        $this->assertSame('sent', WelcomeEmailDelivery::first()->status);
        $mail = (new ExistingUserWelcomeNotification)->toMail($user);
        $this->assertStringContainsString('Forgot Password', implode(' ', $mail->introLines));
    }

    public function test_default_sender_continues_after_300_attempts_and_keeps_batch_size(): void
    {
        Notification::fake();
        // Even a stale legacy config must not block the cron after deployment.
        config(['welcome-emails.daily_limit' => 300]);
        foreach (range(1, 351) as $number) {
            $user = User::forceCreate([
                'name' => 'Student', 'email' => "uncapped{$number}@example.com",
                'password' => 'unused-test-hash', 'role' => 'student',
            ]);
            WelcomeEmailDelivery::create([
                'user_id' => $user->id,
                'status' => $number <= 300 ? 'sent' : 'pending',
                'last_attempted_at' => $number <= 300 ? now() : null,
                'sent_at' => $number <= 300 ? now() : null,
            ]);
        }
        $this->artisan('users:send-welcome-emails', ['--batch' => 50])->assertSuccessful();
        Notification::assertCount(50);
        $this->assertSame(350, WelcomeEmailDelivery::where('status', 'sent')->count());
        $this->artisan('users:send-welcome-emails', ['--batch' => 50])->assertSuccessful();
        Notification::assertCount(51);
        $this->assertSame(351, WelcomeEmailDelivery::where('status', 'sent')->count());
        $this->artisan('users:send-welcome-emails', ['--batch' => 50])->assertSuccessful();
        Notification::assertCount(51);
    }

    public function test_interrupted_deliveries_are_not_automatically_resent(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'Student', 'email' => 'interrupted@example.com',
            'password' => bcrypt('ExistingPassword'), 'role' => 'student',
        ]);
        $delivery = WelcomeEmailDelivery::create([
            'user_id' => $user->id, 'status' => 'processing',
            'claimed_at' => now()->subDay(), 'last_attempted_at' => now()->subDay(),
        ]);

        $this->artisan('users:send-welcome-emails')->assertSuccessful();
        $this->assertSame('uncertain', $delivery->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_transport_error_is_held_for_review_instead_of_retried(): void
    {
        $user = User::create([
            'name' => 'Student', 'email' => 'timeout@example.com',
            'password' => bcrypt('ExistingPassword'), 'role' => 'student',
        ]);
        $delivery = WelcomeEmailDelivery::create(['user_id' => $user->id]);
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP timeout'));

        $this->artisan('users:send-welcome-emails')->assertFailed();
        $this->assertSame('uncertain', $delivery->fresh()->status);
        $this->assertNull($delivery->fresh()->sent_at);
        $this->artisan('users:send-welcome-emails')->assertSuccessful();
    }

    public function test_partial_send_tracks_address_and_holds_password_for_review(): void
    {
        $user = User::create([
            'name' => 'Student', 'email' => 'partial@example.com',
            'password' => 'unused-hash', 'role' => 'student',
        ]);
        $delivery = WelcomeEmailDelivery::create([
            'user_id' => $user->id, 'encrypted_password' => Crypt::encryptString('TestPassword'),
        ]);
        Notification::shouldReceive('send')->twice()->andReturnUsing(function ($recipient, $notification) {
            if ($notification instanceof \App\Notifications\PasswordNotification) {
                throw new \RuntimeException('SMTP timeout');
            }
        });
        $this->artisan('users:send-welcome-emails')->assertFailed();
        $delivery->refresh();
        $this->assertNotNull($delivery->email_address_sent_at);
        $this->assertNull($delivery->password_sent_at);
        $this->assertNull($delivery->sent_at);
        $this->assertNotNull($delivery->encrypted_password);
        $this->assertSame('uncertain', $delivery->status);
        $this->artisan('users:send-welcome-emails')->assertSuccessful();
    }

    public function test_reviewed_partial_delivery_does_not_resend_address(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'Student', 'email' => 'reviewed@example.com',
            'password' => 'unused-hash', 'role' => 'student',
        ]);
        $delivery = WelcomeEmailDelivery::create([
            'user_id' => $user->id, 'encrypted_password' => Crypt::encryptString('TestPassword'),
            'email_address_sent_at' => now()->subDay(),
        ]);
        $this->artisan('users:send-welcome-emails')->assertSuccessful();
        Notification::assertSentTo($user, \App\Notifications\PasswordNotification::class);
        Notification::assertNotSentTo($user, \App\Notifications\EmailAddressNotification::class);
        Notification::assertCount(1);
        $this->assertNotNull($delivery->fresh()->password_sent_at);
        $this->assertSame('sent', $delivery->fresh()->status);
    }

    public function test_diagnostic_email_only_targets_requested_account_and_does_not_process_queue(): void
    {
        Notification::fake();
        $user = User::create([
            'name' => 'Other Student', 'email' => 'do-not-send@example.com',
            'password' => 'unused-hash', 'role' => 'student',
        ]);
        $delivery = WelcomeEmailDelivery::create(['user_id' => $user->id]);
        $before = $delivery->fresh()->getRawOriginal();

        $this->artisan('users:test-welcome-email')->assertSuccessful();

        Notification::assertSentOnDemand(\App\Notifications\WelcomeDeliveryTestNotification::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'mercurio.372282@novaliches.sti.edu.ph');
        Notification::assertNotSentTo($user, \App\Notifications\ExistingUserWelcomeNotification::class);
        Notification::assertCount(1);
        $this->assertSame($before, $delivery->fresh()->getRawOriginal());
    }
}
