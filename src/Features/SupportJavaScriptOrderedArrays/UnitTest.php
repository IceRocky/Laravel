<?php

namespace Livewire\Features\SupportJavaScriptOrderedArrays;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;

class UnitTest extends \Tests\TestCase
{
    public function setUp(): void
    {
        $this->markTestSkipped('Needs to be reworked as per https://github.com/livewire/problems/wiki/JavaScript-and-PHP-handle-arrays-differently');
    }

    /** @test */
    public function unordered_numeric_keyed_arrays_are_reordered_so_javascript_doesnt_do_it_for_us()
    {
        $orderedNumericArray = [
            1 => 'foo',
            0 => 'bar',
        ];

        Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])
            ->assertSnapshotSet('foo', [
                0 => 'bar',
                1 => 'foo',
            ], true);
    }

    /** @test */
    public function unordered_string_keyed_arrays_are_reordered_so_javascript_doesnt_do_it_for_us()
    {
        $orderedNumericArray = [
            '20' => 'baz',
            '19' => 'bar',
            '0' => 'foo',
        ];

        Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])
            ->assertSnapshotSet('foo', [
                '0' => 'foo',
                '19' => 'bar',
                '20' => 'baz',
            ], true);
    }

    /** @test */
    public function numeric_keys_are_ordered_before_string_keys_so_javascript_doesnt_do_it_for_us()
    {
        $orderedNumericArray = [
            1 => 'foo',
            'bob' => 'lob',
            0 => 'bar',
            'abob' => 'lob',
        ];

        Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])
            ->assertSnapshotSet('foo', [
                0 => 'bar',
                1 => 'foo',
                'bob' => 'lob',
                'abob' => 'lob',
            ], true);
    }

    /** @test */
    public function ordered_numeric_arrays_are_reindexed_deeply()
    {
        $orderedNumericArray = [
            [
                1 => 'foo',
                0 => 'bar',
            ],
        ];

        Livewire::test(ComponentWithPropertiesStub::class, ['foo' => $orderedNumericArray])
            ->assertSet('foo', [[0 => 'bar', 1 => 'foo']]);
    }
}

class ComponentWithPropertiesStub extends Component
{
    public $foo;

    public function mount($foo)
    {
        $this->foo = $foo;
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            {{ var_dump($this->foo) }}
        </div>
        HTML;
    }
}

class ModelStub extends Model
{
    protected $guarded = [];
}

