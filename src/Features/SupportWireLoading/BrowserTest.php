<?php

namespace Livewire\Features\SupportWireLoading;

use Livewire\Component;
use Livewire\Form;
use Livewire\Livewire;

class BrowserTest extends \Tests\BrowserTestCase
{
    /** @test */
    function can_wire_target_to_a_form_object_property()
    {
        Livewire::visit(new class extends Component {
            public PostFormStub $form;

            public $localText = '';

            public function updating() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                usleep(500000);
            }

            public function render() {
                return <<<'HTML'
                    <div>
                        <section>
                            <span
                                wire:loading.remove
                                wire:target="localText">
                                Loaded localText...
                            </span>
                            <span
                                wire:loading
                                wire:target="localText"
                            >
                                Loading localText...
                            </span>
                            <input type="text" dusk="localInput" wire:model.live.debounce.100ms="localText">
                            {{ $localText }}
                        </section>
                        <section>
                            <span
                                wire:loading.remove
                                wire:target="form.text">
                                Loaded form.text...
                            </span>
                            <span
                                wire:loading
                                wire:target="form.text"
                            >
                                Loading form.text...
                            </span>
                            <input type="text" dusk="formInput" wire:model.live.debounce.100ms="form.text">
                            {{ $form->text }}
                        </section>
                    </div>
                HTML;
            }
        })
        ->waitForText('Loaded localText')
        ->assertSee('Loaded localText')
        ->type('@localInput', 'Text')
        ->waitUntilMissingText('Loaded localText')
        ->assertDontSee('Loaded localText')
        ->waitForText('Loaded localText')
        ->assertSee('Loaded localText')

        ->waitForText('Loaded form.text')
        ->assertSee('Loaded form.text')
        ->type('@formInput', 'Text')
        ->waitUntilMissingText('Loaded form.text')
        ->assertDontSee('Loaded form.text')
        ->waitForText('Loaded form.text')
        ->assertSee('Loaded form.text')
        ;
    }

    /** @test */
    function wire_loading_attr_doesnt_conflict_with_exist_one()
    {
        Livewire::visit(new class extends Component {
            public $localText = '';

            public function updating() {
                // Need to delay the update so that Dusk can catch the loading state change in the DOM.
                usleep(250000);
            }

            public function render() {
                return <<<'HTML'
                    <div>
                        <section>
                            <button
                                disabled
                                dusk="button"
                                wire:loading.attr="disabled"
                                wire:target="localText">
                                Submit
                            </button>
                            <input type="text" dusk="localInput" wire:model.live.debounce.100ms="localText">
                            {{ $localText }}
                        </section>
                    </div>
                HTML;
            }
        })
        ->waitForText('Submit')
        ->assertSee('Submit')
        ->assertAttribute('@button', 'disabled', 'true')
        ->type('@localInput', 'Text')
        ->assertAttribute('@button', 'disabled', 'true')
        ->waitForText('Text')
        ->assertAttribute('@button', 'disabled', 'true')
        ;
    }

    /** @test */
    function wire_loading_delay_is_removed_after_being_triggered_once()
    {
        /**
         * The (broken) scenario:
         *   - first request takes LONGER than the wire:loading.delay, so the loader shows (and hides) once
         *   - second request takes SHORTER than the wire:loading.delay, the loader shows, but never hides
         */
        Livewire::visit(new class extends Component {
            public $stuff;

            public $count = 0;

            public function updating() {
                // Need to delay the update, but only on the first request
                if ($this->count === 0) {
                    usleep(500000);
                }

                $this->count++;
            }


            public function render() {
                return <<<'HTML'
                    <div>
                        <div wire:loading.delay>
                            <span>Loading...</span>
                        </div>
                        <input wire:model.live="stuff" dusk="input" type="text">
                    </div>
                HTML;
            }
        })
        ->type('@input', 'Hello Caleb')
        ->waitForText('Loading...')
        ->assertSee('Loading...')
        ->waitUntilMissingText('Loading...')
        ->assertDontSee('Loading...')
        ->type('@input', 'Bye Caleb')
        ->pause(500) // wait for the loader to show when it shouldn't (second request is fast)
        ->assertDontSee('Loading...')
        ;
    }
}

class PostFormStub extends Form
{
    public $text = '';
}
