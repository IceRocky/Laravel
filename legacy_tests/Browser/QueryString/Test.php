<?php

namespace LegacyTests\Browser\QueryString;

use Livewire\Livewire;
use LegacyTests\Browser\TestCase;
use LegacyTests\Browser\QueryString\NestedComponent;
use Laravel\Dusk\Browser;

class Test extends TestCase
{
    public function test_core_query_string_pushstate_logic()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, [Component::class, 'nested' => NestedComponent::class], '?foo=baz&eoo=lob')
                /*
                 * Check that the intial property value is set from the query string.
                 */
                ->assertSeeIn('@output', 'baz')
                ->assertInputValue('@input', 'baz')

                /*
                 * Check that Livewire doesn't mess with query string order.
                 */
                ->assertScript('return !! window.location.search.match(/foo=baz&eoo=lob/)')

                /**
                 * Change a property and see it reflected in the query string.
                 */
                ->waitForLivewire()->type('@input', 'bob')
                ->assertSeeIn('@output', 'bob')
                ->assertInputValue('@input', 'bob')
                ->assertQueryStringHas('foo', 'bob')

                /**
                 * Hit the back button and see the change reflected in both
                 * the query string AND the actual property value.
                 */
                ->waitForLivewire()->back()
                ->assertSeeIn('@output', 'baz')
                ->assertQueryStringHas('foo', 'baz')

                /**
                 * Setting a property BACK to it's original value
                 * removes the property entirely from the query string.
                 * As long as it wasn't there to begin with...
                 */
                ->assertSeeIn('@bar-output', 'baz')
                ->waitForLivewire()->type('@bar-input', 'new-value')
                ->assertQueryStringHas('bar')
                ->waitForLivewire()->type('@bar-input', 'baz')
                ->assertQueryStringMissing('bar')

                /**
                 * Add a nested component on the page and make sure
                 * both components play nice with each other.
                 */
                ->assertQueryStringMissing('baz')
                ->waitForLivewire()->click('@show-nested')
                ->pause(25)
                ->assertSeeIn('@baz-output', 'bop')
                ->waitForLivewire()->type('@baz-input', 'lop')
                ->assertQueryStringHas('baz', 'lop')
                ->waitForLivewire()->type('@input', 'plop')
                ->waitForLivewire()->type('@baz-input', 'ploop')
                ->assertQueryStringHas('foo', 'plop')
                ->assertQueryStringHas('baz', 'ploop')
                ->back()
                ->back()
                ->assertQueryStringHas('baz', 'lop')

                /**
                 * Can change an array property.
                 */
                ->assertSeeIn('@bob.output', '["foo","bar"]')
                ->waitForLivewire()->click('@bob.modify')
                ->assertSeeIn('@bob.output', '["foo","bar","baz"]')
                ->refresh()
                ->assertSeeIn('@bob.output', '["foo","bar","baz"]')
            ;
        });
    }

    public function test_query_string_format_in_rfc_1738()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, Component::class)
                ->waitForLivewire()->type('@input', 'foo bar')
                ->assertSeeIn('@output', 'foo bar')
                ->assertInputValue('@input', 'foo bar')
                ->assertQueryStringHas('foo', 'foo bar')
                ->assertScript('return !! window.location.search.match(/foo=foo\+bar/)')
                ->assertScript('return !! window.location.search.match(/quux%26quuz/)')
            ;
        });
    }

    public function test_query_string_with_rfc_1738_bookmarked_url()
    {
        $this->browse(function (Browser $browser) {
            $queryString = '?qux[hyphen]=quux-quuz&qux[comma]=quux,quuz&qux[ampersand]=quux%26quuz&qux[space]=quux+quuz&qux[array][0]=quux&qux[array][1]=quuz';

            $this->visitLivewireComponent($browser, Component::class, $queryString)
                ->assertSeeIn('@qux.hyphen', 'quux-quuz')
                ->assertSeeIn('@qux.comma', 'quux,quuz')
                ->assertSeeIn('@qux.ampersand', 'quux&quuz')
                ->assertSeeIn('@qux.space', 'quux quuz')
                ->assertSeeIn('@qux.array', '["quux","quuz"]')
            ;
        });
    }

    public function test_query_string_with_rfc_3986_bookmarked_url_forbackwards_compatibility()
    {
        $this->browse(function (Browser $browser) {
            $queryString = '?qux%5Bhyphen%5D=quux-quuz&qux%5Bcomma%5D=quux%2Cquuz&qux%5Bampersand%5D=quux%26quuz&qux%5Bspace%5D=quux%20quuz&qux%5Barray%5D%5B%5D=quux&qux%5Barray%5D%5B%5D=quuz';

            $this->visitLivewireComponent($browser, Component::class, $queryString)
                ->assertSeeIn('@qux.hyphen', 'quux-quuz')
                ->assertSeeIn('@qux.comma', 'quux,quuz')
                ->assertSeeIn('@qux.ampersand', 'quux&quuz')
                ->assertSeeIn('@qux.space', 'quux quuz')
                ->assertSeeIn('@qux.array', '["quux","quuz"]')
            ;
        });
    }

    public function test_query_string_with_property_values_containing_ampersand_characters()
    {
        $this->browse(function (Browser $browser) {
            $queryString = '?foo=bar%26quux%26quuz';

            $this->visitLivewireComponent($browser, Component::class, $queryString)
                ->assertSeeIn('@output', 'bar&quux&quuz')
                ->refresh()
                ->assertSeeIn('@output', 'bar&quux&quuz')
            ;
        });
    }

    public function test_back_button_after_refresh_works_with_nested_components()
    {
        $this->markTestSkipped(); // @todo: fix this...

        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, [Component::class, 'nested' => NestedComponent::class])
                ->waitForLivewire()->click('@show-nested')
                ->waitForLivewire()->type('@baz-input', 'foo')
                ->assertSeeIn('@baz-output', 'foo')
                ->refresh()
                ->back()
                ->forward()
                ->assertSeeIn('@baz-output', 'foo')

                // Interact with the page again to make sure everything still works.
                ->waitForLivewire()->type('@baz-input', 'bar')
                ->assertSeeIn('@baz-output', 'bar')
            ;
        });
    }

    // public function test_excepts_results_in_no_query_string()
    // {
    //     $this->browse(function (Browser $browser) {
    //         $this->visitLivewireComponent($browser, ComponentWithExcepts::class)
    //             ->assertSeeIn('@output', 'On page 1');

    //         $this->assertStringNotContainsString('?', $browser->driver->getCurrentURL());

    //         $this->visitLivewireComponent($browser, ComponentWithExcepts::class, '?page=1')
    //             ->assertSeeIn('@output', 'On page 1');

    //         $this->assertStringNotContainsString('?', $browser->driver->getCurrentURL());

    //         $this->visitLivewireComponent($browser, ComponentWithExcepts::class, '?page=2')
    //             ->assertSeeIn('@output', 'On page 2')
    //             ->assertQueryStringHas('page', 2);
    //     });
    // }

    public function test_that_input_values_are_set_after_back_button()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, DirtyDataComponent::class)
                ->waitForLivewire()->type('@input', 'foo')
                ->waitForLivewire()->click('@nextPage')
                ->assertSee('The Next Page')
                ->back()
                ->waitFor('@input')
                ->assertInputValue('@input', 'foo')
                ->forward()
                ->back()
                ->waitFor('@input')
                ->assertInputValue('@input', 'foo')
            ;
        });
    }

    public function test_dynamic_query_string_method()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, ComponentWithMethodInsteadOfProperty::class)
                ->assertQueryStringHas('foo', 'bar')
            ;
        });
    }

    public function test_nested_component_query_string_works_when_parent_is_not_using_query_string()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, [ParentComponentWithNoQueryString::class, 'nested' => NestedComponent::class])
                ->assertPathBeginsWith('/livewire-dusk')
                ->waitForLivewire()->click('@toggle-nested')

                // assert the path hasn't change to /livewire/message
                ->assertPathBeginsWith('/livewire-dusk')
                ->assertQueryStringHas('baz', 'bop')
            ;
        });
    }

    /** @test */
    public function it_does_not_build_query_string_from_referer_if_it_is_coming_from_a_full_page_redirect()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, RedirectLinkToQueryStringComponent::class)
                ->assertPathBeginsWith('/livewire-dusk/LegacyTests%5CBrowser%5CQueryString%5CRedirectLinkToQueryStringComponent')
                ->click('@link')

                ->pause(200)
                // assert the path has changed to new component path
                ->assertPathBeginsWith('/livewire-dusk/LegacyTests%5CBrowser%5CQueryString%5CNestedComponent')
                ->assertQueryStringHas('baz', 'bop')
            ;
        });
    }

    public function test_query_string_hooks_from_traits()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, ComponentWithTraits::class)
                ->assertSee('Post #1')
                ->assertSee('Post #2')
                ->assertSee('Post #3')
                ->assertQueryStringMissing('search')
                // Search for posts where title contains "1".
                ->waitForLivewire()->type('@search', '1')
                ->assertSee('Post #1')
                ->assertSee('Post #10')
                ->assertSee('Post #11')
                ->assertDontSee('Post #2')
                ->assertDontSee('Post #3')
                ->assertQueryStringHas('search', '1')
                // Search for posts where title contains "42".
                ->waitForLivewire()->type('@search', '42')
                ->assertSee('Post #42')
                ->assertQueryStringHas('search', '42')
            ;
        });
    }

    public function test_query_string_aliases()
    {
        $this->browse(function (Browser $browser) {
            $this->visitLivewireComponent($browser, ComponentWithAliases::class)
                ->assertQueryStringMissing('s')
                // Search for posts where title contains "1".
                ->waitForLivewire()->type('@search', '1')
                ->assertQueryStringHas('s', '1')
                ->assertInputValue('@search', '1')
                // Search for posts where title contains "qwerty".
                ->waitForLivewire()->type('@search', 'qwerty')
                ->assertQueryStringHas('s', 'qwerty')
                ->assertInputValue('@search', 'qwerty')
            ;
        });
    }
}
