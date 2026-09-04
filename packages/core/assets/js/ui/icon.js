const resolved = new Map();

function applyAttributes(svg, attributes) {
  const document = new DOMParser().parseFromString(svg, 'image/svg+xml');

  const parserErrorTag = 'parsererror'; // synthetic element DOMParser inserts on failure, not a real tag
  if (document.querySelector(parserErrorTag)) {
    throw new Error('Malformed SVG.');
  }

  const root = document.documentElement;

  const {class: className, ...rest} = attributes;
  root.setAttribute('class', ['icon', className].filter(Boolean).join(' '));

  for (const [attribute, value] of Object.entries(rest)) {
    if (value === true) {
      root.setAttribute(attribute, '');
    } else if (value === false) {
      root.removeAttribute(attribute);
    } else {
      root.setAttribute(attribute, String(value));
    }
  }

  return new XMLSerializer().serializeToString(root);
}

/**
 * Fetches (and caches) an SVG icon by name and returns its markup with the given attributes
 * applied to the root `<svg>` element. Any error (missing name, fetch failure, malformed SVG)
 * is logged to the console and resolves to an empty string rather than rejecting.
 * @param {string} name - icon identifier, e.g. `'lucide:message-square-check'` - maps to
 * `/assets/icons/lucide/message-square-check.svg`.
 * @param {Record<string, string|boolean|number>} [attributes] - attributes to set on the
 * `<svg>` root. `class` is appended to the icon's own base class rather than replacing it.
 * A boolean value adds or removes the attribute entirely (`true` sets it with no value,
 * `false` removes it) - pass the string `'true'`/`'false'` instead if you need the attribute
 * present with that literal value, e.g. `aria-hidden`. Anything else is stringified.
 * @returns {Promise<string>} the icon's SVG markup, or `''` if it couldn't be loaded.
 */
async function icon(name, attributes = {}) {
  try {
    if (!name) {
      // noinspection ExceptionCaughtLocallyJS
      throw new Error('No icon name provided.');
    }

    if (!resolved.has(name)) {
      const response = await fetch(
        `/assets/icons/${name.replaceAll(':', '/')}.svg`
      );

      if (!response.ok) {
        // noinspection ExceptionCaughtLocallyJS
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

export default icon;