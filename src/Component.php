<?php

namespace Livewire;

use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\View;
use Livewire\Exceptions\PublicPropertyTypeNotAllowedException;

abstract class Component
{
    use Concerns\ValidatesInput,
        Concerns\DetectsDirtyProperties,
        Concerns\HandlesActions,
        Concerns\PerformsRedirects,
        Concerns\ReceivesEvents,
        Concerns\InteractsWithProperties,
        Concerns\TracksRenderedChildren;

    public $id;
    protected $lifecycleHooks = [
        'mount', 'hydrate', 'updating', 'updated',
    ];

    public function __construct($id)
    {
        $this->id = $id;
        $this->initializeTraits();
    }

    protected function initializeTraits()
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'initialize'.class_basename($trait))) {
                $this->{$method}();
            }
        }
    }

    public function getName()
    {
        $namespace = collect(explode('.', str_replace(['/', '\\'], '.', config('livewire.class_namespace', 'App\\Http\\Livewire'))))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        $name = collect(explode('.', str_replace(['/', '\\'], '.', static::class)))
            ->map([Str::class, 'kebab'])
            ->implode('.');

        if (Str::startsWith($name, $namespace)) {
            return Str::substr($name, strlen($namespace) + 1);
        }

        return $name;
    }

    public function render()
    {
        return view("livewire.{$this->getName()}");
    }

    public function cache($key = null, $value = null)
    {
        $cacheManager = new ComponentCacheManager($this);

        if (is_null($key)) {
            return $cacheManager;
        }

        if (is_null($value)) {
            return $cacheManager->get($key);
        }

        return $cacheManager->put($key, $value);
    }

    public function output($errors = null)
    {
        // In the service provider, we hijack Laravel's Blade engine
        // with our own. However, we only want Livewire hijackings,
        // while we're rendering Livewire components. So we'll
        // activate it here, and deactivate it at the end
        // of this method.
        $engine = app('view.engine.resolver')->resolve('blade');
        $engine->startLivewireRendering($this);

        $view = $this->render();

        // Normalize all the public properties in the component for JavaScript.
        $this->normalizePublicPropertiesForJavaScript();

        throw_unless($view instanceof View,
            new \Exception('"render" method on ['.get_class($this).'] must return instance of ['.View::class.']'));

        $this->setErrorBag(
            $errorBag = $errors ?: ($view->errors ?: $this->getErrorBag())
        );

        $view
            ->with([
                'errors' => (new ViewErrorBag)->put('default', $errorBag),
                '_instance' => $this,
            ])
            // Automatically inject all public properties into the blade view.
            ->with($this->getPublicPropertiesDefinedBySubClass());

        $output = $view->render();

        $engine->endLivewireRendering();

        return $output;
    }

    public function normalizePublicPropertiesForJavaScript()
    {
        $normalizedProperties = $this->castDataToJavaScriptReadableTypes(
            $this->reindexArraysWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder(
                $this->castDataFromUserDefinedCasters(
                    $this->getPublicPropertiesDefinedBySubClass()
                )
            )
        );

        foreach ($normalizedProperties as $key => $value) {
            $this->setPropertyValue($key, $value);
        }
    }

    public function castDataFromUserDefinedCasters($data)
    {
        $dataCaster = new DataCaster;
        $casts = $this->casts ?? [];

        return collect($data)->map(function ($value, $key) use ($casts, $dataCaster) {
            if (isset($casts[$key])) {
                $type = $casts[$key];

                return $dataCaster->castFrom($type, $value);
            } else {
                return $value;
            }
        })->all();
    }

    protected function reindexArraysWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($data)
    {
        if (! is_array($data)) {
            return $data;
        }

        $normalizedData = $data;

        // Make sure string keys are last (but not ordered). JSON.parse will do this.
        uksort($normalizedData, function ($a, $b) {
            return is_string($a) && is_numeric($b)
                ? 1
                : 0;
        });

        // Order numeric indexes.
        uksort($normalizedData, function ($a, $b) {
            return is_numeric($a) && is_numeric($b)
                ? $a > $b
                : 0;
        });

        return array_map(function ($value) {
            return $this->reindexArraysWithNumericKeysOtherwiseJavaScriptWillMessWithTheOrder($value);
        }, $normalizedData);
    }

    public function castDataToJavaScriptReadableTypes($data)
    {
        array_walk($data, function ($value, $key) {
            throw_unless(
                is_bool($value) || is_null($value) || is_array($value) || is_numeric($value) || is_string($value),
                new PublicPropertyTypeNotAllowedException($this->getName(), $key, $value)
            );
        });

        return json_decode(json_encode($data), true);
    }

    public function __call($method, $params)
    {
        if (
            in_array($method, $this->lifecycleHooks)
            || Str::startsWith($method, ['updating', 'updated'])
        ) {
            // Eat calls to the lifecycle hooks if the dev didn't define them.
            return;
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
