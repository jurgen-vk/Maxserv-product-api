import bus from '@core/event-bus/EventBus';
import { NotificationEvent } from '@core/event/NotificationEvent';

/**
 * Convenience wrappers for showing a toaster notification, one per variant — each just
 * builds a `NotificationEvent` and emits it on the shared `EventBus`.
 */
const notify = {
  /**
   * Shows a success notification in the toaster.
   * @param {string} message - message.
   * @param {string} [icon] - defaults to a preset icon for this variant.
   * @param {number} [duration] - how long to show it, in milliseconds. Defaults to 3000.
   */
  success(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'success', icon, duration));
  },

  /**
   * Shows a danger notification in the toaster.
   * @param {string} message - message.
   * @param {string} [icon] - defaults to a preset icon for this variant.
   * @param {number} [duration] - how long to show it, in milliseconds. Defaults to 3000.
   */
  danger(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'danger', icon, duration));
  },

  /**
   * Shows a warning notification in the toaster.
   * @param {string} message - message.
   * @param {string} [icon] - defaults to a preset icon for this variant.
   * @param {number} [duration] - how long to show it, in milliseconds. Defaults to 3000.
   */
  warning(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'warning', icon, duration));
  },

  /**
   * Shows an info notification in the toaster.
   * @param {string} message - message.
   * @param {string} [icon] - defaults to a preset icon for this variant.
   * @param {number} [duration] - how long to show it, in milliseconds. Defaults to 3000.
   */
  info(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'info', icon, duration));
  },

  /**
   * Shows a default-styled notification in the toaster.
   * @param {string} message - message.
   * @param {string} [icon] - defaults to a preset icon for this variant.
   * @param {number} [duration] - how long to show it, in milliseconds. Defaults to 3000.
   */
  default(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'default', icon, duration));
  }
};

export { notify };