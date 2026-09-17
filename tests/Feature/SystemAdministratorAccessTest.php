<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class SystemAdministratorAccessTest extends TestCase
{
    public function test_mis_cannot_retain_admin_privileges_from_old_permissions(): void
    {
        $user = new User;
        $user->forceFill(['role' => 'mis', 'is_superadmin' => false, 'permissions' => [
            'users', 'users_create', 'users_delete', 'module_access', 'logs', 'mis_tasks', 'analytics',
        ]]);
        foreach (['users', 'users_create', 'users_delete', 'module_access', 'logs'] as $module) {
            $this->assertFalse($user->canAccess($module));
        }
        $this->assertTrue($user->canAccess('mis_tasks'));
        $this->assertTrue($user->canAccess('analytics'));
        $this->assertFalse($user->isAdmin());
    }

    public function test_system_administrator_keeps_full_module_access(): void
    {
        $user = new User;
        $user->forceFill(['role' => 'superadmin', 'is_superadmin' => true, 'permissions' => []]);
        $this->assertTrue($user->isSystemAdministrator());
        $this->assertSame('System Administrator', $user->role_display_name);
        foreach (array_keys(User::allModules()) as $module) {
            $this->assertTrue($user->canAccess($module));
        }
    }

    public function test_all_user_management_routes_are_administrator_only(): void
    {
        $count = 0;
        foreach (app('router')->getRoutes() as $route) {
            if (preg_match('#^(admin/(users|deleted-users|logs)(/|$)|welcome-credentials(/|$))#', $route->uri())) {
                $this->assertContains('superadmin', $route->gatherMiddleware(), $route->uri());
                $count++;
            }
        }
        $this->assertGreaterThan(20, $count);
    }

    public function test_mis_task_route_stays_outside_administrator_only_group(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/admin/mis-tasks', 'GET'));
        $this->assertNotContains('superadmin', $route->gatherMiddleware());
    }
}
