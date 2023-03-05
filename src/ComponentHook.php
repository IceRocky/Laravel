<?php

namespace Livewire;

abstract class ComponentHook
{
    protected $component;

    private $propertyHooks = [];

    function setPropertyHook($name, $hook)
    {
        if (! isset($this->propertyHooks[$name])) $this->propertyHooks[$name] = [];

        $hook->setComponent($this->component);
        $hook->setPropertyName($name);

        $this->propertyHooks[$name][] = $hook;
    }

    function setComponent($component)
    {
        $this->component = $component;
    }

    function callBoot(...$params) {
        $callbacks = [];

        if (method_exists($this, 'boot')) $callbacks[] = $this->boot(...$params);

        foreach ($this->propertyHooks as $property => $hooks) {
            foreach ($hooks as $hook) {
                if (method_exists($hook, 'boot')) $callbacks[] = $hook->boot(...$params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callMount(...$params) {
        $callbacks = [];

        if (method_exists($this, 'mount')) $callbacks[] = $this->mount(...$params);

        foreach ($this->propertyHooks as $property => $hooks) {
            foreach ($hooks as $hook) {
                if (method_exists($hook, 'mount')) $callbacks[] = $hook->mount(...$params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callHydrate(...$params) {
        $callbacks = [];

        if (method_exists($this, 'hydrate')) $callbacks[] = $this->hydrate(...$params);

        foreach ($this->propertyHooks as $property => $hooks) {
            foreach ($hooks as $hook) {
                if (method_exists($hook, 'hydrate')) $callbacks[] = $hook->hydrate(...$params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callUpdate($propertyName, $fullPath, $newValue) {
        $callbacks = [];

        if (method_exists($this, 'update')) $callbacks[] = $this->update($propertyName, $fullPath, $newValue);

        foreach ($this->propertyHooks as $property => $hooks) {
            // Only run "update" on the appropriate hooks...
            if ($property !== $propertyName) continue;

            foreach ($hooks as $hook) {
                if (method_exists($hook, 'update')) $callbacks[] = $hook->update();
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callCall($method, $params, $returnEarly) {
        $callbacks = [];

        if (method_exists($this, 'call')) $callbacks[] = $this->call($method, $params, $returnEarly);

        foreach ($this->propertyHooks as $property => $hooks) {
            // Only run "call" on the appropriate hooks...
            if ($method !== $property) continue;

            foreach ($hooks as $hook) {
                if (method_exists($hook, 'call')) $callbacks[] = $hook->call($method, $params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callRender(...$params) {
        $callbacks = [];

        if (method_exists($this, 'render')) $callbacks[] = $this->render(...$params);

        foreach ($this->propertyHooks as $property => $hooks) {
            foreach ($hooks as $hook) {
                if (method_exists($hook, 'render')) $callbacks[] = $hook->render(...$params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callDehydrate(...$params) {
        $callbacks = [];

        if (method_exists($this, 'dehydrate')) $callbacks[] = $this->dehydrate(...$params);

        foreach ($this->propertyHooks as $property => $hooks) {
            foreach ($hooks as $hook) {
                if (method_exists($hook, 'dehydrate')) $callbacks[] = $hook->dehydrate(...$params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callDestroy(...$params) {
        $callbacks = [];

        if (method_exists($this, 'destroy')) $callbacks[] = $this->destroy(...$params);

        foreach ($this->propertyHooks as $property => $hooks) {
            foreach ($hooks as $hook) {
                if (method_exists($hook, 'destroy')) $callbacks[] = $hook->destroy(...$params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function callException(...$params) {
        $callbacks = [];

        if (method_exists($this, 'exception')) $callbacks[] = $this->exception(...$params);

        foreach ($this->propertyHooks as $property => $hooks) {
            foreach ($hooks as $hook) {
                if (method_exists($hook, 'exception')) $callbacks[] = $hook->exception(...$params);
            }
        }

        return function (...$params) use ($callbacks) {
            foreach ($callbacks as $callback) {
                if (is_callable($callback)) $callback(...$params);
            }
        };
    }

    function getProperties()
    {
        return $this->component->all();
    }

    function getProperty($name)
    {
        return data_get($this->getProperties(), $name);
    }

    function hasAttribute($propertyName, $attribute)
    {
        return \Livewire\Drawer\Utils::hasAttribute($this->component, $propertyName, $attribute);
    }

    function storeSet($key, $value)
    {
        store($this->component)->set($key, $value);
    }

    function storePush($key, $value, $iKey = null)
    {
        store($this->component)->push($key, $value, $iKey);
    }

    function storeGet($key, $default = null)
    {
        return store($this->component)->get($key, $default);
    }

    function storeHas($key)
    {
        return store($this->component)->has($key);
    }
}
