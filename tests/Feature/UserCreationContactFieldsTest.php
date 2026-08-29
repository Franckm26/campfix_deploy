<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\EmailAddressNotification;
use App\Notifications\PasswordNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserCreationContactFieldsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('backup_email')->nullable()->unique();
            $table->string('password');
            $table->string('role');
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->string('student_id')->nullable()->unique();
            $table->json('permissions')->nullable();
            $table->boolean('force_password_change')->default(false);
            $table->timestamp('password_reset_at')->nullable();
            $table->foreignId('created_by')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('is_superadmin')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('item_user_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function test_mis_can_create_user_with_backup_email_and_student_id(): void
    {
        Notification::fake();

        $mis = User::create([
            'name' => 'MIS Administrator',
            'email' => 'mis-create@example.com',
            'password' => bcrypt('password'),
            'role' => 'mis',
        ]);

        $response = $this->actingAs($mis)->postJson(route('admin.users.store'), [
            'first_name' => 'Jamie',
            'last_name' => 'Student',
            'email' => 'jamie.primary@example.com',
            'backup_email' => 'jamie.backup@example.com',
            'student_id' => '02000123456',
            'phone' => '09123456789',
            'role' => 'student',
            'permissions' => ['concerns', 'settings'],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.backup_email', 'jamie.backup@example.com')
            ->assertJsonPath('user.student_id', '02000123456');

        $user = User::where('email', 'jamie.primary@example.com')->firstOrFail();
        $this->assertSame('jamie.backup@example.com', $user->backup_email);
        $this->assertSame('02000123456', $user->student_id);
        Notification::assertSentTo($user, EmailAddressNotification::class);
        Notification::assertSentTo($user, PasswordNotification::class);
    }

    public function test_primary_and_backup_email_must_be_different(): void
    {
        $mis = User::create([
            'name' => 'MIS Administrator',
            'email' => 'mis-validation@example.com',
            'password' => bcrypt('password'),
            'role' => 'mis',
        ]);

        $this->actingAs($mis)->postJson(route('admin.users.store'), [
            'first_name' => 'Jamie',
            'last_name' => 'Student',
            'email' => 'same@example.com',
            'backup_email' => 'SAME@example.com',
            'role' => 'student',
        ])->assertUnprocessable()->assertJsonValidationErrors('backup_email');
    }
}
