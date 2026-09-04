/**
 * A thin wrapper around Mercure's SSE hub, letting you subscribe to a topic without dealing
 * with `EventSource` or JSON-parsing messages yourself.
 */
class Mercure {
  #url = null;

  /**
   * Sets the Mercure hub URL to subscribe through. Must be called before `.subscribe()`.
   * @param {string} url
   */
  configure(url) {
    this.#url = url;
  }

  /**
   * Opens a subscription to `topic`, calling `onMessage` with each message's parsed JSON
   * payload as it arrives.
   * @param {string} topic
   * @param {(data: Object, unsubscribe: () => void) => void} onMessage
   * @returns {() => void} a function that closes the subscription when called.
   * @throws {Error} if called before `.configure()`.
   */
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