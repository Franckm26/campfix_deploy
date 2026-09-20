<?php

namespace App\Http\Controllers;

use App\Support\OpaquePageUrl;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

class OpaquePageController extends Controller
{
    public function __invoke(
        Request $request,
        string $token,
        OpaquePageUrl $opaquePageUrl,
        Router $router,
        HttpKernel $httpKernel
    ): Response {
        $uri = $opaquePageUrl->decode($token);

        abort_if($uri === null, 404);

        $internalRequest = Request::create(
            $uri,
            'GET',
            [],
            $request->cookies->all(),
            [],
            $request->server->all()
        );

        $internalRequest->headers->replace($request->headers->all());
        $internalRequest->setUserResolver(fn () => $request->user());
        $internalRequest->attributes->set('opaque.internal', true);

        if ($request->hasSession()) {
            $internalRequest->setLaravelSession($request->session());
        }

        $application = app();
        $originalRequest = $application->make('request');
        $destinationRoute = $router->getRoutes()->match($internalRequest);
        $hadExcludedMiddleware = array_key_exists('excluded_middleware', $destinationRoute->action);
        $excludedMiddleware = $destinationRoute->action['excluded_middleware'] ?? [];

        // The outer opaque request has already completed the web middleware
        // stack. Replaying it would decrypt cookies and start the session a
        // second time. Exclude that stack except destination model binding;
        // route-specific auth, role and throttling middleware still run.
        $webMiddleware = $httpKernel->getMiddlewareGroups()['web'] ?? [];
        $destinationRoute->withoutMiddleware(array_values(array_filter(
            $webMiddleware,
            fn ($middleware) => explode(':', (string) $middleware, 2)[0] !== SubstituteBindings::class
        )));
        $application->instance('request', $internalRequest);

        try {
            return $router->dispatch($internalRequest);
        } finally {
            if ($hadExcludedMiddleware) {
                $destinationRoute->action['excluded_middleware'] = $excludedMiddleware;
            } else {
                unset($destinationRoute->action['excluded_middleware']);
            }

            $application->instance('request', $originalRequest);
        }
    }
}
