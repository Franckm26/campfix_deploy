<?php

namespace App\Http\Controllers;

use App\Support\OpaquePageUrl;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Response;

class OpaquePageController extends Controller
{
    public function __invoke(
        Request $request,
        string $token,
        OpaquePageUrl $opaquePageUrl,
        Router $router
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
        $application->instance('request', $internalRequest);

        try {
            return $router->dispatch($internalRequest);
        } finally {
            $application->instance('request', $originalRequest);
        }
    }
}
