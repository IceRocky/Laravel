<?php

namespace Livewire\Features\SupportAutoInjectedAssets;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Livewire\ComponentHook;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;

use function Livewire\on;

class SupportAutoInjectedAssets extends ComponentHook
{
    static $hasRenderedAComponentThisRequest = false;
    static $forceScriptInjection = false;

    static function provide()
    {
        on('flush-state', function () {
            static::$hasRenderedAComponentThisRequest = false;
            static::$forceScriptInjection = false;
        });

        if (config('livewire.inject_assets', true) === false) return;

        app('events')->listen(RequestHandled::class, function ($handled) {
            if (! str($handled->response->headers->get('content-type'))->contains('text/html')) return;
            if (! method_exists($handled->response, 'status') || $handled->response->status() !== 200) return;
            if ((! static::$hasRenderedAComponentThisRequest) && (! static::$forceScriptInjection)) return;
            if (app(FrontendAssets::class)->hasRenderedScripts) return;

            $html = $handled->response->getContent();

            if (str($html)->contains('</html>')) {
                $handled->response->setContent(static::injectAssets($html));
            }
        });
    }

    public function dehydrate()
    {
        static::$hasRenderedAComponentThisRequest = true;
    }

    static function injectAssets($html)
    {
        $livewireStyles = FrontendAssets::styles();
        $livewireScripts = FrontendAssets::scripts();

        $html = str($html);

        if ($html->test('/<\s*head[^>]*>/') && $html->test('/<\s*body[^>]*>/')) {
            return $html
                ->replaceMatches('/(<\s*head[^>]*>)/', '$1'.$livewireStyles)
                ->replaceMatches('/(<\s*\/\s*body\s*>)/', $livewireScripts.'$1')
                ->toString();
        }

        return $html
            ->replaceMatches('/(<\s*html[^>]*>)/', '$1'.$livewireStyles)
            ->replaceMatches('/(<\s*\/\s*html\s*>)/', $livewireScripts.'$1')
            ->toString();
    }
}
