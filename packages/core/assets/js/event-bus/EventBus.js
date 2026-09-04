/** @import { AppEvent } from '@core/event/AppEvent' */

/**
 * A simple app-wide pub/sub bus built on the native `EventTarget`, unrelated to the DOM —
 * for broadcasting your own event classes (each extending `AppEvent`, like `NotificationEvent`)
 * to any part of the app.
 */
class EventBus extends EventTarget {
  /**
   * Emits `event` to every listener registered for its class via `.on()`.
   * @param {AppEvent} event - an instance of a custom event class, e.g. `new NotificationEvent(...)`.
   */
  emit(event) {
    this.dispatchEvent(event);
  }

  /**
   * Calls `handler` with the event instance whenever `.emit()` is called with an instance of
   * `EventClass`.
   * @template {AppEvent} T
   * @param {{new(...args: any[]): T, name: string}} EventClass - the custom event class to listen for, e.g. `NotificationEvent`.
   * @param {(event: T) => void} handler
   */
  on(EventClass, handler) {
    this.addEventListener(EventClass.name, handler);
  }
}

export default new EventBus();