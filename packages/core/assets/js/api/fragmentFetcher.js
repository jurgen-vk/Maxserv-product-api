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

async function fetchFragment(path, fragment, options) {
  const fragments = await fetchFragments(path, [fragment], options);
  return fragments[fragment];
}

export {
  fetchFragment,
  fetchFragments
};
