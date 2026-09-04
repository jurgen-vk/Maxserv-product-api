declare global {
  interface JQuery {
    /**
     * Dispatches a genuine native DOM event via `dispatchEvent` on every matched element —
     * unlike jQuery's own `.trigger()`, this reaches native `addEventListener` handlers too,
     * not just jQuery-bound ones. Pass either an event name (picks the correct native
     * constructor automatically — `CustomEvent` if `options.detail` is set, plain `Event`
     * otherwise) or an already-constructed `Event` instance. `bubbles`/`cancelable` default
     * to `true` unless overridden in `options`.
     */
    fire(eventOrName: string | Event, options?: EventInit & Record<string, unknown>): JQuery;
  }
}

export {};