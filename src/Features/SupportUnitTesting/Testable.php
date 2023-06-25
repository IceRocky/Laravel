<?php

namespace Livewire\Features\SupportUnitTesting;

use Synthetic\TestableSynthetic;
use PHPUnit\Framework\Assert as PHPUnit;
use Livewire\Mechanisms\DataStore;
use Livewire\Features\SupportValidation\TestsValidation;
use Livewire\Features\SupportFileDownloads\TestsFileDownloads;
use Livewire\Features\SupportEvents\TestsEvents;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Arr;
use Livewire\Features\SupportRedirects\TestsRedirects;

use function Livewire\store;
use function Livewire\on;

class Testable extends BaseTestable
{
    use MakesAssertions,
        TestsEvents,
        TestsRedirects,
        TestsValidation,
        TestsFileDownloads;

    static function create($name, $params = [], $queryParams = [])
    {
        if (is_array($otherComponents = $name)) {
            $name = array_shift($otherComponents);

            foreach ($otherComponents as $key => $value) {
                if (is_numeric($key)) app('livewire')->component($value);
                else app('livewire')->component($key, $value);
            }
        }

        if (is_object($name)) {
            $name = $name::class;
        }

        $uri = 'livewire-test';

        $symfonyRequest = \Symfony\Component\HttpFoundation\Request::create(
            $uri, 'GET', $parameters = $queryParams,
            $cookies = [], $files = [], $server = [], $content = null
        );

        $request = \Illuminate\Http\Request::createFromBase($symfonyRequest);

        app()->instance('request', $request);

        app('request')->headers->set('X-Livewire', true);

        // \Illuminate\Support\Facades\Facade::clearResolvedInstance('request');

        // This allows the user to test a component by it's class name,
        // and not have to register an alias.
        if (class_exists($name)) {
            if (! is_subclass_of($name, \Livewire\Component::class)) {
                throw new \Exception('Class ['.$name.'] is not a subclass of Livewire\Component.');
            }

            $componentClass = $name;

            app('livewire')->component($name = str()->random(20), $componentClass);
        }

        $component = null;

        $forget = on('mount', function ($instance) use (&$component, &$forget) {
            $forget();

            $component = $instance;
        });

        $html = app('livewire')->mount($name, $params);

        $snapshot = static::extractAttributeDataFromHtml($html, 'wire:snapshot');
        $effects = static::extractAttributeDataFromHtml($html, 'wire:effects');

        store($component)->set('testing.html', $html);

        return new static($snapshot, $effects, $component);
    }

    static function extractAttributeDataFromHtml($html, $attribute)
    {
        $data = (string) str($html)->betweenFirst($attribute.'="', '"');

        return json_decode(
            htmlspecialchars_decode($data, ENT_QUOTES|ENT_SUBSTITUTE),
            associative: true,
        );
    }

    static function actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, $driver = null)
    {
        if (isset($user->wasRecentlyCreated) && $user->wasRecentlyCreated) {
            $user->wasRecentlyCreated = false;
        }

        auth()->guard($driver)->setUser($user);

        auth()->shouldUse($driver);
    }

    function html($stripInitialData = false)
    {
        $html = store($this->target)->get('testing.html');

        if ($stripInitialData) {
            $removeMe = (string) str($html)->betweenFirst(
                'wire:snapshot="', '"'
            );

            $html = str_replace($removeMe, '', $html);

            $removeMe = (string) str($html)->betweenFirst(
                'wire:effects="', '"'
            );

            $html = str_replace($removeMe, '', $html);
        }

        return $html;
    }

    function view()
    {
        return store($this->target)->get('testing.view');
    }

    public function viewData($key)
    {
        return $this->view()->getData()[$key];
    }

    public function id()
    {
        return $this->target->id();
    }

    public function instance()
    {
        return $this->target;
    }

    function call($method, ...$params)
    {
        if ($method === '$refresh') {
            return parent::commit();
        }

        if ($method === '$set') {
            return parent::set(...$params);
        }

        return parent::call($method, ...$params);
    }

    public function fill($values)
    {
        //
    }

    public function toggle($name)
    {
        //
    }

    public function updateProperty($name, $value = null)
    {
        return parent::set($name, $value);
    }

    public function syncInput($name, $value)
    {
        //
    }

    public function syncUploadedFiles($name, $files, $isMultiple = false)
    {
        //
    }

    public function sendMessage($message, $payload)
    {
        //
    }

    public function dump()
    {
        //
    }

    public function tap($callback)
    {
        //
    }
}
