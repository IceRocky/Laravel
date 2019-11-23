import EventAction from "@/action/event";
import HookManager from "@/HookManager";
import MessageBus from "./MessageBus";

const store = {
    componentsById: {},
    listeners: new MessageBus,
    beforeDomUpdateCallback: () => {},
    afterDomUpdateCallback: () => {},
    livewireIsInBackground: false,
    livewireIsOffline: false,
    hooks: HookManager,

    components() {
        return Object.keys(this.componentsById).map(key => {
            return this.componentsById[key]
        })
    },

    addComponent(component) {
        return this.componentsById[component.id] = component
    },

    findComponent(id) {
        return this.componentsById[id]
    },

    hasComponent(id) {
        return !! this.componentsById[id]
    },

    tearDownComponents() {
        this.components().forEach(component => {
            this.removeComponent(component)
        })
    },

    on(event, callback) {
        this.listeners.register(event, callback)
    },

    emit(event, ...params) {
        this.listeners.call(event, ...params)

        this.componentsListeningForEvent(event).forEach(
            component => component.addAction(new EventAction(
                event, params
            ))
        )
    },

    componentsListeningForEvent(event) {
        return this.components().filter(component => {
            return component.events.includes(event)
        })
    },

    registerHook(name, callback) {
        this.hooks.register(name, callback)
    },

    callHook(name, ...params) {
        this.hooks.call(name, ...params)
    },

    beforeDomUpdate(callback) {
        this.beforeDomUpdateCallback = callback
    },

    afterDomUpdate(callback) {
        this.afterDomUpdateCallback = callback
    },

    removeComponent(component) {
        // Remove event listeners attached to the DOM.
        component.tearDown()
        // Remove the component from the store.
        delete this.componentsById[component.id]
    }
}

export default store
