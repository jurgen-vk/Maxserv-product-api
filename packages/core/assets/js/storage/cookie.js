/**
 * Minimal wrapper around `document.cookie` for reading and writing simple cookies.
 */
const cookie = {
  /**
   * Sets a cookie, available site-wide (`path=/`).
   * @param {string} name
   * @param {string} value
   * @param {number} [days=365] - how long until the cookie expires, in days.
   */
  set(name, value, days = 365) {
    const expires = new Date(Date.now() + days * 86400000).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`;
  },

  /**
   * Reads a cookie's value.
   * @param {string} name
   * @returns {string|null} the cookie's value, or `null` if it isn't set.
   */
  get(name) {
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
    return match ? decodeURIComponent(match[1]) : null;
  }
};

export default cookie;