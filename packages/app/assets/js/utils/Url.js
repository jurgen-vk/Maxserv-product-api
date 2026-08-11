class Url extends URL {
  constructor() {
    super(window.location.href);
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

