<?php

namespace Tests\Feature;

use App\Http\Middleware\UseOpaquePageUrls;
use App\Models\User;
use App\Support\OpaquePageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OpaquePageUrlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'app.key' => 'base64:'.base64_encode(str_repeat('o', 32)),
            'app.cipher' => 'AES-256-CBC',
        ]);
        app()->forgetInstance('encrypter');
    }

    public function test_an_authenticated_html_page_is_redirected_to_an_opaque_path(): void
    {
        config(['app.url' => 'https://different-host.example']);

        $request = Request::create('/building-admin/analytics?period=month', 'GET');
        $request->headers->set('Accept', 'text/html');
        $request->setUserResolver(fn () => new User);

        $response = app(UseOpaquePageUrls::class)->handle(
            $request,
            fn () => response('original page')
        );

        $this->assertTrue($response->isRedirect());
        $this->assertMatchesRegularExpression(
            '#^/[A-Za-z0-9_-]{80,}$#',
            $response->headers->get('Location')
        );

        $path = parse_url($response->headers->get('Location'), PHP_URL_PATH);
        $this->assertIsString($path);
        $this->assertSame(1, substr_count(trim($path, '/'), '/') + 1);
        $this->assertStringNotContainsString('/hash/', $path);
        $this->assertStringNotContainsString('building-admin', $path);
        $this->assertStringNotContainsString('analytics', $path);

        $token = ltrim($path, '/');
        $this->assertSame(
            '/building-admin/analytics?period=month',
            app(OpaquePageUrl::class)->decode($token)
        );
    }

    public function test_opaque_tokens_reject_modification(): void
    {
        $opaque = app(OpaquePageUrl::class);
        $token = $opaque->encode('/system-admin/users');
        $position = intdiv(strlen($token), 2);
        $character = $token[$position];
        $replacement = $character === 'A' ? 'B' : 'A';
        $modifiedToken = substr_replace($token, $replacement, $position, 1);

        $this->assertNull($opaque->decode($modifiedToken));
    }

    public function test_non_page_requests_are_not_redirected(): void
    {
        $middleware = app(UseOpaquePageUrls::class);

        foreach ([
            Request::create('/api/room-availability', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']),
            Request::create('/events', 'POST'),
            Request::create('/notifications/unread-count', 'GET', [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]),
        ] as $request) {
            $request->setUserResolver(fn () => new User);
            $response = $middleware->handle($request, fn () => response('unchanged'));

            $this->assertFalse($response->isRedirect());
            $this->assertSame('unchanged', $response->getContent());
        }
    }

    public function test_guests_keep_the_existing_public_path(): void
    {
        $request = Request::create('/my-events', 'GET');
        $request->headers->set('Accept', 'text/html');
        $request->setUserResolver(fn () => null);

        $response = app(UseOpaquePageUrls::class)->handle(
            $request,
            fn () => response('guest response')
        );

        $this->assertFalse($response->isRedirect());
    }

    public function test_an_opaque_url_dispatches_the_original_route_with_its_middleware(): void
    {
        Route::middleware(['web', 'auth', 'superadmin'])
            ->get('/opaque-test-page', function () {
                abort_unless(auth()->check(), 418);

                return response('protected page');
            })
            ->name('test.opaque-page');

        $user = new User;
        $user->forceFill([
            'id' => 987654,
            'role' => 'admin',
            'active_session_id' => null,
            'force_password_change' => false,
        ]);

        $this->actingAs($user);
        $redirect = $this->get('/opaque-test-page');
        $redirect->assertRedirect();

        $location = $redirect->headers->get('Location');
        $this->assertIsString($location);
        $this->assertMatchesRegularExpression('#^/[A-Za-z0-9_-]{80,}$#', $location);

        $this->get($location)
            ->assertOk()
            ->assertSeeText('protected page');
    }
}
