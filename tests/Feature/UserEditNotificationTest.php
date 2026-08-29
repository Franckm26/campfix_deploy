<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\UserAccountUpdatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserEditNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_editing_a_user_keeps_primary_email_updates_backup_email_and_notifies_user(): void
    {
        Notification::fake();

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
}
