<?php

namespace Livewire\Controllers;

use Illuminate\Support\Facades\Request;
use Livewire\Connection\ConnectionHandler;

class HttpConnectionHandler extends ConnectionHandler
{
    public function __construct()
    {
        $originalUrl = request('fingerprint')['url'];

        $route = app('router')->getRoutes()->match(
            Request::create($originalUrl, 'GET')
        );

        $originalRequestMiddleware = $route->gatherMiddleware();

        $configuredMiddlewareGroup = config('livewire.middleware_group', 'web');

        $this->middleware([$configuredMiddlewareGroup, ...$originalRequestMiddleware]);
    }

    public function __invoke()
    {
        return $this->handle(
            request([
                'fingerprint',
                'serverMemo',
                'updates',
            ])
        );
    }
}
