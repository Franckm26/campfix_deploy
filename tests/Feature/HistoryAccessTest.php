<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoryAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_mis_can_open_forensic_audit_logs(): void
    {
        $mis = User::create([
            'name' => 'MIS User',
            'email' => 'mis@example.com',
            'password' => bcrypt('password'),
            'role' => 'mis',
        ]);
        $buildingAdmin = User::create([
            'name' => 'Building Admin',
            'email' => 'building@example.com',
            'password' => bcrypt('password'),
            'role' => 'building_admin',
        ]);

        $this->actingAs($mis)->get(route('admin.logs'))->assertOk();
        $this->actingAs($buildingAdmin)->get(route('admin.logs'))->assertForbidden();
    }

    public function test_personal_history_only_contains_the_current_users_actions(): void
    {
        $user = User::create([
            'name' => 'Faculty User',
            'email' => 'faculty@example.com',
            'password' => bcrypt('password'),
            'role' => 'faculty',
        ]);
        $other = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => bcrypt('password'),
            'role' => 'student',
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'password_changed',
            'description' => 'Visible personal action',
        ]);
        ActivityLog::create([
            'user_id' => $other->id,
            'action' => 'password_changed',
            'description' => 'Hidden action from another account',
        ]);

        $this->actingAs($user)
            ->get(route('history.index'))
            ->assertOk()
            ->assertSee('Visible personal action')
            ->assertDontSee('Hidden action from another account');
    }

    public function test_mis_cannot_use_the_personal_history_page(): void
    {
        $mis = User::create([
            'name' => 'MIS User',
            'email' => 'mis-history@example.com',
            'password' => bcrypt('password'),
            'role' => 'mis',
        ]);

        $this->actingAs($mis)->get(route('history.index'))->assertForbidden();
    }
}
