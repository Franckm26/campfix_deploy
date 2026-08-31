<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\UserAccountUpdatedNotification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserEditNotificationTest extends TestCase
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
            $table->string('backup_email')->nullable();
            $table->string('password');
            $table->string('role');
            $table->json('permissions')->nullable();
            $table->string('phone')->nullable();
            $table->string('department')->nullable();
            $table->string('student_id')->nullable();
            $table->boolean('is_superadmin')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->boolean('force_password_change')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('password_reset_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('item_user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function test_editing_a_user_keeps_primary_email_updates_backup_email_and_notifies_user(): void
    {
        Notification::fake();
        Mail::fake();

        $mis = User::create([
            'name' => 'MIS Administrator',
            'email' => 'mis@example.com',
            'password' => bcrypt('password'),
            'role' => 'mis',
        ]);
        $user = User::create([
            'name' => 'Original Name',
            'email' => 'primary@example.com',
            'backup_email' => 'old-backup@example.com',
            'password' => bcrypt('password'),
            'role' => 'faculty',
        ]);

        $this->actingAs($mis)
            ->put(route('admin.users.update', $user->uuid), [
                'name' => 'Updated Name',
                'email' => 'forged-change@example.com',
                'backup_email' => 'new-backup@example.com',
                'role' => 'faculty',
                'phone' => null,
                'department' => null,
                'student_id' => null,
            ])
            ->assertRedirect(route('admin.users'));

        $user->refresh();

        $this->assertSame('primary@example.com', $user->email);
        $this->assertSame('new-backup@example.com', $user->backup_email);
        Notification::assertSentTo($user, UserAccountUpdatedNotification::class);
    }

    public function test_edit_user_json_includes_backup_email(): void
    {
        $mis = User::create([
            'name' => 'MIS Administrator',
            'email' => 'mis-json@example.com',
            'password' => bcrypt('password'),
            'role' => 'mis',
        ]);
        $user = User::create([
            'name' => 'User With Backup',
            'email' => 'primary-json@example.com',
            'backup_email' => 'backup-json@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        $this->actingAs($mis)
            ->getJson(route('admin.users.edit', $user->uuid))
            ->assertOk()
            ->assertJsonPath('email', 'primary-json@example.com')
            ->assertJsonPath('backup_email', 'backup-json@example.com');
    }

    public function test_json_edit_persists_role_and_custom_module_access(): void
    {
        Notification::fake();
        Mail::fake();
        $mis = User::create(['name' => 'MIS Administrator', 'email' => 'mis-update@example.com', 'password' => bcrypt('password'), 'role' => 'mis']);
        $user = User::create(['name' => 'Faculty User', 'email' => 'faculty-update@example.com', 'password' => bcrypt('password'), 'role' => 'faculty', 'permissions' => ['concerns', 'settings']]);

        $this->actingAs($mis)->putJson(route('admin.users.update', $user->uuid), [
            'name' => $user->name, 'backup_email' => null, 'role' => 'building_admin', 'phone' => null,
            'department' => null, 'student_id' => null, 'permissions' => ['reports', 'events', 'analytics'],
        ])->assertOk()->assertJsonPath('success', true)->assertJsonPath('user.role', 'building_admin')
            ->assertJsonPath('user.permissions', ['reports', 'events', 'analytics', 'settings', 'categories']);

        $user->refresh();
        $this->assertSame('building_admin', $user->role);
        $this->assertSame(['reports', 'events', 'analytics', 'settings', 'categories'], $user->permissions);
    }

    public function test_invalid_json_edit_does_not_claim_success_or_change_role(): void
    {
        $mis = User::create(['name' => 'MIS Administrator', 'email' => 'mis-validation@example.com', 'password' => bcrypt('password'), 'role' => 'mis']);
        $user = User::create(['name' => 'Faculty User', 'email' => 'faculty-validation@example.com', 'password' => bcrypt('password'), 'role' => 'faculty']);

        $this->actingAs($mis)->putJson(route('admin.users.update', $user->uuid), [
            'name' => $user->name, 'role' => 'building_admin', 'phone' => 'invalid-phone', 'permissions' => ['reports'],
        ])->assertUnprocessable()->assertJsonValidationErrors('phone');

        $this->assertSame('faculty', $user->fresh()->role);
    }
}
