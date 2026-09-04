declare global {
  interface JQuery {
    /**
     * Runs `callback` once, immediately (before `.found()` itself returns), if this
     * collection matched at least one element. `this` and the first argument are both the
     * same collection — `this` as a plain array (via `.toArray()`), the argument as the
     * original `JQuery` object.
     */
    found(callback: (this: HTMLElement[], $root: JQuery) => void): JQuery;

    /**
     * Runs `callback` once per matched element, immediately (before `.foundEach()` itself
     * returns). Unlike `.found()`, this fires per-element: `this` is the raw, unwrapped
     * element, matching native jQuery's own `.each()` convention; the jQuery-wrapped version
     * is only the first argument.
     */
    foundEach(callback: (this: HTMLElement, $el: JQuery, index: number) => void): JQuery;

    /** `true` if this collection matched at least one element. Equivalent to `.length > 0`. */
    readonly exist: boolean;
  }
}

export {};