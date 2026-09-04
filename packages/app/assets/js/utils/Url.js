/**
 * A `URL` that also knows how to push or replace itself onto the browser's history, using
 * the current page's URL when constructed without one.
 */
class Url extends URL {
  /**
   * @param {string|URL} [url=window.location.href]
   * @param {string|URL} [base]
   */
  constructor(url = window.location.href, base) {
    super(url, base);
  }

  /** State object to pass along on the next `.push()` or `.replace()` call. */
  state = null;

  /** @returns {Location} */
  get location() {
    return window.location;
  }

  /** @returns {History} */
  get history() {
    return window.history;
  }

  /** Pushes this URL onto the browser history as a new entry, carrying `.state` with it. */
  push() {
    this.history.pushState(this.state, '', this.href);
  }

  /** Replaces the current history entry with this URL, carrying `.state` with it. */
  replace() {
    this.history.replaceState(this.state, '', this.href);
  }
}

export { Url };

