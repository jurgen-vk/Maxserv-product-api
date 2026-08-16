import $ from 'jquery';
import { fetchFragment } from '@core/api/fragmentFetcher';

async function swapTable(tableFragment, params, {signal} = {}) {
  const $tableFragment = $(tableFragment);
  const table = await fetchFragment('/products', '_products-table', {params, signal});

  if (table != null) {
    $tableFragment.replaceWith(table);
  }

  return table;
}

export { swapTable };
