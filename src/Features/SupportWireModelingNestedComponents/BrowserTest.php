<?php

namespace Livewire\Features\SupportWireModelingNestedComponents;

use Livewire\Livewire;

/** @group morphing */
class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    public function can_bind_a_propert_from_parent_to_property_from_child()
    {
        Livewire::visit([
            new class extends \Livewire\Component {
                public $foo = 0;

                public function render() { return <<<'HTML'
                <div>
                    <span dusk="parent">Parent: {{ $foo }}</span>
                    <span x-text="$wire.foo" dusk="parent.ephemeral"></span>

                    <livewire:child wire:model="foo" />

                    <button wire:click="$refresh" dusk="refresh">refresh</button>
                </div>
                HTML; }
            },
            'child' => new class extends \Livewire\Component {
                #[Modelable]
                public $bar;

                public function render() { return <<<'HTML'
                <div>
                    <span dusk="child">Child: {{ $bar }}</span>
                    <span x-text="$wire.bar" dusk="child.ephemeral"></span>
                    <button wire:click="bar++" dusk="increment">increment</button>
                </div>
                HTML; }
            },
        ])
        ->assertSeeIn('@parent', 'Parent: 0')
        ->assertSeeIn('@child', 'Child: 0')
        ->assertSeeIn('@parent.ephemeral', '0')
        ->assertSeeIn('@child.ephemeral', '0')
        ->click('@increment')
        ->assertSeeIn('@parent', 'Parent: 0')
        ->assertSeeIn('@child', 'Child: 0')
        ->assertSeeIn('@parent.ephemeral', '1')
        ->assertSeeIn('@child.ephemeral', '1')
        ->waitForLivewire()->click('@refresh')
        ->assertSeeIn('@parent', 'Parent: 1')
        ->assertSeeIn('@child', 'Child: 1')
        ->assertSeeIn('@parent.ephemeral', '1')
        ->assertSeeIn('@child.ephemeral', '1')
        ;
    }
}
