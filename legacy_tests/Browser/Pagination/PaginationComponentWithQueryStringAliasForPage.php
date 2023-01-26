<?php

namespace LegacyTests\Browser\Pagination;

use LegacyTests\Browser\Pagination\Post;
use Illuminate\Support\Facades\View;
use Livewire\Component as BaseComponent;
use Livewire\WithPagination;

class PaginationComponentWithQueryStringAliasForPage extends BaseComponent
{
    use WithPagination;

    protected $queryString = [
        'paginators.page' => ['as' => 'p']
    ];

    public function render()
    {
        return View::file(__DIR__.'/view.blade.php', [
            'posts' => Post::paginate(3),
        ]);
    }
}
