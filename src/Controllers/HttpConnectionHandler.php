<?php

namespace Livewire\Controllers;

use Livewire\Connection\ConnectionHandler;

class HttpConnectionHandler extends ConnectionHandler
{
    public function __invoke()
    {
        return $this->handle(
            request([
                'actionQueue',
                'name',
                'children',
                'data',
                'meta',
                'memo',
                'id',
                'checksum',
                'locale',
                'fromPrefetch',
                'errorBag',
            ])
        );
    }
}
