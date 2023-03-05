import { findComponent, hasComponent, releaseComponent, resurrect, state, storeComponent } from './state'
import { monkeyPatchDomSetAttributeToAllowAtSymbols } from 'utils'
import { synthetic, trigger } from './synthetic/index'
import { Component } from './component'
import morph from '@alpinejs/morph'
import history from '@alpinejs/history'
import intersect from '@alpinejs/intersect'
import Alpine from 'alpinejs'

export function start() {
    monkeyPatchDomSetAttributeToAllowAtSymbols()

    Alpine.interceptInit(Alpine.skipDuringClone(el => {
        initElement(el)
    }))

    Alpine.plugin(morph)
    Alpine.plugin(history)
    Alpine.plugin(intersect)

    Alpine.addRootSelector(() => '[wire\\:id]')

    Alpine.start()

    setTimeout(() => window.Livewire.initialRenderIsFinished = true)
}

function initElement(el) {
    if (el.hasAttribute('wire:id')) initComponent(el)

    let component

    // @todo: This is bad flow.
    // We have this in a try / catch, becuase if you try to find the "closest component"
    // and one if not found, it will error out rather than breaking things
    // downstream, but in this case we don't want to error out.
    try { component = closestComponent(el) } catch (e) {}

    component && trigger('element.init', el, component)
}

export function initComponent(el) {
    if (el.__livewire) return;

    let id = el.getAttribute('wire:id')
    let initialData = JSON.parse(el.getAttribute('wire:data'))

    if (! initialData) initialData = resurrect(id)

    let component = new Component(synthetic(initialData).__target, el, id)

    el.__livewire = component

    trigger('component.init', component)

    // This makes anything that would normally be available on $wire
    // available directly without needing to prefix "$wire.".
    Alpine.bind(el, {
        'x-data'() { return component.synthetic.reactive },
        // Disabling this for laracon...
        'x-destroy'() {
            releaseComponent(component.id)
        }
    })

    storeComponent(component.id, component)
}

export function closestComponent(el) {
    let closestRoot = Alpine.findClosest(el, i => i.__livewire)

    if (! closestRoot) {
        throw "Could not find Livewire component in DOM tree"
    }

    return closestRoot.__livewire
}
