class Mercure {
  #url = null;

  configure(url) {
    this.#url = url;
  }

  subscribe(topic, onMessage) {
    if (!this.#url) {
      throw new Error('Mercure.subscribe() called before Mercure.configure()');
    }

    const source = new EventSource(`${this.#url}?topic=${encodeURIComponent(topic)}`);
    const unsubscribe = () => source.close();

    source.onmessage = (event) => {
      onMessage(JSON.parse(event.data), unsubscribe);
    };

    return unsubscribe;
  }
}

export default new Mercure();