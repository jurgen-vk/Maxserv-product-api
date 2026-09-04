/**
 * Waits for `ms` milliseconds before resolving.
 * @param {number} ms - how long to wait, in milliseconds.
 * @returns {Promise<void>}
 */
async function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

export default sleep;
