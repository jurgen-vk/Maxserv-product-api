declare global {
  interface JQuery {
    /** Hides every matched element by setting its `hidden` attribute,
     * replacing jQuery's own display-based `.hide()`.
     */
    hide(): JQuery;

    /** Shows every matched element by clearing its `hidden` attribute,
     * replacing jQuery's own display-based `.show()`.
     */
    show(): JQuery;

    /**
     * Shows or hides every matched element based on `state`. If `state` is omitted, toggles
     * based on the *first* matched element's current `hidden` property — for a multi-element
     * collection, every element still moves together, but the direction is decided by the
     * first one only.
     */
    toggle(state?: boolean): JQuery;
  }
}

export {};