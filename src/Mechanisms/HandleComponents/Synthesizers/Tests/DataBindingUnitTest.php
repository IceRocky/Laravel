<?php

namespace Livewire\Mechanisms\HandleComponents\Synthesizers\Tests;

use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\Livewire;
use stdClass;

class DataBindingUnitTest extends \Tests\TestCase
{
    /** @test */
    public function update_component_data()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->set('foo', 'bar');

        $this->assertEquals('bar', $component->foo);
    }

    /** @test */
    public function update_nested_component_data_inside_array()
    {
        $component = Livewire::test(DataBindingStub::class);

        $component->set('foo', []);
        $component->set('foo.0', 'bar');
        $component->set('foo.bar', 'baz');

        $this->assertEquals(['bar', 'bar' => 'baz'], $component->foo);
    }
}

class DataBindingStub extends Component
{
    public $foo;
    public $bar;
    public $propertyWithHook;
    public $arrayProperty = ['foo', 'bar'];

    public function updatedPropertyWithHook($value)
    {
        $this->propertyWithHook = 'something else';
    }

    public function changeFoo($value)
    {
        $this->foo = $value;
    }

    public function changeArrayPropertyOne($value)
    {
        $this->arrayProperty[1] = $value;
    }

    public function removeArrayPropertyOne()
    {
        unset($this->arrayProperty[1]);
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}
