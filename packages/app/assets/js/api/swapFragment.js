import $ from 'jquery';
import { fetchFragment } from '@core/api/fragmentFetcher';

/**
 * Fetches a single named fragment from the server and replaces `root` with the result in
 * the DOM. Returns the replacement content as a live `JQuery` object — empty if the server
 * sent back nothing to swap in.
 * @param {string|HTMLElement|JQuery} root - the element (or selector) to replace.
 * @param {string} path - the URL to fetch the fragment from.
 * @param {string} fragment - name of the fragment to request.
 * @param {URLSearchParams} params - query params to send along with the request.
 * @param {{signal?: AbortSignal}} [options]
 * @returns {Promise<JQuery>}
 */
async function swapFragment(root, path, fragment, params, {signal} = {}) {
  const $root = $(root);
  const html = await fetchFragment(path, fragment, {params, signal});
  const $new = $(html);

  if ($new.exist) {
    $root.replaceWith($new);
  }

  return $new;
}

export { swapFragment };
