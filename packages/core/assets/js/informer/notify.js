import bus from '@core/event-bus/EventBus';
import { NotificationEvent } from '@core/event/NotificationEvent';

export default {
  success(message, icon, duration) {
    bus.emit(new NotificationEvent('success', message, icon, duration));
  },

  danger(message, icon, duration) {
    bus.emit(new NotificationEvent('danger', message, icon, duration));
  },

  warning(message, icon, duration) {
    bus.emit(new NotificationEvent('warning', message, icon, duration));
  },

  info(message, icon, duration) {
    bus.emit(new NotificationEvent('info', message, icon, duration));
  },

  default(message, icon, duration) {
    bus.emit(new NotificationEvent('default', message, icon, duration));
  }
};
