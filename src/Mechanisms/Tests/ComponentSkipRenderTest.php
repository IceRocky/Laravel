<?php

namespace Livewire\Mechanisms\Tests;

use Illuminate\Support\Facades\Route;
use Livewire\Component;
use Livewire\Livewire;

use function Livewire\store;
use function Livewire\str;

class ComponentSkipRenderTest extends \Tests\TestCase
{
    /** @test */
    public function component_renders_like_normal()
    {
        $component = Livewire::test(ComponentSkipRenderStub::class);

        $this->assertTrue(
            str($component->html())->contains([$component->id(), 'foo'])
        );
    }

    /** @test */
    public function on_skip_render_render_is_not_called()
    {
        $component = Livewire::test(ComponentSkipRenderStub::class);

        $component->call('noop');

        $this->assertNull($component->html());
    }

    /** @test */
    public function with_skip_render_attribute_render_is_not_called()
    {
        $component = Livewire::test(ComponentSkipRenderAttributeStub::class);

        $component->call('noop');

        $this->assertNull($component->html());
    }

    /** @test */
    public function on_redirect_in_mount_render_is_not_called()
    {
        // $component = Livewire::test(ComponentSkipRenderOnRedirectInMountStub::class);

        // $this->assertEquals('/foo', $component->effects['redirect']);
        // $this->assertNull($component->view());

        // Route::get('/403', ComponentSkipRenderOnRedirectInMountStub::class);
        // $this->get('/403')->assertRedirect('/foo');

        $component = Livewire::test(ComponentSkipRenderOnRedirectHelperInMountStub::class);

        $this->assertStringEndsWith('/bar', $component->effects['redirect']);
        $this->assertNull($component->view());

        Route::get('/403', ComponentSkipRenderOnRedirectHelperInMountStub::class);
        $this->get('/403')->assertRedirect('/bar');
    }
}

class ComponentSkipRenderStub extends Component
{
    private $noop = false;

    public function noop()
    {
        $this->noop = true;

        $this->skipRender();
    }

    public function render()
    {
        if ($this->noop) {
            throw new \RuntimeException('Render should not be called after noop()');
        }

        return app('view')->make('null-view');
    }
}

class ComponentSkipRenderAttributeStub extends Component
{
    private $noop = false;

    #[\Livewire\Attributes\SkipRender]
    public function noop()
    {
        $this->noop = true;
    }

    public function render()
    {
        if ($this->noop) {
            throw new \RuntimeException('Render should not be called after noop()');
        }

        return app('view')->make('null-view');
    }
}

class ComponentSkipRenderOnRedirectInMountStub extends Component
{
    public function mount()
    {
        store($this)->set('redirect', '/yoyoyo');

        $this->skipRender();

        $this->redirect('/foo');
    }

    public function render()
    {
        throw new \RuntimeException('Render should not be called on redirect');
    }
}

class ComponentSkipRenderOnRedirectHelperInMountStub extends Component
{
    public function mount()
    {
        $this->skipRender();

        return redirect('/bar');
    }

    public function render()
    {
        throw new \RuntimeException('Render should not be called on redirect');
    }
}
