/**
 * Wraps `fn` so it only actually runs once calls to the wrapper stop coming in for `wait`
 * milliseconds - each new call resets the delay, discarding any call still pending.
 * @template {(...args: any[]) => any} F
 * @param {F} fn - the function to debounce.
 * @param {number} wait - delay in milliseconds.
 * @returns {F & {cancel: () => void}} the debounced wrapper, callable exactly like `fn`, with
 * an extra `.cancel()` method to discard a pending call before it runs.
 */
function debounce(fn, wait) {
  let timer;

  function debounced(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), wait);
  }

  debounced.cancel = () => clearTimeout(timer);

  return debounced;
}

export default debounce;
