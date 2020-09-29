<?php

namespace Tests\Unit;

use Livewire\Component;
use Livewire\Livewire;

class TestableLivewireCanAssertRedirectTest extends TestCase
{
    /** @test */
    public function can_assert_a_redirect_without_a_uri()
    {
        $component = Livewire::test(RedirectComponent::class);

        $component->call('performRedirect');

        $component->assertRedirect();
    }

    /** @test */
    public function can_fail_a_redirect_assertion()
    {
        $this->expectException(\PHPUnit\Framework\AssertionFailedError::class);

        Livewire::test(RedirectComponent::class)
            ->assertRedirect();
    }

    /** @test */
    public function can_assert_a_redirect_with_a_uri()
    {
        $component = Livewire::test(RedirectComponent::class);

        $component->call('performRedirect');

        $component->assertRedirect('/some');
    }
}

class RedirectComponent extends Component
{
    public function performRedirect()
    {
        $this->redirect('/some');
    }

    public function render()
    {
        return view('null-view');
    }
}
