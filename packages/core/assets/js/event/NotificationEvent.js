class NotificationEvent {
  constructor(message, type, icon, duration) {
    this.message = message;
    this.type = type ?? 'default';
    this.icon = icon
      ?? NotificationEvent.#defaultIconMap[type]
      ?? NotificationEvent.#defaultIconMap['default'];
    this.duration = duration ?? 3000;
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