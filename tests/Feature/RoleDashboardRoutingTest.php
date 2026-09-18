<?php

namespace Tests\Feature;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class RoleDashboardRoutingTest extends TestCase
{
    public function test_role_home_routes_use_the_correct_dashboard_controller(): void
    {
        $expected = [
            '/mis' => AdminController::class.'@index',
            '/building-admin' => DashboardController::class.'@index',
            '/school-admin' => DashboardController::class.'@index',
            '/academic-head' => DashboardController::class.'@index',
            '/program-head' => DashboardController::class.'@index',
            '/principal-assistant' => DashboardController::class.'@index',
        ];

        foreach ($expected as $uri => $action) {
            $route = app('router')->getRoutes()->match(Request::create($uri, 'GET'));
            $this->assertSame($action, $route->getActionName(), $uri);
        }
    }

    public function test_every_operational_administrator_role_passes_admin_middleware(): void
    {
        foreach (['mis', 'school_admin', 'building_admin', 'academic_head', 'program_head', 'principal_assistant'] as $role) {
            $user = new User;
            $user->forceFill(['id' => 100, 'role' => $role, 'is_superadmin' => false]);
            $this->actingAs($user);

            $response = (new AdminMiddleware)->handle(
                Request::create('/'.$role, 'GET'),
                fn () => response('allowed')
            );

            $this->assertSame(200, $response->getStatusCode(), $role);
        }
    }
}
