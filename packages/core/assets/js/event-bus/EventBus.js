class EventBus extends EventTarget {
  emit(event) {
    this.dispatchEvent(new CustomEvent(event.constructor.name, { detail: event }));
  }

  on(EventClass, handler) {
    this.addEventListener(EventClass.name, (nativeEvent) => handler(nativeEvent.detail));
  }
}

export default new EventBus();