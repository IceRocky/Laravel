<?php

namespace Livewire\Mechanisms\ExtendBlade;

use Illuminate\Support\Facades\Blade;

use function Livewire\invade;
use function Livewire\on;

class ExtendBlade
{
    protected $directives = [];
    protected $precompilers = [];
    protected $renderCounter = 0;

    protected static $livewireComponents = [];

    function startLivewireRendering($component)
    {
        static::$livewireComponents[] = $component;
    }

    function endLivewireRendering()
    {
        array_pop(static::$livewireComponents);
    }

    static function currentRendering()
    {
        return end(static::$livewireComponents);
    }

    static function isRenderingLivewireComponent()
    {
        return ! empty(static::$livewireComponents);
    }

    function boot()
    {
        app()->singleton($this::class);

        Blade::directive('this', fn() => "window.Livewire.find('{{ \$_instance->getId() }}')");

        on('render', function ($target, $view) {
            $this->startLivewireRendering($target);

            $removals = [];

            if ($this->renderCounter === 0) {
                $customDirectives = app('blade.compiler')->getCustomDirectives();
                $precompilers = invade(app('blade.compiler'))->precompilers;

                foreach ($this->directives as $name => $handler) {
                    if (! isset($customDirectives[$name])) {
                        $customDirectives[$name] = $handler;

                        invade(app('blade.compiler'))->customDirectives = $customDirectives;

                        $removals[] = function () use ($name) {
                            $customDirectives = app('blade.compiler')->getCustomDirectives();

                            unset($customDirectives[$name]);

                            invade(app('blade.compiler'))->customDirectives = $customDirectives;
                        };
                    }
                }

                foreach ($this->precompilers as $handler) {
                    if (array_search($handler, $precompilers) === false) {
                        array_unshift($precompilers, $handler);

                        invade(app('blade.compiler'))->precompilers = $precompilers;

                        $removals[] = function () use ($handler) {
                            $precompilers = invade(app('blade.compiler'))->precompilers;

                            $index = array_search($handler, $precompilers);

                            if ($index === false) return;

                            unset($precompilers[$index]);

                            invade(app('blade.compiler'))->precompilers = $precompilers;
                        };
                    }
                }
            }

            $this->renderCounter++;

            return function ($html) use ($view, $removals, $target) {
                $this->endLivewireRendering();

                $this->renderCounter--;

                if ($this->renderCounter === 0) {
                    while ($removals) array_pop($removals)();
                }

                return $html;
            };
        });

        // This is a custom view engine that gets used when rendering
        // Livewire views. Things like letting certain exceptions bubble
        // to the handler, and registering custom directives like: "@this".
        app()->make('view.engine.resolver')->register('blade', function () {
            return new ExtendedCompilerEngine(app('blade.compiler'));
        });
    }

    function livewireOnlyDirective($name, $handler)
    {
        $this->directives[$name] = $handler;
    }

    function livewireOnlyPrecompiler($pattern, $handler)
    {
        $this->precompilers[] = function ($string) use ($pattern, $handler) {
            return preg_replace_callback($pattern, function ($matches) use ($handler, $string) {
                return $handler($matches, $string);
            }, $string);
        };
    }

    function getLivewireCompiler()
    {
        return new class extends \Illuminate\View\Compilers\BladeCompiler {
            //
        };
    }
}
