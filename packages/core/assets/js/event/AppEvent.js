/**
 * Base class for the app's own custom events, dispatched through `EventBus`. Automatically
 * sets the native `type` to the class's own name, so subclasses never have to pass one.
 */
class AppEvent extends Event {
  constructor() {
    super(new.target.name);
  }
}

export { AppEvent };