<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SystemAdministratorAccessTest extends TestCase
{
    public function test_legacy_user_links_open_current_management_views(): void
    {
        $controller = new \App\Http\Controllers\SuperadminController;
        foreach (['active' => 'active', 'archived' => 'archives', 'deleted' => 'deleted', 'locked' => 'locked'] as $status => $view) {
            $response = $controller->users(Request::create('/superadmin/users', 'GET', ['status' => $status, 'search' => 'sample']));
            $this->assertSame(route('admin.users', ['search' => 'sample', 'view' => $view]), $response->getTargetUrl());
        }
        $this->assertSame(route('admin.users', ['create' => 1]), $controller->createUser()->getTargetUrl());
    }

    public function test_administrator_layout_uses_main_app_shell(): void
    {
        $layout = file_get_contents(resource_path('views/superadmin/layout.blade.php'));
        $this->assertStringContainsString("@extends('layouts.app')", $layout);
        $this->assertStringNotContainsString('<aside', $layout);
        $this->assertStringNotContainsString("localStorage.getItem('sa_theme')", $layout);
    }

    public function test_system_dashboard_uses_compact_header_and_aligned_operations_cards(): void
    {
        $dashboard = file_get_contents(resource_path('views/superadmin/dashboard.blade.php'));

        $this->assertStringContainsString('<h2 class="fw-bold mb-0">Dashboard</h2>', $dashboard);
        $this->assertStringNotContainsString('Welcome back,', $dashboard);
        $this->assertStringNotContainsString('System Dashboard', $dashboard);
        $this->assertStringContainsString('grid-template-columns: repeat(7, minmax(0, 1fr))', $dashboard);
        $this->assertStringContainsString('<div class="operations-grid">', $dashboard);
        $this->assertStringContainsString('Open Concerns', $dashboard);
        $this->assertStringContainsString('Open Reports', $dashboard);
        $this->assertStringContainsString('Pending Events', $dashboard);
        $this->assertStringNotContainsString('>Back to App</div>', $dashboard);
        $this->assertStringNotContainsString('>Categories</div>', $dashboard);
        $this->assertStringNotContainsString('<i class="fas fa-gear"></i> System</div>', $dashboard);
    }

    public function test_system_administrator_analytics_reuses_the_operational_analytics_module(): void
    {
        $route = app('router')->getRoutes()->match(Request::create('/system-admin/analytics', 'GET'));
        $analytics = file_get_contents(resource_path('views/admin/analytics.blade.php'));

        $this->assertSame(\App\Http\Controllers\AdminController::class.'@analytics', $route->getActionName());
        $this->assertStringContainsString("isSystemAdministrator() ? 'superadmin.analytics'", $analytics);
        $this->assertStringContainsString("isSystemAdministrator() ? 'superadmin.reports'", $analytics);
        $this->assertStringContainsString('class="risk-table"', $analytics);
        $this->assertStringContainsString('id="executiveSummaryModal"', $analytics);
        $this->assertStringContainsString('CampFix: A Web-Based Platform for Campus Facility Requests', $analytics);
    }

    public function test_system_administrator_sidebar_reuses_operational_modules_without_mis_duplicates(): void
    {
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString("role === 'mis' && ! auth()->user()->isSystemAdministrator()", $layout);
        $this->assertStringContainsString("route('history.index')", $layout);
        $this->assertStringContainsString("route('mis.management', ['tab' => 'categories'])", $layout);
        $this->assertStringContainsString("route('superadmin.users')", $layout);
        $this->assertStringContainsString("route('superadmin.reports')", $layout);
        $this->assertStringContainsString("route('superadmin.events')", $layout);
        $this->assertStringContainsString("route('superadmin.management')", $layout);
        $this->assertStringContainsString("route('superadmin.activity-logs')", $layout);
        $this->assertStringContainsString("route('superadmin.settings')", $layout);
        $this->assertStringNotContainsString('Module Access Control</a>', $layout);
    }

    public function test_legacy_system_administrator_urls_use_existing_operational_controllers(): void
    {
        $reports = app('router')->getRoutes()->match(Request::create('/system-admin/reports', 'GET'));
        $events = app('router')->getRoutes()->match(Request::create('/system-admin/events', 'GET'));
        $activityLogs = app('router')->getRoutes()->match(Request::create('/system-admin/activity-logs', 'GET'));
        $users = app('router')->getRoutes()->match(Request::create('/system-admin/users', 'GET'));
        $management = app('router')->getRoutes()->match(Request::create('/system-admin/management', 'GET'));
        $settings = app('router')->getRoutes()->match(Request::create('/system-admin/settings', 'GET'));

        $this->assertSame(\App\Http\Controllers\AdminController::class.'@reports', $reports->getActionName());
        $this->assertSame(\App\Http\Controllers\EventRequestController::class.'@adminIndex', $events->getActionName());
        $this->assertSame(\App\Http\Controllers\AdminController::class.'@logs', $activityLogs->getActionName());
        $this->assertSame(\App\Http\Controllers\AdminController::class.'@users', $users->getActionName());
        $this->assertSame(\App\Http\Controllers\ManagementController::class.'@index', $management->getActionName());
        $this->assertSame(\App\Http\Controllers\SettingsController::class.'@index', $settings->getActionName());
    }

    public function test_operational_pages_preserve_system_administrator_prefix_for_get_navigation(): void
    {
        foreach ([
            'admin/users.blade.php' => 'superadmin.users',
            'admin/reports.blade.php' => 'superadmin.reports',
            'admin/events.blade.php' => 'superadmin.events',
            'admin/management.blade.php' => 'superadmin.management',
            'admin/logs.blade.php' => 'superadmin.activity-logs',
        ] as $view => $routeName) {
            $contents = file_get_contents(resource_path('views/'.$view));
            $this->assertStringContainsString("isSystemAdministrator() ? '{$routeName}'", $contents, $view);
            $this->assertStringNotContainsString('@php(', $contents, $view.' must use a valid Blade PHP block');
        }
    }

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

    public function test_system_administrator_actions_are_written_to_the_forensic_audit_trail(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::create('activity_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->text('description');
            $table->unsignedBigInteger('concern_id')->nullable();
            $table->unsignedBigInteger('report_id')->nullable();
            $table->unsignedBigInteger('event_request_id')->nullable();
            $table->unsignedBigInteger('facility_request_id')->nullable();
            $table->unsignedBigInteger('item_user_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->text('metadata')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('archived_by')->nullable();
            $table->unsignedBigInteger('log_archive_folder_id')->nullable();
            $table->timestamps();
        });

        try {
            $administrator = new User;
            $administrator->forceFill([
                'id' => 77,
                'name' => 'System Administrator',
                'email' => 'administrator@example.com',
                'role' => 'superadmin',
                'is_superadmin' => true,
            ]);
            $this->actingAs($administrator);

            $log = ActivityLog::log(
                'user_updated',
                'Updated user: Test User',
                88,
                'user',
                ['role' => 'mis'],
                ['role' => 'staff']
            );

            $this->assertNotNull($log);
            $this->assertSame(77, $log->user_id);
            $this->assertSame(88, $log->item_user_id);
            $this->assertSame('forensic', $log->metadata['record_scope']);
            $this->assertSame(1, ActivityLog::forensic()->count());
        } finally {
            auth()->logout();
            Schema::dropIfExists('activity_logs');
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
