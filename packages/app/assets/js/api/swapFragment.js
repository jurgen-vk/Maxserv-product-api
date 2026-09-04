import $ from 'jquery';
import { fetchFragment } from '@core/api/fragmentFetcher';

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
