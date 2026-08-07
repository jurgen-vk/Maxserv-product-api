const resolved = new Map();

function applyAttributes(svg, attributes) {
  const document = new DOMParser().parseFromString(svg, 'image/svg+xml');

  if (document.querySelector('parsererror')) {
    throw new Error('Malformed SVG.');
  }

  const root = document.documentElement;

  const { class: className, ...rest } = attributes;
  root.setAttribute('class', ['icon', className].filter(Boolean).join(' '));

  for (const [attribute, value] of Object.entries(rest)) {
    root.setAttribute(attribute, value === true ? 'true' : value === false ? 'false' : String(value));
  }

  return new XMLSerializer().serializeToString(root);
}

// Fetched icons are cached in memory for the lifetime of the page, so a
// repeated name skips the network entirely rather than re-fetching — actual
// cross-visit caching is handled by the browser's HTTP cache, per the
// Cache-Control headers set on /assets/icons/* itself.
export async function icon(name, attributes = {}) {
  try {
    if (!name) {
      throw new Error('No icon name provided.');
    }

    if (!resolved.has(name)) {
      const response = await fetch(`/assets/icons/${name.replaceAll(':', '/')}.svg`);

      if (!response.ok) {
        throw new Error(`Icon not found: ${name}`);
      }

      resolved.set(name, await response.text());
    }

    return applyAttributes(resolved.get(name), attributes);
  } catch (error) {
    console.error(error);
    return '';
  }
}