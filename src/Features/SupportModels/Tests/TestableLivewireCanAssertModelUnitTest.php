<?php

namespace Livewire\Features\SupportModels\Tests;

use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Livewire\Livewire;
use Sushi\Sushi;

class TestableLivewireCanAssertModelUnitTest extends \Tests\TestCase
{
    /** @test */
    public function can_assert_model_property_value()
    {
        Livewire::test(PropertyTestingComponent::class, [
                'model' => ModelForPropertyTesting::first(),
            ])
            ->assertSet('model.foo.bar', 'baz');
    }
}

class ModelForPropertyTesting extends Model
{
    use Sushi;

    protected $casts = ['foo' => 'array'];

    protected function getRows()
    {
        return [
            ['foo' => json_encode(['bar' => 'baz'])],
        ];
    }
}

class PropertyTestingComponent extends Component
{
    public $model;

    public function render()
    {
        return '<div></div>';
    }
}
