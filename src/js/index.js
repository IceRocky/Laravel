import drivers from './connection/drivers'
import Connection from './connection'
import store from './store'
import Component from "./component";
import DomElement from "./dom/element";

class Livewire {
    constructor({ driver } = { driver: 'http' }) {
        if (typeof driver !== 'object') {
            driver = drivers[driver]
        }

        this.connection = new Connection(driver)
        this.components = store

        this.start()
    }

    emit(event, ...params) {
        this.components.emit(event, ...params)
    }

    restart() {
        this.stop()
        this.start()
    }

    stop() {
        this.components.wipeComponents()
    }

    start() {
        DomElement.rootComponentElementsWithNoParents().forEach(el => {
            this.components.addComponent(
                new Component(el, this.connection)
            )
        })
    }
}

if (!window.Livewire) {
    window.Livewire = Livewire
}

export default Livewire
