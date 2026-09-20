<?php

namespace Tests\Feature;

use App\Http\Middleware\ValidateSystemAdminSignature;
use App\Models\User;
use App\Support\ProtectedRoute;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SystemAdminSignedUrlTest extends TestCase
{
    public function test_system_administrator_get_pages_require_the_signature_middleware(): void
    {
        foreach ([
            'superadmin.dashboard',
            'superadmin.users',
            'superadmin.users.create',
            'superadmin.users.edit',
            'superadmin.concerns',
            'superadmin.reports',
            'superadmin.events',
            'superadmin.activity-logs',
            'superadmin.activity-logs.folder',
            'superadmin.superadmin-logs',
            'superadmin.categories',
            'superadmin.analytics',
            'superadmin.management',
            'superadmin.settings',
        ] as $routeName) {
            $route = app('router')->getRoutes()->getByName($routeName);

            $this->assertNotNull($route, $routeName);
            $this->assertContains('system.signed', $route->gatherMiddleware(), $routeName);
        }
    }

    public function test_generated_page_urls_are_signed_and_allow_display_filters(): void
    {
        $url = ProtectedRoute::url('superadmin.users', ['view' => 'locked', 'search' => 'sample']);
        $request = Request::create($url, 'GET');

        $this->assertNotNull($request->query('signature'));
        $this->assertSame('locked', $request->query('view'));

        $response = (new ValidateSystemAdminSignature)->handle(
            $request,
            fn () => new Response('valid')
        );

        $this->assertSame('valid', $response->getContent());
    }

    public function test_dynamic_record_identifier_is_covered_by_the_signature(): void
    {
        $url = ProtectedRoute::url('superadmin.users.edit', 'user-uuid');
        $request = Request::create($url, 'GET');

        $this->assertStringContainsString('/system-admin/users/user-uuid/edit', $url);
        $this->assertTrue($request->hasValidSignature());
    }

    public function test_modified_page_path_is_rejected(): void
    {
        $signedAnalyticsUrl = ProtectedRoute::url('superadmin.analytics');
        $tamperedUrl = str_replace('/analytics', '/reports', $signedAnalyticsUrl);

        $this->expectException(InvalidSignatureException::class);

        (new ValidateSystemAdminSignature)->handle(
            Request::create($tamperedUrl, 'GET'),
            fn () => new Response('must not run')
        );
    }

    public function test_mutation_routes_remain_unsigned_for_csrf_protected_forms(): void
    {
        $url = ProtectedRoute::url('superadmin.users.update', 'user-uuid');

        $this->assertStringNotContainsString('signature=', $url);
    }

    public function test_operational_action_redirects_return_system_administrator_to_a_signed_page(): void
    {
        $administrator = new User;
        $administrator->forceFill(['role' => 'admin', 'is_superadmin' => true]);
        $this->actingAs($administrator);

        $url = ProtectedRoute::redirect('admin.users', ['view' => 'active'])->getTargetUrl();
        $request = Request::create($url, 'GET');

        $this->assertStringContainsString('/system-admin/users', $url);
        $this->assertNotNull($request->query('signature'));

        $response = (new ValidateSystemAdminSignature)->handle(
            $request,
            fn () => new Response('valid redirect')
        );

        $this->assertSame('valid redirect', $response->getContent());
    }
}
