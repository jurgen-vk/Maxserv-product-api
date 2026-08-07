export class NotificationEvent {
  constructor(type, message, iconName, duration) {
    this.type = type;
    this.message = message;
    this.icon = iconName ?? NotificationEvent.#defaultIconMap[type];
    this.duration = duration ?? 3000;
  }

  static #defaultIconMap = {
    'success': 'lucide:message-square-check',
    'warning': 'lucide:message-square-warning',
    'danger': 'lucide:message-square-x',
    'info': 'lucide:message-square-dot',
    'default': 'lucide:message-square',
  };
}
