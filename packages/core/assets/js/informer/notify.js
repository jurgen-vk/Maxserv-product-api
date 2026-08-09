import bus from '@core/event-bus/EventBus';
import { NotificationEvent } from '@core/event/NotificationEvent';

export default {
  success(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'success', icon, duration));
  },

  danger(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'danger', icon, duration));
  },

  warning(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'warning', icon, duration));
  },

  info(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'info', icon, duration));
  },

  default(message, icon, duration) {
    bus.emit(new NotificationEvent(message, 'default', icon, duration));
  }
};
