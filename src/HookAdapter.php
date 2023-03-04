<?php

namespace Livewire;

use Livewire\Drawer\Utils;
use WeakMap;

class HookAdapter
{
    public $components;

    function adapt($hooks)
    {
        $this->components = new WeakMap;

        foreach ($hooks as $hook) {
            on('mount', function ($component, $params, $parent) use ($hook) {
                $hook = $this->initializeHook($hook, $component);
                $hook->callBoot();
                $hook->callMount($params, $parent, $parent);
            });

            on('hydrate', function ($target, $meta) use ($hook) {
                $hook = $this->initializeHook($hook, $target);
                $hook->callBoot();
                $hook->callHydrate($meta);
            });
        }

        on('update', function ($root, $fullPath, $newValue) {
            if (! is_object($root)) return;

            $propertyName = Utils::beforeFirstDot($fullPath);

            return $this->proxyCallToHooks($root, 'callUpdate')($propertyName, $fullPath, $newValue);
        });

        on('call', function ($target, $method, $params, $addEffect, $earlyReturn) {
            if (! is_object($target)) return;

            return $this->proxyCallToHooks($target, 'callCall')($method, $params, $earlyReturn);
        });

        on('render', function ($target, $view, $data) {
            return $this->proxyCallToHooks($target, 'callRender')($view, $data);
        });

        on('dehydrate', function ($target, $context) {
            $this->proxyCallToHooks($target, 'callDehydrate')($context);

            $this->proxyCallToHooks($target, 'callDestroy')($context);
        });

        on('exception', function ($target, $e, $stopPropagation) {
            return $this->proxyCallToHooks($target, 'callException')($e, $stopPropagation);
        });
    }

    public function initializeHook($hook, $target)
    {
        if (! isset($this->components[$target])) $this->components[$target] = [];

        $this->components[$target][] = $hook = new $hook;

        $hook->setComponent($target);

        $propertiesAndMethods = [
            ...(new \ReflectionClass($target))->getProperties(),
            ...(new \ReflectionClass($target))->getMethods(),
        ];

        foreach ($propertiesAndMethods as $property) {
            $attributes = $property->getAttributes();

            foreach ($attributes as $attribute) {
                if (is_subclass_of($attribute->getName(), PropertyHook::class)) {
                    $propertyHook = $attribute->newInstance();

                    $hook->setPropertyHook($property->getName(), $propertyHook);
                }
            }
        }

        return $hook;
    }

    function proxyCallToHooks($target, $method) {
        return function (...$params) use ($target, $method) {
            $callbacks = [];

            foreach ($this->components[$target] ?? [] as $hook) {
                $callbacks[] = $hook->{$method}(...$params);
            }

            return function (...$forwards) use ($callbacks) {
                foreach ($callbacks as $callback) {
                    $callback(...$forwards);
                }
            };
        };
    }
}
