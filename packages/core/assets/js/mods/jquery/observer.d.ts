type SelectorInput = string | JQuery | Node;

/**
 * Space-separated string (e.g. `'added removed.myNamespace attributeFilter:data-state'`) or
 * an equivalent object. Each flag below is `true` (on), a string (on, namespaced), or
 * omitted/`false` (off). `childlist` is shorthand for `added` + `removed`.
 * `all` expands to every real flag except `childlist`, that is already handled by
 * `added` and `removed`.
 */
interface WatchOptionsObject {
  added?: boolean | string;
  removed?: boolean | string;
  childlist?: boolean | string;
  attributes?: boolean | string;
  subtree?: boolean | string;
  attributeOldValue?: boolean | string;
  characterData?: boolean | string;
  characterDataOldValue?: boolean | string;
  all?: boolean | string;
  attributeFilter?: string[];
  namespace?: string;
}

type WatchOptions = string | WatchOptionsObject;

type WatchCallback = (this: Node[], $targets: JQuery, mutationList: MutationRecord[]) => void;
type WatchEachCallback = (this: HTMLElement, $target: JQuery, mutationList: MutationRecord[], index: number) => void;

declare global {
  interface JQuery {
    /**
     * Watches every matched element for DOM changes using the browser's native
     * `MutationObserver`, sharing one observer instance across the whole collection.
     * `config` and `callback` are exactly what you'd pass to `new MutationObserver()`
     * directly.
     */
    observe(config: MutationObserverInit, callback: MutationCallback): JQuery;

    /**
     * Calls `callback` whenever a change matching `options` happens on an element matching
     * `selector`, anywhere inside this element — including elements that don't exist yet
     * when you call `.watch()`, since matching happens live as changes occur. Calling this
     * again with the same selector and callback just updates what it's watching for, so
     * it's safe to call more than once without worrying about it firing twice.
     */
    watch(options: WatchOptions, selector: SelectorInput, callback: WatchCallback): JQuery;

    /**
     * Same as the selector form, but watches this element itself rather than scoping to
     * descendants — `$targets` is whatever the change directly reports (the node that was
     * added/removed, the element whose attribute changed, etc).
     */
    watch(options: WatchOptions, callback: WatchCallback): JQuery;

    /**
     * Adjusts the types being watched for an existing `.watch()` call (optionally scoped to
     * `selector`) without needing to keep a reference to its original callback — handy for
     * broadening or narrowing what an already-registered watch listens for. Does nothing
     * (and logs a warning) if there's nothing currently being watched that matches.
     */
    watch(options: WatchOptions, selector?: SelectorInput): JQuery;

    /**
     * Same as `.watch()`, but calls `callback` once per matching element individually,
     * instead of once with all of them together as `$targets`.
     */
    watchEach(options: WatchOptions, selector: SelectorInput, callback: WatchEachCallback): JQuery;

    /** Same as `.watchEach()`, watching this element itself rather than scoping to descendants. */
    watchEach(options: WatchOptions, callback: WatchEachCallback): JQuery;

    /**
     * Stops watching for the given types (optionally only for watches scoped to `selector`),
     * without necessarily canceling the whole watch if it's also listening for other types.
     * A watch gets removed automatically once you have removed all its watch options.
     */
    unwatch(options: WatchOptions, selector?: SelectorInput): JQuery;
  }
}

export {};