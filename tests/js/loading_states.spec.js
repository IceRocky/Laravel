import { wait } from 'dom-testing-library'
import { mount } from './utils'

test('show element while loading', async () => {
    mount('<button wire:click="onClick"></button><span style="display: none" wire:loading></span>')

    expect(document.querySelector('span').style.display).toEqual('none')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')
    })
})

test('show element while targeted element is loading', async () => {
    mount(
`<button wire:ref="foo" wire:click="onClick"></button>
<span style="display: none" wire:loading wire:target="foo"></span>
<h1 style="display: none" wire:loading wire:target="bar"></h1>`
    )

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')
        expect(document.querySelector('h1').style.display).toEqual('none')
    })
})

test('loading element can have multiple targets', async () => {
    mount(
`<button wire:ref="foo" wire:click="onClick"></button>
<a wire:ref="bar" wire:click="onClick"></a>
<span style="display: none" wire:loading wire:target="foo, bar"></span>`
    )

    document.querySelector('a').click()

    await wait( () => {
        expect(document.querySelector('span').style.display).toEqual('inline-block')
    })
})

test('add element class while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.class="foo-class"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('foo-class')).toBeTruthy()
    })
})

test('add element class while loading only after minimum time', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.class.min.100ms="foo-class"></span>')

    // @todo - find a good way to test this.
})

test('add element class with spaces while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.class="foo bar"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('foo')).toBeTruthy()
        expect(document.querySelector('span').classList.contains('bar')).toBeTruthy()
    })
})

test('remove element class while loading', async () => {
    mount('<button wire:click="onClick"></button><span class="hidden" wire:loading.class.remove="hidden"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').classList.contains('hidden')).toBeFalsy()
    })
})

test('add element attribute while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.attr="disabled"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').hasAttribute('disabled')).toBeTruthy()
    })
})

test('remove element attribute while loading', async () => {
    mount('<button wire:click="onClick"></button><span wire:loading.attr.remove="disabled" disabled="true"></span>')

    document.querySelector('button').click()

    await wait(() => {
        expect(document.querySelector('span').hasAttribute('disabled')).toBeFalsy()
    })
})

// //TODO - throw a 500 error return from Livewire in a test - maybe a new method in util instead of mount?
test('loading state is reset when an error is thrown', async () => {

})