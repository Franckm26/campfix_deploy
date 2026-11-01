<?php

namespace Tests\Feature;

use App\Http\Middleware\RolePrefixMiddleware;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class ReportProgressRoutingTest extends TestCase
{
    public function test_progress_json_requests_keep_the_registered_endpoint_for_each_role(): void
    {
        foreach (['admin', 'building_admin', 'school_admin', 'mis'] as $role) {
            $this->actingAs(new User(['id' => 1, 'role' => $role]));
            $request = Request::create('/admin/reports/12/modal-data');
            $request->headers->set('Accept', 'application/json');
            $response = (new RolePrefixMiddleware)->handle($request, fn () => response()->json(['report' => ['id' => 12]]));
            $this->assertSame(200, $response->getStatusCode());
            $this->assertSame(12, $response->getData(true)['report']['id']);
        }
    }

    public function test_assignment_posts_are_not_redirected_but_html_pages_still_are(): void
    {
        $this->actingAs(new User(['id' => 1, 'role' => 'admin']));
        $middleware = new RolePrefixMiddleware;
        $response = $middleware->handle(Request::create('/admin/report/12/assign', 'POST'), fn () => response('assigned'));
        $this->assertSame(200, $response->getStatusCode());
        $response = $middleware->handle(Request::create('/admin/reports'), fn () => response('unexpected'));
        $this->assertTrue($response->isRedirect(url('/system-admin/reports')));
    }
}
