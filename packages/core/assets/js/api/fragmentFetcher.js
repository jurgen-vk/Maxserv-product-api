/**
 * @typedef {Object} FetchFragmentsOptions
 * @property {URLSearchParams} [params] - extra query params to send, overriding any
 * same-named params already present on the current page's URL. Defaults to none.
 * @property {boolean} [excludeUrlParams=false] - if `true`, don't carry over the current
 * page's own URL params at all; only `params` is sent.
 * @property {AbortSignal} [signal] - aborts the fetch if triggered before it resolves.
 */

/**
 * Fetches one or more named fragments from `path` and returns them as a map keyed by
 * fragment name. By default, merges in the current page's own URL query params, and
 * always strips out a `fragments` param before sending, so the request doesn't recurse.
 * @param {string} path - the URL to fetch from, without query string.
 * @param {string[]} fragments - names of the fragments to request.
 * @param {FetchFragmentsOptions} [options]
 * @returns {Promise<Record<string, string>>} the requested fragments' HTML, keyed by fragment name.
 */
async function fetchFragments(path, fragments, {params = new URLSearchParams(), excludeUrlParams = false, signal} = {}) {
  const search = excludeUrlParams ? new URLSearchParams() : new URLSearchParams(window.location.search);
  search.delete('fragments');

  new Set(params.keys()).forEach((key) => search.delete(key));
  for (const [key, value] of params.entries()) {
    search.append(key, value);
  }

  fragments.forEach((fragment) => search.append('fragments[]', fragment));

  const response = await fetch(`${path}?${search}`, {signal});
  return response.json();
}

/**
 * Fetches a single named fragment from `path`. By default, merges in the current page's own
 * URL query params, and always strips out a `fragments` param before sending, so the request
 * doesn't recurse.
 * @param {string} path - the URL to fetch from, without query string.
 * @param {string} fragment - name of the fragment to request.
 * @param {FetchFragmentsOptions} [options]
 * @returns {Promise<string>} the requested fragment's HTML.
 */
async function fetchFragment(path, fragment, options) {
  const fragments = await fetchFragments(path, [fragment], options);
  return fragments[fragment];
}

export {
  fetchFragment,
  fetchFragments
};
