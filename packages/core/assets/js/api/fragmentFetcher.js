export async function fetchFragments(path, fragments, {params = {}, excludeUrlParams = false, signal} = {}) {
  const base = excludeUrlParams
    ? {}
    : Object.fromEntries(new URLSearchParams(window.location.search));
  delete base.fragments;

  const merged = {...base, ...params};

  for (const [key, value] of Object.entries(merged)) {
    if (value == null) delete merged[key];
  }

  const search = new URLSearchParams(merged);
  fragments.forEach((fragment) => search.append("fragments[]", fragment));

  const response = await fetch(`${path}?${search}`, {signal});
  return response.json();
}

export async function fetchFragment(path, fragment, options) {
  const fragments = await fetchFragments(path, [fragment], options);
  return fragments[fragment];
}