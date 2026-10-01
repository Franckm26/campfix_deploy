<?php

namespace Tests\Unit;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WebLoginLockoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('archive_folder_id')->nullable();
            $table->unsignedInteger('failed_login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->unsignedTinyInteger('login_lockout_level')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('description');
            $table->unsignedBigInteger('item_user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function test_web_login_counts_each_failure_and_locks_on_the_third_attempt(): void
    {
        $user = User::create([
            'name' => 'Lockout Test User',
            'email' => 'lockout@example.test',
            'password' => Hash::make('CorrectPassword1!'),
        ]);

        $controller = app(AuthController::class);

        $controller->login($this->failedLoginRequest('LOCKOUT@EXAMPLE.TEST'));
        $this->assertSame(1, (int) $user->refresh()->failed_login_attempts);
        $this->assertNull($user->locked_until);

        $controller->login($this->failedLoginRequest('lockout@example.test'));
        $this->assertSame(2, (int) $user->refresh()->failed_login_attempts);
        $this->assertNull($user->locked_until);

        $controller->login($this->failedLoginRequest('lockout@example.test'));
        $user->refresh();

        $this->assertSame(3, (int) $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());

        // A correct password must not bypass the lock created by the third failure.
        $controller->login($this->validLoginRequest('lockout@example.test'));
        $this->assertSame(3, (int) $user->refresh()->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
    }

    private function failedLoginRequest(string $email): Request
    {
        return Request::create('/login', 'POST', [
            'email' => $email,
            'password' => 'incorrect-password',
        ]);
    }

    private function validLoginRequest(string $email): Request
    {
        return Request::create('/login', 'POST', [
            'email' => $email,
            'password' => 'CorrectPassword1!',
        ]);
    }
}
