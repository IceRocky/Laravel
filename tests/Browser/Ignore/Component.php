<?php

namespace Tests\Browser\Ignore;

use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;

class Component extends BaseComponent
{
    public function render()
    {
        return View::file(__DIR__.'/view.blade.php');
    }
}
