import { AppEvent } from '@core/event/AppEvent';

/**
 * A notification to show in the toaster, created via `notify.*()` and emitted onto the bus
 * for `components.ui.toaster` to render.
 */
class NotificationEvent extends AppEvent {
  /**
   * @param {string} message
   * @param {'success'|'warning'|'danger'|'info'|'default'} [variant='default']
   * @param {string} [icon] - defaults to a preset icon based on `variant`.
   * @param {number} [duration=3000] - how long to show the notification, in milliseconds.
   */
  constructor(message, variant = 'default', icon = NotificationEvent.#defaultIconMap[variant] ?? NotificationEvent.#defaultIconMap['default'], duration = 3000) {
    super();
    this.message = message;
    this.variant = NotificationEvent.#defaultIconMap[variant] ? variant : 'default';
    this.icon = icon;
    this.duration = duration;
  }

  static #defaultIconMap = {
    'success': 'lucide:message-square-check',
    'warning': 'lucide:message-square-warning',
    'danger': 'lucide:message-square-x',
    'info': 'lucide:message-square-dot',
    'default': 'lucide:message-square'
  };
}

export { NotificationEvent };