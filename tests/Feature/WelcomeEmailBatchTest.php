<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WelcomeEmailDelivery;
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
        Notification::assertCount(2);

        // A second scheduler invocation on the same day cannot exceed the limit.
        $this->artisan('users:send-welcome-emails', ['--limit' => 2])->assertSuccessful();
        Notification::assertCount(2);

        Carbon::setTestNow('2026-09-06 00:10:00');
        $this->artisan('users:send-welcome-emails', ['--limit' => 2])->assertSuccessful();

        $this->assertSame(3, WelcomeEmailDelivery::where('status', 'sent')->count());
        $this->assertSame(0, WelcomeEmailDelivery::where('status', 'pending')->count());
        Notification::assertCount(3);
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
        Notification::assertCount(3);
    }
}
