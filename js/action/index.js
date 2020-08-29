export default class {
    constructor(el, skipWatcher = false) {
        this.el = el
        this.skipWatcher = skipWatcher
        this.resolveCallback = () => {}
        this.rejectCallback = () => {}
    }

    get ref() {
        return this.el ? this.el.ref : null
    }

    toId() {
        return btoa(encodeURIComponent(this.el.el.outerHTML))
    }

    onResolve(callback) {
        this.resolveCallback = callback
    }

    onReject(callback) {
        this.rejectCallback = callback
    }

    resolve(thing) {
        this.resolveCallback(thing)
    }

    reject(thing) {
        this.rejectCallback(thing)
    }
}
