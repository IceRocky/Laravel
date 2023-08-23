<?php

namespace Livewire\Features\SupportQueryString;

use Livewire\Livewire;
use Livewire\Component;

class UnitTest extends \Tests\TestCase
{
    /** @test */
    function can_track_properties_in_the_url()
    {
        $component = Livewire::test(new class extends Component {
            #[BaseUrl]
            public $count = 1;

            function increment() { $this->count++; }

            public function render() {
                return '<div></div>';
            }
        });

        $this->assertTrue(isset($component->effects['url']));
    }
}
