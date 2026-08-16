import $ from 'jquery';
import { Url } from '@app/utils/Url';

const $root = $('.p_products__products-table__filter-panel');
const pageParam = $root.data('pageParam');
const ownParams = ['category', 'brand', 'minPrice', 'maxPrice', 'minRating'];

$root.find('.filter-form').on('submit', function (event) {
  event.preventDefault();

  const url = new Url();
  new FormData(this).forEach((value, key) => {
    if (value) {
      url.searchParams.set(key, value);
    } else {
      url.searchParams.delete(key);
    }
  });
  url.searchParams.delete(pageParam);
  url.push();

  document.getElementById('products-filter-panel').hidePopover();
});

// delegated on the document: the clear-filters trigger also lives in the
// table's own empty-state, unrelated by DOM position to $root above — each
// click resolves the correct panel via the shared page ancestor instead of
// assuming there's only one panel on the page
$(document).on('click', '.filter-clear', function (event) {
  event.preventDefault();

  const $panel = $(this).closest('.p_products').find('.p_products__products-table__filter-panel');
  const $form = $panel.find('.filter-form');
  const panelPageParam = $panel.data('pageParam');

  $form[0].reset(); // native reset — select/index.js resyncs select2 off this

  const url = new Url();
  const keysToDelete = [...ownParams, panelPageParam];
  keysToDelete.forEach((key) => url.searchParams.delete(key));
  url.push();

  document.getElementById('products-filter-panel').hidePopover();
});
