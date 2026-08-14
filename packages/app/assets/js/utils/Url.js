class Url extends URL {
  constructor(url = null, base = null) {
    super(url ?? window.location.href, base);
  }

  state = null;

  get location() {
    return window.location;
  }

  get history() {
    return window.history;
  }

  push() {
    this.history.pushState(this.state, '', this.href);
  }

  replace() {
    this.history.replaceState(this.state, '', this.href);
  }
}

export { Url };

